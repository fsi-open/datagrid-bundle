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
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;

class ConfigurationBuilder implements EventSubscriberInterface
{
    private const MAIN_CONFIG_DIRECTORY = 'datagrid.yaml.main_config';
    private const BUNDLE_CONFIG_PATH = '%s/Resources/config/datagrid/%s.yml';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Parser
     */
    private $yamlParser;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->yamlParser = new Parser();
    }

    public static function getSubscribedEvents(): array
    {
        return [DataGridEvents::PRE_SET_DATA => ['readConfiguration', 128]];
    }

    public function readConfiguration(DataGridEventInterface $event): void
    {
        $dataGrid = $event->getDataGrid();
        $mainConfiguration = $this->getMainConfiguration($dataGrid->getName());
        if (null !== $mainConfiguration) {
            $this->buildConfiguration($dataGrid, $mainConfiguration);
        } else {
            $this->buildConfigurationFromRegisteredBundles($dataGrid);
        }
    }

    private function buildConfiguration(DataGridInterface $dataGrid, array $configuration): void
    {
        foreach ($configuration['columns'] as $name => $column) {
            $dataGrid->addColumn($name, $column['type'] ?? 'text', $column['options'] ?? []);
        }
    }

    private function getMainConfiguration(string $dataGridName): ?array
    {
        $directory = $this->kernel->getContainer()->getParameter(self::MAIN_CONFIG_DIRECTORY);
        if (null === $directory) {
            return null;
        }

        if (false === is_dir($directory)) {
            throw new RuntimeException(sprintf('"%s" is not a directory!', $directory));
        }

        $configurationFile = sprintf('%s/%s.yml', rtrim($directory, '/'), $dataGridName);
        if (false === file_exists($configurationFile)) {
            return null;
        }

        $configuration = $this->parseYamlFile($configurationFile);
        if (false === is_array($configuration)) {
            return null;
        }

        return $configuration;
    }

    private function buildConfigurationFromRegisteredBundles(DataGridInterface $dataGrid): void
    {
        $dataGridName = $dataGrid->getName();
        $eligibleBundles = array_filter(
            $this->kernel->getBundles(),
            function (BundleInterface $bundle) use ($dataGridName): bool {
                return file_exists(sprintf(self::BUNDLE_CONFIG_PATH, $bundle->getPath(), $dataGridName));
            }
        );

        // The idea here is that the last found configuration should be used
        $configuration = $this->findLastBundleConfiguration($dataGridName, $eligibleBundles);
        if (0 !== count($configuration)) {
            $this->buildConfiguration($dataGrid, $configuration);
        }
    }

    private function findLastBundleConfiguration(string $dataGridName, array $eligibleBundles): array
    {
        return array_reduce(
            $eligibleBundles,
            function (array $configuration, BundleInterface $bundle) use ($dataGridName): array {
                $overridingConfiguration = $this->parseYamlFile(
                    sprintf(self::BUNDLE_CONFIG_PATH, $bundle->getPath(), $dataGridName)
                );
                if (true === is_array($overridingConfiguration)) {
                    $configuration = $overridingConfiguration;
                }

                return $configuration;
            },
            []
        );
    }

    private function parseYamlFile(string $path)
    {
        return $this->yamlParser->parse(file_get_contents($path));
    }
}
