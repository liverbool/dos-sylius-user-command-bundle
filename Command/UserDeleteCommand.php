<?php

namespace Dos\SyliusUserCommandBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class UserDeleteCommand extends AbstractUserCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dos:user:delete')
            ->setDescription('Deletes an user account.')
            ->setDefinition([
                new InputArgument('section', InputArgument::REQUIRED, 'User section eg. admin, web, ..'),
                new InputArgument('identifier', InputArgument::REQUIRED, 'Username or Email'),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->section = $input->getArgument('section');
        $identifier = $input->getArgument('identifier');
        $manager = $this->getEntityManager();

        $user = $this->getUserProvider()->loadUserByUsername($identifier);

        if (null === $user) {
            throw new \InvalidArgumentException(sprintf('Could not find user identified by username or email "%s"', $identifier));
        }

        $manager->remove($user);
        $manager->flush();

        $output->writeln(sprintf('Deleted user <comment>%s</comment>', $identifier));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->section = $input->getArgument('section');

        if (!$input->getArgument('identifier')) {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter an identifier:', false);
            $question->setNormalizer(function ($value) {
                if (empty($value)) {
                    throw new \Exception('Identifier can not be empty');
                }

                return $value;
            });

            $identifier = $helper->ask($input, $output, $question);
            $input->setArgument('identifier', $identifier);
        }
    }
}
