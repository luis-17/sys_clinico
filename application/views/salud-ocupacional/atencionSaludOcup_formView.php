<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
	<form class="row" name="formAtencionMedica">
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
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.cliente.nombres }} {{ fData.cliente.apellidos }} </span> 
	          </div>
	          <div class="form-group col-md-3 col-sm-6 col-xs-12">
	            <div class="mb-n">
	              <label for="inputHelpBlock" class="m-n text-blue"> Doc. de Identidad </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.cliente.num_documento }} </span> 
	          </div>
	          <div class="form-group col-md-3 col-sm-6 col-xs-12">
	            <div class="mb-n">
	              <label for="inputHelpBlock" class="m-n text-blue"> Sexo </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.cliente.sexo }} </span> 
	          </div>
	          <div class="form-group col-md-3 col-sm-6 col-xs-12">
	            <div class="mb-n">
	              <label for="inputHelpBlock" class="m-n text-blue"> N° Historia Clínica </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.cliente.idhistoria }} </span> 
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
	              <label for="inputHelpBlock" class="m-n text-blue"> Fecha de Atención </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.fechaAtencion }}  
	            </span> 
	          </div>
	          <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
	            <div class="mb-n">
	              <label for="inputHelpBlock" class="m-n text-blue"> Edad en la Atención </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.cliente.edadEnAtencion }} </span> 
	          </div>
	          <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs">
	            <div class="mb-n">
	              <label for="inputHelpBlock" class="m-n text-blue"> Especialidad </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n" style="font-weight: bold;"> SALUD OCUPACIONAL </span> 
	          </div>
	          <div class="form-group col-md-3 col-sm-6 col-xs-12 mb-xs" >
	            <div class="mb-n">
	              <label for="inputHelpBlock" class="m-n text-blue"> Actividad Específica </label>
	            </div>
	            <span id="helpBlock" class="help-block text-black m-n"> {{ fData.perfil }} </span> 
	          </div> 
	        </div>
	      </fieldset>
	    </div>
	      <div class="block col-xs-12" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" > 
	        <div class="row"> 
	          <div class="col-md-12 col-sm-12 col-xs-12"> 
	            <div class="mb-sm" style="background-color: #37474f; color: white; text-align: center;"> REALIZACIÓN DEL INFORME </div> 
	            <div class="row"> 
	                <div class="col-md-8 col-xs-12"> 
	                  <div class="form-group mb-sm">
	                    <label class="m-n text-blue block"> Informe en texto plano </label> 
	                    <textarea ng-model="fData.informe_texto" class="form-control input-sm" rows="6" placeholder="Digite el informe en texto plano si así lo requiera." ></textarea>
	                  </div>
	                </div>
	                <div class="col-md-4 col-xs-12"> 
	                  <div class="form-group mb-sm">
	                    <label class="m-n text-blue block"> Profesional Responsable <small style="color:red;">*</small> </label> 
	                  	<div class="input-group">
			                <span class="input-group-btn ">
			                    <input disabled type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fData.idmedico" placeholder="ID" readonly="true" required />
			                </span>
			                <input type="text" class="form-control input-sm" ng-model="fData.medico" placeholder="" typeahead-loading="loadingLocations" ng-change="getClearInputMedico();"
			                	uib-typeahead="item as item.descripcion for item in getPersonalMedicoAutocomplete($viewValue)" typeahead-on-select="getSelectedMedico($item, $model, $label)" typeahead-min-length="1"/>
			            </div>
	                    <i ng-show="loadingLocations" class="fa fa-refresh"></i>
	                    <div ng-show="noResultsMEDRESP">
	                        <i class="fa fa-remove"></i> No se encontró resultados 
	                    </div>
	                  </div>
	                </div>
	                <div class="col-md-12 col-xs-12"> 
	                  <div class="form-group mb-sm">
	                  	<label class="m-n text-blue block"> Informe desde un archivo </label> 
	                	<div class="fileinput fileinput-new" data-provides="fileinput" style="width: 100%;">
							<div class="fileinput-preview thumbnail mb20" data-trigger="fileinput" style="height: 50px; width: 100%; line-height: 3;"></div>
							<div style="height: 60px;">
								<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Quitar</a> 
								<span class="btn btn-default btn-file p-n" style="width: 190px; height: 24px;"><span class="fileinput-new">Seleccionar archivo</span> 
									<input style="position: relative;" type="file" name="file" file-model="fData.archivo" /> 
								</span>
							</div>
						</div>
	                  </div>
	                </div>
	            </div>
	          </div>
	        </div>
	      </div> 
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formAtencionMedica.$invalid">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>