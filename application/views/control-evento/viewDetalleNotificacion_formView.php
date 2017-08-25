<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body" style="top: -10px;">  
    <form class="row" name="formDetalleNotificacionEvento">
      <div class="mb-n col-md-12" >
        <div class="row">
          <div class="col-md-12" >
            <strong class="control-label mb-n">TEXTO NOTIFICACIÓN: </strong>
            <p class="help-block m-n"> 
              <textarea class="form-control" rows="4" ng-model="fData.texto_notificacion" disabled ></textarea>
            </p>        
          </div>         
        </div>        
      </div> 
      <div class="mb-n col-md-12" >
        <div class="row">
          <div class="col-md-4" >
            <strong class="control-label mb-n">FECHA NOTIFICACIÓN: </strong>
            <p class="help-block m-n"><i class="fa fa-clock-o" style="margin-right: 4px;"></i>{{fData.fecha}} </p>       
          </div>
          <div class="col-md-5" >
            <strong class="control-label mb-n">RESPONSABLE: </strong>
            <p class="help-block m-n">{{fData.responsable}} </p>       
          </div>          
        </div>        
      </div>

      <div class="col-md-12 " style="margin-top:10px;" ng-if="fData.identificador != null">
        <div class="row">
          <div class="col-md-4 pull-right">
            <button type="button" class="btn btn-sm btn-primary ml-xs" ng-click="viewDetalleProgramacion(fData); $event.preventDefault();" > 
              <i class="fa fa-info-circle" style="    margin-right: 4px;" ></i> Ver detalle programación</button>   
          </div>                    
        </div>        
      </div> 

    </form>
  </div>
    
  <div class="modal-footer">
      <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
  </div>
</div>