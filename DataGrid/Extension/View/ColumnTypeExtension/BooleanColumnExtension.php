<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\View\ColumnTypeExtension;

use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Translation\TranslatorInterface;

class BooleanColumnExtension extends ColumnAbstractTypeExtension
{
    /**
     * Symfony Translator to generate translations.
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedColumnTypes()
    {
        return ['boolean'];
    }

    /**
     * {@inheritDoc}
     */
    public function initOptions(ColumnTypeInterface $column)
    {
        $column->getOptionsResolver()->setDefaults([
            'true_value' => $this->translator->trans('datagrid.boolean.yes', [], 'DataGridBundle'),
            'false_value' => $this->translator->trans('datagrid.boolean.no', [], 'DataGridBundle')
        ]);

        $translator = $this->translator;
        $column->getOptionsResolver()->setNormalizer(
            'form_options',
            function(Options $options, $value) use ($translator) {
                if ($options['editable'] && count($options['field_mapping']) == 1) {
                    $field = $options['field_mapping'][0];

                    return array_merge(
                        [
                            $field => [
                                'choices' => [
                                    0 => $translator->trans('datagrid.boolean.no', [], 'DataGridBundle'),
                                    1 => $translator->trans('datagrid.boolean.yes', [], 'DataGridBundle')
                                ]
                            ]
                        ],
                        $value
                    );
                }

                return $value;
            }
        );
        $column->getOptionsResolver()->setNormalizer(
            'form_type',
            function(Options $options, $value) {
                if ($options['editable'] && count($options['field_mapping']) == 1) {

                    $field = $options['field_mapping'][0];

                    return array_merge(
                        [$field => 'choice'],
                        $value
                    );
                }

                return $value;
            }
        );
    }
}