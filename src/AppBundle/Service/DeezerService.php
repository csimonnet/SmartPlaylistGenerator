<?php
namespace AppBundle\Service;

/**
* Manages Deezer URLs
*/
class DeezerService {

    protected $deezerAppId;
    protected $deezerAppSecret;
    protected $router;

    public function __construct($router, $deezerAppId, $deezerAppSecret) {
        $this->router = $router;
        $this->deezerAppId = $deezerAppId;
        $this->deezerAppSecret = $deezerAppSecret;
    }

    public function getConnectUrl() {
        $parameters = [
            'app_id' => $this->deezerAppId,
            'redirect_uri' => urlencode($this->router->generate('deezer_authorize')),
            'perms' => 'basic, emails'
        ];
        return 'https://connect.deezer.com/oauth/auth.php?'.http_build_query($parameters);
    }

}

