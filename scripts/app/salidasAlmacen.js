angular.module('theme.salidasAlmacen', ['theme.core.services'])
  .controller('salidasAlmacenController', ['$scope', '$filter','$route', '$routeParams', '$controller', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'salidaAlmacenServices',
    'unidadesLaboratorioServices',
    'reactivoInsumoServices',
    'empleadoServices', 
    'empresaAdminServices', 
    'tipoDocumentoServices',
    'motivomovimientoServices',
    'consultasalidasAlmacenServices',
  function($scope,$filter, $route, $routeParams, $controller, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
    salidaAlmacenServices,
    unidadesLaboratorioServices,
    reactivoInsumoServices,
    empleadoServices,
    empresaAdminServices,
    tipoDocumentoServices,
    motivomovimientoServices,
    consultasalidasAlmacenServices){ 
    'use strict'; 
    $scope.modulo = 'salidas Almacen'; 
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
          $scope.fDataAlmacen.temporal.stock = rpta.datos.stock;
          $('#temporalCantidad').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
        }
      });
    };
    $scope.getEmpleadoAutocomplete = function (value) { 
      var params = {
        search: value, 
        sensor: false
      }
      return empleadoServices.sListarEmpleadosCbo(params).then(function(rpta) { 
        console.log(rpta.datos);
        $scope.noResultsLPSC = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLPSC = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedEmpleado = function ($item, $model, $label) {
      $scope.fDataAlmacen.idempleado = $item.id;
      var arrData = {
        'id': $scope.fDataAlmacen.idempleado
      }
      empleadoServices.sListarEmpleadosporCodigo(arrData).then(function (rpta) {
        if( rpta.flag == 1){
          $scope.fDataAlmacen.idempleado = rpta.datos.id;
          $scope.fDataAlmacen.empleado = rpta.datos.descripcion;
          $('#fDataAlmacenempleado').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
        }
      });
    };

    tipoDocumentoServices.sListarTipoDocumentoVentaCbo().then(function (rpta) {
      $scope.listaTipoDocumento = rpta.datos;
      $scope.listaTipoDocumento.splice(0,0,{ id : '', descripcion:'--Seleccione Documento--'});
      $scope.fDataAlmacen.idtipodocumento = $scope.listaTipoDocumento[0].id; 
    });
    $scope.datosGrid = {
        search : 2
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
        { field: 'reactivoInsumo', displayName: 'Reactivo - Insumo.', width: '61%', enableCellEdit: false },
        { field: 'cantidad', displayName: 'Cantidad', width: '9%', enableCellEdit: false },
        { field: 'unidad', displayName: 'Unidad', width: '18%', enableCellEdit: false },
        { field: 'accion', displayName: 'Acción', width: '7%', enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
      ]
      ,onRegisterApi: function(gridApiCombo) { 
        $scope.gridApiCombo = gridApiCombo; 
        gridApiCombo.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
          //$scope.calcularTotales(); 
          //$scope.calcularVuelto(); 
          //$scope.$apply();
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
                  $scope.fDataAlmacen.temporal.stock = rpta.datos.stock;
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

    $scope.verPopupListaEmpleados = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo_grilla',
        size: size || '',
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.fDataAlmacen = arrToModal.fDataAlmacen;
          $scope.mySelectionComboGrid = [];
          $scope.gridComboOptions = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 500,
            enableRowSelection: true,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
            { field: 'id', displayName: 'ID', maxWidth: 80 },
            { field: 'descripcion', displayName: 'Empleado' }
            ]
            ,onRegisterApi: function(gridApiCombo) {
              $scope.gridApiCombo = gridApiCombo;
              gridApiCombo.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionComboGrid = gridApiCombo.selection.getSelectedRows();
              });
            }
          }
          empleadoServices.sListarEmpleadosCbo().then(function (rpta) {
            $scope.fpc = {};
            $scope.fpc.titulo = ' Empleados.';
            $scope.gridComboOptions.data = rpta.datos;
            angular.forEach($scope.gridComboOptions.data,function(val,index) {
              //$scope.fDataAlmacen = {};
              if( $scope.fDataAlmacen.idempleado == val.id ){
                $timeout(function() {
                  if($scope.gridApiCombo.selection.selectRow){
                    $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[index]);
                  }
                });
              }
            });
            $scope.fpc.aceptar = function () {
              $scope.fDataAlmacen.idempleado = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.empleado = $scope.mySelectionComboGrid[0].descripcion;
              var arrData = {
                  'id': $scope.fDataAlmacen.idempleado
                }
                empleadoServices.sListarEmpleadosporCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fDataAlmacen.idempleado = rpta.datos.id;
                    $scope.fDataAlmacen.empleado = rpta.datos.descripcion;
                    $('#fDataAlmacenempleado').focus();
                    //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
                  }
                });
              $modalInstance.dismiss('cancel');
              $('#fDataAlmacenempleado').focus();
            }
            $scope.fpc.buscar = function () {
              $scope.fpc.nameColumn = 'descripcion';
              empleadoServices.sListarEmpleadosCbo($scope.fpc).then(function (rpta) {
                $scope.gridComboOptions.data = rpta.datos;
              });
            }
            $scope.fpc.seleccionar = function () {
              $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              if( $scope.mySelectionComboGrid.length != 1 || $scope.mySelectionComboGrid.length != 1 ){
                $scope.gridApiCombo.selection.selectRow($scope.gridComboOptions.data[0]);
                $scope.mySelectionComboGrid = $scope.gridApiCombo.selection.getSelectedRows();
              }
              $scope.fDataAlmacen.idempleado = $scope.mySelectionComboGrid[0].id;
              $scope.fDataAlmacen.empleado = $scope.mySelectionComboGrid[0].descripcion;
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
            $scope.fDataAlmacen.temporal.stock = rpta.datos.stock;
            //$('#fDataAlmacenreactivoInsumo').focus();
            $('#temporalCantidad').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
          }else{
            $scope.fDataAlmacen.temporal.reactivoInsumo = null ;
            $scope.fDataAlmacen.temporal.unidadLaboratorio = null ;
            $scope.fDataAlmacen.temporal.stock = null ;
          }
        });

      }
    }
    $scope.obtenerEmpleadoPorCodigo = function () {
      if( $scope.fDataAlmacen.idempleado ){
        var arrData = {
          'id': $scope.fDataAlmacen.idempleado
        }
        empleadoServices.sListarEmpleadosporCodigo(arrData).then(function (rpta) {
          if( rpta.flag == 1){
            $scope.fDataAlmacen.idempleado = rpta.datos.id;
            $scope.fDataAlmacen.empleado = rpta.datos.descripcion;
            $('#fDataAlmacenproveedor').focus();
            //$scope.OnChangeUnidadLaboratorio($scope.fDataAlmacen.idreactivoInsumo);
          }else {
            $scope.fDataAlmacen.proveedor = null;
          }
        });

      }
    }

    $scope.btnQuitarDeLaCesta = function (row) { 
      var index = $scope.gridOptions.data.indexOf(row.entity); 
      $scope.gridOptions.data.splice(index,1); 
      //$scope.calcularTotales();
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
      if($scope.fDataAlmacen.temporal.stock < $scope.fDataAlmacen.temporal.cantidad ){ // console.log('especialidad');
        $scope.fDataAlmacen.temporal.cantidad = null;
        $('#temporalCantidad').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'El stock no cubre la salida .. verifique la cantidad', type: 'error', delay: 2000 });
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
        'cantidad' : $scope.fDataAlmacen.temporal.cantidad ,
        'unidad' : $scope.fDataAlmacen.temporal.unidadLaboratorio
      }; 
      $scope.gridOptions.data.push($scope.arrTemporal);
      $scope.fDataAlmacen.temporal = {} ;
      $('#idtemporalreactivoInsumo').focus();
      //$scope.calcularTotales(); 
      //$scope.calcularVuelto(); 
    }
    $scope.grabar = function (param) { 
      var pParam = param || false; 
      $scope.fDataAlmacen.detalle = $scope.gridOptions.data;
      if( $scope.fDataAlmacen.detalle.length < 1 ){ 
        $('#temporalreactivoinsumo').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 2000 }); 
        return false; 
      }
      salidaAlmacenServices.sRegistrarSalida($scope.fDataAlmacen).then(function (rpta) { 
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
          var productNew = true;
          angular.forEach($scope.gridOptionsDetalleSalida.data, function(value, key){ 
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
            'idkardex' : $scope.gridOptionsDetalleSalida.data[0].idkardex
          }; 

          salidaAlmacenServices.sRegistrarDetalleSalida($scope.arrTemporal).then(function (rpta) { 

            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
              $scope.isRegisterSuccess = true; 
              $scope.fDataAlmacen = {};
              //$route.reload();
              $scope.getPaginationDetalleSalidaServerSide();
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

    $scope.btnAnularDetalleSalida = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          //console.log($scope.mySelectionDetalleSalidaGrid);
          consultasalidasAlmacenServices.sAnularDetallesalidaAlmacen($scope.mySelectionDetalleSalidaGrid[0]).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationDetalleSalidaServerSide();
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
  .service("salidaAlmacenServices",function($http, $q) { 
    return({
        sRegistrarSalida: sRegistrarSalida ,
        sRegistrarDetalleSalida: sRegistrarDetalleSalida 
    });

    function sRegistrarSalida(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"salidaAlmacen/registrar_salida", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarDetalleSalida(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"salidaAlmacen/registrar_detalle_salida", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }





  });