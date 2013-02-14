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

use Symfony\Component\HttpFoundation\Response;

class Excel2003Export extends Excel2007Export
{
    /**
     * @param \PHPExcel $PHPExcel
     * @return \PHPExcel_Writer_Excel5
     */
    protected function getWriter(\PHPExcel $PHPExcel)
    {
        $writer = parent::getWriter($PHPExcel);
        $writer->setOffice2003Compatibility(true);
        $writer->setPreCalculateFormulas(false);
        return $writer;
    }
}
