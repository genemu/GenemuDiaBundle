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

        if ($fields = $this->generateClassFields($metadata)) {
            $code[] = $fields;
        }

        if ($associations = $this->generateClassAssociationFields($metadata)) {
            $code[] = $associations;
        }

        if ($extension = $this->generateExtension($metadata, 'Fields')) {
            $code = array_merge($code, $extension);
        }

        if ($construct = $this->generateClassConstruct($metadata)) {
            $code[] = $construct;
        }

        if ($methods = $this->generateClassMethodFields($metadata)) {
            $code[] = $methods;
        }

        if ($extension = $this->generateExtension($metadata, 'Methods')) {
            $code = array_merge($code, $extension);
        }

        $code[] = '}';

        return implode("\n", str_replace('<spaces>', $this->getSpaces(), $code));
    }

    /**
     * Generate namepsace to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $namespace
     */
    protected function generateNamespace(ClassMetadataInfo $metadata)
    {
        return 'namespace '.$metadata->getNamespace().';';
    }

    /**
     * Generate uses to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $uses
     */
    protected function generateUses(ClassMetadataInfo $metadata)
    {
        $code = array();
        foreach ($metadata->getUses() as $prefix => $mapping) {
            $name = substr($mapping, strrpos($mapping, '\\')+1);

            $code[] = 'use '.$mapping.(($name != $prefix)?' as '.$prefix:'').';';
        }

        return implode("\n", $code);
    }

    /**
     * Generate annotations class to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $annotations
     */
    protected function generateClassAnnotations(ClassMetadataInfo $metadata)
    {
        $annotations = array(
            $metadata->getNamespace().'\\'.$metadata->getName(),
            ''
        );

        if (!$metadata->isMappedSuperclass()) {
            $repository = $metadata->getNamespace().'\\Repository\\'.$metadata->getName();

            $annotations[] = '@'.$this->prefix.'\table(';
            $annotations[] = '<spaces>name="'.$metadata->getTableName().'",';

            if ($extension = $this->generateExtension($metadata, 'ClassTable')) {
                foreach ($extension as $annotation) {
                    $annotations[] = '<spaces>'.$annotation.',';
                }
            }
            $annotations[count($annotations)-1] = substr(end($annotations), 0, -1);

            $annotations[] = ')';
            $annotations[] = '@'.$this->prefix.'\Entity(';
            $annotations[] = '<spaces>repositoryClass="'.$repository.'"';
            $annotations[] = ')';
        }

        if ($extension = $this->generateExtension($metadata, 'ClassAnnotations')) {
            $annotations = array_merge($annotations, $extension);
        }

        return $this->generateAnnotation($annotations);
    }

    /**
     * Generate class name to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $className
     */
    protected function generateClassName(ClassMetadataInfo $metadata)
    {
        $abstract = $metadata->isAbstract()?'abstract ':'';
        $parent = $metadata->getParent()?' extends '.$metadata->getParent()->getName():'';

        return $abstract.'class '.$metadata->getName().$parent;
    }

    /**
     * Generate fields to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $fields
     */
    protected function generateClassFields(ClassMetadataInfo $metadata)
    {
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

    /**
     * Generate associations fields to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $associationFields
     */
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

            $annotations = array_merge(array(
                '@var '.$association['targetEntity'].' $'.$association['fieldName'],
                '',
                '@'.$this->prefix.'\\'.$association['type'].'('
            ), $attributes);

            if ($extension = $this->generateExtension($metadata, 'AssociationFields', $association)) {
                $annotations = array_merge($annotations, $extension);
            }
            $annotations[count($annotations)-1] = substr(end($annotations), 0, -1);

            $annotations[] = ')';

            $code[] = $this->generateField($association['fieldName'], $annotations);
        }

        return implode("\n", $code);
    }

    /**
     * Generate method __construct to Entity
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $methodConstruct
     */
    protected function generateClassConstruct(ClassMetadataInfo $metadata)
    {
        $code = array();

        foreach ($metadata->getFields() as $field) {
            if (isset($field['default']) && $field['default']) {
                $code[] = '$this->'.$field['fieldName'].' = '.$field['default'].';';
            }
        }

        foreach ($metadata->getAssociations() as $association) {
            if (in_array($association['type'], array('ManyToMany', 'OneToMany'))) {
                $code[] = '$this->'.$association['fieldName'].' = new ArrayCollection();';
            }
        }

        if ($extension = $this->generateExtension($metadata, 'Construct')) {
            $code = array_merge($code, $extension);
        }

        return ($code)?$this->generateMethod('__construct', array('Construct'), array(), $code):null;
    }

    /**
     * Generate methods fields
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $methods
     */
    protected function generateClassMethodFields(ClassMetadataInfo $metadata)
    {
        $code = array();

        foreach ($metadata->getFields() as $field) {
            $name = $field['fieldName'];
            $typeInt = (isset($field['type_int']))?$field['type_int']:$field['type'];
            $type = ($field['type'] != $typeInt)?$typeInt:'';

            if (!isset($field['id']) || !$field['id']) {
                $code[] = $this->generateMethod(
                    'set'.ucfirst($name),
                    array('set '.$name, '', '@param '.$typeInt.' $'.$name),
                    array($type.'$'.$name),
                    array('$this->'.$name.' = $'.$name.';')
                );
            }

            $code[] = $this->generateMethod(
                'get'.ucfirst($name),
                array('get '.$name, '', '@return '.$typeInt.' $'.$name),
                array(),
                array('return $this->'.$name.';')
            );
        }

        return implode("\n", $code);
    }

    /**
     * Generate extensions to Entity
     * Generate annotations class and fields
     * Generate fields and methods
     *
     * @param ClassMetadataInfo $metadata
     * @param string            $type
     * @param array/null        $field
     *
     * @return array $extensions
     */
    protected function generateExtension(ClassMetadataInfo $metadata, $type, $field = null)
    {
        $code = array();
        foreach ($metadata->getExtensions() as $name => $generators) {
            foreach ($generators as $generator) {
                $generator->setPrefixO($this->prefix);

                if (method_exists($generator, 'generate'.$name.$type)) {
                    if ($value = $generator->{'generate'.$name.$type}($field?$field:null)) {
                        if (is_array($value)) {
                            $code = array_merge($code, $value);
                        } else {
                            $code[] = $value;
                        }
                    }
                }
            }
        }

        return $code;
    }
}
