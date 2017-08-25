<style type="text/css">
	.zonas{position: relative;cursor:pointer;}
	.css-shapes-middle{ 
	   height: 33.3333334%; 
	   width: 33.3333334%; 
	   background-color: #D0CFC4; 
	   top:33.333334%;left:34.333334%;
	   /*border-radius: 10px;*/
	}
	.css-shapes-up{ 
	    height: 0px; 
	    width: 100%; 
	    border-radius: 10px;
	   	top:-35.3333334%;left:0px; 
	    -webkit-transform: rotate(180deg); 
	    transform: rotate(180deg); 
	    border-top: 0px transparent; 
	    border-left: 10px solid transparent; 
	    border-right: 10px solid transparent; 
	    border-bottom: 10px solid #eee; 
	}
	.css-shapes-bottom{ 
	   height: 0px; 
	   width: 100%;
	   border-radius: 10px;
	   top:2%;left:0px;
	   border-left: 10px solid transparent; 
	   border-right: 10px solid transparent; 
	   border-bottom: 10px solid #EEE;
	}
	.css-shapes-right{ 
	   height: 0px; 
	   width: 100%; 
	   border-radius: 10px;
	   top:-66.6666667%;left:35.3333334%; 
	   -webkit-transform: rotate(-90deg); 
	   transform: rotate(-90deg); 
	   border-top: 0px transparent; 
	   border-left: 10px solid transparent; 
	   border-right: 10px solid transparent; 
	   border-bottom: 10px solid #ddd; 
	}
	.css-shapes-left{ 
	    height: 0px; 
	    width: 100%; 
	    border-radius: 10px;
	    top:-100%;left:-35.3333334%;
	    -webkit-transform: rotate(90deg); 
	    transform: rotate(90deg); 
	    border-top: 0px transparent; 
	    border-left: 10px solid transparent; 
	    border-right: 10px solid transparent; 
	    border-bottom: 10px solid #ddd; 
	}

	.pos_right_2{top:-66.6666667%;}
	.pos_left_2{top:-100%;}

	.pos_up_3{top:-68.6666667%;}
	.pos_right_3{top:-67.6666667%;}
	.pos_left_3{top:-101%;}
	.pos_bottom_3{top:35.3333334%;}
	.pos_middle_3{top: 33.3333334%;}

	.pos_up_4{top:-68.6666667%;}
	.pos_left_4{top:-66.6666667%;}
	.pos_bottom_4{top:35.3333334%;}
	.pos_right_4{top:-100%;}

	.diente{/*position:absolute;*/width: 30px;height: 95px;margin: 0 10px; display:inline-block;text-align: center;}
	.pieza_name{position: relative;top: 20px;left: 17px;display: block;}
	.capa_estado, .capa_procedimiento{height: 30px;width:60px; position: relative;top: -30px;left: -15px;}
	.diente input{width: 40px; text-align:center; position:relative; left:-5px;top:-8px; color:blue; font-weight:bold;}
	.visible{visibility: visible;}
	.oculto{visibility: hidden;}

	.lateral_rojo{border-bottom-color: #CA050D!important;}
	.central_rojo{background-color: #CA050D!important;}
	.lateral_azul{border-bottom-color: #03A9F4!important;}
	.central_azul{background-color: #03A9F4!important;}
	.lateral_azul_oscuro{border-bottom-color: #0055ff;}
	.central_azul_oscuro{background-color: #0055ff;}
	.text_rojo{color:#CA050D!important;}
	#caja .col-md-6, #caja .col-md-3{padding: 0px}
	#odontograma .col-xs-6 {min-height: 70px; padding: 5px;}
	#odontograma .col-xs-6:nth-child(odd) {border-right:1px solid #ccc;}
</style>
<div class="modal-header">
	<h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body pt-sm">
	<form class="row" name="formAtencionOdontologica">
		<fieldset class="col-xs-12 mb-sm pb-n" id="odontograma">
			<div class="col-xs-6" style="min-height: 70px;" ng-repeat="cuadrante in listaOdontograma.cuadrantes"><!-- BUCLE DE CUADRANTES -->
				<div class="diente {{pieza.clase_pieza}}" ng-repeat="pieza in cuadrante.piezas"> 
					<p tooltip="{{ pieza.nombre }}" style="margin: 0 0 5px;"> {{ pieza.id }} </p>
					<!-- <p>{{pieza.marca}}-{{pieza.zonas[0].estados[0].id}}</p> -->
					<input type="text" ng-model="pieza.zonas[0].estados[0].simbolo" readonly="readonly" ng-class="{'text_rojo':pieza.zonas[0].estados[0].id == 16}"/>
					<div style="width:30px;height:30px" ng-click="marcarProcedimientoPieza($parent.$index, pieza, false)">
						<div ng-repeat="zona in pieza.zonas" class="zonas {{ zona.clase }}" ng-class="{
						'lateral_rojo': zona.idzona != 1 && zona.idzona != 6 && zona.estados[0].id == 2,
						'central_rojo': zona.idzona == 1 && zona.estados[0].id == 2 || zona.idzona == 6 && zona.estados[0].id == 2,
						'lateral_azul': zona.idzona != 1 && zona.idzona != 6 && zona.estados[0].id == 3,
						'central_azul': zona.idzona == 1 && zona.estados[0].id == 3 || zona.idzona == 6 && zona.estados[0].id == 3,
						'lateral_azul_oscuro': zona.idzona != 1 && zona.idzona != 6 && zona.estados[0].id == 23,
						'central_azul_oscuro': zona.idzona == 1 && zona.estados[0].id == 3 || zona.idzona == 6 && zona.estados[0].id == 23
						 }"></div>
					</div>
					<div class="capa_estado" style="background:url('assets/img/odonto/{{pieza.zonas[0].estados[0].imagen}}') center no-repeat;" ng-class="pieza.zonas[0].estados[0].imagen == null ? 'oculto' : 'visible'" ng-click="marcarProcedimientoPieza($parent.$index, pieza, true)"></div>

				</div>

			</div>
		</fieldset>
		<fieldset class="col-xs-4 mb-sm pb-n">
			<legend class="col-xs-12 mb-sm pb-n" style="font-size: 16px; font-weight: bold;"> PROCEDIMIENTO ODONTOLOGICO </legend>
			<div class="form-group mb-n">
				<label class="col-md-4 control-label">Paciente</label>
				<div class="col-md-8" style="padding: 0px;">
					<!-- <input type="text" class="form-control" placeholder="{{ fData.cliente }}" readonly="readonly"> -->
					<span class="form-control text-black m-n">{{ fData.cliente }}</span>
				</div>
			</div>
			
            <div class="form-group mb-n">
				<label class="col-md-4 control-label">Fecha</label>
				<div class="col-md-8" style="padding: 0px;">
					<input type="text" class="form-control" value="{{ listaOdontograma.fecha_creacion }}" readonly="readonly">
				</div>
			</div>
           
		</fieldset>
		<fieldset class="col-xs-8 mb-sm pb-n">
			<div class="form-group mb-md col-md-4" >
				<label class="control-label mb-xs">Categoria</label>
				<select class="form-control input-sm" ng-model="fData.categoria" ng-options="item.descripcion for item in listaCategorias track by item.id"> </select>
			</div>
			<div class="form-group mb-md col-md-8" >
				<div class="form-group mb-sm">
                  <label class="m-n text-black block"> Observaciones </label> 
                  <textarea ng-model="fDataProc.observacion" class="form-control input-sm" required placeholder="Digite alguna observación del procedimiento"></textarea>
                </div>
			</div>
    
		</fieldset>
	</form>
</div>
<div class="modal-footer">
    <div class="form-group mb-sm text-right">
	    <button type="button" class="btn btn-primary" ng-click="guardarOdontogramaProcedimientos();">
	    	<i class="fa fa-floppy-o"></i> GUARDAR
	    </button>
    </div>
</div>