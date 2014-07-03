<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use Symfony\Component\HttpKernel\KernelInterface;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    const FIXTURE_PATH = '/tmp/DataGridBundle';

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
        $this->configurationLoader = new ConfigurationLoader($this->configurationImporter);
    }

    public function testLoadConfig()
    {
        $this->configurationLoader->load(sprintf(
            '%s/BarBundle/Resources/config/datagrid/news.yml',
            $this->kernel->getRootDir()
        ));

    }
}
