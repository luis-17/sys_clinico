<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <!-- ATENCION MEDICA CONSULTA -->
	<div class="row" ng-if="fData.idtipoproducto == 12">
        <div class="col-xs-12" style="">
          <fieldset class="row" style="line-height: 1; font-size: 95%;">
            <legend class="col-xs-12 mb-sm pb-n" style="font-size: 14px; font-weight: bold; border: none;"> 
              <div class="block" style="background-color: #5d7581; color: white; text-align: center; line-height: 1.5;"> DATOS DEL PACIENTE </div>
            </legend>
            <div class="col-xs-12 form-inline mb-sm pl-xs"> 
              <div class="form-group col-md-3 col-sm-6 col-xs-12">
                <div class="mb-n">
                  <label for="inputHelpBlock" class="m-n text-blue"> Nombres y Apellidos </label>
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
        <div class="col-xs-12" style="line-height: 1; font-size: 95%;">
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
                      <select ng-disabled="fData.boolNumActoMedico" tabindex="105" ng-model="fData.gestando" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolGestando" > </select> 
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fData.gestando == 1"> 
                    <div class="form-group mb-n">
                      <label class="m-n text-blue"> Ultima Regla (FUR) </label> 
                      <input ng-disabled="fData.boolNumActoMedico" tabindex="106" type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fData.fur" ng-change="calculateSemanaGestacion();" /> 
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fData.gestando == 1"> 
                    <div class="form-group mb-n">
                      <label class="m-n text-blue"> Semana de Gestación </label> 
                      <input tabindex="107" ng-model="fData.semana_gestacion" class="form-control input-sm" placeholder="Semana de Gestación" disabled="true" /> 
                    </div>
                  </div>
                  <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fData.gestando == 1"> 
                    <div class="form-group mb-n">
                      <label class="m-n text-blue"> Fecha probable de parto </label> 
                      <input ng-model="fData.fpp" class="form-control input-sm" placeholder="Fecha probable de parto(FPP)" disabled="true" /> 
                    </div>
                  </div>
                </div>
                <div class="row"> 
                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> ANAMNESIS </div> 
                    <div class="form-group mb-n">
                      <label class="m-n text-blue"> Anamnesis <small class="text-danger">(*)</small> </label> 
                      <textarea required tabindex="108" ng-model="fData.anamnesis" class="form-control input-sm" placeholder="Digite la Anamnesis" ></textarea> 
                    </div>
                  </div>
                </div>
                <div class="row" ng-if="fData.idespecialidad != 28 "> 
                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> SIGNOS VITALES </div> 
                    <div class="row"> 
                      <div class="col-md-3 col-xs-6">
                        <div class="form-inline">
                          <label class="m-n text-blue block"> Presión Arterial <small class="text-gray"> (Mm Hg) </small> </label> 
                          <input tabindex="109" type="text" ng-model="fData.presion_arterial_mm" class="form-control input-sm" style="width: 60px;" /> / 
                          <input tabindex="110" type="text" ng-model="fData.presion_arterial_hg" class="form-control input-sm" style="width: 60px;" /> 
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Frecuencia Cardíaca <small class="text-gray"> (Latidos x Min.) </small> </label> 
                          <input tabindex="111" type="text" ng-model="fData.frecuencia_cardiaca_lxm" class="form-control input-sm" style="width: 100px;" /> 
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Temperatura Corporal <small class="text-gray"> (°C) </small> </label> 
                          <input tabindex="112" type="text" ng-model="fData.temperatura_corporal" class="form-control input-sm" style="width: 100px;" /> 
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Frecuencia Respiratoria <small class="text-gray"> (Por Minuto) </small> </label> 
                          <input tabindex="113" type="text" ng-model="fData.frecuencia_respiratoria" class="form-control input-sm" style="width: 100px;" /> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row" ng-if="fData.idespecialidad != 28 "> 
                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> ANTROPOMETRÍA </div>
                    <div class="row"> 
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Peso <small class="text-gray"> (Kg.) </small> </label> 
                          <input tabindex="114" type="text" ng-change="calculateIMC();" ng-model="fData.peso" class="form-control input-sm" style="width: 100px;" /> 
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Talla <small class="text-gray"> (m) </small> </label> 
                          <input tabindex="115" type="text" ng-change="calculateIMC();" ng-model="fData.talla" class="form-control input-sm" style="width: 100px;" /> 
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> IMC <small class="text-gray"> (%) </small> </label> 
                          <input tabindex="116" type="text" ng-model="fData.imc" class="form-control input-sm" style="width: 100px;" disabled="true" /> 
                        </div>
                      </div>
                      <div class="col-md-3 col-xs-6">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Perímetro Abdominal <small class="text-gray"> (cm) </small> </label> 
                          <input tabindex="117" type="text" ng-model="fData.perimetro_abdominal" class="form-control input-sm" style="width: 100px;" /> 
                        </div>
                      </div>
                      <div class="col-md-7 col-xs-12">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Examen Clínico <small class="text-danger">(*)</small> </label> 
                          <textarea tabindex="118" type="text" ng-model="fData.examen_clinico" class="form-control input-sm" placeholder="Digite el Examen Clínico" required > </textarea> 
                        </div>
                      </div>
                      <div class="col-md-5 col-xs-12">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Antecedentes </label> 
                          <textarea tabindex="118" type="text" ng-model="fData.antecedentes" class="form-control input-sm" placeholder="Digite los antecedentes" > </textarea> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row" ng-if="fData.idespecialidad != 28 "> 
                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES </div>
                    <div class="row"> 
                      <div class="col-md-10 col-xs-12">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Plan de trabajo, Comentarios y/o Observaciones </label> 
                          <textarea tabindex="119" type="text" ng-model="fData.observaciones" class="form-control input-sm" placeholder="Digite el Plan de trabajo, Comentarios y/o Observaciones" > </textarea> 
                        </div>
                      </div>
                      <div class="col-md-2 col-xs-12">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> ¿Atención de Control? </label> 
                          <select required tabindex="120" ng-model="fData.atencion_control" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolAtencionControl" > </select> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- SOLO PARA ODONTOLOGIA -->
                <div class="row" ng-if="fData.idespecialidad == 28 "> 
                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;">  EXAMEN CLINICO  </div>
                    <div class="row"> 
                      <div class="col-md-10 col-xs-12">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Examen Clínico </label> 
                          <textarea required tabindex="119" type="text" ng-model="fData.examen_clinico" class="form-control input-sm" placeholder="Digite el Examen Clínico" > </textarea> 
                        </div>
                      </div>
                      <div class="col-md-2 col-xs-12">
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> ¿Atención de Control? </label> 
                          <select required tabindex="120" ng-model="fData.atencion_control" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolAtencionControl" > </select> 
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!--  -->
                <div class="row"> 
                  <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> DIAGNOSTICO </div>
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
                <!-- <button type="button" class="btn btn-warning" ng-click="btnRegresarAlInicio(); $event.preventDefault();" style="width: 150px;"> 
                  <i class="fa fa-step-backward"></i> Regresar 
                </button>  -->
                <button class="btn btn-success" ng-click="grabarAtencionMedica(); $event.preventDefault();" ng-disabled="formAtencionMedicaAmb.$invalid" style="width: 240px;"> 
                  <i class="fa fa-edit"></i> Grabar 
                </button> 
              </div> 
            </form>
          </tab>
          <!-- PESTAÑA DE PROCEDIMIENTO -->
          <tab heading="PROCEDIMIENTO" class="{{tabs.estadoProcedimiento}}" ng-click="reloadGrid();">
              <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoProcedimiento === 'enabled'">
                <div class="row" ng-show="!formSolicitudProcedimiento"> 
                  <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                    <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> PROCEDIMIENTOS SOLICITADOS/REALIZADOS </div> 
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
                        <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> AGREGAR PROCEDIMIENTO </div>
                        <div class="row"> 
                          <div class="col-md-6 col-xs-6"> 
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Procedimiento </label> 
                              <input type="text" ng-model="fDataProc.procedimiento" class="form-control input-sm" placeholder="Digite el procedimiento para autocompletar" typeahead-loading="loadingLocations" 
                                uib-typeahead="item as item.descripcion for item in getProcedimientoAutocomplete($viewValue)" typeahead-min-length="2" typeahead-on-select="onSelectProcedimiento($item, $model, $label)" required /> 
                              <!-- <input tabindex="114" type="text" ng-model="fDataProc.procedimiento" class="form-control input-sm" required />  --> 
                              <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                              <div ng-show="noResultsLPAC">
                                <i class="fa fa-remove"></i> No se encontró resultados 
                              </div>
                            </div>
                          </div> 
                          <div class="col-md-6 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Cantidad Realizada </label> 
                              <input type="text" ng-model="fDataProc.cantidad" class="form-control input-sm" required ng-init="fDataProc.cantidad = 1" /> 
                            </div>
                          </div> 
                          <div class="col-md-12 col-xs-12"> 
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Observaciones </label> 
                              <textarea ng-model="fDataProc.observacion" class="form-control input-sm" required placeholder="Digite observaciones"></textarea>
                            </div>
                          </div>
                          <div class="col-md-12 col-xs-12"> 
                            <div class="form-group mb-sm text-right">
                              <button type="button" class="btn btn-primary" ng-click="registrarProcedimientoEnAtencion();"> 
                               AGREGAR PROCEDIMIENTO </button> 
                              <button type="button" class="btn btn-warning" ng-click="btnRegresarAlListadoProc();"> 
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
                  <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> RECETAS MÉDICAS </div> 
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
                    <button ng-if="mySelectionRECGrid.length > 0" type="button" class="btn btn-info pull-right mr" ng-click="btnImprimirReceta(); $event.preventDefault();"><i class="fa fa-print"></i> IMPRIMIR </button> 
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
                  <form name="formReceta"> 
                    <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                      <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> NUEVA RECETA </div>
                      <div class="row"> 
                        <div class="col-md-4 col-xs-4"> 
                          <div class="form-group mb-sm">
                            <label class="m-n text-blue block"> Medicamento </label> 
                            <input id="fTemporalmedicamento" type="text" ng-model="fDataREC.fTemporal.medicamento" class="form-control input-sm" placeholder="Digite el medicamento para autocompletar" typeahead-loading="loadingLocations" 
                              uib-typeahead="item as item.medicamento for item in getMedicamentoAutocomplete($viewValue)" typeahead-min-length="2"  /> 
                            <!-- <input tabindex="114" type="text" ng-model="fDataREC.procedimiento" class="form-control input-sm" required />  --> 
                            <i ng-show="loadingLocations" class="fa fa-refresh"></i>
                            <div ng-show="noResultsMEDI">
                              <i class="fa fa-remove"></i> No se encontró resultados 
                            </div>
                          </div>
                        </div> 
                        <div class="col-md-2 col-xs-2">
                          <div class="form-group mb-sm">
                            <label class="m-n text-blue block"> Cantidad </label> 
                            <input type="text" ng-model="fDataREC.fTemporal.cantidad" class="form-control input-sm" /> 
                          </div>
                        </div> 
                        <div class="col-md-5 col-xs-5"> 
                          <div class="form-group mb-sm">
                            <label class="m-n text-blue block"> Indicaciones </label> 
                            <input type="text" ng-model="fDataREC.fTemporal.indicacion" class="form-control input-sm"  placeholder="Indicaciones" />
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
                  <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> EXAMENES AUXILIARES </div> 
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
                  <div ui-grid="gridOptionsExamenAuxiliar" ui-grid-auto-resize ui-grid-edit ui-grid-pagination ui-grid-auto-resize ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid" style="overflow: hidden;"></div>
                </div>
              </div>
              <!-- FORMULARIO DE NUEVO EXAMEN AUXILIAR --> 
              <div class="row" ng-show="contSolicitudExamenAuxiliar"> 
                  <form name="formSolicitudExamenAuxiliar"> 
                    <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                        <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> SOLICITUD DE EXAMENES AUXILIARES - {{ fDataAUX.tipoExamen }} </div>
                        <div class="row"> 
                          <div class="col-md-6 col-xs-6"> 
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
          <tab heading="OTRAS ATENCIONES" class="{{tabs.estadoOtrasAtenciones}}" ng-click="reloadGrid();">
            <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                  <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> OTRAS ATENCIONES  </div> 
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
              <div ui-grid="gridOptionsOAT" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid"> 
                <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsOAT.data.length"> No se encontró registros de atenciones. </div>
              </div> 
            </div>
          </tab>
        </tabset>
    </div>
	<!-- FIN ATENCION MEDICA CONSULTA -->
	
	<!-- PROCEDIMIENTO -->
	<div class="row" ng-if="fData.idtipoproducto == 16"> 
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
		  <tab heading="PROCEDIMIENTO" class="{{tabs.estadoAtencionMedica}}" >
		    <form name="formAtencionProc"> 
		      <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoAtencionMedica === 'enabled'"> 
		        <div class="row"> 
		          <div class="col-md-12 col-sm-12 col-xs-12" style="border-top: 1px solid #e0e0e0;"> 
		            <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> REALIZACION DEL PROCEDIMIENTO </div> 
		            <div class="row"> 
		              <div class="col-md-6 col-xs-6"> 
		                <div class="form-group mb-sm"> 
		                  <label class="m-n text-blue block"> Procedimiento </label> 
		                  <input type="text" ng-model="fData.producto" class="form-control input-sm" required disabled />  
		                </div>
		              </div> 
		              <div class="col-md-6 col-xs-6">
		                <div class="form-group mb-sm">
		                  <label class="m-n text-blue block"> Cantidad Realizada </label> 
		                  <input type="text" ng-model="fData.cantidad" class="form-control input-sm" disabled /> 
		                </div>
		              </div> 
		              <div class="col-md-12 col-xs-12">
		                <div class="form-group mb-sm">
		                  <label class="m-n text-blue block"> Observaciones </label> 
		                  <textarea ng-model="fData.observacion" class="form-control input-sm" disabled ></textarea>
		                </div>
		              </div>
		            </div>
		            <div class="row"> 
		              <div class="col-md-12 col-xs-12"> 
		                <div class="form-group mb-sm">
		                  <label class="m-n text-blue block"> Informe </label> 
		                  <textarea ng-model="fData.proc_informe" class="form-control input-sm" rows="6" required ></textarea>
		                </div>
		              </div>
		            </div>
		          </div>
		        </div>
		      </div> 
		      <div class="block mt text-center"> 
		        <!-- <button class="btn btn-warning" ng-click="btnRegresarAlInicio(); $event.preventDefault();" style="width: 150px;"> 
		          <i class="fa fa-step-backward"></i> Regresar 
		        </button>  -->
		        <button class="btn btn-success" ng-click="grabarAtencionProcedimiento(); $event.preventDefault();" ng-disabled="formAtencionProc.$invalid" style="width: 240px;"> 
		          <i class="fa fa-edit"></i> Grabar 
		        </button> 
		      </div> 
		    </form> 
		  </tab>
		  <tab heading="OTRAS ATENCIONES" class="{{tabs.estadoOtrasAtenciones}}" ng-click="reloadGrid();">
		    <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
		      <div class="row">
		        <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
		          <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> OTRAS ATENCIONES  </div> 
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
		            <button ng-show="gridOptionsOAT.data.length>0" type="button" class="btn btn-info" ng-click="btnImprimirOtrasAtenciones(); $event.preventDefault();"><i class="fa fa-print"></i> IMPRIMIR </button> 
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
		      <div ui-grid="gridOptionsOAT" ui-grid-pagination ui-grid-selection  ui-grid-auto-resize ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"> 
		        <div class="waterMarkEmptyData" style="top: 40px; font-size: 20px;" ng-show="!gridOptionsOAT.data.length"> No se encontró registros de atenciones. </div>
		      </div> 
		    </div>
		  </tab>
		  <button type="button" ng-show="boolOdontologia" class="btn btn-success-alt pull-right" ng-click="btnOdontogramaInicial('xlg', fData.idhistoria);"><i class="ti-layout-grid3"></i> Odontograma Inicial
		  </button>
		  <button type="button" ng-show="boolOdontologia" class="btn btn-success-alt pull-right" ng-click="btnVerOdontogramaProc('xlg');"><i class="ti-layout-grid3"></i> Odontograma Procedimiento </button>
		</tabset>
	</div>
	<!-- FIN PROCEDIMIENTO -->

	<!-- EXAMEN AUXILIAR -->
  	<div class="row" ng-if="fData.idtipoproducto == 11 || fData.idtipoproducto == 14 || fData.idtipoproducto == 15">
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
	      <tab heading="EXAMEN AUXILIAR" class="{{tabs.estadoAtencionMedica}}" >
	        <form name="formAtencionAUX"> 
	          <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" ng-show="tabs.estadoAtencionMedica === 'enabled'"> 
	            <div class="row"> 
	              <div class="col-md-12 col-sm-12 col-xs-12" style="border-top: 1px solid #e0e0e0;"> 
	                <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> REALIZACION DEL EXAMEN AUXILIAR </div> 
	                <div class="row"> 
	                  <div class="col-md-6 col-xs-6"> 
	                    <div class="form-group mb-sm"> 
	                      <label class="m-n text-blue block"> Examen Auxiliar </label> 
	                      <input type="text" ng-model="fData.producto" class="form-control input-sm" required disabled />  
	                    </div>
	                  </div> 
	                  <div class="col-md-6 col-xs-6">
	                    <div class="form-group mb-sm">
	                      <label class="m-n text-blue block"> Cantidad Realizada </label> 
	                      <input type="text" ng-model="fData.cantidad" class="form-control input-sm" disabled /> 
	                    </div>
	                  </div> 
	                  <div class="col-md-12 col-xs-12">
	                    <div class="form-group mb-sm">
	                      <label class="m-n text-blue block"> Indicaciones </label> 
	                      <textarea ng-model="fData.indicaciones" class="form-control input-sm" disabled ></textarea>
	                    </div>
	                  </div>
	                </div>
	                <div class="row">
	                  <div class="col-md-8 col-xs-8"> 
	                    <div class="form-group mb-sm">
	                      <label class="m-n text-blue block"> Personal Responsable </label> 
	                      <input type="text" ng-model="fData.personal" class="form-control input-sm" placeholder="Digite el personal responsable para autocompletar" typeahead-loading="loadingLocations" 
	                          uib-typeahead="item as item.descripcion for item in getPersonalMedicoAutocomplete($viewValue)" typeahead-min-length="2" required />  
	                      <i ng-show="loadingLocations" class="fa fa-refresh"></i>
	                      <div ng-show="noResultsMEDRESP">
	                        <i class="fa fa-remove"></i> No se encontró resultados 
	                      </div>
	                    </div>
	                  </div>
	                  <div class="col-md-4 col-xs-4"> 
	                    <div class="form-group mb-sm">
	                      <label class="m-n text-blue block"> Tipo de Resultado </label> 
	                      <select class="form-control input-sm" ng-model="fData.tipoResultado" ng-options="item.id as item.descripcion for item in listaTipoResultado" > </select>
	                    </div>
	                  </div>
	                  <div class="col-md-12 col-xs-12"> 
	                    <div class="form-group mb-sm">
	                      <label class="m-n text-blue block"> Informe/Resultado </label> 
	                      <textarea ng-model="fData.ex_informe" class="form-control input-sm" rows="6" required ></textarea>
	                    </div>
	                  </div>
	                </div>
	              </div>
	            </div>
	          </div> 
	          <div class="block mt text-center"> 
	            <!-- <button class="btn btn-warning" ng-click="btnRegresarAlInicio(); $event.preventDefault();" style="width: 150px;"> 
	              <i class="fa fa-step-backward"></i> Regresar 
	            </button>  -->
	            <button class="btn btn-success" ng-click="grabarAtencionExamenAux(); $event.preventDefault();" ng-disabled="formAtencionAUX.$invalid" style="width: 240px;"> 
	              <i class="fa fa-edit"></i> Grabar 
	            </button> 
	          </div> 
	        </form> 
	      </tab>
	    </tabset>
  	</div>
	<!-- FIN EXAMEN AUXILIAR -->

	<!-- DOCUMENTOS -->
	<div class="row" ng-if="fData.idtipoproducto == 13">
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
		        <!-- <button class="btn btn-warning" ng-click="btnRegresarAlInicio(); $event.preventDefault();" style="width: 150px;"> 
		          <i class="fa fa-step-backward"></i> Regresar 
		        </button>  -->
		        <button class="btn btn-success" ng-click="grabarAtencionCITT(); $event.preventDefault();" ng-disabled="formAtencionAUX.$invalid" style="width: 240px;"> 
		          <i class="fa fa-edit"></i> Grabar 
		        </button>
		        <!-- <button class="btn-success btn" ng-click="imprimir(); $event.preventDefault();" ng-disabled="fData.num_acto_medico == '-- SIN REGISTRAR --'"> <i class="fa fa-print"> </i> [F4] Imprimir </button> -->
		      </div>

		    </form> 
		  </tab>
		</tabset>
	</div>
	<!-- FIN DOCUMENTOS --> 
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">SALIR</button>
</div>