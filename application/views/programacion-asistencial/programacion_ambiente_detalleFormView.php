<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }} </h4>
</div>

<div class="modal-body">  
    <form class="row" name="detProgramacionAmbienteDias"> 
      	<div class="form-group mb-n col-md-12"  >
	        <div class="form-group mb-n col-md-2"  >
	        	<p class="mb-n">AMBIENTE: {{datos.ambiente}}</p>
	        </div>
	        <div class="form-group mb-n col-md-3"  >
	        	<p class="mb-n">CATEGORIA: {{datos.categoria}}</p>
	        </div>
	        <div class="form-group mb-n col-md-4"  >
	        	<p class="mb-n">SUBCATEGORIA: {{datos.subcategoria}}</p>
	        </div>
      	</div>
      	<div class="form-group mb-n col-md-12"  >
	        <p class="form-group col-md-12">FECHA: {{datos.fecha}}</p>
      	</div>
		<div class="planning-det">
			<div class="header">
		      <div class="item-hora">HORAS</div>
		      <div class="item-ambiente">{{datos.ambiente}}</div>
		    </div>
			<div class="planning-det no-visible">		
				<table class="table table-bordered">
			        <thead>
			          <tr>
			            <th ng-repeat="item in planning_detalle.header" class="{{item.class}}" >
			                <div>{{item.hora_formato}}</div>
			            </th >                      
			          </tr>                      
			        </thead>
			        <tbody>
		              	<tr>
			                <td ng-repeat="item in planning_detalle.gridTotal" class="{{item.clase}} {{datos.tipo}}" ng-click="verDetalleItemAmbiente(item)" >
			                  	<div class="label" >
							            {{item.dato}} <i class="fa fa-info-circle" ng-show="item.tooltip" ></i>
					            </div>
			                </td>
		              	</tr>                      
		            </tbody>                    
			    </table>	
			</div>
		</div>

		<div  class="form-group mb-n col-md-12" style="margin-top: 20px;" >
			<p class="mb-n col-md-12">RESPONSABLE: {{datos.responsable}}</p>
			<label class="mb-n col-md-12">COMENTARIO:</label>
			<p class="col-md-12">{{datos.comentario}}</p>
		</div>

    </form>
</div>	

<div class="modal-footer">
	<button class="btn btn-warning" ng-click="detCancel()">Cerrar</button>
</div>

