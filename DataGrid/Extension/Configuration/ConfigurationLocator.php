<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLocator
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @internal param array $imports
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param array $config
     * @param $bundlePath
     * @return array
     */
    public function localize($config, $bundlePath)
    {
        if (preg_match('/^\//', $config['resource'])) { //Load from global app config
            return $this->getGlobalResourcePath($config['resource']);
        } elseif (preg_match('/:/', $config['resource'])) { //Load from bundle
            return $this->getBundleResourcePath($config['resource']);
        } else {
            return $this->getInlineResourcePath($config['resource'], $bundlePath);
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
     * @param $bundlePath
     * @return string
     */
    protected function getInlineResourcePath($config, $bundlePath)
    {
        return sprintf("%s/Resources/config/datagrid/%s", $bundlePath, $config);
    }
}
