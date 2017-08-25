<style type="text/css">
	.feriado button {
    	background-color: red!important;
    	/*border-radius: 32px;*/
    	color: white;
  	}
  	td button[disabled]{
  		background-color: red!important;
    	/*border-radius: 32px;*/
    	color: white;
  	}
</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>

<div class="modal-body">  
	<form class="row" name="formProgAmbiente">   
    	<div class="form-group col-md-6 mb-xs ">
    		<div class="row">
    			<div class="form-group col-md-12 mb-xs ">
		    		<label class="control-label mb-n">	Ambiente: <small class="text-danger">(*)</small> </label>
		            <select class="form-control input-sm" ng-model="fDataAdd.ambiente" ng-options="item as item.descripcion for item in listaAmbiente" ng-change="cambiarAmbiente();" tabindex="1" focus-me></select> 
    			</div>
    		</div>
    		<div class="row" ng-if="!boolEditar">
    			<div class="form-group col-md-12 mb-xs ">
					<label class="control-label mb-xs"> Seleccione fechas: <small class="text-danger">(*)</small></label> 
					<uib-datepicker class="date-table"
					multi-select='fDataAdd.arrFechas' 
					date-disabled="disabledNuevo(date, mode)"
					select-range='false'
					ng-model='fDataAdd.activeDate'
					ng-required="true"></uib-datepicker>
				</div>    
    		</div>
    		<div class="row" ng-if="boolEditar">
    			<div class="form-group col-md-12 mb-xs ">
					<label class="control-label mb-xs"> Fecha: <small class="text-danger">(*)</small></label> 
					<uib-datepicker class="date-table"
					
					date-disabled="disabled(date, mode)"
					select-range='false'
					ng-model='fDataAdd.activeDateEdit'
					ng-required="true" ng-change="seleccionDia();"></uib-datepicker>
				</div>    
    		</div>
		</div>
       	<div class="form-group col-md-6 mb-xs ">
			<label class="control-label mb-n block"> Horas: <small class="text-danger">(*)</small> </label>

			<input type="checkbox" class="mb-n" ng-model="boolTodos" ng-change="seleccionarHoras();" ng-disabled="listaHoras1.length == 0"> {{textoSeleccion}} todos:
			<div class="row">
				<div class="form-group col-md-6 mb-xs" ng-show="listaHoras1.length > 0">
					<select class="form-control input-sm" multiple="" ng-model="fDataAdd.horas1" ng-options="item as item.descripcion for item in listaHoras1" required tabindex="3" style="height: 218px; width:150px;" >
					</select>
				</div>
				<div class="form-group col-md-6 mb-xs" ng-if="listaHoras1.length == 0">
					<select class="form-control input-sm" multiple="" tabindex="3" style="height: 232px; width:150px;" disabled="true" >
						<option style="opacity:0.45;font-size:1.5em;padding-top:95px;">No hay datos</option>
					</select>
				</div>
			</div>
		</div>
		<div class="form-group col-md-12 mb-xs" ng-if="boolEditar">
			<label class="control-label mb-n"> Horas: </label>
			<button class="btn btn-success pull-right" ng-click="agregarHorasACesta();"> AGREGAR HORAS </button>
		</div>
		<div class="form-group col-md-12 mb-xs" ng-if="boolEditar">
			<div ui-grid="gridOptionsDetalleHoras" ui-grid-resize-columns ui-grid-pagination ui-grid-edit class="grid table-responsive fs-mini-grid  scroll-x-none" ng-style="getTableHeight();">
				<div class="waterMarkEmptyData" ng-show="!gridOptionsDetalleHoras.data.length"> No se encontraron datos. </div>
			</div>
		</div>

		<div class="form-group col-md-12 mb-md" ng-if="!boolEditar">
			<label class="control-label mb-n"> Comentario: </label>
			<textarea class="form-control " ng-model="fDataAdd.comentario" placeholder="Ingrese comentario" tabindex="5" ></textarea>
		</div>
		<!-- llamadas: {{llamadasClase}} -->
	</form>
</div>
	
<div class="modal-footer">
	<button class="btn btn-primary" ng-click="aceptar()" ng-disabled="formProgAmbiente.$invalid || !boolAmbiente || (fDataAdd.arrFechas.length == 0 && !boolEditar)"  ng-if="!boolEditar">Guardar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>

