<?php

namespace App\Controller;

use App\DTO\WorksCreateFromInput;
use App\Entity\Works;
use App\Form\WorksType;
use App\Repository\WorksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WorkController extends AbstractController
{
    public function __construct(
        private WorksRepository $worksRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/works', name: 'app_works', methods: ['GET'])]
    public function worksList(Request $request): Response
    {
        $worksList = $this->worksRepository->findAll();

        return $this->render('work/works.html.twig', [
            'worksList' => $worksList,
        ]);
    }

    #[Route('/works/create', name: 'app_works_form', methods: ['GET'])]
    public function addWorksForm(): Response
    {
        $worksForm = $this->createForm(WorksType::class, new WorksCreateFromInput());
        
        return $this->render('work/form.html.twig', compact('worksForm'));
    }

    #[Route('/works/create', name: 'app_add_works', methods: ['POST'])]
    public function addWorks(Request $request)
    {
        $input = new WorksCreateFromInput();
        $worksForm = $this->createForm(WorksType::class, $input)
            ->handleRequest($request);
        
        if(!$worksForm->isValid()) {
            return $this->render('work/form.html.twig', compact('worksForm'));
        }
        
        $works = $this->worksRepository->add($input);

        $this->addFlash(
            'success',
            "A new work \"{$works->getName()}\" was added with success"
        );

        return new RedirectResponse('/works');
    }

    #[Route('/works/delete/{id}', name: 'app_delete_series', methods: ['DELETE'], requirements: ['id' => '[0-9]+'])]
    public function deleteWorks(int $id, Request $request): Response
    {
        $this->worksRepository->removeById($id);
        $this->addFlash('success', 'The work was removed');

        return new RedirectResponse('/works');
    }

    #[Route('/works/edit/{works}', name: 'app_edit_works_form', methods: ['GET'])]
    public function editWorksForm(Works $works)
    {
        $worksForm = $this->createForm(WorksType::class, $works, ['is_edit' => true]);
        return $this->render('work/form.html.twig', compact('worksForm', 'works'));
    }

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

