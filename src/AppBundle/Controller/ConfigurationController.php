<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\DeezerService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConfigurationController extends Controller
{

    /**
     * @Route("/configure", name="configure")
     */
     public function configureAction(Request $request)
     {
        $response = new StreamedResponse();
        $response->setCallback(function(){
            var_dump('OMG this is a protected action and I need to be logged to see this amazing message');
        }); 
        $response->send();
        return $response;
     }
}