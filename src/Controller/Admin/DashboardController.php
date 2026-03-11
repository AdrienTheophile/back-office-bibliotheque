<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Reservations;
use App\Entity\Utilisateur;
use App\Repository\EmpruntRepository;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $em,
        private EmpruntRepository $empruntRepository,
        private ReservationsRepository $reservationsRepository,
    ) {
    }

    public function index(): Response
    {
        // Purge expired reservations (older than 7 days) before loading counts
        $this->reservationsRepository->deleteExpiredReservations();

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

    #[Route('/admin/statistiques', name: 'admin_statistiques')]
    #[IsGranted('ROLE_ADMIN')]
    public function statistiques(): Response
    {
        $empruntsEnCours = $this->empruntRepository->countEmpruntsEnCours();
        $empruntsTermines = $this->empruntRepository->countEmpruntsTermines();
        $empruntsEnRetard = $this->empruntRepository->countEmpruntsEnRetard();
        $topLivres = $this->empruntRepository->getTopLivresEmpruntes(5);
        $topAdherents = $this->empruntRepository->getTopAdherentsEmprunteurs(5);
        $dureeMoyenne = $this->empruntRepository->getDureeMoyenneEmprunt();

        return $this->render('admin/statistiques.html.twig', [
            'empruntsEnCours' => $empruntsEnCours,
            'empruntsTermines' => $empruntsTermines,
            'empruntsEnRetard' => $empruntsEnRetard,
            'topLivres' => $topLivres,
            'topAdherents' => $topAdherents,
            'dureeMoyenne' => $dureeMoyenne,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Back Office Bibliotheque');
    }

    public function configureAssets(): \EasyCorp\Bundle\EasyAdminBundle\Config\Assets
    {
        return \EasyCorp\Bundle\EasyAdminBundle\Config\Assets::new()
            ->addCssFile('css/admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', Utilisateur::class)->setPermission('ROLE_ADMIN');
        
        yield MenuItem::linkToCrud('Adhérents', 'fa fa-users', Adherent::class);

        yield MenuItem::section('Catalogue')->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Livres', 'fa fa-book', Livre::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Auteurs', 'fa fa-pen-fancy', Auteur::class)->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Categorie::class)->setPermission('ROLE_ADMIN');

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Emprunts', 'fa fa-handshake', Emprunt::class);
        yield MenuItem::linkToCrud('Réservations', 'fa fa-calendar-check', Reservations::class);

        yield MenuItem::section('Analyses')->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToUrl('Statistiques', 'fa fa-chart-bar', $this->container->get('router')->generate('admin_statistiques'))->setPermission('ROLE_ADMIN');

        yield MenuItem::section('Site Public');
        yield MenuItem::linkToUrl('Accéder au site', 'fa fa-external-link-alt', 'http://localhost:4200')
            ->setLinkTarget('_blank');
    }
}
