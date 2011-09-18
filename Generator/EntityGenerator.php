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
        $code[] = $this->generateClassFields($metadata);
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
            $name = substr($mapping, strrpos($mapping, '\\')+1);

            $code[] = 'use '.$mapping.(($name != $prefix)?' as '.$prefix:'').';';
        }

        return implode("\n", $code);
    }

    protected function generateClassAnnotations(ClassMetadataInfo $metadata)
    {
        $code[] = '/**';
        $code[] = ' * '.$metadata->getNamespace().'\\'.$metadata->getName();
        $code[] = ' *';

        if ($metadata->isMappedSuperclass()) {
            $code[] = ' * @'.$this->prefix.'\MappedSuperclass()';
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
        $abstract = $metadata->isAbstract()?'abstract ':'';
        $parent = $metadata->getParent()?' extends '.$metadata->getParent()->getName():'';

        return $abstract.'class '.$metadata->getName().$parent;
    }

    protected function generateClassFields(ClassMetadataInfo $metadata)
    {
        $spaces = $this->getSpaces();
        $code = array();
        foreach ($metadata->getFields() as $field) {
            $params = array();
            foreach ($field as $attr => $value) {
                if (!in_array($attr, array('id', 'fieldName', 'default'))) {
                    $params[] = (($attr == 'columnName')?'name':$attr).'="'.$value.'"';
                }
            }

            $code[] = $spaces.'/**';
            $code[] = $spaces.' * @var '.$field['type'].' $'.$field['fieldName'];
            $code[] = $spaces.' *';
            $code[] = $spaces.' * @'.$this->prefix.'\Column('.implode(', ', $params).')';

            if (isset($field['id']) && $field['id']) {
                $code[] = $spaces.' * @'.$this->prefix.'\Id';
                $code[] = $spaces.' * @'.$this->prefix.'\GeneratedValue(strategy="AUTO")';
            }

            $code[] = $spaces.' */';
            $code[] = $spaces.'protected $'.$field['fieldName'].';';
            $code[] = '';
        }

        return implode("\n", $code);
    }
}
