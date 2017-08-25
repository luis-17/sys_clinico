<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Almacén</li>
  <li class="active">Medicamentos / Almacén</li>
</ol>
<div class="container-fluid" ng-controller="medicamentoAlmacenController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
            <div class="panel-heading">
            <h2>
              <ul class="nav nav-tabs">
                <li class="active"><a data-target="#home" href="" data-toggle="tab">Gestión de Medicamentos / Almacén</a></li>
                <li><a data-target="#tab2" href="" data-toggle="tab">Gestión de Preparados / Almacén</a></li>
              </ul>
            </h2>
          </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <div class="tab-content">
                  <div class="tab-pane active" id="home">
                    <div class="row">
                      <div class="mb-sm demo-btns col-md-7 col-sm-12 ">
                          <div class="pull-left"><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></div> 
                          <div class="m-xs pull-left"> 
                            <strong>ALMACEN: 
                              <select style="height: 26px;" ng-change="listarSubAlmacenesAlmacen(fBusqueda.almacen.id);" class="" ng-model="fBusqueda.almacen" ng-options="item as item.descripcion for item in listaAlmacen"> </select>
                              <!-- <select style="height: 26px;" ng-change="listarSubAlmacenesAlmacen(fBusqueda.idalmacen);" class="" ng-model="fBusqueda.idalmacen" ng-options="item.id as item.descripcion for item in listaAlmacen"> </select>  -->
                            </strong>
                          </div> 
                          <div class="m-xs pull-left"> 
                            <strong>
                              SUB ALMACEN: 
                              <select style="height: 26px;" ng-change="getPaginationServerSide('si',true);" class="" ng-model="fBusqueda.subalmacen" ng-options="item as item.descripcion for item in listaSubAlmacen"> </select> 
                            </strong>
                          </div>
                          <div class="m-xs pull-left"> 
                            <strong>
                              LABORATORIO: 
                              <select style="height: 26px; max-width: 120px" ng-change="getPaginationServerSide('si',true);" class="" ng-model="fBusqueda.laboratorio" ng-options="item as item.descripcion for item in listaLaboratorio"> </select> 
                            </strong>
                          </div>
                          <div class="m-xs pull-left">
                            <label style="display: block;"> 
                                <small>  
                                    <input type="checkbox" ng-change="getPaginationServerSide('si',true);" ng-model="fBusqueda.allStocks" class="" /> Sólo productos con stock > cero 
                                </small>  
                            </label>
                            <!-- <strong>
                              STOCK: 
                              <select style="height: 26px; max-width: 120px" ng-change="getPaginationServerSide('si',true);" class="" ng-model="fBusqueda.tipo" ng-options="item as item.descripcion for item in listaTipoStock"> </select> 
                            </strong> -->
                          </div> 
                      </div>
                    <div class="mb-sm demo-btns col-md-5 col-sm-12" style="height: 50px;"> 
                      <div class="pull-right m-xs" >
                        <button type="button" class="btn btn-default" ng-click='btnExportarListaPdf()' style="padding: 2px 4px;" title="Exportar a PDF">
                          <i class="fa fa-file-pdf-o text-danger f-24" ></i>
                        </button>
                      </div>
                      <div class="pull-right m-xs" >
                        <button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' style="padding: 2px 4px;" title="Exportar a Excel">
                          <i class="fa fa-file-excel-o text-success f-24" ></i>
                        </button>
                      </div>
                      <div class="pull-right m-xs"><button type="button" class="btn btn-success" ng-click='btnAgregarMedicamento();'> AGREGAR AL ALMACEN </button></div>
                      <div class="pull-right m-xs" ng-if="mySelectionGrid.length > 0"><button type="button" class="btn btn-danger" ng-click='btnAnularMedicamento()'>ANULAR</button></div> 
                      <div class="pull-right m-xs" ng-if="mySelectionGrid.length ==1"><button type="button" class="btn btn-info" ng-click='btnVerKardex();'> VER KARDEX </button></div>
                      <div class="pull-right m-xs" ng-if="mySelectionGrid.length ==1 && venta_a_cliente"><button type="button" class="btn btn-warning" ng-click='btnVeHistorialPrecios();'> HISTORIAL DE PRECIOS </button></div>
                    </div>
                    <div class="form-inline col-xs-12">
                      <label class="control-label">Edite en las celdas amarillas, con doble click: </label>
                      <label ng-if="venta_a_cliente" class="ml pull-right"> <input type="radio" ng-model="fBusqueda.mostrarPartes" value="SP" ng-change="mostrarStockOPrecio(venta_a_cliente);" /> Mostrar Precio </label>
                      <label ng-if="venta_a_cliente" class="ml pull-right"><input type="radio" ng-model="fBusqueda.mostrarPartes" value="SS" ng-change="mostrarStockOPrecio(venta_a_cliente);" /> Mostrar Stock </label>
                    </div>

                    <div class="col-xs-12">
                      <div ui-grid="gridOptions" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns ui-grid-pinning class="grid table-responsive fs-mini-grid"></div>
                    </div>
                    </div>
                  </div>
                  <!-- HISTORIAL DE VENTAS POR PREPARADO -->
                  <div class="tab-pane" id="tab2">
                    <div class="row">
                      <div class="mb-sm demo-btns col-md-7 col-sm-12 ">
                          <div class="pull-left"><button class="btn btn-info" type="button" ng-click='btnToggleFiltering()'>Buscar</button></div> 
                          <div class="m-xs pull-left"> 
                            <strong>ALMACEN: 
                              <select style="height: 26px;" ng-change="listarSubAlmacenesAlmacenPreparado(fBusqueda.almacen.id);" class="" ng-model="fBusqueda.almacen" ng-options="item as item.descripcion for item in listaAlmacen"> </select>
                              <!-- <select style="height: 26px;" ng-change="listarSubAlmacenesAlmacen(fBusqueda.idalmacen);" class="" ng-model="fBusqueda.idalmacen" ng-options="item.id as item.descripcion for item in listaAlmacen"> </select>  -->
                            </strong>
                          </div> 
                          <div class="m-xs pull-left"> 
                            <strong>
                              SUB ALMACEN: 
                              <select style="height: 26px;" ng-change="getPaginationPreServerSide('si',true);" class="" ng-model="fBusqueda.subalmacenpreparado" ng-options="item as item.descripcion for item in listaSubAlmacenPreparado"> </select> 
                            </strong>
                          </div>
                          <!--<div class="m-xs pull-left"> 
                            <strong>
                              LABORATORIO: 
                              <select style="height: 26px; max-width: 120px" ng-change="getPaginationServerSide('si',true);" class="" ng-model="fBusqueda.laboratorio" ng-options="item as item.descripcion for item in listaLaboratorio"> </select> 
                            </strong>
                          </div>--> 
                      </div>
                    <div class="mb-sm demo-btns col-md-5 col-sm-12" style="height: 50px;"> 
                      <!--<div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaPdf()' title="Exportar a PDF"><i class="fa fa-file-pdf-o text-danger" ></i> </button></div>
                      <div class="pull-right m-xs" ><button type="button" class="btn btn-default" ng-click='btnExportarListaExcel()' title="Exportar a Excel"><i class="fa fa-file-excel-o text-success" ></i> </button></div>-->
                      <!-- <div class="pull-right m-xs"><button type="button" class="btn btn-success" ng-click='btnAgregarMedicamento();'> AGREGAR AL ALMACEN </button></div>
                      <div class="pull-right m-xs" ng-if="mySelectionGridPreparado.length > 0"><button type="button" class="btn btn-danger" ng-click='btnAnularPreparado()'>ANULAR</button></div>  -->
                      <div class="pull-right m-xs" ng-if="mySelectionGridPreparado.length ==1"><button type="button" class="btn btn-info" ng-click='btnVerKardex();'> VER KARDEX </button></div>
                      <!--<div class="pull-right m-xs" ng-if="mySelectionGrid.length ==1 && venta_a_cliente"><button type="button" class="btn btn-warning" ng-click='btnVeHistorialPrecios();'> HISTORIAL DE PRECIOS </button></div>-->
                    </div>
                    <div class="form-inline col-xs-12">
                      <label class="control-label">Edite en las celdas amarillas, con doble click: </label>
                      <!--<label ng-if="venta_a_cliente" class="ml pull-right"> <input type="radio" ng-model="fBusqueda.mostrarPartes" value="SP" ng-change="mostrarStockOPrecio(venta_a_cliente);" /> Mostrar Precio </label>
                      <label ng-if="venta_a_cliente" class="ml pull-right"><input type="radio" ng-model="fBusqueda.mostrarPartes" value="SS" ng-change="mostrarStockOPrecio(venta_a_cliente);" /> Mostrar Stock </label>
                    </div>-->

                    <div class="col-xs-12">
                      <div ui-grid="gridOptionsPre" ui-grid-pagination ui-grid-edit ui-grid-selection ui-grid-resize-columns ui-grid-auto-resize ui-grid-move-columns ui-grid-pinning class="grid table-responsive fs-mini-grid"></div>
                    </div>
                    </div>
                  </div>
                </div>                
              </div>
            </div>
        </div>
    </div>
</div>