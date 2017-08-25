<div class="modal-header">
	<h4 class="modal-title" ng-bind-html="titleForm"></h4>
	
</div>

<div class="modal-body">
	<div class="col-md-12 mb-md pl-n">
	    <div class="col-md-2 pl-n">
	      <label class="control-label mb-n text-blue"> Desde </label>
	      <span class="help-block text-black m-n"> {{ fBusqueda.desde }} </span> 
	    </div>
	    <div class="col-md-4">
	      <label class="control-label mb-n text-blue"> Hasta </label>
	      <span class="help-block text-black m-n"> {{ fBusqueda.hasta }}</span> 
	    </div>  
	    
	</div>

	<div ui-grid="gridOptionsDetalle" ui-grid-pagination ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div> 

</div>
<div class="modal-footer">

	<div class="col-md-12 p-n">
    	<button class="btn btn-warning pull-right" ng-click="cancel()">Cerrar</button>
    </div>
</div>