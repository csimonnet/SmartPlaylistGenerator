<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class PlaylistController extends Controller
{

     /**
      * @Route("/playlist/generate/deezer", name="deezer_playlist_generate")
      */
     public function generatePlaylistDeezerAction(Request $request, DeezerService $deezerService)
     {
         if(!$deezerService->hasAccessToken()) {
             return $this->redirect($deezerService->getConnectUrl());
         }
         $generatedPlaylist = $deezerService->generateRandomPlaylist();

         $form = $this->createFormBuilder($generatedPlaylist)
                      ->add('name')
                      ->add('tracks', CollectionType::class)
                      ->add('save', SubmitType::class, array('label' => 'Envoyer playlist'))
                      ->getForm();

         return $this->render('playlist/generate_playlist.html.twig', [
            'form' => $form->createView()
         ]);
     }
}