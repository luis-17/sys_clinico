<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormModal }} </h4>
</div>
<div class="modal-body">
	<form class="row" name="formMotivoCancelacion">
		<div class="mb-n col-md-12" >
	        <div class="col-md-6 p-n">
	          <strong class="control-label mb-n">MOTIVO DE CANCELACIÓN Y/O REPROGRAMACIÓN: </strong>
	          <select class="form-control input-sm" ng-model="fDataListaPaciente.motivo"
                   style="width:100%;" ng-change=""
                   ng-options="item.descripcion for item in listaMotivo ">
                </select>
	        </div>

	        <div class="col-md-12 p-n">
	          <strong class="control-label mb-n">COMENTARIO: </strong>
	          <textarea class="form-control" rows="4" ng-model="fDataListaPaciente.descripcion_motivo" required ></textarea>
	        </div>
		</div>
	</form>	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" ng-click="btnCancelMotivo();">SALIR</button>
	<button type="button" class="btn btn-primary" ng-click="btnCancelar();" 
			ng-if="fDataListaPaciente.motivo.id == 1" 
			ng-disabled="formMotivoCancelacion.$invalid">CANCELAR
	</button>
	<button type="button" class="btn btn-primary" ng-click="btnOkMotivo();"
			ng-disabled="formMotivoCancelacion.$invalid || fDataListaPaciente.motivo.id == 0">CANCELAR Y REPROGRAMAR
	</button>
</div>
