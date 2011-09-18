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
    public function generateEntity(ClassMetadataInfo $metadata)
    {
        $path = $metadata->getPath();
        $name = $metadata->getName();

        $this->generate($name, $path, $this->generateEntityClass($metadata));
    }

    protected function generateEntityClass(ClassMetadataInfo $metadata)
    {
        $code[] = '<?php';
        $code[] = '';
        $code[] = $this->generateAnnotations();
        $code[] = '';
        $code[] = $this->generateNamespace($metadata);
        $code[] = '';
        $code[] = $this->generateUses($metadata);
        $code[] = '';
        $code[] = $this->generateClassAnnotations($metadata);
        $code[] = $this->generateClassName($metadata);
        $code[] = '{';
        $code[] = '}';

        return implode("\n", $code);
    }

    protected function generateNamespace(ClassMetadataInfo $metadata)
    {
        return 'namespace '.$metadata->getNamespace().';';
    }

    protected function generateUses(ClassMetadataInfo $metadata)
    {
        $code = array();
        foreach ($metadata->getUses() as $prefix => $mapping) {
            $code[] = 'use '.$mapping.' as '.$prefix.';';
        }

        return implode("\n", $code);
    }

    protected function generateClassAnnotations(ClassMetadataInfo $metadata)
    {
        $code[] = '/**';
        $code[] = ' * '.$metadata->getNamespace().'\\'.$metadata->getName();
        $code[] = ' *';

        if ($metadata->isMappedSupperClass()) {
            $code[] = ' * @'.$this->prefix.'\MappedSupperClass()';
        } else {
            $table = $metadata->getTableName();
            $repository = $metadata->getNamespace().'\\Repository\\'.$metadata->getName();

            $code[] = ' * @'.$this->prefix.'\Table('.($table?'name="'.$table.'"':'').')';
            $code[] = ' * @'.$this->prefix.'\Entity('.($repository?'repositoryClass="'.$repository.'"':'').')';
        }

        $code[] = ' */';

        return implode("\n", $code);
    }

    protected function generateClassName(ClassMetadataInfo $metadata)
    {
        $abstract = ($metadata->isAbstract())?'abstract ':'';

        return $abstract.'class '.$metadata->getName();
    }
}
