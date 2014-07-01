<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use FSi\Component\DataGrid\DataGrid;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamGlobTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;

class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $stream;

    /**
     * @var ConfigurationBuilder
     */
    protected $subscriber;

    private function initConfigurationBuilder()
    {
        $configurationLocator = new ConfigurationLocator($this->kernel);
        $this->configurationLoader = new ConfigurationLoader($this->kernel, $configurationLocator);
        $this->subscriber = new ConfigurationBuilder($this->kernel, $this->configurationLoader);
    }

    public function setUp()
    {
        $this->kernel = new StubKernel(sprintf("%s/Fixtures",sys_get_temp_dir()));
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->kernel->getRootDir()));
        $this->kernel->injectBundle(new StubBundle('BarBundle', $this->kernel->getRootDir()));
        $this->stream = vfsStream::setup($this->kernel->getRootDir());

        $this->initConfigurationBuilder();
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            $this->subscriber->getSubscribedEvents(),
            array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128))
        );
    }

    public function testImportFromGlobalConfig()
    {
        $configFile = <<<YML
columns:
  author:
    type: text
    options:
      label: Author

imports:
  - { resource: "/app/config/datagrid/global.yml" }
YML;
        $global = <<<YML
columns:
  title:
    type: text
    options:
      label: Title
YML;

        $bundleConfigPath = $this->createConfigFile('FooBundle/Resources/config/datagrid/bundle.yml', $configFile);
        $globalConfigPath = $this->createConfigFile('app/config/datagrid/global.yml', $configFile);

        /** @var DataGrid $dataGrid */
        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('global'));

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with(
                $this->equalTo('title'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Title'))
            );

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author'))
            );

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);

    }

    public function testImportFromAnotherBundle()
    {
        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('another_bundle'));

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with(
                $this->equalTo('actions'),
                $this->equalTo('action'),
                array(
                    'label' => 'admin.gallery.datagrid.actions',
                    'field_mapping' => array('id'),
                    'actions' => array(
                        'activate' => array(
                            'route_name' => 'fsi_admin_crud_edit',
                            'additional_parameters' => array('element' => 'gallery'),
                            'parameters_field_mapping' => array('id' => 'id')
                        ),
                        'delete' => array(
                            'route_name' => 'fsi_admin_crud_delete',
                            'additional_parameters' => array('element' => 'gallery'),
                            'parameters_field_mapping' => array('id' => 'id')
                        ),
                    )
                )
            );

        $dataGrid->expects($this->at(5))
            ->method('addColumn')
            ->with(
                $this->equalTo('description'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Description'))
            );

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author'))
            );

        $dataGrid->expects($this->at(6))
            ->method('addColumn')
            ->with(
                $this->equalTo('active'),
                $this->equalTo('boolean'),
                $this->equalTo(array('label' => 'Active'))
            );

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    public function testImportFromSameDirectory()
    {

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('same_bundle'));

        $dataGrid->expects($this->at(5))
            ->method('addColumn')
            ->with(
                $this->equalTo('id'),
                $this->equalTo('number'),
                $this->equalTo(array('label' => 'Identity'))
            );

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author'))
            );


        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with(
                $this->equalTo('actions'),
                $this->equalTo('action'),
                array(
                    'label' => 'admin.gallery.datagrid.actions',
                    'field_mapping' => array('id'),
                    'actions' => array(
                        'edit' => array(
                            'route_name' => 'fsi_admin_crud_edit',
                            'additional_parameters' => array('element' => 'gallery'),
                            'parameters_field_mapping' => array('id' => 'id')
                        ),
                        'delete' => array(
                            'route_name' => 'fsi_admin_crud_delete',
                            'additional_parameters' => array('element' => 'gallery'),
                            'parameters_field_mapping' => array('id' => 'id')
                        )
                    )
                )
            );


        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);

    }

    public function testReadConfigurationFromOneBundle()
    {
        $this->kernel = new StubKernel(array('FooBundle'));

        $this->initConfigurationBuilder();

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with('id', 'number', array('label' => 'Identity'));

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    public function testReadConfigurationFromManyBundles()
    {
        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        // 0 - 3 getName() is called
        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with('id', 'number', array('label' => 'ID'));

        $dataGrid->expects($this->at(5))
            ->method('addColumn')
            ->with('title', 'text', array());

        $dataGrid->expects($this->at(6))
            ->method('addColumn')
            ->with('author', 'text', array());


        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    private function createConfigFile($fileName, $content)
    {
        $path = sprintf("%s/%s", $this->kernel->getRootDir(), $fileName);
        $dirName = dirname($path);

        if (!$this->stream->hasChild($dirName)) {
            $this->stream->removeChild($dirName);
        }
        $streamD = new vfsStreamDirectory($dirName);
        $streamD->addChild(new vfsStreamFile($fileName));
        $this->stream->addChild($streamD);

        return $this->stream->getChild($path);
    }


    private static function thereIsAConfiguration($path, $content)
    {

    }

}
