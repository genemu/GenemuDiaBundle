<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Genemu\Bundle\DiaBundle\Dia\DiaEngine;
use Genemu\Bundle\DiaBundle\Generator\EntityGenerator;

/**
 * DiaCreateEntityCommand
 *
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class DiaCreateEntityCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dia:entity:create')
            ->setDescription('Create entity for schema dia')
            ->addArgument('file', InputArgument::REQUIRED, 'file')
            ->addOption('type', false, InputOption::VALUE_NONE, 'type');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $kernel = $container->get('kernel');
        $registry = $container->get('doctrine');

        $dia = new DiaEngine($kernel, $registry);
        $dia->loadFile($input->getArgument('file'));
        $dia->setExtensions($container->getParameter('genemu_dia.extensions'));

        $dia->setUse('ORM');
        if ($input->getOption('type') == 'mongo') {
            $dia->setUse('MongoDB');
        }

        foreach ($dia->getClasses() as $class) {
            $generator = $this->getEntityGenerator();
            $generator->generateEntity($class);
        }
    }

    /**
     * Create Entity generator
     */
    protected function getEntityGenerator()
    {
        $generator = new EntityGenerator();

        return $generator;
    }
}
