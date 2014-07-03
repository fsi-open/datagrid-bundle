<?php

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration;

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

    public function locate($resourceName)
    {
        if ($this->isGlobal($resourceName)) {
            return $this->getGlobalResourcePath($resourceName);
        }

        if ($this->isBundleResource($resourceName)) {
            return $this->getBundleResourcePath($resourceName);
        }
    }

    /**
     * @param string $resourceName
     * @return bool
     */
    public function isGlobal($resourceName)
    {
        return !preg_match("/:/", $resourceName);
    }

    /**
     * @param string $resourceName
     * @return boolean
     */
    public function isBundleResource($resourceName)
    {
        return boolval(preg_match("/:/", $resourceName));
    }

    private function getGlobalResourcePath($resourceName)
    {
        return sprintf(
            "%s/app/config/%s/%s",
            $this->kernel->getRootDir(),
            $this->configPath,
            $resourceName
        );
    }

    private function getBundleResourcePath($resourceName)
    {
        list($bundleName, $fileName) = explode(':', $resourceName);

        $bundle = $this->kernel->getBundle($bundleName);

        return sprintf("%s/Resources/config/datagrid/%s", $bundle->getPath(), $fileName);
    }

}