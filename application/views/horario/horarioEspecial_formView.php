<div class="modal-content">
	<div class="modal-header">
		<h4 class="modal-title"> {{ titleForm }}  </h4>
	</div>
	<div class="modal-body">
	    <form class="row" name="formHorarioEspecial"> 
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
						<label class="control-label mb-xs"> DIA/HORARIO </label> 
						<!-- <input type="text" class="form-control input-sm mask" ng-model="fData.temporal.horario" data-inputmask="'alias': 'dd-mm-yyyy'" /> -->
						<uib-datepicker class="date-table" ng-model='fData.activeDate' multi-select='fData.temporal.arrFechas' select-range='false'></uib-datepicker>
						<!-- <div ng-repeat='d in fData.selectedDates | orderBy'>
			                {{d | date : 'fullDate'}}
			                <button class='btn btn-xs btn-warning' style='margin:5px'>Remove</button>
			            </div> -->
					</div>
					<div class="col-md-9" id="contNextControl"> 
						<div class="row">
							<div class="col-sm-4 form-group mb-md">
								<label class="control-label mb-xs"> ASISTENCIA </label> 
								<select ng-model="fData.temporal.asistencia" ng-options="item as item.descripcion for item in listaTipoAsistencia" class="form-control input-sm" > </select>
							</div> 
							<div class="col-md-4 form-group mb-md">
								<label class="control-label mb-xs"> MOTIVO </label> 
								<select ng-model="fData.temporal.motivo" ng-options="item as item.descripcion for item in listaMotivoHE" class="form-control input-sm" ng-change="listarSubMotivos();" > </select>
							</div>
							<div class="col-md-4 form-group mb-md">
								<label class="control-label mb-xs"> SUB-MOTIVO </label> 
								<select ng-model="fData.temporal.submotivo" ng-options="item as item.descripcion for item in listaSubMotivoHE" class="form-control input-sm" > </select>
							</div>
						</div> 
						<div class="row" ng-show="fData.temporal.asistencia.id == 'SA'"> 
							<div class="col-md-3 form-group mb-md">
								<label class="control-label mb-xs"> ENTRADA </label> 
								<table class="p-n">
								    <tbody>
								    	<tr>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.hora_entrada" min="0" max="23" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 23" />
								    		</td>
								    		<td>:</td>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_entrada" min="0" max="59" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 59" />
								    		</td>
								    	</tr>
								    </tbody>
								</table>
							</div>

							<div class="col-md-3 form-group mb-md">
								<label class="control-label mb-xs"> DESDE ENTRADA </label>
								<table class="p-n">
								    <tbody>
								    	<tr>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_desde_entrada" min="0" max="23" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 23" ng-disabled="!fData.temporal.entradaDisabled" />
								    		</td>
								    		<td>:</td>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_desde_entrada" min="0" max="59" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 59" ng-disabled="!fData.temporal.entradaDisabled" />
								    		</td>
								    	</tr>
								    </tbody>
								</table>
							</div>
							<div class="col-md-3 form-group mb-md pl-n">
								<label class="control-label mb-xs"> HASTA ENTRADA </label>
								<table class="p-n inline">
								    <tbody>
								    	<tr>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_hasta_entrada" min="0" max="23" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 23" ng-disabled="!fData.temporal.entradaDisabled" />
								    		</td>
								    		<td>:</td>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_hasta_entrada" min="0" max="59" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 59" ng-disabled="!fData.temporal.entradaDisabled" />
								    		</td>
								    	</tr>
								    </tbody>
								</table>
								<input type="checkbox" ng-model="fData.temporal.entradaDisabled" class="ml-sm" />
							</div>
							<div class="col-md-3 form-group mb-md">
								<label class="control-label mb-xs"> TOLERANCIA <small class="help-inline m-n"> en minutos </small> </label> 
								<input ng-change="nextControlForm(2);" style="width: 136px;" type="text" ng-model="fData.temporal.minuto_tolerancia" class="form-control input-sm text-center" ng-maxlength="2" placeholder="Solo minutos" />
							</div> 
						</div>
						<div class="row" ng-show="fData.temporal.asistencia.id == 'SA'"> 
							
							<div class="col-md-3 form-group mb-xs">
								<label class="control-label mb-xs"> SALIDA </label>
								<table class="p-n">
								    <tbody>
								    	<tr>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.hora_salida" min="0" max="23" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 23" />
								    		</td>
								    		<td>:</td>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="generateHorasTrab();nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_salida" min="0" max="59" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 59" />
								    		</td>
								    	</tr>
								    </tbody>
								</table>
							</div>
							<div class="col-md-3 form-group mb-xs">
								<label class="control-label mb-xs"> DESDE SALIDA </label>
								<table class="p-n">
								    <tbody>
								    	<tr>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_desde_salida" min="0" max="23" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 23" ng-disabled="!fData.temporal.salidaDisabled" />
								    		</td>
								    		<td>:</td>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_desde_salida" min="0" max="59" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 59" ng-disabled="!fData.temporal.salidaDisabled" />
								    		</td>
								    	</tr>
								    </tbody>
								</table>
							</div>
							<div class="col-md-3 form-group mb-xs pl-n">
								<label class="control-label mb-xs"> HASTA SALIDA </label>
								<table class="p-n inline">
								    <tbody>
								    	<tr>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.hora_hasta_salida" min="0" max="23" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 23" ng-disabled="!fData.temporal.salidaDisabled" />
								    		</td>
								    		<td>:</td>
								    		<td style="width:60px;" class="form-group">
								    			<input ng-change="nextControlForm(2);" type="number" ng-model="fData.temporal.minuto_hasta_salida" min="0" max="59" class="form-control input-sm text-center" ng-minlength="2" ng-maxlength="2" placeholder="0 a 59" ng-disabled="!fData.temporal.salidaDisabled" />
								    		</td>
								    	</tr>
								    </tbody>
								</table>
								<input type="checkbox" ng-model="fData.temporal.salidaDisabled" class="ml-sm" />
							</div>
							
							<div class="col-md-3 form-group mb-xs">
								<label class="control-label mb-xs"> HORAS TRAB. </label>
								<input style="font-size: 16px; font-weight: bold; text-align: center;" type="text" ng-model="fData.temporal.horas_trabajadas" placeholder="Horas Trabajadas" class="form-control input-sm" ng-disabled="true" />
							</div>
							
						</div>	
						<div class="row"> 
							<div class="col-md-12 form-group mb-md">
								<button type="button" class="btn btn-info m-n" style="width: 100%; margin-top: 15px;" ng-click="agregarHorarioItem(); $event.preventDefault();" > 
									<i class="fa fa-plus"></i> AGREGAR AL HORARIO 
								</button> 
							</div>
						</div> 
					</div>
				</div> 
			</div>
			<div class="col-xs-12">
				<div ui-grid="gridOptionsHorarioAdd" ui-grid-auto-resize ui-grid-resize-columns ui-grid-pagination class="grid table-responsive fs-mini-grid scroll-x-none" style=" overflow-x: hidden;">
					<div class="waterMarkEmptyData" ng-show="!gridOptionsHorarioAdd.data.length"> Aún no se ha agregado horarios </div>
				</div>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<button class="btn btn-default pull-left" ng-click="cancel(); goToUrl('/horario-general'); $event.preventDefault();"> <i class="ti ti-timer"></i> HORARIO GENERAL</button>
	    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formHorarioEspecial.$invalid">ACEPTAR</button>
	    <button class="btn btn-warning" ng-click="cancel();">SALIR</button>
	</div>
</div>