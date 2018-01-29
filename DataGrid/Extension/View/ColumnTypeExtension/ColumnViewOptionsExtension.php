<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;

class ColumnViewOptionsExtension extends ColumnAbstractTypeExtension
{
    public function buildCellView(ColumnTypeInterface $column, CellViewInterface $view): void
    {
        $view->setAttribute('translation_domain', $column->getOption('translation_domain'));
    }

    public function buildHeaderView(ColumnTypeInterface $column, HeaderViewInterface $view): void
    {
        $view->setAttribute('translation_domain', $column->getOption('translation_domain'));
    }

    public function getExtendedColumnTypes(): array
    {
        return [
            'action',
            'boolean',
            'text',
            'datetime',
            'number',
            'money',
            'gedmo_tree',
            'entity',
            'collection',
        ];
    }

    public function initOptions(ColumnTypeInterface $column): void
    {
        $column->getOptionsResolver()->setDefaults([
            'translation_domain' => 'messages',
        ]);

        $column->getOptionsResolver()->setAllowedTypes('translation_domain', [
            'string' ,
            'null'
        ]);
    }
}
