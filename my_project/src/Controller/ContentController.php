<?php

namespace App\Controller;

use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ContentController extends AbstractController
{
    public function __construct(private ContentRepository $contentRepository)
    {
    }

    #[Route('/pages', name: 'content_index')]
    public function index(): Response
    {
        $pages = $this->contentRepository->findRootContents();

        return $this->render('content/index.html.twig', [
            'pages' => $pages,
        ]);
    }

    #[Route('/page/{slug}', name: 'content_show')]
    public function show(string $slug): Response
    {
        $page = $this->contentRepository->findOneBy([
            'slug' => $slug,
            'parent' => null,
            'isActive' => true,
        ]);

        if (!$page) {
            throw new NotFoundHttpException('Page not found.');
        }

        return $this->render('content/show.html.twig', [
            'page' => $page,
            'contact_form' => $this->createForm(ContactType::class)->createView(),
        ]);
    }
}