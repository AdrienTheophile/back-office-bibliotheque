<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'api_categories', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): JsonResponse
    {
        return $this->json($categorieRepository->findAll(), 200, [], ['groups' => 'categorie:read']);
    }

    #[Route('/categories/{idCat}', name: 'api_categories_show', methods: ['GET'])]
    public function show(int $idCat, CategorieRepository $categorieRepository): JsonResponse
    {
        $categorie = $categorieRepository->find($idCat);

        if (!$categorie) {
            return $this->json(['error' => 'Catégorie non trouvée'], 404);
        }

        return $this->json($categorie, 200, [], ['groups' => 'categorie:read']);
    }
}
