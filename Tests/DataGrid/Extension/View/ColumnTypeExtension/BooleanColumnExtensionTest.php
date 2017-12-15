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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;

class BooleanColumnExtensionTest extends TestCase
{
    public function testColumnOptions()
    {
        $column = new Boolean();
        $formExtension = new FormExtension($this->getFormFactory());
        $formExtension->initOptions($column);
        $extension = new BooleanColumnExtension($this->getTranslator());
        $extension->initOptions($column);
        $options = $column->getOptionsResolver()->resolve();

        $this->assertEquals('YES', $options['true_value']);
        $this->assertEquals('NO', $options['false_value']);
    }

    private function getTranslator()
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
     * @return FormFactoryInterface
     */
    private function getFormFactory(): \PHPUnit_Framework_MockObject_MockObject
    {
        return $this->createMock(FormFactoryInterface::class);
    }
}
