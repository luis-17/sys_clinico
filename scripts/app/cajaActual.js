angular.module('theme.cajaActual', ['theme.core.services'])
  .controller('cajaActualController', ['$scope', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'cajaActualServices', 
    'empresaAdminServices',
    'cajaServices', 
    'ventaServices', 
    function($scope, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      cajaActualServices, 
      empresaAdminServices,
      cajaServices,
      ventaServices ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2"); 
    $scope.modulo = 'cajaActual';
    $scope.idmodulo = 1;
    $scope.cajaAbiertaPorMiSession = false;
    $scope.fCajaAbiertaSession = null;
    $scope.fBusqueda = {};
    $scope.fBusqueda.idmodulo = $scope.idmodulo;
    $scope.mySelectionGridV = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringVA = function(){ 
      $scope.gridOptionsVentasAnuladas.enableFiltering = !$scope.gridOptionsVentasAnuladas.enableFiltering;
      $scope.gridApiAnulado.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringEE = function(){
      $scope.gridOptionsVentasEnEspera.enableFiltering = !$scope.gridOptionsVentasEnEspera.enableFiltering;
      $scope.gridApiEnEspera.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringPV = function(){
      $scope.gridOptionsProductosVenta.enableFiltering = !$scope.gridOptionsProductosVenta.enableFiltering;
      $scope.gridApiProducto.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringRI = function(){ 
      $scope.gridOptionsVentasImpresion.enableFiltering = !$scope.gridOptionsVentasImpresion.enableFiltering;
      $scope.gridApiImpresionesVenta.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      //$scope.listaSedeEmpresaAdmin.splice(0,0,{ id : 'all', descripcion:'-- Todos --'});
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rptaDet) { 
        if(rptaDet.flag === 1){
          $scope.listaCajaMaster = rptaDet.datos;
        }
        if( rptaDet.flag === 1 && angular.isObject(rptaDet.cajaactual) ){ 
          $scope.fBusqueda.cajamaster = rptaDet.cajaactual.idcajamaster; 
          $scope.getPaginationServerSide(); // ventas 
          $scope.getPaginationVAServerSide(); // anuladas
          $scope.getPaginationEEServerSide(); // en espera 
          $scope.getPaginationPVServerSide();  // productos 
          $scope.getPaginationRIServerSide(); // impresiones
          $scope.cajaAbiertaPorMiSession = true;
          $scope.fCajaAbiertaSession = rptaDet.cajaactual;
          pinesNotifications.notify({ title: 'Información', text: 'Su caja está abierta.', type: 'success', delay: 4500 });
        }else{
          pinesNotifications.notify({ title: 'Información', text: 'Ud. no tiene ninguna caja abierta.', type: 'warning', delay: 4500 });
        }
      });
    });
    $scope.onChangeEmpresaSede = function () { 
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rpta) { 
        $scope.listaCajaMaster = rpta.datos;
        if( rpta.flag === 0 ){ 
          $scope.listaCajaMaster.push( { id: '', descripcion: 'No se encontraron cajas abiertas.' } );

        }
        $scope.fBusqueda.cajamaster = $scope.listaCajaMaster[0].id; 
        $scope.getPaginationServerSide(); // ventas 
        $scope.getPaginationVAServerSide(); // anuladas 
        $scope.getPaginationEEServerSide(); // en espera 
        $scope.getPaginationPVServerSide(); // productos 
        $scope.getPaginationRIServerSide(); // impresiones
      });
    }
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
        { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '18%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%', cellClass: 'bg-lightblue'},
        { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '9%' },
        { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '12%', enableFiltering: false, sort: { direction: uiGridConstants.DESC}  },
        { field: 'medio', name: 'descripcion_med', displayName: 'Medio de Pago', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SubTotal', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'Total', width: '7%', cellClass: 'bg-lightblue' }, 
        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+
            '<label tooltip-placement="left" tooltip="ATENDIDO" style="box-shadow: 1px 1px 0 black;" class="label label-info ml-xs">'+
            '<i ng-if="COL_FIELD.claseIconAtendido" class="fa {{ COL_FIELD.claseIconAtendido }}"></i> </label>'+
            '</div>' 
        } 
        // { 
        //   field: 'estado', 
        //   displayName: 'Estado', 
        //   width: '5%', 
        //   cellTemplate:'<label class="label label-success" style="margin: 7px;"> <i class="fa fa-check"></i> </label>', 
        //   cellClass:'text-center',
        //   enableColumnMenus: false, 
        //   enableColumnMenu: false,
        //   enableSorting: false,
        //   enableFiltering: false
        // }
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
            'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'sub_total' : grid.columns[8].filters[0].term,
            'total_igv' : grid.columns[9].filters[0].term,
            'total_a_pagar' : grid.columns[10].filters[0].term
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
    paginationOptions.sortName = $scope.gridOptions.columnDefs[5].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      cajaActualServices.sListarVentasCajaActual($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        $scope.gridOptions.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGridV = [];
    };
    $scope.btnSolicitudImprimirTicket = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          cajaActualServices.sEnviarSolicitudImpresion($scope.mySelectionGridV).then(function (rpta) { 
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
            $scope.getPaginationRIServerSide();
          }); 
        }
      });
    }
    $scope.btnImprimirTicket = function (fila) { 
      var pMensaje = '¿Realmente desea realizar la re-impresión?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaActualServices.sImprimirTicketVenta(fila).then(function (rpta) { 
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
            $scope.getPaginationRIServerSide();
          });
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      //console.log($scope.mySelectionGridV); 
      if( $scope.mySelectionGridV[0].estado.claseIconAtendido ){ 
        // console.log($scope.mySelectionGridV[0].estado.claseIconAtendido);
        pinesNotifications.notify({ title: 'Advertencia.', text: 'La venta ya ha sido atendida, no se puede anular. Contacte con el área de sistemas.', type: 'warning', delay: 3500 }); 
        return false;
      }
      // return false; 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaActualServices.sAnularVentaCajaActual($scope.mySelectionGridV[0]).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationVAServerSide();
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          }); 
        }
      });
    }
    $scope.abrirCaja = function () {
      if( $scope.fCajaAbiertaSession && $scope.cajaAbiertaPorMiSession === true){ 
        pinesNotifications.notify({ title: 'Error', text: 'Ud. ya tiene una caja abierta. Presione F5 para actualizar', type: 'danger', delay: 3000 });
      }else{ 
        $modal.open({ 
          templateUrl: angular.patchURLCI+'caja/ver_popup_abrir_caja',
          size: '',
          scope: $scope,
          controller: function ($scope, $modalInstance) { 
            $scope.fData = {};
            $scope.titleForm = 'Apertura de Caja';
            // CAJAS MASTER 
            $scope.listaCajaMaster = [];
            var arrParams = { 
                idmodulo: 1 // hospital 
            };
            cajaServices.sListarTodasCajasMasterCbo(arrParams).then(function (rpta) { 
              $scope.listaCajaMaster = rpta.datos;
              $scope.listaCajaMaster.splice(0,0,{ id : '', descripcion:'--Seleccione Caja--'});
              
              $scope.fData.idcajamaster = $scope.listaCajaMaster[0].id;
            });
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.fData = {};
            }
            $scope.aceptar = function () { 
              var pMensaje = '¿Realmente desea abrir caja ?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){
                  cajaActualServices.sAbrirCajaDeUsuarioSession($scope.fData).then(function (rpta) { 
                    if(rpta.flag == 1){ 
                      pTitle = 'OK!';
                      pType = 'success';
                      $modalInstance.dismiss('cancel');
                      $scope.$parent.reloadPage();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('No se pudo realizar la transacción.');
                    }
                    $scope.fData = {};
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              });
            }
          }
        }); 
      }
    }
    $scope.cerrarCaja = function () { 
      if( $scope.fCajaAbiertaSession && $scope.cajaAbiertaPorMiSession === true){ 
        var pMensaje = '¿Realmente desea cerrar la caja <strong> N° '+$scope.fCajaAbiertaSession.numero_caja+' </strong> ?';
        $bootbox.confirm(pMensaje, function(result) {
          if(result){
            cajaActualServices.sCerrarCajaDeUsuarioSession().then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $scope.$parent.reloadPage();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              // $scope.getPaginationServerSide(); // ventas 
              // $scope.getPaginationVAServerSide(); // anuladas
              // $scope.getPaginationEEServerSide(); // en espera 
              // $scope.getPaginationPVServerSide(); // productos 

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 6000 });
            }); 
          }
        });
      }else{
        pinesNotifications.notify({ title: 'Error', text: 'Ud. no tiene ninguna caja abierta. Presione F5 para actualizar', type: 'danger', delay: 3000 });
      }
    }
    /* GRID DE VENTAS ANULADAS*/
    var paginationOptionsVA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasAnuladas = {
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '18%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '9%' },
        { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '12%', enableFiltering: false  },
        { field: 'medio', name: 'descripcion_med', displayName: 'Medio de Pago', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SubTotal', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'Total', width: '7%', cellClass: 'bg-lightblue' },
        { 
          field: 'estado', 
          displayName: 'Estado', 
          width: '5%', 
          cellTemplate:'<label class="label label-danger" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-ban"></i> </label>', 
          cellClass:'text-center',
          enableColumnMenus: false, 
          enableColumnMenu: false,
          enableSorting: false,
          enableFiltering: false
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiAnulado = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiAnulado.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsVA.sort = null;
            paginationOptionsVA.sortName = null;
          } else {
            paginationOptionsVA.sort = sortColumns[0].sort.direction;
            paginationOptionsVA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationVAServerSide();
        });
        $scope.gridApiAnulado.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsVA.search = true; 
          paginationOptionsVA.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'sub_total' : grid.columns[8].filters[0].term,
            'total_igv' : grid.columns[9].filters[0].term,
            'total_a_pagar' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationVAServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsVA.pageNumber = newPage;
          paginationOptionsVA.pageSize = pageSize;
          paginationOptionsVA.firstRow = (paginationOptionsVA.pageNumber - 1) * paginationOptionsVA.pageSize;
          $scope.getPaginationVAServerSide();
        });
      }
    };
    paginationOptionsVA.sortName = $scope.gridOptionsVentasAnuladas.columnDefs[0].name;
    $scope.getPaginationVAServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptionsVA,
        datos : $scope.fBusqueda
      };
      cajaActualServices.sListarVentasAnuladosCajaActual($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsVentasAnuladas.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasAnuladas.data = rpta.datos;
        $scope.gridOptionsVentasAnuladas.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGrid = [];
    };

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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '18%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '9%' },
        { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '12%' },
        { field: 'medio', name: 'descripcion_med', displayName: 'Medio de Pago', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SubTotal', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'Total', width: '7%', cellClass: 'bg-lightblue' },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>' 
        }
        // { 
        //   field: 'estado', 
        //   displayName: 'Estado', 
        //   width: '5%', 
        //   cellTemplate:'<label class="label label-warning" style="margin: 7px;"> <i class="fa fa-spinner"></i> </label>', 
        //   cellClass:'text-center',
        //   enableColumnMenus: false, 
        //   enableColumnMenu: false,
        //   enableSorting: false
        // }
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
            'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'sub_total' : grid.columns[8].filters[0].term,
            'total_igv' : grid.columns[9].filters[0].term,
            'total_a_pagar' : grid.columns[10].filters[0].term
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
      $scope.datosGrid = {
        paginate : paginationOptionsEE,
        datos : $scope.fBusqueda
      };
      cajaActualServices.sListarVentasConDescuentoCajaActual($scope.datosGrid).then(function (rpta) {
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
          cajaActualServices.sAprobarVentaDescuento($scope.mySelectionGridEE).then(function (rpta) { 
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
            $scope.getPaginationServerSide();
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
              { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '14%' },
              { field: 'especialidad', name: 'especialidad', displayName: 'Especialidad', width: '16%' },
              { field: 'tipoproducto', name: 'nombre_tp', displayName: 'Tipo de Producto', width: '14%' },
              { field: 'producto', name: 'producto', displayName: 'Producto/Servicio', width: '20%' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'Precio Unit.', width: '10%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '6%' },
              { field: 'descuento', name: 'descuento_asignado', displayName: 'Dscto.', width: '8%', cellClass: 'bg-lightblue' },
              { field: 'total_detalle', name: 'total_detalle', displayName: 'Importe', width: '10%', cellClass: 'bg-lightblue' }
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
            //console.log($scope.mySelectionGridEE[0]);
            cajaActualServices.sListarDetalleVenta($scope.datosGrid).then(function (rpta) {
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
      /* GRID DE VENTAS IMPRESION */
    $scope.mySelectionGridRI = [];
    var paginationOptionsRI = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%' },
        { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '18%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '9%' },
        { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '12%',  sort: { direction: uiGridConstants.DESC} },
        { field: 'medio', name: 'descripcion_med', displayName: 'Medio de Pago', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SubTotal', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'Total', width: '7%', cellClass: 'bg-lightblue' },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiImpresionesVenta = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridRI = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridRI = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiImpresionesVenta.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsRI.sort = null;
            paginationOptionsRI.sortName = null;
          } else {
            paginationOptionsRI.sort = sortColumns[0].sort.direction;
            paginationOptionsRI.sortName = sortColumns[0].name;
          }
          $scope.getPaginationRIServerSide();
        });
        $scope.gridApiImpresionesVenta.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsRI.search = true; 
          paginationOptionsRI.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'sub_total' : grid.columns[8].filters[0].term,
            'total_igv' : grid.columns[9].filters[0].term,
            'total_a_pagar' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationRIServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsRI.pageNumber = newPage;
          paginationOptionsRI.pageSize = pageSize;
          paginationOptionsRI.firstRow = (paginationOptionsRI.pageNumber - 1) * paginationOptionsRI.pageSize;
          $scope.getPaginationRIServerSide();
        });
      }
    };
    paginationOptionsRI.sortName = $scope.gridOptionsVentasImpresion.columnDefs[5].name;
    $scope.getPaginationRIServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptionsRI,
        datos : $scope.fBusqueda
      };
      cajaActualServices.sListarVentasConSolicitudImpresionCajaActual($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsVentasImpresion.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasImpresion.data = rpta.datos;
      });
      $scope.mySelectionGridRI = [];
    };

    /* GRID DE PRODUCTOS/SERVICIOS DE LA VENTA */
    $scope.mySelectionGridPV = [];
    var paginationOptionsPV = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsProductosVenta = { 
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '8%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '16%', visible: false },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%', visible: false },
        { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '9%', visible: false },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '10%' },
        // { field: 'sede', name: 'sede', displayName: 'Sede', width: '15%', visible: false },
        { field: 'tipoproducto', name: 'nombre_tp', displayName: 'Tipo de Producto', width: '14%' },
        { field: 'especialidad', name: 'especialidad', displayName: 'Especialidad', width: '14%' },
        { field: 'producto', name: 'producto', displayName: 'Producto/Servicio', width: '24%' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'Precio Unit.', width: '8%' },
        { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '4%' },
        { field: 'descuento', name: 'descuento_asignado', displayName: 'Dscto.', width: '5%' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'Importe', width: '7%' },
        { 
          field: 'estado', 
          displayName: 'Estado', 
          width: '5%', 
          cellTemplate:'<label class="label label-info" style="margin: 7px;"> <i class="fa fa-star"></i> </label>', 
          cellClass:'text-center',
          enableColumnMenus: false, 
          enableColumnMenu: false,
          enableSorting: false
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiProducto = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridPV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridPV = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiProducto.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsPV.sort = null;
            paginationOptionsPV.sortName = null;
          } else {
            paginationOptionsPV.sort = sortColumns[0].sort.direction;
            paginationOptionsPV.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPVServerSide();
        });
        $scope.gridApiProducto.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsPV.search = true; 
          paginationOptionsPV.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'nombre_tp' : grid.columns[6].filters[0].term,
            // 'emp.descripcion' : grid.columns[7].filters[0].term,
            'esp.nombre' : grid.columns[7].filters[0].term,
            'pm.descripcion' : grid.columns[8].filters[0].term,
            'precio_unitario' : grid.columns[9].filters[0].term,
            'cantidad' : grid.columns[10].filters[0].term,
            'descuento_asignado' : grid.columns[11].filters[0].term,
            'total_detalle' : grid.columns[12].filters[0].term
          }
          $scope.getPaginationPVServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPV.pageNumber = newPage;
          paginationOptionsPV.pageSize = pageSize;
          paginationOptionsPV.firstRow = (paginationOptionsPV.pageNumber - 1) * paginationOptionsPV.pageSize;
          $scope.getPaginationPVServerSide();
        });
      }
    };
    paginationOptionsPV.sortName = $scope.gridOptionsProductosVenta.columnDefs[0].name;
    $scope.getPaginationPVServerSide = function() { // console.log('PV');
      $scope.datosGrid = {
        paginate : paginationOptionsPV,
        datos : $scope.fBusqueda
      };
      cajaActualServices.sListarProductosPorVenta($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsProductosVenta.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsProductosVenta.data = rpta.datos;
      });
      $scope.mySelectionGridPV = [];
    };

    /* FIX TAB IN GRID */
    $scope.reloadGrid = function () { console.log('reloadGrid med');
      $interval( function() { 
          $scope.gridApi.core.handleWindowResize();
          $scope.gridApiAnulado.core.handleWindowResize();
          $scope.gridApiEnEspera.core.handleWindowResize();
          $scope.gridApiProducto.core.handleWindowResize();
          $scope.gridApiImpresionesVenta.core.handleWindowResize();
      }, 50, 5);
    }
    $interval( function() {
          $scope.gridApi.core.handleWindowResize();
    }, 10, 500);
    
  }])
  .service("cajaActualServices",function($http, $q) {
    return({
        sListarVentasCajaActual: sListarVentasCajaActual, 
        sListarDetalleVenta: sListarDetalleVenta,
        sListarVentasAnuladosCajaActual: sListarVentasAnuladosCajaActual,
        sListarVentasConDescuentoCajaActual: sListarVentasConDescuentoCajaActual, 
        sListarVentasConSolicitudImpresionCajaActual: sListarVentasConSolicitudImpresionCajaActual, 
        sListarProductosPorVenta: sListarProductosPorVenta, 
        sEnviarSolicitudImpresion: sEnviarSolicitudImpresion, 
        sAprobarSolicitudImpresion: sAprobarSolicitudImpresion,
        sAprobarVentaDescuento: sAprobarVentaDescuento,
        sAnularVentaCajaActual: sAnularVentaCajaActual,
        sImprimirTicketVenta: sImprimirTicketVenta,
        sCerrarCajaDeUsuarioSession: sCerrarCajaDeUsuarioSession,
        sAbrirCajaDeUsuarioSession: sAbrirCajaDeUsuarioSession,
        sDesprobarVentaDescuento: sDesprobarVentaDescuento
    });

    function sListarVentasCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/lista_ventas_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleVenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/lista_detalle_venta_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasConDescuentoCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/lista_ventas_con_descuento_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasConSolicitudImpresionCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/lista_ventas_con_solicitud_impresion_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasAnuladosCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/lista_ventas_anulados_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosPorVenta (datos) {
      var request = $http({ 
            method : "post", 
            url : angular.patchURLCI+"venta/lista_productos_venta_caja_actual", 
            data : datos 
      }); 
      return (request.then( handleSuccess,handleError )); 
    }
    function sEnviarSolicitudImpresion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/enviar_solicitud_reimpresion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAprobarSolicitudImpresion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/aprobar_solicitud_reimpresion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAprobarVentaDescuento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/aprobar_venta_descuento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularVentaCajaActual (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/anular_venta_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirTicketVenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/imprimir_ticket_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCerrarCajaDeUsuarioSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/cerrar_caja_usuario_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAbrirCajaDeUsuarioSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/abrir_caja_usuario_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDesprobarVentaDescuento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/desaprobar_venta_descuento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });