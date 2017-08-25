<style type="text/css">
	.pd{margin: 0 auto;margin-top: 30px;width: 90px;height: 90px;}
	.pd .css-shapes-up{ 
	    border-left: 30px solid transparent; 
	    border-right: 30px solid transparent; 
	    border-bottom: 30px solid #eee; 
	}
	.pd .css-shapes-bottom{ 
	   border-left: 30px solid transparent; 
	   border-right: 30px solid transparent; 
	   border-bottom: 30px solid #EEE;
	}
	.pd .css-shapes-right{ 
	   border-left: 30px solid transparent; 
	   border-right: 30px solid transparent; 
	   border-bottom: 30px solid #ddd; 
	}
	.pd .css-shapes-left{ 
	    border-left: 30px solid transparent; 
	    border-right: 30px solid transparent; 
	    border-bottom: 30px solid #ddd; 
	}
	.pd .lateral_rojo{border-bottom-color: #CA050D;}
	.pd .central_rojo{background-color: #CA050D;}
	.pd .lateral_azul{border-bottom-color: #03A9F4;}
	.pd .central_azul{background-color: #03A9F4;}
	.pd .lateral_azul_oscuro{border-bottom-color: #0055ff;}
	.pd .central_azul_oscuro{background-color: #0055ff;}

</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
	<form class="row" name="formPiezaDental">
		<div class="col-xs-12" style="min-height: 150px;">
		<span ng-if="estado.procedimiento == 0">Estado: {{estado.descripcion}}</span>
		<span ng-if="estado.procedimiento == 1">Procedimiento: {{estado.descripcion}}</span>
			<div class="pd">
				<!-- <div class="zonas {{ zona.clase }}" ng-repeat="zona in pieza.zonas" tooltip="{{ zona.zona }}" ng-click="marcarZona(cuadrante,pieza,$index,estado)" ng-class="{'zona_lateral': zona.idzona != 1 && zona.estados[0].id == estado.id, 'central_rojo':zona.idzona == 1 && zona.estados[0].id == estado.id }">{{zona.estados[0].id}}</div> -->
				<div class="zonas {{ zona.clase }}" ng-repeat="zona in pieza.zonas" tooltip="{{ zona.zona }}" ng-click="marcarZona(cuadrante,pieza,$index,estado)" ng-class="{
				'lateral_rojo': zona.idzona != 1 && zona.idzona != 6 && zona.estados[0].id == 2,
				'central_rojo': zona.idzona == 1 && zona.estados[0].id == 2 || zona.idzona == 6 && zona.estados[0].id == 2,
				'lateral_azul': zona.idzona != 1 && zona.idzona != 6 && zona.estados[0].id == 3,
				'central_azul': zona.idzona == 1 && zona.estados[0].id == 3 || zona.idzona == 6 && zona.estados[0].id == 3,
				'lateral_azul_oscuro': zona.idzona != 1 && zona.idzona != 6 && zona.estados[0].id == 23,
				'central_azul_oscuro': zona.idzona == 1 && zona.estados[0].id == 3 || zona.idzona == 6 && zona.estados[0].id == 23
				 }"></div>
				
			</div>
		</div>
		<div class="col-xs-12" style="text-align: center;">{{pieza.nombre}}</div>
	</form>
</div>
<div class="modal-footer">

    <button class="btn btn-warning" ng-click="aceptar(cuadrante,pieza)">Aceptar</button>
</div>