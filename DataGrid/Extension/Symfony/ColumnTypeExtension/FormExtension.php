<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\EventSubscriber\FormSubscriber;
use FSi\Component\DataGrid\Column\CellViewInterface;
use FSi\Component\DataGrid\Column\ColumnAbstractTypeExtension;
use FSi\Component\DataGrid\Column\ColumnInterface;
use FSi\Component\DataGrid\Extension\Core\ColumnType\Boolean;
use FSi\Component\DataGrid\Extension\Core\ColumnType\DateTime;
use FSi\Component\DataGrid\Extension\Core\ColumnType\Number;
use FSi\Component\DataGrid\Extension\Core\ColumnType\Text;
use FSi\Component\DataGrid\Extension\Doctrine\ColumnType\Entity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormExtension extends ColumnAbstractTypeExtension implements CellFormBuilder
{
    /**
     * @var FormSubscriber
     */
    private $formSubscriber;

    public function __construct(FormSubscriber $formSubscriber)
    {
        $this->formSubscriber = $formSubscriber;
    }

    public function buildCellView(ColumnInterface $column, CellViewInterface $view): void
    {
        if (!$column->getOption('editable')) {
            return;
        }

        $view->setAttribute('form', $this->formSubscriber->getCellForm($view)->createView());
    }

    public function getExtendedColumnTypes(): array
    {
        return [
            Text::class,
            Boolean::class,
            Number::class,
            DateTime::class,
            Entity::class,
        ];
    }

    public function initOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'editable' => false,
            'form_options' => [],
            'form_type' => [],
        ]);

        $optionsResolver->setAllowedTypes('editable', 'bool');
        $optionsResolver->setAllowedTypes('form_options', 'array');
        $optionsResolver->setAllowedTypes('form_type', 'array');
    }

    public function buildCellForm(FormBuilderInterface $form, ColumnInterface $column): void
    {
        // Create fields array. There are column types like entity where field_mapping
        // should not be used to build field array.
        $fields = [];
        if ($column->getType() instanceof EntityType) {
            $field = [
                'name' => $column->getOption('relation_field'),
                'type' => $this->isSymfony3() ? EntityType::class : 'entity',
                'options' => [],
            ];

            $fields[$column->getOption('relation_field')] = $field;
        } else {
            foreach ($column->getOption('field_mapping') as $fieldName) {
                $field = [
                    'name' => $fieldName,
                    'type' => null,
                    'options' => [],
                ];
                $fields[$fieldName] = $field;
            }
        }

        //Pass fields form options from column into $fields array.
        $fieldsOptions = $column->getOption('form_options');
        foreach ($fieldsOptions as $fieldName => $fieldOptions) {
            if (array_key_exists($fieldName, $fields)) {
                if (is_array($fieldOptions)) {
                    $fields[$fieldName]['options'] = $fieldOptions;
                }
            }
        }

        //Pass fields form type from column into $fields array.
        $fieldsTypes = $column->getOption('form_type');
        foreach ($fieldsTypes as $fieldName => $fieldType) {
            if (array_key_exists($fieldName, $fields)) {
                if (is_string($fieldType)) {
                    $fields[$fieldName]['type'] = $fieldType;
                }
            }
        }

        foreach ($fields as $field) {
            $form->add($field['name'], $field['type'], $field['options']);
        }
    }

    private function getEntityTypeName(): string
    {
        return EntityType::class;
    }

    private function isSymfony3(): bool
    {
        return method_exists(AbstractType::class, 'getBlockPrefix');
    }
}
