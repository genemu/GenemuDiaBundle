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
        $code[] = $this->generateClassAssociationFields($metadata);
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

        if (!$metadata->isMappedSuperclass()) {
            $table = $metadata->getTableName();
            $repository = $metadata->getNamespace().'\\Repository\\'.$metadata->getName();

            $code[] = ' * @'.$this->prefix.'\Table('.($table?'name="'.$table.'"':'').')';
            $code[] = ' * @'.$this->prefix.'\Entity('.($repository?'repositoryClass="'.$repository.'"':'').')';
        }

        if ($extension = $this->generateExtension($metadata, 'ClassAnnotations')) {
            $code[] = $extension;
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

            $annotations = array(
                '@var '.$field['type'].' $'.$field['fieldName'],
                '',
                '@'.$this->prefix.'\Column('.implode(', ', $params).')'
            );

            if (isset($field['id']) && $field['id']) {
                $annotations[] = '@'.$this->prefix.'\Id';
                $annotations[] = '@'.$this->prefix.'\GeneratedValue(strategy="AUTO")';
            }

            $code[] = $this->generateField($field['fieldName'], $annotations);
        }

        return implode("\n", $code);
    }

    protected function generateClassAssociationFields(ClassMetadataInfo $metadata)
    {
        $code = array();
        foreach ($metadata->getAssociations() as $association) {

            $attributes = array();
            foreach ($association as $attr => $value) {
                if (in_array($attr, array('targetEntity', 'mappedBy', 'inversedBy'))) {
                    $attributes[] = '<spaces>'.$attr.'="'.$value.'",';
                }
            }

            $last = $attributes[count($attributes)-1];
            $attributes[count($attributes)-1] = substr($last, 0, -1);

            $annotations = array_merge(array(
                '@var '.$association['targetEntity'].' $'.$association['fieldName'],
                '',
                '@'.$this->prefix.'\\'.$association['type'].'('
            ), $attributes);
            $annotations[] = ')';

            $code[] = $this->generateField($association['fieldName'], $annotations);
        }

        return implode("\n", $code);
    }

    protected function generateField($name, array $annotations)
    {
        $spaces = $this->getSpaces();

        $code[] = $spaces.'/**';

        foreach ($annotations as $annotation) {
            $code[] = $spaces.' * '.$annotation;
        }

        $code[] = $spaces.' */';
        $code[] = $spaces.'protected $'.$name.';';
        $code[] = '';

        return implode("\n", str_replace('<spaces>', $spaces, $code));
    }

    protected function generateExtension(ClassMetadataInfo $metadata, $type)
    {
        $code = array();
        foreach ($metadata->getExtensions() as $name => $generator) {
            if (method_exists($generator, 'generate'.$name.$type)) {
                $code[] = $generator->{'generate'.$name.$type}();
            }
        }

        return implode("\n", str_replace('<spaces>', $this->getSpaces(), $code));
    }
}
