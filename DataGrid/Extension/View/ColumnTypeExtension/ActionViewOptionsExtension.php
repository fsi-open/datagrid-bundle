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
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;

class ActionViewOptionsExtension extends ColumnAbstractTypeExtension
{
    private $anchorOptions = array(
        'attributes' => array()
    );

    public function buildCellView(ColumnTypeInterface $column, CellViewInterface $view)
    {
        $this->validateAnchorOptions($column);
        $dataGridName = $column->getDataGrid()->getName();
        $id = $dataGridName . '_' . $view->getAttribute('row') . '_' . $column->getName();

        $anchorsOptions = $column->getOption('anchors');
        foreach ($anchorsOptions as $action => &$options) {
            $actionId = $id . '_' . $action;
            $options = array_merge($this->anchorOptions, $options);

            if (isset($options['attributes']['id'])) {
                $options['attributes']['id'] = $actionId . $options['attributes']['id'];
            }
        }

        $actions = $column->getOption('actions');
        foreach ($actions as $name => $action) {
            if (!array_key_exists($name, $anchorsOptions)) {
                $anchorsOptions[$name] = $this->anchorOptions;
            }
        }

        $view->setAttribute('anchors', $anchorsOptions);
    }

    public function getExtendedColumnTypes()
    {
        return array(
            'action'
        );
    }

    public function getDefaultOptionsValues(ColumnTypeInterface $column)
    {
        return array(
            'anchors' => array()
        );
    }

    public function getAvailableOptions(ColumnTypeInterface $column)
    {
        return array('anchors');
    }

    private function validateAnchorOptions(ColumnTypeInterface $column)
    {
        $actions = $column->getOption('actions');
        $anchors  = $column->getOption('anchors');

        foreach ($anchors as $anchorName => $anchor)  {
            if (!array_key_exists($anchorName, $actions)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Each "anchor" option key must exists in "action" option. Available actions: "%s"',
                        implode(", " , array_keys($actions))
                    )
                );
            }

            if (!is_array($anchor)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Key "%s" in option "anchor" in column: "%s" must be a array.',
                        $anchorName,
                        $column->getName()
                    )
                );
            }

            if (isset($anchor['attributes'])) {
                if (!is_array($anchor['attributes'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Key "attributes" in option "anchor["%s"]" in column: "%s" must be a valid array.',
                            $anchorName,
                            $column->getName()
                        )
                    );
                }

                foreach ($anchor['attributes'] as $attrName => $attrValue) {
                    if ($attrName == 'href') {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'Key "attributes" in option "anchor["%s"]" in column: "%s" can\'t contain "href" attribute.',
                                $anchorName,
                                $column->getName()
                            )
                        );
                    }
                }
            }

            foreach ($anchor as $optionName => $optionValue) {
                if (!array_key_exists($optionName, $this->anchorOptions)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Key "%s" in option "anchor["%s"]" in column: "%s" is not available. ' .
                            'Available "anchor["form"]" options are "%s"',
                            $optionName,
                            $anchorName,
                            $column->getName(),
                            implode(', ',array_keys($this->anchorOptions))
                        )
                    );
                }
            }
        }
    }
}