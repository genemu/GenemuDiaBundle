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
    protected $prefixO;
    protected $parameters;

    /**
     * Construct
     *
     * @param ClassMetdataInfo $metadata
     * @param string           $prefix
     * @param array            $parameters
     */
    public function __construct(ClassMetadataInfo $metadata, $prefix, array $parameters)
    {
        $this->metadata = $metadata;
        $this->prefix = $prefix;
        $this->parameters = $parameters;
    }

    public function setPrefixO($prefixO)
    {
        $this->prefixO = $prefixO;
    }

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
}
