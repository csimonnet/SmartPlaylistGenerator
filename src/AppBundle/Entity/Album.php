<?php

namespace AppBundle\Entity;

class Album
{
    protected $name;

    protected $deezerId;

    protected $link;

    protected $cover;

    protected $coverSmall;

    protected $tracklist;

    protected $artistId;

    protected $artistName;

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param mixed $cover
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    /**
     * @return mixed
     */
    public function getCoverSmall()
    {
        return $this->coverSmall;
    }

    /**
     * @param mixed $coverSmall
     */
    public function setCoverSmall($coverSmall)
    {
        $this->coverSmall = $coverSmall;
    }

    /**
     * @return mixed
     */
    public function getTracklist()
    {
        return $this->tracklist;
    }

    /**
     * @param mixed $tracklist
     */
    public function setTracklist($tracklist)
    {
        $this->tracklist = $tracklist;
    }

    /**
     * @return mixed
     */
    public function getArtistId()
    {
        return $this->artistId;
    }

    /**
     * @param mixed $artistId
     */
    public function setArtistId($artistId)
    {
        $this->artistId = $artistId;
    }

    /**
     * @return mixed
     */
    public function getArtistName()
    {
        return $this->artistName;
    }

    /**
     * @param mixed $artistName
     */
    public function setArtistName($artistName)
    {
        $this->artistName = $artistName;
    }

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



}