<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> idsubalmacenorigen -->
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formTraslado"> 
		<fieldset class="col-lg-6 col-xs-12">
			<div class="row">
				<legend class="col-lg-12 pr-n"> Origen </legend>

				<div class="form-group mb-md col-md-6">
					<label class="control-label mb-xs"> Almacen Origen <small class="text-danger">(*)</small> </label>
					<select class="form-control input-sm" ng-model="fData.almacen" ng-options="item as item.descripcion for item in listaAlmacenes" required tabindex="10" 
						ng-change="listarSubAlmacenesAlmacen1Form(fData.almacen.id)"> </select>
				</div>
				<div class="form-group mb-md col-md-6">
					<label class="control-label mb-xs"> Sub Almacen Origen <small class="text-danger">(*)</small> </label>
					<select class="form-control input-sm" ng-model="fData.idsubalmacenorigen" ng-options="item.id as item.descripcion for item in listaSubAlmacenOrigenForm" 
						ng-change="listarSubAlmacenesAlmacen2Form(fData.almacenDestino.id,fData.idsubalmacenorigen)" tabindex="20" required> </select>
				</div>

				<div class="form-inline col-md-12 text-blue">
					<label class="control-label">Seleccione Productos con stock </label> 
				</div> 
			 	<div class="col-md-12">
	    			<div ui-grid="gridOptionsProdSubAlmacen" ui-grid-pagination ui-grid-selection ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div>
	    		</div>
	    	</div>	
		</fieldset>
		<fieldset class="col-lg-6 col-xs-12">
			<div class="row">
				<legend class="col-lg-12 pr-n"> Destino </legend> 

				<div class="form-group mb-md col-md-4">
					<label class="control-label mb-xs"> Almacen Destino <small class="text-danger">(*)</small> </label>
					<select class="form-control input-sm" ng-model="fData.almacenDestino" ng-options="item as item.descripcion for item in listaAlmacenesDestinoForm" required 
						tabindex="30" ng-change="listarSubAlmacenesAlmacen2Form(fData.almacenDestino.id);"> </select>
				</div>
				<div class="form-group mb-md col-md-4">
					<label class="control-label mb-xs"> Sub Almacen Destino<small class="text-danger">(*)</small> </label>
					<select class="form-control input-sm" ng-model="fData.idsubalmacen2" ng-options="item.id as item.descripcion for item in listaSubAlmacenDestino" tabindex="40" 
						ng-change="getPaginationProdServerSide();" required> </select>
				</div>

				<div class="form-group mb-md col-md-4" ng-if=" fSessionCI.key_group == 'key_sistemas' ">
					<label class="control-label mb-xs"> Fecha de traslado <small class="text-danger">(*)</small> </label>
					<div class="input-group">
						<input tabindex="50" type="text" class="form-control input-sm mask" ng-model="fData.fecha_traslado" style="width: 80px;" data-inputmask="'alias': 'dd-mm-yyyy'" required/>
						<input tabindex="60" type="text" class="form-control input-sm mask" ng-model="fData.hora_traslado" style="width: 45px;" data-inputmask="'mask':'99:99'" ng-pattern="pHoraMinuto" required/>
					</div>
				</div>
				<div class="form-inline col-md-12 text-blue">
					<label class="control-label"> Edite la cantidad y el precio, con doble click en las celdas amarillas: </label> 
				</div>
				<div class="col-md-12">
	    			<div ui-grid="gridOptionsAddProducto" ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid scroll-x-none" 
	    				style="height: 290px!important; overflow-x: hidden;"></div>
	    		</div>
	    		<!-- listaSubAlmacenDestino -->
	    		<div class="form-group col-md-12 mt-xs mb-n">
		   			<label class="control-label mb-xs">Observaciones : </label>
		   			<textarea class="form-control col-md-12 p-n" ng-model="fData.motivo_movimiento" rows="2" tabindex="70"></textarea>
		   		</div>
	    	</div>
		</fieldset> 

	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formTraslado.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>