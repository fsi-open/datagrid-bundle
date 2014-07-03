<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DataGrid\Extension\Configuration;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLoader;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ResourceLocator;
use FSi\Bundle\DataGridBundle\Tests\Double\StubBundle;
use FSi\Bundle\DataGridBundle\Tests\Double\StubKernel;
use FSi\Component\DataGrid\DataGridEvent;
use FSi\Component\DataGrid\DataGridEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class ResourceLocatorTest extends \PHPUnit_Framework_TestCase
{
    const FIXTURE_PATH = '/tmp/DataGridBundle/app';
    /**
     * @var KernelInterface
     */
    protected $kernel;

    protected $resourceLocator;

    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\ConfigurationLocator
     */
    protected $configurationLocator;

    public function setUp()
    {
        $this->kernel = new StubKernel(self::FIXTURE_PATH);
        $this->resourceLocator = new ResourceLocator($this->kernel, 'datagrid');
    }

    public function testLocateGlobalResource()
    {
        $resourcePath = $this->resourceLocator->locate('news.yml');

        $this->assertEquals(
            $resourcePath,
            sprintf("%s/config/datagrid/news.yml", $this->kernel->getRootDir())
        );
    }

    public function testLocateBundleResource()
    {
        $this->kernel->injectBundle(new StubBundle('BarBundle', $this->kernel->getRootDir()));
        $resourcePath = $this->resourceLocator->locate('BarBundle:news.yml');

        $this->assertEquals(
            $resourcePath,
            sprintf("%s/BarBundle/Resources/config/datagrid/news.yml", $this->kernel->getRootDir())
        );

    }
}
