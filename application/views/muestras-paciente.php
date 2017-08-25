<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Laboratorio</li>
  <li class="active"> Generar Examen Clínico </li>
</ol>
<div class="container-fluid" ng-controller="generarAnalisisController">
  <div class="row">
  	<div class="col-md-12">
  		<div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
  			<div class="panel-heading">
            <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div>
            <h2>Generar Examen</h2> 
        </div>

        <div class="panel-body">
          <ul class="form-group demo-btns">
              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
              <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
              <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li>
              <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
          </ul>
          <div class="well well-transparent boxDark col-xs-12 m-n">
            <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
