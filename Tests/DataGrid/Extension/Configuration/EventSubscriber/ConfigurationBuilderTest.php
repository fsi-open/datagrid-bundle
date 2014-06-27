<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\Tests\Double\KernelStub;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;

class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscribedEvents()
    {
        $kernel = new KernelStub(array('FooBundle', 'BarBundle'));
        $subscriber = new ConfigurationBuilder($kernel);

        $this->assertEquals(
            $subscriber->getSubscribedEvents(),
            array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128))
        );
    }

    public function testReadConfigurationFromOneBundle()
    {
        $kernel = new KernelStub(array('FooBundle'));
        $subscriber = new ConfigurationBuilder($kernel);

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

        $subscriber->readConfiguration($event);
    }

    public function testReadConfigurationFromManyBundles()
    {
        $kernel = new KernelStub(array('FooBundle', 'BarBundle'));
        $subscriber = new ConfigurationBuilder($kernel);

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

        $subscriber->readConfiguration($event);
    }
}
