<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension\FormExtension;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension\BooleanColumnExtension;
use FSi\Component\DataGrid\Extension\Core\ColumnType\Boolean;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanColumnExtensionTest extends TestCase
{
    public function testColumnOptions(): void
    {
        $column = new Boolean();
        $formExtension = new FormExtension($this->getFormFactory());
        $formExtension->initOptions($column);
        $extension = new BooleanColumnExtension($this->getTranslator());
        $extension->initOptions($column);
        $options = $column->getOptionsResolver()->resolve();

        self::assertEquals('YES', $options['true_value']);
        self::assertEquals('NO', $options['false_value']);
    }

    /**
     * @return TranslatorInterface&MockObject
     */
    private function getTranslator(): TranslatorInterface
    {
        /** @var TranslatorInterface&MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);

        $translator->expects(self::at(0))
            ->method('trans')
            ->with('datagrid.boolean.yes', [], 'DataGridBundle')
            ->willReturn('YES');

        $translator->expects(self::at(1))
            ->method('trans')
            ->with('datagrid.boolean.no', [], 'DataGridBundle')
            ->willReturn('NO');

        return $translator;
    }

    /**
     * @return FormFactoryInterface&MockObject
     */
    private function getFormFactory(): FormFactoryInterface
    {
        /** @var FormFactoryInterface&MockObject $formFactory */
        $formFactory = $this->createMock(FormFactoryInterface::class);

        return $formFactory;
    }
}
