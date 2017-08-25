<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
    <form class="row mr-n ml-n" name="formCompraServ"> 
		<div class="col-md-6 col-sm-12">
			<fieldset class="row pr" >
				<legend class="col-md-12 pr-n pl-n pb-n mb-md lead"> Datos de la Empresa 
	                <button ng-click="btnNuevo('',false);" class="btn btn-success-alt pull-right btn-sm ml" type="button" ng-show="modulo != 'edicion'"> <i class="fa fa-file"></i> Nueva Empresa </button> 
	                <button ng-click="btnBuscarProveedor('lg');" class="btn btn-info-alt pull-right btn-sm ml" type="button" ng-show="modulo != 'edicion'"> <i class="fa fa-search"></i> Buscar Empresa </button> 
	                <button ng-show="fDataES.proveedor.razon_social.length > 0" ng-click="btnEditar('',false);" class="btn btn-warning-alt pull-right btn-sm ml" type="button"> <i class="fa fa-edit"></i> Editar Empresa </button>
	            </legend>
				<div class="form-group mb-xs col-md-3 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> RUC. <small class="text-danger">(*)</small></label> 
	                  <input id="ruc" type="text" class="form-control input-sm" ng-model="fDataES.ruc" 
	                    ng-enter="obtenerDatosProveedor(); $event.preventDefault();" placeholder="Nº RUC + [Enter]" ng-change="limpiarCampos();" tabindex="101" ng-pattern="pRUC" ng-readonly="modulo == 'edicion'" focus-me /> 
	            </div>
	            <div class="form-group mb-xs col-md-9 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> Razón Social </label> 
	                <input type="text" class="form-control input-sm" ng-model="fDataES.proveedor.razon_social" placeholder="Razón Social" readonly="readonly" /> 
	            </div> 
	            <div class="form-group mb-xs col-md-3 col-sm-12 pl-xs" ng-if="fDataES.forma_pago == 1" >
	                <label class="control-label mb-xs"> Forma de Pago </label>
	                <select class="form-control input-sm" ng-model="fDataES.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago" tabindex="110" ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14"> </select> 
	            </div>
	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" ng-if="fDataES.forma_pago == 2" >
	                <label class="control-label mb-xs"> Forma de Pago </label>
	                <select class="form-control input-sm" ng-model="fDataES.forma_pago" ng-options="item.id as item.descripcion for item in listaFormaPago" tabindex="120"> </select> 
	            </div>
	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" ng-if="fDataES.forma_pago == 2" >
	                <label class="control-label mb-xs"> Fecha Venc. Crédito </label> 
	                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataES.fecha_venc_credito" tabindex="130" required />
	            </div>

	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" ng-if="fDataES.tipodocumento.id != 7 && fDataES.tipodocumento.id != 14 &&  fDataES.tipodocumento.id != 13" >
	                <label class="control-label mb-xs"> Orden de Compra </label> 
	                <input type="text" class="form-control input-sm " ng-model="fDataES.orden_compra" tabindex="140"  />
	            </div>
	            <div class="form-group mb-xs col-md-6 col-sm-6 pl-xs" ng-if="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14">
	                <label class="control-label mb-xs"> Nº Documento de Referencia  <small class="text-danger">(*)</small></label> 
	                <input id="temporalProducto" type="text" ng-model="fDataES.numero_compra" class="form-control input-sm" tabindex="135" placeholder="Busque Número de doc. de referencia." required="true"
                		typeahead-loading="loadingLocations" 
                        uib-typeahead="item as item.descripcion for item in getComprasAutocomplete($viewValue)"
                        typeahead-on-select="getSelectedCompra($item, $model, $label)"
                        typeahead-min-length="2"
                        typeahead-show-hint="true"
                        autocomplete ="off"
                        ng-disabled="fDataES.proveedor.razon_social == null"
                        ng-change="limpiarGrilla();"/>
	            </div>

			</fieldset>
		</div>

		<div class="col-md-6 col-sm-12" >
			<fieldset class="row">
				<legend class="col-lg-12 pr-n pl-n pb-n mb-md lead"> Datos de Documento </legend>
				<div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Tipo de Documento </label>
		            <select ng-change="calcularTotales();" class="form-control input-sm" ng-model="fDataES.tipodocumento" ng-options="item as item.descripcion for item in metodos.arrTipoDocumentos" tabindex="150" > </select>
		        </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> N° de Documento <small class="text-danger">(*)</small> </label> 
	                <div class="input-group" style="width: 100%;">
		            	<input tabindex="160" style="width: 25%;margin-right: 6px;" placeholder="" class="form-control input-sm" type="text" ng-model="fDataES.serie_documento" required maxlength="3" minlength="3"/> 
		            	<input tabindex="170" style="width: 70%;" class="form-control input-sm" type="text" ng-model="fDataES.numero_documento" placeholder="Nº de Documento" required />  
		            </div> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Fecha de Emisión <small class="text-danger">(*)</small></label> 
	                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataES.fecha_emision" tabindex="180" required /> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Guía de Remisión </label> 
	                <input type="text" class="form-control input-sm" ng-model="fDataES.guia_remision" tabindex="190" placeholder="Guía de Remisión" /> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Operación <small class="text-danger">(*)</small> </label>
		            <select ng-disabled="fDataES.operacionDisabled" required id="temporalOperacionServ" ng-change="getlistaSubOperaciones();" class="form-control input-sm" ng-model="fDataES.operacion" ng-options="item as item.descripcion for item in metodos.listaOperacionesForm" tabindex="200" > </select>
		        </div>
		        <div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Sub-Operación <small class="text-danger">(*)</small> </label>
		            <select ng-disabled="fDataES.subOperacionDisabled" required class="form-control input-sm" ng-model="fDataES.suboperacion" ng-options="item as item.descripcion for item in metodos.listaSubOperacionesForm" tabindex="210" > </select>
		        </div>
    		</fieldset>
		</div>
		<div class="col-md-12 col-xs-12">
			<fieldset class="row" >
				<legend class="col-lg-12 pr-n pl-n pb-n  mb-md lead"> Detalle </legend> 
				<div class="form-group mb-xs col-md-1 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Cuenta </label>
		            <input id="temporalDescripcion" type="text" class="form-control input-sm" ng-model="fDataES.temporal.cuenta" ng-disabled="fDataES.temporal.cuentaDisabled" tabindex="220" placeholder="N° Cuenta" /> 
		        </div>
				<div class="form-group mb-xs col-md-3 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Glosa </label>
		            <input id="temporalDescripcion" type="text" class="form-control input-sm" ng-model="fDataES.temporal.descripcion" tabindex="270" placeholder="Glosa" ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14" /> 
		        </div>
	           	<div class="form-group mb-xs col-md-2 col-sm-6 pl-n">
	                <label class="control-label mb-xs"> Importe <small class="text-gray">( SIN {{ fDataES.tipodocumento.nombre_impuesto }} )</small> </label> 
					<input id="temporalImporte" type="text" class="form-control input-sm" ng-model="fDataES.temporal.importe" placeholder="Importe(SIN {{ fDataES.tipodocumento.nombre_impuesto }})" tabindex="320" ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14"/> 
	            </div>
	            <div class="form-group mb-sm mt-lg col-md-2 col-sm-12 pl-n"> 
					<a href="" class="btn btn-info-alt btn-sm ml" ng-click="agregarItem(); $event.preventDefault();" tabindex="330" ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14"> <i class="fa fa-plus"></i> AGREGAR </a> 
	            </div> 
	            <div class="form-group col-xs-12 m-n p-n">
	              <label class="control-label m-n">Agregar al detalle: </label>
	              <button class="btn btn-sm btn-info pull-right mb-xs" ng-click="btnRecargarGrilla();$event.preventDefault();" ng-if="fDataES.numero_compra.id" tooltip-placement="left" uib-tooltip="RECARGAR DETALLE"><i class="fa fa-refresh"></i></button>
	              <div ui-if="gridOptions.data.length>0" ui-grid="gridOptions" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid" style="width: 100%; overflow: hidden;" ng-style="getTableHeight();"></div>
	            </div>
			</fieldset>
		</div>
		<div class="col-md-12 col-xs-12 mt-md">
			<fieldset class="row">
				<div class="col-md-6 col-sm-6  pl-n">
	                <label class="control-label mb-xs"> Observaciones </label>
	                <textarea class="form-control input-sm" ng-model="fDataES.motivo_movimiento" tabindex="260"></textarea> 
		        </div>
		        <div class="col-md-6 col-sm-6 pr-n">
			        <div class="col-md-3 col-sm-6 pr-n form-group">
			        	<label class="radio">
							<input type="radio" name="optionsRadios" ng-model="fDataES.moneda" value="soles" tabindex="270" ng-change="onChangeMoneda();" ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14" /> 
							SOLES 
						</label> 
						<label class="radio">
							<input type="radio" name="optionsRadios" ng-model="fDataES.moneda" value="dolares" tabindex="280" ng-change="onChangeMoneda();" ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14" /> 
							DÓLARES 
							<small class="text-gray block"> TIPO DE CAMBIO {{ fDataES.tipo_cambio_venta }} </small>
						</label>
			        </div>
			        <div class="col-md-1 col-sm-6 pr-n form-group"> 
						<label class="checkbox block">
							<input ng-disabled="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14" type="checkbox" ng-model="fDataES.inafecto" 
								ng-click="calcularTotales();" ng-checked="fDataES.inafecto"> ¿Inafecto? 
						</label>						
			        </div> 
			        <div class="col-md-8 col-sm-6 pr-n">
			            <div class="form-inline mt-xs col-xs-12 text-right pr-n" ng-if="fDataES.tipodocumento.porcentaje != '0'">
			              <label class="control-label mr-xs text-gray"> SUBTOTAL </label>
			              <input id="" type="text" class="form-control input-sm pull-right text-center" ng-model="fDataES.subtotal" disabled placeholder="SUBTOTAL" style="width: 200px;"/> 
			            </div>
			            <div class="form-inline mt-xs col-xs-12 text-right pr-n" ng-if="fDataES.tipodocumento.porcentaje != '0'"> 
			              <label class="control-label mr-xs text-gray"> {{ fDataES.tipodocumento.nombre_impuesto || fDataES.nombre_impuesto_referencia }} ({{ fDataES.tipodocumento.porcentaje }}%) </label> 
			              <input id="" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataES.impuesto" placeholder="{{ fDataES.tipodocumento.nombre_impuesto }}" style="width: 200px;" /> 
			            </div>
			            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
			              <label ng-if="fDataES.tipodocumento.id != 7 && fDataES.tipodocumento.id != 14" class="control-label mr-xs text-danger" 
			              	style="font-size: 17px; font-weight: bolder;"> TOTAL <small> {{ fDataES.simbolo_monetario }} </small>  </label> 
			              <label ng-if="fDataES.tipodocumento.id == 7 || fDataES.tipodocumento.id == 14" class="control-label mr-xs text-danger" 
			              	style="font-size: 17px; font-weight: bolder;"> TOTAL NOTA DE CRÉDITO <small> {{ fDataES.simbolo_monetario }} </small> 
			              </label> 
			              <input id="" type="text" class="form-control input-sm pull-right text-center" ng-disabled="fDataES.tipodocumento.id != 7 && fDataES.tipodocumento.id != 14" 
			              	ng-model="fDataES.total" ng-change="calcularTotales();" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;" /> 
			            </div>
		          	</div>	
		            <!-- <div class="form-inline mt-xs col-xs-12 text-right pr-n">
		              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL </label> 
		              <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataES.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
		            </div> -->
	          	</div>	
			</fieldset>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCompraServ.$invalid" tabindex="500"><i class="fa fa-save"> </i> GRABAR</button>
    <!-- <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(idmovimiento,1);" ng-disabled="!isRegisterSuccess" ng-show="modulo !='edicion'"><i class="fa fa-print"  tabindex="120"></i> [F4] IMPRIMIR</button> -->
    <button class="btn btn-warning" ng-click="cancel();" tabindex="300">Cerrar</button>
</div>