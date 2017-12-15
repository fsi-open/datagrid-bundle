<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Twig\Node;

class DataGridThemeNode extends \Twig_Node
{
    public function __construct(
        \Twig_Node $dataGrid,
        \Twig_Node $theme,
        \Twig_Node_Expression_Array $vars,
        int $lineno,
        ?string $tag = null
    ) {
        parent::__construct(['datagrid' => $dataGrid, 'theme' => $theme, 'vars' => $vars], [], $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$this->env->getExtension(\'FSi\Bundle\DataGridBundle\Twig\Extension\DataGridExtension\')->setTheme(')
            ->subcompile($this->getNode('datagrid'))
            ->raw(', ')
            ->subcompile($this->getNode('theme'))
            ->raw(', ')
            ->subcompile($this->getNode('vars'))
            ->raw(");\n");
    }
}
