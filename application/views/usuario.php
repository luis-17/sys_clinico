<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Seguridad</li>
  <li class="active">Usuario</li>
</ol>
<div class="container-fluid" ng-controller="usuarioController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Gestión de Usuario. </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-info" ng-click='btnHabilitar();'>Habilitar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length > 0 " ><button type="button" class="btn btn-danger" ng-click='btnDeshabilitar();'>Deshabilitar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-warning" ng-click='btnEditar();'>Editar</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-default" ng-click='btnCambiarPassword();'>Cambiar Contraseña</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-default" ng-click='btnAdministrarReportes();'>Administrar Reportes</button></li>
                    <li class="pull-right" ng-if="fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && fSessionCI.key_group == 'key_sistemas'"><button type="button" class="btn btn-default" ng-click='btnResetPassword();'>Restablecer Contraseña</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
              </div>
            </div>
        </div>
    </div>
</div>