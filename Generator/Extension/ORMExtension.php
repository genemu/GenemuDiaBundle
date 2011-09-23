<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Generator\Extension;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class ORMExtension extends GeneratorExtension
{
    /**
     * Generate class annotations
     *
     * @return array $annotations
     */
    public function generateAnnotations()
    {
        $code = array(
            $this->metadata->getTargetEntity(),
            ''
        );

        if (!$this->metadata->isMappedSuperclass()) {
            $code[] = '@'.$this->prefix.'\Table(';
            $code = array_merge($code, $this->metadata->getCodeTable('<spaces>'));
            $code[] = ')';
            $code[] = '@'.$this->prefix.'\Entity(';
            $code[] = $this->metadata->getCodeRepository('<spaces>');
            $code[] = ')';
        }

        $code = array_merge($code, $this->metadata->getAnnotations());

        return $this->generateAnnotation($code);
    }

    /**
     * Generate class fields
     *
     * @return array $fields
     */
    public function generateFields()
    {
        $code = array();

        foreach ($this->metadata->getFields() as $field) {
            $params = array();
            foreach ($field as $attr => $value) {
                if (!in_array($attr, array('id', 'fieldName', 'default', 'methods', 'annotations', 'type_int'))) {
                    $params[] = (($attr == 'columnName')?'name':$attr).'="'.$value.'"';
                }
            }

            $annotations = array(
                '@var '.$field['type'].' $'.$field['fieldName'],
                '',
                '@'.$this->prefix.'\Column('.implode(', ', $params).')'
            );

            if (isset($field['id']) && $field['id']) {
                $annotations[] = '@'.$this->prefix.'\Id()';
                $annotations[] = '@'.$this->prefix.'\GeneratedValue(strategy="AUTO")';
            }

            $annotations = array_merge($annotations, $field['annotations']);

            $code[] = $this->generateField($field['fieldName'], $annotations);
        }

        foreach ($this->metadata->getAssociations() as $association) {
            $attributes = array();
            foreach ($association as $attr => $value) {
                if (in_array($attr, array(
                    'targetEntity',
                    'mappedBy',
                    'inversedBy',
                    'orphanRemoval',
                    'fetch'
                ))) {
                    $attributes[] = '<spaces>'.$attr.'="'.$value.'",';
                } elseif ($attr == 'cascade') {
                    $cascades = array();
                    foreach ($value as $cascade) {
                        $cascades[] = '"'.$cascade.'"';
                    }

                    $attributes[] = '<spaces>'.$attr.'={'.implode(', ', $cascades).'},';
                }
            }

            $annotations = array_merge(array(
                '@var '.$association['targetEntity'].' $'.$association['fieldName'],
                '',
                '@'.$this->prefix.'\\'.$association['type'].'('
            ), $attributes);

            $annotations[count($annotations)-1] = substr(end($annotations), 0, -1);
            $annotations[] = ')';

            if (isset($association['orderBy'])) {
                $order = $association['orderBy'];

                $annotations[] = '@'.$this->prefix.'\OrderBy({"'.$order['name'].'" = "'.$order['value'].'"})';
            }

            if (isset($association['joinColumn'])) {
                $join = $association['joinColumn'];

                if ($association['type'] == 'ManyToOne') {
                    $annotations[] = '@'.$this->prefix.'\JoinColumn(';
                    foreach ($join as $attr => $value) {
                        $annotations[] = '<spaces>'.$attr.'="'.$value.'",';
                    }
                } elseif ($association['type'] == 'ManyToMany') {
                    $annotations[] = '@'.$this->prefix.'\JoinTable(';
                    foreach ($join as $type => $values) {
                        if ($type == 'name') {
                            $annotations[] = '<spaces>'.$type.'="'.$values.'",';
                        } else {
                            $annotations[] = '<spaces>'.$type.'={@'.$this->prefix.'\JoinColumn(';
                            foreach ($values as $attr => $value) {
                                $annotations[] = '<spaces><spaces>'.$attr.'="'.$value.'",';
                            }

                            $annotations[count($annotations)-1] = substr(end($annotations), 0, -1);
                            $annotations[] = '<spaces>)},';
                        }
                    }
                }
                $annotations[count($annotations)-1] = substr(end($annotations), 0, -1);
                $annotations[] = ')';
            }

            $annotations = array_merge($annotations, $association['annotations']);

            $code[] = $this->generateField($association['fieldName'], $annotations);
        }

        return $code;
    }

    /**
     * Generate class methods
     *
     * @return array $methods
     */
    public function generateMethods()
    {
        $construct = array();
        foreach ($this->metadata->getFields() as $field) {
            if (isset($field['default']) && $field['default']) {
                $construct[] = '$this->'.$field['fieldName'].' = '.$field['default'].';';
            }
        }

        foreach ($this->metadata->getAssociations() as $field) {
            if (in_array($field['type'], array('ManyToMany', 'OneToMany'))) {
                $construct[] = '$this->'.$field['fieldName'].' = new ArrayCollection();';
            }
        }

        $code = array();
        if ($construct) {
            $code[] = $this->generateMethod('__construct', array('Construct'), array(), $construct);
        }

        foreach ($this->metadata->getFields() as $field) {
            $name = $field['fieldName'];
            $typeInt = (isset($field['type_int']))?$field['type_int']:$field['type'];
            $type = ($field['type'] != $typeInt)?$typeInt:'';

            $code = array_merge($code, $this->generateMethodFields(
                $field['methods'],
                array('name' => $name, 'type' => $type, 'type_int' => $typeInt, 'target' => $typeInt)
            ));
        }

        foreach ($this->metadata->getAssociations() as $field) {
            $name = $field['fieldName'];
            $target = $field['targetEntity'];
            $typeInt = (isset($field['type_int']))?$field['type_int']:$target;
            $type = substr($target, strrpos($target, '\\')+1);

            $code = array_merge($code, $this->generateMethodFields(
                $field['methods'],
                array('name' => $name, 'type' => $type, 'type_int' => $typeInt, 'target' => $target)
            ));
        }

        foreach ($this->metadata->getMethods() as $name => $values) {
            $code[] = $this->generateMethod($name, $values['annotations'], $values['attributes'], $values['contents']);
        }

        return $code;
    }

    /**
     * Initilization MappedSuperclass
     */
    public function initMappedSuperclass()
    {
        $this->metadata->setMappedSuperclass(true);
        $this->metadata->addAnnotation('@'.$this->prefix.'\MappedSuperclass()');
    }

    /**
     * Initialization Index
     */
    public function initIndex()
    {
        if (!isset($this->parameters['name']) || !isset($this->parameters['columns'])) {
            return null;
        }

        $columns = array();
        foreach (explode(',', $this->parameters['columns']) as $column) {
            $columns[] = '"'.$column.'"';
        }

        $params = array(
            'name="'.$this->parameters['name'].'"',
            'columns={'.implode(', ', $columns).'}'
        );

        $this->metadata->addTableAnnotation('indexes={@'.$this->prefix.'\Index('.implode(', ', $params).')}');
    }

    /**
     * Initialization InheritanceType
     */
    public function initInheritanceType()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        $this->metadata->addAnnotation('@'.$this->prefix.'\InheritanceType("'.$this->parameters['type'].'")');
    }

    /**
     * Initialization DiscrminatorColumn
     */
    public function initDiscriminatorColumn()
    {
        $param = array_replace(array(
            'name' => 'disrc',
            'type' => 'string'
        ), $this->parameters);

        $name = $this->metadata->getName();
        $namespace = $this->metadata->getNamespace().'\\'.$name;
        $column = array(
            'name="'.$param['name'].'"',
            'type="'.$param['type'].'"'
        );

        $code = array(
            '@'.$this->prefix.'\DiscriminatorColumn('.implode(', ', $column).')',
            '@'.$this->prefix.'\DiscriminatorMap({',
            '<spaces>"'.strtolower($name).'" = "'.$namespace.'",'
        );

        foreach ($this->metadata->getChildren() as $children) {
            $name = $children->getName();
            $namespace = $children->getNamespace().'\\'.$name;

            $code[] = '<spaces>"'.strtolower($name).'" = "'.$namespace.'",';
        }
        $code[count($code)-1] = substr(end($code), 0, -1);

        $code[] = '})';

        $this->metadata->addAnnotations($code);
    }

    /**
     * Initialization ChangeTrackingPolicy
     */
    public function initChangeTrackingPolicy()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        $this->metadata->addAnnotation('@'.$this->prefix.'\ChangeTrackingPolicy("'.$this->parameters['type'].'")');
    }

    /**
     * Initialization LifecycleCallbacks
     */
    public function initHasLifecycleCallbacks()
    {
        $this->addAnnotation('@'.$this->prefix.'\HasLifecycleCallbacks()');
    }

    /**
     * Initialization OneToMany
     */
    public function initOneToMany()
    {
        if (!$this->isAssociationExists()) {
            return null;
        }

        $field = array();
        foreach ($this->parameters as $attr => $value) {
            if ($attr == 'cascade') {
                $field[$attr] = explode(',', $value);
            } elseif (in_array($attr, array('orphanRemoval', 'fetch'))) {
                $field[$attr] = $value;
            } elseif ($attr == 'orderBy') {
                list($name, $value) = explode('=', $value);
                $field[$attr] = array('name' => $name, 'value' => $value);
            }
        }

        $this->metadata->updateAssociation(strtolower($this->parameters['sourceEntity']), $field);
    }
}
