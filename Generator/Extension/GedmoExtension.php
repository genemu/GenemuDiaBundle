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

use Genemu\Bundle\DiaBundle\Mapping\ClassMetadataInfo;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class GedmoExtension extends GeneratorExtension
{
    /**
     * Initialization Tree
     * Add use ArrayCollection
     */
    public function initTree()
    {
        $target = $this->metadata->getTargetEntity();

        $this->metadata->addUse('Doctrine\Common\Collections\ArrayCollection', 'ArrayCollection');
        $this->metadata->addAnnotation('@'.$this->prefix.'\Tree("nested")');

        foreach (array('Root' => 'root', 'Left' => 'lft', 'Right' => 'rgt', 'Level' => 'lvl') as $name => $field) {
            $this->metadata->addField(
                array(
                    'name' => $field,
                    'type' => 'integer NOTNULL',
                    'methods' => array('get'),
                    'annotations' => array('@'.$this->prefix.'\Tree'.$name.'()')
                )
            );
        }

        $this->metadata->addAssociation('parent',
            array(
                'type' => 'ManyToOne',
                'fieldName' => 'parent',
                'targetEntity' => $target,
                'sourceEntity' => $this->metadata->getName(),
                'inversedBy' => 'children',
                'methods' => array('set', 'get'),
                'annotations' => array('@'.$this->prefix.'\TreeParent()')
            )
        );

        $this->metadata->addAssociation('children',
            array(
                'type' => 'OneToMany',
                'type_int' => 'Doctrine\Common\Collections\ArrayCollection',
                'fieldName' => 'children',
                'targetEntity' => $target,
                'sourceEntity' => $this->metadata->getName(),
                'mappedBy' => 'parent',
                'methods' => array('add', 'get'),
                'annotations' => array()
            )
        );
    }

    /**
     * Initialization Timestampable
     */
    public function initTimestampable()
    {
        $this->metadata->addField(
            array(
                'name' => 'createdAt',
                'type' => 'datetime NOTNULL',
                'methods' => array('get'),
                'annotations' => array('@'.$this->prefix.'\Timestampable(on="create")')
            )
        );

        $this->metadata->addField(
            array(
                'name' => 'updatedAt',
                'type' => 'datetime NOTNULL',
                'methods' => array('get'),
                'annotations' => array('@'.$this->prefix.'\Timestampable(on="update")')
            )
        );
    }

    /**
     * Initializtion Sluggable
     */
    public function initSluggable()
    {
        if (!$field = $this->isFieldExists()) {
            return null;
        }

        $this->metadata->updateField($field['fieldName'], array(
                'annotations' => array('@'.$this->prefix.'\Sluggable()')
            )
        );

        $paramSlug = array();
        foreach ($this->parameters as $attr => $parameter) {
            if ($attr != 'column') {
                $paramSlug[] = $attr.'="'.$parameter.'"';
            }
        }

        foreach ($field as $attr => $value) {
            if ($attr == 'unique') {
                $paramSlug[] = $attr.'="'.$value.'"';
            }
        }

        $length = isset($field['length'])?$field['length']:'';
        $unique = isset($field['unique'])?' UNIQUE':'';

        $this->metadata->addField(
            array(
                'name' => 'slug',
                'type' => 'string('.$length.')'.$unique.' NOTNULL',
                'methods' => array('get'),
                'annotations' => array('@'.$this->prefix.'\Slug('.implode(', ', $paramSlug).')')
            )
        );
    }
}
