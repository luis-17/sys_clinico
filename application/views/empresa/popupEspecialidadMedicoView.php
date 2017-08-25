<style type="text/css">
	#gridEspecialidades .ui-grid-pager-panel .ui-grid-pager-container .ui-grid-pager-row-count-picker .ui-grid-pager-row-count-label{display: none!important;}
</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormAdd }} </h4>
</div>
<div class="modal-body">
    <form class="row"> 
	    
		<!-- <div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n">Sede: </label>
			<p class="help-block mt-xs"> {{ mySelectionGrid[0].sede }} </p>
		</div> -->
		<div class="form-group col-md-8 mb-md">
			<label class="control-label mb-n text-bold">Empresa: </label>
			<p class="help-block mt-xs inline"> {{ mySelectionGridTab2[0].empresa }} </p>
		</div>
		<fieldset class="col-lg-5 col-xs-12">
			<div class="row">
				<div class="form-inline mb-md col-md-12" >
					<div class="input-group">
							<span class="input-group-btn ">
								<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAdd.idespecialidad" placeholder="ID" readonly="true" />
							</span>
							<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fDataAdd.especialidad" placeholder="Seleccione una especialidad"
								typeahead-loading="loadingLocationsEs" 
								typeahead="item as item.descripcion for item in getEspecialidadNoAgregAutocomplete($viewValue)"
								typeahead-on-select="getSelectedEspecialidad($item, $model, $label)"
								typeahead-min-length="2"
								ng-change="limpiaId()" style="width:342px"/>
							
					</div>
					<button type="button" class="btn btn-info btn-sm pull-right" ng-click="agregarEspecialidad();"> <i class="fa fa-plus"></i>  AGREGAR</button>
					<i ng-show="loadingLocationsEs" class="fa fa-refresh"></i>
		            <div ng-show="noResultsLEspecialidad">
		              <i class="fa fa-remove"></i> No se encontró resultados 
		            </div>


					<!-- <input style="min-width: 42%;" type="text" ng-change="buscar()" class="form-control" ng-model="searchText" 
						placeholder="Busque Especialidad" focus-me /> -->
				</div>
				<div class="form-inline col-md-12 mb-xs">
					<label class="control-label mt">Servicios agregados</label>

					<div class="pull-right ml-xs" ng-if="mySelectionEspecialidadesGrid.length > 0">
                      <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" type="button">
                            MAS OPCIONES <span class="caret"></span>
                        </button>
                        <ul role="menu" class="dropdown-menu">
                            <li><a href="" ng-click="quitarEspecialidadDeEmpresa();">ELIMINAR</a></li>
                        </ul>
                      </div>
                    </div>

					<!-- <button type="button" class="btn btn-danger pull-right ml-xs" ng-click="quitarEspecialidadDeEmpresa();" ng-if="mySelectionEspecialidadesGrid.length > 0"> <i class="fa fa-times-circle"></i>  ELIMINAR </button> -->

					<button type="button" class="btn btn-success pull-right ml-xs" ng-click="habilitarEspecialidadEnEmpresa();" ng-if="mySelectionEspecialidadesGrid.length == 1 && mySelectionEspecialidadesGrid[0].estado_emes == 2"> <i class="fa fa-check"></i>  HABILITAR </button>
					<button type="button" class="btn btn-default pull-right ml-xs" ng-click="deshabilitarEspecialidadEnEmpresa();" ng-if="mySelectionEspecialidadesGrid.length == 1 && mySelectionEspecialidadesGrid[0].estado_emes == 1"> <i class="fa fa-power-off"></i> DESHABILITAR </button>
				</div>
				<div class="form-group mb-md col-md-12">
					<div id="gridEspecialidades" ui-grid="gridOptionsEspecialidades" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid scroll-x-none"style="height: 339px!important; overflow-x: hidden;"></div>
				</div>
			</div>
		</fieldset>
		
		<fieldset class="col-lg-7 col-xs-12">
			<div class="row">
				<div class="form-inline mb-md col-md-12" >
					<div class="input-group">
							<span class="input-group-btn ">
								<input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fDataAddMed.idmedico" placeholder="ID" readonly="true" />
							</span>
							<input autocomplete="off" type="text" class="form-control input-sm" ng-model="fDataAddMed.medico" placeholder="Seleccione un médico"
								typeahead-loading="loadingLocationsMed" 
								typeahead="item as item.descripcion for item in getMedicoNoAgregAutocomplete($viewValue)"
								typeahead-on-select="getSelectedMedico($item, $model, $label)"
								typeahead-min-length="2"
								ng-change="limpiaIdMedico()" style="width:466px"
								ng-disabled="mySelectionEspecialidadesGrid.length < 1"/>
							
					</div>
					<button type="button" ng-if="mySelectionGridTab2[0].idempresa != empresaAdmin.idempresa" class="btn btn-success btn-sm pull-right" ng-click="btnNuevoEmplSalud();" ng-disabled="mySelectionEspecialidadesGrid.length < 1"> 
					<i class="fa fa-file"></i> NUEVO </button>
					<button type="button" class="btn btn-info btn-sm pull-right mr" ng-click="agregarMedico();" ng-disabled="mySelectionEspecialidadesGrid.length < 1"> 
					<i class="fa fa-plus"></i> AGREGAR </button>
					
					<i ng-show="loadingLocationsMed" class="fa fa-refresh"></i>
		            <div ng-show="noResultsLMedicos">
		              <i class="fa fa-remove"></i> No se encontró resultados 
		            </div>


					<!-- <input style="min-width: 42%;" type="text" ng-change="buscar()" class="form-control" ng-model="searchText" 
						placeholder="Busque Especialidad" focus-me /> -->
				</div>
				<div class="form-inline col-md-12 mb-xs">
					<label class="control-label mt">Profesionales por servicio: </label>
					<div class="pull-right ml-xs" ng-if="mySelectionMedicoGrid.length > 0">
                      <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" type="button">
                            MAS OPCIONES <span class="caret"></span>
                        </button>
                        <ul role="menu" class="dropdown-menu">
                            <li><a href="" ng-click="quitarMedicoDeEmpresa();">ELIMINAR</a></li>
                        </ul>
                      </div>
                    </div>
					<!-- <button type="button" class="btn btn-danger pull-right ml-xs" ng-click="quitarMedicoDeEmpresa();" ng-if="mySelectionMedicoGrid.length > 0"> <i class="fa fa-times-circle"></i>  ELIMINAR </button> -->

					<button type="button" class="btn btn-success pull-right ml-xs" ng-click="habilitarMedicoEnEmpresa();" ng-if="mySelectionMedicoGrid.length == 1 && mySelectionMedicoGrid[0].estado_emme == 2"> <i class="fa fa-check"></i>  HABILITAR </button>

					<button type="button" class="btn btn-default pull-right ml-xs" ng-click="deshabilitarMedicoEnEmpresa();" ng-if="mySelectionMedicoGrid.length == 1 && mySelectionMedicoGrid[0].estado_emme == 1"> <i class="fa fa-power-off"></i> DESHABILITAR </button>

					<!-- <button type="button" class="btn btn-warning pull-right ml-xs" ng-click="btnEditarMedico();" ng-if="mySelectionMedicoGrid.length == 1 && mySelectionGridTab2[0].idempresa != empresaAdmin.idempresa">  EDITAR </button> -->

					<button type="button" class="btn btn-warning pull-right ml-xs" ng-click="btnEditarMedico();" ng-if="mySelectionMedicoGrid.length == 1">  EDITAR </button>

				</div>
				<div class="form-group mb-md col-md-12">
					<div ui-grid="gridOptionsMedicos" ui-grid-pagination  ui-grid-selection ui-grid-edit ui-grid-resize-columns ui-grid-edit class="grid table-responsive fs-mini-grid scroll-x-none"style="height: 339px!important; overflow-x: hidden;">
						
						<div class="waterMarkEmptyData" ng-if="mySelectionEspecialidadesGrid.length != 1"> Seleccione una especialidad. </div>
						<div class="waterMarkEmptyData" ng-if="boolDatos"> No se encontraron datos. </div>
					</div>
				</div>
			</div>
		</fieldset>
		
	</form>
</div>
<div class="modal-footer">
    <!-- <button type="button" class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();">Aceptar</button> -->
    <button type="button" class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>