angular.module('theme.caja', ['theme.core.services',, 'ui.grid.edit'])
  .controller('cajaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$routeParams', 
    'cajaServices', 
    'empresaAdminServices', 
    'tipoDocumentoServices', 
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, $routeParams 
      ,cajaServices
      ,empresaAdminServices
      ,tipoDocumentoServices ){ 
    'use strict'; 
    // console.log($routeParams.modulo); 
    shortcut.remove("F2"); $scope.modulo = 'caja'; 
    $scope.fBusqueda = {};

    if( $routeParams.modulo == 'hospital' ){
      $scope.idmodulo = 1;
    }else if( $routeParams.modulo == 'farmacia' ){
      $scope.idmodulo = 3;
    }
    
    $scope.fData = {};
    $scope.mySelectionGrid = [];

    empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) { 
      $scope.listaEmpresaAdmin = rpta.datos;
      $scope.listaEmpresaAdmin.splice(0,0,{ id : 'all', descripcion:'-- Todos --'});
      $scope.fBusqueda.empresa = $scope.fSessionCI.idempresaadmin;
      $scope.listaCajasFormat();
    });

    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    $scope.columnDefs = [
        { field: 'id', name: 'idcaja', displayName: 'ID', width: '4%',  sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
        { field: 'empresa', displayName: 'EMPRESA', width: '14%', enableCellEdit: false },
        { field: 'caja', displayName: 'CAJA', width: '10%', enableCellEdit: false },
        { field: 'maquina_registradora', displayName: 'MAQ. REG.', width: '8%', enableCellEdit: false },
        { field: 'serie', displayName: 'SERIE', width: '5%', enableCellEdit: false }
    ];
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: $scope.columnDefs,
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
          var arrEditCell = {
            'tipodocumento' : colDef.field,
            'numeroserie' : newValue,
            'idcajamaster' : rowEntity.id
          }
          cajaServices.sEditarNumeroDeSerie(arrEditCell).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Error inesperado');
            }
            $scope.listaCajas();
            $scope.fData = {};
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
          $scope.$apply();
        });
      }
    };
    $scope.listaCajas = function () { 
      cajaServices.sListarCajas($scope.fBusqueda).then(function (rpta) { 
        $scope.gridOptions.data = rpta.datos;
      });
    }
    $scope.listaCajasFormat = function () { 
      $scope.fBusqueda.idmodulo = $scope.idmodulo; 
      cajaServices.sListarCajas($scope.fBusqueda).then(function (rpta) { 
        $scope.gridOptions.data = rpta.datos;
        var arrColumns = $scope.gridOptions.data[0];
        var i = 0;
        console.log('datos cajas', $scope.gridOptions.data);
        angular.forEach(arrColumns,function (val,key) { 
          i++;
          if( i > 7 ){ 
            var arrObjectColumns = { 
              field: key, 
              displayName: key, 
              cellTemplate: '<span>{{ COL_FIELD }}</span>',
              type: 'number', 
              cellClass:'text-center', 
              enableColumnMenus: false, 
              enableColumnMenu: false,
              enableCellEdit: true, 
              enableSorting: false
            }
            $scope.columnDefs.push(arrObjectColumns);
          }
        });
      });
    }
    
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'caja/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.fData = {};
          $scope.accion = 'edit';
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de caja';
          $scope.cancel = function () {
            //console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.listaCajas();
          }
          $scope.aceptar = function () { 
            cajaServices.sEditar($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('No se pudo realizar la transacción.');
              }
              $scope.listaCajas();
              $scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          }
        }
      });
    }
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'caja/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          // console.log($scope.mySelectionGrid);
          $scope.fData = {};
          $scope.accion = 'reg';
          $scope.fData.idempresa = $scope.listaEmpresaAdmin[0].id;
          $scope.titleForm = 'Registro de caja';
          /* DATA GRID */ 
          $scope.mySelectionTipoDocsGrid = [];
          $scope.gridOptionsTipoDocs = { 
            multiSelect: false,
            gridMenuShowHideColumns: false,
            minRowsToShow: 6,
            columnDefs: [
              { field: 'id', displayName: 'ID', width: '10%' },
              { field: 'descripcion', displayName: 'Tipo de Documento' },
              { field: 'numero', width: '15%', displayName: 'N° Serie', enableCellEdit: true, enableSorting: false,type: 'number', cellClass:'text-center', enableColumnMenus: false, enableColumnMenu: false }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              //$scope.gridApi.grid.sortColumn(2,$scope.uiGridConstants.ASC, false ); 
            }
          };

          tipoDocumentoServices.sListarTipoDocumentoVenta().then(function (rpta) { 
            $scope.gridOptionsTipoDocs.data = rpta.datos;
          });
          $scope.cancel = function () { 
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            // console.log($scope.gridOptionsTipoDocs.data); 
            $scope.fData.idmodulo = $scope.idmodulo;
            $scope.fData.detalle = $scope.gridOptionsTipoDocs.data;
            cajaServices.sRegistrar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.listaCajas();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
        }
      });          
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.listaCajas();
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
        description: 'Nueva caja',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar caja',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular caja',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar caja',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      });
      // .add ({ 
      //   combo: 's',
      //   description: 'Selección y Navegación',
      //   callback: function() {
      //     $scope.navegateToCell(0,0);
      //   }
      // })
  }])
  .service("cajaServices",function($http, $q) {
    return({
        sListarCajas: sListarCajas,
        sListarCajasCbo: sListarCajasCbo,
        sListarTodasCajasMasterCbo: sListarTodasCajasMasterCbo,
        sListarTodasCajasMasterUsuarioCbo: sListarTodasCajasMasterUsuarioCbo, 
        sGetCajaActualUsuario: sGetCajaActualUsuario,
        sGetFarmaciaCajaActualUsuario: sGetFarmaciaCajaActualUsuario,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sEditarNumeroDeSerie: sEditarNumeroDeSerie
    });

    function sListarCajas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_cajas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCajasCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_cajas_cbo",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTodasCajasMasterCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_todas_cajas_master_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTodasCajasMasterUsuarioCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_todas_cajas_master_usuario_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGetCajaActualUsuario () {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/get_caja_actual_de_usuario"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGetFarmaciaCajaActualUsuario () {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/get_farmacia_caja_actual_de_usuario"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarNumeroDeSerie (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/editar_numero_serie", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });