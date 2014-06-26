<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @var ConfigurationLocator
     */
    protected $configurationLocator;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param ConfigurationLocator $configurationLocator
     * @internal param array $imports
     */
    public function __construct(KernelInterface $kernel, ConfigurationLocator $configurationLocator)
    {
        $this->kernel = $kernel;
        $this->configurationLocator = $configurationLocator;
    }

    /**
     * @param $configs
     * @param BundleInterface $bundle
     * @return array
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function load($configs, BundleInterface $bundle)
    {
        if (isset($configs['imports']) && is_array($configs['imports'])) {
            foreach ($configs['imports'] as $config) {
                $contextBundle = $this->configurationLocator->getBundle($config['resource'], $bundle);
                $configs = $this->mergeConfigs($configs, $config, $contextBundle);
            }
        }
        unset($configs['imports']);
        return $configs;
    }

    /**
     * @param $configs
     * @param $config
     * @param $contextBundle
     * @return mixed
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    private function mergeConfigs($configs, $config, $contextBundle)
    {
        $resourcePath = $this->configurationLocator->locate($config['resource'], $contextBundle);
        $configuration = Yaml::parse($resourcePath);

        if (!is_array($configuration)) {
            throw new FileNotFoundException($resourcePath);
        }

        $configs['columns'] = array_replace_recursive(
            $configs['columns'],
            $configuration['columns']
        );

        $importedConfigs = $this->load($configuration, $contextBundle);
        $configs['columns'] = array_replace_recursive(
            $configs['columns'],
            $importedConfigs['columns']);
        return $configs;
    }
}
