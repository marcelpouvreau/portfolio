<?php

namespace App\Controller;

use App\Entity\Works;
use App\Form\WorksType;
use App\Service\FileUploader;
use App\Repository\WorksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WorkController extends AbstractController
{
    public function __construct(
        private FileUploader $fileUploader,
        private WorksRepository $worksRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/works', name: 'app_works', methods: ['GET'])]
    public function worksList(WorksRepository $worksRepository): Response
    {
        $worksList = $worksRepository->findAll();

        return $this->render('work/works.html.twig', [
            'worksList' => $worksList,
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/works/create', name: 'app_works_form', methods: ['GET'])]
    public function addWorksForm(): Response
    {
        $worksForm = new Works();
        $form = $this->createForm(WorksType::class, $worksForm);
        
        return $this->render('work/form.html.twig', [
            'worksForm' => $form->createView()
    ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/works/create', name: 'app_add_works', methods: ['POST'])]
    public function addWorks(
        Request $request,
        WorksRepository $repository,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager
        ): Response
    {
        $works = new Works();
        $form = $this->createForm(WorksType::class, $works)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if($file) {
                $imageFile = $fileUploader->upload($file);
                $works->setImage($imageFile);
            }

            $entityManager->persist($works);
            $entityManager->flush();

            $works = $form->getData();
            $repository->add($works, true);

            return $this->redirectToRoute('app_works');
        }

        return $this->render('work/form.html.twig', [
            'worksForm' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/works/delete/{id}',
        name: 'app_delete_works',
        methods: ['DELETE'],
        requirements: ['id' => '[0-9]+']
    )]
    public function deleteWorks(int $id, Request $request): Response
    {
        $this->worksRepository->removeById($id);
        $this->addFlash('success', 'The work was removed');

        return new RedirectResponse('/works');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/works/edit/{works}', name: 'app_edit_works_form', methods: ['GET'])]
    public function editWorksForm(Works $works)
    {
        $worksForm = $this->createForm(WorksType::class, $works, ['is_edit' => true]);
        return $this->render('work/form.html.twig', compact('worksForm', 'works'));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/works/edit/{works}', name: 'app_store_works_changes', methods: ['PATCH'])]
    public function storeWorksChanges(Works $works, Request $request): Response
    {
        $worksForm = $this->createForm(WorksType::class, $works, ['is_edit' => true]);
        $worksForm->handleRequest($request);

        if(!$worksForm->isValid()) {
            return $this->render('work/form.html.twig', compact('worksForm', 'works'));
        }

        $this->addFlash('success', "Work \"{$works->getName()}\" edited with success.");
        $this->entityManager->flush();

        return new RedirectResponse('/works');
    }    
}

