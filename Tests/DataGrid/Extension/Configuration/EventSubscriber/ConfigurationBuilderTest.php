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
     * @var string
     */
    protected static $fixtureAppPath;

    /**
     * @var string
     */
    protected static $datagridConfigRelativePath = 'Resources/config/datagrid';

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
        self::$fixtureAppPath = __DIR__ . '/../../../../Fixtures';
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
                return array($self->getBundleMock('FooBundle'));
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
                return array(
                    $self->getBundleMock('FooBundle'),
                    $self->getBundleMock('BarBundle')
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
                return array($self->getBundleMock('BarBundle'));
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
                return array(
                    $self->getBundleMock('BarBundle'),
                    $self->getBundleMock('BazBundle')
                );
            }));

        $this->kernel->expects($this->once())
            ->method('locateResource')
            ->with('@BarBundle/' . self::$datagridConfigRelativePath . '/news.yml', '', false)
            ->will($this->returnValue(self::$fixtureAppPath . '/BarBundle/' . self::$datagridConfigRelativePath . '/news.yml'));

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
                return array(
                    $self->getBundleMock('BarBundle'),
                    $self->getBundleMock('BazBundle'),
                    $self->getBundleMock('FooBundle')
                );
            }));

        $this->kernel->expects($this->once())
            ->method('locateResource')
            ->with('@BarBundle/' . self::$datagridConfigRelativePath . '/extended_news.yml', '', false)
            ->will($this->returnValue(self::$fixtureAppPath . '/BarBundle/' . self::$datagridConfigRelativePath . '/extended_news.yml'));

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

    public function getBundleMock($bundleName)
    {
        $bundleMock = $this->getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundleMock->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(self::$fixtureAppPath . '/' . $bundleName));

        return $bundleMock;
    }

    protected function initSubscriber()
    {
        $this->subscriber = new ConfigurationBuilder(
            new YamlFileLoader(
                new FileLocator($this->kernel, '', self::$datagridConfigRelativePath)
            )
        );
    }
}
