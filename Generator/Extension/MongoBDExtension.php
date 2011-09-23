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
class MongoBDExtension extends GeneratorExtension
{
    public function generateAnnotations()
    {
        $code = array(
            $this->metadata->getTargetEntity(),
            ''
        );

        if (!$this->metadata->isMappedSuperclass()) {
            $code[] = '@'.$this->prefix.'\Document(';
            $code[] = '<spaces>collection="'.$this->metadata->getTable('name').'s",';
            $code[] = $this->metadata->getCodeRepository('<spaces>');
            $code[] = ')';
        }

        $code = array_merge($code, $this->metadata->getAnnotations());

        return $this->generateAnnotation($code);
    }

    public function initMappedSuperclass()
    {
        $this->metadata->setMappedSuperclass(true);
        $this->metadata->addAnnotation('@'.$this->prefix.'\MappedSuperclass()');
    }

    public function initInheritanceType()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        $this->metadata->addAnnotation('@'.$this->prefix.'\InheritanceType("'.$this->parameters['type'].'")');
    }
}
