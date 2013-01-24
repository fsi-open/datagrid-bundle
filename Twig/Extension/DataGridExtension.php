<?php

/*
 * This file is part of the FSi Component package.
*
* (c) Norbert Orzechowicz <norbert@fsi.pl>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace FSi\Bundle\DataGridBundle\Twig\Extension;

use FSi\Component\DataGrid\DataGridViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;
use FSi\Component\DataGrid\Column\CellViewInterface;


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

    /**
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'datagrid';
    }

    /**
     * {@inheritDoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->template = $this->environment->loadTemplate($this->template);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'datagrid_widget' => new \Twig_Function_Method($this, 'datagrid', array('is_safe' => array('html'))),
            'datagrid_header_widget' =>  new \Twig_Function_Method($this, 'datagridHeader', array('is_safe' => array('html'))),
            'datagrid_rowset_widget' =>  new \Twig_Function_Method($this, 'datagridRowset', array('is_safe' => array('html'))),
            'datagrid_column_header_widget' =>  new \Twig_Function_Method($this, 'datagridColumnHeader', array('is_safe' => array('html'))),
            'datagrid_column_cell_widget' =>  new \Twig_Function_Method($this, 'datagridColumnCell', array('is_safe' => array('html'))),
            'datagrid_attributes_widget' =>  new \Twig_Function_Method($this, 'datagridAttributes', array('is_safe' => array('html')))
        );
    }

    /**
     * @param DataGridViewInterface $view
     * @param array $options
     */
    public function datagrid(DataGridViewInterface $view, $options = array())
    {
        $wrapperAttributes = (isset($options['wrapper_attributes']) && is_array($options['wrapper_attributes']))
            ? $options['wrapper_attributes']
            : array();

        if (isset($wrapperAttributes['wrapper_attributes']['id'])) {
            $wrapperAttributes['wrapper_attributes']['id'] = $view->getName() . $wrapperAttributes['wrapper_attributes']['id'];
        }

        return $this->template->renderBlock('datagrid', array(
            'datagrid' => $view,
            'wrapper_attributes' => $wrapperAttributes
        ));
    }

    /**
     * Render header row in datagrid.
     *
     * @param DataGridViewInterface $view
     * @param array $vars
     */
    public function datagridHeader(DataGridViewInterface $view, array $vars = array())
    {
        return $this->template->renderBlock('datagrid_header', array(
            'headers' => $view->getColumns(),
            'vars' => $vars
        ));
    }

    /**
     * Render column header.
     *
     * @param HeaderViewInterface $view
     * @param array $vars - additional values passed to block rendering context
     * under 'vars' key.
     */
    public function datagridColumnHeader(HeaderViewInterface $view, array $vars = array())
    {
        $headerAttributes = $view->getAttribute('header');

        $header = array(
            'tag' => $headerAttributes['label_tag'],
            'label' => $view->getLabel(),
            'column_name' => $view->getName(),
            'column_type' => $view->getType(),
            'wrapper_attributes' => $headerAttributes['wrapper_attributes'],
            'label_attributes' => $headerAttributes['label_attributes'],
            'translation_domain' => $view->getAttribute('translation_domain')
        );

        return $this->template->renderBlock('datagrid_column_header', array(
            'header' => $header,
            'vars' => $vars
        ));
    }

    /**
     * Render DataGrid rows except header.
     *
     * @param DataGridViewInterface $view
     * @param array $vars
     */
    public function datagridRowset(DataGridViewInterface $view, array $vars = array())
    {
        $rowset = array();
        foreach ($view as $index => $row) {
            $cells = array();
            foreach ($row as $cell) {
                $cells[] = $cell;
            }

            $rowset[] = array(
                'index'=> $index,
                'cells' => $cells
            );
        }

        return $this->template->renderBlock('datagrid_rowset', array(
            'rowset' => $rowset,
            'grid_name' => $view->getName(),
            'vars' => $vars
        ));
    }

    /**
     * Render column cell.
     *
     * @param CellViewInterface $view
     */
    public function datagridColumnCell(CellViewInterface $view, array $vars = array())
    {
        $cellAttributes = $view->getAttribute('cell');
        $form = null;

        if ($view->hasAttribute('form')) {
            $formAttributes = $cellAttributes['form'];

            $form = array(
                'element' => $view->getAttribute('form'),
                'wrapper_tag' => $formAttributes['wrapper_tag'],
                'wrapper_attributes' => $formAttributes['wrapper_attributes'],
                'submit' => $formAttributes['submit'],
                'submit_attributes' => $formAttributes['submit_attributes']
            );
        }

        $cell = array(
            'value' => $view->getValue(),
            'tag' => $cellAttributes['value_tag'],
            'column_type' => $view->getType(),
            'column_name' => $view->getName(),
            'form' => $form,
            'wrapper_attributes' => $cellAttributes['wrapper_attributes'],
            'value_attributes' => $cellAttributes['value_attributes'],
            'translation_domain' => $view->getAttribute('translation_domain')
        );

        if ($view->getType() == 'action') {
            $anchorsAttributes =  $view->getAttribute('anchors');
            $cell['anchors'] = $anchorsAttributes;
        }

        return $this->template->renderBlock('datagrid_column_cell', array(
            'cell' => $cell,
            'vars' => $vars
        ));
    }

    /**
     * Render html element attributes.
     * This function is only for internal use.
     *
     * @param array $attributes
     * @param string $translationDomain
     */
    public function datagridAttributes(array $attributes, $translationDomain = null)
    {
        return $this->template->renderBlock('datagrid_attributes', array(
            'attributes' => $attributes,
            'translation_domain' => $translationDomain
        ));
    }
}