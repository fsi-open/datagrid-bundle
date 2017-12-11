# This is an upgrade guide from versions 1.x to 2.0

## Replace removed config parameter if used

`fsi_data_grid.twig.template` configuration parameter has been removed, so you
need to replace it with the `fsi_data_grid.twig.themes` parameter instead.

## Rewritten DependencyInjectionExtension

This extension receives real services in constructor so if for any reason any
of your classes inherited from it, then its constructor should be corrected.

## Autoconfiguration of DataGrid extensions 

Any custom datagrid extension are no longer needed to be tagged manually
with `'datagrid.*'` tags.
