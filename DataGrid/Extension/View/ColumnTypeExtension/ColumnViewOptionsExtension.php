<?php

/*
 * This file is part of the FSi Component package.
 *
 * (c) Norbert Orzechowicz <norbert@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\Column\ColumnViewInterface;
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;

class ColumnViewOptionsExtension extends ColumnAbstractTypeExtension
{
    private $headerOptions = array(
        'wrapper_attributes' => array(),
        'label_attributes' => array(),
        'label_tag' => 'span'
    );

    private $cellOptions = array(
        'wrapper_attributes' => array(),
        'value_attributes' => array(),
        'value_tag' => 'div'
    );

    private $formOptions = array(
        'wrapper_attributes' => array(),
        'wrapper_tag' => 'div',
        'submit' => true,
        'submit_attributes' => array()
    );

    public function buildCellView(ColumnTypeInterface $column, CellViewInterface $view)
    {
        $this->validateCellOptions($column);

        $dataGridName = $column->getDataGrid()->getName();
        $id = $dataGridName . '_' . $view->getAttribute('row') . '_' . $column->getName();

        $cellOptions = $column->getOption('cell');
        $cellOptions = array_merge($this->cellOptions, $cellOptions);

        if (isset($cellOptions['wrapper_attributes']['id'])) {
            $cellOptions['wrapper_attributes']['id'] = $id . $cellOptions['wrapper_attributes']['id'];
        }

        if (isset($cellOptions['value_attributes']['id'])) {
            $cellOptions['value_attributes']['id'] = $id . $cellOptions['value_attributes']['id'];
        }

        if ($column->hasOption('editable')) {
            $this->validateCellFormOptions($column);

            $cellOptions['form'] = array_merge(
                $this->formOptions,
                (isset($cellOptions['form']))
                    ? $cellOptions['form']
                    : array()
            );

            if (isset($cellOptions['form']['wrapper_attributes']['id'])) {
                $cellOptions['form']['wrapper_attributes']['id'] = $id . $cellOptions['form']['wrapper_attributes']['id'];
            }

        }

        $view->setAttribute('cell', $cellOptions);
    }

    public function buildHeaderView(ColumnTypeInterface $column, HeaderViewInterface $view)
    {
        $this->validateHeaderOptions($column);

        $dataGridName = $column->getDataGrid()->getName();

        $headerOptions = $column->getOption('header');
        $headerOptions = array_merge($this->headerOptions, $headerOptions);

        $view->setAttribute('header', $headerOptions);
    }

    public function getExtendedColumnTypes()
    {
        return array(
            'text',
            'datetime',
            'number',
            'money',
            'gedmo.tree',
            'entity',
            'action'
        );
    }

    public function getDefaultOptionsValues(ColumnTypeInterface $column)
    {
        $options = array(
            'header' => $this->headerOptions,
            'cell' => $this->cellOptions
        );

        if ($column->hasOption('editable')) {
            $options['cell']['form'] = $this->formOptions;
        }

        return $options;
    }

    public function getRequiredOptions(ColumnTypeInterface $column)
    {
        return array('header', 'cell');
    }

    public function getAvailableOptions(ColumnTypeInterface $column)
    {
        return array('header', 'cell');
    }

    private function validateCellOptions(ColumnTypeInterface $column)
    {
        $cellOptions = ($column->hasOption('cell'))
            ? $column->getOption('cell')
            : null;

        if (!isset($cellOptions)) {
            return;
        }

        if (!is_array($cellOptions)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Option "cell" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($cellOptions['wrapper_attributes']) && !is_array($cellOptions['wrapper_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "wrapper_attributes" in option "cell" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($cellOptions['label_attributes']) && !is_array($cellOptions['label_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "label_attributes" in option "cell" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($cellOptions['label_tag']) && !is_string($cellOptions['label_tag'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "label_tag" in option "cell" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        foreach ($cellOptions as $optionName => $optionValue) {
            if ($optionName == 'form') {
                continue;
            }

            if (!array_key_exists($optionName, $this->cellOptions)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key "%s" in option "cell" in column: "%s" is not available. ' .
                        'Available cell options are "%s"',
                        $optionName,
                        $column->getName(),
                        implode(', ',array_keys(array_merge(
                            $this->cellOptions,
                            array('form' => null)
                        )))
                    )
                );
            }
        }
    }

    private function validateCellFormOptions(ColumnTypeInterface $column)
    {
        $cellOptions = ($column->hasOption('cell'))
            ? $column->getOption('cell')
            : null;

        if (!isset($cellOptions['form'])) {
            return;
        }

        $formOptions = $cellOptions['form'];

        if (!is_array($formOptions)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "form" in option "cell" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($formOptions['wrapper_attributes']) && !is_array($formOptions['wrapper_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "wrapper_attributes" in option "cell["form"]" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($formOptions['wrapper_tag']) && !is_string($formOptions['wrapper_tag'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "wrapper_tag" in option "cell["form"]" in column: "%s" must be a valid string.',
                    $column->getName()
                )
            );
        }

        if (isset($formOptions['submit_attributes']) && !is_array($formOptions['submit_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "submit_attributes" in option "cell["form"]" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($formOptions['submit']) && !is_bool($formOptions['submit'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "submit" in option "cell["form"]" in column: "%s" must be a valid boolean value.',
                    $column->getName()
                )
            );
        }

        if (isset($formOptions['submit_attributes']) && !is_array($formOptions['submit_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "submit_attributes" in option "cell["form"]" in column: "%s" must be a valid array.',
                    $column->getName()
                )
            );
        }

        if (isset($formOptions['submit_attributes']['type'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "submit_attributes" in option "cell["form"]" in column: "%s" can\'t contain "type" attribute.',
                    $column->getName()
                )
            );
        }

        foreach ($formOptions as $optionName => $optionValue) {
            if (!array_key_exists($optionName, $this->formOptions)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key "%s" in option "cell["form"]" in column: "%s" is not available. ' .
                        'Available "cell["form"]" options are "%s"',
                        $optionName,
                        $column->getName(),
                        implode(', ',array_keys($this->formOptions))
                    )
                );
            }
        }

    }

    private function validateHeaderOptions(ColumnTypeInterface $column)
    {
        $headerOptions = ($column->hasOption('header'))
            ? $column->getOption('header')
            : null;

        if (!isset($headerOptions)) {
            return;
        }

        if (!is_array($headerOptions)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Option "header" in column: "%s" must be an array.',
                    $column->getName()
                )
            );
        }

        if (isset($headerOptions['wrapper_attributes']) && !is_array($headerOptions['wrapper_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "wrapper_attributes" in option "header" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($headerOptions['label_attributes']) && !is_array($headerOptions['label_attributes'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "label_attributes" in option "header" in column: "%s" must be a array.',
                    $column->getName()
                )
            );
        }

        if (isset($headerOptions['label_tag']) && !is_string($headerOptions['label_tag'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key "label_tag" in option "header" in column: "%s" must be a valid string.',
                    $column->getName()
                )
            );
        }

        foreach ($headerOptions as $optionName => $optionValue) {
            if (!array_key_exists($optionName, $this->headerOptions)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key "%s" in option "header" in column: "%s" is not available. ' .
                        'Available "header" options are "%s"',
                        $optionName,
                        $column->getName(),
                        implode(', ',array_keys($this->headerOptions))
                    )
                );
            }
        }
    }
}