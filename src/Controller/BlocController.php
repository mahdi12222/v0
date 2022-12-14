<?php

namespace App\Controller;

use App\Entity\Bloc;
use App\Form\BlocType;
use App\Repository\BlocRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/bloc")
 */
class BlocController extends AbstractController
{
    /**
     * @Route("/", name="bloc_index", methods={"GET"})
     */
    public function index(BlocRepository $blocRepository): Response
    {
        return $this->render('bloc/index.html.twig', [
            'blocs' => $blocRepository->findAll(),
            'current_menu' => 'blocs'
        ]);
    }

    /**
     * @Route("/new", name="bloc_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $bloc = new Bloc();

        $form = $this->createForm(BlocType::class, $bloc);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($bloc);
            $entityManager->flush();
            $this->addFlash('success', 'Bloc créé avec succès.');

            return $this->redirectToRoute('bloc_index');
        }

        return $this->render('bloc/new.html.twig', [
            'bloc' => $bloc,
            'current_menu' => 'blocs',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="bloc_show", methods={"GET"})
     * @param Bloc $bloc
     * @return Response
     */
    public function show(Bloc $bloc): Response
    {
        return $this->render('bloc/show.html.twig', [
            'bloc' => $bloc,
            'current_menu' => 'blocs'
        ]);
    }

    /**
     * @Route("/{id}/edit", name="bloc_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Bloc $bloc): Response
    {
        $form = $this->createForm(BlocType::class, $bloc);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Bloc modifié avec succès.');

            return $this->redirectToRoute('bloc_index');
        }

        return $this->render('bloc/edit.html.twig', [
            'bloc' => $bloc,
            'form' => $form->createView(),
            'current_menu' => 'blocs'
        ]);
    }

    /**
     * @Route("/{id}", name="bloc_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Bloc $bloc): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bloc->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($bloc);
            $entityManager->flush();
            $this->addFlash('success', 'Bloc supprimé avec succès.');
        }

        return $this->redirectToRoute('bloc_index');
    }

}
