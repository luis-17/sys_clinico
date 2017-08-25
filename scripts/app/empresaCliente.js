angular.module('theme.empresaCliente', ['theme.core.services'])
  .controller('empresaClienteController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
      'empresasClienteServices', 
      'sedeServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      empresasClienteServices,
      sedeServices ){
    'use strict';
    $scope.initEmpresaCliente = function () {
      shortcut.remove("F2"); $scope.modulo = 'empresa';
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
        minRowsToShow: 11,
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
          { field: 'idempresacliente', name: 'idempresacliente', displayName: 'ID', width: '5%',  sort: { direction: uiGridConstants.ASC} },
          { field: 'descripcion', name: 'descripcion', displayName: 'Empresa', width: '30%' },
          { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC', width: '20%' },
          { field: 'domicilio_fiscal', name: 'domicilio_fiscal', displayName: 'Direccion', width: '43%' },
          

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
            $scope.getPaginationEmpresaClienteServerSide();
          });
          gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
            paginationOptions.pageNumber = newPage;
            paginationOptions.pageSize = pageSize;
            paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
            $scope.getPaginationEmpresaClienteServerSide();
          });
          $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
            var grid = this.grid;
            paginationOptions.search = true;
            // console.log(grid.columns);
            // console.log(grid.columns[1].filters[0].term);
            paginationOptions.searchColumn = {
              'idempresacliente' : grid.columns[1].filters[0].term,
              'descripcion' : grid.columns[2].filters[0].term,
              'ruc_empresa' : grid.columns[3].filters[0].term,
              'domicilio_fiscal' : grid.columns[4].filters[0].term
            }
            $scope.getPaginationEmpresaClienteServerSide();
          });
        }
      };
      paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
      $scope.getPaginationEmpresaClienteServerSide = function() {
        $scope.datosGrid = {
          paginate : paginationOptions
        };
        empresasClienteServices.sListarEmpresas($scope.datosGrid).then(function (rpta) {
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
        });
        $scope.mySelectionGrid = [];
      };
      $scope.getPaginationEmpresaClienteServerSide();
      $scope.accion22 = 'edit'; 
    }

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnNuevaEmpresa = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'empresaCliente/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationEmpresaClienteServerSide) {
          $scope.accion = 'reg';
          $scope.getPaginationEmpresaClienteServerSide = getPaginationEmpresaClienteServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de cliente corporativo';
          $scope.fData.pertenece_salud_ocup = 2;
          $scope.disabledPSO = false;
          if( $scope.fSessionCI.key_group == 'key_salud_ocup' ){ 
            $scope.fData.pertenece_salud_ocup = 1;
            $scope.disabledPSO = true;
          }
          /* AUTOCOMPLETE EMPRESAS */ 
          $scope.getEmpresasAutocomplete = function(val) { 
            var params = {
              search: val,
              sensor: false
            }
            return empresasClienteServices.sListarEmpresasCbo(params).then(function(rpta) {
              var empresas = rpta.datos.map(function(e) {
                return e.descripcion;
              });
              return empresas;
            });
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            empresasClienteServices.sRegistrar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationEmpresaClienteServerSide();

                
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
          getPaginationEmpresaClienteServerSide: function() {
            return $scope.getPaginationEmpresaClienteServerSide;
          }
        }
      });
    }
    $scope.btnEditar = function (size) {
      var parentScope = $scope.$new();
      $modal.open({
        templateUrl: angular.patchURLCI+'empresaCliente/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationEmpresaClienteServerSide) {
          //console.log(parentScope.gridOptions,$scope.gridOptions);
          $scope.accion = 'edit';
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationEmpresaClienteServerSide = getPaginationEmpresaClienteServerSide;
          $scope.fData = {};
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de empresa';
          $scope.disabledPSO = false;
          if( $scope.fSessionCI.key_group == 'key_salud_ocup' ){ 
            $scope.fData.pertenece_salud_ocup = 1;
            $scope.disabledPSO = true;
          }
          // SEDE    
          sedeServices.sListarSedeCbo().then(function (rpta) {
            $scope.listaSede = rpta.datos;
            $scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});

          });
          $scope.cancel = function () {
            //console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            
            $scope.getPaginationEmpresaClienteServerSide();
          }
          $scope.aceptar = function () { 
            empresasClienteServices.sEditar($scope.fData).then(function (rpta) { 
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $scope.getPaginationEmpresaClienteServerSide();
            });
          }
          //console.log($scope.mySelectionGrid);
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          },
          getPaginationEmpresaClienteServerSide: function() {
            return $scope.getPaginationEmpresaClienteServerSide;
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          empresasClienteServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationEmpresaClienteServerSide();
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
        description: 'Nueva empresa',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar empresa',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular empresa',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar empresa',
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
  .service("empresasClienteServices",function($http, $q) {
    return({
      sListarEmpresasSoloSaludOcup:sListarEmpresasSoloSaludOcup,
      sListarEmpresasClientePorAutocompletado:sListarEmpresasClientePorAutocompletado,
      sListarEmpresas: sListarEmpresas,
      sListarEmpresasCbo: sListarEmpresasCbo,
      sRegistrar: sRegistrar,
      sEditar: sEditar,
      sAnular: sAnular
    });
    function sListarEmpresasSoloSaludOcup (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/lista_empresas_salud_ocupacional_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasClientePorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/lista_empresas_cliente_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/lista_empresas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/lista_empresas_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
 
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaCliente/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });