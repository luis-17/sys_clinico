angular.module('theme.ordenCompra', ['theme.core.services'])
  .controller('ordenCompraController', ['$scope','blockUI', '$filter', '$route', '$sce', '$interval', '$uibModal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox', '$controller',
    'ordenCompraServices',
    'almacenFarmServices',
    'medicamentoAlmacenServices',
    'proveedorFarmaciaServices',
    'medicamentoServices',
    'ModalReporteFactory',
    function($scope, blockUI, $filter, $sce, $route, $interval, $uibModal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      $bootbox, $controller,
      ordenCompraServices,
      almacenFarmServices,
      medicamentoAlmacenServices,
      proveedorFarmaciaServices,
      medicamentoServices,
      ModalReporteFactory ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");
    $controller('proveedorFarmaciaController', { 
      $scope : $scope
    });
    
    $scope.fDataOC = {};
    $scope.listaEstadoOrden = [
      {'id' : 1, 'descripcion' : 'POR APROBAR'},
      {'id' : 2, 'descripcion' : 'APROBADO'}
    ];
    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/;
    $scope.pRUC = /^\d{11}$/;
    $scope.fBusqueda = {};
    $scope.modulo = 'ordenCompra'
    $scope.fBusqueda.almacen = {};
    var hoy = new Date();
    var desde = hoy - 1209600000; // restamos 14 dias
    //var desde = hoy - 1296000000; // restamos 15 dias
    $scope.fBusqueda.desde = $filter('date')(desde,'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(hoy,'dd-MM-yyyy');
    $scope.mySelectionGridOC = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsOC.enableFiltering = !$scope.gridOptionsOC.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringOCA = function(){
      $scope.gridOptionsOCAnulados.enableFiltering = !$scope.gridOptionsOCAnulados.enableFiltering;
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
      $scope.getPaginationOCAServerSide();
      $scope.getPaginationPCServerSide();
    });
    // LISTA FORMA DE PAGO
    $scope.listaFormaPago = [
      {'id' : 1, 'descripcion' : 'AL CONTADO'},
      {'id' : 2, 'descripcion' : 'CREDITO'},
      {'id' : 3, 'descripcion' : 'LETRAS'}
    ];
    $scope.listaMoneda = [
      {'id' : 1, 'descripcion' : 'S/.'},
      {'id' : 2, 'descripcion' : 'US$'}
    ];
    $scope.tienePermiso = false;
    if( $scope.fSessionCI.key_group === 'key_logistica' || $scope.fSessionCI.key_group === 'key_sistemas' ){ 
      $scope.tienePermiso = true;
    }
    $scope.tienePermisoAprobacion = false;
    if( $scope.fSessionCI.key_group === 'key_gerencia' || $scope.fSessionCI.key_group === 'key_sistemas' ){ 
      $scope.tienePermisoAprobacion = true;
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
    $scope.gridOptionsOC = { 
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
        { field: 'orden_compra', name: 'fm.orden_compra', displayName: 'Nº ORDEN', minWidth: 120 },
        { field: 'razon_social', name: 'razon_social', displayName: 'PROVEEDOR', minWidth: 280 },
        { field: 'fecha_movimiento', name: 'fm.fecha_movimiento', displayName: 'FECHA DE CREACION', enableFiltering: false, sort: { direction: uiGridConstants.DESC}, minWidth: 160, cellTooltip: 'Fecha de Creación de la Orden' },
        { field: 'fecha_aprobacion', name: 'fm.fecha_aprobacion', displayName: 'FECHA DE APROBACION', enableFiltering: false, minWidth: 160, cellTooltip: 'Fecha de Aprobación(FINANZAS)'},
        { field: 'fecha_entrega', name: 'fm.fecha_entrega', displayName: 'FECHA ING. ESTIMADA', enableFiltering: false, minWidth: 160, cellTooltip: 'Fecha de Ingreso(Estimado)'},
        { field: 'fecha_entrega_real', name: 'fmc.fecha_movimiento', displayName: 'FECHA ING. REAL', enableFiltering: false, minWidth: 160, cellTooltip: 'Última Fecha de Ingreso'},
        { field: 'subtotal', name: 'fm.sub_total', displayName: 'SUB TOTAL', enableFiltering: false, cellClass:'text-right', minWidth: 100},
        { field: 'igv', name: 'fm.total_igv', displayName: 'IGV', enableFiltering: false, cellClass:'text-right', minWidth: 100},
        { field: 'total', name: 'fm.total_a_pagar', displayName: 'TOTAL', enableFiltering: false,cellClass:'text-right', minWidth: 100},
        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, minWidth: 40, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        },
        { field: 'estado_obj', type: 'object', name: 'estado_obj', displayName: ' ', width: '6%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, minWidth: 120, 
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridOC = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridOC = gridApi.selection.getSelectedRows();
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
          paginationOptions.searchColumn = { 
            'fm.orden_compra' : grid.columns[1].filters[0].term,
            'razon_social' : grid.columns[2].filters[0].term

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
    paginationOptions.sortName = $scope.gridOptionsOC.columnDefs[2].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      ordenCompraServices.sListarOrdenesCompra(arrParams).then(function (rpta) { 
        $scope.gridOptionsOC.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsOC.data = rpta.datos;
        $scope.gridOptionsOC.sumTotal = rpta.sumTotal; 
      }); 
      $scope.mySelectionGridOC = [];
    };
    
    /*=================== BOTON PROCESAR ====================*/
    $scope.procesar = function(){
      if(!$scope.formOrdenCompra.$invalid){
        $scope.getPaginationServerSide();
        $scope.getPaginationOCAServerSide();
        $scope.getPaginationPCServerSide();
      }else{
        pinesNotifications.notify({ title: 'Warning.', text: 'Rellene los campos obligatorios.', type: 'warning', delay: 3000 });
      }
    }
    /*====================================*/
    $scope.btnNuevaOC = function(size) {
      blockUI.start('Ejecutando proceso...');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_orden_compra',
        size: size || 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, blockUI) {
          blockUI.stop();
          $scope.submodulo = 'nuevo';
          $scope.isRegisterSuccess = false;
          $scope.titleForm = 'Registro de Orden de Compra';
          $scope.fDataOC.ruc = null;
          $scope.fDataOC.proveedor = {};
          $scope.fDataOC.forma_pago = $scope.listaFormaPago[0].id;
          $scope.fDataOC.moneda = $scope.listaMoneda[0].id;
          $scope.fDataOC.estado_orden = $scope.listaEstadoOrden[0].id;
          $scope.fDataOC.almacen = $scope.listaAlmacenes[0];
          $scope.fDataOC.orden_compra = '[ ............... ]';
          $scope.fDataOC.modo_igv = 2;
          $scope.fDataOC.fecha_movimiento = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataOC.fecha_entrega = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataOC.fecha_aprobacion = null;
          $scope.cambiarFecha = function(){
            if($scope.fDataOC.estado_orden == 2){
              $scope.fDataOC.fecha_aprobacion = $filter('date')(new Date(),'dd-MM-yyyy');
            }else{
              $scope.fDataOC.fecha_aprobacion = null;
            }
          };
          $scope.fDataOC.temporal = {};
          $scope.fDataOC.temporal.lote = null;
          $scope.fDataOC.temporal.cantidad = 1;
          $scope.fDataOC.temporal.descuento = 0;
          $scope.fDataOC.temporal.producto = [];
          $scope.fDataOC.temporal.producto.excluye_igv = 2;
          $scope.fDataOC.temporal.acepta_caja_unidad = false;
          $scope.cambiarSimbolo = function(){
            angular.forEach($scope.listaMoneda, function(value, key) { 
              if(value.id == $scope.fDataOC.moneda ){ 
                $scope.fDataOC.simbolo_monetario = $scope.listaMoneda[key].descripcion;
              }
            });
          }
          $scope.cambiarSimbolo();
          // LISTA TIPO DE MATERIAL
          ordenCompraServices.sListarTipoMaterialCbo().then(function (rpta) { //console.log(rpta);
            $scope.listaTipoMaterial = rpta.datos;
            $scope.fDataOC.tipoMaterial = $scope.listaTipoMaterial[0];
            $scope.generarNumOrden();
          });
          // LISTA MODO DE MEDIDA
          $scope.listadoCajaUnidad = [
            {id: 'CAJA', descripcion: 'CAJA'},
            {id: 'UNIDAD', descripcion: 'UNIDAD'}
          ]
          $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
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
              { field: 'cantidad', displayName: 'CANT.', width: '5%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center' },
              { field: 'precio', displayName: 'P. UNIT', width: '8%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-right' },
              { field: 'valor', displayName: 'VALOR', width: '8%', enableCellEdit: false, enableSorting: false, cellClass:'text-center' },
              { field: 'descuento', displayName: 'DCTO.(%) ', width: '7%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center'},
              { field: 'descuento_valor', displayName: 'DCTO. VALOR ', width: '9%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible: false },
              { field: 'importe_sin', displayName: 'IMPORTE SIN IGV ', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible: true },
              { field: 'igv', displayName: 'IGV', width: '6%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'importe', displayName: 'IMPORTE CON IGV', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'excluye_igv', displayName: 'INAFECTO', width: '6%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
              },
              { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ]
            ,onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                
                rowEntity.column = colDef.field;
                //console.log(rowEntity.column);

                if(rowEntity.column == 'cantidad'){
                  if( !(rowEntity.cantidad >= 1) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if(rowEntity.column == 'descuento'){
                  if( !(rowEntity.descuento >= 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.descuento = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El descuento debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if(rowEntity.column == 'precio'){
                  if( !(rowEntity.precio >= 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.precio = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El Precio debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                rowEntity.valor = parseFloat(rowEntity.precio) * parseFloat(rowEntity.cantidad);
                rowEntity.descuento_valor = rowEntity.valor * rowEntity.descuento / 100;
                if( $scope.fDataOC.modo_igv == 1 ){
                  console.log('Calculando Modo sin IGV');
                  rowEntity.importe_sin = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                   rowEntity.igv = 0.00;
                  }else{
                   rowEntity.igv = (0.18 * rowEntity.importe_sin).toFixed(2);
                  }
                  rowEntity.importe = (parseFloat(rowEntity.importe_sin) + parseFloat(rowEntity.igv)).toFixed(2);
                }else{
                  console.log('Calculando Modo con IGV');
                  rowEntity.importe = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                    rowEntity.importe_sin = (rowEntity.importe).toFixed(2);
                    rowEntity.igv = 0.00;
                  }else{
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
          $scope.generarNumOrden = function () {
            //console.log($scope.fDataOC);
            ordenCompraServices.sGenerarCodigoOrden($scope.fDataOC).then(function (rpta) { 
              $scope.fDataOC.orden_compra = rpta.codigo_orden; 
            });
          };
          $scope.cambiarModo = function(){
            if( $scope.fDataOC.modo_igv == 1){
              console.log('Modo sin IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe_sin = (parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor)).toFixed(2);
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].igv = 0.00;
                }else{
                  $scope.gridOptions.data[key].igv = (parseFloat($scope.gridOptions.data[key].importe_sin)*0.18).toFixed(2);
                }
                $scope.gridOptions.data[key].importe = (parseFloat($scope.gridOptions.data[key].importe_sin) + parseFloat($scope.gridOptions.data[key].igv)).toFixed(2);
                
              });
            }else{
              console.log('Modo con IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe = (parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor)).toFixed(2);
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].importe_sin = (parseFloat($scope.gridOptions.data[key].importe)).toFixed(2);
                  $scope.gridOptions.data[key].igv = 0.00;
                } else{
                  $scope.gridOptions.data[key].importe_sin = (parseFloat($scope.gridOptions.data[key].importe) / 1.18).toFixed(2);
                  $scope.gridOptions.data[key].igv = (parseFloat($scope.gridOptions.data[key].importe_sin)*0.18).toFixed(2);
                }
                
              });
            }
            $scope.calcularTotales();
          };
          $scope.calcularImporte = function (){
            if($scope.fDataOC.temporal.precio != '' && $scope.fDataOC.temporal.cantidad != '' ){
              $scope.fDataOC.temporal.valor = (parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad).toFixed(2);
              $scope.fDataOC.temporal.descuento_valor = ($scope.fDataOC.temporal.valor * $scope.fDataOC.temporal.descuento / 100).toFixed(2);
              if( $scope.fDataOC.modo_igv == 1 ){
                $scope.fDataOC.temporal.importe_sin = ($scope.fDataOC.temporal.valor - $scope.fDataOC.temporal.descuento_valor).toFixed(2);
                if( angular.isObject($scope.fDataOC.temporal.producto) ){ 
                  if($scope.fDataOC.temporal.producto.excluye_igv == 1){
                    $scope.fDataOC.temporal.igv = 0.00;
                  }else{
                    $scope.fDataOC.temporal.igv = ($scope.fDataOC.temporal.importe_sin * 0.18).toFixed(2);
                  }
                }else{
                  return;
                }
                
                $scope.fDataOC.temporal.importe = (parseFloat($scope.fDataOC.temporal.importe_sin) + parseFloat($scope.fDataOC.temporal.igv)).toFixed(2);
              }else{

                $scope.fDataOC.temporal.importe = (parseFloat($scope.fDataOC.temporal.valor) - parseFloat($scope.fDataOC.temporal.descuento_valor)).toFixed(2);
                if($scope.fDataOC.temporal.producto.excluye_igv == 1){
                  $scope.fDataOC.temporal.importe_sin = parseFloat($scope.fDataOC.temporal.importe).toFixed(2);
                  $scope.fDataOC.temporal.igv = 0.00;
                }else{
                  $scope.fDataOC.temporal.importe_sin = ($scope.fDataOC.temporal.importe / 1.18).toFixed(2);
                  $scope.fDataOC.temporal.igv =($scope.fDataOC.temporal.importe_sin * 0.18).toFixed(2);
                }
              }
            }else{
              $scope.fDataOC.temporal.importe_sin = null;
              $scope.fDataOC.temporal.importe = null;
            }
          }
          $scope.calcularTotales = function () { 
            var subtotal = 0;
            var igv = 0;
            var total = 0;
            angular.forEach($scope.gridOptions.data,function (value, key) { 
              total += parseFloat($scope.gridOptions.data[key].importe);
            });
            $scope.fDataOC.subtotal_temp = (total / 1.18);
            $scope.fDataOC.subtotal = redondear($scope.fDataOC.subtotal_temp).toFixed(2);
            $scope.fDataOC.igv = redondear($scope.fDataOC.subtotal_temp * 0.18).toFixed(2);
            $scope.fDataOC.total = redondear(total).toFixed(2);
          }
          $scope.obtenerMedicamentoPorCodigo = function () {
            blockUI.start();
            if( $scope.fDataOC.temporal.idmedicamento ){
              var arrData = {
                'codigo': $scope.fDataOC.temporal.idmedicamento
              }
              medicamentoServices.sListarMedicamentoPorCodigo(arrData).then(function (rpta) {
                if( rpta.flag == 1){
                  //$scope.fDataOC.idmedicamento = rpta.datos.id;
                  console.log('MedicamentoPorCodigo',rpta);
                  $scope.fDataOC.temporal.producto = rpta.datos[0];
                  // $scope.fDataOC.temporal.producto.id = rpta.datos[0].id;
                  // $('#fDatadepartamento').focus();
                  if(rpta.datos.acepta_caja_unidad == '2'){
                    console.log('no acepta');
                    $scope.fDataOC.temporal.acepta_caja_unidad = false;
                    $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
                  }else{
                    console.log('si acepta');
                    $scope.fDataOC.temporal.acepta_caja_unidad = true;
                    $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
                  }
                  $scope.ultimoPrecioCompra(rpta.datos[0].id);
                  setTimeout(function() {
                      $('#temporalCantidad').focus().select();

                    },500);
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
                }else{
                  alert('Error inesperado...');
                }
                blockUI.stop();
              });

            }
          }
          $scope.getProductoAutocomplete = function (value) { 
            var params = {
              searchText: value, 
              searchColumn: "(COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,''))",
              //especialidadId: $scope.fDataOC.temporal.especialidad.idespecialidad,
              sensor: false
            }
            return medicamentoServices.sListarMedicamentosAutoCompleteParaFarmacia(params).then(function(rpta) {
              //console.log('Datos: ',rpta.datos);
              $scope.noResultsLPSC = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLPSC = true;
              }
              return rpta.datos;
            });
          }
          $scope.ultimoPrecioCompra = function(idmedicamento){
            blockUI.start();
            var paramDatos = {
              idmedicamento: idmedicamento,
              caja_unidad: $scope.fDataOC.temporal.caja_unidad,
              idalmacen: $scope.fDataOC.almacen.id
            }
            ordenCompraServices.sListarUltimoPrecioMedicamento(paramDatos).then(function(rpta) {
              //console.log('Datos: ',rpta.datos);
              $scope.fDataOC.temporal.precio = rpta.datos;
              
              //$scope.fDataOC.temporal.importe = parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad * (1 - $scope.fDataOC.temporal.descuento / 100);
              if($scope.fDataOC.temporal.precio != null){
                $scope.calcularImporte();
              }
              blockUI.stop();
            });
          }
          $scope.getSelectedProducto = function (item, model) {
            console.log('getSelectedProducto');;
            $scope.fDataOC.temporal.idmedicamento = model.id;
            if(model.acepta_caja_unidad == '2'){
              console.log('no acepta');
              $scope.fDataOC.temporal.acepta_caja_unidad = false;
              $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
            }else{
              console.log('si acepta');
              $scope.fDataOC.temporal.acepta_caja_unidad = true;
              $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
            }
            $scope.ultimoPrecioCompra(model.id);
            setTimeout(function() {
              $('#temporalCantidad').focus().select();
            },500);
            /*
            var paramDatos = {
              idmedicamento: model.id,
              caja_unidad: $scope.fDataOC.temporal.caja_unidad
            }
            ordenCompraServices.sListarUltimoPrecioMedicamento(paramDatos).then(function(rpta) {
              //console.log('Datos: ',rpta.datos);
              $scope.fDataOC.temporal.precio = rpta.datos;
              setTimeout(function() {
                $('#temporalPrecio').focus();
              },500);
              //$scope.fDataOC.temporal.importe = parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad * (1 - $scope.fDataOC.temporal.descuento / 100);
              if($scope.fDataOC.temporal.precio != null){
                $scope.calcularImporte();
              }
            });*/
          }
          $scope.limpiarProducto = function(){
            if($scope.fDataOC.temporal.producto){
              console.log('limpiando');
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
            }
          }
          $scope.limpiarId = function(){
            if($scope.fDataOC.temporal.idmedicamento){
              console.log('limpiando');
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
              // $scope.fDataOC.temporal.idmedicamento = null;
              // $scope.fDataOC.temporal.precio = null;
              // $scope.fDataOC.temporal.descuento = null;
              // $scope.fDataOC.temporal.importe_sin = null;
              // $scope.fDataOC.temporal.importe = null;
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
                      $scope.fDataOC.proveedor = $scope.mySelectionProveedorGrid[0]; //console.log($scope.fDataOC.Proveedor);
                      $scope.fDataOC.ruc = $scope.mySelectionProveedorGrid[0].ruc;
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
                paginationOptionsProveedor.sortName = $scope.gridOptionsProveedorBusqueda.columnDefs[0].name;
                $scope.getPaginationProveedorSide = function() {
                  $scope.datosGrid = {
                    paginate : paginationOptionsProveedor
                  };
                  proveedorFarmaciaServices.sListarProveedorFarmacia($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsProveedorBusqueda.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsProveedorBusqueda.data = rpta.datos;
                  });
                  $scope.mySelectionProveedorGrid = [];
                };
                $scope.getPaginationProveedorSide();
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
              }
            });
          }
          $scope.obtenerDatosProveedor = function () { 
            if( $scope.fDataOC.ruc ){ 
              proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fDataOC).then(function (rpta) { 
                $scope.fDataOC.proveedor = rpta.datos;
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al proveedor en el sistema.', type: 'success', delay: 2000 });
                }else{
                  $scope.btnNuevo("",$scope.fDataOC);
                }
              });
            }
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            var index = $scope.gridOptions.data.indexOf(row.entity); 
            $scope.gridOptions.data.splice(index,1);
            $scope.calcularTotales(); 
          }
          $scope.limpiarCampos = function (){
            $scope.fDataOC.proveedor = {};
          }
          
          $scope.agregarItem = function () {
            $('#temporalProducto').focus();
            if( !angular.isObject($scope.fDataOC.temporal.producto) ){ 
              $scope.fDataOC.temporal.idmedicamento = null;
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
              return false;
            }
            console.log($scope.fDataOC.temporal.precio);
            if( !($scope.fDataOC.temporal.precio >= 0) ){
              $scope.fDataOC.temporal.precio = null;
              $('#temporalPrecio').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un precio válido', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataOC.temporal.cantidad >= 1) ){
              $scope.fDataOC.temporal.cantidad = null;
              $('#temporalCantidad').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese una cantidad válida', type: 'warning', delay: 2000 });
              return false;
            }
            var productNew = true;
            angular.forEach($scope.gridOptions.data, function(value, key) { 
              if(value.id == $scope.fDataOC.temporal.producto.id ){ 
                productNew = false;
              }
            });
            if( productNew === false ){
              $scope.fDataOC.temporal.idmedicamento = null;
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              return false;
            } 
            $scope.arrTemporal = { 
              'id' : $scope.fDataOC.temporal.producto.id,
              'descripcion' : $scope.fDataOC.temporal.producto.medicamento,
              'presentacion' : $scope.fDataOC.temporal.producto.presentacion,
              'cantidad' : $scope.fDataOC.temporal.cantidad,
              'precio' : $scope.fDataOC.temporal.precio,
              'valor' : $scope.fDataOC.temporal.valor,
              'descuento' : $scope.fDataOC.temporal.descuento,
              'descuento_valor' : $scope.fDataOC.temporal.descuento_valor,
              'importe_sin' : $scope.fDataOC.temporal.importe_sin,
              'igv' : $scope.fDataOC.temporal.igv,
              'importe' : $scope.fDataOC.temporal.importe,
              'excluye_igv' : $scope.fDataOC.temporal.producto.excluye_igv,
              'caja_unidad' : $scope.fDataOC.temporal.caja_unidad,
              'contenido' : $scope.fDataOC.temporal.producto.contenido
            };
            
            $scope.gridOptions.data.push($scope.arrTemporal);
            $scope.calcularTotales(); 
            $scope.fDataOC.temporal = {
              cantidad: 1,
              descuento: 0,
              importe: null,
              producto: null,
              caja_unidad : $scope.listadoCajaUnidad[0].id
            };
          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.fDataOC = {};
            $scope.getPaginationServerSide();
            $scope.getPaginationPCServerSide();
          }
          $scope.aceptar = function(){
            if($scope.isRegisterSuccess){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La orden ya fue registrada', type: 'warning', delay: 3000 });
              return false;
            }

            if( $scope.fDataOC.proveedor.razon_social == '' || $scope.fDataOC.proveedor.razon_social == null || $scope.fDataOC.proveedor.razon_social == undefined ){
              $scope.fDataOC.ruc = null;
              $('#ruc').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado un proveedor', type: 'warning', delay: 3000 });
              return false;
            }

            $scope.fDataOC.detalle = $scope.gridOptions.data;
            if( $scope.fDataOC.detalle.length < 1 ){ 
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún producto/medicamento', type: 'warning', delay: 3000 }); 
              return false; 
            }
            //console.log('fDataOC: ', $scope.fDataOC);
            blockUI.start('Ejecutando proceso...');
            ordenCompraServices.sRegistrarOrdenCompra($scope.fDataOC).then(function (rpta) { 
              blockUI.stop();
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $scope.isRegisterSuccess = true;
                $scope.idmovimiento = rpta.idmovimiento;
                // $scope.fDataOC = {};
                // $scope.fDataOC.temporal.producto = null;
                // $scope.fDataOC.temporal.precio = null;
                // $scope.fDataOC.temporal.cantidad = null;

                $scope.getPaginationServerSide();
                $scope.getPaginationPCServerSide();
                //$modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                //$scope.fDataOC.fecha_vencimiento = null;
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          
        }
      })  
    }
    // EDICION
    $scope.btnEditarOC = function(size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_orden_compra',
        size: size || 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          // $scope.fDataOC = {};
          $scope.submodulo = 'edicion';
          $scope.fDataOC.fecha_aprobacion = null;
          $scope.cambiarFecha = function(){
            if($scope.fDataOC.estado_orden == 2){
              $scope.fDataOC.fecha_aprobacion = $filter('date')(new Date(),'dd-MM-yyyy');
            }else{
              $scope.fDataOC.fecha_aprobacion = null;
            }
          };

          if( $scope.mySelectionGridOC.length == 1 ){
            $scope.fDataOC = angular.copy($scope.mySelectionGridOC[0]);
            // LISTA TIPO DE MATERIAL
            ordenCompraServices.sListarTipoMaterialCbo().then(function (rpta) { //console.log(rpta);
              $scope.listaTipoMaterial = rpta.datos;
              angular.forEach($scope.listaTipoMaterial, function(value, key) { 
                if(value.id == $scope.fDataOC.idtipomaterial ){
                  console.log('$scope.fDataOC.idtipomaterial',$scope.fDataOC.idtipomaterial);
                  $scope.fDataOC.tipoMaterial = $scope.listaTipoMaterial[key];
                }
              });
            });
            
          }else{
            alert('Seleccione una sola fila');
          }
          console.log('fData->', $scope.fDataOC);
          $scope.titleForm = 'Edición de Orden de Compra';
          $scope.idmovimiento = $scope.fDataOC.idmovimiento;
          // LISTA MODO DE MEDIDA
          $scope.listadoCajaUnidad = [
            {id: 'CAJA', descripcion: 'CAJA'},
            {id: 'UNIDAD', descripcion: 'UNIDAD'}
          ]
          
          
          $scope.fDataOC.temporal = {};
          $scope.fDataOC.temporal.descuento = 0;
          $scope.fDataOC.fecha_movimiento = $scope.fDataOC.fecha_movimiento_or;
          $scope.fDataOC.fecha_aprobacion = $scope.fDataOC.fecha_aprobacion_or;
          $scope.fDataOC.fecha_entrega = $scope.fDataOC.fecha_entrega_or;
          $scope.fDataOC.temporal.lote = null;
          $scope.fDataOC.temporal.cantidad = 1;
          $scope.fDataOC.temporal.acepta_caja_unidad = false;
          $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
          $scope.fDataOC.almacen = $scope.fBusqueda.almacen;
          $scope.cambiarSimbolo = function(){
            angular.forEach($scope.listaMoneda, function(value, key) { 
              if(value.id == $scope.fDataOC.moneda ){ 
                $scope.fDataOC.simbolo_monetario = $scope.listaMoneda[key].descripcion;
              }
            });
          }
          $scope.cambiarSimbolo();
          $scope.obtenerDatosProveedor = function () { 
            if( $scope.fDataOC.ruc ){ 
              proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fDataOC).then(function (rpta) { 
                $scope.fDataOC.proveedor = rpta.datos;
              });
            }
          }
          $scope.obtenerDatosProveedor();

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
              { field: 'cantidad', displayName: 'CANT.', width: '5%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center' },
              { field: 'precio', displayName: 'P. UNIT', width: '8%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-right' },
              { field: 'valor', displayName: 'VALOR', width: '8%', enableCellEdit: false, enableSorting: false, cellClass:'text-center' },
              { field: 'descuento', displayName: 'DCTO.(%) ', width: '7%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center'},
              { field: 'descuento_valor', displayName: 'DCTO. VALOR ', width: '9%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible: false },
              { field: 'importe_sin', displayName: 'IMPORTE SIN IGV ', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible: true },
              { field: 'igv', displayName: 'IGV', width: '6%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'importe', displayName: 'IMPORTE CON IGV', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'excluye_igv', displayName: 'INAFECTO', width: '6%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
              },
              { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ]
            ,onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                
                rowEntity.column = colDef.field;
                //console.log(rowEntity.column);

                if(rowEntity.column == 'cantidad'){
                  if( !(rowEntity.cantidad >= 1) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if(rowEntity.column == 'descuento'){ 
                  if( !(rowEntity.descuento >= 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.descuento = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El descuento debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if(rowEntity.column == 'precio'){ 
                  if( !(rowEntity.precio >= 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.precio = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El Precio debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                //rowEntity.precio = parseFloat($scope.arrTemporal.precio).toFixed(2);
                rowEntity.valor = (parseFloat(rowEntity.precio) * parseFloat(rowEntity.cantidad)).toFixed(2);
                rowEntity.descuento_valor = (rowEntity.valor * rowEntity.descuento / 100).toFixed(2);
                if( $scope.fDataOC.modo_igv == 1 ){
                  console.log('Calculando Modo sin IGV');
                  rowEntity.importe_sin = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                   rowEntity.igv = 0.00;
                  }else{
                   rowEntity.igv = (0.18 * rowEntity.importe_sin).toFixed(2);
                  }
                  rowEntity.importe = (parseFloat(rowEntity.importe_sin) + parseFloat(rowEntity.igv)).toFixed(2);
                }else{
                  console.log('Calculando Modo con IGV');
                  rowEntity.importe = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                    rowEntity.importe_sin = (rowEntity.importe).toFixed(2);
                    rowEntity.igv = 0.00;
                  }else{
                    rowEntity.importe_sin = (rowEntity.importe / 1.18).toFixed(2);
                    rowEntity.igv = (0.18 * rowEntity.importe_sin).toFixed(2);
                  }
                }
                $scope.calcularTotales();
                $scope.$apply();
                
                // GUARDAR EN BASE DE DATOS 
                $scope.fDataOC.detalle = $scope.gridOptions.data;
                if( $scope.fDataOC.detalle.length < 1 ){ 
                  $('#temporalProducto').focus();
                  pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún producto/medicamento', type: 'warning', delay: 3000 }); 
                  return false; 
                }
                // console.log($scope.fDataOC); return false; 
                ordenCompraServices.sEditarOrdenCompra($scope.fDataOC).then(function (rpta) { 
                  if(rpta.flag == 1){
                    var pTitle = 'OK!';
                    var pType = 'success'; 
                    $scope.getPaginationOCServerSide();
                    // $scope.getPaginationPCServerSide();
                    // $modalInstance.dismiss('cancel');
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                    // $scope.fDataOC.fecha_vencimiento = null;
                  }else{
                    alert('Algo salió mal...');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                }); 

                
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
          $scope.getPaginationOCServerSide = function() {
            var arrParams = {
              datos: $scope.fDataOC
            };
            ordenCompraServices.sListarDetalleOrdenCompra(arrParams).then(function (rpta) {
              $scope.gridOptions.data = rpta.datos;
              //$scope.gridOptions.sumTotal = rpta.sumTotal;

            });
          };
          $scope.getPaginationOCServerSide();
          $scope.cambiarModo = function(){
            if( $scope.fDataOC.modo_igv == 1){
              console.log('Modo sin IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe_sin = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor) ).toFixed(2);
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].igv = 0.00;
                }else{
                  $scope.gridOptions.data[key].igv = ( parseFloat($scope.gridOptions.data[key].importe_sin) * 0.18 ).toFixed(2);
                }
                $scope.gridOptions.data[key].importe = ( parseFloat($scope.gridOptions.data[key].importe_sin) + parseFloat($scope.gridOptions.data[key].igv) ).toFixed(2);
                
              });
            }else{
              console.log('Modo con IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor) ).toFixed(2);
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].importe_sin = parseFloat($scope.gridOptions.data[key].importe).toFixed(2);
                  $scope.gridOptions.data[key].igv = 0.00;
                } else{
                  $scope.gridOptions.data[key].importe_sin = ( parseFloat($scope.gridOptions.data[key].importe) / 1.18 ).toFixed(2);
                  $scope.gridOptions.data[key].igv = ( parseFloat($scope.gridOptions.data[key].importe_sin) * 0.18 ).toFixed(2);
                }
                
              });
            }
            $scope.calcularTotales();
          };
          $scope.calcularImporte = function (){
            if($scope.fDataOC.temporal.precio != '' && $scope.fDataOC.temporal.cantidad != '' ){
              $scope.fDataOC.temporal.valor = (parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad).toFixed(2);
              $scope.fDataOC.temporal.descuento_valor = ($scope.fDataOC.temporal.valor * $scope.fDataOC.temporal.descuento / 100).toFixed(2);
              if( $scope.fDataOC.modo_igv == 1 ){
                $scope.fDataOC.temporal.importe_sin = ($scope.fDataOC.temporal.valor - $scope.fDataOC.temporal.descuento_valor).toFixed(2);
                if($scope.fDataOC.temporal.producto.excluye_igv == 1){
                  $scope.fDataOC.temporal.igv = 0.00;
                }else{
                  $scope.fDataOC.temporal.igv = ($scope.fDataOC.temporal.importe_sin * 0.18).toFixed(2);
                }
                $scope.fDataOC.temporal.importe = (parseFloat($scope.fDataOC.temporal.importe_sin) + parseFloat($scope.fDataOC.temporal.igv)).toFixed(2);
              }else{

                $scope.fDataOC.temporal.importe = (parseFloat($scope.fDataOC.temporal.valor) - parseFloat($scope.fDataOC.temporal.descuento_valor)).toFixed(2);
                if($scope.fDataOC.temporal.producto.excluye_igv == 1){
                  $scope.fDataOC.temporal.importe_sin = parseFloat($scope.fDataOC.temporal.importe).toFixed(2);
                  $scope.fDataOC.temporal.igv = 0.00;
                }else{
                  $scope.fDataOC.temporal.importe_sin = ($scope.fDataOC.temporal.importe / 1.18).toFixed(2);
                  $scope.fDataOC.temporal.igv =($scope.fDataOC.temporal.importe_sin * 0.18).toFixed(2);
                }
              }
            }else{
              $scope.fDataOC.temporal.importe_sin = null;
              $scope.fDataOC.temporal.importe = null;
            }
          }
          $scope.calcularTotales = function () { 
            var subtotal = 0;
            var igv = 0;
            var total = 0;
            angular.forEach($scope.gridOptions.data,function (value, key) { 
              subtotal += parseFloat($scope.gridOptions.data[key].importe_sin);
              igv +=  parseFloat($scope.gridOptions.data[key].igv);
              total += parseFloat($scope.gridOptions.data[key].importe);
            });
            $scope.fDataOC.subtotal = subtotal.toFixed(2);
            $scope.fDataOC.igv = igv.toFixed(2);
            //$scope.fDataOC.total = (subtotal + igv).toFixed(2);
            $scope.fDataOC.total = total.toFixed(2);
          }
          $scope.obtenerMedicamentoPorCodigo = function () {
            if( $scope.fDataOC.temporal.idmedicamento ){
              var arrData = {
                'codigo': $scope.fDataOC.temporal.idmedicamento
              }
              medicamentoServices.sListarMedicamentoPorCodigo(arrData).then(function (rpta) {
                if( rpta.flag == 1){
                  //$scope.fDataOC.idmedicamento = rpta.datos.id;
                  console.log('rpta',rpta);
                  $scope.fDataOC.temporal.producto = rpta.datos[0];
                  // $scope.fDataOC.temporal.producto.id = rpta.datos[0].id;
                  // $('#fDatadepartamento').focus();
                  if(rpta.datos.acepta_caja_unidad == '2'){
                    console.log('no acepta');
                    $scope.fDataOC.temporal.acepta_caja_unidad = false;
                    $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
                  }else{
                    console.log('si acepta');
                    $scope.fDataOC.temporal.acepta_caja_unidad = true;
                    $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
                  }
                  $scope.ultimoPrecioCompra(rpta.datos[0].id);
                  setTimeout(function() {
                      $('#temporalCantidad').focus().select();

                    },500);
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
                }else{
                  alert('Error inesperado...');
                }
              });

            }
          }
          $scope.getProductoAutocomplete = function (value) { 
            var params = {
              searchText: value, 
              searchColumn: "(COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,''))",
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
              caja_unidad: $scope.fDataOC.temporal.caja_unidad
            }
            ordenCompraServices.sListarUltimoPrecioMedicamento(paramDatos).then(function(rpta) {
              //console.log('Datos: ',rpta.datos);
              $scope.fDataOC.temporal.precio = rpta.datos;
              setTimeout(function() {
                $('#temporalPrecio').focus();
              },500);
              //$scope.fDataOC.temporal.importe = parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad * (1 - $scope.fDataOC.temporal.descuento / 100);
              if($scope.fDataOC.temporal.precio != null){
                $scope.calcularImporte();
              }
            });
          }
          $scope.getSelectedProducto = function (item, model) {
            $scope.fDataOC.temporal.idmedicamento = model.id;
            if(model.acepta_caja_unidad == '2'){
              console.log('no acepta');
              $scope.fDataOC.temporal.acepta_caja_unidad = false;
              $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
            }else{
              console.log('si acepta');
              $scope.fDataOC.temporal.acepta_caja_unidad = true;
              $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
            }
            $scope.ultimoPrecioCompra(model.id);
          }
          $scope.limpiarProducto = function(){
            if($scope.fDataOC.temporal.producto){
              console.log('limpiando');
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
            }
          }
          $scope.limpiarId = function(){
            if($scope.fDataOC.temporal.idmedicamento){
              console.log('limpiando');
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
            }
          }
          $scope.agregarItem = function () {
            $('#temporalProducto').focus();

            if( !angular.isObject($scope.fDataOC.temporal.producto) ){ 
              $scope.fDataOC.temporal.idmedicamento = null;
              $scope.fDataOC.temporal.producto = null;
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataOC.temporal.precio >= 0) ){
              $scope.fDataOC.temporal.precio = null;
              $('#temporalPrecio').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un precio válido', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataOC.temporal.cantidad >= 1) ){ // console.log('especialidad');
              $scope.fDataOC.temporal.cantidad = null;
              $('#temporalCantidad').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese una cantidad válida', type: 'warning', delay: 2000 });
              return false;
            }
            var productNew = true;
            angular.forEach($scope.gridOptions.data, function(value, key) { 
              if(value.id == $scope.fDataOC.temporal.producto.id ){ 
                productNew = false;
              }
            });
            if( productNew === false ){
              $scope.fDataOC.temporal.idmedicamento = null;
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              return false;
            }
            $scope.arrTemporal = { 
              'id' : $scope.fDataOC.temporal.producto.id,
              'descripcion' : $scope.fDataOC.temporal.producto.medicamento,
              'unidad_medida' : $scope.fDataOC.temporal.producto.idunidadmedida,
              'cantidad' : $scope.fDataOC.temporal.cantidad,
              'precio' : $scope.fDataOC.temporal.precio,
              'valor' : $scope.fDataOC.temporal.valor,
              'descuento' : $scope.fDataOC.temporal.descuento,
              'descuento_valor' : $scope.fDataOC.temporal.descuento_valor,
              'importe_sin' : $scope.fDataOC.temporal.importe_sin,
              'igv' : $scope.fDataOC.temporal.igv,
              'importe' : $scope.fDataOC.temporal.importe,
              'excluye_igv' : $scope.fDataOC.temporal.producto.excluye_igv,
              'es_nuevo' : true,
              'caja_unidad' : $scope.fDataOC.temporal.caja_unidad,
              'contenido' : $scope.fDataOC.temporal.producto.contenido
            };

            $scope.gridOptions.data.push($scope.arrTemporal);
            $scope.calcularTotales(); 
            $scope.fDataOC.temporal = {
              cantidad: 1,
              descuento: 0,
              importe: null,
              producto: null,
              caja_unidad : $scope.listadoCajaUnidad[0].id
            };
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            if( row.entity.es_nuevo ){
              var index = $scope.gridOptions.data.indexOf(row.entity); 
              $scope.gridOptions.data.splice(index,1);
              $scope.calcularTotales();
            }else{
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                
                if(result){
                  var index = $scope.gridOptions.data.indexOf(row.entity); 
                  $scope.gridOptions.data.splice(index,1);
                  $scope.calcularTotales();
                  var paramDatos = {
                    'idmovimiento' : $scope.fDataOC.idmovimiento,
                    'iddetallemovimiento' : row.entity.iddetallemovimiento,
                    'sub_total' : $scope.fDataOC.subtotal,
                    'total_igv' : $scope.fDataOC.igv,
                    'total_a_pagar' : $scope.fDataOC.total
                  }
                  ordenCompraServices.sAnularDetalle(paramDatos).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      $scope.getPaginationOCServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    $scope.getPaginationServerSide();
                    $scope.getPaginationPCServerSide();
                  });
                }
              });  
            }
          }
          $scope.limpiarCampos = function (){
            $scope.fDataOC.proveedor = {};
          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.getPaginationServerSide();
            $scope.getPaginationPCServerSide();
          }
          $scope.aceptar = function(){
            $scope.fDataOC.detalle = $scope.gridOptions.data;
            if( $scope.fDataOC.detalle.length < 1 ){ 
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún producto/medicamento', type: 'warning', delay: 3000 }); 
              return false; 
            }
            // console.log('fDataOC: ', $scope.fDataOC);
            ordenCompraServices.sEditarOrdenCompra($scope.fDataOC).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 

                // $scope.fDataOC = {};
                // $scope.fDataOC.temporal.producto = null;
                // $scope.fDataOC.temporal.precio = null;
                // $scope.fDataOC.temporal.cantidad = null;

                $scope.getPaginationServerSide();
                $scope.getPaginationPCServerSide();
                //$modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                //$scope.fDataOC.fecha_vencimiento = null;
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            }); 
          }
        }
      })  
    }
    // CLONACION OC
    $scope.btnClonarOC = function() {
      blockUI.start('Ejecutando proceso...');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_orden_compra',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.submodulo = 'clonacion';
          $scope.titleForm = 'Clonación de Orden de Compra';
          // $scope.fDataOC = {};
          $scope.fDataOC.ruc = null;
          $scope.fDataOC.proveedor = {};
          $scope.fDataOC.estado_orden = $scope.listaEstadoOrden[0].id;
          $scope.fDataOC.almacen = $scope.listaAlmacenes[0];
          $scope.fDataOC.orden_compra = '[ ............... ]';
          $scope.fDataOC.modo_igv = 2;
          $scope.fDataOC.fecha_movimiento = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataOC.fecha_entrega = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataOC.fecha_aprobacion = null;
          $scope.cambiarFecha = function(){
            if($scope.fDataOC.estado_orden == 2){
              $scope.fDataOC.fecha_aprobacion = $filter('date')(new Date(),'dd-MM-yyyy');
            }else{
              $scope.fDataOC.fecha_aprobacion = null;
            }
          };
          $scope.fDataOC.temporal = {};
          $scope.fDataOC.temporal.lote = null;
          $scope.fDataOC.temporal.cantidad = 1;
          $scope.fDataOC.temporal.descuento = 0;
          $scope.fDataOC.temporal.producto = [];
          $scope.fDataOC.temporal.producto.excluye_igv = 2;
          $scope.fDataOC.temporal.acepta_caja_unidad = false;

          if( $scope.mySelectionGridOC.length == 1 ){
            $scope.fDataOC.idmovimiento = $scope.mySelectionGridOC[0].idmovimiento;
            $scope.fDataOC.ruc = $scope.mySelectionGridOC[0].ruc;
            // $scope.fDataOC.proveedor = $scope.mySelectionGridOC[0].proveedor;
            $scope.fDataOC.idproveedor = $scope.mySelectionGridOC[0].idproveedor;
            $scope.fDataOC.forma_pago = $scope.mySelectionGridOC[0].forma_pago;
            $scope.fDataOC.moneda = $scope.mySelectionGridOC[0].moneda;
            $scope.fDataOC.idtipomaterial = $scope.mySelectionGridOC[0].idtipomaterial;
            $scope.fDataOC.subtotal = $scope.mySelectionGridOC[0].subtotal;
            $scope.fDataOC.igv = $scope.mySelectionGridOC[0].igv;
            $scope.fDataOC.total = $scope.mySelectionGridOC[0].total;
            $scope.fDataOC.letras = $scope.mySelectionGridOC[0].letras;
            $scope.cambiarSimbolo = function(){
              angular.forEach($scope.listaMoneda, function(value, key) { 
                if(value.id == $scope.fDataOC.moneda ){ 
                  $scope.fDataOC.simbolo_monetario = $scope.listaMoneda[key].descripcion;
                }
              });
            }
            $scope.cambiarSimbolo();
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.obtenerDatosProveedor = function () { 
            if( $scope.fDataOC.ruc ){ 
              proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fDataOC).then(function (rpta) { 
                $scope.fDataOC.proveedor = rpta.datos;
              });
            }
          }
          $scope.obtenerDatosProveedor();

          // $scope.idmovimiento = $scope.fDataOC.idmovimiento;
          // LISTA TIPO DE MATERIAL
          ordenCompraServices.sListarTipoMaterialCbo().then(function (rpta) { //console.log(rpta);
            $scope.listaTipoMaterial = rpta.datos;
            angular.forEach($scope.listaTipoMaterial, function(value, key) { 
              if(value.id == $scope.fDataOC.idtipomaterial ){ 
                $scope.fDataOC.tipoMaterial = $scope.listaTipoMaterial[key];
                $scope.generarNumOrden();
              }
            });
          });
          $scope.generarNumOrden = function () {
            ordenCompraServices.sGenerarCodigoOrden($scope.fDataOC).then(function (rpta) { 
              $scope.fDataOC.orden_compra = rpta.codigo_orden; 
            });
          };
          
          console.log('fDataOC->', $scope.fDataOC);
          // LISTA MODO DE MEDIDA
          $scope.listadoCajaUnidad = [
            {id: 'CAJA', descripcion: 'CAJA'},
            {id: 'UNIDAD', descripcion: 'UNIDAD'}
          ]
          $scope.fDataOC.temporal = {};
          $scope.fDataOC.temporal.descuento = 0;
          $scope.fDataOC.temporal.lote = null;
          $scope.fDataOC.temporal.cantidad = 1;
          $scope.fDataOC.temporal.acepta_caja_unidad = false;
          $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;

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
              { field: 'cantidad', displayName: 'CANT.', width: '5%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center' },
              { field: 'precio', displayName: 'P. UNIT', width: '8%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-right' },
              { field: 'valor', displayName: 'VALOR', width: '8%', enableCellEdit: false, enableSorting: false, cellClass:'text-center' },
              { field: 'descuento', displayName: 'DCTO.(%) ', width: '7%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-center'},
              { field: 'descuento_valor', displayName: 'DCTO. VALOR ', width: '9%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible: false },
              { field: 'importe_sin', displayName: 'IMPORTE SIN IGV ', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible: true },
              { field: 'igv', displayName: 'IGV', width: '6%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'importe', displayName: 'IMPORTE CON IGV', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'excluye_igv', displayName: 'INAFECTO', width: '6%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
              },
              { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ]
            ,onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                
                rowEntity.column = colDef.field;
                //console.log(rowEntity.column);

                if(rowEntity.column == 'cantidad'){
                  if( !(rowEntity.cantidad >= 1) || rowEntity.cantidad == '' ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if(rowEntity.column == 'descuento'){
                  if( !(rowEntity.descuento >= 0) || rowEntity.descuento == '' ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.descuento = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El descuento debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                if(rowEntity.column == 'precio'){
                  if( !(rowEntity.precio >= 0)  || rowEntity.precio == ''){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.precio = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El Precio debe ser mayor o igual a 0', type: pType, delay: 3500 });
                    return false;
                  }
                }
                rowEntity.valor = parseFloat(rowEntity.precio) * parseFloat(rowEntity.cantidad);
                rowEntity.descuento_valor = rowEntity.valor * rowEntity.descuento / 100;
                if( $scope.fDataOC.modo_igv == 1 ){
                  console.log('Calculando Modo sin IGV');
                  rowEntity.importe_sin = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                   rowEntity.igv = 0.00;
                  }else{
                   rowEntity.igv = (0.18 * rowEntity.importe_sin).toFixed(2);
                  }
                  rowEntity.importe = (parseFloat(rowEntity.importe_sin) + parseFloat(rowEntity.igv)).toFixed(2);
                }else{
                  console.log('Calculando Modo con IGV');
                  rowEntity.importe = (rowEntity.valor - rowEntity.descuento_valor).toFixed(2);
                  if(rowEntity.excluye_igv == 1){
                    rowEntity.importe_sin = (rowEntity.importe).toFixed(2);
                    rowEntity.igv = 0.00;
                  }else{
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
          $scope.getPaginationOCServerSide = function() {
            var arrParams = {
              datos: $scope.fDataOC
            };
            ordenCompraServices.sListarDetalleOrdenCompra(arrParams).then(function (rpta) {
              $scope.gridOptions.data = rpta.datos;
              //$scope.gridOptions.sumTotal = rpta.sumTotal;

            });
          };
          $scope.getPaginationOCServerSide();
          $scope.cambiarModo = function(){
            if( $scope.fDataOC.modo_igv == 1){
              console.log('Modo sin IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe_sin = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor) ).toFixed(2);
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].igv = 0.00;
                }else{
                  $scope.gridOptions.data[key].igv = ( parseFloat($scope.gridOptions.data[key].importe_sin) * 0.18 ).toFixed(2);
                }
                $scope.gridOptions.data[key].importe = ( parseFloat($scope.gridOptions.data[key].importe_sin) + parseFloat($scope.gridOptions.data[key].igv) ).toFixed(2);
                
              });
            }else{
              console.log('Modo con IGV ');
              angular.forEach($scope.gridOptions.data,function (value, key) {
                $scope.gridOptions.data[key].importe = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento_valor) ).toFixed(2);
                if( $scope.gridOptions.data[key].excluye_igv == 1 ){
                  $scope.gridOptions.data[key].importe_sin = parseFloat($scope.gridOptions.data[key].importe).toFixed(2);
                  $scope.gridOptions.data[key].igv = 0.00;
                } else{
                  $scope.gridOptions.data[key].importe_sin = ( parseFloat($scope.gridOptions.data[key].importe) / 1.18 ).toFixed(2);
                  $scope.gridOptions.data[key].igv = ( parseFloat($scope.gridOptions.data[key].importe_sin) * 0.18 ).toFixed(2);
                }
                
              });
            }
            $scope.calcularTotales();
          };
          $scope.calcularImporte = function (){
            if($scope.fDataOC.temporal.precio != '' && $scope.fDataOC.temporal.cantidad != '' ){
              $scope.fDataOC.temporal.valor = (parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad).toFixed(2);
              $scope.fDataOC.temporal.descuento_valor = ($scope.fDataOC.temporal.valor * $scope.fDataOC.temporal.descuento / 100).toFixed(2);
              if( $scope.fDataOC.modo_igv == 1 ){
                $scope.fDataOC.temporal.importe_sin = ($scope.fDataOC.temporal.valor - $scope.fDataOC.temporal.descuento_valor).toFixed(2);
                if($scope.fDataOC.temporal.producto.excluye_igv == 1){
                  $scope.fDataOC.temporal.igv = 0.00;
                }else{
                  $scope.fDataOC.temporal.igv = ($scope.fDataOC.temporal.importe_sin * 0.18).toFixed(2);
                }
                $scope.fDataOC.temporal.importe = (parseFloat($scope.fDataOC.temporal.importe_sin) + parseFloat($scope.fDataOC.temporal.igv)).toFixed(2);
              }else{

                $scope.fDataOC.temporal.importe = (parseFloat($scope.fDataOC.temporal.valor) - parseFloat($scope.fDataOC.temporal.descuento_valor)).toFixed(2);
                if($scope.fDataOC.temporal.producto.excluye_igv == 1){
                  $scope.fDataOC.temporal.importe_sin = parseFloat($scope.fDataOC.temporal.importe).toFixed(2);
                  $scope.fDataOC.temporal.igv = 0.00;
                }else{
                  $scope.fDataOC.temporal.importe_sin = ($scope.fDataOC.temporal.importe / 1.18).toFixed(2);
                  $scope.fDataOC.temporal.igv =($scope.fDataOC.temporal.importe_sin * 0.18).toFixed(2);
                }
              }
            }else{
              $scope.fDataOC.temporal.importe_sin = null;
              $scope.fDataOC.temporal.importe = null;
            }
          }
          $scope.calcularTotales = function () { 
            var subtotal = 0;
            var igv = 0;
            var total = 0;
            angular.forEach($scope.gridOptions.data,function (value, key) { 
              subtotal += parseFloat($scope.gridOptions.data[key].importe_sin);
              igv +=  parseFloat($scope.gridOptions.data[key].igv);
              total += parseFloat($scope.gridOptions.data[key].importe);
            });
            $scope.fDataOC.subtotal = subtotal.toFixed(2);
            $scope.fDataOC.igv = igv.toFixed(2);
            //$scope.fDataOC.total = (subtotal + igv).toFixed(2);
            $scope.fDataOC.total = total.toFixed(2);
          }
          $scope.obtenerMedicamentoPorCodigo = function () {
            if( $scope.fDataOC.temporal.idmedicamento ){
              var arrData = {
                'codigo': $scope.fDataOC.temporal.idmedicamento
              }
              medicamentoServices.sListarMedicamentoPorCodigo(arrData).then(function (rpta) {
                if( rpta.flag == 1){
                  //$scope.fDataOC.idmedicamento = rpta.datos.id;
                  console.log('rpta',rpta);
                  $scope.fDataOC.temporal.producto = rpta.datos[0];
                  // $scope.fDataOC.temporal.producto.id = rpta.datos[0].id;
                  // $('#fDatadepartamento').focus();
                  if(rpta.datos.acepta_caja_unidad == '2'){
                    console.log('no acepta');
                    $scope.fDataOC.temporal.acepta_caja_unidad = false;
                    $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
                  }else{
                    console.log('si acepta');
                    $scope.fDataOC.temporal.acepta_caja_unidad = true;
                    $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
                  }
                  $scope.ultimoPrecioCompra(rpta.datos[0].id);
                  setTimeout(function() {
                      $('#temporalCantidad').focus().select();

                    },500);
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
                }else{
                  alert('Error inesperado...');
                }
              });

            }
          }
          $scope.getProductoAutocomplete = function (value) { 
            var params = {
              searchText: value, 
              searchColumn: "(COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,''))",
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
              caja_unidad: $scope.fDataOC.temporal.caja_unidad
            }
            ordenCompraServices.sListarUltimoPrecioMedicamento(paramDatos).then(function(rpta) {
              //console.log('Datos: ',rpta.datos);
              $scope.fDataOC.temporal.precio = rpta.datos;
              setTimeout(function() {
                $('#temporalPrecio').focus();
              },500);
              //$scope.fDataOC.temporal.importe = parseFloat($scope.fDataOC.temporal.precio) * $scope.fDataOC.temporal.cantidad * (1 - $scope.fDataOC.temporal.descuento / 100);
              if($scope.fDataOC.temporal.precio != null){
                $scope.calcularImporte();
              }
            });
          }
          $scope.getSelectedProducto = function (item, model) {
            $scope.fDataOC.temporal.idmedicamento = model.id;
            if(model.acepta_caja_unidad == '2'){
              console.log('no acepta');
              $scope.fDataOC.temporal.acepta_caja_unidad = false;
              $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[1].id;
            }else{
              console.log('si acepta');
              $scope.fDataOC.temporal.acepta_caja_unidad = true;
              $scope.fDataOC.temporal.caja_unidad = $scope.listadoCajaUnidad[0].id;
            }
            $scope.ultimoPrecioCompra(model.id);
          }
          $scope.limpiarProducto = function(){
            if($scope.fDataOC.temporal.producto){
              console.log('limpiando');
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
            }
          }
          $scope.limpiarId = function(){
            if($scope.fDataOC.temporal.idmedicamento){
              console.log('limpiando');
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
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
                      $scope.fDataOC.proveedor = $scope.mySelectionProveedorGrid[0]; //console.log($scope.fDataOC.Proveedor);
                      $scope.fDataOC.ruc = $scope.mySelectionProveedorGrid[0].ruc;
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
                paginationOptionsProveedor.sortName = $scope.gridOptionsProveedorBusqueda.columnDefs[0].name;
                $scope.getPaginationProveedorSide = function() {
                  $scope.datosGrid = {
                    paginate : paginationOptionsProveedor
                  };
                  proveedorFarmaciaServices.sListarProveedorFarmacia($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsProveedorBusqueda.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsProveedorBusqueda.data = rpta.datos;
                  });
                  $scope.mySelectionProveedorGrid = [];
                };
                $scope.getPaginationProveedorSide();
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                  $scope.fDataOC = {};
                  $scope.getPaginationServerSide();
                  $scope.getPaginationPCServerSide();
                }
              }
            });
          }
          $scope.obtenerDatosProveedor = function () { 
            if( $scope.fDataOC.ruc ){ 
              proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fDataOC).then(function (rpta) { 
                $scope.fDataOC.proveedor = rpta.datos;
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al proveedor en el sistema.', type: 'success', delay: 2000 });
                }else{
                  $scope.btnNuevo("",$scope.fDataOC);
                }
              });
            }
          }
          $scope.agregarItem = function () {
            $('#temporalProducto').focus();

            if( !angular.isObject($scope.fDataOC.temporal.producto) ){ 
              $scope.fDataOC.temporal.idmedicamento = null;
              $scope.fDataOC.temporal.producto = null;
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
              return false;
            }
            if( $scope.fDataOC.temporal.precio == '' || !($scope.fDataOC.temporal.precio >= 0) ){
              $scope.fDataOC.temporal.precio = null;
              $('#temporalPrecio').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un precio válido', type: 'warning', delay: 2000 });
              return false;
            }
            if( $scope.fDataOC.temporal.cantidad == null ||  !($scope.fDataOC.temporal.cantidad >= 1) ){ // console.log('especialidad');
              $scope.fDataOC.temporal.cantidad = null;
              $('#temporalCantidad').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese una cantidad válida', type: 'warning', delay: 2000 });
              return false;
            }
            var productNew = true;
            angular.forEach($scope.gridOptions.data, function(value, key) { 
              if(value.id == $scope.fDataOC.temporal.producto.id ){ 
                productNew = false;
              }
            });
            if( productNew === false ){
              $scope.fDataOC.temporal.idmedicamento = null;
              $scope.fDataOC.temporal = {
                cantidad: 1,
                descuento: 0,
                importe: null,
                importe_sin: null,
                producto: null,
                caja_unidad : $scope.listadoCajaUnidad[0].id
              };
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              return false;
            }
            $scope.arrTemporal = { 
              'id' : $scope.fDataOC.temporal.producto.id,
              'descripcion' : $scope.fDataOC.temporal.producto.medicamento,
              'unidad_medida' : $scope.fDataOC.temporal.producto.idunidadmedida,
              'cantidad' : $scope.fDataOC.temporal.cantidad,
              'precio' : $scope.fDataOC.temporal.precio,
              'valor' : $scope.fDataOC.temporal.valor,
              'descuento' : $scope.fDataOC.temporal.descuento,
              'descuento_valor' : $scope.fDataOC.temporal.descuento_valor,
              'importe_sin' : $scope.fDataOC.temporal.importe_sin,
              'igv' : $scope.fDataOC.temporal.igv,
              'importe' : $scope.fDataOC.temporal.importe,
              'excluye_igv' : $scope.fDataOC.temporal.producto.excluye_igv,
              'es_nuevo' : true,
              'caja_unidad' : $scope.fDataOC.temporal.caja_unidad,
              'contenido' : $scope.fDataOC.temporal.producto.contenido
            };

            $scope.gridOptions.data.push($scope.arrTemporal);
            $scope.calcularTotales(); 
            $scope.fDataOC.temporal = {
              cantidad: 1,
              descuento: 0,
              importe: null,
              producto: null,
              caja_unidad : $scope.listadoCajaUnidad[0].id
            };
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            if( row.entity.es_nuevo ){
              var index = $scope.gridOptions.data.indexOf(row.entity); 
              $scope.gridOptions.data.splice(index,1);
              $scope.calcularTotales();
            }else{
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                
                if(result){
                  var index = $scope.gridOptions.data.indexOf(row.entity); 
                  $scope.gridOptions.data.splice(index,1);
                  $scope.calcularTotales();
                  var paramDatos = {
                    'idmovimiento' : $scope.fDataOC.idmovimiento,
                    'iddetallemovimiento' : row.entity.iddetallemovimiento,
                    'sub_total' : $scope.fDataOC.subtotal,
                    'total_igv' : $scope.fDataOC.igv,
                    'total_a_pagar' : $scope.fDataOC.total
                  }
                  ordenCompraServices.sAnularDetalle(paramDatos).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      $scope.getPaginationOCServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    $scope.getPaginationServerSide();
                    $scope.getPaginationPCServerSide();
                  });
                }
              });  
            }
          }
          $scope.limpiarCampos = function (){
            $scope.fDataOC.proveedor = {};
          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.fDataOC = {};
            $scope.getPaginationServerSide();
            $scope.getPaginationPCServerSide();
          }
          $scope.aceptar = function(){
            if($scope.isRegisterSuccess){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La orden ya fue registrada', type: 'warning', delay: 3000 });
              return false;
            }

            if( $scope.fDataOC.proveedor.razon_social == '' || $scope.fDataOC.proveedor.razon_social == null || $scope.fDataOC.proveedor.razon_social == undefined ){
              $scope.fDataOC.ruc = null;
              $('#ruc').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado un proveedor', type: 'warning', delay: 3000 });
              return false;
            }

            $scope.fDataOC.detalle = $scope.gridOptions.data;
            if( $scope.fDataOC.detalle.length < 1 ){ 
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún producto/medicamento', type: 'warning', delay: 3000 }); 
              return false; 
            }
            //console.log('fDataOC: ', $scope.fDataOC);
            blockUI.start('Ejecutando proceso...');
            ordenCompraServices.sRegistrarOrdenCompra($scope.fDataOC).then(function (rpta) { 
              blockUI.stop();
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $scope.isRegisterSuccess = true;
                $scope.idmovimiento = rpta.idmovimiento;
                // $scope.fDataOC = {};
                // $scope.fDataOC.temporal.producto = null;
                // $scope.fDataOC.temporal.precio = null;
                // $scope.fDataOC.temporal.cantidad = null;

                $scope.getPaginationServerSide();
                $scope.getPaginationPCServerSide();
                //$modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                //$scope.fDataOC.fecha_vencimiento = null;
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }
      })  
    }
    $scope.btnAnularEntrada = function() {
      var pMensaje = '¿Realmente desea anular la orden de compra?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ordenCompraServices.sAnularOrdenCompra($scope.mySelectionGridOC).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationOCAServerSide();
                $scope.getPaginationPCServerSide();
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
    $scope.btnAprobarOC = function () {
      var pMensaje = '¿Realmente desea aprobar la orden de compra?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ordenCompraServices.sAprobarOrdenCompra($scope.mySelectionGridOC).then(function (rpta) {
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
    $scope.btnImprimir = function(idmovimiento, estado){
      var abreviatura = 'O/C';
      var arrParams = {
          titulo: 'ORDEN DE COMPRA',
          datos:{
            resultado: idmovimiento,
            salida: 'pdf',
            estado: estado,
            tituloAbv: abreviatura,
            titulo: 'ORDEN DE COMPRA'
          },
          metodo: 'php'
        }
        arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_orden_compra',
        ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnVerDetalleOC = function (fEntrada,size) { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_detalle_orden_compra',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Orden de Compra';
          $scope.fEntrada = fEntrada;
          $scope.gridOptionsDetalleOC = {
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
              { field: 'id', displayName: 'COD.', width: '5%', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCION', enableCellEdit: false, enableSorting: false },
              { field: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: false, enableSorting: false },
              { field: 'unidad_medida', displayName: 'UNID. MED.', width: '9%', enableCellEdit: false, enableSorting: false },
              { field: 'precio', displayName: 'P. UNIT', width: '9%', enableCellEdit: false, enableSorting: false },
              { field: 'descuento', displayName: 'DCTO(%)', width: '8%', enableCellEdit: false, enableSorting: false },
              { field: 'descuento_valor', displayName: 'DCTO. VALOR ', width: '10%', enableCellEdit: false, enableSorting: false },
              { field: 'igv', displayName: 'IGV', width: '8%', enableCellEdit: false, enableSorting: false },
              { field: 'importe', displayName: 'IMPORTE', width: '10%', enableCellEdit: false, enableSorting: false },
              { field: 'excluye_igv', displayName: 'INAFECTO', width: '8%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
              },
              { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
                cellTemplate:'<div class="">'+
                  '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
                  '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
                  '</div>' 
              }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          $scope.getPaginationDetalleEntradaServerSide = function() {
            var arrParams = {
              datos: fEntrada
            };
            ordenCompraServices.sListarDetalleOrdenCompra(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleOC.data = rpta.datos;
              $scope.gridOptionsDetalleOC.sumTotal = rpta.sumTotal;
              $scope.gridOptionsDetalleOC.totalItems = rpta.paginate.totalRows;
              $scope.fEntrada.detalle = rpta.datos;
              $scope.fEntrada.proveedor = rpta.proveedor;

            });
          };
          $scope.getPaginationDetalleEntradaServerSide();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnVerIngresosOC = function (fEntrada,size) { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'OrdenCompra/ver_popup_ingresos_con_orden_compra',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Ingresos realizados con la Orden de Compra';
          $scope.fEntrada = fEntrada;
          var paginationOptionsIngresosPorOrden = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsIngresosPorOrden = {
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
              { field: 'idmovimiento', displayName: 'COD.', width: '5%', enableCellEdit: false, enableSorting: false },
              { field: 'orden_compra', displayName: 'ORDEN COMPRA', width: '15%', enableCellEdit: false, enableSorting: false },
              { field: 'factura', displayName: 'Nº FACTURA.', width: '20%', enableCellEdit: false, enableSorting: false },
              { field: 'guia_remision', displayName: 'Nº GUIA REMISION.', width: '20%', enableCellEdit: false, enableSorting: false },
              { field: 'fecha_movimiento', displayName: 'FECHA DE INGRESO', width: '20%', enableCellEdit: false, enableSorting: false },
              
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsIngresosPorOrden.pageNumber = newPage;
                paginationOptionsIngresosPorOrden.pageSize = pageSize;
                paginationOptionsIngresosPorOrden.firstRow = (paginationOptionsIngresosPorOrden.pageNumber - 1) * paginationOptionsIngresosPorOrden.pageSize;
                $scope.getPaginationIngresosPorOCServerSide();
              });
            }
          };
          $scope.getPaginationIngresosPorOCServerSide = function() {
            var arrParams = {
              paginate : paginationOptionsIngresosPorOrden,
              datos: fEntrada
            };
            ordenCompraServices.sListarIngresosOrdenCompra(arrParams).then(function (rpta) {
              $scope.gridOptionsIngresosPorOrden.data = rpta.datos;
              //$scope.gridOptionsIngresosPorOrden.sumTotal = rpta.sumTotal;
              $scope.gridOptionsIngresosPorOrden.totalItems = rpta.paginate.totalRows;
              $scope.fEntrada.detalle = rpta.datos;
              $scope.fEntrada.proveedor = rpta.proveedor;

            });
          };
          $scope.getPaginationIngresosPorOCServerSide();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    /************** GRID DE ORDENES ANULADAS **************/
    var paginationOptionsOCA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsOCAnulados = {
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
        { field: 'orden_compra', name: 'orden_compra', displayName: 'Nº ORDEN', width: '10%',},
        { field: 'razon_social', name: 'razon_social', displayName: 'PROVEEDOR' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE CREACIÓN', width: '12%', enableFiltering: false,sort: { direction: uiGridConstants.DESC}  },
        { field: 'fecha_anulacion', name: 'fecha_anulacion', displayName: 'FECHA DE ANULACIÓN', width: '12%', enableFiltering: false,sort: { direction: uiGridConstants.DESC}  },
        // { field: 'ruc', name: 'ruc', displayName: 'RUC', width: '18%' },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUB TOTAL', width: '9%', enableFiltering: false, },
        { field: 'igv', name: 'igv', displayName: 'IGV', width: '9%', enableFiltering: false, },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '9%', enableFiltering: false,},
        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        }

      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiAnulado = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridOCA = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridOCA = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiAnulado.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsOCA.sort = null;
            paginationOptionsOCA.sortName = null;
          } else {
            paginationOptionsOCA.sort = sortColumns[0].sort.direction;
            paginationOptionsOCA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationOCAServerSide();
        });
        $scope.gridApiAnulado.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsOCA.search = true; 
          paginationOptionsOCA.searchColumn = { 
            'orden_compra' : grid.columns[1].filters[0].term,
            'razon_social' : grid.columns[2].filters[0].term
          }
          $scope.getPaginationOCAServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsOCA.pageNumber = newPage;
          paginationOptionsOCA.pageSize = pageSize;
          paginationOptionsOCA.firstRow = (paginationOptionsOCA.pageNumber - 1) * paginationOptionsOCA.pageSize;
          $scope.getPaginationOCAServerSide();
        });
      }
    };
    paginationOptionsOCA.sortName = $scope.gridOptionsOCAnulados.columnDefs[4].name;
    $scope.getPaginationOCAServerSide = function() { 
      var arrParams = {
        paginate : paginationOptionsOCA,
        datos : $scope.fBusqueda
      };
      ordenCompraServices.sListarOrdenCompraAnulada(arrParams).then(function (rpta) {
        $scope.gridOptionsOCAnulados.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsOCAnulados.data = rpta.datos;
        $scope.gridOptionsOCAnulados.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGridOCA = [];
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
        { field: 'orden_compra', name: 'orden_compra', displayName: 'Nº ORDEN', width: '9%' },
        { field: 'proveedor', name: 'razon_social', displayName: 'PROVEEDOR', width: '16%'},
        { field: 'fecha_aprobacion', name: 'fecha_movimiento', displayName: 'FECHA DE APROBACION', width: '10%', enableFiltering: false,  sort: { direction: uiGridConstants.DESC} },
        { field: 'producto', name: 'denominacion', displayName: 'PRODUCTO' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT.', width: '8%', enableFiltering: false, },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '8%', enableFiltering: false, },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '8%', enableFiltering: false, }
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
            'm.denominacion' : grid.columns[4].filters[0].term,
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
      ordenCompraServices.sListarProductosOrdenCompra($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsProductosCompra.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsProductosCompra.data = rpta.datos;
      });
      $scope.mySelectionGridPC = [];
    };
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */ 
   
    shortcut.remove('F4');
    shortcut.add("F4",function(event) { 
      if($scope.mySelectionGridOC.length == 1 ){ 
        $scope.btnImprimir($scope.mySelectionGridOC[0].idmovimiento, $scope.mySelectionGridOC[0].estado_movimiento); 
      } 
    }); 
    shortcut.remove('F6');
    shortcut.add("F6",function() { 
        $scope.mismoCliente(); 
        $('#temporalEspecialidad').focus();
    }); 
  }])
  .service("ordenCompraServices",function($http, $q) {
    return({
        sListarOrdenesCompra: sListarOrdenesCompra,
        sListarOrdenCompraAnulada: sListarOrdenCompraAnulada,
        sListarDetalleOrdenCompra: sListarDetalleOrdenCompra,
        sListarIngresosOrdenCompra: sListarIngresosOrdenCompra,
        sListarDetalleOrdenCbo: sListarDetalleOrdenCbo,
        sListarProductosOrdenCompra : sListarProductosOrdenCompra,
        sListarUltimoPrecioMedicamento: sListarUltimoPrecioMedicamento,
        sRegistrarOrdenCompra : sRegistrarOrdenCompra,
        sEditarOrdenCompra: sEditarOrdenCompra,
        sAprobarOrdenCompra: sAprobarOrdenCompra,
        sAnularOrdenCompra : sAnularOrdenCompra,
        sAnularDetalle: sAnularDetalle,
        sListarTipoMaterialCbo : sListarTipoMaterialCbo,
        sListarOrdenCompraPorAlmacenCbo : sListarOrdenCompraPorAlmacenCbo,
        sGenerarCodigoOrden : sGenerarCodigoOrden
    });
    
    function sListarOrdenesCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_movimientos_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarOrdenCompraAnulada(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_orden_compra_anulada", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleOrdenCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_detalle_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarIngresosOrdenCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_ingresos_por_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleOrdenCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_detalle_orden_en_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosOrdenCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_productos_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarUltimoPrecioMedicamento(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_ultimo_precio_medicamento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarOrdenCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/registrar_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarOrdenCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/editar_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAprobarOrdenCompra (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/aprobar_orden_compra",  
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularOrdenCompra(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/anular_orden_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularDetalle(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/anular_detalle", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTipoMaterialCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_tipo_material_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarOrdenCompraPorAlmacenCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/lista_orden_compra_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarCodigoOrden(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"OrdenCompra/generar_codigo_orden", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });