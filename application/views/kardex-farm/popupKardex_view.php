<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
	<div class="row">
        <div class="col-md-12">
            <label class=""><strong>Producto:</strong></label>
            <span>{{fDataKardex.medicamento}}</span>
        </div>
        <div class="col-md-6">
            <label class=""><strong>Almacén:</strong></label>
            <span>{{fBusqueda.almacen.descripcion}}</span>
        </div>
        <div class="col-md-6">
            <label class=""><strong>Sub-Almacén:</strong></label>
            <span>{{fBusqueda.subalmacen.descripcion}}</span>
        </div>
        <div class="col-xs-12 col-md-12 mb"> 
            <div class="row">
                <div class="col-md-1 pr-n"> <label> Desde </label> 
                    <div class="input-group" > 
                    <input tabindex="1" type="text" class="form-control input-sm mask text-center" ng-model="fBusqueda.desde" data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" />
                    </div>
                </div>
                <div class="col-md-1 pr-n"> <label> Hasta </label> 
                <div class="input-group" > 
                    <input tabindex="4" type="text" class="form-control input-sm mask text-center" ng-model="fBusqueda.hasta"  data-inputmask="'alias': 'dd-mm-yyyy'" ng-pattern="pFecha" />
                    </div> 
                </div>
                <div class="col-md-4 mt-lg" > 
                    <input type="button" class="btn btn-info" value="PROCESAR" ng-click="procesar()" /> 
                </div> 
                <button class="btn btn-primary pull-right mt-lg mr-md" ng-click="btnExportarPdf();"><i class="fa fa-file-pdf-o"></i> EXPORTAR A PDF </button>
                <button class="btn btn-warning pull-right mt-lg mr-md" ng-click="btnExportarExcel();"><i class="fa fa-file-excel-o"></i> EXPORTAR A EXCEL</button>  
            </div>
            
        </div>
		
        <div  class="col-md-12" style="height:350px; overflow-y:scroll;"> 
            <table class="table table-kardex table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2">FECHA MOV.</th>
                        <th rowspan="2">MOVIMIENTO</th>
                        <th rowspan="2">VALOR<br>UNITARIO</th>
                        <th class="bl" colspan="2">ENTRADAS</th>
                        <th class="or" colspan="2">SALIDAS</th>
                        <th class="gr" colspan="2">SALDO</th>
                        <th rowspan="2">PRECIO<br >PROMEDIO</th>
                    </tr>
                    <tr>
                        <th class="sb">CANTIDAD</th>
                        <th class="sb">VALORES</th>
                        <th class="orl">CANTIDAD</th>
                        <th class="orl">VALORES</th>
                        <th class="grl">CANTIDAD</th>
                        <th class="grl">VALORES</th>
                    </tr>
                </thead>  
                <tbody>
                        <tr ng-repeat="row in listaMovimientos">
                            <td>{{row.fecha_movimiento}}</td>
                            <td>{{row.tipo_movimiento}}</td>
                            <td class="text-right" >{{numberFormat(row.precio_unitario,3)}}</td>
                            <td class="text-center" style="background: #B4E8FC;" >{{numberFormat(row.entrada, 0)}}</td>
                            <td class="text-right">{{numberFormat(row.valor_entrada,2)}}</td>
                            <td class="text-center" style="background: #FBEDD2;">{{numberFormat(row.salida,0)}}</td>
                            <td class="text-right">{{numberFormat(row.valor_salida,2)}}</td>
                            <td class="text-center" style="background: #E9FBD5;">{{numberFormat(row.cantidad_saldo, 0)}}</td>
                            <td class="text-right">{{numberFormat(row.valor_saldo,2)}}</td>
                            <td class="text-right">{{numberFormat(row.promedio,3)}}</td>
                        </tr>
                        <tr ng-if="!listaMovimientos.length" style="height:200px">
                        	<td colspan="10" class="waterMarkEmptyData" style="position: initial;vertical-align:middle"> No se encontraron datos. </td>
                        </tr>
                    
                </tbody>
            </table>
        
        </div>
	</div>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">CERRAR</button>
</div>