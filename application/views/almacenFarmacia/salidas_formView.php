<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> -->
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formSalida"> 
		<fieldset class="col-lg-6 col-xs-12">
			<div class="row">
				<legend class="col-lg-12 pr-n"> Origen </legend>
				<div class="form-group mb-md col-md-6">
					<label class="control-label mb-xs"> Almacen <small class="text-danger">(*)</small> </label>
					<select class="form-control input-sm" ng-model="fData.almacen" ng-options="item as item.descripcion for item in listaAlmacenes" required tabindex="1" ng-change="listarSubAlmacen(fData.almacen.id)"> </select>
				</div>
				
				<div class="form-group mb-md col-md-6">
					<label class="control-label mb-xs"> Sub Almacen Origen <small class="text-danger">(*)</small> </label>
					<select class="form-control input-sm" ng-model="fData.idsubalmacen" ng-options="item.id as item.descripcion for item in listaSubAlmacen" tabindex="2" ng-change="getPaginationProdServerSide(); limpiarCesta();" required> </select>
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
				<div class="form-group mb-md col-md-6" >
					<label class="control-label mb-xs"> Fecha de Baja <small class="text-danger">(*)</small> </label>
					<input type="text" class="form-control input-sm mask" ng-disabled="fSessionCI.key_group != 'key_caja_far' || fSessionCI.key_group != 'key_asis_far'" ng-model="fData.fecha_salida" style="width: 120px;" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="3" required/>
				</div>

				<div class="form-inline col-md-12 text-blue">
					<label class="control-label">Ingrese Fecha de vencimiento en el formato d-m-a (por ejemplo 01-10-2016) </label> 
				</div>
				<div class="col-md-12">
	    			<div ui-grid="gridOptionsAddProducto" ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid scroll-x-none" style="height: 290px!important; overflow-x: hidden;"></div>
	    		</div>
	    		<div class="form-group col-md-12 mt-xs mb-n" >
	    			<label class="control-label mb-xs">Motivo : <small class="text-danger">(*)</small></label>
	    			<textarea class="form-control col-md-12 p-n" ng-model="fData.motivo_movimiento" rows="2" tabindex="4" required></textarea>
	    		</div>
	    	</div>
		</fieldset>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formSalida.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>