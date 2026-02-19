<?php

namespace App\Controller\Api;

use App\Entity\Livre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\LivreRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class LivreController extends AbstractController
{
    #[Route('/livres', name: 'api_livres', methods: ['GET'])]
    public function index(LivreRepository $livreRepository, Request $request): JsonResponse
    {

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $livresPagines = $livreRepository->findPaginated($page, $limit);

        return $this->json($livresPagines, 200, [], ['groups' => 'livre:read']);
    }

    #[Route('/livres/{id}', name: 'api_livres_show', methods: ['GET'])]
    public function show(Livre $livre): JsonResponse
    {
        return $this->json($livre, 200, [], ['groups' => 'livre:read']);
    }

}
