<?php

namespace App\Controller;

use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard_index')]

    public function index(StatsService $statsService): Response
    {
        $stats = $statsService->getStats();

        $bestAds = $statsService->getAdsStats("DESC");

        $worstAds = $statsService->getAdsStats("ASC");
                
        return $this->render('admin/dashboard/index.html.twig', [
                
            'stats' => $stats,
            'bestAds' => $bestAds,
            'worstAds' =>$worstAds
        ]);
    }
}
