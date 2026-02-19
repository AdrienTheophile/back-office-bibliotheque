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

    #[Route('/recherche', name: 'api_livres_search', methods: ['GET'])]
    public function search(Request $request, LivreRepository $livreRepository): JsonResponse
    {
        $params = [
            'titre' => $request->query->get('titre'),
            'auteur' => $request->query->get('auteur'),
            'categorie' => $request->query->get('categorie'),
            'langue' => $request->query->get('langue'),
            'dateMin' => $request->query->get('dateMin'),
            'dateMax' => $request->query->get('dateMax'),
        ];

        $livres = $livreRepository->findByAdvancedSearch($params);

        return $this->json($livres, 200, [], ['groups' => 'livre:read']);
    }
}
