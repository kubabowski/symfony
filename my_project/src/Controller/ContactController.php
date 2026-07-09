<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(CONTACT_ADMIN_EMAIL)%')] private readonly string $adminEmail,
        #[Autowire('%env(MAILER_FROM)%')] private readonly string $fromEmail,
    ) {
    }

    #[Route('/contact', name: 'contact_submit', methods: ['POST'])]
    public function submit(Request $request): RedirectResponse
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        $returnSlug = $form->get('returnSlug')->getData();
        $redirectUrl = $returnSlug
            ? $this->generateUrl('content_show', ['slug' => $returnSlug]) . '#contact'
            : $this->generateUrl('homepage');

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('contact_error', 'Please fill in all fields with a valid email address.');
            return $this->redirect($redirectUrl);
        }

        $data = $form->getData();

        $toAdmin = (new Email())
            ->from(new Address($this->fromEmail, 'Website contact form'))
            ->replyTo($data['email'])
            ->to($this->adminEmail)
            ->subject(sprintf('New contact message from %s', $data['name']))
            ->text(sprintf("Name: %s\nEmail: %s\n\nMessage:\n%s", $data['name'], $data['email'], $data['message']));

        $toUser = (new Email())
            ->from(new Address($this->fromEmail, 'GCHemp'))
            ->to($data['email'])
            ->subject('We received your message')
            ->text(sprintf(
                "Hi %s,\n\nThanks for reaching out — we received your message and will get back to you soon.\n\nYour message:\n%s\n\n— GCHemp",
                $data['name'],
                $data['message']
            ));

        $this->mailer->send($toAdmin);
        $this->mailer->send($toUser);

        $this->addFlash('contact_success', 'Thanks! Your message has been sent — check your inbox for a confirmation.');

        return $this->redirect($redirectUrl);
    }
}