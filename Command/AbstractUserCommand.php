<?php

namespace Dos\SyliusUserCommandBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\UserBundle\Provider\UserProviderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractUserCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $section;

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get(sprintf('sylius.manager.%s_user', $this->section));
    }

    /**
     * @return FactoryInterface
     */
    protected function getUserFactory()
    {
        return $this->getContainer()->get(sprintf('sylius.factory.%s_user', $this->section));
    }

    /**
     * @return RepositoryInterface
     */
    protected function getUserRepository()
    {
        return $this->getContainer()->get(sprintf('sylius.repository.%s_user', $this->section));
    }

    /**
     * @return UserProviderInterface
     */
    protected function getUserProvider()
    {
        return $this->getContainer()->get(sprintf('sylius.%s_user_provider.email_or_name_based', $this->section));
    }
}
