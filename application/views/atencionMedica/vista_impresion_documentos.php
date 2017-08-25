<html>
       <head>
              <link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" />
       </head>
       <body onload="window.print()"> 
              <div class="imp_doc">
              <h2>CERTIFICADO DE INCAPACIDAD TEMPORAL PARA EL TRABAJO</h2>
              <p>EE.SS: VILLA SALUD - VILLA EL SALVADOR</p>
              <p><b>Acto Médico:</b>  {{fData.num_acto_medico}} </p>
              <p><b>Especialidad:</b>  {{fData.especialidad}} </p>
              <hr/>
              <p><b>Paciente:</b>  {{fData.cliente}} </p>
              <p><b>DNI:</b>  {{fData.numero_documento}} </p>
              <hr/>
              <p><b>Area Hospitalaria:</b>  {{fData.area_hospitalaria}} </p>
              <p><b>Contingencia:</b>  {{fData.contingencia}} </p>
              <hr/>
              <p><b>PERIODO DE INCAPACIDAD</b></p>
              <p><b>Fecha de Inicio:</b>  {{fData.fecha_iniciodescanso}} </p>
              <p><b>Fecha Fin:</b>  {{fData.fecha_final}} </p>
              <p><b>Total de Días:</b>  {{fData.dias}} </p>
              <p><b>Fecha de Otorgamiento:</b>  {{fData.fecha_otorgamiento}} </p>
              <p><b>Médico Tratante:</b> Med.  {{fSessionCI.colegiatura}} <br> {{fSessionCI.profesional}} </p>
              <hr/>
              <p><b>OBSERVACIONES:</b></p> 
              <br/>
              <hr/>
              <p style="font-size:11px"><b>Usuario:</b>  {{fSessionCI.username}} </p>
              <p style="font-size:11px"><b>Fecha:</b>  {{fData.fechaAtencion}}  Hora:  {{hora}} </p>
              </div>
       </body>
</html>