<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Programación Asistencial</li>
  <li class="active">Gestión de Notificaciones</li>
</ol>
<div class="container-fluid" ng-controller="gestionNotificacionesProgController">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}' >
          <div class="panel-heading">
            <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
            <h2>Gestión de Notificaciones</h2>
          </div>
          <div class="panel-editbox" data-widget-controls=""></div>
          <div class="panel-body">
            <div class="row">              
              <div class="col-xs-12">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'> Anular</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-info" ng-click='btnVisible()'> Visible</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-default" ng-click='btnOculta()'> Oculta</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnReenviarNotificacion()'>Reenviar</button></li>                     
                    <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevaNotificacion()'>Nuevo</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive tableRowDinamic"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>