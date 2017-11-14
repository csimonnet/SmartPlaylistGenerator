<?php
namespace AppBundle\Service;

use AppBundle\Entity\Album;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\Playlist;
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
    protected $timeout;

    public function __construct($router, $session, $logger, $deezerAppId, $deezerAppSecret, $tracksNumber, $timeout) {
        $this->router = $router;
        $this->session = $session;
        $this->logger = $logger;
        $this->deezerAppId = $deezerAppId;
        $this->deezerAppSecret = $deezerAppSecret;
        $this->tracksNumber = $tracksNumber;
        $this->timeout = $timeout;
    }

    /**
     * generate the URL to display to connect with Deezer
     * @return string : URL to connect with Deezer
     */
    public function getConnectUrl() {
        $parameters = [
            'app_id' => $this->deezerAppId,
            'redirect_uri' => $this->router->generate('deezer_authorize', array(), UrlGeneratorInterface::ABSOLUTE_URL),
            'perms' => 'basic, emails, manage_library'
        ];
        return 'https://connect.deezer.com/oauth/auth.php?'.http_build_query($parameters);
    }

    /**
     * Check if the user has valid access token from Deezer.
     * @return bool
     */
    public function hasAccessToken() {
        return $this->session->get('deezer_access_token') !== null;
    }

    /**
     * Request access token from code sent by Deezer.
     * Set the session with the retrieved access token.
     * @param $code
     */
    public function requestAccessToken($code) {
        $parameters = [
            'app_id' => $this->deezerAppId,
            'secret' => $this->deezerAppSecret,
            'code'   => $code,
            'output' => 'json'
        ];
        $url = 'https://connect.deezer.com/oauth/access_token.php?'.http_build_query($parameters);
        $result = $this->request($url);
        $this->session->set('deezer_access_token', $result['access_token']);
    }

    public function getAccessToken()
    {
        return $this->session->get('deezer_access_token');
    }

    public function getUserDeezerId()
    {
        $url = "https://api.deezer.com/user/me?access_token=".$this->getAccessToken();
        $result = $this->request($url);
        return $result["id"];
    }

    /**
     * Generate a playlist from favorite albums, randomly.
     * @return Playlist
     */
    public function generateRandomPlaylist($parameters)
    {
        $url = "http://api.deezer.com/user/me/albums?access_token=".$this->getAccessToken()."&limit=300";
        $result = $this->request($url);
        $albumList = $this->getRandomAlbums($result['data'], $parameters['tracks_number']);

        $playlist = new Playlist();
        $title = date('d/m/Y H:i:s');
        $playlist->setName($title);

        foreach($albumList as $album) {
            $track = $this->getRandomTrackFromAlbum($album);
            if($track !== null) {
                $playlist->addTrack($this->getRandomTrackFromAlbum($album));
            }
        }
        return $playlist;
    }

    /**
     * Send a request to send the object playlist to Deezer.
     * @param $playlist
     */
    public function sendPlaylistToDeezer($playlist)
    {
        $this->logger->info('sendPlaylistToDeezer');

        $url = "http://api.deezer.com/user/me/playlists?access_token=".$this->getAccessToken();
        $parameters = array(
            "title" => $playlist->getName()
        );
        $result = $this->request($url, "POST", $parameters);

        $tracksId = array_map(function($element) {
            return $element->getDeezerId();
        }, $playlist->getTracks());

        $url = "http://api.deezer.com/playlist/".$result['id']."/tracks?access_token=".$this->getAccessToken();
        $parameters = array(
            "songs" => implode(",", $tracksId)
        );
        $this->request($url, "POST", $parameters);
    }

    public function logout()
    {
        $this->session->set('deezer_access_token', null);
    }

    /**
     * get some albums from user favorites albums
     * @param $albumList
     * @param $tracksNumber
     * @return array
     */
    protected function getRandomAlbums($albumList, $tracksNumber = null)
    {
        $tracksNumber = $tracksNumber ?: $this->tracksNumber;
        $restrictedAlbumsList = [];
        for($i=0; $i < $tracksNumber; $i++) {
            $max = sizeof($albumList) - 1;
            $index = rand(0, $max);
            $restrictedAlbumsList[] = $albumList[$index];
            unset($albumList[$index]);
            $albumList = array_values($albumList);
        }
        return $restrictedAlbumsList;
    }

    /**
     * get a random track from album
     * @param $album
     * @return Track|null
     */
    protected function getRandomTrackFromAlbum($album)
    {
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

    protected function request($url, $method = "GET", $parameters = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout + 5 );
        if($method == "POST") {
            curl_setopt($ch,CURLOPT_POST, count($parameters));
            curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $response;
    }
}


