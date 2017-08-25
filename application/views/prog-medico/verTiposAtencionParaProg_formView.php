<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormModal }} </h4>
</div>
<div class="modal-body">
	<form class="row" name="formTipoAtencionParaProg">
		<div class="mb-n col-md-12 text-center" >
			<label style="font-size: 22px; "> <input type="radio" ng-model="tipoAtencion" value="CM" /> CONSULTA </label> 
		    <label style="font-size: 22px;margin-left: 6%;"> <input type="radio" ng-model="tipoAtencion" value="P" /> PROCEDIMIENTO </label> 
		</div>
	</form>	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" ng-click="modalCancel();">SALIR</button> 
	<button type="button" class="btn btn-primary" ng-click="modalAceptar();"> SIGUIENTE </button>
</div>
