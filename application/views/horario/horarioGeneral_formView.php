<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formHorarioGeneral"> 
    	<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n">PERSONAL: </label>
			<p class="help-block mt-xs"> {{ mySelectionGridEM[0].personal }} </p>
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n">CARGO: </label>
			<p class="help-block mt-xs"> {{ mySelectionGridEM[0].cargo }} </p>
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n">EMPRESA: </label>
			<p class="help-block mt-xs"> {{ mySelectionGridEM[0].empresa }} </p>
		</div>
		<div class="form-group col-md-2 mb-xs text-center" style="margin-top: -20px;">
			<img class="mt-xs" style="margin: auto; height: 74px;" ng-src="{{ dirImages + 'dinamic/empleado/' + mySelectionGridEM[0].nombre_foto }}" />
		</div>
		<div class="col-md-12 pt" style="border-top: 1px solid #e5e5e5;">
			<div class="row "> 
				<div class="col-md-3 form-group mb-md">
					<label class="control-label mb-xs"> HORARIO </label> 
					<select style="height: 137px;" multiple id="temporalHorario" ng-model="fData.temporal.horario" class="form-control input-sm" ng-options="item as item.descripcion for item in listaHorarios">  </select>
				</div>
				<div class="col-md-9 form-group mb-md" id="contNextControl"> 
					<div class="row "> 
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> DESDE ENTRADA </label>
							<table class="p-n">
							    <tbody>
							    	<tr>
							    		<td style="width:60px;" class="form-group"> 
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_desde_entrada" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="23" placeholder="0 a 23" />
							    		</td>
							    		<td>:</td>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_desde_entrada" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="59" placeholder="0 a 59" />
							    		</td>
							    	</tr>
							    </tbody>
							</table>
						</div>
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> ENTRADA </label> 
							<table class="p-n">
							    <tbody>
							    	<tr>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.hora_entrada" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="23" placeholder="0 a 23" />
							    		</td>
							    		<td>:</td>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_entrada" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="59" placeholder="0 a 59" />
							    		</td>
							    	</tr>
							    </tbody>
							</table>
						</div>
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> HASTA ENTRADA </label>
							<table class="p-n">
							    <tbody>
							    	<tr>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_cierre_entrada" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="23" placeholder="0 a 23" />
							    		</td>
							    		<td>:</td>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_cierre_entrada" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="59" placeholder="0 a 59" />
							    		</td>
							    	</tr>
							    </tbody>
							</table>
						</div>
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> TOLERANCIA <!-- <small class="help-inline m-n"> en minutos </small> --> </label> 
							<input ng-change="nextControlForm(2);" style="width: 100%;" type="text" ng-model="fData.temporal.minuto_tolerancia" class="form-control input-sm text-center"ng-minlength="2" ng-maxlength="2" placeholder="Solo minutos" />
						</div>
					</div>
					<div class="row "> 
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> DESDE SALIDA </label>
							<table class="p-n">
							    <tbody>
							    	<tr>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_cierre_salida" class="form-control input-sm text-center"ng-minlength="2" ng-maxlength="2"  min="0" max="23" placeholder="0 a 23" />
							    		</td>
							    		<td>:</td>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_cierre_salida" class="form-control input-sm text-center"ng-minlength="2" ng-maxlength="2"  min="0" max="59" placeholder="0 a 59" />
							    		</td>
							    	</tr>
							    </tbody>
							</table>
						</div>
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> SALIDA </label>
							<table class="p-n">
							    <tbody>
							    	<tr>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.hora_salida" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="23" placeholder="0 a 23" />
							    		</td>
							    		<td>:</td>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_salida" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="59" placeholder="0 a 59" />
							    		</td>
							    	</tr>
							    </tbody>
							</table>
						</div>
						<div class="col-md-3 form-group mb-md">
							<label class="control-label mb-xs"> HASTA SALIDA </label>
							<table class="p-n">
							    <tbody>
							    	<tr>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_hasta_salida" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="23" placeholder="0 a 23" />
							    		</td>
							    		<td>:</td>
							    		<td style="width:60px;" class="form-group">
							    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_hasta_salida" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2"  min="0" max="59" placeholder="0 a 59" />
							    		</td>
							    	</tr>
							    </tbody>
							</table>
						</div>
						<div class="col-md-3 form-group m-n"> {{ fData.temporal.horas_trabajadas }}
							<label class="control-label mb-xs"> HORAS TRAB. </label>
							<input style="font-size: 16px; font-weight: bold; text-align: center;" type="text" ng-model="fData.temporal.horas_trabajadas" placeholder="Horas Trabajadas" class="form-control input-sm" ng-disabled="true" />
						</div>
						<div class="col-xs-12 form-group m-n"> 
							<button tooltip="AGREGAR AL HORARIO" type="button" class="btn btn-info full-block m-n" style="margin-top: 15px;width: 100%;" ng-click="agregarHorarioItem(); $event.preventDefault();" > <i class="fa fa-plus"></i> AGREGAR AL HORARIO </button> 
									<!-- <button tooltip="AGREGAR TODOS"  type="button" class="btn btn-info" style="margin-top: 15px;" ng-click="markAllCheckBox(); $event.preventDefault();" > <i class="fa fa-plus"></i> <i class="fa fa-plus"></i>  </button>  -->
						</div>
					</div>
				</div>
				
			</div>
		</div>
		<div class="col-xs-12">
			<div ui-grid="gridOptionsHorarioAdd" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive fs-mini-grid">
				<div class="waterMarkEmptyData" ng-show="!gridOptionsHorarioAdd.data.length"> Aún no se ha agregado horarios </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button class="btn btn-default pull-left" ng-click="cancel(); goToUrl('/horario-especial'); $event.preventDefault();"> <i class="fa fa-calendar-o"></i> HORARIO ESPECIAL</button>
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formHorarioGeneral.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>