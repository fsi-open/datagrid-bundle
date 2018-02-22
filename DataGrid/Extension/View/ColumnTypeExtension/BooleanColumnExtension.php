<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use FSi\Component\DataGrid\Extension\Core\ColumnType\Boolean;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
        return [Boolean::class];
    }

    public function initOptions(OptionsResolver $optionsResolver): void
    {
        $yes = $this->translator->trans('datagrid.boolean.yes', [], 'DataGridBundle');
        $no = $this->translator->trans('datagrid.boolean.no', [], 'DataGridBundle');
        $optionsResolver->setDefaults([
            'true_value' => $yes,
            'false_value' => $no
        ]);

        $optionsResolver->setNormalizer(
            'form_options',
            function(Options $options, $value) use ($yes, $no) {
                if ($options['editable'] && count($options['field_mapping']) === 1) {
                    $field = $options['field_mapping'][0];
                    $choices = [0 => $no, 1 => $yes];

                    return array_merge(
                        [$field => ['choices' => $this->isSymfony3() ? array_flip($choices) : $choices]],
                        $value
                    );
                }

                return $value;
            }
        );

        $optionsResolver->setNormalizer(
            'form_type',
            function(Options $options, $value) {
                if ($options['editable'] && count($options['field_mapping']) === 1) {
                    $field = $options['field_mapping'][0];
                    return array_merge(
                        [$field => $this->isSymfony3() ? ChoiceType::class : 'choice'],
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
