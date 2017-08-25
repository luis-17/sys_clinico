<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
    <form class="row" name="formConceptos"> 
    	<div class="col-md-12 mb-sm">
    		<div class="row">
    			<div class="col-xs-3">
					<label class="control-label mb-xs"> EMPRESA: </label> 
            		<p class="text-info m-n" > {{fData.planilla.descripcion_empresa}} </p>
				</div>
				<div class="col-xs-4 p-n">
					<label class="control-label mb-xs"> PLANILLA: </label> 
            		<p class="text-info m-n" > {{fData.planilla.descripcion}} </p>
				</div>
				<div class="col-xs-3 p-n">
					<label class="control-label mb-xs"> EMPLEADO: </label> 
            		<p class="text-info m-n" > {{fData.empleado.empleado}} </p>
				</div>
				<div class="col-xs-2">
					<label class="control-label mb-xs"> REGIMEN PENSIONARIO: </label> 
            		<p class="text-info m-n" ng-if="fData.empleado.reg_pensionario == 'ONP'" > {{fData.empleado.reg_pensionario}} </p>
            		<p class="text-info m-n" ng-if="fData.empleado.reg_pensionario == 'AFP'"> {{fData.empleado.reg_pensionario}} - {{fData.empleado.descripcion_afp}} - {{fData.empleado.tipo_comision}} </p>
				</div>
    		</div>    			
    	</div>
    	<div class="col-md-12 mb-sm pt-n">
    		<div class="row">
				<div class="col-xs-3 pl-n mt-xs">
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> REM. COMPUTABLE: <small class="text-danger">(*)</small></label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.sueldo_base" ng-change="actualizaConceptos('sueldo_base');" required ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> REM. BÁSICA : </label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.sueldo_basico" disabled/>
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> REM. DADA:</label>
							<div class="col-xs-3 p-n"> 
			    				<input type="text" class="form-control input-sm" ng-model="fData.remuneracion_dada" ng-change="actualizaConceptos('horas35');" ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>
			    			</div>
						</div>
					</div>					
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> DIAS TRABAJADOS:</label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.dias_trabajados" ng-change="actualizaConceptos('dias_trabajados');" ng-pattern="/^[1-3][0-9]$/"/>
			    			</div>
						</div>
					</div>
				</div>
				<div class="col-xs-3 pl-n mt-xs">
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> HORAS DIARIAS:</label>
							<div class="col-xs-3 p-n"> 
			    				<input type="text" class="form-control input-sm" ng-model="fData.horas_diarias" ng-change="actualizaConceptos('sueldo_base');" ng-pattern="/^[0-9]$/"/>
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> COSTO HORAS TRABAJADA:</label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.costo_hora_trabajada" disabled/>
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> FALTAS: (<span class="text-red text-bold">{{fData.faltas_label}}</span> días)</label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.faltas" ng-change="actualizaConceptos('faltas');" ng-pattern="/^[0-9]{1,2}$/"/>
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> TARDANZAS: (<span class="text-red text-bold">{{fData.tardanzas_label}}</span> minutos)</label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.tardanzas" ng-change="actualizaConceptos('tardanzas');"/>
			    			</div>
						</div>
					</div>
				</div>

				<div class="col-xs-3 pl-n mt-xs ">
					
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0105')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> H.EXTRA. 2 PRIM. HORAS:</label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.horas_extras25" ng-change="actualizaConceptos('horas25');" ng-pattern="/^[0-9]{1,2}$/"/>
			    			</div>
						</div>
					</div>			
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0106')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> H.EXTRA. MAS DE 2 HORAS:</label>
							<div class="col-xs-3 p-n"> 
			    				<input type="text" class="form-control input-sm" ng-model="fData.horas_extras35" ng-change="actualizaConceptos('horas35');" ng-pattern="/^[0-9]{1,2}$/"/>
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0909')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> MOVILIDAD: </label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.movilidad" ng-change="actualizaConceptos('movilidad');" ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>
			    			</div>
						</div>
					</div>		
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0914')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> REFRIGERIO: </label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.refrigerio" ng-change="actualizaConceptos('refrigerio');" ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>
			    			</div>
						</div>
					</div>		

									
				</div>
				<div class="col-xs-3 pl-n mt-n">
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0917')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> CONDICION DE TRABAJO : </label> 
							<div class="col-xs-3 p-n">
			    				<input type="text" class="form-control input-sm" ng-model="fData.condicion_trabajo" ng-change="actualizaConceptos('condicion_trabajo');" ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>
			    			</div>
						</div>
					</div>					
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0605')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> REM. ACUM. ANTERIORES:</label>
							<div class="col-xs-3 p-n"> 
			    				<input type="text" class="form-control input-sm" ng-model="fData.remuneracion_acum" ng-change="actualizaRemuneracion();" ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>
							</div>
						</div>
					</div>					
					<div class="col-xs-12 mb-xs" ng-if="obtenerEstadoConcepto('0605')==1">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> RETEN. ACUM. ANTERIORES:</label>
							<div class="col-xs-3 p-n"> 
			    				<input type="text" class="form-control input-sm" ng-model="fData.retencion_acum" ng-change="actualizaRemuneracion();" ng-pattern="/^[0-9]+(\.[0-9][0-9]?)?$/"/>							
			    			</div>
						</div>
					</div>
					<div class="col-xs-12 mb-xs">
						<div class="row">
							<label class="col-xs-9 control-label mb-xs"> DIAS VACACIONES:</label>
							<div class="col-xs-3 p-n"> 
			    				<input type="text" class="form-control input-sm" ng-model="fData.dias_vacaciones" disabled />							
			    			</div>
						</div>
					</div>
				</div>
							
    		</div>    			
    	</div>
    	<div class="col-md-12 mt-sm mb-sm">
	    	<div class="panel-heading">       
	            <ul class="nav nav-tabs">
	              	<li ng-repeat="(index,tipoConcepto) in fData.planilla.conceptos" ng-class="{active: isActiveTab(tipoConcepto.descripcion_tipo)}">
	              		<a ng-click="onClickTab(tipoConcepto.descripcion_tipo,index)" style="padding: 4px 12px;" href="" data-toggle="tab" data-target="#{{index}}">
	              			<h4 class="m-n"> {{tipoConcepto.descripcion_tipo}} </h4>
	              		</a>
	              	</li>
	            </ul>
	        </div>
	        <div class="tab-pane col-xs-6" ng-if="indextipoCpt == 1" style="min-height: 284px;">
	        	<div ng-repeat="(indexCat, categoria) in fData.planilla.conceptos[indextipoCpt].categorias" ng-if="indexCat != 9 && indexCat != 3">
	        		<h5 class="mb-n" >{{categoria.descripcion_categoria}}</h5>
					<div class="row">
						<div class="col-xs-12 mt-xs" ng-repeat="(indexConcepto, concepto) in categoria.conceptos">
							<div ng-if="concepto.codigo_plame == '0118' || concepto.codigo_plame == '0114' || concepto.codigo_plame == '0406' || concepto.codigo_plame == '0407'" 
								class="col-xs-1 mb-xs p-n list-conceptos" style=" margin-top: -3px;" tooltip-placement="left" ng-click="changeEstadoConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame)" >
								<switch name="enabled" ng-model="concepto.boolBloqueo" class="success" disabled="true" ></switch>
							</div>
							<div ng-if="!(concepto.codigo_plame == '0118' || concepto.codigo_plame == '0114' || concepto.codigo_plame == '0406' || concepto.codigo_plame == '0407')"  
								class="col-xs-1 mb-xs p-n list-conceptos" style=" margin-top: -3px;" tooltip-placement="left" ng-click="changeEstadoConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame)" >
								<switch name="enabled" ng-model="concepto.boolBloqueo" class="success"></switch>
							</div>
							<label class="col-xs-9 control-label mb-xs" style="font-size: 13px;">
								{{concepto.descripcion}}:
							</label>
							<div class="col-xs-2 p-n">
	            				<input type="text" class="form-control input-sm" ng-model="concepto.valor_empleado"
	            					ng-change="cambioConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame);" disabled />
	            			</div>
						</div>
					</div>		
	        	</div>
	        	
	        </div>
	        <div class="tab-pane col-xs-6" ng-if="indextipoCpt == 1" style="min-height: 284px;">
	        	<div ng-repeat="(indexCat, categoria) in fData.planilla.conceptos[indextipoCpt].categorias" ng-if="indexCat == 9 || indexCat == 3">
	        		<h5 class="mb-n" >{{categoria.descripcion_categoria}}</h5>
					<div class="row">
						<div class="col-xs-12 mt-xs" ng-repeat="(indexConcepto, concepto) in categoria.conceptos">
							<div class="col-xs-1 mb-xs p-n list-conceptos" style=" margin-top: -3px;" tooltip-placement="left"  
							ng-if="concepto.codigo_plame == '0312' || concepto.codigo_plame == '0313'">
								<switch name="enabled" ng-model="concepto.boolBloqueo" class="success" disabled="true"></switch>
							</div>

							<div class="col-xs-1 mb-xs p-n list-conceptos" style=" margin-top: -3px;" tooltip-placement="left" ng-click="changeEstadoConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame)" 
							ng-if="!(concepto.codigo_plame == '0312' || concepto.codigo_plame == '0313')">
								<switch  name="enabled" ng-model="concepto.boolBloqueo" class="success"	></switch>
							</div>
							<label class="col-xs-9 control-label mb-xs" style="font-size: 13px;">
								{{concepto.descripcion}}:
							</label>
							<div class="col-xs-2 p-n">
	            				<input type="text" class="form-control input-sm" ng-model="concepto.valor_empleado"
	            					ng-change="cambioConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame);" ng-disabled="concepto.codigo_plame != '0121'" />
	            			</div>
						</div>
					</div>	
	        	</div>
	        </div>
			<div class="tab-pane col-xs-6" ng-repeat="(indexCat, categoria) in fData.planilla.conceptos[indextipoCpt].categorias" ng-if="indextipoCpt != 1" style="min-height: 284px;">
				<h5 class="mb-n" >{{categoria.descripcion_categoria}}</h5>
				<div class="row">
					<div class="col-xs-12 mt-xs" ng-repeat="(indexConcepto, concepto) in categoria.conceptos">
						<div class="col-xs-1 mb-xs p-n list-conceptos" style=" margin-top: -3px;" tooltip-placement="left"  
						ng-if="concepto.codigo_plame == '0601' || concepto.codigo_plame == '0606' || concepto.codigo_plame == '0607' || concepto.codigo_plame == '0608'">
							<switch name="enabled" ng-model="concepto.boolBloqueo" class="success" disabled="true"></switch>
						</div>

						<div class="col-xs-1 mb-xs p-n list-conceptos" style=" margin-top: -3px;" tooltip-placement="left" ng-click="changeEstadoConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame)" 
						ng-if="!(concepto.codigo_plame == '0601' || concepto.codigo_plame == '0606' || concepto.codigo_plame == '0607' || concepto.codigo_plame == '0608')">
							<switch  name="enabled" ng-model="concepto.boolBloqueo" class="success"	></switch>
						</div>

						<label class="col-xs-9 control-label mb-xs" style="font-size: 13px;">
							{{concepto.descripcion}}:
						</label>
						<div class="col-xs-2 p-n">
            				<input type="text" class="form-control input-sm" ng-model="concepto.valor_empleado"
            					ng-change="cambioConcepto(indextipoCpt, indexCat, indexConcepto, concepto.codigo_plame);" disabled/>
            			</div>
					</div>
				</div>					
			</div>
		</div>

		<div class="col-md-12 mt-sm pt-xs" style="border-top: 1px solid #e5e5e5;">
    		<div class="row">
				<div class="col-xs-3">
					<label class="control-label mb-xs text-success"><strong> TOTAL REMUNERACIÓN:</strong></label> 
	    			<input type="text" class="form-control input-md" style="font-size: 15px;font-weight: bold;"  ng-model="fData.total_remuneracion" disabled />
				</div>							

				<div class="col-xs-3">
					<label class="control-label mb-xs text-danger"><strong> TOTAL DESCUENTOS:</strong></label> 
	    			<input type="text" class="form-control input-md" style="font-size: 15px;font-weight: bold;"  ng-model="fData.total_descuentos" disabled />
				</div>							

				<div class="col-xs-3">
					<label class="control-label mb-xs"><strong> TOTAL APORTACIONES: </strong></label> 
	    			<input type="text" class="form-control input-md" style="font-size: 15px;font-weight: bold;" ng-model="fData.total_aportes" disabled />
				</div>								
				<div class="col-xs-3">
					<label class="control-label mb-xs text-info"><strong> SUELDO NETO A PAGAR:</strong></label> 
	    			<input type="text" class="form-control input-md" style="font-size: 15px;font-weight: bold;"  ng-model="fData.total_neto" disabled />
				</div>							
    		</div>
    	</div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-success" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formConceptos.$invalid" ng-if="estadoPlanilla != 2">Guardar</button>
    <button class="btn btn-primary" ng-click="aceptarSalir(); $event.preventDefault();" ng-disabled="formConceptos.$invalid" ng-if="estadoPlanilla != 2">Guardar y Salir</button>
    <button class="btn btn-warning" ng-click="cancel()">Salir sin guardar</button>
</div>