<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Laboratorio</li>
  <li class="active"> Reporte Resultados </li>
</ol>
<div class="container-fluid" ng-controller="reporteResultadosController">
  <div class="row">
  	<div class="col-md-12">
  		<div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
  			<div class="panel-heading">
            <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div>
            <h2> Reporte de Resultados </h2> 
        </div>

        <div class="panel-body">
          <ul class="row demo-btns" ng-show="!registroFormularioAMA && !registroFormularioAP "> 
            <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Filtro de Búsqueda </label> 
              <div class="input-group block"> 
                <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.tipoBusqueda" ng-change="onChangeFiltroBusqueda();" ng-options="item.id as item.descripcion for item in listaFiltroBusqueda" > </select>
              </div>
            </li>
            
            <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showHistoria" > <label> N° de Historia </label> 
              <div class="input-group" style="width: 230px;"> 
                <input tabindex="101" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroHistoria" placeholder="Digite N° de Historia" ng-enter="btnConsultarPacientesAtencion();"/>
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showPaciente" > <label> Paciente </label> 
              <div class="input-group block"> 
                <!-- <input tabindex="103" type="text" class="form-control input-sm" ng-model="fBusqueda.paciente" placeholder="Digite nombre del paciente" />  -->
                <input type="text" ng-model="fBusqueda.paciente" class="form-control input-sm" tabindex="102" placeholder="Digite nombre del paciente" typeahead-loading="loadingLocations" 
                  uib-typeahead="item.descripcion as item.descripcion for item in getPacienteAutocomplete($viewValue)" typeahead-min-length="2" /> 
                <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                <div ng-show="noResultsPACI">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showOrden"> <label style="display: block;"> Fecha y N° de Orden Laboratorio</label> 
              <div class="input-group" style="width: 80px; display: inline-block;">
                <input tabindex="115" type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fBusqueda.fechaexamen" />
              </div>
              <div class="input-group" style="width: 115px; display: inline-block;">
                <input tabindex="120" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroOrden" placeholder="Digite N° de Orden" ng-enter="btnConsultarPacientesAtencion();" focus-me/>
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-5 p-n" > 
                <button tabindex="130" type="button" class="btn btn-success" ng-click="btnConsultarPacientesAtencion();"> <i class="fa fa-search"></i> BUSCAR PACIENTE </button> 
               <!--  <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir();" ng-disabled="!pacEncontrado"><i class="fa fa-print"></i> IMPRIMIR TODO</button>  -->
                <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimirSel();" ng-if="mySelectionGrid.length >= 1" ng-disabled="!pacEncontrado"><i class="fa fa-print"></i> [F4] IMPRIMIR SELECCIONADO</button> 
            </li> 
          </ul>
          <div class="well well-transparent boxDark col-xs-12 m-n" style="min-height:200px">
            <div class="row mb-xl">
              <div class="col-md-8" ng-if="pacEncontrado">
                <div class="col-md-2">
                  <label class="control-label mb-xs text-blue"> Nº Historia </label>
                  <span class="help-block text-black m-n"> {{ fData.idhistoria }} </span> 
                </div>
                <div class="col-md-4">
                  <label class="control-label mb-xs text-blue"> Paciente </label>
                  <span class="help-block text-black m-n"> {{ fData.paciente }} </span> 
                </div>  
                <div class="col-md-2">
                  <label class="control-label mb-xs text-blue"> Edad </label>
                  <span class="help-block text-black m-n"> {{ fData.edad }} años</span> 
                </div>
                <div class="col-md-2">
                  <label class="control-label mb-xs text-blue"> Sexo </label>
                  <span class="help-block text-black m-n"> {{ fData.sexo }} </span> 
                </div>
                <div class="col-md-2">
                  <label class="control-label mb-xs text-blue"> Nº Orden </label>
                  <span class="help-block text-red m-n"><strong> {{ fData.orden_lab }} </strong></span> 
                </div>
              </div>
              <div class="col-md-4" ng-if="pacEncontrado">
                <!-- <div class="col-md-6">
                  <label class="control-label mb-xs text-blue"> Nº Historia </label>
                  <span class="help-block text-black m-n"> {{ fData.idhistoria }} </span> 
                </div>
                <div class="col-md-6">
                  <label class="control-label mb-xs text-blue"> Paciente </label>
                  <span class="help-block text-black m-n"> {{ fData.paciente }} </span> 
                </div> -->
              </div>
            </div>
            

            <div class="waterMarkEmptyData" style="font-size: 20px;" ng-if="!pacEncontrado"> Seleccione filtro de búsqueda y haga Click en  "BUSCAR PACIENTE" </div>
            <div class="row">
              <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
