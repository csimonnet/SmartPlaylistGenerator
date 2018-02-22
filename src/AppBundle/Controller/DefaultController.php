<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
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
        $user = null;
        if ($deezerService->hasAccessToken()) {
            try {
                $user = $deezerService->getCurrentUserInformations();
                return $this->redirectToRoute('playlist_prepare');
            } catch (\Exception $e){

            }
        }

        if(!empty($user)) {
            return $this->forward('AppBundle\Controller\Playlist::preparePlaylist');
        }

        return $this->render('default/index.html.twig', [
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
