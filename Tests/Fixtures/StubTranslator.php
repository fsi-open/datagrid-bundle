<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Tests\Fixtures;

use Symfony\Component\Translation\TranslatorInterface;

class StubTranslator implements TranslatorInterface
{
    private $locale;

    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return '[trans]'.$id.'[/trans]';
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return '[trans]'.$id.'[/trans]';
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
