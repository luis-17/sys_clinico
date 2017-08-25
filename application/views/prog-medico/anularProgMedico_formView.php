<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body" style="height:150px;">  
    <form  class="row" name="formAnulacionProgMedico"> 
    	<div class="form-group mb-md col-md-12">
			<label class="control-label mb-xs">	Motivo de anulación <small class="text-danger">(*)</small> </label>
			<textarea  class="form-control" ng-model="fDataAnular.comentario_anular"  placeholder="Ingrese comentario" required > </textarea>				
		</div>
    </form>
   </div>

   <div class="modal-footer"> 
    <button class="btn btn-primary" ng-click="guardarAnular();" ng-disabled="formAnulacionProgMedico.$invalid" >GUARDAR</button>
    <button class="btn btn-warning" ng-click="cancelAnular();">SALIR</button>
  </div>
</div>