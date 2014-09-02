<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\DataGridTest;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationImporter;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;

class ConfigurationImporterTest extends DataGridTest
{

    /**
     * @var \FSi\Bundle\DataGridBundle\Tests\Double\StubKernel
     */
    protected $kernel;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ResourceLocator
     */
    protected $resourceLocator;

    /**
     * @var ConfigurationBuilder
     */
    protected $importer;

    public function setUp()
    {
        $this->kernel = new StubKernel(self::FIXTURE_PATH);
        $this->configurationLoader = $this->getMockBuilder('FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceLocator = $this->getMockBuilder('FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ResourceLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->importer = new ConfigurationImporter($this->configurationLoader, $this->resourceLocator);
    }

    public function testImportConfigurationFromExternalBundle()
    {
        $config = array(
            'columns' => array(
                'title' => array(
                    'type' => 'text',
                    'options' => array(
                        'label' => 'Title'
                    )
                )
            ),
            'imports' => array(
                array('resource' => 'BarBundle:news.yml')
            )
        );

        $this->resourceLocator->expects($this->once())
            ->method('locate')
            ->with($this->equalTo('BarBundle:news.yml'))
            ->will($this->returnValue(sprintf('%s/BarBundle/Resources/config/datagrid/news.yml', self::FIXTURE_PATH)));

        $this->configurationLoader->expects($this->once())
            ->method('load')
            ->with($this->equalTo(sprintf('%s/BarBundle/Resources/config/datagrid/news.yml', self::FIXTURE_PATH)))
            ->will($this->returnValue(array(
                'columns' => array(
                    'author' => array(
                        'type' => 'text',
                        'options' => array(
                            'label' => 'Author'
                        )
                    )
                )
            )));

        $importedConfiguration = $this->importer->import($config);

        $this->assertEquals($importedConfiguration, array(
            'columns' => array(
                'title' => array(
                    'type' => 'text',
                    'options' => array(
                        'label' => 'Title'
                    )
                ),
                'author' => array(
                    'type' => 'text',
                    'options' => array(
                        'label' => 'Author'
                    )
                )
            ),
        ));
    }
}