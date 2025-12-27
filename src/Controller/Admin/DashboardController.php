<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DashboardService;

#[Route('/api/admin/dashboard', name: 'admin_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(private DashboardService $dashboardService) {}

    #[Route('', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        return $this->json([
            'kpis' => $this->dashboardService->getKPIs(),
            'charts' => [
                'artworksByMonth' => $this->dashboardService->getArtworksByMonth(),
                'connectionsByDay' => $this->dashboardService->getConnectionsByDay(),
                'artworksDisplayed' => $this->dashboardService->getArtworksDisplayedStats(),
                'styles' => $this->dashboardService->getStylesStats(),
                'nationalities' => $this->dashboardService->getNationalitiesStats(),
                'ratingsOverTime' => $this->dashboardService->getRatingsOverTime(),
                'galleriesPublicPrivate' => $this->dashboardService->getGalleriesPublicPrivate(),
            ],
            'tables' => [
                'latestConnections' => $this->dashboardService->getLatestConnections(10),
                'latestArtworks' => $this->dashboardService->getLatestArtworks(10),
                //'latestAdminActions' => $this->dashboardService->getLatestAdminActions(10),
            ]
        ]);
    }
}