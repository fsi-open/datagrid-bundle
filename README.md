# FSi DataGrid Bundle #

Main purpose of this bundle is to register ``FSi DataGrid Component`` service 
and twig rendering functions. 

## Installation ##

* Download DataGridBundle
* Enable the bundle
* Configure the DataGridBundle in config.yaml 

### Step1: Download DataGridBundle ###

Add to composer.json 

```

"require": {
    "fsi/datagrid-bundle": "0.9.0
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

## Usage ##

### Create DataGrid in Controller ###

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
    {{ datagrid_widget(datagrid, {wrapper_attributes : {'class' : 'table table-hover', 'id' : 'table-edit-rows'}}) }}
    </form>
</div>

```

## Additional Column Options ##

There are two additional column options ``header`` and ``cell`` added by DataGridBundle.

* ``header`` -- affects Column HeaderView
    * ``wrapper_attributes``
    * ``wrapper_attributes``
    * ``label_tag`` - default ``span``
* ``cell`` -- affects Column CellView
    * ``wrapper_attributes``
    * ``value_attributes``
    * ``value_tag`` - default ``div``
    * ``form`` - only if column has ``editable`` option
        * ``wrapper_attributes``
        * ``wrapper_tag`` - default ``div``
        * ``submit`` - default ``true``
        * ``submit_attributes`` 

Example usage: 

```
<?php
    
    $column->add("name", "text", array(
        "editable" => true,
        "header" => array(
            "label_tag" = "div",
            "label_attributes" => array)
                "class" => "test"
            ),
            "wrapper_attributes" => array(
                "id" = "author"
            )
        ),
        "cell" => array(
            "wrapper_attributes" => array(
                "class" => "form-group"
            ),
            "value_attributes" => array(
                "class" = "edit_field"
            ),
            "form" => array(
                "wrapper_attributes" => array(
                    "class" = "form-edit hide"
                ),
                "submit_attributes" = array(
                    "class" = "submit",
                )
            )
        ),
    );

```

In most cases the options for all columns will be very similar. There should a mechanism in your code that will 
pass default options to columns.


## Twig Extension ##

``FSiDataGridBundle`` provides also a Twig extension that allow you to render DataGrid with one function call.  
Here is a list of DataGrid rendering functions (widgets) for twig. 
Almost every single widget is rendered from block that is stored in ``DataGridBundle::datagrid.html.twig``

###datagrid_widget###
This widget can render entire DataGrid.   
**Block name** - ``datagrid``

**Available arguments:**  

* ``view`` **(required)** - must be an instance of DataGridView  
* ``options`` **(optional)** - array, currently only one option can be passed inside of options argument. 
``wrapper_attributes``. 

**Example**

```
datagrid_widget(datagrid, {'wrapper_attributes' : {'class' : 'table'} })
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
datagrid_column_header(header)
```

###ddatagrid_column_cell_widget###
This widget is used to render specific column cell.  
**Block name** - ``datagrid_column_cell``

**Available arguments:**  
* ``view`` **(required)** - must be an instance of HeaderViewInterface  
* ``vars`` **(optional)** - additional value passed to block rendering context under 'vars' key. It might be
usefull if you need to overwrite ``datagrid_column_cell`` block and add into it some extra variables. 

**Example**

```
datagrid_column_cell(cell)
```
