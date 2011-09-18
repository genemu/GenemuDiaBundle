DiaBundle
==========

## Installation

### Get the bundle

To install the bundle, place it in the `vendor/bundles/Genemu/Bundle` directory of your project
(so that it lives at `vendor/bundles/Genemu/Bundle/DiaBundle`). You can do this by adding
the bundle as a submodule, cloning it, or simply downloading the source.

    git submodule add https://github.com/genemu/GenemuDiaBundle.git vendor/bundles/Genemu/Bundle/DiaBundle

### Add the namespace to your autoloader

If it is the first Genemu bundle you install in your Symfony 2 project, you
need to add the `Genemu` namespace to your autoloader:

    // app/autoload.php
    $loader->registerNamespaces(array(
        'Genemu'                         => __DIR__.'/../vendor/bundles'
        // ...
    ));

### Initialize the bundle

To start using the bundle, initialize the bundle in your Kernel. This
file is usually located at `app/AppKernel`:

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Genemu\Bundle\FormBundle\GenemuDiaBundle(),
        );
    )

### Uses the bundle

custom your generate Entity and add your extension generator

    genemu_dia:
        MyExtension:
            generator: MyNamespace\Generator\Extension\MyExtensionGenerator
            namespace: MyNamespace\Mapping
            types:
                - 'Type1'
                - 'Type2'
                - ...

generate your Schema to Entity

    ./app/console dia:entity:create app/schema.dia
