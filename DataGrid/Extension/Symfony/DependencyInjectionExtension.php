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
use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
     * @var EventSubscriberInterface[]
     */
    private $eventSubscribers = [];

    /**
     * @param ColumnTypeInterface[] $columnTypes
     * @param ColumnTypeExtensionInterface[] $columnTypesExtensions
     * @param EventSubscriberInterface[] $eventSubscribers
     */
    public function __construct(
        array $columnTypes,
        array $columnTypesExtensions,
        array $eventSubscribers
    ) {
        foreach ($columnTypes as $columnType) {
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

        $this->eventSubscribers = $eventSubscribers;
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

    public function hasColumnTypeExtensions(string $type): bool
    {
        return array_key_exists($type, $this->columnTypesExtensions);
    }

    /**
     * @param string $type
     * @return ColumnTypeExtensionInterface[]
     */
    public function getColumnTypeExtensions(string $type): array
    {
        if (!array_key_exists($type, $this->columnTypesExtensions)) {
            return [];
        }

        return $this->columnTypesExtensions[$type];
    }

    public function registerSubscribers(DataGridInterface $dataGrid): void
    {
        foreach ($this->eventSubscribers as $eventSubscriber) {
            $dataGrid->addEventSubscriber($eventSubscriber);
        }
    }
}
