<div class="panel panel-danger" >
  <div class="panel-heading">
    <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
    <h2>Gestión de Empleados en planilla</h2> 
  </div>
  <div class="panel-editbox" data-widget-controls=""></div>
  <div class="panel-body">
  <div class="col-md-3 col-sm-6 col-xs-12 p-n">
    <h4 class="m-xs"> EMPRESA <strong style="font-weight: 100;" class="text-info"> : {{ mySelectionGrid[0].descripcion_empresa }} </strong> </h4> 
  </div>
  <ul class="form-group demo-btns col-md-6 col-xs-12 clear">
    <li class="pull-left">
      <button type="button" class="btn btn-info" ng-click='btnToggleEmplFiltering()'>BUSCAR</button>
    </li>    
  </ul>
  <ul class="form-group demo-btns col-md-6 col-xs-12">
    <li class="pull-right">
      <button type="button" class="btn btn-info" ng-click='getPaginationEmplServerSide(true)'>
        <i class="fa fa-refresh"></i>
      </button>
    </li>
    <li class="pull-right">
      <button type="button" class="btn btn-warning" ng-click='changeViewEmpleado(false)'>REGRESAR</button>
    </li>
    <li class="pull-right">
      <button type="button" ng-if="mySelectionEmplGrid.length > 0 && mySelectionGrid[0].estado_pl == 1 " class="btn btn-success" ng-click='calcularPlanilla()'>CALCULAR</button>
    </li>
    <li class="pull-right" ng-if="fSessionCI.key_group == 'key_sistemas'">
      <button type="button" class="btn btn-info" ng-click='btnImprimirboleta()' ng-disabled="mySelectionEmplGrid.length < 1">IMPRIMIR BOLETA</button>
    </li>
    <li class="pull-right" ng-if="fSessionCI.key_group == 'key_sistemas'">
      <button type="button" class="btn" ng-click='btnVariablesLey()'>VARIABLES DE LEY</button>
    </li>
  </ul>
    <div ui-grid="gridOptionsEmpl" 
          ui-grid-pagination ui-grid-selection ui-grid-auto-resize ui-grid-pinning ui-grid-resize-columns ui-grid-move-columns 
          class="grid table-responsive"></div> 
  </div>
</div>

