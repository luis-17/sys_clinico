<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">

	
	<div class="row">
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n pl-xs"> Nº Pedido: </label>
			<div style="font-weight: bold;font-size: 1.1em;">
				<span class="mt-xs pl-xs" style="display: inline-block;"> {{ fProVenta.prefijo }} - </span>
				<!-- <span class="help-block mt-xs" style="font-weight: bold;"> {{ fProVenta.correlativo }} </span> -->
				<input id="correlativo" type="text" ng-model="fProVenta.correlativo" class="form-control input-sm" placeholder="" style="display: inline-block;
text-align: center;width: 30%;font-size: 1em;" ng-enter="cargarPedido();" maxlength="4" />
			</div>
			
		</div>
		
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Cliente: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fProVenta.cliente }} </p> 
		</div>
		<div class="form-group col-md-3 mb-md">
			<label class="control-label mb-n"> Fecha de Pedido: </label>
			<p class="help-block mt-xs" style="font-weight: bold;"> {{ fProVenta.fecha_movimiento }} </p> 
		</div>
		<div class="text-right col-md-3 mb-md">
			<small class="text-default block mb-xs" style="font-size: 18px;line-height: 1;" > {{ fProVenta.aleasDocumento }} N° <strong>{{ fProVenta.ticket }}</strong>
		    	<button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarCodigoTicket(); $event.preventDefault();"> <i class="ti ti-reload "></i> </button>
		  	</small>  
		  	<small class="text-gray block" style="font-size: 14px;line-height: 1;" > Orden N° {{ fProVenta.orden }} 
		    	<button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-xs btn-warning" ng-click="generarNumOrden(); $event.preventDefault();"> <i class="ti ti-reload "></i> </button> 
		  	</small>
		</div>
		<div class="form-group col-md-12">
			<div class="form-group col-md-3 col-sm-6 mb-md pl-xs" ng-if="fProVenta.idtipodocumento != 2"> 
	            <label class="control-label mb-xs"> Tipo de Documento </label>
	            <select class="form-control input-sm" ng-model="fProVenta.idtipodocumento" ng-change="generarCodigoTicket();" ng-options="item.id as item.descripcion for item in listaTipoDocumento" required tabindex="102"> </select> 
	        </div>
	        <div class="form-group mb-md col-md-3 col-sm-6 p-n" ng-if="fProVenta.idtipodocumento == 2">
	            <label class="control-label mb-xs"> Tipo de Documento </label>
	            <select class="form-control input-sm" ng-model="fProVenta.idtipodocumento" ng-change="generarCodigoTicket();" ng-options="item.id as item.descripcion for item in listaTipoDocumento" required tabindex="102"> </select> 
	        </div>
	        <div class="form-group mb-md col-md-3 col-sm-6" ng-if="fProVenta.idtipodocumento == 2">
	            <label class="control-label mb-xs"> RUC Empresa </label>
	            <input type="text" ng-model="fProVenta.ruc" class="form-control input-sm" tabindex="103" placeholder="" required disabled  /> 
	        </div>
	        <!-- <div class="form-group mb-md pt-xs mt-md col-md-6 col-sm-12">
	              <label><input type="checkbox" value="" ng-model="fProVenta.estemporal" tabindex="104" ng-change="limpiaDatosMedicamento();" ng-disabled="gridOptions.data.length>0"> Es Temporal</label>
	        </div> -->
	        <div class="form-group mb-md col-md-3 pl-xs col-sm-6">
	            <label class="control-label mb-xs"> Medio de pago </label>
	            <select class="form-control input-sm" ng-change="onChangeMedioPago();" ng-model="fProVenta.idmediopago" ng-options="item.id as item.descripcion for item in listaMedioPago" tabindex="105"> </select> 
	        </div>
            <div class="form-group mb-md pt-md mt-sm col-md-3 col-sm-12">
                <label><input type="checkbox" value="" ng-model="fProVenta.estemporal" tabindex="104" disabled="true"> Es Temporal</label>
            </div>
	       <!--  <div class="form-group mb-md col-md-3 col-sm-6">
	            <label class="control-label mb-n"> Tipo de precio </label>
	            <select class="form-control input-sm" ng-change="calcularTotales();" ng-model="fProVenta.tipoPrecio" ng-options="item as item.descripcion for item in listaPrecios" tabindex="106"> </select> 
	        </div>	 -->
		</div>
		
		<div class="well well-transparent boxDark col-xs-12 m-n">
            <div class="row">

	            <div class="form-group col-xs-12 m-n">
	              <label class="control-label m-n">Detalle del Pedido: </label>
	              <div ui-if="gridOptionsDetalleVenta.data.length>0" ui-grid="gridOptionsDetalleVenta" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive scroll-x-none" style="overflow: hidden;" ng-style="getTableHeight();"></div>
	            </div>
	            <div class="col-lg-9 col-md-7 col-xs-12">
                  	<div class="row" ng-show="fProVenta.total.length >= 1">
                    	<div class="form-inline mt-xs col-xs-12 text-right">
	                      <label class="control-label mr-xs " > <strong style="font-size: 22px;">ENTREGA</strong> </label> 
	                      <input ng-change="calcularVuelto();" type="number" class="form-control pull-right text-center" ng-model="fProVenta.entrega" tabindex="120" placeholder="S/." style="width: 200px; font-size: 20px;" /> 
	                    </div>
	                    <div class="form-inline mt-xs col-xs-12 text-right">
	                      <label class="control-label mr-xs" style=""> <strong style="font-size: 22px;">VUELTO</strong> </label> 
	                      <input id="vuelto" type="number" class="form-control pull-right text-center" disabled ng-model="fProVenta.vuelto" tabindex="122" placeholder="S/." style="width: 200px; font-size: 20px;"/> 
	                    </div>
                  	</div>
                </div>
                <div class="col-lg-3 col-md-5 col-xs-12">
                  	<div class="row">
	                    <div class="form-inline mt-xs col-xs-12 text-right">
	                      <label class="control-label mr-xs text-gray"> SUBTOTAL </label> 
	                      <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fProVenta.subtotal" placeholder="Subtotal" style="width: 200px;" /> 
	                    </div>
	                    <div class="form-inline mt-xs col-xs-12 text-right">
	                      <label class="control-label mr-xs text-gray"> I.G.V. </label> 
	                      <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fProVenta.igv" placeholder="I.G.V." style="width: 200px;" /> 
	                    </div>
	                    <div class="form-inline mt-xs col-xs-12 text-right">
	                      <label class="control-label mr-xs text-danger" style="font-size: 17px; font-weight: bolder;"> TOTAL </label> 
	                      <input id="temporalCantidad" type="text" class="form-control input-sm pull-right text-center" disabled ng-model="fProVenta.total" placeholder="Total" style="width: 200px; font-size: 17px; font-weight: bolder;"/> 
                    	</div>
                  	</div>
                </div>
	        </div>
		</div>
	</div>
</div> 
<div class="modal-footer"> 
    <div class="row">
      	<div class="col-sm-12 text-right">
	        <button class="btn-primary btn" ng-click="grabar(); $event.preventDefault();"> <i class="fa fa-save"> </i> [F2] Grabar </button>
	         <button class="btn-default btn" ng-click="nuevo(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F3] Nuevo </button>
	        <button class="btn-success btn" ng-click="imprimir(); $event.preventDefault();" ng-disabled="!isRegisterSuccess"> <i class="fa fa-print"> </i> [F4] Imprimir </button>
	        <button class="btn-danger btn" ng-click="cancel(); $event.preventDefault();" > <i class="ti ti-share-alt"> </i> Salir </button>
      	</div>
    </div>
</div> 