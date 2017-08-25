<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" />
<link type="text/css" href="assets/plugins/iCheck/skins/flat/_all.css" rel="stylesheet">
<link type="text/css" href="assets/plugins/iCheck/skins/square/_all.css" rel="stylesheet"> -->
<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Laboratorio</li>
  <li class="active"> Registro de Exámenes </li>
</ol>
<div class="container-fluid" ng-controller="atencionMuestraController">
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
    			<div class="panel-heading">
	                <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div>
	                <h2>Registro de Exámenes</h2> 
	            </div>

	            <div class="panel-body">
	            	<form name="formMuestra">
						<div class="col-md-6">
	            			<fieldset class="row" style="padding-right: 10px;">
	            				<legend class="col-md-12 pr-n pl-n"> Datos del Paciente 
	            					<button ng-click="btnBuscarCliente('lg');" class="btn btn-info-alt pull-right btn-sm mt-sm ml" type="button"> <i class="fa fa-search"></i> Buscar Paciente </button> 
	            				</legend>
	            				<div class="form-group mb-md col-md-3 pl-n">
			                        <label class="control-label mb-xs"> Nº Historia </label>
			                        <input id="idhistoria"  type="text" class="form-control input-sm" ng-model="fData.paciente.idhistoria" ng-enter="obtenerDatosPacienteHistoria(); $event.preventDefault();" placeholder="Digite Nº Historia Clínica"  ng-change="limpiarCamposMenosHistoria();" focus-me  required autocomplete="off"/> 
			                    </div>
	            				
			                    <div class="form-group mb-md col-md-4 "> 
			                        <label class="control-label mb-xs"> Nombres </label> 
			                        <input type="text" class="form-control input-sm" ng-model="fData.paciente.nombres" placeholder="Nombres" required disabled />
			                    </div>
			                    <div class="form-group mb-md col-md-5 ">
			                        <label class="control-label mb-xs"> Apellidos </label>
			                        <input type="text" class="form-control input-sm" ng-model="fData.paciente.apellidos" placeholder="Apellidos" required disabled /> 
			                    </div>
			                   <div class="form-group mb-md col-md-3 pl-n">
			                        <label class="control-label mb-xs"> Orden de Venta </label> 
			                        <select class="form-control input-sm" ng-model="fData.ordenventa" ng-options="item.ordenventa for item in fData.ordenes" required ng-change="cargarProductos();$event.preventDefault();"> </select>
			                        <!-- <input id="numOrden" type="text" class="form-control input-sm" ng-model="fData.paciente.ordenventa" 
		                            ng-enter="obtenerDatosPacienteOrden(); $event.preventDefault();" placeholder="Digite el Número de Orden" tabindex="101" required/>  -->
			                    </div>
			                    <div class="form-group mb-md col-md-4 ">
			                        <label class="control-label mb-xs"> Nº Documento </label>
			                        <input type="text" class="form-control input-sm" ng-model="fData.paciente.num_documento" placeholder="DNI" disabled /> 
			                    </div>
			                    <div class="form-group mb-md col-md-3">
			                        <label class="control-label mb-xs"> Edad </label>
			                        <input type="text" class="form-control input-sm" ng-model="fData.paciente.edad" placeholder="Edad" disabled /> 
			                    </div>
			                    <div class="form-group mb-md col-md-2">
			                        <label class="control-label mb-xs"> Sexo </label>
			                        <input type="text" class="form-control input-sm" ng-model="fData.paciente.sexo" placeholder="Sexo" disabled /> 
			                    </div>
	            			</fieldset>
	            		</div>
	            		<div class="col-md-6">
	            			<fieldset class="row" style="padding-right: 10px;">
	            				<legend class="col-md-12 pr-n pl-n"> Datos de la Orden
	            					
	            				</legend>
	            				<!-- <div class="form-group mb-md col-md-4 pl-n"> 
			                        <label class="control-label mb-xs"> Muestras Tomadas </label> 
			                        <input type="checkbox" />
			                        <select class="form-control input-sm" ng-model="fData.idtipomuestra" ng-options="item.id as item.descripcion for item in listaTipoMuestra" required > </select>
			                    </div> -->
			                    <div class="form-group mb-md col-md-4">    
			                        <label class="control-label mb-xs"> Prioridad </label> 
			                        <select class="form-control input-sm" ng-model="fData.prioridad" ng-options="item.id as item.descripcion for item in listaBoolPrioridad" > </select>
			                    </div>
			                    <div class="form-group mb-md col-md-4">    
			                        <label class="control-label mb-xs"> Médico de la orden </label> 
			                        <!-- <input type="text" class="form-control input-sm" ng-model="fData.medico" placeholder="Digite nombre de Médico" /> -->
			                        <input type="text" ng-model="fData.medico" class="form-control input-sm" placeholder="Digite el personal responsable para autocompletar" typeahead-loading="loadingLocations" 
	                                      uib-typeahead="item as item.descripcion for item in getPersonalMedicoAutocomplete($viewValue)" typeahead-min-length="2" />  
	                                <i ng-show="loadingLocations" class="fa fa-refresh"></i>
	                                <div ng-show="noResultsMEDRESP">
	                                    <i class="fa fa-remove"></i> No se encontró resultados 
	                                </div> 
			                    </div>
			                    <div class="form-group mb-n col-md-4">    
			                        <label class="control-label mb-xs"> Orden de Laboratorio</label> 
			                        <input type="text" class="form-control input-lg" ng-model="fData.orden_lab" placeholder="" disabled style="text-align: center; font-size:1.4em ;color: red;font-weight: 600;"/> 
			                    </div>
			                   	<div class="form-group mb-md col-md-8">
									<label class="control-label mb-xs"> Observaciones  </label>
									<textarea class="form-control input-sm" ng-model="fData.observaciones" placeholder="" >
										
									</textarea>
								</div>
								
	            			</fieldset>
	            		</div>
	            		<!-- <div class="row">
		                  <div class="col-sm-12 text-right">
		                    <button class="btn-primary btn" ng-click="agrega(); $event.preventDefault();" ng-show="mySelectionGrid.length == 1"> <i class="fa fa-save"> </i> Agregar Analisis </button>
		                  </div>
		                </div> -->
	            		<div class="well well-transparent boxDark col-xs-12 m-n">
	            			<div ui-grid="gridOptions" ui-grid-pagination ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
	            		</div>
	            	</form>
	            </div>
	            <div class="panel-footer">
	                <div class="row">
	                  <div class="col-sm-12 text-right">
	                    <button class="btn-primary btn" ng-click="grabar(); $event.preventDefault();" ng-disabled="formMuestra.$invalid || isRegisterSuccess"> <i class="fa fa-save"> </i> [F2] Grabar </button>
	                    <button class="btn-default btn" ng-click="nuevo(); $event.preventDefault();" > <i class="fa fa-file"> </i> [F3] Nuevo </button>
	                    <button class="btn-success btn" ng-click="imprimir(); $event.preventDefault();" ng-disabled="!isRegisterSuccess"> <i class="fa fa-print"> </i> [F4] Imprimir Código de barras </button>
	                    
	                  </div>
	                </div>
              </div>
	            
    		</div>
    	</div>
    </div>
</div>