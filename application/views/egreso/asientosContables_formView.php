<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
<!--  
	<div class="row">
		<div class="col-xs-12"> 
			<div ui-grid="gridOptionsDetalle" ui-grid-auto-resize ui-grid-resize-columns class="grid table-responsive fs-mini-grid"></div> 
		</div>
	</div>
-->
	<div class="row">
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> N° RUC: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.ruc }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Empresa: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.empresa }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md" ng-if="modulo != 'cajaChica' ">
			<label class="control-label mb-n"> Periodo: </label>
			<p class="help-block mt-xs"> {{ fBusqueda.desde }} al {{ fBusqueda.hasta }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md" ng-if="modulo == 'cajaChica'">
			<label class="control-label mb-n"> Fecha Apertura: </label>
			<p class="help-block mt-xs"> {{ arr.cajaChica.fecha_apertura }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Fecha: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.fecha_emision }}</p> 
		</div>

		<div class="form-group col-md-6 mb-md">
			<label class="control-label mb-n"> Comentario: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.glosa }}</p> 
		</div>
		<div class="col-xs-12">
			<table class="table table-bordered m-n" cellspacing="0">
				<thead style="background-color: #00bcd4; color: white">
					<tr>
						<th>Cuenta Contable</th>
						<th>Debe</th>
						<th>Haber</th>
					</tr>
				</thead>
				<tbody style="background-color: white; color: black">
					<tr  ng-repeat="datos in fDataDetalle.data">
						<td> {{ datos.codigo_plan }} </td>
						<td> {{ datos.debe }} </td>
						<td> {{ datos.haber }} </td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-xs-12">
	        <div class="text-right">
	        	<h2> TOTAL <strong style="font-weight: 400;" class="text-success"> : {{  fDataDetalle.total_a_pagar }} </strong> </h2>
	        </div>
	    </div>
	</div>	

</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>