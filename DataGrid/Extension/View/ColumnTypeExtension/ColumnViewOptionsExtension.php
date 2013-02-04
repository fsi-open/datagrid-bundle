<?php

/*
 * This file is part of the FSi Component package.
 *
 * (c) Norbert Orzechowicz <norbert@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;

class ColumnViewOptionsExtension extends ColumnAbstractTypeExtension
{
    /**
     * {@inheritDoc}
     */
    public function buildCellView(ColumnTypeInterface $column, CellViewInterface $view)
    {
        $view->setAttribute('translation_domain', $column->getOption('translation_domain'));
    }

    /**
     * {@inheritDoc}
     */
    public function buildHeaderView(ColumnTypeInterface $column, HeaderViewInterface $view)
    {
        $view->setAttribute('translation_domain', $column->getOption('translation_domain'));
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedColumnTypes()
    {
        return array(
            'action',
            'boolean',
            'text',
            'datetime',
            'number',
            'money',
            'gedmo.tree',
            'entity',
            'action'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptionsValues(ColumnTypeInterface $column)
    {
        return array(
            'translation_domain' => null
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableOptions(ColumnTypeInterface $column)
    {
        return array('translation_domain');
    }
}