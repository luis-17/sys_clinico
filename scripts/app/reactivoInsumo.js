angular.module('theme.reactivoInsumo', ['theme.core.services'])
  .controller('reactivoInsumoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
      'reactivoInsumoServices', 'unidadesLaboratorioServices', 'marcaLabServices' ,'presentacionServices' ,
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, reactivoInsumoServices ,unidadesLaboratorioServices , marcaLabServices , presentacionServices ){ 
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'reactivoInsumo';
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
        { field: 'id', name: 'idreactivoinsumo', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'descripcion', name: 'descricpion', displayName: 'Reactivo - Insumo' },
        { field: 'tipo', name: 'tipo', displayName: 'Tipo' ,enableFiltering: false },
        { field: 'stock', name: 'stock', displayName: 'Stock' ,enableFiltering: false },
        { field: 'presentacion', name: 'presentacion', displayName: 'Presentacion' }, 
        { field: 'unidad', name: 'unidad', displayName: 'Unidad' }, 
        { field: 'marca', name: 'marca', displayName: 'Marca' }, 
        { field: 'estado', type: 'object', name: 'estado_pr', displayName: 'Estado', maxWidth: 250, enableFiltering: false, enableSorting: false, 
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
            'r.idreactivoinsumo' : grid.columns[1].filters[0].term,
            'r.descripcion' : grid.columns[2].filters[0].term,
            //'tipo' : grid.columns[3].filters[0].term,
            //'stock' : grid.columns[4].filters[0].term,
            'p.descripcion_pr' : grid.columns[5].filters[0].term,
            'u.descripcion' : grid.columns[6].filters[0].term,
            'm.descripcion_m' : grid.columns[7].filters[0].term
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
      reactivoInsumoServices.sListarReactivoInsumo($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'reactivoInsumo/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.accion = 'edit';
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Edición de Reactivo - Insumo';

          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          unidadesLaboratorioServices.sListarUnidadesLaboratorioCbo($scope.datosGrid).then(function (rpta) {
            $scope.listaUnidadesLaboratorio = rpta.datos;
          });
          marcaLabServices.sListarmarcaLabCbo($scope.datosGrid).then(function (rpta) {
            $scope.listaMarcaLaboratorio = rpta.datos;
          });
          
          presentacionServices.sListarpresentacionCbo($scope.datosGrid).then(function (rpta) {
            $scope.listaFiltroPresentacion = rpta.datos;
          });
          $scope.listaFiltroTipo = [
            { id: 1 , descripcion:'REACTIVO' },
            { id: 2 , descripcion:'INSUMO' }
          ]; 
          /******************************************************/
          $scope.fData.idtipo = $scope.listaFiltroTipo[$scope.fData.idtipo-1].id;
          //$scope.fData.idpresentacion = $scope.listaFiltroPresentacion[$scope.fData.idpresentacion-1].id;

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            reactivoInsumoServices.sEditar($scope.fData).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'reactivoInsumo/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.accion = 'reg';
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Reactivo - Insumo';
          $scope.listaFiltroTipo = [
            { id: 1 , descripcion:'REACTIVO' },
            { id: 2 , descripcion:'INSUMO' }
          ]; 
          $scope.fData.idtipo = $scope.listaFiltroTipo[0].id;

          unidadesLaboratorioServices.sListarUnidadesLaboratorioCbo($scope.datosGrid).then(function (rpta) {
            $scope.listaUnidadesLaboratorio = rpta.datos;
            $scope.listaUnidadesLaboratorio.splice(0,0,{ id : 'oll', descripcion:'--Seleccione la Unidad--'});
            $scope.fData.idunidadlaboratorio = $scope.listaUnidadesLaboratorio[0].id; 
          });
          marcaLabServices.sListarmarcaLabCbo($scope.datosGrid).then(function (rpta) {
            $scope.listaMarcaLaboratorio = rpta.datos;
            $scope.listaMarcaLaboratorio.splice(0,0,{ id : 'oll', descripcion:'--Seleccione la Marca--'});
            $scope.fData.idmarca = $scope.listaMarcaLaboratorio[0].id; 
          });
          
          presentacionServices.sListarpresentacionCbo($scope.datosGrid).then(function (rpta) {
            $scope.listaFiltroPresentacion = rpta.datos;
            $scope.listaFiltroPresentacion.splice(0,0,{ id : 'oll', descripcion:'--Seleccione la Presentacion--'});
            $scope.fData.idpresentacion = $scope.listaFiltroPresentacion[0].id; 
          });

          $scope.getreactivoInsumoAutocomplete = function(val) { 
            var params = {
              search: val,
              sensor: false
            }
            return reactivoInsumoServices.sListarReactivoInsumoCbo(params).then(function(rpta) {
              var insumos = rpta.datos.map(function(e) {
                return e.descripcion;
              });
              return insumos;
            });
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            reactivoInsumoServices.sRegistrar($scope.fData).then(function (rpta) {
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
          reactivoInsumoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Error!';
                var pType = 'error';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1300 });
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
          reactivoInsumoServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
          reactivoInsumoServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Error!';
                var pType = 'error';
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
        description: 'Nuevo reactivo-insumo',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar reactivo-insumo',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular reactivo-insumo',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar reactivo-insumo',
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
  .service("reactivoInsumoServices",function($http, $q) {
    return({
        sListarReactivoInsumo: sListarReactivoInsumo,
        sListarReactivoInsumoCbo: sListarReactivoInsumoCbo,
        sListarReactivoInsumoporCodigo : sListarReactivoInsumoporCodigo ,
        sRegistrar: sRegistrar,
        sDeshabilitar : sDeshabilitar,
        sHabilitar : sHabilitar,
        sEditar: sEditar,
        sAnular: sAnular,
        sListarRiVencidos : sListarRiVencidos ,
        sListarRiStockMinimo : sListarRiStockMinimo ,
        sTotalRiVencidos : sTotalRiVencidos ,
        sTotalRiStockMinimo : sTotalRiStockMinimo ,
        sTratamientoRiVencido : sTratamientoRiVencido
    });
    function sListarReactivoInsumo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_reactivo_insumo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarReactivoInsumoporCodigo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_reactivoInsumo_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarReactivoInsumoCbo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_reactivo_insumo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarRiVencidos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_ri_vencidos",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarRiStockMinimo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_ri_stock_minimo",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sTotalRiVencidos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_reactivo_insumos_vencidos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sTotalRiStockMinimo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/lista_reactivo_insumos_stock_minimo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sTratamientoRiVencido(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"reactivoInsumo/tratamiento_reactivoinsumo_vencido", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }


  });