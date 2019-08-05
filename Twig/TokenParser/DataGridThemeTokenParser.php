<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Twig\TokenParser;

use FSi\Bundle\DataGridBundle\Twig\Node\DataGridThemeNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class DataGridThemeTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): DataGridThemeNode
    {
        $stream = $this->parser->getStream();
        $dataGrid = $this->parser->getExpressionParser()->parseExpression();
        $theme = $this->parser->getExpressionParser()->parseExpression();
        $vars = new ArrayExpression([], $stream->getCurrent()->getLine());

        if ($this->parser->getStream()->test(Token::NAME_TYPE, 'with')) {
            $this->parser->getStream()->next();

            if ($this->parser->getStream()->test(Token::PUNCTUATION_TYPE)) {
                $vars = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new DataGridThemeNode($dataGrid, $theme, $vars, $token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'datagrid_theme';
    }
}
