<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormSelecCupo }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="col-md-12 col-sm-12  mb-xs">
	    	<div class="row">
	    		<div class="form-group col-md-4 mb-xs">
					<label class="control-label mb-n">MÉDICO: </label> 
					<p class="help-block mt-xs"> {{ fBusqueda.programacion.medico }} </p> 
				</div>

				<div class="form-group col-md-4 mb-xs">
					<label class="control-label mb-n">ESPECIALIDAD: </label> 
					<p class="help-block mt-xs"> {{ fBusqueda.programacion.especialidad }} </p> 
				</div>

				<div class="form-group col-md-4 mb-xs">
					<label class="control-label mb-n"> EMPRESA: </label> 
					<p class="help-block mt-xs"> {{ fBusqueda.programacion.empresa }} </p> 
				</div>				
			</div>
		</div>

		<div class="col-md-12 col-sm-12  mb-xs">
	    	<div class="row">
				<div class="form-group col-md-2 mb-xs">
					<label class="control-label mb-n"> AMBIENTE: </label> 
					<p class="help-block mt-xs"> {{ fBusqueda.programacion.ambiente.numero_ambiente }} </p> 
				</div>

				<div class="form-group col-md-2 mb-xs">
					<label class="control-label mb-n"> FECHA: </label> 
					<p class="help-block mt-xs"> {{ fBusqueda.programacion.fecha_str }} </p> 
				</div>

				<div class="form-group col-md-3 mb-xs">
					<label class="control-label mb-n"> TURNO: </label> 
					<p class="help-block mt-xs"> {{ fBusqueda.programacion.turno }} </p> 
				</div>				

		        <div class="pull-right form-group col-md-4 mb-xs" > 	
					<label class="control-label m-n"> CANAL: </label> 
					<div class="input-group help-block m-n "> 
						<select name="canal" style="width:100%;" 
							class="form-control input-sm animate-repeat help-block m-n" ng-model="fBusqueda.canal"	                
				           	ng-options="item.descripcion for item in listaCanalProgAsistencial"
				           	ng-change="getListaCuposCanal();" >
				        </select>
					</div> 	
		        </div>
	    	</div>
	    	<div class="row">
	    		<div class="form-group col-md-12 mb-sm">
		    		<button tooltip-placement="bottom" tooltip="Actualizar Cupos" type="button" class="btn btn-sm btn-warning pull-right" style="margin-top: 6px;" ng-click="getListaCuposCanal(); $event.preventDefault();"> 
						<i class="ti ti-reload"></i> ACTUALIZAR 
					</button>
				</div>
				<div class="form-group col-md-12 mb-sm">
					<select name="canal" style="width: 200px;" 
						class="form-control input-sm animate-repeat pull-right m-n" ng-model="fBusqueda.estado"	                
			           	ng-options="item.descripcion for item in listaEstadosCupo"
			           	ng-change="getListaCuposCanal();" > 
				    </select>
				</div>
	    	</div>	    	
		</div>

		<div class="col-md-12 col-sm-12  mb-xs">
			<div ui-grid="gridOptionsCupos" ui-grid-pagination ui-grid-selection ui-grid-auto-resize class="grid table-responsive tableRowDinamic"></div>
        </div>

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancelSelCupo();"> SALIR </button>
</div>