<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Caja</li>
  <li class="active">Ingresos de Almacen </li>
</ol>
<div class="container-fluid" ng-controller="ingresosAlmacenController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Nuevo Ingreso de Almacen </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <form name="formingresosAlmacen"> 
                  <div class="col-md-12 col-sm-12">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 pr-n pl-n"> Datos del Ingreso
                      </legend>
                      <div class="form-group mb-md col-md-2" >
                        <label class="control-label mb-xs">Fecha de Ingreso </label>  
                        <div class="input-group col-md-12"> 
                          <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataAlmacen.fecha" required tabindex="99" disabled="true" /> 
                        </div>
                      </div>
                      <div class="form-group mb-md col-md-5" >
                        <label class="control-label mb-xs"> Proveedor <small class="text-danger">(*)</small></label>
                        <div class="input-group">
                          <span class="input-group-btn ">
                            <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAlmacen.idproveedor" placeholder="ID" tabindex="1" ng-enter="obtenerProveedorPorCodigo(); $event.preventDefault();" min-length="1" />
                          </span>
                          <input id="fDataAlmacenProveedor" type="text" class="form-control input-sm" ng-model="fDataAlmacen.proveedor" placeholder="Ingrese el Proveedor o Click en Seleccionar" typeahead-loading="loadingLocationsProv" uib-typeahead="item as item.razon_social for item in getProveedorAutocomplete($viewValue)" typeahead-on-select="getSelectedProveedor($item, $model, $label)" typeahead-min-length="2" tabindex="2"/>
                          <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaProveedores('md')">Seleccionar</button>
                          </span>
                        </div>
                        <i ng-show="loadingLocationsProv" class="fa fa-refresh"></i>
                        <div ng-show="noResultsLD">
                          <i class="fa fa-remove"></i> No se encontró resultados 
                        </div>
                      </div>
                      <div class="form-group mb-md col-md-5" >
                        <label class="control-label mb-xs"> Empresa <small class="text-danger">(*)</small></label>
                        <div class="input-group">
                          <span class="input-group-btn ">
                            <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAlmacen.idempresa" placeholder="ID" tabindex="3" ng-enter="obtenerEmpresaPorCodigo(); $event.preventDefault();" min-length="2" />
                          </span>
                          <input id="fDataAlmacenempresa" type="text" class="form-control input-sm" ng-model="fDataAlmacen.empresa" placeholder="Ingrese la Empresa o Click en Seleccionar" typeahead-loading="loadingLocationsEmp" uib-typeahead="item as item.descripcion for item in getEmpresaAutocomplete($viewValue)" typeahead-on-select="getSelectedEmpresa($item, $model, $label)" typeahead-min-length="2" tabindex="4"/>
                          <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaEmpresas('md')">Seleccionar</button>
                          </span>
                        </div>
                        <i ng-show="loadingLocationsEmp" class="fa fa-refresh"></i>
                        <div ng-show="noResultsLD">
                          <i class="fa fa-remove"></i> No se encontró resultados 
                        </div>
                      </div>
                      <div class="form-group mb-md col-md-2">
                        <label class="control-label mb-xs"> Documento <small class="text-danger">(*)</small> </label>
                        <select class="form-control input-sm" ng-model="fDataAlmacen.idtipodocumento" ng-options="item.id as item.descripcion for item in listaTipoDocumento" tabindex="5" required ></select>     
                      </div>
                      <div class="form-group mb-md col-md-3">
                        <label class="control-label mb-xs"> Num. de Documento </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataAlmacen.numeroDocumento" placeholder="Num. de Documento" tabindex="6"/> 
                      </div>
                      <div class="form-group mb-md col-md-2">
                        <label class="control-label mb-xs"> Tipo Ingreso <small class="text-danger">(*)</small> </label>
                        <select class="form-control input-sm" ng-model="fDataAlmacen.idmotivomovimiento" ng-options="item.id as item.descripcion for item in listaMotivoMovimiento" tabindex="7" required ></select>     
                      </div>
                      <div class="form-group mb-md col-md-5">
                        <textarea class="form-control input-sm" ng-model="fDataAlmacen.observaciones" placeholder="Observaciones" cols="50" tabindex="8"></textarea> 
                      </div>
                    </fieldset>
                  </div>
                  <div class="well well-transparent boxDark col-xs-12 m-n">
                      <div class="row">
                        <div class="form-group mb-md col-md-4" >
                          <label class="control-label mb-xs"> Reactivo - Insumo <small class="text-danger">(*)</small></label>
                          <div class="input-group">
                            <span class="input-group-btn ">
                              <input id="idtemporalreactivoInsumo" type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAlmacen.temporal.idreactivoInsumo" placeholder="ID" tabindex="9" ng-enter="obtenerReactivoInsumoPorCodigo(); $event.preventDefault();" min-length="1" />
                            </span>
                            <input id="temporalreactivoInsumo" type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.reactivoInsumo" placeholder="Ingrese el Reactivo-Insumo o Click en Seleccionar" typeahead-loading="loadingLocationsReaIns" uib-typeahead="item as item.descripcion for item in getreactivoInsumoAutocomplete($viewValue)" typeahead-on-select="getSelectedReactivoInsumo($item, $model, $label)" typeahead-min-length="2" tabindex="10"/>
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
                          <label class="control-label mb-xs"> Cant.<small class="text-danger">(*)</small></label>
                          <input id="temporalCantidad" type="number" class="form-control input-sm" ng-model="fDataAlmacen.temporal.cantidad" tabindex="11" placeholder="Cantidad" /> 
                        </div>
                        <div class="form-group mb-md col-md-2 col-sm-6">
                          <label class="control-label mb-xs"> Unidad </label>
                          <input type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.unidadLaboratorio" disabled="true" /> 
                        </div>
                        <div class="form-group mb-md col-md-1" >
                          <label class="control-label mb-xs">Fec.Venc.<small class="text-danger">(*)</small> </label>  
                          <div class="input-group col-md-12"> 
                            <input id="temporalfechavencimiento" type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataAlmacen.temporal.fechavencimiento" tabindex="12" /> 
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-1" >
                          <label class="control-label mb-xs">Num.Lote</label>  
                          <div class="input-group col-md-12"> 
                            <input id="temporalnumerolote" type="text" class="form-control input-sm mask" ng-model="fDataAlmacen.temporal.numerolote" tabindex="13" /> 
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Precio <small class="text-danger">(*)</small> </label>
                          <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataAlmacen.temporal.precio" placeholder="Precio" tabindex="14" /> 
                        </div>
                        <div class="form-group mb-sm mt-md col-md-2 col-sm-12"> 
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt" tabindex="15" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
                          </div>
                          <!-- <input type="button" class="btn btn-info-alt col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" />  -->
                        </div>
                        
                        <div class="form-group col-xs-12 m-n">
                          <label class="control-label">Agregar al detalle: </label>
                          <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                        </div>
                        <div class="col-lg-9 col-md-7 col-xs-12">
                          <div class="row" ng-show="fDataVenta.total.length >= 1">
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs " > <strong style="font-size: 22px;">ENTREGA</strong> </label> 
                              <input ng-change="calcularVuelto();" type="number" class="form-control pull-right text-center" ng-model="fDataVenta.entrega" tabindex="120" placeholder="S/." style="width: 200px; font-size: 20px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs" style=""> <strong style="font-size: 22px;">VUELTO</strong> </label> 
                              <input id="vuelto" type="number" class="form-control pull-right text-center" disabled ng-model="fDataVenta.vuelto" tabindex="122" placeholder="S/." style="width: 200px; font-size: 20px;"/> 
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-3 col-md-5 col-xs-12">
                          <div class="row">
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL </label> 
                              <input id="temporaltotal" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataAlmacen.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                </form>
              </div>
              <div class="panel-footer">
                <div class="row">
                  <div class="col-sm-12 text-right">
                    <button class="btn-primary btn" ng-click="grabar(); $event.preventDefault();" ng-disabled="formingresosAlmacen.$invalid && !isRegisterSuccess"> <i class="fa fa-save"> </i> [F2] Grabar </button>
                    <button class="btn-default btn" ng-click="nuevo(); $event.preventDefault();" > <i class="fa fa-file"> </i> [ESC] Cancelar </button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>