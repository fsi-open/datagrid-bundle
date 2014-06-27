<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

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
     * @param string $resource
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $contextBundle
     * @return string
     */
    public function locateConfig($resource, BundleInterface $contextBundle)
    {
        if ($this->isGlobalResource($resource)) {
            return $this->getGlobalResourcePath($resource);
        }

        if ($this->isBundleResource($resource)) {
            $bundleName = explode(':', $resource);
            $fileName = end($bundleName);
            return $this->getBundleResourcePath($fileName, $contextBundle);
        }

        return $this->getBundleResourcePath($resource, $contextBundle);
    }

    /**
     * @param string $resource
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $contextBundle
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface
     */
    public function getBundleByResource($resource, BundleInterface $contextBundle)
    {
        if (!$this->isBundleResource($resource)) {
            return $contextBundle;
        }

        $configName = explode(':', $resource);
        $bundleName = reset($configName);
        return $this->kernel->getBundle($bundleName);
    }

    /**
     * @param string $config
     * @return string
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     */
    private function getGlobalResourcePath($config)
    {
        $fileName = sprintf(
            '%s%s',
            $this->kernel->getRootDir(),
            $config
        );

        if(!file_exists($fileName)) {
            throw new FileNotFoundException($fileName);
        }

        return $fileName;
    }

    /**
     * @param string $resource
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $bundle
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @return string
     */
    private function getBundleResourcePath($resource, BundleInterface $bundle)
    {
        $filePath = sprintf("%s/Resources/config/datagrid/%s", $bundle->getPath(), $resource);

        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        return $filePath;
    }

    /**
     * @param string $resource
     * @return bool
     */
    private function isGlobalResource($resource)
    {
        return (boolean) preg_match('/^\//', $resource);
    }

    /**
     * @param string $resource
     * @return bool
     */
    private function isBundleResource($resource)
    {
        return (boolean) preg_match('/:/', $resource);
    }
}
