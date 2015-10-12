<?php

/**
 * (c) Fabryka Stron Internetowych sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to assemble datagrid mappers into the mappers chain.
 */
class DataMapperPass implements CompilerPassInterface
{
    /**
     * Collects services that have a mapper tag ("datagrid.data_mapper"),
     * sorts by their priorities and inject them to chain mapper. Priority
     * attribute should be represented as a number.
     *
     * @param ContainerBuilder $container Container builder
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('datagrid.data_mapper.chain')) {
            return;
        }

        $dataMappers = array();

        foreach ($container->findTaggedServiceIds('datagrid.data_mapper') as $serviceId => $tag) {
            $priority = isset($tag[0]['priority'])
                ? $tag[0]['priority']
                : 0;

            $dataMappers[$priority] = new Reference($serviceId);
        }

        ksort($dataMappers, SORT_NUMERIC);

        $container->getDefinition('datagrid.data_mapper.chain')->replaceArgument(0, $dataMappers);
    }
}
