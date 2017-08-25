<style type="text/css">
    #gridMedicamentos .ui-grid-pager-panel .ui-grid-pager-container .ui-grid-pager-row-count-picker .ui-grid-pager-row-count-label{display: none!important;}
</style>
<ol class="breadcrumb">
    <li><a href="#/"> Central de Reportes </a></li>
</ol>
<div class="container-fluid" ng-controller="centralReportesController">
    <div class="row">
        <div class="col-lg-8 col-md-7 col-sm-12">
            <div class="panel panel-success" >
                <div class="panel-heading">
                    <h2> MODULOS </h2>
                </div>
                <div class="panel-body">
                    <accordion close-others="oneAtATime" class="accordion-default" >
                        <accordion-group ng-repeat="(key, obj) in listaEstadisticas" heading="{{obj.textReporte}}" class="col-md-12 col-sm-12 col-xs-12 p-n show" is-open="obj.open" style="visibility: visible;" > 
                            <div ng-repeat="(keyDet, objDet) in obj.reportes" class="col-lg-2 col-md-4 col-sm-6 col-xs-12 heightFixed120" > 
                                <div class="block text-center item-reporte" ng-click="selectReport(objDet)" ng-class="{selected: objDet.id == selectedReport.id}">
                                    <div style="text-align: center; display: inline-block; height: auto;"> 
                                        <i class="fa text-success" ng-class="{ 'fa-file-text-o': objDet.tipoCuadro == 'report' , 'fa-bar-chart-o': objDet.tipoCuadro == 'graph' }"></i> 
                                    </div>
                                    <p class="text-center pl-xs pr-xs" style="padding-bottom: 6px; margin: 4px 0px 0px; line-height: 1;">{{ objDet.name }}</p> 
                                </div>
                            </div>
                        </accordion-group>
                    </accordion>
                    <div class="waterMarkEmptyData" ng-show="showDivEmptyData"> No se encontraron reportes para el usuario. </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-5 col-sm-12">
            <div class="panel panel-success" data-widget='{"id" : "wiget10000"}'>
                <div class="panel-heading">
                    <div class="panel-ctrls button-icon" data-actions-container="" data-action-colorpicker=''> </div> 
                    <h2> FILTROS DE BUSQUEDA </h2> 
                </div>
                <div class="panel-body pl-n pr-n pb-n" style="min-height: 254px;"> 
                    <div class="row" ng-hide="selectedReport.id">
                        <div class="col-md-12 col-sm-12 col-xs-12"> 
                            <div class="waterMarkEmptyData" style="font-size: 22px;" > Seleccione un reporte... </div>
                        </div>
                    </div>
                    <ul class="row demo-btns" ng-show="selectedReport.id" style="min-height: 200px;">
                       <!--  <li class="col-md-12 col-sm-12 form-group"
                            ng-show="selectedReport.id=='LOG-IAF'"> 
                            <label> Almacén </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.almacen" ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                        </li> -->
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='VT-RC'    || selectedReport.id=='VT-RCTD'   || selectedReport.id=='CE-IOM'     
                                || selectedReport.id=='FAR_VT-DCF' || selectedReport.id=='CE-PCE'    || selectedReport.id=='AM-CPM'
                                || selectedReport.id=='FAR_VT-MED' || selectedReport.id=='FAR_VT-RC' || selectedReport.id=='FAR_MED-M'
                                || selectedReport.id=='FAR_VT-CV'  || selectedReport.id=='FAR-MMV'   || selectedReport.id=='FAR-MNV'
                                || selectedReport.id=='FAR-MMC'    || selectedReport.id=='FAR-LMV'   || selectedReport.id=='FAR-CPF'
                                || selectedReport.id=='FAR-REVD'   || selectedReport.id=='LOG-OC'    || selectedReport.id=='HOS-TARIF'
                                || selectedReport.id=='FAR-VM'     || selectedReport.id=='FAR_FORM'  || selectedReport.id=='FAR_RVF'
                                || selectedReport.id=='FAR-FVC'    || selectedReport.id=='MK-LCA'"> 

                            <label> EMPRESAS / SEDES </label> 
                            <select class="form-control input-sm " ng-model="fBusqueda.sedeempresa" ng-options="item.id as item.descripcion for item in listaSedeEmpresaAdmin" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AS-APE' || selectedReport.id=='RH-RVC'"> 
                            <label> POR EMPRESA / SEDE </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.porEmpresaOSede" ng-options="item as item.descripcion for item in listaEmpresaOSede" > </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='FAR-SMPA' || selectedReport.id=='FAR-TARIF' || selectedReport.id=='LOG-IAF'
                                || selectedReport.id=='LOG-TRAS'   || selectedReport.id=='FAR-IVM'   || selectedReport.id=='FAR-IMU'
                                || selectedReport.id=='FAR-SCV'"> 
                            <label> ALMACÉN </label> 
                            <select ng-change="getListaSubAlmacenes();" class="form-control input-sm " ng-model="fBusqueda.almacen" ng-options="item as item.descripcion for item in listaAlmacenes" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='FAR-TARIF' || selectedReport.id=='FAR-SCV'"> 
                            <label> SUBALMACÉN DE VENTA </label> 
                            <select class="form-control input-sm " ng-model="fBusqueda.subalmacen" ng-options="item as item.descripcion for item in listaSubAlmacenes" > </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-REV' || selectedReport.id=='CE-REPR' || selectedReport.id=='CE-RETP' || selectedReport.id=='CE-CNC' || selectedReport.id=='CE-CDU' || selectedReport.id=='CE-REVD'"> 
                            <label> UNIDAD DE NEGOCIO </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.unidadNegocio" ng-options="item as item.descripcion for item in listaUnidadesNegocio" > </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="(selectedReport.id=='AS-APE'   && fBusqueda.porEmpresaOSede.id == 'PS' )
                                || (selectedReport.id=='RH-RVC'    && fBusqueda.porEmpresaOSede.id == 'PS' )
                                || selectedReport.id=='RH-CDD'      || selectedReport.id=='RH-CPO'      || selectedReport.id=='RH-MER'      
                                || selectedReport.id=='RH-CEP'      || selectedReport.id=='RH-AEHT'     || selectedReport.id=='RH-CTC' 
                                || selectedReport.id=='CE-REV'      || selectedReport.id=='CE-REES'     || selectedReport.id=='CE-RSOL' 
                                || selectedReport.id=='CE-REPR'     || selectedReport.id=='CE-RETP'     || selectedReport.id=='CE-RSEX'
                                || selectedReport.id=='CE-REEDS'    || selectedReport.id=='CE-RVD'      || selectedReport.id=='CE-REVD' 
                                || selectedReport.id=='CE-CNC'      || selectedReport.id=='CE-CDU'      || selectedReport.id=='CE-RSME'    
                                || selectedReport.id=='VT-CIE'      || selectedReport.id=='AM-LA'       || selectedReport.id=='PA-RP'
                                || selectedReport.id=='MK-RPE'      || selectedReport.id=='PA-RCP'      || selectedReport.id=='W-VW'
                                || selectedReport.id=='FAR-CMMA'"> 
                            <label> SEDE </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.sede" ng-options="item as item.descripcion for item in listaSedes" ng-change="cargarEmpresaAdminPorSede(fBusqueda.sede)"> </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-REV'    || selectedReport.id=='CE-REES'     || selectedReport.id=='CE-REPR'
                                || selectedReport.id=='CE-RETP'     || selectedReport.id=='CE-REEDS'    || selectedReport.id=='CE-CNC'
                                || selectedReport.id=='CE-CDU'      || selectedReport.id=='CE-REVD'     || selectedReport.id=='CE-RVD'
                                || selectedReport.id=='VT-CIE'      || selectedReport.id=='AM-LA'       || selectedReport.id=='MK-RPE' 
                                || selectedReport.id=='FAR-CMMA'    || selectedReport.id=='FAR-VUC'     || selectedReport.id=='FAR-VUCD'"> 
                            <label> EMPRESA ADMIN </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.empresaAdmin" ng-options="item as item.descripcion for item in listaEmpresasAdminSede" > </select> 
                        </li>
                        <!-- SOLO EMPRESAS ADMIN INDEPENDIENTE DE LA SEDE -->
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AM-LT' || selectedReport.id=='RH-PLAN'">
                            <label> EMPRESA ADMIN </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.empresaSoloAdmin" ng-options="item as item.descripcion for item in listaEmpresasAdmin" ng-change="getEmpresaEspecialidad(fBusqueda.empresaSoloAdmin)"> </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="(selectedReport.id=='AS-APE'   && fBusqueda.porEmpresaOSede.id == 'PE' )
                                || (selectedReport.id=='RH-RVC'    && fBusqueda.porEmpresaOSede.id == 'PE' )
                                || selectedReport.id=='AS-REMP'     || selectedReport.id=='AS-REMT' || selectedReport.id=='AS-REMF'
                                || selectedReport.id=='AS-REMHE'"> 
                            <label style="width: 100%;"> EMPRESA <small style="margin-left: 52%;" ng-show="selectedReport.id=='AS-REMP' || selectedReport.id=='AS-REMT' 
                                || selectedReport.id=='AS-REMF' || selectedReport.id=='AS-REMHE' ">  <input type="checkbox" ng-model="fBusqueda.allEmpresas" class="" /> Todos </small> </label> 
                            <select ng-disabled="fBusqueda.allEmpresas" class="form-control input-sm mb" ng-model="fBusqueda.empresa" ng-options="item as item.descripcion for item in listaEmpresas" > </select> 
                        </li> 
                        <li class="col-sm-6 form-group" ng-show="selectedReport.id=='AS-APE' && fBusqueda.allEmpleados == true"> 
                            <label> TIPO DE CONTRATO </label> 
                            <div isteven-multi-select 
                                input-model="listadoTipoContrato" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.tipoContratosSeleccionadas" button-label="icon name" item-label="icon name maker"tick-property="ticked">
                            </div>
                            <!-- <p class="help-block">Seleccione las tipos de contrato que desea agregar</p> -->
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='RH-CDD'"> 
                            <label> DEPARTAMENTO </label> 
                            <div class="input-group">
                                <span class="input-group-btn ">
                                    <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fBusqueda.iddepartamento" placeholder="ID" ng-change="obtenerDepartamentoPorCodigo(); $event.preventDefault();" min-length="2" />
                                </span>
                                <input id="fDatadepartamento" type="text" class="form-control input-sm" ng-model="fBusqueda.departamento" placeholder="Ingrese el Departamento" typeahead-loading="loadingLocationsDpto" uib-typeahead="item as item.descripcion for item in getDepartamentoAutocomplete($viewValue)" typeahead-on-select="getSelectedDepartamento($item, $model, $label)" typeahead-min-length="2" /> 
                            </div>
                            <i ng-show="loadingLocationsDpto" class="fa fa-refresh"></i>
                            <div ng-show="noResultsLD">
                              <i class="fa fa-remove"></i> No se encontró resultados 
                            </div>
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='RH-CDD'"> 
                            <label> PROVINCIA </label> 
                            <div class="input-group">
                                <span class="input-group-btn ">
                                    <input type="text" class="form-control input-sm" style="width:40px;margin-right:4px;" ng-model="fBusqueda.idprovincia" placeholder="ID" ng-change="obtenerProvinciaPorCodigo(); $event.preventDefault();" min-length="2" />
                                </span>
                                <input id="fDataprovincia" type="text" class="form-control input-sm" ng-model="fBusqueda.provincia" placeholder="Ingrese la Provincia"   typeahead-loading="loadingLocationsProv" 
                              uib-typeahead="item as item.descripcion for item in getProvinciaAutocomplete($viewValue)" typeahead-on-select="getSelectedProvincia($item, $model, $label)" typeahead-min-length="2" />
                                
                            </div>
                            <i ng-show="loadingLocationsProv" class="fa fa-refresh"></i>
                            <div ng-show="noResultsLP">
                              <i class="fa fa-remove"></i> No se encontró resultados 
                            </div>
                        </li> 
                        <li class="col-md-12 col-sm-12 form-group" 
                            ng-show="selectedReport.id=='RH-CDD' && listaDistritos.length>0"> 
                            <label> DISTRITO </label> 
                            <div isteven-multi-select orientation="horizontal" 
                                input-model="listaDistritos" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.distritosSeleccionados" button-label="icon name" item-label="icon name maker" tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione los distritos que desea agregar</p>
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" style="margin-top: -10px;margin-bottom: 14px;" 
                            ng-show="(selectedReport.id=='AS-APE' && fBusqueda.porEmpresaOSede.id == 'PE' )"> 
                            <label style="display: block;"> Empleado <small style="margin-left: 50%;">  <input type="checkbox" ng-model="fBusqueda.allEmpleados" class="" /> Todos </small>  </label> 
                            <div class="input-icon right typeahead"> 
                                <input ng-disabled="fBusqueda.allEmpleados" id="fDataAlmacenEmpleado" type="text" class="form-control input-sm mb" ng-model="fBusqueda.empleado" placeholder="Digite el empleado" typeahead-loading="loadingLocationsEmp" 
                                    uib-typeahead="item as item.descripcion for item in getEmpleadoAutocomplete($viewValue)" typeahead-min-length="2" autocomplete="off"/>
                                <i ng-show="loadingLocationsEmp" class="fa fa-refresh"></i>
                                <div ng-show="noResultsLE">
                                  <i class="fa fa-remove"></i> No se encontró resultados 
                                </div>
                            </div> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='VT-RCTD'"> 
                            
                            <label> Tipo Documento </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.tipodocumento" ng-options="item as item.descripcion for item in listaTipoDoc" multiple > </select> 
                        </li>
                        <!-- FARMACIA -->
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='FAR-MMV' || selectedReport.id=='FAR-MNV' || selectedReport.id=='FAR-MMC' ">
                            <label> Modalidad </label> 
                            <select tabindex="2" class="form-control input-sm" ng-model="fBusqueda.modalidad" 
                              ng-options="item as item.descripcion for item in listaModalidades" > </select> 
                        </li>

                        <!-- FORMULAS Y MEDICAMENTOS -->
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-if="selectedReport.id=='FAR_VT-DCF'">
                            <label> TIPO DE PRODUCTO </label> 
                            <select tabindex="2" class="form-control input-sm" ng-model="fBusqueda.modalidadTipo" 
                              ng-options="item as item.descripcion for item in listaModalidadTipo" > </select> 
                        </li>
                        <!-- GERENCIA -->
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-if="selectedReport.id=='CE-RSOL' || selectedReport.id=='CE-RSME'">
                            <label> Modalidad </label> 
                            <select tabindex="2" class="form-control input-sm" ng-model="fBusqueda.modalidadTiempo" 
                              ng-options="item as item.descripcion for item in listaModalidadTiempo" > </select> 
                        </li>
                        
                        <li class="col-md-6 col-xs-12 form-group clear" 
                            ng-if="selectedReport.id=='VT-RC'       || selectedReport.id=='VT-RCTD'     || selectedReport.id=='CE-RVD' 
                                || selectedReport.id=='AM-CPE'      || selectedReport.id=='AM-PEPM'     || selectedReport.id=='AM-LT'
                                || selectedReport.id=='AM-LA'       || selectedReport.id=='VT-DCF'      || selectedReport.id=='CE-CPE'
                                || selectedReport.id=='AS-APE'      || selectedReport.id=='AS-REMP'     || selectedReport.id=='AS-REMT'
                                || selectedReport.id=='AS-REMF'     || selectedReport.id=='AS-REMHE'    || selectedReport.id=='FAR_VT-DCF'
                                || selectedReport.id=='FAR_VT-MED'  || selectedReport.id=='FAR_RVF'     || selectedReport.id=='FAR-FVC'
                                || selectedReport.id=='FAR_VT-RC'   || selectedReport.id=='FAR_MED-M'   || selectedReport.id=='FAR_VT-CV' 
                                || selectedReport.id=='FAR-MMV'     || selectedReport.id=='FAR-MNV'     || selectedReport.id=='FAR-MMC'
                                || selectedReport.id=='FAR-LMV'     || selectedReport.id=='FAR-CPF'     || selectedReport.id=='FAR_FORM'
                                || selectedReport.id=='LOG-OC'      || selectedReport.id=='LOG-IAF'     || selectedReport.id=='LOG-TRAS'
                                || selectedReport.id=='MK-LCA'      || selectedReport.id=='PA-RP'       || selectedReport.id=='MK-RPE'
                                || selectedReport.id=='PA-RCP'      || selectedReport.id=='W-VW'        || selectedReport.id=='W-RU'
                                || selectedReport.id=='FAR-VUC'     || selectedReport.id=='FAR-VUCD'
                                || (selectedReport.id=='CE-RSOL' && fBusqueda.modalidadTiempo.id == 'dias')
                                || (selectedReport.id=='CE-RSME' && fBusqueda.modalidadTiempo.id == 'dias') "> 
                            <label> DESDE </label> 
                            <div class="input-group" > 
                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.desde" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="3" style="width: 80px;" />
                                <input tabindex="5" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeHora" style="width: 45px;" /> 
                                <input tabindex="7" type="text" class="form-control input-sm" ng-model="fBusqueda.desdeMinuto" style="width: 45px;" /> 
                            </div>
                        </li>
                        <li class="col-md-6 col-xs-12 form-group" 
                            ng-if="selectedReport.id=='VT-RC'       || selectedReport.id=='VT-RCTD'     || selectedReport.id=='CE-RVD' 
                                || selectedReport.id=='AM-CPE'      || selectedReport.id=='AM-PEPM'     || selectedReport.id=='AM-LT'
                                || selectedReport.id=='AM-LA'       || selectedReport.id=='VT-DCF'      || selectedReport.id=='CE-CPE'
                                || selectedReport.id=='AS-APE'      || selectedReport.id=='AS-REMP'     || selectedReport.id=='AS-REMT'
                                || selectedReport.id=='AS-REMF'     || selectedReport.id=='AS-REMHE'    || selectedReport.id=='FAR_VT-DCF'
                                || selectedReport.id=='FAR_VT-MED'  || selectedReport.id=='FAR_RVF'     || selectedReport.id=='FAR-FVC'
                                || selectedReport.id=='FAR_VT-RC'   || selectedReport.id=='FAR_MED-M'   || selectedReport.id=='FAR_VT-CV' 
                                || selectedReport.id=='FAR-MMV'     || selectedReport.id=='FAR-MNV'     || selectedReport.id=='FAR-MMC'
                                || selectedReport.id=='FAR-LMV'     || selectedReport.id=='FAR-CPF'     || selectedReport.id=='FAR_FORM'
                                || selectedReport.id=='LOG-OC'      || selectedReport.id=='LOG-IAF'     || selectedReport.id=='LOG-TRAS'
                                || selectedReport.id=='MK-LCA'      || selectedReport.id=='FAR-SMPA'    || selectedReport.id=='PA-RP'
                                || selectedReport.id=='MK-RPE'      || selectedReport.id=='PA-RCP'      || selectedReport.id=='W-VW'
                                || selectedReport.id=='W-RU'        || selectedReport.id=='FAR-VUC'     || selectedReport.id=='FAR-VUCD'
                                || (selectedReport.id=='CE-RSOL' && fBusqueda.modalidadTiempo.id == 'dias')
                                || (selectedReport.id=='CE-RSME' && fBusqueda.modalidadTiempo.id == 'dias') "> 
                            <label> HASTA </label> 
                            <div class="input-group" > 
                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="20" style="width: 80px;" /> 
                                <input tabindex="22" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaHora" style="width: 45px;"/> 
                                <input tabindex="24" type="text" class="form-control input-sm" ng-model="fBusqueda.hastaMinuto" style="width: 45px;"/> 
                            </div>
                        </li>
                        <li class="col-md-3 col-xs-12 form-group" 
                            ng-show="selectedReport.id=='AM-CPM' "> 
                            <label> DESDE </label> 
                            <div class="input-group" > 
                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.desde" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="3" style="width: 80px;" />
                            </div>
                        </li>
                        <li class="col-md-3 col-xs-12 form-group" 
                            ng-show="selectedReport.id=='AM-CPM'"> 
                            <label> HASTA </label> 
                            <div class="input-group" > 
                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.hasta" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="20" style="width: 80px;" /> 
                            </div>
                        </li>
                        <li class="col-md-6 col-xs-12 form-group" ng-show="selectedReport.id=='CE-REEDS' || selectedReport.id=='CE-IOM' || selectedReport.id=='FAR-CMMA'"> 
                            <label> DESDE MES </label>  
                            <div class="input-group" style="width: 100%;"> 
                                <select class="form-control input-sm mb" ng-options="item.id as item.mes for item in listaMeses" ng-model="fBusqueda.mesDesdeCbo" style="width: 50%;"> </select> 
                                <select class="form-control input-sm mb" ng-options="item.id as item.ano for item in listaAnos" ng-model="fBusqueda.anioDesdeCbo" style="width: 50%;"> </select>
                            </div>
                        </li>
                        <li class="col-md-6 col-xs-12 form-group" ng-show="selectedReport.id=='FAR-CMMA'"> 
                            <label> HASTA MES </label>  
                            <div class="input-group" style="width: 100%;"> 
                                <select class="form-control input-sm mb" ng-options="item.id as item.mes for item in listaMeses" ng-model="fBusqueda.mesHastaCbo" style="width: 50%;"> </select> 
                                <select class="form-control input-sm mb" ng-options="item.id as item.ano for item in listaAnos" ng-model="fBusqueda.anioHastaCbo" style="width: 50%;"> </select>
                            </div>
                        </li>
                        <li class="col-md-6 col-xs-12 form-group"
                            ng-if="selectedReport.id=='CE-REVD'     || selectedReport.id=='FAR-IVM' || selectedReport.id=='FAR-IMU'
                                || selectedReport.id=='CE-RSEX'     || selectedReport.id=='RH-RVC'  || selectedReport.id=='RH-PLAN' 
                                || (selectedReport.id=='CE-RSOL' && fBusqueda.modalidadTiempo.id == 'meses') 
                                || (selectedReport.id=='CE-RSME' && fBusqueda.modalidadTiempo.id == 'meses') "> 
                            <label> MES / AÑO </label>  
                            <div class="input-group" style="width: 100%;"> 
                                <select class="form-control input-sm mb" ng-options="item as item.mes for item in listaMeses" ng-model="fBusqueda.mes" style="width: 50%;"> </select> 
                                <select class="form-control input-sm mb" ng-options="item.id as item.ano for item in listaAnos" ng-model="fBusqueda.anioDesdeCbo" style="width: 50%;"> </select>
                            </div>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" style="margin-top: -10px;" 
                            ng-show="selectedReport.id=='FAR-SMPA' || selectedReport.id=='FAR-SCV' "> 
                            <label style="display: block;"> 
                                <small>  
                                    <input type="checkbox" ng-model="fBusqueda.allStocks" class="" ng-disabled="!fBusqueda.boolStock"/> Sólo productos con stock mayor a cero 
                                </small>  
                            </label> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='VT-DCP' || selectedReport.id=='VT-DC'  || selectedReport.id=='FAR_VT-DC'"> 
                            <label> Cajero(a) </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.usuario" ng-options="item as item.descripcion for item in listaUsuariosCaja" ng-change="listarTodasCajasMasterUsuarioCbo()" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='VT-DCP' || selectedReport.id=='VT-DC'  || selectedReport.id=='FAR_VT-DC'"> 
                            <label> Caja </label> 
                            <select class="form-control input-sm mb" ng-model="fBusqueda.caja" ng-options="item as item.descripcion for item in listaCajas" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='VT-DCP' || selectedReport.id=='VT-DC'  || selectedReport.id=='FAR_VT-DC'"> 
                            <label> Fecha </label> 
                            <input type="text" class="form-control input-sm" ng-model="fBusqueda.fecha" data-inputmask="'alias': 'dd-mm-yyyy'" tabindex="28" ng-change="listarTodasCajasMasterUsuarioCbo()" /> 
                        </li> 
                        <li class="col-md-12 col-sm-12 form-group" 
                            ng-show=" selectedReport.id=='AM-CPM' "> 
                            <div class="well well-sm mb-xs">
                                <h4 style="margin:0;"> TURNO MAÑANA </h4>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label style="line-height: 1"> DESDE: </label> 
                                        <div class="input-group">
                                            <span class="input-group-btn" style="font-size: 10px;"> 
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.horaDesdeManana" style="width: 45px;" /> 
                                                <label style="margin-top: 10px; display: block; font-size: 12px;"> : </label>
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.minutoDesdeManana" style="width: 45px; margin-left: 4px;margin-top: -18px;" /> 
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label style="line-height: 1"> HASTA: </label> 
                                        <div class="input-group">
                                            <span class="input-group-btn" style="font-size: 10px;"> 
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.horaHastaManana" style="width: 45px;" /> 
                                                <label style="margin-top: 10px; display: block; font-size: 12px;"> : </label>
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.minutoHastaManana" style="width: 45px; margin-left: 4px;margin-top: -18px;" /> 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="well well-sm mt-md mb-xs">
                                <h4 style="margin:0;"> TURNO TARDE </h4>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label style="line-height: 1"> DESDE: </label> 
                                        <div class="input-group">
                                            <span class="input-group-btn" style="font-size: 10px;"> 
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.horaDesdeTarde" style="width: 45px;" /> 
                                                <label style="margin-top: 10px; display: block; font-size: 12px;"> : </label>
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.minutoDesdeTarde" style="width: 45px; margin-left: 4px;margin-top: -18px;" /> 
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label style="line-height: 1"> HASTA: </label> 
                                        <div class="input-group">
                                            <span class="input-group-btn" style="font-size: 10px;"> 
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.horaHastaTarde" style="width: 45px;" /> 
                                                <label style="margin-top: 10px; display: block; font-size: 12px;"> : </label>
                                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.minutoHastaTarde" style="width: 45px; margin-left: 4px;margin-top: -18px;" /> 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show=" selectedReport.id=='AM-FAM' "> 
                            <label> N° Acto Médico </label> 
                            <div class="input-group">
                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.num_acto_medico" tabindex="30"/> 
                                <span class="input-group-btn">
                                    <button class="btn btn-default input-sm" type="button" style="font-size: 11px;" >CONSULTAR</button>
                                </span>
                            </div>
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AM-PEPM'"> 
                            <label> EMPRESA/ESPECIALIDAD </label> 
                            <select tabindex="40" class="form-control input-sm" ng-model="fBusqueda.empresaespecialidad" ng-change="getListaMedicos();" 
                              ng-options="item as item.descripcion for item in listaEmpresaEspecialidades" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AM-LT'"> 
                            <label> ESPECIALIDAD - EMPRESA</label> 
                            <select tabindex="40" class="form-control input-sm" ng-model="fBusqueda.empresaespecialidad" 
                              ng-options="item as item.descripcion for item in listaEmpEspecialidadPorEmpresa" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AM-CPM' || selectedReport.id=='AM-CPE' || selectedReport.id=='CE-CPE' || selectedReport.id=='CE-REES' 
                                || selectedReport.id=='CE-REEDS' || selectedReport.id=='CE-IOM' || selectedReport.id=='CE-CNC' || selectedReport.id=='CE-RSME'
                                || selectedReport.id=='MK-RPE'   || selectedReport.id=='PA-RCP' || (selectedReport.id=='CE-PCE' && fBusqueda.tipoCuadro == 'grafico')
                                || (selectedReport.id=='CE-RSOL' && fBusqueda.mostrarTodasSolicitudes == false )"> 
                            <label> ESPECIALIDAD </label> 
                            <select tabindex="40" class="form-control input-sm mb" ng-model="fBusqueda.especialidad" ng-change="getListaMedicosSoloEsp();" 
                              ng-options="item as item.descripcion for item in listaEspecialidades" > </select> 
                        </li>
                        
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AM-CPM' || selectedReport.id=='AM-PEPM' || ( selectedReport.id=='CE-RSOL' && fBusqueda.mostrarTodasSolicitudes == false ) 
                                  || selectedReport.id=='CE-IOM' || selectedReport.id=='CE-RSME' || selectedReport.id=='PA-RCP'"> 
                            <label> MEDICO </label> 
                            <select tabindex="50" class="form-control input-sm mb" ng-model="fBusqueda.medico" 
                              ng-options="item as item.medico for item in listaMedicos" > </select> 
                        </li> 

                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-RSME'"> 
                            <label> ESPECIALIDAD  DE SOLICITUD</label> 
                            <select tabindex="40" class="form-control input-sm mb" ng-model="fBusqueda.especialidadSolicitud" 
                              ng-options="item as item.descripcion for item in listaEspecialidadSolicitud" > </select> 
                        </li>
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='CE-IOM'">
                            <label> PRODUCTO <small>(con indicadores)</small> </label> 
                            <div isteven-multi-select 
                                input-model="listadoProductos" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.productosSeleccionados" button-label="icon name" item-label="icon name maker" tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione los productos que desea agregar</p>
                        </li>
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='RH-CPO'">
                            <label> PROFESIÓN </label> 
                            <div isteven-multi-select 
                                input-model="listadoProfesiones" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.profesionesSeleccionadas" button-label="icon name" item-label="icon name maker"tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione las profesiones que desea agregar</p>
                        </li>
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='RH-MER'">
                            <label> ESPECIALIDAD </label> 
                            <div isteven-multi-select 
                                input-model="listadoEspecialidades" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.especialidadesSeleccionadas" button-label="icon name" item-label="icon name maker"tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione las especialidades que desea agregar</p>
                        </li>
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='RH-CEP'">
                            <label> EMPRESA </label> 
                            <div isteven-multi-select 
                                input-model="listadoEmpresas" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.empresasSeleccionadas" button-label="icon name" item-label="icon name maker"tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione las empresas que desea agregar</p>
                        </li>
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='RH-CTC'"> 
                            <label> TIPO DE CONTRATO </label> 
                            <div isteven-multi-select 
                                input-model="listadoTipoContrato" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.tipoContratosSeleccionadas" button-label="icon name" item-label="icon name maker"tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione las tipos de contrato que desea agregar</p>
                        </li>
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='RH-AEHT'">
                            <label> RANGO DE EDAD </label> 
                            <div isteven-multi-select 
                                input-model="listadoRangoEdad" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.rangoEdadSeleccionadas" button-label="icon name" item-label="icon name maker"tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione las tipos de contrato que desea agregar</p>
                        </li>
                        <!-- FARMACIA -->
                        <li class="col-md-12 col-sm-12 form-group" ng-show="selectedReport.id=='FAR_VT-CV' || selectedReport.id=='FAR-SCV'"> 
                            <label> CONDICIÓN DE VENTA </label> 
                            <div isteven-multi-select 
                                input-model="listadoCondicionVenta" helper-elements="filter none all" translation="$parent.localLang" output-model="fBusqueda.condicionVentaSeleccionadas" button-label="icon name" item-label="icon name maker" tick-property="ticked">
                            </div>
                            <p class="help-block">Seleccione las Condiciones de Venta que desea agregar</p>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='AM-PEPM'"> 
                            <label> TIPO ATENCION </label> 
                            <select tabindex="60" class="form-control input-sm" ng-model="fBusqueda.idTipoAtencion" 
                                ng-options="item.id as item.descripcion for item in listaTipoAtencionMedica" > </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-PCE'"> 
                            <label> TIPO ATENCION </label> 
                            <select tabindex="60" class="form-control input-sm" ng-model="fBusqueda.idTipoAtencion" 
                                ng-options="item.id as item.descripcion for item in listaTipoAtencion" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-PCE' || selectedReport.id=='CE-CNC'"> 
                            <label> TIPO RANGO</label> 
                            <select tabindex="60" class="form-control input-sm" ng-model="fBusqueda.idTipoRango" ng-change="changeToGraphic();"
                                ng-options="item.id as item.descripcion for item in listaFiltroTipoRango" > </select>
                        </li>
                        <!-- <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='CE-PCE' && fBusqueda.idTipoRango == '1'"> 
                            <label> AÑO </label> 
                            <div class="input-group">
                                <span class="input-group-btn" style="font-size: 10px;"> 
                                    <input type="text" class="form-control input-sm text-center" ng-model="fBusqueda.anio" style="width: 50px;" />  
                                </span>
                            </div>
                        </li>  -->
                        <!-- <li class="col-md-6 col-sm-6 form-group" ng-show="(selectedReport.id=='CE-PCE' || selectedReport.id=='CE-CNC') && fBusqueda.idTipoRango == '2' "> 
                            <label > RANGO DE AÑOS </label> 
                            <div class="input-group" >
                                <span class="input-group-btn" style="font-size: 10px;"> 
                                    <input type="text" class="form-control input-sm text-center" ng-model="fBusqueda.anioDesde" style="width: 50px;" /> 
                                    <input type="text" class="form-control input-sm text-center" ng-model="fBusqueda.anioHasta" style="width: 50px;" /> 
                                </span>
                            </div>
                        </li>  -->
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="((selectedReport.id=='CE-REV'  || selectedReport.id=='CE-REPR' || selectedReport.id=='CE-REES'
                                || selectedReport.id=='CE-RETP'     || selectedReport.id=='CE-CDU' 
                                || selectedReport.id=='FAR-VM')     && (contRangoAnos))
                                || ((selectedReport.id=='CE-PCE' || selectedReport.id=='CE-CNC') && fBusqueda.idTipoRango == '2') "> 
                            <label> RANGO DE AÑOS </label> 
                            <div class="input-group">
                                <span class="input-group-btn" style="font-size: 10px;"> 
                                    <input type="text" class="form-control input-sm text-center" ng-model="fBusqueda.anioDesde" style="width: 50px;" /> 
                                    <input type="text" class="form-control input-sm text-center" ng-model="fBusqueda.anioHasta" style="width: 50px;" /> 
                                </span>
                            </div>
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="((selectedReport.id=='CE-REV' || selectedReport.id=='CE-REPR' || selectedReport.id=='CE-REES'
                            || selectedReport.id=='CE-RETP' ||  selectedReport.id=='FAR-VM')   && !(contRangoAnos))
                            || ((selectedReport.id=='CE-PCE' || selectedReport.id=='CE-CNC') && fBusqueda.idTipoRango == '1')"> 
                            <label> AÑO </label> 
                            <!-- <div class="input-group"> -->
                                <!-- <span class="input-group-btn" style="font-size: 10px;">  -->
                                <input type="text" class="form-control input-sm" ng-model="fBusqueda.anio" style="width: 100px;" /> 
                                    <!-- <input type="text" class="form-control input-sm" ng-model="fBusqueda.anio" style="width: 100px;" /> 
                                    
                                </span> -->
                            <!-- </div> -->
                        </li>
                        <li class="col-md-6 col-xs-12 form-group" ng-show="selectedReport.id=='VT-CIE' || selectedReport.id=='FAR-REVD'"> 
                            <label> AÑO </label>  
                            <div class="input-group" style="width: 100%;"> 
                                
                                <select class="form-control input-sm mb" ng-options="item.id as item.ano for item in listaAnos" ng-model="fBusqueda.anioDesdeCbo" > </select>
                            </div>
                        </li>
                        
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='FAR-REVD'"> 
                            <label> MES </label> 
                            <select ng-model="fBusqueda.mes" class="form-control input-sm" ng-options="item as item.mes for item in listaMeses">  </select>
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-REES'"> 
                            <label> TIPO REPORTE </label> 
                            <select ng-model="fBusqueda.ventaPrestacion" class="form-control input-sm" ng-options="item.id as item.descripcion for item in listaTipoReporte">  </select>
                        </li> 
                        <li class="col-xs-12 form-group" 
                            ng-show="selectedReport.id=='CE-CNC'"> 
                            <label> PACIENTES NUEVOS/CONTINUADORES </label> 
                            <select ng-disabled="fBusqueda.pacienteNCDisabled && fBusqueda.idTipoRango == '1'" ng-model="fBusqueda.pacienteNC" class="form-control input-sm" ng-options="item as item.descripcion disable when item.isDisabled for item in listaPacientesNC" >  </select>
                        </li> 
                        <li class="col-xs-12 form-group" 
                            ng-show="selectedReport.id=='CE-CNC'"> 
                            <label> LÓGICA DE PACIENTES NUEVOS </label> 
                            <select ng-model="fBusqueda.logicaPacienteNC" class="form-control input-sm" ng-options="item as item.descripcion for item in listaLogicaPacientesNC">  </select> 
                            <small class="help-block m-n" style="line-height: 1;"> Sólo aplica desde Enero - 2016 </small>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-REV' || selectedReport.id=='CE-REVD' || selectedReport.id=='CE-REPR' || selectedReport.id=='CE-REES'  || selectedReport.id=='FAR-REVD'
                            || selectedReport.id=='CE-RETP'  || selectedReport.id=='CE-CNC' || selectedReport.id=='CE-CDU' || selectedReport.id=='FAR-VM' || selectedReport.id=='CE-PCE'" > 
                            <label> TIPO DE CUADRO </label> 
                            <div class="input-group">
                                <input type="radio" ng-model="fBusqueda.tipoCuadro" value="reporte" checked ng-change="changeToGraphic();"/> REPORTE 
                                <input type="radio" ng-model="fBusqueda.tipoCuadro" value="grafico" ng-change="changeToGraphic();"/> GRAFICO 
                            </div>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='FAR-MMV' || selectedReport.id=='FAR-MNV' || selectedReport.id=='FAR-MMC' || selectedReport.id=='FAR-LMV' || selectedReport.id=='FAR-CPF' "> 
                            <label> Top </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.top" 
                              ng-options="item as item.descripcion for item in listaTops" > </select> 
                        </li>
                        <!-- REPORTE ENCUESTA DE TABLETS-->
                        <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='CE-PRPT' || selectedReport.id=='CE-ERT'" > <label> Desde </label>
                            <div class="input-group" style="width: 230px;">
                            <input type="text" class="form-control input-sm datepicker" uib-datepicker-popup="{{dateUIDesde.format}}" ng-model="fBusqueda.desdeEncuesta" is-open="dateUIDesde.opened" datepicker-options="dateUIDesde.datePikerOptions" ng-required="true" close-text="Close" alt-input-formats="altInputFormats" />
                            <span class="input-group-btn">
                              <button type="button" class="btn btn-default btn-sm" ng-click="dateUIDesde.openDP($event)"><i class="ti ti-calendar"></i></button>
                            </span>
                          </div>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='CE-PRPT' || selectedReport.id=='CE-ERT'" > <label> Hasta </label>
                          <div class="input-group" style="width: 230px;"> 
                            <input type="text" class="form-control input-sm datepicker" datepicker-popup="{{dateUIHasta.format}}" ng-model="fBusqueda.hastaEncuesta" 
                              is-open="dateUIHasta.opened" datepicker-options="dateUIHasta.datePikerOptions" close-text="Close" placeholder="Hasta" tabindex="3" />
                            <div class="input-group-btn">
                              <button type="button" class="btn btn-default btn-sm" ng-click="dateUIHasta.openDP($event)" tabindex="4"><i class="ti ti-calendar"></i></button>
                            </div>
                          </div>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-ERT'">
                            <label> PREGUNTA </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.pregunta" 
                              ng-options="item as item.descripcion for item in listaPreguntas" > </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='CE-PRPT' || selectedReport.id=='CE-ERT'">
                            <label> N° TABLET </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.tablet" 
                              ng-options="item as item.descripcion for item in listaTablets" > </select> 
                        </li>                        
                        <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='CE-PRPTTT'"><!-- ng-show="selectedReport.id=='CE-ERT'" -->
                            <label> AGRUPAR POR </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.agrupar" 
                              ng-options="item as item.descripcion for item in listaAgrupar" > </select> 
                        </li>
                        <!-- FIN DEL FILTRO ENCUESTA DE TABLETS -->
                        <li class="col-md-6 col-sm-6 form-group mt-md" ng-show="selectedReport.id=='FAR-MMV'">  
                            <label style="display: block;"> 
                                <small> 
                                    <input type="checkbox" ng-model="fBusqueda.formula_derma" checked="true"/> Excluir formulas dermatológicas 
                                </small> 
                            </label> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" 
                            ng-show="selectedReport.id=='VT-DC'  || selectedReport.id=='FAR_VT-DC'  || selectedReport.id=='VT-DCF'
                                || selectedReport.id=='VT-CIE'   || selectedReport.id=='FAR_VT-CV'  || selectedReport.id=='AM-LA'
                                || selectedReport.id=='AM-CPE'   || selectedReport.id=='CE-RVD'     || selectedReport.id=='CE-CPE' 
                                || selectedReport.id=='AS-APE'   || selectedReport.id=='AS-REMP'    || selectedReport.id=='AS-REMT'
                                || selectedReport.id=='AS-REMF'  || selectedReport.id=='AS-REMHE'   || selectedReport.id=='RH-MER'
                                || selectedReport.id=='CE-IOM'   || selectedReport.id=='FAR_VT-DCF' || selectedReport.id=='FAR_VT-MED'  
                                || selectedReport.id=='FAR_VT-RC'|| selectedReport.id=='FAR_MED-M'  || selectedReport.id=='CE-RSOL'
                                || selectedReport.id=='FAR_FORM' || selectedReport.id=='FAR_RVF'    || selectedReport.id=='FAR-MMV'
                                || selectedReport.id=='FAR-MNV'  || selectedReport.id=='FAR-MMC'    || selectedReport.id=='FAR-LMV'
                                || selectedReport.id=='FAR-CPF'  || selectedReport.id=='FAR-SMPA'   || selectedReport.id=='FAR-TARIF'
                                || selectedReport.id=='FAR-SCV'  || selectedReport.id=='LOG-OC'     || selectedReport.id=='LOG-IAF' 
                                || selectedReport.id=='LOG-TRAS' || selectedReport.id=='HOS-TARIF'  || selectedReport.id=='FAR-FVC' 
                                || selectedReport.id=='CE-RSEX'  || selectedReport.id=='AM-CPM'     || selectedReport.id=='RH-RVC'
                                || selectedReport.id=='CE-RSME'  || selectedReport.id=='PA-RP'      || selectedReport.id=='MK-RPE'
                                || selectedReport.id=='W-VW'"> 

                            <label> SALIDA </label> 
                            <div class="input-group">
                                <input type="radio" ng-model="fBusqueda.salida" value="pdf" checked /> PDF 
                                <input type="radio" ng-model="fBusqueda.salida" value="excel"  /> EXCEL 
                            </div>
                        </li>
                        
                        <li class="col-md-12 col-sm-12 form-group" 
                            ng-show="selectedReport.id=='CE-REEDS' || selectedReport.id=='CE-PRPT' || selectedReport.id=='CE-ERT'
                                || selectedReport.id=='PA-RCP'"> 
                            <label> TIPO SALIDA </label> 
                            <div class="input-group">
                                <input type="radio" ng-model="fBusqueda.tiposalida" value="pdf" checked /> PDF 
                                <input type="radio" ng-model="fBusqueda.tiposalida" value="excel" /> EXCEL 
                                <input type="radio" ng-model="fBusqueda.tiposalida" value="grafico"  /> GRAFICO 
                            </div>
                        </li>
                        <li class="col-xs-12 form-group" 
                            ng-show="selectedReport.id=='PA-RCP' && fBusqueda.tiposalida=='grafico'"> 
                            <label> LÓGICA DE PACIENTES NUEVOS </label> 
                            <select ng-model="fBusqueda.logicaGraficoCumplProg" class="form-control input-sm" ng-options="item as item.descripcion for item in listaLogicaGraficoCumplProg">  </select> 
                        </li>
                       <li class="col-md-12 col-sm-12 form-group" 
                        ng-show="selectedReport.id=='FAR-IVM' || selectedReport.id=='FAR-IMU'"> 
                            <label> MEDICAMENTOS </label> 
                             <div id="gridMedicamentos" ui-grid="gridOptions" ui-grid-pagination ui-grid-selection ui-grid-auto-resize class="grid table-responsive fs-mini-grid scroll-x-none"></div>
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" style="margin-top: -10px;"
                            ng-show=" selectedReport.id=='AS-APE'  || selectedReport.id=='RH-RVC' "> 
                            <label style="display: block;"> 
                                <small> 
                                    <input type="checkbox" ng-model="fBusqueda.soloEmpActivos" checked="true" /> Solo empleados activos 
                                </small> 
                            </label> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" ng-show=" selectedReport.id=='CE-RSOL' "> 
                            <label style="display: block;"> 
                                <small> 
                                    <input type="checkbox" ng-model="fBusqueda.mostrarTodasSolicitudes" checked="true" /> Mostrar todas las solicitudes 
                                </small> 
                            </label> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='FAR-CMMA'">
                            <label> TIPO PRODUCTO </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.tipoProducto" 
                              ng-options="item as item.descripcion for item in listaBusquedaTipoProductos" > </select> 
                        </li>
                        <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='FAR-CMMA' ">
                            <label> LABORATORIO </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.laboratorio" 
                              ng-options="item as item.descripcion for item in listaLaboratorio" > </select> 
                        </li> 
                        <li class="col-md-6 col-sm-6 form-group" ng-show="selectedReport.id=='FAR-VUC' || selectedReport.id=='FAR-VUCD' ">
                            <label> TURNO </label> 
                            <select tabindex="50" class="form-control input-sm" ng-model="fBusqueda.turno" 
                              ng-options="item as item.descripcion for item in listaTurno" > </select> 
                        </li> 
                    </ul> 
                     <!-- GRILLA DE MEDICAMENTOS -->
                    
                    <div class="row" style="width: 100%; " ng-show="selectedReport.id"> 
                        <div class="col-md-12 col-sm-12 col-xs-12" style="border: 1px solid #f1f3f4;margin-left: 16px;"> 
                            <button type="button" class="btn btn-primary pull-right m" ng-click="btnConsultarReporte();"> <i class="fa fa-refresh"></i> PROCESAR </button> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- container-fluid --> 
