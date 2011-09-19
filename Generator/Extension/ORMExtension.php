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

    public function generateMappedSuperclassClassAnnotations()
    {
        return ' * @'.$this->prefix.'\MappedSuperclass()';
    }

    public function generateInheritanceTypeClassAnnotations()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        return ' * @'.$this->prefix.'\InheritanceType("'.$this->parameters['type'].'")';
    }

    public function generateDiscriminatorColumnClassAnnotations()
    {
        $param = array_replace(array(
            'name' => 'disrc',
            'type' => 'string'
        ), $this->parameters);

        $code[] = ' * @'.$this->prefix.'\DiscriminatorColumn(name="'.$param['name'].'", type="'.$param['type'].'")';

        $map = array();
        foreach ($this->metadata->getChildren() as $index => $children) {
            $name = $children->getName();
            $namespace = $children->getNamespace().'\\'.$name;

            $map[] = ' * <spaces>"'.strtolower($name).'" = "'.$namespace.'",';
        }
        $last = $map[count($map)-1];
        $map[count($map)-1] = substr($last, 0, -1);

        if ($map) {
            $name = $this->metadata->getName();
            $namespace = $this->metadata->getNamespace().'\\'.$name;

            $code[] = ' * @'.$this->prefix.'\DiscriminatorMap({';
            $code[] = ' * <spaces>"'.strtolower($name).'" = "'.$namespace.'",';
            $code[] = implode("\n", $map);
            $code[] = ' * })';
        }

        return implode("\n", $code);
    }

    public function generateChangeTrackingPolicyClassAnnotations()
    {
        if (!isset($this->parameters['type'])) {
            return null;
        }

        return ' * @'.$this->prefix.'\ChangeTrackingPolicy("'.$this->parameters['type'].'")';
    }

    public function generateHasLifecycleCallbacksClassAnnotations()
    {
        return ' * @'.$this->prefix.'\HasLifecycleCallbacks()';
    }
}
