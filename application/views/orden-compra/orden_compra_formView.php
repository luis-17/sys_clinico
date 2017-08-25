<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> -->
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
    <form class="row mr-n ml-n" name="formOrdenCompra">
    	<!-- <div class="col-md-12 col-sm-12">
	    	<div class="form-group mb-md col-md-4 col-sm-12 pl-xs"> 
	            <label class="control-label mb-xs"> Tipo de Entrada </label>
	            <select class="form-control input-sm" ng-model="fDataOC.idtipoentrada" ng-options="item.id as item.descripcion for item in listaTipoEntrada" required > </select> 
	        </div>
        </div> -->
		<div class="col-md-6 col-sm-12">
			<fieldset class="row pr" >
				<legend class="col-md-12 pr-n pl-n pb-n mb-md lead"> Datos del Proveedor
                    <div class="btn-group pull-right" ng-if="submodulo != 'edicion'">
                        <button type="button" class="btn btn-success-alt dropdown-toggle btn-sm" data-toggle="dropdown">
                            <i class="fa fa-tasks"> </i>  ACCIONES <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="" ng-click='btnNuevo();' ng-show="submodulo != 'edicion'"><i class="fa fa-file"></i> Nuevo Proveedor </a></li>
                            <li><a href="" ng-click="btnBuscarProveedor('lg');" ng-show="submodulo != 'edicion'"><i class="fa fa-search"></i> Buscar Proveedor</a></li>
                            <!-- <li class="divider"></li> -->
                        </ul>
                    </div>

	                <!-- <button ng-click="btnNuevo();" class="btn btn-success-alt pull-right btn-sm mt-sm ml" type="button" ng-show="submodulo != 'edicion'"> <i class="fa fa-file"></i> Nuevo Proveedor </button> 
	                <button ng-click="btnBuscarProveedor('lg');" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button" ng-show="submodulo != 'edicion'"> <i class="fa fa-search"></i> Buscar Proveedor </button>  -->
	                
	            </legend>
				<div class="form-group mb-xs col-md-3 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> RUC. <small class="text-danger">(*)</small></label> 
	                  <input id="ruc" type="text" class="form-control input-sm" ng-model="fDataOC.ruc" 
	                    ng-enter="obtenerDatosProveedor(); $event.preventDefault();" placeholder="RUC Nº" ng-change="limpiarCampos();" tabindex="101" ng-pattern="pRUC" ng-readonly="submodulo == 'edicion'" focus-me /> 
	            </div>
	            <div class="form-group mb-xs col-md-5 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> Razón Social </label> 
	                <input type="text" class="form-control input-sm" ng-model="fDataOC.proveedor.razon_social" placeholder="Razón Social" readonly="readonly" /> 
	              </div>
	            <div class="form-group mb-xs col-md-4 col-sm-6 pl-xs">
	                <label class="control-label mb-xs"> Representante </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataOC.proveedor.representante" placeholder="Representante Legal" readonly="readonly" /> 
	            </div>
	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs">
	                <label class="control-label mb-xs"> Telefono </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataOC.proveedor.telefono" placeholder="Telefono" readonly="readonly" /> 
	            </div>
	            <div class="form-group mb-xs col-md-5 col-sm-12 pl-xs" ng-if="fDataOC.forma_pago == 1" >
	                <label class="control-label mb-xs"> Forma de Pago </label>
	                <select class="form-control input-sm" ng-model="fDataOC.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago" tabindex="102"> </select> 
	            </div>

	            
	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" ng-if="fDataOC.forma_pago == 2 || fDataOC.forma_pago == 3" >
	                <label class="control-label mb-xs"> Forma de Pago </label>
	                <select class="form-control input-sm" ng-model="fDataOC.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago" tabindex="102"> </select> 
	            </div>
	            <div class="form-group mb-xs col-md-2 col-sm-6 pl-xs" ng-if="fDataOC.forma_pago == 2" >
	                <label class="control-label mb-xs"> Días </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataOC.letras" placeholder="Cant. días"  tabindex="103"/>
	            </div>
	            <div class="form-group mb-xs col-md-2 col-sm-6 pl-xs" ng-if="fDataOC.forma_pago == 3" >
	                <label class="control-label mb-xs"> Letras </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataOC.letras" placeholder="Cant. Letras"  tabindex="103"/>
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> Moneda </label>
	                <select class="form-control input-sm" ng-model="fDataOC.moneda" ng-options="item.id as item.descripcion for item in listaMoneda" tabindex="104" ng-change="cambiarSimbolo();"> </select> 
	            </div>
	           <!--  <div class="form-group mb-md col-md-9 col-sm-12 pr-n">
	                <label class="control-label mb-xs"> Dirección </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataOC.proveedor.direccion_fiscal" placeholder="Dirección" readonly="readonly" /> 
	            </div> -->
			</fieldset>
		</div>

		<div class="col-md-6 col-sm-12" >
			<fieldset class="row">
				<legend class="col-lg-12 pr-n pl-n pb-n mb-md lead"> Datos de la Orden de Compra 
					<div class="pull-right text-right pt">
	                    <small class="text-default block mb-xs" style="font-size: 18px;line-height: 1;" > Orden N° <strong>{{ fDataOC.orden_compra }}</strong>
	                        <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarNumOrden(); $event.preventDefault();" ng-if="submodulo != 'edicion'"> <i class="ti ti-reload "></i> </button>
	                    </small>  
                    </div>
				</legend>
				
	            
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Fecha de Creación <small class="text-danger">(*)</small></label>
	                <input type="text" class="form-control input-sm mask" ng-model="fDataOC.fecha_movimiento" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="105"/>
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> Fecha de Aprobación </label>
	                <input disabled type="text" class="form-control input-sm mask" ng-model="fDataOC.fecha_aprobacion" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="106"/>
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> Fecha de Ingreso Estimada <small class="text-danger">(*)</small></label>
	                <input type="text" class="form-control input-sm mask" ng-model="fDataOC.fecha_entrega" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="107"/>
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Almacen </label>
	                <select class="form-control input-sm" ng-model="fDataOC.almacen" ng-options="item as item.descripcion for item in listaAlmacenes" ng-change="generarNumOrden()" ng-disabled="submodulo == 'edicion'" tabindex="108"> </select> 
	            </div>
	           	<div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> Tipo de material </label>
	                <select class="form-control input-sm" ng-model="fDataOC.tipoMaterial" ng-options="item as item.descripcion for item in listaTipoMaterial" ng-change="generarNumOrden()" tabindex="109" ng-disabled="submodulo != 'nuevo'"> </select> 
	            </div>
	            <!-- <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" ng-if="submodulo == 'edicion'">
	                <label class="control-label mb-xs"> Tipo de material </label>
	                <select class="form-control input-sm" ng-model="fDataOC.tipoMaterial" ng-options="item.id as item.descripcion for item in listaTipoMaterial" ng-disabled="true" > </select> 
	            </div> -->
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Estado de la orden </label>
	                <select disabled class="form-control input-sm" ng-model="fDataOC.estado_orden" ng-options="item.id as item.descripcion for item in listaEstadoOrden" ng-change="cambiarFecha();" tabindex="110"> </select> 
	            </div>
    		</fieldset>
		</div>
		<div class="col-md-12 col-xs-12">
			<fieldset class="row" >
				<legend class="col-lg-12 pr-n pl-n pb-n  mb-md lead"> Detalle </legend>
				<!-- <div class="input-group">
					<span class="input-group-btn ">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.iddepartamento" placeholder="ID" tabindex="12" ng-change="obtenerDepartamentoPorCodigo(); $event.preventDefault();" min-length="2" />
					</span>
					<input id="fDatadepartamento" type="text" class="form-control input-sm" ng-model="fData.departamento" placeholder="Ingrese el Departamento o Click en Seleccionar" typeahead-loading="loadingLocationsDpto" uib-typeahead="item as item.descripcion for item in getDepartamentoAutocomplete($viewValue)" typeahead-on-select="getSelectedDepartamento($item, $model, $label)" typeahead-min-length="2" tabindex="13"/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaDptos('md')">Seleccionar</button>
					</span>
				</div> -->
				<div class="form-group mb-xs col-md-5 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Producto </label>
		            <div class="input-group">
		            	<span class="input-group-btn ">
							<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataOC.temporal.idmedicamento" placeholder="ID" tabindex="111" ng-change="limpiarProducto();" ng-enter="obtenerMedicamentoPorCodigo(); $event.preventDefault();" min-length="1" />
						</span>
						<input id="temporalProducto" type="text" ng-model="fDataOC.temporal.producto" class="form-control input-sm"
							placeholder="Busque Producto." typeahead-loading="loadingLocations" 
		            		uib-typeahead="item as item.medicamento for item in getProductoAutocomplete($viewValue)"
		            		typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" autocomplete ="off"
		            		ng-change="limpiarId();"  tabindex=""/>
		            </div>
		             

		            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
		            <div ng-show="noResultsLPSC">
		                <i class="fa fa-remove"></i> No se encontró resultados 
		            </div>
		        </div>
		        <div class="form-group pl-xs mb-xs col-md-1 col-sm-6">
		                <label class="control-label mb-xs"> Caja/Und. </label>
		                <select class="form-control input-sm" ng-model="fDataOC.temporal.caja_unidad" ng-options="item.id as item.descripcion for item in listadoCajaUnidad" tabindex="" ng-disabled="!fDataOC.temporal.acepta_caja_unidad" ng-change="ultimoPrecioCompra(fDataOC.temporal.producto.id)"> </select> 
		        </div>
		        <div class="form-group mb-xs col-md-1 col-sm-6">
	                <label class="control-label mb-xs"> Cantidad </label>
	                <input id="temporalCantidad" type="text" class="form-control input-sm" ng-model="fDataOC.temporal.cantidad" tabindex="112" placeholder="Cantidad"  ng-change="calcularImporte();"/> 
	            </div>   
	            <div class="form-group mb-xs col-md-1 col-sm-6">
	                <label class="control-label mb-xs"> P. Unit.</label>
	                <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataOC.temporal.precio" placeholder="Precio" tabindex="113" ng-change="calcularImporte();"/> 
	            </div>
	            
	            <div class="form-group mb-xs col-md-1 col-sm-6 pl-xs pr-xs">
	                <label class="control-label mb-xs"> Descuento (%)</label>
	                <input id="temporaldescuento" type="text" class="form-control input-sm" ng-model="fDataOC.temporal.descuento" tabindex="114" placeholder="%" ng-change="calcularImporte();"/> 
	            </div>
	           	<div class="form-group mb-xs col-md-1 col-sm-6">
	                <label class="control-label mb-xs"> Importe </label>
	                <input id="temporalImporte" type="text" class="form-control input-sm" ng-model="fDataOC.temporal.importe_sin" placeholder="Importe" disabled ng-if="fDataOC.modo_igv == 1"/> 
	                <input id="temporalImporte" type="text" class="form-control input-sm" ng-model="fDataOC.temporal.importe" placeholder="Importe" disabled ng-if="fDataOC.modo_igv == 2"/> 
	            </div>

	            <div class="form-group mb-sm mt-md col-md-1 col-sm-12"> 
	              <div class="btn-group" style="min-width: 100%">
		                <a href="" class="btn btn-info-alt" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;"  tabindex="115">Agregar</a>
	              </div>
	              <!-- <input type="button" class="btn btn-info-alt col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" />  -->
	            </div>
	            <div class="form-group col-xs-12 m-n p-n">
	              <label class="control-label m-n">Agregar al detalle: </label>
	              <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid" style="overflow: hidden;" ng-style="getTableHeight();"></div>
	            </div>
			</fieldset>
			
		</div>
		<div class="col-md-12 col-xs-12 mt-md">
			<fieldset class="row">
				<div class="col-md-6 col-sm-6  pl-n">

	                <label class="control-label mb-xs"> Observaciones </label>
	                <textarea class="form-control input-sm" ng-model="fDataOC.motivo_movimiento" tabindex="116"></textarea>
	                <!-- <input id="temporalLote" type="text" class="form-control input-sm" ng-model="fDataOC.temporal.lote" tabindex="" placeholder="" /> -->
		        </div>
		        <div class="col-md-2 col-sm-6 pr-n form-group">
		        	<label class="radio">
						<input type="radio" name="optionsRadios" id="optionsRadios1" value="1" ng-model="fDataOC.modo_igv" ng-change="cambiarModo();calcularImporte();" checked  tabindex="117">
						Precios sin IGV
					</label>

					<label class="radio">
						<input type="radio" name="optionsRadios" id="optionsRadios2" value="2" ng-model="fDataOC.modo_igv" ng-change="cambiarModo();calcularImporte();"  tabindex="118">
						Precios con IGV
					</label>
		        </div>
		        <div class="col-md-4 col-sm-6 pr-n">
		            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-gray"> SUBTOTAL </label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataOC.subtotal" placeholder="Subtotal" style="width: 200px;" /> 
		            </div>
		            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-gray"> I.G.V. </label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataOC.igv" placeholder="I.G.V." style="width: 200px;" /> 
		            </div>
		            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL {{fDataOC.simbolo_monetario}}</label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataOC.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
		            </div>
	          	</div>	
			</fieldset>
          	
        </div>


	</form>
</div>
<div class="modal-footer">
	
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formOrdenCompra.$invalid" tabindex="119"><i class="fa fa-save"> </i> [F2] Grabar</button>
    <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(idmovimiento,1);" ng-disabled="!isRegisterSuccess" ng-show="submodulo !='edicion'"><i class="fa fa-print"  tabindex="120"></i> [F4] IMPRIMIR</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="121">Cerrar</button>
</div>