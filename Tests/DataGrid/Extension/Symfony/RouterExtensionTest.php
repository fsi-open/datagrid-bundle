<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DatagridBundle\Tests\DataGrid\Extension\Symfony;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\RouterExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RouterExtensionTest extends TestCase
{
    public function testSymfonyExtension(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $extension = new RouterExtension($router, $requestStack);

        self::assertTrue($extension->hasColumnType('action'));
    }
}
