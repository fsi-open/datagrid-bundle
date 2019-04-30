<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle;

use FSi\Bundle\DataGridBundle\DependencyInjection\Compiler\DataGridPass;
use FSi\Bundle\DataGridBundle\DependencyInjection\Compiler\ExtensionsPass;
use FSi\Bundle\DataGridBundle\DependencyInjection\Compiler\TemplatePathPass;
use FSi\Bundle\DataGridBundle\DependencyInjection\FSIDataGridExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DataGridBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        // this needs to be first, otherwise the extensions will not be loaded in
        // DataGridPass
        $container->addCompilerPass(new ExtensionsPass());
        $container->addCompilerPass(new DataGridPass());
        $container->addCompilerPass(new TemplatePathPass());
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new FSIDataGridExtension();
        }

        return $this->extension;
    }
}
