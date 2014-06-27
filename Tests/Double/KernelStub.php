<?php

namespace FSi\Bundle\DataGridBundle\Tests\Double;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class KernelStub implements KernelInterface
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
    public function getBundles()
    {
        return $this->bundles;
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
    public function locateResource($name, $dir = null, $first = true)
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        
    }

    /**
     * {@inheritdoc}
     */
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
    public function getRootDir()
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
}
