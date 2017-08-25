<script type="text/ng-template" id="nodes_renderer.html">
  <div ui-tree-handle class="tree-node tree-node-content" data-nodrag ng-click="verNode(this);">
    <a class="btn btn-success btn-xs" ng-if="node.nodes && node.nodes.length > 0" data-nodrag ng-click="toggle(this)"><span
        class="glyphicon"
        ng-class="{
          'glyphicon-chevron-right': collapsed,
          'glyphicon-chevron-down': !collapsed
        }"></span></a>
    {{node.title}}
    <a class="pull-right btn btn-danger btn-xs" data-nodrag ng-click="remove(this)"><span
        class="glyphicon glyphicon-remove"></span></a>
    <a class="pull-right btn btn-primary btn-xs" data-nodrag ng-click="newSubItem(this)" style="margin-right: 8px;"><span
        class="glyphicon glyphicon-plus"></span></a>
  </div>
  <ol ui-tree-nodes="" ng-model="node.nodes" ng-class="{hidden: collapsed}">
    <li ng-repeat="node in node.nodes" ui-tree-node ng-include="'nodes_renderer.html'">
    </li>
  </ol>
</script>

<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Contabilidad</li>
  <li>Mantenimiento</li>
  <li class="active">Plan Contable</li>
</ol>
<div class="container-fluid" ng-controller="planContableController">
    <!-- <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Plan Contable</h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div> -->


  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
        <div class="panel-heading">
          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
          <h2>Gestión de Plan Contable</h2> 
        </div>
        <div class="panel-editbox" data-widget-controls=""></div>
        <div class="panel-body">

          <div class="col-sm-6">
            <button ng-click="expandAll()">Expand all</button>
            <button ng-click="collapseAll()">Collapse all</button>
            <div ui-tree id="tree-root">
              <ol ui-tree-nodes ng-model="data">
                <li ng-repeat="node in data" ui-tree-node ng-include="'nodes_renderer.html'"></li>
              </ol>
            </div>
          </div>
        
        </div>
      </div>
    </div>
  </div>
</div>
<!--   <div class="row"> -->

   <!--  <div class="col-sm-6">
      <div class="info">
        {{info}}
      </div>
      <pre class="code">{{ data | json }}</pre>
    </div> -->
  

