angular.module('theme.motivoTraslado', ['theme.core.services'])
  .controller('motivoTrasladoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'motivoTrasladoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, uiGridConstants, pinesNotifications, hotkeys, motivoTrasladoServices ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null,
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
        { field: 'id', name: 'idmotivotraslado', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'descripcion_mt', displayName: 'Nombre Laboratorio' },
        { field: 'estado', type: 'object', name: 'estado_mt', displayName: 'Estado', maxWidth: 250, enableFiltering: false, enableSorting: false ,
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
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = {
            'idmotivotraslado' : grid.columns[1].filters[0].term,
            'descripcion_mt' : grid.columns[2].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };

    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      motivoTrasladoServices.sListarMotivoTraslado($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* MANTENIMIENTO MOTIVO TRASLADO */
  
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'motivoTraslado/ver_popup_formulario',
        size: size || 'xs',
        backdrop: 'static',
        keyboard:false,
        scope:$scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de motivo de Traslado';
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            motivoTrasladoServices.sRegistrarMotivoTraslado($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
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
          
        }, 
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }

    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'motivoTraslado/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope:$scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};         
  
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];  
          }else{
            alert('Seleccione una sola fila');
          }         

          $scope.titleForm = 'Edición de motivo de Traslado';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {             
            motivoTrasladoServices.sEditarMotivoTraslado($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                $scope.getPaginationServerSide();
              }else{
                alert('Error inesperado');
              }
              $scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $scope.getPaginationServerSide();
            });
          }
  
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          },
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          motivoTrasladoServices.sAnularMotivoTraslado($scope.mySelectionGrid).then(function (rpta) {
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
  .service("motivoTrasladoServices",function($http, $q) { 
    return({
        sListarMotivoTraslado: sListarMotivoTraslado,
        sListaMotivoTraslado: sListaMotivoTraslado,
        sRegistrarMotivoTraslado: sRegistrarMotivoTraslado,
        sEditarMotivoTraslado: sEditarMotivoTraslado,
        sAnularMotivoTraslado: sAnularMotivoTraslado
    });

    function sListarMotivoTraslado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"motivoTraslado/listar_motivo_traslado", 
            data : datos
      });

      return (request.then( handleSuccess,handleError ));
    }
    function sListaMotivoTraslado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"motivoTraslado/lista_motivo_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarMotivoTraslado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"motivoTraslado/registrar_motivo_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sEditarMotivoTraslado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"motivoTraslado/editar_motivo_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularMotivoTraslado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"motivoTraslado/anular_motivo_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });