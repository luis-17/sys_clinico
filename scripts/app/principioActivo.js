angular.module('theme.principioActivo', ['theme.core.services'])
  .controller('principioActivoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',
    'principioActivoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys , 
      principioActivoServices
      ){
    'use strict';
    $scope.fBusqueda = {};
    $scope.listaComponenteFormula = [ 
      { id: 0, 'descripcion': 'TODOS' }, 
      { id: 1, 'descripcion': 'ES COMPONENTE FÓRMULA' }, 
      { id: 2, 'descripcion': 'NO ES COMPONENTE FÓRMULA' }
    ];
    $scope.fBusqueda.componenteFormula = $scope.listaComponenteFormula[0];

    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
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
      enableFiltering: true,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idprincipioactivo', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'descripcion', displayName: 'Principio Activo' },
        { field: 'estado', type: 'object', name: 'estado_pa', displayName: 'Estado', maxWidth: 250, enableFiltering: false, enableSorting: false ,
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
              'idprincipioactivo' : grid.columns[1].filters[0].term,
              'descripcion' : grid.columns[2].filters[0].term
              
            }
            $scope.getPaginationServerSide();
          });

      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      console.log($scope.fBusqueda);
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos: $scope.fBusqueda.componenteFormula
      };
      principioActivoServices.sListarprincipioActivo($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'principioActivo/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.es_componente_formula = '0';
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Principio Activo';
          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};

            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            principioActivoServices.sEditar($scope.fData).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'principioActivo/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Principio Activo';
          $scope.fData.es_componente_formula = '0';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            principioActivoServices.sRegistrar($scope.fData).then(function (rpta) {
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
          principioActivoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
          principioActivoServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
          principioActivoServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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
        description: 'Nuevo Principio Activo',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({
        combo: 'e',
        description: 'Editar Principio Activo',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({
        combo: 'del',
        description: 'Anular Principio Activo',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnDeshabilitar();
          }
        }
      })
      .add ({
        combo: 'b',
        description: 'Buscar Principio Activo',
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
  .service("principioActivoServices",function($http, $q) {
    return({
        sListarprincipioActivoCbo : sListarprincipioActivoCbo,
        sListarprincipioActivo : sListarprincipioActivo,
        sListarprincipioActivoxMed : sListarprincipioActivoxMed,
        sListarSinprincipioActivoxMed : sListarSinprincipioActivoxMed,
        sListarBusquedaPrincipioActivo : sListarBusquedaPrincipioActivo,
        sRegistrar : sRegistrar,
        sRegistrarprincipioMed : sRegistrarprincipioMed,
        sEditar : sEditar,
        sHabilitar : sHabilitar,
        sDeshabilitar : sDeshabilitar,
        sAnular : sAnular,
        sAnularprincipioMed : sAnularprincipioMed
    });

    function sListarprincipioActivoCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/lista_principio_activo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarprincipioActivo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/lista_principio_activo",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarprincipioActivoxMed(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/lista_principio_activo_medicamento",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSinprincipioActivoxMed(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/lista_sin_principio_activo_medicamento",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarBusquedaPrincipioActivo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/lista_busqueda_principio_activo",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/registrar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarprincipioMed (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/registrar_principio_activo_medicamento",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/editar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/habilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/deshabilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/anular",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularprincipioMed (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"principioActivo/anular_principio_activo_medicamento",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });