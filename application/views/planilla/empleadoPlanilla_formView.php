<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formCargo"> 
		<div class="col-md-12">
			<ul class="form-group demo-btns col-xs-12">
              <li class="pull-left">
              	<button type="button" class="btn btn-info" ng-click='btnToggleEmplFiltering()'>BUSCAR</button>
              </li>
             </ul>
			<div ui-grid="gridOptionsEmpl" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCargo.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>