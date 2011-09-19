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

    /**
     * Construct
     *
     * @param Kernel   $kernel
     * @param Registry $registry
     */
    public function __construct($kernel, $registry)
    {
        $this->kernel = $kernel;
        $this->registry = $registry;
    }

    /**
     * Load file dia
     *
     * @param string $file
     */
    public function loadFile($file)
    {
        $zd = gzopen($file, 'r');
        $contents = gzread($zd, 512000);
        gzclose($zd);

        $this->xml = new DiaXML($contents);
    }

    /**
     * Set extensions
     *
     * @param array $extensions
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Get classes
     *
     * @return array $classes
     */
    public function getClasses()
    {
        if ($this->classes) {
            return $this->classes;
        }

        /**
         * Search all class to xml document
         */
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

            /**
             * Search attributes to class
             */
            foreach ($element->getAttributes() as $attribute) {
                $class->addField(array(
                    'name' => $attribute->getName(),
                    'type' => $attribute->getType(),
                    'default' => $attribute->getValue()
                ));
            }

            /**
             * Search extensions to class
             */
            foreach ($element->getOperations() as $operation) {
                $name = $operation->getName();

                $parameters = array();
                foreach ($operation->getParameters() as $parameter) {
                    $parameters[$parameter->getName()] = $parameter->getType();
                }

                foreach ($this->extensions as $prefix => $extension) {
                    if (in_array($name, $extension['types'])) {
                        $class->addUse($extension['namespace'], $prefix);

                        $generator = new $extension['generator']($class, $prefix, $parameters);
                        if (method_exists($generator, 'init'.$name)) {
                            $generator->{'init'.$name}();
                        }

                        $class->addExtension($name, $generator);
                    }
                }
            }

            $this->classes[$element->getId()] = $class;
        }

        /**
         * Search parent class
         */
        foreach ($this->xml->getGeneralization() as $general) {
            $connect = $general->getConnection($this->classes);

            $connect['to']->setParent($connect['from']);
            $connect['from']->addChildren($connect['to']);
        }

        /**
         * Search associations
         */
        foreach ($this->xml->getAssociations() as $association) {
            $connect = $association->getConnection($this->classes, 'association');
            $name = $association->getName();

            switch ($association->getAssocType()) {
                case 0:
                    $connect['from']->addManyToMany($connect['to']);
                    break;
                case 1:
                    $connect['from']->addOneToMany($connect['to'], $name);
                    break;
            }
        }

        return $this->classes;
    }
}
