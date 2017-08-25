angular.module('theme.resumenVenta', ['theme.core.services'])
  .controller('resumenVentaController', ['$scope', '$filter', '$sce', '$modal', '$interval', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ModalReporteFactory', 
    'resumenVentaServices', 
    'empresaAdminServices', 
    'cajaServices', 
    'cajaActualServices', 
    'tipoDocumentoServices',
    'ventaFarmaciaServices',
    function($scope, $filter, $sce, $modal, $interval, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ModalReporteFactory,
      resumenVentaServices, 
      empresaAdminServices,
      cajaServices,
      cajaActualServices,
      tipoDocumentoServices,
      ventaFarmaciaServices ){ 
    'use strict';
    shortcut.remove("F2"); 
    $scope.modulo = 'resumenVentas'; 
    $scope.idmodulo = 3;
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.fBusqueda = {};
    $scope.fBusqueda.idmodulo = $scope.idmodulo;
    $scope.mySelectionGrid = [];
    $scope.listaTipoDocumento = []; 
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    //$scope.fBusqueda.tipodocumento = ['6','3','2','1'];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsRV.enableFiltering = !$scope.gridOptionsRV.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsRV.data[rowIndex], $scope.gridOptionsRV.columnDefs[colIndex]);
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
    $scope.gridOptionsRV = {
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
    paginationOptions.sortName = $scope.gridOptionsRV.columnDefs[0].name;
    $scope.getPaginationServerSide = function() { 
      var arrParams = { 
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      resumenVentaServices.sListarAperturaCajas(arrParams).then(function (rpta) {
        $scope.gridOptionsRV.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsRV.data = rpta.datos;
        $scope.gridOptionsRV.sumCantNC = rpta.sumCantNC;
        $scope.gridOptionsRV.sumCantA = rpta.sumCantA;
        $scope.gridOptionsRV.sumCantV = rpta.sumCantV;
        $scope.gridOptionsRV.sumTotalV = rpta.sumTotalV;

        $scope.gridOptionsTXTD.data = rpta.datosTXTD;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide(); // agregado para que cargue datos al entrar a la vista sin tener que darle al boton PROCESAR.
     // el combo de sedeempresaadmin se carga despues del getPaginationServerSide, por eso en el modelo de caja_farmacia se agregé la condicion
     // si no existe la variable sedeempresaadmin que lo pille de la sesion, que viene a ser lo mismo que se hace aqui en el js, en:
     // $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin de la lista del combo.
    $scope.exportarLiquidacion = function (tipoExport) {
      if (tipoExport == 'csv') { 
        var myElement = angular.element(document.querySelectorAll(".custom-csv-link-location"));
        $scope.gridApi.exporter.csvExport( 'all', 'all', myElement );
      } else if (tipoExport == 'pdf') { 
        $scope.fBusqueda.titulo = 'RESUMEN DE CAJAS';
        var arrParams = {
          titulo: $scope.fBusqueda.titulo,
          url: angular.patchURLCI+'CentralReportes/report_resumen_cajas',
          datos: $scope.fBusqueda
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
            resumenVentaServices.sListarCajasPorTipoDoc($scope.mySelectionGrid[0]).then(function (rpta) {
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
      // console.log(rowEntity,$scope.mySelectionGrid[0]); 
      if( angular.isUndefined(rowEntity) ) { 
        rowEntity = $scope.mySelectionGrid[0];
      }
      $modal.open({ 
        templateUrl: angular.patchURLCI+'caja/ver_popup_detalle_caja_farm',
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
              { field: 'cliente', name: 'cliente', displayName: 'CLIENTE', width: '16%' },
              { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%' },
              { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '11%' },
              { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%', enableFiltering: false  },
              { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
              { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '9%' },
              { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '9%' },
              { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '9%', cellClass: 'bg-lightblue' },
              { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', cellClass:'text-center', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
                cellTemplate:'<div class="">'+
                  '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
                  '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+
                  '</div>' 
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
                  'descripcion_med' : grid.columns[6].filters[0].term,
                  'sub_total' : grid.columns[7].filters[0].term,
                  'total_igv' : grid.columns[8].filters[0].term,
                  'total_a_pagar' : grid.columns[9].filters[0].term
                }
                $scope.getPaginationDetalleVentaServerSide();
              });
            }
          };
          paginationDetalleVentaOptions.sortName = $scope.gridOptionsDetalleCaja.columnDefs[0].name;
          $scope.getPaginationDetalleVentaServerSide = function() { 
            var arrParams = {
              paginate : paginationDetalleVentaOptions, 
              datos : rowEntity 
            };
            resumenVentaServices.sListarDetalleAperturaCajas(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleCaja.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleCaja.data = rpta.datos;
              $scope.gridOptionsDetalleCaja.sumTotalV = rpta.sumTotalV;
              $scope.gridOptionsDetalleCaja.sumCantA = rpta.sumCantA;
              $scope.gridOptionsDetalleCaja.sumCantNC = rpta.sumCantNC;
              $scope.gridOptionsDetalleCaja.sumTotalNC = rpta.sumTotalNC;
              $scope.gridOptionsDetalleCaja.sumCantV = rpta.sumCantV;
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
                    if(rpta.flag == 3) { // FALTA APROBAR, ESTÁ EN ESPERA.
                      var pTitle = 'Advertencia';
                      var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
                      var pType = 'warning';
                    }
                    pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 }); 
                  }
                  //$scope.getPaginationRIServerSide();
                });
              }
            });
          }
          // $scope.btnSolicitudImprimirTicket = function (fila) { 
          //   var pMensaje = '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
          //   $bootbox.confirm(pMensaje, function(result) { 
          //     if(result){
          //       cajaActualServices.sEnviarSolicitudImpresion(fila).then(function (rpta) { 
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
          //         //$scope.getPaginationRIServerSide();
          //       }); 
          //     }
          //   });
          // }
        }
      });
    }
    $scope.reloadGrid = function () { // console.log('click med');
      $interval( function() { 
          $scope.gridApi.core.handleWindowResize();
          $scope.gridApiTXTD.core.handleWindowResize();
      }, 50, 5);
    }
    $scope.reloadGrid();

    $scope.btnExportarListaPdf = function (){
      $scope.fBusqueda.titulo = 'RESUMEN VENTAS FARMACIA';
      $scope.fBusqueda.tituloAbv = 'RVF';
      $scope.fBusqueda.salida = 'pdf';
      var arrParams = {
        titulo: $scope.fBusqueda.titulo,
        datos: $scope.fBusqueda,
        url: angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_resumen_ventas',
        metodo: 'php'
      }

      ModalReporteFactory.getPopupReporte(arrParams);
    }
  }])
  .service("resumenVentaServices",function($http, $q) {
    return({
        sListarAperturaCajas: sListarAperturaCajas,
        sListarDetalleAperturaCajas: sListarDetalleAperturaCajas,
        sListarCajasPorTipoDoc: sListarCajasPorTipoDoc
    });

    function sListarAperturaCajas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_apertura_cajas_farm", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleAperturaCajas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_detalle_apertura_cajas_farm", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCajasPorTipoDoc (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_apertura_caja_tipo_doc_farm", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });