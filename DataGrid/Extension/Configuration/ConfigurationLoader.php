<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{
    /**
     * @var ConfigurationImporter
     */
    protected $configurationImporter;

    /**
     * @param ConfigurationImporter $configurationImporter
     */
    public function __construct(ConfigurationImporter $configurationImporter = null)
    {
        $this->configurationImporter = $configurationImporter;
    }

    /**
     * @param string $resourcePath
     * @return bool
     */
    private function exist($resourcePath)
    {
        return file_exists($resourcePath);
    }

    /**
     * @param string $resourcePath
     * @return array
     */
    public function load($resourcePath)
    {
        if (!$this->exist($resourcePath)) {
            return array();
        }
        $dataGridConfiguration = $this->parse($resourcePath);

        if (!$this->configurationImporter) {
            return $dataGridConfiguration;
        }

        return $this->configurationImporter->import($dataGridConfiguration);
    }

    /**
     * @param string $resourcePath
     * @return array
     */
    private function parse($resourcePath)
    {
        return Yaml::parse($resourcePath);
    }

    public function setConfiguratinImporter(ConfigurationImporter $configurationImporter)
    {
        $this->configurationImporter = $configurationImporter;
    }

}