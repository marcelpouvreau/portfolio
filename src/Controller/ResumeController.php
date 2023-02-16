<?php

namespace App\Controller;

use App\Entity\Resume;
use App\Form\ResumeType;
use App\Repository\ResumeRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;

class ResumeController extends AbstractController
{
    public function __construct(
        private FileUploader $fileUploader,
        private ResumeRepository $resumeRepository,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/resume', name: 'app_resume_list', methods: ['GET'])]
    public function resumeList(ResumeRepository $resumeRepository): Response
    {
        $resumeList = $resumeRepository->findAll();

        return $this->render('resume/resume.html.twig', [
            'resumeList' => $resumeList,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/resume/create', name: 'app_resume_form', methods: ['GET'])]
    public function addResumeForm(): Response
    {
        $resume = new Resume();
        $form = $this->createForm(ResumeType::class, $resume);
        
        return $this->render('resume/form.html.twig', [
            'resumeForm' => $form->createView()
    ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/resume/create', name: 'app_add_resume', methods: ['POST'])]
    public function addResume(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager): Response
    {

        $resume = new Resume();
        $resumeForm = $this->createForm(ResumeType::class, $resume)->handleRequest($request);
        // $resume->setFile(
        //     new File($this->getParameter('resume_directory').''.$resume->getFile())
        // );

        if($resumeForm->isSubmitted() && $resumeForm->isValid()) {
            $file = $resumeForm->get('file')->getData();
            if($file) {
                $fileName = $fileUploader->upload($file);
                $resume->setName($fileName);
            }

            $entityManager->persist($resume);
            $entityManager->flush();

            return $this->render('resume/form.html.twig', compact('resumeForm'));
        }
        
        // $this->addFlash(
        //     'success', 
        //     "A new resume \"{$resume->getName()}\" was added with success"
        // );

        return new RedirectResponse('/resume');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/resume/delete/{id}',
        name: 'app_delete_resume',
        methods: ['DELETE'],
        requirements: ['id' => '[0-9]+']
    )]
    public function deleteResume(int $id): Response
    {
        $this->resumeRepository->removeById($id);
        $this->addFlash('success', 'The resume was removed');

        return new RedirectResponse('/resume');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/resume/edit/{resume}', name: 'app_edit_resume_form', methods: ['GET'])]
    public function editWorksForm(Resume $resume)
    {
        $resumeForm = $this->createForm(WorksType::class, $resume, ['is_edit' => true]);
        return $this->render('work/form.html.twig', compact('resumeForm', 'resume'));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/resume/edit/{resume}', name: 'app_store_resume_changes', methods: ['PATCH'])]
    public function storeResumeChanges(Resume $resume, Request $request): Response
    {
        $resumeForm = $this->createForm(ResumeType::class, $resume, ['is_edit' => true]);
        $resumeForm->handleRequest($request);

        if(!$resumeForm->isValid()) {
            return $this->render('resume/form.html.twig', compact('resumeForm', 'resume'));
        }

        $this->addFlash('success', "Resume \"{$resume->getName()}\" edited with success.");
        $this->entityManager->flush();

        return new RedirectResponse('/resume');
    }

    #[Route('/download-resume/{id}', name: 'app_download_resume')]
    public function downloadFile($id)
    {
        $file = $this->resumeRepository->find($id);

        return $this->file($_SERVER['DOCUMENT_ROOT']."/Uploads/".$file->getName());
    }
}
