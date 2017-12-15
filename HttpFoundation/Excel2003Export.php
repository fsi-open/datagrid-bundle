<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\HttpFoundation;

class Excel2003Export extends Excel2007Export
{
    protected function getWriter(\PHPExcel $PHPExcel): \PHPExcel_Writer_Abstract
    {
        $writer = parent::getWriter($PHPExcel);
        $writer->setOffice2003Compatibility(true);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}
