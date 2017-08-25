<div class="modal-header">
  <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body"> 
  <div class="row" > 
    <div class="col-md-12">
        <p class="mb-n">FECHA: {{fComentario.fecha_evento}}</p>
        <p class="mb-n">RESPONSABLE: {{fComentario.nombres}} {{fComentario.apellido_paterno}} {{fComentario.apellido_materno}}</p>
        <label class="mb-n">COMENTARIO:</label>
        <p class="">{{fComentario.comentario}}</p>
    </div>
  </div>    
</div>  
<div class="modal-footer"> 
    <button class="btn btn-warning" ng-click="cancelComent();">SALIR</button>
</div>