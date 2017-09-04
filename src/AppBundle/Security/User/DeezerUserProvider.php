<?php

namespace AppBundle\Security\User;

use AppBundle\Security\User\DeezerUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class DeezerUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {

    }

    public function refreshUser(UserInterface $user)
    {
    }

    public function supportsClass($class)
    {
        return DeezerUser::class === $class;
    }
}