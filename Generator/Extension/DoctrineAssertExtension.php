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
class DoctrineAssertExtension extends GeneratorExtension
{
    /**
     * Initialization UniqueEntity
     */
    public function initUniqueEntity()
    {
        if (!isset($this->parameters['fields'])) {
            return null;
        }

        $fields = array();
        foreach ($this->parameters['fields'] as $field) {
            $fields[] = '"'.$field.'"';
        }
        $fields = implode(', ', $fields);
        $annotation = '@'.$this->prefix.'\UniqueEntity('.$fields.')';

        $this->metadata->addAnnotation($annotation);
    }
}
