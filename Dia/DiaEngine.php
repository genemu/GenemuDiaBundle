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
    protected $extensions;
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

    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
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

            $prefix = str_replace('Bundle', '', $bundle->getName());
            $prefix = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $prefix));

            $class = new ClassMetadataInfo($name, $path, $abstract);
            $class->setNamespace($this->registry->getEntityNamespace($bundle->getName()));
            $class->setTableName($prefix.'_'.strtolower($name));

            foreach ($element->getAttributes() as $attribute) {
                $class->addField(array(
                    'name' => $attribute->getName(),
                    'type' => $attribute->getType(),
                    'default' => $attribute->getValue()
                ));
            }

            foreach ($element->getOperations() as $operation) {
                $name = $operation->getName();

                foreach ($this->extensions as $prefix => $extension) {
                    if (in_array($name, $extension['types'])) {
                        $class->addUse($extension['namespace'], $prefix);

                        $generator = new $extension['generator']();
                        if (method_exists($generator, 'init'.$name)) {
                            $generator->{'init'.$name}($class);
                        }
                    }
                }
            }

            $this->classes[$element->getId()] = $class;
        }

        return $this->classes;
    }
}
