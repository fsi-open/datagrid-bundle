<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class FSIDataGridExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('datagrid.xml');

        if (isset($config['yaml_configuration']) && $config['yaml_configuration']) {
            $loader->load('datagrid_yaml_configuration.xml');
        }

        if (isset($config['twig']['enabled']) && $config['twig']['enabled']) {
            $this->registerTwigConfiguration($config['twig'], $container, $loader);
        }

        $container
            ->getDefinition('datagrid.subscriber.configuration.configuration_builder')
            ->replaceArgument(0, $config['datagrid']);
    }

    public function registerTwigConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('twig.xml');
        $container->setParameter('datagrid.twig.themes', $config['themes']);
    }

    /**
     * Append bundles datagrid and datasource configurations to the Showcase bundle configuration
     *
     * @param ContainerBuilder $container Container Builder
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $yamlParser = new Parser();

        foreach ($container->getParameter('kernel.bundles') as $bundleClass) {

            $targetDirectory = dirname((new \ReflectionClass($bundleClass))->getFileName())
                . '/Resources/config/datagrid';
            if (!(file_exists($targetDirectory) && is_dir($targetDirectory))) {
                continue;
            }

            $finder = new Finder();
            $finder->in($targetDirectory)->name('*.yml');

            foreach ($finder as $file) {

                $container->addResource(new FileResource($file->getPathName()));

                $alias      = $file->getBaseName('.yml');
                $parameters = $yamlParser->parse(file_get_contents($file->getPathName()));

                $config = [
                    $alias => $parameters
                ];
                $container->prependExtensionConfig($this->getAlias(), $config);
            }
        }
    }
}
