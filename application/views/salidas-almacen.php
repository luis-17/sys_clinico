<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Caja</li>
  <li class="active">Salidas de Almacen </li>
</ol>
<div class="container-fluid" ng-controller="salidasAlmacenController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Nueva Salida de Almacen </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <form name="formsalidasAlmacen"> 
                  <div class="col-md-12 col-sm-12">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 pr-n pl-n"> Datos de la Salida
                      </legend>
                      <div class="form-group mb-md col-md-2" >
                        <label class="control-label mb-xs">Fecha de Salida </label>  
                        <div class="input-group col-md-12"> 
                          <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataAlmacen.fecha" required tabindex="99" disabled="true" /> 
                        </div>
                      </div>
                      <div class="form-group mb-md col-md-6" >
                        <label class="control-label mb-xs"> Responsable <small class="text-danger">(*)</small></label>
                        <div class="input-group">
                          <span class="input-group-btn ">
                            <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAlmacen.idempleado" placeholder="ID" tabindex="1" ng-enter="obtenerEmpleadoPorCodigo(); $event.preventDefault();" min-length="1" />
                          </span>
                          <input id="fDataAlmacenEmpleado" type="text" class="form-control input-sm" ng-model="fDataAlmacen.empleado" placeholder="Ingrese el Empleado o Click en Seleccionar" typeahead-loading="loadingLocationsEmp" uib-typeahead="item as item.descripcion for item in getEmpleadoAutocomplete($viewValue)" typeahead-on-select="getSelectedEmpleado($item, $model, $label)" typeahead-min-length="2" tabindex="2" autocomplete="off"/>
                          <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaEmpleados('md')">Seleccionar</button>
                          </span>
                        </div>
                        <i ng-show="loadingLocationsEmp" class="fa fa-refresh"></i>
                        <div ng-show="noResultsLD">
                          <i class="fa fa-remove"></i> No se encontró resultados 
                        </div>
                      </div>
                      <div class="form-group mb-md col-md-2">
                        <label class="control-label mb-xs"> Num. de Documento </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataAlmacen.numeroDocumento" tabindex="3" placeholder="Num. de Documento" /> 
                      </div>
                      <div class="form-group mb-md col-md-2">
                        <label class="control-label mb-xs"> Tipo Salida <small class="text-danger">(*)</small> </label>
                        <select class="form-control input-sm" ng-model="fDataAlmacen.idmotivomovimiento" ng-options="item.id as item.descripcion for item in listaMotivoMovimiento" tabindex="4" required ></select>     
                      </div>
                      <div class="form-group mb-md col-md-5">
                        <textarea class="form-control input-sm" ng-model="fDataAlmacen.observaciones" tabindex="5" placeholder="Observaciones" cols="50"></textarea> 
                      </div>
                    </fieldset>
                  </div>
                  <div class="well well-transparent boxDark col-xs-12 m-n">
                      <div class="row">
                        <div class="form-group mb-md col-md-6" >
                          <label class="control-label mb-xs"> Reactivo - Insumo <small class="text-danger">(*)</small></label>
                          <div class="input-group">
                            <span class="input-group-btn ">
                              <input id="idtemporalreactivoInsumo" type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAlmacen.temporal.idreactivoInsumo" placeholder="ID" tabindex="9" ng-enter="obtenerReactivoInsumoPorCodigo(); $event.preventDefault();" min-length="1" />
                            </span>
                            <input id="temporalreactivoInsumo" type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.reactivoInsumo" placeholder="Ingrese el Reactivo-Insumo o Click en Seleccionar" typeahead-loading="loadingLocationsReaIns" uib-typeahead="item as item.descripcion for item in getreactivoInsumoAutocomplete($viewValue)" typeahead-on-select="getSelectedReactivoInsumo($item, $model, $label)" typeahead-min-length="2" tabindex="10" autocomplete="off"/>
                            <span class="input-group-btn">
                              <button class="btn btn-default btn-sm" type="button" ng-click="verPopupReactivoInsumo('md')">Seleccionar</button>
                            </span>
                          </div>
                          <i ng-show="loadingLocationsReaIns" class="fa fa-refresh"></i>
                          <div ng-show="noResultsLD">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs text-danger"> Stock </label>
                          <input id="temporalStock" type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.stock" tabindex="88" placeholder="Cantidad" disabled="true" /> 
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Cant.<small class="text-danger">(*)</small></label>
                          <input id="temporalCantidad" type="number" class="form-control input-sm" ng-model="fDataAlmacen.temporal.cantidad" tabindex="11" placeholder="Cantidad" /> 
                        </div>
                        <div class="form-group mb-md col-md-2 col-sm-6">
                          <label class="control-label mb-xs"> Unidad </label>
                          <input type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.unidadLaboratorio" disabled="true" /> 
                        </div>
                        <div class="form-group mb-sm mt-md col-md-2 col-sm-12"> 
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt" tabindex="13" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
                          </div>
                          <!-- <input type="button" class="btn btn-info-alt col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" />  -->
                        </div>
                        
                        <div class="form-group col-xs-12 m-n">
                          <label class="control-label">Agregar al detalle: </label>
                          <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                        </div>
                      </div>
                    </div>
                </form>
              </div>
              <div class="panel-footer">
                <div class="row">
                  <div class="col-sm-12 text-right">
                    <button class="btn-primary btn" ng-click="grabar(); $event.preventDefault();" ng-disabled="formsalidasAlmacen.$invalid && !isRegisterSuccess"> <i class="fa fa-save"> </i> [F2] Grabar </button>
                    <button class="btn-default btn" ng-click="nuevo(); $event.preventDefault();" > <i class="fa fa-file"> </i> [ESC] Cancelar </button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>