# This file contains changes in the 2.x branch

## Deleted deprecated template configuration parameter

The `fsi_data_grid.twig.template` configuration parameter has been removed and 
now you are required to use the `fsi_data_grid.twig.themes` parameter instead.

## Rewritten DependencyInjectionExtension

This extension has been completely rewritten and now accepts real services in 
constructor instead of just their IDs.

## Autoconfiguration of DataGrid extensions

All services implementing the following interfaces are now automatically tagged
with corresponding tags:

`FSi\Component\DataGrid\DataGridExtensionInterface` - `'datagrid.extension`
`FSi\Component\DataGrid\Column\ColumnTypeInterface` - `'datagrid.column'`
`FSi\Component\DataGrid\Column\ColumnTypeExtensionInterface` - `'datagrid.column_extension'`
`FSi\Bundle\DataGridBundle\DataGrid\EventSubscriberInterface` - `'datagrid.subscriber'`
