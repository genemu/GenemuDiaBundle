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
    protected $spaces;
    protected $regenerate;

    /**
     * Construct
     * intiliaze extension, prefix and spaces
     */
    public function __construct()
    {
        $this->extension = 'php';
        $this->spaces = 4;
        $this->regenerate = true;
    }

    /**
     * Set regenerate
     *
     * @param boolean $regenerate
     */
    public function setRegenerate($regenerate)
    {
        $this->regenerate = $regenerate;
    }

    /**
     * Set spaces
     *
     * @param int $spaces
     */
    public function setSpaces($spaces)
    {
        $this->spaces = $spaces;
    }

    /**
     * Get spaces
     *
     * @param int $nb
     *
     * @return string $spaces
     */
    public function getSpaces($nb = 1)
    {
        return str_repeat(str_repeat(' ', $this->spaces), $nb);
    }

    /**
     * Generate file
     *
     * @param string $name
     * @param string $path
     * @param string $contents
     */
    protected function generate($name, $path, $contents)
    {
        $file = $path.'/'.$name.'.'.$this->extension;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_file($file) || $this->regenerate) {
            file_put_contents($file, $contents);
        }
    }
}
