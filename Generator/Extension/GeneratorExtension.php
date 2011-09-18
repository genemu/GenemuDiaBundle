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
class GeneratorExtension
{
    protected $metadata;
    protected $prefix;
    protected $parameters;

    public function __construct(ClassMetadataInfo $metadata, $prefix, array $parameters)
    {
        $this->metadata = $metadata;
        $this->prefix = $prefix;
        $this->parameters = $parameters;
    }
}
