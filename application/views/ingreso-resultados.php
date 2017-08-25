<style type="text/css">
  .fila{border-bottom: 1px solid rgb(224, 224, 224);padding-top: 10px;padding-bottom: 10px;}
  .fila:last-child{border-bottom:none;}
  .fila:nth-child(even) {background: #fefefe; }
  .fila:nth-child(odd) {background: #F8F8F8;}
  .fila_oscura{background: #ddd!important;}
  .analisis{
    margin-bottom: 4px;
    font-size: 19px;
    font-weight: 300;
    line-height: 1.4;}
</style>
<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Laboratorio</li>
  <li class="active"> Registro de Resultados </li>
</ol>
<div class="container-fluid" ng-controller="registrarResultadosController">
  <div class="row">
  	<div class="col-md-12">
  		<div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
  			<div class="panel-heading">
            <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div>
            <h2> Registro de Resultados </h2> 
        </div>

        <div class="panel-body">
          <ul class="row demo-btns" ng-show="!registroFormularioAMA && !registroFormularioAP "> 
            <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Filtro de Búsqueda </label> 
              <div class="input-group block"> 
                <select tabindex="1" class="form-control input-sm" ng-model="fBusqueda.tipoBusqueda" ng-click="onChangeFiltroBusqueda();" ng-options="item.id as item.descripcion for item in listaFiltroBusqueda" > </select>
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showOrden"> <label style="display: block;"> Fecha y N° de Orden Laboratorio</label> 
              <div class="input-group" style="width: 80px; display: inline-block;">
                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fBusqueda.fechaexamen" tabindex="2" />
              </div>
              <div class="input-group" style="width: 115px; display: inline-block;">
                <input tabindex="3" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroOrden" placeholder="Digite N° de Orden" ng-enter="btnConsultarPacientesAtencion();" focus-me/>
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showHistoria" > <label> N° de Historia </label> 
              <div class="input-group" style="width: 230px;"> 
                <input tabindex="4" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroHistoria" placeholder="Digite N° de Historia" ng-enter="btnConsultarPacientesAtencion();"/>
              </div>
            </li>
            <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showPaciente" > <label> Paciente </label> 
              <div class="input-group block"> 
                <!-- <input tabindex="103" type="text" class="form-control input-sm" ng-model="fBusqueda.paciente" placeholder="Digite nombre del paciente" />  -->
                <input type="text" ng-model="fBusqueda.paciente" class="form-control input-sm" tabindex="5" placeholder="Digite nombre del paciente" typeahead-loading="loadingLocations" 
                  uib-typeahead="item.descripcion as item.descripcion for item in getPacienteAutocomplete($viewValue)" typeahead-min-length="2" ng-enter="btnConsultarPacientesAtencion();"/> 
                <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                <div ng-show="noResultsPACI">
                  <i class="fa fa-remove"></i> No se encontró resultados 
                </div>
              </div>
            </li>
            
            <li class="form-group mr mt-sm col-sm-5 p-n" > 
                <button tabindex="200" type="button" class="btn btn-success" ng-click="btnConsultarPacientesAtencion();"> <i class="fa fa-search"></i> BUSCAR PACIENTE </button> 
                <button tabindex="300" type="button" class="btn btn-info" ng-click="generarResultado();" ng-if="boolGenerar && (fSessionCI.idsedeempresaadmin == 9) "> <i class="fa fa-crosshairs"></i> GENERAR RESULTADOS </button> 
                <!-- <button type="button" class="btn btn-info ml-sm" ng-click="btnIngresarSel();" ng-if="mySelectionGrid.length >= 1 && !sel"><i class="fa fa-edit"></i> INGRESAR SELECCIONADOS </button>  -->
            </li> 
          </ul>
          <div class="well well-transparent boxDark col-xs-12 m-n" style="min-height:220px">
            <div class="row">
              <div class="col-md-12" ng-if="pacEncontrado">
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
                  <span class="help-block text-black m-n"> {{ fData.edad }} </span> 
                </div>
                <div class="col-md-2">
                  <label class="control-label mb-xs text-blue"> Sexo </label>
                  <span class="help-block text-black m-n"> {{ fData.sexo }} </span> 
                </div>
                <div class="col-md-2">
                  <label class="control-label mb-xs text-blue"> Nº Orden </label>
                  <span class="help-block text-red m-n"> {{ fData.orden_lab }} </span> 
                </div>
              </div>
            </div>
            

            <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-if="!pacEncontrado"> Seleccione filtro de búsqueda y haga Click en  "BUSCAR PACIENTE" </div>
            <div style="min-height:50px;padding-top: 10px;" ng-if="!(pacEncontrado && sel)">
                <!-- <button type="button" class="btn btn-danger ml-sm pull-left" ng-click="btnObservarSel();" ng-if="mySelectionGrid.length >= 1 && !sel"><i class="fa fa-edit"></i> OBSERVACIONES </button> -->
                <button type="button" class="btn btn-warning ml-sm pull-right" ng-click="btnEntregarSel();" ng-if="mySelectionGrid.length >= 1 && !sel"><i class="fa fa-edit"></i> ENTREGAR </button>
                <button type="button" class="btn btn-success ml-sm pull-right" ng-click="btnImprimirSel();" ng-if="mySelectionGrid.length >= 1 && !sel" ng-disabled="!pacEncontrado"><i class="fa fa-print"></i> [F4] IMPRIMIR SELECCIONADO</button> 
                <button type="button" class="btn btn-info ml-sm pull-right" ng-click="btnIngresarSel();" ng-if="mySelectionGrid.length >= 1 && !sel"><i class="fa fa-edit"></i> INGRESAR SELECCIONADOS </button>
                
              
            </div>
            <div class="row" >
              <div ng-show="pacEncontrado && !sel" ui-grid="gridOptions" ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
              </div>
            </div>
            <div class="panel-body" ng-if="pacEncontrado && sel" style="padding-top: 0px;">
              <form name="formParametros" >
                <div class="col-md-12 col-xs-12">
                  <fieldset class="row" style="padding-right: 10px;">
                    <legend style="font-size: 16px; background-color: #5d7581; color: white; line-height: 1.5; padding-top: 2px; padding-bottom: 2px;    min-height: 32px;" class="mt mb-n">
                    <div class="form-group mb-n col-md-4" style="border-right: 1px solid #8f9da5;" >Examen</div>
                    <div class="form-group mb-n col-md-4" style="border-right: 1px solid #8f9da5;">Resultado</div>
                    <div class="form-group mb-n col-md-2" style="border-right: 1px solid #8f9da5;">Valor Normal</div>
                    <div class="form-group mb-n col-md-2">Método</div>
                    </legend>
                    <div style="overflow-y:scroll; height:450px;">
                      <div ng-repeat="seccion in fDataArrPrincipal" class="" ng-show="seccion.seleccionado">
                        <h4 class="form-group mb-md col-md-12 text-center">{{ seccion.seccion }}</h4>
                         <div ng-repeat="analisis in seccion.analisis" class="" ng-show="analisis.seleccionado">
                          <label class="analisis mt-sm" ng-if="analisis.cantidad > 1"><strong>{{analisis.descripcion_anal}} ({{$index+1}})</strong></label>
                          <label class="analisis mt-sm" ng-if="analisis.cantidad == 1"><strong>{{analisis.descripcion_anal}}</strong></label>
                          <div ng-repeat="parametro in analisis.parametros" > 
                            <!-- <label class="m-n text-blue"> {{ parametro.descripcion_par }} </label>  -->
                            <!-- 57  : ANTIBIOGRAMA --> 
                            <!-- 456 : OBSERVACIONES --> 
                            <!-- 545 : SE AISLA --> 
                            <div class="form-group mb-n col-md-12 fila" ng-class="{'fila_oscura':parametro.separador == 1 || parametro.idparametro == 456}">
                              <!-- PARAMETRO -->
                              <div class="form-group mb-n col-md-4" ng-class="{'pl-xs':parametro.separador == 1 || parametro.idparametro == 456}">
                                <span class="m-n" ng-class="{'text-black':parametro.separador == 0 && parametro.idparametro != 456}">{{ parametro.parametro }}</span> 
                              </div>
                              <!-- RESULTADO -->
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.idparametro == 272">
                                <span class="text-red" ng-class="{'text-green':sumPorcentajes == 100}">{{sumPorcentajes}}%</span>
                              </div>
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.separador != 1 && parametro.combo == 2 && parametro.formula == 2">
                                <input class="form-control input-sm" type="text" ng-model="parametro.resultado" enter-as-tab/>
                              </div>

                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.separador != 1 && parametro.combo == 2 && parametro.formula == 1">
                                <input class="form-control input-sm" type="text" ng-model="parametro.resultado" ng-enter="calcularFormulaParametro(parametro.idparametro,{{$parent.$parent.$parent.$index}},{{$parent.$parent.$index}},{{$index}});" enter-as-tab/>
                                
                              </div>

                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.combo == 1 && parametro.idparametro == 545">
                                <select class="form-control input-sm" ng-model="parametro.resultado" ng-options="item.id as item.descripcion for item in listaBacteria" enter-as-tab> </select>
                              </div>
                              <!-- <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.combo == 1 && parametro.idparametro == 528">
                                <select class="form-control input-sm" ng-model="parametro.resultado" ng-options="item.id as item.descripcion for item in listaParasito" enter-as-tab> </select>
                              </div> -->
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.combo == 1 && parametro.idparametro != 545">
                                <select class="form-control input-sm" ng-model="parametro.resultado" ng-options="item.id as item.descripcion for item in parametro.lista_combo" enter-as-tab> </select>
                              </div>

                              <!-- TEXTO ADICIONAL -->
                              <div class="form-group mb-n col-md-2">
                                <div class="alert alert-dismissable mb-n p-n">
                                  <span ng-show="parametro.requiere_texto_adicional == 1">{{parametro.texto_adicional}}</span>
                                </div>
                              </div>
                              <!-- VALOR NORMAL -->
                              <div class="form-group mb-n col-md-2">
                                <div class="alert alert-dismissable mb-n p-n">
                                  <span ng-bind-html="parametro.valor_normal"></span>
                                </div>
                              </div>
                              <!-- METODO -->
                              <div class="form-group mb-n col-md-2">
                                <span class="help-block text-black m-n"> {{ parametro.metodo }} </span> 
                              </div> 
                            </div> 
                            
                            <div  class="form-group mb-n col-md-12 fila" ng-if="parametro.subparametros" ng-repeat="subparametro in parametro.subparametros">
                               <!-- SUBPARAMETRO -->
                               <div class="form-group mb-n col-md-4" >
                                <span class="m-n pl-xxl" >{{ subparametro.subparametro }} </span> 
                              </div>
                               <!-- RESULTADO -->
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 2 && subparametro.formula == 2 && parametro.idparametro != 272">
                                <input class="form-control input-sm" type="text" ng-model="subparametro.resultado" enter-as-tab/>
                              </div>

                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="parametro.idparametro == 272">
                                <input class="form-control input-sm" type="text" ng-model="subparametro.resultado" ng-change="calcularSumaPorcentajes({{$parent.$parent.$parent.$parent.$parent.$index}},{{$parent.$parent.$parent.$parent.$index}},{{$parent.$parent.$parent.$index}})" enter-as-tab/>
                              </div>
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 2 && subparametro.formula == 1">
                                <input class="form-control input-sm" type="text" ng-model="subparametro.resultado" ng-enter="calcularFormulaSubparametro(subparametro.idsubparametro,{{$parent.$parent.$parent.$parent.$parent.$index}},{{$parent.$parent.$parent.$parent.$index}},{{$parent.$parent.$parent.$index}});" enter-as-tab/>
                              </div>

                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 1 && parametro.idparametro == 57">
                                <select class="form-control input-sm" ng-model="subparametro.resultado" ng-options="item.id as item.descripcion for item in listaCaracteristica" enter-as-tab> </select>
                              </div>
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 1 && parametro.idparametro != 57">
                                <select class="form-control input-sm" ng-model="subparametro.resultado" ng-options="item.id as item.descripcion for item in subparametro.lista_combo" enter-as-tab> </select>
                              </div>

                              <!-- <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 1 && subparametro.idsubparametro == 175">
                                <select class="form-control input-sm" ng-model="subparametro.resultado" ng-options="item.id as item.descripcion for item in listaColorHeces" enter-as-tab> </select>
                              </div>
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 1 && subparametro.idsubparametro == 689">
                                <select class="form-control input-sm" ng-model="subparametro.resultado" ng-options="item.id as item.descripcion for item in listaColorOrina" enter-as-tab> </select>
                              </div>
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 1 && subparametro.idsubparametro == 84">
                                <select class="form-control input-sm" ng-model="subparametro.resultado" ng-options="item.id as item.descripcion for item in listaAspecto" enter-as-tab> </select>
                              </div>
                              <div class="form-group mb-n pl-n pr-n col-md-2" ng-if="subparametro.subcombo == 1 && subparametro.idsubparametro == 292 || subparametro.idsubparametro == 675">
                                <select class="form-control input-sm" ng-model="subparametro.resultado" ng-options="item.id as item.descripcion for item in listaGermenes" enter-as-tab> </select>
                              </div> -->
                               <!-- TEXTO ADICIONAL -->
                              <div class="form-group mb-n col-md-2">
                                <div class="alert alert-dismissable mb-n p-n">
                                  <span ng-show="subparametro.requiere_texto_adicional == 1">{{subparametro.texto_adicional}}</span>
                                </div>
                              </div>

                              <div class="form-group mb-n col-md-2">
                                <div class="alert alert-dismissable mb-n p-n">
                                  <span ng-bind-html="subparametro.valor_normal"></span>
                                </div>
                              </div>
                              <div class="form-group mb-n col-md-2">
                                <span class="help-block text-black m-n"> {{ parametro.metodo }} </span> 
                              </div> 
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                    
                    
                </fieldset>
                </div>
                <div class="panel-footer col-md-12 col-xs-12 mt-md">
                  <button class="btn btn-primary" ng-click="btnGuardar(); $event.preventDefault();" ng-disabled="formParametros.$invalid"> <i class="fa fa-save"> </i> [F2] Guardar </button>
                  <button class="btn btn-warning" ng-click="btnNuevo(); $event.preventDefault();"><i class="fa fa-file"> </i> [F3] Nuevo </button>
                  <button class="btn btn-danger" ng-click="btnVolver(); $event.preventDefault();"><i class="fa fa-rotate-left"> </i> Regresar </button>
                  <button type="button" class="btn btn-success ml-sm pull-right" ng-click="btnImprimirSel();" ><i class="fa fa-print"></i> [F4] IMPRIMIR</button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
