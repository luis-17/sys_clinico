<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="row" name="formUsuario" novalidate > 
    	<div class="form-group mb-md col-md-4">
			<label class="control-label mb-xs">Grupo <small class="text-danger">(*)</small> </label>
			<select required class="form-control input-sm"  ng-model="fDataUsuario.groupId" ng-options="item.id as item.descripcion for item in listaGrupos" focus-me ng-change="cambiarVistaSedeEmpresa();"></select>
		</div>
		<!-- <div class="form-group mb-md col-md-6" >
			<label class="control-label mb-xs">Sede <small class="text-danger">(*)</small> </label> 
			<div isteven-multi-select 
                input-model="listaSedes" helper-elements="filter none all"  output-model="fDataUsuario.sedes" button-label="icon name" item-label="icon name maker" tick-property="ticked" >
            </div>
		</div> -->
		<div class="form-group mb-md col-md-8">
			<label class="control-label mb-xs">Usuario <small class="text-danger">(*)</small> </label>
			<input required ng-minlength="4" type="text" class="form-control input-sm" ng-model="fDataUsuario.usuario" placeholder="Registre su usuario" />
		</div>
		<div class="form-group mb-md col-md-4" >
			<label class="control-label mb-xs" ng-if="boolForm == 'reg'">Clave <small class="text-danger">(*)</small> </label> 
			<input required ng-minlength="6" type="password" class="form-control input-sm" ng-model="fDataUsuario.clave" placeholder="Registre su clave" ng-if="boolForm == 'reg'" />
		</div>
		<div class="form-group mb-md col-md-8">
			<label class="control-label mb-xs">E-mail </label>
			<input type="email" class="form-control input-sm" ng-model="fDataUsuario.email" placeholder="Registre su correo electrónico" />
		</div>
		<div class="col-md-4" >
			<h5 class="mt-n mb-sm text-center">AGREGAR EMPRESA - SEDE </h5>
			<div class="row">
				<div class="form-group mb-md col-md-12" ng-show="fDataUsuario.siEmpresa">
					<label class="control-label mb-xs">Empresa </label> 
					<select class="form-control input-sm" ng-model="userTemporal.empresa" ng-options="item as item.descripcion for item in listaEmpresaAdmin" ng-change="cargarSedes(userTemporal.empresa)"> </select>
				</div>
				
<!-- <<<<<<< HEAD -->
				<div class="form-group mb-md col-xs-12" ng-if="fDataUsuario.siSedeDeEmpresa">
<!-- =======
				<div class="form-group mb-md col-xs-6" ng-if="fDataUsuario.siSedeDeEmpresa"> 
>>>>>>> refs/remotes/origin/master -->
					<label class="control-label mb-xs">Sede </label> 
					<select class="form-control input-sm" ng-model="userTemporal.sede" ng-options="item as item.descripcion for item in listaSede" > </select>
				</div>
				<div class="form-group mb-md col-xs-12" ng-if="fDataUsuario.siSoloSede">
					<label class="control-label mb-xs">Sede </label>
					<select class="form-control input-sm" ng-model="userTemporal.sede" ng-options="item as item.descripcion for item in listaSede" > </select>
				</div>
				<div class="col-md-12"> 
					<button ng-show="!editarSedeBool" type="button" class="btn btn-success" style="width: 100%;" ng-click="agregarSedeACesta();"> AGREGAR SEDE >>> </button>
					<button ng-show="editarSedeBool" type="button" class="btn btn-success" style="width: 100%;" ng-click="actualizarSede();"> ACTUALIZAR SEDE >>> </button>
				</div>
			</div>
		</div>
		<div class="col-md-8" >
            <div ui-if="gridOptionsEmpresaSede.data.length>0" ui-grid="gridOptionsEmpresaSede"ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid fs-mini-grid scroll-x-none">
				<div class="waterMarkEmptyData" ng-show="!gridOptionsEmpresaSede.data.length"> No se encontraron datos. </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formUsuario.$invalid">Guardar</button>
    <button class="btn btn-warning" ng-click="cancel()">Salir</button> 
</div>