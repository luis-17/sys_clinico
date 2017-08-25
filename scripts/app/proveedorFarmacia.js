angular.module('theme.proveedorFarmacia', ['theme.core.services'])
  .controller('proveedorFarmaciaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'tipoProveedorFarmaciaServices',
      'proveedorFarmaciaServices',  
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, tipoProveedorFarmaciaServices , proveedorFarmaciaServices ){ 
    'use strict';
    $scope.initProveedor = function () {
      shortcut.remove("F2");
      $scope.modulo = 'proveedor';
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
        enableFiltering: false,
        enableFullRowSelection: true,
        multiSelect: true,
        columnDefs: [
          { field: 'id', name: 'idproveedor', displayName: 'ID', width: 70,  sort: { direction: uiGridConstants.DESC} },
          { field: 'razon_social', name: 'razon_social', displayName: 'Razon Social' },
          { field: 'nombre_comercial', name: 'nombre_comercial', displayName: 'Nombre Comercial' },
          { field: 'ruc', name: 'ruc', displayName: 'RUC', width: 90 },
          { field: 'descripcion_tprov', name: 'descripcion_tprov', displayName: 'Tipo Proveedor' },
          { field: 'telefono', name: 'telefono', displayName: 'Telefono', width: 80, }, 
          { field: 'estado', type: 'object', name: 'estado_prov', displayName: 'Estado', maxWidth: 110, enableFiltering: false, enableSorting: false, 
            cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 95px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
              'idproveedor' : grid.columns[1].filters[0].term,
              'razon_social' : grid.columns[2].filters[0].term,
              'ruc' : grid.columns[3].filters[0].term,
              'descripcion_tprov' : grid.columns[4].filters[0].term,
              'telefono' : grid.columns[5].filters[0].term
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
        proveedorFarmaciaServices.sListarProveedorFarmacia($scope.datosGrid).then(function (rpta) {
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
        });
        $scope.mySelectionGrid = [];
      };
      $scope.getPaginationServerSide(); 
    }
    $scope.pRUC = /^\d{11}$/;
    $scope.listaFormaPago = [
      {'id' : 1, 'descripcion' : 'AL CONTADO'},
      {'id' : 2, 'descripcion' : 'CREDITO'},
      {'id' : 3, 'descripcion' : 'LETRAS'}
    ];
    $scope.listaMoneda = [
      {'id' : 1, 'descripcion' : 'S/.'},
      {'id' : 2, 'descripcion' : 'US$'}
    ]
    
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'proveedorFarmacia/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.accion = 'edit';
          //$scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          
          // $scope.fData.forma_pago = $scope.listaFormaPago[0].id;
          // $scope.fData.moneda = $scope.listaMoneda[0].id;
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Proveedor Farmacia';
          tipoProveedorFarmaciaServices.sListarTipoProveedorCbo().then(function (rpta) {
            $scope.listaTipoProveedor = rpta.datos;
            //$scope.listaTipoProveedor.splice(0,0,{ id : '', descripcion:'--Seleccione un tipo --'});
          });
          console.log('->',$scope.fData.idtipoproveedor);
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            proveedorFarmaciaServices.sEditar($scope.fData).then(function (rpta) {
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
    $scope.btnNuevo = function (size, fProveedor) {
      $scope.fProveedor = fProveedor || null;
      $modal.open({
        templateUrl: angular.patchURLCI+'proveedorFarmacia/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.accion = 'reg';
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.forma_pago = $scope.listaFormaPago[0].id;
          $scope.fData.moneda = $scope.listaMoneda[0].id;
          if($scope.fProveedor != null){
            $scope.fData.ruc = $scope.fProveedor.ruc;
          }else{
           $scope.fData.ruc = null;
          }
          console.log('$scope.fData');
          console.log($scope.fData);
          $scope.titleForm = 'Registro de Proveedor';

          tipoProveedorFarmaciaServices.sListarTipoProveedorCbo().then(function (rpta) {
            $scope.listaTipoProveedor = rpta.datos;
            $scope.listaTipoProveedor.splice(0,0,{ id : '', descripcion:'--Seleccione un Tipo--'});
            $scope.fData.idtipoproveedor = $scope.listaTipoProveedor[0].id; 
          });

          /* AUTOCOMPLETE EMPRESAS */ 
          $scope.getProveedorAutocomplete = function(val) { 
            var params = {
              search: val,
              sensor: false
            }
            return proveedorFarmaciaServices.sListarProveedorFarmaciaCbo(params).then(function(rpta) {
              var proveedores = rpta.datos.map(function(e) {
                return e.razon_social;
              });
              return proveedores;
            });
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            console.log('Registrando Proveedor...');
            // console.log('modulo',$scope.modulo);
            // console.log('submodulo',$scope.submodulo);
            // console.log('fDataOC',$scope.fDataOC);
            // console.log('fDataEntrada',$scope.fDataEntrada);
            
            proveedorFarmaciaServices.sRegistrar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                if($scope.modulo == 'entradas' ){
                  console.log('Listando Proveedor...(Modulo: entradas)');
                  proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fData).then(function (rpta) {
                    $scope.fDataEntrada.proveedor = rpta.datos;
                    $scope.fDataEntrada.ruc = $scope.fDataEntrada.proveedor.ruc;
                  });

                }else if($scope.modulo === 'ordenCompra'){
                  console.log('Listando Proveedor...(Modulo: ordenCompra)');
                  proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fData).then(function (rpta) {
                    console.log('rpta.datos.ruc',rpta.datos.ruc);
                    $scope.fDataOC.proveedor = rpta.datos;
                    $scope.fDataOC.ruc = rpta.datos.ruc;
                    setTimeout(function() {
                      $('#ruc').focus();
                    },500);
                  });
                }else{  
                  $scope.getPaginationServerSide();
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
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
          proveedorFarmaciaServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
          proveedorFarmaciaServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
          proveedorFarmaciaServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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
        description: 'Nuevo proveedor',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar proveedor',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular proveedor',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar proveedor',
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
  .service("proveedorFarmaciaServices",function($http, $q) {
    return({
        sListarProveedorFarmacia: sListarProveedorFarmacia,
        sListarProveedorFarmaciaCbo: sListarProveedorFarmaciaCbo,
        sListarProveedorFarmaciaporCodigo : sListarProveedorFarmaciaporCodigo ,
        sListarEsteProveedorPorRuc : sListarEsteProveedorPorRuc,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sDeshabilitar: sDeshabilitar,
        sHabilitar: sHabilitar,
        sAnular: sAnular
    });

    function sListarProveedorFarmacia(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/lista_proveedor_farmacia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProveedorFarmaciaCbo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/lista_proveedor_farmacia_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProveedorFarmaciaporCodigo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/lista_proveedor_farmacia_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEsteProveedorPorRuc (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/listar_este_proveedor_por_ruc",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"proveedorFarmacia/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });