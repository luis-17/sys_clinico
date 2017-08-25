<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormGenCupo }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="col-md-3 col-xs-12  mb-xs">
	    	<div class="row">
	    		<div class="col-md-12 mb-xs">
					<label class="control-label mb-n"> N° de Doc: </label> 
					<p class="help-block mt-xs"> {{ fDataVenta.numero_documento }} </p> 
				</div>
				<div class="col-md-12 mb-xs">
					<label class="control-label mb-n"> Paciente: </label> 
					<p class="help-block mt-xs truncate"> {{ fDataVenta.cliente.nombres }} {{ fDataVenta.cliente.apellidos }} </p> 
				</div>
				<!-- <div class="col-md-1 mb-xs">
					<label class="control-label mb-n"> Edad: </label> 
					<p class="help-block mt-xs"> {{ fDataVenta.cliente.edad }} </p> 
				</div>  -->
				<div class="col-md-12 mb-xs" > 
	        	    <div class="form-group mb-xs">
						<label class="control-label mb-n"> Especialidad: </label> 
						<p class="help-block mt-xs"> 
							<select name="especialidad" style="width:100%;" ng-change="getPlanning(false,false,false);" ng-disabled="boolExterno"
				                class="form-control input-sm animate-repeat help-block" ng-model="fBusqueda.especialidad"	                
					           	ng-options="item.descripcion for item in listaEspecialidadesProgAsistencial" >
					        </select>
						</p> 
					</div>
		        </div>
		        <div class="col-md-12 mb-xs" > 
		           	<div class="form-group mb-xs">
						<label class="control-label mb-n"> Producto: </label> 
						<p class="help-block mt-xs truncate"> {{ genCupo.itemVenta.producto.descripcion }} </p> 
					</div>	                    
		        </div>
		        <div class="col-md-12 mb-xs" > 
		        	<label class="control-label mb-n"> Fecha: </label> 
			        <div class="input-group pb-xs pull-right"> 
			            <span class="input-group-btn m-n">
			            	<button type="button" class="btn btn-success input-sm help-block m-n" ng-disabled="!genCupo.hayAnterior" style="height: 30px;" ng-click="getPlanning(false,'prev', true)"><i class="ti ti-arrow-left"></i></button>
			            </span>
			            <input type="text" placeholder="Desde" class="form-control input-sm datepicker help-block m-n" uib-datepicker-popup="{{dateUIDesde.format}}" popup-placement="auto right-top" 
			            	ng-model="fBusqueda.desde" is-open="dateUIDesde.opened" datepicker-options="dateUIDesde.datePikerOptions" close-text="Cerrar" alt-input-formats="altInputFormats" 
			            	ng-change="getPlanning(false, 'calendar')" style="font-size: 18px; text-align: center;height: 30px;" />
			            <span class="input-group-btn m-n">
			              <button type="button" class="btn btn-default input-sm help-block m-n" ng-click="dateUIDesde.openDP($event)" style="height: 30px;"><i class="ti ti-calendar"></i></button>
			            </span>
			            <span class="input-group-btn m-n">
			            	<button style="height: 30px;" type="button" class="btn btn-success input-sm help-block m-n" ng-disabled="!genCupo.haySiguiente" ng-click="getPlanning(true,'next')"><i class="ti ti-arrow-right"></i></button> 
			            </span>
		          	</div>
		        </div>
	    	</div>	    	
		</div>
		<div class="col-md-9 col-xs-12 mb-xs">
			<div ng-if="genCupo.hayPlanning" class="planning-medicos genera-cupo" >
		        <div class="planning" >
		          <div class="hora-header">
		            H./AMB.
		          </div>
		          <div class="header">
		            <table class="table table-bordered mb-n" style="">
		              <thead>
		                 <tr>
		                  <th ng-repeat="ambiente in genCupo.planning.ambientes" class="item-ambiente {{ambiente.classHoveredAmbiente}}"> 
		                    <div >{{ambiente.dato}} <!-- <span class="badge {{ ambiente.classTag }}">{{ ambiente.tag }}</span>  --></div>
		                  </th>
		                </tr> 
		              </thead>                    
		            </table>
		          </div>
		          <aside class="sidebar">
		            <table class="table table-bordered">
		              <tbody>
		                <tr ng-repeat="hora in genCupo.planning.horas" >
		                  <td class="{{hora.class}} {{hora.classHoveredHora}}">
		                    <div style="letter-spacing: -1px;">
		                    	{{hora.dato}} - {{hora.dato_fin}} 
		                    </div>
		                  </td>
		                </tr>                  
		              </tbody>                    
		            </table>
		          </aside>
		          <div class="body" scroller >
		            <table class="table table-bordered m-n">
		              <tbody>
		                <tr ng-repeat="grid in genCupo.planning.gridTotal" style="height:{{ 22 + 1 }}px;" >
		                  <td ng-repeat="item in grid" class="{{item.class}}" rowspan="{{item.rowspan}}" ng-if="!item.unset" 
		                  	ng-click="seleccionarCupo(item);" ng-mouseover="hoverInHoras(item);" ng-mouseleave="hoverOutHoras(item);" > 
		                      <div class="detalle truncate" style="height:{{(item.rowspan*22) +1}}px;"  >
		                      	<div class="barra-ocupacion" style="height:{{item.porcentaje}}%;" ></div>
		                        <a href="" class="label label-info" ng-click="" title="{{item.tooltip_text}}" > {{item.dato}} 
		                        	<!-- <span ng-if="item.detalle" class="adicionales" style="font-style: italic;font-size: 10px;">  </span>  -->
		                        	<!-- <p ng-if="item.detalle" class="ocupacion m-n" style="font-style: italic;font-size: 11px;">({{item.porcentaje}}%)</p>  -->
		                        	<p ng-if="item.detalle" class="ocupacion m-n" style="font-style: italic;font-size: 11px;">({{ item.total_cupos_no_cancelados }}/{{ item.total_cupos }})
		                        	 <span style="color:#4d840e;"> +({{item.total_adi_vendidos}}/{{item.cupos_adicionales}}) </span></p> 
		                        </a>
		                      </div>
		                  </td>                      
		                </tr>                      
		              </tbody>                    
		            </table>              
		          </div> 
		        </div>
	      	</div>
	      	<div ng-if="!genCupo.hayPlanning" >
	      		<div style="font-size: 16px;" class="alert alert-warning m-n p-sm text-center">{{genCupo.alerta}}</div>
	      	</div>
	      	<div class="clearfix"></div>
     	</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel();"> SALIR </button>
</div>