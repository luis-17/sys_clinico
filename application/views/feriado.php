<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Capital Humano</li>
  <li>Mantenimiento</li>
  <li class="active">Feriado</li>
</ol>
<div class="container-fluid" ng-controller="feriadoController">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
        <div class="panel-heading">
          <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
          <h2>Gestión de Feriados</h2> 
        </div>
        <div class="panel-editbox" data-widget-controls=""></div>
        <div class="panel-body">
          <ul class="form-group demo-btns">
              <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()' style="margin-top: -6px">Buscar</button></li>
              <li > <select ng-change="getPaginationServerSide();getPascua();" class="form-control" ng-model="fBusqueda.anyo" ng-options="item as item.descripcion for item in listaAnyo" > </select> </li>
              <li class="pull-right" ng-if="mySelectionGrid.length > 0" ><button type="button" class="btn btn-danger" ng-click='btnAnular()'>Anular</button></li>
              <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-warning" ng-click='btnEditar()'>Editar</button></li> -->
              <li class="pull-right"><button type="button" class="btn btn-success" ng-click='btnNuevo()'>Nuevo</button></li>
          </ul>
          <div class="row">
            <div class="col-xs-12">
              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns ui-grid-edit class="grid table-responsive"></div>
            </div>
          </div>
        </div>
      </div>
      <!-- datepicker de prueba -->
      
      <!-- <div class="panel panel-default" data-widget='{"draggable": "false"}'>
        <div class="panel-heading">
          <h2>Date Picker</h2>
          <div class="panel-ctrls" data-actions-container="" data-action-collapse='{"target": ".panel-body"}'></div>
        </div>
        <div class="panel-body">
          <div class="form-horizontal row-border">

            <div class="form-group">
              <label for="#" class="col-md-3 control-label">Inline</label>
              <div class="col-md-6">
                <div uib-datepicker ng-model="dt" class="date-table" date-disabled="disabled(date, mode)"
                  min-date="minDate"
                  show-weeks="false"
                  max-mode="month"
                  starting-day="1"
                  ></div>
              </div>
            </div>

            <div class="form-group">
              <label for="#" class="col-md-3 control-label">Input Group</label>
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control datepicker" datepicker-popup="{{format}}" ng-model="dt" is-open="opened" min-date="minDate" max-date="'2016-11-30'" datepicker-options="dateOptions" date-disabled="disabled(date, mode)" ng-required="true" close-text="Close" />
                  <div class="input-group-btn">
                    <button type="button" class="btn btn-default" ng-click="open($event)"><i class="ti ti-calendar"></i></button>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-sm-3">Format:</label> 
              <div class="col-sm-6">
                <select class="form-control" ng-model="format" ng-options="f for f in formats">
                  <option></option>
                </select>
                <br>
                <button type="button" class="btn btn-sm btn-info" ng-click="today()">Hoy</button>
                <button type="button" class="btn btn-sm btn-default" ng-click="dt = '2009-08-01'">2009-08-1</button>
                <button type="button" class="btn btn-sm btn-danger" ng-click="clear()">Clear</button>
                <button type="button" class="btn btn-sm btn-default" ng-click="toggleMin()" tooltip="After today restriction">Min date</button>
              </div>
            </div>

            <div class="form-group">
              <div class="col-md-6 col-md-offset-3 control-label">
                <pre>La fecha seleccionada es: <em>{{dt | date:'fullDate' }}</em></pre>
              </div>
            </div>
          </div>
        </div>
      </div> -->
      <!-- fin prueba -->
    </div>
  </div>
</div>