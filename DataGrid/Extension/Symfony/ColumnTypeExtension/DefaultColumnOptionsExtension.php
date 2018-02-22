<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnType\Action;
use FSi\Component\DataGrid\Extension\Core\ColumnTypeExtension\DefaultColumnOptionsExtension as BaseDefaultColumnOptionsExtension;

class DefaultColumnOptionsExtension extends BaseDefaultColumnOptionsExtension
{
    public function getExtendedColumnTypes(): array
    {
        return [Action::class];
    }
}
