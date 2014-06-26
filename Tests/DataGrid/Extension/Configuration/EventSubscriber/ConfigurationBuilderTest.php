<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
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
     * @var ConfigurationBuilder
     */
    protected $subscriber;

    public function setUp()
    {
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $configurationLocator = $this->getMock(
            'FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator',
            array('__construct'),
            array($this->kernel)
        );

        $this->configurationLoader = $this->getMock(
            'FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader',
            array('__construct'),
            array($this->kernel,$configurationLocator)
        );

        $this->subscriber = new ConfigurationBuilder($this->kernel, $this->configurationLoader);
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
        $self = $this;

        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function () use ($self) {
                $bundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $bundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));
                return array($bundle);
            }));


        $kernel = $this->kernel;

        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../../../Fixtures'));

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('global'));

        $kernel->expects($this->at(4))
            ->method('locateResource')
            ->with($kernel->getRootDir() . '/app/config/datagrid/galleries.yml')
            ->will($this->returnValue($kernel->getRootDir() . '/app/config/datagrid/galleries.yml'));

        $dataGrid->expects($this->at(2))
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
        $self = $this;

        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function () use ($self) {
                $fooBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $fooBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                return array($fooBundle);
            }));

        $kernel = $this->kernel;

        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../../../Fixtures'));

        $barBundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $barBundle->expects($this->any())
            ->method('getPath')
            ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle'));

        $kernel->expects($this->once())
            ->method('getBundle')
            ->with('BarBundle')
            ->will($this->returnValue($barBundle));

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('another_bundle'));

        $dataGrid->expects($this->at(3))
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

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with(
                $this->equalTo('description'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Description')));

        $dataGrid->expects($this->at(5))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author')));

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with(
                $this->equalTo('active'),
                $this->equalTo('boolean'),
                $this->equalTo(array('label' => 'Active')));


        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);

    }

    public function testImportFromSameDirectory()
    {
        $self = $this;

        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function () use ($self) {
                $bundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $bundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));
                return array($bundle);
            }));

        $kernel = $this->kernel;

        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../../../Fixtures'));

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('same_bundle'));

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with(
                $this->equalTo('id'),
                $this->equalTo('number'),
                $this->equalTo(array('label' => 'Identity')));

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author')));


        $dataGrid->expects($this->at(3))
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
        $self = $this;
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function () use ($self) {
                $bundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $bundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                return array($bundle);
            }));

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
        $self = $this;
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function () use ($self) {
                $fooBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $fooBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                $barBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $barBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle'));
                return array(
                    $fooBundle,
                    $barBundle
                );
            }));

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

}