angular.module('theme.liquidacionFarm', ['theme.core.services'])
  .controller('liquidacionFarmController', ['$scope', '$route', '$sce', 'blockUI','$interval', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ventaServices',
    'cajaActualServices', 
    'liquidacionFarmServices', 
    'empresaAdminServices',
    'cajaServices',
    'ventaFarmaciaServices',
    'tipoDocumentoServices', 
    'medioPagoServices',
    'precioServices',
    'medicamentoAlmacenServices',
    'clienteServices',
    'empresasClienteServices',
    function($scope, $sce, $route, blockUI, $interval, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ventaServices,
      cajaActualServices, 
      liquidacionFarmServices,
      empresaAdminServices,
      cajaServices,
      ventaFarmaciaServices,
      tipoDocumentoServices, 
      medioPagoServices,
      precioServices,
      medicamentoAlmacenServices,
      clienteServices,
      empresasClienteServices
    ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2"); 
    $scope.modulo = 'liquidacionFarm';
    $scope.idmodulo = 3;
    $scope.cajaAbiertaPorMiSession = false;
    $scope.fCajaAbiertaSession = null;
    $scope.fBusqueda = {};
    $scope.fBusqueda.idmodulo = $scope.idmodulo;
    $scope.mySelectionGridV = [];
    $scope.ventaNormal = true;
    liquidacionFarmServices.sObtenerParametrosConfig().then(function (rpta){
      
      if(rpta.datos.modo_venta_far == 'VP'){
        console.log(rpta.datos,' (Venta por Pedido)');
        $scope.ventaNormal = false;
      }
    });
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringVA = function(){ 
      $scope.gridOptionsVentasAnuladas.enableFiltering = !$scope.gridOptionsVentasAnuladas.enableFiltering;
      $scope.gridApiAnulado.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringEE = function(){
      $scope.gridOptionsVentasEnEspera.enableFiltering = !$scope.gridOptionsVentasEnEspera.enableFiltering;
      $scope.gridApiEnEspera.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringPV = function(){
      $scope.gridOptionsProductosVenta.enableFiltering = !$scope.gridOptionsProductosVenta.enableFiltering;
      $scope.gridApiProducto.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringRI = function(){ 
      $scope.gridOptionsVentasImpresion.enableFiltering = !$scope.gridOptionsVentasImpresion.enableFiltering;
      $scope.gridApiImpresionesVenta.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { // console.log(rpta); 
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      //$scope.listaSedeEmpresaAdmin.splice(0,0,{ id : 'all', descripcion:'-- Todos --'});
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rptaDet) { 
        if(rptaDet.flag === 1){
          $scope.listaCajaMaster = rptaDet.datos;
        }
        
        if( rptaDet.flag === 1 && angular.isObject(rptaDet.cajaactual) ){ 
          $scope.fBusqueda.cajamaster = rptaDet.cajaactual.idcajamaster; 
          $scope.getPaginationPedServerSide(); // pedidos
          $scope.getPaginationServerSide(); // ventas 
          //$scope.getPaginationVAServerSide(); // anuladas
          //$scope.getPaginationEEServerSide(); // en espera 
          //$scope.getPaginationPVServerSide();  // productos 
          //$scope.getPaginationRIServerSide(); // impresiones
          $scope.cajaAbiertaPorMiSession = true;
          $scope.fCajaAbiertaSession = rptaDet.cajaactual;
          pinesNotifications.notify({ title: 'Información', text: 'Su caja está abierta.', type: 'success', delay: 4500 });
        }else{
          pinesNotifications.notify({ title: 'Información', text: 'Ud. no tiene ninguna caja abierta.', type: 'warning', delay: 4500 });
        }
        // if(rpta.flag === 0){

        // }
      });
    });
    $scope.onChangeEmpresaSede = function () { 
      cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rpta) { 
        $scope.listaCajaMaster = rpta.datos;
        if( rpta.flag === 0 ){ 
          $scope.listaCajaMaster.push( { id: '', descripcion: 'No se encontraron cajas abiertas.' } );

        }
        $scope.fBusqueda.cajamaster = $scope.listaCajaMaster[0].id;
        $scope.getPaginationPedServerSide(); // pedidos
        $scope.getPaginationServerSide(); // ventas 
        $scope.getPaginationVAServerSide(); // anuladas 
        $scope.getPaginationEEServerSide(); // en espera 
        $scope.getPaginationPVServerSide(); // productos 
        $scope.getPaginationRIServerSide(); // impresiones
      });
    }

    /* GRID DE APROBACION DE PEDIDOS EN CAJA*/
    $scope.mySelectionGridPed = [];
    var paginationOptionsPed = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasPedidos = {
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
        { field: 'orden_pedido', name: 'orden_pedido', displayName: 'N° ORDEN', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE' },
        // { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%' },
        // { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%' },
        //{ field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'vendedor', name: 'username', displayName: 'VENDEDOR', width: '15%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%' },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiPed = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridPed = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridPed = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiPed.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsPed.sort = null;
            paginationOptionsPed.sortName = null;
          } else {
            paginationOptionsPed.sort = sortColumns[0].sort.direction;
            paginationOptionsPed.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPedServerSide();
        });
        $scope.gridApiPed.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsPed.search = true;
          paginationOptionsPed.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            
            'descripcion_med' : grid.columns[6].filters[0].term,
            'sub_total' : grid.columns[7].filters[0].term,
            'total_igv' : grid.columns[8].filters[0].term,
            'total_a_pagar' : grid.columns[9].filters[0].term
          }
          $scope.getPaginationPedServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPed.pageNumber = newPage;
          paginationOptionsPed.pageSize = pageSize;
          paginationOptionsPed.firstRow = (paginationOptionsPed.pageNumber - 1) * paginationOptionsPed.pageSize;
          $scope.getPaginationPedServerSide();
        });
      }
    };
    paginationOptionsPed.sortName = $scope.gridOptionsVentasPedidos.columnDefs[0].name;
    $scope.getPaginationPedServerSide = function() {
      var arrParams = { 
        paginate : paginationOptionsPed,
        datos : $scope.fBusqueda
      };
      liquidacionFarmServices.sListarPedidosVentas(arrParams).then(function (rpta) {
        $scope.gridOptionsVentasPedidos.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasPedidos.data = rpta.datos;
      });
      $scope.mySelectionGridPed = [];
    };
    
    $scope.btnProcesarVenta = function (fVenta,size) { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_procesar_venta',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Procesar Venta';
          $scope.fProVenta = {};
          $scope.fProVenta = angular.copy(fVenta);

          $scope.isRegisterSuccess = false;
          $scope.cajaAbiertaPorMiSession = false;
          $scope.fCajaAbiertaSession = null;
          $scope.antesDeImprimir = true;
          
          cajaServices.sGetFarmaciaCajaActualUsuario().then(function (rpta) { 
            if(rpta.flag === 1) {
              $scope.cajaAbiertaPorMiSession = true;
              $scope.fCajaAbiertaSession = rpta.datos; 
            }
          });
          
          $scope.fProVenta.idventaregister = null;
          $scope.fProVenta.aleasDocumento = 'TICKET';
          $scope.fProVenta.ticket = '[ ............... ]';
          $scope.fProVenta.temporal = {
            //especialidad : null,
            producto: null,
            cliente: null
          };
          $scope.fProVenta.temporal.cantidad = 1;
          var paginationOptionsDetalleVenta = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };

          $scope.gridOptionsDetalleVenta = {
            //minRowsToShow: 6,
            paginationPageSize: 10,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            data: null,
            rowHeight: 30,
            enableCellEditOnFocus: true,
            multiSelect: false,
            showColumnFooter: true,
            showGridFooter: true,
            columnDefs: [
              { field: 'id', displayName: 'Item', width: '5%', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCIÓN', enableCellEdit: false, enableSorting: false },
              { field: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false },
              { field: 'precio_unitario', displayName: 'PRECIO', width: '9%', enableCellEdit: false, enableSorting: false },
              { field: 'valor', displayName: 'VALOR', width: '9%', aggregationType: uiGridConstants.aggregationTypes.sum, enableCellEdit: false, enableSorting: false },
              { field: 'descuento', displayName: 'DESCUENTO', width: '9%', aggregationType: uiGridConstants.aggregationTypes.sum, 
                  enableCellEdit: true,
                  cellEditableCondition: function ($scope) {
                    console.log('idtipocliente: ', $scope.row.entity.idtipocliente);
                    if($scope.row.entity.idtipocliente){
                      return false;
                    }else{
                      return true; // editable solo si el cliente no tiene asignado un tipocliente
                    }
                  },
                  cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idtipocliente){
                      return false;
                    }else{
                      return 'ui-editCell'; // se aplica colorcito amarillo solo si es editable
                    }
                  },
                  enableSorting: false
              },
              { field: 'total', displayName: 'TOTAL', width: '12%', enableCellEdit: false, enableSorting: false }, 
              { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApiCombo) { 
              $scope.gridApiCombo = gridApiCombo; 
              gridApiCombo.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){
                rowEntity.column = colDef.field;
                //console.log($scope.isRegisterSuccess);
                if($scope.isRegisterSuccess){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  var pMessage = 'No se puede editar. La venta ya fue guardada';
                  if(rowEntity.column == 'cantidad'){
                    rowEntity.cantidad = oldValue;
                  }else{
                    rowEntity.descuento = oldValue;
                  }
                  pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3000 });
                  console.log('Ya se registró. Por lo tanto no se puede editar');

                }else{
                  console.log('Si se puede eliminar');

                  if(rowEntity.column == 'cantidad' && newValue != oldValue){
                    if( !(rowEntity.cantidad >= 1) ){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      rowEntity.cantidad = oldValue;
                      pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                      return;
                    }
                    if ( !(rowEntity.cantidad % 1 == 0) ) {
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      rowEntity.cantidad = oldValue;
                      pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser un numero entero', type: pType, delay: 3500 });
                      return;
                    }
                  }
                  else if(rowEntity.column == 'descuento' && newValue != oldValue){
                    if( !(rowEntity.descuento >= 0) ){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      rowEntity.descuento = oldValue;
                      pinesNotifications.notify({ title: pTitle, text: 'El descuento no es válido', type: pType, delay: 3500 });
                      return;
                    }
                  }
                  $scope.calcularTotales(); 
                  $scope.calcularVuelto();
                  console.log($scope.fProventa, '$scope.fProventa');
                  var arrParams = {
                    'idmovimiento': rowEntity.idmovimiento,
                    'idmedicamento': rowEntity.id,
                    'idmedicamentoalmacen': rowEntity.idmedicamentoalmacen,
                    'cantidad': rowEntity.cantidad,
                    'descuento': rowEntity.descuento,
                    'total_detalle': rowEntity.total,
                    'total_a_pagar': $scope.fProVenta.total,
                    'sub_total': $scope.fProVenta.subtotal,
                    'total_igv': $scope.fProVenta.igv,
                    'estemporal' : $scope.fProventa.estemporal  // SE AGREGA EL VALOR TEMPORAL
                  }
                  liquidacionFarmServices.sActualizarDetalleVentaPedido(arrParams).then(function (rpta) { 
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success'; 
                      $scope.getPaginationPedServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      if(rowEntity.column == 'cantidad'){
                        rowEntity.cantidad = oldValue;
                      }else{
                        rowEntity.descuento = oldValue;
                      }
                    }else{
                      alert('Algo salió mal...');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                }
                
                $scope.$apply();
              }); 
            }
          };
          $scope.getTableHeight = function() {
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height
             var footerHeight = 30; // your footer height 
             return {
                height: ($scope.gridOptionsDetalleVenta.data.length * rowHeight + headerHeight + footerHeight + 60) + "px"
             };
          };
          //paginationOptionsDetalleVenta.sortName = $scope.gridOptionsDetalleVenta.columnDefs[0].name;
          $scope.cargarPedido = function (){
            console.log('Cargando pedido...');
            $scope.fProVenta.orden_pedido = $scope.fProVenta.prefijo + '-' + ("000" + $scope.fProVenta.correlativo).slice (-4);
            console.log($scope.fProVenta.orden_pedido);

            var arrParams = {
                'orden_pedido': $scope.fProVenta.orden_pedido
               
            }
            liquidacionFarmServices.sListarPedido(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.fProVenta.cliente = rpta.datos.cliente;
                $scope.fProVenta.fecha_movimiento = rpta.datos.fecha_movimiento;
                $scope.fProVenta.id = rpta.datos.idmovimiento;

                $scope.cargarDetalle();
                $scope.generarNumOrden();
                $scope.generarCodigoTicket();
                //$scope.fProVenta = rpta.datos;
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          $scope.cargarDetalle = function() {
            //$scope.$parent.blockUI.start();
            var arrParams = {
              paginate: paginationOptionsDetalleVenta,
              datos: $scope.fProVenta
            };
            //console.log($scope.mySelectionGridEE[0]);
            liquidacionFarmServices.sListarDetallePedido(arrParams).then(function (rpta) {
              
              $scope.gridOptionsDetalleVenta.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleVenta.data = rpta.datos;

              angular.forEach($scope.gridOptionsDetalleVenta.data,function (value, key) {
                $scope.gridOptionsDetalleVenta.data[key].precio_unitario = parseFloat( (value.precio_unitario).slice(4).replace(/,/g,'') ).toFixed(2);
                $scope.gridOptionsDetalleVenta.data[key].valor = $scope.gridOptionsDetalleVenta.data[key].precio_unitario * $scope.gridOptionsDetalleVenta.data[key].cantidad;
                $scope.gridOptionsDetalleVenta.data[key].total = parseFloat( (value.total).slice(4).replace(/,/g,'') ).toFixed(2);
                $scope.gridOptionsDetalleVenta.data[key].descuento = parseFloat( (value.descuento).slice(4).replace(/,/g,'') ).toFixed(2);
                
                
              });
              // console.log('rpta.datos');
              // console.log($scope.gridOptionsDetalleVenta.data);
              $scope.calcularTotales();
              //$scope.$parent.blockUI.stop();
            });
            //$scope.mySelectionDetalleVentaGrid = [];
          };
          
          tipoDocumentoServices.sListarTipoDocumentoVentaCbo().then(function (rpta) {
            $scope.listaTipoDocumento = rpta.datos;
            $scope.listaTipoDocumento.splice(0,0,{ id : '0', descripcion:'--Seleccione Tipo de Documento--'});
            $scope.fProVenta.idtipodocumento = $scope.listaTipoDocumento[1].id; // 
            $scope.generarCodigoTicket();
          }); 
          medioPagoServices.sListarmedioPagoVentaCbo().then(function (rptaMaster) {
            $scope.listaMedioPago = rptaMaster.datos;
            //$scope.listaMedioPago.splice(0,0,{ id : '', descripcion:'--Seleccione Medio de Pago--'});
            $scope.fProVenta.idmediopago = $scope.listaMedioPago[0].id;
            precioServices.sListarPrecioCbo().then(function (rpta) { 
              //$scope.listaPrecios = rpta.datos; 
              $scope.listaPrecios = [];
              if( $scope.fProVenta.idmediopago == 1 ){ // SI ES AL CONTADO, SACAR TARJETA 
                angular.forEach(rpta.datos,function (value,key) {
                  if( value.id != 1 ){ // SI ES DIFERENTE A TIPO DE PRECIO CON TARJETA VISA 
                    $scope.listaPrecios.push(value);
                  }
                });
                $scope.listaPrecios.splice(0,0,{ id : '', descripcion:'PRECIO POR DEFECTO', tipo_precio:'0', porcentaje:'0' });
              }
              if( $scope.fProVenta.idmediopago == 2 ){ // SI ES TARJETA VISA 
                angular.forEach(rpta.datos,function (value,key) {
                  if( value.id == 1 ){ // SI ES IGUAL A TIPO DE PRECIO CON TARJETA VISA
                    $scope.listaPrecios.push(value);
                  }
                })
              }
              
              $scope.fProVenta.tipoPrecio = $scope.listaPrecios[0]; 
              $scope.cargarDetalle();
            });
          });
          $scope.onChangeMedioPago = function () { 
            precioServices.sListarPrecioCbo().then(function (rpta) { 
              //$scope.listaPrecios = rpta.datos; 
              $scope.listaPrecios = [];
              if( $scope.fProVenta.idmediopago == 1 ){ // SI ES AL CONTADO, SACAR TARJETA 
                angular.forEach(rpta.datos,function (value,key) {
                  if( value.id != 1 ){ // // SI ES DIFERENTE A TIPO DE PRECIO CON TARJETA VISA 
                    $scope.listaPrecios.push(value);
                  }
                });
                $scope.listaPrecios.splice(0,0,{ id : '', descripcion:'PRECIO POR DEFECTO', tipo_precio:'0', porcentaje:'0' });
              }
              if( $scope.fProVenta.idmediopago == 2 ){ // SI ES TARJETA VISA 
                angular.forEach(rpta.datos,function (value,key) { 
                  console.log(value);
                  if( value.id == 1 ){ // SI ES IGUAL A TIPO DE PRECIO CON TARJETA VISA 
                    $scope.listaPrecios.push(value);
                  }
                });
              }
              $scope.fProVenta.tipoPrecio = $scope.listaPrecios[0];
            });
          }
          $scope.generarNumOrden = function () { 
            ventaFarmaciaServices.sGenerarCodigoOrden().then(function (rpta) { 
              $scope.fProVenta.orden = rpta.codigo_orden; 
              $scope.fProVenta.idcaja = rpta.idcaja;
              $scope.fProVenta.idcajamaster = rpta.idcajamaster;
            });
          } 
          $scope.generarNumOrden();
          $scope.generarCodigoTicket = function () { 
            if( $scope.fProVenta.idtipodocumento ){ 
              //console.log($scope.fProVenta.idtipodocumento); 
              $scope.fProVenta.idmodulo = 3; // FARMACIA 
              ventaServices.sGenerarCodigoTicket($scope.fProVenta).then(function (rpta) {
                $scope.fProVenta.ticket = rpta.ticket;
                $scope.fProVenta.serie = rpta.serie;
                $scope.fProVenta.numero_serie = rpta.numero_serie;
                if( $scope.fProVenta.idtipodocumento == '1' ){ // BOLETA 
                  $scope.fProVenta.aleasDocumento = 'TICKET';
                }
                if( $scope.fProVenta.idtipodocumento == '2' ){ // FACTURA  
                  $scope.fProVenta.aleasDocumento = 'FACT.';
                  // alert('Cargara Las Empresas...')
                 $scope.btnBuscarEmpresaCliente('lg');
                }
                if( $scope.fProVenta.idtipodocumento == '6' ){ // RECIBO 
                  $scope.fProVenta.aleasDocumento = 'REC.';
                }
                if( $scope.fProVenta.idtipodocumento == '3' ){ // OPERACION  
                  $scope.fProVenta.aleasDocumento = 'OPE.';
                }
              });
            }
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            
            if($scope.isRegisterSuccess){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
              var pMessage = 'Ya no se puede eliminar ningun producto.';
              pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3000 });
              console.log('Ya se registró. Por lo tanto no se puede eliminar');

            }else{
              console.log('Si se puede eliminar');
              var index = $scope.gridOptionsDetalleVenta.data.indexOf(row.entity); 
              
              if($scope.gridOptionsDetalleVenta.data.length >= 2){ // La grilla no puede quedar vacia 
                $scope.gridOptionsDetalleVenta.data.splice(index,1);
                $scope.calcularTotales();
                $scope.calcularVuelto();
                var arrParams = {
                  'idmovimiento': row.entity.idmovimiento,
                  'idmedicamento': row.entity.id,
                  'total_a_pagar': $scope.fProVenta.total,
                  'sub_total': $scope.fProVenta.subtotal,
                  'total_igv': $scope.fProVenta.igv
                }
                liquidacionFarmServices.sAnularDetalleVentaPedido(arrParams).then(function (rpta) { 
                  if(rpta.flag == 1){
                    var pTitle = 'OK!';
                    var pType = 'success'; 
                    $scope.getPaginationPedServerSide();
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Algo salió mal...');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });
              }else{
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                var pMessage = 'No se puede eliminar el producto, debe haber al menos uno.'
                pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3000 });
              }
              $scope.getPaginationPedServerSide();
            }
            /**/
          }

          $scope.calcularTotales = function () {
            console.log('Calculando totales...');
            var totales = 0; 
            if( !$scope.fProVenta.tipoPrecio.tipo_precio ) { 
              $scope.fProVenta.tipoPrecio = $scope.listaPrecios[0];
            }
            angular.forEach($scope.gridOptionsDetalleVenta.data,function (value, key) { 
              var porcentaje = 1;
              if($scope.fProVenta.tipoPrecio.tipo_precio !== '0') { 
                // console.log($scope.fProVenta.precio); 
                porcentaje = ( parseFloat($scope.fProVenta.tipoPrecio.porcentaje) / 100 ) + 1;
              }

              $scope.gridOptionsDetalleVenta.data[key].precio = (parseFloat(value.precio_unitario) * porcentaje ).toFixed(2); 
              $scope.gridOptionsDetalleVenta.data[key].valor = ($scope.gridOptionsDetalleVenta.data[key].precio * parseFloat($scope.gridOptionsDetalleVenta.data[key].cantidad)).toFixed(2);
              $scope.gridOptionsDetalleVenta.data[key].total = ( parseFloat($scope.gridOptionsDetalleVenta.data[key].valor) - parseFloat($scope.gridOptionsDetalleVenta.data[key].descuento || 0) ).toFixed(2);
              totales += parseFloat($scope.gridOptionsDetalleVenta.data[key].total);
            });
            $scope.fProVenta.total = totales.toFixed(2);
            $scope.fProVenta.igv = ( totales - (totales / 1.18) ).toFixed(2);
            $scope.fProVenta.subtotal = ($scope.fProVenta.total - $scope.fProVenta.igv).toFixed(2);
          }
          $scope.calcularVuelto = function (){
            console.log('Calculando vuelto...');
            if($scope.fProVenta.total !== 0){
              vuelto= parseFloat($scope.fProVenta.entrega) - parseFloat($scope.fProVenta.total); 
              $scope.fProVenta.vuelto = vuelto; 
            }

          }
          $scope.limpiarCampos = function (){ 
            $scope.fProVenta.cliente = {};
          }
          // ========================> BUSCAR EMPRESA DEL CLIENTE - SOLO PARA FACTURAS 
          $scope.btnBuscarEmpresaCliente = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'cliente/ver_popup_busqueda_empresa_cliente',
              size: size || '',
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                $scope.titleForm = 'Búsqueda de Empresa - cliente';
                var paginationOptionsClienteEnVentas = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.mySelectionEmpresaClienteGrid = [];
                
                $scope.gridOptionsEmpresaClienteBusqueda = {
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
                    { field: 'id', name: 'idempresacliente', displayName: 'ID', maxWidth: 50, visible:false  },
                    { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC', maxWidth: 120, sort: { direction: uiGridConstants.ASC}},
                    { field: 'descripcion', name: 'descripcion', displayName: 'Empresa' } 
                  ],
                  onRegisterApi: function(gridApi) { // gridComboOptions
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionEmpresaClienteGrid = gridApi.selection.getSelectedRows();
                      $scope.fProVenta.empresa = $scope.mySelectionEmpresaClienteGrid[0]; //console.log($scope.fProVenta.cliente);
                      $scope.fProVenta.ruc = $scope.mySelectionEmpresaClienteGrid[0].ruc_empresa;
                      $modalInstance.dismiss('cancel');
                    });

                    $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                      if (sortColumns.length == 0) {
                        paginationOptionsClienteEnVentas.sort = null;
                        paginationOptionsClienteEnVentas.sortName = null;
                      } else {
                        paginationOptionsClienteEnVentas.sort = sortColumns[0].sort.direction;
                        paginationOptionsClienteEnVentas.sortName = sortColumns[0].name;
                      }
                      $scope.getPaginationClienteEnVentaServerSide();
                    });
                    gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                      paginationOptionsClienteEnVentas.pageNumber = newPage;
                      paginationOptionsClienteEnVentas.pageSize = pageSize;
                      paginationOptionsClienteEnVentas.firstRow = (paginationOptionsClienteEnVentas.pageNumber - 1) * paginationOptionsClienteEnVentas.pageSize;
                      $scope.getPaginationClienteEnVentaServerSide();
                    });
                    $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                      var grid = this.grid;
                      paginationOptionsClienteEnVentas.search = true;
                      // console.log(grid.columns);
                      // console.log(grid.columns[1].filters[0].term);
                      paginationOptionsClienteEnVentas.searchColumn = { 
                        'idempresacliente' : grid.columns[1].filters[0].term,
                        'ruc_empresa' : grid.columns[2].filters[0].term,
                        'descripcion' : grid.columns[3].filters[0].term,
                        
                      }
                      $scope.getPaginationClienteEnVentaServerSide();
                    });
                  }
                };
                $scope.navegateToCellListaBusquedaCliente = function( rowIndex, colIndex ) { 
                  console.log(rowIndex, colIndex);
                  $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsEmpresaClienteBusqueda.data[rowIndex], $scope.gridOptionsEmpresaClienteBusqueda.columnDefs[colIndex]); 
                  
                };
                paginationOptionsClienteEnVentas.sortName = $scope.gridOptionsEmpresaClienteBusqueda.columnDefs[0].name;
                $scope.getPaginationClienteEnVentaServerSide = function() {
                  //$scope.$parent.blockUI.start();
                  $scope.datosGrid = {
                    paginate : paginationOptionsClienteEnVentas
                  };
                  clienteServices.sListarEmpresasCliente($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsEmpresaClienteBusqueda.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsEmpresaClienteBusqueda.data = rpta.datos;
                    //$scope.$parent.blockUI.stop();
                  });
                  $scope.mySelectionEmpresaClienteGrid = [];
                };
                $scope.getPaginationClienteEnVentaServerSide();

                shortcut.add("down",function() { 

                  $scope.navegateToCellListaBusquedaCliente(0,0);
                });
                $scope.btnNuevo = function (){
                  $modalInstance.dismiss('cancel');
                  $scope.btnNuevaEmpresa('lg');
                }
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
              }
            });
          }
          // FIN DE BUSQUEDA EMPRESA DEL CLIENTE
          $scope.btnNuevaEmpresa = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'empresaCliente/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              controller: function ($scope, $modalInstance, arrToModal) {
                
                $scope.accion = 'reg';
                //$scope.getPaginationClienteEnVentaServerSide = getPaginationClienteEnVentaServerSide;
                $scope.fData = {};
                $scope.fProVenta = arrToModal.fVenta;
                $scope.fProVenta.empresa = {};
                $scope.titleForm = 'Registro de empresa';
                
                /* AUTOCOMPLETE EMPRESAS */ 
                $scope.getEmpresasAutocomplete = function(val) { 
                  var params = {
                    search: val,
                    sensor: false
                  }
                  return empresasClienteServices.sListarEmpresasCbo(params).then(function(rpta) {
                    var empresas = rpta.datos.map(function(e) {
                      return e.descripcion;
                    });
                    return empresas;
                  });
                };
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () { 
                  empresasClienteServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $modalInstance.dismiss('cancel');
                      $scope.fProVenta.ruc = rpta.ruc;
                      $scope.fProVenta.empresa.id = rpta.idempresacliente;
                      console.log($scope.fProVenta);
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
                
              }, 
              resolve: {
                 arrToModal: function() {
                  return {
                    fVenta : $scope.fProVenta
                  }
                }
              }
            });
          }
          // =========================

          $scope.grabar = function (param) { 
            var pParam = param || false; 
            $scope.fProVenta.detalle = $scope.gridOptionsDetalleVenta.data;

            if( $scope.fProVenta.detalle.length < 1 ){ 
              $('#temporalProducto').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 2000 }); 
              return false; 
            }

            // if($scope.fProVenta.estemporal){  // SI ES TEMPORAL
            //   liquidacionFarmServices.sRegistrarVentaPedidoTemporal($scope.fProVenta).then(function (rpta) { 
            //     if(rpta.flag == 1){
            //       pTitle = 'OK!';
            //       pType = 'success'; 
            //       $scope.isRegisterSuccess = true; 
            //       $scope.fProVenta.idventaregister = rpta.idventaregister;
            //       $scope.fProVenta.temporal.producto = null;
            //       $scope.fProVenta.temporal.precio = null;
            //       $scope.fProVenta.temporal.cantidad = null;
            //       $scope.fProVenta.temporal.descuento = null;
            //       $scope.getPaginationPedServerSide(); // pedidos
            //       $scope.getPaginationServerSide(); // ventas 
            //       $scope.getPaginationEEServerSide(); // en espera 
            //       $scope.getPaginationPVServerSide();  // productos 
            //     }else if(rpta.flag == 0){
            //       var pTitle = 'Error!';
            //       var pType = 'danger';
            //     }else{
            //       alert('Algo salió mal...');
            //     }
            //     pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            //   });
            // }
            // else{    // SI NO ES TEMPORAL
              liquidacionFarmServices.sRegistrarVentaPedido($scope.fProVenta).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success'; 
                  $scope.isRegisterSuccess = true; 
                  $scope.fProVenta.idventaregister = rpta.idventaregister;
                  $scope.fProVenta.temporal.producto = null;
                  $scope.fProVenta.temporal.precio = null;
                  $scope.fProVenta.temporal.cantidad = null;
                  $scope.fProVenta.temporal.descuento = null;
                  $scope.getPaginationPedServerSide(); // pedidos
                  $scope.getPaginationServerSide(); // ventas 
                  $scope.getPaginationEEServerSide(); // en espera 
                  $scope.getPaginationPVServerSide();  // productos 
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Algo salió mal...');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              });
            // }


          }
          $scope.imprimir = function () { 
            console.log('fVenta');
            console.log($scope.fProVenta);
            if( $scope.fProVenta.idventaregister  ){
              
              var arrParams = {
                'id': $scope.fProVenta.idventaregister
              }
              ventaFarmaciaServices.sImprimirTicketVenta(arrParams).then(function (rpta) { 
                if(rpta.flag == 1){
                  var printContents = rpta.html;
                  var popupWin = window.open('', 'windowName', 'width=500,height=500');
                  popupWin.document.open()
                  popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
                  popupWin.document.close();
                }else { 
                  if(rpta.flag == 0) { // ALGO SALIÓ MAL
                    var pTitle = 'Error';
                    var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                    var pType = 'warning';
                  }
                  if(rpta.flag == 3) { // FALTA APROBAR, ESTÁ EN ESPERA.
                    var pTitle = 'Advertencia';
                    var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
                    var pType = 'warning';
                  }
                  pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
                }
              });
            }else{
              pinesNotifications.notify({ title: 'Advertencia', text: 'No seleccionó ninguna venta. Busque la venta desde Caja Actual.', type: 'warning', delay: 3500 });
            } 
            $scope.antesDeImprimir = false;

            
          }
          $scope.nuevo = function () { 
            
            setTimeout(function() {
              
              $scope.fProVenta.cliente = null;
              $scope.fProVenta.fecha_movimiento = null;
              $scope.fProVenta.id = null;
              $scope.gridOptionsDetalleVenta.data = [];
              $scope.fProVenta.correlativo = null;
              $scope.isRegisterSuccess = false; // console.log($('#temporalProducto'));
              $('#correlativo').focus();
            },400);
            console.log('nuevos');
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          /* ============================ */
          /* ATAJOS DE TECLADO NAVEGACION */
          /* ============================ */ 
          shortcut.remove('F2');
          shortcut.add("F2",function($event) { 
            $scope.grabar(); 
          }); 
          shortcut.remove('F3');
          shortcut.add("F3",function(event) { console.log('click me');
            $scope.nuevo(); 
          });
          shortcut.remove('F4');
          shortcut.add("F4",function(event) { 
            if($scope.isRegisterSuccess == true){ 
              $scope.imprimir(); 
            } 
          }); 
          shortcut.remove('F6');
          
          shortcut.remove('F8');
        }
      });
    }
    $scope.btnAnularPedido = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          liquidacionFarmServices.sAnularPedidoVenta($scope.mySelectionGridPed).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationPedServerSide();
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          }); 
        }
      });
    }

    $scope.pagoMixto = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_multi_pago',
        size: 'sm',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'Formulario Multi Pago';
          $scope.fDataTemporal = {};

           var paginationOptionsDetalleVenta = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsDetPagoMixto = {
            paginationPageSize: 10,
            useExternalPagination: false,
            useExternalSorting: false,
            useExternalFiltering : false,
            enableGridMenu: false,
            enableFiltering: false,
            minRowsToShow: 4,
            enableCellEditOnFocus: true,
            data: [],
            columnDefs: [
              { field: 'mediopago', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', visible:true },
              { field: 'monto', name: 'monto', displayName: 'MONTO', nableCellEdit: true, cellClass:'ui-editCell'  },
              
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field;
                
                if(rowEntity.column == 'monto'){
                  var index = $scope.gridOptionsDetPagoMixto.data.indexOf(rowEntity);
                  angular.forEach($scope.gridOptionsDetPagoMixto.data,function (value, key) { 
                    if( key != index ){
                      console.log('sel ', $scope.mySelectionGridV[0].total_sf);
                      value.monto = (parseFloat($scope.mySelectionGridV[0].total_sf) - parseFloat(newValue)).toFixed(2);
                    }
                  });
                }
                $scope.$apply();
              });

            }
          };
          $scope.getPaginationServerSideDetallePagoMixto = function() {
            $scope.datosGrid = {
              // paginate : paginationOptionsDetalleVenta,
              datos : $scope.mySelectionGridV[0]
            };
            ventaFarmaciaServices.sListarPagoMixto($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetPagoMixto.data = rpta.datos;

            });
            // $scope.mySelectionGridDetalleVenta = [];
            //$scope.fData.monto = null;
            
          };
          $scope.getPaginationServerSideDetallePagoMixto();
          $scope.guardar = function (){
            ventaFarmaciaServices.sEditarPagoMixto($scope.gridOptionsDetPagoMixto.data).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
            // $scope.fDataVenta.totalMixto = 0;
            // var contador = 0;
            // angular.forEach($scope.fDataTemporal.pagoMixto, function(val,index) {
            //   console.log('val ', val);
            //   console.log('index ', index);
            //   if( val.monto && val.monto != 0 ){
            //     $scope.fDataVenta.totalMixto += parseFloat(val.monto);
            //     contador++;
            //   }
            // });
            // var pTitle = 'Advertencia!';
            // var pType = 'warning';
            // if( parseFloat($scope.fDataVenta.total) != $scope.fDataVenta.totalMixto){
            //   var pMessage = 'Los montos no coinciden. Verifique los montos a pagar.'
            // }else if( contador < 2 ){
            //   var pMessage = 'Ingrese mas de un medio de pago mayor a cero.'
            // }else{
            //   $scope.fDataVenta.boolPagoMixto = true;
            //   $modalInstance.dismiss('cancel');
            // }
            // if(!$scope.fDataVenta.boolPagoMixto){ // si no esta correcto lanzar un aviso
            //   pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3000 });
            // }
            
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }          
        }
      });
    }
    /* GRID DE VENTAS */
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%' },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%', cellClass: 'bg-lightblue'},
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%' },
        // { field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' }, 
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%', enableFiltering: false, sort: { direction: uiGridConstants.DESC}  },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%' /*,visible: false*/ },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' }, 
        { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridV = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
          // console.log(sortColumns);
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
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'descripcion_med' : grid.columns[6].filters[0].term,
            'sub_total' : grid.columns[7].filters[0].term,
            'total_igv' : grid.columns[8].filters[0].term,
            'total_a_pagar' : grid.columns[9].filters[0].term
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
    paginationOptions.sortName = $scope.gridOptions.columnDefs[4].name;
    $scope.getPaginationServerSide = function(loader) { 
      var loader = loader || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      liquidacionFarmServices.sListarVentasCajaActual(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        $scope.gridOptions.sumTotal = rpta.sumTotal;
        if( loader ){ 
          blockUI.stop(); 
        }
      });
      $scope.mySelectionGridV = [];
      
    };
    $scope.btnSolicitudImprimirTicket = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          liquidacionFarmServices.sEnviarSolicitudImpresion($scope.mySelectionGridV).then(function (rpta) { 
            if(rpta.flag == 1){ 
              var pTitle = 'OK!'; 
              var pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            //$scope.getPaginationRIServerSide();
          }); 
        }
      });
    }
    $scope.btnImprimirTicket = function (fila) { 
      var pMensaje = '¿Realmente desea realizar la re-impresión?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ventaFarmaciaServices.sImprimirTicketVenta(fila).then(function (rpta) { 
            if(rpta.flag == 1){
              var printContents = rpta.html;
              var popupWin = window.open('', 'windowName', 'width=300,height=300');
              popupWin.document.open()
              popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
              popupWin.document.close();
            }else {
              if(rpta.flag == 0) { // ALGO SALIÓ MAL
                var pTitle = 'Error';
                var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                var pType = 'warning';
              }
              if(rpta.flag == 2) { // FALTA APROBAR, ESTÁ EN ESPERA.
                var pTitle = 'Advertencia';
                var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
                var pType = 'warning';
              }
              if(rpta.flag == 3) { // YA ESTA IMPRESO, NO SE PUEDE REIMPRIMIR
                var pTitle = 'Advertencia';
                var pText = 'Ya se imprimió el ticket. Solicite la reimpresión del ticket desde su Liquidación Actual.';
                var pType = 'warning';
              }
              if(rpta.flag == 4) { // SOLICITUD DE IMPRESION EN PROCESO, EL AREA DE SISTEMAS ESTÁ EVALUANDO LA SOLICITUD.
                var pTitle = 'Información';
                var pText = 'Solicitud de reimpresión <strong> en proceso </strong>. El Área de Sistemas está evaluando su solicitud.';
                var pType = 'info';
              }
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 }); 
            }
            //$scope.getPaginationRIServerSide();
          });
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      if($scope.fSessionCI.key_group == 'key_caja_far'){
        console.log('Ingrese contraseña:');
        $uibModal.open({ 
          templateUrl: angular.patchURLCI+'usuario/ver_popup_ingreso_usuario_password',
          size: '',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Confirmación de Anulación';

            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.fData = {};
            }
            $scope.aceptar = function () {
              console.log('Se verifica contraseña');
              liquidacionFarmServices.sVerificarUsuarioDirector($scope.fData).then(function (rpta) {
                
                if(rpta.flag == 1){
                  console.log('Anulando...');
                  liquidacionFarmServices.sAnularVentaCajaActual($scope.mySelectionGridV).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success'; 
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                    }else{
                      alert('Algo salió mal...');
                    }
                    $scope.getPaginationVAServerSide();
                    $scope.getPaginationServerSide();
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                  $modalInstance.dismiss('cancel');
                }else{
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  $('#usuario').focus();
                }
              });
            }
          }
        });
        return;
      }
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          liquidacionFarmServices.sAnularVentaCajaActual($scope.mySelectionGridV).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationVAServerSide();
            $scope.getPaginationServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
    $scope.abrirCaja = function () {
      if( $scope.fCajaAbiertaSession && $scope.cajaAbiertaPorMiSession === true){ 
        pinesNotifications.notify({ title: 'Error', text: 'Ud. ya tiene una caja abierta. Presione F5 para actualizar', type: 'danger', delay: 3000 });
      }else{ 
        $uibModal.open({ 
          templateUrl: angular.patchURLCI+'caja/ver_popup_abrir_caja',
          size: '',
          scope: $scope,
          controller: function ($scope, $modalInstance) { 
            $scope.fData = {};
            $scope.titleForm = 'Apertura de Caja';
            // CAJAS MASTER 
            $scope.listaCajaMaster = [];
            var arrParams = { 
                idmodulo: 3 // hospital 
            };
            cajaServices.sListarTodasCajasMasterCbo(arrParams).then(function (rpta) { 
              $scope.listaCajaMaster = rpta.datos;
              $scope.listaCajaMaster.splice(0,0,{ id : '', descripcion:'--Seleccione Caja--'});
              
              $scope.fData.idcajamaster = $scope.listaCajaMaster[0].id;
            });
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.fData = {};
            }
            $scope.aceptar = function () { 
              var pMensaje = '¿Realmente desea abrir caja ?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){
                  $scope.fData.idmodulo = arrParams.idmodulo;
                  cajaActualServices.sAbrirCajaDeUsuarioSession($scope.fData).then(function (rpta) { 
                    if(rpta.flag == 1){ 
                      pTitle = 'OK!';
                      pType = 'success';
                      $modalInstance.dismiss('cancel');
                      $scope.$parent.reloadPage();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                    }else{
                      alert('No se pudo realizar la transacción.');
                    }
                    $scope.fData = {};
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              });
            }
          }
        }); 
      }
    }
    $scope.btnCerrarCaja = function (){
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_cerrar_caja',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          console.log('fBusqueda ', $scope.gridOptions.data[0]);
          $scope.titleForm = 'Cierre de Caja';
          $scope.fData = {};
          $scope.fData.totalCaja = $scope.gridOptions.sumTotal;
          var diferencia = 0;
          $scope.calcularDiferencia = function (){
            if($scope.fData.totalFisico == ''){
               $scope.fData.diferencia = '';
               return;
            }
            diferencia = parseFloat($scope.fData.totalFisico) - parseFloat($scope.fData.totalCaja);
            if(diferencia >= 0 ){
              $scope.fData.clase = 'text-green';
            }else{
              $scope.fData.clase = 'text-red';
            }
            $scope.fData.diferencia = diferencia.toFixed(2);
            console.log('dif ', diferencia);
          }
          $scope.aceptar = function (){
            $scope.fData.idmodulo = 3
            // var arrParams = { 
            //   datos: 3 // hospital 
            // };
            cajaActualServices.sCerrarCajaDeUsuarioSession($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $scope.$parent.reloadPage();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              // $scope.getPaginationServerSide(); // ventas 
              // $scope.getPaginationVAServerSide(); // anuladas
              // $scope.getPaginationEEServerSide(); // en espera 
              // $scope.getPaginationPVServerSide(); // productos 

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 6000 });
            })
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
          }
        }
      });
    }
    $scope.cerrarCaja = function () { 
      if( $scope.fCajaAbiertaSession && $scope.cajaAbiertaPorMiSession === true){

        var pMensaje = '¿Realmente desea cerrar la caja <strong> N° ' + $scope.fCajaAbiertaSession.numero_caja + ' </strong> ?';
        $bootbox.confirm(pMensaje, function(result) {
          if(result){ 
            var arrParams = { 
              idmodulo: 3 // hospital 
            };
            cajaActualServices.sCerrarCajaDeUsuarioSession(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $scope.$parent.reloadPage();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              // $scope.getPaginationServerSide(); // ventas 
              // $scope.getPaginationVAServerSide(); // anuladas
              // $scope.getPaginationEEServerSide(); // en espera 
              // $scope.getPaginationPVServerSide(); // productos 

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 6000 });
            }); 
          }
        });
      }else{
        pinesNotifications.notify({ title: 'Error', text: 'Ud. no tiene ninguna caja abierta. Presione F5 para actualizar', type: 'danger', delay: 3000 });
      }
    }
    /* GRID DE VENTAS ANULADAS*/
    var paginationOptionsVA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasAnuladas = {
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%' },
        //{ field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%', enableFiltering: false  },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' },
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
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiAnulado.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsVA.sort = null;
            paginationOptionsVA.sortName = null;
          } else {
            paginationOptionsVA.sort = sortColumns[0].sort.direction;
            paginationOptionsVA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationVAServerSide();
        });
        $scope.gridApiAnulado.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsVA.search = true; 
          paginationOptionsVA.searchColumn = { 
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
          $scope.getPaginationVAServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsVA.pageNumber = newPage;
          paginationOptionsVA.pageSize = pageSize;
          paginationOptionsVA.firstRow = (paginationOptionsVA.pageNumber - 1) * paginationOptionsVA.pageSize;
          $scope.getPaginationVAServerSide();
        });
      }
    };
    paginationOptionsVA.sortName = $scope.gridOptionsVentasAnuladas.columnDefs[0].name;
    $scope.getPaginationVAServerSide = function(loader) { 
      var loader = loader || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptionsVA,
        datos : $scope.fBusqueda
      };
      liquidacionFarmServices.sListarVentasAnuladosCajaActual(arrParams).then(function (rpta) {
        $scope.gridOptionsVentasAnuladas.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasAnuladas.data = rpta.datos;
        $scope.gridOptionsVentasAnuladas.sumTotal = rpta.sumTotal;
        if( loader ){ 
          blockUI.stop(); 
        }
      });
      $scope.mySelectionGrid = [];
    };

    /* GRID DE VENTAS EN ESPERA*/
    $scope.mySelectionGridEE = [];
    var paginationOptionsEE = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasEnEspera = {
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%' },
        //{ field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%' },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'ESTADO', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiEnEspera = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridEE = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridEE = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiEnEspera.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsEE.sort = null;
            paginationOptionsEE.sortName = null;
          } else {
            paginationOptionsEE.sort = sortColumns[0].sort.direction;
            paginationOptionsEE.sortName = sortColumns[0].name;
          }
          $scope.getPaginationEEServerSide();
        });
        $scope.gridApiEnEspera.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsEE.search = true; 
          paginationOptionsEE.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            
            'descripcion_med' : grid.columns[6].filters[0].term,
            'sub_total' : grid.columns[7].filters[0].term,
            'total_igv' : grid.columns[8].filters[0].term,
            'total_a_pagar' : grid.columns[9].filters[0].term
          }
          $scope.getPaginationEEServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsEE.pageNumber = newPage;
          paginationOptionsEE.pageSize = pageSize;
          paginationOptionsEE.firstRow = (paginationOptionsEE.pageNumber - 1) * paginationOptionsEE.pageSize;
          $scope.getPaginationEEServerSide();
        });
      }
    };
    paginationOptionsEE.sortName = $scope.gridOptionsVentasEnEspera.columnDefs[0].name;
    $scope.getPaginationEEServerSide = function(loader) { 
      var loader = loader || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = { 
        paginate : paginationOptionsEE,
        datos : $scope.fBusqueda
      };
      liquidacionFarmServices.sListarVentasConDescuentoCajaActual(arrParams).then(function (rpta) {
        $scope.gridOptionsVentasEnEspera.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasEnEspera.data = rpta.datos;
        if( loader ){ 
          blockUI.stop(); 
        }
      });
      $scope.mySelectionGridEE = [];
    };
    $scope.btnAprobarVenta = function (mensaje) { 
      // console.log('aprobar');
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          liquidacionFarmServices.sAprobarVentaDescuento($scope.mySelectionGridEE).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){ // 
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationEEServerSide();
            $scope.getPaginationServerSide();
            $scope.getPaginationPedServerSide();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    } 
    $scope.btnVerDetalleVenta = function (fVenta,size) { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_detalle_venta',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Venta';
          $scope.fVenta = fVenta;
          var paginationOptionsDetalleVenta = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleVentaGrid = [];
          $scope.btnToggleFiltering = function(){
            $scope.gridOptionsDetalleVenta.enableFiltering = !$scope.gridOptionsDetalleVenta.enableFiltering;
            $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
          };
          $scope.gridOptionsDetalleVenta = {
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
              { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '14%' },
              { field: 'medicamento', name: 'denominacion', displayName: 'PRODUCTO' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT.', width: '12%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '8%' },
              { field: 'descuento', name: 'descuento_asignado', displayName: 'DSCTO.', width: '10%', cellClass: 'bg-lightblue' },
              { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '13%', cellClass: 'bg-lightblue' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDetalleVentaGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsDetalleVenta.sort = null;
                  paginationOptionsDetalleVenta.sortName = null;
                } else {
                  paginationOptionsDetalleVenta.sort = sortColumns[0].sort.direction;
                  paginationOptionsDetalleVenta.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleVentaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsDetalleVenta.pageNumber = newPage;
                paginationOptionsDetalleVenta.pageSize = pageSize;
                paginationOptionsDetalleVenta.firstRow = (paginationOptionsDetalleVenta.pageNumber - 1) * paginationOptionsDetalleVenta.pageSize;
                $scope.getPaginationDetalleVentaServerSide();
              });
            }
          };
          paginationOptionsDetalleVenta.sortName = $scope.gridOptionsDetalleVenta.columnDefs[0].name;
          $scope.getPaginationDetalleVentaServerSide = function() {
            //$scope.$parent.blockUI.start();
            var arrParams = {
              paginate: paginationOptionsDetalleVenta,
              datos: fVenta
            };
            //console.log($scope.mySelectionGridEE[0]);
            liquidacionFarmServices.sListarDetalleVenta(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleVenta.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleVenta.data = rpta.datos;
              //$scope.$parent.blockUI.stop();
            });
            $scope.mySelectionDetalleVentaGrid = [];
          };
          $scope.getPaginationDetalleVentaServerSide();

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    /* GRID DE PRODUCTOS/SERVICIOS DE LA VENTA */
    $scope.mySelectionGridPV = [];
    var paginationOptionsPV = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsProductosVenta = { 
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '8%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE', width: '16%', visible: true },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%', visible: false },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%', visible: false },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '10%' },
        { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '18%' },
        { field: 'medicamento', name: 'denominacion', displayName: 'PRODUCTO/SERVICIO' },
        { field: 'precio_unitario', name: 'precio_unitario', displayName: 'PRECIO UNIT..', width: '8%' },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '8%' },
        { field: 'descuento', name: 'descuento_asignado', displayName: 'DESCUENTO.', width: '6%' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '8%' }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiProducto = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridPV = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridPV = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiProducto.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsPV.sort = null;
            paginationOptionsPV.sortName = null;
          } else {
            paginationOptionsPV.sort = sortColumns[0].sort.direction;
            paginationOptionsPV.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPVServerSide();
        });
        $scope.gridApiProducto.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsPV.search = true; 
          paginationOptionsPV.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'nombre_lab' : grid.columns[6].filters[0].term,
            // 'emp.descripcion' : grid.columns[7].filters[0].term,
            'm.denominacion' : grid.columns[7].filters[0].term,
            'precio_unitario' : grid.columns[8].filters[0].term,
            'cantidad' : grid.columns[9].filters[0].term,
            'descuento_asignado' : grid.columns[10].filters[0].term,
            'total_detalle' : grid.columns[11].filters[0].term
          }
          $scope.getPaginationPVServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPV.pageNumber = newPage;
          paginationOptionsPV.pageSize = pageSize;
          paginationOptionsPV.firstRow = (paginationOptionsPV.pageNumber - 1) * paginationOptionsPV.pageSize;
          $scope.getPaginationPVServerSide();
        });
      }
    };
    paginationOptionsPV.sortName = $scope.gridOptionsProductosVenta.columnDefs[0].name;
    $scope.getPaginationPVServerSide = function(loader) { // console.log('PV'); 
      var loader = loader || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = { 
        paginate : paginationOptionsPV,
        datos : $scope.fBusqueda
      };
      liquidacionFarmServices.sListarProductosPorVenta(arrParams).then(function (rpta) {
        $scope.gridOptionsProductosVenta.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsProductosVenta.data = rpta.datos;
        if( loader ){ 
          blockUI.stop(); 
        }
      });
      $scope.mySelectionGridPV = [];
    };
    
    /* GRID DE VENTAS IMPRESION */
    $scope.mySelectionGridRI = [];
    var paginationOptionsRI = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsVentasImpresion = {
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
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '9%' },
        //{ field: 'medico', name: 'medico', displayName: 'Profesional', width: '15%' },
        { field: 'fecha_movimiento', name: 'fecha_movimiento', displayName: 'FECHA DE EMISION', width: '12%' },
        { field: 'medio', name: 'descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'subtotal', name: 'sub_total', displayName: 'SUBTOTAL', width: '7%' },
        { field: 'igv', name: 'total_igv', displayName: 'I.G.V.', width: '7%' },
        { field: 'total', name: 'total_a_pagar', displayName: 'TOTAL', width: '7%', cellClass: 'bg-lightblue' },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiImpresionesVenta = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridRI = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridRI = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiImpresionesVenta.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsRI.sort = null;
            paginationOptionsRI.sortName = null;
          } else {
            paginationOptionsRI.sort = sortColumns[0].sort.direction;
            paginationOptionsRI.sortName = sortColumns[0].name;
          }
          $scope.getPaginationRIServerSide();
        });
        $scope.gridApiImpresionesVenta.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsRI.search = true; 
          paginationOptionsRI.searchColumn = { 
            'orden_venta' : grid.columns[1].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[2].filters[0].term,
            'descripcion_td' : grid.columns[3].filters[0].term,
            'ticket_venta' : grid.columns[4].filters[0].term,
            'CONCAT(m.med_nombres," ",m.med_apellido_paterno," ",m.med_apellido_materno)' : grid.columns[5].filters[0].term,
            'descripcion_med' : grid.columns[7].filters[0].term,
            'sub_total' : grid.columns[8].filters[0].term,
            'total_igv' : grid.columns[9].filters[0].term,
            'total_a_pagar' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationRIServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsRI.pageNumber = newPage;
          paginationOptionsRI.pageSize = pageSize;
          paginationOptionsRI.firstRow = (paginationOptionsRI.pageNumber - 1) * paginationOptionsRI.pageSize;
          $scope.getPaginationRIServerSide();
        });
      }
    };
    paginationOptionsRI.sortName = $scope.gridOptionsVentasImpresion.columnDefs[5].name;
    $scope.getPaginationRIServerSide = function(loader) { 
      var loader = loader || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptionsRI,
        datos : $scope.fBusqueda
      };
      liquidacionFarmServices.sListarVentasConSolicitudImpresionCajaActual(arrParams).then(function (rpta) {
        $scope.gridOptionsVentasImpresion.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsVentasImpresion.data = rpta.datos;
        if( loader ){ 
          blockUI.stop(); 
        }
      });
      $scope.mySelectionGridRI = [];
    };

  }])
  .service("liquidacionFarmServices",function($http, $q) { 
    return({
        sListarPedidosVentas: sListarPedidosVentas,
        sListarVentasCajaActual: sListarVentasCajaActual,
        sListarPedido : sListarPedido,
        sListarDetallePedido : sListarDetallePedido,
        sListarDetalleVenta: sListarDetalleVenta,
        sListarVentasAnuladosCajaActual: sListarVentasAnuladosCajaActual,
        sListarVentasConDescuentoCajaActual: sListarVentasConDescuentoCajaActual, 
        sRegistrarVentaPedido: sRegistrarVentaPedido, 
        // sRegistrarVentaPedidoTemporal: sRegistrarVentaPedidoTemporal, 
        sListarProductosPorVenta: sListarProductosPorVenta, 
        sListarVentasConSolicitudImpresionCajaActual: sListarVentasConSolicitudImpresionCajaActual,
        sEnviarSolicitudImpresion: sEnviarSolicitudImpresion,
        sAprobarVentaDescuento: sAprobarVentaDescuento,
        sAnularVentaCajaActual: sAnularVentaCajaActual,
        sAnularPedidoVenta: sAnularPedidoVenta,
        sAnularDetalleVentaPedido: sAnularDetalleVentaPedido,
        sActualizarDetalleVentaPedido: sActualizarDetalleVentaPedido,
        sObtenerParametrosConfig : sObtenerParametrosConfig,
        sVerificarUsuarioDirector : sVerificarUsuarioDirector,
    });
    function sListarPedidosVentas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_pedidos_ventas_por_aprobar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_ventas_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPedido (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_pedido_por_orden_pedido", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetallePedido (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_detalle_pedido", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleVenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_detalle_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasConDescuentoCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_ventas_con_descuento_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentasAnuladosCajaActual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_ventas_anulados_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductosPorVenta (datos) {
      var request = $http({ 
            method : "post", 
            url : angular.patchURLCI+"ventaFarmacia/lista_productos_venta_caja_actual", 
            data : datos 
      }); 
      return (request.then( handleSuccess,handleError )); 
    }
    function sListarVentasConSolicitudImpresionCajaActual (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_solicitudes_impresion_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEnviarSolicitudImpresion (datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/enviar_solicitud_reimpresion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
    function sRegistrarVentaPedido (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/registrar_venta_pedido", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    // function sRegistrarVentaPedidoTemporal (datos) {
    //   var request = $http({
    //         method : "post",
    //         url : angular.patchURLCI+"ventaFarmacia/registrar_venta_pedido_temporal", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }
    function sAprobarVentaDescuento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/aprobar_venta_descuento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularVentaCajaActual (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/anular_venta_caja_actual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularPedidoVenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/anular_pedido_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularDetalleVentaPedido (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/anular_detalle_venta_pedido", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarDetalleVentaPedido (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/actualizar_detalle_venta_pedido", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerParametrosConfig (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/getParametrosConfig", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sVerificarUsuarioDirector (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/verificarUsuarioDirector", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });