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
    public function initMappedSuperclass()
    {
        $this->metadata->setMappedSuperclass(true);
    }

    public function generateIndexClassTable()
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

        return 'indexes={@'.$this->prefix.'\Index('.implode(', ', $params).')}';
    }

    public function generateMappedSuperclassClassAnnotations()
    {
        return '@'.$this->prefix.'\MappedSuperclass()';
    }

    public function generateInheritanceTypeClassAnnotations()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        return '@'.$this->prefix.'\InheritanceType("'.$this->parameters['type'].'")';
    }

    public function generateDiscriminatorColumnClassAnnotations()
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

        return $code;
    }

    public function generateChangeTrackingPolicyClassAnnotations()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        return '@'.$this->prefix.'\ChangeTrackingPolicy("'.$this->parameters['type'].'")';
    }

    public function generateHasLifecycleCallbacksClassAnnotations()
    {
        return '@'.$this->prefix.'\HasLifecycleCallbacks()';
    }

    public function generateOneToManyAssociationFields(array $field)
    {
        if (
            !isset($this->parameters['sourceEntity']) ||
            $this->parameters['sourceEntity'] != $field['sourceEntity']
        ) {
            return null;
        }

        $code = array();
        foreach ($this->parameters as $attr => $value) {
            if ($attr == 'cascade') {
                $cascades = array();
                foreach (explode(',', $value) as $value) {
                    $cascades[] = '"'.$value.'"';
                }
                $code[] = '<spaces>'.$attr.'={'.implode(', ', $cascades).'},';
            } elseif (in_array($attr, array('orphanRemoval', 'fetch'))) {
                $code[] = '<spaces>'.$attr.'="'.$value.'",';
            }
        }
        $code[count($code)-1] = substr(end($code), 0, -1);

        return $code;
    }
}
