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
    protected $isMappedSuperclass;
    protected $name;
    protected $path;
    protected $parent;
    protected $children;
    protected $namespace;
    protected $extensions;
    protected $uses;
    protected $repositoryUse;
    protected $table;
    protected $annotations;
    protected $fields;
    protected $associations;
    protected $methods;

    /**
     * Construct
     *
     * @param string  $name
     * @param string  $path
     * $param boolean $abstract
     */
    public function __construct($name, $namespace, $path, $abstract = false)
    {
        $this->isAbstract = $abstract;
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;

        $this->annotations = array();
        $this->extensions = array();
        $this->fields = array();
        $this->associations = array();
        $this->methods = array();
    }

    /**
     * Get string name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get string namespace
     *
     * @return string $namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get string repository namespace
     *
     * @return string $namespace
     */
    public function getRepositoryNamespace()
    {
        return $this->namespace.'\\Repository';
    }

    /**
     * Get string target entity
     *
     * @return string $namespace\$name
     */
    public function getTargetEntity()
    {
        return $this->namespace.'\\'.$this->name;
    }

    /**
     * Get target repository
     *
     * @return string $namespace\repository\$name
     */
    public function getTargetRepository()
    {
        return $this->getRepositoryNamespace().'\\'.$this->name;
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
     * Get path repository
     *
     * @return string $path
     */
    public function getRepositoryPath()
    {
        return $this->path.'/Repository';
    }

    /**
     * Get repository use
     *
     * @return string $repositoryUse
     */
    public function getRepositoryUse()
    {
        return $this->repositoryUse;
    }

    /**
     * Get table
     *
     * @param string $name
     *
     * @return string\array\null $table
     */
    public function getTable($name)
    {
        return isset($this->table[$name])?$this->table[$name]:null;
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
     * Get code namespace
     *
     * @return string $namespace
     */
    public function getCodeNamespace()
    {
        return 'namespace '.$this->namespace.';';
    }

    /**
     * Get code repository namespace
     *
     * @return string $namespace
     */
    public function getCodeRepositoryNamespace()
    {
        return 'namespace '.$this->getRepositoryNamespace().';';
    }

    /**
     * Get code uses
     *
     * @return array $uses
     */
    public function getCodeUses()
    {
        $uses = array();
        foreach ($this->uses as $namespace => $as) {
            $uses[] = 'use '.$namespace.($as?' as '.$as:'').';';
        }

        return $uses;
    }

    /**
     * Get code class
     *
     * @return string $class
     */
    public function getCodeClass()
    {
        $abstract = $this->isAbstract?'abstract ':'';
        $parent = $this->parent?' extends '.$this->parent->getName():'';

        return $abstract.'class '.$this->name.$parent;
    }

    /**
     * Get code repository class
     *
     * @return string $class
     */
    public function getCodeRepositoryClass()
    {
        $repository = substr($this->repositoryUse, strrpos($this->repositoryUse, '\\')+1);

        return 'class '.$this->name.($repository?' extends '.$repository:'');
    }

    /**
     * Get code repository
     *
     * @param string $spaces
     *
     * @return string $repository
     */
    public function getCodeRepository($spaces = '')
    {
        return $spaces.'repositoryClass="'.$this->getTargetRepository().'"';
    }

    /**
     * Get code repository use
     *
     * @return string $repositoryUse
     */
    public function getCodeRepositoryUse()
    {
        return 'use '.$this->repositoryUse.';';
    }

    /**
     * Get code table
     *
     * @param string $spaces
     *
     * @return string $table
     */
    public function getCodeTable($spaces = '')
    {
        $prefix = isset($this->table['prefix'])?$this->table['prefix'].'_':'';
        $table = array($spaces.'name="'.$prefix.$this->table['name'].'",');

        foreach ($this->table['annotations'] as $annotation) {
            $table[] = $spaces.$annotation.',';
        }

        $table[count($table)-1] = substr(end($table), 0, -1);

        return $table;
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
     * Get extension
     *
     * @param string $name
     *
     * @return array $extension
     */
    public function getExtension($name)
    {
        return isset($this->extensions[$name])?$this->extensions[$name]:null;
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
     * Get methods
     *
     * @return array $methods
     */
    public function getMethods()
    {
        return $this->methods;
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
     * Is mapped super class
     *
     * @return boolean $isMappedSuperclass
     */
    public function isMappedSuperclass()
    {
        return $this->isMappedSuperclass;
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
     * Set table
     *
     * @param array $table
     */
    public function setTable(array $table)
    {
        $this->table = $table;
    }

    /**
     * Set table name
     *
     * @param string $name
     */
    public function setTableName($name)
    {
        $this->table['name'] = $name;
    }

    /**
     * Add table annotation
     *
     * @param string $annotation
     */
    public function addTableAnnotation($annotation)
    {
        $this->table['annotations'][] = $annotation;
    }

    /**
     * Add annotation class
     *
     * @param string $annotation
     */
    public function addAnnotation($annotation)
    {
        $this->annotations[] = $annotation;
    }

    /**
     * Remove annotation class
     *
     * @param $integer $index
     */
    public function removeAnnotation($index)
    {
        if (!isset($this->annotations[$index])) {
            return null;
        }

        unset($this->annotations[$index]);
    }

    /**
     * Add annotations class
     *
     * @param array $annotations
     */
    public function addAnnotations(array $annotations)
    {
        $this->annotations = array_merge($this->annotations, $annotations);
    }

    /**
     * Add method class
     *
     * @param string $name
     * @param array  $annotations
     * @param array  $attributes
     * @param array  $contents
     */
    public function addMethod($name, array $annotations, array $attributes, array $contents)
    {
        $this->methods[$name] = array(
            'annotations' => $annotations,
            'attributes' => $attributes,
            'contents' => $contents
        );
    }

    /**
     * Set extensions
     *
     * @param array              $extensions
     * @param GeneratorExtension $generator
     */
    public function setExtensions(array $extensions, $generator)
    {
        foreach ($extensions as $name) {
            $this->extensions[$name] = array($generator);
        }
    }

    /**
     * Add extension
     *
     * @param string             $name
     * @param GeneratorExtension $generator
     */
    public function addExtension($name, $generator)
    {
        if (!isset($this->extensions[$name])) {
            $this->extensions[$name] = array();
        }

        $this->extensions[$name] = array_merge($this->extensions[$name], array($generator));
    }

    /**
     * Set parent
     *
     * @param ClassMetadataInfo $parent
     */
    public function setParent(ClassMetadataInfo $parent)
    {
        if ($this->namespace != $parent->getNamespace()) {
            $this->addUse($parent->getTargetEntity());
        }

        $this->parent = $parent;
    }

    /**
     * Add children
     *
     * @param ClassMetadataInfo $children
     */
    public function addChildren(ClassMetadataInfo $children)
    {
        $this->children[$children->getTargetEntity()] = $children;
    }

    /**
     * Set children
     *
     * @param array $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * Set repositoryUse
     *
     * @param string $repositoryUse
     */
    public function setRepositoryUse($repositoryUse)
    {
        $this->repositoryUse = $repositoryUse;
    }

    /**
     * Add use
     *
     * @param string $namespace
     * @param string $as
     */
    public function addUse($namespace, $as = null)
    {
        $namespace = ($namespace == 'Collection')?'Doctrine\Common\Collections\ArrayCollection':$namespace;

        $this->uses[$namespace] = $as;
    }

    /**
     * Get annotations class
     *
     * @return array $annotations
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Add field
     *
     * @param array $attributes
     */
    public function addField(array $attributes)
    {
        $field = array(
            'nullable' => 'true',
            'methods' => array('get', 'set'),
            'annotations' => array()
        );

        foreach ($attributes as $attr => $value) {
            if ($value) {
                switch ($attr) {
                    case 'name':
                        $field['fieldName'] = $value;

                        $column = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $value));
                        if ($column != $value) {
                            $field['columnName'] = $column;
                        }
                        break;
                    case 'type':
                        foreach (explode(' ', $value) as $type) {
                            preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                            $type = strtolower(isset($matches[1][0])?$matches[1][0]:$type);
                            $length = isset($matches[2][0])?$matches[2][0]:null;

                            switch ($type) {
                                case 'primarykey':
                                    $field['id'] = true;
                                    $field['type'] = 'integer';
                                    $field['methods'] = array('get');
                                    unset($field['nullable']);
                                    break;
                                case 'precision':
                                case 'scale':
                                    $field[$type] = $length;
                                    break;
                                case 'notnull':
                                    unset($field['nullable']);
                                    break;
                                case 'unique':
                                    $field['unique'] = 'true';
                                    break;
                                case 'string':
                                case 'integer':
                                case 'smallint':
                                case 'bigint':
                                case 'boolean':
                                case 'decimal':
                                case 'date':
                                case 'time':
                                case 'datetime':
                                case 'text':
                                case 'object':
                                case 'array':
                                case 'float':
                                    $field['type'] = $type;

                                    if ($type == 'datetime') {
                                        $field['type_int'] = '\\DateTime';
                                    }

                                    if ($length) {
                                        $field['length'] = $length;
                                    }
                                    break;
                            }
                        }
                        break;
                    case 'default':
                    case 'methods':
                    case 'annotations':
                        $field[$attr] = $value;
                        break;
                }
            }
        }

        $this->fields[$field['fieldName']] = $field;
    }

    /**
     * Update field
     *
     * @param string $name
     * @param array  $attributes
     */
    public function updateField($name, array $attributes)
    {
        if (!isset($this->fields[$name])) {
            return;
        }

        foreach ($attributes as $type => $values) {
            if (isset($this->fields[$name][$type])) {
                $old = $this->fields[$name][$type];

                if (is_array($old) && is_array($values)) {
                    $this->fields[$name][$type] = array_merge($old, $values);
                } else {
                    $this->fields[$name][$type] = $values;
                }
            }
        }
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
     * Update association
     *
     * @param string $name
     * @param array  $attributes
     */
    public function updateAssociation($name, array $attributes)
    {
        if (!isset($this->associations[$name])) {
            return;
        }

        foreach ($attributes as $type => $values) {
            if (isset($this->associations[$name][$type])) {
                $old = $this->associations[$name][$type];

                if (is_array($old) && is_array($values)) {
                    $this->associations[$name][$type] = array_merge($old, $values);
                } else {
                    $this->associations[$name][$type] = $values;
                }
            } else {
                $this->associations[$name][$type] = $values;
            }
        }
    }

    /**
     * Add One To Many
     *
     * @param ClassMetadataInfo $class
     * @param string            $name
     */
    public function addOneToMany(ClassMetadataInfo $class, $name = null)
    {
        $this->addUse('Collection');

        if ($this->namespace != $class->getNamespace()) {
            $this->addUse($class->getTargetEntity());
            $class->addUse($this->gettargetEntity());
        }


        $nameFrom = strtolower($name?$name:$this->name);
        $nameTo = strtolower($name?$name:$class->getName());

        $this->associations[$nameTo] = array(
            'type' => 'OneToMany',
            'type_int' => 'Doctrine\Common\Collections\ArrayCollection',
            'fieldName' => $nameTo.'s',
            'targetEntity' => $class->getTargetEntity(),
            'mappedBy' => $nameFrom,
            'methods' => array('add', 'get'),
            'annotations' => array()
        );

        $class->addAssociation($nameFrom, array(
            'type' => 'ManyToOne',
            'fieldName' => $nameFrom,
            'targetEntity' => $this->getTargetEntity(),
            'inversedBy' => $nameTo.'s',
            'joinColumn' => array(
                'name' => $nameFrom.'_id',
                'referencedColumnName' => 'id'
            ),
            'methods' => array('set', 'get'),
            'annotations' => array()
        ));
    }

    /**
     * Add Many To Many
     *
     * @param ClassMetadataInfo $class
     */
    public function addManyToMany(ClassMetadataInfo $class)
    {
        $this->addUse('Collection');
        $class->addUse('Collection');

        if ($this->namespace != $class->getNamespace()) {
            $this->addUse($class->getTargetEntity());
            $class->addUse($this->gettargetEntity());
        }

        $prefix = isset($this->table['prefix'])?$this->table['prefix'].'_':'';

        $nameTo = strtolower($class->getName());
        $nameFrom = strtolower($this->name);

        $this->associations[$nameTo] = array(
            'type' => 'ManyToMany',
            'fieldName' => $nameTo.'s',
            'targetEntity' => $class->getTargetEntity(),
            'mappedBy' => $nameFrom.'s',
            'sourceEntity' => $class->getName(),
            'methods' => array('add', 'get'),
            'annotations' => array()
        );

        $class->addAssociation($nameFrom, array(
            'type' => 'ManyToMany',
            'fieldName' => $nameFrom.'s',
            'targetEntity' => $this->getTargetEntity(),
            'inversedBy' => $nameTo.'s',
            'sourceEntity' => $this->name,
            'joinColumn' => array(
                'name' => $prefix.$nameTo.'s_'.$nameFrom.'s',
                'joinColumns' => array(
                    'name' => $nameTo.'_id',
                    'referencedColumnName' => 'id'
                ),
                'inverseJoinColumns' => array(
                    'name' => $nameFrom.'_id',
                    'referencedColumnName' => 'id'
                )
            ),
            'methods' => array('add', 'get'),
            'annotations' => array()
        ));
    }
}
