<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\DataGridTest;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use Symfony\Component\HttpKernel\KernelInterface;

class ConfigurationLoaderTest extends DataGridTest
{

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationImporter
     */
    protected $configurationImporter;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    public function setUp()
    {
        $this->kernel = new StubKernel(self::FIXTURE_PATH);
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->kernel->getRootDir()));
        $this->configurationImporter = $this->getMockBuilder('FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationImporter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareFileSystem();

        $this->configurationLoader = new ConfigurationLoader($this->configurationImporter);
    }

    public function tearDown()
    {
        $this->destroyFileSystem();
    }

    public function testLoadConfig()
    {
        $bundleConfig = <<<YML
columns:
  title:
    type: text
    options:
      label: News Title
YML;

        $this->createConfigurationFile('BarBundle/Resources/config/datagrid/news.yml', $bundleConfig);

        $parsedConfig = array(
            'columns' => array(
                'title' => array(
                    'type' => 'text',
                    'options' => array(
                        'label' => 'News Title'
                    )
                )
            )
        );

        $this->configurationImporter->expects($this->any())
            ->method('import')
            ->with($this->equalTo($parsedConfig))
            ->will($this->returnValue($parsedConfig));

        $loadedConfig = $this->configurationLoader->load(sprintf(
            '%s/BarBundle/Resources/config/datagrid/news.yml',
            $this->kernel->getRootDir()
        ));

        $this->assertEquals($parsedConfig, $loadedConfig);

    }
}
