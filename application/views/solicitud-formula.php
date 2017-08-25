<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>SOLICITUD FORMULA</li>
  <li class="active"> NUEVA SOLICITUD </li>
</ol>
<div class="container-fluid" ng-controller="solicitudFormulaController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Nueva Solicitud Fórmula </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <form name="formVenta"> 
                  <div class="col-md col-sm-6">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 pr-n pl-n"> Datos del Cliente

                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-sm btn-success-alt dropdown-toggle mt-sm ml" data-toggle="dropdown">
                              <i class="fa fa-plus"> </i>  Mas Opciones <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="" ng-click="btnNuevoCliente('xlg');limpiarCampos();"> <i class="fa fa-file"></i> Nuevo Cliente </a></li>
                                <li ng-show="fDataVenta.cliente.nombres.length > 0"><a href="" ng-click="btnEditar('xlg');"><i class="fa fa-edit"></i> Editar Cliente </a></li>
                            </ul>
                        </div>

                        <button class="btn btn-info-alt text-left btn-sm mt-sm ml pull-right" ng-click="btnBuscarCliente('lg');limpiarCampos();" type="button"> <i class="fa fa-search"></i> Buscar Cliente </button>

                        <!--< button ng-click="btnNuevoCliente('xlg');limpiarCampos();" class="btn btn-success-alt text-left btn-sm mt-sm ml" type="button"> <i class="fa fa-file"></i> Nuevo Cliente </button> 
                        <button ng-show="fDataVenta.cliente.nombres.length > 0" ng-click="btnEditar('xlg');" class="btn btn-warning-alt text-left btn-sm mt-sm ml" type="button"> <i class="fa fa-edit"></i> Editar Cliente </button>    -->                 
                      </legend>
                      <div class="form-group mb-md col-md-2 col-sm-6 pl-n"> 
                        <label class="control-label mb-xs"> N° de Doc. </label> 
                        <input id="txtNumeroDocumento" type="text" class="form-control input-sm" ng-model="fDataVenta.numero_documento" 
                            ng-enter="obtenerDatosCliente(); $event.preventDefault();" placeholder="Digite Número de Documento" ng-change="limpiarCampos();" tabindex="101" />
                      </div>

                      <div class="form-group mb-md col-md-5 col-sm-6"> 
                        <label class="control-label mb-xs"> Nombres </label> 
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.nombres" placeholder="Nombres" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-4 col-sm-6 pl-n">
                        <label class="control-label mb-xs"> Apellidos </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.apellidos" placeholder="Apellidos" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-1 col-sm-6 pl-n pr-n">
                        <label class="control-label mb-xs"> Edad </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.edad" placeholder="Edad" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-7 col-sm-12 pl-n">
                        <label class="control-label mb-xs"> Dirección </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.direccion" placeholder="Dirección" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-2 col-sm-12 pl-n">
                        <label class="control-label mb-xs"> Teléfono </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.telefono" placeholder="Telefono" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12 pl-n pr-n">
                        <label class="control-label mb-xs"> Celular </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.celular" placeholder="Celular" readonly="true" /> 
                      </div>
                    </fieldset>
                  </div>
                  <div class="col-md col-sm-6">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 pr-n pl-n"> Datos de la Solicitud
                        <div class="pull-right text-right">
                          <small class="text-default block mb-xs" style="font-size: 18px;line-height: 1;" > {{ fDataVenta.aleasDocumento }} N° <strong>{{ fDataVenta.idsolicitudformula }}</strong>                            
                          </small>
                        </div> 
                      </legend>
                      <div class="form-group mb-md col-md-8 col-sm-12">
                        <label class="control-label mb-xs"> Personal de Salud </label>
                        <div class="input-group">
                          <span class="input-group-btn ">
                            <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataVenta.idmedico" placeholder="ID" readonly="true" ng-required = "!fDataVenta.esMedLibre" />
                          </span>
                          <input id="temporalMedico" autocomplete="off" ng-change="getClearInputMedico();" type="text" class="form-control input-sm"
                            ng-model="fDataVenta.medico" placeholder="Digite el personal de salud"
                            typeahead-loading="loadingLocationsMed"
                            uib-typeahead="item as item.descripcion for item in getPersonalMedicoAutocomplete($viewValue)"
                            typeahead-on-select="getSelectedMedico($item, $model, $label)"
                            typeahead-min-length="2"
                            tabindex="104" ng-required = "!fDataVenta.esMedLibre"
                            ng-disabled="fDataVenta.esMedLibre" /> 
                        </div>
                        <i ng-show="loadingLocationsMed" class="fa fa-refresh"></i>
                        <div ng-show="noResultsMedico"> <i class="fa fa-remove"></i> No se encontró resultados </div>
                      </div>
                      <div class="form-group mb-md pt-n mt-lg col-md-4 col-sm-12">
                          <label class="mb-xs"><input type="checkbox" value="" ng-model="fDataVenta.esMedLibre" tabindex="105" ng-change="fDataVenta.medico=null;getClearInputMedico();"> Médico Libre </label>
                      </div>
                    </fieldset>
                  </div>
                  <div class="well well-transparent boxDark col-xs-12 m-n">
                      <div class="row">
                        <div class="form-group mb-md col-md-6 col-sm-6 pr-lg"> 
                          <label class="control-label mb-xs"> Producto </label>
                          <div class="input-group">
                            <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" class="form-control input-sm" tabindex="106" placeholder="Busque Preparado." typeahead-loading="loadingLocations" 
                            uib-typeahead="item as item.descripcion_stock for item in getProductoAutocomplete($viewValue)"  typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" typeahead-show-hint="true" autocomplete ="off" ng-change="limpiarCamposProductoTemporal();"/>
                            <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="button" ng-click="btnBuscarProducto('lg')"><i class="fa fa-search"></i> </button>
                            <button class="btn btn-default btn-sm" type="button" ng-click="btnNuevoProducto('lg')">NUEVO PRODUCTO </button>
                              <!--<button class="btn btn-default btn-sm" type="button" ng-click="verPopupPrincipioActivo('lg')" ng-disabled="!fDataVenta.temporal.producto.id">INFO.PRODUCTO [F8] </button>-->
                            </span>
                          </div> 
                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                          <div ng-show="noResultsLPSC">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-2 col-sm-6">
                          <label class="control-label mb-xs"> Categoria </label>
                          <select class="form-control input-sm" ng-model="fDataVenta.temporal.categoria" ng-options="item.id as item.descripcion for item in listaCategoria" ng-disabled="fDataVenta.temporal.categoria != '' " > </select>
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6 pl-n pr-n">
                          <label class="control-label mb-xs"> Uso </label>
                          <select class="form-control input-sm" ng-model="fDataVenta.temporal.uso" ng-options="item.id as item.descripcion for item in listaUsos" ng-disabled="fDataVenta.temporal.uso != '' " > </select>
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Precio </label>
                          <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.precio" placeholder="Precio" ng-disabled="!fDataVenta.esEditable" /> 
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Cantidad </label>
                          <input id="temporalCantidad" type="text" pattern="[0-9]+" ng-pattern-restrict class="form-control input-sm" ng-model="fDataVenta.temporal.cantidad" tabindex="109" placeholder="Cantidad"/> 
                        </div>
                        
                        <div class="form-group mb-sm mt-lg pl-n pr-n col-md-1 col-sm-6"> 
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt btn-sm" tabindex="111" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
                          </div>
                        </div>
                        <div class="form-group col-xs-12 m-n">
                          <label class="control-label m-n">Agregar al detalle: </label>
                          <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive scroll-x-none" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12 pull-right">
                          <div class="row">
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs" style=""> <strong style="font-size: 22px;">TOTAL</strong> </label> 
                              <input id="total" class="form-control pull-right text-center" disabled ng-model="fDataVenta.suma_total" tabindex="122" placeholder="S/." style="width: 160px; font-size: 20px;"/> 
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
                    <button class="btn-primary btn" ng-click="grabar(); $event.preventDefault();" ng-disabled="formVenta.$invalid && !isRegisterSuccess"> <i class="fa fa-save"> </i> [F2] Grabar </button>
                    <button class="btn-default btn" ng-click="nuevo(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F3] Nuevo </button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>