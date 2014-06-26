<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ConfigurationLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator
     */
    protected $configurationLocator;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    public function setUp()
    {
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $this->configurationLocator = $this->getMock(
            'FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator',
            array('__construct'),
            array($this->kernel)
        );
        $this->configurationLoader = new ConfigurationLoader($this->kernel, $this->configurationLocator);
    }

    public function testLocateGlobalResource()
    {
        $this->kernel->expects($this->once())
            ->method('locateResource')
            ->with($this->kernel->getRootDir() . '/app/config/datagrid/galleries.yml')
            ->will($this->returnValue($this->kernel->getRootDir() . '/app/config/datagrid/galleries.yml'));

        $configPath = '/app/config/datagrid/galleries.yml';
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../../../Fixtures/FooBundle'));

        $resourcePath = $this->configurationLocator->locate($configPath, $bundle);
        $globalPath = $this->kernel->getRootDir() . '/app/config/datagrid/galleries.yml';

        $this->assertEquals($globalPath, $resourcePath);
    }

    public function testLocateBundleResource()
    {
        $configPath = 'BarBundle:galleries.yml';

        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../../../Fixtures/BarBundle'));


        $resourcePath = $this->configurationLocator->locate($configPath, $bundle);
        $expectedPath = sprintf('%s/Resources/config/datagrid/%s', $bundle->getPath(), end(explode(":",$configPath)));

        $this->assertEquals($expectedPath, $resourcePath);
    }

    public function testLocateInlineResource()
    {
        $configPath = 'galleries.yml';

        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../../../Fixtures/FooBundle'));

        $resourcePath = $this->configurationLocator->locate($configPath, $bundle);
        $expectedPath = sprintf('%s/Resources/config/datagrid/%s', $bundle->getPath(), $configPath);

        $this->assertEquals($expectedPath, $resourcePath);
    }
}