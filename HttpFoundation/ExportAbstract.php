<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\HttpFoundation;

use FSi\Component\DataGrid\DataGridViewInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

abstract class ExportAbstract extends Response
{
    /**
     * @var DataGridViewInterface
     */
    protected $datagrid;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var TranslatorInterface|null
     */
    protected $translator;

    public function __construct(
        DataGridViewInterface $datagrid,
        string $filename,
        int $status = 200,
        array $headers = [],
        TranslatorInterface $translator = null
    ) {
        parent::__construct('', $status, $headers);

        $this->translator = $translator;
        $this->filename = $filename;
        $this->datagrid = $datagrid;
        $this->setData();
    }

    public function getDataGrid(): DataGridViewInterface
    {
        return $this->datagrid;
    }

    public function getFileName(): string
    {
        return $this->filename;
    }

    abstract protected function setData(): void;
}
