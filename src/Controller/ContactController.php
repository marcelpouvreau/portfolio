<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact-me', name: 'app_contact')]
    public function sendEmail(Request $request, MailerInterface $mailer): Response
    {
        $contactForm = $this->createForm(ContactType::class);

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {

            $data = $contactForm->getData();
            
            $address = $data['email'];
            $content = $data['content'];

            $email = (new Email())
                ->from($address)
                ->to('marcel.pouvreau@gmail.com')
                ->subject('Demande de contact')
                ->text($content);

            $mailer->send($email);
        }

        return $this->render('contact/contactme.html.twig', [
            'contactForm' => $contactForm,
        ]);
    }
}
