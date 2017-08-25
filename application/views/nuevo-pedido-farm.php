<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>GESTION DE CAJA</li>
  <li class="active"> NUEVO PEDIDO </li>
</ol>
<div class="container-fluid" ng-controller="pedidoVentaFarmaciaController">
    <div class="row">
        <div class="col-md-12" ng-show="ventaPedido">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Nuevo Pedido </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <form name="formVenta"> 
                  <div class="col-md-12 col-sm-12">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 pr-n pl-n"> Datos del Cliente 
                        <button ng-click="btnNuevoCliente('xlg');limpiarCampos();" class="btn btn-success-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-file"></i> Nuevo cliente </button> 
                        <button ng-click="btnBuscarCliente('lg');limpiarCampos();" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-search"></i> Buscar Cliente </button> 
                        <button ng-show="fDataPedido.cliente.nombres.length > 0" ng-click="btnEditar('xlg');" class="btn btn-warning-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-edit"></i> Editar Cliente </button>
                        <div class="pull-right btn-lg pt-sm" style="font-size:14px;font-weight: bold;color:red;"><input type="checkbox" value="" ng-model="fDataPedido.estemporal" tabindex="104" ng-change="limpiaDatosMedicamento();" ng-disabled="gridOptions.data.length>0"> Es Temporal</div> 
                      </legend>
                      <div class="form-group mb-md col-md-2 col-sm-6 pl-n"> 
                        <label class="control-label mb-xs"> N° de Doc. </label> 
                          <input id="txtNumeroDocumento" type="text" class="form-control input-sm" ng-model="fDataPedido.numero_documento" 
                            ng-enter="obtenerDatosCliente(); $event.preventDefault();" placeholder="Digite Número de Documento" ng-change="limpiarCampos();" tabindex="101" focus-me /> 
                      </div>
                      <div class="form-group mb-md col-md-4 col-sm-6"> 
                        <label class="control-label mb-xs"> Nombres </label> 
                        <input type="text" class="form-control input-sm" ng-model="fDataPedido.cliente.nombres" placeholder="Nombres" disabled /> 
                      </div>
                      <div class="form-group mb-md col-md-4 col-sm-6">
                        <label class="control-label mb-xs"> Apellidos </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataPedido.cliente.apellidos" placeholder="Apellidos" disabled /> 
                      </div>
                      <div class="form-group mb-md col-md-2 col-sm-6 pr-n text-right" style="font-size: 20px">
                        <label class="control-label mb-xs"> Nº Pedido </label>
                        <span class="text-gray block">{{fDataPedido.prefijo}}-<span style="color:red; font-weight:bold;" > {{fDataPedido.correlativo}}</span>
                          <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarNumOrden(); $event.preventDefault();"> <i class="ti ti-reload "></i> </button> 
                        </span>
                      </div>
                      <div class="form-group mb-md col-md-2 col-sm-6 pl-n">
                        <label class="control-label mb-xs"> Edad </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataPedido.cliente.edad" placeholder="Edad" disabled /> 
                      </div>
                      <div class="form-group mb-md col-md-4 col-sm-12">
                        <label class="control-label mb-xs"> Dirección </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataPedido.cliente.direccion" placeholder="Dirección" disabled /> 
                      </div>
                      <!-- // -->
                      <div class="form-group mb-md col-md-2 col-sm-6"> <label> Sub-Almacén</label>
                        <div class="input-group block"> 
                          <select class="form-control input-sm" ng-model="fDataPedido.idsubalmacen" ng-options="item.id as item.descripcion for item in listaSubAlmacenVenta" > </select> 
                        </div>
                      </div>
                      
                      <div class="form-group mb-md col-md-2 col-sm-6">
                        <label class="control-label mb-xs"> Fecha del Pedido</label>
                        <small class="text-gray block" style="font-size: 14px;line-height: 1.8;" > {{fDataPedido.fecha_pedido | date:'EEEE, dd MMMM yyyy'}}
                        </small>
                        <!-- <input type="text" class="form-control input-sm" ng-model="fDataPedido.num_pedido" placeholder="Dirección" disabled />  -->
                      </div>
                      <div class="form-group mb-md col-md-2 col-sm-6 text-right" ng-show="fDataPedido.cliente.tipocliente">
                      <!-- <label class="control-label mb-xs">  </label> -->
                        <span class="text-blue block" style="font-size: 16px;font-weight:bolder;"><span class="text-blue block" style="font-size: 16px;font-weight:bolder;"><i class="fa fa-child" style="font-size: 1.5em;"></i> {{fDataPedido.cliente.tipocliente}}</span>
                      </div>
                    </fieldset>
                  </div>

                  <div class="well well-transparent boxDark col-xs-12 m-n">
                      <div class="row">
                        <div class="form-group mb-md col-md-5 col-sm-6">
                          <label class="control-label mb-xs"> Producto </label>
                          <div class="input-group">
                            <input id="temporalProducto" type="text" ng-model="fDataPedido.temporal.producto" class="form-control input-sm" tabindex="108" placeholder="Busque Producto/Servicio."  
                            uib-typeahead="item as item.descripcion for item in getProductoAutocomplete($viewValue)" typeahead-loading="loadingLocations" typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" autocomplete="off"/> 
                            <span class="input-group-btn">
                              <button class="btn btn-default btn-sm" type="button" ng-click="verPopupPrincipioActivo('lg')">VER PRINCIPIO ACTIVO [F8] </button>
                            </span>
                          </div> 
                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                          <div ng-show="noResultsLPSC">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Stock </label>
                          <input ng-class="{'text-red':fDataPedido.temporal.stockActual <= fDataPedido.temporal.stockMinimo, 'text-blue':fDataPedido.temporal.stockActual > fDataPedido.temporal.stockMinimo}" 
                            id="temporalPrecio" type="number" class="form-control input-sm" ng-model="fDataPedido.temporal.stockActual" placeholder="Stock" disabled style="max-width: 106px;" /> 
                        </div> 
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Precio </label>
                          <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataPedido.temporal.precio" placeholder="Precio" ng-disabled="!fDataPedido.estemporal" /> 
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Cantidad </label>
                          <input id="temporalCantidad" type="text" class="form-control input-sm" ng-model="fDataPedido.temporal.cantidad" tabindex="109" placeholder="Cantidad" ng-change="calcularDescuento();"/> 
                        </div>
                        <div class="form-group mb-md col-lg-1 col-md-2 col-sm-6">
                          <label class="control-label mb-xs"> Descuento </label>
                          <input type="text" class="form-control input-sm" ng-model="fDataPedido.temporal.descuento" tabindex="110" placeholder="Descuento" ng-disabled="fDataPedido.cliente.tipocliente || fDataPedido.estemporal" /> 
                        </div>
                        <div class="form-group mb-sm mt-md col-md-2 col-sm-12"> 
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt" tabindex="111" ng-click="agregarItem(); $event.preventDefault();">Agregar</a>
                             
                          </div>
                        </div>
                        
                        <div class="form-group col-xs-12 m-n">
                          <label class="control-label m-n">Agregar al detalle: </label>
                          <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive scroll-x-none" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                        </div>
                       
                        <div class="col-lg-6 col-md-5 col-xs-12 pull-right">
                          <div class="row">
                            <div class="form-inline mt-xs col-xs-12 text-right" ng-show="fDataPedido.totalDescuento > 0">
                                <label class="control-label mr-xs text-gray"> TOTAL DESCUENTO </label> 
                                <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataPedido.totalDescuento" placeholder="Descuentos" style="width: 200px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL </label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataPedido.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
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
                    <button class="btn-warning btn" ng-click="mismoCliente(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F6] Mismo Cliente </button>
                  </div>
                </div>
              </div>
            </div>
        </div>

        <div class="col-md-12" ng-show="!ventaPedido">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> NUEVO PEDIDO. </h2>
              </div>
              <div class="panel-body">
                <div class="col-xs-12">
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> 
                    Esta Página no está disponible en el Modo de Venta Normal... 
                  </div>
                  
                </div>
              </div>
            </div>
        </div>
    </div>
</div>