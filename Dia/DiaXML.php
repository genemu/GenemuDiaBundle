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
    public function getClasses()
    {
        return $this->xpath($this->toXPath('object', 'UML - Class'));
    }

    public function getId()
    {
        return (string) $this[0]->attributes()->id;
    }

    public function getName()
    {
        $element = $this->xpath($this->toXPath('string', 'name', ''));

        return $element?str_replace('#', '', (string) $element[0]):null;
    }

    public function getPosition()
    {
        $element = $this->xpath($this->toXPath('rectangle', 'obj_bb', ''));

        return $element?preg_split('/[,]|[;]/', $element[0]->attributes()->val):null;
    }

    public function isAbstract()
    {
        $element = $this->xpath($this->toXPath('boolean', 'abstract', ''));

        return $element?(($element[0]->attributes()->val == 'true')?true:false):null;
    }

    public function getNamePackage(\SimpleXMLElement $element)
    {
        $cPosition = $element->getPosition();
        foreach($this->xpath($this->toXPath('object', 'UML - LargePackage')) as $element) {
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
                $cssExpr = sprintf('dia|attribute[name="%s"] > dia|%s', $replace, $type);
        }

        return ($cssExpr)?CssSelector::toXPath($cssExpr, $prefix):null;
    }
}
