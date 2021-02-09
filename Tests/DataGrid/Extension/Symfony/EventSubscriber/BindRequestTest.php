<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DatagridBundle\Tests\DataGrid\Extension\Symfony\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\EventSubscriber\BindRequest;
use FSi\Component\DataGrid\DataGridEventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use FSi\Component\DataGrid\DataGridInterface;

class BindRequestTest extends TestCase
{
    public function testPreBindDataWithoutRequestObject()
    {
        $event = $this->createMock(DataGridEventInterface::class);
        $event->expects($this->never())
            ->method('setData');

        $subscriber = new BindRequest();

        $subscriber->preBindData($event);
    }

    public function testPreBindDataPOST()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
             ->method('getMethod')
             ->will($this->returnValue('POST'));

        $requestBag = $this->createMock(ParameterBag::class);
        $requestBag->expects($this->once())
            ->method('get')
            ->with('grid', [])
            ->will($this->returnValue(['foo' => 'bar']));

        $request->request = $requestBag;

        $grid = $this->createMock(DataGridInterface::class);
        $grid->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('grid'));

        $event = $this->createMock(DataGridEventInterface::class);
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('setData')
            ->with(['foo' => 'bar']);

        $event->expects($this->once())
            ->method('getDataGrid')
            ->will($this->returnValue($grid));

        $subscriber = new BindRequest();

        $subscriber->preBindData($event);
    }

    public function testPreBindDataGET()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
             ->method('getMethod')
             ->will($this->returnValue('GET'));

        $queryBag = new InputBag();
        $queryBag->set('grid', ['foo' => 'bar']);

        $request->query = $queryBag;

        $grid = $this->createMock(DataGridInterface::class);
        $grid->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('grid'));

        $event = $this->createMock(DataGridEventInterface::class);
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('setData')
            ->with(['foo' => 'bar']);

        $event->expects($this->once())
            ->method('getDataGrid')
            ->will($this->returnValue($grid));

        $subscriber = new BindRequest();

        $subscriber->preBindData($event);
    }
}
