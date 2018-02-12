<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Playlist;
use AppBundle\Entity\Track;
use AppBundle\Form\PlaylistParametersType;
use AppBundle\Form\TrackType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DeezerService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Session\Session;


class PlaylistController extends Controller
{

     /**
      * @Route("/playlist/generate/deezer", name="deezer_playlist_generate")
      */
     public function generatePlaylistDeezerAction(Request $request, DeezerService $deezerService, Session $session)
     {
         if (!$deezerService->hasAccessToken()) {
             return $this->redirect($deezerService->getConnectUrl());
         }

         $playlistParameters = $request->query->get('playlist_parameters');

         $generatedPlaylist = new Playlist();
         $generatedPlaylist->setTracks(array());

         if($request->getMethod() !== "POST") {
             $generatedPlaylist = $deezerService->generateRandomPlaylist($playlistParameters);
         }

         $form = $this->createFormBuilder($generatedPlaylist)
             ->setAction($this->generateUrl('deezer_playlist_generate'))
             ->add('name', null, array(
                          'required' => true,
                          'empty_data' => date('d/m/Y H:i:s')
                      ))
                     ->add('tracks', CollectionType::class, array(
                            'entry_type' => TrackType::class,
                            'entry_options' => array('label' => false)
                      ))
                      ->add('save', SubmitType::class, array('label' => 'Envoyer playlist'))
                      ->getForm();

         if($request->getMethod() == 'POST') {
             $form->handleRequest($request);
             $playlist = $form->getData();
             $data = $request->request->get('form');

             foreach($data['tracks'] as $trackId) {
                 $track = new Track();
                 $track->setDeezerId($trackId['deezerId']);
                 $playlist->addTrack($track);
             }

             $deezerService->sendPlaylistToDeezer($playlist);
             $deezerService->logout();
             $session->getFlashBag()->add('notice', 'Playlist <a href="http://www.deezer.com/fr/playlist/'.$playlist->getDeezerId().'">"'.$playlist->getName().'"</a> envoyée sur Deezer' );
             return $this->redirect($this->generateUrl('playlist_prepare'));
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