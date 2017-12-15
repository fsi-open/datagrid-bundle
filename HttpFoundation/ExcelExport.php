<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\HttpFoundation;

class ExcelExport extends ExportAbstract
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $fileExtension = 'xls';

    /**
     * @var string
     */
    protected $mimeType = 'application/vnd.ms-excel';

    protected function setData(): void
    {
        $PHPExcel = new \PHPExcel();
        $dataGrid = $this->getDataGrid();
        $rowNum = 1;
        $colNum = 0;

        foreach ($dataGrid->getColumns() as $header) {
            $label =  isset($this->translator)
                ? $this->translator->trans($header->getLabel(), [], $header->getAttribute('translation_domain'))
                : $header->getLabel();

            $PHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colNum, $rowNum, $label);
            $colNum++;
        }

        $rowNum++;

        foreach ($dataGrid as $row) {
            $colNum = 0;
            foreach ($row as $cell) {
                $PHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colNum, $rowNum, (string) $cell->getValue());
                $colNum++;
            }

            $rowNum++;
        }

        $writer = $this->getWriter($PHPExcel);
        ob_start();
        $writer->save("php://output");
        $this->data = ob_get_clean();
        $this->update();
    }

    protected function getWriter(\PHPExcel $PHPExcel): \PHPExcel_Writer_Abstract
    {
        return new \PHPExcel_Writer_Excel5($PHPExcel);
    }

    private function update(): void
    {
        $fileName = sprintf('%s.%s', $this->getFileName(), $this->fileExtension);
        $this->headers->set('Content-Type', $this->mimeType);
        $this->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $this->setContent($this->data);
    }
}
