angular.module('theme.historialCajaChica', ['theme.core.services'])
  .controller('historialCajaChicaController', ['$scope', '$filter', '$uibModal', '$sce', '$route', '$interval', '$controller', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI', 
    'historialCajaChicaServices',
    'cajaChicaServices',
    function($scope, $filter, $uibModal, $sce, $route, $interval, $controller, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI, 
      historialCajaChicaServices,
      cajaChicaServices){ 
    'use strict';
    shortcut.remove("F2");
    shortcut.remove("F8");

    $scope.modulo = 'historialCajaChica';
    $scope.mySelectionGridHC = [];

    /* GRID DE CAJA CHICA */
    var paginationOptions = { 
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    }; 
    
    var paginationOptionsDet = { 
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    }; 

    $scope.gridOptions = { 
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [ 
        { field: 'idcajachica', name: 'idcajachica', displayName: 'ID', width: '5%' },
        { field: 'idaperturacajachica', name: 'idaperturacajachica', displayName: 'ID APERTURA', visible:false, width: '5%' },
        { field: 'numero_cheque', name: 'numero_cheque', displayName: 'N° CHEQUE', width: '8%' },
        { field: 'nombre_caja', name: 'nombre_caja', displayName: 'CAJA', width: '8%' },
        { field: 'responsable', name: 'responsable', displayName: 'RESPONSABLE APERTURA', width: '14%' },
        { field: 'fecha_apertura', name: 'fecha_apertura', displayName: 'FECHA APERTURA', width: '8%', enableFiltering: false, },
        { field: 'fecha_liquidacion', name: 'fecha_liquidacion', displayName: 'FECHA LIQUIDACION', width: '8%', enableFiltering: false, visible:false },
        { field: 'responsable_cierre', name: 'responsable_cierre', displayName: 'RESPONSABLE CIERRE', width: '14%' },
        { field: 'fecha_cierre', name: 'fecha_cierre', displayName: 'FECHA CIERRE', width: '8%', enableFiltering: false,  },
        
        { field: 'monto_inicial', name: 'monto_inicial', displayName: 'MONTO INICIAL', width: '8%', cellClass: 'text-right', enableFiltering: false,  },
        { field: 'importe_total', name: 'importe_total', displayName: 'IMPORTE TOTAL', width: '8%', cellClass: 'text-right', enableFiltering: false,  },
        { field: 'saldo', name: 'saldo', displayName: 'SALDO', width: '8%', cellClass: 'text-right columna-resaltada', enableFiltering: false },
        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '9%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+ 
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+ 
            '{{COL_FIELD.labelText}} </label>'+ 
            '</div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridHC = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridHC = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
          console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide(true);
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'x.idcajachica' : grid.columns[1].filters[0].term,
            'x.numero_cheque' : grid.columns[3].filters[0].term,
            'x.nombre_caja' : grid.columns[4].filters[0].term,
            "x.responsable" : grid.columns[5].filters[0].term,
            "x.responsable_cierre" : grid.columns[8].filters[0].term
            
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide(true);
        }); 
      }
    };

    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    }; 

    $scope.getPaginationServerSide = function(load) { 
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptions,
      };
      historialCajaChicaServices.sListarCajaChicaHistorial(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        if( loader ){ 
          blockUI.stop();
        }
      });
      $scope.mySelectionGridHC = [];
    };
    $scope.getPaginationServerSide();

    $scope.btnCerrarCaja = function(row){
      blockUI.start('Abriendo formulario...');
      $scope.fData = angular.copy(row);
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'CajaChica/ver_popup_cierre',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Cierre de Caja Chica';

          $scope.gridOptionsDet = { 
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            multiSelect: false,
            columnDefs: [ 
              { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID', width: '8%'},
              { field: 'numero_documento', name: 'numero_documento', displayName: 'Nº DOCUMENTO', width: '12%'},
              { field: 'fecha_registro', name: 'fecha_registro', displayName: 'FECHA DE REGISTRO', width: '14%', enableFiltering: false, sort: { direction: uiGridConstants.DESC} },
              { field: 'glosa', name: 'glosa', displayName: 'GLOSA', width: '24%'},
              { field: 'importe_local', name: 'importe_local', displayName: 'IMPORTE', width: '12%', cellClass:'text-right', enableFiltering: false}, 
              { field: 'importe_local_con_igv', name: 'importe_local_con_igv', displayName: 'IMPORTE CON IGV', width: '12%', cellClass:'text-right', enableFiltering: false}, 
              { field: 'estado', type: 'object', name: 'estado_obj', displayName: ' ', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
                width: '12%', cellTemplate:'<div class="ui-grid-cell-contents">' + 
                  '<label style="box-shadow: 1px 1px 0 black; display: block;font-size: 12px;" class="label {{ COL_FIELD.claseLabel }} "> {{ COL_FIELD.labelText }}' + 
                  '</label></div>' 
              }
              // ,{ field: 'acciones', displayName: ' ', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
              //   width: '5%', cellTemplate:'<div class="ui-grid-cell-contents">' + 
              //     '<label ng-if="row.entity.estado_movimiento == 1" ng-click="grid.appScope.btnAnularDetalle(row.entity)" style="box-shadow: 1px 1px 0 black; display: block;font-size: 12px;" class="label label-danger ">'+
              //     ' <i class="fa fa-close"></i>' + 
              //     '</label></div>' 
              // }              
            ],
            onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
                if (sortColumns.length == 0) {
                  paginationOptionsDet.sort = null;
                  paginationOptionsDet.sortName = null;
                } else {
                  paginationOptionsDet.sort = sortColumns[0].sort.direction;
                  paginationOptionsDet.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSide(true);
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
                var grid = this.grid;
                paginationOptionsDet.search = true; 
                paginationOptionsDet.searchColumn = { 
                  'acc.idaperturacajachica' : grid.columns[1].filters[0].term,
                  'cch.nombre' : grid.columns[2].filters[0].term,
                  "CONCAT(emp.nombres,' ',emp.apellido_paterno,' ',emp.apellido_materno)" : grid.columns[3].filters[0].term,
                  "CONCAT(emp2.med_nombres,' ',emp2.med_apellido_paterno,' ',emp2.med_apellido_materno)" : grid.columns[6].filters[0].term,
                  'acc.numero_cheque' : grid.columns[8].filters[0].term,
                }
                $scope.getPaginationServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
                paginationOptionsDet.pageNumber = newPage;
                paginationOptionsDet.pageSize = pageSize;
                paginationOptionsDet.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
                $scope.getPaginationServerSide(true);
              }); 
            }
          };

          $scope.aceptar = function(){
            cajaChicaServices.sCerrarCajaChica($scope.fData).then(function (rpta) { 
              console.log(rpta,'rpta');
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                $scope.getPaginationServerSide(); 
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          $scope.cancel = function(){
            $scope.getPaginationServerSide();
            $modalInstance.dismiss('cancel');
          }

          $scope.getPaginationServerSideDet = function(load) { 
            var loader = load || false;
            if( loader ){ 
              blockUI.start('Ejecutando proceso...');
            }
            var arrParams = {
              datos: $scope.mySelectionGridHC[0],
              paginate : paginationOptions,
            };
            historialCajaChicaServices.sListarDetCajaChica(arrParams).then(function (rpta) { 
              $scope.gridOptionsDet.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDet.data = rpta.datos;
              // $scope.fData.saldo = (rpta.saldo_caja).toFixed(2);
              // $scope.fData.importe_total_numeric = ($scope.fData.monto_inicial_numeric - $scope.fData.saldo).toFixed(2);
              if( loader ){ 
                blockUI.stop();
              }
            });
            $scope.mySelectionGridDet = [];
          };
          $scope.getPaginationServerSideDet();

          $scope.btnAnularDetalle = function(row){ 
            var pMensaje = '¿Realmente desea anular el compra?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                cajaChicaServices.sAnularMovimiento(row).then(function(rpta){
                  if(rpta.flag == 1){
                    var pTitle = 'OK!';
                    var pType = 'success'; 
                    $scope.getPaginationServerSideDet();
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Algo salió mal...');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });
              }
            });            
          }
        }
      });
    }

    $scope.btnVerMovimientos = function(row) {
      blockUI.start('Abriendo formulario...');
      
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'HistorialCajaChica/ver_popup_detalle_movimiento',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Movimientos de Caja'; 
          // TRAER FILA DE APERTURA 
          var arrParams = {
            idaperturacajachica: $scope.mySelectionGridHC[0].idaperturacajachica  
          }
          historialCajaChicaServices.sListarEstaAperturaCajaChica(arrParams).then(function(rpta) {
            $scope.fData = rpta.fData; 
          }); 

          $scope.btnToggleFilteringDM = function() {
            $scope.gridOptionsDetMov.enableFiltering = !$scope.gridOptionsDetMov.enableFiltering;
            $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
          }
          $scope.dirImagesSemaforo = $scope.dirImages + 'semaforos/'; // 
          var paginationOptionsDetMov = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          }; 
          $scope.gridOptionsDetMov = { 
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [ 
              { field: 'idmovimiento', name: 'mo.idmovimiento', displayName: 'ID', maxWidth: 60, sort: { direction: uiGridConstants.DESC}},
              { field: 'descripcion_td', name: 'td.descripcion_td', displayName: 'DOCUMENTO', minWidth: '200', width:100},
              { field: 'numero_documento', name: 'numero_documento', displayName: 'Nº DOCUMENTO', minWidth: 20},
              { field: 'empresa', name: 'empresa', displayName: 'PROVEEDOR', minWidth: 20},
              { field: 'fecha_registro', name: 'fecha_registro', displayName: 'FECHA DE REGISTRO', minWidth: 100, enableFiltering: false },
              { field: 'fecha_emision', name: 'fecha_emision', displayName: 'FECHA DE EMISIÓN', minWidth: 100, enableFiltering: false },
              { field: 'glosa', name: 'glosa', displayName: 'GLOSA', minWidth: 200 },
              { field: 'importe_local_con_igv', name: 'importe_local_con_igv', displayName: 'IMPORTE', minWidth: 120, cellClass:'text-right', enableFiltering: false}, 
              { field: 'estado_color_obj', type: 'object', name: 'estado', displayName: ' ', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
                width: 50, cellTemplate:'<div class="ui-grid-cell-contents text-center" title="{{ COL_FIELD.label }}">' + '<img style="width: 20px;" class="" ng-src="{{grid.appScope.dirImagesSemaforo + COL_FIELD.nombre_img}}" alt="{{COL_FIELD.label}}" />' + '</div>' 
              }
            ], //   
            onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionGridDM = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionGridDM = gridApi.selection.getSelectedRows();
              });
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
                if (sortColumns.length == 0) {
                  paginationOptionsDetMov.sort = null;
                  paginationOptionsDetMov.sortName = null;
                } else {
                  paginationOptionsDetMov.sort = sortColumns[0].sort.direction;
                  paginationOptionsDetMov.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSideDet(true);
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
                var grid = this.grid;
                paginationOptionsDetMov.search = true; 
                paginationOptionsDetMov.searchColumn = { 
                  'mo.idmovimiento' : grid.columns[1].filters[0].term,
                  'td.descripcion_td' : grid.columns[2].filters[0].term,
                  'numero_documento' : grid.columns[3].filters[0].term,
                  'glosa' : grid.columns[6].filters[0].term 
                }
                $scope.getPaginationServerSideDet();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
                paginationOptionsDetMov.pageNumber = newPage;
                paginationOptionsDetMov.pageSize = pageSize;
                paginationOptionsDetMov.firstRow = (paginationOptionsDetMov.pageNumber - 1) * paginationOptionsDetMov.pageSize;
                $scope.getPaginationServerSideDet(true);
              }); 
            }
          };
          $scope.cancelDet1 = function(){
            $scope.getPaginationServerSideDet();
            $scope.getPaginationServerSide();
            $modalInstance.dismiss('cancel');
          }
          paginationOptionsDetMov.sortName = $scope.gridOptionsDetMov.columnDefs[0].name;
          $scope.getPaginationServerSideDet = function(load) { 
            var loader = load || false;
            if( loader ){ 
              blockUI.start('Ejecutando proceso...');
            }
            var arrParams = {
              datos: $scope.mySelectionGridHC[0],
              paginate : paginationOptionsDetMov,
            };
            historialCajaChicaServices.sListarDetCajaChica(arrParams).then(function (rpta) { 
              $scope.gridOptionsDetMov.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetMov.data = rpta.datos;
              // $scope.fData.saldo = (rpta.saldo_caja).toFixed(2);
              // $scope.fData.importe_total_numeric = ($scope.fData.monto_inicial_numeric - $scope.fData.saldo).toFixed(2);
              if( loader ){ 
                blockUI.stop();
              }
            });
            $scope.mySelectionGridDM = [];
          };
          $scope.getPaginationServerSideDet();

          $scope.btnAbrirConversacion = function(rowDet) { 
            blockUI.start('Abriendo formulario...');
            $scope.fDataCV = angular.copy(rowDet); 
            $scope.dirImagesSemaforo = $scope.dirImages + 'semaforos/'; // 
            // console.log($scope.fDataCV,'$scope.fDataCV'); dirImagesSemaforo
            $uibModal.open({ 
              templateUrl: angular.patchURLCI+'HistorialCajaChica/ver_popup_abir_conversacion',
              size: 'lg',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                blockUI.stop(); 
                $scope.fDataCom = {}; 
                $scope.titleFormCV = 'Conversación/Estados'; 
                var arrParams = {
                  idmovimiento: $scope.fDataCV.idmovimiento
                };
                $scope.getListaEstadosComentarios = function(argument) {
                  historialCajaChicaServices.sListarComentariosDeMovimiento(arrParams).then(function(rpta) {
                    $scope.listaComentario = rpta.datos.comentarios;
                  });
                }
                $scope.getListaEstadosComentarios(); 

                $scope.cambiarEstadoColor = function() { 
                  if( $scope.fSessionCI.key_group == 'key_sistemas' || $scope.fSessionCI.key_group == 'key_gerencia' ){ 
                    if( $scope.fDataCV.estado_color_obj.label_cambio == 'APROBADO' ){
                      $scope.fDataCV.estado_color_obj.label_cambio = 'OBSERVADO';
                      $scope.fDataCV.estado_color_obj.nombre_img_cambio = 'amarillo.png';
                      $scope.fDataCV.estado_color_obj.flag = 2;
                    }else if( $scope.fDataCV.estado_color_obj.label_cambio == 'OBSERVADO' ){
                      $scope.fDataCV.estado_color_obj.label_cambio = 'ANULADO';
                      $scope.fDataCV.estado_color_obj.nombre_img_cambio = 'rojo.png';
                      $scope.fDataCV.estado_color_obj.flag = 3;
                    }else if( $scope.fDataCV.estado_color_obj.label_cambio == 'ANULADO' ){
                      $scope.fDataCV.estado_color_obj.label_cambio = 'APROBADO';
                      $scope.fDataCV.estado_color_obj.nombre_img_cambio = 'verde.png';
                      $scope.fDataCV.estado_color_obj.flag = 1;
                    }
                  }else{
                    return false; 
                  }
                  
                }
                $scope.btnAgregarItem = function() { 
                  var arrParams = {
                    estado_color_obj: $scope.fDataCV.estado_color_obj,
                    comentario: $scope.fDataCom.comentario_text,
                    idmovimiento: $scope.fDataCV.idmovimiento,
                    idaperturacajachica: $scope.mySelectionGridHC[0].idaperturacajachica,
                    fila: $scope.fDataCV 
                  }
                  blockUI.start('Ejecutando proceso...');
                  historialCajaChicaServices.sAgregarComentarioEstado(arrParams).then(function(rpta) { 
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success'; 

                      $scope.fDataCV.estado_color_obj.label = $scope.fDataCV.estado_color_obj.label_cambio;
                      $scope.fDataCV.estado_color_obj.nombre_img = $scope.fDataCV.estado_color_obj.nombre_img_cambio;
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                    }else{
                      alert('Algo salió mal...');
                    }
                    
                    var arrParams = { 
                      idaperturacajachica: $scope.mySelectionGridHC[0].idaperturacajachica  
                    } 
                    historialCajaChicaServices.sListarEstaAperturaCajaChica(arrParams).then(function(rpta) {
                      $scope.fData.importe_total = rpta.fData.importe_total; 
                      $scope.fData.monto_inicial = rpta.fData.monto_inicial; 
                      $scope.fData.saldo = rpta.fData.saldo; 
                      $scope.fData.saldo_numeric = rpta.fData.saldo_numeric; 
                      $scope.fData.importe_total_numeric = rpta.fData.importe_total_numeric;
                      $scope.mySelectionGridHC[0] = $scope.fData; 
                      console.log($scope.mySelectionGridHC[0],$scope.fData,'$scope.mySelectionGridHC[0]'); 
                    }); 

                    $scope.getPaginationServerSideDet();
                    $scope.getListaEstadosComentarios();

                    $scope.fDataCom.comentario_text = null; 

                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    blockUI.stop();
                  });
                }
                $scope.cancelDet2 = function(){ 
                  $scope.getPaginationServerSideDet();
                  $modalInstance.dismiss('cancel');

                }

              }
            }); 
          }
        }
      });
    }
  }])
  .service("historialCajaChicaServices",function($http, $q) {
    return({
        sListarCajaChicaHistorial: sListarCajaChicaHistorial,
        sListarEstaAperturaCajaChica: sListarEstaAperturaCajaChica, 
        sListarDetCajaChica: sListarDetCajaChica,
        sListarComentariosDeMovimiento: sListarComentariosDeMovimiento,
        sAgregarComentarioEstado: sAgregarComentarioEstado
    });

    function sListarCajaChicaHistorial(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCajaChica/lista_historial_caja_chica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEstaAperturaCajaChica(datos) { // lista_esta_apertura_caja_chica
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCajaChica/lista_esta_apertura_caja_chica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetCajaChica(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCajaChica/listar_movimientos_una_caja", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sListarComentariosDeMovimiento(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCajaChica/listar_comentarios_de_movimiento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarComentarioEstado(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCajaChica/agregar_comentario_estado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });