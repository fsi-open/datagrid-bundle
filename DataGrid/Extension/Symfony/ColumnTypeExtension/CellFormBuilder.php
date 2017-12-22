<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnInterface;
use Symfony\Component\Form\FormBuilderInterface;

interface CellFormBuilder
{
    public function buildCellForm(FormBuilderInterface $form, ColumnInterface $column): void;
}
