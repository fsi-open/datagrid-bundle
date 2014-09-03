<?php

namespace FSi\Bundle\DataGridBundle\Tests\Double;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class StubBundle implements BundleInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
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
    public function build(ContainerBuilder $container)
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return sprintf('%s/../Fixtures/%s', __DIR__, $this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        
    }
}
