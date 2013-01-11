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


