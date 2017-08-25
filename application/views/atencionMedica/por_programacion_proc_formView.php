<div class="modal-header" >
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
    <form class="row" > 
		<div class="form-group mb-md col-md-4">
			<strong class="mb-xs"> Programación de hoy: <span class="text-info">{{fData.fecha}} </span></strong>
			<select ng-options="item.id as item.descripcion for item in listaProgramaciones" class="form-control input-sm mt-xs" ng-model="fData.programacion" ng-change="getPaginationProgServerSide();" tabindex="1" focus-me required style="width: 82%;"> </select>
		</div>
		<div class="form-group mb-md col-md-3">
			<strong class="mb-xs"> Especialidad: </strong>
			<p class="help-block">{{fSessionCI.especialidad}}</p>
		</div>
        <div class="form-group mb-md col-md-2">
            <strong class="mb-xs"> N° Procedimientos: </strong>
            <p class="help-block">{{fCountTotales.proc_atendidos}} / {{fCountTotales.proc_totales}}</p>
        </div>
        <div class="form-group mb-md col-md-12">
            <button tooltip-placement="bottom" tooltip="Actualizar" title="" type="button" class="btn btn-sm btn-info pull-right" ng-click="getPaginationProgServerSide(); $event.preventDefault();"> <i class="ti ti-reload "></i>  ACTUALIZAR </button>
        </div>
        <div class="form-group col-xs-12 m-n">   
            <div ui-if="gridOptionsProg.data.length>0" 
                ui-grid="gridOptionsProg" ui-grid-resize-columns ui-grid-auto-resize ui-grid-pagination class="grid table-responsive" style="overflow: hidden;" ></div>
        </div>
	</form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>