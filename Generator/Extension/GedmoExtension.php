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
     * Init Tree
     * Add use ArrayCollection
     */
    public function initTree()
    {
        $this->metadata->addUse('Doctrine\Common\Collections\ArrayCollection', 'ArrayCollection');
    }

    /**
     * Generate annotation Tree to Entity
     *
     * @return string $annotation
     */
    public function generateTreeClassAnnotations()
    {
        return '@'.$this->prefix.'\Tree(type="nested")';
    }

    /**
     * Add method construct $children
     *
     * @return string $construct
     */
    public function generateTreeConstruct()
    {
        return '$this->children = new ArrayCollection();';
    }

    /**
     * Generate fields Tree
     *
     * @return array $fields
     */
    public function generateTreeFields()
    {
        $target = $this->metadata->getNamespace().'\\'.$this->metadata->getName();
        $code = array();

        foreach (array('Root' => 'root', 'Left' => 'lft', 'Right' => 'rgt', 'Level' => 'lvl') as $name => $field) {
            $code[] = $this->generateField(
                $field,
                array(
                    '@var integer $'.$field,
                    '',
                    '@'.$this->prefix.'\Tree'.$name.'()',
                    '@'.$this->prefixO.'\Column(type="integer")'
                )
            );
        }

        $code[] = $this->generateField(
            'parent',
            array(
                '@var '.$target.' $parent',
                '',
                '@'.$this->prefix.'\TreeParent()',
                '@'.$this->prefixO.'\ManyToOne(targetEntity="'.$target.'", inversedBy="children")'
            )
        );

        $code[] = $this->generateField(
            'children',
            array(
                '@var '.$target.' $children',
                '',
                '@'.$this->prefixO.'\OneToMany(targetEntity="'.$target.'", mappedBy="parent")',
                '@'.$this->prefixO.'\OrderBy=({"lft" = "DESC"})'
            )
        );

        return $code;
    }

    /**
     * Generate methods Tree
     *
     * @return array $methods
     */
    public function generateTreeMethods()
    {
        $name = $this->metadata->getName();
        $target = $this->metadata->getNamespace().'\\'.$name;
        $code = array();

        foreach (array('root', 'lft', 'rgt', 'lvl') as $method) {
            $code[] = $this->generateMethod(
                'get'.ucfirst($method),
                array('get $'.$method, '', '@return integer $'.$method),
                array(),
                array('return $this->'.$method.';')
            );
        }

        $code[] = $this->generateMethod(
            'getParent',
            array('get parent', '', '@return '.$target.' $parent'),
            array(),
            array('return $this->parent;')
        );

        $code[] = $this->generateMethod(
            'getChildren',
            array('get $children', '', '@return \Doctrine\Common\Collections\ArrayCollection $children'),
            array(),
            array('return $this->children;')
        );

        $code[] = $this->generateMethod(
            'setParent',
            array('set $parent', '', '@param '.$target.' $parent'),
            array($name.' $parent'),
            array('$this->parent = $parent;')
        );

        $code[] = $this->generateMethod(
            'addChildren',
            array('add $children', '', '@param '.$target.' $children'),
            array($name.' $children'),
            array('$this->children->add($children);')
        );

        return $code;
    }

    /**
     * Generate fileds Timestampable
     *
     * @return array $fields
     */
    public function generateTimestampableFields()
    {
        return array(
            $this->generateField(
                'creadedAt',
                array(
                    '@var \DateTime $createdAt',
                    '',
                    '@'.$this->prefix.'\Timestampable(on="create")',
                    '@'.$this->prefixO.'\Column(name="created_at", type="datetime")'
                )
            ),
            $this->generateField(
                'updatedAt',
                array(
                    '@var \DateTime $updatedAt',
                    '',
                    '@'.$this->prefix.'\Timestampable(on="update")',
                    '@'.$this->prefixO.'\Column(name="updated_at", type="datetime")'
                )
            )
        );
    }

    /**
     * Generate methods Timestampable
     *
     * @return array $methods
     */
    public function generateTimestampableMethods()
    {
        return array(
            $this->generateMethod(
                'getCreatedAt',
                array('get $createdAt', '', '@return \DateTime $createdAt'),
                array(),
                array('return $this->createdAt;')
            ),
            $this->generateMethod(
                'getUpdatedAt',
                array('get $updatedAt', '', '@return \DateTime $updatedAt'),
                array(),
                array('return $this->updatedAt;')
            )
        );
    }
}
