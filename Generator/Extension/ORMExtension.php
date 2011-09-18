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
    public function initMappedSuperClass()
    {
        $this->metadata->setMappedSuperClass(true);
    }
}
