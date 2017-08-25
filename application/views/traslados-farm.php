<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Almacén</li>
  <li class="active">Traslado de productos</li>
</ol>
<div class="container-fluid" ng-controller="trasladosFarmController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Traslado de productos</h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <!-- -->
                <ul class="form-group demo-btns col-xs-12">  
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                    <div class="input-group col-sm-12 col-md-12"> 
                      <input tabindex="110" type="text" class="form-control input-sm mask" ng-model="fBusqueda.desde" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="115" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 20%; margin-left: 4px;" />
                      <input tabindex="116" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 20%; margin-left: 4px;" />
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                    <div class="input-group col-sm-12 col-md-12"> 
                      <input tabindex="120" type="text" class="form-control input-sm mask" ng-model="fBusqueda.hasta" style="width: 54%;" data-inputmask="'alias': 'dd-mm-yyyy'" />
                      <input tabindex="122" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 20%; margin-left: 4px;" />
                      <input tabindex="123" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 20%; margin-left: 4px;" />
                    </div> 
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> 
                    <div class="input-group" style=""> 
                      <!-- <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> -->
                      <button type="button" class="btn btn-info" ng-click="getPaginationServerSide('true');">
                        <i class="ti ti-reload"> </i> PROCESAR
                      </button>
                    </div> 
                  </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Almacén Origen</label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="listarSubAlmacenesAlmacen1(fBusqueda.almacen.id)" 
                        ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Sub-Almacén Origen</label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.idsubalmacen1" ng-change="listarSubAlmacenesAlmacen2(fBusqueda.almacenDestino.id,fBusqueda.idsubalmacen1)" 
                        ng-options="item.id as item.descripcion for item in listaSubAlmacenOrigen" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Almacén Destino </label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.almacenDestino" ng-options="item as item.descripcion for item in listaAlmacenesDestino" tabindex="200" 
                      ng-change="listarSubAlmacenesAlmacen2(fBusqueda.almacenDestino.id);"> </select> 
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Sub-Almacén Destino</label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.idsubalmacen2" ng-change="getPaginationServerSide();" 
                        ng-options="item.id as item.descripcion for item in listaSubAlmacenDestino" > </select> 
                    </div>
                  </li>
                 <!--  <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Producto </label> 
                    <div class="input-group block"> 
                      <select tabindex="100" class="form-control input-sm" ng-model="fBusqueda.producto" ng-change="getPaginationServerSide();"  listaSubAlmacenDestino 
                        ng-options="item as item.descripcion for item in listaProductos" > </select> 
                    </div>
                  </li> -->
                </ul> 
                <ul class="form-group demo-btns col-xs-12" style="padding-top: 10px; border-top: 1px solid #ccc;">
                  <li ><button class="btn btn-info" type="button" ng-click='btnToggleFiltering();'>Buscar</button></li>

                  <li class="pull-right" ng-if="mySelectionGrid.length > 0 && (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_dir_far' || fSessionCI.key_group == 'key_admin_far')"><button type="button" class="btn btn-danger" ng-click='btnAnularTraslado();'> <i class="fa fa-times-circle"> </i> Anular Traslado</button></li>
                  <li class="pull-right" ><button type="button" class="btn btn-success" ng-click='btnNuevoTraslado();'> <i class="fa fa-exchange"> </i> Nuevo Traslado</button></li>
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1 && items_guia_remision" > 
                    <button type="button" class="btn btn-midnightblue" ng-click='btnGenerarGuiaRemision();'>
                    <i class="fa fa-new"></i> Generar Guía de Remisión </button>
                  </li>
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1 && guia_remision" >
                    <button type="button" class="btn btn-warning" ng-click='btnVerListaGuiaRemision(mySelectionGrid[0]);'>
                    <i class="fa fa-eye"></i> Ver Guía de Remisión</button>
                  </li>
                  <li class="pull-right" ng-if="mySelectionGrid.length == 1" >
                    <button type="button" class="btn btn-warning" ng-click='btnVerDetalleTraslado(mySelectionGrid[0]);'>
                    <i class="fa fa-eye"></i>  Ver Detalle</button>
                  </li>
                  <li class="pull-right">
                    <button type="button" class="btn btn-info ml-sm" ng-click="btnImprimir(mySelectionGrid[0]);" ng-if="mySelectionGrid.length == 1" ><i class="fa fa-print"></i> [F4] Imprimir</button>
                  </li>

                </ul> 
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptions" ui-grid-pagination ui-grid-auto-resize ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive fs-mini-grid"></div>
                </div>

                
              </div>
            </div>
        </div>
    </div>
</div>