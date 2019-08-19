<?php

/**
 * (c) FSi Sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataGridPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('datagrid.extension')) {
            return;
        }

        $columns = [];
        foreach ($container->findTaggedServiceIds('datagrid.column') as $serviceId => $tag) {
            $alias = $tag[0]['alias'] ?? $serviceId;

            $columns[$alias] = new Reference($serviceId);
        }

        $container->getDefinition('datagrid.extension')->replaceArgument(0, $columns);

        $columnExtensions = [];
        foreach ($container->findTaggedServiceIds('datagrid.column_extension') as $serviceId => $tag) {
            $alias = $tag[0]['alias'] ?? $serviceId;

            $columnExtensions[$alias] = new Reference($serviceId);
        }

        $container->getDefinition('datagrid.extension')->replaceArgument(1, $columnExtensions);

        $subscribers = [];
        foreach ($container->findTaggedServiceIds('datagrid.subscriber') as $serviceId => $tag) {
            $alias = $tag[0]['alias'] ?? $serviceId;

            $subscribers[$alias] = new Reference($serviceId);
        }

        $container->getDefinition('datagrid.extension')->replaceArgument(2, $subscribers);
    }
}
