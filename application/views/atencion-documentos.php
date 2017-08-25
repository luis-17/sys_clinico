<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestión de Citas</li>
  <li class="active"> Atención de Documentos </li>
</ol>
<div class="container-fluid" ng-controller="atencionCittController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div>
                <h2>Atención de Documentos</h2> 
              </div>
              <div class="panel-body">
                <div class="col-xs-12" ng-show="!(fSessionCI.key_group == 'key_salud' || fSessionCI.key_group == 'key_dir_salud' || fSessionCI.key_group == 'key_dir_esp' 
                  || fSessionCI.key_group == 'key_salud_caja' || fSessionCI.key_group == 'key_coord_salud' || fSessionCI.key_group == 'key_salud_ocup')"> 
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> Acceso solo para Personal de Salud </div>
                </div>
                <div ng-show="(fSessionCI.key_group == 'key_salud' || fSessionCI.key_group == 'key_dir_salud' || fSessionCI.key_group == 'key_dir_esp' 
                  || fSessionCI.key_group == 'key_salud_caja' || fSessionCI.key_group == 'key_coord_salud' || fSessionCI.key_group == 'key_salud_ocup')">
                  <ul class="row demo-btns" ng-show="!registroFormularioAMA && !registroFormularioAP "> 
                      <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Filtro de Búsqueda </label> 
                        <div class="input-group block"> 
                          <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.tipoBusqueda" ng-change="onChangeFiltroBusqueda();" ng-options="item.id as item.descripcion for item in listaFiltroBusqueda" > </select>
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showHistoria" > <label> N° de Historia </label> 
                        <div class="input-group" style="width: 230px;"> 
                          <input tabindex="101" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroHistoria" placeholder="Digite N° de Historia" focus-me ng-enter="btnConsultarPacientesAtencion();"/>
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showPaciente" > <label> Paciente </label> 
                        <div class="input-group block"> 
                          <!-- <input tabindex="103" type="text" class="form-control input-sm" ng-model="fBusqueda.paciente" placeholder="Digite nombre del paciente" />  -->
                          <input type="text" ng-model="fBusqueda.paciente" class="form-control input-sm" tabindex="102" placeholder="Digite nombre del paciente" typeahead-loading="loadingLocations" 
                            uib-typeahead="item.descripcion as item.descripcion for item in getPacienteAutocomplete($viewValue)" typeahead-min-length="2" autocomplete ="off" /> 
                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                          <div ng-show="noResultsPACI">
                            <i class="fa fa-remove"></i> No se encontró resultados 
                          </div>
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-2 p-n" ng-show="showOrden"> <label> N° de Orden </label> 
                        <div class="input-group" style="width: 230px;"> 
                          <input tabindex="103" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroOrden" placeholder="Digite N° de Orden" />
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-sm-5 p-n" > 
                          <button tabindex="104" type="button" class="btn btn-success" ng-click="btnConsultarPacientesAtencion();"> <i class="fa fa-search"></i> BUSCAR PACIENTE </button> 
                          <!-- <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir();"><i class="fa fa-print"></i> IMPRIMIR </button>  -->
                      </li> 
                  </ul> 
                  <div class="row">
                    <div class="col-md-12">
                      <div class="panel panel-default mb-md panel-sm" data-widget='{"id" : "wiget10001"}'>
                        <div class="panel-heading">
                          <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div> 
                          <h2 ng-show="!registroFormularioAMA && !registroFormularioAP " class="tab-container"> 
                            <ul class="nav nav-tabs">
                              <li class="active"><a data-target="#ppa" href="" data-toggle="tab" ng-click="reloadGrid();" > PACIENTES POR ATENDER </a></li> 
                              <li class=""><a data-target="#pa" href="" data-toggle="tab" ng-click="reloadGrid(); getPaginationServerSidePAD();"> PACIENTES ATENDIDOS DEL DÍA </a></li> 
                            </ul>
                          </h2> 
                          <h2 ng-show="registroFormularioAMA" class="tab-container"> 
                            <ul class="nav nav-tabs"> 
                              <li class="active"><a style="height: inherit; font-size: 16px; border: 0px none; background-color: #fafafa;margin-top: -8px;" data-target="#rama" href="" data-toggle="tab"> RESULTADOS DE la evaluacion del documento </a></li> 
                            </ul>
                          </h2> 

                          <button ng-show="registroFormularioAMA" class="btn btn-warning pull-right mt-xs input-sm" type="button" ng-click="btnRegresarAlInicio();" style="font-size: 12px; width: 180px;" > 
                            <i class="fa fa-step-backward"></i> REGRESAR 
                          </button>
                          <!-- <button ng-show="registroFormularioAP" class="btn btn-warning pull-right mt-xs input-sm" type="button" ng-click="btnRegresarAlInicio();" style="font-size: 12px; width: 180px;" > 
                            <i class="fa fa-step-backward"></i> REGRESAR 
                          </button> -->
                        </div>
                        <div class="panel-body" ng-show="!registroFormularioAMA && !registroFormularioAP ">
                          <div class="tab-content">
                            <div class="tab-pane active" id="ppa">
                              <ul class="form-group demo-btns col-xs-12">
                                <li class="pull-right" ng-if="mySelectionGrid.length == 1"> 
                                  <button type="button" class="btn btn-midnightblue-alt" ng-click='btnAtenderAlPaciente()'>
                                    <i class="fa fa-thumbs-up"></i> ATENDER AL PACIENTE 
                                  </button>
                                </li>
                                <!-- <li class="pull-right" ng-if="mySelectionGrid.length == 1"><button type="button" class="btn btn-success" ng-click='btnConsultarEspecialidad("lg")'>Consultar Especialidades</button></li> -->
                              </ul> 
                              <div ui-grid="gridOptionsPPA" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"> 
                                <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsPPA.data.length"> Seleccione filtro de búsqueda y haga Click en  "BUSCAR PACIENTE" </div>
                              </div> 
                            </div>
                            <div class="tab-pane" id="pa">
                              <ul class="form-group demo-btns col-xs-12">
                                <li ><button class="btn btn-info" type="button" ng-click='btnToggleFilteringPAD()'> <i class="fa fa-search"></i> Buscar</button></li>
                                <li class="form-inline ml"> <label> Tipo de Atención </label>  
                                  <select ng-change="getPaginationServerSidePAD();" class="form-control input-sm" ng-model="fBusquedaPAD.idTipoAtencion" ng-options="item.id as item.descripcion for item in listaTipoAtencionMedica" > </select> 
                                </li> 
                                <li class="pull-right"><button class="btn btn-primary-alt" type="button" ng-click='getPaginationServerSidePAD();'> <i class="fa fa-refresh"></i> Actualizar</button></li>
                                <li class="pull-right" ng-if="mySelectionPADGrid.length == 1"> 
                                  <button type="button" class="btn btn-midnightblue-alt" ng-click='btnAtenderAlPaciente("si",mySelectionPADGrid)'> 
                                    <i class="fa fa-eye-slash"></i> CONSULTAR ATENCION 
                                  </button>
                                </li>
                                <!-- <li class="pull-right" ng-if="mySelectionPADGrid.length == 1"><button type="button" class="btn btn-success" ng-click='btnConsultarEspecialidad("lg")'>Consultar Especialidades</button></li> -->
                              </ul> 
                              <div ui-grid="gridOptionsPAD" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"> 
                                <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsPAD.data.length"> No se encontró registros de atenciones. </div>
                              </div> 
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- ============================= -->
                    <!-- FORMULARIO DE ATENCION DOCUMENTO -->
                    <!-- ============================= -->
                    <div ng-show="registroFormularioAMA" class="col-md-9"> 
                      <div class="row">
                        <div class="col-xs-12" style="line-height: 1.1; font-size: 95%;">
                          <fieldset class="row" >
                            <legend class="col-xs-12 mb-sm pb-n" style="font-size: 14px; font-weight: bold; border: none;"> 
                              <div class="block" style="background-color: #5d7581; color: white; text-align: center; line-height: 1.5;"> DATOS DEL PACIENTE </div>
                            </legend>
                            <div class="col-xs-12 form-inline mb-sm pl-xs"> 
                              <div class="form-group col-md-3 col-sm-6 col-xs-12">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Apellidos y Nombres </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.cliente }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Doc. de Identidad </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.numero_documento }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Sexo </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.sexo }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> N° Historia Clínica </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.idhistoria }} </span> 
                              </div>
                            </div>
                          </fieldset>
                        </div>
                        <div class="col-xs-12" style="line-height: 1.1; font-size: 95%;">
                          <fieldset class="row">
                            <legend class="col-xs-12  mb-sm pb-n" style="font-weight: bold; font-size: 14px; border: none;"> 
                              <div class="block" style="background-color: #5d7581; color: white; text-align: center; line-height: 1.5;"> ACTO MEDICO </div>
                            </legend> 
                            <div class="col-xs-12 form-inline mb-sm pl-xs"> 
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class=" mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> N° de Acto Médico </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n" style="text-transform: lowercase; font-style: oblique;"> {{ fData.num_acto_medico }} </span> 
                              </div> 
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class=" mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> N° Orden </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.orden }} </span> 
                              </div> 
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Area Hospitalaria </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.area_hospitalaria }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Profesional </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n" style="min-height: 16px;"> {{ fSessionCI.profesional }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Fecha de Atención </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.fechaAtencion }} -  
                                  <strong ng-show="fData.boolNumActoMedico"> {{ fData.horaAtencion }} </strong> 
                                </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Edad en la Atención </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.edadEnAtencion }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Especialidad </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n" style="font-weight: bold;"> {{ fData.especialidad }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs" >
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Actividad Específica </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.producto }} </span> 
                              </div> 
                            </div>
                          </fieldset>
                        </div>
                        <tabset tab-theme="default" tab-position="top" class="mb-n col-xs-12 tab-sm">
                          <tab heading="DOCUMENTO" class="{{tabs.estadoAtencionMedica}}" >
                            <form name="formAtencionAUX"> 
                              <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoAtencionMedica === 'enabled'"> 
                                <div class="row"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12" style="border-top: 1px solid #e0e0e0;"> 
                                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> REALIZACION DEL DOCUMENTO </div> 
                                    <div class="row"> 
                                      <div class="col-md-4"> 
                                        <div class="form-group mb-sm"> 
                                          <label class="m-n text-blue block"> Documento </label> 
                                          <input type="text" ng-model="fData.producto" class="form-control input-sm" disabled />  
                                        </div>
                                      </div>
                                      <div class="col-md-3" ng-if="documentoCitt"> 
                                        <div class="form-group mb-sm"> 
                                          <label class="m-n text-blue block"> Contingencia </label> 
                                          <input type="text" ng-model="fData.contingencia" class="form-control input-sm" disabled />  
                                        </div>
                                      </div> 
                                      <div class="col-md-2" ng-if="documentoCitt"> 
                                          <label class="m-n text-blue block"> Fecha de Inicio </label> 
                                          <div class="input-group">
                                            <input type="text" class="form-control input-sm" ng-model="fData.fecha_iniciodescanso" disabled="true" /> 
                                          </div>
                                      </div>
                                      <div class="col-md-1" ng-if="documentoCitt"> 
                                          <label class="m-n text-blue block"> Días </label> 
                                          <div class="input-group">
                                            <input type="text" class="form-control input-sm" ng-model="fData.dias" disabled="true" /> 
                                          </div>
                                      </div>
                                      <div class="col-md-2" ng-if="documentoCitt"> 
                                          <label class="m-n text-blue block"> Fecha Fin </label> 
                                          <div class="input-group">
                                            <input type="text" class="form-control input-sm" ng-model="fData.fecha_final" disabled="true" /> 
                                          </div>
                                      </div>
                                    </div>
                                    <div class="row" ng-if="!documentoCitt">
                                      <div class="col-md-12 col-xs-12"> 
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Informe/Resultado </label> 
                                          <textarea ng-model="fData.doc_informe" class="form-control input-sm" rows="6" required ></textarea>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div> 
                              <div class="block mt text-center"> 
                                <button class="btn btn-warning" ng-click="btnRegresarAlInicio(); $event.preventDefault();" style="width: 150px;"> 
                                  <i class="fa fa-step-backward"></i> Regresar 
                                </button> 
                                <button class="btn btn-success" ng-click="grabarAtencionCITT(); $event.preventDefault();" ng-disabled="formAtencionAUX.$invalid" style="width: 240px;"> 
                                  <i class="fa fa-edit"></i> Grabar 
                                </button>
                                <!-- <button class="btn-success btn" ng-click="imprimir(); $event.preventDefault();" ng-disabled="fData.num_acto_medico == '-- SIN REGISTRAR --'"> <i class="fa fa-print"> </i> [F4] Imprimir </button> -->
                              </div>

                            </form> 
                          </tab>
                        </tabset>
                      </div>
                    </div>

                    <!-- ============================= -->
                    <!--          OTROS LISTADOS       -->
                    <!-- ============================= -->
                    <div ng-show="registroFormularioAMA" class="col-md-3"> 
                      <div class="panel panel-midnightblue">
                        <div class="panel-heading" style="height: 30px;">
                          <h2 class="p-xs" style="font-size: 12px;">Afecciones Médica</h2> 
                        </div>
                        <div class="panel-body p-sm" style="height: 140px; overflow:auto;font-weight: bold;line-height: 1;">
                          <div class="block">
                            <label class="text-danger"><strong>ENFERMEDADES</strong></label> 
                            <ul >
                              <li ng-repeat="fila in gridOptionsAfe.data" ng-if="fila.tipoafeccion=='ENFERMEDAD'">{{fila.descripcion}}</li>
                              
                            </ul>
                          </div>
                          <div class="block">
                            <label class="text-danger"><strong>ALERGIAS</strong></label> 
                            <ul >
                              <li ng-repeat="fila in gridOptionsAfe.data" ng-if="fila.tipoafeccion=='ALERGIA'">{{fila.descripcion}}</li>
                              
                            </ul>
                          </div>
  
                        </div>
                      
                      </div>
                      <div class="panel panel-midnightblue">
                        <div class="panel-heading" style="height: 30px;">
                          <h2 class="p-xs" style="font-size: 12px;"> Últimos Medicamentos Recetados </h2> 
                        </div>
                        <div class="panel-body" style="height: 200px; overflow:auto;"> 

                        </div>
                      </div>
                      <div class="panel panel-midnightblue">
                        <div class="panel-heading" style="height: 30px;">
                          <h2 class="p-xs" style="font-size: 12px;"> Últimos Exámenes Auxiliares </h2> 
                        </div>
                        <div class="panel-body" style="height: 140px; overflow:auto;"> 

                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>