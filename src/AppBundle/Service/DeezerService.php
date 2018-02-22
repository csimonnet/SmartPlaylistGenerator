<?php
namespace AppBundle\Service;

use AppBundle\Entity\Album;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\Playlist;
use AppBundle\Entity\Track;

/**
* Manages Deezer URLs
*/
class DeezerService {

    const ACCESS_TOKEN_URL = 'https://connect.deezer.com/oauth/access_token.php';
    const CONNECT_URL = 'https://connect.deezer.com/oauth/auth.php';
    const USER_ALBUMS_URL = 'http://api.deezer.com/user/me/albums';
    const USER_ALBUMS_CHART_URL = 'http://api.deezer.com/user/me/charts/albums';
    const USER_PLAYLISTS_URL = 'http://api.deezer.com/user/me/playlists';
    const ALBUM_URL = 'http://api.deezer.com/album';
    const PLAYLIST_URL = 'http://api.deezer.com/playlist';
    const ALBUMS_MAX = '300';
    const PLAYLIST_MAX_TRACK = 30;
    const TRACKS_MAX_TRIES = 5;

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
        return self::CONNECT_URL.'?'.http_build_query($parameters);
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
        $url = self::ACCESS_TOKEN_URL.'?'.http_build_query($parameters);
        $result = $this->request($url);
        if(empty($result['access_token'])) {
            throw new AccessDeniedHttpException('Access token could not be retrieved');

        }
        $this->session->set('deezer_access_token', $result['access_token']);
    }

    public function getAccessToken()
    {
        return $this->session->get('deezer_access_token');
    }

    /**
     * Generate a playlist from favorite albums, randomly.
     * @return Playlist
     */
    public function generateRandomPlaylist($parameters)
    {
        if($parameters['tracks_number'] > self::PLAYLIST_MAX_TRACK) {
            throw new \Exception('Ouh là, vous voulez beaucoup trop de tracks dans votre playlist ! (et vous avez un peu triché)');
        }

        $albums = $this->getUserAlbums();

        $albumList = $this->getRandomAlbums($albums, $parameters['tracks_number']);

        $playlist = new Playlist();
        $title = date('d/m/Y H:i:s');
        $playlist->setName($title);

        foreach($albumList as $album) {
            $tried = 0;
            $track = null;
            while(empty($track) && $tried <= self::TRACKS_MAX_TRIES){
                $tried++;
                $track = $this->getRandomTrackFromAlbum($album);
                if($track !== null) {
                    $playlist->addTrack($track);
                } else {
                    $albumTryAgain = $this->getRandomAlbums($result['data'], 1);
                    $track = $this->getRandomTrackFromAlbum($albumTryAgain[0]);
                    if($track !== null) {
                        $playlist->addTrack($track);
                    }
                }
            }
        }
        return $playlist;
    }

    public function getUserAlbums()
    {
        $url = self::USER_ALBUMS_URL."?access_token=".$this->getAccessToken()."&limit=".self::ALBUMS_MAX;
        $result = $this->request($url);
        if(sizeof($result['data']) === 0) {
            throw new \Exception('Pas assez d\'albums sont dans votre bibliothèque, le générateur n\'arrive pas à travailler... ');
        }
        return $result['data'];
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
        $playlist->setDeezerId($result['id']);
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

    public function getCurrentUserInformations()
    {
        if($this->hasAccessToken()) {
            return $this->request('https://api.deezer.com/user/me?access_token='.$this->getAccessToken());
        }
        return null;
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
            $deezerAlbum = $albumList[$index];
            $album = new Album();
            $album->setDeezerId($deezerAlbum['id']);
            $album->setArtistId($deezerAlbum['artist']['id']);
            $album->setArtistName($deezerAlbum['artist']['name']);
            $album->setName($deezerAlbum['title']);
            $album->setTracklist($deezerAlbum['tracklist']);
            $album->setCoverSmall($deezerAlbum['cover_small']);
            $album->setCover($deezerAlbum['cover_big']);
            $album->setDeezerLink($deezerAlbum['link']);
            unset($albumList[$index]);
            $albumList = array_values($albumList);
            $restrictedAlbumsList[] = $album;
        }
        return $restrictedAlbumsList;
    }

    /**
     * get a random track from album
     * @param $album
     * @return Track|null
     */
    protected function getRandomTrackFromAlbum(Album $album)
    {
        $url = $album->getTracklist()."?access_token=".$this->getAccessToken();
        $tracks = $this->request($url);
        if(array_key_exists('data', $tracks)) {
            $track = new Track();
            $max = sizeof($tracks['data']) - 1;
            $dataTrack =  $tracks['data'][rand(0,$max)];
            $track->setDeezerId($dataTrack['id']);
            $track->setName($dataTrack['title']);
            $track->setAlbum($album);
            return $track;
        }
        return null;

    }

    public function getAlbumByDeezerId($albumDeezerId)
    {
        $url = self::ALBUM_URL.'/'.$albumDeezerId;
        $albumData = $this->request($url);
        $album = new Album();
        $album->setArtistId($albumData['artist']['id']);
        $album->setArtistName($albumData['artist']['name']);
        $album->setName($albumData['title']);
        $album->setTracklist($albumData['tracklist']);
        $album->setCoverSmall($albumData['cover_small']);
        $album->setCover($albumData['cover_big']);
        return $album;
    }

    /**
     * Retrieves all albums of the users and most listened albums of the users.
     * Return one of the less listened album.
     * Tries to retrieve an album which is not in the top 5 most listened albums of the users.
     * It's kind of a fake "less listened", sorry, this seems to be the only way to do this kind of thing with Deezer :)
     * @return mixed|null
     */
    public function getLessListenedAlbum()
    {
        $allAlbums = $this->getUserAlbums();
        $album = $this->getRandomAlbums($allAlbums, 1);
        $chart = $this->request(self::USER_ALBUMS_CHART_URL."?access_token=".$this->getAccessToken());
        $i = 0;
        $albumToSuggest = null;
        $topFiveAlbumsId = array();
        for($j = 0; $j < 5; $j++) {
            $topFiveAlbumsId[] = $chart['data'][$j]['id'];
        }
        while($albumToSuggest === null && $i < self::TRACKS_MAX_TRIES) {
            $i++;
            if(!in_array($album[0]->getDeezerId(), $topFiveAlbumsId)) {
                $albumToSuggest = $album[0];
            } else {
                $album = $this->getRandomAlbums($allAlbums['data'], 1);
            }
        }
        if(!isset($albumToSuggest)) {
            $albumToSuggest = $album[0];
        }

        return $albumToSuggest;
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
        if(!$response) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new AccessDeniedException($message);
        }
        curl_close($ch);
        if(is_array($response) && array_key_exists('error', $response) && $response['error']['type'] == 'OAuthException' && $response['error']['code'] == 300 ) {
            throw new AccessDeniedException('Token Expired');
        }
        return $response;
    }

}


