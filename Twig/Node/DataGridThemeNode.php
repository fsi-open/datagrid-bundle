<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node;

class DataGridThemeNode extends Node
{
    public function __construct(
        Node $dataGrid,
        Node $theme,
        ArrayExpression $vars,
        int $lineno,
        ?string $tag = null
    ) {
        parent::__construct(['datagrid' => $dataGrid, 'theme' => $theme, 'vars' => $vars], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write(
                '$this->env->getExtension(\'FSi\Bundle\DataGridBundle\Twig\Extension\DataGridExtension\')->setTheme('
            )
            ->subcompile($this->getNode('datagrid'))
            ->raw(', ')
            ->subcompile($this->getNode('theme'))
            ->raw(', ')
            ->subcompile($this->getNode('vars'))
            ->raw(");\n");
    }
}
