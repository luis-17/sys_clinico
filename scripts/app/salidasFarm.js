angular.module('theme.salidasFarm', ['theme.core.services'])
  .controller('salidasFarmController', ['$scope', 'blockUI', '$filter', '$route', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ventaFarmaciaServices', 
    'trasladosFarmServices',
    'almacenFarmServices',
    'salidasFarmServices',
    'ModalReporteFactory',
    function($scope, blockUI, $filter, $sce, $route, $interval, $modal, $bootbox ,$window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ventaFarmaciaServices, 
      trasladosFarmServices,
      almacenFarmServices,
      salidasFarmServices,
      ModalReporteFactory ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    $scope.fBusqueda.almacen = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringSA = function(){
      $scope.gridOptionsBajasAnuladas.enableFiltering = !$scope.gridOptionsBajasAnuladas.enableFiltering;
      $scope.gridApiAnulado.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringPB = function(){
      $scope.gridOptionsProductosBaja.enableFiltering = !$scope.gridOptionsProductosBaja.enableFiltering;
      $scope.gridApiProducto.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTAR ALMACENES
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { //console.log(rpta);
      $scope.listaAlmacenes = rpta.datos;
      $scope.fBusqueda.almacen = $scope.listaAlmacenes[0];
      $scope.listarSubAlmacenesAlmacen($scope.fBusqueda.almacen.id);
    });
    // LISTAR SUB-ALMACENES
    $scope.listarSubAlmacenesAlmacen = function (idalmacen) { 
      var arrParams = {
        'idalmacen': idalmacen
      }
      almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rpta) {  
        $scope.listaSubAlmacen = rpta.datos;
        $scope.listaSubAlmacen.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
        $scope.fBusqueda.idsubalmacen = $scope.listaSubAlmacen[0].id;
      });
    }
    /* GRILLA PRINCIPAL */
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
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
      multiSelect: false,
      columnDefs: [ 
        { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID', width: '6%', visible: true, sort: { direction: uiGridConstants.DESC} },
        { field: 'almacen', name: 'nombre_alm', displayName: 'ALMACÉN', width: '13%' },
        { field: 'subAlmacen', name: 'salm.nombre_salm', displayName: 'SUB ALMACÉN ', width: '13%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE BAJA', width: '12%', enableFiltering: false  },
       
        { field: 'usuario', name: 'usuario', displayName: 'RESPONSABLE',  },
        { field: 'motivo_movimiento', name: 'motivo_movimiento', displayName: 'MOTIVO MOVIMIENTO',  },
        { field: 'tipomovimiento', name: 'tipomovimiento', displayName: 'TIPO', width: '11%' ,enableFiltering: false ,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
        },
        { field: 'estadomov', name: 'estadomov', displayName: 'ESTADO', width: '7%' ,enableFiltering: false ,
          cellTemplate:'<label tooltip-placement="left" tooltip="{{ COL_FIELD.string }}"  style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 30px;" class="label {{ COL_FIELD.clase }} "><i class="fa {{ COL_FIELD.icon}}"></i></label>' }

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
            // POR DEFECTO ORDENAR POR: [0] => ID
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'nombre_alm' : grid.columns[2].filters[0].term,
            "nombre_salm" : grid.columns[3].filters[0].term,
            'usuario' : grid.columns[5].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
        // $interval( function() {
        //   $scope.gridApi.core.handleWindowResize();
        // }, 10, 500);
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function(loader) { 
      var loader = loader || false;
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      if( loader ){
        blockUI.start('Ejecutando proceso...');
      }
      salidasFarmServices.sListarSalidas(arrParams).then(function (rpta) {
        if(rpta.flag == 99){
          var pTitle = 'Advertencia!';
          var pType = 'danger';
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
          return;
        } 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        if( loader ){
          blockUI.stop();
        }
      });
      $scope.mySelectionGrid = [];
    };
    /*==================================== BOTON PROCESAR =========================================================*/
    $scope.procesar = function(load){ 
      var loader = load || false;
      if(!$scope.formSalida.$invalid){
        $scope.getPaginationServerSide(loader);
        $scope.getPaginationSAServerSide();
        $scope.getPaginationPBServerSide();
      }else{
        pinesNotifications.notify({ title: 'Warning.', text: 'Rellene los campos obligatorios.', type: 'warning', delay: 3000 });
      }
      
    }
    $scope.btnNuevaSalida = function (size) { 
      blockUI.start('Ejecutando proceso...');
      $modal.open({
        templateUrl: angular.patchURLCI+'AlmacenFarmacia/ver_popup_salida',
        size: size || 'xlg',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,blockUI) {
          $scope.fData = {};
          $scope.fData.almacen = {};
          $scope.fData.fecha_salida = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.titleForm = 'Salidas de Sub Almacenes - BAJAS';
          blockUI.stop();
          var i = 0;
          //var index = 0;
          angular.forEach($scope.listaAlmacenes, function(val,index){
            if(val.id == $scope.fBusqueda.almacen.id){
              $scope.index = i;
            }else{
              i = i + 1;
            }
          });
          $scope.fData.almacen = $scope.listaAlmacenes[$scope.index];
          $scope.fData.idsubalmacen = angular.copy($scope.fBusqueda.idsubalmacen);
          $scope.listaSubAlmacen = angular.copy($scope.listaSubAlmacen);
          $scope.listaSubAlmacen.splice(0,1);
          if($scope.fData.idsubalmacen == 0){
            $scope.fData.idsubalmacen = $scope.listaSubAlmacen[0].id;
          }
 
          // LISTAR SUB-ALMACENES
          $scope.listarSubAlmacen = function (idalmacen) { 
            var arrParams = {
              'idalmacen': idalmacen
            }
            almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rpta) {  
              $scope.listaSubAlmacen = rpta.datos;
              $scope.fData.idsubalmacen = $scope.listaSubAlmacen[0].id;
              $scope.getPaginationProdServerSide();

            });
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          // GRILLA DE PRODUCTOS A DAR SALIDA
          var paginationOptionsProd = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsProdSubAlmacen = { 
            paginationPageSizes: [10, 50, 100, 500, 1000], 
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: true,
            enableFullRowSelection: true,
            multiSelect: true,
            columnDefs: [
              { field: 'idmedicamento', name: 'med.idmedicamento', displayName: 'ID', width: 80 },
              { field: 'producto', name: 'denominacion', displayName: 'PRODUCTO', width: 250 },
              { field: 'stock', name: 'stock_actual_malm', displayName: 'STOCK', width: 100, enableFiltering: false,  sort: { direction: uiGridConstants.DESC} }
            ],
            onRegisterApi: function(gridApiProd) {
              $scope.gridApiProd = gridApiProd;
              gridApiProd.selection.on.rowSelectionChanged($scope,function(row){ 
                $scope.mySelectionProdGrid = gridApiProd.selection.getSelectedRows();
                angular.forEach($scope.mySelectionProdGrid, function (value, key) { 
                  var tempCopy = angular.copy(value);
                  var boolNoAgregar = false;
                  if(value.stock == 0){
                    boolNoAgregar = true;
                  }else{
                    angular.forEach($scope.gridOptionsAddProducto.data, function (valueDet, keyDet) {
                      if( valueDet.idmedicamento == value.idmedicamento ){
                        boolNoAgregar = true;
                      }
                    }) 
                  }
                  if( !(boolNoAgregar) ) {
                    tempCopy.cantidad = 1;
                    $scope.gridOptionsAddProducto.data.push( tempCopy );
                  }
                });
              });
              gridApiProd.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionProdGrid = gridApiProd.selection.getSelectedRows(); 
                angular.forEach($scope.mySelectionProdGrid, function (value, key) { 
                  var tempCopy = angular.copy(value);
                  var boolNoAgregar = false;
                  if(value.stock == 0){
                    boolNoAgregar = true;
                  }else{
                    angular.forEach($scope.gridOptionsAddProducto.data, function (valueDet, keyDet) {
                      if( valueDet.idmedicamento == value.idmedicamento ){
                        boolNoAgregar = true;
                      }
                    }) 
                  }
                  if( !(boolNoAgregar) ) {
                    tempCopy.cantidad = 1;
                    $scope.gridOptionsAddProducto.data.push( tempCopy );
                  }
                });
              });
              $scope.gridApiProd.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) { 
                  paginationOptionsProd.sort = null;
                  paginationOptionsProd.sortName = null;
                } else {
                  paginationOptionsProd.sort = sortColumns[0].sort.direction;
                  paginationOptionsProd.sortName = sortColumns[0].name;
                }
                $scope.getPaginationProdServerSide();
              });
              gridApiProd.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsProd.pageNumber = newPage;
                paginationOptionsProd.pageSize = pageSize;
                paginationOptionsProd.firstRow = (paginationOptionsProd.pageNumber - 1) * paginationOptionsProd.pageSize;
                $scope.getPaginationProdServerSide();
              });
              $scope.gridApiProd.core.on.filterChanged( $scope, function(grid, searchColumns) { 
                var grid = this.grid;
                paginationOptionsProd.search = true;
                paginationOptionsProd.searchColumn = {
                  'med.idmedicamento' : grid.columns[1].filters[0].term,
                  'denominacion' : grid.columns[2].filters[0].term,
                }
                $scope.getPaginationProdServerSide();
              });

            }
          }
          paginationOptionsProd.sortName = $scope.gridOptionsProdSubAlmacen.columnDefs[2].name;
          $scope.getPaginationProdServerSide = function() {
            var arrParams = {
              paginate : paginationOptionsProd,
              datos: $scope.fData
            };
            salidasFarmServices.sListarProductosSubAlmacen(arrParams).then(function (rpta) {
              $scope.gridOptionsProdSubAlmacen.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsProdSubAlmacen.data = rpta.datos;
            });
            $scope.mySelectionProdGrid = [];
          };
          $scope.getPaginationProdServerSide();
          $scope.getTableHeight = function () {
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return {
                height: ($scope.gridOptionsAddProducto.data.length * rowHeight + headerHeight + 30) + "px"
             };
          }
          $scope.gridOptionsAddProducto = { 
            minRowsToShow: 7,
            paginationPageSize: 10,
            columnDefs: [ 
             { field: 'idmedicamento',displayName: 'ID', width: '9%', enableCellEdit: false },
              { field: 'producto', displayName: 'PRODUCTO', enableCellEdit: false,  sort: { direction: uiGridConstants.ASC} },
              { field: 'cantidad', displayName: 'CANT.', width: '11%', enableFiltering: false, cellClass:'ui-editCell' },
              { field: 'fecha_vencimiento',displayName: 'F.V.', width: '14%', enableFiltering: false, cellClass:'ui-editCell', cellFilter:'date' },
              { field: 'lote', displayName: 'LOTE', width: '12%', enableFiltering: false, cellClass:'ui-editCell' },
              { field: 'accion', displayName: '', width: '6%', 
              cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-danger m-xs" ng-click="grid.appScope.btnQuitarDeLaCesta(row)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>' , enableCellEdit: false
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field;
                if( rowEntity.column == 'cantidad'){
                  if( !(rowEntity.cantidad >= 1) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if( rowEntity.column == 'fecha_vencimiento'){
                  if( rowEntity.fecha_vencimiento == '' || rowEntity.fecha_vencimiento == null ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.fecha_vencimiento = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'No ha ingresado una fecha de vencimiento', type: pType, delay: 3500 });
                    return false;
                  }
                }
                // salidasFarmServices.sValidarCantidad(rowEntity).then(function (rpta) { 
                //   if(rpta.flag == 0){
                //     var pTitle = 'Error!';
                //     var pType = 'danger';
                //     rowEntity.cantidad = 1;
                //     pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
                //   }else if(rpta.flag != 1){
                //     alert('Error inesperado');
                //   }
                //   $scope.getPaginationProdServerSide();
                  
                // });
                $scope.$apply();
              });
            }
          };
          $scope.limpiarCesta = function(){
            $scope.gridOptionsAddProducto.data = [];
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            // var arrParams = row.entity;
            var index = $scope.gridOptionsAddProducto.data.indexOf(row.entity); 
            $scope.gridOptionsAddProducto.data.splice(index,1); 
          }
          $scope.aceptar = function () { 
            $scope.fData.productos = $scope.gridOptionsAddProducto.data;
            salidasFarmServices.sRealizarSalida($scope.fData).then(function (rpta) { 
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
              $scope.getPaginationServerSide();
              $scope.getPaginationSAServerSide();
              $scope.getPaginationPBServerSide();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
            });
          }
        }
      })
    }
    $scope.btnAnularSalida = function(mensaje){
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          salidasFarmServices.sAnularSalida($scope.mySelectionGrid[0]).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationSAServerSide();
                $scope.getPaginationPBServerSide();
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
    $scope.btnVerDetalleBaja = function (fSalida,size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'SalidasFarm/ver_popup_detalle_salida',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Salida';
          $scope.fSalida = fSalida;
          $scope.gridOptionsDetalleSalida = {
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: true,
            enableSelectAll: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [ 
              // { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '14%' },
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'CODIGO', width: '8%' },
              { field: 'medicamento', name: 'denominacion', displayName: 'PRODUCTO' },
              { field: 'fecha_vencimiento', name: 'fecha_vencimiento', displayName: 'FEC. VENC',width: '12%' },
              { field: 'num_lote', name: 'num_lote', displayName: 'NUM. LOTE', width: '12%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '8%' },

            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          $scope.getPaginationDetalleSalidaServerSide = function() {
            var arrParams = {
              datos: fSalida
            };
            salidasFarmServices.sListarDetalleSalida(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleSalida.data = rpta.datos;


              $scope.fSalida.detalle = rpta.datos;
            });
          };
          $scope.getPaginationDetalleSalidaServerSide();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnImprimir = function(idmovimiento, estado){
      console.log('movimiento: ', idmovimiento);
      console.log('estado: ', estado);
      var abreviatura = 'SAL';
      if( estado == 0 ){
        abreviatura = 'ANLDO';
      }
      var arrParams = {
          titulo: 'REPORTE DE SALIDA DEL ALMACEN',
          datos:{
            resultado: idmovimiento,
            salida: 'pdf',
            tituloAbv: abreviatura,
            titulo: 'REPORTE DE SALIDA DEL ALMACEN'
          },
          metodo: 'php'
        }
        console.log('arrParams: ', arrParams);
        arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_salida',
        ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnVerDetalleSalida = function (size) { 
      $modal.open({
        templateUrl: angular.patchURLCI+'AlmacenFarmacia/ver_popup_detalle_salida_medicamento',
        size: size || 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Salida';
          $scope.fData = {};
          $scope.fData = $scope.mySelectionGrid[0];
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    /************** GRID DE INGRESOS ANULADOS **************/
    var paginationOptionsSA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsSalidasAnuladas = {
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
        { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID', width: '6%', visible: true, sort: { direction: uiGridConstants.DESC} },
        { field: 'almacen', name: 'nombre_alm', displayName: 'ALMACÉN', width: '13%' },
        { field: 'subAlmacen', name: 'salm.nombre_salm', displayName: 'SUB ALMACÉN ', width: '13%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE BAJA', width: '12%', enableFiltering: false  },
       
        { field: 'usuario', name: 'usuario', displayName: 'RESPONSABLE',  },
        { field: 'motivo_movimiento', name: 'motivo_movimiento', displayName: 'MOTIVO MOVIMIENTO',  },
        { field: 'tipomovimiento', name: 'tipomovimiento', displayName: 'TIPO', width: '11%' ,enableFiltering: false ,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
        },
        { field: 'estadomov', name: 'estadomov', displayName: 'ESTADO', width: '7%' ,enableFiltering: false ,
          cellTemplate:'<label tooltip-placement="left" tooltip="{{ COL_FIELD.string }}"  style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 30px;" class="label {{ COL_FIELD.clase }} "><i class="fa {{ COL_FIELD.icon}}"></i></label>' }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiAnulado = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridSA = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridSA = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiAnulado.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsSA.sort = null;
            paginationOptionsSA.sortName = null;
          } else {
            paginationOptionsSA.sort = sortColumns[0].sort.direction;
            paginationOptionsSA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationSAServerSide();
        });
        $scope.gridApiAnulado.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsSA.search = true; 
          paginationOptionsSA.searchColumn = { 
            'nombre_alm' : grid.columns[2].filters[0].term,
            "nombre_salm" : grid.columns[3].filters[0].term,
            'usuario' : grid.columns[5].filters[0].term
          }
          $scope.getPaginationSAServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsSA.pageNumber = newPage;
          paginationOptionsSA.pageSize = pageSize;
          paginationOptionsSA.firstRow = (paginationOptionsSA.pageNumber - 1) * paginationOptionsSA.pageSize;
          $scope.getPaginationSAServerSide();
        });
      }
    };
    paginationOptionsSA.sortName = $scope.gridOptionsSalidasAnuladas.columnDefs[0].name;
    $scope.getPaginationSAServerSide = function() { 
      var arrParams = {
        paginate : paginationOptionsSA,
        datos : $scope.fBusqueda
      };
      salidasFarmServices.sListarSalidasAnuladas(arrParams).then(function (rpta) {
        $scope.gridOptionsSalidasAnuladas.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsSalidasAnuladas.data = rpta.datos;
      });
      $scope.mySelectionGridSA = [];
    };
    /* ======================== GRID DE PRODUCTOS DE LA BAJA */
    var paginationOptionsPB = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsProductosBaja = { 
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
        { field: 'iddetallemovimiento', name: 'iddetallemovimiento', displayName: 'ID', width: '6%', visible: false },
        { field: 'almacen', name: 'nombre_alm', displayName: 'ALMACÉN', width: '13%' },
        { field: 'subAlmacen', name: 'salm.nombre_salm', displayName: 'SUB ALMACÉN ', width: '13%' },
        { field: 'producto', name: 'denominacion', displayName: 'PRODUCTO', width: '30%' },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '9%' ,enableFiltering: false },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA MOVIMIENTO', width: '12%', enableFiltering: false,sort: { direction: uiGridConstants.DESC}  },
        { field: 'tipomovimiento', name: 'tipomovimiento', displayName: 'TIPO', width: '11%' ,enableFiltering: false ,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
        },
        { field: 'estadomov', name: 'estadomov', displayName: 'ESTADO', width: '7%' ,enableFiltering: false ,
          cellTemplate:'<label tooltip-placement="left" tooltip="{{ COL_FIELD.string }}"  style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 30px;" class="label {{ COL_FIELD.clase }} "><i class="fa {{ COL_FIELD.icon}}"></i></label>' },
      ],
      onRegisterApi: function(gridApiProducto) { 
        $scope.gridApiProducto = gridApiProducto;
        gridApiProducto.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApiProducto.selection.getSelectedRows();
        });
        gridApiProducto.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApiProducto.selection.getSelectedRows();
        });

        $scope.gridApiProducto.core.on.sortChanged($scope, function(grid, sortColumns) { 
          if (sortColumns.length == 0) {
            paginationOptionsPB.sort = null;
            paginationOptionsPB.sortName = null;
          } else {
            // POR DEFECTO ORDENAR POR: [6] => fecha_movimiento
            paginationOptionsPB.sort = sortColumns[6].sort.direction;
            paginationOptionsPB.sortName = sortColumns[6].name;
          }
          $scope.getPaginationPBServerSide();
        });
        $scope.gridApiProducto.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptionsPB.search = true; 
          paginationOptionsPB.searchColumn = { 
            'nombre_alm' : grid.columns[2].filters[0].term,
            "nombre_salm" : grid.columns[3].filters[0].term,
            'denominacion' : grid.columns[4].filters[0].term
          }
          $scope.getPaginationPBServerSide();
        });
        gridApiProducto.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPB.pageNumber = newPage;
          paginationOptionsPB.pageSize = pageSize;
          paginationOptionsPB.firstRow = (paginationOptionsPB.pageNumber - 1) * paginationOptionsPB.pageSize;
          $scope.getPaginationPBServerSide();
        });
      }
    };
    paginationOptionsPB.sortName = $scope.gridOptionsProductosBaja.columnDefs[3].name;
    $scope.getPaginationPBServerSide = function() {
      var arrParams = {
        paginate : paginationOptionsPB,
        datos : $scope.fBusqueda
      };

      salidasFarmServices.sListarProductoSalidas(arrParams).then(function (rpta) {
        // if(rpta.flag == 0){
        //   var pTitle = 'Advertencia!';
        //   var pType = 'warning';
        //   pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
        //   return;
        // } 
        $scope.gridOptionsProductosBaja.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsProductosBaja.data = rpta.datos;
      });
      //$scope.mySelectionGridPB = [];
    };

    
   
    

  }])
  .service("salidasFarmServices",function($http, $q) {
    return({
        sListarSalidas: sListarSalidas,
        sListarSalidasAnuladas: sListarSalidasAnuladas,
        sListarProductoSalidas: sListarProductoSalidas,
        sListarProductosSubAlmacen: sListarProductosSubAlmacen,
        sListarDetalleSalida: sListarDetalleSalida,
        sListarSalidasEnEspera: sListarSalidasEnEspera,
        sRealizarSalida : sRealizarSalida,
        sAnularSalida : sAnularSalida,
        sValidarCantidad : sValidarCantidad,
        sAprobarSolicitudSalida : sAprobarSolicitudSalida
    });

    function sListarSalidasAnuladas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/lista_salidas_anuladas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSalidas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/lista_salidas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductoSalidas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/lista_producto_salidas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosSubAlmacen(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/lista_Productos_SubAlmacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleSalida(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/lista_detalle_salidas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSalidasEnEspera(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/lista_salida_en_espera", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRealizarSalida(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/realizar_salida", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularSalida(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/anular_salida", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sValidarCantidad(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/validar_cantidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAprobarSolicitudSalida(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SalidasFarm/aprobar_salida", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });