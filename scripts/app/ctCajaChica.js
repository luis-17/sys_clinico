angular.module('theme.ctCajaChica', ['theme.core.services'])
  .controller('ctCajaChicaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',
    'ctCajaChicaServices',
    'empresaAdminServices',
    'centroCostoServices',
    'cajaChicaServices',
    'usuarioServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ctCajaChicaServices,
      empresaAdminServices,
      centroCostoServices,
      cajaChicaServices,
      usuarioServices
      ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    // LISTA SEDE EMPRESA ADMIN
      empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
        $scope.listaSedeEmpresaAdmin = rpta.datos;
      });
    // LISTA DE CATEGORIA Y SUBCATEGORIA
    centroCostoServices.sListarCategoriaSubCatCentroCostoCbo().then(function (rpta) { 
      $scope.listaSubCatCentroCosto = rpta.datos;
      $scope.listaSubCatCentroCosto.splice(0,0,{ id : 'all', descripcion:'-- Seleccione --'});
      $scope.listaCentroCosto = [];
      $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione 1º Cat/SubCategoria --'});
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
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idcajachica', displayName: 'ID', maxWidth: 80, minWidth:70,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'nombre', displayName: 'CAJA CHICA', minWidth:110},
        { field: 'empresa_admin', name: 'razon_social', displayName: 'EMPRESA ADMIN', minWidth:110},
        { field: 'centro_costo', name: 'nombre_cc', displayName: 'CENTRO DE COSTO', minWidth:110},
        { field: 'responsable', name: 'responsable', displayName: 'RESPONSABLE', minWidth:110},
        { field: 'estado', type: 'object', name: 'estado_cch', displayName: 'Estado', maxWidth: 250, minWidth:80, width: 150, enableFiltering: false, enableSorting: false ,
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
              'cch.idcajachica' : grid.columns[1].filters[0].term,
              'cch.nombre' : grid.columns[2].filters[0].term,
              "concat_ws(' - ',ea.razon_social, s.descripcion)" : grid.columns[3].filters[0].term,
              'cc.nombre_cc' : grid.columns[4].filters[0].term,
              
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
      ctCajaChicaServices.sListarctCajaChica($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'ctCajaChica/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.cargarCentroCosto = function(idsubcatcentrocosto,modoCambio){
            centroCostoServices.sListarCentroCostoCbo(idsubcatcentrocosto).then(function (rpta) { 
              $scope.listaCentroCosto = rpta.datos;
              $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione --'});
              if(modoCambio){
                $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
              }
            });
          }
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
            console.log('load me', $scope.fData);
            if(!$scope.fData.idcentrocosto){
              $scope.fData.idsubcatcentrocosto = $scope.listaSubCatCentroCosto[0].id;
              $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
            }else{
              $scope.cargarCentroCosto($scope.fData.idsubcatcentrocosto,false);
            }
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Caja Chica';
          
          

          $scope.aceptar = function () {
            ctCajaChicaServices.sEditar($scope.fData).then(function (rpta) {
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
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};

            $scope.getPaginationServerSide();
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
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'ctCajaChica/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Caja Chica';
          $scope.fData.idsedeempresa = $scope.fSessionCI.idsedeempresaadmin;
          $scope.fData.idsubcatcentrocosto = $scope.listaSubCatCentroCosto[0].id;
          $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
          $scope.cargarCentroCosto = function(idsubcatcentrocosto){
            centroCostoServices.sListarCentroCostoCbo(idsubcatcentrocosto).then(function (rpta) { 
              $scope.listaCentroCosto = rpta.datos;
              $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione --'});
              $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
            });
          }
          $scope.aceptar = function () {
            ctCajaChicaServices.sRegistrar($scope.fData).then(function (rpta) {
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
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
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
          ctCajaChicaServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
          ctCajaChicaServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
          ctCajaChicaServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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

    /*ASIGNACION*/
    $scope.btnAsignar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'ctCajaChica/ver_popup_formulario_asignar',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.cargarCentroCosto = function(idsubcatcentrocosto,modoCambio){
            centroCostoServices.sListarCentroCostoCbo(idsubcatcentrocosto).then(function (rpta) { 
              $scope.listaCentroCosto = rpta.datos;
              $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione --'});
              if(modoCambio){
                $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
              }
            });
          }
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Asignar Caja Chica';
          $scope.cargarSaldoAnterior = function(caja){
            $scope.fData.saldo_anterior = null;
            $scope.fData.monto_inicial = null;
            cajaChicaServices.sListarSaldoAnterior(caja).then(function(rpta){
              var pTitle;
              var pType;
              if(rpta.flag==1){
                $scope.fData.saldo_anterior = rpta.datos;
                $scope.calcularMontoInicio();
              }else if(rpta.flag==0){
                $scope.fData.saldo_anterior = rpta.datos;
                $scope.calcularMontoInicio();
                pTitle = 'Advertencia!';
                pType = 'warning';
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 }); 
              }else{
                alert('Error inesperado');
              }
            });
          }
          $scope.cargarSaldoAnterior($scope.fData);

          $scope.calcularMontoInicio = function(){
            $scope.fData.monto_inicial = parseFloat($scope.fData.saldo_anterior) + parseFloat($scope.fData.monto_cheque);
            console.log('calculando...');
          }
          
          $scope.getResponsableAutocomplete = function (value) {
            var params = {
              search: value,
              sensor: false,
            }
            return usuarioServices.sUserEmpleadoAutocomplete(params).then(function(rpta) { 
              $scope.noResultsLM = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLM = true;
              }
              return rpta.datos; 
            });
          }

          $scope.getSelectedResponsable = function ($item, $model, $label) {
            $scope.fData.idresponsable = $item.idempleado;         
          };

          $scope.aceptar = function () {
            ctCajaChicaServices.sAsignar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $modalInstance.dismiss('cancel');
                $scope.fData = {};
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                
              }else{
                alert('Error inesperado');
              }              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};

            $scope.getPaginationServerSide();
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

    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nuevo Tipo de Zona',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({
        combo: 'e',
        description: 'Editar Tipo de Zona',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({
        combo: 'del',
        description: 'Anular Tipo de Zona',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnDeshabilitar();
          }
        }
      })
      .add ({
        combo: 'b',
        description: 'Buscar Tipo de Zona',
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
  .service("ctCajaChicaServices",function($http, $q) {
    return({
        sListarTipoZonaCbo: sListarTipoZonaCbo,
        sListarctCajaChica : sListarctCajaChica,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sHabilitar: sHabilitar,
        sDeshabilitar: sDeshabilitar,
        sAnular: sAnular,
        sAsignar:sAsignar,
    });

    function sListarTipoZonaCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/lista_caja_chica_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarctCajaChica(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/lista_caja_chica",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/registrar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/editar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/habilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/deshabilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/anular",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAsignar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ctCajaChica/asignar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });