<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

class ConfigurationImporter
{
    /**
     * @var ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var ResourceLocator
     */
    protected $resourceLocator;

    /**
     * @param ConfigurationLoader $configurationLoader
     * @param ResourceLocator $resourceLocator
     */
    public function __construct(ConfigurationLoader $configurationLoader, ResourceLocator $resourceLocator)
    {
        $this->configurationLoader = $configurationLoader;
        $this->resourceLocator = $resourceLocator;
    }

    public function import(array $configuration)
    {
        if (!array_key_exists('imports', $configuration) || !is_array($configuration['imports'])) {
            return $configuration;
        }

        foreach ($configuration['imports'] as $resource) {
            $resourcePath =  $this->resourceLocator->locateByResourcePath($resource['resource']);
            $configuration = array_replace_recursive(
                $configuration,
                $this->configurationLoader->load($resourcePath)
            );
        }
        unset($configuration['imports']);

        return $configuration;
    }
}