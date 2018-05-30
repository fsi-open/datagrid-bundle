<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Component\DataGrid\DataGridEventInterface;
use FSi\Component\DataGrid\DataGridEvents;
use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;

class ConfigurationBuilder implements EventSubscriberInterface
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public static function getSubscribedEvents(): array
    {
        return [DataGridEvents::PRE_SET_DATA => ['readConfiguration', 128]];
    }

    public function readConfiguration(DataGridEventInterface $event): void
    {
        $dataGrid = $event->getDataGrid();

        $this->buildConfigurationFromRegisteredBundles($dataGrid);
    }

    protected function hasDataGridConfiguration(string $bundlePath, string $dataGridName): bool
    {
        return file_exists(sprintf($bundlePath . '/Resources/config/datagrid/%s.yml', $dataGridName));
    }

    protected function getDataGridConfiguration(string $bundlePath, string $dataGridName)
    {
        // TODO use one instance of the parser instead of creating it each time
        $yamlParser = new Parser();

        return $yamlParser->parse(
            file_get_contents(
                sprintf($bundlePath . '/Resources/config/datagrid/%s.yml', $dataGridName)
            )
        );
    }

    protected function buildConfiguration(DataGridInterface $dataGrid, array $configuration): void
    {
        foreach ($configuration['columns'] as $name => $column) {
            $type = array_key_exists('type', $column)
                ? $column['type']
                : 'text';
            $options = array_key_exists('options', $column)
                ? $column['options']
                : [];

            $dataGrid->addColumn($name, $type, $options);
        }
    }

    private function buildConfigurationFromRegisteredBundles(DataGridInterface $dataGrid): void
    {
        $dataGridName = $dataGrid->getName();
        $eligibleBundles = array_filter(
            $this->kernel->getBundles(),
            function (BundleInterface $bundle) use ($dataGridName): bool {
                return $this->hasDataGridConfiguration($bundle->getPath(), $dataGridName);
            }
        );
        $configuration = array_reduce(
            $eligibleBundles,
            function (array $configuration, BundleInterface $bundle) use ($dataGridName): array {
                $overridingConfiguration = $this->getDataGridConfiguration($bundle->getPath(), $dataGridName);
                // The idea here is that the last found configuration should be used
                if (true === is_array($overridingConfiguration)) {
                    $configuration = $overridingConfiguration;
                }

                return $configuration;
            },
            []
        );

        if (0 !== count($configuration)) {
            $this->buildConfiguration($dataGrid, $configuration);
        }
    }
}
