<?php

namespace FSi\Bundle\DataGridBundle\DataGrid;

use Symfony\Component\Filesystem\Filesystem;

abstract class DataGridTest extends \PHPUnit_Framework_TestCase
{
    const FIXTURE_PATH = '/tmp/DataGridBundle';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fileSystem;

    /**
     * @param string $fileName
     * @param string $content
     * @return string
     */
    protected function createConfigurationFile($fileName, $content)
    {
        $path = sprintf("%s/%s", $this->kernel->getRootDir(), $fileName);
        $dirName = dirname($path);

        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }

        file_put_contents($path, $content);

        return $path;
    }

    protected function prepareFileSystem()
    {
        $this->fileSystem = new Filesystem($this->kernel->getRootDir());
        $this->fileSystem->mkdir($this->kernel->getRootDir());
    }

    protected function destroyFileSystem()
    {
        $this->fileSystem->remove($this->kernel->getRootDir());
    }
}