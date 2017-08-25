<ol class="breadcrumb m-n">
  <li><a href="#/">INICIO</a></li>
  <li>ASISTENCIA</li>
  <li class="active">MARCAR ASISTENCIA</li>
</ol>
<div class="container-fluid" ng-controller="marcarAsistenciaController">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-danger">
          <div class="panel-heading">
            <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
            <h2>MARCACIÓN DE ASISTENCIA</h2>
          </div>
          <div class="panel-body">
          <form id="formAsistencia" name="formAsistencia">
            <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr mt-sm col-md-2 col-sm-6 col-xs-12 p-n" ng-if=" fSessionCI.key_group == 'key_sistemas' "> 
                    <div class="input-group col-sm-12 col-md-12" > 
                        <input tabindex="110" type="text" class="form-control input mask" ng-model="fData.fecha" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" required="true" />
                        <input tabindex="115" type="text" class="form-control input mask" ng-model="fData.hora" style="width: 17%; margin-left: 4px;" ng-pattern="pHora" required="true" />
                        <input tabindex="116" type="text" class="form-control input mask" ng-model="fData.minuto" style="width: 17%; margin-left: 4px;" ng-pattern="pMinuto" required="true" />
                      </div>
                  </li>
                  
                  <li class="form-group mr mt-sm col-md-2 col-sm-6 col-xs-12 p-n"> 
                    <input id="fData.codigo" tabindex="100" type="text" class="form-control" maxlength="8" minlength="8" ng-model="fData.codigo" 
                      placeholder="Ingrese código de empleado." focus-me /> 
                  </li>
                  <li class="col-md-3 col-sm-6 col-xs-12 pull-right">
                    <div style="font-size: 42px; margin-top: 24px; text-align: center;"> {{ clock | date:'mediumTime' }} </div>
                  </li>
              </ul>
          </form>
            
          </div>
        </div>
      </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger">
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>LISTADO DE ASISTENCIA</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                    <div class="input-group col-sm-12 col-md-12" > 
                      <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 17%; margin-left: 4px;" />
                      <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 17%; margin-left: 4px;" />
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                    <div class="input-group col-sm-12 col-md-12" > 
                      <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 17%; margin-left: 4px;" />
                      <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 17%; margin-left: 4px;" />
                    </div> 
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> 
                    <div class="input-group" style=""> 
                      <button type="button" class="btn btn-info" ng-click="getPaginationServerSide();">
                          <i class="ti ti-reload"> </i> PROCESAR
                      </button>
                    </div> 
                  </li>
                </ul>
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive">
                    <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>