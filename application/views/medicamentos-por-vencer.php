<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>FARMACIA</li>
  <li>ALERTAS</li>
  <li class="active">MEDICAMENTOS POR VENCER</li>
</ol>
<div class="container-fluid" ng-controller="medicamentosPorVencerController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2>Gestión de Medicamentos Vencidos y/o Próximos a Vencer</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <div class="p-n demo-btns col-md-7 col-sm-12 ">
                    <div class="pull-left"><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></div> 
                    <div class="m-xs pull-left"> 
                      <strong>TIPO DE VENCIMIENTO: 
                        <select style="height: 26px;" ng-change="getPaginationServerSide()" class="" ng-model="fBusqueda.tipoVence" ng-options="item as item.descripcion for item in listaTipoVence"> </select>
                        <!-- <select style="height: 26px;" ng-change="listarSubAlmacenesAlmacen(fBusqueda.idalmacen);" class="" ng-model="fBusqueda.idalmacen" ng-options="item.id as item.descripcion for item in listaAlmacen"> </select>  -->
                      </strong>
                    </div>
                </div>
                

                <div class="p-n demo-btns col-md-5 col-sm-12" style="height: 40px;">
                  <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaPdf()' title="Exportar a PDF"><i class="fa fa-file-pdf-o text-danger" ></i> </button></div>
                  <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button></div>
                  <div class="pull-right m-xs" ng-if="mySelectionGrid.length > 0"><button type="button" class="btn btn-danger" ng-click='btnQuitarAlerta()'>QUITAR ALERTA</button></div>
                  
                </div>
                
                <div ui-grid="gridOptions" ui-grid-auto-resize ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive"></div>
              </div>
            </div>
        </div>
    </div>
</div>