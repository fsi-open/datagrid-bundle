<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
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
        $this->kernel = new StubKernel(__DIR__.'/../../../Fixtures');
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->kernel->getRootDir()));
        $this->configurationLocator = new ConfigurationLocator($this->kernel);
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

        $configLoaded = $this->configurationLoader->load($configs, $this->kernel->getBundle('FooBundle'));
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
