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
     * @param array $configuration
     * @param BundleInterface $bundle
     * @return array
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function load(array $configuration, BundleInterface $bundle)
    {
        if (isset($configuration['imports']) && is_array($configuration['imports'])) {
            foreach ($configuration['imports'] as $import) {
                $contextBundle = $this->configurationLocator->getBundleByResource($import['resource'], $bundle);
                $configuration = $this->importResource($configuration, $import['resource'], $contextBundle);
            }
        }
        unset($configuration['imports']);
        return $configuration;
    }

    /**
     * @param array $configuration
     * @param string $resource
     * @param BundleInterface $contextBundle
     * @return array
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    private function importResource($configuration, $resource, BundleInterface $contextBundle)
    {
        $resourcePath = $this->configurationLocator->locateConfig($resource, $contextBundle);
        $importedConfiguration = Yaml::parse($resourcePath);

        if (!is_array($importedConfiguration)) {
            throw new FileNotFoundException($resourcePath);
        }

        $configuration['columns'] = array_replace_recursive(
            $configuration['columns'],
            $importedConfiguration['columns']
        );

        $nestedConfiguration = $this->load($importedConfiguration, $contextBundle);
        $configuration['columns'] = array_replace_recursive(
            $configuration['columns'],
            $nestedConfiguration['columns']
        );

        return $configuration;
    }
}
