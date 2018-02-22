<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension\CellFormBuilder;
use FSi\Bundle\DataGridBundle\Form\Type\RowType;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\ColumnInterface;
use FSi\Component\DataGrid\DataGrid;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var bool
     */
    private $csrfTokenEnabled;

    /**
     * @var FormInterface[]
     */
    private $forms;

    public static function getSubscribedEvents(): array
    {
        return [
            DataGridEvents::POST_SET_DATA => 'buildForms',
        ];
    }

    public function __construct(FormFactoryInterface $formFactory, bool $csrfTokenEnabled = true)
    {
        $this->formFactory = $formFactory;
        $this->csrfTokenEnabled = $csrfTokenEnabled;
        $this->forms = [];
    }

    public function buildForms(DataGridEvent $event): void
    {
        $dataGrid = $event->getDataGrid();
        $data = $event->getData();

        $entryType = $this->isSymfony3() ? RowType::class : 'row';

        if ($this->csrfTokenEnabled) {
            $formBuilderOptions['csrf_protection'] = false;
        }

        $editableColumns = $this->getEditableColumns($dataGrid);
        if (0 === count($editableColumns)) {
            return;
        }

        $formBuilder = $this->formFactory
            ->createNamedBuilder($dataGrid->getName(), $this->getFormTypeName(), $data, $formBuilderOptions);

        foreach ($data as $index => $row) {
            $formBuilder->add($index, $entryType, [
                'data_class' => get_class($row),
                'property_path' => sprintf('[%s]', $index),
            ]);
            $rowFormBuilder = $formBuilder->get($index);

            foreach ($editableColumns as $column) {
                $rowFormBuilder->add($column->getName(), $this->getFormTypeName(), ['inherit_data' => true]);
                $cellFormBuilder = $rowFormBuilder->get($column->getName());

                $columnTypeExtensions = $dataGrid->getFactory()->getColumnTypeExtensions($column->getType());
                foreach ($columnTypeExtensions as $columnTypeExtension) {
                    if (!$columnTypeExtension instanceof CellFormBuilder) {
                        continue;
                    }

                    $columnTypeExtension->buildCellForm($cellFormBuilder, $column);
                }
            }
        }

        $this->forms[$dataGrid->getName()] = $formBuilder->getForm();
    }

    public function handleDataGridForm(DataGrid $dataGrid, Request $request): bool
    {
        if (!array_key_exists($dataGrid->getName(), $this->forms)) {
            throw new \RuntimeException(sprintf(
                'DataGrid "%s" does not have an associated form builder',
                $dataGrid->getName()
            ));
        }

        $dataGridForm = $this->forms[$dataGrid->getName()];
        if (!$request->request->has($dataGrid->getName())) {
            return false;
        }

        $submittedData = $request->request->get($dataGrid->getName());
        foreach ($dataGridForm->all() as $index => $rowForm) {
            if (!isset($submittedData[$index])) {
                $dataGridForm->remove($index);
                continue;
            }

            foreach ($rowForm->all() as $columnName => $cellForm) {
                if (!isset($submittedData[$index][$columnName])) {
                    $rowForm->remove($columnName);
                }
            }
        }

        $dataGridForm->handleRequest($request);

        return $dataGridForm->isSubmitted() && $dataGridForm->isValid();
    }

    public function getCellForm(CellViewInterface $cellView): FormInterface
    {
        if (!array_key_exists($cellView->getDataGridName(), $this->forms)) {
            throw new \RuntimeException(sprintf(
                'DataGrid "%s" does not have an associated form builder',
                $cellView->getDataGridName()
            ));
        }

        $index = $cellView->getIndex();
        if (!$this->forms[$cellView->getDataGridName()]->has($index)) {
            throw new \RuntimeException(sprintf(
                'DataGrid "%s" does not have a row with index "%s"',
                $cellView->getDataGridName(),
                $index
            ));
        }

        $columnName = $cellView->getName();
        if (!$this->forms[$cellView->getDataGridName()]->get($index)->has($columnName)) {
            throw new \RuntimeException(sprintf(
                'DataGrid "%s" does not have a column "%s"',
                $cellView->getDataGridName(),
                $columnName
            ));
        }

        return $this->forms[$cellView->getDataGridName()]->get($index)->get($columnName);
    }

    private function getFormTypeName(): string
    {
        return $this->isSymfony3() ? FormType::class : 'form';
    }

    private function isSymfony3(): bool
    {
        return method_exists(AbstractType::class, 'getBlockPrefix');
    }

    /**
     * @param DataGridInterface $dataGrid
     * @return ColumnInterface[]
     */
    private function getEditableColumns(DataGridInterface $dataGrid): array
    {
        return array_filter(
            $dataGrid->getColumns(),
            function (ColumnInterface $column): bool {
                return $column->hasOption('editable') && $column->getOption('editable');
            }
        );
    }
}
