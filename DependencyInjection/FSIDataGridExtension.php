<?php

/**
 * (c) FSi Sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class FSIDataGridExtension extends Extension
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

        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration('FSi\Component\DataGrid\DataGridExtensionInterface')
                ->addTag('datagrid.extension');
            $container->registerForAutoconfiguration('FSi\Component\DataGrid\Column\ColumnTypeInterface')
                ->addTag('datagrid.column');
            $container->registerForAutoconfiguration('FSi\Component\DataGrid\Column\ColumnTypeExtensionInterface')
                ->addTag('datagrid.column_extension');
            $container->registerForAutoconfiguration('FSi\Bundle\DataGridBundle\DataGrid\EventSubscriberInterface')
                ->addTag('datagrid.subscriber');
        }
    }

    public function registerTwigConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('twig.xml');
        $container->setParameter('datagrid.twig.themes', $config['themes']);
    }
}
