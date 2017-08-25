<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
    <form class="row mr-n ml-n" name="formGuiaRemision">
		<div class="col-md-6 col-sm-12">
			<fieldset class="row pr" >
				<legend class="col-md-12 pr-n pl-n pb-n mb-md lead"> 
					Datos generales 
	            </legend>
				<div class="form-group mb-xs col-md-2 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs"> RUC. </label> 
	                  <input id="ruc" type="text" class="form-control input-sm" ng-model="fDataGR.destinatario.ruc" 
	                    readonly="readonly" /> 
	            </div>
	            <div class="form-group mb-xs col-md-4 col-sm-6 pl-xs"> 
	                <label class="control-label mb-xs">  Razón Social </label> 
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.destinatario.razon_social" placeholder="Razón Social" readonly="readonly" /> 
	            </div>
	            
	            <div class="form-group mb-xs col-md-6 col-sm-6 pl-xs">
	                <label class="control-label mb-xs"> Domicilio </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.destinatario.domicilio" placeholder="Domicilio" readonly="readonly" /> 
	            </div>

	            <div class="form-group mb-xs col-md-6 col-sm-6 pl-xs">
	                <label class="control-label mb-xs"> Punto de Partida <small class="text-danger">(*)</small></label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.punto_partida" placeholder="Punto de partida"  tabindex="1" required maxlength="60"/>
	            </div>
	            <div class="form-group mb-xs col-md-6 col-sm-6 pl-xs">
	                <label class="control-label mb-xs"> Punto de Llegada <small class="text-danger">(*)</small></label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.punto_llegada" placeholder="Punto de llegada"  tabindex="2" required maxlength="60"/>
	            </div>

	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" >
	                <label class="control-label mb-xs"> Fecha de Guía <small class="text-danger">(*)</small></label>
	                <input type="text" class="form-control input-sm mask" ng-model="fDataGR.fecha_guia" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="3" required/>
	            </div>

	            <div class="form-group mb-xs col-md-3 col-sm-6 pl-xs" >
	                <label class="control-label mb-xs"> Costo Mínimo </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.costo_minimo" ng-pattern="/^[0-9]*$/" tabindex="4"/>
	            </div>
	            <div class="form-group pl-xs mb-xs col-md-3 col-sm-6">
		            <label class="control-label mb-xs"> Estado Traslado<small class="text-danger">(*)</small></label>
		            <select id='motivo_traslado' class="form-control input-sm" ng-model="fDataGR.estado" ng-options="item.id as item.descripcion for item in listaEstadoTraslado" tabindex="5" > </select> 
		        </div>
	            <div class="form-group pl-xs mb-xs col-md-3 col-sm-6">
		            <label class="control-label mb-xs"> Motivo Traslado<small class="text-danger">(*)</small></label>
		            <select id='motivo_traslado' class="form-control input-sm" ng-model="fDataGR.motivo_traslado" ng-options="item.id as item.descripcion for item in listaMotivoTraslado" tabindex="5" > </select> 
		        </div>
		        <div class="form-group mb-xs col-md-12 col-sm-12 pl-xs" ng-if="fDataGR.motivo_traslado == 13">
	                <label class="control-label mb-xs"> Otro Motivo del Traslado </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.motivo_otros" placeholder="Razón social o nombre de transportista"  tabindex="6"/>
	            </div> 

			</fieldset>
		</div>

		<div class="col-md-6 col-sm-12" >
			<fieldset class="row">
				<legend class="col-lg-12 pr-n pl-n pb-n mb-md lead"> Datos del Transporte 
					<div class="col-md-6 pull-right text-right pt-sm" ng-if="fDataGR.submodulo =='nuevo'"> 
						<small class="col-md-5 text-default block mb-xs p-n" style="font-size: 14px;line-height: 1;" > GUÍA DE REMISIÓN	</small> 
                        <select class="col-md-2 form-control input-sm" ng-model="fDataGR.serie" ng-change="cambiarSerie()" ng-options="item.id for item in listaNumeroSerie" tabindex="7" style="width: 19%;margin-top: -6px;margin-left: 10px;"> </select>
                        <small class="col-md-4 text-default block mb-xs p-n" style="font-size: 14px;line-height: 1;" ><strong>N° {{ fDataGR.numero_serie }}</strong>
                            <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarCodigoTicket();$event.preventDefault();"> 
                            	<i class="ti ti-reload "></i> </button>
                        </small>
                    </div>
                    <div class="col-md-6 pull-right text-right pt-sm" ng-if="fDataGR.submodulo =='editar'"> 
						<small class="text-default block mb-xs p-n" style="font-size: 14px;line-height: 1;" > 
						 	GUÍA DE REMISIÓN <strong> {{ fDataGR.guia_remision }} </strong></small>       
                    </div>
				</legend> 
	            <div class="form-group mb-xs col-md-6 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Marca de Vehículo </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.marca_vehiculo" placeholder="Marca de vehículo"  tabindex="8"/>
	            </div>
	            <div class="form-group mb-xs col-md-6 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> N° de Placa </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.placa_vehiculo" placeholder="Marca de vehículo"  tabindex="9"/>
	            </div>
	            <div class="form-group mb-xs col-md-6 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> N° de Const. de Inscripción </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.constancia_inscripcion" placeholder="N° de Constancia de Inscripción."  tabindex="10"/>
	            </div>
	            <div class="form-group mb-xs col-md-6 col-sm-12 pl-xs" >
	                <label class="control-label mb-xs"> N° de Licencia de conducir </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.licencia_conducir" placeholder="N° de licencia de conducir"  tabindex="11"/>
	            </div>
	            <div class="form-group mb-xs col-md-12 col-sm-12 pl-xs">
	                <label class="control-label mb-xs"> Razón Social o Apellidos y Nombres de Transportista </label>
	                <input type="text" class="form-control input-sm" ng-model="fDataGR.razon_social_nombre" placeholder="Razón social o nombre de transportista"  tabindex="12"/>
	            </div> 
    		</fieldset>
		</div>
		<div class="col-md-12 col-xs-12">
			<fieldset class="row pt" >
				<legend class="col-xs-10 pr-n pl-n pb-n  mb-md lead"> 
					Detalle	
					<!-- <button tooltip-placement="bottom" tooltip="Recargar Detalle" title="" type="button" class="btn btn-sm btn-info" ng-click="btnCargarDetalle()" ng-if="fDataGR.submodulo =='nuevo'"> <i class="ti ti-reload "></i> </button> -->
				</legend>
				<legend class="col-xs-2 pr-n pl-n pb-n  mb-md lead" style="text-align: right;"> 
					({{ fDataGR.guia }} / {{ fDataGR.cantidad_guias }})
				</legend>
	            <div class="form-group col-xs-12 m-n p-n">	             
	              <div ui-if="gridOptionsGR.data.length>0" ui-grid="gridOptionsGR" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid" style="overflow: hidden;" ng-style="getTableHeight();">
	              	<div class="waterMarkEmptyData" ng-show="!gridOptionsGR.data.length"> No se encontraron datos. </div>
	              </div>
	            </div>
			</fieldset>			
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formGuiaRemision.$invalid" tabindex="13" ng-if="fDataGR.submodulo =='nuevo'">
    	<i class="fa fa-save"> </i> [F2] Grabar</button>
    <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimirGuiaRemision();" ng-disabled="!isRegisterSuccess" ng-if="fDataGR.submodulo =='nuevo'" tabindex="14"> 
    	<i class="fa fa-print" ></i> [F4] IMPRIMIR</button>
	<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formGuiaRemision.$invalid" tabindex="13" ng-if="fDataGR.submodulo =='editar'"> Aceptar </button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="13">Cerrar</button>
</div>