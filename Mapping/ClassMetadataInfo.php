<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Mapping;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class ClassMetadataInfo
{
    protected $isAbstract;
    protected $name;
    protected $path;
    protected $namespace;

    public function __construct($name, $path, $abstract)
    {
        $this->isAbstract = $abstract;
        $this->name = $name;
        $this->path = $path;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function isAbstract()
    {
        return $this->isAbstract;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getPath()
    {
        return $this->path;
    }
}

