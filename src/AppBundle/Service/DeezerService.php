<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
* Manages Deezer URLs
*/
class DeezerService {

    protected $deezerAppId;
    protected $deezerAppSecret;
    protected $router;
    protected $session;

    public function __construct($router, $session, $logger, $deezerAppId, $deezerAppSecret) {
        $this->router = $router;
        $this->session = $session;
        $this->logger = $logger;
        $this->deezerAppId = $deezerAppId;
        $this->deezerAppSecret = $deezerAppSecret;
    }

    public function getConnectUrl() {
        $parameters = [
            'app_id' => $this->deezerAppId,
            'redirect_uri' => $this->router->generate('deezer_authorize', array(), UrlGeneratorInterface::ABSOLUTE_URL),
            'perms' => 'basic, emails'
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
        $playlist = [];
        foreach($albumList as $album) {
            $playlist[] = $this->getRandomTrackFromAlbum($album);
        }
        return $playlist;
    }

    protected function getRandomAlbums($albumList) 
    {
        $max = sizeof($albumList) - 1;
        $nbRequested = 5;
        $restrictedAlbumsList = [];
        for($i=0; $i < $nbRequested; $i++) {
            $index = rand(0, $max);
            $restrictedAlbumsList[] = $albumList[$index];
        }
        return $restrictedAlbumsList;
    }


    protected function getRandomTrackFromAlbum($album)
    {
        $this->logger->info('getRandomTrackFromAlbum');
        $url = $album["tracklist"]."?access_token=".$this->getAccessToken();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tracks = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $max = sizeof($tracks['data']) - 1;
        return $tracks['data'][rand(0,$max)];
    }
}

