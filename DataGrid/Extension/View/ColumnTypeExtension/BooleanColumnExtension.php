<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;

class BooleanColumnExtension extends ColumnAbstractTypeExtension
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getExtendedColumnTypes(): array
    {
        return ['boolean'];
    }

    public function initOptions(ColumnTypeInterface $column): void
    {
        $column->getOptionsResolver()->setDefaults([
            'true_value' => $this->translator->trans('datagrid.boolean.yes', [], 'DataGridBundle'),
            'false_value' => $this->translator->trans('datagrid.boolean.no', [], 'DataGridBundle')
        ]);

        $translator = $this->translator;

        $column->getOptionsResolver()->setNormalizer(
            'form_options',
            function(Options $options, $value) use ($translator) {
                if ($options['editable'] && count($options['field_mapping']) === 1) {
                    $field = $options['field_mapping'][0];
                    $choices = [
                        0 => $translator->trans('datagrid.boolean.no', [], 'DataGridBundle'),
                        1 => $translator->trans('datagrid.boolean.yes', [], 'DataGridBundle')
                    ];
                    return array_merge(
                        [$field => ['choices' => $this->isSymfony3() ? array_flip($choices) : $choices]],
                        $value
                    );
                }

                return $value;
            }
        );

        $column->getOptionsResolver()->setNormalizer(
            'form_type',
            function(Options $options, $value) {
                if ($options['editable'] && count($options['field_mapping']) === 1) {
                    $field = $options['field_mapping'][0];
                    return array_merge(
                        [
                            $field => $this->isSymfony3()
                                ? ChoiceType::class
                                : 'choice'
                        ],
                        $value
                    );
                }

                return $value;
            }
        );
    }

    private function isSymfony3(): bool
    {
        return method_exists(AbstractType::class, 'getBlockPrefix');
    }
}
