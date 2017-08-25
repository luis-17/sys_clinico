<style type="text/css">
	.rTable { display: table; }
	.rTableRow { display: initial; }
	.rTableHeading { display: table-header-group; }
	.rTableBody { display: table-row-group; }
	.rTableFoot { display: table-footer-group; }
	.rTableCell, .rTableHead { display: table-cell; border: 1px solid #eeeeee; }
	.rTableHead {padding: 10px 10px;}
	.toggle-div[aria-expanded=true] .fa-chevron-down {display: none;}
	.toggle-div[aria-expanded=false] .fa-chevron-up {display: none;}
</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
	<div class="row">
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Empresa: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.descripcion_empresa }} </p> 
		</div>
		<div class="form-group col-md-4 mb-md">
			<label class="control-label mb-n"> Planilla: </label>
			<p class="help-block mt-xs"> {{ fDataDetalle.descripcion}} </p> 
		</div>
		
		<div class="col-xs-12">
			<div class="panel-heading">       
	            <ul class="nav nav-tabs">
	              	<li class="active">
	              		<a style="padding: 4px 12px;" href="" data-toggle="tab" data-target="#ac">
	              			<h4 class="m-n"> Asiento de Planilla </h4>
	              		</a>
	              	</li>
	              	<li>
	              		<a style="padding: 4px 12px;" href="" data-toggle="tab" data-target="#ap">
	              			<h4 class="m-n"> Asiento de Provisiones </h4>
	              		</a>
	              	</li>
	            </ul>
	        </div>
	        <div class="panel-body" >
                <div class="tab-content">
                    <div class="tab-pane active" id="ac">
						<div class="rTable table-bordered m-n" cellspacing="0">
							<div class="rTableHeading" style="background-color: #00bcd4; color: white">
								<div class="rTableRow">
									<div class="rTableHead" style="text-align: center;width: 100px;">Cuenta Contable</div>
									<div class="rTableHead" style="text-align: center;width: 580px;">Descripción</div>
									<div class="rTableHead" style="text-align: center;width: 100px;">Debe</div>
									<div class="rTableHead" style="text-align: center;width: 100px;">Haber</div>
								</div>
							</div>
							<div class="rTableBody" style="background-color: white; color: black">
								<div ng-repeat="datos in fDataDetalle.data">						
									<div class="rTableRow">
										<div class="rTableCell toggle-div" style="text-align: left; padding: 5px 10px;width: 100px; cursor: pointer;" ng-if="datos.detalle" 
												data-toggle="collapse" href="#{{ datos.codigo_plan }}" aria-expanded="false">
											<i class="fa fa-chevron-down pull-right"></i> <i class="fa fa-chevron-up pull-right"></i>{{ datos.codigo_plan }}
										</div>

										<div class="rTableCell" style="text-align: left; padding: 5px 10px;width: 100px;" ng-if="!datos.detalle">
											{{ datos.codigo_plan }}
										</div>

				
										<div class="rTableCell" style="padding: 5px 10px;width: 580px;"> {{ datos.glosa }} </div>

										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'D'"> {{ numberFormat(datos.monto,2) }} </div>
										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'D'">  </div>

										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'H'">  </div>
										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'H'"> {{ numberFormat(datos.monto,2) }} </div>
									</div>

									<div ng-if="datos.detalle" id="{{ datos.codigo_plan }}" class="panel-collapse collapse" style="background: #dff8fb;">

										<div ng-repeat="detalle in datos.detalle">
											<div class="rTableCell" style="padding: 5px 10px 5px 109px;width: 560px;border: 0;"> {{detalle.centro_costo}}: </div>
											<div class="rTableCell" style="padding: 5px 10px;width: 100px;border: 0;text-align: right;"> {{numberFormat(detalle.importe_local,2)}} </div>								 
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
					        <div class="text-right">
					        	<h2> TOTAL S/.<strong style="font-weight: 400;" class="text-success"> : {{  numberFormat(fDataDetalle.total_a_pagar,2) }} </strong> </h2>
					        </div>
					    </div>
						
                    </div>
                    <div class="tab-pane" id="ap">
					    <div class="rTable table-bordered m-n" cellspacing="0">
							<div class="rTableHeading" style="background-color: #e19d12; color: white">
								<div class="rTableRow">
									<div class="rTableHead" style="text-align: center;width: 100px;">Cuenta Contable</div>
									<div class="rTableHead" style="text-align: center;width: 580px;">Descripción</div>
									<div class="rTableHead" style="text-align: center;width: 100px;">Debe</div>
									<div class="rTableHead" style="text-align: center;width: 100px;">Haber</div>
								</div>
							</div>
							<div class="rTableBody" style="background-color: white; color: black">
								<div ng-repeat="datos in fDataDetalle.dataProv">						
									<div class="rTableRow">
										<div class="rTableCell toggle-div" style="text-align: left; padding: 5px 10px;width: 100px; cursor: pointer;" ng-if="datos.detalle" 
												data-toggle="collapse" href="#{{ datos.codigo_plan }}" aria-expanded="false">
											<i class="fa fa-chevron-down pull-right"></i> <i class="fa fa-chevron-up pull-right"></i>{{ datos.codigo_plan }}
										</div>

										<div class="rTableCell" style="text-align: left; padding: 5px 10px;width: 100px;" ng-if="!datos.detalle">
											{{ datos.codigo_plan }}
										</div>

				
										<div class="rTableCell" style="padding: 5px 10px;width: 580px;"> {{ datos.glosa }} </div>

										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'D'"> {{ numberFormat(datos.monto,2) }} </div>
										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'D'">  </div>

										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'H'">  </div>
										<div class="rTableCell" style="text-align: right; padding: 5px 10px;width: 100px;" ng-if="datos.debe_haber == 'H'"> {{ numberFormat(datos.monto,2) }} </div>
									</div>

									<div ng-if="datos.detalle" id="{{ datos.codigo_plan }}" class="panel-collapse collapse" style="background: #dff8fb;">

										<div ng-repeat="detalle in datos.detalle">
											<div class="rTableCell" style="padding: 5px 10px 5px 109px;width: 560px;border: 0;"> {{detalle.centro_costo}}: </div>
											<div class="rTableCell" style="padding: 5px 10px;width: 100px;border: 0;text-align: right;"> {{numberFormat(detalle.importe_local,2)}} </div>								 
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xs-12">
					        <div class="text-right">
					        	<h2> TOTAL S/.<strong style="font-weight: 400;" class="text-success"> : {{  numberFormat(fDataDetalle.total_provisiones,2) }} </strong> </h2>
					        </div>
					    </div>
                    </div>
                </div>
            </div>

		</div>
	</div>	

</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Cerrar</button>
</div>