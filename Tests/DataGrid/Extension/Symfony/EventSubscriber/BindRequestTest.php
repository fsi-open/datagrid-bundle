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
use function class_exists;

class BindRequestTest extends TestCase
{
    public function testPreBindDataWithoutRequestObject(): void
    {
        $event = $this->createMock(DataGridEventInterface::class);
        $event->expects(self::never())->method('setData');

        $subscriber = new BindRequest();

        $subscriber->preBindData($event);
    }

    public function testPreBindDataPOST(): void
    {
        /** @var Request&MockObject $request */
        $request = $this->createMock(Request::class);
        $request->expects(self::once())->method('getMethod')->willReturn('POST');

        /** @var ParameterBag&MockObject $requestBag */
        $requestBag = $this->createMock(ParameterBag::class);
        $requestBag->expects(self::once())->method('get')->with('grid', [])->willReturn(['foo' => 'bar']);

        $request->request = $requestBag;

        $grid = $this->createMock(DataGridInterface::class);
        $grid->expects(self::once())->method('getName')->willReturn('grid');

        $event = $this->createMock(DataGridEventInterface::class);
        $event->expects(self::once())->method('getData')->willReturn($request);
        $event->expects(self::once())->method('setData')->with(['foo' => 'bar']);
        $event->expects(self::once())->method('getDataGrid')->willReturn($grid);

        $subscriber = new BindRequest();

        $subscriber->preBindData($event);
    }

    public function testPreBindDataGET(): void
    {
        $request = new Request();
        $request->setMethod('GET');
        $request->query->set('grid', ['foo' => 'bar']);

        $grid = $this->createMock(DataGridInterface::class);
        $grid->expects(self::once())->method('getName')->willReturn('grid');

        $event = $this->createMock(DataGridEventInterface::class);
        $event->expects(self::once())->method('getData')->willReturn($request);
        $event->expects(self::once())->method('setData')->with(['foo' => 'bar']);
        $event->expects(self::once())->method('getDataGrid')->willReturn($grid);

        $subscriber = new BindRequest();

        $subscriber->preBindData($event);
    }
}
