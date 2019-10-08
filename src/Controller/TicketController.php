<?php

namespace App\Controller;

use App\Entity\Bloc;
use App\Form\TicketType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/tickets")
 */
class TicketController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @Route("/", name="ticket_index")
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Response
    {
        $glpi = new GLPIController();
        $glpi->connect();
        $tickets = $glpi->getItem('Ticket');
        $tickets = is_array($tickets) ? $tickets : [$tickets];
        return $this->render('tickets/index.html.twig', [
            'tickets' => $tickets,
            'current_menu' => 'tickets'
        ]);
    }

    /**
     * @Route("/{bloc}/new", name="ticket_new", methods={"GET","POST"})
     * @param Bloc $bloc
     * @return Response
     */
    public function new(Bloc $bloc, Request $request): Response
    {
        $form = $this->createForm(TicketType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $glpi = new GLPIController();
            $ticket = $glpi->addTicket($bloc->getType(), $bloc->getGlpicategory(), $data['titre'], $data['contenu']);
            $ticketid = $ticket->id;

//            $doc = $glpi->addDocument(2019090544, $data['piece']);

            $this->addFlash('success', 'Ticket créé avec succès.');
            return $this->redirectToRoute('ticket_show', ['id' => $ticketid]);
        }

        return $this->render('tickets/new_ticket.html.twig', [
            'bloc' => $bloc,
            'current_menu' => 'blocs',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_show", methods={"GET", "POST"})
     * @param $id
     * @param Request $request
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show($id, Request $request): Response
    {
        $form = $this->createForm(TicketType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $glpi = new GLPIController();
            $ticket = $glpi->addTicketFollowup($id, $data['note']);

            $this->addFlash('success', 'Note ajoutée avec succès.');

            unset($form);
            $form = $this->createForm(TicketType::class);
        }

        $glpi = new GLPIController();
        $glpi->connect();

        $ticket = $glpi->getItem('Ticket',  $id);
        $users = $glpi->getTicketUsers($id);
        $items = $glpi->getTicketItems($id);
        $histo = $glpi->getTicketHisto($id);

        return $this->render('tickets/detail.html.twig', [
            'ticket' => $ticket,
            'users' => $users,
            'items' => $items,
            'histo' => $histo,
            'current_menu' => 'tickets',
            'form' => $form->createView()
        ]);
    }

}