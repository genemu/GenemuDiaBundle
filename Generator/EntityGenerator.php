<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Generator;

use Genemu\Bundle\DiaBundle\Mapping\ClassMetadataInfo;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class EntityGenerator extends Generator
{
    /**
     * Generate file Entity
     *
     * @param ClassMetadataInfo $metadata
     */
    public function generateEntity(ClassMetadataInfo $metadata)
    {
        $path = $metadata->getPath();
        $name = $metadata->getName();

        $this->generate($name, $path, $this->generateEntityClass($metadata));
    }

    /**
     * Generate content file
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $content
     */
    protected function generateEntityClass(ClassMetadataInfo $metadata)
    {
        $abstract = $metadata->isAbstract()?'abstract ':'';
        $parent = $metadata->getParent()?' extends '.$metadata->getParent()->getName():'';

        $code[] = '<?php';
        $code[] = '';
        $code[] = '/*';
        $code[] = ' * This file is generate by DiaBundle for symfony package';
        $code[] = ' *';
        $code[] = ' * (c) Olivier Chauvel <olchauvel@gmail.com>';
        $code[] = ' *';
        $code[] = ' * For the full copyright and license information, please view the LICENSE';
        $code[] = ' * file that was distributed with this source code.';
        $code[] = ' */';
        $code[] = '';
        $code[] = 'namespace '.$metadata->getNamespace().';';
        $code[] = '';

        foreach ($metadata->getUses() as $prefix => $mapping) {
            $name = substr($mapping, strrpos($mapping, '\\')+1);

            $code[] = 'use '.$mapping.(($name != $prefix)?' as '.$prefix:'').';';
        }

        $code[] = '';

        foreach ($metadata->getExtension('Annotations') as $generator) {
            if (method_exists($generator, 'generateAnnotations')) {
                $code[] = $generator->{'generateAnnotations'}();
            }
        }

        $code[] = $abstract.'class '.$metadata->getName().$parent;
        $code[] = '{';

        foreach ($metadata->getExtension('Fields') as $generator) {
            if (method_exists($generator, 'generateFields')) {
                $code = array_merge($code, $generator->{'generateFields'}());
            }
        }

        foreach ($metadata->getExtension('Methods') as $generator) {
            if (method_exists($generator, 'generateMethods')) {
                $code = array_merge($code, $generator->{'generateMethods'}());
            }
        }

        $code[] = '}';

        return implode("\n", str_replace('<spaces>', $this->getSpaces(), $code));
    }
}
