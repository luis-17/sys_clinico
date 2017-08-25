<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormGenCupo }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
    	<div class="col-md-12 col-xs-12  mb-xs">
	    	
    		<div class="col-md-1 mb-xs">
				<label class="control-label mb-n"> N° de Doc: </label> 
				<p class="help-block mt-xs"> {{ fDataVenta.numero_documento }} </p> 
			</div>
			<div class="col-md-3 mb-xs">
				<label class="control-label mb-n"> Paciente: </label> 
				<p class="help-block mt-xs truncate"> {{ fDataVenta.cliente.nombres }} {{ fDataVenta.cliente.apellidos }} </p> 
			</div>
			<div class="col-md-3 mb-xs" > 
        	    <div class="form-group mb-xs">
					<label class="control-label mb-n"> Especialidad: </label> 
					<p class="help-block mt-xs truncate"> {{ fBusqueda.especialidad.descripcion }} </p> 
				</div>
	        </div>
	        <div class="col-md-3 mb-xs" > 
	           	<div class="form-group mb-xs">
					<label class="control-label mb-n"> Tipo de Producto: </label> 
					<p class="help-block mt-xs truncate"> {{ genCupo.itemVenta.producto.tipo_producto }} </p> 
				</div>	                    
	        </div>
	        <div class="col-md-2 mb-xs" > 
	           	<div class="form-group mb-xs">
					<label class="control-label mb-n"> Producto: </label> 
					<p class="help-block mt-xs truncate"> {{ genCupo.itemVenta.producto.descripcion }} </p> 
				</div>	                    
	        </div>
    	</div>

		<div class="col-md-12 col-xs-12  mb-xs">
			<div class="col-md-4 mb-xs" > 
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

		<div class="col-md-12 col-sm-12  mb-xs">
			<div ui-grid="gridOptionsProc" ui-grid-selection ui-grid-auto-resize class="grid table-responsive tableRowDinamic">
				<div class="waterMarkEmptyData" ng-show="!gridOptionsProc.data.length"> No hay programaciones en la fecha seleccionada. </div>
			</div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel();"> SALIR </button>
</div>