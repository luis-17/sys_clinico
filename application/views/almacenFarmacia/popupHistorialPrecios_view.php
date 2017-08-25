<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formStocks">
    	<div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs text-bold" style="width: 80px;">PRODUCTO: </label>
			<span>{{fDataHistorial.medicamento}}</span>
		</div>
		<div class="form-group mb-sm col-md-6">
			<label class="control-label mb-xs text-bold" style="width:120px;">PRECIO ACTUAL: </label>
			<span>{{fDataHistorial.precio_actual}}</span>
		</div>
    	<div class="col-md-6">
            <label class="control-label mb-xs text-bold" style="width: 80px;">ALMACÉN:</label>
            <span>{{fBusqueda.almacen.descripcion}}</span>
        </div>
        <div class="col-md-6">
            <label class="control-label mb-xs text-bold" style="width:120px;">SUB-ALMACÉN:</label>
            <span>{{fBusqueda.subalmacen.descripcion}}</span>
        </div>
	</form>
	<div class="panel-body scroll-pane mt-sm" style="height: 320px;">
		<div class="scroll-content mb-md" style=" border: 2px inset #ddd;padding: 10px">
			<div class="waterMarkEmptyData" ng-if="!listaHistorial.length">No se han realizado cambios en el precio de venta</div>
			<ul class="mini-timeline" ng-if="listaHistorial.length">
				<li class="mini-timeline-lime" ng-repeat="historial in listaHistorial">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<span class="text-bold" style="color:#616161">{{historial.usuario}}</span> modificó el precio de venta de: <span class="text-bold text-info">{{historial.precio_venta_anterior}}</span> a <span class="text-bold text-info">{{historial.precio_venta_actual}}</span>
							<span class="block text-danger">"{{historial.motivo}}"</span>
							<span class="time">{{historial.fecha_cambio}}</span>
						</div>
					</div>
				</li>
				<!--
				<li class="mini-timeline-deeporange">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<a href="#/" class="name">Aldo Palomino</a> modificó el precio de venta de: <a href="#/" class="name">S./ 13.00</a> a <a href="#/" class="name">S./ 11.00</a>
							<span class="time">05/06/2016</span>
						</div>
					</div>
				</li>

				<li class="mini-timeline-info">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<a href="#/" class="name">Vladi</a> modificó el precio de venta de: <a href="#/" class="name">S./ 11.00</a> a <a href="#/" class="name">S./ 13.00</a>
							<span class="time">04/06/2016</span>
						</div>
					</div>
				</li>

				<li class="mini-timeline-indigo">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<a href="#/" class="name">Jonathan Smith</a> modificó el precio de venta de: <a href="#/" class="name">S./ 10.00</a> a <a href="#/" class="name">S./ 11.00</a>
							<span class="time">01/06/2016</span>
						</div>
					</div>
				</li>
				<li class="mini-timeline-lime">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<a href="#/" class="name">Lucho</a> modificó el precio de venta de: <a href="#/" class="name">S./ 12.00</a> a <a href="#/" class="name">S./ 10.00</a>
							<span class="time">30/05/2016</span>
						</div>
					</div>
				</li>

				<li class="mini-timeline-deeporange">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<a href="#/" class="name">Aldo Palomino</a> modificó el precio de venta de: <a href="#/" class="name">S./ 11.00</a> a <a href="#/" class="name">S./ 12.00</a>
							<span class="time">20/05/2016</span>
						</div>
					</div>
				</li>

				<li class="mini-timeline-info">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							<a href="#/" class="name">Vladi</a> modificó el precio de venta de: <a href="#/" class="name">S./ 10.00</a> a <a href="#/" class="name">S./ 11.00</a>
							<span class="time">12/05/2016</span>
						</div>
					</div>
				</li>

				<li class="mini-timeline-indigo" style="/*position: fixed; bottom: 80px;box-shadow: 1px 1px 5px;padding: 0px 5px; background: white;bottom: 75px;width: 93%*/">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content">
							Precio Inicial: <a href="#/" class="name">S./ 10.00</a>
							<span class="time">06/05/2016</span>
						</div>
					</div>
				</li>
				-->
				<!-- <li class="mini-timeline-default">
					<div class="timeline-body ml-n">
						<div class="timeline-content">
							<button type="button" data-loading-text="Loading..." class="loading-example-btn btn btn-sm btn-default">See more</button>
						</div>
					</div>
				</li> -->
			</ul>
		</div>
	</div>
	<div class="mini-timeline-default" style="position: fixed; padding: 5px 45px;bottom: 75px;">
		<div class="timeline-body ml-n">
			<div class="timeline-content">
				<label class="control-label mb-xs text-bold">PRECIO INICIAL: </label>
				<span>{{fDataHistorial.precio_inicial}}</span>
			</div>
		</div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>