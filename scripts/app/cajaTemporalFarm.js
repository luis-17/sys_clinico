angular.module('theme.cajaTemporalFarm', ['theme.core.services'])
  .controller('cajaTemporalFarmController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox',
    'cajaTemporalFarmServices',
    'almacenFarmServices','blockUI',
    'ModalReporteFactory',
    function($scope, $filter, $sce, $route, $interval, $modal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      $bootbox,
      cajaTemporalFarmServices,
      almacenFarmServices,
      blockUI,
      ModalReporteFactory ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");

    $scope.fBusqueda = {};
    $scope.fBusqueda.almacen = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fDatosTrasladoOrigen = {};
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringLP = function(){ 
      $scope.gridOptionsLP.enableFiltering = !$scope.gridOptionsLP.enableFiltering;
      $scope.gridApiLP.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    /* GRILLA PRINCIPAL */
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
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [ 
        { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID ', width: '8%' },
        { field: 'idtipomovimiento', name: 'idtipomovimiento', displayName: 'TIPO ', width: '6%' , visible : false },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE MOVIMIENTO', width: '16%', enableFiltering: false,sort: { direction: uiGridConstants.DESC}  },
        { field: 'nombre_alm', name: 'nombre_alm', displayName: 'ALMACEN ORIGEN', width: '20%', sort: { direction: uiGridConstants.DESC}  },
        { field: 'tipo_movimiento', displayName : 'TIPO MOVIMIENTO' , filter: {
          term: 0,
          type: uiGridConstants.filter.SELECT,
          selectOptions: [{ value:0 , label:'TODOS'} , { value: 1, label: 'VENTA' }, { value: 2, label: 'COMPRA' } , { value: 3, label: 'TRASLADO' }]
        }, cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} "><i class="fa {{ COL_FIELD.icon}}"></i> {{ COL_FIELD.string}} </label>'},
        { field: 'usuario', name: 'usuario', displayName: 'USUARIO',enableFiltering: false, width: '23%' },

        { field: 'estado', displayName : 'ESTADO' , filter: {
          term: 0,
          type: uiGridConstants.filter.SELECT,
          selectOptions: [{ value:0 , label:'TODOS'} , { value: 1, label: 'POR APROBAR' }, { value: 3, label: 'REGULARIZADO' }]
        }, cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} "><i class="fa {{ COL_FIELD.icon}}"></i> {{ COL_FIELD.string}} </label>'}
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
            // POR DEFECTO ORDENAR POR: [6] => fecha_movimiento
            paginationOptions.sort = sortColumns[1].sort.direction;
            paginationOptions.sortName = sortColumns[1].name;
          }
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'idmovimiento' : grid.columns[1].filters[0].term,
            'fa.nombre_alm' : grid.columns[4].filters[0].term,
            'tipo_movimiento' : grid.columns[5].filters[0].term,
            'es_temporal' : grid.columns[7].filters[0].term
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
    paginationOptions.sortName = $scope.gridOptions.columnDefs[2].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      cajaTemporalFarmServices.sListarMovimientosTemporales(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.btnAprobarMovimiento = function() {
      var pMensaje = '¿Realmente desea aprobar el movimiento?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaTemporalFarmServices.sAprobarMovimientoTemporal($scope.mySelectionGrid[0]).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }

    $scope.btnVerDetalleMovimiento = function (fMovimiento,size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'cajaTemporalFarm/ver_popup_detalle_movimiento',
        size: size || 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle del Movimiento ' + $scope.mySelectionGrid[0].orden_venta;
          $scope.fMovimiento = fMovimiento;
          $scope.gridOptionsDetalleMovimiento = {
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: true,
            enableSelectAll: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [ 
              { field: 'iddetallemovimiento', name: 'iddetallemovimiento', displayName: 'ID', width: '6%' },
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'CODIGO', width: '10%' },
              { field: 'medicamento', name: 'denominacion', displayName: 'PRODUCTO' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT.', width: '12%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '8%' },
              { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '12%', cellClass: 'bg-lightblue' },
              { field: 'stock_actual', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: '8%' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          //paginationOptionsDetalleEntrada.sortName = $scope.gridOptionsDetalleEntrada.columnDefs[0].name;
          $scope.getPaginationDetalleMovimientoServerSide = function() {
            var arrParams = {
              datos: $scope.mySelectionGrid[0]
            };
            cajaTemporalFarmServices.sListarDetalleMovimiento(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleMovimiento.data = rpta.datos;
              $scope.gridOptionsDetalleMovimiento.sumTotal = rpta.sumTotal;
              $scope.getBuscarTrasladoOrigen();
            });
          };
          $scope.getPaginationDetalleMovimientoServerSide();
          $scope.getBuscarTrasladoOrigen = function(){
            var arrParams = {
              datos: $scope.mySelectionGrid[0]
            };
            cajaTemporalFarmServices.sListarEsteMovimientoTemporalAlmacen(arrParams).then(function (rpta) {
              $scope.fDatosTrasladoOrigen = rpta.datos[0];
            });
            
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    /* GRID DE MEDICAMENTOS */
    var paginationOptionsLP = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsLP = { 
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
        { field: 'id', name: 'iddetallemovimiento', displayName: 'ID', maxWidth: '60', visible: false, sort: { direction: uiGridConstants.DESC} },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE MOV.', maxWidth: '125', enableFiltering: false  },
        { field: 'idmedicamento', name: 'idmedicamento', displayName: 'COD. MED.', maxWidth: '120' },
        { field: 'medicamento', name: 'denominacion', displayName: 'MEDICAMENTO', minWidth:'200'},
       
        { field: 'laboratorio', name: 'laboratorio', displayName: 'LABORATORIO', width: '15%', visible: true},
        { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '80' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PREC. UNI.', width: '7%' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' },
        { field: 'stock_actual', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: '100' },
        
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiLP = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiLP.core.on.sortChanged($scope, function(grid, sortColumns) { 
          console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsLP.sort = null;
            paginationOptionsLP.sortName = null;
          } else {
            paginationOptionsLP.sort = sortColumns[0].sort.direction;
            paginationOptionsLP.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideLP();
        });
        $scope.gridApiLP.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptionsLP.search = true; 
          paginationOptionsLP.searchColumn = {
            'fdm.iddetallemovimiento' : grid.columns[1].filters[0].term,
            // 'orden_venta' : grid.columns[2].filters[0].term,
            'fdm.idmedicamento' : grid.columns[3].filters[0].term,
            'denominacion' : grid.columns[4].filters[0].term,
            'nombre_lab' : grid.columns[5].filters[0].term,
            'cantidad' : grid.columns[6].filters[0].term,
            'precio_unitario' : grid.columns[7].filters[0].term,
            'total_detalle' : grid.columns[8].filters[0].term,
            'stock_actual_malm' : grid.columns[9].filters[0].term,
          }
          $scope.getPaginationServerSideLP();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsLP.pageNumber = newPage;
          paginationOptionsLP.pageSize = pageSize;
          paginationOptionsLP.firstRow = (paginationOptionsLP.pageNumber - 1) * paginationOptionsLP.pageSize;
          $scope.getPaginationServerSideLP();
        });
      }
    };
    paginationOptionsLP.sortName = $scope.gridOptionsLP.columnDefs[3].name;
    $scope.getPaginationServerSideLP = function(load) {
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptionsLP,
        datos : $scope.fBusqueda
      };
      cajaTemporalFarmServices.sListarProductosMovimientosTemporales(arrParams).then(function (rpta) { 
        $scope.gridOptionsLP.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsLP.totalVentas = rpta.paginate.sumCantidad;
        $scope.gridOptionsLP.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptionsLP.data = rpta.datos;
        if( loader ){ 
          blockUI.stop();
        }
      });
      $scope.mySelectionGridV = [];
    };
      /* ============= */
     /* EXPORTACIONES */
    /* ============= */
    // $scope.btnExportarListaPdf = function(){
    //   console.log('fBusqueda: ', $scope.fBusqueda);
    //   console.log('paginate: ', paginationOptions);
    //   var arrParams = {
    //     titulo: 'INVENTARIO',
    //     datos:{
    //       resultado: $scope.fBusqueda,
    //       paginate: paginationOptions,
    //       salida: 'pdf',
    //       tituloAbv: 'INV',
    //       titulo: 'INVENTARIO',
    //     },
    //     metodo: 'php'
    //   }
    //   console.log('arrParams: ', arrParams);
    //   arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_inventario_farmacia',
    //   ModalReporteFactory.getPopupReporte(arrParams);
    // }
    $scope.btnExportarListaExcel = function(){
      var arrParams = {
        titulo: 'LISTADO DE PRODUCTOS EN MOVIMIENTOS TEMPORALES',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptionsLP,
          salida: 'excel',
          tituloAbv: 'FAR-LPMT',
          titulo: 'LISTADO DE PRODUCTOS EN MOVIMIENTOS TEMPORALES',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicamentos_movimientos_temporales_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }

  }])
  .service("cajaTemporalFarmServices",function($http, $q) {
    return({
        sListarMovimientosTemporales: sListarMovimientosTemporales,
        sListarProductosMovimientosTemporales: sListarProductosMovimientosTemporales,
        sListarEsteMovimientoTemporalAlmacen : sListarEsteMovimientoTemporalAlmacen ,
        sListarDetalleMovimiento : sListarDetalleMovimiento ,
        sAprobarMovimientoTemporal : sAprobarMovimientoTemporal ,
        // sRegistrarMovimientoTemporal : sRegistrarMovimientoTemporal
    });

    function sListarMovimientosTemporales(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cajaTemporalFarm/lista_movimientos_temporales", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosMovimientosTemporales(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cajaTemporalFarm/lista_productos_movimientos_temporales", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleMovimiento(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cajaTemporalFarm/lista_detalle_movimientos_temporales", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEsteMovimientoTemporalAlmacen(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cajaTemporalFarm/lista_este_movimiento_temporal_almacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAprobarMovimientoTemporal(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cajaTemporalFarm/aprobar_movimiento_temporal", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    // function sRegistrarMovimientoTemporal(datos) { 
    //   var request = $http({
    //         method : "post",
    //         url : angular.patchURLCI+"cajaTemporalFarm/registrar_movimiento_temporal", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }

  });