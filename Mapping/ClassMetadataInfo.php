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

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class ClassMetadataInfo
{
    protected $isAbstract;
    protected $name;
    protected $path;
    protected $parent;
    protected $namespace;
    protected $uses;
    protected $tableName;
    protected $isMappedSuperclass;
    protected $fields;
    protected $associations;

    public function __construct($name, $path, $abstract = false)
    {
        $this->isAbstract = $abstract;
        $this->name = $name;
        $this->path = $path;

        $this->fields = array();
        $this->associations = array();
        $this->addUse('Doctrine\ORM\Mapping', 'ORM');
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        if ($this->namespace != $parent->getNamespace()) {
            $this->addUse($parent->getNamespace().'\\'.$parent->getName(), $parent->getName());
        }
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function setMappedSuperclass($boolean)
    {
        $this->isMappedSuperclass = $boolean;
    }

    public function addUse($mapping, $prefix)
    {
        if (!isset($this->uses[$prefix])) {
            $this->uses[$prefix] = $mapping;
        }
    }

    public function isAbstract()
    {
        return $this->isAbstract;
    }

    public function isMappedSuperclass()
    {
        return $this->isMappedSuperclass;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getUses()
    {
        return $this->uses;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getAssociations()
    {
        return $this->associations;
    }

    public function addField(array $attributes)
    {
        $types = explode(' ', $attributes['type']);
        $type = $types[0];

        preg_match_all('/(.*)\((.*)\)/', $type, $matches);

        $name = $attributes['name'];
        $default = $attributes['default'];
        $column = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));
        $type = isset($matches[1][0])?$matches[1][0]:$type;
        $length = isset($matches[2][0])?$matches[2][0]:null;

        $field = array(
            'fieldName' => $name,
            'type' => ($type != 'primaryKey')?$type:'integer'
        );

        if ($type == 'primaryKey') {
            $field['id'] = true;
        } elseif ($type == 'datetime') {
            $field['type_int'] = '\\DateTime';
        }

        if ($name != $column) {
            $field['columnName'] = $column;
        }

        if ($default) {
            $field['default'] = $default;
        }

        if ($length) {
            $field['length'] = $length;
        }

        if (!in_array('NOTNULL', $types) && $type != 'primaryKey') {
            $field['nullable'] = 'true';
        }

        if (in_array('UNIQUE', $types) && $type != 'primaryKey') {
            $field['unique'] = 'true';
        }

        $this->fields[$name] = $field;
    }

    public function addAssociation($name, array $attributes)
    {
        $this->associations[$name] = $attributes;
    }

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
            'mappedBy' => $nameFrom
        ));

        $class->addAssociation($nameFrom, array(
            'type' => 'ManyToOne',
            'fieldName' => $nameFrom,
            'targetEntity' => $targetFrom,
            'inversedBy' => $nameTo
        ));
    }

    public function addManyToMany(ClassMetadataInfo $class)
    {

    }

    public function getFields()
    {
        return $this->fields;
    }
}
