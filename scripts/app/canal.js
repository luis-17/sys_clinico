angular.module('theme.canal', ['theme.core.services'])
  .controller('canalController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'canalServices',  
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, canalServices ){
    'use strict';
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
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
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
        { field: 'id', name: 'idcanal', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC,priority: 0 } },
        { field: 'descripcion', name: 'descripcion_can', displayName: 'Descripción' },
        { field: 'porcentaje_canal', name: 'porcentaje_canal', displayName: 'Porcentaje de venta' }
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
          console.log(paginationOptions);

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
              'idcanal' : grid.columns[1].filters[0].term,
              'descripcion_can' : grid.columns[2].filters[0].term
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
      canalServices.sListarCanal($scope.datosGrid).then(function (rpta) {
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
      $modal.open({
        templateUrl: angular.patchURLCI+'Canal/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
            $scope.fData.porcentaje = Number($scope.mySelectionGrid[0].porcentaje_canal);
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de canal';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            canalServices.sEditar($scope.fData).then(function (rpta) { 
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
              $scope.fData = {};
              $scope.getPaginationServerSide();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }
          //console.log($scope.mySelectionGrid);
        }
       
      });
    }
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'Canal/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.titleForm = 'Registro de canal';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
         

          $scope.aceptar = function () { 
            // console.log($scope.fData);
            canalServices.sRegistrar($scope.fData).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }
          //console.log($scope.mySelectionGrid);
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          canalServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
    
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
   hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva canal',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar canal',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular canal',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar canal',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      })
      .add ({ 
        combo: 's',
        description: 'Selección y Navegación',
        callback: function() {
          $scope.navegateToCell(0,0);
        }
      });
  }])
  .service("canalServices",function($http, $q) {
    return({
        sListarCanal: sListarCanal,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sListaCanalCbo: sListaCanalCbo,
    });

    function sListarCanal(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Canal/lista_canal", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Canal/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Canal/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Canal/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    

    function sListaCanalCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Canal/lista_canal_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });