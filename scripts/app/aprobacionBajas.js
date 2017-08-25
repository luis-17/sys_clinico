angular.module('theme.aprobacionBajas', ['theme.core.services'])
  .controller('aprobacionBajasController', ['$scope', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'aprobacionBajasServices', 'salidasFarmServices' , 
    function($scope, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      aprobacionBajasServices , salidasFarmServices){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2"); $scope.modulo = 'aprobacionBajas';
    $scope.fBusqueda = {};
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    /* GRID DE VENTAS IMPRESION */
    $scope.mySelectionGrid = [];
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
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
        { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'Fecha Movimiento', width: '12%' },
        { field: 'almacen', name: 'almacen', displayName: 'Almacen', width: '25%' },
        { field: 'subAlmacen', name: 'subAlmacen', displayName: 'SubAlmacen', width: '30%' },        
        { field: 'tipomovimiento', name: 'tipomovimiento', displayName: 'Tipo', width: '10%' ,enableFiltering: false },
        { field: 'estado_movimiento', type: 'object', name: 'estado', displayName: 'Estado', width: '10%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="text-center"><label tooltip-placement="left" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px; color:#fff;" class="badge {{ COL_FIELD.claseLabel }} "> <i class="ti {{ COL_FIELD.claseIcon }} mr-xs"></i>{{ COL_FIELD.labelText }} </label></div>' 
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

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { // 
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'idmovimiento' : grid.columns[1].filters[0].term,
            'fecha_movimiento' : grid.columns[2].filters[0].term,
            'nombre_alm' : grid.columns[3].filters[0].term,
            'nombre_salm' : grid.columns[4].filters[0].term
            //'total_a_pagar' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      salidasFarmServices.sListarSalidasEnEspera($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    $scope.btnVerDetalleSalida = function (size) { 
      $modal.open({
        templateUrl: angular.patchURLCI+'AlmacenFarmacia/ver_popup_detalle_salida',
        size: size || 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Salida';
          $scope.fData = {};
          //$scope.fSalida = fSalida;
          var paginationOptionsDetalleSalida = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleSalidaGrid = [];
          $scope.gridOptionsDetalleSalida = {
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: false,
            enableGridMenu: false,
            enableSorting: false,
            //enableRowSelection: true,
            //enableSelectAll: false,
            //enableFullRowSelection: true,
            //multiSelect: false,
            columnDefs: [ 
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: '10%' ,enableSorting: false ,enableColumnMenu: false  },
              { field: 'medicamento', name: 'medicamento', displayName: 'Producto', width: '80%' ,enableSorting: false ,enableColumnMenu: false  },
              { field: 'cantidad', name: 'cantidad', displayName: 'Cantidad', width: '10%' ,cellClass: 'bg-lightblue',enableSorting: false ,enableColumnMenu: false }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          paginationOptionsDetalleSalida.sortName = $scope.gridOptionsDetalleSalida.columnDefs[0].name;
          $scope.getPaginationDetalleSalidaServerSide = function() {
            $scope.datosGrid = {
              datos: $scope.mySelectionGrid[0]
            };
            $scope.fData = $scope.mySelectionGrid[0];
            salidasFarmServices.sListarDetalleSalida($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalleSalida.data = rpta.datos;
            });
            $scope.mySelectionDetalleSalidaGrid = [];
          };
          $scope.getPaginationDetalleSalidaServerSide();

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnAprobarSolicitudSalida = function (fila) { // IMPRESION 
      var pMensaje = '¿Realmente desea APROBAR LA SOLICITUD DE SALIDA enviada?'; 
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          salidasFarmServices.sAprobarSolicitudSalida(fila).then(function (rpta) { // console.log(fila);
            if(rpta.flag == 1){ 
              var pTitle = 'OK!'; 
              var pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          }); 
        }
      });
    }
    /* FIX TAB IN GRID */
    $scope.reloadGrid = function () { // console.log('click med');
      $interval( function() { 
          $scope.gridApiVentaImpresion.core.handleWindowResize();
      }, 50, 5);
    }
  }])
  .service("aprobacionBajasServices",function($http, $q) {
    return({
        sListarVentasEnEsperaCajaActual: sListarVentasEnEsperaCajaActual, 
    });
    function sListarVentasEnEsperaCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/lista_ventas_con_solicitud_impresion_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });