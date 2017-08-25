<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>RR.HH</li>
  <li class="active">Personal de Salud</li>
</ol>
<div class="container-fluid" ng-controller="empleadoSaludController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Gestión de Personal de Salud </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li > <select ng-change="getPaginationServerSide();" class="form-control" ng-model="fBusqueda.tercero" ng-options="item.id as item.descripcion for item in listaTercero" > </select> </li>
                    <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-midnightblue" ng-click='btnAgregarEspecialidad()'>Agregar Especialidad</button></li> -->
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_dir_salud' ) "><button type="button" class="btn btn-success" ng-click='btnConsultarEspecialidad()'> Especialidades</button></li>
                </ul>
                <div  ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
              </div>
            </div>
        </div>
    </div>
</div>