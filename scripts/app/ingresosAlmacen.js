angular.module('theme.ingresosAlmacen', ['theme.core.services'])
  .controller('ingresosAlmacenController', ['$scope', '$filter','$route', '$routeParams', '$controller', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ingresoAlmacenServices',
    'unidadesLaboratorioServices',
    'reactivoInsumoServices',
    'empleadoServices', 
    'empresaAdminServices', 
    'empresaServices', 
    'proveedorServices', 
    'tipoDocumentoServices',
    'motivomovimientoServices',
    'consultaingresosAlmacenServices',
  function($scope,$filter, $route, $routeParams, $controller, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
    ingresoAlmacenServices,
    unidadesLaboratorioServices,
    reactivoInsumoServices,
    empleadoServices,
    empresaAdminServices,
    empresaServices,
    proveedorServices,
    tipoDocumentoServices,
    motivomovimientoServices,
    consultaingresosAlmacenServices){ 
    'use strict'; 
    $scope.modulo = 'ingresos Almacen'; 
    $scope.isRegisterSuccess = false;
    $scope.fDataAlmacen = {};
    $scope.fDataAlmacen.fecha = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fDataAlmacen.temporal = {
      reactivoInsumo : null
    };
    $scope.getreactivoInsumoAutocomplete = function (value) { 
      var params = {
        search: value, 
        sensor: false
      }
      return reactivoInsumoServices.sListarReactivoInsumoCbo(params).then(function(rpta) { 
        $scope.noResultsLPSC = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLPSC = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedReactivoInsumo = function ($item, $model, $label) {
      $scope.fDataAlmacen.temporal.idreactivoInsumo = $item.id;
      var arrData = {
        'id': $scope.fDataAlmacen.temporal.idreactivoInsumo
      }
      reactivoInsumoServices.sListarReactivoInsumoporCodigo(arrData).then(function (rpta) {
        if( rpta.flag == 1){
          $scope.fDataAlmacen.temporal.idreactivoInsumo = rpta.datos.id;
          $scope.fDataAlmacen.temporal.reactivoInsumo = rpta.datos.descripcion;
          $scope.fDataAlmacen.temporal.unidadLaboratorio = rpta.datos.nombre_unidad;
          $('#temporalCantidad').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
        }
      });
    };
    $scope.getProveedorAutocomplete = function (value) { 
      var params = {
        search: value, 
        sensor: false
      }
      return proveedorServices.sListarProveedorCbo(params).then(function(rpta) { 
        console.log(rpta.datos);
        $scope.noResultsLPSC = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLPSC = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedProveedor = function ($item, $model, $label) {
      $scope.fDataAlmacen.idproveedor = $item.id;
      var arrData = {
        'id': $scope.fDataAlmacen.idproveedor
      }
      proveedorServices.sListarProveedorporCodigo(arrData).then(function (rpta) {
        if( rpta.flag == 1){
          $scope.fDataAlmacen.idproveedor = rpta.datos.id;
          $scope.fDataAlmacen.proveedor = rpta.datos.razon_social;
          $('#fDataAlmacenproveedor').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
        }
      });
    };

    $scope.getEmpresaAutocomplete = function (value) { 
      var params = {
        search: value, 
        sensor: false
      }
      return empresaServices.sListarEmpresasCbo(params).then(function(rpta) { 
        $scope.noResultsLPSC = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLPSC = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedEmpresa = function ($item, $model, $label) {
      $scope.fDataAlmacen.idempresa = $item.id;
      var arrData = {
        'id': $scope.fDataAlmacen.idempresa
      }
      empresaServices.sListarEmpresaporCodigo(arrData).then(function (rpta) {
        if( rpta.flag == 1){
          $scope.fDataAlmacen.idempresa = rpta.datos.id;
          $scope.fDataAlmacen.empresa = rpta.datos.razon_social;
          $('#fDataAlmacenempresa').focus();
        }
      });
    };
    tipoDocumentoServices.sListarTipoDocumentoAlmacenlabCbo().then(function (rpta) {
      console.log("LISTA Tipo");
      console.log(rpta.datos);
      //console.log(rpta);
      $scope.listaTipoDocumento = rpta.datos;
      $scope.listaTipoDocumento.splice(0,0,{ id : '', descripcion:'--Seleccione Documento--'});
      $scope.fDataAlmacen.idtipodocumento = $scope.listaTipoDocumento[0].id; 
    });
    $scope.datosGrid = {
        search : 1
      };
    motivomovimientoServices.sListarmotivomovimientoTipo($scope.datosGrid).then(function (rpta) {
      $scope.listaMotivoMovimiento = rpta.datos;
      $scope.listaMotivoMovimiento.splice(0,0,{ id : '', descripcion:'--Seleccione Movimiento--'});
      $scope.fDataAlmacen.idmotivomovimiento = $scope.listaMotivoMovimiento[0].id; 
    });
    $scope.mySelectionGrid = [];
    $scope.gridOptions = { 
      paginationPageSize: 10,
      enableRowSelection: false,
      enableSelectAll: false,
      enableFiltering: false,
      enableFullRowSelection: false,
      data: null,
      rowHeight: 30,
      enableCellEditOnFocus: true,
      multiSelect: false,
      columnDefs: [
        { field: 'id', displayName: 'ID', width: '5%', enableCellEdit: false },
        { field: 'reactivoInsumo', displayName: 'Reactivo - Insumo.', width: '32%', enableCellEdit: false },
        { field: 'cantidad', displayName: 'Cantidad', width: '9%', enableCellEdit: false },
        { field: 'unidad', displayName: 'Unidad', width: '15%', enableCellEdit: false },
        { field: 'precio', displayName: 'Precio', width: '9%', enableCellEdit: true },
        { field: 'importe', displayName: 'Importe', width: '12%', enableCellEdit: false }, 
        { field: 'fechavencimiento', displayName: 'Fec.Vencim.', width: '10%', enableCellEdit: false }, 
        { field: 'accion', displayName: 'Acción', width: '6%', enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
      ]
      ,onRegisterApi: function(gridApiCombo) { 
        $scope.gridApiCombo = gridApiCombo; 
        gridApiCombo.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
        }); 
      }
    };
    $scope.getTableHeight = function() {
       var rowHeight = 30; // your row height 
       var headerHeight = 30; // your header height 
       return {
          height: ($scope.gridOptions.data.length * rowHeight + headerHeight + 30) + "px"
       };
    };

    $scope.verPopupReactivoInsumo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
        size: size || '',
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.fDataAlmacen = arrToModal.fDataAlmacen;
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
          reactivoInsumoServices.sListarReactivoInsumoCbo().then(function (rpta) {
            $scope.fpc = {};
            $scope.fpc.titulo = ' Reactivos - Insumos.';
            $scope.gridComboOptions.data = rpta.datos;
            angular.forEach($scope.gridComboOptions.data,function(val,index) {
              //$scope.fDataAlmacen = {};
              if( $scope.fDataAlmacen.temporal.idreactivoInsumo == val.id ){
                $timeout(function() {
                  if($scope.gridApiCombo.selection.selectRow){
                    $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                  }
                });
              }
            });
            $scope.fpc.aceptar = function () {
              $scope.fDataAlmacen.temporal.idreactivoInsumo = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.temporal.reactivoInsumo = $scope.mySelectionComboGrid[0].descripcion;
              var arrData = {
                  'id': $scope.fDataAlmacen.temporal.idreactivoInsumo
              }
              reactivoInsumoServices.sListarReactivoInsumoporCodigo(arrData).then(function (rpta) {
                if( rpta.flag == 1){
                  $scope.fDataAlmacen.temporal.idreactivoInsumo = rpta.datos.id;
                  $scope.fDataAlmacen.temporal.reactivoInsumo = rpta.datos.descripcion;
                  $scope.fDataAlmacen.temporal.unidadLaboratorio = rpta.datos.nombre_unidad;
                  $('#temporalCantidad').focus();
                  //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
                }
              });
              $modalInstance.dismiss('cancel');
              $('#fDataAlmacenreactivoInsumo').focus();
            }
            $scope.fpc.buscar = function () {
              $scope.fpc.nameColumn = 'descripcion';
              reactivoInsumoServices.sListarReactivoInsumoCbo($scope.fpc).then(function (rpta) {
                $scope.gridComboOptions.data = rpta.datos;
              });
            }
            $scope.fpc.seleccionar = function () {
              $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              }
              $scope.fDataAlmacen.temporal.idreactivoInsumo = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.temporal.reactivoInsumo = $scope.mySelectionComboGrid[0].descripcion;
              $modalInstance.dismiss('cancel');
              //$('#fDatadepartamento').focus();
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
                fDataAlmacen : $scope.fDataAlmacen
              }
            }
          }
          });
    }

    $scope.verPopupListaProveedores = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
        size: size || '',
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.fDataAlmacen = arrToModal.fDataAlmacen;
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
            { field: 'razon_social', displayName: 'Razon Social' }
            ]
            ,onRegisterApi: function(gridApiCombo) {
              $scope.gridApiCombo = gridApiCombo;
              gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
              });
            }
          }
          proveedorServices.sListarProveedorCbo().then(function (rpta) {
            $scope.fpc = {};
            $scope.fpc.titulo = ' Razón Social.';
            $scope.gridComboOptions.data = rpta.datos;
            angular.forEach($scope.gridComboOptions.data,function(val,index) {
              //$scope.fDataAlmacen = {};
              if( $scope.fDataAlmacen.idproveedor == val.id ){
                $timeout(function() {
                  if($scope.gridApiCombo.selection.selectRow){
                    $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                  }
                });
              }
            });
            $scope.fpc.aceptar = function () {
              $scope.fDataAlmacen.idproveedor = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.proveedor = $scope.mySelectionComboGrid[0].descripcion;
              var arrData = {
                  'id': $scope.fDataAlmacen.idproveedor
                }
                proveedorServices.sListarProveedorporCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fDataAlmacen.idproveedor = rpta.datos.id;
                    $scope.fDataAlmacen.proveedor = rpta.datos.razon_social;
                    $('#fDataAlmacenproveedor').focus();
                    //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
                  }
                });
              $modalInstance.dismiss('cancel');
              $('#fDataAlmacenproveedor').focus();
            }
            $scope.fpc.buscar = function () {
              $scope.fpc.nameColumn = 'razon_social';
              proveedorServices.sListarProveedorCbo($scope.fpc).then(function (rpta) {
                $scope.gridComboOptions.data = rpta.datos;
              });
            }
            $scope.fpc.seleccionar = function () {
              $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              }
              $scope.fDataAlmacen.idproveedor = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.proveedor = $scope.mySelectionComboGrid[0].descripcion;
              $modalInstance.dismiss('cancel');
              //$('#fDatadepartamento').focus();
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
                fDataAlmacen : $scope.fDataAlmacen
              }
            }
          }
          });
    }

    $scope.verPopupListaEmpresas = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
        size: size || '',
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.fDataAlmacen = arrToModal.fDataAlmacen;
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
            { field: 'descripcion', displayName: 'Razon Social' }
            ]
            ,onRegisterApi: function(gridApiCombo) {
              $scope.gridApiCombo = gridApiCombo;
              gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
              });
            }
          }
          empresaServices.sListarEmpresasCbo().then(function (rpta) {
            $scope.fpc = {};
            $scope.fpc.titulo = ' Razón Social.';
            $scope.gridComboOptions.data = rpta.datos;
            angular.forEach($scope.gridComboOptions.data,function(val,index) {
              //$scope.fDataAlmacen = {};
              if( $scope.fDataAlmacen.idempresa == val.id ){
                $timeout(function() {
                  if($scope.gridApiCombo.selection.selectRow){
                    $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                  }
                });
              }
            });
            $scope.fpc.aceptar = function () {
              $scope.fDataAlmacen.idempresa = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.empresa = $scope.mySelectionComboGrid[0].descripcion;
              var arrData = {
                  'id': $scope.fDataAlmacen.idempresa
                }
                empresaServices.sListarEmpresaporCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fDataAlmacen.idempresa = rpta.datos.id;
                    $scope.fDataAlmacen.empresa = rpta.datos.descripcion;
                    $('#fDataAlmacenempresa').focus();
                    //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
                  }
                });
              $modalInstance.dismiss('cancel');
              $('#fDataAlmacenempresa').focus();
            }
            $scope.fpc.buscar = function () {
              $scope.fpc.nameColumn = 'descripcion';
              empresaServices.sListarEmpresasCbo($scope.fpc).then(function (rpta) {
                $scope.gridComboOptions.data = rpta.datos;
              });
            }
            $scope.fpc.seleccionar = function () {
              $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              }
              $scope.fDataAlmacen.idempresa = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.empresa = $scope.mySelectionComboGrid[0].descripcion;
              $modalInstance.dismiss('cancel');
              //$('#fDatadepartamento').focus();
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
                fDataAlmacen : $scope.fDataAlmacen
              }
            }
          }
          });
    }

    $scope.obtenerReactivoInsumoPorCodigo = function () {
      if( $scope.fDataAlmacen.temporal.idreactivoInsumo ){
        var arrData = {
          'id': $scope.fDataAlmacen.temporal.idreactivoInsumo
        }
        reactivoInsumoServices.sListarReactivoInsumoporCodigo(arrData).then(function (rpta) {
          if( rpta.flag == 1){
            $scope.fDataAlmacen.temporal.idreactivoInsumo = rpta.datos.id;
            $scope.fDataAlmacen.temporal.reactivoInsumo = rpta.datos.descripcion;
            $scope.fDataAlmacen.temporal.unidadLaboratorio = rpta.datos.nombre_unidad;
            //$('#fDataAlmacenreactivoInsumo').focus();
            $('#temporalCantidad').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
          }else{
            $scope.fDataAlmacen.temporal.reactivoInsumo = null ;
            $scope.fDataAlmacen.temporal.unidadLaboratorio = null ;
          }
        });

      }
    }
    $scope.obtenerProveedorPorCodigo = function () {
      if( $scope.fDataAlmacen.idproveedor ){
        var arrData = {
          'id': $scope.fDataAlmacen.idproveedor
        }
        proveedorServices.sListarProveedorporCodigo(arrData).then(function (rpta) {
          if( rpta.flag == 1){
            $scope.fDataAlmacen.idproveedor = rpta.datos.id;
            $scope.fDataAlmacen.proveedor = rpta.datos.razon_social;
            $('#fDataAlmacenproveedor').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
          }else {
            $scope.fDataAlmacen.proveedor = null;
          }
        });

      }
    }
    $scope.obtenerEmpresaPorCodigo = function () {
      if( $scope.fDataAlmacen.idempresa ){
        var arrData = {
          'id': $scope.fDataAlmacen.idempresa
        }
        empresaServices.sListarEmpresaporCodigo(arrData).then(function (rpta) {
          if( rpta.flag == 1){
            $scope.fDataAlmacen.idempresa = rpta.datos.id;
            $scope.fDataAlmacen.empresa = rpta.datos.razon_social;
            $('#fDataAlmacenempresa').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
          }else{
            $scope.fDataAlmacen.empresa = null;
          }
        });

      }
    }

    $scope.btnQuitarDeLaCesta = function (row) { 
      var index = $scope.gridOptions.data.indexOf(row.entity); 
      $scope.gridOptions.data.splice(index,1); 
      $scope.calcularTotales();
      //$scope.calcularVuelto();
    }
    $scope.agregarItem = function () { 
      if(angular.isObject($scope.fDataAlmacen.temporal.reactivoInsumo)){ // console.log('especialidad');
        $scope.fDataAlmacen.temporal.reactivoInsumo = null;
        $('#temporalreactivoInsumo').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el Reactivo Insumo', type: 'warning', delay: 2000 });
        return false;
      }
      if(!angular.isNumber($scope.fDataAlmacen.temporal.cantidad)){ // console.log('especialidad');
        $scope.fDataAlmacen.temporal.cantidad = null;
        $('#temporalCantidad').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la cantidad', type: 'warning', delay: 2000 });
        return false;
      }
      //if(!angular.isNumber($scope.fDataAlmacen.temporal.precio)){ // console.log('especialidad');
      if(isNaN(parseFloat($scope.fDataAlmacen.temporal.precio))){
        $scope.fDataAlmacen.temporal.precio = null;
        $('#temporalPrecio').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el precio', type: 'warning', delay: 2000 });
        return false;
      }
      if($scope.fDataAlmacen.temporal.fechavencimiento == null){ // console.log('especialidad');
        $scope.fDataAlmacen.temporal.fechavencimiento = null;
        $('#temporalfechavencimiento').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la fecha de Vencimiento', type: 'warning', delay: 2000 });
        return false;
      }
      var productNew = true;
      angular.forEach($scope.gridOptions.data, function(value, key){ 
        if(value.id == $scope.fDataAlmacen.temporal.idreactivoInsumo ){ 
          productNew = false;
        }
      });
      if( productNew === false ){ 
        pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
        $scope.fDataAlmacen.temporal.reactivoInsumo= null;
        $scope.fDataAlmacen.temporal.cantidad= 1;
        return false;
      } 
      $scope.arrTemporal = { 
        'id' : $scope.fDataAlmacen.temporal.idreactivoInsumo,
        'reactivoInsumo' : $scope.fDataAlmacen.temporal.reactivoInsumo,
        'cantidad' : $scope.fDataAlmacen.temporal.cantidad,
        'unidad' : $scope.fDataAlmacen.temporal.unidadLaboratorio,
        'precio' : parseFloat($scope.fDataAlmacen.temporal.precio).toFixed(2),
        'importe' : (parseFloat($scope.fDataAlmacen.temporal.precio) * parseFloat($scope.fDataAlmacen.temporal.cantidad)).toFixed(2),
        'fechavencimiento' : $scope.fDataAlmacen.temporal.fechavencimiento ,
        'numerolote' : $scope.fDataAlmacen.temporal.numerolote
      }; 
      $scope.gridOptions.data.push($scope.arrTemporal);
      $scope.fDataAlmacen.temporal = {} ;
      $('#idtemporalreactivoInsumo').focus();
      $scope.calcularTotales(); 
    }
    $scope.calcularTotales = function () { 
      var totales = 0;
      angular.forEach($scope.gridOptions.data,function (value, key) { 
        totales += parseFloat($scope.gridOptions.data[key].importe);
      });
      $scope.fDataAlmacen.total = totales.toFixed(2);
    }
    $scope.grabar = function (param) { 
      var pParam = param || false; 
      $scope.fDataAlmacen.detalle = $scope.gridOptions.data;
      if( $scope.fDataAlmacen.detalle.length < 1 ){ 
        $('#temporalreactivoinsumo').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 2000 }); 
        return false; 
      }
      ingresoAlmacenServices.sRegistrarIngreso($scope.fDataAlmacen).then(function (rpta) { 
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success'; 
          $scope.isRegisterSuccess = true; 
          $scope.fDataAlmacen = {};
          $scope.gridOptions.data=[];
          $scope.fDataAlmacen.idtipodocumento = $scope.listaTipoDocumento[0].id; 
          $scope.fDataAlmacen.idmotivomovimiento = $scope.listaMotivoMovimiento[0].id;
          // $scope.fDataVenta = {}; 
          $route.reload();
        }else if(rpta.flag == 0){
          var pTitle = 'Error!';
          var pType = 'danger';
        }else{
          alert('Algo salió mal...');
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    }

    $scope.agregarItemEdicion = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          if(angular.isObject($scope.fDataAlmacen.temporal.reactivoInsumo)){ // console.log('especialidad');
            $scope.fDataAlmacen.temporal.reactivoInsumo = null;
            $('#temporalreactivoInsumo').focus();
            pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el Reactivo Insumo', type: 'warning', delay: 2000 });
            return false;
          }
          if(!angular.isNumber($scope.fDataAlmacen.temporal.cantidad)){ // console.log('especialidad');
            $scope.fDataAlmacen.temporal.cantidad = null;
            $('#temporalCantidad').focus();
            pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la cantidad', type: 'warning', delay: 2000 });
            return false;
          }
          if(isNaN(parseFloat($scope.fDataAlmacen.temporal.precio))){
          //if(!angular.isNumber($scope.fDataAlmacen.temporal.precio)){ // console.log('especialidad');
            $scope.fDataAlmacen.temporal.precio = null;
            $('#temporalPrecio').focus();
            pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el precio', type: 'warning', delay: 2000 });
            return false;
          }
          if($scope.fDataAlmacen.temporal.fechavencimiento == null){ // console.log('especialidad');
            $scope.fDataAlmacen.temporal.fechavencimiento = null;
            $('#temporalfechavencimiento').focus();
            pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la fecha de Vencimiento', type: 'warning', delay: 2000 });
            return false;
          }
          var productNew = true;
          angular.forEach($scope.gridOptionsDetalleIngreso.data, function(value, key){ 
            if(value.idreactivoinsumo == $scope.fDataAlmacen.temporal.idreactivoInsumo ){ 
              productNew = false;
            }
          });
          if( productNew === false ){ 
            pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
            $scope.fDataAlmacen.temporal = {};
            return false;
          } 
          $scope.arrTemporal = { 
            'id' : $scope.fDataAlmacen.temporal.idreactivoInsumo,
            'descripcion' : $scope.fDataAlmacen.temporal.reactivoInsumo,
            'cantidad' : $scope.fDataAlmacen.temporal.cantidad,
            'unidad' : $scope.fDataAlmacen.temporal.unidadLaboratorio,
            'precio' : parseFloat($scope.fDataAlmacen.temporal.precio).toFixed(2),
            'importe' : (parseFloat($scope.fDataAlmacen.temporal.precio) * parseFloat($scope.fDataAlmacen.temporal.cantidad)).toFixed(2),
            'fechavencimiento' : $scope.fDataAlmacen.temporal.fechavencimiento ,
            'numerolote' : $scope.fDataAlmacen.temporal.numerolote,
            'idkardex' : $scope.gridOptionsDetalleIngreso.data[0].idkardex
          }; 

          ingresoAlmacenServices.sRegistrarDetalleIngreso($scope.arrTemporal).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
              $scope.isRegisterSuccess = true; 
              $scope.fDataAlmacen = {};
              $scope.getPaginationServerSide();
              $scope.getPaginationDetalleIngresoServerSide();
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }

    $scope.btnAnularDetalleIngreso = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          consultaingresosAlmacenServices.sAnularDetalleingresoAlmacen($scope.mySelectionDetalleIngresoGrid[0]).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationDetalleIngresoServerSide();
              }else if(rpta.flag == 0){
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

  }])
  .service("ingresoAlmacenServices",function($http, $q) { 
    return({
        sRegistrarIngreso: sRegistrarIngreso ,
        sRegistrarDetalleIngreso: sRegistrarDetalleIngreso 
        
     });

    function sRegistrarIngreso(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ingresoAlmacen/registrar_ingreso", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarDetalleIngreso(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ingresoAlmacen/registrar_detalle_ingreso", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });