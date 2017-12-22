<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class RowType extends AbstractType
{
    public function getName(): string
    {
        return 'row';
    }

    public function getBlockPrefix(): string
    {
        return $this->getName();
    }
}
