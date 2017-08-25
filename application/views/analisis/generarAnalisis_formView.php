<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> -->
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" name="formParametros">
    	<div class="col-md-12">
    		<fieldset class="row" style="padding-right: 10px;">
				<div class="form-group mb-md col-md-6 "> 
                    <label class="m-n text-blue"> Apellidos y Nombres </label> 
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fData.paciente }} </span> 
                </div>
               	<div class="form-group mb-md col-md-2 ">
                    <label class="m-n text-blue"> Nº Muestra </label> 
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fData.id }} </span> 
                </div>
                <div class="form-group mb-md col-md-4 ">
                    <label class="m-n text-blue"> Tipo </label>
                    <span id="helpBlock" class="help-block text-black m-n"> {{ fData.tipomuestra }} </span> 
                </div>
			</fieldset>
    	</div>
    	<div class="col-md-12">
    		
    	</div>
		<div class="well well-transparent boxDark col-xs-12 m-n">
			<div class="row">
				 <div class="form-group col-xs-12 m-n">
                    <label class="control-label"> Seleccione los Análisis a realizarse con la muestra seleccionada </label>
                        <div ui-if="gridOptionsAn.data.length>0" 
                        ui-grid="gridOptionsAn" ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize class="grid table-responsive" style="overflow: hidden;" ng-style="getTableHeight();"></div>
                </div>
			</div>
		</div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsAn.data.length==0">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>