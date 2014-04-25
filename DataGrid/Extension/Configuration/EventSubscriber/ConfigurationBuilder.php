<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Component\DataGrid\DataGridEventInterface;
use FSi\Component\DataGrid\DataGridEvents;
use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class ConfigurationBuilder implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @param KernelInterface $kernel
     */
    function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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

            if ($this->hasDataGridConfiguration($bundle->getPath(), $dataGrid->getName())) {
                $configuration = $this->getDataGridConfiguration($bundle->getPath(), $dataGrid->getName());

                if (is_array($configuration)) {
                    $dataGridConfiguration = $configuration;
                }
            }
        }

        if (count($dataGridConfiguration)) {
            $this->buildConfiguration($dataGrid, $dataGridConfiguration);
        }
    }

    /**
     * @param string $bundlePath
     * @param string $dataGridName
     * @return bool
     */
    protected function hasDataGridConfiguration($bundlePath, $dataGridName)
    {
        return file_exists(sprintf($bundlePath . '/Resources/config/datagrid/%s.yml', $dataGridName));
    }

    /**
     * @param string $bundlePath
     * @param string $dataGridName
     * @return mixed
     */
    protected function getDataGridConfiguration($bundlePath, $dataGridName)
    {
        $conf = Yaml::parse(sprintf($bundlePath . '/Resources/config/datagrid/%s.yml', $dataGridName));

        if(isset($conf['imports']) && $conf['imports']) {
            $conf = $this->importResources($conf,$bundlePath);
        }

        return $conf;
    }

    /**
     * @param DataGridInterface $dataGrid
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

    /**
     * @param $configuration
     * @return array
     */
    protected function importResources($configuration, $bundlePath)
    {

        foreach($configuration['imports'] as $config) {

            if(preg_match('/^\//',$config['resource']) || preg_match('/^@/',$config['resource'])) {
                $resource = $this->kernel->locateResource($config['resource']);
            } else {
                $resource = $bundlePath."/Resources/config/datagrid/{$config['resource']}";
            }

            if($tempConfig = Yaml::parse($resource)) {
                if(isset($tempConfig['imports']) && $tempConfig['imports']) {
                    $tempConfig = array_merge_recursive($tempConfig, $this->importResources($tempConfig, $bundlePath));
                }
                $configuration = array_merge_recursive($tempConfig,$configuration) ;
            }

        }

        unset($configuration['imports']);
        return $configuration;
    }
}
