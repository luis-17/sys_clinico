angular.module('theme.ventaDescuentoFarm', ['theme.core.services'])
  .controller('ventaDescuentoFarmController', ['$scope', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'liquidacionFarmServices', 
    'empresaAdminServices',
    'cajaServices',
    'ventaDescuentoFarmServices', 
    function($scope, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      liquidacionFarmServices, 
      empresaAdminServices,
      cajaServices,
      ventaDescuentoFarmServices ){ 

    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2"); 
    $scope.modulo = 'ventaDescuentoFarm';
    $scope.idmodulo = 3;
    
    $scope.cajaAbiertaPorMiSession = false;
    $scope.fCajaAbiertaSession = null;
    $scope.fBusqueda = {};
    $scope.fBusqueda.idmodulo = $scope.idmodulo;
    $scope.btnToggleFilteringEE = function(){
      $scope.gridOptionsVentasEnEspera.enableFiltering = !$scope.gridOptionsVentasEnEspera.enableFiltering;
      $scope.gridApiEnEspera.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rptaDet) { 
        if(rptaDet.flag === 1){
          $scope.listaCajaMaster = rptaDet.datos;
        }
        
        if( rptaDet.flag === 1 && angular.isObject(rptaDet.cajaactual) ){ 
          $scope.fBusqueda.cajamaster = rptaDet.cajaactual.idcajamaster; 
          $scope.getPaginationEEServerSide(); // en espera 
          $scope.cajaAbiertaPorMiSession = true;
          $scope.fCajaAbiertaSession = rptaDet.cajaactual;
          pinesNotifications.notify({ title: 'Información', text: 'Su caja está abierta.', type: 'success', delay: 4500 });
        }else{
            pinesNotifications.notify({ title: 'Información', text: 'Ud. no tiene ninguna caja abierta.', type: 'warning', delay: 4500 });
        }
        // if(rpta.flag === 0){

        // }
      });
    });
    $scope.onChangeEmpresaSede = function () { 
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rpta) { 
        $scope.listaCajaMaster = rpta.datos;
        if( rpta.flag === 0 ){ 
          $scope.listaCajaMaster.push( { id: '', descripcion: 'No se encontraron cajas abiertas.' } );
        }
        $scope.fBusqueda.cajamaster = $scope.listaCajaMaster[0].id; 
        $scope.getPaginationEEServerSide(); // en espera 
      });
    }

    /* GRID DE VENTAS EN ESPERA*/
    $scope.mySelectionGridEE = [];
    var paginationOptionsEE = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasEnEspera = {
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '10%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE', width: '20%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%' },
        // { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE VENTA', width: '12%',enableFiltering: false },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false, enableFiltering: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%', enableFiltering: false },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%', enableFiltering: false },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue', enableFiltering: false },
        { 
          field: 'estado', 
          displayName: 'Estado', 
          width: '5%', 
          cellTemplate:'<label class="label label-warning" style="margin: 7px;"> <i class="fa fa-spinner fa-spin"></i> </label>', 
          cellClass:'text-center',
          enableColumnMenus: false, 
          enableColumnMenu: false,
          enableSorting: false,
          enableFiltering: false
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiEnEspera = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridEE = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridEE = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiEnEspera.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsEE.sort = null;
            paginationOptionsEE.sortName = null;
          } else {
            paginationOptionsEE.sort = sortColumns[0].sort.direction;
            paginationOptionsEE.sortName = sortColumns[0].name;
          }
          $scope.getPaginationEEServerSide();
        });
        $scope.gridApiEnEspera.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsEE.search = true; 
          paginationOptionsEE.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            //'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            // 'descripcion_med' : grid.columns[7].filters[0].term,
            // 'sub_total' : grid.columns[8].filters[0].term,
            // 'total_igv' : grid.columns[9].filters[0].term,
            // 'total_a_pagar' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationEEServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsEE.pageNumber = newPage;
          paginationOptionsEE.pageSize = pageSize;
          paginationOptionsEE.firstRow = (paginationOptionsEE.pageNumber - 1) * paginationOptionsEE.pageSize;
          $scope.getPaginationEEServerSide();
        });
      }
    };
    paginationOptionsEE.sortName = $scope.gridOptionsVentasEnEspera.columnDefs[0].name;
    $scope.getPaginationEEServerSide = function() {
      var arrParams = {
        paginate : paginationOptionsEE,
        datos : $scope.fBusqueda
      };
      ventaDescuentoFarmServices.sListarVentasEnEsperaCajaActual(arrParams).then(function (rpta) {
        console.log(rpta);
        $scope.gridOptionsVentasEnEspera.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasEnEspera.data = rpta.datos;
      });
      $scope.mySelectionGridEE = [];
    };
    $scope.btnAprobarVenta = function (mensaje) { 
      // console.log('aprobar');
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          liquidacionFarmServices.sAprobarVentaDescuento($scope.mySelectionGridEE).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){ // 
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationEEServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
    $scope.btnVerDetalleVenta = function (fVenta,size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_detalle_venta',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Venta';
          $scope.fVenta = fVenta;
          var paginationOptionsDetalleVenta = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleVentaGrid = [];
          $scope.btnToggleFiltering = function(){
            $scope.gridOptionsDetalleVenta.enableFiltering = !$scope.gridOptionsDetalleVenta.enableFiltering;
            $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
          };
          $scope.gridOptionsDetalleVenta = {
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
              { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '14%' },
              { field: 'medicamento', name: 'denominacion', displayName: 'PRODUCTO' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT.', width: '12%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '8%' },
              { field: 'descuento', name: 'descuento_asignado', displayName: 'DSCTO.', width: '10%', cellClass: 'bg-lightblue' },
              { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '13%', cellClass: 'bg-lightblue' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDetalleVentaGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsDetalleVenta.sort = null;
                  paginationOptionsDetalleVenta.sortName = null;
                } else {
                  paginationOptionsDetalleVenta.sort = sortColumns[0].sort.direction;
                  paginationOptionsDetalleVenta.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleVentaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsDetalleVenta.pageNumber = newPage;
                paginationOptionsDetalleVenta.pageSize = pageSize;
                paginationOptionsDetalleVenta.firstRow = (paginationOptionsDetalleVenta.pageNumber - 1) * paginationOptionsDetalleVenta.pageSize;
                $scope.getPaginationDetalleVentaServerSide();
              });
            }
          };
          paginationOptionsDetalleVenta.sortName = $scope.gridOptionsDetalleVenta.columnDefs[0].name;
          $scope.getPaginationDetalleVentaServerSide = function() {
            //$scope.$parent.blockUI.start();
            var arrParams = {
              paginate: paginationOptionsDetalleVenta,
              datos: fVenta
            };
            //console.log($scope.mySelectionGridEE[0]);
            liquidacionFarmServices.sListarDetalleVenta(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleVenta.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleVenta.data = rpta.datos;
              //$scope.$parent.blockUI.stop();
            });
            $scope.mySelectionDetalleVentaGrid = [];
          };
          $scope.getPaginationDetalleVentaServerSide();

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    /* FIX TAB IN GRID */
    $scope.reloadGrid = function () { // console.log('click med');
      setTimeout( function() { 
          $scope.gridApiEnEspera.core.handleWindowResize();
      }, 100 );
    }
  }])
  .service("ventaDescuentoFarmServices",function($http, $q) {
    return({
        sListarVentasEnEsperaCajaActual: sListarVentasEnEsperaCajaActual, 
    });
    function sListarVentasEnEsperaCajaActual(datos) { 

      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_ventas_en_espera_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });