angular.module('theme.recepcionFormula', ['theme.core.services'])
  .controller('recepcionFormulaController', ['$scope', '$filter', '$route', '$sce', '$interval', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications',
    'hotkeys',
    'blockUI',
    'ModalReporteFactory',
    'recepcionServices',
    function($scope, $filter, $sce, $route, $interval, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications,
      hotkeys,
      blockUI,
      ModalReporteFactory,
      recepcionServices){ 
    'use strict';    
    shortcut.remove("F2");    

    $scope.modulo = 'recepcionFormula';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGrid = [];
    $scope.mySelectionGridFR = [];
    $scope.tabRecibidas = false;

    // PESTAÑA DE FORMULAS POR RECIBIR
      var paginationOptions = { 
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
        enableFiltering: true,
        enableFullRowSelection: true,
        multiSelect: true,
        columnDefs: [ 
          { field: 'idsolicitudformula', name: 'idsolicitudformula', displayName: 'N° SOLICITUD', width: 80, sort: { direction: uiGridConstants.DESC}, cellClass:'text-right' },
          { field: 'codigo_pedido', name: 'codigo_pedido', displayName: 'N° PEDIDO', width: 80, cellClass:'text-right' },
          { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA PEDIDO', width: 120, enableFiltering: false, enableSorting: false},
          { field: 'cliente', name: 'paciente', displayName: 'CLIENTE', minWidth: 140 },
          { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: 80, cellClass:'text-right' },
          { field: 'idmedicamento', name: 'idmedicamento', displayName: 'COD.', width: 80, cellClass:'text-right' },
          { field: 'formula', name: 'denominacion', displayName: 'FÓRMULA', minWidth: 140 },
          { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: 60, visible: true, enableSorting: false, cellClass:'text-center' },
          { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', width: 90, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
            cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
          }
        ],
        onRegisterApi: function(gridApi) { 
          $scope.gridApi = gridApi;
          gridApi.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
          });
          gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
          });

          $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {           
            if (sortColumns.length == 0) {
              paginationOptions.sort = null;
              paginationOptions.sortName = null;
            } else {
              paginationOptions.sort = sortColumns[0].sort.direction;
              paginationOptions.sortName = sortColumns[0].name;
            }
            $scope.getPaginationServerSide(false);
          });
          $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
            var grid = this.grid;
            paginationOptions.search = true; 
            paginationOptions.searchColumn = { 
              'fm.idsolicitudformula' : grid.columns[1].filters[0].term,
              'fm.codigo_pedido' : grid.columns[2].filters[0].term,
              "CONCAT_WS(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno)" : grid.columns[4].filters[0].term,
              'telefono' : grid.columns[5].filters[0].term,
              'me.idmedicamento' : grid.columns[6].filters[0].term,
              'me.denominacion' : grid.columns[7].filters[0].term,
              'fdm.cantidad' : grid.columns[8].filters[0].term           
            }
            $scope.getPaginationServerSide(false);
          });
          gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
            paginationOptions.pageNumber = newPage;
            paginationOptions.pageSize = pageSize;
            paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
            $scope.getPaginationServerSide(false);
          });
        }
      };
      paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
      $scope.getPaginationServerSide = function(loader) {
        if(loader){
          blockUI.start('Cargando datos...');
          $scope.gridOptions.data = [];
        }
        var arrParams = {
          paginate : paginationOptions,
          datos : $scope.fBusqueda
        };
        recepcionServices.sListarFormulasPagadas(arrParams).then(function (rpta) {
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
          $scope.gridOptions.sumTotal = rpta.sumTotal;
          if(loader){
            blockUI.stop();
          }
        });
        $scope.mySelectionGrid = [];
        $scope.mySelectionGridFR = [];
      };
      $scope.getPaginationServerSide(true);
    
      $scope.btnRecibirFormula = function (grupoRecepcion) { 
        var grupoRecepcion = grupoRecepcion || false;
        console.log('grupoRecepcion',grupoRecepcion);
        $uibModal.open({
          templateUrl: angular.patchURLCI+'EntradasFarm/ver_popup_entrada_formula',
          size: '',
          backdrop: 'static',
          keyboard:false,
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.fDataEntrada = {};
            $scope.fDataEntrada.fecha_recepcion = $filter('date')(new Date(),'dd-MM-yyyy');
            $scope.fDataEntrada.hora = $filter('date')(new Date(),'HH');
            $scope.fDataEntrada.minuto = $filter('date')(new Date(),'mm');
            $scope.fDataEntrada.detalle = $scope.mySelectionGrid;
            $scope.titleForm = 'RECEPCION DE FORMULAS'
            $scope.aceptar = function (){
              blockUI.start('Ejecutando proceso...');
              if(grupoRecepcion == 'tecnica'){
                recepcionServices.sRecibirFormulaTecnica($scope.fDataEntrada).then(function (rpta) {                
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success'; 
                    $modalInstance.dismiss('cancel');
                    $scope.getPaginationServerSide(true);
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Algo salió mal...');
                  }
                  blockUI.stop();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });  
              }else{
                recepcionServices.sRecibirFormula($scope.fDataEntrada).then(function (rpta) {                
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success'; 
                    $modalInstance.dismiss('cancel');
                    $scope.getPaginationServerSide(true);
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Algo salió mal...');
                  }
                  blockUI.stop();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });                  
              }
            }
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.getPaginationServerSide(true);
            }
          }
        });
      }
    // PESTAÑA DE FORMULAS RECIBIDAS
      $scope.listaEstados = [
        {id:0, descripcion: 'TODAS'},
        {id:3, descripcion: 'RECIBIDAS'},
        {id:4, descripcion: 'POR CONFIRMAR'},
        {id:2, descripcion: 'ENTREGADAS'},
      ];
      $scope.fBusqueda.estadoRecibido = $scope.listaEstados[0];
      var paginationFROptions = { 
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      $scope.gridOptionsFR = { 
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: true,
        enableFiltering: true,
        enableFullRowSelection: true,
        multiSelect: true,
        columnDefs: [ 
          { field: 'idsolicitudformula', name: 'idsolicitudformula', displayName: 'N° SOLICITUD', width: 80, sort: { direction: uiGridConstants.DESC}, cellClass:'text-right' },
          { field: 'codigo_pedido', name: 'codigo_pedido', displayName: 'N° PEDIDO', width: 80, cellClass:'text-right' },
          { field: 'fecha_venta', name: 'fecha_movimiento', displayName: 'FEC. VENTA', width: 120, enableFiltering: false, enableSorting: false},
          { field: 'fecha_recepcion', name: 'fecha_recepcion', displayName: 'FEC. RECEPCION', width: 120, enableFiltering: false, enableSorting: false},
          { field: 'guia_remision', name: 'guia_remision', displayName: 'G. REMISION', minWidth: 90, width: 90},
          { field: 'cliente', name: 'paciente', displayName: 'CLIENTE', minWidth: 140 },
          { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: 80, cellClass:'text-right' },
          { field: 'idmedicamento', name: 'idmedicamento', displayName: 'COD.', width: 80, cellClass:'text-right' },
          { field: 'formula', name: 'denominacion', displayName: 'FORMULA', minWidth: 140 },
          { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: 60, visible: true, enableSorting: false, cellClass:'text-center' },
          { field: 'precio_costo', name: 'precio_costo', displayName: 'PRECIO COSTO', width: 90, visible: false, enableFiltering: false, enableSorting: false, cellClass:'text-right' },
          { field: 'costo_total', name: 'costo_total', displayName: 'COSTO TOTAL', width: 90, visible: false, enableFiltering: false, enableSorting: false, cellClass:'text-right' },
          { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', width: 115, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, pinnedRight:true,
            cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
          }
        ],
        onRegisterApi: function(gridApi) { 
          $scope.gridApi = gridApi;
          gridApi.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionGridFR = gridApi.selection.getSelectedRows();
            $scope.mySelectionGridFR = gridApi.selection.getSelectedRows();
          });
          gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionGridFR = gridApi.selection.getSelectedRows();
            $scope.mySelectionGridFR = gridApi.selection.getSelectedRows();
          });

          $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {           
            if (sortColumns.length == 0) {
              paginationFROptions.sort = null;
              paginationFROptions.sortName = null;
            } else {
              paginationFROptions.sort = sortColumns[0].sort.direction;
              paginationFROptions.sortName = sortColumns[0].name;
            }
            $scope.getPaginationFRServerSide(false);
          });
          $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
            var grid = this.grid;
            paginationFROptions.search = true; 
            paginationFROptions.searchColumn = { 
              'fm.idsolicitudformula' : grid.columns[1].filters[0].term,
              'fm.codigo_pedido' : grid.columns[2].filters[0].term,
              "CONCAT_WS(' ', cli.nombres, cli.apellido_paterno, cli.apellido_materno)" : grid.columns[4].filters[0].term,
              'telefono' : grid.columns[5].filters[0].term,
              'me.idmedicamento' : grid.columns[6].filters[0].term,
              'me.denominacion' : grid.columns[7].filters[0].term,
              'fdm.cantidad' : grid.columns[8].filters[0].term          
            }
            $scope.getPaginationFRServerSide(false);
          });
          gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
            paginationFROptions.pageNumber = newPage;
            paginationFROptions.pageSize = pageSize;
            paginationFROptions.firstRow = (paginationFROptions.pageNumber - 1) * paginationFROptions.pageSize;
            $scope.getPaginationFRServerSide(false);
          });
        }
      };
      paginationFROptions.sortName = $scope.gridOptionsFR.columnDefs[0].name;
      $scope.getPaginationFRServerSide = function(loader) {
        if(loader){
          blockUI.start('Cargando datos...');
          $scope.gridOptionsFR.data = [];
        }
        var arrParams = {
          paginate : paginationFROptions,
          datos : $scope.fBusqueda
        };
        recepcionServices.sListarFormulasRecibidas(arrParams).then(function (rpta) {
          $scope.gridOptionsFR.totalItems = rpta.paginate.totalRows;
          $scope.gridOptionsFR.data = rpta.datos;
          $scope.gridOptionsFR.sumTotal = rpta.sumTotal;
          if(loader){
            blockUI.stop();
          }
        });
        $scope.mySelectionGrid = [];
        $scope.mySelectionGridFR = [];
      };
      //$scope.getPaginationFRServerSide();
    // BOTONES
      $scope.btnEntregarPedido = function (mensaje) {
        var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
        $bootbox.confirm(pMensaje, function(result) {
          if(result){
            recepcionServices.sEntregarFormula($scope.mySelectionGridFR).then(function (rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                var pTiempo = 3500; // tiempo en milisegundos que aparecerá la notificación
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                var pTiempo = 3500;
              }else{
                alert('Algo salió mal...');
              }
              $scope.getPaginationFRServerSide(true);
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: pTiempo });
            }); 
          }
        });
      }

      $scope.btnConfirmarRecibido = function (mensaje) {
        var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
        $bootbox.confirm(pMensaje, function(result) {
          if(result){
            recepcionServices.sConfirmarRecepcionFormula($scope.mySelectionGridFR).then(function (rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                var pTiempo = 3500; // tiempo en milisegundos que aparecerá la notificación
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                var pTiempo = 3500;
              }else{
                alert('Algo salió mal...');
              }
              $scope.getPaginationFRServerSide(true);
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: pTiempo });
            });
          }
        });
      }
      $scope.btnExportarListaExcel = function(){
        console.log('fBusqueda: ', $scope.fBusqueda);
        console.log('paginate: ', paginationFROptions);
        var arrParams = {
          titulo: 'FORMULAS RECIBIDAS',
          datos:{
            filtro: $scope.fBusqueda,
            paginate: paginationFROptions,
            salida: 'excel',
            tituloAbv: 'FORM',
            titulo: 'FORMULAS RECIBIDAS',
          },
          metodo: 'js'
        }
        console.log('arrParams: ', arrParams);
        arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_formulas_recibidas_costo_excel',
        ModalReporteFactory.getPopupReporte(arrParams);
      }
    $scope.btnProcesar = function(){
      if($scope.tabRecibidas){
        $scope.getPaginationFRServerSide(true); // formulas recibidas
      }else{
        $scope.getPaginationServerSide(true); // formulas por recibir
      }
    }
  }])
  .service("recepcionServices",function($http, $q) {
    return({
        sListarFormulasPagadas: sListarFormulasPagadas,
        sRecibirFormula: sRecibirFormula,
        sListarFormulasRecibidas: sListarFormulasRecibidas,
        sEntregarFormula: sEntregarFormula,
        sRecibirFormulaTecnica:sRecibirFormulaTecnica,
        sConfirmarRecepcionFormula:sConfirmarRecepcionFormula,
    });
    function sListarFormulasPagadas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"VentaFarmacia/lista_formulas_pagadas_para_recepcion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRecibirFormula (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/recibir_formula", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarFormulasRecibidas (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"VentaFarmacia/lista_formulas_recibidas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEntregarFormula (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"VentaFarmacia/entregar_formula", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRecibirFormulaTecnica (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/recibir_formula_tecnica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sConfirmarRecepcionFormula (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/confirmar_recepcion_formula", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });