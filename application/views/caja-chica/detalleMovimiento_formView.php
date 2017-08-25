<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-xs">
  <form class="row"> 
		<div class="form-group col-md-3 mb-md"> 
      	<label class="m-n">CAJA</label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs">{{fData.nombre_caja}}</p>
       	</div>
    </div>
    <div class="form-group col-md-3 mb-md"> 
      	<label class="m-n">CENTRO DE COSTO</label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs">{{fData.codigo_cc}}-{{fData.nombre_cc}}</p>
       	</div>
    </div>
    <div class="form-group col-md-3 mb-md"> 
      	<label class="m-n"># CHEQUE</label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs">{{fData.numero_cheque}}</p>
       	</div>
    </div>
    <div class="form-group col-md-3 mb-md"> 
      	<label class="m-n">RESPONSABLE</label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs">{{ fData.responsable }}</p>
       	</div>
    </div>
    <div class="form-group col-md-3 mb-md"> 
      	<label class="m-n">FONDO FIJO: </label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs f-18">{{ fData.monto_inicial }}</p>
       	</div>
    </div>
    <div class="form-group col-md-3 mb-md"> 
      	<label class="m-n">IMPORTE TOTAL: </label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs f-18">S/. {{ fData.importe_total_numeric }}</p>
       	</div>
    </div>
    <div class="form-group col-md-3 mb-md"> 
      	<label class="m-n">SALDO: </label> 
      	<div class="input-group"> 
        	<p class="text-info m-xs f-18"> {{ fData.saldo }}</p>
       	</div>
    </div> 
    <div class="col-xs-12 pb">
      <div class="pull-left" >
        <button class="btn btn-info" type="button" ng-click='btnToggleFilteringDM();'>Buscar</button> 
      </div> 
      <div class="pull-right" ng-if="mySelectionGridDM.length == 1"> 
          <button type="button" class="btn btn-info" ng-click='btnAbrirConversacion(mySelectionGridDM[0]);'> <i class="fa fa-eye"> </i> CONVERSACIÓN </button> 
      </div>
    </div>
    <div class="col-xs-12">
      <div ui-grid="gridOptionsDetMov" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid">
        <div class="waterMarkEmptyData" ng-show="!gridOptionsDetMov.data.length"> No se encontraron datos. </div>
      </div>
    </div>  
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ><i class="fa fa-save"> </i> CERRAR CAJA</button> --> 
    <button class="btn btn-warning" ng-click="cancelDet1();">SALIR</button>
</div>