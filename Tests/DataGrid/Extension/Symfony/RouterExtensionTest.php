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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RouterExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testSymfonyExtension()
    {
        $router = $this->createMock(RouterInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $extension = new RouterExtension($router, $requestStack);

        $this->assertTrue($extension->hasColumnType('action'));
    }
}
