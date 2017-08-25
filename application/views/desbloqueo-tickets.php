<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li>Gestion de Citas</li>
  <li class="active">Desbloqueo de Tickets</li>
</ol>
<div class="container-fluid" ng-controller="desbloqueoTicketsController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Desbloqueo de Tickets </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <!-- -->
                <ul class="form-group demo-btns col-xs-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Especialidad <small class="text-danger">(*)</small></label> 
                      <div class="input-group block"> 
                        <select tabindex="1" class="form-control input-sm" ng-model="fData.idespecialidad" 
                          ng-options="item.id as item.descripcion for item in listaEspecialidades" required="true" focus-me enter-as-tab> </select> 
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Nº Orden </label> 
                      <div class="input-group block"> 
                        <input tabindex="2" type="text" class="form-control input-sm" ng-model="fData.orden_venta" ng-enter="getPaginationServerSide();" placeholder="Ingrese los 3 ultimos dígitos" />
                      </div>
                    </li>
                    <!-- <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Apellido Paterno </label> 
                      <div class="input-group block"> 
                        <input tabindex="1" type="text" class="form-control input-sm" ng-model="fData.ApellidoPaterno" ng-enter="buscar();"  focus-me/>
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Apellido Materno </label> 
                      <div class="input-group block"> 
                        <input tabindex="1" type="text" class="form-control input-sm" ng-model="fData.ApellidoMaterno"  ng-enter="buscar();" />
                      </div>
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Nombres </label> 
                      <div class="input-group block"> 
                        <input tabindex="1" type="text" class="form-control input-sm" ng-model="fData.Nombres"  ng-enter="buscar();" />
                      </div>
                    </li> -->
                    <li class="form-group mr mt-sm col-sm-2 p-n"> 
                      <div class="input-group" style=""> 
                        <!-- <input type="button" class="btn btn-info" value="BUSCAR" 
                        ng-disabled="(fData.ApellidoPaterno == null || fData.ApellidoPaterno == '') && (fData.ApellidoMaterno == null || fData.ApellidoMaterno == '') && (fData.Nombres == null || fData.Nombres == '') " 
                        ng-click="getPaginationServerSide();" /> --> 
                        <input type="button" class="btn btn-info btn-sm mt-sm" value="BUSCAR" ng-disabled="fData.idespecialidad == null || fData.idespecialidad == ''" ng-click="getPaginationServerSide();" />
                        
                      </div> 
                    </li>
                </ul>
                <div class="col-xs-12 p-n">
                  <div ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns ui-grid-auto-resize class="grid table-responsive fs-mini-grid"></div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>