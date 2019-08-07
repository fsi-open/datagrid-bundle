# Configuration reference

Below you can find all avaible bundle options along with their default values:

```yaml
fsi_data_grid:
    yaml_configuration:
        enabled: true
        # Use this option to provide a global directory path, from which configuration
        # files will be fetched. These will override any files that would have been
        # loaded from bundles directories.
        main_configuration_directory: null
    twig:
        enabled: true
        themes:
            - '@DataGrid/datagrid.html.twig'
```
