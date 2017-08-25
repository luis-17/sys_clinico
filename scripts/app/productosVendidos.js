angular.module('theme.productosVendidos', ['theme.core.services'])
  .controller('productosVendidosController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI',
    'cajaActualServices', 
    'empresaAdminServices',
    'especialidadServices', 
    'productosVendidosServices',
    //'convenioServices',
    function($scope, $filter, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI,
      cajaActualServices, 
      empresaAdminServices,
      especialidadServices,
      productosVendidosServices 
      /*convenioServices*/ ){ 
    'use strict';
    // $scope.$parent.reloadPage();
    shortcut.remove("F2");
    shortcut.remove("F8");
    // shortcut.add("F8",function() { 
    //   if($scope.mySelectionGridV.length > 0){ 
    //     $scope.btnImprimirTicketManual(); 
    //     $('#fechaVenta').focus(); 
    //   }
    // });

    $scope.modulo = 'productosPorVentas';
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
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      var ind = 0;
      angular.forEach($scope.listaSedeEmpresaAdmin, function(value, key) {
        if(value.id == $scope.fSessionCI.idsedeempresaadmin){
          ind = key;
        }
      });
      $scope.fBusqueda.sedeempresa = $scope.listaSedeEmpresaAdmin[ind];
      $scope.cargarEspecialidades($scope.fBusqueda.sedeempresa);
    });

    // ESPECIALIDAD
    $scope.cargarEspecialidades = function(datos){
      especialidadServices.sListarEspecialidadesCbo(datos).then(function (rpta) { 
        $scope.listaEspecialidades = rpta.datos;
        $scope.listaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
        $scope.fBusqueda.especialidad = $scope.listaEspecialidades[0];
      });
    }
    // CONVENIO 
    // convenioServices.sListarConvenioCbo().then(function (rpta) { 
    //   $scope.listaConvenios = rpta.datos;
    //   $scope.listaConvenios.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
    //   $scope.fBusqueda.convenio = $scope.listaConvenios[0];
    // });
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%', sort: { direction: uiGridConstants.DESC} },
        { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '18%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%', cellClass: 'bg-lightblue', visible: false },
        { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '9%', sort: { direction: uiGridConstants.DESC} },
        { field: 'medico', name: 'medico', displayName: 'Prof. Solic.', width: '15%' }, 
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '12%', enableFiltering: false  },
        { field: 'medio', name: 'descripcion_med', displayName: 'Medio de Pago', width: '10%', visible: false },
        { field: 'especialidad', name: 'esp.nombre', displayName: 'Especialidad', width: '10%' },
        { field: 'producto', name: 'pm.descripcion', displayName: 'Producto', width: '10%' },
        // { field: 'subtotal', name: 'sub_total', displayName: 'SubTotal', width: '7%' },
        // { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'Importe', width: '7%', cellClass: 'bg-lightblue' }, 
        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
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
          $scope.getPaginationServerSide(true);
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            "CONCAT(m.med_nombres,' ',m.med_apellido_paterno,' ',m.med_apellido_materno)" : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'esp.nombre' : grid.columns[8].filters[0].term,
            'pm.descripcion' : grid.columns[9].filters[0].term,
            'total_detalle' : grid.columns[10].filters[0].term
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
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function(loader) { 
      if( loader ){
        blockUI.start('Cargando información.');
      }
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      productosVendidosServices.sListarVentasHistorial(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptions.data = rpta.datos;
        if( loader ){
          blockUI.stop();
        }
      });
      $scope.mySelectionGridV = [];
    };
    // $scope.btnSolicitudImprimirTicket = function (mensaje) { 
    //   var pMensaje = mensaje || '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
    //   $bootbox.confirm(pMensaje, function(result) { 
    //     if(result){
    //       cajaActualServices.sEnviarSolicitudImpresion($scope.mySelectionGridV).then(function (rpta) { 
    //         if(rpta.flag == 1){ 
    //           var pTitle = 'OK!'; 
    //           var pType = 'success'; 
    //         }else if(rpta.flag == 0){
    //           var pTitle = 'Error!';
    //           var pType = 'danger';
    //         }else{
    //           alert('Algo salió mal...');
    //         }
    //         $scope.getPaginationServerSide();
    //         pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
    //         // $scope.getPaginationRIServerSide();
    //       }); 
    //     }
    //   });
    // } 
    // $scope.btnImprimirTicket = function (fila) { 
    //   var pMensaje = '¿Realmente desea realizar la re-impresión?';
    //   $bootbox.confirm(pMensaje, function(result) {
    //     if(result){
    //       cajaActualServices.sImprimirTicketVenta(fila).then(function (rpta) { 
    //         if(rpta.flag == 1){
    //           var printContents = rpta.html;
    //           var popupWin = window.open('', 'windowName', 'width=300,height=300');
    //           popupWin.document.open()
    //           popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
    //           popupWin.document.close();
    //         }else {
    //           if(rpta.flag == 0) { // ALGO SALIÓ MAL
    //             var pTitle = 'Error';
    //             var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
    //             var pType = 'danger';
    //           }
    //           if(rpta.flag == 2) { // FALTA APROBAR, ESTÁ EN ESPERA.
    //             var pTitle = 'Advertencia';
    //             var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
    //             var pType = 'warning';
    //           }
    //           if(rpta.flag == 3) { // YA ESTA IMPRESO, NO SE PUEDE REIMPRIMIR
    //             var pTitle = 'Advertencia';
    //             var pText = 'Ya se imprimió el ticket. Solicite la reimpresión del ticket desde su Liquidación Actual.';
    //             var pType = 'warning';
    //           }
    //           if(rpta.flag == 4) { // SOLICITUD DE IMPRESION EN PROCESO, EL AREA DE SISTEMAS ESTÁ EVALUANDO LA SOLICITUD.
    //             var pTitle = 'Información';
    //             var pText = 'Solicitud de reimpresión <strong> en proceso </strong>. El Área de Sistemas está evaluando su solicitud.';
    //             var pType = 'info';
    //           }
    //           pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
    //         }
    //         // $scope.getPaginationRIServerSide();
    //       });
    //     }
    //   });
    // }
    // $scope.btnImprimirTicketManual = function () { 
    //   $modal.open({
    //     templateUrl: angular.patchURLCI+'venta/ver_popup_impresion_ticket_manual',
    //     size: 'sm',
    //     scope: $scope,
    //     controller: function ($scope, $modalInstance) { 
    //       $scope.titleForm = 'Impresión Manual de Ticket';
    //       $scope.fVenta = $scope.mySelectionGridV[0];
    //       $scope.fVenta.fecha_venta = '03-09-2016';
    //       $scope.fVenta.hora_venta = '07';
    //       $scope.fVenta.minuto_venta = '00';
    //       console.log('venta', $scope.fVenta);
    //       $scope.aceptar = function(){
    //         productosVendidosServices.sImprimirTicketVentaManual($scope.fVenta).then(function (rpta) { 
    //           if(rpta.flag == 1){
    //             var printContents = rpta.html;
    //             var popupWin = window.open('', 'windowName', 'width=300,height=300');
    //             popupWin.document.open()
    //             popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
    //             popupWin.document.close();
    //             $modalInstance.dismiss('cancel');
    //           }else {
    //             if(rpta.flag == 0) { // ALGO SALIÓ MAL
    //               var pTitle = 'Error';
    //               var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
    //               var pType = 'danger';
    //             }
                
    //             pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
    //           }
    //           // $scope.getPaginationRIServerSide();
    //         });
    //       }
    //       $scope.cancel = function () {
    //         $modalInstance.dismiss('cancel');
    //       }
    //     }
    //   });
    // }
    // $scope.btnAnular = function (mensaje) { 
    //   if( $scope.mySelectionGridV[0].estado.claseIconAtendido ){ 
    //     pinesNotifications.notify({ title: 'Advertencia.', text: 'La venta ya ha sido atendida, no se puede anular. Contacte con el área de sistemas.', type: 'warning', delay: 3500 }); 
    //     return false;
    //   }
    //   var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
    //   $bootbox.confirm(pMensaje, function(result) {
    //     if(result){
    //       cajaActualServices.sAnularVentaCajaActual($scope.mySelectionGridV).then(function (rpta) { 
    //         if(rpta.flag == 1){
    //           pTitle = 'OK!';
    //           pType = 'success'; 
    //         }else if(rpta.flag == 0){
    //           var pTitle = 'Error!';
    //           var pType = 'danger';
    //         }else{
    //           alert('Algo salió mal...');
    //         }
    //         $scope.getPaginationVAServerSide();
    //         $scope.getPaginationServerSide();
    //         pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
    //       }); 
    //     }
    //   });
    // }

    // $scope.btnVerDetalleVenta = function (fVenta,size) { 
    //   $modal.open({
    //     templateUrl: angular.patchURLCI+'venta/ver_popup_detalle_venta',
    //     size: size || 'xlg',
    //     scope: $scope,
    //     controller: function ($scope, $modalInstance) { 
    //       $scope.titleForm = 'Detalle de la Venta';
    //       $scope.fVenta = fVenta;
    //       var paginationOptionsDetalleVenta = {
    //         pageNumber: 1,
    //         firstRow: 0,
    //         pageSize: 10,
    //         sort: uiGridConstants.ASC,
    //         sortName: null,
    //         search: null
    //       };
    //       $scope.mySelectionDetalleVentaGrid = [];
    //       $scope.btnToggleFiltering = function(){
    //         $scope.gridOptionsDetalleVenta.enableFiltering = !$scope.gridOptionsDetalleVenta.enableFiltering;
    //         $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    //       };
    //       $scope.gridOptionsDetalleVenta = {
    //         minRowsToShow: 6,
    //         paginationPageSizes: [10, 50, 100, 500, 1000],
    //         paginationPageSize: 10,
    //         useExternalPagination: true,
    //         useExternalSorting: true,
    //         enableGridMenu: false,
    //         enableRowSelection: true,
    //         enableSelectAll: false,
    //         enableFullRowSelection: true,
    //         multiSelect: false,
    //         columnDefs: [ 
    //           { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '14%' },
    //           { field: 'especialidad', name: 'especialidad', displayName: 'Especialidad', width: '16%' },
    //           { field: 'tipoproducto', name: 'nombre_tp', displayName: 'Tipo de Producto', width: '14%' },
    //           { field: 'producto', name: 'producto', displayName: 'Producto/Servicio', width: '20%' },
    //           { field: 'precio_unitario', name: 'precio_unitario', displayName: 'Precio Unit.', width: '10%' },
    //           { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '6%' },
    //           { field: 'descuento', name: 'descuento_asignado', displayName: 'Dscto.', width: '8%', cellClass: 'bg-lightblue' },
    //           { field: 'total_detalle', name: 'total_detalle', displayName: 'Importe', width: '10%', cellClass: 'bg-lightblue' }
    //         ],
    //         onRegisterApi: function(gridApi) { // gridComboOptions
    //           $scope.gridApi = gridApi;
    //           gridApi.selection.on.rowSelectionChanged($scope,function(row){
    //             $scope.mySelectionDetalleVentaGrid = gridApi.selection.getSelectedRows();
    //           });

    //           $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
    //             if (sortColumns.length == 0) {
    //               paginationOptionsDetalleVenta.sort = null;
    //               paginationOptionsDetalleVenta.sortName = null;
    //             } else {
    //               paginationOptionsDetalleVenta.sort = sortColumns[0].sort.direction;
    //               paginationOptionsDetalleVenta.sortName = sortColumns[0].name;
    //             }
    //             $scope.getPaginationDetalleVentaServerSide();
    //           });
    //           gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
    //             paginationOptionsDetalleVenta.pageNumber = newPage;
    //             paginationOptionsDetalleVenta.pageSize = pageSize;
    //             paginationOptionsDetalleVenta.firstRow = (paginationOptionsDetalleVenta.pageNumber - 1) * paginationOptionsDetalleVenta.pageSize;
    //             $scope.getPaginationDetalleVentaServerSide();
    //           });
    //         }
    //       };
    //       paginationOptionsDetalleVenta.sortName = $scope.gridOptionsDetalleVenta.columnDefs[0].name;
    //       $scope.getPaginationDetalleVentaServerSide = function() {
    //         //$scope.$parent.blockUI.start();
    //         $scope.datosGrid = {
    //           paginate: paginationOptionsDetalleVenta,
    //           datos: fVenta
    //         };
    //         //console.log($scope.mySelectionGridEE[0]);
    //         cajaActualServices.sListarDetalleVenta($scope.datosGrid).then(function (rpta) {
    //           $scope.gridOptionsDetalleVenta.totalItems = rpta.paginate.totalRows;
    //           $scope.gridOptionsDetalleVenta.data = rpta.datos;
    //           //$scope.$parent.blockUI.stop();
    //         });
    //         $scope.mySelectionDetalleVentaGrid = [];
    //       };
    //       $scope.getPaginationDetalleVentaServerSide();

    //       $scope.cancel = function () {
    //         $modalInstance.dismiss('cancel');
    //       }
    //     }
    //   });
    // }

    /* FIX TAB IN GRID */
    // $scope.reloadGrid = function () { 
    //   $interval( function() { 
    //       $scope.gridApi.core.handleWindowResize(); 
    //   }, 50, 5);
    // }
    // $scope.reloadGrid();
  }])
  .service("productosVendidosServices",function($http, $q) {
    return({
        sListarVentasHistorial: sListarVentasHistorial 
    });

    function sListarVentasHistorial(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProductosVendidos/lista_productos_vendidos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    
  });