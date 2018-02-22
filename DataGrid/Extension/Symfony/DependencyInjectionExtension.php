<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony;

use FSi\Component\DataGrid\Column\ColumnTypeExtensionInterface;
use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\DataGridExtensionInterface;

class DependencyInjectionExtension implements DataGridExtensionInterface
{
    /**
     * @var ColumnTypeInterface[]
     */
    private $columnTypes = [];

    /**
     * @var ColumnTypeExtensionInterface[][]
     */
    private $columnTypesExtensions = [];

    /**
     * @param ColumnTypeInterface[] $columnTypes
     * @param ColumnTypeExtensionInterface[] $columnTypesExtensions
     */
    public function __construct(
        array $columnTypes,
        array $columnTypesExtensions
    ) {
        foreach ($columnTypes as $columnType) {
            $this->columnTypes[get_class($columnType)] = $columnType;
            $this->columnTypes[$columnType->getId()] = $columnType;
        }

        foreach ($columnTypesExtensions as $columnTypeExtension) {
            foreach ($columnTypeExtension->getExtendedColumnTypes() as $extendedColumnType) {
                if (!array_key_exists($extendedColumnType, $this->columnTypesExtensions)) {
                    $this->columnTypesExtensions[$extendedColumnType] = [];
                }
                $this->columnTypesExtensions[$extendedColumnType][] = $columnTypeExtension;
            }
        }
    }

    public function hasColumnType(string $type): bool
    {
        return array_key_exists($type, $this->columnTypes);
    }

    public function getColumnType(string $type): ColumnTypeInterface
    {
        if (!array_key_exists($type, $this->columnTypes)) {
            throw new \InvalidArgumentException(sprintf(
                'The column type "%s" is not registered with the service container.',
                $type
            ));
        }

        return $this->columnTypes[$type];
    }

    public function hasColumnTypeExtensions(ColumnTypeInterface $columnType): bool
    {
        foreach ($this->columnTypesExtensions as $extendedColumnType => $columnTypeExtensions) {
            if (is_a($columnType, $extendedColumnType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ColumnTypeInterface $columnType
     * @return ColumnTypeExtensionInterface[]
     */
    public function getColumnTypeExtensions(ColumnTypeInterface $columnType): array
    {
        foreach ($this->columnTypesExtensions as $extendedColumnType => $extensions) {
            if (is_a($columnType, $extendedColumnType)) {
                return $extensions;
            }
        }

        return [];
    }
}
