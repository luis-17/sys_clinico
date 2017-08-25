<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
	<form name="formReceta" novalidate>
		<ul class="form-group demo-btns col-xs-12">
			<li class="form-group mr mt-sm col-sm-2 p-n" > <label> Nº de Solicitud <small class="text-danger">(*)</small></label> 
	            <div class="input-group col-sm-12 col-md-12" > 
	            	<input tabindex="1" type="text" class="form-control input-sm" ng-model="fBusqueda.idsolicitudformula" ng-required="true" focus-me ng-change="gridOptionsRecetaPreparados.data = []"/>
	            </div>
          	</li>
          	<!-- <li class="form-group mr mt-sm col-sm-2 p-n" ng-if="!fDataVenta.boolSolicitud"> <label> Nº de Ticket <small class="text-danger">(*)</small></label> 
	            <div class="input-group col-sm-12 col-md-12" > 
	            	<input tabindex="1" type="text" class="form-control input-sm" ng-model="fBusqueda.idsolicitudformula" ng-required="!fDataVenta.boolSolicitud" focus-me/>
	            </div>
          	</li> -->
          	<li class="form-group mr mt-lg col-sm-2 p-n"> 
                <div class="input-group" style=""> 
                  <button type="submit" class="btn btn-info btn-sm" ng-click="getPaginationRecetaPreparadosEnVentaServerSide(); $event.preventDefault();" ng-disabled="formReceta.$invalid" tabindex="2">
                      BUSCAR
                  </button>
                </div> 
            </li>
            <li class="form-group mr mt-sm col-sm-6 p-n"> <label class="text-bold"> Paciente: </label> <span>{{gridOptionsRecetaPreparados.data[0].paciente}}</span></li>
			  <!-- <li class="form-group mr mt-sm col-sm-3 p-n"> <label class="text-bold"> Nº Historia: </label> <span>{{gridOptionsRecetaPreparados.data[0].idhistoria}}</span></li> -->
			<li class="form-group mr mt-sm col-sm-4 p-n"> <label class="text-bold"> Num. Documento: </label> <span>{{gridOptionsRecetaPreparados.data[0].num_documento}}</span></li>
		</ul>
	</form>
    <div class="row">
		<div class="form-group mb-xs col-xs-12">
			<div ui-grid="gridOptionsRecetaPreparados" ui-grid-pagination class="grid table-responsive"></div> 
		</div>
		
		<div class="col-xs-12">
	        <div class="text-right">
	          <h3 class="mb-xs mt-xs lead"> TOTAL <strong style="font-weight: 400;" class="text-success"> : S/. {{ gridOptionsRecetaPreparados.sumTotal }} </strong> </h3>
	        </div>
	    </div>
		<div ng-if="!fDataVenta.boolSolicitud">    
		    <div class="col-xs-12">
		        <div class="text-right">
		          <h3 class="mt-n mb-xs lead"> PAGÓ A CUENTA <strong style="font-weight: 400;" class="text-success"> : S/. {{ numberFormat(gridOptionsRecetaPreparados.aCuenta, 2) }} </strong> </h3>
		        </div>
		    </div>
		    <div class="col-xs-12">
		        <div class="text-right">
		          <h2 class="mt-n mb-n"> SALDO <strong style="font-weight: 400;" class="text-success"> : S/. {{numberFormat((gridOptionsRecetaPreparados.sumTotal - gridOptionsRecetaPreparados.aCuenta),2)  }} </strong> </h2>
		        </div>
		    </div>
		</div>
		

	</div>
	
</div>
<div class="modal-footer">
    <button id="aceptar" class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsRecetaPreparados.data.length==0" tabindex="3">Agregar</button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="4">Salir</button>
</div>