<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    public function __construct(private readonly ContentRepository $contentRepository)
    {
    }

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        $page = $this->contentRepository->findHomepage();

        if (!$page) {
            throw new NotFoundHttpException('Homepage content not found.');
        }

        return $this->render('content/show.html.twig', [
            'page' => $page,
            'contact_form' => $this->createForm(ContactType::class)->createView(),
        ]);
    }
}