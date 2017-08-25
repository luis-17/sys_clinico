angular.module('theme.liquidacion', ['theme.core.services'])
  .controller('liquidacionController', ['$scope', '$filter', '$sce', '$modal', '$interval', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ModalReporteFactory', 
    'liquidacionServices', 
    'empresaAdminServices', 
    'cajaServices', 
    'cajaActualServices', 
    'tipoDocumentoServices', 
    function($scope, $filter, $sce, $modal, $interval, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ModalReporteFactory,
      liquidacionServices, 
      empresaAdminServices,
      cajaServices,
      cajaActualServices,
      tipoDocumentoServices ){ 
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'liquidacion';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.fBusqueda = {};
    $scope.mySelectionGrid = [];
    $scope.listaTipoDocumento = [];
    // $scope.fBusqueda.desde = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
    // var f=moment().format('YYYY-MM-DD'); 
    // f=moment().subtract('days',30); 
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.tipodocumento = ['6','3','2','1'];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsLiquid.enableFiltering = !$scope.gridOptionsLiquid.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsLiquid.data[rowIndex], $scope.gridOptionsLiquid.columnDefs[colIndex]);
    };

    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { 
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      //$scope.listaSedeEmpresaAdmin.splice(0,0,{ id : 'all', descripcion:'-- Todos --'});
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rptaDet) { 
        if(rptaDet.flag === 1){
          $scope.listaCajaMaster = rptaDet.datos;
        }
        if( rptaDet.flag === 1 && angular.isObject(rptaDet.cajaactual) ) {
          $scope.fBusqueda.cajamaster = rptaDet.cajaactual.idcajamaster; 
          $scope.getPaginationServerSide();
        }
        
        
      });
    });
    // TIPO DOCUMENTOS 
    tipoDocumentoServices.sListarTipoDocumentoVentaCbo().then(function (rpta) { 
      if( rpta.flag == 1 ){ 
        $scope.listaTipoDocumento = rpta.datos;
      }
      
    });
    $scope.gridOptionsLiquid = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      minRowsToShow: 5,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : false, // se verá después
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'fecha_apertura', name: 'fecha_apertura', displayName: 'FECHA', width: '18%',  sort: { direction: uiGridConstants.DESC}, enableFiltering: false }, 
        { field: 'numero_caja', name: 'numero_caja', displayName: 'CAJA', width: '10%' },
        { field: 'usuario', name: 'usuario', displayName: 'CAJERO', width: '14%'},
        { field: 'cantidad_venta', name: 'cantidad_venta', displayName: 'N° EMITIDOS', width: '11%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'cantidad_anulado', name: 'cantidad_anulado', displayName: 'N° ANULADOS', width: '11%', type:'number', cellClass:'text-center', 
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleAnulados(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'cantidad_nota_credito', name: 'cantidad_nota_credito', displayName: 'N° N.C.R.', width: '11%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleNC(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'suma_nota_credito', name: 'suma_nota_credito', displayName: 'TOTAL N.C.R.', width: '8%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleNC(row.entity);" > <strong class="m-pen"> {{ COL_FIELD }} </strong> </a>' },
        { field: 'total_venta', name: 'total_venta', displayName: 'TOTAL VENTAS', width: '12%', type:'number', cellClass:'text-center', 
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black; text-decoration: underline;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong class="m-pen"> {{ COL_FIELD }} </strong> </a>' }
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
        // $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { // ESTO SE VERÁ DESPUES 
        //   var grid = this.grid;
        //   paginationOptions.search = true; 
        //   paginationOptions.searchColumn = { 
        //     'orden_venta' : grid.columns[1].filters[0].term,
        //     'CONCAT(c.nombres," ",c.apellido_paterno," ",c.apellido_materno)' : grid.columns[2].filters[0].term,
        //     'descripcion_td' : grid.columns[3].filters[0].term,
        //     'ticket_venta' : grid.columns[4].filters[0].term,
        //     'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
        //     'esp.nombre' : grid.columns[6].filters[0].term
        //   }
        //   $scope.getPaginationServerSide();
        // });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptionsLiquid.columnDefs[0].name;
    $scope.getPaginationServerSide = function() { 
      $scope.datosGrid = { 
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      liquidacionServices.sListarAperturaCajas($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsLiquid.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsLiquid.data = rpta.datos;
        $scope.gridOptionsLiquid.sumCantNC = rpta.sumCantNC;
        $scope.gridOptionsLiquid.sumCantA = rpta.sumCantA;
        $scope.gridOptionsLiquid.sumCantV = rpta.sumCantV;
        $scope.gridOptionsLiquid.sumTotalV = rpta.sumTotalV;

        $scope.gridOptionsTXTD.data = rpta.datosTXTD;
      });
      $scope.mySelectionGrid = [];
      /* TOTALIZADO POR TIPO DOC */ 
      // liquidacionServices.sListarCajasPorTipoDoc($scope.fBusqueda).then(function (rpta) {
      //   //$scope.gridOptionsTXTD.totalItems = rpta.paginate.totalRows;
      //   $scope.gridOptionsTXTD.data = rpta.datos;
      // });
    };
    $scope.btnConsultar = function () { 
      $scope.getPaginationServerSide();
    }
    $scope.exportarLiquidacion = function (tipoExport) {
      if (tipoExport == 'csv') { 
        var myElement = angular.element(document.querySelectorAll(".custom-csv-link-location"));
        $scope.gridApi.exporter.csvExport( 'all', 'all', myElement );
      } else if (tipoExport == 'pdf') { 
        $scope.fBusqueda.titulo = 'RESUMEN DE CAJAS';
        var arrParams = {
          titulo: $scope.fBusqueda.titulo,
          url: angular.patchURLCI+'CentralReportes/report_resumen_cajas',
          datos: $scope.fBusqueda,
          salida: 'js'
        }
        ModalReporteFactory.getPopupReporte(arrParams); 
      };
    }
    $scope.verDetalleTotalizadoTipoDocEnPopUp = function () {
      $modal.open({ 
        templateUrl: angular.patchURLCI+'caja/ver_popup_detalle_caja_por_tipo_documento',
        size: 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance ) { 
          /* DATA GRID */
          // var paginationDetalleVentaOptions = {
          //   pageNumber: 1,
          //   firstRow: 0,
          //   pageSize: 100,
          //   sort: uiGridConstants.DESC,
          //   sortName: null,
          //   search: null
          // };
          //$scope.mySelectionDetalleVentaGrid = [];
          /* TOTALIZADO POR TIPO DE DOCUMENTO EN MODAL*/
          $scope.gridOptionsPopUpTXTD = {
            paginationPageSizes: [100, 500, 1000],
            minRowsToShow: 5,
            paginationPageSize: 100,
            enableGridMenu: true,
            enableRowSelection: true,
            multiSelect: true,
            columnDefs: [ 
              // { field: 'fecha_apertura', name: 'fecha_apertura', displayName: 'FECHA', width: '18%', enableFiltering: false }, 
              // { field: 'numero_caja', name: 'numero_caja', displayName: 'CAJA', enableFiltering: false },
              // { field: 'usuario', name: 'usuario', displayName: 'CAJERO', width: '14%'},
              { field: 'tipo_documento', name: 'tipo_documento', displayName: 'TIPO DE DOC.', enableFiltering: false }, 
              { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '12%', type:'number', cellClass:'text-center', 
                cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' }, 
              { field: 'total', name: 'total', displayName: 'TOTAL', width: '12%', type:'number', cellClass:'text-center', 
                cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong class="m-pen"> {{ COL_FIELD }} </strong> </a>' }
            ], 
            onRegisterApi: function(gridApiPopUpTXTD) { 
              $scope.gridApiPopUpTXTD = gridApiPopUpTXTD;
            }
          }

          //$scope.fCabecera = rowEntity;
          $scope.titleFormDetalleTD = 'Ver Totalizado por Tipo de Documento';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel'); 
          }
          // $scope.datosGrid = { 
          //   paginate : paginationOptions,
          //   datos : $scope.fBusqueda
          // };
          $scope.getPaginationTXTDServerSide = function() { 
            // $scope.fBusqueda.
            liquidacionServices.sListarCajasPorTipoDoc($scope.mySelectionGrid[0]).then(function (rpta) {
              $scope.gridOptionsPopUpTXTD.data = rpta.datos;
            });
          }
          $scope.getPaginationTXTDServerSide();
        }
      });
    }

    /* TOTALIZADO POR TIPO DE DOCUMENTO */
    $scope.gridOptionsTXTD = {
      paginationPageSizes: [100, 500, 1000],
      paginationPageSize: 100,
      enableGridMenu: true,
      enableRowSelection: true,
      multiSelect: true,
      columnDefs: [ 
        { field: 'fecha_apertura', name: 'fecha_apertura', displayName: 'FECHA', width: '18%', enableFiltering: false }, 
        { field: 'numero_caja', name: 'numero_caja', displayName: 'CAJA', enableFiltering: false },
        { field: 'usuario', name: 'usuario', displayName: 'CAJERO', width: '14%'},
        { field: 'tipo_documento', name: 'tipo_documento', displayName: 'TIPO DE DOC.', enableFiltering: false }, 
        { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '18%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' }, 
        { field: 'total', name: 'total', displayName: 'TOTAL', width: '18%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong class="m-pen"> {{ COL_FIELD }} </strong> </a>' }
      ], 
      onRegisterApi: function(gridApiTXTD) { 
        $scope.gridApiTXTD = gridApiTXTD;
        
      }
    }
    $scope.verDetalleVentas = function (rowEntity) { 
      // console.log(row);  fCabecera
      $modal.open({ 
        templateUrl: angular.patchURLCI+'caja/ver_popup_detalle_caja',
        size: 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance ) { 
          /* DATA GRID */
          $scope.btnToggleFiltering = function(){
            $scope.gridOptionsDetalleCaja.enableFiltering = !$scope.gridOptionsDetalleCaja.enableFiltering;
            $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
          };
          var paginationDetalleVentaOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 100,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleVentaGrid = [];
          $scope.gridOptionsDetalleCaja = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 100,
            useExternalPagination: true,
            minRowsToShow: 6,
            useExternalSorting: true,
            useExternalFiltering: true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: true,
            columnDefs: [
              { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '13%',  sort: { direction: uiGridConstants.ASC} },
              { field: 'cliente', name: 'cliente', displayName: 'Cliente', width: '16%' },
              { field: 'tipodocumento', name: 'descripcion_td', displayName: 'Tipo Doc.', width: '9%' },
              { field: 'ticket', name: 'ticket_venta', displayName: 'Ticket', width: '11%' },
              { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%', visible: false },
              { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '12%', enableFiltering: false  },
              { field: 'medio', name: 'descripcion_med', displayName: 'Medio de Pago', width: '10%', visible: false },
              { field: 'subtotal', name: 'sub_total', displayName: 'SubTotal', width: '9%' },
              { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '9%' },
              { field: 'total', name: 'total_a_pagar', displayName: 'Total', width: '9%', cellClass: 'bg-lightblue' },
              { 
                field: 'estado', 
                displayName: 'Estado', 
                width: '5%', 
                cellTemplate:'<label class="label label-success" style="margin: 7px;"> <i class="fa fa-check"></i> </label>', 
                cellClass:'text-center',
                enableColumnMenus: false, 
                enableColumnMenu: false,
                enableSorting: false,
                enableFiltering: false
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDetalleVentaGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionDetalleVentaGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationDetalleVentaOptions.sort = null;
                  paginationDetalleVentaOptions.sortName = null;
                } else {
                  paginationDetalleVentaOptions.sort = sortColumns[0].sort.direction;
                  paginationDetalleVentaOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleVentaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationDetalleVentaOptions.pageNumber = newPage;
                paginationDetalleVentaOptions.pageSize = pageSize;
                paginationDetalleVentaOptions.firstRow = (paginationDetalleVentaOptions.pageNumber - 1) * paginationDetalleVentaOptions.pageSize;
                $scope.getPaginationDetalleVentaServerSide();
              }); 
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { // ESTO SE VERÁ DESPUES 
                var grid = this.grid;
                paginationDetalleVentaOptions.search = true; 
                paginationDetalleVentaOptions.searchColumn = { 
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
                $scope.getPaginationDetalleVentaServerSide();
              });
            }
          };
          paginationDetalleVentaOptions.sortName = $scope.gridOptionsDetalleCaja.columnDefs[0].name;
          $scope.getPaginationDetalleVentaServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationDetalleVentaOptions, 
              datos : rowEntity 
            };
            liquidacionServices.sListarDetalleAperturaCajas($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalleCaja.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleCaja.data = rpta.datos;
              $scope.gridOptionsDetalleCaja.sumTotalV = rpta.sumTotalV;
            });
            $scope.mySelectionDetalleVentaGrid = [];
          };
          $scope.getPaginationDetalleVentaServerSide();

          $scope.fCabecera = rowEntity;
          $scope.titleFormDetalle = 'Ver Detalle de Caja';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel'); 
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
                  // $scope.getPaginationRIServerSide();
                });
              }
            });
          }
          $scope.btnSolicitudImprimirTicket = function (fila) { 
            var pMensaje = '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
            $bootbox.confirm(pMensaje, function(result) { 
              if(result){
                cajaActualServices.sEnviarSolicitudImpresion(fila).then(function (rpta) { 
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
                  //$scope.getPaginationRIServerSide();
                }); 
              }
            });
          }
        }
      });
    }
    $scope.verMasFiltros = function () { 
      if($scope.contMasFiltros){
        $scope.contMasFiltros = false;
      }else{
        $scope.contMasFiltros = true;
      }
    }
    $scope.reloadGrid = function () { // console.log('click med');
      $interval( function() { 
          $scope.gridApi.core.handleWindowResize();
          $scope.gridApiTXTD.core.handleWindowResize();
      }, 50, 5);
    }
    $scope.reloadGrid(); 
  }])
  .service("liquidacionServices",function($http, $q) {
    return({
        sListarAperturaCajas: sListarAperturaCajas,
        sListarDetalleAperturaCajas: sListarDetalleAperturaCajas,
        sListarCajasPorTipoDoc: sListarCajasPorTipoDoc
    });

    function sListarAperturaCajas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_apertura_cajas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleAperturaCajas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_detalle_apertura_cajas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCajasPorTipoDoc (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_apertura_caja_tipo_doc", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });