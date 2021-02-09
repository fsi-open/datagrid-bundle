<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DatagridBundle\Tests\DataGrid\Extension\Symfony;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\FormExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class FormExtensionTest extends TestCase
{
    public function testSymfonyFormExtension(): void
    {
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $extension = new FormExtension($formFactory);

        self::assertFalse($extension->hasColumnType('foo'));
        self::assertTrue($extension->hasColumnTypeExtensions('text'));
    }
}
