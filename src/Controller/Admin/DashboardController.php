<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Reservations;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function index(): Response
    {
        $nbAdherents = $this->em->getRepository(Adherent::class)->count([]);
        $nbAuteurs = $this->em->getRepository(Auteur::class)->count([]);
        $nbCategories = $this->em->getRepository(Categorie::class)->count([]);
        $nbEmprunts = $this->em->getRepository(Emprunt::class)->count([]);
        $nbLivres = $this->em->getRepository(Livre::class)->count([]);
        $nbReservations = $this->em->getRepository(Reservations::class)->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'nbAdherents' => $nbAdherents,
            'nbAuteurs' => $nbAuteurs,
            'nbCategories' => $nbCategories,
            'nbEmprunts' => $nbEmprunts,
            'nbLivres' => $nbLivres,
            'nbReservations' => $nbReservations,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Back Office Bibliotheque');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::linkToCrud('Adhérents', 'fa fa-users', Adherent::class);

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToCrud('Livres', 'fa fa-book', Livre::class);
        yield MenuItem::linkToCrud('Auteurs', 'fa fa-pen-fancy', Auteur::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Categorie::class);

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Emprunts', 'fa fa-handshake', Emprunt::class);
        yield MenuItem::linkToCrud('Réservations', 'fa fa-calendar-check', Reservations::class);
    }
}
