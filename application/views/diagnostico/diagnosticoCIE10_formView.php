<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
 	<!-- <ul class="form-group demo-btns">
        <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></li>
    </ul> -->
    <div class="row">
    	<div class="form-group mb-md col-xs-12">
			<div ui-grid="gridOptionsCIE10" ui-grid-pagination ui-grid-cellNav ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div> 
		</div>
    </div>
</div>
<div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div>