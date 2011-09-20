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

class GenemuDiaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $extensions = array(
            'ORM' => array(
                'generator' => 'Genemu\Bundle\DiaBundle\Generator\Extension\ORMExtension',
                'namespace' => 'Doctrine\ORM\Mapping',
                'types' => array(
                    'MappedSuperclass',
                    'InheritanceType',
                    'DiscriminatorColumn',
                    'ChangeTrackingPolicy',
                    'HasLifecycleCallbacks',
                    'Index',
                    'OneToMany',
                    'ManyToOne',
                    'ManyToMany'
                )
            ),
            'Assert' => array(
                'generator' => 'Genemu\Bundle\DiaBundle\Generator\Extension\AssertExtension',
                'namespace' => 'Symfony\Component\Validator\Constraints',
                'types' => array(
                    'NotBlank',
                    'Blank',
                    'NotNull',
                    'Null',
                    'Tree',
                    'False',
                    'Type',
                    'Email',
                    'MinLength',
                    'MaxLength',
                    'Url',
                    'Regex',
                    'Ip',
                    'Max',
                    'Min',
                    'Date',
                    'Time',
                    'DateTime',
                    'Choice',
                    'Collection',
                    'Language',
                    'Locale',
                    'Country',
                    'File',
                    'Callback',
                    'Valid',
                    'All'
                )
            ),
            'DoctrineAssert' => array(
                'generator' => 'Genemu\Bundle\DiaBundle\Generator\Extension\DoctrineAssertExtension',
                'namespace' => 'Symfony\Bridge\Doctrine\Validator\Constraints',
                'types' => array(
                    'UniqueEntity'
                )
            ),
            'Gedmo' => array(
                'generator' => 'Genemu\Bundle\DiaBundle\Generator\Extension\GedmoExtension',
                'namespace' => 'Gedmo\Mapping\Annotation',
                'types' => array(
                    'Timestampable',
                    'Sluggable',
                    'Tree',
                    'Translatable',
                    'Loggable'
                )
            )
        );

        if (isset($configs[0]['extensions'])) {
            $extensions = array_merge($extensions, $configs[0]['extensions']);
        }

        $container->setParameter('genemu_dia.extensions', $extensions);
    }
}
