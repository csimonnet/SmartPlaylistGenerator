<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;

class UserController extends Controller
{
    /**
     * @Route("/", name="deezer_register")
     */
    public function deezerRegisterAction(Request $request, DeezerService $deezerService)
    {
    }
}
