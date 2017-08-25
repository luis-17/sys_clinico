angular.module('theme.producto', ['theme.core.services'])
  .controller('productoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'ModalReporteFactory', 
    'productoServices', 'tipoProductoServices',
    'especialidadServices',
    'empresaAdminServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, ModalReporteFactory,
      productoServices, tipoProductoServices,
      especialidadServices,
      empresaAdminServices ){ 
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'producto';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.fBusqueda = {};
    $scope.fBusqueda.sedeempresa = '1';
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTA DE EMPRESAS-SEDES
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) {
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      $scope.getPaginationServerSide();  
    });
    $scope.onChangeEmpresaSede = function () { 
      $scope.getPaginationServerSide();
    }
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
        { field: 'id', name: 'pm.idproductomaster', displayName: 'ID', width: '8%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'tp', name: 'tp.nombre_tp', displayName: 'Categoria', width: '15%' },
        { field: 'producto', name: 'pm.descripcion', displayName: 'Producto' },
        { field: 'precio', name: 'precio', displayName: 'Precio', width: '10%', cellClass: 'text-right' },
        { field: 'especialidad', name: 'especialidad', displayName: 'Especialidad', width: '15%' }, 
        { field: 'estado', type: 'object', name: 'estado_pps', displayName: 'Estado', width: '10%', enableFiltering: false, enableSorting: false, 
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
            'pm.idproductomaster' : grid.columns[1].filters[0].term,
            'tp.nombre_tp' : grid.columns[2].filters[0].term,
            'pm.descripcion' : grid.columns[3].filters[0].term,
            'precio' : grid.columns[4].filters[0].term,
            'esp.nombre' : grid.columns[5].filters[0].term
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
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      productoServices.sListarProductos($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    // $scope.getPaginationServerSide();
    
      /* ============= */
     /* MANTENIMIENTO */
    /* ============= */
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'producto/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          //$scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.fData = {};
          $scope.accion = 'reg';
          $scope.fData.newProduct = false ;
          // =============================================================
          // AUTOCOMPLETADO SOLO ESPECIALIDAD 
          // =============================================================
          $scope.getSoloEspecialidadAutocomplete = function (value) {
            var params = {
              search: value,
              sensor: false
            }
            return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
              $scope.noResultsSoloEspecialidad = false;
              if( rpta.flag === 0 ){
                $scope.noResultsSoloEspecialidad = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedSoloEspecialidad = function ($item, $model, $label) {
              $scope.fData.idespecialidad = $item.id;
          };
          $scope.getClearInputSoloEspecialidad = function () {
            if(!angular.isObject($scope.fData.especialidad)){ 
              $scope.fData.idespecialidad = null;
            }
          }
          /* DATA GRID */ 
          var paginationSedeEmpresaOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsSedeEmpresa = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            minRowsToShow: 5,
            useExternalPagination: true,
            useExternalSorting: true,
            enableFiltering: false,
            columnDefs: [
              { field: 'razon_social', name: 'razon_social', displayName: 'EMPRESA ADMIN',sort: { direction: uiGridConstants.ASC} },
              { field: 'descripcion', name: 'descripcion', displayName: 'SEDE', width: '25%' },
              { field: 'precio_sede', name: 'precio_sede', displayName: 'PRECIO SEDE',width: '12%', enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false } 
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationSedeEmpresaOptions.sort = null;
                  paginationSedeEmpresaOptions.sortName = null;
                } else {
                  paginationSedeEmpresaOptions.sort = sortColumns[0].sort.direction;
                  paginationSedeEmpresaOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationSedeEmpresaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationSedeEmpresaOptions.pageNumber = newPage;
                paginationSedeEmpresaOptions.pageSize = pageSize;
                paginationSedeEmpresaOptions.firstRow = (paginationSedeEmpresaOptions.pageNumber - 1) * paginationSedeEmpresaOptions.pageSize;
                $scope.getPaginationSedeEmpresaServerSide();
              });
            }
          };
          paginationSedeEmpresaOptions.sortName = $scope.gridOptionsSedeEmpresa.columnDefs[0].name;
          $scope.getPaginationSedeEmpresaServerSide = function() { 
            $scope.datosGrid = { 
              paginate : paginationSedeEmpresaOptions
            };
            empresaAdminServices.sListarSedeEmpresaAdmin($scope.datosGrid).then(function (rpta) { 
              $scope.gridOptionsSedeEmpresa.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsSedeEmpresa.data = rpta.datos;
              
            });
            // $scope.mySelectionSedeEmpresaGrid = [];
          };
          $scope.getPaginationSedeEmpresaServerSide();
          /* END DATA GRID */

          $scope.titleForm = 'Registro de Producto';
          var params = {
            'modulo' : '1', // clinica
          }          
          // TIPO PRODUCTO
          tipoProductoServices.sListarTipoProductoCbo(params).then(function (rpta) {
            $scope.listaTiposProducto = rpta.datos;
            $scope.listaTiposProducto.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de Producto--'});
            $scope.fData.idtipoproducto = $scope.listaTiposProducto[0].id;
            
          });
          $scope.getProductosAutocomplete = function (val) {
            var params = {
              search: val,
              sensor: false
            }
            return productoServices.sListarProductosCbo(params).then(function(rpta) {
              var data = rpta.datos.map(function(e) { 
                return e.nombre;
              });
              return data;
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            $scope.fData.detalle = $scope.gridOptionsSedeEmpresa.data;
            productoServices.sRegistrar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){ 
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';}
              else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
            });
          }
        }
      });
    }
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'producto/ver_popup_formulario',
        size: size || '',
        scope: $scope,
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,arrToModal) {
          $scope.mySelectionGrid = arrToModal.mySelectionGrid;
          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.fData = {};
          $scope.accion = 'edit';
          $scope.fData.newProduct = false ;
          // =============================================================
          // AUTOCOMPLETADO SOLO ESPECIALIDAD 
          // =============================================================
          $scope.getSoloEspecialidadAutocomplete = function (value) {
            var params = {
              search: value,
              sensor: false
            }
            return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
              $scope.noResultsSoloEspecialidad = false;
              if( rpta.flag === 0 ){
                $scope.noResultsSoloEspecialidad = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedSoloEspecialidad = function ($item, $model, $label) {
              $scope.fData.idespecialidad = $item.id;
          };
          $scope.getClearInputSoloEspecialidad = function () {
            if(!angular.isObject($scope.fData.especialidad)){ 
              $scope.fData.idespecialidad = null;
            }
          }
          
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          
          /* DATA GRID */ 
          var paginationSedeEmpresaOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsSedeEmpresa = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            minRowsToShow: 5,
            useExternalPagination: true,
            useExternalSorting: true,
            enableFiltering: false,
            columnDefs: [
              { field: 'razon_social', name: 'razon_social', displayName: 'EMPRESA ADMIN',sort: { direction: uiGridConstants.ASC} },
              { field: 'descripcion', name: 'descripcion', displayName: 'SEDE', width: '25%' },
              { field: 'precio_sede', name: 'precio_sede', displayName: 'PRECIO SEDE',width: '12%', enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false } 
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                
                rowEntity.column = colDef.field;
                //console.log(rowEntity.column);

                if(rowEntity.column == 'precio_sede'){
                  if( rowEntity.precio_sede === 0){
                    rowEntity.precio_sede = '0.00';
                  }
                  if( rowEntity.precio_sede === ''){
                    rowEntity.precio_sede = null;
                  }
                }
                
                $scope.$apply();
              });
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationSedeEmpresaOptions.sort = null;
                  paginationSedeEmpresaOptions.sortName = null;
                } else {
                  paginationSedeEmpresaOptions.sort = sortColumns[0].sort.direction;
                  paginationSedeEmpresaOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationSedeEmpresaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationSedeEmpresaOptions.pageNumber = newPage;
                paginationSedeEmpresaOptions.pageSize = pageSize;
                paginationSedeEmpresaOptions.firstRow = (paginationSedeEmpresaOptions.pageNumber - 1) * paginationSedeEmpresaOptions.pageSize;
                $scope.getPaginationSedeEmpresaServerSide();
              });
            }
          };
          paginationSedeEmpresaOptions.sortName = $scope.gridOptionsSedeEmpresa.columnDefs[0].name;
          $scope.getPaginationSedeEmpresaServerSide = function() { 
            $scope.datosGrid = { 
              paginate : paginationSedeEmpresaOptions,
              datos :  $scope.fData
            };
            empresaAdminServices.sListarSedeEmpresaAdminPrecio($scope.datosGrid).then(function (rpta) { 
              $scope.gridOptionsSedeEmpresa.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsSedeEmpresa.data = rpta.datos;
              
            });
          };
          $scope.getPaginationSedeEmpresaServerSide();
          /* END DATA GRID */
          $scope.titleForm = 'Edición de Producto';
         
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            $scope.fData.detalle = $scope.gridOptionsSedeEmpresa.data;
            productoServices.sEditar($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                $scope.getPaginationServerSide();
              }else{
                alert('Error inesperado'); return false;
              }
              $scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
            // TIPO PRODUCTO
          tipoProductoServices.sListarTipoProductoCbo().then(function (rpta) {
            $scope.listaTiposProducto = rpta.datos;
            $scope.listaTiposProducto.splice(0,0,{ id : '', nombre:'--Seleccione tipo de Producto--'});
          });
        }, 
        resolve: {
          arrToModal : function () {
            return {
              mySelectionGrid : $scope.mySelectionGrid,
              getPaginationServerSide : $scope.getPaginationServerSide
            }
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          productoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
    $scope.btnHabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          $scope.fBusqueda.seleccion = $scope.mySelectionGrid;
          productoServices.sHabilitar($scope.fBusqueda).then(function (rpta) {
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
          $scope.fBusqueda.seleccion = $scope.mySelectionGrid;
          productoServices.sDeshabilitar($scope.fBusqueda).then(function (rpta) {
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
    $scope.btnHistorialPrecios = function () { 
      $modal.open({
        templateUrl: angular.patchURLCI+'producto/ver_popup_historial_precios',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          // $scope.listaAlmacenes = angular.copy($scope.listaAlmacen);
          $scope.fDataHistorial = {};
          $scope.fDataHistorial = angular.copy($scope.mySelectionGrid[0]);
          $scope.titleForm = 'HISTORIAL DE PRECIOS';
          var arrParams = {
            datos: $scope.mySelectionGrid[0]
          };
          productoServices.sListarHistorial(arrParams).then(function (rpta) {
            $scope.listaHistorial = rpta.datos;
            if( rpta.datos[0] != null ){
              $scope.fDataHistorial.precio_actual = rpta.datos[0].precio_venta;
              $scope.fDataHistorial.precio_inicial = rpta.precio_inicial;
              if(rpta.datos[0].precio_venta_anterior == null){
                $scope.listaHistorial = null;
              }
            }else{
              $scope.fDataHistorial.precio_inicial = $scope.mySelectionGrid[0].precio;
              $scope.fDataHistorial.precio_actual = $scope.mySelectionGrid[0].precio;
            }
          });
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
      /* ============= */
     /* EXPORTACIONES */
    /* ============= */
    $scope.btnExportarListaPdf = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'TARIFARIO',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'pdf',
          tituloAbv: 'HOS-TRF',
          titulo: 'TARIFARIO',
        },
        metodo: 'php'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_tarifario_sede',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnExportarListaExcel = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'TARIFARIO',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'excel',
          tituloAbv: 'HOS-TRF',
          titulo: 'TARIFARIO',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportes/report_tarifario_sede_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nuevo producto',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar producto',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular producto',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar producto',
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
  .service("productoServices",function($http, $q) {
    return({
        sListarProductos: sListarProductos,
        sListarProductosCbo: sListarProductosCbo,
        sListarProductosCboCampania:sListarProductosCboCampania,        
        sListarProductosSessionCbo: sListarProductosSessionCbo,
        sListarProductosSedeEmpresaAdminCbo: sListarProductosSedeEmpresaAdminCbo,
        sListarProductosSedeEmpresaAdminCboCampania:sListarProductosSedeEmpresaAdminCboCampania,
        sListarProductosSedeEmpresaAdminCboCampaniaID : sListarProductosSedeEmpresaAdminCboCampaniaID,
        sListarProductosConvenioCbo: sListarProductosConvenioCbo,
        sListarProductosIndicadores: sListarProductosIndicadores,
        sListarPerfilesSaludOcup: sListarPerfilesSaludOcup,
        sListarHistorial: sListarHistorial,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sDeshabilitar: sDeshabilitar,
        sHabilitar: sHabilitar,
        sAnular: sAnular
    });

    function sListarProductos(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/lista_productos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/lista_productos_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosCboCampania (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/lista_productos_cbo_campania", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarProductosSessionCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/lista_productos_de_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosSedeEmpresaAdminCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/lista_productos_de_sede_empresa_admin", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosSedeEmpresaAdminCboCampania (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/lista_productos_de_sede_empresa_admin_campania", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sListarProductosSedeEmpresaAdminCboCampaniaID (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/lista_productos_de_sede_empresa_admin_campania_id", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }      
    function sListarProductosConvenioCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/lista_productos_convenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosIndicadores (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/lista_productos_indicadores", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPerfilesSaludOcup (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/lista_productos_salud_ocup", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarHistorial (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Producto/cargar_historial_precios", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"producto/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });