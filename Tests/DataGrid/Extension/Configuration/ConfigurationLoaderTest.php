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

        $configs = array(
            'columns' => array(),
            'imports' => array(
                array ('resource' => 'galleries.yml')
            )
        );

        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/../../../Fixtures/FooBundle'));

        $configLoaded = $this->configurationLoader->load($configs, $bundle);
        $expected = array(
            'columns' => array(
                'id' => array(
                    'type' => 'number',
                    'options' => array('label' => 'Identity')
                ),
                'actions' => array(
                    'type' => 'action',
                    'options' => array(
                        'label' => 'admin.gallery.datagrid.actions',
                        'field_mapping' => array('id'),
                        'actions' => array(
                            'edit' => array(
                                'route_name' => 'fsi_admin_crud_edit',
                                'additional_parameters' => array('element' => 'gallery'),
                                'parameters_field_mapping' => array('id' => 'id')
                            )
                        )
                    )
                )
            )
        );

        $this->assertEquals($expected, $configLoaded);
    }
}