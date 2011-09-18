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

/**
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class Generator
{
    protected $extension;

    public function __construct()
    {
        $this->extension = 'php';
    }

    protected function generate($name, $path, $contents)
    {
        $file = $path.'/'.$name.'.'.$this->extension;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($file, $contents);
    }

    protected function generateAnnotations()
    {
        $code[] = '/*';
        $code[] = ' * This file is generate by DiaBundle for smyfony package';
        $code[] = ' *';
        $code[] = ' * (c) Olivier Chauvel <olchauvel@gmail.com>';
        $code[] = ' * ';
        $code[] = ' * For the full copyright and license information, please view the LICENSE';
        $code[] = ' * file that was distributed with this source code.';
        $code[] = ' */';

        return implode("\n", $code);
    }
}