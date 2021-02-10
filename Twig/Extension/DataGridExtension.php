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
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\InitRuntimeInterface;
use Twig\Template;
use Twig\TwigFunction;

class DataGridExtension extends AbstractExtension implements InitRuntimeInterface
{
    /**
     * @var Template[]
     */
    private $themes;

    /**
     * @var array
     */
    private $themesVars;

    /**
     * @var string[]
     */
    private $baseThemesNames;

    /**
     * @var Template[]
     */
    private $baseThemes;

    /**
     * @var Environment
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
        $this->baseThemesNames = $themes;
        $this->baseThemes = [];
        $this->translator = $translator;
    }

    public function getName(): string
    {
        return 'datagrid';
    }

    public function initRuntime(Environment $environment): void
    {
        $this->environment = $environment;
        for ($i = count($this->baseThemesNames) - 1; $i >= 0; $i--) {
            $this->baseThemes[$i] = $this->environment->loadTemplate($this->baseThemesNames[$i]);
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('datagrid_widget', [$this, 'datagrid'], ['is_safe' => ['html']]),
            new TwigFunction('datagrid_header_widget', [$this, 'datagridHeader'], ['is_safe' => ['html']]),
            new TwigFunction('datagrid_rowset_widget', [$this, 'datagridRowset'], ['is_safe' => ['html']]),
            new TwigFunction('datagrid_column_header_widget', [$this, 'datagridColumnHeader'], ['is_safe' => ['html']]),
            new TwigFunction('datagrid_column_cell_widget', [$this, 'datagridColumnCell'], ['is_safe' => ['html']]),
            new TwigFunction(
                'datagrid_column_cell_form_widget',
                [$this, 'datagridColumnCellForm'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'datagrid_column_type_action_cell_action_widget',
                [$this, 'datagridColumnActionCellActionWidget'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction('datagrid_attributes_widget', [$this, 'datagridAttributes'], ['is_safe' => ['html']])
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
     * @param Template|string $theme
     * @param array $vars
     */
    public function setTheme(DataGridViewInterface $dataGrid, $theme, array $vars = []): void
    {
        $this->themes[$dataGrid->getName()] = ($theme instanceof Template)
            ? $theme
            : $this->environment->loadTemplate($theme);

        $this->themesVars[$dataGrid->getName()] = $vars;
    }

    /**
     * @param Template[]|Template|string $theme
     */
    public function setBaseTheme($theme): void
    {
        $themes = is_array($theme) ? $theme : [$theme];

        $this->baseThemes = [];
        foreach ($themes as $theme) {
            $this->baseThemes[] = ($theme instanceof Template)
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
            'vars' => $this->getDataGridVars($view)
        ];

        return $this->renderTheme($view, $context, $blockNames);
    }

    public function datagridHeader(DataGridViewInterface $view, array $vars = []): string
    {
        $blockNames = [
            'datagrid_' . $view->getName() . '_header',
            'datagrid_header',
        ];

        $context = [
            'headers' => $view->getColumns(),
            'vars' => array_merge(
                $this->getDataGridVars($view),
                $vars
            )
        ];

        return $this->renderTheme($view, $context, $blockNames);
    }

    public function datagridColumnHeader(HeaderViewInterface $view, array $vars = []): string
    {
        $dataGridView = $view->getDataGridView();
        $blockNames = [
            'datagrid_' . $dataGridView->getName() . '_column_name_' . $view->getName() . '_header',
            'datagrid_' . $dataGridView->getName() . '_column_type_' . $view->getType() . '_header',
            'datagrid_column_name_' . $view->getName() . '_header',
            'datagrid_column_type_' . $view->getType() . '_header',
            'datagrid_' . $dataGridView->getName() . '_column_header',
            'datagrid_column_header',
        ];

        $context = [
            'header' => $view,
            'translation_domain' => $view->getAttribute('translation_domain'),
            'vars' => array_merge(
                $this->getDataGridVars($view->getDataGridView()),
                $vars
            )
        ];

        return $this->renderTheme($dataGridView, $context, $blockNames);
    }

    public function datagridRowset(DataGridViewInterface $view, array $vars = []): string
    {
        $blockNames = [
            'datagrid_' . $view->getName() . '_rowset',
            'datagrid_rowset',
        ];

        $context = [
            'datagrid' => $view,
            'vars' => array_merge($this->getDataGridVars($view), $vars)
        ];

        return $this->renderTheme($view, $context, $blockNames);
    }

    public function datagridColumnCell(CellViewInterface $view, array $vars = []): string
    {
        $dataGridView = $view->getDataGridView();
        $blockNames = [
            'datagrid_' . $dataGridView->getName() . '_column_name_' . $view->getName() . '_cell',
            'datagrid_' . $dataGridView->getName() . '_column_type_' . $view->getType() . '_cell',
            'datagrid_column_name_' . $view->getName() . '_cell',
            'datagrid_column_type_' . $view->getType() . '_cell',
            'datagrid_' . $dataGridView->getName() . '_column_cell',
            'datagrid_column_cell',
        ];

        $context = [
            'cell' => $view,
            'row_index' => $view->getAttribute('row'),
            'datagrid_name' => $dataGridView->getName(),
            'translation_domain' => $view->getAttribute('translation_domain'),
            'vars' => array_merge($this->getDataGridVars($dataGridView), $vars)
        ];

        return $this->renderTheme($dataGridView, $context, $blockNames);
    }

    public function datagridColumnCellForm(CellViewInterface $view, array $vars = []): string
    {
        if (!$view->hasAttribute('form')) {
            return '';
        }

        $dataGridView = $view->getDataGridView();
        $blockNames = [
            'datagrid_' . $dataGridView->getName() . '_column_name_' . $view->getName() . '_cell_form',
            'datagrid_' . $dataGridView->getName() . '_column_type_' . $view->getType() . '_cell_form',
            'datagrid_column_name_' . $view->getName() . '_cell_form',
            'datagrid_column_type_' . $view->getType() . '_cell_form',
            'datagrid_' . $dataGridView->getName() . '_column_cell_form',
            'datagrid_column_cell_form',
        ];

        $context = [
            'form' => $view->getAttribute('form'),
            'vars' => array_merge($this->getDataGridVars($view->getDataGridView()), $vars)
        ];

        return $this->renderTheme($dataGridView, $context, $blockNames);
    }

    public function datagridColumnActionCellActionWidget(
        CellViewInterface $view,
        string $action,
        string $content,
        array $urlAttrs = [],
        array $fieldMappingValues = []
    ): string {
        $dataGridView = $view->getDataGridView();
        $blockNames = [
            'datagrid_' . $dataGridView->getName() . '_column_type_action_cell_action_' . $action,
            'datagrid_column_type_action_cell_action_' . $action ,
            'datagrid_' . $dataGridView->getName() . '_column_type_action_cell_action',
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

        return $this->renderTheme($dataGridView, $context, $blockNames);
    }

    public function datagridAttributes(array $attributes, ?string $translationDomain = null): string
    {
        $attrs = [];

        foreach ($attributes as $attributeName => $attributeValue) {
            if ($attributeName === 'title') {
                $attributeValue = $this->translator->trans($attributeValue, [], $translationDomain);
            }

            $attrs[] = sprintf('%s="%s"', $attributeName, $attributeValue);
        }

        return ' ' . implode(' ', $attrs);
    }

    /**
     * @param DataGridViewInterface $dataGrid
     * @return Template[]
     */
    private function getTemplates(DataGridViewInterface $dataGrid): array
    {
        $templates = [];

        if (isset($this->themes[$dataGrid->getName()])) {
            $templates[] = $this->themes[$dataGrid->getName()];
        }

        for ($i = count($this->baseThemes) - 1; $i >= 0; $i--) {
            $templates[] = $this->baseThemes[$i];
        }

        return $templates;
    }

    private function getDataGridVars(DataGridViewInterface $dataGrid): array
    {
        if (isset($this->themesVars[$dataGrid->getName()])) {
            return $this->themesVars[$dataGrid->getName()];
        }

        return [];
    }

    private function renderTheme(
        DataGridViewInterface $datagridView,
        array $contextVars = [],
        array $availableBlocks = []
    ): string {
        $templates = $this->getTemplates($datagridView);

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

    private function templateHasBlock(Template $template, string $blockName, array $context = null): bool
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
