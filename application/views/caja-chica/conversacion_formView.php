<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormCV }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formConvers">
    	<div class="col-md-5 pb">
			<label class="control-label m-n text-bold">PROVEEDOR: </label>
			<span class="block">{{fDataCV.empresa}}</span>
		</div>
		<div class="col-md-3 pb">
			<label class="control-label m-n text-bold">TIPO DE DOCUMENTO: </label>
			<span class="block">{{fDataCV.descripcion_td}}</span>
		</div>
    	<div class="col-md-2 pb">
            <label class="control-label m-n text-bold">NUMERO DE DOC.:</label>
            <span class="block">{{fDataCV.numero_documento}}</span>
        </div>
        <div class="col-md-2 pb">
            <label class="control-label m-n text-bold">IMPORTE TOTAL:</label>
            <span class="block">{{fDataCV.importe_local_con_igv}}</span>
        </div>
        <div class="col-md-5 pb">
            <label class="control-label m-n text-bold">SUBOPERACIÓN:</label>
            <span class="block">{{fDataCV.suboperacion}}</span>
        </div>
        <div class="col-md-7 pb">
            <label class="control-label m-n text-bold">CONCEPTO:</label>
            <span class="block">{{fDataCV.glosa}}</span>
        </div>
	</form>
	<div class="panel-body scroll-pane mt-sm" style="height: 260px;">
		<div class="scroll-content mb-md" style=" border: 2px inset #ddd;padding: 10px">
			<!-- <div class="waterMarkEmptyData" ng-if="!listaComentario.length">No se han encontrados datos</div> -->
			<ul class="mini-timeline">
				<li class="mini-timeline-lime">
					<div class="timeline-icon"></div>
					<div class="timeline-body"> 
						<div class="timeline-content"> 
							<span class="text-bold" style="color:#616161">{{fDataCV.empleado}}</span> 
							agregó un nuevo movimiento por concepto de: 
							<span class="text-info">{{fDataCV.glosa}}</span> 
							por un monto de 
							<span class="text-info">{{fDataCV.importe_local_con_igv}}</span>
							<span class="time">{{fDataCV.fecha_registro}}</span>
						</div>
					</div>
				</li>
				<li class="mini-timeline-lime" ng-repeat="row in listaComentario">
					<div class="timeline-icon"></div>
					<div class="timeline-body">
						<div class="timeline-content" ng-if="row.comentario && !(row.color_estado)">
							<span class="text-bold" style="color:#616161">{{row.responsable}}</span> 
							<span> escribió un comentario: </span>
							<span class="block text-italic text-info">"{{row.comentario}}"</span>
							<span class="time">{{row.fecha_registro}}</span>
						</div>
						<div class="timeline-content" ng-if="!(row.comentario) && row.color_estado"> 
							<span class="text-bold" style="color:#616161">{{row.responsable}}</span> 
							<span> cambió el estado a <img class="pointer" style="width: 20px;" src="{{ dirImagesSemaforo+row.estado_color_obj.nombre_img  }}" alt="{{row.estado_color_obj.label}}" /> </span>
							<span class="time">{{row.fecha_registro}}</span>
						</div>
						<div class="timeline-content" ng-if="row.comentario && row.color_estado"> 
							<span class="text-bold" style="color:#616161">{{row.responsable}}</span> 
							<span> cambió el estado a <img class="pointer" style="width: 20px;" src="{{ dirImagesSemaforo+row.estado_color_obj.nombre_img  }}" alt="{{row.estado_color_obj.label}}" /> </span> 
							<span> y escribió un comentario: </span>
							<span class="text-italic text-info">"{{row.comentario}}"</span>
							<span class="time">{{row.fecha_registro}}</span>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<form ng-if="(mySelectionGridHC[0].estado_acc == 1 || mySelectionGridHC[0].estado_acc == 2) || (mySelectionGridES[0].estado_acc == 1 || mySelectionGridES[0].estado_acc == 2)"> 
		<div class="form-group mb">
			<label class="control-label mb-xs"> CAMBIAR EL ESTADO: <small>{{ fDataCV.estado_color_obj.label_cambio }}</small> </label> 
			<img ng-click="cambiarEstadoColor();" class="block pointer" style="width: 40px;" src="{{ dirImagesSemaforo+fDataCV.estado_color_obj.nombre_img_cambio  }}" alt="{{ fDataCV.estado_color_obj.label_cambio }}" /> 
		</div>
		<div class="form-group mb">
			<label class="control-label mb-xs"> AGREGAR COMENTARIO </label> 
			<textarea ng-model="fDataCom.comentario_text" class="form-control input-sm"></textarea> 
		</div>
		<div class="form-group mb"> 
			<button ng-click="btnAgregarItem();" class="btn btn-success pull-right btn-sm ml" type="button" > <i class="fa fa-plus"> </i> AGREGAR ITEM </button> 
			<div class="clearfix"></div>
		</div>
	</form>
	
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancelDet2();">Cerrar</button>
</div>