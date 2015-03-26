<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader;

use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;

abstract class FileLoader extends BaseFileLoader
{
    /**
     * @var \FSi\Component\DataGrid\DataGridInterface
     */
    protected $dataGrid;

    /**
     * @param \FSi\Component\DataGrid\DataGridInterface $dataGrid
     */
    public function setDataGrid(DataGridInterface $dataGrid)
    {
        $this->dataGrid = $dataGrid;
    }
}
