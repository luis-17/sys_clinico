<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body" style="top: -10px;">  
    <form class="row" name="formNotificacionEvento">
      <div ng-if="!boolExterno || (boolExterno && !boolRepeat)" class="mb-n col-md-12" >
        <div class="row">
          <div class="col-md-6" >
            <strong class="control-label mb-n">TEXTO NOTIFICACIÓN: </strong>
            <p class="help-block m-n"> 
              <textarea class="form-control" rows="4" ng-model="fData.texto_notificacion" required ></textarea>
            </p>        
          </div>
          <div class="col-md-4" >
            <div class="form-group">
              <strong for="checkbox" class=" col-sm-5 control-label mb-n">GRUPOS DE USUARIOS: </strong>
              <div class="col-sm-6">
                <div ng-repeat="grupo in fData.listaGrupos" class="checkbox block">
                  <label><input type="checkbox"  
                          ng-true-value="true" 
                          ng-false-value="false" 
                          ng-model="grupo.checked">{{grupo.descripcion}}</label>
                </div>
              </div>        
            </div>        
          </div>
          <div class="col-md-2" >
            <div class="form-group">
              <strong for="checkbox" class=" col-sm-2 control-label mb-n">ESTADO: </strong>
              <div class="col-sm-10">
                 <label>
                  <input type="radio" ng-model="fData.estado_ce" value="1">
                  Visible
                </label><br/>
                <label>
                  <input type="radio" ng-model="fData.estado_ce" value="2">
                  Oculta
                </label>
              </div>
            </div>
          </div>          
        </div>        
      </div> 

      <div ng-if="boolExterno && boolRepeat" ng-repeat="(key, fData) in fDataLista" class="col-md-12" style="padding-bottom:10px;">
        <div class="row" ng-if="fData.visible">
          <div class="mb-n col-md-12" >
            <div class="row">
              <div class="col-md-6" >
                <strong class="control-label mb-n">TEXTO NOTIFICACIÓN: </strong>
                <p class="help-block m-n"> 
                  <textarea class="form-control" rows="4" ng-model="fData.texto_notificacion" required ></textarea>
                </p>        
              </div>
              <div class="col-md-4" >
                <div class="form-group">
                  <strong for="checkbox" class=" col-sm-5 control-label mb-n">GRUPOS DE USUARIOS: </strong>
                  <div class="col-sm-6">
                    <div ng-repeat="grupo in fData.listaGrupos" class="checkbox block">
                      <label><input type="checkbox"  
                              ng-true-value="true" 
                              ng-false-value="false" 
                              ng-model="grupo.checked">{{grupo.descripcion}}</label>
                    </div>
                  </div>        
                </div>        
              </div>
              <div class="col-md-2" >
                <div class="form-group">
                  <strong for="checkbox" class=" col-sm-2 control-label mb-n">ESTADO: </strong>
                  <div class="col-sm-10">
                     <label>
                      <input type="radio" ng-model="fData.estado_ce" value="1">
                      Visible
                    </label><br/>
                    <label>
                      <input type="radio" ng-model="fData.estado_ce" value="2">
                      Oculta
                    </label>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button ng-if ="boolExterno" class="btn btn-primary" ng-click="btnGenerarNotificacionItem(key,fData); $event.preventDefault();" >Guardar</button>
            </div>
            </div>        
          </div>
        </div>
      </div> 


    </form>
  </div>
    
  <div class="modal-footer">
      <button ng-if ="!boolExterno" class="btn btn-primary" ng-click="btnGenerarNotificacion(); $event.preventDefault();" ng-disabled="formNotificacionEvento.$invalid ">Guardar</button>
      <button ng-if ="boolExterno" class="btn btn-primary" ng-click="btnGenerarNotificacion(); $event.preventDefault();" ng-disabled="formNotificacionEvento.$invalid ">Guardar Todo</button>
      <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
  </div>
</div>