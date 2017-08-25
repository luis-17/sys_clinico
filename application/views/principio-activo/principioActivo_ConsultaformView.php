<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formPrincipioActivo">
    	<div class="form-group mb-sm col-md-8">
    		<label class="control-label text-bold mb-xs" style="min-width: 105px;">CÓDIGO: </label>
			<span>{{fDataVenta.temporal.producto.id}}</span>
			<br>
			<label class="control-label text-bold mb-xs" style="min-width: 105px;">PRODUCTO: </label>
			<span>{{fDataVenta.temporal.producto.descripcion}}</span>
			<br>
			<label class="control-label text-bold mb-xs" style="min-width: 105px;">LABORATORIO: </label>
			<span>{{fDataVenta.temporal.producto.laboratorio}}</span>
			<br>
			<label class="control-label text-bold mb-xs" style="min-width: 105px;" ng-if="!fDataVenta.esPreparado">STOCK: </label>
			<span ng-if="!fDataVenta.esPreparado"> {{fDataVenta.temporal.producto.stockActual}}</span>
			<br ng-if="!fDataVenta.esPreparado">
			<label class="control-label text-bold mb-xs" style="min-width: 105px;" ng-if="!fDataVenta.esPreparado">STOCK CENTRAL: </label>
			<span ng-if="!fDataVenta.esPreparado"> {{fDataVenta.temporal.producto.stock_central}}</span>
			<br ng-if="!fDataVenta.esPreparado">
			<label class="control-label text-bold mb-xs" style="min-width: 105px;">PRECIO: </label>
			<span> {{fDataVenta.temporal.producto.precio}}</span>

		</div>
		<div class="form-group mb-sm col-md-4" ng-if="!fDataVenta.esPreparado">
			<label class="control-label block text-bold mb-xs">PRINCIPIOS ACTIVOS: </label>
			<span class="block" ng-repeat="principio in fDataVenta.temporal.principios"> - {{principio.descripcion}}</span>
			<span ng-show="!fDataVenta.temporal.principios.length" class="text-muted">No tiene principios activos asignados</span>
		</div>
		<div class="form-group mb-sm col-md-12 text-left" style="min-height: 1px">
			
		</div>
		<div class="form-group mb-sm col-md-12 text-left" style="min-height: 1px">
			
		</div>
		<div class="form-group mb-sm col-md-12 text-left" style="min-height: 1px">
			
		</div>
		<div class="col-md-12" ng-if="fDataVenta.esPreparado">
			<label class="control-label block text-bold mb-xs">COMPONENTES: </label>
			<div ui-grid="gridOptionsComponentes" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid">
		      	<div class="waterMarkEmptyData" ng-show="!gridOptionsComponentes.data.length"> No hay datos. </div>
		    </div>
		</div>
		
		
    	<div class="row" ng-if="!fDataVenta.esPreparado">
	        <div class="col-md-12">
	            <div class="panel panel-default m-n" data-widget='{"id" : "wiget10001"}'>
	                <div class="panel-body pt-n pb-n">
	                  <div class="tab-content">
	                    <div class="tab-pane active" id="home"> 
	                      <div ui-grid="gridOptionsPrincipioBusqueda" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid">
	                      	<div class="waterMarkEmptyData" ng-show="!gridOptionsPrincipioBusqueda.data.length"> No se encontraron medicamentos similares. </div>
	                      </div>

	                    </div>
	                    

	                  </div>
	                </div>
	            </div>
	        </div>
        </div>



	</form>
</div>
<div class="modal-footer">
    <!--<button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formPrincipioActivo.$invalid">Aceptar</button>-->
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>