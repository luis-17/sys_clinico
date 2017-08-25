<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<form id="formEmpleado" name="formEmpleado" novalidate> 
		<h4 class="m-n pb-sm mb-sm" style="border-bottom: 1px solid #e0e0e0;"> 
			<a class="text-info block" href="" ng-click="collapse.toggleEmpleado('DG');">Datos Generales 
			<span class="btn btn-default btn-sm button-icon has-bg pull-right"> <i class="ti ti-angle-down"></i> </span></a> 
		</h4>
	  	<div uib-collapse="collapse.isCollapsedDG !== collapse.collapsedAbv" class="row pt-md mb" style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
	  		<div class="form-group mb-md col-md-2"  id="topModal" name="topModal">
				<label class="control-label mb-xs">Tipo Documento </label> 
				<select style="width: 170px; margin-right: 8px;" class="form-control input-sm" ng-model="fData.tipoDocumento" 
					ng-options="item as item.descripcion for item in listaTipoDocumento" ></select>
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Nº Documento <small class="text-danger">(*)</small>  </label> 
				<input type="text" class="form-control input-sm" ng-model="fData.num_documento" placeholder="Registre su Nº de doc." focus-me ng-minlength="fData.tipoDocumento.longitud" ng-pattern="/^[0-9]*$/" ng-change="verificaDNI();" required />
			</div>
			<!--
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs"> Carnet Extranjería </label>
				<input type="text" class="form-control input-sm" ng-model="fData.carnet_extranjeria" placeholder="Extranjería" />
			</div>-->
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Cod. Essalud </label>
				<input type="text" class="form-control input-sm" ng-model="fData.codigo_essalud" placeholder="Cod. de ESSALUD" />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Nombres <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.nombres" placeholder="Registre su nombre" required />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Apellido Paterno <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.apellido_paterno" placeholder="Registre su apellido paterno" required />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Apellido Materno <small class="text-danger">(*)</small> </label>
				<input type="text" class="form-control input-sm" ng-model="fData.apellido_materno" placeholder="Registre su apellido materno" required />
			</div>
			<div class="col-md-12" >
				<div class="row">
					<div class="form-group mb-md col-sm-2">
						<label class="control-label mb-xs">Foto del Empleado </label>
						<div class="fileinput fileinput-new" data-provides="fileinput" style="width: 100%;">
							<div class="fileinput-preview thumbnail mb20" data-trigger="fileinput" style="width: 100%; text-align: center;">
								<img ng-if="fData.nombre_foto" ng-src="{{ dirImages + 'dinamic/empleado/' + fData.nombre_foto }}" />
							</div>
							<div>
								<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a> 
								<span class="btn btn-default btn-file"><span class="fileinput-new">Seleccionar imagen</span> 
									<span class="fileinput-exists">Cambiar</span> 
									<input type="file" name="file" file-model="fData.fotoEmpleado" /> 
								</span>
							</div>
						</div>
					</div>
					<div class="col-sm-10">
						<div class="row">
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs"> Departamento </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.iddepartamento" placeholder="ID" ng-change="obtenerDepartamentoPorCodigo(); $event.preventDefault();" min-length="2" />
									</span>
									<input autocomplete="off" id="fDatadepartamento" type="text" class="form-control input-sm" ng-model="fData.departamento" placeholder="Ingrese el Departamento" typeahead-loading="loadingLocationsDpto" uib-typeahead="item as item.descripcion for item in getDepartamentoAutocomplete($viewValue)" typeahead-on-select="getSelectedDepartamento($item, $model, $label)" typeahead-min-length="2" />
									
								</div>
								<i ng-show="loadingLocationsDpto" class="fa fa-refresh"></i>
				                <div ng-show="noResultsLD">
				                  <i class="fa fa-remove"></i> No se encontró resultados 
				                </div>
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs"> Provincia </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idprovincia" placeholder="ID" ng-change="obtenerProvinciaPorCodigo(); $event.preventDefault();" min-length="2" />
									</span>
									<input autocomplete="off" id="fDataprovincia" type="text" class="form-control input-sm" ng-model="fData.provincia" placeholder="Ingrese la Provincia"   typeahead-loading="loadingLocationsProv" 
				                  uib-typeahead="item as item.descripcion for item in getProvinciaAutocomplete($viewValue)" typeahead-on-select="getSelectedProvincia($item, $model, $label)" typeahead-min-length="2" />
									
								</div>
								<i ng-show="loadingLocationsProv" class="fa fa-refresh"></i>
				                <div ng-show="noResultsLP">
				                  <i class="fa fa-remove"></i> No se encontró resultados 
				                </div>
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs"> Distrito </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.iddistrito" placeholder="ID" ng-change="obtenerDistritoPorCodigo(); $event.preventDefault();" min-length="2" />
									</span>
									<input autocomplete="off" id="fDatadistrito" type="text" class="form-control input-sm" ng-model="fData.distrito" placeholder="Ingrese el Distrito"  typeahead-loading="loadingLocationsDistr" uib-typeahead="item as item.descripcion for item in getDistritoAutocomplete($viewValue)" typeahead-on-select="getSelectedDistrito($item, $model, $label)" typeahead-min-length="2" />
									
								</div>
								<i ng-show="loadingLocationsDistr" class="fa fa-refresh"></i>
				                <div ng-show="noResultsLDis">
				                  <i class="fa fa-remove"></i> No se encontró resultados 
				                </div>
							</div>
						</div>
						<div class="row">
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs">Dirección</label>
								<input type="text" class="form-control input-sm" ng-model="fData.direccion" placeholder="Registre su dirección" />
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs">Referencia</label>
								<input type="text" class="form-control input-sm" ng-model="fData.referencia" placeholder="Referencia" />
							</div>
							<div class="form-group mb-md col-md-2" style="padding-right: 5px;">
								<label class="control-label mb-xs">Banco</label>
								<select class="form-control input-sm" ng-model="fData.banco" ng-options="item as item.descripcion for item in listaBancos" ></select>
							</div>
							<div class="form-group mb-md col-md-2" >
								<label class="control-label mb-xs">Cta. Corriente</label>
								<input type="text" class="form-control input-sm" ng-model="fData.cuenta_corriente" placeholder="Registre cuenta" />
							</div>
						</div>
						<div class="row">
							<div class="form-group mb-md col-md-4">
								
								<div class="orm-group mb-md col-md-6" style="padding-left: 0; padding-right: 5px;">
									<label class="control-label mb-xs">Teléfono Móvil</label> 	 
									<input type="tel" class="form-control input-sm" ng-model="fData.telefono" placeholder="Registre su teléfono" ng-minlength="6" ng-pattern="/^[# *]?\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{3})$/"/>
								</div>
								<div class="orm-group mb-md col-md-6" style="padding-left: 5px; padding-right: 0;">
									<label class="control-label mb-xs">Teléfono Fijo</label>
									<input type="tel" class="form-control input-sm" ng-model="fData.telefono_fijo" placeholder="Registre su teléfono fijo" ng-minlength="7" />
								</div>
							</div>
							<div class="form-group mb-md col-md-2" style="padding-right: 5px;">
								<label class="control-label mb-xs">Sexo</label>
								<select class="form-control input-sm" ng-model="fData.sexo" ng-options="item.id as item.descripcion for item in listaSexo" ></select>
							</div>
							<div class="form-group mb-md col-md-2" style="padding-left: 5px;">
								<label class="control-label mb-xs">Estado Civil </label>
								<select class="form-control input-sm" ng-model="fData.estado_civil" ng-options="item.id as item.descripcion for item in listaEstadoCivil" > </select>
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs">E-mail</label>
								<input type="email" class="form-control input-sm" ng-model="fData.email" placeholder="Registre su e-mail" />
							</div>
						</div>
						<div class="row">
							<div class="form-group mb-md col-md-4" > 
								<label class="control-label mb-xs"> Asignar Sede <small class="text-danger">(*)</small> </label> 
								<div class="input-group"> 
									<span class="input-group-btn "> 
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idsede" placeholder="ID" readonly="true" /> 
									</span> 
									<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fData.sede" placeholder="Registre sede del empleado" typeahead-loading="loadingLocationsSede" ng-change="getClearInputSede();" 
										typeahead="item as item.descripcion for item in getSedeAutocomplete($viewValue)" typeahead-on-select="getSelectedSede($item, $model, $label)" required/> 
								</div>
								<i ng-show="loadingLocationsSede" class="fa fa-refresh"></i>
					            <div ng-show="noResultsSede"> <i class="fa fa-remove"></i> No se encontró resultados </div> 
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs"> Asignar Empresa </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idempresa" placeholder="ID" readonly="true" />
									</span>
									<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fData.empresa" placeholder="Registre la empresa del empleado" typeahead-loading="loadingLocations" ng-change="getClearInputEmpresa();" 
										typeahead="item as item.descripcion for item in getEmpresaAutocomplete($viewValue)" typeahead-on-select="getSelectedEmpresa($item, $model, $label)" ng-disabled="boolExterior"/> 
								</div>
								<i ng-show="loadingLocations" class="fa fa-refresh"></i>
					            <div ng-show="noResultsEmpresa"> <i class="fa fa-remove"></i> No se encontró resultados </div> 
							</div>
							<div class="form-group mb-md col-md-4" > 
								<label class="control-label mb-xs"> Asignar Consultorio </label> 
								<div class="input-group"> 
									<span class="input-group-btn "> 
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idespecialidad" placeholder="ID" readonly="true" /> 
									</span> 
									<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fData.soloEspecialidad" placeholder="Opcional" typeahead-loading="loadingLocations" ng-change="getClearInputSoloEspecialidad();" 
										typeahead="item as item.descripcion for item in getSoloEspecialidadAutocomplete($viewValue)" typeahead-on-select="getSelectedSoloEspecialidad($item, $model, $label)"/> 
								</div>
								<i ng-show="loadingLocations" class="fa fa-refresh"></i>
					            <div ng-show="noResultsSoloEspecialidad"> <i class="fa fa-remove"></i> No se encontró resultados </div> 
							</div>
						</div>
						
					</div>
				</div>
			</div>
			<div class="col-md-12" >
				<div class="row">
					<div class="col-sm-8">
						<div class="row">
							<div class="form-group mb-md col-md-3" >
								<label class="control-label mb-xs">Fecha de Nac. </label>
								<div class="input-group" style="width: 100%;"> 
									<input type="text" class="form-control input-sm datepicker" ng-model="fData.fecha_nacimiento" data-inputmask="'alias': 'dd-mm-yyyy'"  placeholder="dd-mm-yyyy" />
								</div>
							</div>
							<div class="form-group mb-md col-md-5" >
								<label class="control-label mb-xs"> Cat/SubCategoria </label>
								<select class="form-control input-sm" ng-model="fData.idsubcatcentrocosto" ng-options="item.id as item.descripcion for item in listaSubCatCentroCosto" ng-change="cargarCentroCosto(fData.idsubcatcentrocosto,true);"> </select> 
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs"> Centro Costo  </label>
								<select class="form-control input-sm" ng-model="fData.idcentrocosto" ng-options="item.id as item.descripcion for item in listaCentroCosto" > </select> 
							</div>
							

							<!-- <div class="form-group mb-md col-md-3" >
								<label class="control-label mb-xs">Asignar Área </label>
								<select class="form-control input-sm" ng-model="fData.area_empresa" ng-options="item as item.descripcion for item in listaAreaEmpresa" > </select> 
							</div> -->
							<div class="form-group mb-md col-md-3">
								<label class="control-label mb-xs"> Condición Laboral </label>
								<select class="form-control input-sm" ng-model="fData.condicion_laboral" ng-options="item.id as item.descripcion for item in listaCondicionLaboral" ng-change="clearSelectCondLaboral()"> </select>
							</div>

							<div class="form-group mb-md col-md-5" >
								<label class="control-label mb-xs"> Asignar Cargo <small class="text-danger">(*)</small> </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idcargo" placeholder="ID" readonly="true" required />
									</span>
									<input autocomplete="off" ng-change="getClearInputCargo();" type="text" class="form-control input-sm" ng-model="fData.cargo" placeholder="Ingrese el cargo" typeahead-loading="loadingLocations" uib-typeahead="item as item.descripcion for item in getCargoAutocomplete($viewValue)" typeahead-on-select="getSelectedCargo($item, $model, $label)"/> 
								</div>
								<i ng-show="loadingLocations" class="fa fa-refresh"></i>
					            <div ng-show="noResultsLCargo"> <i class="fa fa-remove"></i> No se encontró resultados </div>
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs"> Asignar Profesion <small class="text-danger">(*)</small> </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idprofesion" placeholder="ID" readonly="true" required />
									</span>
									<input autocomplete="off" ng-change="getClearInputProfesion();" type="text" class="form-control input-sm" ng-model="fData.profesion" placeholder="Ingrese Profesión" typeahead-loading="loadingLocationsProf" uib-typeahead="item as item.descripcion for item in getProfesionAutocomplete($viewValue)" typeahead-on-select="getSelectedProfesion($item, $model, $label)" required /> 
								</div>
								<i ng-show="loadingLocationsProf" class="fa fa-refresh"></i>
					            <div ng-show="noResultsProfesion"> <i class="fa fa-remove"></i> No se encontró resultados </div>
							</div>

							

							<!-- <div class="form-group mb-md col-md-3" >
								<label class="control-label mb-xs">Centro Atención ESSALUD</label>
								<input type="text" class="form-control input-sm" ng-model="fData.centro_essalud" placeholder="Centro de ESSALUD" />
							</div> -->
							<div class="form-group mb-md col-md-3" >
								<label class="control-label mb-xs">RUC</label>
								<input type="text" class="form-control input-sm" ng-model="fData.ruc" placeholder="R.U.C." />
							</div>
							<div class="form-group mb-md col-md-5" >
								<label class="control-label mb-xs"> Asignar Cargo del Superior </label>
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idcargosup" placeholder="ID" readonly="true" />
									</span>
									<input autocomplete="off" ng-change="getClearInputCargoSup();" type="text" class="form-control input-sm" ng-model="fData.cargo_sup" placeholder="Ingrese Cargo Jefe Inmediato" typeahead-loading="loadingLocationsCargoSup" 
										uib-typeahead="item as item.descripcion for item in getCargoSupAutocomplete($viewValue)" typeahead-on-select="getSelectedCargoSup($item, $model, $label)" /> 
								</div>
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs">Gr. Sanguíneo</label>
								<input type="text" class="form-control input-sm" ng-model="fData.grupo_sanguineo" placeholder="Grupo Sanguíneo" />
							</div>
							<!-- <div class="form-group mb-md col-md-3" >
								<label class="control-label mb-xs">S. Básico</label> 
								<input type="text" class="form-control input-sm" ng-model="fData.salario_basico" placeholder="Ingrese Salario Básico" />
							</div> -->
							<div class="form-group mb-md col-md-3">
								<label class="control-label mb-xs"> Reg. Pensionario <small class="text-danger" ng-if="(fData.condicion_laboral == 'EN PLANILLA')">(*)</small></label> 
								<select class="form-control input-sm" ng-model="fData.reg_pensionario" ng-options="item.id as item.descripcion for item in listaRegPensionario" ng-disabled="!(fData.condicion_laboral == 'EN PLANILLA')" ng-change="clearSelectRegPensionario()"></select>
							</div>
							
							<div class="form-group mb-md col-md-2">
								<label class="control-label mb-xs"> # CUSPP <small class="text-danger" ng-if="(fData.reg_pensionario == 'AFP')">(*)</small></label>
								<input type="text" class="form-control input-sm" ng-model="fData.cuspp" ng-disabled="!(fData.reg_pensionario == 'AFP')" placeholder="Ingrese CUSPP" />
							</div>
							<div class="form-group mb-md col-md-3">
								<label class="control-label mb-xs"> AFP <small class="text-danger" ng-if="(fData.reg_pensionario == 'AFP')">(*)</small></label> 
								<select class="form-control input-sm" ng-model="fData.afp" ng-options="item as item.descripcion for item in listaAFP" ng-disabled="!(fData.reg_pensionario == 'AFP')" > </select>
							</div>
							<div class="form-group mb-md col-md-4" >
								<label class="control-label mb-xs">Comisión de AFP <small class="text-danger" ng-if="(fData.reg_pensionario == 'AFP')">(*)</small></label>
								<select class="form-control input-sm" ng-model="fData.comision_afp" ng-options="item as item.descripcion for item in listComisionAFP" ng-disabled="!(fData.reg_pensionario == 'AFP')" > 
								</select>
							</div>
							<div class="form-group mb-md col-md-3" >
								<label class="control-label mb-xs">Remuneración Dada (S/.)</label>
								<input type="text" class="form-control input-sm" ng-model="fData.salario_basico" placeholder="Remuneración Dada" />
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="row">
							<div class="form-group mb-md col-md-12" ng-show="fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_rrhh' || fSessionCI.key_group == 'key_rrhh_asistente' || fSessionCI.key_group == 'key_gerencia'" > 
								<label class="control-label mb-xs">Asignar un <a href="" ng-click="nuevoUsuario('md')">Usuario</a></label> 
								<div class="input-group">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idusuario" placeholder="ID" readonly="true" />
									</span>
									<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fData.usuario" ng-enter="verPopupListaUsuarios('md')" placeholder="Presione ENTER o Seleccionar" ng-change="fData.idusuario=null"/>
									<span class="input-group-btn">
										<button class="btn btn-default btn-sm" type="button" ng-click="verPopupListaUsuarios('md')">Seleccionar</button>
									</span>
								</div>
							</div>
							<div class="form-group col-md-2" style="margin-bottom: 22px ! important;">
								<label class="control-label"> </label>
								<div class="input-group"> 
									<input type="checkbox" ng-model="fData.tercero_propio" ng-disabled="boolExterior" > ¿Es Tercero? 
								</div>
							</div>
							<div class="form-group col-md-2" style="margin-bottom: 22px ! important;">
								<label class="control-label"> </label>
								<div class="input-group"> 
									<input type="checkbox" ng-model="fData.si_asistencia" checked > ¿Marca Asistencia? 
								</div>
							</div>
							<div class="form-group m-n col-md-12">
								<div class="checkbox" style="border-top: 1px solid #e8e8e8" > 
									<label><input type="checkbox" ng-model="fData.personalSalud" ng-disabled="boolExterior"/> ¿ES PERSONAL DE SALUD? </label> 
								</div> 
							</div> 
							<!-- <div class="form-group m-n col-md-6" ng-if="fData.personalSalud">
								<label class="control-label mb-xs">RNE</label>
								<input type="text" class="form-control input-sm" ng-model="fData.registro_nacional_especialista" placeholder="Registre su RNE" maxlength="6" /> 
							</div> -->
							<div class="form-group mb-md col-md-6" ng-if="fData.personalSalud">
								<label class="control-label mb-xs">Colegiatura Prof.</label>
								<input type="text" class="form-control input-sm" ng-model="fData.colegiatura_profesional" placeholder="Registre su Colegiatura" maxlength="6" />
							</div>
							<div class="form-group mb-md col-md-6" ng-if="fData.personalSalud">
								<label class="control-label mb-xs"> Fecha Caducidad Hab. Prof.</label>
								<div class="input-group" style="width: 100%;"> 
									<input type="text" class="form-control input-sm datepicker" ng-model="fData.fecha_caducidad_coleg" data-inputmask="'alias': 'dd-mm-yyyy'" />
								</div>
							</div>
							<!-- <div class="form-group mb-md col-md-12" ng-if="fData.personalSalud" >
								<label class="control-label mb-xs" ng-if="accion=='reg'">Asignar una Especialidad <small class="text-default">(Sólo si atenderá)</small> </label>
								<div class="input-group" ng-if="accion=='reg'">
									<span class="input-group-btn ">
										<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idempresaespecialidad" placeholder="ID" readonly="true" />
									</span>
									<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fData.especialidad" placeholder=""
										typeahead-loading="loadingLocationsEs" 
										typeahead="item as item.descripcion for item in getEspecialidadAutocomplete($viewValue)"
										typeahead-on-select="getSelectedEspecialidad($item, $model, $label)"
										ng-change = "fData.idempresaespecialidad = null;"
										ng-disabled="boolExterior"/> 
								</div>
								<i ng-show="loadingLocationsEs" class="fa fa-refresh"></i>
					            <div ng-show="noResultsLEspecialidad">
					              <i class="fa fa-remove"></i> No se encontró resultados 
					            </div>
							</div> -->

							<!-- <div class="form-group mb-md col-md-12" ng-if="fData.personalSalud" >
								<label class="control-label mb-xs" > Categoría Personal de Salud <small class="text-danger">(*)</small></label>
								<select class="form-control input-sm" ng-model="fData.categoriaPersonalSalud" ng-required=" fData.personalSalud "
					             	ng-options="item as item.descripcion_cps for item in listaCategoriaPersonalSalud ">
					          	</select> 
							</div> -->
						</div>
						<div class="row" ng-if="!boolExterior">
							<!---  PERSONAL DE FARMACIA  -->
							<div class="mb-n col-md-12">
								<div class="row">
									<div class="form-group mb-n col-md-12" >
										<div class="checkbox" style="border-top: 1px solid #e8e8e8"> 
											<label><input type="checkbox" ng-model="fData.personalFarmacia" /> ¿ES PERSONAL DE FARMACIA? </label> 
										</div> 
									</div> 
									<div class="form-group mb-md col-md-12" ng-if="fData.personalFarmacia">
								        <div class="row">
								            <div class="form-group col-md-6" > <label> Almacen </label> 
								               <select class="form-control input-sm" ng-model="fData.idalmacenfarmacia" ng-options="item.id as item.descripcion for item in listaAlmacen" ng-change="OnChangeAlmacen(fData.idalmacenfarmacia)"></select>
								            </div> 
								            <div class="form-group col-md-6" > <label> Sub-Almacen </label> 
								               <select class="form-control input-sm" ng-model="fData.idsubalmacenfarmacia" ng-options="item.id as item.descripcion for item in listaSubalmacen"></select> 
								            </div>
								        </div>
									</div>
								</div>
							</div>
							<!--- FIN PERSONAL DE FARMACIA -->
						</div>
						<div class="row"ng-if="!boolExterior">
							<!---  PERSONAL ADMINISTRATIVO  -->
							<div class="mb-n col-md-12">
								<div class="row">
									<div class="form-group mb-n col-md-12" >
										<div class="checkbox" style="border-top: 1px solid #e8e8e8"> 
											<label><input type="checkbox" ng-model="fData.personalAdministrativo" /> ¿ES PERSONAL ADMINISTRATIVO? </label> 
										</div> 
									</div> 
									<div class="form-group mb-md col-md-6" ng-if="fData.personalAdministrativo"> 
										<label class="control-label mb-xs">Colegiatura Prof.</label>
										<input type="text" class="form-control input-sm" ng-model="fData.colegiatura_profesional_emp" placeholder="Registre su Colegiatura" maxlength="6" />
									</div>
									<div class="form-group mb-md col-md-6" ng-if="fData.personalAdministrativo">
										<label class="control-label mb-xs"> Fecha Caducidad Hab. Prof.</label>
										<div class="input-group" style="width: 100%;"> 
											<input type="text" class="form-control input-sm datepicker" ng-model="fData.fecha_caducidad_coleg" data-inputmask="'alias': 'dd-mm-yyyy'" />
										</div>
									</div>
								</div>
							</div>
							<!--- FIN PERSONAL DE FARMACIA -->
						</div>

						<div class="form-group m-n col-md-4 mb-lg" style="padding-left: 0">
							<div class="checkbox mt-lg"> 
								<label><input type="checkbox" ng-model="fData.personalIPRESS" /> ¿ES IPRESS? </label> 
							</div> 
						</div>
						<div class="form-group m-n col-md-6 mb-lg">
							<div class="checkbox mt-lg"> 
								<label><input type="checkbox" ng-model="fData.personalPrivado" /> ¿ES PRIVADO? </label> 
							</div> 
						</div>

					</div>
				</div>
			</div>
		</div>
		<h4 class="m-n mb-sm pb-sm" style="border-bottom: 1px solid #e0e0e0;" ng-if="!boolExterior" > 
			<a class="text-info block" href="" ng-click="collapse.toggleEmpleado('DF');"> Datos Familiares 
			<span class="btn btn-default btn-sm button-icon has-bg pull-right"> <i class="ti ti-angle-down"></i> </span></a> 
		</h4>
		<div uib-collapse="collapse.isCollapsedDF !== collapse.collapsedAbv" class="row"> 
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Nombres 
					<small class="block" style="line-height: 0.8; font-size: 12px; color: #afafaf;"> Cónyugue o conviviente</small>
				</label>
				<input type="text" class="form-control input-sm" ng-model="fData.nombres_cy" placeholder="Registre su nombre" />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Apellido Paterno <small class="block" style="line-height: 0.8; font-size: 12px; color: #afafaf;"> Cónyugue o conviviente</small></label>
				<input type="text" class="form-control input-sm" ng-model="fData.apellido_paterno_cy" placeholder="Registre su apellido paterno" />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Apellido Materno <small class="block" style="line-height: 0.8; font-size: 12px; color: #afafaf;"> Cónyugue o conviviente</small></label>
				<input type="text" class="form-control input-sm" ng-model="fData.apellido_materno_cy" placeholder="Registre su apellido materno" />
			</div>
			<div class="form-group mb-md col-md-2" >
				<label class="control-label mb-xs">Fecha de Nac. <small class="block" style="line-height: 0.8; font-size: 12px; color: #afafaf;"> Cónyugue o conviviente</small></label>
				<div class="input-group" style="width: 100%;"> 
					<input type="text" class="form-control input-sm datepicker" ng-model="fData.fecha_nacimiento_cy" data-inputmask="'alias': 'dd-mm-yyyy'" />
				</div>
			</div>
			<div class="form-group mb-md col-md-4">
				<label class="control-label mb-xs"> Lugar donde labora <small class="block" style="line-height: 0.8; font-size: 12px; color: #afafaf;"> Cónyugue o conviviente</small></label>
				<input type="text" class="form-control input-sm" ng-model="fData.lugar_labores_cy" placeholder="Lugar donde labora" />
			</div>
			<div class="col-md-4" >
				<h5 class="mt-n mb-sm text-center">AGREGAR MAS PARIENTES</h5> 
				<div class="row">
					<div ng-class="classEditPanel"> 
						<div class="form-group mb-md col-md-6">
							<label class="control-label mb-xs">Nombres </label>
							<input id="temporalNombrePar" type="text" class="form-control input-sm" ng-model="fData.parTemporal.nombres" placeholder="Nombres" />
						</div>
						<div class="form-group mb-md col-md-6">
							<label class="control-label mb-xs">Apellido Paterno </label>
							<input type="text" class="form-control input-sm" ng-model="fData.parTemporal.ap_paterno" placeholder="Ap. Paterno" />
						</div>
						<div class="form-group mb-md col-md-6">
							<label class="control-label mb-xs">Apellido Materno </label>
							<input type="text" class="form-control input-sm" ng-model="fData.parTemporal.ap_materno" placeholder="Ap. Materno" />
						</div>
						<div class="form-group mb-md col-md-6">
							<label class="control-label mb-xs">Parentesco </label> 
							<select class="form-control input-sm" ng-model="fData.parTemporal.parentesco" ng-options="item.id as item.descripcion for item in listaParentesco" > </select>
							<!-- <input type="text" class="form-control input-sm" ng-model="fData.parTemporal.parentesco" placeholder="Parentesco" /> -->
						</div>
						<div class="form-group mb-md col-md-6">
							<label class="control-label mb-xs">Ocupación </label>
							<input type="text" class="form-control input-sm" ng-model="fData.parTemporal.ocupacion" placeholder="Ocupación" />
						</div>
						<div class="form-group mb-md col-md-6">
							<label class="control-label mb-xs">Fecha Nac. </label>
							<input type="text" class="form-control input-sm mask" ng-model="fData.parTemporal.fecha_nacimiento"  data-inputmask="'alias': 'dd-mm-yyyy'" />
						</div>
						<div class="form-group mb-md col-md-4">
							<label class="control-label mb-xs">Estado Civil </label>
							<select class="form-control input-sm" ng-model="fData.parTemporal.estado_civil" ng-options="item as item.descripcion for item in listaEstadoCivil" > </select>
						</div>
						<div class="form-group mb-md col-md-2 pl-n">
							<label class="control-label mb-xs">Vive </label>
							<select class="form-control input-sm" ng-model="fData.parTemporal.vive" ng-options="item as item.descripcion for item in listaVive" > </select>
						</div>
						<div class="form-group mb-md col-md-6">
							<!-- <label class="control-label mb-xs">  </label> si_notificacion_emer --> 
							<div class="input-group" style="font-size: 13px;"> 
								<label> <input type="checkbox" ng-model="fData.parTemporal.notificar_emergencia" ng-true-value="'SI'" ng-false-value="'NO'" /> <small style="display: block; line-height: 1;">Notificar en Situación de Emergencia </small> </label>
							</div>
						</div>
						<div class="form-group mb-md col-md-6" ng-show="fData.parTemporal.notificar_emergencia == 'SI'" >
							<label class="control-label mb-xs">Dirección </label>
							<input type="text" class="form-control input-sm" ng-model="fData.parTemporal.direccion" placeholder="Dirección" />
						</div>
						<div class="form-group mb-md col-md-6" ng-show="fData.parTemporal.notificar_emergencia == 'SI'" >
							<label class="control-label mb-xs">Teléfono </label>
							<input type="text" class="form-control input-sm" ng-model="fData.parTemporal.telefono" placeholder="Teléfono" />
						</div>
						<div class="col-md-12"> 
							<button ng-show="!editarParienteBool" type="button" class="btn btn-success" style="width: 100%;" ng-click="agregarParienteACesta();"> AGREGAR PARIENTE >>> </button>
							<button ng-show="editarParienteBool" type="button" class="btn btn-success" style="width: 100%;" ng-click="actualizarPariente();"> ACTUALIZAR PARIENTE >>> </button>
						</div>
					</div>
				</div>
			</div> 
			<div class="col-md-8"> 
				<div ui-if="gridOptionsParientes.data.length>0" ui-grid="gridOptionsParientes" ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid ">
					<div class="waterMarkEmptyData" ng-show="!gridOptionsParientes.data.length"> No se encontraron datos. </div>
				</div>
			</div>
		</div>
		<h4 class="pb-sm" style="border-bottom: 1px solid #e0e0e0;" ng-if="!boolExterior" > 
			<a class="text-info block" href="" ng-click="collapse.toggleEmpleado('DL');"> Historial de Contratos 
			<span class="btn btn-default btn-sm button-icon has-bg pull-right"> <i class="ti ti-angle-down"></i> </span></a> 
		</h4>
		<div uib-collapse="collapse.isCollapsedDL !== collapse.collapsedAbv" class="row">
			<div class="col-md-4 col-sm-6" >
				<h5 class="mt-n mb-sm text-center" ng-show="!editarContratoBool">AGREGAR CONTRATO</h5> 
				<h5 class="mt-n mb-sm text-center" ng-show="editarContratoBool">EDICIÓN DEL CONTRATO</h5> 
				<div class="row">
					<div ng-class="classEditPanel"> 
						<div class="form-group mb-md col-sm-6">
							<label class="control-label mb-xs"> Empresa <small class="text">(*)</small> </label>
							<select class="form-control input-sm" ng-model="fData.conTemporal.empresaadmin" ng-options="item as item.descripcion for item in metodos.listaEmpresaAdmin" > </select>
						</div>
						<div class="form-group mb-md col-sm-6">
							<label class="control-label mb-xs"> Condición Laboral <small class="text">(*)</small> </label>
							<select class="form-control input-sm" ng-model="fData.conTemporal.condicion_laboral" ng-options="item as item.descripcion for item in listaCondicionLaboral" > </select>
						</div>
						<div class="form-group mb-md col-md-12" >
							<label class="control-label mb-xs"> Asignar Cargo <small class="text">(*)</small> </label>
							<div class="input-group">
								<span class="input-group-btn ">
									<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.conTemporal.idcargo" placeholder="ID" readonly="true" />
								</span>
								<input placeholder="Digite el cargo" autocomplete="off" ng-change="getClearInputCargoHC();" type="text" class="form-control input-sm" ng-model="fData.conTemporal.cargo" placeholder="" typeahead-loading="loadingLocations" uib-typeahead="item as item.descripcion for item in getCargoAutocomplete($viewValue)" typeahead-on-select="getSelectedCargoHC($item, $model, $label)"/> 
							</div>
							<i ng-show="loadingLocations" class="fa fa-refresh"></i>
				            <div ng-show="noResultsLCargo"> <i class="fa fa-remove"></i> No se encontró resultados </div>
						</div>
						<div class="form-group mb-md col-sm-6">
							<label class="control-label mb-xs"> Fecha Ingreso <small class="text">(*)</small> </label>
							<input type="text" class="form-control input-sm" ng-model="fData.conTemporal.fecha_ingreso" data-inputmask="'alias': 'dd-mm-yyyy'" /> 
						</div>
						<div class="form-group mb-md col-sm-6" ng-if="editarContratoBool">
							<label class="control-label mb-xs"> Fecha de Cese </label>
							<input type="text" class="form-control input-sm" ng-model="fData.conTemporal.fecha_cese" data-inputmask="'alias': 'dd-mm-yyyy'" /> 
						</div>
						<div class="form-group mb-md col-sm-6">
							<label class="control-label mb-xs"> Fecha Inicio Contrato <small class="text">(*)</small> </label>
							<input type="text" class="form-control input-sm" ng-model="fData.conTemporal.fecha_inicio_contrato" data-inputmask="'alias': 'dd-mm-yyyy'" /> 
						</div>
						<div class="form-group mb-md col-sm-6">
							<label class="control-label mb-xs"> Fecha Fin Contrato <small class="text">(*)</small> </label>
							<input type="text" class="form-control input-sm" ng-model="fData.conTemporal.fecha_fin_contrato" data-inputmask="'alias': 'dd-mm-yyyy'" /> 
						</div>
						<div class="form-group mb-md col-sm-6" ng-if="!editarContratoBool">
							<label class="control-label mb-xs"> Sueldo </label>
							<input type="text" class="form-control input-sm" ng-model="fData.conTemporal.sueldo" placeholder="Sueldo S/. " /> 
						</div>
						<div class="form-group mb-n col-sm-6"> 
							<div class="input-group" style="font-size: 13px;"> 
								<label> <input type="checkbox" ng-model="fData.conTemporal.contrato_vigente" ng-true-value="1" ng-false-value="2"  /> 
									<small style="display: block; line-height: 1;"> ¿Es Contrato Vigente? </small> 
								</label>
							</div>
						</div>
						<div class="form-group mb-md col-sm-6" ng-if="editarContratoBool">
							<label class="control-label mb-xs"> Sueldo </label>
							<input type="text" class="form-control input-sm" ng-model="fData.conTemporal.sueldo" placeholder="Sueldo S/. " /> 
						</div>
						<div class="col-md-12"> 
							<button ng-show="!editarContratoBool" type="button" class="btn btn-success" style="width: 100%;" ng-click="agregarContratoACesta();"> AGREGAR CONTRATO >>> </button>
							<button ng-show="editarContratoBool" type="button" class="btn btn-success" style="width: 100%;" ng-click="actualizarContrato();"> ACTUALIZAR CONTRATO >>> </button>
						</div>
					</div>
				</div>
			</div> 
			<div class="col-md-8 col-sm-6"> 
				<div ui-if="gridOptionsContrato.data.length>0" ui-grid="gridOptionsContrato" ui-grid-pinning ui-grid-pagination ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid ">
					<div class="waterMarkEmptyData" ng-show="!gridOptionsContrato.data.length"> No se encontraron datos. </div>
				</div>
			</div>
			
			<!-- <div class="form-group mb-md col-md-3">
				<label class="control-label mb-xs"> Fecha de Afiliación </label>
				<input type="text" class="form-control input-sm mask" ng-model="fData.fecha_afiliacion"  data-inputmask="'alias': 'dd-mm-yyyy'" />
			</div>
			<div class="form-group mb-md col-md-6">
				<label class="control-label mb-xs"> Documento de Afiliación </label>
				<input type="text" class="form-control input-sm" ng-model="fData.documento_afiliacion" />
			</div>
			
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs">Fecha de Ingreso </label>
				<input type="text" class="form-control input-sm mask" ng-model="fData.fecha_ingreso"  data-inputmask="'alias': 'dd-mm-yyyy'" />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs" style="font-size: 12px;">Fecha Inicio Contrato/Convenio </label>
				<input type="text" class="form-control input-sm mask" ng-model="fData.fecha_inicio_contrato"  data-inputmask="'alias': 'dd-mm-yyyy'" />
			</div>
			<div class="form-group mb-md col-md-2">
				<label class="control-label mb-xs" style="font-size: 12px;">Fecha Fin Contrato/Convenio </label>
				<input type="text" class="form-control input-sm mask" ng-model="fData.fecha_fin_contrato"  data-inputmask="'alias': 'dd-mm-yyyy'" />
			</div> -->
		</div>
		<h4 class="pb-sm" style="border-bottom: 1px solid #e0e0e0;" ng-if="!boolExterior" > 
			<a class="text-info block" href="" ng-click="collapse.toggleEmpleado('DE');"> Datos de Estudios 
			<span class="btn btn-default btn-sm button-icon has-bg pull-right"> <i class="ti ti-angle-down"></i> </span></a> 
		</h4>
		<div uib-collapse="collapse.isCollapsedDE !== collapse.collapsedAbv" class="row"> 
			<div ng-class="classEditPanel"> 
				<div class="form-group mb-md col-md-2">
					<label class="control-label mb-xs">Tipo de Estudios </label>
					<select class="form-control input-sm" ng-model="fData.estTemporal.tipo_estudio" ng-options="item as item.descripcion for item in listaTipoEstudio" ng-change="cargarNivelEstudio(fData.estTemporal.tipo_estudio.id);"> </select>
				</div>
				<div class="form-group mb-md col-md-2">
					<label class="control-label mb-xs">Nivel de Estudios </label>
					<select class="form-control input-sm" ng-model="fData.estTemporal.nivel_estudio" ng-options="item as item.descripcion for item in listaNivelEstudio" > </select>
				</div>
				<div class="form-group mb-md col-md-4">
					<label class="control-label mb-xs">Centro de Estudios </label>
					<input id="centro_estudio" type="text" class="form-control input-sm" ng-model="fData.estTemporal.centro_estudio" placeholder="" />
				</div>
				<div class="form-group mb-md col-md-2">
					<label class="control-label mb-xs">Fecha Desde </label>
					<input id="fecha_desde" type="text" class="form-control input-sm mask" ng-model="fData.estTemporal.fecha_desde"  data-inputmask="'alias': 'dd-mm-yyyy'" />
				</div>
				<div class="form-group mb-md col-md-2">
					<label class="control-label mb-xs">Fecha Hasta </label> 
					<input id="fecha_hasta" type="text" class="form-control input-sm mask" ng-model="fData.estTemporal.fecha_hasta"  data-inputmask="'alias': 'dd-mm-yyyy'" />
				</div>
				<div class="form-group mb-md col-md-2">
					<label class="control-label mb-xs">Completo / Incompleto </label>
					<select class="form-control input-sm" ng-model="fData.estTemporal.estudio_completo" ng-options="item.id as item.descripcion for item in listaEstudioCompleto"> </select>
				</div>
				<div class="form-group mb-md col-md-4">
					<label class="control-label mb-xs"> Denominación / Especialidad </label>
					<input type="text" class="form-control input-sm" ng-model="fData.estTemporal.especialidad" placeholder="" ng-disabled="fData.estTemporal.tipo_estudio.id == '1'"/>
				</div>
				<div class="form-group mb-md col-md-4">
					<label class="control-label mb-xs">Grado Académico </label>
					<select class="form-control input-sm" ng-model="fData.estTemporal.grado_academico" ng-options="item.id as item.descripcion for item in listaGradoAcademico" ng-disabled="fData.estTemporal.tipo_estudio.id == '1'" > </select>
				</div>
				<div class="col-md-2"> 
					<button ng-show="!editarEstudioBool" type="button" class="btn btn-success" style="width: 100%;margin-top: 16px;" ng-click="agregarEstudioACesta();"> AGREGAR ESTUDIO </button>
					<button ng-show="editarEstudioBool" type="button" class="btn btn-success" style="width: 100%;margin-top: 16px;" ng-click="actualizarEstudio();"> ACTUALIZAR ESTUDIO </button>
				</div>
			</div>
			<div class="col-md-12"> 
				<div ui-if="gridOptionsEstudios.data.length>0" ui-grid="gridOptionsEstudios" ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid ">
					<div class="waterMarkEmptyData" ng-show="!gridOptionsEstudios.data.length"> Agregue los estudios realizados. </div>
				</div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
	<button class="btn btn-primary" ng-click="aceptar()" >Guardar</button>
    <!-- <button class="btn btn-primary" ng-click="aceptar()" ng-disabled="formEmpleado.$invalid" >Guardar</button> -->
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>