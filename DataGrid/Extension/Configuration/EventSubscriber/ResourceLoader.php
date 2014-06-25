<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

class ResourceLoader
{
    /**
     * @var array
     */
    protected $configs = array();

    /**
     * @var string
     */
    protected $bundlePath = '';

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @param array $configs
     * @param string $bundlePath
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @internal param array $imports
     */
    public function __construct($configs, $bundlePath, KernelInterface $kernel)
    {
        $this->configs = $configs;
        $this->bundlePath = $bundlePath;
        $this->kernel = $kernel;
    }


    /**
     * @param array $configs
     * @return array
     */
    public function getConfig($configs = array())
    {
        if (isset($this->configs['imports'])) {
            foreach ($this->configs['imports'] as $config) {
                $resourcePath = $this->getResourcePath($config);
                $this->configs = array_replace_recursive(
                    $this->getImportedConfiguration($resourcePath),
                    $this->configs
                );
            }
        }
        return $this->configs;

    }

    /**
     * @param string $resourcePath
     * @return array
     */
    private function getImportedConfiguration($resourcePath)
    {
        if ($configuration = Yaml::parse($resourcePath)) {

            if (isset($configuration['imports']) && is_array($configuration['imports'])) {
                $resourceLoader = new ResourceLoader($configuration, $this->bundlePath, $this->kernel);

                $configuration = array_replace_recursive(
                    $configuration,
                    $resourceLoader->getConfig()
                );
            }
        }
        return array();
    }

    /**
     * @param $config
     * @return string
     */
    protected function getResourcePath($config)
    {
        if (preg_match('/^\//', $config['resource'])) { //Load from global app config
            return $this->getGlobalResourcePath($config['resource']);
        } elseif (preg_match('/:/', $config['resource'])) { //Load from bundle
            return $this->getBundleResourcePath($config['resource']);
        } else {
            return $this->getInlineResourcePath($config['resource']);
        }
    }

    /**
     * @param $config
     * @return array|string
     */
    protected function getGlobalResourcePath($config)
    {
        return $this->kernel->locateResource(
            sprintf(
                '%s%s',
                $this->kernel->getRootDir(),
                $config
            )
        );
    }

    /**
     * @param $config
     * @return array|string
     * @throws
     */
    protected function getBundleResourcePath($config)
    {
        $bundle = explode(':', $config);
        if (count($bundle) == 2) {
            return $this->kernel->locateResource(
                sprintf('@%s:Resources/config/datagrid/%s', $bundle[0], $bundle[1])
            );
        } else {
            throw Exception('Invalid config path. It should looks like DemoBundle:config.yml');
        }
    }

    /**
     * @param $config
     * @return string
     */
    protected function getInlineResourcePath($config)
    {
        return sprintf("%s/Resources/config/datagrid/%s", $this->bundlePath, $config);
    }


}