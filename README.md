# FSi DataGrid Bundle #

Main purpose of this bundle is to register ``FSi DataGrid Component`` service
and twig rendering functions.

#Installation#

* Download DataGridBundle
* Enable the bundle
* Configure the DataGridBundle in config.yaml

### Step1: Download DataGridBundle ###

Add to composer.json

```

"require": {
    "fsi/datagrid-bundle": "0.9.*
}

```

### Step2: Enable the bundle ###

```

<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FSi\Bundle\DataGridBundle\DataGridBundle(),
    );
}

```

### Step3: Configure the DataGridBundle in config.yaml ###

Add to config.yaml

```
fsi_data_grid:
    twig: ~
```

# Usage #

Basic DataGrid usage.

```

<?php

namespace FSi\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="demo_index")
     * @Template()
     */
    public function indexAction()
    {
        $array = $this->getDoctrine()->getManager()
            ->getRepository('FSiSiteBundle:News')
            ->findAll();

        $dataGrid = $this->get('datagrid.factory')->createDataGrid();
        $dataGrid->addColumn('id', 'number', array(
            'label' => 'identity'
        ));
        $dataGrid->addColumn('title', 'text', array(
            'editable' => true
        ));
        $dataGrid->addColumn('author', 'text', array(
            'editable' => true
        ));

        $dataGrid->setData($array);
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $dataGrid->bindData($request);
            $this->getDoctrine()->getManager()->flush();
        }

        return array(
            'datagrid' => $dataGrid->createView()
        );
    }
}

```

How to display created datagrid in twig template:

```

{# src/FSi/Bundle/DemoBundle/Resources/views/Default/index.html.twig #}

<div class="table-border">
    <form action="{{ path('demo_index') }}" method="post">
    {{ datagrid_widget(datagrid) }}
    </form>
</div>

```

# Twig Extension #

##Available Twig Functions##

``FSiDataGridBundle`` provides also a Twig extension that allows you to render DataGrid with one function call.
Here is a list of DataGrid rendering functions (widgets) for twig.
Almost every single widget is rendered from block that is stored in ``DataGridBundle::datagrid.html.twig``

###datagrid_widget###
This widget can render entire DataGrid.
**Block name** - ``datagrid``

**Available arguments:**

* ``view`` **(required)** - must be an instance of DataGridView

**Example**

```
datagrid_widget(datagrid)
```

###datagrid_header_widget###
This widget is used to render row with column headers (Table Head).
**Block name** - ``datagrid_header``

**Available arguments:**
* ``view`` **(required)** - must be an instance of DataGridView
* ``vars`` **(optional)** - additional value passed to block rendering context under 'vars' key. It might be
usefull if you need to overwrite ``datagrid_header`` block and add into it some extra variables.

**Example**

```
datagrid_header_widget(datagrid)
```

###datagrid_rowset_widget###
This widget is used to render every single datagrid row in loop.
**Block name** - ``datagrid_rowset``

**Available arguments:**
* ``view`` **(required)** - must be an instance of DataGridView
* ``vars`` **(optional)** - additional value passed to block rendering context under 'vars' key. It might be
usefull if you need to overwrite ``datagrid_header`` block and add into it some extra variables.

**Example**

```
datagrid_rowset_widget(datagrid)
```

###datagrid_column_header_widget###
This widget is used to render specific column header.
**Block name** - ``datagrid_column_header``

**Available arguments:**
* ``view`` **(required)** - must be an instance of HeaderViewInterface
* ``vars`` **(optional)** - additional value passed to block rendering context under 'vars' key. It might be
usefull if you need to overwrite ``datagrid_column_header`` block and add into it some extra variables.

**Example**

```
datagrid_column_header_widget(header)
```

###datagrid_column_cell_widget###
This widget is used to render specific column cell.
**Block name** - ``datagrid_column_cell``

**Available arguments:**
* ``view`` **(required)** - must be an instance of CellViewInterface
* ``vars`` **(optional)** - additional value passed to block rendering context under 'vars' key. It might be
usefull if you need to overwrite ``datagrid_column_cell`` block and add into it some extra variables.

**Example**

```
ddatagrid_column_cell_widget(cell)
```

###datagrid_column_cell_form_widget###
This widget is used to render form if column is editable.
**Block name** - ``datagrid_column_cell_form``

**Available arguments:**
* ``view`` **(required)** - must be an instance of CellViewInterface
* ``vars`` **(optional)** - additional value passed to block rendering context under 'vars' key. It might be
usefull if you need to overwrite ``datagrid_column_cell`` block and add into it some extra variables.

**Example**

```
ddatagrid_column_cell_widget(cell)
```

##Theming DataGrid##

Default DataGrid block used to render each parts of DataGrid are very simple, but in most cases you will
need to overwrite them.
This can be easily done with theming mechanism.
Theme is nothing else than a twig template that contains specific blocks.

###Basic Themes###

If you want to set theme for your DataGridView Object you need to use special tag ``datagrid_theme``.

example:

```
{% block body %}
    {% datagrid_theme datagrid_view 'FSiDemoBundle::datagrid.html.twig' %}

    {{ datagrid_widget(datagrid_view) }}
{% endblock %}

```

Now in file ``FSiDemoBundle::datagrid.html.twig`` you can create block ``datagrid`` and ``datagrid_widget`` will use
it to render DataGridView.
**Heads Up!!** You can also pass any kind of resources into theme. All you need to do is to pass them in array

```
{% datagrid_theme datagrid_view 'FSiDemoBundle::datagrid.html.twig' with {'ds' : datasource} %}
```

And then ``datasource`` object will be available in all blocks from theme under ``vars.ds``.
You can also use ``_self`` theme location instead of standalone file.

```
{% datagrid_theme datagrid_view _self with {'ds' : datasource} %}
```


```
{# FSiDemoBundle::datagrid.html.twig #}

{% block datagrid %}
    <h2>Custom DataGrid View</h2>
    <table class="table table-hover" id="table-edit-rows">
        <thead>
            {{ datagrid_header_widget(datagrid) }}
        </thead>
        <tbody>
            {{ datagrid_rowset_widget(datagrid) }}
        </tbody>
    </table>
{% endblock %}
```
Simple, isn't it?

But what if you have to render two different DataGridView objects, one in admin panel ane second one in user part?
You can simply create two different themes and use them depending on situation, but there is also another way.
There is a way to create block for specific datagrid, column cell or column header.
Only thing you need to do is to create block name in proper naming convention.

For ``datagrid_widget`` you can use two patterns:
* {grid_name}_datagrid
* datagrid

Example:

```
{# FSiDemoBundle::datagrid.html.twig #}

{% block admin_datagrid %}
    <h2>DataGrid View for admin panel</h2>
    <table class="table table-hover" id="table-edit-rows">
        <thead>
            {{ datagrid_header_widget(datagrid) }}
        </thead>
        <tbody>
            {{ datagrid_rowset_widget(datagrid) }}
        </tbody>
    </table>
{% endblock %}

{% block orders_datagrid %}
    <h2>Your orders list.</h2>
    <table class="table table-hover" id="table-edit-rows">
        <thead>
            {{ datagrid_header_widget(datagrid) }}
        </thead>
        <tbody>
            {{ datagrid_rowset_widget(datagrid) }}
        </tbody>
    </table>
{% endblock %}
```

##Blocks naming conventions for widgets###

``datagrid_widget``
* {grid_name}_datagrid
* datagrid

``datagrid_header_widget``
* datagrid_{grid_name}_header
* datagrid_header

``datagrid_column_header_widget``
* datagrid_{grid_name}_column_name_{column_name}_header
* datagrid_{grid_name}_column_type_{column_type}_header
* datagrid_column_name_{column_name}_header
* datagrid_column_type_{column_type}_header
* datagrid_{grid_name}_column_header
* datagrid_column_header

``datagrid_rowset_widget``
* datagrid_{grid_name}_rowset
* datagrid_rowset

``datagrid_column_cell_widget``
* datagrid_{grid_name}_column_name_{column_name}_cell
* datagrid_{grid_name}_column_type_{column_type}_cell
* datagrid_column_name_{column_name}_cell
* datagrid_column_type_{column_type}_cell
* datagrid_{grid_name}_column_cell
* datagrid_column_cell

``datagrid_column_cell_form_widget``
* datagrid_{grid_name}_column_name_{column_name}_cell_form
* datagrid_{grid_name}_column_type_{column_type}_cell_form
* datagrid_column_name_{column_name}_cell_form
* datagrid_column_type_{column_type}_cell_form
* datagrid_{grid_name}_column_cell_form
* datagrid_column_cell_form

As you can see there are many ways to overwrite default block even for specific column in specific grid.

# Exports #

Sometimes you need to export data from grid into csv or xls file. ``DataGridBundle`` supports the following
exports formats:
* CSV
* CSVExcel
* Excel - for version 95-2003
* Excel2003 - for version 2003
* Excel2007

To export DataGrid data you need to create special ResponseObject and set it as action return.

```
public function exportAction()
{
    return new \FSi\Bundle\DataGridBundle\HttpFoundation\CSVExport($dataGrid->createView(), 'export_file');
}
```

**Don't forget to update your project dependencies before using export feature!**
If you want to export data to excel formats you need to add into your project ``composer.json`` file following line:

```
{
    "require" : {
        ...
        "phpoffice/phpexcel": "dev-calcEngine",
        ...
    }
}
```