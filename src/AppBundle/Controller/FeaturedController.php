<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class FeaturedController extends Controller
{
    /**
     * @Route("/featured-panel", name="featured_panel")
     */
    public function featuredPanelAction(Request $request, DeezerService $deezerService)
    {
        $deezerAlbumId = $this->getParameter('featured_album');
        $album = $deezerService->getAlbumByDeezerId($deezerAlbumId);
        $description = $this->getParameter('featured_album_description');
        $quotes = $this->getParameter('featured_album_quotes');
        $review = $this->getParameter('featured_album_review');
        $video = $this->getParameter('featured_album_video');

        return $this->render('featured/full_panel.html.twig', [
            'album' => $album,
            'description' => $description,
            'quotes' => $quotes,
            'review' => $review,
            'video'  => $video
        ]);
    }
}
