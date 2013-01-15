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
            'datagrid_widget' => new \Twig_Function_Method($this, 'datagrid', array('is_safe' => array('html'))),
            'datagrid_header_widget' =>  new \Twig_Function_Method($this, 'datagridHeader', array('is_safe' => array('html'))),
            'datagrid_rowset_widget' =>  new \Twig_Function_Method($this, 'datagridRowset', array('is_safe' => array('html'))),
            'datagrid_render_attributes' =>  new \Twig_Function_Method($this, 'datagridAttributes', array('is_safe' => array('html')))
        );
    }

    public function datagrid(DataGridViewInterface $view, $options = array())
    {
        $style = $this->getRenderingStyle($options);

        $headerOptions = array_merge(
            array(
                'style' => $style
            ),
            (isset($options['header_options']) && is_array($options['header_options']))
                ? $options['header_options']
                : array()
        );

        $rowsetOptions = array_merge(
            array(
                'style' => $style
            ),
            (isset($options['rowset_options']) && is_array($options['rowset_options']))
                ? $options['rowset_options']
                : array()
        );

        $wrapperAttributes = (isset($options['wrapper_attributes']) && is_array($options['wrapper_attributes']))
            ? $options['wrapper_attributes']
            : array();

        return $this->template->renderBlock('datagrid_' . $style, array(
            'datagrid' => $view,
            'header_options' => $headerOptions,
            'rowset_options' => $rowsetOptions,
            'wrapper_attributes' => $wrapperAttributes
        ));
    }

    public function datagridHeader(DataGridViewInterface $view, $options = array())
    {
        $style = $this->getRenderingStyle($options);

        $columns = $view->getColumns();
        $columnsView = array();

        foreach ($columns as $column) {
            $headerAttributes = $column->getAttribute('header');

            $columnsView[] = array(
                'tag' => $headerAttributes['label_tag'],
                'label' => $column->getLabel(),
                'wrapper_attributes' => $headerAttributes['wrapper_attributes'],
                'label_attributes' => $headerAttributes['label_attributes'],
                'translation_domain' => $column->getAttribute('translation_domain')
            );
        }

        return $this->template->renderBlock('datagrid_header_' . $style, array(
            'columns' => $columnsView
        ));
    }

    public function datagridRowset(DataGridViewInterface $view, $options = array())
    {
        $style = $this->getRenderingStyle($options);

        $rowset = array();
        foreach ($view as $index => $row) {
            $cells = array();

            foreach ($row as $cell) {
                $cellAttributes = $cell->getAttribute('cell');
                $form = null;

                if ($cell->hasAttribute('form')) {
                    $formAttributes = $cellAttributes['form'];

                    $form = array(
                        'element' => $cell->getAttribute('form'),
                        'wrapper_tag' => $formAttributes['wrapper_tag'],
                        'wrapper_attributes' => $formAttributes['wrapper_attributes'],
                        'submit' => $formAttributes['submit'],
                        'submit_attributes' => $formAttributes['submit_attributes']
                    );
                }

                $cellViewAttributes = array(
                    'value' => $cell->getValue(),
                    'tag' => $cellAttributes['value_tag'],
                    'type' => $cell->getType(),
                    'form' => $form,
                    'wrapper_attributes' => $cellAttributes['wrapper_attributes'],
                    'value_attributes' => $cellAttributes['value_attributes'],
                    'translation_domain' => $cell->getAttribute('translation_domain')
                );

                if ($cell->getType() == 'action') {
                    $anchorsAttributes =  $cell->getAttribute('anchors');
                    $cellViewAttributes['anchors'] = $anchorsAttributes;
                }

                $cells[] = $cellViewAttributes;
            }

            $rowset[] = array(
                'index'=> $index,
                'cells' => $cells
            );
        }

        return $this->template->renderBlock('datagrid_rowset_' . $style, array(
            'rowset' => $rowset,
            'grid_name' => $view->getName()
        ));
    }

    public function datagridAttributes(array $attributes, $translationDomain = null)
    {
        return $this->template->renderBlock('datagrid_render_attributes', array(
            'attributes' => $attributes,
            'translation_domain' => $translationDomain
        ));
    }

    private function getRenderingStyle($options)
    {
        if (!isset($options['style'])) {
            return 'table';
        }

        $styles = array('table'); //TODO: add div render style
        if (in_array($options['style'], $styles, true)) {
            return strtolower($options['style']);
        }

        return 'table';
    }
}