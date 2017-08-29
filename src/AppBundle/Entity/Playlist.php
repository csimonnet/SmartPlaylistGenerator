<?php

namespace AppBundle\Entity;

class Playlist
{
    protected $name;

    protected $tracks;

    protected $deezerId;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTracks()
    {
        return $this->tracks;
    }

    public function setTracks($tracks)
    {
        $this->tracks = $tracks;
        return $this;
    }

    public function addTrack(Track $track)
    {
        $this->tracks[] = $track;
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

}