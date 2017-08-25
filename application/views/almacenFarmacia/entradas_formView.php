<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> -->
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
    <form class="row mr-n ml-n" name="formEntrada">
    	<!-- <div class="col-md-12 col-sm-12">
	    	<div class="form-group mb-md col-md-4 col-sm-12 pl-xs"> 
	            <label class="control-label mb-xs"> Tipo de Entrada </label>
	            <select class="form-control input-sm" ng-model="fDataEntrada.idtipoentrada" ng-options="item.id as item.descripcion for item in listaTipoEntrada" required > </select> 
	        </div>
        </div> -->
        <div class="col-md-6 col-sm-12" >
			<fieldset class="row">
				<legend class="col-lg-12 pr-n pl-n mb-sm lead"> Datos de la Compra 
					
				</legend>
				<div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Almacen </label>
	                <select class="form-control input-sm" ng-model="fDataEntrada.almacen" ng-options="item as item.descripcion for item in listaAlmacenes" tabindex="100" ng-change="cargarOrdenCbo();"> </select> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" ng-if="fDataEntrada.idtipoentrada == 2">
	                <label class="control-label mb-xs"> Orden de compra </label>
	                <select class="form-control input-sm" ng-model="fDataEntrada.orden_compra" ng-options="item as item.descripcion for item in listaOrdenes" tabindex="101" ng-disabled="fDataEntrada.estemporal" ng-change="cargarDetalle()"> </select> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> Fecha de Movimiento <small class="text-danger">(*)</small></label>
	                <input tabindex="102" type="text" class="form-control input-sm mask" ng-model="fDataEntrada.fecha_entrada" data-inputmask="'alias': 'dd-mm-yyyy'" ng-disabled="fSessionCI.key_group === 'key_caja_far'" />

	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" ng-if="fDataEntrada.idtipoentrada != 6">
	                <label class="control-label mb-xs"> Factura <small class="text-danger" ng-show="fDataEntrada.idtipoentrada==2">(*)</small> </label>
	                <input id="factura" type="text" class="form-control input-sm" ng-model="fDataEntrada.factura" placeholder="Factura Nº" tabindex="103"/> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" ng-if="fDataEntrada.idtipoentrada == 2">
	                <label class="control-label mb-xs"> Guia de Remisión </label>
	                <input id="factura" type="text" class="form-control input-sm" ng-model="fDataEntrada.guia_remision" placeholder="Guia de Remisión Nº" tabindex="104"/> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" ng-if="fDataEntrada.idtipoentrada == 2">
	                <label class="control-label mb-xs"> Fecha de Compra <small class="text-danger">(*)</small></label>
	                <input tabindex="105" type="text" class="form-control input-sm mask" ng-model="fDataEntrada.fecha_compra" data-inputmask="'alias': 'dd-mm-yyyy'" />
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs" ng-if="fDataEntrada.idtipoentrada == 2">
	                <label class="control-label mb-xs"> Fecha Vencimiento Factura <small class="text-danger">(*)</small></label>
	                <input tabindex="105" type="text" class="form-control input-sm mask" ng-model="fDataEntrada.fecha_vence_factura" data-inputmask="'alias': 'dd-mm-yyyy'" />
	            </div>	            
	            
    		</fieldset>
		</div>
		<div class="col-md-6 col-sm-12" ng-if="fDataEntrada.idtipoentrada == 2 || fDataEntrada.idtipoentrada == 4">
			<fieldset class="row pr" >
				<legend class="col-md-12 pr-n pl-n mb-sm lead"> Datos del Proveedor 
	                <button ng-click="btnNuevo();" class="btn btn-success-alt pull-right btn-sm mt-xs ml" type="button" ng-show="fDataEntrada.orden_compra.id == 0 "> <i class="fa fa-file"></i> Nuevo Proveedor </button> 
	                <button ng-click="btnBuscarProveedor('lg');" class="btn btn-info-alt pull-right btn-sm mt-xs ml" type="button"  ng-show="fDataEntrada.orden_compra.id == 0 "> <i class="fa fa-search"></i> Buscar Proveedor </button> 
	            </legend>
				<div class="form-group mb-xs col-md-3 col-sm-6 pl-n"> 
	                <label class="control-label mb-xs"> RUC. <small class="text-danger">(*)</small> </label> 
	                  <input id="ruc" type="text" class="form-control input-sm" ng-model="fDataEntrada.ruc" 
	                    ng-enter="obtenerDatosProveedor(); $event.preventDefault();" placeholder="RUC Nº" ng-change="limpiarCampos();" tabindex="106" tooltip="Ingrese el RUC y presione ENTER" ng-disabled="fDataEntrada.orden_compra.id != 0 " required="true" focus-me/> 
	            </div>
	            <div class="form-group mb-xs col-md-5 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> Razón Social </label> 
	                <input type="text" class="form-control input-sm" ng-model="fDataEntrada.proveedor.razon_social" placeholder="Razón Social" readonly="readonly" /> 
	              </div>
	            <div class="form-group mb-xs col-md-4 col-sm-6 pl-xs pr-n">
	                <label class="control-label mb-xs"> Representante </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataEntrada.proveedor.representante" placeholder="Representante Legal" readonly="readonly" /> 
	            </div>
	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-n">
	                <label class="control-label mb-xs"> Telefono </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataEntrada.proveedor.telefono" placeholder="Telefono" readonly="readonly" /> 
	            </div>
	            <div class="form-group mb-xs col-md-5 col-sm-12 pl-xs" ng-if="fDataEntrada.forma_pago == 1" >
	                <label class="control-label mb-xs"> Forma de Pago </label>
	                <select class="form-control input-sm" ng-model="fDataEntrada.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago"  ng-disabled="fDataEntrada.orden_compra.id != 0 "> </select> 
	            </div>
	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" ng-if="fDataEntrada.forma_pago == 2 || fDataEntrada.forma_pago == 3" >
	                <label class="control-label mb-xs"> Forma de Pago </label>
	                <select class="form-control input-sm" ng-model="fDataEntrada.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago" ng-disabled="fDataEntrada.orden_compra.id != 0 " > </select> 
	            </div>
	            <div class="form-group mb-xs col-md-2 col-sm-6 pl-xs" ng-if="fDataEntrada.forma_pago == 2" >
	                <label class="control-label mb-xs"> Días </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataEntrada.letras" placeholder="Cant. Días"  ng-disabled="fDataEntrada.orden_compra.id != 0 "/>
	            </div>
	            <div class="form-group mb-xs col-md-2 col-sm-6 pl-xs" ng-if="fDataEntrada.forma_pago == 3" >
	                <label class="control-label mb-xs"> Letras </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataEntrada.letras" placeholder="Cant. Letras"  ng-disabled="fDataEntrada.orden_compra.id != 0 "/>
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs pr-n" >
	                <label class="control-label mb-xs"> Moneda </label>
	                <select class="form-control input-sm" ng-model="fDataEntrada.moneda" ng-options="item.id as item.descripcion for item in listaMoneda" ng-disabled="fDataEntrada.orden_compra.id != 0 " > </select> 
	            </div>
	            <!-- <div class="form-group mb-xs col-md-9 col-sm-12 pr-n">
	                <label class="control-label mb-xs"> Dirección </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataEntrada.proveedor.direccion_fiscal" placeholder="Dirección" readonly="readonly" /> 
	            </div> -->
			</fieldset>
		</div>
		
		<div class="col-md-12 col-xs-12">
			<fieldset class="row" >
				<legend class="col-lg-12 pr-n pl-n mb-sm lead"> Detalle </legend>
				<div ng-show="fDataEntrada.orden_compra.id == 0">
					<div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
			            <label class="control-label mb-xs"> Medicamento <small class="text-danger" ng-if="fDataEntrada.estemporal">(*)</small> </label>
			            <input id="temporalProducto" type="text" ng-model="fDataEntrada.temporal.producto" class="form-control input-sm" tabindex="108" placeholder="Busque Producto." typeahead-loading="loadingLocations" 
			            uib-typeahead="item as item.medicamento for item in getProductoAutocomplete($viewValue)"  typeahead-on-select="getSelectedProducto($item, $model, $label)" typeahead-min-length="2" autocomplete ="off"/> 
			            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
			            <div ng-show="noResultsLPSC">
			                <i class="fa fa-remove"></i> No se encontró resultados 
			            </div>
			        </div>
			        <div class="form-group pl-xs mb-xs col-md-1 col-sm-6">
		                <label class="control-label mb-xs"> Caja/Und. </label>
		                <select class="form-control input-sm" ng-model="fDataEntrada.temporal.caja_unidad" ng-options="item.id as item.descripcion for item in listadoCajaUnidad" tabindex="" ng-disabled="!fDataEntrada.temporal.acepta_caja_unidad" ng-change="ultimoPrecioCompra(fDataEntrada.temporal.producto.id)" ng-if="fDataEntrada.idtipoentrada == 2"> </select>
		                <select class="form-control input-sm" ng-model="fDataEntrada.temporal.caja_unidad" ng-options="item.id as item.descripcion for item in listadoCajaUnidad" tabindex="" ng-disabled="!fDataEntrada.temporal.acepta_caja_unidad" ng-if="fDataEntrada.idtipoentrada != 2"> </select> 
		            </div>
			        <div class="form-group pl-xs mb-xs col-md-1 col-sm-6">
		                <label class="control-label mb-xs"> Cant. <small class="text-danger" ng-if="fDataEntrada.estemporal">(*)</small> </label>
		                <input id="temporalCantidad" type="text" pattern="[0-9]+" ng-pattern-restrict class="form-control input-sm" ng-model="fDataEntrada.temporal.cantidad" tabindex="109" placeholder="Cantidad"  ng-change="calcularImporte();"/> 
		            </div>   
		            <div class="form-group pl-xs mb-xs col-md-1 col-sm-6">
		                <label class="control-label mb-xs"> P. Unit.<small class="text-danger" ng-if="fDataEntrada.estemporal">(*)</small> </label>
		                <input id="temporalPrecio" type="text" class="form-control input-sm" ng-model="fDataEntrada.temporal.precio" placeholder="Precio" tabindex="110" ng-change="calcularImporte();" ng-disabled="fDataEntrada.idtipoentrada != 2"/> 
		            </div>
		            
		            <div class="form-group mb-xs col-md-1 col-sm-6 pl-xs pr-xs">
		                <label class="control-label mb-xs"> Descuento (%)</label>
		                <input id="temporaldescuento" type="text" class="form-control input-sm" ng-model="fDataEntrada.temporal.descuento" tabindex="111" placeholder="%" ng-change="calcularImporte();" ng-disabled="fDataEntrada.idtipoentrada != 2"/> 
		            </div>
		           	<div class="form-group mb-xs col-md-1 col-sm-6" ng-if="fDataEntrada.modo_igv == 1">
		                <label class="control-label mb-xs"> Importe </label>
		                <input id="temporalImporte" type="text" class="form-control input-sm" ng-model="fDataEntrada.temporal.importe_sin" placeholder="Importe" disabled /> 
		            </div>
		            <div class="form-group mb-xs col-md-1 col-sm-6" ng-if="fDataEntrada.modo_igv == 2">
		                <label class="control-label mb-xs"> Importe </label>
		                <input id="temporalImporte" type="text" class="form-control input-sm" ng-model="fDataEntrada.temporal.importe" placeholder="Importe" disabled /> 
		            </div>
		            <div class="form-group pl-xs mb-xs col-md-1 col-sm-6">
		                <label class="control-label mb-xs"> Fec.Venc. <small class="text-danger" ng-if="fDataEntrada.estemporal">(*)</small> </label>
		                <input id="temporalFechaVencimiento" type="text" class="form-control input-sm" ng-model="fDataEntrada.temporal.fecha_vencimiento" tabindex="112" placeholder="dd-mm-yyyy" data-inputmask="'alias': 'dd-mm-yyyy'"/> 
		            </div>
		            <div class="form-group pl-xs mb-xs col-md-1 col-sm-6">
		                <label class="control-label mb-xs"> Lote </label>
		                <input id="temporalLote" type="text" class="form-control input-sm" ng-model="fDataEntrada.temporal.lote" tabindex="113" placeholder="Lote" /> 
		            </div>
		            <div class="form-group mb-sm mt-md col-md-1 col-sm-12"> 
		              <div class="btn-group" style="min-width: 100%">
			                <a href="" class="btn btn-info-alt" tabindex="114" ng-click="agregarItem(); $event.preventDefault();" style="min-width: 80%;">Agregar</a>
		              </div>
		              <!-- <input type="button" class="btn btn-info-alt col-md-12 btn-sm" ng-click="agregarItem(); $event.preventDefault();" tabindex="110" value="Agregar" />  -->
		            </div>
				</div>
	            <div class="form-group col-xs-12 m-n p-n">
	              <label class="control-label m-n">Ingrese la fecha de vencimiento en el formato <span class="text-red">dd-mm-yyyy</span> (dia-mes-año): </label>
	              <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid" style="overflow: hidden;" ng-style="getTableHeight();"></div>
	            </div>
			</fieldset>
			
		</div>
		<div class="col-md-12 col-xs-12 mt-md">
			<fieldset class="row">
				<div class="col-md-6 col-sm-6  pl-n">

	                <label class="control-label mb-xs"> Observaciones </label>
	                <textarea class="form-control input-sm" ng-model="fDataEntrada.motivo_movimiento" tabindex="115"></textarea>
		        </div>
		        <div class="col-md-2 col-sm-6 pr-n form-group">
		        	<label class="radio" >
						<input type="radio" name="optionsRadios" id="optionsRadios1" value="1" ng-model="fDataEntrada.modo_igv" ng-disabled='fDataEntrada.orden_compra.id != 0' ng-change="cambiarModo();calcularImporte();">
						Precios sin IGV
					</label>

					<label class="radio" >
						<input type="radio" name="optionsRadios" id="optionsRadios2" value="2" ng-model="fDataEntrada.modo_igv" ng-disabled='fDataEntrada.orden_compra.id != 0' ng-change="cambiarModo();calcularImporte();">
						Precios con IGV
					</label>
		        </div>
		        <div class="col-md-4 col-sm-6 pr-n">
		            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-gray"> SUBTOTAL </label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataEntrada.subtotal" placeholder="Subtotal" style="width: 200px;" /> 
		            </div>
		            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-gray"> I.G.V. </label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataEntrada.igv" placeholder="I.G.V." style="width: 200px;" /> 
		            </div>
		            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL </label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataEntrada.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
		            </div>
	          	</div>
			</fieldset>
          	
        </div>


	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formEntrada.$invalid" tabindex="116"><i class="fa fa-save"> </i> Grabar</button>
    <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(idmovimiento,1);" ng-disabled="!isRegisterSuccess"><i class="fa fa-print"></i> [F4] IMPRIMIR</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="117">Cerrar</button>
</div>