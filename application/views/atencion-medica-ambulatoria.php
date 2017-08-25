<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Citas</li>
  <li class="active" ng-if="fSessionCI.idespecialidad != 28 "> Atención Médica </li>
  <li class="active" ng-if="fSessionCI.idespecialidad == 28 "> Atención Odontológica </li>
</ol>
<div class="container-fluid" ng-controller="atencionMedicaAmbController" ng-init="initAtencionMed()">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div>
                <h2 ng-if="fSessionCI.idespecialidad != 28 ">Atención Médica Ambulatoria</h2> 
                <h2 ng-if="fSessionCI.idespecialidad == 28 ">Atención Odontológica</h2> 
              </div>
              <div class="panel-body">
                <div class="col-xs-12" ng-show="!(fSessionCI.key_group == 'key_salud' || fSessionCI.key_group == 'key_dir_salud' || 
                  fSessionCI.key_group == 'key_dir_esp' || fSessionCI.key_group == 'key_salud_caja'  || fSessionCI.key_group == 'key_coord_salud')"> 
                  <div class="waterMarkEmptyData" style="position:relative; top:inherit; font-size: 28px;"> Acceso solo para Personal de Salud </div>
                </div>
                <div ng-show="(fSessionCI.key_group == 'key_salud' || fSessionCI.key_group == 'key_dir_salud' || fSessionCI.key_group == 'key_dir_esp' 
                  || fSessionCI.key_group == 'key_salud_caja' || fSessionCI.key_group == 'key_coord_salud')"> 
                  <ul class="row demo-btns" ng-show="!registroFormularioAMA && !registroFormularioAP "> 
                      <li class="form-group mr mt-sm col-md-2 col-sm-4 p-n col-xs-6 p-n" > <label> Filtro de Búsqueda </label> 
                        <div class="input-group block"> 
                          <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.tipoBusqueda" ng-change="onChangeFiltroBusqueda();" ng-options="item.id as item.descripcion for item in listaFiltroBusqueda" ng-disabled="tieneProgramacion"> </select>
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-md-2 col-sm-4 p-n col-xs-6 p-n" ng-show="showOrden"> <label> N° de Orden </label> 
                        <div class="input-group" style="width: 90%;"> 
                          <input tabindex="101" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroOrden" placeholder="Digite N° de Orden" />
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-md-2 col-sm-4 p-n col-xs-6 p-n" ng-show="showHistoria" > <label> N° de Historia </label> 
                        <div class="input-group" style="width: 90%;"> 
                          <input tabindex="102" type="text" class="form-control input-sm" ng-model="fBusqueda.numeroHistoria" placeholder="Digite N° de Historia" focus-me ng-enter="btnConsultarPacientesAtencion();"/>
                        </div>
                      </li>
                      <li class="form-group mr mt-sm col-md-2 col-sm-4 p-n col-xs-6" ng-show="showPaciente" > <label> Paciente </label> 
                        <div class="input-group block"> 
                          <!-- <input tabindex="103" type="text" class="form-control input-sm" ng-model="fBusqueda.paciente" placeholder="Digite nombre del paciente" />  -->
                          <input type="text" ng-model="fBusqueda.paciente" class="form-control input-sm" tabindex="103" placeholder="Digite nombre del paciente" typeahead-loading="loadingLocations" 
                            uib-typeahead="item.descripcion as item.descripcion for item in getPacienteAutocomplete($viewValue)" typeahead-min-length="2" autocomplete ="off"/> 
                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                          <div ng-show="noResultsPACI">
                            <i class="fa fa-remove"></i> No se encontró resultados. 
                          </div>
                        </div>
                      </li>
                      <li class="form-group mr mt-md col-md-2 col-sm-4 p-n" > 
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
                              <li class="active"><a data-target="#ppa" href="" data-toggle="tab" ng-click="" > PACIENTES POR ATENDER </a></li> 
                              <li class=""><a data-target="#pa" href="" data-toggle="tab" ng-click=" getPaginationServerSidePAD();"> PACIENTES ATENDIDOS DEL DÍA </a></li> 
                            </ul>
                          </h2> 
                          <h2 ng-show="registroFormularioAMA" class="tab-container"> 
                            <ul class="nav nav-tabs"> 
                              <li class="active" ng-if="fData.idespecialidad != 28 "><a style="height: inherit; font-size: 16px; border: 0px none; background-color: #fafafa;margin-top: -8px;" data-target="#rama" href="" data-toggle="tab"> REGISTRO DE ATENCIÓN MÉDICA AMBULATORIA </a></li>
                              <li class="active" ng-if="fData.idespecialidad == 28 "><a style="height: inherit; font-size: 16px; border: 0px none; background-color: #fafafa;margin-top: -8px;" data-target="#rama" href="" data-toggle="tab"> REGISTRO DE ATENCIÓN ODONTOLÓGICA </a></li> 
                            </ul>
                          </h2> 
                          <h2 ng-show="registroFormularioAP" class="tab-container"> 
                            <ul class="nav nav-tabs"> 
                              <li class="active"><a style="height: inherit; font-size: 16px; border: 0px none; background-color: #fafafa;margin-top: -8px;" data-target="#rap" href="" data-toggle="tab"> REGISTRO DE ATENCION DE PROCEDIMIENTO </a></li> 
                            </ul>
                          </h2> 
                          <button ng-show="registroFormularioAMA" class="btn btn-warning pull-right mt-xs input-sm" type="button" ng-click="btnRegresarAlInicio();" style="font-size: 12px; width: 180px;" > 
                            <i class="fa fa-step-backward"></i> REGRESAR 
                          </button>
                          <button ng-show="registroFormularioAP" class="btn btn-warning pull-right mt-xs input-sm" type="button" ng-click="btnRegresarAlInicio();" style="font-size: 12px; width: 180px;" > 
                            <i class="fa fa-step-backward"></i> REGRESAR 
                          </button>
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
                              <div ui-grid="gridOptionsPPA" ui-grid-pagination ui-grid-auto-resize ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid "> 
                                <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsPPA.data.length"> Seleccione filtro de búsqueda y haga Click en  "BUSCAR PACIENTE" </div>
                              </div> 
                            </div>
                            <div class="tab-pane" id="pa">
                              <ul class="form-group demo-btns col-xs-12">
                                <li><button class="btn btn-info" type="button" ng-click='btnToggleFilteringPAD()'> <i class="fa fa-search"></i> Buscar</button></li>
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
                              <div ui-grid="gridOptionsPAD" ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"> 
                                <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsPAD.data.length"> No se encontró registros de atenciones. </div>
                              </div> 
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- ============================= -->
                    <!-- FORMULARIO DE ATENCION MÉDICA -->
                    <!-- ============================= -->
                    <div ng-if="registroFormularioAMA" class="col-md-9">
                      <div class="row">
                        <div class="col-xs-12" style="line-height: 1.1; font-size: 95%;">
                          <fieldset class="row" >
                            <legend class="col-xs-12 mb-sm pb-n" style="font-size: 14px; font-weight: bold; border: none;"> 
                              <div class="block" style="background-color: #5d7581; color: white; text-align: center; line-height: 1.5;"> DATOS DEL PACIENTE </div>
                            </legend>
                            <div class="col-xs-12 form-inline mb-sm pl-xs"> 
                              <div class="form-group col-md-3 col-sm-6 col-xs-12">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Nombres y Apellidos  </label>
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
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.fechaAtencion }} </span> 
                              </div>
                              <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs" ng-show="fData.boolNumActoMedico">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Hora de Atención </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fData.horaAtencion }} </span> 
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
                              <!-- <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                                <div class="mb-n">
                                  <label for="inputHelpBlock" class="m-n text-blue"> Empresa </label>
                                </div>
                                <span id="helpBlock" class="help-block text-black m-n"> {{ fSessionCI.empresa }} </span> 
                              </div> -->
                            </div>
                          </fieldset>
                        </div>
                        <tabset tab-theme="default" tab-position="top" class="mb-n col-xs-12 tab-sm">
                          <tab heading="ATENCION MEDICA" class="{{tabs.estadoAtencionMedica}}" ng-click="verAccionDiagnostico();" >
                            <form name="formAtencionMedicaAmb">
                              
                              <div class="block" style="max-height: 400px;" ng-show="tabs.estadoAtencionMedica === 'enabled'" scroll-glue="glued">
                                <div class="row" ng-if="fData.boolSexo == 'F'"> 
                                  <div class="col-md-2 col-sm-4 col-xs-12 mb-sm"> 
                                    <div class="form-group mb-n"> 
                                      <label class="m-n text-blue"> ¿Gestando? </label> 
                                      <select ng-disabled="fData.boolNumActoMedico" tabindex="105" ng-model="fData.fInputs.gestando" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolGestando" > </select> 
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fData.fInputs.gestando == 1"> 
                                    <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Ultima Regla (FUR) </label> 
                                      <input ng-disabled="fData.boolNumActoMedico" tabindex="106" type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fData.fInputs.fur" ng-change="calculateSemanaGestacion();" /> 
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fData.fInputs.gestando == 1"> 
                                    <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Semana de Gestación </label> 
                                      <input tabindex="107" ng-model="fData.fInputs.semana_gestacion" class="form-control input-sm" placeholder="Semana de Gestación" disabled="true" /> 
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fData.fInputs.gestando == 1"> 
                                    <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Fecha probable de parto </label> 
                                      <input ng-model="fData.fInputs.fpp" class="form-control input-sm" placeholder="Fecha probable de parto(FPP)" disabled="true" /> 
                                    </div>
                                  </div>
                                </div>
                                <div class="row"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> ANAMNESIS </div> 
                                    <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Anamnesis <small class="text-danger">(*)</small> </label> 
                                      <textarea required tabindex="108" ng-model="fData.fInputs.anamnesis" class="form-control input-sm" placeholder="Digite la Anamnesis" ></textarea> 
                                    </div>
                                  </div>
                                </div>
                                <div class="row" ng-if="fData.idespecialidad != 28 "> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> SIGNOS VITALES </div> 
                                    <div class="row"> 
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-inline">
                                          <label class="m-n text-blue block"> Presión Arterial <small class="text-gray"> (Mm Hg) </small> </label> 
                                          <input tabindex="109" type="text" ng-model="fData.fInputs.presion_arterial_mm" class="form-control input-sm" style="width: 60px;" /> / 
                                          <input tabindex="110" type="text" ng-model="fData.fInputs.presion_arterial_hg" class="form-control input-sm" style="width: 60px;" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Frecuencia Cardíaca <small class="text-gray"> (Latidos x Min.) </small> </label> 
                                          <input tabindex="111" type="text" ng-model="fData.fInputs.frecuencia_cardiaca_lxm" class="form-control input-sm" style="width: 100px;" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Temperatura Corporal <small class="text-gray"> (°C) </small> </label> 
                                          <input tabindex="112" type="text" ng-model="fData.fInputs.temperatura_corporal" class="form-control input-sm" style="width: 100px;" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Frecuencia Respiratoria <small class="text-gray"> (Por Minuto) </small> </label> 
                                          <input tabindex="113" type="text" ng-model="fData.fInputs.frecuencia_respiratoria" class="form-control input-sm" style="width: 100px;" /> 
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="row" ng-if="fData.idespecialidad != 28 "> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> ANTROPOMETRÍA </div>
                                    <div class="row"> 
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Peso <small class="text-gray"> (Kg.) </small> </label> 
                                          <input tabindex="114" type="text" ng-change="calculateIMC();" ng-model="fData.fInputs.peso" class="form-control input-sm" style="width: 100px;" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Talla <small class="text-gray"> (m) </small> </label> 
                                          <input tabindex="115" type="text" ng-change="calculateIMC();" ng-model="fData.fInputs.talla" class="form-control input-sm" style="width: 100px;" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> IMC <small class="text-gray"> (%) </small> </label> 
                                          <input tabindex="116" type="text" ng-model="fData.fInputs.imc" class="form-control input-sm" style="width: 100px;" disabled="true" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-3 col-xs-6">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Perímetro Abdominal <small class="text-gray"> (cm) </small> </label> 
                                          <input tabindex="117" type="text" ng-model="fData.fInputs.perimetro_abdominal" class="form-control input-sm" style="width: 100px;" /> 
                                        </div>
                                      </div>
                                      <div class="col-md-7 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Examen Clínico <small class="text-danger">(*)</small> </label> 
                                          <textarea tabindex="118" type="text" ng-model="fData.fInputs.examen_clinico" class="form-control input-sm" placeholder="Digite el Examen Clínico" required > </textarea> 
                                        </div>
                                      </div>
                                      <div class="col-md-5 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Antecedentes </label> 
                                          <textarea tabindex="118" type="text" ng-model="fData.fInputs.antecedentes" class="form-control input-sm" placeholder="Digite los antecedentes" > </textarea> 
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="row" ng-if="fData.idespecialidad != 28 "> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES </div>
                                    <div class="row"> 
                                      <div class="col-md-10 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Plan de trabajo, Comentarios y/o Observaciones </label> 
                                          <textarea tabindex="119" type="text" ng-model="fData.fInputs.observaciones" class="form-control input-sm" placeholder="Digite el Plan de trabajo, Comentarios y/o Observaciones" > </textarea> 
                                        </div>
                                      </div>
                                      <div class="col-md-2 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> ¿Atención de Control? </label> 
                                          <select required tabindex="120" ng-model="fData.fInputs.atencion_control" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolAtencionControl" > </select> 
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <!-- SOLO PARA ODONTOLOGIA -->
                                <div class="row" ng-if="fData.idespecialidad == 28 "> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                    <div class="mb-sm bg-darkgray text-white text-center">  EXAMEN CLINICO  </div>
                                    <div class="row"> 
                                      <div class="col-md-10 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Examen Clínico </label> 
                                          <textarea required tabindex="119" type="text" ng-model="fData.fInputs.examen_clinico" class="form-control input-sm" placeholder="Digite el Examen Clínico" > </textarea> 
                                        </div>
                                      </div>
                                      <div class="col-md-2 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> ¿Atención de Control? </label> 
                                          <select required tabindex="120" ng-model="fData.fInputs.atencion_control" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolAtencionControl" > </select> 
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <!--  -->
                                <div class="row"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> DIAGNOSTICO </div>
                                    <div class="row"> 
                                      <div class="col-md-12 col-xs-12">
                                        <div class="form-group mb-sm">
                                          <label class="m-n text-blue block"> Diagnóstico </label>
                                          <div class="input-group">
                                            <span class="input-group-btn "> 
                                              <input id="codDiagnostico" tabindex="121" type="text" class="form-control" 
                                                style="width:70px;margin-right:4px;" ng-model="fData.fTemporalDiag.codigo_diagnostico" placeholder="COD." ng-change="onChangeGetDiagnostico();" />
                                            </span>
                                            <input type="text" ng-model="fData.fTemporalDiag.diagnostico" class="form-control" tabindex="122" placeholder="Digite el código del diagnóstico para autocompletar" typeahead-loading="loadingLocations" 
                                              autocomplete="off" uib-typeahead="item as item.descripcion for item in getDiagnosticosAutocomplete($viewValue)" typeahead-min-length="2" typeahead-on-select="onSelectDiagnostico($item, $model, $label)" ng-disabled="true" />
                                              
                                            <!-- <span class="input-group-btn"><button><i class="fa fa-search"></i></button></span> -->
                                            <span class="input-group-btn ">
                                              <button type="button" tooltip="BUSCAR DIAGNOSTICO CIE-10" class="btn btn-default" style="height:32px" ng-click="VerDiagnosticos(); $event.preventDefault();"><i class="ti ti-search"></i></button>
                                              <input tabindex="123" type="button" class="btn btn-default" value="AGREGAR" ng-click="agregarDiagnosticoACesta();$event.preventDefault();" style="font-size: 12px; min-width: 160px;height:32px" /> 
                                            </span>
                                          </div>
                                          <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                                          <div ng-show="noResultsLEESS">
                                            <i class="fa fa-remove"></i> No se encontró resultados 
                                          </div>
                                        </div>
                                      </div>
                                      <div class="col-md-12 col-xs-12"> 
                                        <div ui-if="gridOptionsDiagnostico.data.length>0" ui-grid="gridOptionsDiagnostico" ui-grid-edit ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="block mt text-center"> 
                                <button type="button" class="btn btn-warning" ng-click="btnRegresarAlInicio(); $event.preventDefault();" style="width: 150px;"> 
                                  <i class="fa fa-step-backward"></i> Regresar 
                                </button> 
                                <button class="btn btn-success" ng-click="grabarAtencionMedica(); $event.preventDefault();" ng-disabled="formAtencionMedicaAmb.$invalid" style="width: 240px;"> 
                                  <i class="fa fa-edit"></i> Grabar 
                                </button> 
                              </div> 
                            </form>
                          </tab>
                          <!-- PESTAÑA DE PROCEDIMIENTO -->
                          <tab heading="PROCEDIMIENTO" class="{{tabs.estadoProcedimiento}}" ng-click="">
                              <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoProcedimiento === 'enabled'">
                                <div class="row" ng-show="!formSolicitudProcedimiento"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> PROCEDIMIENTOS SOLICITADOS/REALIZADOS </div> 
                                  </div>
                                  <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                    <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Desde </label> 
                                      <input type="text" class="form-control input-sm" ng-model="fBusquedaPROC.desde" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                    </div>
                                  </div>
                                  <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                    <div class="form-group mb-n">
                                        <label class="m-n text-blue"> Hasta </label> 
                                        <input type="text" class="form-control input-sm" ng-model="fBusquedaPROC.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                    </div>
                                  </div>
                                  <div class=" col-lg-8 col-md-12 col-sm-12 col-xs-12 mt-sm"> 
                                    <div class="form-group mb-n">
                                      <button type="button" class="btn btn-success" ng-click="getPaginationPROCServerSide(); $event.preventDefault();"> <i class="fa fa-refresh"></i> PROCESAR </button> 
                                      <button ng-show="gridOptionsProcedimientos.data.length>0" type="button" class="btn btn-info" ng-click="btnImprimirProc();"><i class="fa fa-print"></i> IMPRIMIR </button> 
                                      <button type="button" class="btn btn-midnightblue pull-right" ng-click="btnVerFormRegistrarProc(); $event.preventDefault();"><i class="fa fa-file"></i> NUEVO PROCEDIMIENTO </button> 
                                    </div>
                                  </div>
                                </div>
                                <div class="row" ng-show="!formSolicitudProcedimiento"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                    <div ui-grid="gridOptionsProcedimientos" ui-grid-auto-resize ui-grid-edit ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="overflow: hidden;">
                                    </div>
                                  </div>
                                </div>
                                <!-- FORMULARIO DE NUEVO PROCEDIMIENTO -->
                                <div class="row" ng-show="formSolicitudProcedimiento"> 
                                    <form name="formProcedimiento"> 
                                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                        <div class="mb-sm bg-darkgray text-white text-center"> AGREGAR PROCEDIMIENTO </div>
                                        <div class="row">                                      
                                          <div class="col-md-4 col-xs-4">
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Especialidad <small class="text-danger">(*)</small></label> 
                                              <select class="form-control input-sm" ng-model="fDataProc.especialidad" 
                                              ng-options="item as item.descripcion for item in listaEspecialidades" > </select> 
                                            </div>
                                          </div>
                                          <div class="col-md-4 col-xs-4"> 
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Procedimiento <small class="text-danger">(*)</small></label> 
                                              <input type="text" ng-model="fDataProc.procedimiento" class="form-control input-sm" placeholder="Digite el procedimiento para autocompletar" typeahead-loading="loadingLocations" 
                                                uib-typeahead="item as item.descripcion for item in getProcedimientoAutocomplete($viewValue)" typeahead-min-length="2" typeahead-on-select="onSelectProcedimiento($item, $model, $label)" required /> 
                                              <!-- <input tabindex="114" type="text" ng-model="fDataProc.procedimiento" class="form-control input-sm" required />  --> 
                                              <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                                              <div ng-show="noResultsLPAC">
                                                <i class="fa fa-remove"></i> No se encontró resultados 
                                              </div>
                                            </div>
                                          </div> 
                                          <div class="col-md-4 col-xs-4">
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Cantidad Realizada <small class="text-danger">(*)</small></label> 
                                              <input type="text" ng-model="fDataProc.cantidad" class="form-control input-sm" required ng-init="fDataProc.cantidad = 1" /> 
                                            </div>
                                          </div> 
                                          <div class="col-md-12 col-xs-12"> 
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Observaciones </label> 
                                              <textarea ng-model="fDataProc.observacion" class="form-control input-sm" placeholder="Digite observaciones"></textarea>
                                            </div>
                                          </div>
                                          <div class="col-md-12 col-xs-12"> 
                                            <div class="form-group mb-sm text-right">
                                              <button class="btn btn-primary" ng-click="registrarProcedimientoEnAtencion();" ng-disabled="formProcedimiento.$invalid"> 
                                               AGREGAR PROCEDIMIENTO </button> 
                                              <button type="button" class="btn btn-warning" ng-click="btnRegresarAlListadoProc(); $event.preventDefault();"> 
                                                <i class="fa fa-step-backward"></i> REGRESAR </button>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </form>
                                </div>
                              </div>
                              <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="!(tabs.estadoProcedimiento === 'enabled')">
                                <div style="position: relative; top: inherit;" class="waterMarkEmptyData"> Primero debe registrar la atención médica </div> 
                              </div>
                          </tab>
                          <!-- PESTAÑA DE RECETA -->
                          <tab heading="RECETA" class="{{tabs.estadoReceta}}">
                            <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoReceta === 'enabled'"> 
                              <div class="row" ng-show="!formRecetaMedica"> 
                                <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                  <div class="mb-sm bg-darkgray text-white text-center"> RECETAS MÉDICAS </div> 
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                  <div class="form-group mb-n">
                                    <label class="m-n text-blue"> Desde </label> 
                                    <input type="text" class="form-control input-sm" ng-model="fBusquedaREC.desde" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                  </div>
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                  <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Hasta </label> 
                                      <input type="text" class="form-control input-sm" ng-model="fBusquedaREC.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                  </div>
                                </div>
                                <div class=" col-lg-8 col-md-12 col-sm-12 col-xs-12 mt-sm"> 
                                  <div class="form-group mb-n">
                                    <button type="button" class="btn btn-success" ng-click="getPaginationRECServerSide(); $event.preventDefault();"> <i class="fa fa-refresh"></i> PROCESAR </button> 
                                    
                                    <button type="button" class="btn btn-midnightblue pull-right" ng-click="btnVerFormRegistrarReceta(); $event.preventDefault();"><i class="fa fa-file"></i> NUEVA RECETA </button>
                                    <button ng-if="mySelectionRECGrid.length > 0" type="button" class="btn btn-info pull-right mr" ng-click="btnImprimirRecetaPdf(); $event.preventDefault();"><i class="fa fa-print"></i> IMPRIMIR </button>

                                    
                                    <!-- <button type="button" class="btn btn-info pull-right mr" ng-click="btnBusquedaMedicamentos(); $event.preventDefault();"><i class="fa fa-search"></i> MEDICAMENTOS </button>  -->
                                  </div>
                                </div>
                              </div>
                              <div class="row" ng-show="!formRecetaMedica"> 
                                <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                  <div ui-grid="gridOptionsRecetaMedica" ui-grid-auto-resize ui-grid-edit ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive" style="overflow: hidden;"></div>
                                </div>
                              </div>
                              <!-- FORMULARIO DE NUEVA RECETA -->
                              <div class="row" ng-show="formRecetaMedica"> 
                                  <form name="formReceta" id="formReceta"> 
                                    <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                      <div class="mb-sm bg-darkgray text-white text-center"> NUEVA RECETA </div>
                                      <div class="row"> 
                                        <div class="col-md-4 col-xs-4"> 
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Medicamento </label> 
                                            <input id="fTemporalmedicamento" type="text" ng-model="fDataREC.fTemporal.medicamento" class="form-control input-sm" placeholder="Digite el medicamento para autocompletar" typeahead-loading="loadingLocations" 
                                              uib-typeahead="item as item.medicamento_stock for item in getMedicamentoAutocomplete($viewValue)" typeahead-on-select="getSelectedMedicamento($item, $model, $label)" typeahead-min-length="3" /> 
                                            <!-- <input tabindex="114" type="text" ng-model="fDataREC.procedimiento" class="form-control input-sm" required />  --> 
                                            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                                            <div ng-show="noResultsMEDI">
                                              <i class="fa fa-remove"></i> No se encontró resultados 
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-md-1 col-xs-2">
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Stock </label> 
                                            <input type="text" ng-model="fDataREC.fTemporal.medicamento.stock" class="form-control input-sm" readonly="true" /> 
                                          </div>
                                        </div>
                                        <div class="col-md-1 col-xs-2 p-n">
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Forma Far. </label> 
                                            <input type="text" ng-model="fDataREC.fTemporal.medicamento.formafarmaceutica" class="form-control input-sm" readonly="true" /> 
                                          </div>
                                        </div>
                                        <div class="col-md-1 col-xs-2">
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Cantidad </label> 
                                            <input id="fTemporalCantidad" type="text" ng-model="fDataREC.fTemporal.cantidad" class="form-control input-sm" /> 
                                          </div>
                                        </div>
                                        <div class="col-md-4 col-xs-5"> 
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Indicaciones </label> 
                                            <input type="text" ng-model="fDataREC.fTemporal.indicacion" class="form-control input-sm"  placeholder="Dosis, Via de Administración, Frecuencia, Duración, etc" />
                                            <!-- <textarea ng-model="fDataREC.indicacion" class="form-control input-sm" required ></textarea> --> 
                                          </div>
                                        </div>
                                        <div class="col-md-1 col-xs-1" style="margin-top: 15px;"> 
                                            <button tooltip-placement="left" tooltip="AGREGAR" type="button" class="btn btn-info-alt" ng-click="agregarMedicamentoAReceta(); $event.preventDefault();"> <i class="fa fa-plus"></i> </button> 
                                        </div> 
                                        <div class="form-group col-xs-12 m-n">
                                          <label class="control-label"> Medicamentos agregados: </label>
                                          <div ui-if="gridOptionsMedicamentosAdd.data.length>0" ui-grid="gridOptionsMedicamentosAdd" ui-grid-cellNav ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeightMED();"></div>
                                        </div>
                                        <div class="col-md-12 col-xs-12 mt"> 
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Indicaciones Generales </label> 
                                            <textarea ng-model="fDataREC.indicaciones_generales" class="form-control input-sm"  placeholder="Indicaciones Generales" > </textarea> 
                                          </div>
                                        </div>
                                        <div class="col-md-12 col-xs-12 mt"> 
                                          <div class="form-group mb-sm text-right"> 
                                            <button type="button" class="btn btn-primary" ng-click="registrarRecetaMedica(); $event.preventDefault();"> 
                                             GRABAR RECETA </button> 
                                            <button type="button" class="btn btn-warning" ng-click="btnRegresarAlListadoReceta(); $event.preventDefault();"> 
                                              <i class="fa fa-step-backward"></i> REGRESAR </button> 
                                          </div> 
                                        </div>
                                      </div>
                                    </div>
                                  </form>
                              </div>
                            </div>
                            <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="!(tabs.estadoReceta === 'enabled')">
                              <div style="position: relative; top: inherit;" class="waterMarkEmptyData"> Primero debe registrar la atención médica </div> 
                            </div> 
                          </tab> 
                          <!-- PESTAÑA DE EXAMEN AUXILIAR -->
                          <tab heading="EXAMEN AUXILIAR" class="{{tabs.estadoExamenAuxiliar}}"> 
                            <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoExamenAuxiliar === 'enabled'"> 
                              <div class="row" ng-show="!contSolicitudExamenAuxiliar"> 
                                <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                  <div class="mb-sm bg-darkgray text-white text-center"> EXAMENES AUXILIARES SOLICITADOS </div> 
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-sm text-center"> 
                                  <div class="p-sm" style="border: 1px solid #5d7581;">
                                    <label> SOLICITAR NUEVO EXAMEN DE :  </label>
                                    <div class="inline">
                                      <button type="button" class="btn btn-midnightblue-alt btn-sm" ng-click="btnVerFormRegistrarExamenAux('I'); $event.preventDefault();"> IMAGENOLOGIA </button> 
                                    </div>
                                    <div class="inline">
                                      <button type="button" class="btn btn-midnightblue-alt btn-sm" ng-click="btnVerFormRegistrarExamenAux('PC'); $event.preventDefault();"> LABORATORIO </button> 
                                    </div>
                                    <div class="inline">
                                      <button type="button" class="btn btn-midnightblue-alt btn-sm" ng-click="btnVerFormRegistrarExamenAux('AP'); $event.preventDefault();"> ANATOMIA PATOLOGICA </button> 
                                    </div>
                                  </div>
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                  <div class="form-group mb-n">
                                    <label class="m-n text-blue"> Tipo de Examen </label> 
                                    <select tabindex="100" class="form-control input-sm" ng-model="fBusquedaAUX.tipoExamen" ng-options="item.id as item.descripcion for item in listaTipoExamen" > </select>
                                  </div>
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 ">
                                  <div class="form-group mb-n">
                                    <label class="m-n text-blue"> Desde </label> 
                                    <input type="text" class="form-control input-sm" ng-model="fBusquedaAUX.desde" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                  </div>
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                  <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Hasta </label> 
                                      <input type="text" class="form-control input-sm" ng-model="fBusquedaAUX.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                  </div>
                                </div>
                                <div class=" col-lg-6 col-md-12 col-sm-12 col-xs-12 mt-sm"> 
                                  <div class="form-group mb-n">
                                    <button type="button" class="btn btn-success" ng-click="getPaginationAUXServerSide(); $event.preventDefault();"> <i class="fa fa-refresh"></i> PROCESAR </button> 
                                    <button ng-show="gridOptionsExamenAuxiliar.data.length>0" type="button" class="btn btn-info" ng-click="btnImprimirListadoExamen(); $event.preventDefault();"><i class="fa fa-print"></i> IMPRIMIR </button> 
                                  </div>
                                </div>
                              </div>
                              <div class="row" ng-show="!contSolicitudExamenAuxiliar"> 
                                <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                  <div ui-grid="gridOptionsExamenAuxiliar" ui-grid-auto-resize ui-grid-edit ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid" style="overflow: hidden;"></div>
                                </div>
                              </div>
                              <!-- FORMULARIO DE NUEVO EXAMEN AUXILIAR --> 
                              <div class="row" ng-show="contSolicitudExamenAuxiliar"> 
                                  <form name="formSolicitudExamenAuxiliar"> 
                                    <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                        <div class="mb-sm bg-darkgray text-white text-center"> SOLICITUD DE EXAMENES AUXILIARES - {{ fDataAUX.tipoExamen }} </div>
                                        <div class="row">
                                          <div class="col-md-4 col-xs-4"> 
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Especialidad <small class="text-danger">(*)</small></label> 
                                              <select class="form-control input-sm" ng-model="fDataAUX.especialidad" 
                                              ng-options="item as item.descripcion for item in listaEspecialidades" > </select> 
                                            </div>
                                          </div>
                                          <div class="col-md-4 col-xs-4"> 
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> {{ fDataAUX.tipoExamen }} <small class="text-danger">(*)</small></label> 
                                              <input type="text" ng-model="fDataAUX.examen_auxiliar" class="form-control input-sm" placeholder="Digite el examen auxiliar para autocompletar" typeahead-loading="loadingLocations" 
                                                uib-typeahead="item as item.descripcion for item in getExamenAuxiliarAutocomplete($viewValue)" typeahead-min-length="2" required /> 
                                              <!--  typeahead-on-select="onSelectProcedimiento($item, $model, $label)" --> 
                                              <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                                              <div ng-show="noResultsAUX">
                                                <i class="fa fa-remove"></i> No se encontró resultados 
                                              </div>
                                            </div>
                                          </div> 
                                          <div class="col-md-12 col-xs-12"> 
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Indicaciones <small class="text-danger">(*)</small></label> 
                                              <textarea ng-model="fDataAUX.indicaciones" class="form-control input-sm" required placeholder="Digite las indicaciones pertinentes"></textarea>
                                            </div>
                                          </div>
                                          <div class="col-md-12 col-xs-12"> 
                                            <div class="form-group mb-sm text-right">
                                              <button type="button" class="btn btn-primary" ng-click="registrarExamenAuxiliarEnAtencion();"ng-disabled="formSolicitudExamenAuxiliar.$invalid"> 
                                               AGREGAR {{ fDataAUX.tipoExamen }} </button> 
                                              <button type="button" class="btn btn-warning" ng-click="btnRegresarAlListadoAux();"> 
                                                <i class="fa fa-step-backward"></i> REGRESAR </button>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                  </form>
                              </div>
                            </div> 
                            <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="!(tabs.estadoExamenAuxiliar === 'enabled')"> 
                              <div style="position: relative; top: inherit;" class="waterMarkEmptyData"> Primero debe registrar la atención médica </div> 
                            </div> 
                          </tab> 
                          <!-- PESTAÑA DE OTRAS ATENCIONES -->
                          <tab heading="OTRAS ATENCIONES" class="{{tabs.estadoOtrasAtenciones}}" ng-click="getPaginationServerSideOAT();">
                            <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                              <div class="row">
                                <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                  <div class="mb-sm bg-darkgray text-white text-center"> OTRAS ATENCIONES  </div> 
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                  <div class="form-group mb-n">
                                    <label class="m-n text-blue"> Desde </label> 
                                    <input type="text" class="form-control input-sm" ng-model="fBusquedaOAT.desde" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                  </div>
                                </div>
                                <div class="col-lg-2 col-md-6 col-sm-6 col-xs-12 "> 
                                  <div class="form-group mb-n">
                                      <label class="m-n text-blue"> Hasta </label> 
                                      <input type="text" class="form-control input-sm" ng-model="fBusquedaOAT.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" style="width:auto;" />
                                  </div>
                                </div>
                                <div class=" col-lg-8 col-md-12 col-sm-12 col-xs-12 mt-sm"> 
                                  <div class="form-group mb-n">
                                    <button type="button" class="btn btn-success" ng-click="getPaginationServerSideOAT(); $event.preventDefault();"> <i class="fa fa-refresh"></i> PROCESAR </button> 
                                    <!-- <button ng-show="gridOptionsOAT.data.length>0" type="button" class="btn btn-info" ng-click="btnImprimirOtrasAtenciones(); $event.preventDefault();"><i class="fa fa-print"></i> IMPRIMIR </button> --> 
                                  </div>
                                </div>
                              </div>
                              <!-- <p>OTRAS ATENCIONES</p> -->
                              <ul class="form-group demo-btns col-xs-12">
                                <!-- <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'> <i class="fa fa-search"></i> Buscar</button></li> -->
                                <li class="pull-right" ng-if="mySelectionOATGrid.length == 1"> 
                                  <button type="button" class="btn btn-midnightblue-alt" ng-click='btnVerFichaAtencion(mySelectionOATGrid)'> 
                                    <i class="fa fa-eye-slash"></i> FICHA DE ATENCIÓN 
                                  </button>
                                </li> 
                              </ul> 
                              <div ui-grid="gridOptionsOAT" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns class="grid table-responsive fs-mini-grid"> 
                                <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsOAT.data.length"> No se encontró registros de atenciones. </div>
                              </div> 
                            </div>
                          </tab>
                          <!-- PESTAÑA DE CITT -->
                          <tab heading="DESCANSO MEDICO" class="{{tabs.estadoCitt}}" ng-click="getPaginationCITTServerSide();"> 
                              <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoCitt === 'enabled'">
                                <div class="row" ng-show="!formSolicitudCitt"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                    <div class="mb-sm bg-darkgray text-white text-center"> DESCANSO MEDICO SOLICITADOS/REALIZADOS </div> 
                                  </div>
                                  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12 ">
                                  </div>
                                  <div class=" col-lg-8 col-md-12 col-sm-12 col-xs-12 mt-sm"> 
                                    <div class="form-group mb-n">
                                      <!--<button type="button" class="btn btn-success" ng-click="getPaginationPROCServerSide(); $event.preventDefault();"> <i class="fa fa-refresh"></i> PROCESAR </button> -->
                                      <!--<button ng-show="gridOptionsCitt.data.length>0" type="button" class="btn btn-info" ng-click="btnImprimirProc();"><i class="fa fa-print"></i> IMPRIMIR </button> -->
                                      <button type="button" class="btn btn-midnightblue pull-right" ng-click="btnVerFormRegistrarCitt(); $event.preventDefault();"><i class="fa fa-file"></i> NUEVO DESCANSO MEDICO </button> 
                                    </div>
                                  </div>
                                </div>
                                <div class="row" ng-show="!formSolicitudCitt"> 
                                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                                    <div ui-grid="gridOptionsCitt" ui-grid-auto-resize ui-grid-edit ui-grid-pagination ui-grid-selection ui-grid-cellNav ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="overflow: hidden;">
                                      
                                    </div>
                                  </div>
                                </div>
                                <!-- FORMULARIO DE NUEVO CITT -->
                                <div class="row" ng-show="formSolicitudCitt"> 
                                    <form name="formCitt"> 
                                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                                        <div class="mb-sm bg-darkgray text-white text-center"> AGREGAR {{fDataCitt.producto}} </div>
                                        <div class="row"> 
                                          <div class="col-md-12 col-xs-12"> 
                                              <label class="m-n text-blue block"> Fecha de otorgamiento </label> 
                                              <div class="input-group">
                                                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataCitt.fecha_otorgamiento" required tabindex="0" disabled="true" /> 
                                              </div>
                                          </div>
                                          <div class="col-md-12 col-xs-12"> 
                                          <div class="form-group mb-sm">
                                            <label class="m-n text-blue block"> Diagnosticos </label>
                                            <div ui-if="gridOptionsDiagnostico.data.length>0" ui-grid="gridOptionsDiagnostico" ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();">
                                            </div>
                                          </div>
                                          </div>
                                          <div class="col-md-6 col-xs-6"> 
                                            <div class="form-group mb-sm">
                                              <label class="m-n text-blue block"> Tipo de Atención </label>
                                              <input type="text" ng-model="fDataCitt.tipoatencion" class="form-control input-sm" required ng-init="fDataCitt.tipoatencion='CONSULTA EXTERNA'" disabled="true" placeholder="CONSULTA EXTERNA" tabindex="3" />
                                            </div>
                                          </div>
                                       
                                          <div class="col-md-6 col-xs-6" > 
                                            <label class="m-n text-blue block">Contingencia  <small class="text-danger">(*)</small> </label>
                                            
                                            <div class="input-group">
                                              <select tabindex="100" class="form-control input-sm" ng-model="fDataCitt.idcontingencia" ng-options="item.id as item.descripcion for item in listaContingencias" > </select>
                                            </div>
                                            
                                          </div>
                                          <div class="col-md-12 col-xs-12" ng-show="fData.fInputs.gestando == 1"> 
                                              <label class="m-n text-blue block"> Fecha Posible de parto </label> 
                                              <div class="input-group"> 
                                                <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataCitt.fecha_parto" required tabindex="5" /> 
                                              </div>
                                          </div>
                                          <div class="col-md-12 col-xs-12" ng-show="fData.fInputs.gestando > 1"></div>
                                          <div class="col-md-4 col-xs-4"> 
                                            <label class="m-n text-blue block"> Fecha Inicial Descanso </label> 
                                            <div class="input-group"> 
                                              <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataCitt.fecha_inicio" required tabindex="6" /> 
                                            </div>
                                          </div>
                                          <div class="col-md-4 col-xs-4"> 
                                            <label class="m-n text-blue block"> Nº Dias de descanso </label> 
                                            <div class="input-group"> 
                                              <input type="text" ng-model="fDataCitt.dias" class="form-control input-sm" required ng-init="fDataCitt.dias" ng-change="AgregaDias(fDataCitt.fecha_inicio,fDataCitt.dias);" tabindex="7" /> 
                                            </div>
                                          </div>
                                          <div class="col-md-4 col-xs-4"> 
                                            <label class="m-n text-blue block"> Fecha Final Descanso </label> 
                                            <div class="input-group"> 
                                              <input type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataCitt.fecha_final" disabled="true" required tabindex="8"/> 
                                            </div>
                                          </div>

                                          <div class="col-md-12 col-xs-12">
                                            <hr/>
                                          </div>  
                                          <div class="col-md-12 col-xs-12"> 
                                            <div class="form-group mb-sm text-right">
                                              <button type="button" class="btn btn-primary" ng-click="registrarCittEnAtencion();"> 
                                               AGREGAR DESCANSO MEDICO </button> 
                                              <button type="button" class="btn btn-warning" ng-click="btnRegresarAlListadoCitt();"> 
                                                <i class="fa fa-step-backward"></i> REGRESAR </button>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </form>
                                </div>
                              </div>
                              <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="!(tabs.estadoProcedimiento === 'enabled')">
                                <div style="position: relative; top: inherit;" class="waterMarkEmptyData"> Primero debe registrar la atención médica </div> 
                              </div>
                          </tab>
                          <button class="btn btn-success-alt pull-right" ng-click="btnOdontogramaInicial('xlg', fData.idhistoria); $event.preventDefault();" style="width: 200px;" ng-if="fData.idespecialidad == 28 "> 
                            <i class="ti-layout-grid3"></i> Odontograma Inicial
                          </button>
                        </tabset>
                      </div>
                    </div>

                    <!-- ============================= -->
                    <!--  OTROS LISTADOS -->
                    <!-- ============================= -->
                    <div ng-show="registroFormularioAMA" class="col-md-3"> 
                      <div class="panel panel-danger">
                        <div class="panel-heading col-md-10" style="height: 30px;">
                          <h2 class="p-xs" style="font-size: 12px;">Afecciones Médicas</h2> 
                        </div>
                        <button tooltip-placement="left" tooltip="AGREGAR" type="button" class="btn btn-danger col-md-2" ng-click="btnNuevaAfeccion(); $event.preventDefault();" style="height: 30px;"> <i class="fa fa-plus" ></i> </button> 
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
                        <div class="panel-body" style="height: 200px; overflow:auto; padding: 10px;"> 
                          <div class="table-responsive panel-lateral" ng-show="listadoUltRecetas.length > 0">
                            <table class="table">
                              <thead>
                                <tr>
                                  <td>Fecha</td>
                                  <td>Medicamento</td>
                                </tr>
                              </thead>
                              <tbody>
                                <tr ng-repeat="item in listadoUltRecetas">
                                  <td align="left">{{item.fecha}}</td>
                                  <td>{{item.medicamento}}</td>
                                </tr>
                              </tbody>
                            </table>
                        </div>
                      </div>
                    </div>
                    <div class="panel panel-midnightblue">
                      <div class="panel-heading" style="height: 30px;">
                        <h2 class="p-xs" style="font-size: 12px;"> Últimos Exámenes Auxiliares </h2> 
                      </div>
                      <div class="panel-body" style="height: 200px; overflow:auto; padding: 10px;"> 
                        <div class="table-responsive panel-lateral" ng-show="listadoUltExamenes.length > 0">
                            <table class="table">
                              <thead>
                                <tr>
                                  <td>Fecha</td>
                                  <td>Examen</td>
                                </tr>
                              </thead>
                              <tbody>
                                <tr ng-repeat="item in listadoUltExamenes">
                                  <td align="left">{{item.fecha}}</td>
                                  <td>{{item.producto}}</td>
                                </tr>
                              </tbody>
                            </table>
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