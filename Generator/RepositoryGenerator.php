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
class RepositoryGenerator extends Generator
{
    /**
     * Generate file Entity
     *
     * @param ClassMetadataInfo $metadata
     */
    public function generateRepository(ClassMetadataInfo $metadata)
    {
        foreach ($metadata->getExtensions() as $name => $generators) {
            foreach ($generators as $generator) {
                if (method_exists($generator, 'init'.$name)) {
                    $generator->{'init'.$name}();
                }
            }
        }

        $path = $metadata->getRepositoryPath();
        $name = $metadata->getName();

        $this->generate($name, $path, $this->generateRepositoryClass($metadata));
    }

    /**
     * Generate content file
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string $content
     */
    protected function generateRepositoryClass(ClassMetadataInfo $metadata)
    {
        $code[] = '<?php';
        $code[] = '';
        $code[] = '/*';
        $code[] = ' * This file is generate by DiaBundle for symfony package';
        $code[] = ' *';
        $code[] = ' * (c) Olivier Chauvel <olchauvel@gmail.com>';
        $code[] = ' *';
        $code[] = ' * For the full copyright and license information, please view the LICENSE';
        $code[] = ' * file that was distributed with this source code.';
        $code[] = ' */';
        $code[] = '';
        $code[] = $metadata->getCodeRepositoryNamespace();
        $code[] = '';
        $code[] = $metadata->getCodeRepositoryUse();
        $code[] = '';
        $code[] = $metadata->getCodeRepositoryClass();
        $code[] = '{';
        $code[] = '';
        $code[] = '}';

        return implode("\n", $code);
    }
}
