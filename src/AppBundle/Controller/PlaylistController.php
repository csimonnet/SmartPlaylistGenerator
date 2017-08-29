<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\DeezerService;

class PlaylistController extends Controller
{

     /**
      * @Route("/playlist/generate/deezer", name="deezer_playlist_generate")
      */
     public function generatePlaylistDeezerAction(Request $request, DeezerService $deezerService)
     {
         $generatedPlaylist = $deezerService->generateRandomPlaylist();
         return $this->render('playlist/generate_playlist.html.twig', [
            'playlist' => $generatedPlaylist
        ]);
     }
}