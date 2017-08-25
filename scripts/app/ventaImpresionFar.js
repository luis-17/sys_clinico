angular.module('theme.ventaImpresionFar', ['theme.core.services'])
  .controller('ventaImpresionFarController', ['$scope', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'cajaActualServices', 
    'empresaAdminServices',
    'ventaImpresionFarServices',
    'liquidacionFarmServices',
    function($scope, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      cajaActualServices, 
      empresaAdminServices,
      ventaImpresionFarServices,
      liquidacionFarmServices ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2"); $scope.modulo = 'ventaImpresionFar';
    $scope.cajaAbiertaPorMiSession = false;
    $scope.fCajaAbiertaSession = null;
    $scope.fBusqueda = {};
    $scope.btnToggleFilteringIM = function(){
      $scope.gridOptionsVentasImpresion.enableFiltering = !$scope.gridOptionsVentasImpresion.enableFiltering;
      $scope.gridApiVentaImpresion.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      //$scope.listaSedeEmpresaAdmin.splice(0,0,{ id : 'all', descripcion:'-- Todos --'});
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      // cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rptaDet) { 
      //   if(rptaDet.flag === 1){
      //     $scope.listaCajaMaster = rptaDet.datos;
      //   }else{
      //     return false;
      //   }
      //   if( rptaDet.flag === 1 && angular.isObject(rptaDet.cajaactual) ){ 
      //     $scope.fBusqueda.cajamaster = rptaDet.cajaactual.idcajamaster; 
      //     $scope.cajaAbiertaPorMiSession = true;
      //     $scope.fCajaAbiertaSession = rptaDet.cajaactual;
      //     pinesNotifications.notify({ title: 'Información', text: 'Su caja está abierta.', type: 'success', delay: 4500 });
      //   }else{
      //       $scope.fBusqueda.cajamaster = $scope.listaCajaMaster[0].id; 
      //       pinesNotifications.notify({ title: 'Información', text: 'Ud. no tiene ninguna caja abierta.', type: 'warning', delay: 4500 });
      //   }
      // });
      $scope.getPaginationIMServerSide();
    });
    // $scope.onChangeEmpresaSede = function () { 
    //   cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rpta) { 
    //     $scope.listaCajaMaster = rpta.datos;
    //     if( rpta.flag === 0 ){ 
    //       $scope.listaCajaMaster.push( { id: '', descripcion: 'No se encontraron cajas abiertas.' } );
    //     }
    //     $scope.fBusqueda.cajamaster = $scope.listaCajaMaster[0].id; 
    //     $scope.getPaginationIMServerSide(); // IMPRESIONES SOLICITADAS 
    //   });
    // }

    /* GRID DE VENTAS IMPRESION */
    $scope.mySelectionGridIM = [];
    var paginationOptionsEE = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasImpresion = {
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', minWidth: '100',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE', minWidth: '230', visible: false },
        { field: 'cajero', name: 'emp.nombres', displayName: 'CAJERO', minWidth: '180' },
        { field: 'caja', name: 'cj.descripcion', displayName: 'CAJA', minWidth: '120' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', minWidth: '120' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', minWidth: '120' },
        { field: 'medico', name: 'medico', displayName: 'PROFESIONAL', minWidth: '180', visible: false },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA DE VENTA', minWidth: '140', enableFiltering: false },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', minWidth: '120', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', minWidth: '100' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', minWidth: '100' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', minWidth: '100', cellClass: 'bg-lightblue' },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', minWidth: '80', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>' 
        } 
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiVentaImpresion = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){ 
          $scope.mySelectionGridIM = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridIM = gridApi.selection.getSelectedRows();
        });
        $scope.gridApiVentaImpresion.core.on.sortChanged($scope, function(grid, sortColumns) { // 
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsEE.sort = null;
            paginationOptionsEE.sortName = null;
          } else {
            paginationOptionsEE.sort = sortColumns[0].sort.direction;
            paginationOptionsEE.sortName = sortColumns[0].name;
          }
          $scope.getPaginationIMServerSide();
        });
        $scope.gridApiVentaImpresion.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsEE.search = true; 
          paginationOptionsEE.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'CONCAT(emp.nombres," ",emp.apellido_paterno," ",emp.apellido_materno)' : grid.columns[3].filters[0].term,
            'cj.descripcion' : grid.columns[3].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'sub_total' : grid.columns[8].filters[0].term,
            'total_igv' : grid.columns[9].filters[0].term,
            'total_a_pagar' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationIMServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsEE.pageNumber = newPage;
          paginationOptionsEE.pageSize = pageSize;
          paginationOptionsEE.firstRow = (paginationOptionsEE.pageNumber - 1) * paginationOptionsEE.pageSize;
          $scope.getPaginationIMServerSide();
        });
      }
    };
    paginationOptionsEE.sortName = $scope.gridOptionsVentasImpresion.columnDefs[0].name;
    $scope.getPaginationIMServerSide = function() {
      var arrParams = { 
        paginate : paginationOptionsEE,
        datos : $scope.fBusqueda
      };
      ventaImpresionFarServices.sListarVentasEnEspera(arrParams).then(function (rpta) {
        $scope.gridOptionsVentasImpresion.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasImpresion.data = rpta.datos;
      });
      $scope.mySelectionGridIM = [];
    };
    $scope.btnAprobarVenta = function (mensaje) { // DESCUENTO 
      // console.log('aprobar');
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          cajaActualServices.sAprobarVentaDescuento($scope.mySelectionGridIM).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){ // 
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationIMServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
    $scope.btnVerDetalleVenta = function (fVenta,size) { 
      $modal.open({
        templateUrl: angular.patchURLCI+'venta/ver_popup_detalle_venta',
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
            $scope.datosGrid = {
              paginate: paginationOptionsDetalleVenta,
              datos: fVenta
            };
            //console.log($scope.mySelectionGridIM[0]);
            liquidacionFarmServices.sListarDetalleVenta($scope.datosGrid).then(function (rpta) {
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
    $scope.btnAprobarSolicitudImprimirTicket = function (fila) { // IMPRESION 
      var pMensaje = '¿Realmente desea APROBAR LA SOLICITUD DE IMPRESIÓN enviada?'; 
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          ventaImpresionFarServices.sAprobarSolicitudImpresion(fila).then(function (rpta) { // console.log(fila);
            if(rpta.flag == 1){ 
              var pTitle = 'OK!'; 
              var pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationIMServerSide();
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
  .service("ventaImpresionFarServices",function($http, $q) {
    return({
        sListarVentasEnEspera: sListarVentasEnEspera, 
        sAprobarSolicitudImpresion: sAprobarSolicitudImpresion
    });
    function sListarVentasEnEspera(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_ventas_con_solicitud_impresion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAprobarSolicitudImpresion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/aprobar_solicitud_reimpresion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });