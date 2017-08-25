angular.module('theme.entradasFarm', ['theme.core.services'])
  .controller('entradasFarmController', ['$scope', 'blockUI', '$filter', '$route', '$sce', '$interval', '$uibModal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox', '$controller',
    'entradasFarmServices',
    'trasladosFarmServices',
    'almacenFarmServices',
    'medicamentoAlmacenServices',
    'proveedorFarmaciaServices',
    'ordenCompraServices',
    'cajaTemporalFarmServices',
    'medicamentoServices',
    'ModalReporteFactory',
    function($scope, blockUI, $filter, $sce, $route, $interval, $uibModal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      $bootbox, $controller,
      entradasFarmServices,
      trasladosFarmServices,
      almacenFarmServices,
      medicamentoAlmacenServices,
      proveedorFarmaciaServices,
      ordenCompraServices ,
      cajaTemporalFarmServices,
      medicamentoServices,
      ModalReporteFactory){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");
    $controller('proveedorFarmaciaController', {
        $scope : $scope
    });
    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    $scope.modulo = 'entradas'
    $scope.fBusqueda.almacen = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGridIngr = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringIA = function(){
      $scope.gridOptionsIngresosAnulados.enableFiltering = !$scope.gridOptionsIngresosAnulados.enableFiltering;
      $scope.gridApiAnulado.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringPC = function(){
      $scope.gridOptionsProductosCompra.enableFiltering = !$scope.gridOptionsProductosCompra.enableFiltering;
      $scope.gridApiProducto.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTAR ALMACENES
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { //console.log(rpta);
      $scope.listaAlmacenes = rpta.datos;
      $scope.fBusqueda.almacen = $scope.listaAlmacenes[0];

      $scope.getPaginationServerSide();
      $scope.getPaginationIAServerSide();
      $scope.getPaginationPCServerSide();
    });

    // LISTA TIPO DE ENTRADA
    $scope.listaTipoEntrada = [
      { id:'0', descripcion:'TODOS' },
      { id:'2', descripcion:'COMPRA' },
      { id:'4', descripcion:'REGALO' },
      { id:'6', descripcion:'REINGRESO'}
    ];
    $scope.fBusqueda.idtipoentrada = $scope.listaTipoEntrada[0].id;

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
        { field: 'factura', name: 'ticket_venta', displayName: 'FACTURA', width: '8%',},
        { field: 'orden_compra', name: 'orden_compra', displayName: 'ORDEN DE COMPRA', width: '10%',},
        { field: 'guia_remision', name: 'guia_remision', displayName: 'GUIA DE REMISION', width: '10%', visible:false },
        { field: 'razon_social', name: 'razon_social', displayName: 'PROVEEDOR' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE INGRESO', width: '10%', enableFiltering: false  },
        { field: 'fecha_vence_factura', name: 'fecha_vence_factura', displayName: 'FECHA VENCE FACT.', width: '10%', enableFiltering: false, visible:false  },        
        { field: 'subtotal', name: 'sub_total', displayName: 'SUB TOTAL', width: '9%', enableFiltering: false, },
        { field: 'igv', name: 'igv', displayName: 'IGV', width: '9%', enableFiltering: false, },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '9%', enableFiltering: false,},
        { field: 'tipo_entrada', type: 'object', name: 'tipo_movimiento', displayName: 'TIPO DE INGRESO', width: '10%', enableFiltering: false,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' },

        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        }

      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridIngr = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridIngr = gridApi.selection.getSelectedRows();
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
            'idmovimiento' : grid.columns[1].filters[0].term,
            'ticket_venta' : grid.columns[2].filters[0].term,
            'orden_compra' : grid.columns[3].filters[0].term,
            'guia_remision' : grid.columns[4].filters[0].term,
            'razon_social' : grid.columns[5].filters[0].term

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
      entradasFarmServices.sListarEntradas(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        $scope.gridOptions.sumTotal = rpta.sumTotal;
        if( loader ){
          blockUI.stop();
        }
      });
      $scope.mySelectionGridIngr = [];
    };
    
    /*==================================== BOTON PROCESAR =========================================================*/
    $scope.procesar = function(load){
      var loader = load || false;
      if(!$scope.formEntrada.$invalid){
        $scope.getPaginationServerSide(loader);
        $scope.getPaginationIAServerSide();
        $scope.getPaginationPCServerSide();
      }else{
        pinesNotifications.notify({ title: 'Warning.', text: 'Rellene los campos obligatorios.', type: 'warning', delay: 3000 });
      }
    }
    /*========================================*/
    $scope.btnNuevaEntrada = function(size) { 
      blockUI.start('Ejecutando proceso...');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'AlmacenFarmacia/ver_popup_entrada',
        size: size || 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, blockUI) { 
          blockUI.stop();
          $scope.isRegisterSuccess = false;
          //$scope.fDataEntrada = {};
          $scope.fDataEntrada.temporal = {};
          $scope.fDataEntrada.fecha_entrada = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataEntrada.fecha_compra = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataEntrada.fecha_vence_factura = $filter('date')(new Date(),'dd-MM-yyyy');
          //$scope.fDataEntrada.temporal.fecha_vencimiento = $filter('date')(new Date(),'dd-MM-yyyy');
          // $scope.fDataEntrada.temporal.fecha_vencimiento = '';
          // LISTA MODO DE MEDIDA
          $scope.listadoCajaUnidad = [
            {id: 'CAJA', descripcion: 'CAJA'},
            {id: 'UNIDAD', descripcion: 'UNIDAD'}
          ]
          $scope.fDataEntrada.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
          $scope.fDataEntrada.temporal.lote = null;
          $scope.fDataEntrada.temporal.cantidad = 1;
          
          $scope.fDataEntrada.ruc = null;
          $scope.fDataEntrada.proveedor = {};
          $scope.fDataEntrada.factura = null;
          $scope.fDataEntrada.guia_remision = null;
          $scope.fDataEntrada.motivo_movimiento = null;
          $scope.fDataEntrada.total = null;
          $scope.fDataEntrada.igv = null;
          $scope.fDataEntrada.subtotal = null;
          $scope.fDataEntrada.temporal.descuento = 0;
          $scope.fDataEntrada.modo_igv = 2;
          $scope.fDataEntrada.compratemp = true ;
          $scope.fDataEntrada.temporal.acepta_caja_unidad = true;
          // if($scope.fSessionCI.key_group === 'key_caja_far'){
          //   $scope.fDataEntrada.estemporal = true ;
          // }else{
          //   $scope.fDataEntrada.estemporal = false ;
          // }

          $scope.fDataEntrada.almacen = $scope.listaAlmacenes[0];
          if($scope.fDataEntrada.idtipoentrada == 2){
            $scope.titleForm = 'Registro de Compra';
          }else if($scope.fDataEntrada.idtipoentrada == 4){
            $scope.titleForm = 'Registro de Regalo de Productos';
            $scope.fDataEntrada.temporal.precio = 0;
          }else if($scope.fDataEntrada.idtipoentrada == 6){
            $scope.titleForm = 'Reingreso de Productos';
            $scope.fDataEntrada.temporal.precio = 0;
          }else{
            $scope.titleForm = 'Formato General';
          }
          $scope.mySelectionGrid = [];
          $scope.gridOptions = {
            paginationPageSize: 10,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            data: null,
            rowHeight: 26,
            enableCellEditOnFocus: true,
            multiSelect: false,
            columnDefs: [
              { field: 'id', displayName: 'COD.', width: '5%', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCION', enableCellEdit: false, enableSorting: false,
                cellTooltip: 
                  function( row, col ) {
                    return row.entity.descripcion;
                  }
              },
              { field: 'caja_unidad', displayName: 'CAJA/UND.', width: '8%', enableCellEdit: false, enableSorting: false },
              { field: 'fecha_vencimiento', displayName: 'FECHA VENC.', width: '8%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center'},
              { field: 'lote', displayName: 'LOTE', width: '5%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center' },
              { field: 'cantidad', displayName: 'CANT.', width: '5%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center' },
              { field: 'cant_ingr_de_total', displayName: 'CANT. INGR.', width: 80, enableCellEdit: false, enableSorting: false, cellClass:'text-center' },
              
              { field: 'precio', displayName: 'P. UNIT', width: '6%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-right' },
              { field: 'descuento', displayName: 'DCTO(%)', width: '6%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center' },
              { field: 'descuento_valor', displayName: 'DCTO. VALOR ', width: '9%', enableCellEdit: false, enableSorting: false, cellClass:'text-right',
                /*cellTemplate: function(row, col){
                  return '<span>' + row.entity.descuento_valor + ' (' + row.entity.descuento +')</span>';
                } */ },
              { field: 'igv', displayName: 'IGV', width: 60, enableCellEdit: false, enableSorting: false, cellClass:'text-right' },
              { field: 'importe', displayName: 'IMP.+IGV', width: 80, enableCellEdit: false, enableSorting: false, cellClass:'text-right' },
              { field: 'excluye_igv', displayName: 'INAFECTO', width: '6%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
              },
              { field: 'numero_lotes', displayName: 'N°LOTES', width:70, enableSorting: false, editableCellTemplate: 'ui-grid/dropdownEditor', cellFilter: 'mapNumero', 
                cellClass:'ui-editCell text-center', editDropdownValueLabel: 'number', editDropdownOptionsArray: [ 
                  { id: '1', number: '1' },
                  { id: '2', number: '2' },
                  { id: '3', number: '3' },
                  { id: '4', number: '4' },
                  { id: '5', number: '5' }
                ],

              },
              { field: 'accion', displayName: '', width: 30, enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' },

            ]
            ,onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              // $scope.gridApi.selection.on.rowSelectionChanged($scope, function(rowEntity, colDef) {
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                
                rowEntity.column = colDef.field;
                //console.log(rowEntity.column);

                if(rowEntity.column == 'cantidad'){
                  //console.log(rowEntity.cantidad);
                  //console.log(oldValue);
                  if( !(rowEntity.cantidad >= 1) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'danger';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                    return false;
                  }
                  
                }
                else if(rowEntity.column == 'descuento'){
                  if( !(rowEntity.descuento >= 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.descuento = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El descuento debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                else if(rowEntity.column == 'precio'){
                  if( !(rowEntity.precio >= 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.precio = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El precio debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                else if(rowEntity.column == 'numero_lotes'){
                  if( rowEntity.numero_lotes >= 1 && !rowEntity.boolHijoLote){
                    console.log('key', rowEntity.$$hashKey);
                    console.log('Se multiplicara por ',rowEntity.numero_lotes);
                    var arrParaEliminar = [];
                    angular.forEach($scope.gridOptions.data, function(value, key) {
                      if(value.iddetallemovimiento == rowEntity.iddetallemovimiento && value.boolHijoLote){ 
                        console.log('indice...', key);
                        console.log('fila...', value);
                        //var index = $scope.gridOptions.data.indexOf(row.entity); 
                        //$scope.gridOptions.data.splice(key,1);
                        arrParaEliminar.push(value.$$hashKey);

                      }
                    });
                    console.log('arrParaEliminar', arrParaEliminar);
                    angular.forEach(arrParaEliminar, function(value, key) {
                      var index = $scope.gridOptions.data.indexOf(value);
                      //console.log('eliminando...', key);
                      $scope.gridOptions.data.splice(index,1);
                    });
                    var i=1;
                    while(i<rowEntity.numero_lotes){
                      var arrTemporal = { 
                        'id' : rowEntity.id,
                        'iddetallemovimiento' : rowEntity.iddetallemovimiento,
                        'idmedicamento' : rowEntity.idmedicamento,
                        'descripcion' : rowEntity.descripcion,
                        'unidad_medida' : rowEntity.unidad_medida,
                        'cantidad' : '0',
                        'precio' : rowEntity.precio,
                        'valor' : rowEntity.valor,
                        'descuento' : rowEntity.descuento,
                        'descuento_valor' : rowEntity.descuento_valor,
                        'importe_sin' : '0',
                        'igv' : '0',
                        'importe' : '0',
                        'excluye_igv' : rowEntity.excluye_igv,
                        'fecha_vencimiento' : rowEntity.fecha_vencimiento,
                        'lote' : rowEntity.lote,
                        'caja_unidad' : rowEntity.caja_unidad,
                        'contenido' : rowEntity.contenido,
                        'boolHijoLote' : true,
                      };
                      $scope.gridOptions.data.push(arrTemporal);
                      i++;
                    }
                    //return false;
                  }else{
                    rowEntity.numero_lotes = oldValue;
                  }
                }
                // rowEntity.descuento_valor = parseFloat(rowEntity.precio) * rowEntity.cantidad * rowEntity.descuento / 100;
                // rowEntity.importe_sin = (parseFloat(rowEntity.precio) * parseFloat(rowEntity.cantidad)* (1 - rowEntity.descuento / 100)).toFixed(2);
                // if(rowEntity.excluye_igv == 1){
                //  rowEntity.igv = 0.00;
                //  rowEntity.importe =rowEntity.importe_sin;
                // }else{
                //  rowEntity.igv = (0.18 *rowEntity.importe_sin).toFixed(2);
                //  rowEntity.importe = (1.18 *rowEntity.importe_sin).toFixed(2);
                // }
                rowEntity.valor = parseFloat(rowEntity.precio) * parseFloat(rowEntity.cantidad);
                rowEntity.descuento_valor = rowEntity.valor * rowEntity.descuento / 100;
                if( $scope.fDataEntrada.modo_igv == 1 ){
                  console.log('Calculando Modo sin IGV');
                  rowEntity.importe_sin = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                   rowEntity.igv = 0.00;
                  }else{
                   rowEntity.igv = (0.18 * rowEntity.importe_sin).toFixed(2);
                  }
                  rowEntity.importe = (parseFloat(rowEntity.importe_sin) + parseFloat(rowEntity.igv)).toFixed(2);
                }else{
                  // console.log('Calculando Modo con IGV');
                  console.log(rowEntity,'rowEntity');
                  rowEntity.importe = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                    console.log('excluye_igv = 1',rowEntity.importe,'rowEntity.importe');
                    rowEntity.importe_sin = (parseFloat(rowEntity.importe)).toFixed(2);
                    // rowEntity.importe_sin = (rowEntity.importe).toFixed(2);
                    rowEntity.igv = 0.00;
                  }else{ 
                    console.log('excluye_igv = 2'); 
                    rowEntity.importe_sin = (rowEntity.importe / 1.18).toFixed(2);
                    rowEntity.igv = (0.18 * rowEntity.importe_sin).toFixed(2);
                  }
                }
                $scope.calcularTotales();
                $scope.$apply();
              });
            }
          };
          $scope.getTableHeight = function() {
             var rowHeight = 26; // your row height 
             var headerHeight = 25; // your header height 
             return {
                // height: ($scope.gridOptions.data.length * rowHeight + headerHeight + 40) + "px"
                height: (6 * rowHeight + headerHeight + 20) + "px"
             };
          };
          $scope.fDataEntrada.forma_pago = 1;
          $scope.fDataEntrada.moneda = 1;
          // LISTAR ORDENES DE COMPRA
          $scope.cargarOrdenCbo = function(){
            ordenCompraServices.sListarOrdenCompraPorAlmacenCbo($scope.fDataEntrada).then(function (rpta) { //console.log(rpta);
              $scope.listaOrdenes = rpta.datos;
              $scope.listaOrdenes.splice(0,0,{id : '0', descripcion : '--Seleccione una Orden--'});
              $scope.fDataEntrada.orden_compra = $scope.listaOrdenes[0];
            });
            $scope.fDataEntrada.ruc = null;
            $scope.fDataEntrada.proveedor = null;
            $scope.gridOptions.data = [];
          }
          $scope.cargarOrdenCbo();
          $scope.cambiarModo = function(){
            if( $scope.fDataEntrada.modo_igv == 1){
              console.log('Modo sin IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe_sin = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor) );
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].igv = 0.00;
                }else{
                  $scope.gridOptions.data[key].igv = redondear( parseFloat($scope.gridOptions.data[key].importe_sin) * 0.18 );
                }
                $scope.gridOptions.data[key].importe = ( parseFloat($scope.gridOptions.data[key].importe_sin) + parseFloat($scope.gridOptions.data[key].igv) );
                
              });
            }else{
              console.log('Modo con IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor) );
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].importe_sin = parseFloat($scope.gridOptions.data[key].importe);
                  $scope.gridOptions.data[key].igv = 0.00;
                } else{
                  $scope.gridOptions.data[key].importe_sin = redondear( parseFloat($scope.gridOptions.data[key].importe) / 1.18 );
                  // $scope.gridOptions.data[key].igv = ( parseFloat($scope.gridOptions.data[key].importe_sin) * 0.18 );
                  $scope.gridOptions.data[key].igv = $scope.gridOptions.data[key].importe - $scope.gridOptions.data[key].importe_sin;
                }
                
              });
            }
            $scope.calcularTotales();
          };
          $scope.calcularImporte = function (){
            if( $scope.fDataEntrada.temporal.producto != null && $scope.fDataEntrada.temporal.precio != null ){
              console.log('Calculando importe... ');
              $scope.fDataEntrada.temporal.valor = parseFloat($scope.fDataEntrada.temporal.precio) * $scope.fDataEntrada.temporal.cantidad;
              $scope.fDataEntrada.temporal.descuento_valor = ($scope.fDataEntrada.temporal.valor * $scope.fDataEntrada.temporal.descuento / 100);

              if( $scope.fDataEntrada.modo_igv == 1 ){ // PRECIOS SIN IGV
                $scope.fDataEntrada.temporal.importe_sin = ($scope.fDataEntrada.temporal.valor - $scope.fDataEntrada.temporal.descuento_valor);
                if($scope.fDataEntrada.temporal.producto.excluye_igv == 1){
                  $scope.fDataEntrada.temporal.igv = 0.00;
                }else{
                  $scope.fDataEntrada.temporal.igv = redondear($scope.fDataEntrada.temporal.importe_sin * 0.18);
                }
                $scope.fDataEntrada.temporal.importe = (parseFloat($scope.fDataEntrada.temporal.importe_sin) + parseFloat($scope.fDataEntrada.temporal.igv));
              }else{  // PRECIOS CON IGV
                $scope.fDataEntrada.temporal.importe = (parseFloat($scope.fDataEntrada.temporal.valor) - parseFloat($scope.fDataEntrada.temporal.descuento_valor));
                if($scope.fDataEntrada.temporal.producto.excluye_igv == 1){
                  $scope.fDataEntrada.temporal.importe_sin = parseFloat($scope.fDataEntrada.temporal.importe);
                  $scope.fDataEntrada.temporal.igv = 0.00;
                }else{
                  $scope.fDataEntrada.temporal.importe_sin = redondear($scope.fDataEntrada.temporal.importe / 1.18);
                  $scope.fDataEntrada.temporal.igv = $scope.fDataEntrada.temporal.importe - $scope.fDataEntrada.temporal.importe_sin;
                  // $scope.fDataEntrada.temporal.igv = ($scope.fDataEntrada.temporal.importe_sin * 0.18);
                  console.log('importe_sin ', $scope.fDataEntrada.temporal.importe_sin);
                  console.log('igv ', $scope.fDataEntrada.temporal.igv);
                }
              }
            }
          }
          $scope.calcularTotales = function () { 
            var subtotal = 0;
            var igv = 0;
            var total = 0;
            var totalSoloAfecto = 0; 
            console.log($scope.gridOptions.data,'$scope.gridOptions.data'); 
            if($scope.gridOptions.data.length >= 1 ){ 
              angular.forEach($scope.gridOptions.data,function (value, key) { 
                total += parseFloat($scope.gridOptions.data[key].importe);
                if( !(value.excluye_igv == 1) ){ // NO(SI)
                  totalSoloAfecto += parseFloat($scope.gridOptions.data[key].importe); 
                }
                
              });
            }
            // $scope.fDataEntrada.subtotal_temp = (total / 1.18);
            // $scope.fDataEntrada.subtotal = redondear($scope.fDataEntrada.subtotal_temp).toFixed(2);
            // $scope.fDataEntrada.igv = redondear($scope.fDataEntrada.subtotal_temp * 0.18).toFixed(2);
            //Calculamos el IGV 
            $scope.fDataEntrada.igv = ( totalSoloAfecto - (redondear(totalSoloAfecto / 1.18)) ).toFixed(2); 
            $scope.fDataEntrada.total = redondear(total).toFixed(2); 
            $scope.fDataEntrada.subtotal = (redondear(total) - (totalSoloAfecto - (redondear(totalSoloAfecto / 1.18))) ).toFixed(2); 
          } 
          $scope.getProductoAutocomplete = function (value) { 
            var params = {
              searchText: value, 
              searchColumn: "(COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,''))",
              //especialidadId: $scope.fDataEntrada.temporal.especialidad.idespecialidad,
              sensor: false
            }
            return medicamentoServices.sListarMedicamentosAutoCompleteParaFarmacia(params).then(function(rpta) { 
              $scope.noResultsLPSC = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLPSC = true;
              }
              return rpta.datos; 
            });
          }
          $scope.ultimoPrecioCompra = function(idmedicamento){
            var paramDatos = {
              idmedicamento: idmedicamento,
              caja_unidad: $scope.fDataEntrada.temporal.caja_unidad,
              idalmacen: $scope.fDataEntrada.almacen.id
            }
            ordenCompraServices.sListarUltimoPrecioMedicamento(paramDatos).then(function(rpta) {
              //console.log('Datos: ',rpta.datos);
              $scope.fDataEntrada.temporal.precio = rpta.datos;
              setTimeout(function() {
                $('#temporalPrecio').focus();
              },500);
              //$scope.fDataEntrada.temporal.importe = parseFloat($scope.fDataEntrada.temporal.precio) * $scope.fDataEntrada.temporal.cantidad * (1 - $scope.fDataEntrada.temporal.descuento / 100);
              if($scope.fDataEntrada.temporal.precio != null){
                $scope.calcularImporte();
              }
            });
          }
          $scope.getSelectedProducto = function (item, model) {
            if(model.acepta_caja_unidad == '2'){
              console.log('no acepta');
              $scope.fDataEntrada.temporal.acepta_caja_unidad = false;
              $scope.fDataEntrada.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
            }else{
              console.log('si acepta');
              $scope.fDataEntrada.temporal.acepta_caja_unidad = true;
              $scope.fDataEntrada.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
            }

            if( $scope.fDataEntrada.idtipoentrada == 2 ){
              $scope.ultimoPrecioCompra(model.id);
            }else{
              $scope.fDataEntrada.temporal.importe_sin = 0;
              $scope.fDataEntrada.temporal.descuento = 0;
              $scope.fDataEntrada.temporal.precio = 0;
              $scope.calcularImporte();
              setTimeout(function() {
                $('#temporalFechaVencimiento').focus();
              },500);
            }
          }
          $scope.btnBuscarProveedor = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'ProveedorFarmacia/ver_popup_busqueda_proveedor',
              size: size || '',
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                $scope.titleForm = 'Búsqueda de Proveedores';
                var paginationOptionsProveedor = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.mySelectionProveedorGrid = [];
                
                $scope.gridOptionsProveedorBusqueda = {
                  rowHeight: 36,
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  enableGridMenu: false,
                  enableRowSelection: false,
                  enableSelectAll: true,
                  enableFiltering: true,
                  // enableRowHeaderSelection: false, // fila cabecera 
                  enableFullRowSelection: true,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'id', name: 'idproveedor', displayName: 'ID', width: '10%',  sort: { direction: uiGridConstants.ASC} },
                    { field: 'ruc', name: 'ruc', displayName: 'RUC.', width: '15%' },
                    { field: 'razon_social', name: 'razon_social', displayName: 'Razón Social', width: '40%' },
                    { field: 'direccion_fiscal', name: 'direccion_fiscal', displayName: 'Dirección', width: '30%' },
                  ],
                  onRegisterApi: function(gridApi) { // gridComboOptions
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionProveedorGrid = gridApi.selection.getSelectedRows();
                      $scope.fDataEntrada.proveedor = $scope.mySelectionProveedorGrid[0]; //console.log($scope.fDataEntrada.Proveedor);
                      $scope.fDataEntrada.ruc = $scope.mySelectionProveedorGrid[0].ruc;
                      $modalInstance.dismiss('cancel');
                      setTimeout(function() {
                        $('#temporalProducto').focus(); //console.log('focus me',$('#temporalProducto'));
                      }, 1000);
                      
                    });

                    $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                      if (sortColumns.length == 0) {
                        paginationOptionsProveedor.sort = null;
                        paginationOptionsProveedor.sortName = null;
                      } else {
                        paginationOptionsProveedor.sort = sortColumns[0].sort.direction;
                        paginationOptionsProveedor.sortName = sortColumns[0].name;
                      }
                      $scope.getPaginationProveedorSide();
                    });
                    gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                      paginationOptionsProveedor.pageNumber = newPage;
                      paginationOptionsProveedor.pageSize = pageSize;
                      paginationOptionsProveedor.firstRow = (paginationOptionsProveedor.pageNumber - 1) * paginationOptionsProveedor.pageSize;
                      $scope.getPaginationProveedorSide();
                    });
                    $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                      var grid = this.grid;
                      paginationOptionsProveedor.search = true;
                      // console.log(grid.columns);
                      // console.log(grid.columns[1].filters[0].term);
                      paginationOptionsProveedor.searchColumn = { 
                        'p.idproveedor' : grid.columns[1].filters[0].term,
                        'ruc' : grid.columns[2].filters[0].term,
                        'razon_social' : grid.columns[3].filters[0].term,
                        'direccion_fiscal' : grid.columns[4].filters[0].term
                      }
                      $scope.getPaginationProveedorSide();
                    });
                  }
                };
                // $scope.navegateToCellListaBusquedaProveedor = function( rowIndex, colIndex ) { 
                //   console.log(rowIndex, colIndex);
                //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsProveedorBusqueda.data[rowIndex], $scope.gridOptionsProveedorBusqueda.columnDefs[colIndex]); 
                  
                // };
                paginationOptionsProveedor.sortName = $scope.gridOptionsProveedorBusqueda.columnDefs[0].name;
                $scope.getPaginationProveedorSide = function() {
                  //$scope.$parent.blockUI.start();
                  $scope.datosGrid = {
                    paginate : paginationOptionsProveedor
                  };
                  proveedorFarmaciaServices.sListarProveedorFarmacia($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsProveedorBusqueda.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsProveedorBusqueda.data = rpta.datos;
                     
                    //$scope.$parent.blockUI.stop();
                  });
                  $scope.mySelectionProveedorGrid = [];
                };
                $scope.getPaginationProveedorSide();

                // shortcut.add("down",function() { 

                //   $scope.navegateToCellListaBusquedaProveedor(0,0);
                // });
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
              }
            });
          }
          $scope.obtenerDatosProveedor = function () { 
            if( $scope.fDataEntrada.ruc ){ 
              proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fDataEntrada).then(function (rpta) { 
                $scope.fDataEntrada.proveedor = rpta.datos;
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al proveedor en el sistema.', type: 'success', delay: 2000 });
                }else{
                  $scope.btnNuevo("",$scope.fDataEntrada);
                }
              });
            }
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            var index = $scope.gridOptions.data.indexOf(row.entity); 
            $scope.gridOptions.data.splice(index,1);
            $scope.calcularTotales(); 
            // $scope.calcularVuelto(); 
          }
          $scope.limpiarCampos = function (){
            $scope.fDataEntrada.proveedor = {};
          }
          $scope.cargarDetalle = function(){
            $scope.gridOptions.data = [];
            if($scope.fDataEntrada.orden_compra.id != 0){
              console.log('Cargando detalle...');
              console.log($scope.fDataEntrada.orden_compra);
              var arrParams = {
                datos: $scope.fDataEntrada.orden_compra
              };
              ordenCompraServices.sListarDetalleOrdenCbo(arrParams).then(function (rpta) {
                $scope.gridOptions.data = rpta.datos;
                $scope.fDataEntrada.motivo_movimiento = rpta.datos[0].motivo_movimiento;
                $scope.fDataEntrada.proveedor = rpta.proveedor;
                $scope.fDataEntrada.ruc = $scope.fDataEntrada.proveedor.ruc;
                $scope.fDataEntrada.forma_pago =  $scope.fDataEntrada.orden_compra.forma_pago;
                $scope.fDataEntrada.moneda =  $scope.fDataEntrada.orden_compra.moneda;
                $scope.fDataEntrada.letras =  $scope.fDataEntrada.orden_compra.letras;
                $scope.fDataEntrada.modo_igv = $scope.fDataEntrada.orden_compra.modo_igv;
                $scope.calcularTotales();
              });
              $('#factura').focus();
            }else{
              console.log('No ha seleccionado una orden...');
              $scope.fDataEntrada.proveedor = null;
              $scope.fDataEntrada.ruc = null;
              $scope.fDataEntrada.total = null;
              $scope.fDataEntrada.igv = null;
              $scope.fDataEntrada.subtotal = null;
              $scope.fDataEntrada.motivo_movimiento = null;
            }
          }
          $scope.agregarItem = function () {
            $('#temporalProducto').focus();

            if( !angular.isObject($scope.fDataEntrada.temporal.producto) ){ 
              $scope.fDataEntrada.temporal.producto = null;
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
              return false;
            }
            if( $scope.fDataEntrada.idtipoentrada == 2 ){ // console.log('especialidad');
              if( !($scope.fDataEntrada.temporal.precio > 0) ){
                $scope.fDataEntrada.temporal.precio = null;
                $('#temporalPrecio').focus();
                pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un precio válido', type: 'warning', delay: 2000 });
                return false;
              }
              
            }
            //console.log($scope.fDataEntrada,$scope.fDataEntrada.temporal.cantidad);
            if( !($scope.fDataEntrada.temporal.cantidad >= 1) ){ // console.log('especialidad');
              $scope.fDataEntrada.temporal.cantidad = null;
              $('#temporalCantidad').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado una cantidad correcta', type: 'warning', delay: 2000 });
              return false;
            }
            if( $scope.fDataEntrada.temporal.fecha_vencimiento == null || $scope.fDataEntrada.temporal.fecha_vencimiento == '' || $scope.fDataEntrada.temporal.fecha_vencimiento == undefined){ // console.log('especialidad');
              $('#temporalFechaVencimiento').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese Fecha de Vencimiento', type: 'warning', delay: 2000 });
              return false;
            }

            var productNew = true;
            angular.forEach($scope.gridOptions.data, function(value, key) { 
              if(value.id == $scope.fDataEntrada.temporal.producto.id ){ 
                productNew = false;
              }
            });
            if( productNew === false ){
              $scope.fDataEntrada.temporal.producto= null;
              $scope.fDataEntrada.temporal.cantidad= 1;
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              return false;
            } 
            
            $scope.arrTemporal = { 
              'id' : $scope.fDataEntrada.temporal.producto.id,
              'idmedicamento' : $scope.fDataEntrada.temporal.producto.id,
              'descripcion' : $scope.fDataEntrada.temporal.producto.medicamento,
              'unidad_medida' : $scope.fDataEntrada.temporal.producto.idunidadmedida,
              'cantidad' : $scope.fDataEntrada.temporal.cantidad,
              'precio' : $scope.fDataEntrada.temporal.precio,
              'valor' : $scope.fDataEntrada.temporal.valor,
              'descuento' : $scope.fDataEntrada.temporal.descuento,
              'descuento_valor' : $scope.fDataEntrada.temporal.descuento_valor,
              'importe_sin' : $scope.fDataEntrada.temporal.importe_sin,
              'igv' : $scope.fDataEntrada.temporal.igv,
              'importe' : $scope.fDataEntrada.temporal.importe,
              'excluye_igv' : $scope.fDataEntrada.temporal.producto.excluye_igv,
              'fecha_vencimiento' : $scope.fDataEntrada.temporal.fecha_vencimiento,
              'lote' : $scope.fDataEntrada.temporal.lote,
              'caja_unidad' : $scope.fDataEntrada.temporal.caja_unidad,
              'contenido' : $scope.fDataEntrada.temporal.producto.contenido
            };
            $scope.gridOptions.data.push($scope.arrTemporal);

            $scope.calcularTotales(); 
            // $scope.calcularVuelto(); 
            $scope.fDataEntrada.temporal = {
              cantidad: 1,
              descuento: 0,
              importe: null,
              producto: null,
              caja_unidad: $scope.listadoCajaUnidad[0].id
            };
          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function(){
            //console.log($scope.fDataEntrada);
            $scope.fDataEntrada.detalle = $scope.gridOptions.data;
            if($scope.isRegisterSuccess){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La compra ya fue registrada', type: 'warning', delay: 3000 });
              return false;
            }
            if( $scope.fDataEntrada.idtipoentrada == 2 ){ // compra
              if( $scope.fDataEntrada.proveedor == null ){
                $scope.fDataEntrada.ruc = null;
                $('#ruc').focus();
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado un proveedor', type: 'warning', delay: 2000 });
                return false;
              } 
              if( $scope.fDataEntrada.proveedor.razon_social == '' || $scope.fDataEntrada.proveedor.razon_social == null || $scope.fDataEntrada.proveedor.razon_social == undefined ){
                $scope.fDataEntrada.ruc = null;
                $('#ruc').focus();
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado un proveedor válido', type: 'warning', delay: 2000 });
                return false;
              }
              if( $scope.fDataEntrada.factura == '' || $scope.fDataEntrada.factura == null || $scope.fDataEntrada.factura == undefined ){
                $scope.fDataEntrada.factura = null;
                $('#factura').focus();
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado el número de la factura', type: 'warning', delay: 2000 });
                return false;
              }

            }
            else if( $scope.fDataEntrada.idtipoentrada == 6 ){ // reingreso
              $scope.fDataEntrada.proveedor == {}
            }
            if( $scope.fDataEntrada.detalle.length < 1 ){ 
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún producto/medicamento', type: 'warning', delay: 2000 }); 
              return false; 
            }         

            // if($scope.fDataEntrada.estemporal){   //  ES TEMPORAL 
            //   $scope.fDataEntrada.productos = $scope.gridOptions.data;
            //   cajaTemporalFarmServices.sRegistrarMovimientoTemporal($scope.fDataEntrada).then(function (rpta) { 
            //     if(rpta.flag == 1){
            //       var pTitle = 'OK!';
            //       var pType = 'success';
            //       $scope.isRegisterSuccess = true;
            //       //$scope.fDataEntrada = {};
            //       $scope.idmovimiento = rpta.idmovimiento;
            //       $scope.getPaginationServerSide();
            //       //$modalInstance.dismiss('cancel');
            //     }else if(rpta.flag == 0){
            //       var pTitle = 'Advertencia!';
            //       var pType = 'danger';
            //     }else{
            //       alert('Algo salió mal...');
            //     }
            //     pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            //   }); 

            // }else{    // NO ES TEMPORAL
            blockUI.start('Registrando...');
            entradasFarmServices.sRegistrarEntrada($scope.fDataEntrada).then(function (rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                //$scope.fDataEntrada = {};
                $scope.isRegisterSuccess = true;
                $scope.idmovimiento = rpta.idmovimiento;
                $scope.getPaginationServerSide();
                $scope.getPaginationPCServerSide();
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'danger';
                //$scope.fDataEntrada.fecha_vencimiento = null;
              }else{
                alert('Algo salió mal...');
              }
              blockUI.stop();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            }); 
            
          }
         
        }
      })  
    }
    $scope.btnAnularEntrada = function() {
      var pMensaje = '¿Realmente desea anular la entrada?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          entradasFarmServices.sAnularEntrada($scope.mySelectionGridIngr).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationIAServerSide();
                $scope.getPaginationPCServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }
    $scope.btnImprimir = function(idmovimiento, estado){
      // console.log('movimiento: ', idmovimiento);
      // console.log('estado: ', estado);
      var abreviatura = 'COMP';
      //var estado = 0;
      // if( estado == 0 ){
      //   abreviatura = 'ANLDO';
      // }
      var arrParams = {
          titulo: 'REPORTE DE INGRESO AL ALMACEN',
          datos:{
            resultado: idmovimiento,
            salida: 'pdf',
            tituloAbv: abreviatura,
            estado: estado,
            empresa: $scope.fBusqueda.almacen,
            titulo: 'REPORTE DE INGRESO AL ALMACEN'
          },
          metodo: 'php'
      }
      //console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_compra',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    
    $scope.btnVerDetalleEntrada = function (fEntrada,size) { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'EntradasFarm/ver_popup_detalle_entrada',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          if( fEntrada.tipo_entrada.bool == '2' ){
            $scope.titleForm = 'Detalle de la Compra';
          }else if( fEntrada.tipo_entrada.bool == '4' ){
            $scope.titleForm = 'Detalle del Regalo';
          }else if( fEntrada.tipo_entrada.bool == '6' ){
            $scope.titleForm = 'Detalle del Reingreso';
          }
          
          $scope.fEntrada = fEntrada;
          $scope.gridOptionsDetalleEntrada = {
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
              { field: 'cantidadf', name: 'cantidad', displayName: 'CANT.', width: '8%'},
              { field: 'unidad_medida', name: 'unidad_medida', displayName: 'UNI. MED.', width: '8%' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT.', width: '12%' },
              
              { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '13%', cellClass: 'bg-lightblue' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };

          $scope.getPaginationDetalleEntradaServerSide = function() {
            var arrParams = {
              datos: fEntrada
            };
            entradasFarmServices.sListarDetalleEntrada(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleEntrada.data = rpta.datos;
              $scope.gridOptionsDetalleEntrada.sumTotal = rpta.sumTotal;

              $scope.fEntrada.detalle = rpta.datos;
            });
          };
          $scope.getPaginationDetalleEntradaServerSide();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }

          /* PARA ELIMINAR */
          $scope.btnImprimir2 = function(){
            console.log('fEntrada: ',$scope.fEntrada);
            $scope.fDataImprimir = angular.copy($scope.fEntrada);
            $scope.fDataImprimir.fecha_movimiento = $scope.fDataImprimir.fmovimiento;
            console.log('almacen: ', $scope.fBusqueda.almacen);
            $scope.fDataImprimir.almacen = {
              'descripcion' : $scope.fBusqueda.almacen.descripcion
            }
            $scope.fDataImprimir.orden_compra = {
              'descripcion' :$scope.fDataImprimir.orden_compra
            }
            var abreviatura = 'COMP';
            var estado = 1;
            if( $scope.fDataImprimir.estado.labelText == 'ANULADO' ){
              abreviatura = 'ANLDO';
              estado = 0;
            }
            
            var arrParams = {
              titulo: 'REPORTE DE INGRESO AL ALMACEN',
              datos:{
                resultado: $scope.fDataImprimir,
                salida: 'pdf',
                tituloAbv: abreviatura,
                estado: estado,
                titulo: 'REPORTE DE INGRESO AL ALMACEN'
              },
              metodo: 'php'
            }
            arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_compra', 
            ModalReporteFactory.getPopupReporte(arrParams);
          }
          /* END PARA ELIMINAR */
        }
      });
    }

    /************** GRID DE INGRESOS ANULADOS **************/
    var paginationOptionsIA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsIngresosAnulados = {
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
        { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID', width: '6%', visible: false },
        { field: 'factura', name: 'factura', displayName: 'FACTURA', width: '10%',},
        { field: 'guia_remision', name: 'guia_remision', displayName: 'GUIA DE REMISION', width: '10%' },
        { field: 'razon_social', name: 'razon_social', displayName: 'PROVEEDOR' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE INGRESO', width: '12%', enableFiltering: false,sort: { direction: uiGridConstants.DESC}  },
       
        // { field: 'ruc', name: 'ruc', displayName: 'RUC', width: '18%' },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUB TOTAL', width: '9%', enableFiltering: false, },
        { field: 'igv', name: 'igv', displayName: 'IGV', width: '9%', enableFiltering: false, },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '9%', enableFiltering: false,},
        { field: 'tipo_entrada', type: 'object', name: 'tipo_movimiento', displayName: 'TIPO DE INGRESO', width: '10%', enableFiltering: false,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' },
        { 
          field: 'estado', 
          displayName: 'ESTADO', 
          width: '5%', 
          cellTemplate:'<label class="label label-danger" style="margin: 7px;opacity: 0.5;"> <i class="fa fa-ban"></i> </label>', 
          cellClass:'text-center',
          enableColumnMenus: false,
          enableColumnMenu: false,
          enableSorting: false,
          enableFiltering: false
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiAnulado = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridIA = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridIA = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiAnulado.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsIA.sort = null;
            paginationOptionsIA.sortName = null;
          } else {
            paginationOptionsIA.sort = sortColumns[0].sort.direction;
            paginationOptionsIA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationIAServerSide();
        });
        $scope.gridApiAnulado.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsIA.search = true; 
          paginationOptionsIA.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            //'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[6].filters[0].term,
            'sub_total' : grid.columns[7].filters[0].term,
            'total_igv' : grid.columns[8].filters[0].term,
            'total_a_pagar' : grid.columns[9].filters[0].term
          }
          $scope.getPaginationIAServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsIA.pageNumber = newPage;
          paginationOptionsIA.pageSize = pageSize;
          paginationOptionsIA.firstRow = (paginationOptionsIA.pageNumber - 1) * paginationOptionsIA.pageSize;
          $scope.getPaginationIAServerSide();
        });
      }
    };
    paginationOptionsIA.sortName = $scope.gridOptionsIngresosAnulados.columnDefs[4].name;
    $scope.getPaginationIAServerSide = function() { 
      var arrParams = {
        paginate : paginationOptionsIA,
        datos : $scope.fBusqueda
      };
      entradasFarmServices.sListarIngresosAnulados(arrParams).then(function (rpta) {
        $scope.gridOptionsIngresosAnulados.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsIngresosAnulados.data = rpta.datos;
        $scope.gridOptionsIngresosAnulados.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGridIA = [];
    };
    

    /************** GRID DE PRODUCTOS DE LA COMPRA **************/
    $scope.mySelectionGridPC = [];
    var paginationOptionsPC = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsProductosCompra = { 
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
        { field: 'factura', name: 'ticket_venta', displayName: 'FACTURA', width: '9%' },
        { field: 'proveedor', name: 'razon_social', displayName: 'PROVEEDOR', width: '16%'},
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE COMPRA', width: '10%', enableFiltering: false,  sort: { direction: uiGridConstants.DESC} },
        { field: 'lote', name: 'num_lote', displayName: 'LOTE', width: '6%', enableFiltering: false },
        { field: 'fecha_vencimiento', name: 'fecha_vencimiento', displayName: 'FECHA DE VENC.', width: '8%', enableFiltering: false },
        { field: 'producto', name: 'denominacion', displayName: 'PRODUCTO', width: '27%' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT.', width: '8%', enableFiltering: false, },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '8%', enableFiltering: false },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '8%', enableFiltering: false }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiProducto = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridPC = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridPC = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiProducto.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsPC.sort = null;
            paginationOptionsPC.sortName = null;
          } else {
            paginationOptionsPC.sort = sortColumns[0].sort.direction;
            paginationOptionsPC.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPCServerSide();
        });
        $scope.gridApiProducto.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsPC.search = true; 
          paginationOptionsPC.searchColumn = { 
            'ticket_venta' : grid.columns[1].filters[0].term,
            "razon_social" : grid.columns[2].filters[0].term,
            'm.denominacion' : grid.columns[6].filters[0].term,
          }
          $scope.getPaginationPCServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPC.pageNumber = newPage;
          paginationOptionsPC.pageSize = pageSize;
          paginationOptionsPC.firstRow = (paginationOptionsPC.pageNumber - 1) * paginationOptionsPC.pageSize;
          $scope.getPaginationPCServerSide();
        });
      }
    };
    paginationOptionsPC.sortName = $scope.gridOptionsProductosCompra.columnDefs[2].name;
    $scope.getPaginationPCServerSide = function() { // console.log('PV');
      $scope.datosGrid = {
        paginate : paginationOptionsPC,
        datos : $scope.fBusqueda
      };
      entradasFarmServices.sListarProductosPorCompra($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsProductosCompra.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsProductosCompra.data = rpta.datos;
      });
      $scope.mySelectionGridPC = [];
    };
  }])
  .service("entradasFarmServices",function($http, $q) {
    return({
        sListarEntradas: sListarEntradas,
        sListarIngresosAnulados: sListarIngresosAnulados,
        sListarDetalleEntrada: sListarDetalleEntrada,
        sListarProductosPorCompra : sListarProductosPorCompra,
        sRegistrarEntrada : sRegistrarEntrada,
        sAnularEntrada : sAnularEntrada,
    });

    function sListarEntradas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/lista_movimientos_entradas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarIngresosAnulados(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/lista_entradas_anuladas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleEntrada(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/lista_detalle_entrada", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosPorCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/lista_productos_entrada", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarEntrada(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/registrar_entrada", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularEntrada(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EntradasFarm/anular_entrada", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  }).filter('mapNumero', function() {
    var numberHash = { 
      '1': '1',
      '2': '2',
      '3': '3',
      '4': '4',
      '5': '5',

    };
    return function(input) {
      if (!input){
        return '';
      } else {
        return numberHash[input];
      }
    }
  });