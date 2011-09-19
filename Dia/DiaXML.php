<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\Dia;

use Symfony\Component\CssSelector\CssSelector;

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class DiaXML extends \SimpleXMLElement
{
    /**
     * Get packages to xml
     *
     * @return \SimpleXMLElement $packages
     */
    public function getPackages()
    {
        return $this->xpath($this->toXPath('object', 'UML - LargePackage', ''));
    }

    /**
     * Get classes to xml
     *
     * @return \SimpleXMLElement $classes
     */
    public function getClasses()
    {
        return $this->xpath($this->toXPath('object', 'UML - Class', ''));
    }

    /**
     * Get parent class
     *
     * @return \SimpleXMLElement $parent
     */
    public function getGeneralization()
    {
        return $this->xpath($this->toXPath('object', 'UML - Generalization', ''));
    }

    /**
     * Get associations to class
     *
     * @return \SimpleXMLElement $associations
     */
    public function getAssociations()
    {
        return $this->xpath($this->toXPath('object', 'UML - Association', ''));
    }

    /**
     * Get fileds to class
     *
     * @return \SimpleXMLElement $attributes
     */
    public function getAttributes()
    {
        return $this->xpath($this->toXPath('composite', 'umlattribute'));
    }

    /**
     * Get opperations to class
     *
     * @return \SimpleXMLElement $operations
     */
    public function getOperations()
    {
        return $this->xpath($this->toXPath('composite', 'umloperation'));
    }

    /**
     * Get parameters
     *
     * @return \SimpleXMLElement $parameters
     */
    public function getParameters()
    {
        return $this->xpath($this->toXPath('composite', 'umlparameter'));
    }

    /**
     * Get id
     *
     * @return string id attribute
     */
    public function getId()
    {
        return (string) $this[0]->attributes()->id;
    }

    /**
     * Get name
     *
     * @return string\null $name
     */
    public function getName()
    {
        $element = $this->xpath($this->toXPath('string', 'name'));

        return $element?str_replace('#', '', (string) $element[0]):null;
    }

    /**
     * Get type
     *
     * @return string\null $type
     */
    public function getType()
    {
        $element = $this->xpath($this->toXPath('string', 'type'));

        return $element?str_replace('#', '', (string) $element[0]):null;
    }

    /**
     * Get association type
     *
     * @return integer\null attribute association type
     */
    public function getAssocType()
    {
        $element = $this->xpath($this->toXPath('enum', 'assoc_type'));

        return $element?(int) $element[0]->attributes()->val:null;
    }

    /**
     * Get value
     *
     * @return string\null $value
     */
    public function getValue()
    {
        $element = $this->xpath($this->toXPath('string', 'value'));

        return $element?str_replace('#', '', (string) $element[0]):null;
    }

    /**
     * Get position
     *
     * @return array\null position attribute
     */
    public function getPosition()
    {
        $element = $this->xpath($this->toXPath('rectangle', 'obj_bb'));

        return $element?preg_split('/[,]|[;]/', $element[0]->attributes()->val):null;
    }

    /**
     * Get abstract
     *
     * @return boolean\null abstract attribute
     */
    public function isAbstract()
    {
        $element = $this->xpath($this->toXPath('boolean', 'abstract'));

        return $element?(($element[0]->attributes()->val == 'true')?true:false):null;
    }

    /**
     * Get direction
     *
     * @return int\null direction attribute
     */
    public function getDirection()
    {
        $element = $this->xpath($this->toXPath('enum', 'direction'));

        return $element?(int) $element[0]->attributes()->val:null;
    }

    /**
     * get name package
     *
     * @param \SimpleXMLElement $element
     *
     * @return string\null package name
     */
    public function getNamePackage(\SimpleXMLElement $element)
    {
        $cPosition = $element->getPosition();
        foreach($this->getPackages() as $element) {
            $pPosition = $element->getPosition();
            if (
                (double) $pPosition[0] < (double) $cPosition[0] &&
                (double) $pPosition[1] < (double) $cPosition[1] &&
                (double) $pPosition[2] > (double) $cPosition[2] &&
                (double) $pPosition[3] > (double) $cPosition[3]
            ) {
                return $element->getName();
            }
        }

        return null;
    }

    /**
     * Get connection
     *
     * @param array  $classes
     * @param string $type
     *
     * return array $connection classes
     */
    public function getConnection(array $classes, $type = 'simple')
    {
        $element = $this->xpath($this->toXPath('connection'));

        if (!$element) {
            return null;
        }

        $connect = array(
            'from' => $classes[(string) $element[0]->attributes()->to],
            'to' => $classes[(string) $element[1]->attributes()->to]
        );

        if ($type == 'association') {
            if ($this->getDirection() == 2) {
                $temp = $connect['from'];

                $connect['from'] = $connect['to'];
                $connect['to'] = $temp;
            }
        }

        return $connect;
    }

    /**
     * toXPath element xml
     *
     * @param string $type
     * @param string $replace
     * @param string $prefix
     *
     * @return string $cssExpr
     */
    protected function toXPath($type, $replace = null, $prefix = 'descendant-or-self::')
    {
        $cssExpr = null;
        switch($type) {
            case 'object':
                $cssExpr = sprintf('dia|layer[visible="true"] > dia|object[type="%s"]', $replace);
                break;
            case 'composite':
                $cssExpr = sprintf('dia|composite[type="%s"]', $replace);
                break;
            case 'connection':
                $cssExpr = 'dia|connections > dia|connection';
                break;
            case 'boolean':
            case 'string':
            case 'rectangle':
            case 'enum':
                $cssExpr = sprintf('dia|attribute[name="%s"] > dia|%s', $replace, $type);
        }

        return ($cssExpr)?CssSelector::toXPath($cssExpr, $prefix):null;
    }
}
