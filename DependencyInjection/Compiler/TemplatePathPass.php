<?php

/**
 * (c) FSi Sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use FSi\Bundle\DataGridBundle\DataGridBundle;

class TemplatePathPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $loaderDefinition = $container->getDefinition('twig.loader.filesystem');;

        $refl = new \ReflectionClass(DataGridBundle::class);
        $path = dirname($refl->getFileName()).'/Resources/views';
        $loaderDefinition->addMethodCall('addPath', [$path]);
    }
}
