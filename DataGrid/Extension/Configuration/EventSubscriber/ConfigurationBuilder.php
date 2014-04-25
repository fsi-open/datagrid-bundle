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
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
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
        return file_exists(sprintf('%s/Resources/config/datagrid/%s.yml', $bundlePath, $dataGridName));
    }

    /**
     * @param string $bundlePath
     * @param string $dataGridName
     * @return mixed
     */
    protected function getDataGridConfiguration($bundlePath, $dataGridName)
    {
        $conf = Yaml::parse(sprintf('%s/Resources/config/datagrid/%s.yml', $bundlePath, $dataGridName));

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

//        echo PHP_EOL.json_encode($configuration).PHP_EOL;

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
     * @param $bundlePath
     * @throws
     * @return array
     */
    protected function importResources($configuration, $bundlePath)
    {

        foreach($configuration['imports'] as $config) {

            if(preg_match('/^\//',$config['resource'])) { //Load from global app config

                $resource = $this->kernel->locateResource(sprintf(
                    '%s%s',
                    $this->kernel->getRootDir(),
                    $config['resource']
                ));
//                echo PHP_EOL.'locate resources'.PHP_EOL;

            } elseif( preg_match('/:/',$config['resource'])) { //Load from bundle

                $bundle = explode(':',$config['resource']);
                try {
                    if(count($bundle) == 2) {
                        $resource = $this->kernel->locateResource(sprintf('@%s:Resources/config/datagrid/%s',$bundle[0],$bundle[1]));
//                        echo PHP_EOL.'locate resources'.PHP_EOL;
                    } else {
                        throw Exception('Invalide config path. It should looks like DemoBundle:config.yml');
                    }
                } catch (Exception $e) {
                    throw $e;
                }


            } else { //Load from current brunch

                $resource = sprintf("%s/Resources/config/datagrid/%s", $bundlePath, $config['resource']);
            }
//            echo PHP_EOL.'import-resources'.PHP_EOL;
//            echo PHP_EOL.$resource.PHP_EOL;

            if($tempConfig = Yaml::parse($resource)) {

                if(!is_array($tempConfig)) continue;

                if(isset($tempConfig['imports']) && is_array($tempConfig['imports'])) {
                    $tempConfig = array_replace_recursive($tempConfig, $this->importResources($tempConfig, $bundlePath));
                }

                $configuration = array_replace_recursive($tempConfig, $configuration);

            }

        }

        unset($configuration['imports']);
        return $configuration;
    }

}
