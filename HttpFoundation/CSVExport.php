<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\HttpFoundation;

class CSVExport extends ExportAbstract
{
    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @var string
     */
    protected $fileExtension = 'csv';

    /**
     * @var string
     */
    protected $mimeType = 'text/csv';

    /**
     * @var string
     */
    protected $data;

    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    protected function setData(): void
    {
        $dataGrid = $this->getDataGrid();
        $fp = fopen('php://temp', 'r+');
        // BOM
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $columns = [];

        foreach ($dataGrid->getColumns() as $column) {
            $columns[] = isset($this->translator)
                ? $this->translator->trans($column->getLabel(), [], $column->getAttribute('translation_domain'))
                : $column->getLabel();
        }

        fputcsv($fp, $columns, $this->delimiter, $this->enclosure);

        foreach ($dataGrid as $row) {
            $rowArray = [];
            foreach ($row as $cell) {
                $rowArray[] = $cell->getValue();
            }

            fputcsv($fp, $rowArray, $this->delimiter, $this->enclosure);
        }

        rewind($fp);
        $this->data = stream_get_contents($fp);
        $this->data = $this->convertLineEndings($this->data);
        $this->update();
    }

    protected function convertLineEndings(string $data): string
    {
        return $data;
    }

    private function update(): void
    {
        $fileName = sprintf('%s.%s', $this->getFileName(), $this->fileExtension);
        $this->headers->set('Content-Type', $this->mimeType);
        $this->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->setContent($this->data);
    }
}
