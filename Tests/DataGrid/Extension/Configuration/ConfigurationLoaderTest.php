<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use Symfony\Component\HttpKernel\KernelInterface;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
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

    public function testImportConfig()
    {
        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('admin_galleries'));

        $configs = array();
        $this->configurationLoader->getConfig(array(),'');
    }
}