<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\Double;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 *
 * Class StubKernel
 * @package FSi\Bundle\DataGridBundle\Tests\Double
 * @author Michal Szczur <michal.szczur@fsi.pl>
 */
class StubKernel implements KernelInterface
{
    private $bundles;

    public function __construct(array $bundles = array())
    {
        foreach($bundles as $bundle) {
            $this->bundles[$bundle] = new StubBundle($bundle);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return __DIR__ . '/../Fixtures';
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isClassInActiveBundle($class)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBundle($name, $first = true)
    {
        return $this->bundles[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    }

    public function getEnvironment()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getStartTime()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCharset()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function locateResource($name, $dir = null, $first = true)
    {
    }
}