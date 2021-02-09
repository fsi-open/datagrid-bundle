<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Tests\Twig\Extension;

use FSi\Bundle\DataGridBundle\Tests\Fixtures\StubTranslator;
use FSi\Bundle\DataGridBundle\Tests\Fixtures\TwigRuntimeLoader;
use FSi\Bundle\DataGridBundle\Twig\Extension\DataGridExtension;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\HeaderViewInterface;
use FSi\Component\DataGrid\DataGridViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\Template;

class DataGridExtensionTest extends TestCase
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var DataGridExtension
     */
    protected $extension;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function setUp(): void
    {
        $loader = new FilesystemLoader([
            __DIR__ . '/../../../vendor/symfony/twig-bridge/Resources/views/Form',
            __DIR__ . '/../../../Resources/views', // datagrid base theme
            __DIR__ . '/../../Resources/views', // templates used in tests
        ]);

        $this->translator = new StubTranslator();
        $twig = new Environment($loader);
        $twig->addExtension(new TranslationExtension($this->translator));
        $twig->addGlobal('global_var', 'global_value');

        $twigRendererEngine = new TwigRendererEngine(['form_div_layout.html.twig'], $twig);
        $renderer = new FormRenderer($twigRendererEngine);
        $formExtension = new FormExtension();
        $twig->addExtension($formExtension);
        $twig->addRuntimeLoader(new TwigRuntimeLoader([$renderer]));

        $this->twig = $twig;
        $this->extension = new DataGridExtension(['datagrid.html.twig'], $this->translator);
    }

    public function testInitRuntimeShouldThrowExceptionBecauseNotExistingTheme(): void
    {
        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Unable to find template "this_is_not_valid_path.html.twig"');

        $this->twig->addExtension(new DataGridExtension(['this_is_not_valid_path.html.twig'], $this->translator));
        // force initRuntime()
        $this->twig->load('datagrid.html.twig');
    }

    public function testInitRuntimeWithValidPathToTheme(): void
    {
        $this->twig->addExtension($this->extension);
        self::assertNotNull($this->twig->load('datagrid.html.twig'));
    }

    public function testRenderDataGridWidget(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');

        $datagridView = $this->getDataGridView('grid');
        $datagridView->method('getColumns')
            ->willReturn(['title' => $this->getColumnHeaderView($datagridView, 'text', 'title', 'Title')]);

        $datagridWithThemeView = $this->getDataGridView('grid_with_theme');
        $datagridWithThemeView->method('getColumns')
            ->willReturn(['title' => $this->getColumnHeaderView($datagridWithThemeView, 'text', 'title', 'Title')]);

        $html = $this->twig->render('datagrid/datagrid_widget_test.html.twig', [
            'datagrid' => $datagridView,
            'datagrid_with_theme' => $datagridWithThemeView,
        ]);

        self::assertSame($this->getExpectedHtml('datagrid/datagrid_widget_result.html'), $html);
    }

    public function testRenderColumnHeaderWidget(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');

        $datagridView = $this->getDataGridView('grid');
        $datagridWithThemeView = $this->getDataGridView('grid_with_header_theme');

        $headerView = $this->getColumnHeaderView($datagridView, 'text', 'title', 'title');
        $headerWithThemeView = $this->getColumnHeaderView($datagridWithThemeView, 'text', 'title', 'title');

        $html = $this->twig->render('datagrid/header_widget_test.html.twig', [
            'grid_with_header_theme' => $datagridWithThemeView,
            'header' => $headerView,
            'header_with_theme' => $headerWithThemeView,
        ]);

        self::assertSame($this->getExpectedHtml('datagrid/datagrid_header_widget_result.html'), $html);
    }

    public function testRenderCellWidget(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');

        $datagridView = $this->getDataGridView('grid');
        $datagridWithThemeView = $this->getDataGridView('grid_with_header_theme');

        $cellView = $this->getColumnCellView($datagridView, 'text', 'title', 'This is value 1');
        $cellWithThemeView = $this->getColumnCellView($datagridWithThemeView, 'text', 'title', 'This is value 2');

        $html = $this->twig->render('datagrid/cell_widget_test.html.twig', [
            'grid_with_header_theme' => $datagridWithThemeView,
            'cell' => $cellView,
            'cell_with_theme' => $cellWithThemeView,
        ]);

        self::assertSame($this->getExpectedHtml('datagrid/datagrid_cell_widget_result.html'), $html);
    }

    public function testRenderCellActionWidget(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');

        $datagridView = $this->getDataGridView('grid');
        $datagridWithThemeView = $this->getDataGridView('grid_with_header_theme');

        $cellView = $this->getColumnCellView($datagridView, 'actions', 'action', []);
        $cellWithThemeView = $this->getColumnCellView($datagridWithThemeView, 'actions', 'action', []);

        $html = $this->twig->render('datagrid/action_cell_action_widget_test.html.twig', [
            'grid_with_header_theme' => $datagridWithThemeView,
            'cell' => $cellView,
            'cell_with_theme' => $cellWithThemeView,
        ]);

        self::assertSame($this->getExpectedHtml('datagrid/action_cell_action_widget_result.html'), $html);
    }

    public function testDataGridRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))->method('hasBlock')->with('datagrid_grid')->willReturn(false);
        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(2))->method('hasBlock')->with('datagrid')->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');

        $template->expects(self::at(3))
            ->method('displayBlock')
            ->with('datagrid', [
                'datagrid' => $datagridView,
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagrid($datagridView);
    }

    public function testDataGridMultipleTemplates(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');

        $template1 = $this->getTemplateMock();
        $template1->expects(self::at(0))->method('hasBlock')->with('datagrid_grid')->willReturn(false);
        $template1->expects(self::at(1))->method('getParent')->with([])->willReturn(false);
        $template1->expects(self::at(2))->method('hasBlock')->with('datagrid')->willReturn(true);

        $template2 = $this->getTemplateMock();
        $template2->expects(self::at(0))->method('hasBlock')->with('datagrid_grid')->willReturn(false);
        $template2->expects(self::at(1))->method('getParent')->with([])->willReturn(false);
        $template2->expects(self::at(2))->method('hasBlock')->with('datagrid')->willReturn(false);
        $template2->expects(self::at(3))->method('getParent')->with([])->willReturn(false);

        $this->extension->setBaseTheme([$template1, $template2]);
        $datagridView = $this->getDataGridView('grid');

        $template1->expects(self::at(3))
            ->method('displayBlock')
            ->with('datagrid', [
                'datagrid' => $datagridView,
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagrid($datagridView);
    }

    public function testDataGridRenderBlockFromParent(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');

        $template = $this->getTemplateMock();
        $parent = $this->getTemplateMock();

        $template->expects(self::at(0))->method('hasBlock')->with('datagrid_grid')->willReturn(false);
        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(2))->method('hasBlock')->with('datagrid')->willReturn(false);
        $template->expects(self::at(3))->method('getParent')->with([])->willReturn($parent);

        $parent->expects(self::at(0))->method('hasBlock')->with('datagrid')->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');

        $template->expects(self::at(4))
            ->method('displayBlock')
            ->with('datagrid', [
                'datagrid' => $datagridView,
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagrid($datagridView);
    }

    public function testDataGridHeaderRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))->method('hasBlock')->with('datagrid_grid_header')->willReturn(false);
        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(2))->method('hasBlock')->with('datagrid_header')->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');
        $datagridView->method('getColumns')->willReturn([]);

        $template->expects(self::at(3))
            ->method('displayBlock')
            ->with('datagrid_header', [
                'headers' => [],
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagridHeader($datagridView);
    }

    public function testDataGridColumnHeaderRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))
            ->method('hasBlock')
            ->with('datagrid_grid_column_name_title_header')
            ->willReturn(false);

        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(2))
            ->method('hasBlock')
            ->with('datagrid_grid_column_type_text_header')
            ->willReturn(false);

        $template->expects(self::at(3))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(4))
            ->method('hasBlock')
            ->with('datagrid_column_name_title_header')
            ->willReturn(false);

        $template->expects(self::at(5))->method('getParent')->with([])
            ->willReturn(false);

        $template->expects(self::at(6))
            ->method('hasBlock')
            ->with('datagrid_column_type_text_header')
            ->willReturn(false);

        $template->expects(self::at(7))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(8))
            ->method('hasBlock')
            ->with('datagrid_grid_column_header')
            ->willReturn(false);

        $template->expects(self::at(9))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(10))
            ->method('hasBlock')
            ->with('datagrid_column_header')
            ->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');

        $headerView = $this->getColumnHeaderView($datagridView, 'text', 'title', 'Title');
        $headerView->method('getAttribute')->with('translation_domain')->willReturn(null);

        $template->expects(self::at(11))
            ->method('displayBlock')
            ->with('datagrid_column_header', [
                'header' => $headerView,
                'translation_domain' => null,
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagridColumnHeader($headerView);
    }

    public function testDataGridRowsetRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))->method('hasBlock')->with('datagrid_grid_rowset')->willReturn(false);
        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(2))->method('hasBlock')->with('datagrid_rowset')->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');

        $template->expects(self::at(3))
            ->method('displayBlock')
            ->with('datagrid_rowset', [
                'datagrid' => $datagridView,
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagridRowset($datagridView);
    }

    public function testDataGridColumnCellRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))
            ->method('hasBlock')
            ->with('datagrid_grid_column_name_title_cell')
            ->willReturn(false);

        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(2))
            ->method('hasBlock')
            ->with('datagrid_grid_column_type_text_cell')
            ->willReturn(false);

        $template->expects(self::at(3))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(4))->method('hasBlock')->with('datagrid_column_name_title_cell')->willReturn(false);
        $template->expects(self::at(5))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(6))->method('hasBlock')->with('datagrid_column_type_text_cell')->willReturn(false);
        $template->expects(self::at(7))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(8))->method('hasBlock')->with('datagrid_grid_column_cell')->willReturn(false);
        $template->expects(self::at(9))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(10))->method('hasBlock')->with('datagrid_column_cell')->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');
        $cellView = $this->getColumnCellView($datagridView, 'text', 'title', 'Value 1');

        $cellView
            ->method('getAttribute')
            ->willReturnCallback(
                function ($key) {
                    if ('row' === $key) {
                        return 0;
                    }

                    return null;
                }
            );

        $template->expects(self::at(11))
            ->method('displayBlock')
            ->with('datagrid_column_cell', [
                'cell' => $cellView,
                'row_index' => 0,
                'datagrid_name' => 'grid',
                'translation_domain' => null,
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagridColumnCell($cellView);
    }

    public function testDataGridColumnCellFormRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))
            ->method('hasBlock')
            ->with('datagrid_grid_column_name_title_cell_form')
            ->willReturn(false);

        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(2))
            ->method('hasBlock')
            ->with('datagrid_grid_column_type_text_cell_form')
            ->willReturn(false);

        $template->expects(self::at(3))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(4))
            ->method('hasBlock')
            ->with('datagrid_column_name_title_cell_form')
            ->willReturn(false);

        $template->expects(self::at(5))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(6))
            ->method('hasBlock')
            ->with('datagrid_column_type_text_cell_form')
            ->willReturn(false);

        $template->expects(self::at(7))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(8))
            ->method('hasBlock')
            ->with('datagrid_grid_column_cell_form')
            ->willReturn(false);

        $template->expects(self::at(9))->method('getParent')->with([])->willReturn(false);
        $template->expects(self::at(10))->method('hasBlock')->with('datagrid_column_cell_form')->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');

        $cellView = $this->getColumnCellView($datagridView, 'text', 'title', 'Value 1');
        $cellView->method('hasAttribute')->with('form')->willReturn(true);
        $cellView->method('getAttribute')->with('form')->willReturn('form');

        $template->expects(self::at(11))
            ->method('displayBlock')
            ->with('datagrid_column_cell_form', [
                'form' => 'form',
                'vars' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagridColumnCellForm($cellView);
    }

    public function testDataGridColumnActionCellActionRenderBlock(): void
    {
        $this->twig->addExtension($this->extension);
        $this->twig->load('datagrid.html.twig');
        $template = $this->getTemplateMock();

        $template->expects(self::at(0))
            ->method('hasBlock')
            ->with('datagrid_grid_column_type_action_cell_action_edit')
            ->willReturn(false);

        $template->expects(self::at(1))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(2))
            ->method('hasBlock')
            ->with('datagrid_column_type_action_cell_action_edit')
            ->willReturn(false);

        $template->expects(self::at(3))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(4))
            ->method('hasBlock')
            ->with('datagrid_grid_column_type_action_cell_action')
            ->willReturn(false);

        $template->expects(self::at(5))->method('getParent')->with([])->willReturn(false);

        $template->expects(self::at(6))
            ->method('hasBlock')
            ->with('datagrid_column_type_action_cell_action')
            ->willReturn(true);

        $this->extension->setBaseTheme($template);
        $datagridView = $this->getDataGridView('grid');

        $cellView = $this->getColumnCellView($datagridView, 'action', 'actions', []);
        $cellView->method('getAttribute')->with('translation_domain')->willReturn(null);

        $template->expects(self::at(7))
            ->method('displayBlock')
            ->with('datagrid_column_type_action_cell_action', [
                'cell' => $cellView,
                'action' => 'edit',
                'content' => 'content',
                'attr' => [],
                'translation_domain' => null,
                'field_mapping_values' => [],
                'global_var' => 'global_value'
            ])
            ->willReturn(true);

        $this->extension->datagridColumnActionCellActionWidget($cellView, 'edit', 'content');
    }

    /**
     * @param string $name
     * @return DataGridViewInterface&MockObject
     */
    private function getDataGridView(string $name): DataGridViewInterface
    {
        /** @var DataGridViewInterface&MockObject $datagridView */
        $datagridView = $this->getMockBuilder(DataGridViewInterface::class)->disableOriginalConstructor()->getMock();
        $datagridView->method('getName')->willReturn($name);

        return $datagridView;
    }

    /**
     * @param DataGridViewInterface $datagridView
     * @param string $type
     * @param string $name
     * @param string|null $label
     * @return HeaderViewInterface&MockObject
     */
    private function getColumnHeaderView(
        DataGridViewInterface $datagridView,
        string $type,
        string $name,
        ?string $label = null
    ): HeaderViewInterface {
        /** @var HeaderViewInterface&MockObject $column */
        $column = $this->createMock(HeaderViewInterface::class);
        $column->method('getType')->willReturn($type);
        $column->method('getLabel')->willReturn($label);
        $column->method('getName')->willReturn($name);
        $column->method('getDataGridView')->willReturn($datagridView);

        return $column;
    }

    /**
     * @param DataGridViewInterface $datagridView
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @return CellViewInterface&MockObject
     */
    private function getColumnCellView(
        DataGridViewInterface $datagridView,
        string $type,
        string $name,
        $value
    ): CellViewInterface {
        /** @var CellViewInterface&MockObject $column */
        $column = $this->createMock(CellViewInterface::class);
        $column->method('getType')->willReturn($type);
        $column->method('getValue')->willReturn($value);
        $column->method('getName')->willReturn($name);
        $column->method('getDataGridView')->willReturn($datagridView);

        return $column;
    }

    private function getExpectedHtml(string $filename): string
    {
        $path = __DIR__ . '/../../Resources/views/expected/' . $filename;
        if (false === file_exists($path)) {
            throw new RuntimeException(sprintf('Invalid expected html file path "%s"', $path));
        }

        return file_get_contents($path);
    }

    /**
     * @return Template&MockObject
     */
    private function getTemplateMock(): Template
    {
        /** @var Template&MockObject $template */
        $template = $this->createMock(Template::class);

        return $template;
    }
}
