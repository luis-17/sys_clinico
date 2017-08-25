<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formStocks">
    	<div class="form-group mb-sm col-md-6 f-18">
			<label class="control-label block mb-xs">PRODUCTO: </label>
			<span>{{fDataVenta.temporal.producto.descripcion}}</span>
		</div>
    	<div class="row">
	        <div class="col-md-12">
	            <div class="panel panel-default m-n" data-widget='{"id" : "wiget10001"}'>
	                <div class="panel-body pt-n pb-n">
	                  <div class="tab-content">
	                    <div class="tab-pane active" id="home"> 
	                      <div ui-grid="gridOptionsStocks" ui-grid-pagination ui-grid-resize-columns class="grid table-responsive fs-mini-grid">
	                      	<div class="waterMarkEmptyData" ng-show="!gridOptionsStocks.data.length"> No se encontraron datos. </div>
	                      </div>
	                    </div>
	                  </div>
	                </div>
	            </div>
	        </div>
	        <div class="col-md-12">
            	<div class="text-right mr-md">
                    <h2> STOCK TOTAL <strong style="font-weight: 400;" class="text-success"> : {{ gridOptionsStocks.data[0].stock_total }} </strong> </h2>
                </div>
            </div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>