<script type="text/ng-template" id="customAutocompleteTemplate.html">                        
  <ul class="dropdown-menu ng-isolate-scope" ng-show="isOpen() &amp;&amp; !moveInProgress" 
      ng-style="{top: position().top+'px', left: position().left+'px'}" 
      style="display: block; top: 48px; left: 16px;" role="listbox" aria-hidden="{{!isOpen()}}" 
      matches="matches" active="activeIdx" select="select(activeIdx)" move-in-progress="moveInProgress" query="query" position="position">
    <li ng-repeat="match in matches track by $index" ng-class="{active: isActive($index) }" ng-mouseenter="selectActive($index)" 
        ng-click="selectMatch($index)" role="option" id="{{::match.id}}" class="dropdown-row {{match.model.clase}}" style="">
        <a href="" tabindex="-1" ng-bind-html="match.label | uibTypeaheadHighlight:query" class="">
          {{match.model.descripcion}}
        </a>
    </li>
  </ul>
</script>

<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Caja</li>
  <li class="active">Nueva Venta </li>
</ol>
<div class="container-fluid" ng-controller="ventaController">
    <div class="row">
        <div class="col-md-12">
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
                      <legend class="col-md-12 pr-n pl-n"> Datos del Paciente 
                        <button ng-click="btnNuevoCliente('xlg');" class="btn btn-success-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-file"></i> Nuevo Paciente </button> 
                        <button ng-click="btnBuscarCliente('lg');" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-search"></i> Buscar Paciente </button> 
                        <button ng-show="fDataVenta.cliente.nombres.length > 0" ng-click="btnEditar('xlg');" class="btn btn-warning-alt pull-right btn-sm mt-sm" type="button"> <i class="fa fa-edit"></i> Editar Paciente </button>
                        <button ng-click="btnBuscarEmpresaCliente('lg');" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button" ng-show="fDataVenta.idtipodocumento == 2"> <i class="fa fa-search"></i> Buscar Empresa </button>
                      </legend>
                      <div class="form-group mb-md col-md-3 col-sm-6 pl-n"> 
                        <label class="control-label mb-xs"> N° de Doc. </label> 
                          <input id="txtNumeroDocumento" type="text" class="form-control input-sm" ng-model="fDataVenta.numero_documento" 
                            ng-enter="obtenerDatosCliente(); $event.preventDefault();" placeholder="Digite Número de Documento" ng-change="limpiarCampos();" tabindex="101" focus-me /> 
                      </div>
                      <div class="form-group mb-md col-md-5 col-sm-6"> 
                        <label class="control-label mb-xs"> Nombres </label> 
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.nombres" placeholder="Nombres" disabled /> 
                      </div>
                      <div class="form-group mb-md col-md-4 col-sm-6">
                        <label class="control-label mb-xs"> Apellidos </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.apellidos" placeholder="Apellidos" disabled /> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-6 pl-n">
                        <label class="control-label mb-xs"> Edad </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.edad" placeholder="Edad" disabled /> 
                      </div>
                      <div class="form-group mb-md col-md-9 col-sm-12">
                        <label class="control-label mb-xs"> Dirección </label>
                        <input type="text" class="form-control input-sm" ng-model="fDataVenta.cliente.direccion" placeholder="Dirección" disabled /> 
                      </div>
                    </fieldset>
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <fieldset class="row pl-sm" style="padding-right: 10px;"> 
                      <legend class="col-md-12 p-n"> Datos de la Venta 
                        <div class="pull-right text-right">
                          <small class="text-default block mb-xs" style="font-size: 18px;line-height: 1;" > {{ fDataVenta.aleasDocumento }} N° <strong>{{ fDataVenta.ticket }}</strong>
                            <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarCodigoTicket(); $event.preventDefault();"> <i class="ti ti-reload "></i> </button>
                          </small>  
                          <small class="text-gray block" style="font-size: 14px;line-height: 1;" > Orden N° {{ fDataVenta.orden }} 
                            <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarNumOrden(); $event.preventDefault();"> <i class="ti ti-reload "></i> </button> 
                          </small>
                        </div>
                      </legend> 
                      <div class="form-group mb-md col-md-6 col-sm-12 pl-xs" ng-if="fDataVenta.idtipodocumento != 2"> 
                        <label class="control-label mb-xs"> Tipo de Documento </label>
                        <select class="form-control input-sm" ng-model="fDataVenta.idtipodocumento" ng-change="generarCodigoTicket();" ng-options="item.id as item.descripcion for item in listaTipoDocumento" required tabindex="102"> </select> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12 p-n" ng-if="fDataVenta.idtipodocumento == 2">
                        <label class="control-label mb-xs"> Tipo de Documento </label>
                        <select class="form-control input-sm" ng-model="fDataVenta.idtipodocumento" ng-change="generarCodigoTicket();" ng-options="item.id as item.descripcion for item in listaTipoDocumento" required tabindex="102"> </select> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12" ng-if="fDataVenta.idtipodocumento == 2">
                        <label class="control-label mb-xs"> RUC Empresa </label>
                        <input type="text" ng-model="fDataVenta.ruc" class="form-control input-sm" tabindex="103" placeholder="fDataVenta.idmediopago" required disabled  /> 
                      </div>
                      <div class="form-group mb-md col-md-6 col-sm-12">
                        <label style="width: 100%;" class="control-label mb-n"> 
                          <span class="" style="position: absolute;">Profesional de Salud. </span>
                          <span class="" style="float: right;">
                            <strong>¿Paciente Externo?</strong> <input type="checkbox" ng-model="fDataVenta.pacienteExterno" tabindex="104" ng-change="cambiarChkPacienteExt();" /> 
                          </span>
                        </label> 
                        <input type="text" ng-model="fDataVenta.medico" class="form-control input-sm" tabindex="104" placeholder="Busque Personal de Salud." 
                          uib-typeahead="item as item.descripcion for item in getPersonalMedicoAutocomplete($viewValue)" typeahead-min-length="2" ng-disabled="(fDataVenta.pacienteExterno)" /> 
                      </div>
                      <div class="form-group mb-md col-md-6 col-sm-12 pl-xs">
                        <label class="control-label mb-xs"> Medio de pago </label>
                        <select class="form-control input-sm" ng-change="onChangeMedioPago();" ng-model="fDataVenta.idmediopago" ng-options="item.id as item.descripcion for item in listaMedioPago" tabindex="105"> </select> 
                      </div>
                      <div class="form-group mb-md col-md-6 col-sm-12" ng-if="!fDataVenta.cliente.idtipocliente || fSessionCI.idsedeempresaadmin != fDataVenta.cliente.sede_convenio">
                        <label class="control-label mb-xs"> Tipo de precio </label>
                        <select class="form-control input-sm" ng-change="calcularTotales();" ng-model="fDataVenta.precio" ng-options="item as item.descripcion for item in listaPrecios" tabindex="106"> </select> 
                      </div>

                      <div class="form-group mb-md col-md-3 col-sm-12" ng-if="fDataVenta.cliente.idtipocliente && fSessionCI.idsedeempresaadmin == fDataVenta.cliente.sede_convenio">
                        <label class="control-label mb-xs"> Tipo de precio </label>
                        <select class="form-control input-sm" ng-change="calcularTotales();" ng-model="fDataVenta.precio" ng-options="item as item.descripcion for item in listaPrecios" tabindex="106"> </select> 
                      </div>
                      <div class="form-group mb-md col-md-3 col-sm-12" ng-if="fDataVenta.cliente.idtipocliente && fSessionCI.idsedeempresaadmin == fDataVenta.cliente.sede_convenio">
                        <label><input type="checkbox" value="" ng-model="fDataVenta.convenio" tabindex="107" ng-change="elegirConvenio();" ng-disabled="gridOptions.data.length>0"> Convenio </label>
                      </div>

                    </fieldset>
                  </div>
                  <div class="well well-transparent boxDark col-xs-12 m-n">
                      <div class="row">
                        <div class="form-group mb-md col-md-3 col-sm-12"> 
                          <label class="control-label mb-xs"> Servicio </label> 
                          <label ng-show="gridOptions.data.length >= 1" class="control-label block m-n" style="font-size: 18px;font-weight: bold;"> {{ fDataVenta.temporal.especialidad.descripcion }} </label> 
                          <div ng-show="!(gridOptions.data.length >= 1)">
                            <input id="temporalEspecialidad" type="text" ng-model="fDataVenta.temporal.especialidad" class="form-control input-sm" tabindex="107" placeholder="Busque Servicio." typeahead-loading="loadingLocations" 
                              uib-typeahead="item as item.descripcion for item in getEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedEspecialidad($item, $model, $label)" typeahead-min-length="2" autocomplete="off" ng-change="limpiarTemporal();"/> 
                            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                            <div ng-show="noResultsLEESS">
                              <i class="fa fa-remove"></i> No se encontró resultados 
                            </div>
                          </div>
                        </div>
                        <div class="form-group mb-md col-md-3 col-sm-6"> 
                          <label class="control-label mb-xs"> Producto </label> 
                          <input id="temporalProducto" type="text" ng-model="fDataVenta.temporal.producto" class="form-control input-sm" tabindex="108" placeholder="Busque Producto/Servicio." typeahead-loading="loadingLocations" 
                            uib-typeahead="item as item.descripcion for item in getProductoAutocomplete($viewValue)" 
                            typeahead-on-select="getSelectedProducto($item, $model, $label)" 
                            typeahead-min-length="2" ng-change="limpiarTemporal2();" 
                            typeahead-popup-template-url="customAutocompleteTemplate.html"
                            ng-disabled="!fDataVenta.temporal.especialidad.id"/> 
                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                          <div ng-show="noResultsLPSC">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                          </div>
                        </div> 
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Precio </label>
                          <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.precio" placeholder="Precio" ng-disabled="fDataVenta.temporal.boolEdicionPrecio" tabindex="109" ng-change="precioEditado();"/> 
                        </div>
                        <div class="form-group mb-md col-md-1 col-sm-6">
                          <label class="control-label mb-xs"> Cantidad </label>
                          <input id="temporalCantidad" type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.cantidad" tabindex="110" placeholder="Cantidad" ng-disabled="fDataVenta.temporal.boolCantidad"/> 
                        </div>
                        <div class="form-group mb-md col-lg-1 col-md-2 col-sm-6">
                          <label class="control-label mb-xs"> Descuento </label>
                          <input type="text" class="form-control input-sm" ng-model="fDataVenta.temporal.descuento" tabindex="111" placeholder="Descuento" /> 
                        </div>
                        <div class="form-group mb-sm mt-md col-md-2 col-sm-12"> 
                          <div class="btn-group" style="min-width: 100%">
                              <a href="" class="btn btn-info-alt" tabindex="112" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
                              <a href="" class="btn btn-info-alt dropdown-toggle" tabindex="113" data-toggle="dropdown"><span class="caret"></span></a>
                              <ul class="dropdown-menu sm" role="menu" style="padding:0;">
                                  <li><a ng-click="btnMostrarListadoSolicitudes(); $event.preventDefault();" class="btn btn-info-alt" href="" tabindex="114">SOLICITUDES</a></li>
                                  <li><a ng-click="btnMostrarListadoCampanias(); $event.preventDefault();" class="btn btn-info-alt" href="" tabindex="116">CAMPAÑAS/CUPONES</a></li>
                              </ul>
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
                              <label class="control-label mr-xs text-gray"> SUBTOTAL </label> 
                              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.subtotal" placeholder="Subtotal" style="width: 200px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-gray"> I.G.V. </label> 
                              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.igv" placeholder="I.G.V." style="width: 200px;" /> 
                            </div>
                            <div class="form-inline mt-xs col-xs-12 text-right">
                              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL </label> 
                              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataVenta.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
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
                    <button class="btn-primary btn pull-left" ng-click="goToUrl('/historial-citas'); $event.preventDefault();" > <i class="fa fa-eye"> </i> [F7] Ver Citas </button> 
                    <button class="btn-primary btn" ng-click="verificaUsuario(); $event.preventDefault();" ng-disabled="formVenta.$invalid && !isRegisterSuccess"> <i class="fa fa-save"> </i> [F2] Grabar </button>
                    <button class="btn-default btn" ng-click="nuevo(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F3] Nuevo </button>
                    <button class="btn-success btn" ng-click="imprimir(); $event.preventDefault();" ng-disabled="!isRegisterSuccess"> <i class="fa fa-print"> </i> [F4] Imprimir </button>
                    <button class="btn-warning btn" ng-click="mismoCliente(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F6] Mismo Cliente </button>
                  </div>
                </div>
              </div>
            </div>
            <div ng-show="!cajaAbiertaPorMiSession" class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> La Caja está cerrada. </h2> 
              </div>
              <div class="panel-body">
                <div class="col-xs-12">
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> Proceda a abrir caja para comenzar... </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>