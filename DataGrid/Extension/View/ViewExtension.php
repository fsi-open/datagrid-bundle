<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View;

use FSi\Component\DataGrid\DataGridAbstractExtension;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

class ViewExtension extends DataGridAbstractExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadColumnTypesExtensions()
    {
        return array(
            new ColumnTypeExtension\ColumnViewOptionsExtension(),
            new ColumnTypeExtension\ActionViewOptionsExtension(),
        );
    }
}
