<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ResourceLocator
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @var string
     */
    protected $configPath;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param string $configPath
     */
    public function __construct(KernelInterface $kernel, $configPath)
    {
        $this->kernel = $kernel;
        $this->configPath = $configPath;
    }

    /**
     * @param string $resourceName
     * @return string
     */
    public function locateByResourcePath($resourceName)
    {
        if ($this->isBundleResource($resourceName)) {
            return $this->getBundleResourcePath($resourceName);
        }

        return $this->getGlobalResourcePath($resourceName);
    }

    /**
     * @param BundleInterface $bundle
     * @param string $fileName
     * @return string
     */
    public function locateByBundle(BundleInterface $bundle, $fileName)
    {
        return sprintf(
            "%s/Resources/config/%s/%s",
            $bundle->getPath(),
            $this->configPath,
            $fileName
        );
    }

        /**
     * @param string $resourceName
     * @return boolean
     */
    private function isBundleResource($resourceName)
    {
        return preg_match("/:/", $resourceName);
    }

    /**
     * @param string $resourceName
     * @return string
     */
    private function getGlobalResourcePath($resourceName)
    {
        return sprintf(
            "%s/config/%s/%s",
            $this->kernel->getRootDir(),
            $this->configPath,
            $resourceName
        );
    }

    /**
     * @param string $resourceName
     * @return string
     */
    private function getBundleResourcePath($resourceName)
    {
        list($bundleName, $fileName) = explode(':', $resourceName);
        $bundle = $this->kernel->getBundle($bundleName);

        return $this->locateByBundle($bundle, $fileName);
    }
}