<?php

/**
 * (c) FSi Sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DependencyInjection\Compiler;

use FSi\Bundle\DataGridBundle\DataGridBundle;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TemplatePathPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $loaderDefinition = $container->getDefinition('twig.loader.native_filesystem');

        $reflection = new ReflectionClass(DataGridBundle::class);
        $loaderDefinition->addMethodCall(
            'addPath',
            [dirname($reflection->getFileName()) . '/Resources/views']
        );
    }
}
