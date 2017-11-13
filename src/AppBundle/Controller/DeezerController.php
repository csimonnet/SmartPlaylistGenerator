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
        
        return $this->redirect($this->get('router')->generate('playlist_prepare', array(), true));
     }
}