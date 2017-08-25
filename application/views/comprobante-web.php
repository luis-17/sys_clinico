<ol class="breadcrumb m-n">
  <li><a href="#/">CONTABILIDAD</a></li>
  <li>COMPROBANTES DE PAGO WEB</li> 
</ol>
<div class="container-fluid" ng-controller="comprobanteWebController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>COMPROBANTES DE PAGO WEB</h2> 
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns">
                    <li><button class="btn btn-info" type="button" style="position: relative;top: -3px;" ng-click='btnToggleFiltering()'>Buscar</button></li>
                    <li>
                      <select class="form-control" 
                              ng-model="filtroEstado" ng-change="getPaginationServerSide();" 
                              ng-options="item.descripcion for item in listaEstado" 
                              aria-invalid="false" style="">
                      </select>
                    </li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_comprobante.boolean == 2"><button type="button" class="btn btn-success" ng-click='btnCargar(mySelectionGrid[0])'>Cargar Comprobante</button></li>
                    <li class="pull-right" ng-if="mySelectionGrid.length == 1 && mySelectionGrid[0].estado_comprobante.boolean == 1"><button type="button" class="btn btn-warning" ng-click='btnBorrar(mySelectionGrid[0])'>Borrar Comprobante</button></li>
                </ul>
                <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>