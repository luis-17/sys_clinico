<style type="text/css">
	.fila{border-bottom: 1px solid rgb(224, 224, 224);padding-top: 10px;padding-bottom: 10px;}
	.fila:last-child{border-bottom:none;}
	.fila:nth-child(even) {background: #fff }
.fila:nth-child(odd) {background: #F8F8F8;}
</style>
<!-- <link rel="stylesheet" type='text/css' href="assets/plugins/iCheck/skins/minimal/_all.css" /> -->
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
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
		<label class="control-label mb-xs text-blue"> Nº Muestra </label>
	 	<span class="help-block text-black m-n"> {{ fData.idmuestrapaciente }} </span> 
	</div>
	
    <form class="row" name="formParametros">
    	<div class="col-md-12">
    		<fieldset class="row" style="padding-right: 10px;">
    			<legend style="font-size: 16px; background-color: #5d7581; color: white; line-height: 1.5; padding-top: 2px; padding-bottom: 2px;" class="mt mb-n">
					<div class="form-group mb-n col-md-4" style="border-right: 1px solid #8f9da5;" >Examen</div>
					<div class="form-group mb-n col-md-2" style="border-right: 1px solid #8f9da5;">Resultado</div>
					<div class="form-group mb-n col-md-4" style="border-right: 1px solid #8f9da5;">Valor Normal</div>
					<div class="form-group mb-n col-md-2">Método</div>
    			</legend>
    			<h4 class="form-group mb-md col-md-12 text-center">{{ fData.seccion }}</h4>
               	<div class="form-group mb-n col-md-12 fila" ng-repeat="parametro in fData.parametros"> 
                    <!-- <label class="m-n text-blue"> {{ parametro.descripcion_par }} </label>  -->

                    <div class="form-group mb-n col-md-4" ng-class="{'pl-xs':parametro.separador == 1, 'pl-xxl':parametro.separador == 0}">
                    	<span class="m-n" ng-class="{'text-danger':parametro.separador == 1, 'text-black':parametro.separador == 0}"> {{ parametro.descripcion_par }} </span> 
                    </div>
                    <div class="form-group mb-n pl-n pr-n col-md-2">
                    	<input ng-if="parametro.separador == 0" class="form-control input-sm" type="text" ng-model="fData.parametros[$index].resultado"/>
                    </div>
                    <!-- <div class="form-group mb-n col-md-4">
                    	<textarea class="autosize" style="overflow-y: hidden; border: medium none; width: 100%;resize: none; word-wrap:break-word" ng-readonly="false">{{ parametro.valor_normal }}</textarea>
                    </div> -->
                    <div class="form-group mb-n col-md-4">
                    	<div class="alert alert-dismissable mb-n p-n">
                    		<span>{{ parametro.valor_normal }}</span>
                    	</div>
                    </div>
                    
                    <div class="form-group mb-n col-md-2">
                    	<span class="help-block text-black m-n"> {{ parametro.metodo }} </span> 
                    </div>
                </div>
			</fieldset>
    	</div>
    	<div class="col-md-12">
    		
    	</div>
		
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="gridOptionsAn.data.length==0">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>
<script type="text/javascript">
	// function adjustHeight(el){
	//     el.style.height = (el.scrollHeight > el.clientHeight) ? (el.scrollHeight)+"px" : "20px";
	// }
</script>

