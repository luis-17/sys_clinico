<div class="modal-header">
	<h4 class="modal-title"> REPORTE ESTADISTICO  </h4>
</div>
<div class="modal-body">
	<div class="row">
		<hc-chart ng-if="metodos.chartOptions" id="chartOptions" style="width: 100%;" options="metodos.chartOptions"> GRAFICO XD </hc-chart> 
	</div>
	<div class="row" ng-if="metodos.listaColumns">

		<div class="col-md-12">
			<a class="text-primary block pt-xs text-center" ng-click="linkVerTablaDatos();"> {{ metodos.linkText }} </a> 
		</div>
		<div class="col-md-12 p-n" ng-show="metodos.contTablaDatos">
        	<div class="table-responsive">
                <table class="table table-condensed table-reporte table-bordered" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th class="text-center" ng-repeat="(key, value) in metodos.listaColumns"> {{ value }} </th> 
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="(key, value) in metodos.listaData"> 
                            <td class="text-center" ng-repeat="(keyDet, valueDet) in value track by $index " ng-bind-html="valueDet">  </td>
                        </tr>
                    </tbody>
                </table>
        	</div>
		</div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>