<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>ALERTAS</li>
  <li class="active">CONTROL STOCKS FARMACIA</li>
</ol>
<div class="container-fluid" ng-controller="controlStockFarmaciaController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Control de Stocks de Medicamentos de Farmacia</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <div class="row">
                  <div class="mb-sm demo-btns col-md-12 col-sm-12 ">
                    <div class="pull-left"><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></div>
                    
                    <div class="m-xs pull-left"> 
                      <strong>ALMACEN: 
                        <select style="height: 26px;" ng-change="getPaginationServerSide();" class="" ng-model="fBusqueda.almacen" ng-options="item as item.descripcion for item in listaAlmacen"> </select>
                      </strong>
                    </div>
                    <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a PDF"><i class="fa fa-file-excel-o text-success" ></i> EXPORTAR A EXCEL</button></div>
                    <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaPdf()' title="Exportar a PDF"><i class="fa fa-file-pdf-o text-danger" ></i> EXPORTAR A PDF</button></div>
                  </div>
                </div>
                
                <div ui-grid="gridOptions" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive">
                  <div class="waterMarkEmptyData" ng-show="!gridOptions.data.length"> No se encontraron datos. </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>