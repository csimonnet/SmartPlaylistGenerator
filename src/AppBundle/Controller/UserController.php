<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class UserController extends Controller
{
    public function userPanelAction(Request $request, DeezerService $deezerService)
    {
        return $this->render('user/connection_panel.html.twig', [
            'user' => $deezerService->getCurrentUserInformations(),
            'connection_url' => $deezerService->getConnectUrl()
        ]);
    }

    /**
     * @Route("/logout", name="user_logout")
     */
    public function logoutAction(Request $request, DeezerService $deezerService)
    {
        $deezerService->logout();
        return $this->redirectToRoute('homepage');
    }

    public function loginAction(Request $request)
    {

    }

}
