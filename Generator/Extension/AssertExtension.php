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
class AssertExtension extends GeneratorExtension
{
    /**
     * Initialization NotBlank
     */
    public function initNotBlank()
    {
        $this->updateAnnotation('NotBlank', array('message'));
    }

    /**
     * Initialization Blank
     */
    public function initBlank()
    {
        $this->updateAnnotation('Blank', array('message'));
    }

    /**
     * Initialization NotNull
     */
    public function initNotNull()
    {
        $this->updateAnnotation('NotNull', array('message'));
    }

    /**
     * Initialization Null
     */
    public function initNull()
    {
        $this->updateAnnotation('Null', array('message'));
    }

    /**
     * Initialization True
     */
    public function initTrue()
    {
        $this->updateAnnotation('True', array('message'));
    }

    /**
     * Initialization False
     */
    public function initFalse()
    {
        $this->updateAnnotation('False', array('message'));
    }

    /**
     * Initialization Type
     */
    public function initType()
    {
        $this->updateAnnotation('Type', array('message', 'type'));
    }

    /**
     * Initialization Email
     */
    public function initEmail()
    {
        $this->updateAnnotation('Email', array('message', 'checkMX'));
    }

    /**
     * Initialization MinLength
     */
    public function initMinLength()
    {
        $this->updateAnnotation('MinLength', array(
            'message',
            'limit',
            'charset'
        ));
    }

    /**
     * Initialization MaxLength
     */
    public function initMaxLength()
    {
        $this->updateAnnotation('MaxLength', array(
            'message',
            'limit',
            'charset'
        ));
    }

    /**
     * Initialization Url
     */
    public function initUrl()
    {
        $this->updateAnnotation('Url', array('message', 'protocols'));
    }

    /**
     * Initialization Regex
     */
    public function initRegex()
    {
        $this->updateAnnotation('Regex', array('pattern', 'match', 'message'));
    }

    /**
     * Initialization Ip
     */
    public function initIp()
    {
        $this->updateAnnotation('Ip', array('version', 'message'));
    }

    /**
     * Initialization Max
     */
    public function initMax()
    {
        $this->updateAnnotation('Max', array(
            'limit',
            'message',
            'invalidMessage'
        ));
    }

    /**
     * Initialization Min
     */
    public function initMin()
    {
        $this->updateAnnotation('Min', array(
            'limit',
            'message',
            'invalidMessage'
        ));
    }

    /**
     * Initialization Date
     */
    public function initDate()
    {
        $this->updateAnnotation('Date', array('message'));
    }

    /**
     * Initialization DateTime
     */
    public function initDateTime()
    {
        $this->updateAnnotation('DateTime', array('message'));
    }

    /**
     * Initialization Time
     */
    public function initTime()
    {
        $this->updateAnnotation('Time', array('message'));
    }

    /**
     * Initialization Choice
     */
    public function initChoice()
    {
        $this->updateAnnotation('Choice', array(
            'choices',
            'callback',
            'multiple',
            'min',
            'max',
            'message',
            'multipleMessage',
            'minMessage',
            'maxMessage',
            'strict'
        ));
    }

    /**
     * Initialization Language
     */
    public function initLanguage()
    {
        $this->updateAnnotation('Language', array('message'));
    }

    /**
     * Initialization Locale
     */
    public function initLocale()
    {
        $this->updateAnnotation('Locale', array('message'));
    }

    /**
     * Initialization Country
     */
    public function initCountry()
    {
        $this->updateAnnotation('Country', array('message'));
    }

    /**
     * Initialization File
     */
    public function initFile()
    {
        $this->updateAnnotation('File', array(
            'maxSize',
            'mimeTypes',
            'maxSizeMessage',
            'mimeTypesMessage',
            'notFoundMessage',
            'notReadableMessage',
            'uploadIniSizeErrorMessage',
            'uploadFormSizeErrorMessage',
            'uploadErrorMessage'
        ));
    }

    /**
     * Initialization Image
     */
    public function initImage()
    {
        $this->updateAnnotation('Image', array(
            'mimeTypes',
            'mimeTypesMessage'
        ));
    }

    /**
     * Initialization Callback
     */
    public function initCallback()
    {
        $this->updateAnnotation('Callback', array('methods'));
    }

    /**
     * Initialization Valid
     */
    public function initValid()
    {
        $this->updateAnnotation('Valid', array('traverse'));
    }

    /**
     * Update annotation if field or association exist
     *
     * @param string $type
     * @param array  $parameters
     */
    protected function updateAnnotation($type, array $parameters)
    {
        if (
            !$field = $this->isFieldExists() ||
            $field = $this->isAssociationExists()
        ) {
            return null;
        }
        $parameters = array_flip($parameters);
        $parameters = array_intersect_key($this->parameters, $parameters);

        if (!$parameters) {
            $annotations = array('@'.$this->prefix.'\\'.$type.'()');
        } else {
            $attributes = array();
            foreach ($parameters as $attr => $value) {
                $attributes[] = '<spaces>'.$attr.'="'.$value.'",';
            }
            $attributes[count($attributes)-1] = substr(end($attributes), 0, -1);

            $annotations = array('@'.$this->prefix.'\\'.$type.'(');
            $annotations = array_merge($annotations, $attributes);
            $annotations[] = ')';
        }

        $this->metadata->updateField(
            $field['fieldName'],
            array('annotations' => $annotations)
        );
    }
}
