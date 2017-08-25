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

    public function __construct($router, $session, $deezerAppId, $deezerAppSecret) {
        $this->router = $router;
        $this->session = $session;
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

    }


}

