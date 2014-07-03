<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationImporter;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ResourceLocator;
use FSi\Component\DataGrid\DataGridEventInterface;
use FSi\Component\DataGrid\DataGridEvents;
use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ConfigurationBuilder implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader $configurationLoader
     * @param \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ResourceLocator $resourceLocator
     */
    function __construct(
        KernelInterface $kernel,
        ConfigurationLoader $configurationLoader,
        ConfigurationImporter $configurationImporter,
        ResourceLocator $resourceLocator
    ) {
        $this->kernel = $kernel;
        $this->configurationLoader = $configurationLoader;
        $this->resourceLocator = $resourceLocator;
        $this->configurationLoader->setConfiguratinImporter($configurationImporter);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128));
    }

    /**
     * {@inheritdoc}
     */
    public function readConfiguration(DataGridEventInterface $event)
    {
        $dataGrid = $event->getDataGrid();
        $dataGridConfiguration = array();
        foreach ($this->kernel->getBundles() as $bundle) {
            $resourcePath = $this->resourceLocator->locate($this->getBundleResourcePath($bundle, $dataGrid));
            $dataGridConfiguration = $this->configurationLoader->load($resourcePath);
        }

        if (count($dataGridConfiguration)) {
            $this->buildConfiguration($dataGrid, $dataGridConfiguration);
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $bundle
     * @param \FSi\Component\DataGrid\DataGridInterface $dataGrid
     * @return string
     */
    private function getBundleResourcePath(BundleInterface $bundle, DataGridInterface $dataGrid)
    {
        return sprintf(
            "%s:%s.yml",
            $bundle->getName(),
            $dataGrid->getName()
        );
    }

    /**
     * @param \FSi\Component\DataGrid\DataGridInterface  $dataGrid
     * @param array $configuration
     */
    protected function buildConfiguration(DataGridInterface $dataGrid, array $configuration)
    {
        foreach ($configuration['columns'] as $name => $column) {
            $type = array_key_exists('type', $column)
                ? $column['type']
                : 'text';
            $options = array_key_exists('options', $column)
                ? $column['options']
                : array();

            $dataGrid->addColumn($name, $type, $options);
        }
    }
}
