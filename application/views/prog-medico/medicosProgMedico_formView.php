<div class="modal-content ">
  <div class="modal-header">
    <h4 class="modal-title"> {{ titleForm }} </h4>
  </div>

  <div class="modal-body">  
    <form class="row" name="formMedicoProgMedico"> 
      <div class="form-group mb-md col-md-12" >
        <div ui-grid="gridOptionsMedico" ui-grid-selection ui-grid-pagination ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
      </div>
    </form>
  </div>
    
  <div class="modal-footer">
      <button class="btn btn-primary" ng-click="aceptarMedico(); ">Aceptar</button>
  </div>
</div>