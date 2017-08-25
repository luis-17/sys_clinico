<div class="modal-header"> <!-- MODULO HOSPITAL -->
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formStocks">
    	<div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs text-bold" style="width: 80px;">EMPLEADO: </label>
			<span>{{fDataHistorial.personal}}</span>
		</div>
		<!-- <div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs text-bold" style="width:120px;">CARGO ACTUAL: </label>
			<span>{{fDataHistorial.cargo}}</span>
		</div> -->
	</form>
	<div class="panel-body scroll-pane mt-sm" style="height: 320px;">
		<div class="scroll-content mb-md" style=" border: 2px inset #ddd;padding: 10px">
			<div class="waterMarkEmptyData" ng-if="!metodos.listaHistorial.length">No se han registrado contratos para el empleado. </div>
			<ul class="mini-timeline" ng-if="metodos.listaHistorial.length">
				<li class="mini-timeline-lime" ng-repeat="row in metodos.listaHistorial">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content" ng-class="row.clase_contrato_actual">
							<!-- <span class="text-bold" style="color:#616161">{{row.usuario}}</span> dirIconoFormat ng-click="verDocumentoContrato(row.codigo);" --> 
							Nuevo <span class="text-bold text-info">CONTRATO</span> perteneciente a 
							<span class="text-bold text-info">{{row.empresa}}</span> desempeñandose como 
							<span class="text-bold text-info">{{row.cargo}}</span> con el salario de 
							<span class="text-bold text-info">{{row.sueldo}}</span> y la Condición Laboral <span class="text-bold text-info">{{row.condicion_laboral}}</span> 
							<span class="time"> <b>Duración: </b> {{row.fecha_ini_contrato_str}} - {{ row.fecha_fin_contrato_str }} </span>
							<a ng-if="!(row.archivo.hay_archivo)" href="" ng-click="subirDocumentoContrato(row.codigo);" style="text-decoration: underline;"> Subir Contrato <i class="ti ti-upload"></i> </a> 
							<a ng-if="row.archivo.hay_archivo" href="{{dirContratosEmpleados + row.archivo.documento}} " target="_blank" style="text-decoration: underline;"> 
								<img ng-src="{{dirIconoFormat + row.archivo.icono}}" title="Ver Contrato" style="width: 20px; margin-right: 10px;" /> Ver Contrato 
							</a> 
							<a ng-if="row.archivo.hay_archivo" href="" style="color: red; text-decoration: underline; padding: 0px 6px;" ng-click="quitarDocumentoContrato(row.codigo);">  X </a> 
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<!-- 
	{{ grid.appScope.dirImagesDocEmpleados + COL_FIELD.documento }}
	<div class="mini-timeline-default" style="position: fixed; padding: 5px 45px;bottom: 75px;">
		<div class="timeline-body ml-n">
			<div class="timeline-content">
				<label class="control-label mb-xs text-bold">PRECIO INICIAL: </label>
				<span>{{fDataHistorial.precio_inicial}}</span>
			</div>
		</div>
	</div> -->
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>