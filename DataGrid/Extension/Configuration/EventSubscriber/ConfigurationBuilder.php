<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Component\DataGrid\DataGridEventInterface;
use FSi\Component\DataGrid\DataGridEvents;
use FSi\Component\DataGrid\DataGridInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;

class ConfigurationBuilder implements EventSubscriberInterface
{
    /**
     * Datagrid configuration
     *
     * @var array
     */
    protected $datagridConfiguration;

    /**
     * Constructor
     *
     * @param array $datagridConfiguration Datagrid configuration
     */
    public function __construct(array $datagridConfiguration)
    {
        $this->datagridConfiguration = $datagridConfiguration;
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(DataGridEvents::PRE_SET_DATA => array('configureDatagrid', 128));
    }


    /**
     * Configure datagrid
     *
     * @param DataGridEventInterface $event DataGrid PRE_SET_DATA event
     *
     * @return void
     */
    public function configureDatagrid(DataGridEventInterface $event)
    {
        $dataGrid = $event->getDataGrid();
        if (array_key_exists($dataGrid->getName(), $this->datagridConfiguration)) {
            foreach ($this->datagridConfiguration[$dataGrid->getName()]['columns'] as $name => $column) {
                $type = array_key_exists('type', $column)
                    ? $column['type']
                    : 'text';
                $options = array_key_exists('options', $column)
                    ? $column['options']
                    : array();

                $dataGrid->addColumn($name, $type, $options);
            }
        }
    }
}
