<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

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
     * @param array $configs
     * @param $bundlePath
     * @return array
     */
    public function getConfig($configs, $bundlePath)
    {

        if (isset($configs['imports'])) {
            foreach ($configs['imports'] as $k => $config) {
                $resourcePath = $this->configurationLocator->localize($config, $bundlePath);
                $configs = array_replace_recursive(
                    $this->getImportedConfiguration($resourcePath, $bundlePath),
                    $configs
                );
                unset($configs[$k]);
            }
        }
        return $configs;
    }

    /**
     * @param string $resourcePath
     * @param $bundlePath
     * @return array
     */
    private function getImportedConfiguration($resourcePath, $bundlePath)
    {
        if ($configuration = Yaml::parse($resourcePath)) {
            if (isset($configuration['imports']) && is_array($configuration['imports'])) {
                $configuration = array_replace_recursive(
                    $configuration,
                    $this->getConfig($configuration, $bundlePath)
                );
            }
            return $configuration;
        }
        return array();
    }
}
