angular.module('theme.zona', ['theme.core.services'])
  .controller('zonaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications','hotkeys','zonaServices','tipoZonaServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications,hotkeys,zonaServices,tipoZonaServices
      ){
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
        { field: 'id', name: 'idzona', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'tipozona', name: 'descripcion_tz', displayName: 'Tipo de Zona' },
        { field: 'nombre', name: 'descripcion_zo', displayName: 'Zona' },
        { field: 'estado', type: 'object', name: 'estado_zo', displayName: 'Estado', maxWidth: 250 , enableFiltering: false, enableSorting: false,
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
            // console.log(grid.columns);
            // console.log(grid.columns[1].filters[0].term);
            paginationOptions.searchColumn = {
              'idzona' : grid.columns[1].filters[0].term,
              'descripcion_tz' : grid.columns[2].filters[0].term,
              'descripcion_zo' : grid.columns[3].filters[0].term
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
      zonaServices.sListarZona($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'zona/ver_popup_formulario',
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
          $scope.titleForm = 'Edición de Zona';
           // TIPO ZONA
          tipoZonaServices.sListarTipoZonaCbo().then(function (rpta) {
            $scope.listaTiposZona = rpta.datos;
            $scope.listaTiposZona.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de zona--'});
            
          });
        
          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};

            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            zonaServices.sEditar($scope.fData).then(function (rpta) {
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
      $modal.open({
        templateUrl: angular.patchURLCI+'zona/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de zona';
           // TIPO ZONA
          tipoZonaServices.sListarTipoZonaCbo().then(function (rpta) {
            $scope.listaTiposZona = rpta.datos;
            $scope.listaTiposZona.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de zona--'});
            $scope.fData.idtipozona = $scope.listaTiposZona[0].id;
            $scope.fData.tipozona = $scope.listaTiposZona[0].descripcion;
          });
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            zonaServices.sRegistrar($scope.fData).then(function (rpta) {
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
          zonaServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
    /* HABILITAR Y DESHABILITAR     */
    /* ============================ */

   $scope.btnHabilitar = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          zonaServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
          zonaServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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
        description: 'Nueva Zona',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({
        combo: 'e',
        description: 'Editar Zona',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({
        combo: 'del',
        description: 'Anular Zona',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnDeshabilitar();
          }
        }
      })
      .add ({
        combo: 'b',
        description: 'Buscar Zona',
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
  .service("zonaServices",function($http, $q) {
    return({ 
        sListarZona: sListarZona,
        sListarZonaCbo: sListarZonaCbo,
        sListarZonaPorAutocompletado: sListarZonaPorAutocompletado,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sHabilitar: sHabilitar,
        sDeshabilitar: sDeshabilitar,
        sAnular: sAnular
    });
    function sListarZona(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/lista_zona",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarZonaCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/lista_zona_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarZonaPorAutocompletado(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/lista_zona_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/registrar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/editar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/habilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/deshabilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"zona/anular",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });