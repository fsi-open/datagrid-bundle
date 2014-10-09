<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader\FileLoader;
use FSi\Component\DataGrid\DataGridEventInterface;
use FSi\Component\DataGrid\DataGridEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigurationBuilder implements EventSubscriberInterface
{
    /**
     * @var \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader\FileLoader
     */
    protected $fileLoader;

    /**
     * @param \FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\Loader\FileLoader $fileLoader
     */
    function __construct(FileLoader $fileLoader)
    {
        $this->fileLoader = $fileLoader;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(DataGridEvents::PRE_SET_DATA => array('readConfiguration', 128));
    }

    /**
     * {@inheritdoc}
     */
    public function readConfiguration(DataGridEventInterface $event)
    {
        $dataGrid = $event->getDataGrid();
        $this->fileLoader->setDataGrid($dataGrid);
        $this->fileLoader->load($dataGrid->getName() . '.yml');
    }
}
