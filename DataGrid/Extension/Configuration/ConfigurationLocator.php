<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
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
     * @param $bundle
     * @return array
     */
    public function locate($config, BundleInterface $bundle)
    {
        if (preg_match('/^\//', $config)) { //Load from global app config
            return $this->getGlobalResourcePath($config);
        } elseif (preg_match('/:/', $config)) { //Load from bundle
            $fileName = end(explode(':', $config));
            return $this->getBundleResourcePath($fileName, $bundle);
        } else {
            return $this->getBundleResourcePath($config, $bundle);
        }
    }

    /**
     * @param $config
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $contextBundle
     * @throws \Exception
     * @internal param $contextPath
     * @return string
     */
    public function getBundle($config, BundleInterface $contextBundle)
    {
        if (preg_match('/:/', $config)) {
            $bundleName = reset(explode(':', $config));
            if($bundle = $this->kernel->getBundle($bundleName)) {
                return $bundle;
            } else {
                throw new \Exception(sprintf('%s cannot be found.', $bundleName));
            }
        } else {
            return $contextBundle;
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
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $bundle
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @internal param $bundlePath
     * @return string
     */
    protected function getBundleResourcePath($config, BundleInterface $bundle)
    {
        $filePath = sprintf("%s/Resources/config/datagrid/%s", $bundle->getPath(), $config);
        if(is_file($filePath)) {
            return $filePath;
        } else {
            throw new FileNotFoundException($filePath);
        }
    }
}
