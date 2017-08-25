angular.module('theme.odontograma', ['theme.core.services'])
  .controller('odontogramaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
      'odontogramaServices', 
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      odontogramaServices ){ 
    'use strict';
    
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
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
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idpiezadental', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'nombre', name: 'pieza_dental', displayName: 'Pieza Dental' },
        { field: 'zona_dental', name: 'zona_dental', displayName: 'Zona Dental' },
        { field: 'estado_dental', name: 'estado_dental', displayName: 'Estado Dental' },
        { field: 'idodontograma', name: 'idodontograma', displayName: 'Odontograma' },
        { field: 'idatencionmedica', name: 'idatencionmedica', displayName: 'Atención Médica' }
        
        // { field: 'estado', type: 'object', name: 'estado_prod', displayName: 'Estado', maxWidth: 250, enableFiltering: false, enableSorting: false, 
        //   cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = { 
            'idpiezadental' : grid.columns[1].filters[0].term,
            'descripcion_pd' : grid.columns[2].filters[0].term,
            'descripcion_zp' : grid.columns[3].filters[0].term,
            'descripcion_ep' : grid.columns[4].filters[0].term,
            'idodontograma' : grid.columns[5].filters[0].term,
            'idatencionmedica' : grid.columns[6].filters[0].term
            
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
      odontogramaServices.sListarPiezasOdontograma($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
         
        
      });
      // ============================= para mostrar el var_dump
      $scope.datos = {
        idodontograma : 1,
        estado: 0
      };
      odontogramaServices.sListarOdontogramaVacio($scope.datos).then(function (rpta) {
        // console.log(rpta);
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    

  }])
  .service("odontogramaServices",function($http, $q) {
    return({
      sListarPiezasOdontograma,
      sListarOdontogramaInicial,
      sListarEstadoDentalCbo,
      sListarProcedimientosCbo,
      sRegistrar,
      sEditar
       
    });

    function sListarPiezasOdontograma(datos) { 
      var request = $http({
            method : "post",
            // url : angular.patchURLCI+"odontograma/lista_todas_las_piezas_de_odontograma", 
            url : angular.patchURLCI+"odontograma/lista_estado_por_zona_por_pieza_por_odontograma", // muestra en grilla
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sListarOdontogramaInicial(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"odontograma/lista_piezas_con_zonas", // muestra odontograma inicial si lo tiene, sino uno vacio
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarEstadoDentalCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"odontograma/lista_estado_pieza_dental_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProcedimientosCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"odontograma/lista_procedimientos_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"odontograma/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"odontograma/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
  });