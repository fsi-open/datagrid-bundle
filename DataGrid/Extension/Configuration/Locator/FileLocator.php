<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Locator;

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

class FileLocator extends BaseFileLocator
{
    private $kernel;
    private $bundleRelativePath;
    private $path;

    /**
     * @param KernelInterface $kernel A KernelInterface instance
     * @param string $path
     * @param string $bundleRelativePath
     */
    public function __construct(KernelInterface $kernel, $path, $bundleRelativePath)
    {
        $this->kernel = $kernel;
        $this->bundleRelativePath = $bundleRelativePath;
        $this->path = $path;

        $paths = array($path);
        $paths = array_merge($paths, $this->getBundlesPaths());

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ($this->isFileInBundle($file)) {
            return $this->kernel->locateResource(
                $this->getFilePathInBundle($file),
                $this->path,
                $first
            );
        }

        if (!$this->isFileGlobal($file) && !$this->isPathInBundle($currentPath)) {
            $file = $this->prependBundleRelativePath($file);
        }

        return parent::locate($file, $currentPath, $first);
    }

    /**
     * @return array
     */
    private function getBundlesPaths()
    {
        $paths = array();
        foreach (array_reverse($this->kernel->getBundles()) as $bundle) {
            $paths[] = $bundle->getPath();
        }
        return $paths;
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFileInBundle($file)
    {
        return strpos($file, ':') !== false;
    }

    /**
     * @param $file
     * @return string
     */
    private function getFilePathInBundle($file)
    {
        list($bundleName, $fileName) = explode(':', $file, 2);

        return sprintf('@%s/%s/%s', $bundleName, $this->bundleRelativePath, $fileName);
    }

    /**
     * @param string $file
     * @return bool
     */
    private function isFileGlobal($file)
    {
        return $file[0] === '/';
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isPathInBundle($path)
    {
        return strpos($path, $this->bundleRelativePath) !== false;
    }

    /**
     * @param $file
     * @return string
     */
    private function prependBundleRelativePath($file)
    {
        return sprintf('%s/%s', $this->bundleRelativePath, $file);
    }
}
