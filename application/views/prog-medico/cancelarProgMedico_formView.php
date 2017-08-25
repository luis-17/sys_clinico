<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body" style="height:150px;">  
    <form  class="row" name="formCancelProgMedico"> 
    	<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">	Motivo de cancelación <small class="text-danger">(*)</small> </label>
			<textarea  class="form-control" ng-model="fDataCancelar.comentario_cancelar"  placeholder="Ingrese comentario" required > </textarea>				
		</div>
    </form>
   </div>

   <div class="modal-footer"> 
    <button class="btn btn-primary" ng-click="guardarCancelar();" ng-disabled="formCancelProgMedico.$invalid" >GUARDAR</button>
    <button class="btn btn-warning" ng-click="cancelar();">SALIR</button>
  </div>
</div>