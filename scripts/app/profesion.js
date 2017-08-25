angular.module('theme.profesion', ['theme.core.services'])
  .controller('profesionController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'profesionServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , profesionServices
      ){
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
      useExternalFiltering: true,
      useExternalPagination: true,
      useExternalSorting: true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idprofesion', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'descripcion', name: 'descripcion_prf', displayName: 'NOMBRE'},
        
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
            'idprofesion' : grid.columns[1].filters[0].term,
            'descripcion_prf' : grid.columns[2].filters[0].term,
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
      profesionServices.sListarProfesion($scope.datosGrid).then(function (rpta) {

        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        console.log($scope.gridOptions);
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* MANTENIMIENTO PROFESION */

    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'Profesion/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope:$scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Profesión';
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 

            profesionServices.sRegistrarProfesion($scope.fData).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'Profesion/ver_popup_formulario',
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

          $scope.titleForm = 'Edición de Profesión';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {             
            profesionServices.sEditarProfesion($scope.fData).then(function (rpta) { 
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
          profesionServices.sAnularProfesion($scope.mySelectionGrid).then(function (rpta) {
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
  .service("profesionServices",function($http, $q) {
    return({
        sListarProfesionPorAutocompletado: sListarProfesionPorAutocompletado,
        sListarProfesionesCbo: sListarProfesionesCbo,
        sListarProfesion: sListarProfesion,
        sRegistrarProfesion:sRegistrarProfesion,
        sEditarProfesion:sEditarProfesion,
        sAnularProfesion:sAnularProfesion
    });

    function sListarProfesionPorAutocompletado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesion/lista_profesiones_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProfesionesCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesion/lista_profesiones_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProfesion(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesion/listar_profesion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarProfesion(pDatos){
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesion/registrar_profesion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarProfesion(pDatos){
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesion/editar_profesion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularProfesion(pDatos){
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Profesion/anular_profesion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });