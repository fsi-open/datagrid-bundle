<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use FSi\Component\DataGrid\DataGrid;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\HttpKernel\KernelInterface;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder;

class ConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $stream;

    /**
     * @var ConfigurationBuilder
     */
    protected $subscriber;

    private function initConfigurationBuilder()
    {
        $configurationLocator = new ConfigurationLocator($this->kernel);
        $this->configurationLoader = new ConfigurationLoader($this->kernel, $configurationLocator);
        $this->subscriber = new ConfigurationBuilder($this->kernel, $this->configurationLoader);
    }

    public function setUp()
    {
        $this->stream = vfsStream::setup("Fixtures");
        $this->kernel = new StubKernel($this->stream->url());

        $this->initConfigurationBuilder();
    }

    public function tearDown()
    {
        $this->stream = null;
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            $this->subscriber->getSubscribedEvents(),
            array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128))
        );
    }

    public function testImportFromGlobalConfig()
    {
        $configFile = <<<YML
columns:
  author:
    type: text
    options:
      label: Author

imports:
  - { resource: "/app/config/datagrid/global.yml" }
YML;
        $globalFile = <<<YML
columns:
  title:
    type: text
    options:
      label: Title
YML;

        $this->kernel->removeBundles();
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->stream->url()));
        $this->initConfigurationBuilder();

        $this->createConfigFile('FooBundle/Resources/config/datagrid/bundle.yml', $configFile);
        $this->createConfigFile('app/config/datagrid/global.yml', $globalFile);

        /** @var DataGrid $dataGrid */
        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bundle'));

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with(
                $this->equalTo('title'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Title'))
            );

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author'))
            );


        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);

    }

    public function testImportFromAnotherBundle()
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
      label: Title
YML;

        $this->kernel->removeBundles();
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->stream->url()));
        $this->kernel->injectBundle(new StubBundle('BarBundle', $this->stream->url()));
        $this->initConfigurationBuilder();

        $this->createConfigFile('FooBundle/Resources/config/datagrid/news.yml', $fooBundleDatagridConfig);
        $this->createConfigFile('BarBundle/Resources/config/datagrid/news.yml', $barBundleDatagridConfig);

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->at(0))
            ->method('getName')
            ->will($this->returnValue('news'));
        $dataGrid->expects($this->at(1))
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->at(4))
            ->method('addColumn')
            ->with(
                $this->equalTo('title'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Title'))
            );

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author'))
            );

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    public function testImportFromSameDirectory()
    {

        $authorConfig = <<<YML
columns:
  author:
    type: text
    options:
      label: Author

imports:
  - { resource: "news_extended.yml" }
YML;

        $titleConfig = <<<YML
columns:
  title:
    type: text
    options:
      label: Title
YML;

        $this->kernel->removeBundles();
        $this->kernel->injectBundle(new StubBundle('FooBundle', $this->stream->url()));
        $this->initConfigurationBuilder();

        $this->createConfigFile('FooBundle/Resources/config/datagrid/news.yml', $authorConfig);
        $this->createConfigFile('FooBundle/Resources/config/datagrid/news_extended.yml', $titleConfig);

        $dataGrid = $this->getMockBuilder('FSi\Component\DataGrid\DataGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('news'));

        $dataGrid->expects($this->at(3))
            ->method('addColumn')
            ->with(
                $this->equalTo('title'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Title'))
            );

        $dataGrid->expects($this->at(2))
            ->method('addColumn')
            ->with(
                $this->equalTo('author'),
                $this->equalTo('text'),
                $this->equalTo(array('label' => 'Author'))
            );

        $event = new DataGridEvent($dataGrid, array());

        $this->subscriber->readConfiguration($event);
    }

    private function createConfigFile($fileName, $content)
    {
        $path = sprintf("%s/%s", $this->kernel->getRootDir(), $fileName);
        $dirName = dirname($path);

        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }

        file_put_contents($path, $content);

        return $path;
    }

}
