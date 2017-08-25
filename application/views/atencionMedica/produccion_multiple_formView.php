<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormMP }}  </h4> 
</div>
<div class="modal-body">
	<div class="row">
		<div class="col-md-12">
			<!-- <ul class="form-group demo-btns"> -->
<!-- 			<div class="form-group mr mt-sm col-sm-2 p-n"> 
				<label> EMPRESA </label> 
		        <span> {{ fDataES.proveedor.razon_social }}  </span>
		    </div>
		    <div class="form-group mr mt-sm col-sm-2 p-n"> 
		    	<label> ESPECIALIDAD </label> 
		        <span> {{ fDataES.temporal.especialidad.descripcion }}  </span>
		    </div>
		    <div class="form-group mr mt-sm col-sm-2 p-n"> 
		    	<label> PERIODO: </label> 
		        <span> {{ fDataES.temporal.mes.descripcion }} - {{ fDataES.temporal.anio }} </span>
		    </div> -->

		    <div class="form-group col-md-4">
				<label class="control-label m-n"> EMPRESA: </label>
				<p class="help-block"> {{ fDataES.proveedor.razon_social }} </p>
			</div>
			<div class="form-group col-md-4">
				<label class="control-label m-n"> ESPECIALIDAD: </label>
				<p class="help-block"> {{ fDataES.temporal.especialidad.descripcion }} </p>
			</div>
			<div class="form-group col-md-4">
				<label class="control-label m-n"> PERIODO: </label>
				<p class="help-block"> {{ fDataES.temporal.mes.descripcion }} - {{ fDataES.temporal.anio }} </p>
			</div>
			<!-- </ul> -->
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div ui-grid="gridOptionsMP" ui-grid-selection ui-grid-resize-columns class="grid table-responsive"></div>
		</div>
	</div>
</div>
<div class="modal-footer">
	<!-- <div class="col-md-10">
	    <div class="text-right pull-right">
	      <h4 class="well well-sm" style="margin-top: 0px;"> TOTAL <strong style="font-weight: 400;" class="text-success"> : S/. {{ total }} </strong> </h4>
	    </div>
	</div> -->
    <button class="btn btn-warning" ng-click="cancelMT();">Cerrar</button>
</div>