angular.module('theme.afp', ['theme.core.services'])
  .controller('afpController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'afpServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , afpServices
      ){
    'use strict';
        $scope.fData = {};
    $scope.fData.temporal = {};
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
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
      enableCellEditOnFocus: true,
      columnDefs: [
        { field: 'id', name: 'idafp', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC}, enableCellEdit: false, },
        { field: 'descripcion', name: 'descripcion_afp', displayName: 'Descripción', enableCellEdit: true, },
        { field: 'a_oblig', name: 'a_oblig', displayName: '% A. OBLIG', enableCellEdit: true, },
        { field: 'comision', name: 'comision', displayName: '% COMISION', enableCellEdit: true, },
        { field: 'p_seguro', name: 'p_seguro', displayName: '% P. SEGURO', enableCellEdit: true, },
        { field: 'comision_m', name: 'comision_m', displayName: '% COMISION M.', enableCellEdit: true, },
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
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
         $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = { 
            'idafp' : grid.columns[1].filters[0].term,
            'descripcion_afp' : grid.columns[2].filters[0].term,
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
      afpServices.sListarAFP($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    
      /* ============= */
     /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Afp/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
            
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de AFP';
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            afpServices.sEditar($scope.fData).then(function (rpta) {
              if(rpta.flag != 2){
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $modalInstance.dismiss('cancel');
                  $scope.getPaginationServerSide();
                  $scope.fData = {};
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
              }else{
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          //console.log($scope.mySelectionGrid);
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
    $scope.btnNuevo = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Afp/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.temporal = {};
          $scope.titleForm = 'Registro de Cargo';

          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            afpServices.sRegistrar($scope.fData).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          //console.log($scope.mySelectionGrid);
        }, 
        resolve: {
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
          afpServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
  }])
  .service("afpServices",function($http, $q) { 
    return({
        sListarAFPcbo: sListarAFPcbo,
        sListarAFP: sListarAFP,
        sEditar:sEditar,
        sRegistrar:sRegistrar,
        sAnular:sAnular,
    });

    function sListarAFPcbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Afp/lista_afp_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarAFP(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Afp/lista_afp", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Afp/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Afp/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Afp/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });