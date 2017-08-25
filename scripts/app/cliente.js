angular.module('theme.cliente', ['theme.core.services'])
  .controller('clienteController', ['$scope', '$sce', '$filter', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',
    'clienteServices',
    'convenioServices',
    'tipoViaServices',
    'tipoZonaServices',
    'ubigeoServices',
    'zonaServices',
    'empresasClienteServices',
    'procedenciaServices',
    function($scope, $sce, $filter, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys,
      clienteServices,
      convenioServices,
      tipoViaServices,
      tipoZonaServices,
      ubigeoServices,
      zonaServices,
      empresasClienteServices,
      procedenciaServices
    ) {
    // 'use strict';
    $scope.initCliente = function () {
      $scope.fData = {};
      var paginationOptions = {
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };

      console.log("sesion :",$scope.fSessionCI);
      $scope.mySelectionGrid = [];
      $scope.btnToggleFiltering = function(){
        $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
        $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
      };
      $scope.navegateToCell = function( rowIndex, colIndex ) {
        $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
      }; 
      $scope.gridOptions = {
        rowHeight: 36,
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
        multiSelect: false,
        columnDefs: [
          { field: 'id', name: 'idcliente', displayName: 'ID', width: '4%',  sort: { direction: uiGridConstants.DESC} },
          { field: 'num_documento', name: 'num_documento', displayName: 'N° Doc.', width: '7%' },
          { field: 'nombres', name: 'nombres', displayName: 'Nombres', width: '15%' },
          { field: 'apellidos', name: 'apellido_paterno', displayName: 'Apellidos', width: '15%' },
          { field: 'edad', name: 'fecha_nacimiento', displayName: 'Edad', cellTemplate:'<b class="text-center center-block" style="margin: 5px;"> {{ COL_FIELD + " años" }}</b>', enableFiltering: false, width: '6%' },
          { field: 'celular', name: 'celular', displayName: 'Celular', type:'number', width: '8%' },
          { field: 'email', name: 'email', displayName: 'E-mail', width: '11%' },
          { field: 'direccion', name: 'direccion', displayName: 'Dirección', type:'number', width: '14%' },
          { field: 'departamento', name: 'departamento', displayName: 'Departamento', width: '9%' },
          { field: 'provincia', name: 'provincia', displayName: 'Provincia', width: '9%' },
          { field: 'distrito', name: 'distrito', displayName: 'Distrito', width: '9%' }
        ],
        onRegisterApi: function(gridApi) { // gridComboOptions
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
              'cl.idcliente' : grid.columns[1].filters[0].term,
              'num_documento' : grid.columns[2].filters[0].term,
              'cl.nombres' : grid.columns[3].filters[0].term,
              'apellido_paterno' : grid.columns[4].filters[0].term,
              'celular' : grid.columns[6].filters[0].term,
              'email' : grid.columns[7].filters[0].term,
              'direccion' : grid.columns[8].filters[0].term,
              'dpto.descripcion_ubig' : grid.columns[9].filters[0].term,
              'prov.descripcion_ubig' : grid.columns[10].filters[0].term,
              'dist.descripcion_ubig' : grid.columns[11].filters[0].term
            }
            $scope.getPaginationServerSide();
          });
        }
      };
      paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
      $scope.getPaginationServerSide = function() {
        //$scope.$parent.blockUI.start();
        $scope.datosGrid = {
          paginate : paginationOptions
        };
        clienteServices.sListarClientes($scope.datosGrid).then(function (rpta) {
          console.log(rpta);
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;

          //$scope.$parent.blockUI.stop();
        });
        $scope.mySelectionGrid = [];
      };
      $scope.getPaginationServerSide();
    }


    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'cliente/ver_popup_formulario',
        size: size || '',
        scope: $scope,
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.listaSexos = arrToModal.listaSexos;
          $scope.mySelectionGrid = arrToModal.mySelectionGrid;
          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.dateUI = arrToModal.dateUI;
          //$scope.obtenerDepartamentoPorCodigoTr = arrToModal.obtenerDepartamentoPorCodigo;
          $scope.fData = {};

          if( $scope.modulo == 'venta' || $scope.modulo == 'solicitudFormula' ){
            //console.log($scope.fDataVenta.cliente);
            $scope.fData = $scope.fDataVenta.cliente; /*los datos del paciente en el formulario venta son enviados a editar*/
          }else if( $scope.modulo == 'pedido' ){
            $scope.fData = $scope.fDataPedido.cliente;
          }
          else{
            if( $scope.mySelectionGrid.length == 1 ){
              $scope.fData = $scope.mySelectionGrid[0];
            }else{
              alert('Seleccione una sola fila');
            }
          }
          $scope.titleForm = 'Edición de cliente';
          $scope.disabledPSO = false;
          //$scope.fData.idusuariocreacion = $scope.fSessionCI.idusers;
          if( $scope.fSessionCI.key_group == 'key_salud_ocup' ){ 
            $scope.fData.pertenece_salud_ocup = 1;
            $scope.disabledPSO = true;
          }
           // TIPO DE CLIENTE
          convenioServices.sListarConvenioCbo().then(function (rpta) {
            $scope.listaTiposClientes = angular.copy(rpta.datos);
            $scope.listaTiposClientes.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de cliente--'});
            if($scope.fData.idtipocliente == null){
              $scope.fData.idtipocliente = $scope.listaTiposClientes[0].id;
            }
          });
          // TIPO VIA
          tipoViaServices.sListarTipoViaCbo().then(function (rpta) {
            $scope.listaTipoVias = rpta.datos;
            $scope.listaTipoVias.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de vía--'});
            if($scope.fData.idtipovia == null){
              $scope.fData.idtipovia = $scope.listaTipoVias[0].id;
            }
          });
          // TIPO ZONA
          tipoZonaServices.sListarTipoZonaCbo().then(function (rpta) {
            $scope.listaTipoZonas = rpta.datos;
            $scope.listaTipoZonas.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de zona--'});
            if($scope.fData.idtipozona == null){
              $scope.fData.idtipozona = $scope.listaTipoZonas[0].id;
            }
          });
          // PROCEDENCIA
          procedenciaServices.sListarProcedenciaCbo().then(function (rpta) {
            $scope.listaProcedencias = rpta.datos;
            $scope.listaProcedencias.splice(0,0,{ id : '', descripcion:'--Seleccione Procedencia--'});  
            if($scope.fData.idprocedencia == null){
              $scope.fData.idprocedencia = $scope.listaProcedencias[0].id;
            }          
          });

          //=============================================================
          // AUTOCOMPLETADO EMPRESA CLIENTE - EDITAR 
          //=============================================================
            $scope.getEmpresaClienteAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return empresasClienteServices.sListarEmpresasClientePorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsEmpresaCliente = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsEmpresaCliente = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedEmpresaCliente = function ($item, $model, $label) {
                $scope.fData.idempresacliente = $item.id;
            };
            $scope.getClearInputEmpresaCliente = function () { 
              if(!angular.isObject($scope.fData.empresacliente) ){ 
                $scope.fData.idempresacliente = null; 
              }
            }
          //=============================================================
          // UBIGEO - EDITAR
          //=============================================================
            $scope.getDepartamentoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return ubigeoServices.sListarDepartamentoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLD = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLD = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getProvinciaAutocomplete = function (value) {
              var params = {
                search: value,
                id: $scope.fData.iddepartamento,
                sensor: false
              }
              return ubigeoServices.sListarProvinciaPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLP = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLP = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getDistritoAutocomplete = function (value) {
              //console.log($scope.fData.idprovincia);
              var params = {
                search: value,
                id_dpto: $scope.fData.iddepartamento,
                id_prov: $scope.fData.idprovincia,
                sensor: false
              }
              return ubigeoServices.sListarDistritoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLDis = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLDis = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedDepartamento = function ($item, $model, $label) {
                $scope.fData.iddepartamento = $item.id;
                $scope.fData.idprovincia = null;
                $scope.fData.provincia = null;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getSelectedProvincia = function ($item, $model, $label) {
                $scope.fData.idprovincia = $item.id;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getSelectedDistrito = function ($item, $model, $label) {
                $scope.fData.iddistrito = $item.id;
            };
            $scope.verPopupListaDptos = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  ubigeoServices.sListarDepartamentos().then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Departamentos.';
                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.iddepartamento == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.iddepartamento = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.departamento = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadepartamento').focus();
                      $scope.fData.idprovincia = null;
                      $scope.fData.provincia = null;
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_ubig';
                      ubigeoServices.sListarDepartamentos($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.iddepartamento = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.departamento = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadepartamento').focus();
                      $scope.fData.idprovincia = null;
                      $scope.fData.provincia = null;
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                  });
                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.obtenerDepartamentoPorCodigo = function () {
              if( $scope.fData.iddepartamento ){
                var arrData = {
                  'codigo': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddepartamento = rpta.datos.id;
                    $scope.fData.departamento = rpta.datos.descripcion;
                    $('#fDatadepartamento').focus();
                  }
                });

              }
            }
            $scope.verPopupListaProvincias = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  ubigeoServices.sListarProvinciasDeDepartamento($scope.fData).then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Provincias.';
                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.idprovincia == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.idprovincia = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.provincia = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDataprovincia').focus();
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                    $scope.fpc.iddepartamento = $scope.fData.iddepartamento;
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_ubig';
                      $scope.fpc.iddepartamento = $scope.fData.iddepartamento;
                      ubigeoServices.sListarProvinciasDeDepartamento($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.idprovincia = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.provincia = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDataprovincia').focus();
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                  });
                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.obtenerProvinciaPorCodigo = function () {
              if( $scope.fData.idprovincia ){
                var arrData = {
                  'codigo': $scope.fData.idprovincia,
                  'iddepartamento': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarProvinciaDeDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.idprovincia = rpta.datos.id;
                    $scope.fData.provincia = rpta.datos.descripcion;
                    $('#fDataprovincia').focus();
                  }
                });

              }
            }
            $scope.verPopupListaDistritos = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  ubigeoServices.sListarDistritosDeProvincia($scope.fData).then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Distritos.';

                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.iddistrito == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.iddistrito = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.distrito = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadistrito').focus();
                    }
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_ubig';
                      $scope.fpc.iddepartamento = $scope.fData.iddepartamento;
                      $scope.fpc.idprovincia = $scope.fData.idprovincia;
                      ubigeoServices.sListarDistritosDeProvincia($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.iddistrito = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.distrito = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadistrito').focus();
                    }
                  });
                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.obtenerDistritoPorCodigo = function () {
              if( $scope.fData.iddistrito ){
                var arrData = {
                  'codigo': $scope.fData.iddistrito,
                  'iddepartamento': $scope.fData.iddepartamento,
                  'idprovincia': $scope.fData.idprovincia
                }
                ubigeoServices.sListarDistritosDeProvinciaPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddistrito = rpta.datos.id;
                    $scope.fData.distrito = rpta.datos.descripcion;
                    $('#fDatadistrito').focus();
                  }
                });

              }
            }
            $scope.limpiaDpto = function(){
              $scope.fData.departamento = null;
              $scope.fData.idprovincia = null;
              $scope.fData.provincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaIdDpto = function(){
              $scope.fData.iddepartamento = null;
              $scope.fData.idprovincia = null;
              $scope.fData.provincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaProv = function(){
              $scope.fData.provincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaIdProv = function(){
              $scope.fData.idprovincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaDist = function(){
              $scope.fData.distrito = null;
            }
            $scope.limpiaIdDist = function(){
              $scope.fData.iddistrito = null;
            }
          //=============================================================
          // DIRECCION - EDITAR
          //=============================================================
            $scope.getZonaAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return zonaServices.sListarZonaPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLZona = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLZona = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedZona = function ($item, $model, $label) {
                $scope.fData.idzona = $item.id;
            };
            $scope.verPopupListaZonas = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  zonaServices.sListarZonaCbo($scope.fData).then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Zonas.';
                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.idzona == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.idzona = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.zona = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatazona').focus();
                    }
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_zo';
                      $scope.fpc.lista = null;
                      zonaServices.sListarZonaCbo($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.idzona = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.zona = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatazona').focus();
                    }
                  });
                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.generateDireccion = function (idTipoVia, idTipoZona) {
              //console.log(item);
              //console.log(idTipoVia, idTipoZona, $scope.listaTipoZonas);
              var copyListaTipoVia = angular.copy($scope.listaTipoVias);
              $scope.fTipoVia = $filter('getRowSelect')(copyListaTipoVia, idTipoVia);
              $scope.fTipoZona = $filter('getRowSelect')($scope.listaTipoZonas, idTipoZona);
              var strNumero = '';
              if(!$scope.fTipoVia.id){
                $scope.fTipoVia.descripcion = '';
              }
              if(!$scope.fData.nombre_via){
                $scope.fData.nombre_via = '';
              }
              if( !$scope.fData.zona ){
                $scope.fData.zona = '';
              }
              if($scope.fData.numero){
                strNumero = ' N°. '+$scope.fData.numero;
              }
              var strKilometro = '';
              if($scope.fData.kilometro){
                strKilometro = ' Km. '+$scope.fData.kilometro;
              }
              var strGrupo = '';
              if($scope.fData.grupo){
                strGrupo = ' Gr. '+$scope.fData.grupo;
              }
              var strSector = '';
              if($scope.fData.sector){
                strSector = ' Sc. '+$scope.fData.sector;
              }
              var strManzana = '';
              if($scope.fData.manzana){
                strManzana = ' Mz. '+$scope.fData.manzana;
              }
              var strLote = '';
              if($scope.fData.lote){
                strLote = ' Lt. '+$scope.fData.lote;
              }
              var strInterior = '';
              if($scope.fData.interior){
                strInterior = ' Int. '+$scope.fData.interior;
              }
              var strDpto = '';
              if($scope.fData.numero_departamento){
                strDpto = ' Dpto. '+$scope.fData.numero_departamento;
              }
              $scope.fData.direccion = $scope.fTipoVia.descripcion + " " + $scope.fData.nombre_via + strNumero + strKilometro + strGrupo + strSector + strManzana + strLote + strInterior + strDpto + " " + $scope.fData.zona
            }

          
          // VERIFICAMOS SI YA EXISTE UN REGISTRO CON IGUAL NOMBRES-APELLIDOS
          $scope.verificarCli = function () {
           clienteServices.sValidarClienteExiste($scope.fData).then(function (rpta) {
              if(rpta.flag === 1){ // existe
                //var pMensaje = 'YA EXISTE UN PACIENTE CON LOS DATOS INGRESADOS. ¿Realmente desea realizar la acción?';
                var pTitle = 'Error!';
                var message = ' YA EXISTE UN PACIENTE CON LOS DATOS INGRESADOS.';
                var pType = 'error';
                pinesNotifications.notify({ title: pTitle, text: message, type: pType, delay: 2000 });
              }else{
                $scope.aceptar();
              }
            });
          }
          $scope.aceptar = function () {
            $scope.fData.fecha_nacimiento = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd');
            console.log("datos gen:",$scope.fData);
            clienteServices.sEditar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                if( $scope.modulo === 'venta' || $scope.modulo == 'solicitudFormula' ){
                  //console.log($scope.fData);
                  $scope.fDataVenta.numero_documento = $scope.fData.num_documento;
                }else if($scope.modulo === 'pedido'){
                  $scope.fDataPedido.numero_documento = $scope.fData.num_documento;
                }
                $scope.fData = {};
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }


              if( $scope.modulo !== 'venta' && $scope.modulo !== 'solicitudFormula' && $scope.modulo !== 'pedido' ){
                $scope.getPaginationServerSide();
              }else if($scope.modulo == 'solicitudFormula'){
                setTimeout(function() {
                  $('#temporalMedico').focus();
                }, 1000);
              }
              else{
                setTimeout(function() {
                  $('#temporalProducto').focus();
                  $('#temporalEspecialidad').focus();
                }, 1000);
              }

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            if($scope.modulo !== 'venta' && $scope.modulo !== 'pedido' && $scope.modulo !== 'solicitudFormula') {
              $scope.getPaginationServerSide();
            }
          }
          /* ATAJOS DE TECLADO */
          hotkeys.bindTo($scope)
            .add({
              combo: 'ins',
              description: 'Ejecutar acción',
              callback: function() {
                if(!$scope.formCliente.$invalid) {
                  $scope.aceptar();
                }
              }
            });
        },
        resolve: {
          arrToModal: function() {
            return {
              mySelectionGrid: $scope.mySelectionGrid,
              getPaginationServerSide : $scope.getPaginationServerSide,
              dirImages : $scope.dirImages,
              dateUI : $scope.dateUI,
              listaSexos : $scope.listaSexos
            }
          }
        }
      });
    }
    //console.log(blockUI);
    $scope.btnNuevoCliente = function (size, num_documento, idtipocliente,fnCallBack) { 
      //$scope.$parent.blockUI.start();
      $scope.num_doc = num_documento || null;
      $modal.open({
        templateUrl: angular.patchURLCI+'cliente/ver_popup_formulario',
        size: size || '',
        scope: $scope,
        
        controller: function ($scope, $modalInstance, arrToModal) {
          //console.log($scope.num_doc, 'num_documento');
          $scope.fData = {};
          $scope.listaSexos = arrToModal.listaSexos;
          $scope.fData.sexo = $scope.listaSexos[0].id;
          $scope.fData.pertenece_salud_ocup = 2;
          $scope.disabledPSO = false;
          if( $scope.fSessionCI.key_group == 'key_salud_ocup' ){ 
            $scope.fData.pertenece_salud_ocup = 1;
            $scope.disabledPSO = true;
          }

          //llamada externa
          $scope.boolExterno = false;
          if($scope.modulo === 'convenio'){
            $scope.boolExterno = true;
          }

          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.gridComboOptions = arrToModal.gridComboOptions;
          $scope.mySelectionComboGrid = arrToModal.mySelectionComboGrid;
          $scope.dateUI = arrToModal.dateUI;
          //$scope.fData.idtipocliente = 1;
          $scope.titleForm = 'Registro de cliente';
          $scope.fData.num_documento = $scope.num_doc;
          $scope.fData.idusuariocreacion = $scope.fSessionCI.idusers;          
           // TIPO DE CLIENTE
          convenioServices.sListarConvenioCbo().then(function (rpta) {
            $scope.listaTiposClientes = angular.copy(rpta.datos);
            $scope.listaTiposClientes.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de cliente--'});
            $scope.fData.idtipocliente = $scope.listaTiposClientes[0].id;
            if($scope.boolExterno && $scope.modulo==='convenio'){
              $scope.fData.idtipocliente = idtipocliente;
            }
          });

          // TIPO VIA
          tipoViaServices.sListarTipoViaCbo().then(function (rpta) {
            $scope.listaTipoVias = rpta.datos;
            $scope.listaTipoVias.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de vía--'});
            $scope.fData.idtipovia = $scope.listaTipoVias[0].id;
            
          });
          // TIPO ZONA
          tipoZonaServices.sListarTipoZonaCbo().then(function (rpta) {
            $scope.listaTipoZonas = rpta.datos;
            $scope.listaTipoZonas.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de zona--'});
            $scope.fData.idtipozona = $scope.listaTipoZonas[0].id;
          });
          // PROCEDENCIA
          procedenciaServices.sListarProcedenciaCbo().then(function (rpta) {
            $scope.listaProcedencias = rpta.datos;
            $scope.listaProcedencias.splice(0,0,{ id : '', descripcion:'--Seleccione Procedencia--'});
            $scope.fData.idprocedencia = $scope.listaProcedencias[0].id;
          });

          //=============================================================
          // AUTOCOMPLETADO EMPRESA CLIENTE - NUEVO
          //=============================================================
            $scope.getEmpresaClienteAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return empresasClienteServices.sListarEmpresasClientePorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsEmpresaCliente = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsEmpresaCliente = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedEmpresaCliente = function ($item, $model, $label) {
                $scope.fData.idempresacliente = $item.id;
            };
            $scope.getClearInputEmpresaCliente = function () { 
              if(!angular.isObject($scope.fData.empresacliente) ){ 
                $scope.fData.idempresacliente = null; 
              }
            }
          
          //=============================================================
          // UBIGEO - NUEVO
          //=============================================================
            $scope.getDepartamentoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return ubigeoServices.sListarDepartamentoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLD = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLD = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getProvinciaAutocomplete = function (value) {
              var params = {
                search: value,
                id: $scope.fData.iddepartamento,
                sensor: false
              }
              return ubigeoServices.sListarProvinciaPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLP = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLP = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getDistritoAutocomplete = function (value) {
              console.log($scope.fData.idprovincia);
              var params = {
                search: value,
                id_dpto: $scope.fData.iddepartamento,
                id_prov: $scope.fData.idprovincia,
                sensor: false
              }
              return ubigeoServices.sListarDistritoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLDis = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLDis = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedDepartamento = function ($item, $model, $label) {
                $scope.fData.iddepartamento = $item.id;
                $scope.fData.idprovincia = null;
                $scope.fData.provincia = null;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getSelectedProvincia = function ($item, $model, $label) {
                $scope.fData.idprovincia = $item.id;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getSelectedDistrito = function ($item, $model, $label) {
              $scope.fData.iddistrito = $item.id;
            };
            $scope.verPopupListaDptos = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                // scope: scope,
                controller: function ($scope, $modalInstance, arrToModal) {
                  //console.log(scope.blockUI);
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  ubigeoServices.sListarDepartamentos().then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Departamentos.';
                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.iddepartamento == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.iddepartamento = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.departamento = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadepartamento').focus();
                      $scope.fData.idprovincia = null;
                      $scope.fData.provincia = null;
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_ubig';
                      ubigeoServices.sListarDepartamentos($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.iddepartamento = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.departamento = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadepartamento').focus();
                      $scope.fData.idprovincia = null;
                      $scope.fData.provincia = null;
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                  });


                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.obtenerDepartamentoPorCodigo = function () {
              if( $scope.fData.iddepartamento ){
                var arrData = {
                  'codigo': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddepartamento = rpta.datos.id;
                    $scope.fData.departamento = rpta.datos.descripcion;
                    $('#fDatadepartamento').focus();
                  }
                });

              }
            }
            $scope.verPopupListaProvincias = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  ubigeoServices.sListarProvinciasDeDepartamento($scope.fData).then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Provincias.';
                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.idprovincia == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.idprovincia = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.provincia = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDataprovincia').focus();
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                    $scope.fpc.iddepartamento = $scope.fData.iddepartamento;
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_ubig';
                      $scope.fpc.iddepartamento = $scope.fData.iddepartamento;
                      ubigeoServices.sListarProvinciasDeDepartamento($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.idprovincia = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.provincia = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDataprovincia').focus();
                      $scope.fData.iddistrito = null;
                      $scope.fData.distrito = null;
                    }
                  });
                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.obtenerProvinciaPorCodigo = function () {
              if( $scope.fData.idprovincia ){
                var arrData = {
                  'codigo': $scope.fData.idprovincia,
                  'iddepartamento': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarProvinciaDeDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.idprovincia = rpta.datos.id;
                    $scope.fData.provincia = rpta.datos.descripcion;
                    $('#fDataprovincia').focus();
                  }
                });

              }
            }
            $scope.verPopupListaDistritos = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  ubigeoServices.sListarDistritosDeProvincia($scope.fData).then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Distritos.';

                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.iddistrito == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.iddistrito = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.distrito = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadistrito').focus();
                    }
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_ubig';
                      $scope.fpc.iddepartamento = $scope.fData.iddepartamento;
                      $scope.fpc.idprovincia = $scope.fData.idprovincia;
                      ubigeoServices.sListarDistritosDeProvincia($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.iddistrito = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.distrito = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatadistrito').focus();
                    }
                  });
                  hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.obtenerDistritoPorCodigo = function () {
              if( $scope.fData.iddistrito ){
                var arrData = {
                  'codigo': $scope.fData.iddistrito,
                  'iddepartamento': $scope.fData.iddepartamento,
                  'idprovincia': $scope.fData.idprovincia
                }
                ubigeoServices.sListarDistritosDeProvinciaPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddistrito = rpta.datos.id;
                    $scope.fData.distrito = rpta.datos.descripcion;
                    $('#fDatadistrito').focus();
                  }
                });
              }
            }
            $scope.limpiaDpto = function(){
              $scope.fData.departamento = null;
              $scope.fData.idprovincia = null;
              $scope.fData.provincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaIdDpto = function(){
              $scope.fData.iddepartamento = null;
              $scope.fData.idprovincia = null;
              $scope.fData.provincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaProv = function(){
              $scope.fData.provincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaIdProv = function(){
              $scope.fData.idprovincia = null;
              $scope.fData.iddistrito = null;
              $scope.fData.distrito = null;
            }
            $scope.limpiaDist = function(){
              $scope.fData.distrito = null;
            }
            $scope.limpiaIdDist = function(){
              $scope.fData.iddistrito = null;
            }
          //=============================================================
          // DIRECCION - NUEVO 
          //=============================================================
            $scope.getZonaAutocomplete = function (value) {
              var params = {
                search: value,
                //valor : 'texto',
                sensor: false
              }
              return zonaServices.sListarZonaPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLZona = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLZona = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedZona = function ($item, $model, $label) {
              $scope.fData.idzona = $item.id;
            };
            $scope.verPopupListaZonas = function (size) {
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionComboGrid = [];
                  $scope.gridComboOptions = {
                    paginationPageSizes: [10, 50, 100, 500, 1000],
                    paginationPageSize: 10,
                    enableRowSelection: true,
                    enableSelectAll: false,
                    enableFiltering: false,
                    enableFullRowSelection: true,
                    multiSelect: false,
                    columnDefs: [
                      { field: 'id', displayName: 'ID', maxWidth: 80 },
                      { field: 'descripcion', displayName: 'Descripción' }
                    ]
                    ,onRegisterApi: function(gridApiCombo) {
                      $scope.gridApiCombo = gridApiCombo;
                      gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                        $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
                      });
                    }
                  }
                  zonaServices.sListarZonaCbo($scope.fData).then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Zonas.';
                    $scope.gridComboOptions.data = rpta.datos;
                    angular.forEach($scope.gridComboOptions.data,function(val,index) {
                      if( $scope.fData.idzona == val.id ){
                        $timeout(function() {
                          if($scope.gridApiCombo.selection.selectRow){
                            $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                          }
                        });
                      }
                    });
                    $scope.fpc.aceptar = function () {
                      $scope.fData.idzona = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.zona = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatazona').focus();
                    }
                    $scope.fpc.buscar = function () {
                      $scope.fpc.nameColumn = 'descripcion_zo';
                      zonaServices.sListarZonaCbo($scope.fpc).then(function (rpta) {
                        $scope.gridComboOptions.data = rpta.datos;
                      });
                    }
                    $scope.fpc.seleccionar = function () {
                      $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                        $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                        $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
                      }
                      $scope.fData.idzona = $scope.mySelectionComboGrid[0].id;
                      $scope.fData.zona = $scope.mySelectionComboGrid[0].descripcion;
                      $modalInstance.dismiss('cancel');
                      $('#fDatazona').focus();
                    }
                    hotkeys.bindTo($scope)
                    .add({
                      combo: 'a',
                      description: 'Ejecutar acción',
                      callback: function() {
                        $scope.fpc.aceptar();
                      }
                    });
                  });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData
                    }
                  }
                }
              });
            }
            $scope.generateDireccion = function (idTipoVia, idTipoZona) {
              //console.log(item);
              //console.log(idTipoVia, idTipoZona, $scope.listaTipoZonas);
              var copyListaTipoVia = angular.copy($scope.listaTipoVias);
              $scope.fTipoVia = $filter('getRowSelect')(copyListaTipoVia, idTipoVia);
              $scope.fTipoZona = $filter('getRowSelect')($scope.listaTipoZonas, idTipoZona);
              var strNumero = '';
              if(!$scope.fTipoVia.id){
                $scope.fTipoVia.descripcion = '';
              }
              if(!$scope.fData.nombre_via){
                $scope.fData.nombre_via = '';
              }
              if( !$scope.fData.zona ){
                $scope.fData.zona = '';
              }
              if($scope.fData.numero){
                strNumero = ' N°. '+$scope.fData.numero;
              }
              var strKilometro = '';
              if($scope.fData.kilometro){
                strKilometro = ' Km. '+$scope.fData.kilometro;
              }
              var strGrupo = '';
              if($scope.fData.grupo){
                strGrupo = ' Gr. '+$scope.fData.grupo;
              }
              var strSector = '';
              if($scope.fData.sector){
                strSector = ' Sc. '+$scope.fData.sector;
              }
              var strManzana = '';
              if($scope.fData.manzana){
                strManzana = ' Mz. '+$scope.fData.manzana;
              }
              var strLote = '';
              if($scope.fData.lote){
                strLote = ' Lt. '+$scope.fData.lote;
              }
              var strInterior = '';
              if($scope.fData.interior){
                strInterior = ' Int. '+$scope.fData.interior;
              }
              var strDpto = '';
              if($scope.fData.numero_departamento){
                strDpto = ' Dpto. '+$scope.fData.numero_departamento;
              }
              $scope.fData.direccion = $scope.fTipoVia.descripcion + " " + $scope.fData.nombre_via + strNumero + strKilometro + strGrupo + strSector + strManzana + strLote + strInterior + strDpto + " " + $scope.fData.zona
            }
          
          // VERIFICAMOS SI YA EXISTE UN REGISTRO CON IGUAL NOMBRES-APELLIDOS
          $scope.verificarCli = function () {
            clienteServices.sValidarClienteExiste($scope.fData).then(function (rpta) {
              if(rpta.flag === 1){ // existe
                var pTitle = 'Error!';
                var message = ' YA EXISTE UN PACIENTE CON LOS DATOS INGRESADOS.';
                var pType = 'error';
                pinesNotifications.notify({ title: pTitle, text: message, type: pType, delay: 2000 });
              }else{
                $scope.aceptar();
              }
            });
          }
          // VERIFICAR SI EXISTE EN DB RENIEC 
          $scope.verificaDNI = function () {
            if($scope.fData.num_documento) {
              if($scope.fData.num_documento.length === 8){
                $scope.fData.procedencia = 'cliente';
                clienteServices.sVerificarCliente($scope.fData).then(function (rpta) {
                  if(rpta.flag == 1){ 
                      pTitle = 'OK!';
                      pType = 'success';
                      //$scope.fData.dni = rpta.datos.dni;
                      $scope.fData.nombres = rpta.datos.Nombres;
                      $scope.fData.apellido_paterno = rpta.datos.Ape_Pat;
                      $scope.fData.apellido_materno = rpta.datos.Ape_Mat;
                      $scope.fData.fecha_nacimiento = rpta.datos.fecha_nacimiento;
                      $scope.fData.sexo = rpta.datos.sexo;
                      console.log($scope.fData.nombres);
                      //$scope.getPaginationServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Oops';
                      var pType = 'danger';
                    }else{
                      // alert('Error inesperado');
                      pinesNotifications.notify({ title: 'Advertencia', text: 'No se pudo conectar a la BD de la RENIEC', type: 'warning', delay: 2000 }); 
                      return false;
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1500 });
                });
              }else {
                $scope.fData.nombres = null;
                $scope.fData.apellido_paterno = null;
                $scope.fData.apellido_materno = null;
                $scope.fData.fecha_nacimiento = null;
                $scope.fData.sexo = null;
              }
            }
          }
          // BOTONES 
          $scope.aceptar = function () { 
            $scope.fData.fecha_nacimiento = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd');
            console.log("datos gen:",$scope.fData);
            clienteServices.sRegistrar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');                
                if( $scope.modulo === 'venta' ){
                  var arrCliente = {
                    'idcliente' : rpta.idcliente,
                    'numero_documento' : $scope.fData.num_documento
                  }
                  clienteServices.sListarEsteCliente(arrCliente).then(function (rpta) {
                    $scope.fDataVenta.cliente = rpta.datos[0];
                    $scope.fDataVenta.numero_documento = rpta.datos[0].num_documento;
                  });
                  setTimeout(function() {
                    $('#temporalProducto').focus();
                    $('#temporalEspecialidad').focus();
                  }, 1000);
                  
                  // $scope.fDataVenta.cliente = $scope.fData;
                  // $scope.fDataVenta.numero_documento = $scope.fData.num_documento;
                }else if($scope.modulo === 'pedido'){
                  var arrCliente = {
                    'idcliente' : rpta.idcliente,
                    'numero_documento' : $scope.fData.num_documento
                  }
                  clienteServices.sListarEsteCliente(arrCliente).then(function (rpta) {
                    $scope.fDataPedido.cliente = rpta.datos[0];
                    $scope.fDataPedido.numero_documento = rpta.datos[0].num_documento;
                  });
                  setTimeout(function() {
                    $('#temporalProducto').focus();
                  }, 1000);
                }else if($scope.modulo === 'solicitudFormula'){
                  var arrCliente = {
                    'idcliente' : rpta.idcliente,
                    'numero_documento' : $scope.fData.num_documento
                  }
                  clienteServices.sListarEsteCliente(arrCliente).then(function (rpta) {
                    $scope.fDataVenta.cliente = rpta.datos[0];
                    $scope.fDataVenta.numero_documento = rpta.datos[0].num_documento;
                  });
                  setTimeout(function() {
                    $('#temporalMedico').focus();
                  }, 1000);
                }else if( $scope.modulo === 'trabajadorPerfiles' ){

                }else if( $scope.modulo === 'convenio' ){
                  fnCallBack();
                }else{
                  $scope.getPaginationServerSide();
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          /* ATAJOS DE TECLADO */
          hotkeys.bindTo($scope)
            .add({
              combo: 'ins',
              description: 'Ejecutar acción',
              callback: function() {
                if(!$scope.formCliente.$invalid) {
                  $scope.aceptar();
                }
              }
            });
        },
        resolve: {
          arrToModal: function() {
            return {
              dateUI: $scope.dateUI,
              getPaginationServerSide : $scope.getPaginationServerSide,
              listaSexos : $scope.listaSexos,
              gridComboOptions : $scope.gridComboOptions,
              mySelectionComboGrid : $scope.mySelectionComboGrid
            }
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          clienteServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
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
        description: 'Nuevo cliente',
        callback: function() {
          $scope.btnNuevoCliente("xlg");
        }
      })
      .add ({
        combo: 'e',
        description: 'Editar cliente',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar("xlg");
          }
        }
      })
      .add ({
        combo: 'del',
        description: 'Anular cliente',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({
        combo: 'b',
        description: 'Buscar cliente',
        callback: function() {
          if($scope.modulo !== 'venta') {
            $scope.btnToggleFiltering();
          }

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
  .service("clienteServices",function($http, $q) {
    return({
        sListarClientes: sListarClientes,
        sListarClienteHistoriaAutoComplete: sListarClienteHistoriaAutoComplete,
        sListarClientesOcupacionalAutocomplete: sListarClientesOcupacionalAutocomplete,
        sListarClientesOcupacionalConPerfilAutocomplete: sListarClientesOcupacionalConPerfilAutocomplete,
        sListarEmpresasCliente: sListarEmpresasCliente,
        sListarClienteVentaAutoComplete: sListarClienteVentaAutoComplete,
        sListarEsteCliente: sListarEsteCliente,
        sListarEsteClientePorNumDoc: sListarEsteClientePorNumDoc,
        sListarEsteClientePorHistoria: sListarEsteClientePorHistoria,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sVerificarCliente: sVerificarCliente,
        sValidarClienteExiste: sValidarClienteExiste,
        sAfiliarPuntos: sAfiliarPuntos,
        sComprobarAfiliacionPuntos: sComprobarAfiliacionPuntos,
        sActualizarDatosCliente:sActualizarDatosCliente,
    });

    function sListarClientes(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/lista_clientes",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarClientesOcupacionalAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/lista_clientes_ocupacional_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarClientesOcupacionalConPerfilAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/lista_clientes_perfiles_ocupacional_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasCliente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/lista_empresas_cliente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarClienteHistoriaAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cliente/lista_clientes_con_historia_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarClienteVentaAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/lista_clientes_venta_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEsteCliente (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/listar_este_cliente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEsteClientePorNumDoc (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/listar_este_cliente_por_num_doc",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEsteClientePorHistoria (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"cliente/listar_este_cliente_por_historia",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/registrar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/editar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/anular",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sVerificarCliente(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/buscar_dni",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sValidarClienteExiste(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/validar_si_cliente_existe",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAfiliarPuntos(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/afiliar_a_puntos",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sComprobarAfiliacionPuntos(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/comprobar_afiliacion_puntos",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarDatosCliente(datos){
       var request = $http({
            method : "post",
            url : angular.patchURLCI+"Cliente/actualizar_datos_cliente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });