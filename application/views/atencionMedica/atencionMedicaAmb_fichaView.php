<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body row pt-sm" style="line-height: 1;">
    <div class="col-md-12"> 
        <div class="row">
            <div class="col-xs-12" style="line-height: 1; font-size: 95%;">
              <fieldset class="row" >
                <legend class="col-xs-12 mb-sm pb-n" style="font-size: 14px; font-weight: bold; border: none;"> 
                  <div class="block" style="background-color: #5d7581; color: white; text-align: center; line-height: 1.5;"> DATOS DEL PACIENTE </div>
                </legend>
                <div class="col-xs-12 form-inline mb-sm pl-xs"> 
                  <div class="form-group col-md-3 col-sm-6 col-xs-12">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Nombres y Apellidos</label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.cliente }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Doc. de Identidad </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.numero_documento }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Sexo </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.sexo }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> N° Historia Clínica </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.idhistoria }} </span> 
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
                    <span id="helpBlock" class="help-block text-black m-n" style=""> {{ fDataFicha.num_acto_medico }} </span> 
                  </div> 
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                    <div class=" mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> N° Orden </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.orden }} </span> 
                  </div> 
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Area Hospitalaria </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.area_hospitalaria }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Profesional </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n" style="min-height: 16px;"> {{ fDataFicha.personalatencion.descripcion }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Fecha de Atención </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.fechaAtencion }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs" ng-show="fDataFicha.boolNumActoMedico">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Hora de Atención </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.horaAtencion }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Edad en la Atención </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fDataFicha.edadEnAtencion }} </span> 
                  </div>
                  <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
                    <div class="mb-n">
                      <label for="inputHelpBlock" class="m-n text-blue"> Especialidad </label>
                    </div>
                    <span id="helpBlock" class="help-block text-black m-n" style="font-weight: bold;"> {{ fDataFicha.especialidad }} </span> 
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
            <!-- CONSULTA MEDICA -->
            <div class="tab-content col-xs-12" style="border-top: 1px solid gray; padding-top: 10px;  font-size: 84%;" ng-if="fDataFicha.idtipoproducto == 12"> 
              <div class="tab-pane active">
                <form name="formAtencionMedicaAmb"> 
                  <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                    <div class="row" ng-if="fDataFicha.boolSexo == 'F'"> 
                      <div class="col-md-2 col-sm-4 col-xs-12 mb-sm"> 
                        <div class="form-group mb-n"> 
                          <label class="m-n text-blue"> ¿Gestando? </label> 
                          <span class="help-block text-black m-n ng-binding" style="" ng-if="fDataFicha.gestando == '2'"> NO </span> 
                          <span class="help-block text-black m-n ng-binding" style="" ng-if="fDataFicha.gestando == '1'"> SI </span> 
                          <!-- <select ng-disabled="fDataFicha.boolNumActoMedico" tabindex="105" ng-model="fDataFicha.gestando" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolGestando" > </select>  -->
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fDataFicha.gestando == 1"> 
                        <div class="form-group mb-n">
                          <label class="m-n text-blue"> Ultima Regla (FUR) </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.fur }} </span> 
                          <!-- <input ng-disabled="fDataFicha.boolNumActoMedico" tabindex="106" type="text" class="form-control input-sm mask" data-inputmask="'alias': 'dd-mm-yyyy'" ng-model="fDataFicha.fur" ng-change="calculateSemanaGestacion();" />  -->
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fDataFicha.gestando == 1"> 
                        <div class="form-group mb-n">
                          <label class="m-n text-blue"> Semana de Gestación </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.semana_gestacion }} </span> 
                          <!-- <input tabindex="107" ng-model="fDataFicha.semana_gestacion" class="form-control input-sm" placeholder="Semana de Gestación" disabled="true" />  -->
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-3 col-xs-12 mb-xs" ng-show="fDataFicha.gestando == 1"> 
                        <div class="form-group mb-n">
                          <label class="m-n text-blue"> Fecha probable de parto </label> 
                          <!-- <input ng-model="fDataFicha.fpp" class="form-control input-sm" placeholder="Fecha probable de parto(FPP)" disabled="true" />  -->
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.fpp }} </span> 
                        </div>
                      </div>
                    </div>
                    <div class="row"> 
                      <div class="col-md-12 col-sm-12 col-xs-12 pt-xs"> 
                        <div class="mb-sm bg-darkgray text-white text-center" style=""> ANAMNESIS </div> 
                        <div class="form-group mb-n">
                          <label class="m-n text-blue"> Anamnesis </label> 
                          <span class="help-block text-black m-n ng-binding"> {{ fDataFicha.anamnesis }} </span> 
                          <!-- <textarea required tabindex="108" ng-model="fDataFicha.anamnesis" class="form-control input-sm" placeholder="Digite la Anamnesis" ></textarea>  -->
                        </div>
                      </div>
                    </div>
                    <div class="row" ng-show="fDataFicha.especialidad != 'ODONTOLOGIA'"> 
                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                        <div class="mb-sm bg-darkgray text-white text-center" style=""> SIGNOS VITALES </div> 
                        <div class="row"> 
                          <div class="col-md-3 col-xs-6">
                            <div class="form-inline">
                              <label class="m-n text-blue block"> Presión Arterial <small class="text-gray"> (Mm Hg) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.presion_arterial_mm }} / {{ fDataFicha.presion_arterial_hg }} </span> 
                              <!-- <input tabindex="109" type="text" ng-model="fDataFicha.presion_arterial_mm" class="form-control input-sm" required style="width: 60px;" /> / 
                              <input tabindex="110" type="text" ng-model="fDataFicha.presion_arterial_hg" class="form-control input-sm" required style="width: 60px;" />  --> 
                            </div>
                          </div>
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Frecuencia Cardíaca <small class="text-gray"> (Latidos x Min.) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.frecuencia_cardiaca_lxm }} </span> 
                              <!-- <input tabindex="111" type="text" ng-model="fDataFicha.frecuencia_cardiaca_lxm" class="form-control input-sm" style="width: 100px;" required />  -->
                            </div>
                          </div>
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Temperatura Corporal <small class="text-gray"> (°C) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.temperatura_corporal }} </span> 
                              <!-- <input tabindex="112" type="text" ng-model="fDataFicha.temperatura_corporal" class="form-control input-sm" style="width: 100px;" required />  -->
                            </div>
                          </div>
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Frecuencia Respiratoria <small class="text-gray"> (Por Minuto) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.frecuencia_respiratoria }} </span> 
                              <!-- <input tabindex="113" type="text" ng-model="fDataFicha.frecuencia_respiratoria" class="form-control input-sm" style="width: 100px;" required />  -->
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row" ng-show="fDataFicha.especialidad != 'ODONTOLOGIA'">  
                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                        <div class="mb-sm bg-darkgray text-white text-center" style=""> ANTROPOMETRÍA </div>
                        <div class="row"> 
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Peso <small class="text-gray"> (Kg.) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.peso }} </span> 
                              <!-- <input tabindex="114" type="text" ng-change="calculateIMC();" ng-model="fDataFicha.peso" class="form-control input-sm" style="width: 100px;" required />  -->
                            </div>
                          </div>
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Talla <small class="text-gray"> (m) </small> </label> 
                              <!-- <input tabindex="115" type="text" ng-change="calculateIMC();" ng-model="fDataFicha.talla" class="form-control input-sm" style="width: 100px;" required />  -->
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.talla }} </span> 
                            </div>
                          </div>
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> IMC <small class="text-gray"> (%) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.imc }} </span> 
                              <!-- <input tabindex="116" type="text" ng-model="fDataFicha.imc" class="form-control input-sm" style="width: 100px;" disabled="true" required />  -->
                            </div>
                          </div>
                          <div class="col-md-3 col-xs-6">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Perímetro Abdominal <small class="text-gray"> (cm) </small> </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.perimetro_abdominal }} </span> 
                              <!-- <input tabindex="117" type="text" ng-model="fDataFicha.perimetro_abdominal" class="form-control input-sm" style="width: 100px;" required />  -->
                            </div>
                          </div>
                          <div class="col-md-7 col-xs-12">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Examen Clínico </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.examen_clinico }} </span> 
                              <!-- <textarea tabindex="118" type="text" ng-model="fDataFicha.examen_clinico" class="form-control input-sm" placeholder="Digite el Examen Clínico" required > </textarea>  -->
                            </div>
                          </div>
                          <div class="col-md-5 col-xs-12">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Antecedentes </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.antecedentes }} </span> 
                              <!-- <textarea tabindex="118" type="text" ng-model="fDataFicha.antecedentes" class="form-control input-sm" placeholder="Digite los antecedentes" > </textarea>  --> 
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row" ng-show="fDataFicha.especialidad != 'ODONTOLOGIA'"> 
                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                        <div class="mb-sm bg-darkgray text-white text-center" style=""> PLAN DE TRABAJO, COMENTARIOS Y/O OBSERVACIONES </div>
                        <div class="row"> 
                          <div class="col-md-8 col-xs-12">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Plan de trabajo, Comentarios y/o Observaciones </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.observaciones }} </span> 
                            </div>
                          </div>
                          <div class="col-md-4 col-xs-12">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> ¿Atención de Control? </label> 
                              <span class="help-block text-black m-n ng-binding" style="" ng-if="fDataFicha.atencion_control == 2"> no </span>
                              <span class="help-block text-black m-n ng-binding" style="" ng-if="fDataFicha.atencion_control == 1"> si </span> 
                              <!-- <select required tabindex="120" ng-model="fDataFicha.atencion_control" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolAtencionControl" > </select>  -->
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row" ng-show="fDataFicha.especialidad == 'ODONTOLOGIA'"> 
                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                        <div class="mb-sm bg-darkgray text-white text-center" style=""> EXAMEN CLINICO </div>
                        <div class="row"> 
                          <div class="col-md-8 col-xs-12">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> Examen Clínico </label> 
                              <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.examen_clinico }} </span> 
                            </div>
                          </div>
                          <div class="col-md-4 col-xs-12">
                            <div class="form-group mb-sm">
                              <label class="m-n text-blue block"> ¿Atención de Control? </label> 
                              <span class="help-block text-black m-n ng-binding" style="" ng-if="fDataFicha.atencion_control == 2"> no </span>
                              <span class="help-block text-black m-n ng-binding" style="" ng-if="fDataFicha.atencion_control == 1"> si </span> 
                              <!-- <select required tabindex="120" ng-model="fDataFicha.atencion_control" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaBoolAtencionControl" > </select>  -->
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row"> 
                      <div class="col-md-12 col-sm-12 col-xs-12 mb-xs mt-sm pt-xs" style="border-top: 1px solid #e0e0e0;"> 
                        <div class="mb-sm bg-darkgray text-white text-center" style=""> DIAGNOSTICO </div>
                        <div class="row"> 
                          <div class="col-md-12 col-xs-12">
                            <div ui-if="gridOptionsFichaDiagnostico.data.length>0" ui-grid="gridOptionsFichaDiagnostico" ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row"> 
                      <div class="mb-sm bg-darkgray text-white text-center" style=""> RECETA MEDICA </div>
                      <div class="col-md-12 col-sm-12 col-xs-12 pt-xs" ng-show="gridOptionsFichaReceta.data.length > 0"> 
                        <div ui-grid="gridOptionsFichaReceta" ui-grid-auto-resize ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                      </div>
                      <div class="col-md-12 col-sm-12 col-xs-12 pt-xs" ng-show="gridOptionsFichaReceta.data.length < 1"> 
                        <p style="text-align: center;"> No se registró receta médica </p> 
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <!-- EXAMEN AUXILIAR -->
            <div class="tab-content col-xs-12" style="border-top: 1px solid gray; padding-top: 10px;  font-size: 84%;" ng-if="fDataFicha.idtipoproducto == 11 || fDataFicha.idtipoproducto == 14 || fDataFicha.idtipoproducto == 15"> 
              <div class="tab-pane active">
                <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                  <div class="row"> 
                      <div class="mb-sm bg-darkgray text-white text-center" style=""> INFORME </div>
                      <div class="col-md-8 col-xs-8"> 
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue"> Personal Responsable </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.personal.descripcion }} </span> 
                        </div>
                      </div>
                      <div class="col-md-4 col-xs-4"> 
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Tipo de Resultado </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.strTipoResultado }} </span> 
                        </div>
                      </div>
                      <div class="col-md-12 col-xs-12"> 
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Informe/Resultado </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.ex_informe }} </span> 
                        </div>
                      </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- PROCEDIMIENTO -->
            <div class="tab-content col-xs-12" style="border-top: 1px solid gray; padding-top: 10px;  font-size: 84%;" ng-if="fDataFicha.idtipoproducto == 16"> 
              <div class="tab-pane active">
                <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                  <div class="row"> 
                      <div class="mb-sm bg-darkgray text-white text-center" style=""> INFORME </div> 
                      <div class="col-md-12 col-xs-12"> 
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Observaciones </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.proc_observacion }} </span> 
                        </div>
                      </div>
                      <div class="col-md-12 col-xs-12"> 
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Informe/Resultado </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.proc_informe }} </span> 
                        </div>
                      </div>
                  </div>
                  <div class="row"> 
                    <div class="mb-sm bg-darkgray text-white text-center" style=""> RECETA MEDICA </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 pt-xs" ng-show="gridOptionsFichaReceta.data.length > 0"> 
                      <div ui-grid="gridOptionsFichaReceta" ui-grid-auto-resize ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 pt-xs" ng-show="gridOptionsFichaReceta.data.length < 1"> 
                      <p style="text-align: center;"> No se registró receta médica </p> 
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- DOCUMENTOS -->
            <div class="tab-content col-xs-12" style="border-top: 1px solid gray; padding-top: 10px;  font-size: 84%;" ng-if="fDataFicha.idtipoproducto == 13"> 
              <div class="tab-pane active">
                <div class="block" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                  <div class="row"> 
                      <div class="mb-sm bg-darkgray text-white text-center" style=""> INFORME </div> 
                      <div class="col-md-12 col-xs-12"> 
                        <div class="form-group mb-sm">
                          <label class="m-n text-blue block"> Informe/Resultado </label> 
                          <span class="help-block text-black m-n ng-binding" style=""> {{ fDataFicha.doc_informe }} </span> 
                        </div>
                      </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <!-- <button class="btn btn-success" ng-click="imprimir()" tabindex="28" > <i class="fa fa-print"></i> Imprimir </button> -->
    <button class="btn btn-warning" ng-click="cancel()" tabindex="30"> Salir </button>
</div>
