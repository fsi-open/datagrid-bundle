<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DatagridBundle\Tests\DataGrid\Extension\Symfony\ColumnType;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnType\Action;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension\DefaultColumnOptionsExtension;
use FSi\Bundle\DataGridBundle\Tests\Fixtures\Request;
use FSi\Component\DataGrid\DataGridFactory;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataGrid\DataGridInterface;
use FSi\Component\DataGrid\Tests\Fixtures\SimpleDataGridExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\RouterInterface;

class ActionTest extends TestCase
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var DataGridFactoryInterface
     */
    private $dataGridFactory;

    protected function setUp()
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects($this->any())
            ->method('getMasterRequest')
            ->will($this->returnValue(new Request()));

        $this->dataGridFactory = new DataGridFactory(
            new EventDispatcher(),
            [new SimpleDataGridExtension(
                new DefaultColumnOptionsExtension(),
                new Action($this->router, $this->requestStack)
            )]
        );
    }

    public function testFilterValueWrongActionsOptionType()
    {
        $this->expectException(InvalidOptionsException::class);
        $this->dataGridFactory->createColumn(
            $this->getDataGridMock(),
            Action::class,
            'actions',
            ['actions' => 'boo']
        );
    }

    public function testFilterValueInvalidActionInActionsOption()
    {
        $this->expectException(\TypeError::class);

        $this->dataGridFactory->createColumn($this->getDataGridMock(), Action::class, 'actions', [
            'actions' => ['edit' => 'asdasd'],
            'field_mapping' => ['id'],
        ]);
    }

    public function testFilterValueRequiredActionInActionsOption()
    {
        $this->router->expects($this->any())
            ->method('generate')
            ->with('foo', ['redirect_uri' => Request::RELATIVE_URI], false)
            ->will($this->returnValue('/test/bar?redirect_uri=' . urlencode(Request::ABSOLUTE_URI)));

        $column = $this->dataGridFactory->createColumn($this->getDataGridMock(), Action::class, 'actions', [
            'actions' => [
                'edit' => [
                    'route_name' => 'foo',
                    'absolute' => false,
                ],
            ],
            'field_mapping' => ['foo'],
        ]);
        $cellView = $this->dataGridFactory->createCellView($column, 0, (object) ['foo' => 'bar']);

        $this->assertSame(
            [
                'edit' => [
                    'content' => 'edit',
                    'field_mapping_values' => [
                        'foo' => 'bar'
                    ],
                    'url_attr' => [
                        'href' => '/test/bar?redirect_uri=http%3A%2F%2Fexample.com%2F%3Ftest%3D1%26test%3D2'
                    ]
                ]
            ],
            $cellView->getValue()
        );
    }

    public function testFilterValueAvailableActionInActionsOption()
    {
        $this->router->expects($this->once())
            ->method('generate')
            ->with('foo', ['Foo' => 'bar', 'redirect_uri' => Request::RELATIVE_URI], true)
            ->will($this->returnValue('https://fsi.pl/test/bar?redirect_uri=' . urlencode(Request::RELATIVE_URI)));

        $column = $this->dataGridFactory->createColumn($this->getDataGridMock(), Action::class, 'actions', [
            'actions' => [
                'edit' => [
                    'route_name' => 'foo',
                    'parameters_field_mapping' => ['Foo' => 'foo'],
                    'absolute' => true
                ]
            ],
            'field_mapping' => ['foo'],
        ]);
        $cellView = $this->dataGridFactory->createCellView($column, 0, (object) ['foo' => 'bar']);

        $this->assertSame(
            [
                'edit' => [
                    'content' => 'edit',
                    'field_mapping_values' => [
                        'foo' => 'bar'
                    ],
                    'url_attr' => [
                        'href' => 'https://fsi.pl/test/bar?redirect_uri=' . urlencode(Request::RELATIVE_URI)
                    ]
                ]
            ],
            $cellView->getValue()
        );
    }

    public function testFilterValueWithRedirectUriFalse()
    {
        $this->router->expects($this->once())
            ->method('generate')
            ->with('foo', [], false)
            ->will($this->returnValue('/test/bar'));

        $column = $this->dataGridFactory->createColumn($this->getDataGridMock(), Action::class, 'actions', [
            'actions' => [
                'edit' => [
                    'route_name' => 'foo',
                    'absolute' => false,
                    'redirect_uri' => false,
                ]
            ],
            'field_mapping' => ['foo'],
        ]);
        $cellView = $this->dataGridFactory->createCellView($column, 0, (object) ['foo' => 'bar']);

        $this->assertSame(
            [
                'edit' => [
                    'content' => 'edit',
                    'field_mapping_values' => [
                        'foo' => 'bar'
                    ],
                    'url_attr' => [
                        'href' => '/test/bar'
                    ]
                ]
            ],
            $cellView->getValue()
        );
    }

    private function getDataGridMock(): DataGridInterface
    {
        return $this->createMock(DataGridInterface::class);
    }
}
