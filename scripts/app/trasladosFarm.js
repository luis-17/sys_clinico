angular.module('theme.trasladosFarm', ['theme.core.services']) 
  .controller('trasladosFarmController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$uibModal', '$window', '$http', 
    '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox',
    'trasladosFarmServices',
    'almacenFarmServices',
    'guiaRemisionServices',
    'ModalReporteFactory',
    'blockUI',
    'motivoTrasladoServices',
    function($scope, $filter, $sce, $route, $interval, $modal, $uibModal, $window, $http, $theme, $log, $timeout, uiGridConstants, 
      pinesNotifications, hotkeys, $bootbox,
      trasladosFarmServices,
      almacenFarmServices,
      guiaRemisionServices,
      ModalReporteFactory,
      blockUI,
      motivoTrasladoServices ){ 
    'use strict';
    //$scope.$parent.reloadPage(); listarSubAlmacenesAlmacen2
    shortcut.remove("F2");
    
    $scope.fBusqueda = {};
    $scope.fBusqueda.almacen = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGrid = [];
    $scope.arr = {}; 
    $scope.arr.listaEstadoOrden = [
      {'id' : 1, 'descripcion' : 'POR APROBAR'},
      {'id' : 2, 'descripcion' : 'APROBADO'}
    ];
    $scope.guia_remision = false;
    $scope.items_guia_remision = false;
    $scope.estado_guia_remision = false;
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTAR ALMACENES
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { //console.log(rpta);
      $scope.listaAlmacenes = rpta.datos;
      $scope.fBusqueda.almacen = $scope.listaAlmacenes[0];
      $scope.listarSubAlmacenesAlmacen1($scope.fBusqueda.almacen.id);
      
    });
    // LISTAR SUB-ALMACENES ORIGEN
    $scope.listarSubAlmacenesAlmacen1 = function (idalmacen) { 
      var arrParams = {
        'idalmacen': idalmacen
      }
      almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rpta) {  
        $scope.listaSubAlmacenOrigen = rpta.datos;
        $scope.listaSubAlmacenOrigen.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
        $scope.fBusqueda.idsubalmacen1 = $scope.listaSubAlmacenOrigen[0].id;
        $scope.listarAlmacenesDestino($scope.fBusqueda.almacen.idempresaadmin);
      });
    }
    // LISTAR ALMACENES DESTINO DE LA EMPRESA SELECCIONADA listarSubAlmacenDestino listarSubAlmacenesAlmacen2Form
    $scope.listarAlmacenesDestino = function(idempresaadmin) {
      var arrParams = {
        'idempresaadmin': idempresaadmin
      }; 
      almacenFarmServices.sListarAlmacenesDestinoDeEmpresaCbo(arrParams).then(function (rpta) {  
        $scope.listaAlmacenesDestino = rpta.datos;
        //$scope.listaAlmacenesDestino.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
        $scope.fBusqueda.almacenDestino = $scope.listaAlmacenesDestino[0];
        $scope.listarSubAlmacenesAlmacen2($scope.fBusqueda.almacenDestino.id,$scope.fBusqueda.idsubalmacen1,$scope.fBusqueda.almacen.id); 
      });
    }

    // LISTAR SUB-ALMACENES DESTINO 
    $scope.listarSubAlmacenesAlmacen2 = function (idalmacen,idsubalmacen1,idsubalmacenorigen) { 
      var arrParams = {
        'idalmacen': idalmacen,
        'idsubalmacen1': idsubalmacen1,
        'idsubalmacenorigen': idsubalmacenorigen 
      }; 
      almacenFarmServices.sListarSubAlmacenesDeAlmacenExceptoCbo(arrParams).then(function (rpta) { 
        $scope.listaSubAlmacenDestino = rpta.datos;
        $scope.listaSubAlmacenDestino.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
        $scope.fBusqueda.idsubalmacen2 = $scope.listaSubAlmacenDestino[0].id;
        $scope.getPaginationServerSide();
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
        { field: 'idmovimiento1', name: 'fm.idmovimiento', displayName: 'COD.MOV.', width: '6%', visible: true,
          sort: { direction: uiGridConstants.DESC}  },
        { field: 'fecha_movimiento', name: 'fm.fecha_movimiento', displayName: 'FECHA DE TRASLADO', width: '12%', enableFiltering: false },
        { field: 'idalmacen', name: 'alm.idalmacen', displayName: 'ID ALMACEN ORIGEN', width: '6%', visible: false },
        { field: 'almacen', name: 'alm.nombre_alm', displayName: 'ALMACÉN ORIGEN', width: '15%'},
        { field: 'subAlmacenOrigen', name: 'salm.nombre_salm', displayName: 'SUB ALMACÉN ORIGEN', width: '15%' },
        { field: 'idalmacen2', name: 'fm2.idalmacen', displayName: 'ID ALMACEN DESTINO', width: '6%', visible: false },
        // { field: 'almacen_destino', name: 'alm2.nombre_alm', displayName: 'ALMACÉN DESTINO', width: '15%' },
        { field: 'almacen2', name: 'alm2.nombre_alm', displayName: 'ALMACÉN DESTINO', width: '15%'},
        { field: 'subAlmacenDestino', name: 'salm2.nombre_salm', displayName: 'SUB ALMACÉN DESTINO', width: '15%' },
        { field: 'usuario', name: 'usuario', displayName: 'RESPONSABLE'}, 
        { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO ', width: '4%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="" style="padding-left: 27%;">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        },
        { field: 'guias', type: 'object', name: 'guia', displayName: 'GUÍA', width: '4%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:
          '<div class="" style="padding-left: 27%;">'+
          '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" ng-if="row.entity.guias.numero > 0" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" '+
          'class="label {{ COL_FIELD.claseLabel }} ml-xs">{{ COL_FIELD.numero }}</label></div>'
        },
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
          $scope.btnConsultarGuiaRemision($scope.mySelectionGrid[0]);
          $scope.btnConsultarItemsParaGuiaRemision($scope.mySelectionGrid[0]);
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            // POR DEFECTO ORDENAR POR: [6] => fecha_movimiento
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = {
            'fm.idmovimiento' : grid.columns[1].filters[0].term,
            'alm.nombre_alm' : grid.columns[4].filters[0].term,
            'salm.nombre_salm' : grid.columns[5].filters[0].term,
            "alm2.nombre_alm" : grid.columns[6].filters[0].term,
            'salm2.nombre_salm' : grid.columns[7].filters[0].term,
            "(rhe.apellido_paterno || ' ' || rhe.apellido_materno  || ', ' || rhe.nombres)" : grid.columns[8].filters[0].term 
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
    $scope.getPaginationServerSide = function(load) { 
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      } 
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      trasladosFarmServices.sListarTraslados(arrParams).then(function (rpta) { 
        $scope.gridOptions.data = rpta.datos;
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;   
        if( loader ){
          blockUI.stop();
        }

      });
      $scope.mySelectionGrid = [];
    };

    //TIENE GUIA DE REMISION
    $scope.btnConsultarGuiaRemision = function(row){   
    console.log(row);   
      var arrParams = {
        idmovimiento : row.idmovimiento1 
      };
      guiaRemisionServices.sConsultarGuiaRemision(arrParams).then(function (rpta) {
        if(rpta.flag == 1){
          $scope.guia_remision = true;
        }else{
          $scope.guia_remision = false;
        }
      });     
    }

    //TIENE ITEMS PARA GUIA DE REMISION
    $scope.btnConsultarItemsParaGuiaRemision = function(row){   
    console.log(row);   
      var arrParams = {
        idmovimiento : row.idmovimiento1 
      };
      guiaRemisionServices.sConsultarItemsParaGuiaRemision(arrParams).then(function (rpta) {
        if(rpta.flag == 1){
          $scope.items_guia_remision = true;
        }else{
          $scope.items_guia_remision = false;
        }
      });     
    }

    $scope.btnNuevoTraslado = function(size) { 
      $modal.open({
        templateUrl: angular.patchURLCI+'AlmacenFarmacia/ver_popup_traslado',
        size: size || 'xlg',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.fData.almacen = {};
          $scope.fData.fecha_traslado = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fData.hora_traslado = $filter('date')(new Date(),'HH:mm');
          $scope.pHoraMinuto = /^[0-1][0-9]:[0-5][0-9]|2[0-3]:[0-5][0-9]$/;
          $scope.titleForm = 'Traslado entre Sub Almacenes';
          
          //$scope.fData.almacen = angular.copy($scope.fBusqueda.almacen); listaSubAlmacenOrigen
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
          // se obtiene una copia de la lista de sub almacenes origen provenientes de la vista anterior  listaSubAlmacenOrigenForm listaAlmacenes 
          $scope.fData.idsubalmacenorigen = angular.copy($scope.fBusqueda.idsubalmacen1);
          $scope.listaSubAlmacenOrigenForm = angular.copy($scope.listaSubAlmacenOrigen);

          $scope.listaSubAlmacenOrigenForm.splice(0,1);
          if($scope.fData.idsubalmacenorigen == 0){ 
            $scope.fData.idsubalmacenorigen = $scope.listaSubAlmacenOrigenForm[0].id;
          }
          $scope.listaAlmacenesDestinoForm = angular.copy($scope.listaAlmacenesDestino);
          $scope.fData.almacenDestino = $scope.listaAlmacenesDestinoForm[0];

          // LISTAR SUB-ALMACENES ORIGEN
          $scope.listarSubAlmacenesAlmacen1Form = function (idalmacen) { 
            console.log(idalmacen,'idalmacen');
            var arrParams = {
              'idalmacen': idalmacen
            };
            almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rptaSAAC) { 
              console.log(rptaSAAC.datos,'rptaSAAC.datos');
              $scope.listaSubAlmacenOrigenForm = rptaSAAC.datos;
              $scope.listaSubAlmacenOrigenForm.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
              $scope.fData.idsubalmacenorigen = $scope.listaSubAlmacenOrigenForm[0].id;
              console.log($scope.listaSubAlmacenOrigenForm,'$scope.listaSubAlmacenOrigenForm');
              $scope.listarAlmacenesDestino($scope.fData.almacen.idempresaadmin);
            });
          } 

          // inicialmente se carga la lista de sub almacenes destino 
          // LISTAR ALMACENES DESTINO DE LA EMPRESA SELECCIONADA listarSubAlmacenDestino idsubalmacenorigen  almacenDestino
          $scope.listarAlmacenesDestino = function(idempresaadmin) {
            var arrParams = {
              'idempresaadmin': idempresaadmin
            };
            almacenFarmServices.sListarAlmacenesDestinoDeEmpresaCbo(arrParams).then(function (rptaADEC) {  
              $scope.listaAlmacenesDestinoForm = rptaADEC.datos;
              $scope.fData.almacenDestino = $scope.listaAlmacenesDestinoForm[0];
              $scope.listarSubAlmacenesAlmacen2Form($scope.fData.almacenDestino.id,$scope.fData.idsubalmacenorigen,$scope.fData.almacen.id); 
            });
          }

          // LISTAR SUB-ALMACENES DESTINO
          $scope.listarSubAlmacenesAlmacen2Form = function (idalmacen,idsubalmacen1,idsubalmacenorigen) { 
            var arrParams = {
              'idalmacen': idalmacen,
              'idsubalmacen1': idsubalmacen1,
              'idsubalmacenorigen': idsubalmacenorigen 
            }; 
            almacenFarmServices.sListarSubAlmacenesDeAlmacenExceptoCbo(arrParams).then(function (rptaAAEC) { 
              $scope.listaSubAlmacenDestino = rptaAAEC.datos;
              $scope.fData.idsubalmacen2 = $scope.listaSubAlmacenDestino[0].id;
              $scope.getPaginationProdServerSide(true);
            });
          } 
          $scope.listarSubAlmacenesAlmacen2Form($scope.fData.almacenDestino.id,$scope.fData.idsubalmacenorigen,$scope.fData.almacen.id); 

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          // GRILLA DE PRODUCTOS A TRASLADAR
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
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'Id.', width: '10%'},
              { field: 'producto', name: 'denominacion', displayName: 'Producto', },
              { field: 'stock', name: 'stock_actual_malm', displayName: 'Stock', width: '12%', enableFiltering: false,
                sort: { direction: uiGridConstants.DESC},
                cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                  if(row.entity.stock == 0){
                    return 'ui-bgBrownCell text-center';
                  }else{
                    return 'text-center'; // se aplica colorcito amarillo solo si es editable
                  }
                }
              },
              { field: 'precio', name: 'precio', displayName: 'S/.', width: '12%', enableFiltering: false, cellClass: 'text-right'}
            ],
            onRegisterApi: function(gridApiProd) {
              $scope.gridApiProd = gridApiProd;
              gridApiProd.selection.on.rowSelectionChanged($scope,function(row){ 
                $scope.mySelectionProdGrid = gridApiProd.selection.getSelectedRows();
                angular.forEach($scope.mySelectionProdGrid, function (value, key) { 
                  var tempCopy = angular.copy(value);
                  var boolNoAgregar = false;
                  if(value.stock <= 0){
                    boolNoAgregar = true; // no agrega si no hay stock
                  }else{
                    angular.forEach($scope.gridOptionsAddProducto.data, function (valueDet, keyDet) {
                      if( valueDet.idmedicamento == value.idmedicamento ){
                        boolNoAgregar = true; // no agrega si ya se agregó el producto
                      }
                    }) 
                  }
                  if( !(boolNoAgregar) ) {
                    tempCopy.cantidad = value.stock; // agrega con una cantidad de 1 por defecto
                    $scope.gridOptionsAddProducto.data.push( tempCopy );
                  }
                });
              });
              gridApiProd.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionProdGrid = gridApiProd.selection.getSelectedRows(); 
                angular.forEach($scope.mySelectionProdGrid, function (value, key) { 
                  var tempCopy = angular.copy(value);
                  var boolNoAgregar = false;
                  if(value.stock <= 0){
                    boolNoAgregar = true;  // no agrega si no hay stock
                  }else{
                    angular.forEach($scope.gridOptionsAddProducto.data, function (valueDet, keyDet) {
                      if( valueDet.idmedicamento == value.idmedicamento ){
                        boolNoAgregar = true;  // no agrega si ya se agregó el producto
                      }
                    }) 
                  }
                  if( !(boolNoAgregar) ) {
                    tempCopy.cantidad = value.stock;
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
                $scope.getPaginationProdServerSide(true);
              });
              gridApiProd.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsProd.pageNumber = newPage;
                paginationOptionsProd.pageSize = pageSize;
                paginationOptionsProd.firstRow = (paginationOptionsProd.pageNumber - 1) * paginationOptionsProd.pageSize;
                $scope.getPaginationProdServerSide(true);
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
          $scope.getPaginationProdServerSide = function(loader) { 
            var loader = loader || false;
            if( loader ){ 
              blockUI.start('Cargando datos...'); 
            }
            var arrParams = {
              paginate : paginationOptionsProd,
              datos: $scope.fData
            };
            trasladosFarmServices.sListarProductosSubAlmacen(arrParams).then(function (rpta) {
              $scope.gridOptionsProdSubAlmacen.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsProdSubAlmacen.data = rpta.datos; 
              if( loader ){ 
                blockUI.stop(); 
              }
            });
            $scope.mySelectionProdGrid = [];
          };
          // $scope.getPaginationProdServerSide();
          
          $scope.gridOptionsAddProducto = { 
            minRowsToShow: 6,
            rowHeight: 30,
            //paginationPageSize: 10,
            enableCellEdit: false,
            showGridFooter: true,
            columnDefs: [ 
             { field: 'idmedicamento', name: 'idmedicamento', displayName: 'Id.', width: '10%' },
              { field: 'producto', name: 'denominacion', displayName: 'Producto',  sort: { direction: uiGridConstants.ASC} },
              { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '12%', enableFiltering: false, enableCellEdit: true, cellClass:'ui-editCell' },
              { field: 'precio', name: 'precio', displayName: 'S/.', width: '12%', enableFiltering: false, enableCellEdit: true,cellClass:'ui-editCell'},
              { field: 'accion', name:'accion', displayName: 'ACCION', width: '12%', 
              cellTemplate:'<div class="">'+
                '<button type="button" class="btn btn-sm btn-danger inline-block m-xs" ng-click="grid.appScope.btnQuitarDeLaCesta(row)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>'
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
                
                rowEntity.column = colDef.field;
                if( rowEntity.column == 'cantidad' && newValue != oldValue ){ // valida solo si la cant ingresada es diferente

                  trasladosFarmServices.sValidarCantidad(rowEntity).then(function (rpta) { 
                    if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      rowEntity.cantidad = oldValue;
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
                    }else if(rpta.flag != 1){
                      alert('Error inesperado');
                    }
                    $scope.getPaginationProdServerSide();
                    
                  });
                }else if( rowEntity.column == 'precio' && newValue != oldValue ){

                  if( isFinite(rowEntity.precio) ){ // verifica si es un numero valido
                    if( rowEntity.precio < 0 ){ // si es un numero valido tiene q ser positivo
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      var pMessage = 'Ingrese un monto mayor o igual a 0';
                      rowEntity.precio = oldValue;
                      pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3500 });
                    }
                  }else{
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    var pMessage = 'Ingrese un monto válido';
                    rowEntity.precio = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3500 });
                  }
                  
                }
                
                $scope.$apply();
              });
            }
          }; 
          $scope.btnQuitarDeLaCesta = function (row) { 
            // var arrParams = row.entity;
            var index = $scope.gridOptionsAddProducto.data.indexOf(row.entity); 
            $scope.gridOptionsAddProducto.data.splice(index,1); 
          }
          $scope.aceptar = function () { 
            blockUI.start('Ejecutando proceso...');
            $scope.fData.productos = $scope.gridOptionsAddProducto.data;
            $scope.fData.estemporal = null;
            trasladosFarmServices.sRealizarTraslado($scope.fData).then(function (rpta) { 
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
              blockUI.stop();
              $scope.getPaginationServerSide();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
            });
          }
        }
      })
    }
    $scope.btnAnularTraslado = function() {
      var pMensaje = '¿Realmente desea anular el traslado?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          trasladosFarmServices.sAnularTraslado($scope.mySelectionGrid).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
    $scope.btnVerDetalleTraslado = function (fTraslado) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'AlmacenFarmacia/ver_popup_detalle_traslado',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          
          $scope.titleForm = 'Detalle del Traslado';

          var paginationOptionsDet = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.fTraslado = fTraslado;
          $scope.gridOptionsDetalleTraslado = {
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
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'CODIGO', width: '10%' },
              { field: 'producto', name: 'denominacion', displayName: 'PRODUCTO' },
              { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '20%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '10%'},
              
              
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsDet.sort = null;
                  paginationOptionsDet.sortName = null;
                } else {
                  paginationOptionsDet.sort = sortColumns[0].sort.direction;
                  paginationOptionsDet.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleTrasladoServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationOptionsDet.search = true; 
                paginationOptionsDet.searchColumn = { 
                  'idmedicamento' : grid.columns[1].filters[0].term,
                  'denomincaion' : grid.columns[2].filters[0].term
                }
                $scope.getPaginationDetalleTrasladoServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsDet.pageNumber = newPage;
                paginationOptionsDet.pageSize = pageSize;
                paginationOptionsDet.firstRow = (paginationOptionsDet.pageNumber - 1) * paginationOptionsDet.pageSize;
                $scope.getPaginationDetalleTrasladoServerSide();
              });
            }
          };

          $scope.getPaginationDetalleTrasladoServerSide = function() {
            var arrParams = {
              paginate : paginationOptionsDet,
              datos: fTraslado
            };
            trasladosFarmServices.sListarDetalleTraslado(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleTraslado.data = rpta.datos;
              $scope.gridOptionsDetalleTraslado.totalItems = rpta.paginate.totalRows;
            });
          };
          $scope.getPaginationDetalleTrasladoServerSide();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
         
        }
      });
    }
    $scope.btnImprimir = function(fTraslado){
      var abreviatura = 'TRAS';
      $scope.fTraslado = fTraslado;
      if( $scope.fTraslado.estado_movimiento == 0 ){
        abreviatura = 'ANLDO';
      }
      $scope.fTraslado.titulo = 'TRASLADO';
      $scope.fTraslado.tituloAbv = abreviatura;
      $scope.fTraslado.salida = 'pdf';
      var arrParams = {
          titulo: $scope.fTraslado.titulo,
          datos: $scope.fTraslado,
          url: angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_traslado',
          metodo: 'php'
      }
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnGenerarGuiaRemision = function() { 
      blockUI.start('Abriendo formulario...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'GuiaRemision/ver_popup_guia_remision',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.submodulo = 'nuevo';
          $scope.isRegisterSuccess = false;
          $scope.titleForm = 'Registro de Guía de Remisión';
          $scope.fDataGR = {};
          $scope.fDataGR.idmovimiento = $scope.mySelectionGrid[0].idmovimiento1;
          $scope.fDataGR.usuario = $scope.mySelectionGrid[0].usuario;
          $scope.fDataGR.destinatario = {};
          $scope.fDataGR.destinatario.ruc = $scope.mySelectionGrid[0].ruc;
          $scope.fDataGR.destinatario.razon_social = $scope.mySelectionGrid[0].razon_social;
          $scope.fDataGR.destinatario.domicilio = $scope.mySelectionGrid[0].domicilio_fiscal;
          $scope.fDataGR.estado_orden = $scope.arr.listaEstadoOrden[0].id;
          $scope.fDataGR.almacen = $scope.listaAlmacenes[0];
          $scope.fDataGR.almacenDestino = $scope.listaAlmacenesDestino[0];
          $scope.fDataGR.fecha_movimiento = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataGR.fecha_guia = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataGR.fecha_aprobacion = null;
          $scope.fDataGR.idtipodocumento = 5;
          $scope.fDataGR.submodulo = $scope.submodulo;
          $scope.listaEstadoTraslado  = [
            { id:'1', descripcion:'POR ENVIAR' },
            { id:'2', descripcion:'ENVIADO' }
          ];
          $scope.fDataGR.estado = $scope.listaEstadoTraslado[0].id;
          // Lista Motivo Traslado
          motivoTrasladoServices.sListaMotivoTraslado().then(function (rpta) { 
            $scope.listaMotivoTraslado = rpta.datos;
            $scope.listaMotivoTraslado.splice(0,0,{ id : '0', descripcion:'-- Seleccione --'});
            $scope.fDataGR.motivo_traslado = $scope.listaMotivoTraslado[0].id;
          });
          // Lista Series
          guiaRemisionServices.sListarNumeroSerie().then(function (rpta) { 
            $scope.listaNumeroSerie = rpta.datos;
            $scope.fDataGR.serie = $scope.listaNumeroSerie[0];
            $scope.fDataGR.numero_serie = $scope.listaNumeroSerie[0].numero_serie;
            $scope.fDataGR.idcajamaster = $scope.listaNumeroSerie[0].idcajamaster;
          });
          //Cantidad Guias - Cantidad Items
          guiaRemisionServices.sCantidadItemsGuias($scope.fDataGR).then(function (rpta) { 
            $scope.fDataGR.guia = rpta.guia;
            $scope.fDataGR.cantidad_guias = rpta.cantidad_guias;
            $scope.fDataGR.items = rpta.cantidad_items;
          });

          //cantidad de guias generadas 1/4
          $scope.fDataGR.relacion_guias = $scope.fDataGR.items
          $scope.mySelectionGrid = [];
          $scope.gridOptionsGR = { 
            paginationPageSize: 100,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            data: null,
            rowHeight: 26,
            enableCellEditOnFocus: true,
            multiSelect: false,
            columnDefs: [
              { field: 'item', displayName: 'ITEM', width: 80, enableCellEdit: false, enableSorting: false },
              { field: 'cantidad', displayName: 'CANT.', width: 90, enableCellEdit: false, enableSorting: false },
              { field: 'codigo', displayName: 'CÓDIGO', width: 100, enableCellEdit: false, enableSorting: false },
              { field: 'nombre_lab', displayName: 'LABORATORIO', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCIÓN', enableCellEdit: false, enableSorting: false },
              { field: 'caja_unidad', displayName: 'CAJA/UND.', width: 90, enableCellEdit: false, enableSorting: false },
              /*{ field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, 
                cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' 
              }*/
            ]
            ,onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              /*gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field; 
                $scope.$apply();
              });*/
            }
          };
          $scope.getTableHeight = function() {
             var rowHeight = 26; // your row height 
             var headerHeight = 25; // your header height 
             return {
                // height: ($scope.gridOptionsGR.data.length * rowHeight + headerHeight + 40) + "px"
                height: (10 * rowHeight + headerHeight + 20) + "px"
             };
          };
          $scope.generarCodigoTicket = function () {  
            guiaRemisionServices.sGenerarNumeroSerie($scope.fDataGR).then(function (rpta) { 
              $scope.fDataGR.numero_serie = rpta.numero_serie;
            });
          };
          $scope.cambiarSerie = function (valor) {  
            $scope.fDataGR.idcajamaster = $scope.fDataGR.serie.idcajamaster;
            guiaRemisionServices.sGenerarNumeroSerie($scope.fDataGR).then(function (rpta) { 
              $scope.fDataGR.numero_serie = rpta.numero_serie;
            });
          };
          $scope.btnQuitarDeLaCesta = function (row) { 
            var index = $scope.gridOptionsGR.data.indexOf(row.entity); 
            $scope.gridOptionsGR.data.splice(index,1);
            // $scope.calcularTotales(); 
          }
          $scope.btnCargarDetalle = function () { 
            guiaRemisionServices.sListarTrasladosParaGuiaLIMIT($scope.fDataGR).then(function (rpta) { 
              $scope.gridOptionsGR.data = rpta.datos;
            }); 
          }
          $scope.btnCargarDetalle();

          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.fDataGR = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function(){

            if($scope.isRegisterSuccess){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La guía ya fue registrada', type: 'warning', delay: 3000 });
              return false;
            }

            $scope.fDataGR.detalle = $scope.gridOptionsGR.data;
            if( $scope.fDataGR.detalle.length < 1 ){ 
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún Producto/Medicamento', type: 'warning', delay: 3000 }); 
              return false; 
            }
            //console.log('fDataOC: ', $scope.fDataOC);
            blockUI.start('Ejecutando proceso...');
            guiaRemisionServices.sRegistrar($scope.fDataGR).then(function (rpta) { 
              blockUI.stop();
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $scope.isRegisterSuccess = true;
                $scope.getPaginationServerSide();
                //$modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';               
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          
          $scope.btnImprimirGuiaRemision = function () {

            $scope.fDataGR.guia_remision = $scope.fDataGR.serie.id + ' - N° ' + $scope.fDataGR.serie.numero_serie;
            guiaRemisionServices.sImprimirGuiaRemision($scope.fDataGR).then(function (rpta) { 
            if(rpta.flag == 1){
              var printContents = rpta.html;
              var popupWin = window.open('', 'windowName', 'width=1270,height=847');
              popupWin.document.open()
              popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
              popupWin.document.close();
            }else { 
              if(rpta.flag == 0) { // ALGO SALIÓ MAL
                var pTitle = 'Error';
                var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                var pType = 'warning';
              }
              
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
            }
          });
                
          }

          /* ============================ */ 
          /* ATAJOS DE TECLADO NAVEGACION */ 
          /* ============================ */
          shortcut.remove('F2');
          shortcut.add("F2",function($event) { 
            $scope.aceptar(); 
          });

          shortcut.remove('F4');
          shortcut.add("F4",function($event) { 
            $scope.btnImprimirGuiaRemision(); 
          });
        }
      }) 
    }
    $scope.btnVerListaGuiaRemision = function (fTraslado) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'GuiaRemision/ver_popup_lista_guias_remision',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) {           
          $scope.titleForm = 'Lista Guias Remisión';
          $scope.fDataEdit = {};
          $scope.fTraslado = fTraslado;
          $scope.fDataEdit.idmovimiento = $scope.fTraslado.idmovimiento1;
          $scope.fDataEdit.usuario = $scope.fTraslado.usuario;
          $scope.fDataEdit.destinatario = {};
          $scope.fDataEdit.destinatario.ruc = $scope.fTraslado.ruc;
          $scope.fDataEdit.destinatario.razon_social = $scope.fTraslado.razon_social;
          $scope.fDataEdit.destinatario.domicilio = $scope.fTraslado.domicilio_fiscal;
          $scope.fDataEdit.almacenDestino = $scope.listaAlmacenesDestino[0];

          var paginationOptionsListaGR = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionGridGR = [];
          $scope.gridOptionsListaGR = {
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [ 
              { field: 'idmovimiento', name: 'idmovimiento', displayName: 'CODIGO', width: '10%', visible:false },
              { field: 'codigo', name: 'codigo', displayName: 'N° GUÍA', width: '15%' },
              { field: 'fecha_guia', name: 'fecha_guia', displayName: 'FECHA TRASLADO', width: '18%' },
              { field: 'punto_partida', name: 'punto_partida', displayName: 'PUNTO PARTIDA' },
              { field: 'punto_llegada', name: 'punto_llegada', displayName: 'PUNTO LLEGADA'},
              { field: 'estado_gr', name: 'estado_gr', displayName: 'ESTADO', width: '10%'},                     
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionGridGR = gridApi.selection.getSelectedRows();
                if ($scope.mySelectionGridGR[0].estado == 1) {$scope.estado_guia_remision = true;
                }else{$scope.estado_guia_remision = false;} 
                console.log();              
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionGridGR = gridApi.selection.getSelectedRows();
              });
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsListaGR.sort = null;
                  paginationOptionsListaGR.sortName = null;
                } else {
                  paginationOptionsListaGR.sort = sortColumns[0].sort.direction;
                  paginationOptionsListaGR.sortName = sortColumns[0].name;
                }
                $scope.getPaginationListaGuiaRemision();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationOptionsListaGR.search = true; 
                paginationOptionsListaGR.searchColumn = { 
                  'idmedicamento' : grid.columns[1].filters[0].term,
                  'denomincaion' : grid.columns[2].filters[0].term
                }
                $scope.getPaginationListaGuiaRemision();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsListaGR.pageNumber = newPage;
                paginationOptionsListaGR.pageSize = pageSize;
                paginationOptionsListaGR.firstRow = (paginationOptionsListaGR.pageNumber - 1) * paginationOptionsListaGR.pageSize;
                $scope.getPaginationListaGuiaRemision();
              });
            }
          };

          $scope.getPaginationListaGuiaRemision = function() {
            var arrParams = {
              paginate : paginationOptionsListaGR,
              datos: $scope.fDataEdit
            };
            guiaRemisionServices.sListarGuiasRemision(arrParams).then(function (rpta) {
              $scope.gridOptionsListaGR.data = rpta.datos;
              $scope.gridOptionsListaGR.totalItems = rpta.paginate.totalRows;

              console.log($scope.gridOptionsListaGR.data);
            });
          };
          $scope.getPaginationListaGuiaRemision();

          $scope.btnEditarGuiaRemision = function() { 

            if($scope.mySelectionGridGR[0].estado == 2){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se pueden editar guías enviadas', type: 'warning', delay: 3000 });
              return false;
            }

            blockUI.start('Abriendo formulario...');
            $uibModal.open({ 
              templateUrl: angular.patchURLCI+'GuiaRemision/ver_popup_guia_remision',
              size: 'xlg',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                blockUI.stop();
                $scope.submodulo = 'editar';
                $scope.titleForm = 'Registro de Guía de Remisión';
                $scope.fDataGR = $scope.mySelectionGridGR[0];
                $scope.fDataGR.idmovimiento = $scope.fDataEdit.idmovimiento;
                $scope.fDataGR.usuario = $scope.fDataEdit.usuario;
                $scope.fDataGR.destinatario = $scope.fDataEdit.destinatario;
                $scope.fDataGR.idtipodocumento = 5;
                $scope.fDataGR.submodulo = $scope.submodulo;
                $scope.fDataGR.almacenDestino = $scope.listaAlmacenesDestino[0];
                $scope.listaEstadoTraslado  = [
                  { id:'1', descripcion:'POR ENVIAR' },
                  { id:'2', descripcion:'ENVIADO' }
                ];

                // Lista Motivo Traslado
                motivoTrasladoServices.sListaMotivoTraslado().then(function (rpta) { 
                  $scope.listaMotivoTraslado = rpta.datos;
                  $scope.listaMotivoTraslado.splice(0,0,{ id : '0', descripcion:'-- Seleccione --'});
                });

                $scope.fDataGR.guia_remision = $scope.fDataGR.numero_serie + ' - N° ' + $scope.fDataGR.numero_correlativo;
                $scope.fDataGR.numero_serie = $scope.fDataGR.numero_correlativo;

                $scope.mySelectionGrid = [];
                $scope.gridOptionsGR = { 
                  paginationPageSize: 100,
                  enableRowSelection: false,
                  enableSelectAll: false,
                  enableFiltering: false,
                  enableFullRowSelection: false,
                  data: null,
                  rowHeight: 26,
                  enableCellEditOnFocus: true,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'item', displayName: 'ITEM', width: 80, enableCellEdit: false, enableSorting: false },
                    { field: 'cantidad', displayName: 'CANT.', width: 90, enableCellEdit: false, enableSorting: false },
                    { field: 'codigo', displayName: 'CÓDIGO', width: 100, enableCellEdit: false, enableSorting: false },
                    { field: 'nombre_lab', displayName: 'LABORATORIO', enableCellEdit: false, enableSorting: false },
                    { field: 'descripcion', displayName: 'DESCRIPCIÓN', enableCellEdit: false, enableSorting: false },   
                  ]                 
                };
                $scope.getTableHeight = function() {
                   var rowHeight = 26; // your row height 
                   var headerHeight = 25; // your header height 
                   return {
                      // height: ($scope.gridOptionsGR.data.length * rowHeight + headerHeight + 40) + "px"
                      height: (10 * rowHeight + headerHeight + 20) + "px"
                   };
                };
                $scope.generarCodigoTicket = function () {  
                  guiaRemisionServices.sGenerarNumeroSerie($scope.fDataGR).then(function (rpta) { 
                    $scope.fDataGR.numero_serie = rpta.numero_serie;
                  });
                };
                $scope.cambiarSerie = function (valor) {  
                  $scope.fDataGR.idcajamaster = $scope.fDataGR.serie.idcajamaster;
                  guiaRemisionServices.sGenerarNumeroSerie($scope.fDataGR).then(function (rpta) { 
                    $scope.fDataGR.numero_serie = rpta.numero_serie;
                  });
                };
                $scope.btnQuitarDeLaCesta = function (row) { 
                  var index = $scope.gridOptionsGR.data.indexOf(row.entity); 
                  $scope.gridOptionsGR.data.splice(index,1);
                  // $scope.calcularTotales(); 
                }
                $scope.btnCargarDetalle = function () { 
                  guiaRemisionServices.sListarDetalleGuia($scope.fDataGR).then(function (rpta) { 
                    $scope.gridOptionsGR.data = rpta.datos;
                    $scope.fDataGR.guia = rpta.guia;
                    $scope.fDataGR.cantidad_guias = rpta.cantidad_guias;
                  }); 
                }
                $scope.btnCargarDetalle();

                $scope.cancel = function(){
                  $modalInstance.dismiss('cancel');
                  $scope.fDataGR = {};
                  $scope.getPaginationListaGuiaRemision();
                }
                $scope.aceptar = function(){

                  blockUI.start('Ejecutando proceso...');
                  guiaRemisionServices.sEditar($scope.fDataGR).then(function (rpta) { 
                    blockUI.stop();
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success'; 
                      $modalInstance.dismiss('cancel');                      
                      $scope.fDataGR = {};
                      $scope.mySelectionGrid = [];
                      $scope.getPaginationListaGuiaRemision();
                      
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';               
                    }else{
                      alert('Algo salió mal...');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                }
              }
            }) 
          }

          $scope.btnAnularGuiaRemision = function() {
            if($scope.mySelectionGridGR[0].estado == 2){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se pueden anular guías enviadas', type: 'warning', delay: 3000 });
              return false;
            }

            var pMensaje = '¿Realmente desea anular el traslado?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                var arrParams = {
                  mySelectionGridGR : $scope.mySelectionGridGR[0],
                  almacenDestino: $scope.listaAlmacenesDestino[0]
                }; 
                guiaRemisionServices.sAnular(arrParams).then(function (rpta) {
                  if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.getPaginationListaGuiaRemision();
                      $scope.getPaginationServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });
              }
            });
          }

          $scope.btnImprimirGuiaRemision = function () {

            $scope.fDataGR = $scope.mySelectionGridGR[0];
            $scope.fDataGR.idmovimiento = $scope.fDataEdit.idmovimiento;
            $scope.fDataGR.usuario = $scope.fDataEdit.usuario;
            $scope.fDataGR.destinatario = $scope.fDataEdit.destinatario;
            $scope.fDataGR.idtipodocumento = 5;
            $scope.fDataGR.submodulo='editar';
            $scope.fDataGR.guia_remision = $scope.fDataGR.numero_serie + ' - N° ' + $scope.fDataGR.numero_correlativo;
            $scope.fDataGR.almacenDestino = $scope.listaAlmacenesDestino[0];
            
            guiaRemisionServices.sListarDetalleGuia($scope.fDataGR).then(function (rpta) { 
              $scope.fDataGR.detalle = rpta.datos;
                guiaRemisionServices.sImprimirGuiaRemision($scope.fDataGR).then(function (rpta) { 
                if(rpta.flag == 1){
                  var printContents = rpta.html;
                  var popupWin = window.open('', 'windowName', 'width=1270,height=847');
                  popupWin.document.open()
                  popupWin.document.write('<html><head></head><body onload="window.print()">' + printContents + '</html>');
                  popupWin.document.close();
                }else { 
                  if(rpta.flag == 0) { // ALGO SALIÓ MAL
                    var pTitle = 'Error';
                    var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                    var pType = 'warning';
                  }
                  
                  pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
                }
              });
            });    
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }

          /* ============================ */ 
          /* ATAJOS DE TECLADO NAVEGACION */ 
          /* ============================ */

          shortcut.remove('F4');
          shortcut.add("F4",function($event) { 
            $scope.btnImprimirGuiaRemision(); 
          });
         
        }
      });
    }
    
    
  }])
  .service("trasladosFarmServices",function($http, $q) {
    return({
        sListarTraslados: sListarTraslados,
        sListarProductosSubAlmacen: sListarProductosSubAlmacen,
        sListarDetalleTraslado: sListarDetalleTraslado,
        sRealizarTraslado : sRealizarTraslado,
        sRealizarTrasladoTemporal : sRealizarTrasladoTemporal,
        sValidarCantidad : sValidarCantidad,
        sAnularTraslado : sAnularTraslado
    });

    function sListarTraslados(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/lista_traslados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosSubAlmacen(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/lista_Productos_SubAlmacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleTraslado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/lista_detalle_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRealizarTraslado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/realizar_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRealizarTrasladoTemporal(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/realizar_traslado_temporal", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sValidarCantidad(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/validar_cantidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularTraslado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrasladosFarm/anular_traslado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });