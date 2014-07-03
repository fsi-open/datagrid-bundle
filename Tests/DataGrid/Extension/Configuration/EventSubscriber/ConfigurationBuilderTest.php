<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\DataGridTest;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationImporter;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ResourceLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;

class ConfigurationBuilderTest extends DataGridTest
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var ConfigurationBuilder
     */
    protected $subscriber;

    public function setUp()
    {
        $this->kernel = new StubKernel(self::FIXTURE_PATH);
        $this->kernel->injectBundle(new StubBundle('BarBundle', $this->kernel->getRootDir()));
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->kernel->getRootDir()));

        $resourceLocator = new ResourceLocator($this->kernel, 'datagrid');
        $configurationLoader = new ConfigurationLoader();
        $configurationImporter = new ConfigurationImporter($configurationLoader, $resourceLocator);
        $configurationLoader->setConfiguratinImporter($configurationImporter);
        $this->prepareFileSystem();

        $this->subscriber = new ConfigurationBuilder($this->kernel, $configurationLoader, $configurationImporter, $resourceLocator);
    }

    public function tearDown()
    {
        $this->destroyFileSystem();
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            $this->subscriber->getSubscribedEvents(),
            array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128))
        );
    }

    public function testReadConfigurationFromBundle()
    {
        $fooBundleDatagridConfig = <<<YML
columns:
  author:
    type: text
    options:
      label: Author

imports:
  - { resource: "BarBundle:news.yml" }
YML;
        $barBundleDatagridConfig = <<<YML
columns:
  title:
    type: text
    options:
      label: News Title
YML;

        $this->createConfigurationFile('FooBundle/Resources/config/datagrid/news.yml', $fooBundleDatagridConfig);
        $this->createConfigurationFile('BarBundle/Resources/config/datagrid/news.yml', $barBundleDatagridConfig);

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new DataGridEvent($dataGrid, array());

        $dataGrid->expects($this->at(0))
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->at(1))
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($dataGridSpy = $this->any())
            ->method('addColumn');

        $this->subscriber->readConfiguration($event);

        $this->assertThereColumnExists($dataGridSpy->getInvocations(), array(
            'title' => array(
                'type' => 'text',
                'options' => array(
                    'label' => 'News Title'
                )
            ),
            'author' => array(
                'type' => 'text',
                'options' => array(
                    'label' => 'Author'
                )
            )
        ));
    }

    public function testReadConfigurationFromGlobalConfig()
    {
        $fooBundleDataGridConfig = <<<YML
columns:
  author:
    type: text
    options:
      label: Author
imports:
  - { resource: "news.yml" }
YML;
        $globalDataGridConfig = <<<YML
columns:
  title:
    type: text
    options:
      label: News Title
YML;
        $this->createConfigurationFile('FooBundle/Resources/config/datagrid/foo_news.yml', $fooBundleDataGridConfig);
        $this->createConfigurationFile('config/datagrid/news.yml', $globalDataGridConfig);

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new DataGridEvent($dataGrid, array());

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo_news'));

        $dataGrid->expects($dataGridSpy = $this->any())
            ->method('addColumn');

        $this->subscriber->readConfiguration($event);

        $this->assertThereColumnExists($dataGridSpy->getInvocations(), array(
            'title' => array(
                'type' => 'text',
                'options' => array(
                    'label' => 'News Title'
                )
            ),
            'author' => array(
                'type' => 'text',
                'options' => array(
                    'label' => 'Author'
                )
            )
        ));
    }

    /**
     * @param array $invocations
     * @param array $configuration
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public static function assertThereColumnExists($invocations, $configuration)
    {
        foreach ($configuration as $columnName => $config) {
            $error = true;
            foreach ($invocations as $invocation) {
                if (self::columnExistInInvocation($invocation, $columnName, $config)) {
                    $error = false;
                    break;
                }
            }
            if ($error) {
                throw new \PHPUnit_Framework_AssertionFailedError(
                    sprintf(
                        'Column %s does not exist.',
                        $columnName
                    )
                );
            }
        }
    }

    /**
     * @param object $invocation
     * @param string $columnName
     * @param array $columnOptions
     * @return bool
     */
    public static function columnExistInInvocation($invocation, $columnName, $columnOptions)
    {
        $invocationColumnName = $invocation->parameters[0];
        $invocationColumnType = $invocation->parameters[1];
        $invocationColumnOptions = $invocation->parameters[2];

        if ($columnName == $invocationColumnName ) {
            self::assertEquals($columnOptions['type'], $invocationColumnType);
            self::assertEquals($columnOptions['options'], $invocationColumnOptions);
            return true;
        }

        return false;
    }
}