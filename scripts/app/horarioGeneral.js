angular.module('theme.horarioGeneral', ['theme.core.services'])
  .controller('horarioGeneralController', ['$scope', '$sce', '$filter', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications',
     'empleadoServices',
     'horarioGeneralServices',
     'horarioServices',
     'asistenciaServices',
     'ModalReporteFactory',
    function($scope, $sce, $filter, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
      empleadoServices,
      horarioGeneralServices,
      horarioServices,
      asistenciaServices,
      ModalReporteFactory
    ) { 
    // 'use strict';
    shortcut.remove("F2"); $scope.modulo = 'horarioGeneral'; 
    $scope.fBusqueda = {};
    $scope.fBusquedaMC = {};
    $scope.fDataMC = {};
    $scope.fData = {};
    $scope.fData.temporal = {}; 
    $scope.fData.temporal.horario = null;
    /* LISTA DE HORARIOS */
    var arrParamsH = {};
    horarioServices.sListarHorariosCbo(arrParamsH).then(function (rpta) {
      $scope.listaHorarios = rpta.datos; 
    });
    $scope.listaTercero = [
      { id: 'all', descripcion: 'TODOS' },
      { id: 1, descripcion: 'PERSONAL - TERCEROS' },
      { id: 2, descripcion: 'PERSONAL - PROPIOS' }
    ];
    $scope.fBusqueda.tercero = 2;
    var paginationOptions = { 
      pageNumber: 1,
      firstRow: 0,
      pageSize: 50,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGridEM = [];
    $scope.mySelectionGridHG = [];
    $scope.mySelectionGridMC = [];
    /* GRILLA DE EMPLEADOS */ 
    $scope.dirImagesEmpleados = $scope.dirImages + "dinamic/empleado/";
    $scope.gridOptionsEmpleado = {
      // rowHeight: 48,
      minRowsToShow: 8,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 50,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableSelectAll: false,
      enableFiltering: true,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'num_documento', name: 'e.numero_documento', displayName: 'N° DOC.',width: 100, visible: false },
        { field: 'personal', name: "CONCAT(e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno)", displayName: 'PERSONAL',width: 250, sort: { direction: uiGridConstants.DESC } }, 
        { field: 'nombre_foto', name: 'nombre_foto', displayName: 'FOTO',width: 60, enableFiltering: false, enableSorting: false, 
          cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpleados + COL_FIELD }}" /> </div>' },
        { field: 'cargo', name: 'c.descripcion_ca', displayName: 'CARGO',width: 180 },
        { field: 'empresa', name: 'empresa', displayName: 'EMPRESA',width: 180 },
        { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD',width: 160 }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridEM = gridApi.selection.getSelectedRows(); 
          $scope.getPaginationServerSideHG();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideEM();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSideEM();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = { 
            //'idempleado' : grid.columns[1].filters[0].term,
            'e.numero_documento' : grid.columns[1].filters[0].term,
            "CONCAT(e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno)" : grid.columns[2].filters[0].term,
            'c.descripcion_ca' : grid.columns[4].filters[0].term,
            'em.descripcion' : grid.columns[5].filters[0].term,
            'esp.nombre' : grid.columns[6].filters[0].term
          }; 
          $scope.getPaginationServerSideEM();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptionsEmpleado.columnDefs[0].name;
    $scope.getPaginationServerSideEM = function() { 
      $scope.fBusqueda.modulo = 'asist';
      var arrParams = { 
        paginate : paginationOptions,
        datos: $scope.fBusqueda
      };
      empleadoServices.sListarEmpleados(arrParams).then(function (rpta) {
        $scope.gridOptionsEmpleado.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsEmpleado.data = rpta.datos;
      });
      $scope.mySelectionGridEM = [];
      $scope.gridOptionsHorario.data = [];
    };
    

    /* GRILLA DE HORARIOS */ 
    var paginationOptionsHG = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 50,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsHorario = { 
      minRowsToShow: 9,
      paginationPageSizes: [10, 50],
      paginationPageSize: 50,
      enableSelectAll: true,
      enableFullRowSelection: true,
      multiSelect: true,
      data: [],
      columnDefs: [
        { field: 'id', name: 'idhorarioempleado', displayName: 'ID',width: 40 },
        { field: 'horario', name: 'descripcion', displayName: 'HORARIO',width: 100 },
        { field: 'entrada', name: "hora_entrada", displayName: 'ENTRADA',width: 200, type:'object'
          ,cellTemplate:'<div class="ui-grid-cell-contents" > <span class="help-inline pr-sm m-n" style="font-size:11px;"> {{COL_FIELD.desde_entrada}} >> </span> {{COL_FIELD.entrada}} <span class="help-inline pr-sm m-n" style="font-size:11px;"> << {{COL_FIELD.hasta_entrada}} </span> </div>' },
        { field: 'salida', name: "hora_salida", displayName: 'SALIDA',width: 200, type:'object'
          ,cellTemplate:'<div class="ui-grid-cell-contents" > <span class="help-inline pl-sm m-n" style="font-size:11px;"> {{COL_FIELD.desde_salida}} >> </span> {{COL_FIELD.salida}} <span class="help-inline pl-sm m-n" style="font-size:11px;"> << {{COL_FIELD.hasta_salida}} </span> </div>' },
        { field: 'tiempo_tolerancia', name: "tiempo_tolerancia", displayName: 'TOLERANCIA',width: 110 },
        { field: 'horas_trabajadas', name: "horas_trabajadas", displayName: 'HORAS TRAB.',width: 100 }
      ],
      onRegisterApi: function(gridApiHG) {
        $scope.gridApiHG = gridApiHG;
        gridApiHG.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridHG = gridApiHG.selection.getSelectedRows();
        });
        gridApiHG.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridHG = gridApiHG.selection.getSelectedRows();
        });
      }
    };
    $scope.getPaginationServerSideHG = function () { 
      if( $scope.mySelectionGridEM.length > 0 ){ 
        var arrParams = { 
          datos: $scope.mySelectionGridEM[0]
        };
        horarioGeneralServices.sListarHorarioGeneralEmpleado(arrParams).then(function (rpta) {
          $scope.gridOptionsHorario.data = rpta.datos; 
          
        });
        $scope.mySelectionGridHG = [];
      }else{
        $scope.gridOptionsHorario.data = [];
      }
    }
    $scope.nextControlForm = function (lengthString) { 
      var lengthString = lengthString || 1;
      var myElement = document.activeElement;
      if( $(myElement).val().length >= lengthString ){ 
        var allControls = $('#contNextControl').find('input, textarea, select');
        var index = allControls.index(myElement);
        if(index > -1 &&(index+1) < (allControls.length - 1)){
          allControls.eq(index+1).focus();
          allControls.eq(index+1).select();
        }
      }
    }
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnNuevo = function () { 
      $modal.open({
        templateUrl: angular.patchURLCI+'HorarioGeneral/ver_popup_formulario',
        size: 'lg',
        //backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'CREACION DEL HORARIO'; 
          $scope.tieneHorarios = false;
          //console.log( moment().format() );
          //$scope.fData.hora_desde_entrada = moment().format('HH');

          $scope.gridOptionsHorarioAdd = {
            //paginationPageSizes: [10, 50],
            minRowsToShow: 7,
            paginationPageSize: 50,
            enableSelectAll: false,
            //enableFullRowSelection: true,
            multiSelect: false,
            data: [],
            columnDefs: [ 
              { field: 'horario', name: 'descripcion', displayName: 'HORARIO',width: 140 },
              { field: 'entrada', name: "hora_entrada", displayName: 'ENTRADA',width: 206, type:'object',
                cellTemplate:'<div class="ui-grid-cell-contents" > <span class="help-inline pr-sm m-n" style="font-size:11px;"> {{COL_FIELD.desde_entrada}} >> </span> {{COL_FIELD.entrada}} <span class="help-inline pr-sm m-n" style="font-size:11px;"> << {{COL_FIELD.hasta_entrada}} </span> </div>' },
              { field: 'salida', name: "hora_salida", displayName: 'SALIDA',width: 206, type:'object',
                cellTemplate:'<div class="ui-grid-cell-contents" > <span class="help-inline pl-sm m-n" style="font-size:11px;"> {{COL_FIELD.desde_salida}} >> </span> {{COL_FIELD.salida}} <span class="help-inline pl-sm m-n" style="font-size:11px;"> << {{COL_FIELD.hasta_salida}} </span> </div>' },
              { field: 'horas_trabajadas', name: "horas_trabajadas", displayName: 'HORAS TRAB.',width: 110 },
              { field: 'tiempo_tolerancia', name: "tiempo_tolerancia", displayName: 'TOLERANCIA',width: 110 },
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApiHG) {
              $scope.gridApiHG = gridApiHG; 
            }
          };
          $scope.gridOptionsHorarioAdd.data = $scope.gridOptionsHorario.data; 

          if($scope.gridOptionsHorario.data.length > 0){
            $scope.tieneHorarios = true;
          }
          $scope.agregarHorarioItem = function () { 
            if( !angular.isObject($scope.fData.temporal.horario) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el horario', type: 'warning', delay: 2500 }); 
              return false; 
            }
            if( $scope.fData.temporal.hora_desde_entrada.length < 1 ){
            //if( !($scope.fData.temporal.hora_desde_entrada)  ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: DESDE ENTRADA', type: 'warning', delay: 2500 });
              return false;
            }
            if( ($scope.fData.temporal.hora_entrada.length < 1 || $scope.fData.temporal.minuto_entrada.length < 1) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: ENTRADA', type: 'warning', delay: 2500 });
              return false;
            }
            if( $scope.fData.temporal.hora_hasta_salida.length < 1 ){ 
            // if( !($scope.fData.temporal.hora_hasta_salida) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: HASTA SALIDA', type: 'warning', delay: 2500 });
              return false;
            }
            if( $scope.fData.temporal.hora_salida.length < 1 || $scope.fData.temporal.minuto_salida.length < 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: SALIDA', type: 'warning', delay: 2500 });
              return false;
            }
            if( !($scope.fData.temporal.minuto_tolerancia) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: TOLERANCIA', type: 'warning', delay: 2500 });
              return false;
            }
            $('#temporalHorario').focus();
            angular.forEach($scope.fData.temporal.horario,function (value,key) {
              $scope.arrTemporal = { 
                'idhorario' : value.idhorario,
                'horario' : value.descripcion,
                'entrada' : {
                  'desde_entrada': $scope.fData.temporal.hora_desde_entrada + ':' + $scope.fData.temporal.minuto_desde_entrada,
                  'entrada': $scope.fData.temporal.hora_entrada + ':' + $scope.fData.temporal.minuto_entrada,
                  'hasta_entrada': $scope.fData.temporal.hora_cierre_entrada + ':' + $scope.fData.temporal.minuto_cierre_entrada
                },
                'salida' : {
                  'desde_salida': $scope.fData.temporal.hora_cierre_salida + ':' + $scope.fData.temporal.minuto_cierre_salida,
                  'salida': $scope.fData.temporal.hora_salida + ':' + $scope.fData.temporal.minuto_salida,
                  'hasta_salida': $scope.fData.temporal.hora_hasta_salida + ':' + $scope.fData.temporal.minuto_hasta_salida
                },
                'horas_trabajadas' : $scope.fData.temporal.horas_trabajadas,
                'tiempo_tolerancia': $scope.fData.temporal.minuto_tolerancia 
              }; 
              angular.forEach($scope.gridOptionsHorarioAdd.data, function(valueDet, keyDet) { 

                if( value.idhorario == valueDet.idhorario ){ 
                  if (keyDet > -1) {
                    $scope.gridOptionsHorarioAdd.data.splice(keyDet, 1);
                  }
                }
              });
              $scope.gridOptionsHorarioAdd.data.push($scope.arrTemporal);
            });
            console.log($scope.gridOptionsHorarioAdd.data);
            $scope.fData.temporal = {};
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            // console.log(row.entity);
            if( $scope.tieneHorarios && (row.entity.id) ){ 

              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){ 
                  row.entity.modo = 'inline';
                  horarioGeneralServices.sEliminarHorarioDeEmpleado(row.entity).then(function (rpta) { 
                    if(rpta.flag == 1){ 
                      var pTitle = 'OK!';
                      var pType = 'success';
                    }else if(rpta.flag == 0){ 
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{ 
                      alert('Error inesperado'); 
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
                    $scope.getPaginationServerSideDetHG(); 
                    $scope.getPaginationServerSideHG();
                  });
                }
              });
            }else{
              var index = $scope.gridOptionsHorarioAdd.data.indexOf(row.entity); 
              $scope.gridOptionsHorarioAdd.data.splice(index,1); 
            }
          }
          $scope.getPaginationServerSideDetHG = function () { 
            if( $scope.mySelectionGridEM.length > 0 ){ 
              var arrParams = { 
                datos: $scope.mySelectionGridEM[0]
              };
              horarioGeneralServices.sListarHorarioGeneralEmpleado(arrParams).then(function (rpta) {
                $scope.gridOptionsHorarioAdd.data = rpta.datos; 
              });
              $scope.mySelectionGridHG = [];
            } 
          }
          $scope.generateHorasTrab = function () { 
            //console.log('validar dfs');
            //if( ($scope.fData.temporal.hora_entrada) && ($scope.fData.temporal.minuto_entrada) && ($scope.fData.temporal.hora_salida) && ($scope.fData.temporal.minuto_salida) ){ 
              //console.log('validar');
              var fechaEntrada  = moment().format('DD-MM-YYYY')+' '+$scope.fData.temporal.hora_entrada+':'+$scope.fData.temporal.minuto_entrada;
              var fechaSalida = moment().format('DD-MM-YYYY')+' '+$scope.fData.temporal.hora_salida+':'+$scope.fData.temporal.minuto_salida;

              $scope.fData.temporal.horas_trabajadas = moment.utc(moment(fechaSalida,"DD/MM/YYYY HH:mm:ss").diff(moment(fechaEntrada,"DD/MM/YYYY HH:mm:ss"))).format("HH:mm:ss");
              //console.log($scope.fData.temporal.horas_trabajadas);
              //$scope.fData.temporal.horas_trabajadas = 8;
            //}
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          } 
          $scope.aceptar = function () { 
            $scope.fData.idempleado = $scope.mySelectionGridEM[0].id;
            $scope.fData.arrHorarios = $scope.gridOptionsHorarioAdd.data;
            horarioGeneralServices.sRegistrarHorarioGen($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSideHG();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
            });
          }
        }
      });
    }
    $scope.btnAnularHorario = function () {
      var pMensaje = '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          horarioGeneralServices.sEliminarHorarioDeEmpleado($scope.mySelectionGridHG).then(function (rpta) {
            if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.getPaginationServerSideHG();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }
    $scope.btnActualizarMarcado = function () { 
      $modal.open({
        templateUrl: angular.patchURLCI+'HorarioGeneral/ver_popup_actualizar_marcacion',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'ACTUALIZAR MARCACIÓN';  
          $scope.fBusquedaMC.desde = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fBusquedaMC.desdeHora = '00';
          $scope.fBusquedaMC.desdeMinuto = '00';
          $scope.fBusquedaMC.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fBusquedaMC.hastaHora = 23;
          $scope.fBusquedaMC.hastaMinuto = 59;
          var paginationOptionsMC = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 100,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsMC = { 
            minRowsToShow: 9,
            paginationPageSizes: [100, 500, 1000],
            paginationPageSize: 100,
            enableSelectAll: true,
            enableFullRowSelection: true,
            useExternalPagination: true,
            useExternalSorting: true,
            multiSelect: true,
            enableGridMenu: true,
            data: [],
            columnDefs: [ 
              { field: 'id', name: 'idasistencia', displayName: 'ID', width: '8%' },
              { field: 'fecha', name: 'fecha', displayName: 'FECHA', width: '18%',enableFiltering: false, sort: { direction: uiGridConstants.DESC } },
              { field: 'hora', name: 'hora', displayName: 'HORA', width: '18%' },
              { field: 'diferencia', name: 'diferencia_tiempo', displayName: 'DIF.', width: '15%', cellTemplate: '<div class="ui-grid-cell-contents">{{ COL_FIELD }}</div>' },
              { field: 'tipo', name: 'tipo_asistencia', displayName: 'TIPO', width: '16%' },
              { field: 'personal', name: 'personal', displayName: 'PERSONAL', width: '30%', visible:false },
              { field: 'estado', name: 'es.descripcion', displayName: 'ESTADO', width: '22%' ,enableFiltering: false,
                cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 160px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
              }
            ],
            onRegisterApi: function(gridApiMC) {
              $scope.gridApiMC = gridApiMC;
              gridApiMC.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionGridMC = gridApiMC.selection.getSelectedRows();
              });
              gridApiMC.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionGridMC = gridApiMC.selection.getSelectedRows(); 
              });

              $scope.gridApiMC.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationOptionsMC.sort = null;
                  paginationOptionsMC.sortName = null;
                } else {
                  paginationOptionsMC.sort = sortColumns[0].sort.direction;
                  paginationOptionsMC.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSideMC();
              });
              gridApiMC.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsMC.pageNumber = newPage;
                paginationOptionsMC.pageSize = pageSize;
                paginationOptionsMC.firstRow = (paginationOptionsMC.pageNumber - 1) * paginationOptionsMC.pageSize;
                $scope.getPaginationServerSideMC();
              });
            }
          };
          paginationOptionsMC.sortName = $scope.gridOptionsMC.columnDefs[1].name;
          $scope.getPaginationServerSideMC = function () { 
              var arrParams = { 
                paginate : paginationOptionsMC, 
                datos: { 
                  'idempleado' : $scope.mySelectionGridEM[0].id,
                  'filtros':  $scope.fBusquedaMC
                }
              };
              asistenciaServices.sListarAsistenciasDeEmpleado(arrParams).then(function (rpta) {
                $scope.gridOptionsMC.data = rpta.datos; 
                $scope.gridOptionsMC.totalItems = rpta.paginate.totalRows.contador;
              });
          }
          $scope.getPaginationServerSideMC();
          $scope.aceptar = function () { 
            // $scope.fData.idempleado = $scope.mySelectionGridEM[0].id;
            $scope.fDataMC.asistencias = $scope.mySelectionGridMC;
            asistenciaServices.sActualizarMarcaciones($scope.fDataMC).then(function (rpta) { 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                //$modalInstance.dismiss('cancel');
                $scope.getPaginationServerSideMC();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          } 
        }
      });
    }
    $scope.btnAnularEmpleado = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          empleadoServices.sAnular($scope.mySelectionGridEM).then(function (rpta) {
            if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.getPaginationServerSideEM();
                $scope.getPaginationServerSideHG();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }


    $scope.btnVerFichaEmpleado = function (){
      console.log("ficha empleado");

      $scope.fDataImprimir = {};
      $scope.fDataImprimir = angular.copy($scope.mySelectionGridEM[0]);
      $scope.fDataImprimir.lugar_nacimiento = {
        'departamento' : '',
        'provincia' : '',
        'distrito' : ''
      }
      $scope.fDataImprimir.dirImagesEmpleados = $scope.dirImagesEmpleados;

      var arrParams = {
        titulo: 'FICHA DE DATOS DEL TRABAJADOR',
        datos:{
          resultado:  $scope.fDataImprimir,
          salida: 'pdf',
          tituloAbv: 'FIC-EMPL',
          titulo: 'FICHA DE DATOS DEL TRABAJADOR'
        },
        metodo: 'php'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/ficha_datos_empleado',
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    
    /*$scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          empleadoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }*/

    $scope.getPaginationServerSideEM();
  }])
  .service("horarioGeneralServices",function($http, $q) {
    return({
        sListarHorarioGeneralEmpleado: sListarHorarioGeneralEmpleado,
        sRegistrarHorarioGen: sRegistrarHorarioGen,
        sEliminarHorarioDeEmpleado: sEliminarHorarioDeEmpleado
    });

    function sListarHorarioGeneralEmpleado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HorarioGeneral/lista_horario_general_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarHorarioGen (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HorarioGeneral/registrar_horario_generado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarHorarioDeEmpleado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HorarioGeneral/eliminar_horario_de_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });