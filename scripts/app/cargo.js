angular.module('theme.cargo', ['theme.core.services'])
  .controller('cargoController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'cargoServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , cargoServices
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
        { field: 'id', name: 'idcargo', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC}, enableCellEdit: false, },
        { field: 'descripcion', name: 'descripcion_ca', displayName: 'Descripción', enableCellEdit: true, },
        { field: 'agrega_horario_especial_string', name: 'agrega_horario_especial', displayName: 'Agrega Horario Esp.',maxWidth: 150, enableFiltering: false,
        cellClass:"text-center", enableCellEdit: false, },

      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
          rowEntity.column = colDef.field;
          rowEntity.newValue = newValue;
          rowEntity.oldValue = oldValue;
          if(rowEntity.column == 'descripcion'){
            if( rowEntity.newValue != rowEntity.oldValue ){
              console.log('Descripcion modificada');
              cargoServices.sEditar(rowEntity).then(function (rpta) {
                if(rpta.flag != 2){
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  $scope.getPaginationServerSide();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                }
                
              });
            }
          }
          $scope.$apply();
        }); // -edit
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
            'idcargo' : grid.columns[1].filters[0].term,
            'descripcion_ca' : grid.columns[2].filters[0].term,
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
      cargoServices.sListarCargo($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'Cargo/ver_popup_formulario',
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
            $scope.fData.oldValue = $scope.mySelectionGrid[0].descripcion;
            $scope.fData.oldValueAgrega = $scope.mySelectionGrid[0].agrega_horario_especial;
            
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Cargo';
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            $scope.fData.newValue = $scope.fData.descripcion;
            $scope.fData.newValueAgrega = $scope.fData.agrega_horario_especial;
            cargoServices.sEditar($scope.fData).then(function (rpta) {
              if(rpta.flag != 2){
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $modalInstance.dismiss('cancel');
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
              $scope.getPaginationServerSide();
              $scope.fData = {};
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
        templateUrl: angular.patchURLCI+'Cargo/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.temporal = {};
          $scope.fData.agrega_horario_especial = false;
          $scope.titleForm = 'Registro de Cargo';

          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            cargoServices.sRegistrar($scope.fData).then(function (rpta) {
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
          cargoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
  .service("cargoServices",function($http, $q) {
    return({
      sListarCargosCbo: sListarCargosCbo,
      sListarCargoPorAutocompletado: sListarCargoPorAutocompletado,
      sListarCargo: sListarCargo,
      sRegistrar: sRegistrar,
      sEditar: sEditar,
      sAnular: sAnular,
    });

    function sListarCargosCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cargo/lista_cargo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCargoPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cargo/lista_cargos_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCargo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cargo/lista_cargo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cargo/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cargo/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cargo/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });