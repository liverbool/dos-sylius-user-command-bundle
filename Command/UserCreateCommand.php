<?php

namespace Dos\SyliusUserCommandBundle\Command;

use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class UserCreateCommand extends AbstractUserCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dos:user:create')
            ->setDescription('Creates a new user account.')
            ->setDefinition([
                new InputArgument('section', InputArgument::REQUIRED, 'User section eg. admin, web, ..'),
                new InputArgument('email', InputArgument::REQUIRED, 'Email'),
                new InputArgument('password', InputArgument::REQUIRED, 'Password'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $this->section = $input->getArgument('section');

        $user = $this->createUser($email, $password);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $output->writeln(sprintf('Created user <comment>%s</comment>', $email));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $this->section = $input->getArgument('section');

        if (!$input->getArgument('email')) {
            $question = new Question('Please enter an email:', false);
            $question->setNormalizer(function ($value) {
                if (empty($value)) {
                    throw new \Exception('Email can not be empty');
                }

                return $value;
            });

            $email = $helper->ask($input, $output, $question);
            $input->setArgument('email', $email);
        }

        if ($this->getUserRepository()->findOneBy(['email' => $input->getArgument('email')])) {
            throw new \Exception('This email already exist.');
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please enter password:', false);
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setNormalizer(function ($value) {
                if (empty($value)) {
                    throw new \Exception('password can not be empty');
                }

                return $value;
            });

            $password = $helper->ask($input, $output, $question);
            $input->setArgument('password', $password);
        }
    }

    protected function createUser($email, $password, array $securityRoles = [])
    {
        /** @var UserInterface $user */
        $user = $this->getUserFactory()->createNew();
        $user->setUsername($email);
        $user->setUsernameCanonical($email);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user->setEmail($email);
            $user->setEmailCanonical($email);
        }

        $user->setPlainPassword($password);
        $user->enable();

        foreach ($securityRoles as $role) {
            $user->addRole($role);
        }

        return $user;
    }
}
