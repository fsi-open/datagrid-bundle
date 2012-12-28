<?php

namespace FSi\Bundle\DataGridBundle\Twig\Extension;

use FSi\Component\DataGrid\DataGridViewInterface;

class DataGridExtension extends \Twig_Extension
{
    /**
     * @var Twig_TemplateInterface
     */
    private $template;

    /**
     * @var Twig_Environment
     */
    private $environment;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function getName()
    {
        return 'datagrid';
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->template = $this->environment->loadTemplate($this->template);
    }

    public function getFunctions()
    {
        return array(
            'datagrid_header' =>  new \Twig_Function_Method($this, 'datagridHeader', array('is_safe' => array('html'))),
            'datagrid_rowset' =>  new \Twig_Function_Method($this, 'datagridRowset', array('is_safe' => array('html'))),
            'datagrid_render_attributes' =>  new \Twig_Function_Method($this, 'datagridAttributes', array('is_safe' => array('html')))
        );
    }

    public function datagridHeader(DataGridViewInterface $view, $style = 'table')
    {
        $style = $this->getRenderingStyle($style);

        $columns = $view->getColumns();
        $columnsView = array();

        foreach ($columns as $column) {
            $headerAttributes = $column->getAttribute('header');

            $columnsView[] = array(
                'tag' => $headerAttributes['label_tag'],
                'label' => $column->getLabel(),
                'wrapper_attributes' => $headerAttributes['wrapper_attributes'],
                'label_attributes' => $headerAttributes['label_attributes']
            );
        }

        return $this->template->renderBlock('datagrid_header_' . $style, array(
            'style' => $style,
            'columns' => $columnsView
        ));
    }

    public function datagridRowset(DataGridViewInterface $view, $style = 'table')
    {
        $style = $this->getRenderingStyle($style);

        $rowset = array();
        foreach ($view as $index => $row) {
            $cells = array();
            $wrapperAttributes = array();

            foreach ($row as $cell) {
                $cellAttributes = $cell->getAttribute('cell');

                $form = null;
                if ($cell->hasAttribute('form')) {
                    $formAttributes = $cellAttributes['form'];
                    $formWrapperAttributes = array();

                    $form = array(
                        'element' => $cell->getAttribute('form'),
                        'wrapper_tag' => $formAttributes['wrapper_tag'],
                        'wrapper_attributes' => $formAttributes['wrapper_attributes'],
                        'submit' => $formAttributes['submit'],
                        'submit_attributes' => $formAttributes['submit_attributes']
                    );
                }

                $cells[] = array(
                    'value' => $cell->getValue(),
                    'tag' => $cellAttributes['value_tag'],
                    'type' => $cell->getType(),
                    'form' => $form,
                    'wrapper_attributes' => $cellAttributes['wrapper_attributes'],
                    'value_attributes' => $cellAttributes['value_attributes']
                );
            }

            $rowset[] = array(
                'index'=> $index,
                'cells' => $cells
            );
        }

        return $this->template->renderBlock('datagrid_rowset_' . $style, array(
            'style' => $style,
            'rowset' => $rowset
        ));
    }

    public function datagridAttributes(array $attributes)
    {
        return $this->template->renderBlock('datagrid_attributes', array(
            'attributes' => $attributes
        ));
    }

    private function getRenderingStyle($style)
    {
        $styles = array('table'); //TODO: add div render style
        if (in_array($style, $styles, true)) {
            return $style;
        }

        return 'table';
    }
}