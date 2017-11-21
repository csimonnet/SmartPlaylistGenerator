<?php

namespace AppBundle\Controller;

use AppBundle\Form\PlaylistParametersType;
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

         $playlistParameters = $request->query->get('playlist_parameters');

         $generatedPlaylist = $deezerService->generateRandomPlaylist($playlistParameters);

         $form = $this->createFormBuilder($generatedPlaylist)
                      ->add('name', null, array(
                          'required' => true,
                          'empty_data' => date('d/m/Y H:i:s')
                      ))
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
             $deezerService->logout();
         }

         return $this->render('playlist/generate_playlist.html.twig', [
            'form' => $form->createView()
         ]);
     }

    /**
     * @Route("/playlist/prepare", name="playlist_prepare")
     */
     public function preparePlaylistAction(Request $request, DeezerService $deezerService)
     {
         if(!$deezerService->hasAccessToken()) {
             return $this->redirect($deezerService->getConnectUrl());
         }

         $form = $this->createForm(PlaylistParametersType::class, null, array(
             'action' => $this->generateUrl('deezer_playlist_generate'),
             'method' => 'GET'
         ));

         return $this->render('playlist/prepare_playlist.html.twig', [
             'form' => $form->createView()
         ]);

     }
}