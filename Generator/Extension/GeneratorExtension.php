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
abstract class GeneratorExtension
{
    protected $metadata;
    protected $prefix;
    protected $parameters;

    /**
     * Construct
     *
     * @param ClassMetdataInfo $metadata
     * @param string           $prefix
     * @param array            $parameters
     */
    public function __construct(ClassMetadataInfo $metadata, $prefix, array $parameters = array())
    {
        $this->metadata = $metadata;
        $this->prefix = $prefix;
        $this->parameters = $parameters;
    }

    /**
     * Is Field exists
     *
     * @return mixed $field
     */
    protected function isFieldExists()
    {
        $fields = $this->metadata->getFields();

        if (
            !isset($this->parameters['column']) ||
            !isset($fields[$this->parameters['column']])
        ) {
            return false;
        }

        return $fields[$this->parameters['column']];
    }

    /**
     * Is association exists
     *
     * @return mixed $association
     */
    protected function isAssociationExists()
    {
        $associations = $this->metadata->getAssociations();

        if (
            !isset($this->parameters['sourceEntity']) ||
            !isset($associations[$this->parameters['sourceEntity']])
        ) {
            return false;
        }

        return $associations[$this->parameters['sourceEntity']];
    }

    /**
     * Generate field
     *
     * @param string $name
     * @param array  $annotations
     *
     * @return string $field
     */
    protected function generateField($name, array $annotations)
    {
        return implode("\n", array(
            $this->generateAnnotation($annotations, '<spaces>'),
            '<spaces>protected $'.$name.';',
            ''
        ));
    }

    /**
     * Generate annotation
     *
     * @param array  $annotations
     * @param string $spaces
     * @param string $first
     *
     * @return string $annotation
     */
    protected function generateAnnotation(array $annotations, $spaces = '', $first = '/**')
    {
        $code[] = $spaces.$first;
        foreach ($annotations as $annotation) {
            $code[] = $spaces.' * '.$annotation;
        }
        $code[] = $spaces.' */';

        return implode("\n", $code);
    }

    /**
     * Generate method
     *
     * @param string $name
     * @param array  $annotations
     * @param array  $parameters
     * @param array  $contents
     *
     * @return string $method
     */
    protected function generateMethod($name, array $annotations, array $parameters, array $contents)
    {
        $code = array(
            $this->generateAnnotation($annotations, '<spaces>'),
            '<spaces>public function '.$name.'('.implode(', ', $parameters).')',
            '<spaces>{'
        );

        foreach ($contents as $content) {
            $code[] = $content?'<spaces><spaces>'.$content:'';
        }

        $code[] = '<spaces>}';
        $code[] = '';

        return implode("\n", $code);
    }

    /**
     * Generate all methods to field
     *
     * @param array $methods
     * @param array $parameters
     *
     * @return array $code
     */
    protected function generateMethodFields(array $methods, array $parameters)
    {
        $name = $parameters['name'];

        $code = array();

        foreach ($methods as $method) {
            $return = '';
            $annotation = '';
            $params = array();

            if ($method == 'get') {
                $return = 'return $this->'.$name.';';
                $annotation = '@return '.$parameters['type_int'].' $'.$name;
            } else {
                $return = '$this->'.$name.' = $'.$name.';';
                if ($method == 'add') {
                    $return = '$this->'.$name.'->add($'.$name.');';
                }
                $annotation = '@param '.$parameters['target'].' $'.$name;
                $params = array(($parameters['type']?$parameters['type'].' ':'').'$'.$name);
            }

            $code[] = $this->generateMethod(
                $method.ucfirst($name),
                array($method.' '.$name, '', $annotation),
                $params,
                array($return)
            );
        }

        return $code;
    }
}
