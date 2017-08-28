<?php

namespace AppBundle\Entity;

class Track
{
    protected $name;

    protected $deezerId;

    protected $album;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDeezerId($deezerId)
    {
        $this->deezerId = $deezerId;
        return $this;
    }

    public function getDeezerId()
    {
        return $this->deezerId;
    }

    public function setAlbum(Album $album)
    {
        $this->album = $album;
        return $this;
    }

    public function getAlbum()
    {
        return $this->album;
    }

}