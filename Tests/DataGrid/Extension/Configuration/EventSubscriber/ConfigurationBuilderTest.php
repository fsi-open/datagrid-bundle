<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader\YamlFileLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Locator\FileLocator;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;

class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ConfigurationBuilder
     */
    protected $subscriber;

    public function setUp()
    {
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\Kernel', array(), array('dev', true));
    }

    public function testSubscribedEvents()
    {
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array()));

        $this->initSubscriber();

        $this->assertEquals(
            $this->subscriber->getSubscribedEvents(),
            array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128))
        );
    }

    public function testReadConfigurationFromOneBundle()
    {
        $self = $this;
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function() use ($self) {
                $bundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $bundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                return array($bundle);
            }));

        $this->initSubscriber();

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->once())
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
            ->will($this->returnCallback(function() use ($self) {
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

        $this->initSubscriber();

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->at(1))
            ->method('addColumn')
            ->with('id', 'number', array('label' => 'ID'));

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with('title', 'text', array());

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with('author', 'text', array());

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    public function testImportConfigurationFromSameBundle()
    {
        $self = $this;
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function() use ($self) {
                $barBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $barBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle'));

                return array(
                    $barBundle
                );
            }));

        $this->initSubscriber();

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('extended_news'));

        $dataGrid->expects($this->at(1))
            ->method('addColumn')
            ->with('id', 'number', array('label' => 'ID'));

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with('title', 'text', array());

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with('author', 'text', array());

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with('actions', 'actions', array(
                'field_mapping' => array('id'),
                'actions' => array(
                    'edit' => array(
                        'route_name' => 'route_edit',
                        'parameters_field_mapping' =>
                            array('id' => 'id')
                    )
                )
            ));

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    public function testImportConfigurationFromAnotherBundle()
    {
        $self = $this;
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function() use ($self) {
                $barBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $barBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle'));

                $bazBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $bazBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BazBundle'));

                return array(
                    $barBundle,
                    $bazBundle
                );
            }));

        $this->kernel->expects($self->once())
            ->method('locateResource')
            ->with('@BarBundle/Resources/config/datagrid/news.yml', '', false)
            ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle/Resources/config/datagrid/news.yml'));

        $this->initSubscriber();

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('extended_news'));

        $dataGrid->expects($this->at(1))
            ->method('addColumn')
            ->with('id', 'number', array('label' => 'ID'));

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with('title', 'text', array());

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with('author', 'text', array());

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with('actions', 'actions', array(
                'field_mapping' => array('id'),
                'actions' => array(
                    'view' => array(
                        'route_name' => 'route_view',
                        'parameters_field_mapping' =>
                            array('id' => 'id')
                    )
                )
            ));

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    public function testImportConfigurationFromDifferentSources()
    {
        $self = $this;
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function() use ($self) {
                $fooBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $fooBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                $barBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $barBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle'));

                $bazBundle = $self->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
                $bazBundle->expects($self->any())
                    ->method('getPath')
                    ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BazBundle'));

                return array(
                    $barBundle,
                    $bazBundle,
                    $fooBundle,
                );
            }));

        $this->kernel->expects($self->once())
            ->method('locateResource')
            ->with('@BarBundle/Resources/config/datagrid/extended_news.yml', '', false)
            ->will($self->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle/Resources/config/datagrid/extended_news.yml'));

        $this->initSubscriber();

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('extended_news'));

        $dataGrid->expects($this->at(1))
            ->method('addColumn')
            ->with('id', 'number', array('label' => 'ID'));

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with('title', 'text', array());

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with('author', 'text', array());

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with('actions', 'actions', array(
                'field_mapping' => array('id'),
                'actions' => array(
                    'edit' => array(
                        'route_name' => 'route_edit',
                        'parameters_field_mapping' =>
                            array('id' => 'id')
                    )
                )
            ));

        $dataGrid->expects($this->at(5))
            ->method('addColumn')
            ->with('actions2', 'actions', array(
                'field_mapping' => array('id'),
                'actions' => array(
                    'some_action' => array(
                        'route_name' => 'route_some_action',
                        'parameters_field_mapping' =>
                            array('id' => 'id')
                    )
                )
            ));

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    protected function initSubscriber()
    {
        $this->subscriber = new ConfigurationBuilder(
            new YamlFileLoader(
                new FileLocator($this->kernel, '', 'Resources/config/datagrid')
            )
        );
    }
}
