<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'api_login_check', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return $this->json(['message' => 'Missing credentials'], 401);
    }
    
    #[Route('/user/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }
        $adherent = $user->getAdherent(); // récupère le profil Adherent
        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'nom'       => $user->getNom(),
            'prenom'    => $user->getPrenom(),
            'roles'     => $user->getRoles(),
            'adherent'  => $adherent ? [
                'id'            => $adherent->getIdAdh(),
                'dateAdhesion'  => $adherent->getDateAdhesion()?->format('Y-m-d'),
                'numTel'        => $adherent->getNumTel(),
                'estActif'      => $adherent->isEstActif(),
            ] : null,
        ]);
    }
}