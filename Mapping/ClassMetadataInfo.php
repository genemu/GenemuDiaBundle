<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Mapping;

use Genemu\Bundle\DiaBundle\Generator\Extension\GeneratorExtension;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class ClassMetadataInfo
{
    protected $isAbstract;
    protected $name;
    protected $path;
    protected $parent;
    protected $children;
    protected $namespace;
    protected $extensions;
    protected $uses;
    protected $tableName;
    protected $isMappedSuperclass;
    protected $fields;
    protected $associations;

    /**
     * Construct
     * Add default use Doctrine\ORM\Mapping as ORM
     *
     * @param string  $name
     * @param string  $path
     * $param boolean $abstract
     */
    public function __construct($name, $path, $abstract = false)
    {
        $this->isAbstract = $abstract;
        $this->name = $name;
        $this->path = $path;

        $this->extensions = array();
        $this->fields = array();
        $this->associations = array();
        $this->addUse('Doctrine\ORM\Mapping', 'ORM');
    }

    /**
     * Set parent class
     *
     * @param ClassMetadataInfo $parent
     */
    public function setParent(ClassMetadataInfo $parent)
    {
        $this->parent = $parent;

        if ($this->namespace != $parent->getNamespace()) {
            $this->addUse($parent->getNamespace().'\\'.$parent->getName(), $parent->getName());
        }
    }

    /**
     * Add children class
     *
     * @param ClassMetadataInfo $children
     */
    public function addChildren(ClassMetadataInfo $children)
    {
        $this->children[$children->getName()] = $children;
    }

    /**
     * Set namepsace
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Set table name
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Set mappedSuperclass
     *
     * @param boolean $boolean
     */
    public function setMappedSuperclass($boolean)
    {
        $this->isMappedSuperclass = $boolean;
    }

    /**
     * Add extension
     *
     * @param string             $type
     * @param GeneratorExtension $generator
     */
    public function addExtension($type, GeneratorExtension $generator)
    {
        if (!isset($this->extensions[$type])) {
            $this->extensions[$type] = array();
        }

        $this->extensions[$type] = array_merge($this->extensions[$type], array($generator));
    }

    /**
     * Add use
     *
     * @param string $mapping
     * @param string $prefix
     */
    public function addUse($mapping, $prefix)
    {
        if (!isset($this->uses[$prefix])) {
            $this->uses[$prefix] = $mapping;
        }
    }

    /**
     * Is abstract
     *
     * @return boolean $isAbstract
     */
    public function isAbstract()
    {
        return $this->isAbstract;
    }

    /**
     * Is mappedSuperclass
     *
     * @return boolean $isMappedSuperclass
     */
    public function isMappedSuperclass()
    {
        return $this->isMappedSuperclass;
    }

    /**
     * Get parent
     *
     * @return ClassMetadataInfo $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get children
     *
     * @return array $children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get namespace
     *
     * @return string $namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get extensions
     *
     * @return array $extensions
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get uses
     *
     * @return array $uses
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Get table name
     *
     * @return string $tableName
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get associations
     *
     * @return array $associations
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Get fields
     *
     * @return array $fields
     */
    public function addField(array $attributes)
    {
        $types = explode(' ', $attributes['type']);

        $name = $attributes['name'];
        $default = $attributes['default'];
        $column = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));

        $field = array('fieldName' => $name);

        foreach ($types as $type) {
            preg_match_all('/(.*)\((.*)\)/', $type, $matches);
            $attr = array(
                isset($matches[1][0])?$matches[1][0]:$type,
                isset($matches[2][0])?$matches[2][0]:null
            );

            if ($attr[0] == 'scale') {
                $field['scale'] = $attr[1];
            } elseif ($attr[0] == 'precision') {
                $field['precision'] = $attr[1];
            } elseif (in_array($attr[0], array(
                'string',
                'integer',
                'smallint',
                'bigint',
                'boolean',
                'decimal',
                'date',
                'time',
                'datetime',
                'text',
                'object',
                'array',
                'float'
            ))) {
                $field['type'] = $attr[0];

                if ($attr[0] == 'datetime') {
                    $field['type_int'] = '\\DateTime';
                }

                if ($attr[1]) {
                    $field['length'] = $attr[1];
                }
            } elseif ($attr[0] == 'primaryKey') {
                $field['type'] = 'integer';
                $field['id'] = true;
            }
        }

        if ($name != $column) {
            $field['columnName'] = $column;
        }

        if ($default) {
            $field['default'] = $default;
        }

        if (!in_array('NOTNULL', $types) && $type != 'primaryKey') {
            $field['nullable'] = 'true';
        }

        if (in_array('UNIQUE', $types) && $type != 'primaryKey') {
            $field['unique'] = 'true';
        }

        $this->fields[$name] = $field;
    }

    /**
     * Add association
     *
     * @param string $name
     * @param array  $attributes
     */
    public function addAssociation($name, array $attributes)
    {
        $this->associations[$name] = $attributes;
    }

    /**
     * Add OneToMany association
     *
     * @param ClassMetadataInfo $class
     * @param string            $name
     */
    public function addOneToMany(ClassMetadataInfo $class, $name = null)
    {
        $this->addUse('Doctrine\Common\Collections\ArrayCollection', 'ArrayCollection');

        $nameFrom = strtolower($name?$name:$this->name);
        $nameTo = strtolower($name?$name:$class->getName()).'s';
        $targetFrom = $this->namespace.'\\'.$this->name;
        $targetTo = $class->getNamespace().'\\'.$class->getName();

        if ($this->namespace != $class->getNamespace()) {
            $this->addUse($targetTo, $class->getName());
            $class->addUse($targetFrom, $this->name);
        }

        $this->addAssociation($nameTo, array(
            'type' => 'OneToMany',
            'fieldName' => $nameTo,
            'targetEntity' => $targetTo,
            'sourceEntity' => $class->getName(),
            'mappedBy' => $nameFrom
        ));

        $class->addAssociation($nameFrom, array(
            'type' => 'ManyToOne',
            'fieldName' => $nameFrom,
            'targetEntity' => $targetFrom,
            'sourceEntity' => $this->name,
            'inversedBy' => $nameTo
        ));
    }

    /**
     * Add ManyToMany association
     *
     * @param ClassMetdataInfo $class
     */
    public function addManyToMany(ClassMetadataInfo $class)
    {

    }

    /**
     * Get fields
     *
     * @return array $fields
     */
    public function getFields()
    {
        return $this->fields;
    }
}
