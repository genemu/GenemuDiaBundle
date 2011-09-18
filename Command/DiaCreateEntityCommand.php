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

use Genemu\Bundle\DiaBundle\Dia\DiaEngine;
use Genemu\Bundle\DiaBundle\Generator\EntityGenerator;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class DiaCreateEntityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dia:entity:create')
            ->setDescription('Create entity for schema dia')
            ->addArgument('file', InputArgument::REQUIRED, 'file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $dia = new DiaEngine($container->get('kernel'), $container->get('doctrine'));
        $dia->loadFile($input->getArgument('file'));

        foreach ($dia->getClasses() as $class) {
            $generator = $this->getEntityGenerator();
            $generator->generateEntity($class);
        }
    }

    protected function getEntityGenerator()
    {
        $generator = new EntityGenerator();

        return $generator;
    }
}
