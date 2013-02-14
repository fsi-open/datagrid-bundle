<?php

/*
 * This file is part of the FSi Component package.
*
* (c) Norbert Orzechowicz <norbert@fsi.pl>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace FSi\Bundle\DataGridBundle\HttpFoundation;

use FSi\Component\DataGrid\DataGridViewInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class ExportAbstract extends Response
{
    /**
     * @var \FSi\Component\DataGrid\DataGridViewInterface
     */
    protected $datagrid;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param \FSi\Component\DataGrid\DataGridViewInterface $datagrid
     * @param string $filename - filename without extension.
     * @param int $status
     * @param array $headers
     */
    public function __construct(DataGridViewInterface $datagrid, $filename, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->filename = $filename;
        $this->datagrid = $datagrid;
        $this->setData();
    }

    /**
     * @return \FSi\Component\DataGrid\DataGridViewInterface
     */
    public function getDataGrid()
    {
        return $this->datagrid;
    }

    /**
     * Return filename without file extension.
     * File extension should be determined by class that extends ExportAbstract.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * @return ExportAbstract
     */
    public abstract function setData();
}