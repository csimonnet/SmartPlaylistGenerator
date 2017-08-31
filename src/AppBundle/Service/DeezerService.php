<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\Playlist;
use AppBundle\Entity\Album;
use AppBundle\Entity\Track;

/**
* Manages Deezer URLs
*/
class DeezerService {

    protected $deezerAppId;
    protected $deezerAppSecret;
    protected $router;
    protected $session;
    protected $tracksNumber;

    public function __construct($router, $session, $logger, $deezerAppId, $deezerAppSecret, $tracksNumber) {
        $this->router = $router;
        $this->session = $session;
        $this->logger = $logger;
        $this->deezerAppId = $deezerAppId;
        $this->deezerAppSecret = $deezerAppSecret;
        $this->tracksNumber = $tracksNumber;
    }

    public function getConnectUrl() {
        $parameters = [
            'app_id' => $this->deezerAppId,
            'redirect_uri' => $this->router->generate('deezer_authorize', array(), UrlGeneratorInterface::ABSOLUTE_URL),
            'perms' => 'basic, emails, manage_library'
        ];
        return 'https://connect.deezer.com/oauth/auth.php?'.http_build_query($parameters);
    }

    public function hasAccessToken() {
        return $this->session->get('deezer_access_token') !== null;
    }

    public function requestAccessToken($code) {
        $parameters = [
            'app_id' => $this->deezerAppId,
            'secret' => $this->deezerAppSecret,
            'code'   => $code,
            'output' => 'json'
        ];
        $url = 'https://connect.deezer.com/oauth/access_token.php?'.http_build_query($parameters);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
        $this->session->set('deezer_access_token', $result->access_token);
        curl_close($ch);
    }

    public function getAccessToken()
    {
        return $this->session->get('deezer_access_token');
    }

    public function getUserDeezerId()
    {
        $url = "https://api.deezer.com/user/me?access_token=".$this->getAccessToken();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        return $result->id;
    }

    public function generateRandomPlaylist()
    {
        $url = "http://api.deezer.com/user/me/albums?access_token=".$this->getAccessToken()."&limit=300";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $albumList = $this->getRandomAlbums($result['data']);
        $playlist = new Playlist();
        foreach($albumList as $album) {
            $track = $this->getRandomTrackFromAlbum($album);
            if($track !== null) {
                $playlist->addTrack($this->getRandomTrackFromAlbum($album));
            }
        }

        $this->sendPlaylistToDeezer($playlist);

        return $playlist;
    }

    protected function sendPlaylistToDeezer($playlist)
    {
        $this->logger->info('sendPlaylistToDeezer');
        $title = date('d/m/Y H:i:s');
        $playlist->setName($title);
        $url = "http://api.deezer.com/user/me/playlists?access_token=".$this->getAccessToken();
        $parameters = array(
            "title" => $title
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POST, count($parameters));
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($parameters));
        $result = json_decode(curl_exec($ch), true);
        $this->logger->info(json_encode($result));
        curl_close($ch);

        $tracksId = array_map(function($element) {
            return $element->getDeezerId();
        }, $playlist->getTracks());

        $url = "http://api.deezer.com/playlist/".$result['id']."/tracks?access_token=".$this->getAccessToken();
        $parameters = array(
            "songs" => implode(",", $tracksId)
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POST, count($parameters));
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_exec($ch);
        curl_close($ch);
    }

    protected function getRandomAlbums($albumList) 
    {
        $max = sizeof($albumList) - 1;
        $restrictedAlbumsList = [];
        for($i=0; $i < $this->tracksNumber; $i++) {
            $index = rand(0, $max);
            $restrictedAlbumsList[] = $albumList[$index];
        }
        return $restrictedAlbumsList;
    }


    protected function getRandomTrackFromAlbum($album)
    {
        $this->logger->info('getRandomTrackFromAlbum');
        $url = $album["tracklist"]."?access_token=".$this->getAccessToken();
        $this->logger->info($url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tracks = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $this->logger->info(json_encode($tracks));
        if(array_key_exists('data', $tracks)) {
            $track = new Track();
            $max = sizeof($tracks['data']) - 1;
            $dataTrack =  $tracks['data'][rand(0,$max)];
            $track->setDeezerId($dataTrack['id']);
            $track->setName($dataTrack['title']);
            return $track;
        }
        return null;

    }
}

