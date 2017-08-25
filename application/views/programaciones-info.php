<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Informes</li>
  <li class="active">Programaciones - Informes</li>
</ol>
<div class="container-fluid" ng-controller="programacionesInfoController">
    <form class="row"> 
    	<div class="col-md-12">
    		<div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Programación de Médicos</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
				<div class="panel-body">
					<div class="row">
				    	<div class="col-md-2 col-xs-12 mb-xs">
					    	<div class="row">
								<div class="col-md-12 mb-xs" > 
					        	    <div class="form-group mb-xs">
										<label class="control-label mb-n"> Especialidad: </label> 
										<p class="help-block mt-xs"> 
										<!-- <select name="especialidad" style="width:100%;" ng-change="getPlanning(false,false,false,'VE');" ng-disabled="boolExterno"
								                class="form-control input-sm animate-repeat help-block" ng-model="fBusqueda.especialidad"	                
									           	ng-options="item.descripcion for item in listaEspecialidadesProgAsistencial" >
									        </select> -->
											<select name="especialidad" style="width:100%;" ng-change="getMedicos();" ng-disabled="boolExterno"
								                class="form-control input-sm animate-repeat help-block" ng-model="fBusqueda.especialidad"             
									           	ng-options="item as item.descripcion for item in listaEspecialidadesProgAsistencial" >
									        </select>

										</p> 
									</div>
						        </div>

								<div class="col-md-12 mb-xs" > 
					        	    <div class="form-group mb-xs">
										<label class="control-label mb-n"> Médico: </label> 
										<p class="help-block mt-xs"> 
											<select name="medico" style="width:100%;" ng-change="getPlanning(false,false,false,'VM');" class="form-control input-sm animate-repeat help-block" ng-model="fBusqueda.medico" ng-options="item.medico for item in listaMedicosProgAsistencial" >
									        </select>
										</p> 
									</div>
						        </div>
						        <div class="col-md-12 mb-xs" > 
					        	    <div class="form-group mb-xs">
										<label class="control-label mb-n"> Tipo de Atención: </label> 
										<p class="help-block mt-xs"> 
											<select name="atencion" style="width:100%;" ng-change="getPlanning(false,false,false,'VM');" class="form-control input-sm animate-repeat help-block" ng-model="fBusqueda.tipoAtencion" ng-options="item.descripcion for item in listaTipoAtencion" >
									        </select>
										</p> 
									</div>
						        </div>
						        <div class="col-md-12 mb-xs" > 
						        	<label class="control-label mb-n"> Fecha: </label> 
							        <div class="input-group pb-xs pull-right"> 
							            <span class="input-group-btn m-n">
							            	<button type="button" class="btn btn-success input-sm help-block m-n" ng-disabled="!genCupo.hayAnterior" style="height: 30px;" ng-click="getPlanning(false,'prev', true ,fBusqueda.view ) "><i class="ti ti-arrow-left"></i></button>
							            </span>
							            <input type="text" placeholder="Desde" class="form-control input-sm datepicker help-block m-n" uib-datepicker-popup="{{dateUIDesde.format}}" popup-placement="auto right-top" 
							            	ng-model="fBusqueda.desde" is-open="dateUIDesde.opened" datepicker-options="dateUIDesde.datePikerOptions" close-text="Cerrar" alt-input-formats="altInputFormats" 
							            	ng-change="getPlanning(false, 'calendar')" style="font-size: 18px; text-align: center;height: 30px;" />
							            <span class="input-group-btn m-n">
							              <button type="button" class="btn btn-default input-sm help-block m-n" ng-click="dateUIDesde.openDP($event)" style="height: 30px;"><i class="ti ti-calendar"></i></button>
							            </span>
							            <span class="input-group-btn m-n">
							            	<button style="height: 30px;" type="button" class="btn btn-success input-sm help-block m-n" ng-disabled="!genCupo.haySiguiente" ng-click="getPlanning(true,'next',fBusqueda.view)"><i class="ti ti-arrow-right"></i></button> 
							            </span>
						          	</div>
						        </div>
					    	</div>	    	
						</div>
				     	<div class="col-md-10 col-xs-12 mb-xs">
							<div ng-if="genCupo.hayPlanning" class="planning-medicos genera-cupo info">
						        <div class="planning" >
						          <div class="hora-header" style="height: 51px;padding: 12px; width: 120px">
						            H./AMB.
						          </div>
						          <div class="header" style="height: auto; margin-left: 120px;">
						            <table class="table table-bordered mb-n" style="border-bottom: none;">
						              <thead>
						                <tr>       
						                  	<td ng-repeat="hora in genCupo.planning.horas" class="{{hora.class}} {{hora.classHoveredHora}}" style="max-width: 61px; border-right: 1px solid #00BCD4;">
							                    <div style="letter-spacing: -1px; font-size: 14px;">
							                    	<span> {{hora.dato}} </span><br>
							                    	<span> {{hora.dato_fin}} </span> 
							                    </div>
						                  	</td>                       
						                </tr> 
						              </thead>                    
						            </table>
						          </div>
						           <aside class="sidebar" style="width:120px;">
						            <table class="table table-bordered">
						              <tbody>
						                <tr ng-repeat="ambiente in genCupo.planning.ambientes" class="item-ambiente info ">
						                  <td class="{{ambiente.classHoveredAmbiente}}" style="width:62px;"
						                  	ng-style="genCupo.planning.gridTotal2[0][1].CM != null && genCupo.planning.gridTotal2[0][1].P != null ? { 'height':'80px' } : { 'height': '60px' }">
						                    <div class="ambiente-rotate p-xs">
						                    	{{ambiente.dato}} 
						                    </div>
						                  </td>
						                  <td style="width:62px; border: 1px solid #00BCD4;"
						                  	ng-style="genCupo.planning.gridTotal2[0][1].CM != null && genCupo.planning.gridTotal2[0][1].P != null ? { 'height':'80px' } : { 'height': '60px' }">
						                    <div style="font-size: 16px; width:62px; height:40px;" class="p-xs"
						                    	ng-style="genCupo.planning.gridTotal2[0][1].CM != null && genCupo.planning.gridTotal2[0][1].P != null && { 'border-bottom':'1px solid #00BCD4' }"
						                    	ng-if="genCupo.planning.gridTotal2[0][1].CM != null">
						                    	CONS. 
						                    </div>
						                    <div style="font-size: 16px; width:62px; height:40px;" class="p-xs"
						                    	ng-if="genCupo.planning.gridTotal2[0][1].P != null">
						                    	PROC. 
						                    </div>
						                  </td>
						                </tr>                  
						              </tbody>                    
						            </table>
						          </aside>

						         <div class="body" scroller>
						            <table class="table table-bordered m-n">
						              <tbody>
						                <tr ng-repeat="grid in genCupo.planning.gridTotal2" style="height:80px; border-bottom: 1px solid #dad8d8;" 
						                	ng-style="grid[1].CM != null && grid[1].P != null ? { 'height':'80px' } : { 'height': '60px' }"> 
						                  <td ng-repeat="item in grid"  style="min-width:61px; width:61px; max-width:61px; border-right: 1px solid #dad8d8; z-index: 0"
						                  		ng-style="item.CM != null && item.P != null ? { 'height':'40px' } : { 'height': '60px' }"> 
						                      
						                      	<div class="detalle truncate {{item.CM.class}} cons" style="width:{{item.CM.rowspan*61}}px;" ng-click="seleccionarCupo(item.CM);" 
						                      		ng-mouseover="hoverInHoras(item.CM);" ng-mouseleave="hoverOutHoras(item.CM);" ng-if="item.CM != null">
							                      	<div ng-if="!item.CM.unset" class="barra-ocupacion" style="height:{{item.CM.porcentaje}}%; width:{{item.CM.rowspan*61}}px;"></div>
							                        <a ng-if="!item.CM.unset" href="" class="label label-info" title="{{item.CM.tooltip_text}}" > {{item.CM.dato}} 
							                        	<p ng-if="item.CM.detalle" class="ocupacion m-n" style="font-style: italic;font-size: 11px;">({{ item.CM.total_cupos_no_cancelados }}/{{ item.CM.total_cupos }})
							                        	 <span style="color:#4d840e;"> +({{item.CM.total_adi_vendidos}}/{{item.CM.cupos_adicionales}}) </span></p> 
							                        </a>
						                      	</div>

						                      	<div class="detalle truncate {{item.P.class}} proc" style="width:{{item.P.rowspan*61}}px;" ng-click="verListaPacientesProc(item.P);" 
						                      		ng-mouseover="hoverInHoras(item.P);" ng-mouseleave="hoverOutHoras(item.P);"
						                      		ng-style="item.CM != null && { 'border-top':'1px solid #DAD8D8' }" ng-if="item.P != null">
							                        <a ng-if="item.P.detalle" href="" class="label label-info"  title="{{item.P.tooltip_text}}" style="padding: 9px;"> {{item.P.dato}} </a>
						                      	</div>
						                  	</td>
						                </tr>                      
						              </tbody>                    
						            </table>              
						          </div>						     
						        </div>
					      	</div>	
				     	</div>
			     	</div>
		     	</div>
	     	</div>
     	</div>
	</form>
</div>