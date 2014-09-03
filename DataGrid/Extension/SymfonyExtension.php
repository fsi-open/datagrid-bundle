<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension;

use FSi\Component\DataGrid\DataGridAbstractExtension;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\EventSubscriber;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\ColumnType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SymfonyExtension extends DataGridAbstractExtension
{
    /**
     * FormFactory used by extension to build forms.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $formFactory
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadColumnTypes()
    {
        return array(
            new ColumnType\Action($this->container),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadSubscribers()
    {
        return array(
            new EventSubscriber\BindRequest(),
        );
    }
}
