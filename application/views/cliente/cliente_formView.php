<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
    <form class="" name="formCliente" novalidate>
    	<div class="row">
    		<div class="form-group mb-md col-md-4">
				<label class="control-label mb-xs"> DNI ó Documento de Identidad </label>
				<!-- <input type="text" class="form-control input-sm" ng-model="fData.num_documento" placeholder="Registre su dni" tabindex="1" focus-me ng-minlength="8" ng-pattern="/^[0-9]*$/" />  -->
				<input ng-init="verificaDNI();" type="text" class="form-control input-sm" ng-model="fData.num_documento" placeholder="Registre su dni" tabindex="1" focus-me ng-minlength="8" ng-pattern="/^[0-9]*$/" ng-change="verificaDNI();" /> 
			</div>
    		<div class="form-group mb-md col-md-4">
				<label class="control-label mb-xs">Nombres <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.nombres" placeholder="Registre su nombre" required tabindex="2" />
			</div>
			<div class="form-group mb-md col-md-4">
				<label class="control-label mb-xs">Apellido Paterno <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.apellido_paterno" placeholder="Registre su apellido paterno" required tabindex="3" />
			</div>
    	</div>
    	<div class="row">
			<div class="form-group mb-md col-md-4">
				<label class="control-label mb-xs">Apellido Materno <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.apellido_materno" placeholder="Registre su apellido materno" required tabindex="4" /> 
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Teléfono Móvil <small class="text-danger">(*)</small> </label>
				<input type="tel" class="form-control input-sm" ng-model="fData.celular" placeholder="Registre su celular" ng-pattern="/^[0-9]{9}$/" required ng-minlength="9" ng-maxlength="9" tabindex="5" />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Teléfono Casa  </label>
				<input type="tel" class="form-control input-sm" ng-model="fData.telefono" placeholder="Registre su teléfono" ng-pattern="/^[0-9]{7}$/" ng-minlength="7" ng-maxlength="7" tabindex="6" />
			</div>			
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs"> Asignar Empresa <small style="color: #a9a9a9;">(Salud Ocupacional)</small> </label>
				<div class="input-group">
					<span class="input-group-btn ">
						<input disabled type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idempresacliente" placeholder="ID" min-length="2" />
					</span>
					<input type="text" class="form-control input-sm" ng-model="fData.empresacliente" placeholder="Ingrese el texto para autocompletar." ng-change="getClearInputEmpresaCliente();" 
						typeahead-loading="loadingLocationsEmpresa" uib-typeahead="item as item.descripcion for item in getEmpresaClienteAutocomplete($viewValue)" 
						typeahead-on-select="getSelectedEmpresaCliente($item, $model, $label)" typeahead-min-length="2" tabindex="7"/>
				</div> 
				<i ng-show="loadingLocationsEmpresa" class="fa fa-refresh"></i>
                <div ng-show="noResultsLD">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
			</div>
		</div>
		<div class="row">
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs">E-mail</label>
				<input type="email" class="form-control input-sm" ng-model="fData.email" placeholder="Registre su e-mail" tabindex="7" />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Fecha de Nacimiento <small class="text-danger">(*)</small> </label>  
				<div class="input-group"> 
					<input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fData.fecha_nacimiento" required tabindex="8"/> 
				</div>
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs"> Salud Ocupacional </label>  
				<label class=""> 
					<input type="checkbox" ng-disabled="disabledPSO" class="" ng-model="fData.pertenece_salud_ocup" ng-false-value="2" ng-true-value="1" tabindex="9"/> 
					¿Asigna a Salud Ocup.?
				</label>
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="block" style="margin-bottom: 4px;"> Sexo <small class="text-danger">(*)</small> </label>
				<select class="form-control input-sm" ng-model="fData.sexo" ng-options="item.id as item.descripcion for item in listaSexos" tabindex="10" required > </select>
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="block" style="margin-bottom: 4px;"> Tipo de cliente </label> 
				<select class="form-control input-sm" ng-model="fData.idtipocliente" ng-options="item.id as item.descripcion for item in listaTiposClientes" tabindex="11" ng-disabled="boolExterno"  > </select>
			</div>
		</div>
		<div class="row">
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs"> Departamento <small class="text-danger">(*)</small> </label>
				<div class="input-group">
					<span class="input-group-btn">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.iddepartamento" placeholder="ID" tabindex="12" ng-change="obtenerDepartamentoPorCodigo(); $event.preventDefault();limpiaDpto();" min-length="2" required/>
					</span>
					<input id="fDatadepartamento" type="text" class="form-control input-sm" ng-model="fData.departamento" placeholder="Ingrese el Departamento o Click en Seleccionar" typeahead-loading="loadingLocationsDpto" uib-typeahead="item as item.descripcion for item in getDepartamentoAutocomplete($viewValue)" typeahead-on-select="getSelectedDepartamento($item, $model, $label)" typeahead-min-length="2" ng-change="limpiaIdDpto();" tabindex="13" required/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaDptos('md')">Seleccionar</button>
					</span>
				</div>
				<i ng-show="loadingLocationsDpto" class="fa fa-refresh"></i>
                <div ng-show="noResultsLD">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
			</div>
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs"> Provincia <small class="text-danger">(*)</small> </label>
				<div class="input-group">
					<span class="input-group-btn ">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idprovincia" placeholder="ID" tabindex="14" ng-change="obtenerProvinciaPorCodigo(); $event.preventDefault();limpiaProv();" min-length="2" required/>
					</span>
					<input id="fDataprovincia" type="text" class="form-control input-sm" ng-model="fData.provincia" placeholder="Ingrese la Provincia o Click en Seleccionar" typeahead-loading="loadingLocationsProv" 
                  uib-typeahead="item as item.descripcion for item in getProvinciaAutocomplete($viewValue)" typeahead-on-select="getSelectedProvincia($item, $model, $label)" typeahead-min-length="2" ng-change="limpiaIdProv();" tabindex="15" required/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaProvincias('md')">Seleccionar</button>
					</span>
				</div>
				<i ng-show="loadingLocationsProv" class="fa fa-refresh"></i>
                <div ng-show="noResultsLP">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
			</div>
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs"> Distrito <small class="text-danger">(*)</small> </label>
				<div class="input-group">
					<span class="input-group-btn ">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.iddistrito" placeholder="ID" tabindex="16" ng-change="obtenerDistritoPorCodigo(); $event.preventDefault();limpiaDist();" min-length="2" required />
					</span>
					<input id="fDatadistrito" type="text" class="form-control input-sm" ng-model="fData.distrito" placeholder="Ingrese el Distrito o Click en Seleccionar"  typeahead-loading="loadingLocationsDistr" uib-typeahead="item as item.descripcion for item in getDistritoAutocomplete($viewValue)" typeahead-on-select="getSelectedDistrito($item, $model, $label)" typeahead-min-length="2"  ng-change="limpiaIdDist();"tabindex="17" required/>
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaDistritos('md')">Seleccionar</button>
					</span>
				</div>
				<i ng-show="loadingLocationsDistr" class="fa fa-refresh"></i>
                <div ng-show="noResultsLDis">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
			</div>
		</div>
		<div class="row">
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Tipo de Via</label>
				<select class="form-control input-sm" ng-model="fData.idtipovia" ng-options="item.id as item.descripcion for item in listaTipoVias" tabindex="18" > </select>
			</div>
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs">Nombre de Vía </label>
				<input type="text" class="form-control input-sm" ng-model="fData.nombre_via" placeholder="Registre su vía" tabindex="19"  />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Tipo de Zona</label>
				<select class="form-control input-sm" ng-model="fData.idtipozona" ng-options="item.id as item.descripcion for item in listaTipoZonas" tabindex="20"> </select>
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs"> Zona </label>
				<!-- <div class="input-group"> -->
					<!-- <span class="input-group-btn ">
						<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idzona" placeholder="ID" readonly="true" />
					</span> -->
					<input id="fDatazona" type="text" class="form-control input-sm" ng-model="fData.zona" placeholder="" typeahead-loading="loadingLocationsZona" uib-typeahead="item as item.descripcion for item in getZonaAutocomplete($viewValue)" typeahead-on-select="getSelectedZona($item, $model, $label)" typeahead-min-length="2" tabindex="21" />
					<!-- <span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaZonas('md')">Seleccionar</button>
					</span> -->
				<!-- </div> -->
				<i ng-show="loadingLocationsZona" class="fa fa-refresh"></i>
                <div ng-show="noResultsLZona">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Procedencia <small class="text-danger">(*)</small> </label>
				<select class="form-control input-sm" ng-model="fData.idprocedencia" ng-options="item.id as item.descripcion for item in listaProcedencias" tabindex="22" required> </select>
			</div>
		</div>
		<div class="row">
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Número</label>
				<input type="text" class="form-control input-sm" ng-model="fData.numero" placeholder="N° correspondiente a domicilio" tabindex="23" />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Interior</label>
				<input type="text" class="form-control input-sm" ng-model="fData.interior" placeholder="Número/Letra interior" tabindex="24" />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">N° Departamento</label>
				<input type="text" class="form-control input-sm" ng-model="fData.numero_departamento" placeholder="Número/Letra departamento" tabindex="25" />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Sector</label>
				<input type="text" class="form-control input-sm" ng-model="fData.sector" placeholder="Sector" tabindex="26" />
			</div>
			<div class="form-group mb-md col-md-1" >
				<label class="control-label mb-xs">Grupo</label>
				<input type="text" class="form-control input-sm" ng-model="fData.grupo" placeholder="Grupo" tabindex="27" />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Manzana</label>
				<input type="text" class="form-control input-sm" ng-model="fData.manzana" placeholder="Indique la manzana." tabindex="28" />
			</div>
			<div class="form-group mb-md col-md-1" >
				<label class="control-label mb-xs">Lote</label>
				<input type="text" class="form-control input-sm" ng-model="fData.lote" placeholder="Indique el Lote" tabindex="29" />
			</div>

		</div>
		<div class="row">
			<div class="form-group mb-md col-md-6" >
				<label class="control-label mb-xs">Dirección</label>
				<div class="input-group">
					<input type="text" class="form-control input-sm" ng-model="fData.direccion" placeholder="La dirección se genera automáticamente" disabled />
					<span class="input-group-btn">
						<button class="btn btn-default btn-sm" type="button" ng-click="generateDireccion(fData.idtipovia,fData.idtipozona)" tabindex="30" >Generar</button>
					</span>
				</div>
			</div>
			<div class="form-group mb-md col-md-6" >
				<label class="control-label mb-xs">Referencia</label>
				<textarea class="form-control input-sm" ng-model="fData.referencia" placeholder="Registre su referencia" tabindex="31" > </textarea>
			</div>
			<div class="form-group mb-md col-md-6" style="margin-top: -30px;" >
				<div class="alert alert-info p-sm m-n">
					<strong class="block text-center">INFORMACION DE UBIGEO</strong>
					<div class="block">
						<span > Departamento de Lima: </span> <label class="label label-success m-n">14</label>
					</div>
					<div class="block">
						<span > Provincia de Lima: </span> <label class="label label-success m-n">01</label>
					</div>
					<div class="block">
						<span > Distrito de Villa el Salvador: </span> <label class="label label-success m-n">42</label>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-success" ng-click="imprimir()" tabindex="28" > <i class="fa fa-print"></i> Imprimir </button> -->
    <button class="btn btn-primary" ng-click="verificarCli(); $event.preventDefault();" ng-disabled="formCliente.$invalid" tabindex="32" > Aceptar </button>
    <button class="btn btn-warning" ng-click="cancel()" tabindex="32"> Cancelar </button>
</div>
