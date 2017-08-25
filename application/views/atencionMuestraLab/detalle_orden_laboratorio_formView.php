<div class="modal-header">
	<h4 class="modal-title" ng-bind-html="titleForm"></h4>
	
</div>

<div class="modal-body">
	<div class="col-md-12 mb-md pl-n">
	    <div class="col-md-2 pl-n">
	      <label class="control-label mb-n text-blue"> Nº Historia </label>
	      <span class="help-block text-black m-n"> {{ fData.idhistoria }} </span> 
	    </div>
	    <div class="col-md-4">
	      <label class="control-label mb-n text-blue"> Paciente </label>
	      <span class="help-block text-black m-n"> {{ fData.apellido_paterno }} {{ fData.apellido_materno }}, {{ fData.nombres }}</span> 
	    </div>  
	    <div class="col-md-2">
	      <label class="control-label mb-n text-blue"> Edad </label>
	      <span class="help-block text-black m-n"> {{ fData.edad }} </span> 
	    </div>
	    <div class="col-md-2">
	      <label class="control-label mb-n text-blue"> Sexo </label>
	      <span class="help-block text-black m-n"> {{ fData.sexo }} </span> 
	    </div>
	</div>
	<div class="col-md-12 mb-md pl-n" ng-if="fData.observaciones">
		<div class="col-md-8  pl-n">
			<label class="control-label mb-n text-blue"> Observaciones </label>
		    <span class="help-block text-black m-n"> {{ fData.observaciones }} </span> 
		</div>
		
	</div>

	<div ui-grid="gridOptionsDetalle" ui-grid-pagination ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>

</div>
<div class="modal-footer">

	<div class="col-md-12 p-n">
    	<button class="btn btn-warning pull-right" ng-click="cancel()">Cerrar</button>
    </div>
</div>