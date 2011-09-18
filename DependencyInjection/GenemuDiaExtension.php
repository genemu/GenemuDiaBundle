<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olchauvel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\DiaBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class GenemuDiaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        if (isset($configs[0]['extensions'])) {
            $container->setParameter('genemu_dia.extensions', $configs[0]['extensions']);
        }
    }
}
