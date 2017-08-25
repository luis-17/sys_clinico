<ol class="breadcrumb m-n">
  <li><a href="#/">Inicio</a></li>
  <li class="active">Historias Anteriores</li>
</ol>
<div class="container-fluid" ng-controller="historiasAnterioresController"> 
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              <div class="panel-heading">
                <div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                <h2> Historias Clínicas Anteriores </h2>
              </div>
              <div class="panel-editbox" data-widget-controls=""></div>
              <div class="panel-body">
                <ul class="form-group demo-btns col-md-12">
                    <li class="form-group mr mt-sm col-sm-2 p-n" > <label> Apellido Paterno </label> 
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
                    </li>
                    <li class="form-group mr mt-sm col-sm-2 p-n"> 
                      <div class="input-group" style=""> 
                        <input type="button" class="btn btn-info" value="BUSCAR" ng-disabled="(fData.ApellidoPaterno == null || fData.ApellidoPaterno == '') && (fData.ApellidoMaterno == null || fData.ApellidoMaterno == '') && (fData.Nombres == null || fData.Nombres == '') " ng-click="buscar();" /> 
                        
                      </div> 
                    </li>
                </ul>
                <div class="col-md-12 p-n">
                  <div ui-grid="gridOptions1" ui-grid-pagination ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" ></div>
                </div>
                

                <div ng-if="mySelectionGrid.length == 1" class="col-md-12 p-n">
                  
                  <ul class="form-group demo-btns col-xs-12 mt-md" style="padding-top: 10px; border-top: 1px solid gray;min-height:50px;">
                    <li><legend style="margin-top: 10px;">DETALLE</legend></li>  
                    <li class="pull-right" ng-if="mySelectionGridDet.length > 0 && (fSessionCI.key_group == 'key_sistemas' || fSessionCI.key_group == 'key_admin' || fSessionCI.key_group == 'key_dir_salud' || fSessionCI.key_group == 'key_gerencia')"> 
                      <button type="button" class="btn btn-success" ng-click='btnImprimirFichaAtencion(mySelectionGridDet)'> Imprimir Ficha de Atención </button>
                    </li>
                    <li class="pull-right" ng-if="mySelectionGridDet.length == 1" ><button type="button" class="btn btn-info" ng-click='btnVerFichaAtencion(mySelectionGridDet)'>Ver Ficha de Atención</button></li>
                  </ul> 
                  <div ui-grid="gridOptionsDet" ui-grid-selection ui-grid-resize-columns ui-grid-move-columns class="grid table-responsive" style="min-height:200px"></div>
                </div>

              </div>
            </div>
        </div>
    </div>
</div>