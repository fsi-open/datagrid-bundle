<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use FSi\Component\DataGrid\DataGrid;

class ConfigurationBuilderTest extends TestCase
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
        $kernelMockBuilder = $this->getMockBuilder(Kernel::class)
            ->setConstructorArgs(['dev', true]);
        if (version_compare(Kernel::VERSION, '2.7.0', '<')) {
            $kernelMockBuilder->setMethods(['registerContainerConfiguration', 'registerBundles', 'getBundles', 'init']);
        } else {
            $kernelMockBuilder->setMethods(['registerContainerConfiguration', 'registerBundles', 'getBundles']);
        }
        $this->kernel = $kernelMockBuilder->getMock();

        $this->subscriber = new ConfigurationBuilder($this->kernel);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            $this->subscriber->getSubscribedEvents(),
            [DataGridEvents::PRE_SET_DATA => ['readConfiguration', 128]]
        );
    }

    public function testReadConfigurationFromOneBundle()
    {
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function() {
                $bundle = $this->createMock(Bundle::class);
                $bundle->expects($this->any())
                    ->method('getPath')
                    ->will($this->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                return [$bundle];
            }));

        $dataGrid = $this->getMockBuilder(DataGrid::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->once())
            ->method('addColumn')
            ->with('id', 'number', ['label' => 'Identity']);

        $event = new DataGridEvent($dataGrid, []);

        $this->subscriber->readConfiguration($event);
    }

    public function testReadConfigurationFromManyBundles()
    {
        $this->kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnCallback(function() {
                $fooBundle = $this->createMock(Bundle::class);
                $fooBundle->expects($this->any())
                    ->method('getPath')
                    ->will($this->returnValue(__DIR__ . '/../../../../Fixtures/FooBundle'));

                $barBundle = $this->createMock(Bundle::class);
                $barBundle->expects($this->any())
                    ->method('getPath')
                    ->will($this->returnValue(__DIR__ . '/../../../../Fixtures/BarBundle'));
                return [
                    $fooBundle,
                    $barBundle
                ];
            }));

        $dataGrid = $this->getMockBuilder(DataGrid::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        // 0 - 3 getName() is called
        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with('id', 'number', ['label' => 'ID']);

        $dataGrid->expects($this->at(5))
            ->method('addColumn')
            ->with('title', 'text', []);

        $dataGrid->expects($this->at(6))
            ->method('addColumn')
            ->with('author', 'text', []);

        $event = new DataGridEvent($dataGrid, []);

        $this->subscriber->readConfiguration($event);
    }
}
