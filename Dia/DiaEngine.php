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
class DiaEngine
{
    protected $xml;
    protected $classes;

    public function __construct($file)
    {
        $zd = gzopen($file, 'r');
        $contents = gzread($zd, 512000);
        gzclose($zd);

        $this->xml = new DiaXML($contents);
    }

    public function getClasses()
    {
        if ($this->classes) {
            return $this->classes;
        }

        foreach ($this->xml->getClasses() as $class) {
            $name = $class->getName();
            $isAbstract = $class->isAbstract();
            $package = $this->xml->getPackage($class);
        }
    }
}
