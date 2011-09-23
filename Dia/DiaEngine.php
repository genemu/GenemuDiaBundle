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
    protected $use;

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
        $this->use = 'ORM';
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
     * Set use
     *
     * @param string $type
     */
    public function setUse($type)
    {
        $this->use = $type;
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
            $orm = $this->extensions[$this->use];

            $name = $element->getName();
            $abstract = $element->isAbstract();

            $package = $this->xml->getNamePackage($element);

            $bundle = $this->kernel->getBundle($package);
            $type = ($this->use == 'ORM')?'Entity':'Document';
            $path = $bundle->getPath().'/'.$type;
            $namespace = $bundle->getNamespace().'\\'.$type;

            $prefix = str_replace('Bundle', '', $bundle->getName());
            $prefix = preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $prefix);

            $class = new ClassMetadataInfo($name, $namespace, $path, $abstract);
            $class->setTable(array(
                'prefix' => strtolower($prefix),
                'name' => strtolower($name),
                'annotations' => array()
            ));
            $class->addUse($orm['namespace'], $this->use);
            $class->setExtensions(
                array('Annotations', 'Fields', 'Methods'),
                new $orm['generator']($class, $this->use)
            );

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

                $params = array();
                foreach ($operation->getParameters() as $parameter) {
                    $params[$parameter->getName()] = $parameter->getType();
                }

                foreach ($this->extensions as $prefix => $extension) {
                    if (
                        (
                            in_array($name, $extension['types']) &&
                            !in_array($prefix, array('ORM', 'MongoDB'))
                        ) ||
                        $this->use == $prefix
                    ) {
                        $generator = $extension['generator'];
                        $generator = new $generator($class, $prefix, $params);

                        $class->addUse($extension['namespace'], $prefix);
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
            $connect = $association->getConnection(
                $this->classes,
                'association'
            );
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
