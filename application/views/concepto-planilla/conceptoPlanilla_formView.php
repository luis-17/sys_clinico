<div class="modal-header">
  <h4 class="modal-title"> {{ titleForm }}  </h4>
</div>
<div class="modal-body">
  <form class="row" name="formConceptosAdd">
 
   <div class="col-md-12 col-sm-12">
      <div class="form-group col-md-3 mb-md" style="padding: 0" >
        <strong class="control-label mb-n">EMPRESA: </strong>
        <p class="help-block m-n"> {{planilla.descripcion_empresa}} </p> 
      </div>
      <div class="form-group col-md-4 mb-md">
        <strong class="control-label mb-n">PLANILLA: </strong>
        <p class="help-block m-n"> {{planilla.descripcion}} </p>
      </div>
      <div class="col-md-3 col-md-offset-2 mb-md" style="padding: 0">
        <strong class="control-label mb-n">CATEGORIA:</strong>
        <select class="form-control input-sm" ng-model="categoria_concepto" ng-options="item as item.descripcion for item in listaCategoriaConceptos" ng-change="cargar_listas()" style="margin-top: 1%;height: 30px;"> </select> 
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <strong class="control-label mb-n">Conceptos disponibles: </strong>
      <div class="col-md-12 p-n" ui-grid="gridOptionsConceptos" ui-grid-pagination ui-grid-auto-resize ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
    </div> 
    <div class="col-md-6 col-sm-12">
      <strong class="control-label mb-n">Conceptos agregados: </strong> 
      <div class="col-md-12 p-n" ui-grid="gridOptionsConceptosAdd" ui-grid-edit  ui-grid-pagination ui-grid-auto-resize ui-grid-resize-columns  class="grid table-responsive fs-mini-grid" style="overflow: hidden;"> </div>
    </div>
  </form>
</div>
<div class="modal-footer">
    <button class="btn btn-warning" ng-click="cancel()">Salir</button>
</div>