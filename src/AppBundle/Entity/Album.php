<?php

namespace AppBundle\Entity;

class Album
{
    protected $name;

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