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
use FSi\Bundle\DataGridBundle\Tests\Fixtures\Request;
use FSi\Component\DataGrid\Extension\Core\ColumnTypeExtension\DefaultColumnOptionsExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActionTest extends TestCase
{
    /**
     * @var RouterInterface&MockObject
     */
    private $router;

    /**
     * @var Action
     */
    private $column;

    protected function setUp(): void
    {
        /** @var RouterInterface&MockObject $router */
        $router = $this->createMock(RouterInterface::class);
        $this->router = $router;

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getMasterRequest')->willReturn(new Request());

        $column = new Action($this->router, $requestStack);
        $column->setName('action');
        $column->initOptions();

        $extension = new DefaultColumnOptionsExtension();
        $extension->initOptions($column);

        $this->column = $column;
    }

    public function testFilterValueWrongActionsOptionType()
    {
        $this->expectException(InvalidOptionsException::class);
        $this->column->setOption('actions', 'boo');
    }

    public function testFilterValueInvalidActionInActionsOption()
    {
        $this->column->setOption('actions', ['edit' => 'asdasd']);

        $this->expectException(\InvalidArgumentException::class);
        $this->column->filterValue([]);
    }

    public function testFilterValueRequiredActionInActionsOption()
    {
        $this->router->method('generate')
            ->with('foo', ['redirect_uri' => Request::RELATIVE_URI], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/test/bar?redirect_uri=' . urlencode(Request::ABSOLUTE_URI));

        $this->column->setName('action');
        $this->column->initOptions();

        $extension = new DefaultColumnOptionsExtension();
        $extension->initOptions($this->column);


        $this->column->setOption('actions', [
            'edit' => [
                'route_name' => 'foo',
                'absolute' => UrlGeneratorInterface::ABSOLUTE_PATH
            ]
        ]);

        self::assertSame(
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
            $this->column->filterValue([
                'foo' => 'bar'
            ])
        );
    }

    public function testFilterValueAvailableActionInActionsOption()
    {
        $this->router->expects(self::once())
            ->method('generate')
            ->with(
                'foo',
                ['foo' => 'bar', 'redirect_uri' => Request::RELATIVE_URI],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://fsi.pl/test/bar?redirect_uri=' . urlencode(Request::RELATIVE_URI));

        $this->column->setName('action');
        $this->column->initOptions();

        $extension = new DefaultColumnOptionsExtension();
        $extension->initOptions($this->column);

        $this->column->setOption('field_mapping', ['foo']);
        $this->column->setOption('actions', [
            'edit' => [
                'route_name' => 'foo',
                'parameters_field_mapping' => ['foo' => 'foo'],
                'absolute' => UrlGeneratorInterface::ABSOLUTE_URL
            ]
        ]);

        self::assertSame(
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
            $this->column->filterValue([
                'foo' => 'bar'
            ])
        );
    }


    public function testFilterValueWithRedirectUriFalse()
    {
        $this->router->expects(self::once())
            ->method('generate')
            ->with('foo', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/test/bar');

        $this->column->setName('action');
        $this->column->initOptions();

        $extension = new DefaultColumnOptionsExtension();
        $extension->initOptions($this->column);

        $this->column->setOption('actions', [
            'edit' => [
                'route_name' => 'foo',
                'absolute' => UrlGeneratorInterface::ABSOLUTE_PATH,
                'redirect_uri' => false
            ]
        ]);

        self::assertSame(
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
            $this->column->filterValue([
                'foo' => 'bar'
            ])
        );
    }
}
