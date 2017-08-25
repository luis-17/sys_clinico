angular.module('theme.resumenSolicitudFormula', ['theme.core.services'])
  .controller('resumenSolicitudFormulaController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',     
    'resumenSolicitudServices',
    function($scope, $filter, $sce, $route, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys,       
      resumenSolicitudServices){ 
    'use strict';    
    shortcut.remove("F2");    

    $scope.modulo = 'resumenSolicitudFormula';
    $scope.fBusqueda = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGrid = [];
    $scope.listaEstadoPreparado = [ 
      { id: 'all', 'descripcion': 'TODOS' }, 
      { id: 1, 'descripcion': 'ENTREGADOS' }, 
      { id: 2, 'descripcion': 'CANCELADOS' },
      { id: 3, 'descripcion': 'A CUENTA' }, 
      { id: 4, 'descripcion': 'PENDIENTE' }
    ];
    $scope.fBusqueda.estadoPreparado = $scope.listaEstadoPreparado[0];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    /* GRID DE RESUMEN SOLICITUDES */
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
        { field: 'idsolicitudformula', name: 'idsolicitudformula', displayName: 'N° SOLICITUD', width: '9%', sort: { direction: uiGridConstants.DESC} },
        { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA', width: '9%', enableFiltering: false, enableSorting: false},
        { field: 'encargado', name: 'encargado', displayName: 'ENCARGADO', visible: false},
        { field: 'paciente', name: 'paciente', displayName: 'PACIENTE', },
        { field: 'num_documento', name: 'num_documento', displayName: 'N° DOCUMENTO'},
        { field: 'total_solicitud', name: 'total_solicitud', displayName: 'TOTAL', width: '15%', visible: false, enableFiltering: false, enableSorting: false },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
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

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {           
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
            'fsf.idsolicitudformula' : grid.columns[1].filters[0].term,
            "CONCAT_WS(' ', e.nombres, e.apellido_paterno, e.apellido_materno)" : grid.columns[3].filters[0].term,
            "CONCAT_WS(' ', c.nombres, c.apellido_paterno, c.apellido_materno)" : grid.columns[4].filters[0].term,
            'c.num_documento' : grid.columns[5].filters[0].term           
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
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      resumenSolicitudServices.sListarResumenSolicitudFormula(arrParams).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };

    $scope.getPaginationServerSide();
    
    $scope.btnEntregarPedido = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          resumenSolicitudServices.sEntregarPedido($scope.mySelectionGrid).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          }); 
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          resumenSolicitudServices.sAnularSolicitud($scope.mySelectionGrid).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          }); 
        }
      });
    }
    

    $scope.btnVerDetalleSolicitud = function (fDetalle,size) { 
      $modal.open({
        templateUrl: angular.patchURLCI+'ResumenSolicitudFormula/ver_popup_detalle_solicitud',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Solicitud';
          $scope.fDetalle = fDetalle;
          var paginationOptionsDetalleSolicitud = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleSolicitudGrid = [];
          $scope.btnToggleFiltering = function(){
            $scope.gridOptionsDetalleSolicitud.enableFiltering = !$scope.gridOptionsDetalleSolicitud.enableFiltering;
            $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
          };
          $scope.gridOptionsDetalleSolicitud = {
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
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID MEDICAMENTO', width: '6%' },
              { field: 'denominacion', name: 'denominacion', displayName: 'Medicamento'},
              { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '6%' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'Precio Unit.', width: '10%' },
              { field: 'total_detalle', name: 'total_detalle', displayName: 'Total', width: '10%', cellClass: 'bg-lightblue' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDetalleSolicitudGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsDetalleSolicitud.sort = null;
                  paginationOptionsDetalleSolicitud.sortName = null;
                } else {
                  paginationOptionsDetalleSolicitud.sort = sortColumns[0].sort.direction;
                  paginationOptionsDetalleSolicitud.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleVentaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsDetalleSolicitud.pageNumber = newPage;
                paginationOptionsDetalleSolicitud.pageSize = pageSize;
                paginationOptionsDetalleSolicitud.firstRow = (paginationOptionsDetalleSolicitud.pageNumber - 1) * paginationOptionsDetalleSolicitud.pageSize;
                $scope.getPaginationDetalleSolicitudServerSide();
              });
            }
          };
          paginationOptionsDetalleSolicitud.sortName = $scope.gridOptionsDetalleSolicitud.columnDefs[0].name;
          $scope.getPaginationDetalleSolicitudServerSide = function() {
            
            $scope.datosGrid = {
              paginate: paginationOptionsDetalleSolicitud,
              datos: fDetalle
            };
            
            resumenSolicitudServices.sListarDetalleSolicitud($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalleSolicitud.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleSolicitud.data = rpta.datos;              
            });
            $scope.mySelectionDetalleSolicitudGrid = [];
          };
          $scope.getPaginationDetalleSolicitudServerSide();

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    /* FIX TAB IN GRID */
    $scope.reloadGrid = function () { 
      $interval( function() { 
          $scope.gridApi.core.handleWindowResize();
          //$scope.gridApiAnulado.core.handleWindowResize();
          //$scope.gridApiProducto.core.handleWindowResize();
          //$scope.gridApiImpresionesVenta.core.handleWindowResize();
      }, 50, 5);
    }
    $scope.reloadGrid();
  }])
  .service("resumenSolicitudServices",function($http, $q) {
    return({
        sListarResumenSolicitudFormula: sListarResumenSolicitudFormula,
        sListarDetalleSolicitud: sListarDetalleSolicitud,
        sEntregarPedido: sEntregarPedido,
        sAnularSolicitud: sAnularSolicitud
    });

    function sListarResumenSolicitudFormula(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ResumenSolicitudFormula/lista_resumen_solicitud", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarDetalleSolicitud (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ResumenSolicitudFormula/lista_detalle_solicitud", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sEntregarPedido (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ResumenSolicitudFormula/entregar_preparado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAnularSolicitud(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ResumenSolicitudFormula/anular_solicitud", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
       
  });