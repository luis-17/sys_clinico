angular.module('theme.centroCosto', ['theme.core.services'])
  .controller('centroCostoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'centroCostoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, uiGridConstants, pinesNotifications, hotkeys, centroCostoServices ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null,
    };
    // LISTA DE CATEGORIA Y SUBCATEGORIA
    centroCostoServices.sListarCategoriaSubCatCentroCostoCbo().then(function (rpta) { 
      $scope.listaSubCatCentroCosto = rpta.datos;
      $scope.listaSubCatCentroCosto.splice(0,0,{ id : 'all', descripcion:'-- Seleccione --'});
    });
    
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
        { field: 'id', name: 'idprofesion', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'descripcion', name: 'descripcion_prf', displayName: 'DESCRIPCIÓN',  maxWidth: 100}, 
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
            'cc.idcentrocosto' : grid.columns[1].filters[0].term,
            'codigo_cc' : grid.columns[2].filters[0].term,
            'nombre_cc' : grid.columns[3].filters[0].term,
            'descripcion_ccc' : grid.columns[4].filters[0].term,
            'descripcion_scc' : grid.columns[5].filters[0].term,
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
      centroCostoServices.sListarCentroCosto($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* MANTENIMIENTO CENTRO COSTO */
  
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'centroCosto/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope:$scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Centro de Costo';
          $scope.fData.idsubcat = $scope.listaSubCatCentroCosto[0].id;
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            centroCostoServices.sRegistrarCentroCosto($scope.fData).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'centroCosto/ver_popup_formulario',
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

          $scope.titleForm = 'Edición de Centro de Costo';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {             
            centroCostoServices.sEditarCentroCosto($scope.fData).then(function (rpta) { 
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
          centroCostoServices.sAnularCentroCosto($scope.mySelectionGrid).then(function (rpta) {
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
  .service("centroCostoServices",function($http, $q) { 
    return({
        sListarCentroCosto: sListarCentroCosto,
        sListarCentroCostoGrilla: sListarCentroCostoGrilla,
        sListarCentroCostoCbo: sListarCentroCostoCbo,
        sListarCatCentroCostoCbo: sListarCatCentroCostoCbo,
        sListarSubCatCentroCostoCbo: sListarSubCatCentroCostoCbo,
        sListarCategoriaSubCatCentroCostoCbo: sListarCategoriaSubCatCentroCostoCbo,
        sListarTipoCambio: sListarTipoCambio,
        sRegistrarTipoCambio: sRegistrarTipoCambio,
        sRegistrarCentroCosto: sRegistrarCentroCosto,
        sEditarCentroCosto: sEditarCentroCosto,
        sAnularCentroCosto: sAnularCentroCosto
    });

    function sListarCentroCosto(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"centroCosto/listar_centro_costo", 
            data : datos
      });

      return (request.then( handleSuccess,handleError ));
    }
    function sListarCentroCostoGrilla(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/lista_centro_costo_grilla", 
            data : datos
      });

      return (request.then( handleSuccess,handleError ));
    }
    function sListarCentroCostoCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/lista_centro_costo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCatCentroCostoCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/listar_categoria_centro_costo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubCatCentroCostoCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/listar_subcategoria_centro_costo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCategoriaSubCatCentroCostoCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/listar_categoria_subcat_centro_costo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTipoCambio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/listar_tipo_cambio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarTipoCambio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/registrar_tipo_cambio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarCentroCosto (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/registrar_centro_costo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sEditarCentroCosto (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/editar_centro_costo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularCentroCosto (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CentroCosto/anular_centro_costo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });