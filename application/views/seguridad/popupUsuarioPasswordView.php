<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formUsuario" novalidate>
    	<p class="help-block col-xs-12">Ingrese Usuario y Contraseña de Director Técnico de Farmacia</p>
    	<div class="form-group mb-md col-xs-12">
            <div class="input-group">                           
                <span class="input-group-addon">
                    <i class="fa fa-user"></i>
                </span>
                <input id="usuario" ng-model="fData.usuario" type="text" class="form-control" placeholder="Usuario" data-parsley-minlength="6" required focus-me enter-as-tab/>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-key"></i>
                </span>
                <input ng-model="fData.clave" type="password" class="form-control" id="exampleInputPassword1" placeholder="Clave" required ng-enter="aceptar()"/>
            </div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formUsuario.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button>
</div>