<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
	<form name="formReceta" novalidate>
		<ul class="form-group demo-btns col-xs-12">
			<li class="form-group mr mt-sm col-sm-2 p-n"> <label> Nº de Receta <small class="text-danger">(*)</small></label> 
	            <div class="input-group col-sm-12 col-md-12" > 
	            	<input tabindex="1" type="text" class="form-control input-sm" ng-model="fBusqueda.idreceta" required focus-me/>
	            </div>
          	</li>
          	<li class="form-group mr mt-lg col-sm-2 p-n"> 
                <div class="input-group" style=""> 
                  <button type="submit" class="btn btn-info btn-sm" ng-click="getPaginationRecetaMedicaEnVentaServerSide();" ng-disabled="formReceta.$invalid">
                      BUSCAR
                  </button>
                </div> 
            </li>
            <li class="form-group mr mt-sm col-sm-4 p-n"> <label class="text-bold"> Paciente: </label> <span>{{gridOptionsRecetaBusqueda.data[0].paciente}}</span></li>
			<li class="form-group mr mt-sm col-sm-3 p-n"> <label class="text-bold"> Nº Historia: </label> <span>{{gridOptionsRecetaBusqueda.data[0].idhistoria}}</span></li>
			<li class="form-group mr mt-sm col-sm-4 p-n"> <label class="text-bold"> Personal Med.: </label> <span>{{gridOptionsRecetaBusqueda.data[0].medico}}</span></li>
			<li class="form-group mr mt-sm col-sm-3 p-n"> <label class="text-bold"> Convenio: </label> <span>{{gridOptionsRecetaBusqueda.data[0].descripcion_tc}}</span></li>
		</ul>
	</form>
    <div class="row">
		<div class="form-group mb-md col-xs-12">
			<div ui-grid="gridOptionsRecetaBusqueda" ui-grid-pagination class="grid table-responsive"></div> 
		</div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsRecetaBusqueda.data.length==0">Agregar</button>
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>