<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class UserController extends Controller
{
    public function userPanelAction(Request $request, DeezerService $deezerService)
    {
        $user = null;
        try {
            $user = $deezerService->getCurrentUserInformations();
        } catch (AccessDeniedException $e) {

        }
        return $this->render('user/connection_panel.html.twig', [
            'user' => $user,
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
