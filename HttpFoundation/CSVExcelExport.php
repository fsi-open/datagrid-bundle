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

class CSVExcelExport extends CSVExport
{
    /**
     * Set CRLF line ending
     * @param $data
     * @return mixed
     */
    public function setLineEndings($data)
    {
        $data = str_replace("\n", "\r\n", $data);

        return $data;
    }
}
