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
use FSi\Bundle\DataGridBundle\Twig\TokenParser\DataGridThemeTokenParser;

class DataGridExtension extends \Twig_Extension
{
    /**
     * Default theme key in themes array.
     */
    const DEFAULT_THEME = 'default_theme';

    /**
     * @var array
     */
    private $themes;

    /**
     * @var array
     */
    private $themesVars;

    /**
     * @var Twig_TemplateInterface
     */
    private $baseTemplate;

    /**
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @param string $template
     */
    public function __construct($template)
    {
        $this->themes = array();
        $this->themesVars = array();
        $this->baseTemplate = $template;
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
        $this->themes[self::DEFAULT_THEME] = $this->environment->loadTemplate($this->baseTemplate);
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
            'datagrid_column_cell_form_widget' =>  new \Twig_Function_Method($this, 'datagridColumnCellForm', array('is_safe' => array('html'))),
            'datagrid_attributes_widget' =>  new \Twig_Function_Method($this, 'datagridAttributes', array('is_safe' => array('html')))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return array(
            new DataGridThemeTokenParser(),
        );
    }

    /**
     * Set theme for specific DataGrid.
     * Theme is nothing more than twig template that contains block required to render
     * DataGrid.
     *
     * @param DataGridViewInterface $dataGrid
     * @param $theme
     * @param array $vars
     */
    public function setTheme(DataGridViewInterface $dataGrid, $theme, array $vars = array())
    {
        $this->themes[$dataGrid->getName()] = ($theme instanceof \Twig_TemplateInterface)
            ? $theme
            : $this->environment->loadTemplate($theme);
        $this->themesVars[$dataGrid->getName()] = $vars;
    }

    /**
     * @param DataGridViewInterface $view
     * @param array $options
     */
    public function datagrid(DataGridViewInterface $view)
    {
        $templates = $this->getTemplates($view);
        $blockNames = array(
            'datagrid_' . $view->getName(),
            'datagrid',
        );

        ob_start();

        foreach ($blockNames as $blockName) {
            foreach ($templates as $template) {
                if ($template->hasBlock($blockName)) {
                    $template->displayBlock($blockName, array(
                        'datagrid' => $view,
                        'vars' => $this->getVars($view)
                    ));

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
    }

    /**
     * Render header row in datagrid.
     *
     * @param DataGridViewInterface $view
     * @param array $vars
     */
    public function datagridHeader(DataGridViewInterface $view, array $vars = array())
    {
        $templates = $this->getTemplates($view);
        $blockNames = array(
            'datagrid_' . $view->getName() . '_header',
            'datagrid_header',
        );

        ob_start();

        foreach ($blockNames as $blockName) {
            foreach ($templates as $template) {
                if ($template->hasBlock($blockName)) {
                    $template->displayBlock($blockName, array(
                        'headers' => $view->getColumns(),
                        'vars' => array_merge(
                            $this->getVars($view),
                            $vars
                        )
                    ));

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
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

        $dataGridView = $view->getDataGridView();
        $templates = $this->getTemplates($dataGridView);
        $blockNames = array(
            'datagrid_' . $dataGridView->getName() . '_column_name_' . $view->getName() . '_header',
            'datagrid_' . $dataGridView->getName() . '_column_type_' . $view->getType() . '_header',
            'datagrid_column_name_' . $view->getName() . '_header',
            'datagrid_column_type_' . $view->getType() . '_header',
            'datagrid_column_header',
        );

        ob_start();

        foreach ($blockNames as $blockName) {
            foreach ($templates as $template) {
                if ($template->hasBlock($blockName)) {
                    $template->displayBlock($blockName, array(
                        'header' => $view,
                        'translation_domain' => $view->getAttribute('translation_domain'),
                        'vars' => array_merge(
                            $this->getVars($view->getDataGridView()),
                            $vars
                        )
                    ));

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
    }

    /**
     * Render DataGrid rows except header.
     *
     * @param DataGridViewInterface $view
     * @param array $vars
     */
    public function datagridRowset(DataGridViewInterface $view, array $vars = array())
    {
        $templates = $this->getTemplates($view);
        $blockNames = array(
            'datagrid_' . $view->getName() . '_rowset',
            'datagrid_rowset',
        );

        ob_start();

        foreach ($blockNames as $blockName) {
            foreach ($templates as $template) {
                if ($template->hasBlock($blockName)) {
                    $template->displayBlock($blockName, array(
                        'datagrid' => $view,
                        'vars' => array_merge(
                            $this->getVars($view),
                            $vars
                        )
                    ));

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
    }

    /**
     * Render column cell.
     *
     * @param CellViewInterface $view
     */
    public function datagridColumnCell(CellViewInterface $view, array $vars = array())
    {
        $dataGridView = $view->getDataGridView();
        $templates = $this->getTemplates($dataGridView);
        $blockNames = array(
            'datagrid_' . $dataGridView->getName() . '_column_name_' . $view->getName() . '_cell',
            'datagrid_' . $dataGridView->getName() . '_column_type_' . $view->getType() . '_cell',
            'datagrid_column_name_' . $view->getName() . '_cell',
            'datagrid_column_type_' . $view->getType() . '_cell',
            'datagrid_column_cell',
        );

        ob_start();

        foreach ($blockNames as $blockName) {
            foreach ($templates as $template) {
                if ($template->hasBlock($blockName)) {
                    $template->displayBlock($blockName, array(
                        'cell' => $view,
                        'row_index' => $view->getAttribute('row'),
                        'datagrid_name' => $dataGridView->getName(),
                        'translation_domain' => $view->getAttribute('translation_domain'),
                        'vars' => array_merge(
                            $this->getVars($dataGridView),
                            $vars
                        )
                    ));

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
    }

    /**
     * Render column form if exists.
     *
     * @param CellViewInterface $view
     * @param array $vars
     */
    public function datagridColumnCellForm(CellViewInterface $view, array $vars = array())
    {
        if (!$view->hasAttribute('form')) {
            return;
        }

        $dataGridView = $view->getDataGridView();
        $templates = $this->getTemplates($dataGridView);
        $blockNames = array(
            'datagrid_' . $dataGridView->getName() . '_column_name_' . $view->getName() . '_cell_form',
            'datagrid_' . $dataGridView->getName() . '_column_type_' . $view->getType() . '_cell_form',
            'datagrid_column_name_' . $view->getName() . '_cell_form',
            'datagrid_column_type_' . $view->getType() . '_cell_form',
            'datagrid_column_cell_form',
        );

        ob_start();

        foreach ($blockNames as $blockName) {
            foreach ($templates as $template) {
                if ($template->hasBlock($blockName)) {
                    $template->displayBlock($blockName, array(
                        'form' => $view->getAttribute('form'),
                        'vars' => array_merge(
                            $this->getVars($view->getDataGridView()),
                            $vars
                        )
                    ));

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
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
        $attrs = array();

        foreach ($attributes as $attributeName => $attributeValue) {
            if ($attributeName == 'title') {
                $attrs[] = $attributeName . '="' . $this->environment->getExtension('translator')->trans($attributeValue, array(), $translationDomain) . '"';
                continue;
            }

            $attrs[] = $attributeName . '="' . $attributeValue . '"';
        }

        return ' ' . implode(' ', $attrs);
    }

    /**
     * Return list of templates that might be useful to render DataGridView.
     * Always the last template will be default one.
     *
     * @param DataGridViewInterface $dataGrid
     * @return array
     */
    private function getTemplates(DataGridViewInterface $dataGrid)
    {
        $templates = array();

        if (isset($this->themes[$dataGrid->getName()])) {
            $templates[] = $this->themes[$dataGrid->getName()];
        }

        $templates[] = $this->themes[self::DEFAULT_THEME];

        return $templates;
    }

    /**
     * Return vars passed to theme. Those vars will be added to block context.
     *
     * @param DataGridViewInterface $dataGrid
     * @return array
     */
    private function getVars(DataGridViewInterface $dataGrid)
    {
        if (isset($this->themesVars[$dataGrid->getName()])) {
            return $this->themesVars[$dataGrid->getName()];
        }

        return array();
    }
}