<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\DeezerService;

class DeezerController extends Controller
{

    /**
     * @Route("/deezer/authorize", name="deezer_authorize")
     */
     public function authorizeAction(Request $request, DeezerService $deezerService)
     {
        $code = $request->query->get('code');

        try {

            $deezerService->requestAccessToken($code);

        } catch(\Exception $e) {

        }
        
        return $this->redirect($this->get('router')->generate('homepage', array(), true));
     }

     /**
      * @Route("/deezer/playlist/generate", name="deezer_playlist_generate")
      */
     public function generatePlaylistAction(Request $request, DeezerService $deezerService)
     {
         $generatedPlaylist = $deezerService->generateRandomPlaylist();
         return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'deezer_url' => $deezerService->getConnectUrl(),
            'access_token' => $this->get('session')->get('deezer_access_token'),
            'user_id' => ($this->get('session')->get('deezer_access_token') !== null) ?  : ''
        ]);
     }
}