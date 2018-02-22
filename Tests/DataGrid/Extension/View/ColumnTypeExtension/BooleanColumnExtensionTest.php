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
use FSi\Component\DataGrid\DataGridInterface;
use FSi\Component\DataGrid\Extension\Core\ColumnType\Boolean;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanColumnExtensionTest extends TestCase
{
    public function testColumnOptions()
    {
        $columnType = new Boolean();
        $columnType->addExtension(new FormExtension($this->getFormFactory()));
        $columnType->addExtension(new BooleanColumnExtension($this->getTranslator()));

        $column = $columnType->createColumn($this->createMock(DataGridInterface::class), 'test', []);

        $this->assertEquals('YES', $column->getOption('true_value'));
        $this->assertEquals('NO', $column->getOption('false_value'));
    }

    private function getTranslator(): TranslatorInterface
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $translator->expects($this->at(0))
            ->method('trans')
            ->with('datagrid.boolean.yes', [], 'DataGridBundle')
            ->will($this->returnValue('YES'));

        $translator->expects($this->at(1))
            ->method('trans')
            ->with('datagrid.boolean.no', [], 'DataGridBundle')
            ->will($this->returnValue('NO'));

        return $translator;
    }

    /**
     * @return FormFactoryInterface|MockObject
     */
    private function getFormFactory(): FormFactoryInterface
    {
        return $this->createMock(FormFactoryInterface::class);
    }
}
