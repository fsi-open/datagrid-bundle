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
     * @param array $config
     * @param $bundle
     * @return array
     */
    public function locate($config, BundleInterface $bundle)
    {
        if ($this->isGlobalConfig($config)) {
            return $this->getGlobalResourcePath($config);
        }

        if ($this->isAnotherBundle($config)) {
            $bundleName = explode(':', $config);
            $fileName = end($bundleName);
            return $this->getBundleResourcePath($fileName, $bundle);
        }

        return $this->getBundleResourcePath($config, $bundle);
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
        if (!$this->isAnotherBundle($config)) {
            return $contextBundle;
        }

        $configName = explode(':', $config);
        $bundleName = reset($configName);
        return $this->loadBundle($bundleName);
    }

    /**
     * @param $config
     * @return array|string
     */
    protected function getGlobalResourcePath($config)
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
     * @param $config
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $bundle
     * @throws \Symfony\Component\Filesystem\Exception\FileNotFoundException
     * @internal param $bundlePath
     * @return string
     */
    protected function getBundleResourcePath($config, BundleInterface $bundle)
    {
        $filePath = sprintf("%s/Resources/config/datagrid/%s", $bundle->getPath(), $config);

        if (!is_file($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        return $filePath;
    }

    /**
     * @param $bundleName
     * @return BundleInterface
     * @throws \Exception
     */
    private function loadBundle($bundleName)
    {
        if (!($bundle = $this->kernel->getBundle($bundleName))) {
            throw new \Exception(sprintf('%s cannot be found.', $bundleName));
        }

        return $bundle;
    }

    /**
     * @param $config
     * @return bool
     */
    private function isGlobalConfig($config)
    {
        return (boolean) preg_match('/^\//', $config);
    }

    /**
     * @param $config
     * @return bool
     */
    private function isAnotherBundle($config)
    {
        return (boolean) preg_match('/:/', $config);
    }
}
