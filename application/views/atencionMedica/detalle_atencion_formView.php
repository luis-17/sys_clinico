<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
	
</div>

<div class="modal-body">
	<div class="col-md-12">
		<ul class="form-group demo-btns">
			<li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
		      <div style="width: 330px; margin-top: 10px;"> 
		        <span> {{fBusqueda.desde}}  </span>
		        <span> {{fBusqueda.desdeHora}} : </span>
		        <span> {{fBusqueda.desdeMinuto}}  </span>
		      </div>
		    </li>
		    <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
		      <div style="width: 330px; margin-top: 10px;"> 
		        <span> {{fBusqueda.hasta}}  </span>
		        <span> {{fBusqueda.hastaHora}} : </span>
		        <span> {{fBusqueda.hastaMinuto}}  </span>
		      </div>
		    </li>
		</ul>
		
	</div>

	<div ui-grid="gridOptionsDetalle" ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>

</div>
<div class="modal-footer">
	<div class="col-md-10">
	    <div class="text-right pull-right">
	      <h4 class="well well-sm" style="margin-top: 0px;"> TOTAL <strong style="font-weight: 400;" class="text-success"> : S/. {{ total }} </strong> </h4>
	    </div>
	</div>
	<div class="col-md-2">
    	<button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
    </div>
</div>