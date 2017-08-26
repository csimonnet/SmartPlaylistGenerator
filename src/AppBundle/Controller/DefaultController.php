<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, DeezerService $deezerService)
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'deezer_url' => $deezerService->getConnectUrl(),
            'access_token' => $this->get('session')->get('deezer_access_token'),
            'user_id' => ($this->get('session')->get('deezer_access_token') !== null) ?  : ''
        ]);
    }
}
