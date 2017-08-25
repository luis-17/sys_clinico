angular.module('theme.seguimientoOrden', ['theme.core.services'])
  .controller('seguimientoOrdenController', ['$scope','$uibModal','blockUI', '$filter', '$route', '$sce', '$interval', '$modal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox', '$controller',
    'seguimientoOrdenServices',
    'ordenCompraServices',
    'almacenFarmServices',
    'areasOrdenCompraServices',
    'ModalReporteFactory',
    function($scope, $uibModal, blockUI, $filter, $sce, $route, $interval, $modal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      $bootbox, $controller,
      seguimientoOrdenServices,
      ordenCompraServices,
      almacenFarmServices,
      areasOrdenCompraServices,
      ModalReporteFactory ){ 
    'use strict';
    
    shortcut.remove("F2");
    $scope.fData = {};
    // $controller('proveedorFarmaciaController', { 
    //   $scope : $scope
    // });
    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    $scope.modulo = 'seguimientoOrden';
    $scope.metodos = {};
    $scope.fBusqueda.almacen = {};
    var hoy = new Date();
    var desde = hoy - 1209600000; // restamos 14 dias
    //var desde = hoy - 1296000000; // restamos 15 dias
    $scope.fBusqueda.desde = $filter('date')(desde,'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(hoy,'dd-MM-yyyy');
    $scope.mySelectionGridOC = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsOC.enableFiltering = !$scope.gridOptionsOC.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTAR ALMACENES
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { //console.log(rpta);
      $scope.listaAlmacenes = rpta.datos;
      $scope.fBusqueda.almacen = $scope.listaAlmacenes[0];
    });
    // LISTAR AREAS INTERES 
    areasOrdenCompraServices.sListarAreasCbo().then(function (rpta) { //console.log(rpta);
      $scope.metodos.listaAreasOC = rpta.datos;
      $scope.metodos.listaAreasOC.splice(0,0,{ id : 'none', descripcion:'--Seleccione--'});
    });

    /* PERMISOS CON LA ORDEN DE COMPRA */
    $scope.permisoAprobado = true;
    
    $scope.permisoRechazado = false;
    if( $scope.fSessionCI.key_group === 'key_sistemas' || /* sistemas */
      $scope.fSessionCI.key_group === 'key_admin_far' || /*vladi*/
      $scope.fSessionCI.key_group === 'key_gerencia' /* lic ruby*/  ){ 
      $scope.permisoRechazado = true;
    }
    $scope.permisoObservado = false;
    if( $scope.fSessionCI.key_group === 'key_sistemas' || /*sistemas */
        $scope.fSessionCI.key_group === 'key_admin_far' || /*vladi */
        $scope.fSessionCI.key_group === 'key_gerencia' /*lic ruby*/  ){ 
      $scope.permisoObservado = true;
    }
    // $scope.permisoAnulado = true;
    // if( $scope.fSessionCI.key_group === 'key_sistemas' || /*sistemas */
    //     $scope.fSessionCI.key_group === 'key_logistica' /*Marino */ ){ 
    //   $scope.permisoAnulado = true;
    // }
    // LISTA FORMA DE PAGO
    $scope.listaFormaPago = [
      {'id' : 1, 'descripcion' : 'AL CONTADO'},
      {'id' : 2, 'descripcion' : 'CREDITO'},
      {'id' : 3, 'descripcion' : 'LETRAS'}
    ];
    $scope.listaMoneda = [
      {'id' : 1, 'descripcion' : 'S/.'},
      {'id' : 2, 'descripcion' : 'US$'}
    ];
    // $scope.tienePermiso = false;
    // if( $scope.fSessionCI.key_group === 'key_logistica' || $scope.fSessionCI.key_group === 'key_sistemas' ){ 
    //   $scope.tienePermiso = true;
    // }
    // $scope.tienePermisoAprobacion = false;
    // if( $scope.fSessionCI.key_group === 'key_gerencia' || $scope.fSessionCI.key_group === 'key_sistemas' ){ 
    //   $scope.tienePermisoAprobacion = true;
    // }
    /* GRILLA PRINCIPAL */
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    }; 
    // $scope.columnsDynamic = ; 

    $scope.gridOptionsOC = { 
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: false,
      multiSelect: false,
      columnDefs: [ 
        { field: 'orden_compra', name: 'fm.orden_compra', displayName: 'Nº ORDEN', minWidth: 60},
        { field: 'razon_social', name: 'razon_social', displayName: 'PROVEEDOR', minWidth: 280 },
        { field: 'fecha_movimiento', name: 'fm.fecha_movimiento', displayName: 'FECHA DE CREACION', enableFiltering: false, sort: { direction: uiGridConstants.DESC}, minWidth: 160, cellTooltip: 'Fecha de Creación de la Orden' },
        { field: 'fecha_aprobacion', name: 'fm.fecha_aprobacion', displayName: 'FECHA DE APROBACION', enableFiltering: false, minWidth: 160, cellTooltip: 'Fecha de Aprobación(FINANZAS)' },
        { field: 'fecha_entrega', name: 'fm.fecha_entrega', displayName: 'FECHA ING. ESTIMADA', enableFiltering: false, minWidth: 160, cellTooltip: 'Fecha de Ingreso(Estimado)', visible:false},
        // { field: 'fecha_entrega_real', name: 'fmc.fecha_movimiento', displayName: 'FECHA ING. REAL', enableFiltering: false, minWidth: 160, visible:false},
        { field: 'subtotal', name: 'fm.sub_total', displayName: 'SUB TOTAL', enableFiltering: false, cellClass:'text-right', minWidth: 100, visible:false},
        { field: 'igv', name: 'fm.total_igv', displayName: 'IGV', enableFiltering: false, cellClass:'text-right', minWidth: 100, visible:false},
        { field: 'total', name: 'fm.total_a_pagar', displayName: 'TOTAL', enableFiltering: false,cellClass:'text-right', minWidth: 100}
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridOC = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridOC = gridApi.selection.getSelectedRows();
        });
        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          //$scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'fm.orden_compra' : grid.columns[1].filters[0].term,
            'razon_social' : grid.columns[2].filters[0].term
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
    paginationOptions.sortName = $scope.gridOptionsOC.columnDefs[2].name;
    // LISTAR AREAS: 
    areasOrdenCompraServices.sListarAreasOrdenCompra().then(function (rpta) { 
      angular.forEach(rpta.datos,function (val,key) { 
        $scope.gridOptionsOC.columnDefs.push({ 
            field: val.field, 
            name: val.id, 
            displayName: val.descripcion, 
            type: 'object', 
            minWidth: 120, 
            enableFiltering: false, 
            enableSorting: false,
            headerCellClass: 'column-spanning',
            cellTemplate: '<div class="ui-grid-cell-contents text-center"><a ng-if="COL_FIELD.strHtml" class="btn btn-default btn-xs" tooltip-placement="left" tooltip="{{COL_FIELD.estado}}" style="font-size: 20px;padding: 0; width: 60px;height: 24px;" ng-click="grid.appScope.verDetalleEstado(COL_FIELD,row);" href="" ng-bind-html="COL_FIELD.strHtml"> </a> </div>'
        }); 
      });
      $scope.gridOptionsOC.columnDefs.push({
            field: 'enviar_mensaje', 
            name: 'enviar_mensaje', 
            displayName: 'ENVIAR MENSAJE', 
            type: 'object', 
            minWidth: 100, 
            enableFiltering: false, 
            enableSorting: false,
            headerCellClass: 'column-spanning-light',
            cellTemplate: '<div class="ui-grid-cell-contents text-center" > <a ng-if="COL_FIELD.strHtml" class="btn btn-default btn-xs" tooltip-placement="left" tooltip="ENVIAR MENSAJE A PROVEEDOR" style="font-size: 20px;padding: 0; width: 60px;height: 24px;" ng-click="grid.appScope.enviarCorreoProveedor(COL_FIELD.bool,row);" href="" ng-bind-html="COL_FIELD.strHtml"> </a> <span ng-if="COL_FIELD.conteo>0" class="badge badge-success badge-ui-grid" > {{ COL_FIELD.conteo }} </span> </div>'
        });
      //console.log($scope.gridOptionsOC.columnDefs);
      //$scope.getPaginationServerSide(); 
    }); 
    // console.log();
    // $scope.$watch('columnsDynamic', function(newVal, oldVal){
    //      console.log('added');
    // }, true);

    //console.log($scope.gridOptionsOC.columnDefs); 
    $scope.getPaginationServerSide = function() {
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      //console.log($scope.fBusqueda);
      seguimientoOrdenServices.sListarOrdenesCompra(arrParams).then(function (rpta) { 
        $scope.gridOptionsOC.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsOC.data = rpta.datos;
        $scope.gridOptionsOC.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGridOC = [];
    };
    
    /*=================== BOTON PROCESAR ====================*/
    $scope.procesar = function(){
      if(!$scope.formOrdenCompra.$invalid){
        $scope.getPaginationServerSide();
        // $scope.getPaginationOCAServerSide();
        // $scope.getPaginationPCServerSide(); arrParams
      }else{
        pinesNotifications.notify({ title: 'Warning.', text: 'Rellene los campos obligatorios.', type: 'warning', delay: 3000 });
      }
      
    }
    /*===================================== MANTENIMIENTO =========================================================*/ 
    $scope.verDetalleEstado = function (colField,row) { 
      // console.log(colField,'colField');
      blockUI.start('Abriendo ventana...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_detalle_estado_oc',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) { 
          $scope.fDataDetalle = row.entity;
          $scope.fDataDetalle.comentario = colField.comentario;
          $scope.fDataDetalle.estado = colField.estado;
          $scope.fDataDetalle.fecha_estado = colField.fecha_estado;
          $scope.fDataDetalle.strHtml = colField.strHtml;
          $scope.fDataDetalle.descripcion = colField.descripcion;
          $scope.fDataDetalle.idestadoporarea = colField.idestadoporarea;
          $scope.fDataDetalle.idordencompraestado = colField.idordencompraestado;
          $scope.titleForm = 'Detalle de Estado'; 
          $scope.cancel = function () { 
            $modalInstance.dismiss('cancel'); 
          }
          $scope.deshacerAccionEstado = function () { 
            var pMensaje = '¿Realmente desea deshacer la acción?';
            $bootbox.confirm(pMensaje, function(result) { 
              if(result){
                seguimientoOrdenServices.sDeshacerAccion($scope.fDataDetalle).then(function (rpta) { 
                  if(rpta.flag == 1){ 
                    var pTitle = 'OK!';
                    var pType = 'success';
                    $modalInstance.dismiss('cancel');
                    $scope.getPaginationServerSide();
                  }else if(rpta.flag == 0){ 
                    var pTitle = 'Error!';
                    var pType = 'error';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2800 });
                });
              }
            });
          }
          blockUI.stop(); 
        }
      });
    }
    $scope.enviarCorreoProveedor = function (boolEnviaMensaje,row) {
      if( boolEnviaMensaje ){ 
        blockUI.start('Abriendo ventana...');
        $uibModal.open({ 
          templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_enviar_correo_oc',
          size: 'md',
          backdrop: 'static',
          scope: $scope,
          keyboard:false,
          controller: function ($scope, $modalInstance) { 
            $scope.fDataDetalle = row.entity;
            $scope.titleForm = 'Envío de correo al proveedor'; 
            $scope.listaAreasOC = []; 
            seguimientoOrdenServices.sListarEstaOCEtapas($scope.fDataDetalle).then(function (rpta) { 
              $scope.listaAreasOC = rpta.datos;
              $scope.fDataDetalle.correo_proveedor = $scope.listaAreasOC[0].correo_proveedor;
            });
            // $('#correoProveedor').focus();
            $scope.cancel = function () { 
              $modalInstance.dismiss('cancel'); 
            }
            $scope.aceptar = function () { 
              var pMensaje = '¿Realmente desea enviar el correo electrónico?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){ 
                  blockUI.start('Ejecutando proceso...');
                  seguimientoOrdenServices.sEnviarCorreoProveedor($scope.fDataDetalle).then(function (rpta) { 
                    if(rpta.flag == 1){ 
                      var pTitle = 'OK!';
                      var pType = 'success';
                      $modalInstance.dismiss('cancel');
                      $scope.getPaginationServerSide();
                    }else if(rpta.flag == 0){ 
                      var pTitle = 'Error!';
                      var pType = 'error';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2800 }); 
                    blockUI.stop();
                  });
                }
              });
            }
            blockUI.stop(); 
          }
        });
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'No se puede enviar correo electrónico cuando la O.C aún no ha sido aprobada por todas las area.', type: 'warning', delay: 2800 });
      }

    }
    $scope.cambiarEstadoOC = function (abvEstado) { 
      blockUI.start('Abriendo ventana...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_estado_oc',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) { 
          $scope.fData = {};
          // $scope.fData.oc = $scope.mySelectionGridOC[0]; mySelectionGridOC
          $scope.titleForm = 'Control de Estados'; 
          if( abvEstado === 'a' ){ 
            $scope.fData.estado_cambio = 'APROBADO'; 
            $scope.fData.classEstado = 'f13 label label-success';
          }
          if( abvEstado === 'r' ){ 
            $scope.fData.estado_cambio = 'RECHAZADO'; 
            $scope.fData.classEstado = 'f13 label label-danger';
          }
          if( abvEstado === 'o' ){ 
            $scope.fData.estado_cambio = 'OBSERVADO'; 
            $scope.fData.classEstado = 'f13 label label-warning';
          }
          $scope.fData.abv_estado_cambio = abvEstado;
          $scope.disabledListaAreasOC = false;
          $scope.fData.area_interes = $scope.metodos.listaAreasOC[0]; 
          if($scope.fSessionCI.key_group === 'key_logistica'){ /* marinin */ 
            $scope.fData.area_interes = $scope.metodos.listaAreasOC[1]; 
            $scope.disabledListaAreasOC = true;
          }
          if($scope.fSessionCI.key_group === 'key_admin_far'){ /* vladi */ 
            $scope.fData.area_interes = $scope.metodos.listaAreasOC[2]; 
            $scope.disabledListaAreasOC = true;
          }
          if($scope.fSessionCI.key_group === 'key_gerencia'){ /* rubyzHiTtA */ 
            $scope.fData.area_interes = $scope.metodos.listaAreasOC[3]; 
            $scope.disabledListaAreasOC = true;
          }
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            blockUI.start('Ejecutando proceso...');
            $scope.fData.idmovimiento = $scope.mySelectionGridOC[0].idmovimiento;
            $scope.fData.orden_compra = $scope.mySelectionGridOC[0].orden_compra;
            $scope.fData.proveedor = $scope.mySelectionGridOC[0].razon_social;
            $scope.fData.fecha_movimiento = $scope.mySelectionGridOC[0].fecha_movimiento;
            seguimientoOrdenServices.sRegistrarEstado($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){ 
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){ 
                var pTitle = 'Error!';
                var pType = 'error';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2800 }); 
              blockUI.stop();
            });
          }
          blockUI.stop();
        }
      });
    }
    // $scope.btnAnularEntrada = function() {
    //   var pMensaje = '¿Realmente desea anular la orden de compra?';
    //   $bootbox.confirm(pMensaje, function(result) {
    //     if(result){
    //       ordenCompraServices.sAnularOrdenCompra($scope.mySelectionGridOC).then(function (rpta) {
    //         if(rpta.flag == 1){
    //             pTitle = 'OK!';
    //             pType = 'success';
    //             $scope.getPaginationServerSide();
    //             // $scope.getPaginationOCAServerSide();
    //             // $scope.getPaginationPCServerSide();
    //           }else if(rpta.flag == 0){
    //             var pTitle = 'Error!';
    //             var pType = 'danger';
    //           }else{
    //             alert('Error inesperado');
    //           }
    //           pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
    //       });
    //     }
    //   });
    // }
    // $scope.btnAprobarOC = function () {
    //   var pMensaje = '¿Realmente desea aprobar la orden de compra?';
    //   $bootbox.confirm(pMensaje, function(result) {
    //     if(result){
    //       ordenCompraServices.sAprobarOrdenCompra($scope.mySelectionGridOC).then(function (rpta) {
    //         if(rpta.flag == 1){
    //             pTitle = 'OK!';
    //             pType = 'success';
    //             $scope.getPaginationServerSide();
    //           }else if(rpta.flag == 0){
    //             var pTitle = 'Error!';
    //             var pType = 'danger';
    //           }else{
    //             alert('Error inesperado');
    //           }
    //           pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
    //       });
    //     }
    //   });
    // }
    $scope.btnImprimir = function(idmovimiento, estado){
      var abreviatura = 'O/C';
      var arrParams = {
          titulo: 'ORDEN DE COMPRA',
          datos:{
            resultado: idmovimiento,
            salida: 'pdf',
            estado: estado,
            tituloAbv: abreviatura,
            titulo: 'ORDEN DE COMPRA'
          },
          metodo: 'php'
        }
        arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_orden_compra',
        ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnVerDetalleOC = function (fEntrada,size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_detalle_orden_compra',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Orden de Compra';
          $scope.fEntrada = fEntrada;
          $scope.gridOptionsDetalleOC = {
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
              { field: 'id', displayName: 'COD.', width: '5%', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCION', enableCellEdit: false, enableSorting: false },
              { field: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: false, enableSorting: false },
              { field: 'unidad_medida', displayName: 'UNID. MED.', width: '9%', enableCellEdit: false, enableSorting: false },
              { field: 'precio', displayName: 'P. UNIT', width: '9%', enableCellEdit: false, enableSorting: false },
              { field: 'descuento', displayName: 'DCTO(%)', width: '8%', enableCellEdit: false, enableSorting: false },
              { field: 'descuento_valor', displayName: 'DCTO. VALOR ', width: '10%', enableCellEdit: false, enableSorting: false },
              { field: 'igv', displayName: 'IGV', width: '8%', enableCellEdit: false, enableSorting: false },
              { field: 'importe', displayName: 'IMPORTE', width: '10%', enableCellEdit: false, enableSorting: false },
              { field: 'excluye_igv', displayName: 'INAFECTO', width: '8%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
              },
              { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
                cellTemplate:'<div class="">'+
                  '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
                  '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
                  '</div>' 
              }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          $scope.getPaginationDetalleEntradaServerSide = function() {
            var arrParams = {
              datos: fEntrada
            };
            ordenCompraServices.sListarDetalleOrdenCompra(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleOC.data = rpta.datos;
              $scope.gridOptionsDetalleOC.sumTotal = rpta.sumTotal;
              $scope.gridOptionsDetalleOC.totalItems = rpta.paginate.totalRows;
              $scope.fEntrada.detalle = rpta.datos;
              $scope.fEntrada.proveedor = rpta.proveedor;

            });
          };
          $scope.getPaginationDetalleEntradaServerSide();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
  }])
  .service("seguimientoOrdenServices",function($http, $q) { 
    return({
        sListarOrdenesCompra: sListarOrdenesCompra,
        sListarOrdenCompraAnulada: sListarOrdenCompraAnulada,
        sListarEstaOCEtapas: sListarEstaOCEtapas,
        sEnviarCorreoProveedor: sEnviarCorreoProveedor,
        sRegistrarEstado: sRegistrarEstado,
        sDeshacerAccion: sDeshacerAccion
    });

    function sListarOrdenesCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_ordenes_compra_etapas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEstaOCEtapas (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_esta_orden_compra_etapas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarOrdenCompraAnulada(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_orden_compra_anulada", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEnviarCorreoProveedor (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/enviar_correo_proveedor", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarEstado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/agregar_estado_orden", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshacerAccion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/deshacer_accion_estado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });