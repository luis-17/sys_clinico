<ol class="breadcrumb"> 
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Almacén</li>
  <li class="active">Kardex</li>
</ol> 
<div class="container-fluid" ng-controller="kardexFarmController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Kardex de Farmacia </h2> 
              </div>
              <div class="panel-body">
                
                <ul class="form-group demo-btns col-xs-12">  
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Desde </label> 
                    <div class="input-group col-sm-12 col-md-12"> 
                      <input type="text" class="form-control input-sm datepicker" uib-datepicker-popup="{{dateUIDesde.format}}" ng-model="fBusqueda.desde" is-open="dateUIDesde.opened" datepicker-options="dateUIDesde.datePikerOptions" ng-required="true" close-text="Close" alt-input-formats="altInputFormats" />
                      <div class="input-group-btn">
                        <button type="button" class="btn btn-default btn-sm" ng-click="dateUIDesde.openDP($event)"><i class="ti ti-calendar"></i></button>
                      </div>
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> <label> Hasta </label> 
                    <div class="input-group col-sm-12 col-md-12"> 
                      <input type="text" class="form-control input-sm datepicker" datepicker-popup="{{dateUIHasta.format}}" ng-model="fBusqueda.hasta" 
                          is-open="dateUIHasta.opened" datepicker-options="dateUIHasta.datePikerOptions" close-text="Close" placeholder="Hasta" tabindex="3" />
                      <div class="input-group-btn">
                        <button type="button" class="btn btn-default btn-sm" ng-click="dateUIHasta.openDP($event)" tabindex="4"><i class="ti ti-calendar"></i></button>
                      </div>
                    </div> 
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n"> 
                    <div class="input-group" style=""> 
                      <!-- <input type="button" class="btn btn-info" value="PROCESAR" ng-click="getPaginationServerSide();" /> -->
                      <button type="button" class="btn btn-info" ng-click="getPaginationServerSide();">
                        <i class="ti ti-reload"> </i> PROCESAR
                      </button>
                    </div> 
                  </li>
                </ul>
                <ul class="form-group demo-btns col-xs-12">
                  <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Almacén </label>
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.almacen" ng-change="listarSubAlmacenesAlmacen(fBusqueda.almacen.id)" 
                        ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                    </div>
                  </li>
                  <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Sub-Almacén</label> 
                    <div class="input-group block"> 
                      <select class="form-control input-sm" ng-model="fBusqueda.subalmacen" ng-change="getPaginationServerSide();"ng-options="item as item.descripcion for item in listaSubAlmacen" > </select> 
                    </div>
                  </li>
                </ul>
              </div>
            </div>
        </div>
    </div>
</div>