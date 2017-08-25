<ol class="breadcrumb m-n">
    <li><a href="#/">Inicio</a></li>
</ol>
<div class="container-fluid" ng-controller="inicioController">
    <div class="row">
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="panel panel-danger" data-widget='{"id" : "wiget5"}'>
                        <div class="panel-heading">
                            <div class="panel-ctrls button-icon" 
                                data-actions-container="" 
                                data-action-collapse='{"target": ".panel-body"}'
                                data-action-colorpicker=''>
                            </div>
                            <h2>AVISOS IMPORTANTES </h2>
                        </div>
                        <div class="panel-editbox" data-widget-controls=""></div>
                        <div class="panel-body" > 
                            <div class="block" style="overflow:auto;max-height: 380px;"> 
                                <div ng-repeat="fila in arrays.listaAvisos" class="info-tile ml-xs mr-sm">
                                    <div class="tile-heading mb" style="font-size: 18px;color: #263238;"><span>{{fila.titulo}}</span> <span style="float: right;"> {{fila.fecha_creacion}} </span> </div>
                                    <p class="tile-body" style="font-size: 15px; color: #989898;line-height: 1.2; text-align: left;" ng-bind-html="fila.redaccion"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12"> 
                    <div class="panel panel-danger" data-widget='{"id" : "wiget5"}'>
                        <div class="panel-heading">
                            <div class="panel-ctrls button-icon" 
                                data-actions-container="" 
                                data-action-collapse='{"target": ".panel-body"}'
                                data-action-colorpicker=''>
                            </div>
                            <h2> DOCUMENTOS INTERNOS </h2>
                        </div>
                        <div class="panel-editbox" data-widget-controls=""></div>
                        <div class="panel-body" > 
                            <div class="block" style="overflow:auto;max-height: 465px;"> 
                                <div ng-repeat="fila in arrays.listaDocumentosInterno" class="info-tile ml-xs mr-sm">
                                    <div class="tile-heading mb" style="font-size: 18px;color: #263238;"><span class="text-primary">{{fila.nombre_documento}}</span> <span style="float: right;"> {{fila.fecha_subida}} </span> </div>
                                    <div class="tile-body" style="font-size: 15px; color: #989898;line-height: 1.2;">
                                       <a style="display: block; margin-top: -40px;" target="_blank" href="{{ dirImages + 'dinamic/documentosInternos/' + fila.archivo.documento }} "> 
                                            DESCARGAR AQUI.
                                            <img style="height: 32px;" ng-src="{{ dirImages + 'formato-imagen/' + fila.archivo.icono }}" alt=" {{fila.nombre_documento}} " />
                                       </a>
                                       <small class="block" style="text-transform: uppercase; font-size: 10px; color:#b5b5c2;margin-bottom: -11px;margin-top: 8px;"> Subido por: <b>{{ fila.empleado }}</b> </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-12"> 
                    <div class="panel panel-danger" data-widget='{"id" : "wiget5"}'>
                        <div class="panel-heading">
                            <div class="panel-ctrls button-icon" 
                                data-actions-container="" 
                                data-action-collapse='{"target": ".panel-body"}'
                                data-action-colorpicker=''>
                            </div>
                            <h2>CUMPLEAÑOS DEL MES DE {{fDataFiltro.mesCbo.mes}}</h2>
                        </div>
                        <div class="panel-editbox" data-widget-controls=""></div>
                        <div class="panel-body" >
                            <div style="max-width:100%;"> 
                                <select class="form-control input-sm mb" ng-options="item as item.mes for item in listaMeses" ng-model="fDataFiltro.mesCbo" ng-change="listarCumpleanos();"> </select>
                            </div>
                            <div class="block" style="overflow: auto; max-height: 380px;">
                                
                                <div ng-repeat="fila in arrays.listaCumpleaneros" style="{{ fila.estilo }}" class="info-tile tile-indigo m-xs mb-md pt-sm {{ fila.clase }} ">
                                    <div class="tile-icon foto"> <img style="height: 70px;" ng-src="{{ dirImages + 'dinamic/empleado/' + fila.nombre_foto }}" alt="{{ fila.empleado }}" /> </div>
                                    <div class="tile-heading">
                                        <span style="font-size: 18px;color:white;">{{fila.empleado}}</span>
                                    </div> 
                                    <div class="tile-body"><span style="color:white;">{{ fila.fecha_cumpleanos }}</span></div>
                                    <div class="tile-footer" style="font-size: 16px;font-weight: bold;"><span class="text-success"> {{ fila.cargo }} <i class="fa fa-level-up"></i></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12"> 
                    <div class="panel panel-danger" data-widget='{"id" : "wiget5"}'>
                        <div class="panel-heading">
                            <div class="panel-ctrls button-icon" 
                                data-actions-container="" 
                                data-action-collapse='{"target": ".panel-body"}'
                                data-action-colorpicker=''>
                            </div>
                            <h2>DIRECTORIO TELEFÓNICO</h2>
                        </div>
                        <div class="panel-editbox" data-widget-controls="">
                            
                        </div>
                        <div class="panel-body"> 
                            <input class="form-control mb" placeholder="Digite Empleado o Teléfono" ng-model="fBusqueda.empleado_celular" focus-me style="width: 100%;" />
                            <div class="block" style="overflow: auto; max-height: 420px;">
                                <div class="info-tile tile-info m-xs p-md" ng-repeat="filaDet in arrays.listaTelefonica | filter:fBusqueda"> 
                                    <div class="tile-icon foto" style="height: 42px;"> <img style="height: 46px;" ng-src="{{ dirImages + 'dinamic/empleado/' + filaDet.nombre_foto }}" alt="{{ filaDet.empleado }}" /> </div>
                                    <div class="tile-heading" style="font-size: 18px;"><span>{{filaDet.empleado}}</span></div>
                                    <div class="tile-body mb" style="font-size: 26px; line-height: 40px;"><span>{{ filaDet.telefono }}</span></div>
                                    <div class="tile-footer" style="font-size: 16px;font-weight: bold;"><span class="text-success"> {{ filaDet.cargo }} <i class="fa fa-level-up"></i></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>