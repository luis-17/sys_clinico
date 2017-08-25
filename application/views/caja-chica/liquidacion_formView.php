<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
    <form class="row">

		<div class="form-group col-md-6 mb-md"> 
          	<label class="m-n">CAJA ABIERTA</label> 
          	<div class="input-group"> 
            	<p class="text-info m-xs">{{arr.cajaChica.nombre_caja}}</p>
           	</div>
        </div>

        <div class="form-group col-md-6 mb-md"> 
          	<label class="m-n">CENTRO DE COSTO</label> 
          	<div class="input-group"> 
            	<p class="text-info m-xs">{{arr.cajaChica.codigo_cc}}-{{arr.cajaChica.nombre_cc}}</p>
           	</div>
        </div>

        <div class="form-group col-md-6 mb-md"> 
          	<label class="m-n"># CHEQUE</label> 
          	<div class="input-group"> 
            	<p class="text-info m-xs">{{arr.cajaChica.numero_cheque}}</p>
           	</div>
        </div>
        <div class="form-group col-md-6 mb-md"> 
          	<label class="m-n">RESPONSABLE</label> 
          	<div class="input-group"> 
            	<p class="text-info m-xs">{{ fSessionCI.nombres + ' ' + fSessionCI.apellido_paterno + ' ' + fSessionCI.apellido_materno }}</p>
           	</div>
        </div>

        <div class="form-group col-md-4 mb-md text-center f-16"> 
          	<label class="m-n">FONDO FIJO: </label> 
          	<div class="text-center"> 
            	<p class="text-info m-xs f-18">{{ arr.cajaChica.monto_inicial }}</p>
           	</div>
        </div>
        <div class="form-group col-md-4 mb-md text-center f-16"> 
          	<label class="m-n">IMPORTE TOTAL: </label> 
          	<div class="text-center"> 
            	<p class="text-info m-xs f-18">S/.{{ gridOptionsES.sumTotal }}</p>
           	</div>
        </div>
        <div class="form-group col-md-4 mb-md text-center f-16"> 
          	<label class="m-n">SALDO: </label> 
          	<div class="text-center"> 
            	<p class="text-info m-xs f-18">{{ arr.cajaChica.saldo }}</p>
           	</div>
        </div>

		
    </form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ><i class="fa fa-save"> </i> LIQUIDAR LA CAJA </button>
    <button class="btn btn-warning" ng-click="cancel();">SALIR</button>
</div>