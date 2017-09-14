<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class UserController extends Controller
{
    /**
     * @Route("/user/register/deezer", name="deezer_register")
     * The route used to create a user via his / her Deezer account.
     */
    public function deezerRegisterAction(Request $request, DeezerService $deezerService)
    {
    }

    /**
     * @Route("/user/login/deezer", name="deezer_login")
     * Route that will try to authenticate the user from Deezer. If the user does not exist,
     * should redirect user to register and authenticate new user.
     */
    public function deezerLoginAction(Request $request, DeezerService $deezerService)
    {

    }
}
