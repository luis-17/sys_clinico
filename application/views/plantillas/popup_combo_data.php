<div class="modal-header">
	<h4 class="modal-title"> Selección de {{ fpc.titulo }} </h4>
</div>
<div class="modal-body row">
	<div class="form-inline mb-md col-md-12" >
		<input style="min-width: 42%;" type="text" class="form-control" ng-model="fpc.search" placeholder="Busque {{ fpc.titulo }}" />
		<button class="btn btn-info" ng-click="fpc.buscar()">Buscar</button>
	</div>
	<div class="table-responsive col-md-12" >
		<table class="table table-condensed" style="margin:0;">
			<thead>
	            <tr>
	                <th style="width:20%;"> ID </th>
	                <th style="width:80%;" > DESCRIPCION </th>
	            </tr>
	        </thead>
		</table>
		<div style="max-height: 250px; overflow:auto;">
		<table class="table table-condensed table-bordered table-hoverable">
	        <tbody>
	            <tr ng-repeat="item in fpc.lista" ng-click="fpc.selectedItem(item)" ng-class="{midnightblue: item.id == selected}">
	                <td style="width:20%;">{{ item.id }}</td>
	                <td style="width:80%;">{{ item.descripcion }}</td>
	            </tr>
	            <tr ng-if="fpc.lista.length < 1" class="text-center">
	            	<td colspan="2"> No hay datos que mostrar </td>
	            </tr>
	        </tbody>
	    </table>
	    </div>
    </div>
</div>
<div class="modal-footer">
   <!--  <button class="btn btn-primary" ng-click="aceptar()">Aceptar</button>
    <button class="btn btn-warning" ng-click="cancel()">Cancelar</button> -->
</div>