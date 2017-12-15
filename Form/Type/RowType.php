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
use Symfony\Component\Form\FormBuilderInterface;

class RowType extends AbstractType
{
    /**
     * @var array
     */
    protected $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->fields as $field) {
            $builder->add($field['name'], $field['type'], $field['options']);
        }
    }

    public function getName(): string
    {
        return 'row';
    }
}
