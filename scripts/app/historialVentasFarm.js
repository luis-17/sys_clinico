angular.module('theme.historialVentasFarm', ['theme.core.services'])
  .controller('historialVentasFarmController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ventaFarmaciaServices', 
    'empresaAdminServices',
    'historialVentaFarmServices',
    'liquidacionFarmServices',
    'blockUI',
    'ModalReporteFactory',
    function($scope, $filter, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ventaFarmaciaServices, 
      empresaAdminServices,
      historialVentaFarmServices,
      liquidacionFarmServices,
      blockUI,
      ModalReporteFactory ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");
    $scope.modulo = 'historialVentasFarm';
    $scope.cajaAbiertaPorMiSession = false;
    $scope.fCajaAbiertaSession = null;
    $scope.fBusqueda = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGridV = [];
    $scope.btnToggleFiltering = function(){ 
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringMed = function(){ 
      $scope.gridOptionsMed.enableFiltering = !$scope.gridOptionsMed.enableFiltering;
      $scope.gridApiMed.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringPre = function(){ 
      $scope.gridOptionsPre.enableFiltering = !$scope.gridOptionsPre.enableFiltering;
      $scope.gridApiPre.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
    });

    /* GRID DE VENTAS */
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%' },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE'},
        // { field: 'idtipodocumento', name: 'idtipodocumento', displayName: 'idtipodocumento', width: '18%', visible:false },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%', cellClass: 'bg-lightblue'},
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%', sort: { direction: uiGridConstants.DESC} },
        
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%', enableFiltering: false  },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' }, 
        { field: 'estado', type: 'object', name: 'ESTADO', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+ 
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+ 
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '<label tooltip-placement="left" tooltip="ATENDIDO" style="box-shadow: 1px 1px 0 black;" class="label label-info ml-xs">'+ 
            '<i ng-if="COL_FIELD.claseIconAtendido" class="fa {{ COL_FIELD.claseIconAtendido }}"></i> </label>'+ 
            '</div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
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
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'sub_total' : grid.columns[7].filters[0].term,
            'total_igv' : grid.columns[8].filters[0].term,
            'total_a_pagar' : grid.columns[9].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
        // $interval( function() {
        //   $scope.gridApi.core.handleWindowResize();
        // }, 10, 500);
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[3].name;
    $scope.getPaginationServerSide = function(load) {
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      historialVentaFarmServices.sListarVentasHistorial(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptions.data = rpta.datos;
        if( loader ){ 
          blockUI.stop();
        }
      });
      $scope.mySelectionGridV = [];
    };

    $scope.btnImprimirTicket = function (fila) { 
      var pMensaje = '¿Realmente desea realizar la impresión?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ventaFarmaciaServices.sImprimirTicketVenta(fila).then(function (rpta) { 
            if(rpta.flag == 1){
              var printContents = rpta.html;
              var popupWin = window.open('', 'windowName', 'width=300,height=300');
              popupWin.document.open()
              popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
              popupWin.document.close();
            }else {
              if(rpta.flag == 0) { // ALGO SALIÓ MAL
                var pTitle = 'Error';
                var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                var pType = 'danger';
              }
              if(rpta.flag == 2) { // FALTA APROBAR, ESTÁ EN ESPERA.
                var pTitle = 'Advertencia';
                var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
                var pType = 'warning';
              }
              if(rpta.flag == 3) { // YA ESTA IMPRESO, NO SE PUEDE REIMPRIMIR
                var pTitle = 'Advertencia';
                var pText = 'Ya se imprimió el ticket. Solicite la reimpresión del ticket desde su Liquidación Actual.';
                var pType = 'warning';
              }
              if(rpta.flag == 4) { // SOLICITUD DE IMPRESION EN PROCESO, EL AREA DE SISTEMAS ESTÁ EVALUANDO LA SOLICITUD.
                var pTitle = 'Información';
                var pText = 'Solicitud de reimpresión <strong> en proceso </strong>. El Área de Sistemas está evaluando su solicitud.';
                var pType = 'info';
              }
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
            }
            // $scope.getPaginationRIServerSide();
          });
        }
      });
    }
    $scope.btnSolicitudImprimirTicket = function () { 
      var pMensaje = '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){ 
          liquidacionFarmServices.sEnviarSolicitudImpresion($scope.mySelectionGridV).then(function (rpta) { 
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
    $scope.btnVerDetalleVenta = function (fVenta,size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_detalle_venta',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Venta';
          $scope.fVenta = fVenta;
          console.log($scope.fVenta);
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
    /* GRID DE HISTORIAL DE VENTAS POR MEDICAMENTOS */
    var paginationOptionsMed = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsMed = { 
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
        { field: 'id', name: 'idmovimiento', displayName: 'ID', maxWidth: '60', visible: false},
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', maxWidth: '120' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%', cellClass: 'bg-lightblue'},
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%', sort: { direction: uiGridConstants.DESC} },
        { field: 'medicamento', name: 'denominacion', displayName: 'MEDICAMENTO', minWidth:'200'},
        { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '10%', visible: false},
        
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%', enableFiltering: false  },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '80' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PREC. UNI.', width: '7%' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' }, 
        { field: 'estado', type: 'object', name: 'ESTADO', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+ 
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+ 
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '<label tooltip-placement="left" tooltip="ATENDIDO" style="box-shadow: 1px 1px 0 black;" class="label label-info ml-xs">'+ 
            '<i ng-if="COL_FIELD.claseIconAtendido" class="fa {{ COL_FIELD.claseIconAtendido }}"></i> </label>'+ 
            '</div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiMed = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiMed.core.on.sortChanged($scope, function(grid, sortColumns) { 
          console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsMed.sort = null;
            paginationOptionsMed.sortName = null;
          } else {
            paginationOptionsMed.sort = sortColumns[0].sort.direction;
            paginationOptionsMed.sortName = sortColumns[0].name;
          }
          $scope.getPaginationMedServerSide();
        });
        $scope.gridApiMed.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptionsMed.search = true; 
          paginationOptionsMed.searchColumn = {
            'fm.idmovimiento' : grid.columns[1].filters[0].term,
            'orden_venta' : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'denominacion' : grid.columns[5].filters[0].term,
            'nombre_lab' : grid.columns[6].filters[0].term,
            'descripcion_med' : grid.columns[8].filters[0].term,
            'cantidad' : grid.columns[9].filters[0].term,
            'precio_unitario' : grid.columns[10].filters[0].term,
            'total_detalle' : grid.columns[11].filters[0].term
          }
          $scope.getPaginationMedServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsMed.pageNumber = newPage;
          paginationOptionsMed.pageSize = pageSize;
          paginationOptionsMed.firstRow = (paginationOptionsMed.pageNumber - 1) * paginationOptionsMed.pageSize;
          $scope.getPaginationMedServerSide();
        });
      }
    };
    paginationOptionsMed.sortName = $scope.gridOptionsMed.columnDefs[3].name;
    $scope.getPaginationMedServerSide = function(load) {
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptionsMed,
        datos : $scope.fBusqueda
      };
      historialVentaFarmServices.sListarVentasHistorialMed(arrParams).then(function (rpta) { 
        $scope.gridOptionsMed.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsMed.totalVentas = rpta.paginate.sumCantidad;
        $scope.gridOptionsMed.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptionsMed.data = rpta.datos;
        if( loader ){ 
          blockUI.stop();
        }
      });
      $scope.mySelectionGridV = [];
    };

    /* GRID DE HISTORIAL DE VENTAS POR PREPARADOS */
    var paginationOptionsPre = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsPre = { 
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
        { field: 'id', name: 'idmovimiento', displayName: 'ID', maxWidth: '60', visible: false},
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', maxWidth: '120'},
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%', cellClass: 'bg-lightblue', visible: false},
        { field: 'receta_referencia', name: 'idsolicitudformula', displayName: 'N° SOLICITUD', width: '6%', sort: { direction: uiGridConstants.DESC} },
        { field: 'medicamento', name: 'denominacion', displayName: 'MEDICAMENTO',  width: '15%', minWidth:'200'},
        { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', minWidth:'100'},
        { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '10%', visible: false},
        
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%', enableFiltering: false  },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '80' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PREC. UNI.', width: '7%' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiPre = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiPre.core.on.sortChanged($scope, function(grid, sortColumns) { 
          console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsPre.sort = null;
            paginationOptionsPre.sortName = null;
          } else {
            paginationOptionsPre.sort = sortColumns[0].sort.direction;
            paginationOptionsPre.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPreServerSide();
        });
        $scope.gridApiPre.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptionsPre.search = true; 
          paginationOptionsPre.searchColumn = {
            'fm.idmovimiento' : grid.columns[1].filters[0].term,
            'orden_venta' : grid.columns[2].filters[0].term,
            'idsolicitudformula' : grid.columns[3].filters[0].term,
            'descripcion_td' : grid.columns[4].filters[0].term,            
            'denominacion' : grid.columns[5].filters[0].term,
            "CONCAT_WS(' ',c.nombres, c.apellido_paterno,c.apellido_materno)" : grid.columns[6].filters[0].term,
            'nombre_lab' : grid.columns[8].filters[0].term,
            'descripcion_med' : grid.columns[9].filters[0].term,
            'cantidad' : grid.columns[10].filters[0].term,
            'precio_unitario' : grid.columns[11].filters[0].term,
            'total_detalle' : grid.columns[12].filters[0].term
          }
          $scope.getPaginationPreServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPre.pageNumber = newPage;
          paginationOptionsPre.pageSize = pageSize;
          paginationOptionsPre.firstRow = (paginationOptionsPre.pageNumber - 1) * paginationOptionsPre.pageSize;
          $scope.getPaginationPreServerSide();
        });
      }
    };
    paginationOptionsPre.sortName = $scope.gridOptionsPre.columnDefs[3].name;
    $scope.getPaginationPreServerSide = function(load) {
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptionsPre,
        datos : $scope.fBusqueda
      };
      historialVentaFarmServices.sListarVentasHistorialPre(arrParams).then(function (rpta) { 
        $scope.gridOptionsPre.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsPre.totalVentas = rpta.paginate.sumCantidad;
        $scope.gridOptionsPre.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptionsPre.data = rpta.datos;
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
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'HISTORIAL DE VENTAS POR MEDICAMENTO',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptionsMed,
          salida: 'excel',
          tituloAbv: 'HVM',
          titulo: 'HISTORIAL DE VENTAS POR MEDICAMENTO',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_historial_venta_medicamento_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    /* FIX TAB IN GRID */
    // $scope.reloadGrid = function () { 
    //   $interval( function() { 
    //       $scope.gridApi.core.handleWindowResize();
    //   }, 50, 5);
    // }
    // $scope.reloadGrid();
  }])
  .service("historialVentaFarmServices",function($http, $q) {
    return({
        sListarVentasHistorial: sListarVentasHistorial,
        sListarVentasHistorialMed: sListarVentasHistorialMed,
        sListarVentasHistorialPre: sListarVentasHistorialPre,
    });

    function sListarVentasHistorial(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"historialVentasFarm/lista_historial_ventas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasHistorialMed(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"historialVentasFarm/lista_historial_ventas_por_medicamento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasHistorialPre(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"historialVentasFarm/lista_historial_ventas_por_preparado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
  });