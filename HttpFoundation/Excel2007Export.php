<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\HttpFoundation;

/**
 * @deprecated
 */
class Excel2007Export extends ExcelExport
{
    /**
     * @var string
     */
    protected $fileExtension = 'xlsx';

    /**
     * @var string
     */
    protected $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    protected function getWriter(\PHPExcel $PHPExcel): \PHPExcel_Writer_Abstract
    {
        $writer = new \PHPExcel_Writer_Excel2007($PHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}
