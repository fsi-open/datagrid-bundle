<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\Tests\DependencyInjection;

use FSi\Bundle\DataGridBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    public function testDefaultOptions()
    {
        $defaults = [
            'yaml_configuration' => [
                'enabled' => true,
                'main_configuration_directory' => null
            ],
            'twig' => [
                'enabled' => true,
                'themes' => ['@DataGrid/datagrid.html.twig']
            ]
        ];
        $this->assertSame(
            $defaults,
            $this->processor->processConfiguration(new Configuration(), ['fsi_data_grid' => []])
        );
    }

    public function testFoldedYamlConfigurationForTrue()
    {
        $folded = [
            'yaml_configuration' => [
                'enabled' => true,
                'main_configuration_directory' => null
            ],
            'twig' => [
                'enabled' => true,
                'themes' => ['@DataGrid/datagrid.html.twig']
            ]
        ];
        $this->assertSame(
            $folded,
            $this->processor->processConfiguration(new Configuration(), [
                'fsi_data_grid' => ['yaml_configuration' => true]
            ])
        );
    }

    public function testFoldedYamlConfigurationForFalse()
    {
        $folded = [
            'yaml_configuration' => [
                'enabled' => false,
                'main_configuration_directory' => null
            ],
            'twig' => [
                'enabled' => true,
                'themes' => ['@DataGrid/datagrid.html.twig']
            ]
        ];
        $this->assertSame(
            $folded,
            $this->processor->processConfiguration(new Configuration(), [
                'fsi_data_grid' => ['yaml_configuration' => false]
            ])
        );
    }

    public function testThemesOption()
    {
        $config = $this->processor->processConfiguration(new Configuration(), [
            'fsi_data_grid' => ['twig' => ['themes' => ['@DataGrid/custom_datagrid.html.twig']]]
        ]);

        $this->assertSame(
            [
                'twig' => ['themes' => ['@DataGrid/custom_datagrid.html.twig'], 'enabled' => true],
                'yaml_configuration' => ['enabled' => true, 'main_configuration_directory' => null]
            ],
            $config
        );
    }

    public function testCustomMainConfigurationFilesPath()
    {
        $config = $this->processor->processConfiguration(new Configuration(), [
            'fsi_data_grid' => [
                'yaml_configuration' => [
                    'main_configuration_directory' => 'a path to main configuration directory'
                ]
            ]
        ]);

        $this->assertSame(
            [
                'yaml_configuration' => [
                    'main_configuration_directory' => 'a path to main configuration directory',
                    'enabled' => true
                ],
                'twig' => ['enabled' => true, 'themes' => ['@DataGrid/datagrid.html.twig']]
            ],
            $config
        );
    }

    protected function setUp()
    {
        $this->processor = new Processor();
    }
}
