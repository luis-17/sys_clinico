<div class="modal-header">
	<h4 class="modal-title" ng-bind-html="titleForm"></h4>	
</div>
<div class="modal-body">
	<form class="row" name="formProductosConvenio"> 
		<div class="col-md-4 pr-n">
			<div style="margin-top: 60px;" ui-grid="gridOptionsProd" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
		</div>
		<div class="col-md-1"  style="text-align: center; margin-top: 10%;">
			<div>
				<button class="btn btn-info" style="font-size: 20px;width: 100%;" 
						ng-click="agregarTodos();"							
						uib-tooltip="Agregar Todos" tooltip-placement="top" >
						<i class="fa fa-chevron-right"></i>
				</button> 
			</div> 
			<div style="margin-top: 10px;" >
				<button class="btn btn-primary"  style="font-size: 20px;width: 100%;"
						ng-click="agregarSeleccionados();"
						uib-tooltip="Agregar Seleccionados" tooltip-placement="bottom"> 
						<i class="fa fa-angle-double-right"></i>
				</button>
			</div>
		</div>
		<div class="col-md-7 pl-n">
			<ul class="form-group demo-btns row"> 
	           	<li class="pull-left col-md-3 p-n"> 
		            <label class="control-label" for="porcentaje" style=" top: 11px;" > Porcentaje Dscto. </label>
		            <div class="input-group " >
						<input type="text" placeholder="% Cliente" class="form-control" name="porcentaje" ng-model="fData.porcentaje">
						<span class="input-group-btn">
							<button class="btn btn-success" type="button" ng-click="btnUpdatePorcentajeConvenio();"><i class="fa fa-edit"></i></button>
						</span>	
					</div>
		        </li> 
		        <li class="pull-left col-md-3" >
		        	<label class="checkbox-inline" style="top: 11px;position: relative;left: 10px;margin-top: 15px;">
		        		<input type="checkbox" style="top: -5px;" ng-model="fBusqueda.soloDecimales" ng-change="checkVerPreciosConDecimal();">Precios decimales
		        	</label>
		        </li>
		        <li class="pull-right" ng-if="mySelectionProductosConvenioGrid.length > 0" >
		        	<button type="button" class="btn btn-danger" ng-click='btnAnularProducto()'>Anular</button>
		        </li>
	            <li class="pull-right" ng-if="mySelectionProductosConvenioGrid.length > 0">
	            	<button type="button" class="btn btn-warning" ng-click='btnDeshabilitarProducto()'>Deshabilitar</button>
	            </li>
	            <li class="pull-right" ng-if="mySelectionProductosConvenioGrid.length > 0"> 
	            	<button type="button" class="btn btn-primary" ng-click='btnHabilitarProducto()'>Habilitar</button>
	            </li>
			</ul>
			<div ui-grid="gridOptionsDetalle" ui-grid-pagination ui-grid-selection ui-grid-edit ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
		</div>
	</form>
</div> 
<div class="modal-footer">
	<button class="btn btn-success " ng-click="guardarProductos()">Guardar</button>
	<button class="btn btn-warning " ng-click="cancel()">Cerrar</button>
</div>