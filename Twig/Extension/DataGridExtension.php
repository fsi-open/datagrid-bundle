<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Twig\Extension;

use FSi\Bundle\DataGridBundle\Twig\TokenParser\DataGridThemeTokenParser;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;
use FSi\Component\DataGrid\DataGridViewInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DataGridExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var \Twig_Template[]
     */
    private $themes;

    /**
     * @var array
     */
    private $themesVars;

    /**
     * @var \Twig_Template[]
     */
    private $baseThemes;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string[] $themes
     * @param TranslatorInterface $translator
     */
    public function __construct(array $themes, TranslatorInterface $translator)
    {
        $this->themes = [];
        $this->themesVars = [];
        $this->baseThemes = $themes;
        $this->translator = $translator;
    }

    public function getName(): string
    {
        return 'datagrid';
    }

    public function initRuntime(\Twig_Environment $environment): void
    {
        $this->environment = $environment;
        for ($i = count($this->baseThemes) - 1; $i >= 0; $i--) {
            $this->baseThemes[$i] = $this->environment->loadTemplate($this->baseThemes[$i]);
        }
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('datagrid_widget', [$this, 'datagrid'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_header_widget', [$this, 'datagridHeader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_rowset_widget', [$this, 'datagridRowset'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_column_header_widget', [$this, 'datagridColumnHeader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_column_cell_widget', [$this, 'datagridColumnCell'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_column_cell_form_widget', [$this, 'datagridColumnCellForm'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_column_type_action_cell_action_widget', [$this, 'datagridColumnActionCellActionWidget'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('datagrid_attributes_widget', [$this, 'datagridAttributes'], ['is_safe' => ['html']])
        ];
    }

    public function getTokenParsers(): array
    {
        return [
            new DataGridThemeTokenParser(),
        ];
    }

    /**
     * @param DataGridViewInterface $dataGrid
     * @param \Twig_Template|string $theme
     * @param array $vars
     */
    public function setTheme(DataGridViewInterface $dataGrid, $theme, array $vars = []): void
    {
        $this->themes[$dataGrid->getName()] = ($theme instanceof \Twig_Template)
            ? $theme
            : $this->environment->loadTemplate($theme);

        $this->themesVars[$dataGrid->getName()] = $vars;
    }

    /**
     * @param \Twig_Template[]|\Twig_Template|string $theme
     */
    public function setBaseTheme($theme): void
    {
        $themes = is_array($theme) ? $theme : [$theme];

        $this->baseThemes = [];
        foreach ($themes as $theme) {
            $this->baseThemes[] = ($theme instanceof \Twig_Template)
                ? $theme
                : $this->environment->loadTemplate($theme);
        }
    }

    public function datagrid(DataGridViewInterface $view): string
    {
        $blockNames = [
            'datagrid_' . $view->getName(),
            'datagrid',
        ];

        $context = [
            'datagrid' => $view,
            'vars' => $this->getDataGridVars($view->getName())
        ];

        return $this->renderTheme($view->getName(), $context, $blockNames);
    }

    public function datagridHeader(DataGridViewInterface $view, array $vars = []): string
    {
        $blockNames = [
            'datagrid_' . $view->getName() . '_header',
            'datagrid_header',
        ];

        $context = [
            'headers' => $view->getHeaders(),
            'vars' => array_merge(
                $this->getDataGridVars($view->getName()),
                $vars
            )
        ];

        return $this->renderTheme($view->getName(), $context, $blockNames);
    }

    public function datagridColumnHeader(HeaderViewInterface $view, array $vars = []): string
    {
        $blockNames = [
            'datagrid_' . $view->getDataGridName() . '_column_name_' . $view->getName() . '_header',
            'datagrid_' . $view->getDataGridName() . '_column_type_' . $view->getType() . '_header',
            'datagrid_column_name_' . $view->getName() . '_header',
            'datagrid_column_type_' . $view->getType() . '_header',
            'datagrid_' . $view->getDataGridName() . '_column_header',
            'datagrid_column_header',
        ];

        $context = [
            'header' => $view,
            'translation_domain' => $view->getAttribute('translation_domain'),
            'vars' => array_merge(
                $this->getDataGridVars($view->getDataGridName()),
                $vars
            )
        ];

        return $this->renderTheme($view->getDataGridName(), $context, $blockNames);
    }

    public function datagridRowset(DataGridViewInterface $view, array $vars = []): string
    {
        $blockNames = [
            'datagrid_' . $view->getName() . '_rowset',
            'datagrid_rowset',
        ];

        $context = [
            'datagrid' => $view,
            'vars' => array_merge($this->getDataGridVars($view->getName()), $vars)
        ];

        return $this->renderTheme($view->getName(), $context, $blockNames);
    }

    public function datagridColumnCell(CellViewInterface $view, array $vars = []): string
    {
        $blockNames = [
            'datagrid_' . $view->getDataGridName() . '_column_name_' . $view->getName() . '_cell',
            'datagrid_' . $view->getDataGridName() . '_column_type_' . $view->getType() . '_cell',
            'datagrid_column_name_' . $view->getName() . '_cell',
            'datagrid_column_type_' . $view->getType() . '_cell',
            'datagrid_' . $view->getDataGridName() . '_column_cell',
            'datagrid_column_cell',
        ];

        $context = [
            'cell' => $view,
            'row_index' => $view->getAttribute('row_index'),
            'datagrid_name' => $view->getDataGridName(),
            'translation_domain' => $view->getAttribute('translation_domain'),
            'vars' => array_merge($this->getDataGridVars($view->getDataGridName()), $vars)
        ];

        return $this->renderTheme($view->getDataGridName(), $context, $blockNames);
    }

    public function datagridColumnCellForm(CellViewInterface $view, array $vars = []): string
    {
        if (!$view->hasAttribute('form')) {
            return '';
        }

        $blockNames = [
            'datagrid_' . $view->getDataGridName() . '_column_name_' . $view->getName() . '_cell_form',
            'datagrid_' . $view->getDataGridName() . '_column_type_' . $view->getType() . '_cell_form',
            'datagrid_column_name_' . $view->getName() . '_cell_form',
            'datagrid_column_type_' . $view->getType() . '_cell_form',
            'datagrid_' . $view->getDataGridName() . '_column_cell_form',
            'datagrid_column_cell_form',
        ];

        $context = [
            'form' => $view->getAttribute('form'),
            'vars' => array_merge($this->getDataGridVars($view->getDataGridName()), $vars)
        ];

        return $this->renderTheme($view->getDataGridName(), $context, $blockNames);
    }

    public function datagridColumnActionCellActionWidget(
        CellViewInterface $view,
        string $action,
        string $content,
        array $urlAttrs = [],
        array $fieldMappingValues = []
    ): string {
        $blockNames = [
            'datagrid_' . $view->getDataGridName() . '_column_type_action_cell_action_' . $action,
            'datagrid_column_type_action_cell_action_' . $action ,
            'datagrid_' . $view->getDataGridName() . '_column_type_action_cell_action',
            'datagrid_column_type_action_cell_action',
        ];

        $context = [
            'cell' => $view,
            'action' => $action,
            'content' => $content,
            'attr' => $urlAttrs,
            'translation_domain' => $view->getAttribute('translation_domain'),
            'field_mapping_values' => $fieldMappingValues
        ];

        return $this->renderTheme($view->getDataGridName(), $context, $blockNames);
    }

    public function datagridAttributes(array $attributes, ?string $translationDomain = null): string
    {
        $attrs = [];

        foreach ($attributes as $attributeName => $attributeValue) {
            if ($attributeName === 'title') {
                $attributeValue = $this->translator->trans($attributeValue, [], $translationDomain);
            }

            $attrs[] = sprintf( '%s="%s"', $attributeName, $attributeValue);
        }

        return ' ' . implode(' ', $attrs);
    }

    /**
     * @param string $dataGridName
     * @return \Twig_Template[]
     */
    private function getTemplates(string $dataGridName): array
    {
        $templates = [];

        if (isset($this->themes[$dataGridName])) {
            $templates[] = $this->themes[$dataGridName];
        }

        for ($i = count($this->baseThemes) - 1; $i >= 0; $i--) {
            $templates[] = $this->baseThemes[$i];
        }

        return $templates;
    }

    private function getDataGridVars(string $dataGridName): array
    {
        if (isset($this->themesVars[$dataGridName])) {
            return $this->themesVars[$dataGridName];
        }

        return [];
    }

    private function renderTheme(
        string $dataGridName,
        array $contextVars = [],
        array $availableBlocks = []
    ): string {
        $templates = $this->getTemplates($dataGridName);

        $contextVars = $this->environment->mergeGlobals($contextVars);

        ob_start();

        foreach ($availableBlocks as $blockName) {
            foreach ($templates as $template) {
                if ($this->templateHasBlock($template, $blockName, $contextVars)) {
                    $template->displayBlock($blockName, $contextVars);

                    return ob_get_clean();
                }
            }
        }

        return ob_get_clean();
    }

    private function templateHasBlock(\Twig_Template $template, string $blockName, array $context = null): bool
    {
        while ($template !== false) {
            if ($template->hasBlock($blockName, $context)) {
                return true;
            }

            $template = $template->getParent([]);
        }

        return false;
    }
}
