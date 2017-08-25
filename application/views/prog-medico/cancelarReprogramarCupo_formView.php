<div class="modal-header">
	<h4 class="modal-title"> {{ titleFormModal }} </h4>
</div>
<div class="modal-body">
	<div ng-if="fDataModal.tipo == 'cancelar'" class="row">
		<div class="col-md-12 control-label mb text-center" style="font-size: 16px; font-weight: bold;color: red;"> 
			{{fDataModal.mensaje}} 
		</div>
		<div class="mb-n col-md-12" >
			<div class="row" >
		        <div class="col-md-4">
		          <strong class="control-label mb-n">PROFESIONAL PROGRAMADO: </strong>
		          <p class="help-block m-n truncate"> {{ fDataListaPaciente.medico }} </p>
		        </div>

		        <div class="col-md-4">
		          <strong class="control-label mb-n">SERVICIO: </strong>
		          <p class="help-block m-n truncate"> {{ fDataListaPaciente.especialidad }} </p>
		        </div>

		        <div class="col-md-4">
		          <strong class="control-label mb-n">EMPRESA: </strong>
		          <p class="help-block m-n truncate"> {{ fDataListaPaciente.empresa }} </p>
		        </div>
			</div>
		</div>
		<div class="mb-n col-md-12" >
			<div class="row">
				<div class="col-md-4">
		          <strong class="control-label mb-n">FECHA: </strong>
		          <p class="help-block m-n"> {{ fDataListaPaciente.fecha_programada }} </p>
		        </div>

		        <div class="col-md-4">
		          <strong class="control-label mb-n">AMBIENTE: </strong>
		          <p class="help-block m-n"> {{ fDataListaPaciente.ambiente.numero_ambiente }} </p>
		        </div>

		        <div class="col-md-4">
		          <strong class="control-label mb-n">INTERVALO DE ATENCIÓN: </strong>
		          <p class="help-block m-n"> {{ fDataListaPaciente.intervalo_hora_int }} Min. </p>
		        </div>
			</div>
		</div>
		<div class="mb-n col-md-12" >
			<div class="row">
				<div class="col-md-4">
		          <strong class="control-label mb-n">N° CUPO </strong>
		          <p class="help-block m-n"> {{ fDataModal.cupo.numero_cupo }} </p>
		        </div>
		        <div class="col-md-4">
		          <strong class="control-label mb-n">HORA INICIO ATENCIÓN: </strong>
		          <p class="help-block m-n"> {{ fDataModal.cupo.hora_inicio_formato }} </p>
		        </div>

		        <div class="col-md-4">
		          <strong class="control-label mb-n">HORA FIN ATENCIÓN: </strong>
		          <p class="help-block m-n"> {{ fDataModal.cupo.hora_fin_formato }} </p>
		        </div>		     
			</div>
		</div>
	</div> 
	<div ng-if="fDataModal.tipo == 'reprogramar' " class="row" style="padding-left: 10px;padding-right: 10px;">
		<div class="control-label" style="font-size: 12pt; padding-bottom: 10px;">{{fDataModal.mensaje}} </div>
		<strong class="control-label mb-n">PACIENTE: </strong><div class="help-block inline" >{{fDataModal.oldCita.paciente}} </div> 
		<div class="mb-n col-md-12" >
			<div class="row" >
		        <div class="col-md-12">
		          <strong class="control-label mb-n">CITA ANTERIOR: </strong>
		        </div>
			</div>

			<div class="mb-n col-md-12" >
				<div class="row" >
			        <div class="col-md-4">
			          <strong class="control-label mb-n">PROFESIONAL: </strong>
			          <p class="help-block m-n truncate"> {{ fDataModal.oldCita.medico }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">SERVICIO: </strong>
			          <p class="help-block m-n truncate"> {{ fDataModal.oldCita.especialidad }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">EMPRESA: </strong>
			          <p class="help-block m-n truncate"> {{ fDataModal.oldCita.empresa }} </p>
			        </div>
				</div>
			</div>

			<div class="mb-n col-md-12" >
				<div class="row">
					<div class="col-md-4">
			          <strong class="control-label mb-n">FECHA: </strong>
			          <p class="help-block m-n"> {{ fDataModal.oldCita.fecha_programada }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">AMBIENTE: </strong>
			          <p class="help-block m-n"> {{ fDataModal.oldCita.numero_ambiente }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">INTERVALO DE ATENCIÓN: </strong>
			          <p class="help-block m-n"> {{ fDataModal.oldCita.intervalo_hora_int }} Min. </p>
			        </div>
				</div>
			</div>

			<div class="mb-n col-md-12" >
				<div class="row">
					<div class="col-md-4">
			          <strong class="control-label mb-n">N° CUPO </strong>
			          <p class="help-block m-n"> {{ fDataModal.oldCita.numero_cupo }} <span ng-if="fDataModal.oldCita.si_adicional"> (adicional)</span></p>
			        </div>
			        <div class="col-md-4">
			          <strong class="control-label mb-n">HORA INICIO ATENCIÓN: </strong>
			          <p class="help-block m-n"> {{ fDataModal.oldCita.hora_inicio_formato }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">HORA FIN ATENCIÓN: </strong>
			          <p class="help-block m-n"> {{ fDataModal.oldCita.hora_fin_formato }} </p>
			        </div>		     
				</div>
			</div>

		</div>

		<div class="mb-n col-md-12" >
			<div class="row" >
		        <div class="col-md-12">
		          <strong class="control-label mb-n">NUEVA CITA SELECCIONADA: </strong>
		        </div>
			</div>		

			<div class="mb-n col-md-12" >
				<div class="row" >
			        <div class="col-md-4">
			          <strong class="control-label mb-n">PROFESIONAL: </strong>
			          <p class="help-block m-n truncate"> {{ fBusqueda.programacion.medico }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">SERVICIO: </strong>
			          <p class="help-block m-n truncate"> {{ fBusqueda.programacion.especialidad }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">EMPRESA: </strong>
			          <p class="help-block m-n truncate"> {{ fBusqueda.programacion.empresa }} </p>
			        </div>
				</div>
			</div>

			<div class="mb-n col-md-12" >
				<div class="row">
					<div class="col-md-4">
			          <strong class="control-label mb-n">FECHA: </strong>
			          <p class="help-block m-n"> {{ fBusqueda.programacion.fecha_str }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">AMBIENTE: </strong>
			          <p class="help-block m-n"> {{ fBusqueda.programacion.ambiente.numero_ambiente }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">INTERVALO DE ATENCIÓN: </strong>
			          <p class="help-block m-n"> {{ fBusqueda.programacion.intervalo_hora_int }} Min. </p>
			        </div>
				</div>
			</div>

			<div class="mb-n col-md-12" >
				<div class="row">
					<div class="col-md-4">
			          <strong class="control-label mb-n">N° CUPO </strong>
			          <p class="help-block m-n"> {{ fDataModal.nuevaCita.numero_cupo }} <span ng-if="fDataModal.nuevaCita.si_adicional"> (adicional)</span> </p>
			        </div>
			        <div class="col-md-4">
			          <strong class="control-label mb-n">HORA INICIO ATENCIÓN: </strong>
			          <p class="help-block m-n"> {{ fDataModal.nuevaCita.hora_inicio_formato }} </p>
			        </div>

			        <div class="col-md-4">
			          <strong class="control-label mb-n">HORA FIN ATENCIÓN: </strong>
			          <p class="help-block m-n"> {{ fDataModal.nuevaCita.hora_fin_formato }} </p>
			        </div>		     
				</div>
			</div>
		</div>
	</div> 
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" ng-click="btnCancel();">SALIR</button>
	<button ng-show="fDataModal.tipo == 'cancelar'" type="button" class="btn btn-primary" ng-click="btnOk();">{{fDataModal.boton}}</button>
	<button ng-show="fDataModal.tipo == 'reprogramar'" type="button" class="btn btn-primary" ng-click="btnOk();">SI,DESEO MODIFICAR LA CITA</button>
</div>