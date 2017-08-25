angular.module('theme.tipoDocumento', ['theme.core.services'])
  .controller('tipoDocumentoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'tipoDocumentoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , tipoDocumentoServices
      ){
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'tipoDocumento';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null
    };


    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idtipodocumento', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'descripcion_td', name: 'descripcion_td', displayName: 'Descripcion' },
        { field: 'abreviatura', name: 'abreviatura', displayName: 'Abreviatura', maxWidth: 150, cellTemplate:'<div style="text-align:center">{{ COL_FIELD }}</div>'},
        
        { field: 'estado_td', type: 'object', name: 'estado_td', displayName: 'Estado', maxWidth: 250,  
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      tipoDocumentoServices.sListarTipoDoc($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
         
        
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    $scope.btnHabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          tipoDocumentoServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
    $scope.btnDeshabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          tipoDocumentoServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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

  }])

  .service("tipoDocumentoServices",function($http, $q) {
    return({
        sListarTipoDoc: sListarTipoDoc,
        sListarTipoDocumentoVentaCbo: sListarTipoDocumentoVentaCbo,
        sListarTipoDocumentoAlmacenlabCbo: sListarTipoDocumentoAlmacenlabCbo ,
        sDeshabilitar: sDeshabilitar,
        sHabilitar: sHabilitar,
        sListarTipoDocumentoVenta: sListarTipoDocumentoVenta,
        sTipoDocumento: sTipoDocumento,
        sListarTipoDocumentoContabilidad: sListarTipoDocumentoContabilidad
    });
     function sListarTipoDoc(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/lista_tipo_documento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTipoDocumentoVentaCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/lista_tipo_documento_venta_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarTipoDocumentoAlmacenlabCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/lista_tipo_documento_almacenlab_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTipoDocumentoVenta(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/lista_tipo_documento_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sTipoDocumento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Empleado/lista_tipo_documento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTipoDocumentoContabilidad(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoDocumento/lista_tipo_documento_contabilidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });