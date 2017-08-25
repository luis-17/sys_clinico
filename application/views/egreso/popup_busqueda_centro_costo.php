<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
		<ul class="form-group demo-btns col-xs-12">
            <li class="form-group mr mt-sm col-sm-2 pr-n" > <label> Categoria </label>
              <div class="input-group block"> 
                <select class="form-control input-sm" ng-model="fBusqueda.categoria" ng-change="cargarSubCat(fBusqueda.categoria);" 
                  ng-options="item as item.descripcion for item in listaCatCentroCosto" > </select> 
              </div>
            </li>
          
            <li class="form-group mr mt-sm col-sm-4 p-n" > <label> Subcategoria </label> 
              <div class="input-group block"> 
                <select class="form-control input-sm" ng-model="fBusqueda.subcategoria" ng-change="getPaginationCCServerSide()" 
                  ng-options="item as item.descripcion for item in listaSubCatCentroCosto" > </select> 
              </div>
            </li>
        </ul>
    </div>    
	<div class="row">
		<div class="form-group mb-md col-xs-12">
			<div ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid="gridOptionsCCBusqueda" class="grid table-responsive"></div> 
		</div>
	</div>
</div> 
<div class="modal-footer"> 
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCliente.$invalid" > Seleccionar </button>  -->
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div>