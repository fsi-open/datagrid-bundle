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
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Symfony3RowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['fields'] as $field) {
            $builder->add($field['name'], $field['type'], $field['options']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('fields', []);
    }

    public function getBlockPrefix(): string
    {
        return 'row';
    }
}
