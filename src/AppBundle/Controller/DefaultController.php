<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class DefaultController extends Controller
{
    /**
     * @Route("/home", name="homepage")
     */
    public function indexAction(Request $request, DeezerService $deezerService)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->forward('AppBundle:Default:dashboard');
        }
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'deezer_url' => $deezerService->getConnectUrl(),
            'access_token' => $this->get('session')->get('deezer_access_token'),
            'user_id' => ($this->get('session')->get('deezer_access_token') !== null) ?  : ''
        ]);

    }

    /**
    * @Route("/dashboard", name ="dashboard")
    */
    public function dashboardAction(Request $request, DeezerService $deezerService)
    {
        return $this->render('default/dashboard.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'deezer_url' => $deezerService->getConnectUrl(),
            'access_token' => $this->get('session')->get('deezer_access_token'),
            'user_id' => ($this->get('session')->get('deezer_access_token') !== null) ?  : ''
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render('default/about.html.twig', []);
    }
}
