<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Dia;

use Genemu\Bundle\DiaBundle\Mapping\ClassMetadataInfo;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class DiaEngine
{
    protected $kernel;
    protected $registry;
    protected $xml;
    protected $classes;

    public function __construct($kernel, $registry)
    {
        $this->kernel = $kernel;
        $this->registry = $registry;
    }

    public function loadFile($file)
    {
        $zd = gzopen($file, 'r');
        $contents = gzread($zd, 512000);
        gzclose($zd);

        $this->xml = new DiaXML($contents);
    }

    public function getClasses()
    {
        if ($this->classes) {
            return $this->classes;
        }

        foreach ($this->xml->getClasses() as $element) {
            $name = $element->getName();
            $abstract = $element->isAbstract();
            $bundle = $this->kernel->getBundle($this->xml->getNamePackage($element));
            $path = $bundle->getPath().'/Entity';

            $class = new ClassMetadataInfo($name, $path, $abstract);
            $class->setNamespace($this->registry->getEntityNamespace($bundle->getName()));

            $this->classes[$element->getId()] = $class;
        }

        return $this->classes;
    }
}
