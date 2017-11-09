<?php

namespace AppBundle\Controller;

use AppBundle\Form\TrackType;
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
                     ->setAction($this->generateUrl('deezer_playlist_generate'))
                     ->add('tracks', CollectionType::class, array(
                            'entry_type' => TrackType::class,
                            'entry_options' => array('label' => false)
                      ))
                      ->add('save', SubmitType::class, array('label' => 'Envoyer playlist'))
                      ->getForm();

         if($request->getMethod() == 'POST') {
             $form->handleRequest($request);
             $playlist = $form->getData();
             $deezerService->sendPlaylistToDeezer($playlist);
         }

         return $this->render('playlist/generate_playlist.html.twig', [
            'form' => $form->createView()
         ]);
     }
}