<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
    <form class="row mr-n ml-n" name="formEgresoServ"> 
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
	                    ng-enter="obtenerDatosProveedor(); $event.preventDefault();" placeholder="RUC Nº" ng-change="limpiarCampos();" tabindex="100" ng-pattern="pRUC" ng-readonly="modulo == 'edicion'" focus-me /> 
	            </div>
	            <div class="form-group mb-xs col-md-9 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> Razón Social </label> 
	                <input type="text" class="form-control input-sm" ng-model="fDataES.proveedor.razon_social" placeholder="Razón Social" readonly="readonly" /> 
	            </div>	            
			</fieldset>
		</div>

		<div class="col-md-6 col-sm-12" >
			<fieldset class="row">
				<legend class="col-lg-12 pr-n pl-n pb-n mb-md lead"> Datos de Documento</legend>
				<div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Tipo de Documento </label>
		            <select ng-change="calcularTotales();" class="form-control input-sm" ng-model="fDataES.tipodocumento" ng-options="item as item.descripcion for item in metodos.arrTipoDocumentos" tabindex="110" > </select>
		        </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> N° de Documento <small class="text-danger">(*)</small> </label> 
	                <div class="input-group" style="width: 100%;">
		            	<input tabindex="120" style="width: 25%;margin-right: 6px;" placeholder="" class="form-control input-sm" type="number" ng-model="fDataES.serie_documento" required maxlength="3" minlength="3"/> 
		            	<input tabindex="130" style="width: 70%;" class="form-control input-sm" type="number" ng-model="fDataES.numero_documento" placeholder="Nº de Documento" required />  
		            </div> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Fecha de Emisión <small class="text-danger">(*)</small></label> 
	                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataES.fecha_emision" tabindex="140" required /> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Centro de Costo</label> 
	                <p class="help-block m-n" > {{fDataES.guia_remision}} </p>
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Operación <small class="text-danger">(*)</small> </label>
		            <p class="help-block m-n" > {{fDataES.operacion.descripcion}} </p>
		        </div>
		        <div class="form-group mb-xs col-md-4 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Sub-Operación <small class="text-danger">(*)</small> </label>
		            <select ng-disabled="fDataES.subOperacionDisabled" required class="form-control input-sm" ng-model="fDataES.suboperacion" ng-options="item as item.descripcion for item in metodos.listaSubOperacionesForm" tabindex="170" > </select>
		        </div>
    		</fieldset>
		</div>
		<div class="col-md-12 col-xs-12">
			<fieldset class="row" >
				<legend class="col-lg-12 pr-n pl-n pb-n  mb-md lead"> Detalle </legend> 
				<div class="form-group mb-xs col-md-1 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Cuenta </label>
		            <input id="temporalDescripcion" type="text" class="form-control input-sm" ng-model="fDataES.temporal.cuenta" ng-disabled="fDataES.temporal.cuentaDisabled" tabindex="180" placeholder="N° Cuenta" /> 
		        </div>
				<div class="form-group mb-xs col-md-3 col-sm-6 pl-n"> 
		            <label class="control-label mb-xs"> Glosa </label>
		            <input id="temporalDescripcion" type="text" class="form-control input-sm" ng-model="fDataES.temporal.descripcion" tabindex="190" placeholder="Glosa" /> 
		        </div>     
            	<div class="form-group mb-xs col-md-2 col-sm-6 pl-n">
	                <label class="control-label mb-xs"> Importe <small class="text-gray">( SIN {{ fDataES.tipodocumento.nombre_impuesto }} )</small> </label> 
	                <input id="temporalImporte" type="text" class="form-control input-sm" ng-model="fDataES.temporal.importe" placeholder="Importe(SIN {{ fDataES.tipodocumento.nombre_impuesto }})" tabindex="200" /> 
	            </div>
	            <div class="form-group mb-sm mt-lg col-md-2 col-sm-12 pl-n"> 
		        	 <!--<a href="" class="btn btn-info-alt btn-sm" ng-click="consultarEMA(); $event.preventDefault();" tabindex="230" ng-show="mostrarEMAReporte">OBTENER E.M.A</a> 
		        	<a href="" class="btn btn-info-alt btn-sm" ng-click="consultarReporteTercero(); $event.preventDefault();" uib-tooltip="CONSULTAR REPORTE" tabindex="210" ng-show="mostrarEMAReporte"> <i class="fa fa-eye"></i> </a> -->
		        	<a href="" class="btn btn-info-alt btn-sm ml" ng-click="agregarItem(); $event.preventDefault();" tabindex="210"> <i class="fa fa-plus"></i> AGREGAR </a> 
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
	                <textarea class="form-control input-sm" ng-model="fDataES.motivo_movimiento" tabindex="220"></textarea> 
		        </div>
		        <div class="col-md-6 col-sm-6 pr-n">
		        	<div class="col-md-4 col-sm-6 pr-n form-group" > 
						<label class="checkbox block" ng-show="fDataES.tipodocumento.nombre_impuesto != 'NOHAY'">
							<input type="checkbox" ng-model="fDataES.inafecto" ng-click="calcularTotales();" ng-checked="fDataES.inafecto"> ¿Inafecto?
						</label>						
			        </div>
			        <div class="col-md-8 col-sm-6 pr-n">
			            <div class="form-inline mt-xs col-xs-12 text-right pr-n" ng-if="fDataES.tipodocumento.porcentaje != '0'">
			              <label class="control-label mr-xs text-gray"> SUBTOTAL </label> 
			              <input id="" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataES.subtotal" placeholder="SUBTOTAL" style="width: 200px;" /> 
			            </div>
			            <div class="form-inline mt-xs col-xs-12 text-right pr-n" ng-if="fDataES.tipodocumento.porcentaje != '0'"> 
			              <label class="control-label mr-xs text-gray"> {{ fDataES.tipodocumento.nombre_impuesto }} ({{ fDataES.tipodocumento.porcentaje }}%) </label> 
			              <input id="" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataES.impuesto" placeholder="{{ fDataES.tipodocumento.nombre_impuesto }}" style="width: 200px;" /> 
			            </div>
			            <div class="form-inline mt-xs col-xs-12 text-right pr-n">
			              <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL <small> {{ fDataES.simbolo_monetario }} </small> </label> 
			              <input id="" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fDataES.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
			            </div>
		          	</div>	
	          	</div>	
			</fieldset>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formEgresoServ.$invalid" tabindex="230"><i class="fa fa-save"> </i> GRABAR</button>
    <button class="btn btn-warning" ng-click="cancel();" tabindex="240">Cerrar</button>
</div>