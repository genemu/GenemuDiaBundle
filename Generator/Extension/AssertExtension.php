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
class AssertExtension extends GeneratorExtension
{
    public function initNotBlank()
    {
        $this->updateAnnotation('NotBlank');
    }

    public function initBlank()
    {
        $this->updateAnnotation('Blank');
    }

    public function initNotNull()
    {
        $this->updateAnnotation('NotNull');
    }

    public function initNull()
    {
        $this->updateAnnotation('Null');
    }

    public function initTrue()
    {
        $this->updateAnnotation('True');
    }

    public function initFalse()
    {
        $this->updateAnnotation('False');
    }

    public function initType()
    {
        $this->updateAnnotation('Type');
    }

    protected function updateAnnotation($type)
    {
        if (!$field = $this->isFieldExists()) {
            return null;
        }

        unset($this->parameters['column']);

        $annotations = array('@'.$this->prefix.'\\'.$type.'()');
        $parameters = array();
        foreach ($this->parameters as $attr => $value) {
            $parameters[] = '<spaces>'.$attr.'="'.$value.'",';
        }

        if ($parameters) {
            $parameters[count($parameters)-1] = substr(end($parameters), 0, -1);

            $annotations = array('@'.$this->prefix.'\\'.$type.'(');
            $annotations = array_merge($annotations, $parameters);
            $annotations[] = ')';
        }

        $this->metadata->updateField($field['fieldName'], array('annotations' => $annotations));
    }
}
