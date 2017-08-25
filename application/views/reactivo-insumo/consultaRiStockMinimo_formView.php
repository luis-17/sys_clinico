<div class="modal-header">
  <h4 class="modal-title"> {{ titleForm }} </h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="form-group mb-md col-xs-12">
      <div ui-grid="gridOptionsRiStockMinimo" ui-grid-pagination ui-grid-selection class="grid table-responsive"></div> 
    </div>
  </div>
</div> 
<div class="modal-footer"> 
    <!-- <button class="btn btn-primary" ng-click="aceptar(); $event.preventDefault();" ng-disabled="formCliente.$invalid" > Seleccionar </button>  -->
    <button class="btn btn-warning" ng-click="cancel()" > Salir </button> 
</div>