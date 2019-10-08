<?php

namespace App\Controller;

use App\Entity\Bloc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class HomeController extends AbstractController
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
     * @Route("/", name="home")
     * @return Response
     */
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Bloc::class);
        $blocs = $repository->findRoot();
        return $this->render('pages/home.html.twig', [
            'blocs' => $blocs,
            'current_menu' => 'home'
        ]);
    }

    /**
     * @Route("/bloc-{id}", name="home_bloc")
     * @param $id
     * @return Response
     */
    public function bloc($id): Response
    {
        $repository = $this->getDoctrine()->getRepository(Bloc::class);
        $parent = $repository->find($id);
        if($parent->getType() > 0)
        {
            return $this->redirectToRoute('ticket_new', [
                'bloc' => $id
                ]
            );
        }
        else
        {
            $blocs = $repository->findByParent($id);
            return $this->render('pages/home.html.twig', [
                'blocs' => $blocs,
                'current_menu' => 'home'
            ]);
        }

    }
}