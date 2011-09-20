DiaBundle
==========

## Installation

### Installation using the `bin/vendor` method

If you're using the `bin/vendors` method to manage your vendor libraries, add the following entries to the deps in the root of your project file:

    [GenemuDiaBundle]
        git=http://github.com/genemu/GenemuDiaBundle.git
        target=bundles/Genemu/Bundle/DiaBundle

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
            new Genemu\Bundle\DiaBundle\GenemuDiaBundle(),
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
