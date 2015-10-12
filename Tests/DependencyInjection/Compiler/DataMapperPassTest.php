<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\Tests\DependencyInjection\Compiler;

use FSi\Bundle\DataGridBundle\DependencyInjection\Compiler\DataMapperPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @see FSi\Bundle\DataGridBundle\DependencyInjection\Compiler\DataMapperPass
 */
class DataMapperPassTest extends TestCase
{
    /**
     * Container stub.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * Definition of chain datamapper.
     *
     * @var Definition
     */
    private $chainDefinition;

    /**
     * Initialize container stub with fixture services.
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $chainMapperClass      = 'FSi\Component\DataGrid\DataMapper\ChainMapper';
        $this->chainDefinition = new Definition($chainMapperClass, array(array()));

        $this->container->setDefinition('datagrid.data_mapper.chain', $this->chainDefinition);

        $mappersPriorities = array(
            'third_mapper'  => 30,
            'first_mapper'  => 10,
            'second_mapper' => 20,
        );

        foreach ($mappersPriorities as $serviceId => $priority) {
            $mapperDefinition = new Definition('Whatever');
            $mapperDefinition->addTag('datagrid.data_mapper', ['priority' => $priority]);
            $this->container->setDefinition($serviceId, $mapperDefinition);
        }
    }

    /**
     * Test that tagged services are passed into chain mapper.
     */
    public function testProcessRegular()
    {
        $pass = new DataMapperPass();
        $pass->process($this->container);

        $chainDefinition = $this->container->getDefinition('datagrid.data_mapper.chain');
        $this->assertSame($this->chainDefinition, $chainDefinition);

        $arguments = $chainDefinition->getArguments();
        $this->assertCount(1, $arguments);

        $expectedReferences = array(
            10 => new Reference('first_mapper'),
            20 => new Reference('second_mapper'),
            30 => new Reference('third_mapper'),
        );

        $this->assertEquals($expectedReferences, $arguments[0]);
    }

    /**
     * Test that tagged services are not passed into chain mapper when container
     * has not chain mapper definition.
     */
    public function testNoChainDefinition()
    {
        $this->container->removeDefinition('datagrid.data_mapper.chain');

        $pass = new DataMapperPass();
        $pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition('datagrid.data_mapper.chain'));
        $this->assertEquals(array(array()), $this->chainDefinition->getArguments());
    }
}
