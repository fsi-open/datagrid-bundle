<?php

namespace spec\FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber;

use FSi\Component\DataGrid\DataGrid;
use FSi\Component\DataGrid\DataGridEventInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;

class ConfigurationBuilderSpec extends ObjectBehavior
{

    function let(KernelInterface $kernelInterface)
    {
        /** @var $this TYPE_NAME */
        $this->beConstructedWith($kernelInterface);

    }

    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\DataGridBundle\DataGrid\Extension\Configuration\EventSubscriber\ConfigurationBuilder');
    }

    function it_should_read_configuration_for_admin(
        KernelInterface $kernelInterface,
        DataGridEventInterface $dataGridEventInterface,
        DataGrid $dataGrid,
        Bundle $bundle
    ) {

        $dataGridEventInterface->getDataGrid()->willReturn($dataGrid);

        $kernelInterface->getBundles()->willReturn(array($bundle));

        $bundle->getPath()->willReturn('spec/FSi/Bundle/DataGridBundle/');

        $dataGrid->getName()->willReturn('admin_news');

        $kernelInterface->locateResource(Argument::type('string'))->shouldBeCalled();

        $dataGrid->addColumn('title','text',array('label'=>'admin.news.datagrid.title'))->shouldBeCalled();

        $dataGrid->addColumn('actions','action',array(
            'label'=>'admin.news.datagrid.actions',
            'field_mapping' => array('id'),
            'actions' => array(
                'edit' => array(
                    'route_name' => 'fsi_admin_crud_edit',
                    'additional_parameters' => array('element'=>'news'),
                    'parameters_field_mapping' => array('id'=>'id')

                ),
                'delete' => array(
                    'route_name' => 'fsi_admin_crud_delete',
                    'additional_parameters' => array('element'=>'news'),
                    'parameters_field_mapping' => array('id'=>'id')

                )
            )
        ))->shouldBeCalled();

        $this->readConfiguration($dataGridEventInterface);

    }


    function it_should_read_configuration(
        KernelInterface $kernelInterface,
        DataGridEventInterface $dataGridEventInterface,
        DataGrid $dataGrid,
        Bundle $bundle
    ) {
        $dataGridEventInterface->getDataGrid()->willReturn($dataGrid);
        $kernelInterface->getBundles()->willReturn(array($bundle));

        $bundle->getPath()->willReturn('spec/FSi/Bundle/DataGridBundle/');

        $dataGrid->getName()->willReturn('news');

        $dataGrid->addColumn('title','text',array('label'=>'admin.news.datagrid.title'))->shouldBeCalled();

        $dataGrid->addColumn('actions','action',array(
            'label'=>'admin.news.datagrid.actions',
            'field_mapping' => array('id'),
            'actions' => array(
                'edit' => array(
                    'route_name' => 'fsi_admin_crud_edit',
                    'additional_parameters' => array('element'=>'news'),
                    'parameters_field_mapping' => array('id'=>'id')

                )
            )
        ))->shouldBeCalled();

        $this->readConfiguration($dataGridEventInterface);

    }

}
