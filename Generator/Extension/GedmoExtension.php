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
     * Generate annotation Tree to Entty
     */
    public function generateTreeClassAnnotations()
    {
        return '@'.$this->prefix.'\Tree(type="nested")';
    }
}
