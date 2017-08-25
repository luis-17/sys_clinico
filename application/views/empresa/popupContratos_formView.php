<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body">
    <form class="row" novalidate > 	    
    	<div class="col-md-5 col-sm-7" >
			<h5 class="mt-n mb-sm text-center text-primary" ng-show="!contratos.editarContratoBool"><i class="fa fa-file-text-o"></i> AGREGAR CONTRATO</h5> 
			<h5 class="mt-n mb-sm text-center" ng-show="contratos.editarContratoBool">EDICIÓN DEL CONTRATO</h5> 
			<div class="row">
				<div ng-class="contratos.classEditPanel"> 
					<div ng-if="contratos.editarContratoBool" class="form-group mb-md col-sm-12" style="margin-top:5px;">
						<span class="label label-info">{{contratos.fData.codigo}}</span>
        			</div>

					<div class="form-group mb-md col-sm-6">
						<label class="control-label mb-xs text-bold"> EMPRESA ADMINISTRADORA </label>
						<p class="m-n text-bold text-primary"><i class="fa fa-medkit"></i> {{contratos.empresaadmin}} </p>
        			</div>

        			<div class="form-group mb-md col-sm-6">
						<label class="control-label mb-xs text-bold"> EMPRESA </label>
						<p class="m-n text-bold text-primary"><i class="fa fa-institution"></i> {{contratos.empresa}} </p>
        			</div>

        			<div class="form-group col-sm-6 mb-md">
						<label class="control-label mb-xs text-bold"> CODIGO DE CONTRATO : <small class="text text-danger">(*)</small></label>
						<input type="text" class="form-control input-sm" ng-model="contratos.fData.codigo" required /> 
        			</div>  

					<div class="clearfix"></div>
					<div class="form-group mb-md col-sm-6">
						<label class="control-label mb-xs"> Fecha Inicio Contrato <small class="text text-danger">(*)</small> </label>
						<input type="text" class="form-control input-sm" ng-model="contratos.fData.fecha_inicio" data-inputmask="'alias': 'dd-mm-yyyy'"  required /> 
					</div>
					<div class="form-group mb-md col-sm-6">
						<label class="control-label mb-xs"> Fecha Fin Contrato <small class="text text-danger">(*)</small> </label>
						<input type="text" class="form-control input-sm" ng-model="contratos.fData.fecha_fin" data-inputmask="'alias': 'dd-mm-yyyy'" required /> 
					</div>

					<div class="form-group mb-md col-sm-6"> 
						<div class="input-group checkbox-inline" style="font-size: 15px;"> 
							<label> <input type="checkbox" ng-model="contratos.fData.contrato_actual" ng-true-value="1" ng-false-value="2" /> 
								<small style="display: block; "> ¿Es Contrato Vigente? </small> 
							</label>
						</div>
					</div>
					<div class="form-group mb-md col-sm-6"> 
						<div class="input-group checkbox-inline" style="font-size: 15px;"> 
							<label> <input type="checkbox" ng-model="contratos.fData.contrato_formal" ng-true-value="1" ng-false-value="2" /> 
								<small style="display: block; "> ¿Es Contrato Formal? </small> 
							</label>
						</div>
					</div>	
					<div class="form-group mb-md col-sm-12"> 
						<label class="control-label mb-n"> Condiciones: </label>		
						<textarea class="form-control col-md-12" ng-model="contratos.fData.condiciones" placeholder="Agregue Condiciones de la adenda"></textarea>
					</div>									
				
					<div class="col-md-12" style="text-align:center;"> 
						<button ng-if="!contratos.editarContratoBool" type="button" class="btn btn-success" style="width: 60%;" ng-click="agregarContrato();"> AGREGAR CONTRATO >>> </button>
						<button ng-if="contratos.editarContratoBool" type="button" class="btn btn-warning" style="width: 49%;" ng-click="salirActualizarContrato();"> CANCELAR </button>
						<button ng-if="contratos.editarContratoBool" type="button" class="btn btn-success" style="width: 49%;" ng-click="actualizarContrato();"> ACTUALIZAR CONTRATO >>> </button>
						
					</div>
				</div>
			</div>
		</div> 
		<div class="col-md-7 col-sm-5"> 
			<div class="panel-body scroll-pane mt-sm" style="height: 320px;">
				<div class="scroll-content mb-md" style=" border: 2px inset #ddd;padding: 10px">
					<div class="waterMarkEmptyData" ng-if="!contratos.listaHistorial.length">No se han registrado contratos para la empresa. </div>
					<ul class="mini-timeline" ng-if="contratos.listaHistorial.length">
						<li class="mini-timeline-lime" ng-repeat="row in contratos.listaHistorial">
							<div class="timeline-icon"></div>
							<div class="timeline-body">
								<div class="timeline-content" ng-class="row.clase_contrato_actual">									
									<p class="m-n"><span class="badge badge-info">{{row.codigo}}</span></p>
									Nuevo <span class="text-bold text-info">CONTRATO</span> perteneciente a la EMA
									<span class="text-bold text-info">{{row.empresa}}</span> asociada a la empresa administradora <span class="text-bold text-info">{{contratos.empresaadmin}}</span> 
									<span class="time"> <b>Duración: </b> {{row.fecha_inicio_str}} - {{ row.fecha_fin_str }} </span>
									<span class="time"> <b>Fecha de registro contrato: </b> {{row.fecha_registro_str}} </span>
									<a ng-if="!(row.archivo.hay_archivo)" href="" ng-click="subirContrato(row);" style="text-decoration: underline;"> Subir Contrato <i class="ti ti-upload"></i> </a> 
									<a ng-if="row.archivo.hay_archivo" href="{{dirContratosEma + row.archivo.documento}} " target="_blank" style="text-decoration: underline;"> 
										<img ng-src="{{dirIconoFormat + row.archivo.icono}}" title="Ver Contrato" style="width: 20px; margin-right: 10px;" /> Ver Contrato 
									</a> 
									<a ng-if="row.archivo.hay_archivo" href="" style="color: red; text-decoration: underline; padding: 0px 6px;" ng-click="quitarDocumento(row.idcontrato);">  X </a> 
									<a href="" style="color: #ecb100; text-decoration: underline; padding: 0px 6px;" ng-click="editarContrato(row);"> Editar </a> 
									<a href="" style="color: red; text-decoration: underline; padding: 0px 6px;" ng-click="anularContrato(row);"> Anular </a>
									<a href="" style="color: #8BC34A; text-decoration: underline; padding: 0px 6px;" ng-click="agregarAdenda(row);"><i class="fa fa-edit"></i> Adendas </a>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>