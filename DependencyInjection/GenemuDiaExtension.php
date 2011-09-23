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

/**
 * GenemuDiaExtension
 *
 * @author Olivier Chauvel <olchauvel@gmail.com>
 */
class GenemuDiaExtension extends Extension
{
    protected $namespace = 'Genemu\Bundle\DiaBundle\Generator\Extension\\';
    protected $extensions;

    public function load(array $configs, ContainerBuilder $container)
    {
        $this->extensions = array();

        $this->addORMExtension();
        $this->addMongoDBExtension();
        $this->addAssertExtension();
        $this->addDoctrineAssertExtension();
        $this->addGedmoExtension();

        if (isset($configs[0]['extensions'])) {
            $extensions = $configs[0]['extensions'];
            $extensions = array_merge($this->extensions, $extensions);
        }

        $container->setParameter('genemu_dia.extensions', $extensions);
    }

    protected function addORMExtension()
    {
        $generator = $this->namespace.'ORMExtension';
        $namespace = 'Doctrine\ORM\Mapping';
        $repository = 'Doctrine\ORM\EntityRepository';
        $types = array(
            'MappedSuperclass',
            'InheritanceType',
            'DiscriminatorColumn',
            'ChangeTrackingPolicy',
            'HasLifecycleCallbacks',
            'Index',
            'OneToMany',
            'ManyToOne',
            'ManyToMany'
        );

        $this->extensions['ORM'] = array(
            'generator' => $generator,
            'namespace' => $namespace,
            'repository' => $repository,
            'types' => $types
        );
    }

    protected function addMongoDBExtension()
    {
        $generator = $this->namespace.'MongoBDExtension';
        $namespace = 'Doctrine\ODM\MongoDB\Mapping\Annotations';
        $repository = 'Doctrine\ODM\MongoDB\DocumentRepository';
        $types = array();

        $this->extensions['MongoDB'] = array(
            'generator' => $generator,
            'namespace' => $namespace,
            'repository' => $repository,
            'types' => $types
        );
    }

    protected function addAssertExtension()
    {
        $generator = $this->namespace.'AssertExtension';
        $namespace = 'Symfony\Component\Validator\Constraint';
        $types = array(
            'NotBlank',
            'Blank',
            'NotNull',
            'Null',
            'True',
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
            'Language',
            'Locale',
            'Country',
            'File',
            'Image',
            'Callback',
            'Valid'
        );

        $this->extensions['Assert'] = array(
            'generator' => $generator,
            'namespace' => $namespace,
            'types' => $types
        );
    }

    protected function addDoctrineAssertExtension()
    {
        $generator = $this->namespace.'DoctrineAssertExtension';
        $namespace = 'Symfony\Bridge\Doctrine\Validator\Constraints';
        $types = array('UniqueEntity');

        $this->extensions['DoctrineAssert'] = array(
            'generator' => $generator,
            'namespace' => $namespace,
            'types' => $types
        );
    }

    protected function addGedmoExtension()
    {
        $generator = $this->namespace.'GedmoExtension';
        $namespace = 'Gedmo\Mapping\Annotation';
        $types = array(
            'Timestampable',
            'Sluggable',
            'Tree',
            'Translatable',
            'Loggable'
        );

        $this->extensions['Gedmo'] = array(
            'generator' => $generator,
            'namespace' => $namespace,
            'types' => $types
        );
    }
}
