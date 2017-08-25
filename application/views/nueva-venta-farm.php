<style type="text/css">
  .switch {top:4px;}
  .switch .off, .switch .on {top: 0%;}
  .switch .switch-text {font-size:11px;}
</style>
<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>GESTION DE CAJA</li>
  <li class="active"> NUEVA VENTA </li>
  <div class="active pull-right" style="font-size: 1.5em ! important;">
    <b>SUBALMACEN: </b>
    {{fSessionCI.nombre_salm}}
  </div>
</ol>
<div class="container-fluid" ng-controller="ventaFarmaciaController">
    <div class="row">
        <div class="col-md-12" ng-show="ventaNormal">
            <div ng-show="cajaAbiertaPorMiSession" class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Nueva Venta </h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <form name="formVenta"> 
                  <div class="col-md-6 col-sm-12">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 pr-n pl-n"> Datos del Cliente 
                        <button ng-click="btnNuevoCliente('xlg');limpiarCampos();" class="btn btn-success-alt pull-right btn-sm mt-sm ml" type="button" ng-disabled="fDataVenta.esPreparado"> <i class="fa fa-file"></i> Nuevo Cliente </button> 
                        <button ng-click="btnBuscarCliente('lg');limpiarCampos();" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button" ng-disabled="fDataVenta.esPreparado"> <i class="fa fa-search"></i> Buscar Cliente </button> 
                        <button ng-show="fDataVenta.cliente.nombres.length > 0" ng-click="btnEditar('xlg');" class="btn btn-warning-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-edit"></i> Editar Cliente </button>
                        <button ng-click="btnBuscarEmpresaCliente('lg');" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button" ng-show="fDataVenta.idtipodocumento != 3"> <i class="fa fa-search"></i> Buscar Empresa </button>
                      </legend>
                      <div class="form-group mb-md col-md-3 col-sm-6 pl-n"> 
                        <label class="control-label mb-xs"> N° de Doc. </label> 
                        <input id="txtNumeroDocumento" type="text" class="form-control input-sm" ng-model="fDataVenta.numero_documento" 
                            ng-enter="obtenerDatosCliente(); $event.preventDefault();" placeholder="Digite Número de Documento" ng-change="limpiarCampos();" tabindex="101" focus-me ng-readonly="fDataVenta.esPreparado"/>
                        <!--  ng-if="fDataVenta.cliente.si_afiliado_puntos != '2'"
                        <div class="input-group" ng-if="fDataVenta.cliente.si_afiliado_puntos == '2'">
                          <input id="txtNumeroDocumento" type="text" class="form-control input-sm" ng-model="fDataVenta.numero_documento" placeholder="Digite Número de Documento" tabindex="101" />
                          <div class="input-group-btn">
                            <button type="button" class="btn btn-info input-sm f-12" ng-click="btnAfiliar(fDataVenta.cliente.id)">AFILIAR</button>
                          </div>
                        </div> -->
                      </div>

                      <div class="form-group mb-md col-md-5 col-sm-6"> 
                        <label class="control-label mb-xs"> Nombres </label> 
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.nombres" placeholder="Nombres" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-4 col-sm-6">
                        <label class="control-label mb-xs"> Apellidos </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.apellidos" placeholder="Apellidos" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-6 pl-n">
                        <label class="control-label mb-xs"> Edad </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.edad" placeholder="Edad" readonly="true" /> 
                      </div>
                      <div class="form-group mb-md col-md-9 col-sm-12">
                        <label class="control-label mb-xs"> Dirección </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.direccion" placeholder="Dirección" readonly="true" /> 
                      </div>
                    </fieldset>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <fieldset class="row" style="padding-right: 10px;">
                      <legend class="col-md-12 p-n"> Datos de la Venta 
                        <div class="pull-right text-right">
                          <small class="text-default block mb-xs" style="font-size: 18px;line-height: 1;" > {{ fDataVenta.aleasDocumento }} N° <strong>{{ fDataVenta.ticket }}</strong>
                            <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarCodigoTicket(); generarNumOrden();$event.preventDefault();"> <i class="ti ti-reload "></i> </button>
                          </small>  
                          <small class="text-gray block" style="font-size: 14px;line-height: 1;" > Orden N° {{ fDataVenta.orden }} 
                            <!-- <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarNumOrden(); $event.preventDefault();"> <i class="ti ti-reload "></i> </button> --> 
                          </small>
                        </div>
                      </legend> 
                      <!-- <div class="form-group mb-md col-md-6 col-sm-12 pl-xs" ng-if="fDataVenta.idtipodocumento == 3"> 
                        <label class="control-label mb-xs"> Tipo de Documento </label>
                        <select class="form-control input-sm" ng-model="fDataVenta.idtipodocumento" ng-change="fDataVenta.ruc = null; generarCodigoTicket();" ng-options="item.id as item.descripcion for item in listaTipoDocumento" required tabindex="102"> </select> 
                      </div> -->
                      <div class="form-group mb-md col-md-3 col-sm-12 p-n">
                        <label class="control-label mb-xs"> Tipo de Documento </label>
                        <select class="form-control input-sm" ng-model="fDataVenta.idtipodocumento" ng-change="onChangeTipoDocumento();" ng-options="item.id as item.descripcion for item in listaTipoDocumento" required tabindex="102"> </select> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12" ng-if="fDataVenta.idtipodocumento != 2">
                        <label class="control-label mb-xs"> RUC Empresa </label>
                        <input type="text" ng-model="fDataVenta.ruc" class="form-control input-sm" tabindex="103" placeholder="" disabled  /> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12" ng-if="fDataVenta.idtipodocumento == 2">
                        <label class="control-label mb-xs"> RUC Empresa <small  class="text-danger">(*)</small> </label>
                        <input type="text" ng-model="fDataVenta.ruc" class="form-control input-sm" tabindex="103" placeholder="" required disabled  /> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12" style="display:none;">
                        <label class="control-label mb-xs"> Tipo de precio </label>
                        <select class="form-control input-sm" ng-change="calcularTotales();" ng-model="fDataVenta.precio" ng-options="item as item.descripcion for item in listaPrecios" tabindex="104"> </select> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12">
                        <label class="control-label mb-xs"> Personal de Salud </label>
                        <input type="text" ng-model="fDataVenta.medico" class="form-control input-sm" tabindex="104" placeholder="Busque Personal de Salud." 
                          uib-typeahead="item as item.descripcion for item in getPersonalMedicoAutocomplete($viewValue)" typeahead-min-length="2" ng-disabled="personalSaludDisabled" /> 
                      </div>
                      <div class="form-group mb-md pt-n mt-n col-md-3 col-sm-12"> 
                          <label class="mb-xs"><input type="checkbox" value="" ng-model="fDataVenta.esclienteexterno" ng-change="onChangeClienteExterno();" tabindex="120" > Cliente Externo</label> 
                          <label class="mb-xs"><input type="checkbox" value="" ng-model="fDataVenta.estemporal" tabindex="130" ng-change="limpiaDatosMedicamento();" ng-disabled="gridOptions.data.length>0"> Venta Sin Stock</label>
                          <label class="mb-xs"><input type="checkbox" value="" ng-model="fDataVenta.esPreparado" tabindex="140" ng-change="limpiaDatosMedicamento();" ng-disabled="gridOptions.data.length>0"> Venta preparados</label>
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12 p-n">
                        <label class="control-label mb-xs"> Sub-Almacén</label>
                        <select class="form-control input-sm" ng-model="fDataVenta.idsubalmacen" ng-options="item.id as item.descripcion for item in listaSubAlmacenVenta" > </select> 

                      </div>
                     
                      <div class="form-group mb-md col-md-3 col-sm-12 pl-xs">
                        <label class="control-label mb-xs"> Medio de pago </label>
                        <select class="form-control input-sm" ng-change="onChangeMedioPago();" ng-model="fDataVenta.idmediopago" ng-options="item.id as item.descripcion for item in listaMedioPago" tabindex="150"> </select> 
                      </div>
                      <!-- <div class="form-group mb-md col-md-3 col-sm-12" ng-if="fDataVenta.cliente.sede_convenio">
                        <label><input type="checkbox" value="" ng-model="fDataVenta.convenio" tabindex="155" ng-change="elegirConvenio();" ng-disabled="gridOptions.data.length>0"> Convenio </label>
                      </div> -->
                      <div class="form-group mb-md col-md-3 col-sm-12 mt-lg" ng-if="fDataVenta.esPreparado">
                        <!-- <label class="control-label mb-xs"> Nº Solicitud <small class="text-danger">(*)</small> </label> -->
                        <div class="input-group">
                          <!-- <input type="text" ng-model="fDataVenta.idsolicitudformula" class="form-control input-sm" tabindex="103" placeholder="" required ng-enter="btnCargarSolicitud()"/> -->
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt btn-sm" tabindex="160" ng-click="btnCargarSolicitudFormula(); $event.preventDefault();" style="min-width: 100%;">[F9] Cargar Solicitud</a>
                             <!--  <a href="" class="btn btn-info-alt btn-sm dropdown-toggle" tabindex="112" data-toggle="dropdown"><span class="caret"></span></a> -->
                              <!-- <ul class="dropdown-menu sm" role="menu" style="padding:0;">
                                  <li><a ng-click="btnAgregarVentaParcial(); $event.preventDefault();" class="btn btn-info-alt" href="" tabindex="114">Desde Venta A cuenta</a></li> 
                              </ul> -->
                          </div>
                        </div>
                      </div>

                      <div class="form-group mb-md col-md-3 col-sm-12 mt-lg" ng-if="fDataVenta.esPreparado">
                        <!-- <label class="control-label mb-xs"> Nº Solicitud <small class="text-danger">(*)</small> </label> -->
                        <div class="input-group">
                          <!-- <input type="text" ng-model="fDataVenta.idsolicitudformula" class="form-control input-sm" tabindex="103" placeholder="" required ng-enter="btnCargarSolicitud()"/> -->
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-success-alt btn-sm" tabindex="170" ng-click="btnAgregarVentaParcial(); $event.preventDefault();" style="min-width: 100%;">[F10] Cargar venta a cuenta</a> 
                          </div>
                        </div>
                      </div>
                      
                    </fieldset>
                  </div>
                  <div class="well well-transparent boxDark col-xs-12 m-n">
                      <div class="row" ng-show="!fDataVenta.esPreparado">
                        <div class="form-group mb-md col-md-6 col-sm-6 pr-lg"> 
                          <label class="control-label mb-xs"> Producto </label>
                          <div class="input-group">
                            <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" class="form-control input-sm" tabindex="180" placeholder="Busque Producto/Servicio." typeahead-loading="loadingLocations" 
                            uib-typeahead="item as item.descripcion_stock for item in getProductoAutocomplete($viewValue)"  typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="3" typeahead-show-hint="true" autocomplete ="off" ng-change="limpiarCamposProductoTemporal();"/>
                            <!-- <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" class="form-control input-sm" tabindex="108" placeholder="Busque Producto/Servicio." typeahead-loading="loadingLocations" 
                            uib-typeahead="item as item.descripcion + ' | ' + item.stockActual for item in getProductoAutocomplete($viewValue)"  typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="3" typeahead-show-hint="true" autocomplete ="off"/>  -->
                            <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="button" ng-click="btnBuscarProducto('lg')"><i class="fa fa-search"></i> </button>
                              <button class="btn btn-default btn-sm" type="button" ng-click="verPopupStocks('lg')" ng-disabled="!fDataVenta.temporal.producto.id">VER STOCKS [F7] </button>
                              <button class="btn btn-default btn-sm" type="button" ng-click="verPopupPrincipioActivo('lg')" ng-disabled="!fDataVenta.temporal.producto.id">INFO.PRODUCTO [F8] </button>
                            </span>
                          </div> 
                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                          <div ng-show="noResultsLPSC">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Stock </label>
                          <input ng-class="{'text-red':fDataVenta.temporal.stockActual <= fDataVenta.temporal.stockMinimo || fDataVenta.temporal.stockActual <= 0, 'text-blue':fDataVenta.temporal.stockActual > fDataVenta.temporal.stockMinimo}" 
                            id="temporalPrecio" type="number" class="form-control input-sm" ng-model="fDataVenta.temporal.stockActual" placeholder="Stock" disabled /> 
                        </div> 
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Precio </label>
                          <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.precio" placeholder="Precio" ng-disabled="!fDataVenta.esEditable" /> 
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Cantidad </label>
                          <input id="temporalCantidad" type="text" pattern="[0-9]+" ng-pattern-restrict class="form-control input-sm" ng-model="fDataVenta.temporal.cantidad" tabindex="190" placeholder="Cantidad" ng-change="calcularDescuento();"/> 
                        </div>
                        <!-- <div class="form-group mb-md col-md-1 col-md-2 col-sm-6">
                          <label class="control-label mb-xs" ng-if="fDataVenta.temporal.porcentaje_dcto"> Dcto.({{fDataVenta.temporal.porcentaje_dcto}}%)</label>
                          <label class="control-label mb-xs" ng-if="!fDataVenta.temporal.porcentaje_dcto"> Descuento </label>
                          <input type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.descuento" tabindex="110" placeholder="Descuento" ng-disabled="fDataVenta.cliente.tipocliente || fDataVenta.estemporal" /> 
                        </div> -->
                        <div class="form-group mb-md col-md-1 col-md-2 col-sm-6" ng-if="fDataVenta.temporal.siBonificacion">
                          <label class="control-label mb-xs"> Bonificacion </label>
                          <switch name="enabled" ng-model="fDataVenta.temporal.bonificacion" disabled="!fDataVenta.temporal.siBonificacion" class="success" on="Si" off="No"></switch>
                        </div>
                        <div class="form-group mb-sm mt-lg pl-n pr-n col-md-1 col-sm-6"> 
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt btn-sm" tabindex="200" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
                              <a href="" class="btn btn-info-alt btn-sm dropdown-toggle" tabindex="210" data-toggle="dropdown"><span class="caret"></span></a>
                              <ul class="dropdown-menu sm" role="menu" style="padding:0;">
                                  <li><a ng-click="btnAgregarReceta(); $event.preventDefault();" class="btn btn-info-alt" href="" tabindex="220">DESDE RECETA MED.</a></li>
                                  
                              </ul>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="form-group col-xs-12 m-n">
                          <label class="control-label m-n">Agregar al detalle: </label>
                          <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive scroll-x-none" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12 mt-xs">
                          <!-- AFILIADO A PUNTOS -->
                          <div class="p-xs" style="border: 1px solid #E6E7E8;" >
                            <label class="control-label m-n" style="width:100%; text-align:center;background:#EDEDED">CLIENTE AFILIADO A PUNTOS </label>
                            <div class="input-group">
                              <input id="txtNumeroDocumento_afiliado" type="text" class="form-control input-sm" ng-model="fDataVenta.numero_documento_afiliado" placeholder="Digite Número de Documento" ng-enter="btnComprobarAfiliacion(fDataVenta.numero_documento_afiliado)" ng-change="mensaje=null" tabindex="230" />
                              <div class="input-group-btn">
                                <button type="button" class="btn btn-info input-sm f-12" ng-click="btnComprobarAfiliacion(fDataVenta.numero_documento_afiliado)">Comprobar</button>
                              </div>
                            </div>
                            <span ng-show="mensaje" class="{{clase}}">{{mensaje}}</span>
                          </div>
                          
                          <!-- MULTI PAGO -->
                          <div class="mt p-xs" style="border: 1px solid #E6E7E8;" ng-if="fDataVenta.total > 0 && fDataVenta.idmediopago == 6">
                            <label class="control-label m-n" style="width:100%; text-align:center;background:#EDEDED">PAGO MIXTO </label>
                            <div class="row">
                              <div class="form-group col-md-12 mt-xs mb-n text-left" ng-repeat="item in fDataVenta.pagoMixto">
                                <label class="control-label mb-n"> {{item.descripcion}}</label>
                                <input tabindex="240" type="text" class="form-control input-sm" ng-model="item.monto" placeholder="Ingrese Monto" ng-change="calcularPagoMixto($index);" ng-disabled="fDataVenta.idtipodocumento == 12 && !(fDataVenta.a_cuenta > 0) "/>
                              </div>
                            </div>
                          </div>
                          
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12">
                          <div class="row" ng-show='fDataVenta.esPreparado && fDataVenta.boolSolicitud && fDataVenta.idtipodocumento == 12'>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs " > <strong style="font-size: 22px;">A CTA.</strong> </label> 
                              <input ng-change="calcularSaldo();" type="number" class="form-control pull-right text-center" ng-model="fDataVenta.a_cuenta" tabindex="250" placeholder="S/." style="width: 160px; font-size: 20px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs" style=""> <strong style="font-size: 22px;">SALDO</strong> </label> 
                              <input id="saldo" type="number" class="form-control pull-right text-center" disabled ng-model="fDataVenta.saldo" tabindex="260" placeholder="S/." style="width: 160px; font-size: 20px;"/> 
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12">
                          <div class="row">
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs " > <strong style="font-size: 22px;">ENTREGA</strong> </label> 
                              <input ng-change="calcularVuelto();" type="number" class="form-control pull-right text-center" ng-model="fDataVenta.entrega" tabindex="270" placeholder="S/." style="width: 160px; font-size: 20px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs" style=""> <strong style="font-size: 22px;">VUELTO</strong> </label> 
                              <input id="vuelto" type="number" class="form-control pull-right text-center" disabled ng-model="fDataVenta.vuelto" tabindex="280" placeholder="S/." style="width: 160px; font-size: 20px;"/> 
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-xs-12 pl-n">
                          <div class="row">
                            <div class="form-inline mt-xs col-xs-12 text-right" ng-if="bool_exonerado">
                              <label class="control-label mr-xs text-gray"> EXONERADO </label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.igv_exonerado" placeholder="Exonerado" style="width: 160px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-gray"> SUBTOTAL </label> 
                              <input type="text" class="form-control input-sm pull-right text-center m-pen" disabled ng-model="fDataVenta.subtotal" placeholder="Subtotal" style="width: 160px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-gray"> I.G.V. </label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.igv" placeholder="I.G.V." style="width: 160px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-gray"> IMPORTE TOTAL </label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.total_sin_redondeo" placeholder="Total" style="width: 160px;"/> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right" ng-if="fDataVenta.esPreparado && fDataVenta.pago_a_cuenta">
                              <label class="control-label mr-xs text-gray"> PAGO A CUENTA </label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.pago_a_cuenta" placeholder="Pago a cuenta" style="width: 160px;"/> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-gray"> REDONDEO </label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.redondeo" placeholder="Redondeo" style="width: 160px;"/> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right" ng-if="fDataVenta.esPreparado && fDataVenta.pago_a_cuenta">
                              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> IMPORTE A PAGAR</label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.total_saldo" placeholder="Total a Pagar" style="width: 160px; font-size: 17px; font-weight: bolder;"/> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right" ng-if="!fDataVenta.esPreparado || !fDataVenta.pago_a_cuenta">
                              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> IMPORTE A PAGAR</label> 
                              <input type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.total" placeholder="Total a Pagar" style="width: 160px; font-size: 17px; font-weight: bolder;"/> 
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
                    <button class="btn-success btn" ng-click="imprimir(); $event.preventDefault();" ng-disabled="!isRegisterSuccess"> <i class="fa fa-print"> </i> [F4] Imprimir </button>
                    <button class="btn-warning btn" ng-click="mismoCliente(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F6] Mismo Cliente </button>
                   
                  </div>
                </div>
              </div>
            </div>
            <div ng-show="!cajaAbiertaPorMiSession && fSessionCI.idalmacenfarmacia == 0" class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> La Caja está cerrada. </h2> 
              </div>
              <div class="panel-body">
                <div class="col-xs-12">
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> 
                    Usted no tiene un almacen asignado... 
                  </div>
                </div>
              </div>
            </div>
            <div ng-show="!cajaAbiertaPorMiSession && fSessionCI.idalmacenfarmacia != 0" class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> La Caja está cerrada. </h2> 
              </div>
              <div class="panel-body">
                <div class="col-xs-12">
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> 
                    Proceda a abrir caja para comenzar... 
                  </div>
                  <button ng-click="abrirCaja();" class="btn btn-success btn-lg ng-scope block" type="button" ng-if="!cajaAbiertaPorMiSession" style="margin: auto;"> 
                    <i class="ti ti-plus"></i> ABRIR CAJA </button> 
                </div>
              </div>
            </div>
        </div>
        
        <div class="col-md-12" ng-show="!ventaNormal">
          <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> NUEVA VENTA. </h2> 
              </div>
              <div class="panel-body">
                <div class="col-xs-12">
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> 
                    Esta Página no está disponible en el Modo de Venta Por Pedido... 
                  </div>
                  
                </div>
              </div>
            </div>
        </div>
    </div>
</div>