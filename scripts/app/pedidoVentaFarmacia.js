angular.module('theme.pedidoVenta', ['theme.core.services'])
  .controller('pedidoVentaFarmaciaController', ['$scope', '$route', '$routeParams', '$controller', '$sce', '$filter', '$modal',
    '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ventaServices', 
    'pedidoVentaFarmaciaServices',
    'empleadoSaludServices', 
    'medicamentoServices', 
    'medicamentoAlmacenServices',
    'tipoDocumentoServices', 
    'medioPagoServices', 
    'especialidadServices', 
    'precioServices',
    'clienteServices',
    'convenioServices',
    'empresasClienteServices',
    'cajaServices',
    'cajaActualServices', 
    'campaniaServices',
    'solicitudCittServices',
    'solicitudExamenServices',
    'solicitudProcedimientoServices',
    'principioActivoServices',
    'almacenFarmServices',
    'liquidacionFarmServices',
    function($scope, $route, $routeParams, $controller, $sce, $filter, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ventaServices,
      pedidoVentaFarmaciaServices,
      empleadoSaludServices,
      medicamentoServices,
      medicamentoAlmacenServices,
      tipoDocumentoServices,
      medioPagoServices,
      especialidadServices,
      precioServices,
      clienteServices,
      convenioServices,
      empresasClienteServices,
      cajaServices,
      cajaActualServices,
      campaniaServices,
      solicitudCittServices,
      solicitudExamenServices,
      solicitudProcedimientoServices,
      principioActivoServices,
      almacenFarmServices,
      liquidacionFarmServices )
    { 
      'use strict'; 
      // console.log('load me 514');
      $controller('clienteController', { 
        $scope : $scope
      });
      $scope.modulo = 'pedido';
      $scope.ventaPedido = false;
      liquidacionFarmServices.sObtenerParametrosConfig().then(function (rpta){
        if(rpta.datos.modo_venta_far == 'VP'){
          console.log(rpta.datos,' (Venta por Pedido)');
          $scope.ventaPedido = true;
        }
      });
      $scope.isRegisterSuccess = false;
      $scope.fDataPedido = {};
      $scope.fDataPedido.cliente = {};
      $scope.fDataPedido.idventaregister = null;
      $scope.fDataPedido.fecha_pedido = new Date();
      console.log($scope.fDataPedido.fecha_pedido);
    
      $scope.fDataPedido.temporal = {
        especialidad : null,
        producto: null,
        cliente: null
      };
      $scope.fDataPedido.temporal.cantidad = 1;

      $scope.fDataPedido.idtipodocumento = 1;
      medioPagoServices.sListarmedioPagoVentaCbo().then(function (rptaMaster) {
        $scope.listaMedioPago = rptaMaster.datos;
        //$scope.listaMedioPago.splice(0,0,{ id : '', descripcion:'--Seleccione Medio de Pago--'});
        $scope.fDataPedido.idmediopago = $scope.listaMedioPago[0].id;
        precioServices.sListarPrecioCbo().then(function (rpta) { 
          //$scope.listaPrecios = rpta.datos; 
          $scope.listaPrecios = [];
          if( $scope.fDataPedido.idmediopago == 1 ){ // SI ES AL CONTADO, SACAR TARJETA 
            angular.forEach(rpta.datos,function (value,key) {
              if( value.id != 1 ){ // SI ES DIFERENTE A TIPO DE PRECIO CON TARJETA VISA 
                $scope.listaPrecios.push(value);
              }
            });
            $scope.listaPrecios.splice(0,0,{ id : '', descripcion:'PRECIO POR DEFECTO', tipo_precio:'0', porcentaje:'0' });
          }
          if( $scope.fDataPedido.idmediopago == 2 ){ // SI ES TARJETA VISA 
            angular.forEach(rpta.datos,function (value,key) {
              if( value.id == 1 ){ // SI ES IGUAL A TIPO DE PRECIO CON TARJETA VISA
                $scope.listaPrecios.push(value);
              }
            })
          }
          
          $scope.fDataPedido.precio = $scope.listaPrecios[0]; 
        });
      });
      
      $scope.generarNumOrden = function () { 
        pedidoVentaFarmaciaServices.sGenerarCodigoOrden($scope.fDataPedido).then(function (rpta) { 
          $scope.fDataPedido.orden_pedido = rpta.codigo_orden;
          $scope.fDataPedido.prefijo = rpta.prefijo; 
          $scope.fDataPedido.correlativo = rpta.correlativo; 
        });
      } 
      
      $scope.listarSubAlmacenesAlmacenVenta = function () {
        almacenFarmServices.sListarSubAlmacenesVentaDeAlmacenCbo().then(function (rpta) {  
          $scope.listaSubAlmacenVenta = rpta.datos;
          console.log('listaSubAlmacenVenta: ', $scope.listaSubAlmacenVenta);
          //$scope.getValidateSession();
          if($scope.fSessionCI.key_group != 'key_sistemas'){
            $scope.fDataPedido.idsubalmacen = $scope.fSessionCI.idsubalmacenfarmacia;
          }else{
            $scope.fDataPedido.idsubalmacen = $scope.listaSubAlmacenVenta[0].id;
          }
          $scope.fDataPedido.idalmacen = $scope.listaSubAlmacenVenta[0].idalmacen;
          $scope.fDataPedido.idsedeempresaadmin = $scope.listaSubAlmacenVenta[0].idsedeempresaadmin;

          $scope.generarNumOrden(); // llamar luego de cargar combo subalmacen porque ahi tengo el idsedeempresaadmin necesario para sistemas
        });
      }
      setTimeout(function() {
        $scope.listarSubAlmacenesAlmacenVenta();
        
      }, 1000);
      $scope.obtenerDatosCliente = function () { 
        if( $scope.fDataPedido.numero_documento ){ 
          clienteServices.sListarEsteClientePorNumDoc($scope.fDataPedido).then(function (rpta) { 
            $scope.fDataPedido.cliente = rpta.datos[0];
            if( rpta.flag === 1 ){
              pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
            }else{
              $scope.btnNuevoCliente("xlg",$scope.fDataPedido.numero_documento);
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
        $('#temporalProducto').focus();

        if( !angular.isObject($scope.fDataPedido.temporal.producto) ){ 
          $scope.fDataPedido.temporal.producto = null;
          $('#temporalProducto').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
          return false;
        }
        //console.log($scope.fDataPedido,$scope.fDataPedido.temporal.cantidad);
        if( !($scope.fDataPedido.temporal.cantidad >= 1) ){ // console.log('especialidad');
          $scope.fDataPedido.temporal.cantidad = null;
          $('#temporalCantidad').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la cantidad', type: 'warning', delay: 2000 });
          return false;
        }
        if( !($scope.fDataPedido.temporal.cantidad % 1 == 0) ){
          $scope.fDataPedido.temporal.cantidad = 1;
          $('#temporalCantidad').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'La cantidad debe ser un numero entero', type: 'warning', delay: 2000 });
          return false;
        }
        if( !($scope.fDataPedido.temporal.cantidad <= $scope.fDataPedido.temporal.stockActual) ){ // console.log('especialidad');
          $scope.fDataPedido.temporal.cantidad = null;
          $('#temporalCantidad').focus();
          pinesNotifications.notify({ title: 'STOCK MENOR', text: 'El STOCK es menor a la cantidad ingresada.', type: 'warning', delay: 3000 });
          return false;
        }
        if( !($scope.fDataPedido.temporal.precio > 0) ){ // console.log('especialidad');
          pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto tiene un precio no válido', type: 'warning', delay: 2000 });
          return false;
        }
        var productNew = true;
        angular.forEach($scope.gridOptions.data, function(value, key) { 
          if(value.id == $scope.fDataPedido.temporal.producto.id ){ 
            productNew = false;
          }
        });
        if( productNew === false ){ 
          pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
          $scope.fDataPedido.temporal.producto= null;
          $scope.fDataPedido.temporal.cantidad= 1;
          return false;
        } 
        console.log($scope.fDataPedido);

        $scope.arrTemporal = { 
          'id' : $scope.fDataPedido.temporal.producto.id,
          'idmedicamentoalmacen' : $scope.fDataPedido.temporal.idmedicamentoalmacen,
          'descripcion' : $scope.fDataPedido.temporal.producto.descripcion,
          'cantidad' : $scope.fDataPedido.temporal.cantidad,
          'precioBase' : ($scope.fDataPedido.temporal.precio),
          'precio' : ($scope.fDataPedido.temporal.producto.precio),
          'descuento' : $scope.fDataPedido.temporal.descuento || '0.00',
          'idtipocliente' : $scope.fDataPedido.cliente.idtipocliente || null,
          'porcentaje_dcto' : $scope.fDataPedido.temporal.porcentaje_dcto || '0.00',
          'idtipoclientedescuento' : $scope.fDataPedido.temporal.idtipoclientedescuento || null
          // 'valor' : (parseFloat($scope.fDataPedido.temporal.precio) * parseFloat($scope.fDataPedido.temporal.cantidad)).toFixed(2)
        }; 
        //$scope.arrTemporal.valor = $scope.arrTemporal.valor.toFixed(2);
        //$scope.arrTemporal.total = parseFloat(($scope.arrTemporal.valor)) - parseFloat($scope.arrTemporal.descuento || 0);
        $scope.gridOptions.data.push($scope.arrTemporal);
        
        $scope.calcularTotales(); 
        //$scope.calcularVuelto(); 
        $scope.fDataPedido.temporal = {
          cantidad: 1
        }
      }
      $scope.calcularDescuento = function (){
        if($scope.fDataPedido.cliente.idtipocliente && $scope.fDataPedido.temporal.precio > 0){
          console.log('Calculando nuevo descuento... ', $scope.fDataPedido.temporal.porcentaje_dcto);
          $scope.fDataPedido.temporal.descuento = (parseFloat($scope.fDataPedido.temporal.porcentaje_dcto * $scope.fDataPedido.temporal.precio * $scope.fDataPedido.temporal.cantidad )/100).toFixed(2);
        }
      }
      $scope.calcularTotales = function () { 
        var totales = 0;
        var totalDescuento = 0;
        $scope.fDataPedido.totalDescuento = 0;
        if( !$scope.fDataPedido.precio.tipo_precio ) { 
          $scope.fDataPedido.precio = $scope.listaPrecios[0];
        }
        // =======
        
      // var productNew = true;
      // angular.forEach($scope.gridOptions.data, function(value, key) { 
      //   if(value.id == $scope.fDataPedido.temporal.producto.id ){ 
      //     productNew = false;
          // >>>>>>> refs/remotes/origin/master
        //}
        angular.forEach($scope.gridOptions.data,function (value, key) { 
          var porcentaje = 1;
          if($scope.fDataPedido.precio.tipo_precio !== '0') { 
            // console.log($scope.fDataPedido.precio); 
            porcentaje = ( parseFloat($scope.fDataPedido.precio.porcentaje) / 100 ) + 1;
          }
          $scope.gridOptions.data[key].precio = ( parseFloat(value.precioBase) * porcentaje ).toFixed(2); 
          $scope.gridOptions.data[key].valor = ($scope.gridOptions.data[key].precio * parseFloat($scope.gridOptions.data[key].cantidad)).toFixed(2);
          $scope.gridOptions.data[key].total = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento || 0) ).toFixed(2);
          totalDescuento += parseFloat($scope.gridOptions.data[key].descuento);
          totales += parseFloat($scope.gridOptions.data[key].total);
        });
        $scope.fDataPedido.totalDescuento = totalDescuento.toFixed(2);
        $scope.fDataPedido.total = totales.toFixed(2);
        $scope.fDataPedido.igv = ( totales - (totales / 1.18) ).toFixed(2);
        $scope.fDataPedido.subtotal = ($scope.fDataPedido.total - $scope.fDataPedido.igv).toFixed(2);
      }
      $scope.limpiarCampos = function (){ 
        $scope.fDataPedido.cliente = {};
        $scope.fDataPedido.temporal = {};
        $scope.fDataPedido.temporal.cantidad = 1;
        $scope.gridOptions.data = [];
      }
      $scope.limpiaDatosMedicamento = function(){
        $scope.fDataPedido.temporal = {};
        $scope.fDataPedido.temporal.cantidad = 1;
      }
      $scope.getProductoAutocomplete = function (value) { 
        var params = {
          searchText: value, 
          searchColumn: "(COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,''))",
          sensor: false,
          subalmacen: $scope.fDataPedido.idsubalmacen
        }
        return medicamentoAlmacenServices.sListarMedicamentosAlmacenVentaAutoComplete(params).then(function(rpta) { 
          $scope.noResultsLPSC = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLPSC = true;
          }
          return rpta.datos;
        });
      } 
      $scope.getSelectedProducto = function (item, model) { 
        var stago = false ;
        var ptitle ;
        var ptext ;
        if(!$scope.fDataPedido.estemporal){
          if( model.stockActual <= 0 ){
            stago = true ;
            ptitle = 'STOCK AGOTADO !!!';
            ptext = 'No se ha encontrado STOCK para el producto.';
          }
        }else{
          if( model.stockTemporal == 0 ){
            stago = true ;
            ptitle = 'STOCK AGOTADO !!!';
            ptext = 'No se ha encontrado STOCK para el producto.';
          }
          if ( model.stockTemporal < 0){
            stago = true ;
            ptitle = 'Advertencia !!!';
            ptext = 'Verifique el Producto ... FALTA regularizar algun movimiento.';
          }
        }
        if(stago == true ){
          pinesNotifications.notify({ title: ptitle , text: ptext , type: 'error', delay: 3000 }); 
          $scope.fDataPedido.temporal = {
            cantidad: 1
          }
          return;
        }
        // if( model.stockActual <= 0 ){
        //   pinesNotifications.notify({ title: 'STOCK AGOTADO.', text: 'No se ha encontrado STOCK para el producto.', type: 'error', delay: 3000 }); 
        //   $scope.fDataPedido.temporal = {
        //     cantidad: 1 
        //   }
        //   return;
        // }
        $scope.fDataPedido.temporal.precio = model.precioSF; 
        $scope.fDataPedido.temporal.stockActual = model.stockActual; 
        $scope.fDataPedido.temporal.stockMinimo = model.stockMinimo; 
        $scope.fDataPedido.temporal.idmedicamentoalmacen = model.idmedicamentoalmacen;
        // OBTENER PORCENTAJE DE DESCUENTO DEL PRODUCTO SI SE SELECCIONO CLIENTE CON DESCUENTO
        if($scope.fDataPedido.cliente.idtipocliente){
          console.log('Obteniendo descuento...');
        }
        if(!$scope.fDataPedido.estemporal == true){
          $scope.fDataPedido.temporal.stockActual = model.stockActual; 
          if( model.stockActual <= model.stockMinimo ){
            pinesNotifications.notify({ title: 'STOCK MINIMO.'+model.stockMinimo, text: 'El producto se está agotando.', type: 'warning', delay: 3000 }); 
          }
        }else{
          $scope.fDataPedido.temporal.stockActual = model.stockTemporal; 
        }
      }
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
        showColumnFooter: true,
        showGridFooter: true,
        columnDefs: [
          { field: 'id', displayName: 'Cod.', width: '5%', enableCellEdit: false, enableSorting: false },
          { field: 'descripcion', displayName: 'Descripción', enableCellEdit: false, enableSorting: false 
            // ,cellTemplate:'<span class="ml-xs"> {{ COL_FIELD.descripcion }} <label ng-show="COL_FIELD.si_campania || COL_FIELD.si_solicitud" style="box-shadow: 1px 1px 0 black; margin-left: 8px; display: inline;" class="label {{COL_FIELD.clase}}"> {{COL_FIELD.tipo}} </label></span>'
          },
          { field: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false },
          { field: 'precio', displayName: 'PRECIO', width: '9%', enableCellEdit: false, enableSorting: false },
          { field: 'valor', displayName: 'VALOR', width: '9%', aggregationType: uiGridConstants.aggregationTypes.sum,
            // footerCellTemplate: '<div class="ui-grid-cell-contents" style="background-color: green;color: White">{{}}</div>',
            enableCellEdit: false, enableSorting: false },
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
        ]
        ,onRegisterApi: function(gridApiCombo) { 
          $scope.gridApiCombo = gridApiCombo; 
          gridApiCombo.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
            
            rowEntity.column = colDef.field;
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
                pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser un número entero', type: pType, delay: 3500 });
                return;
              }
              if($scope.fDataPedido.cliente.idtipocliente){
                console.log('Calc Dcto...');
                rowEntity.descuento = (parseFloat(rowEntity.porcentaje_dcto * rowEntity.precio * rowEntity.cantidad )/100).toFixed(2);
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
            
            $scope.$apply();
          }); 
        }
      };
      $scope.toggleColumnFooter = function() {
        $scope.gridOptions.showColumnFooter = !$scope.gridOptions.showColumnFooter;
        $scope.gridApi.core.notifyDataChange(uiGridConstants.dataChange.OPTIONS);
      };
      $scope.toggleColumnFooter = function() {
        $scope.gridOptions.showColumnFooter = !$scope.gridOptions.showColumnFooter;
        $scope.gridApi.core.notifyDataChange(uiGridConstants.dataChange.OPTIONS);
      };
      $scope.getTableHeight = function() {
         var rowHeight = 30; // your row height 
         var headerHeight = 30; // your header height
         var footerHeight = 30; // your footer height 
         return {
            height: ($scope.gridOptions.data.length * rowHeight + headerHeight + footerHeight + 60) + "px"
         };
      };
      $scope.verPopupPrincipioActivo = function (size) {
        $modal.open({
          templateUrl: angular.patchURLCI+'principioActivo/ver_popup_formulario_principio_activo',
          size: size || '',
          backdrop: 'static',
          keyboard:false,
          scope: $scope,
          controller: function ($scope, $modalInstance, arrToModal) {
            $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
            $scope.titleForm = 'Búsqueda de Principios Activos - Medicamentos';

            var paginationOptionsPrincipio = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.mySelectionPrincipioGrid = [];
            
            $scope.gridOptionsPrincipioBusqueda = {
              //rowHeight: 36,
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
                { field: 'id', name: 'me.idmedicamento', displayName: 'ID', maxWidth: 50 },
                { field: 'medicamento', name: 'me.denominacion', displayName: 'Medicamento', minWidth: 120 },
                { field: 'principio_activo', name: 'principios', displayName: 'Principio Activo', minWidth: 200 },
                { field: 'stock', name: 'stock_actual_malm', displayName: 'Stock', maxWidth: 80 },
                { field: 'precio', name: 'precio_venta', displayName: 'Precio', maxWidth: 80 },
                { field: 'medicamentoalm', name: 'ma.idmedicamento', displayName: 'Disponible', maxWidth: 150 ,enableFiltering: false, enableSorting: true, sort: { direction: uiGridConstants.ASC},
                  cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' 
                 }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.fDataPedido.temporal = {};
                  $scope.mySelectionPrincipioGrid = gridApi.selection.getSelectedRows();
                  $modalInstance.dismiss('cancel');
                  

                  setTimeout(function() {
                    //console.log('focus me',$('#temporalProducto'));
                    if($scope.mySelectionPrincipioGrid[0].medicamentoalm['string'] == 'DISPONIBLE'){
                      var parametros = {
                        idmedicamentoalmacen: $scope.mySelectionPrincipioGrid[0].idmedicamentoalmacen,
                        subalmacen: $scope.fDataPedido.idsubalmacen
                      }
                      pedidoVentaFarmaciaServices.slistarMedicamentoAlmacen(parametros).then(function (rpta) { 
                        if(rpta.flag == 1){
                          if( rpta.datos[0].stockActual <= 0 ){
                            pTitle = 'STOCK AGOTADO.';
                            pType = 'error';
                            pMessage = 'No se ha encontrado STOCK para el producto.';
                            $('#temporalProducto').focus(); 
                          }else{
                            pTitle = 'OK!';
                            pType = 'success';
                            pMessage = rpta.message;
                            $scope.fDataPedido.temporal.producto = {
                              'id': rpta.datos[0].id,
                              'descripcion': rpta.datos[0].descripcion,
                              'precio':rpta.datos[0].precioSF
                            };
                            $scope.fDataPedido.temporal.idmedicamentoalmacen = rpta.datos[0].idmedicamentoalmacen;
                            $scope.fDataPedido.temporal.stockActual = rpta.datos[0].stockActual;
                            $scope.fDataPedido.temporal.stockMinimo = rpta.datos[0].stockMinimo;
                            $scope.fDataPedido.temporal.precio = rpta.datos[0].precioSF;
                            $scope.fDataPedido.temporal.cantidad = 1;
                            $('#temporalCantidad').focus();
                            // OBTENER PORCENTAJE DE DESCUENTO DEL PRODUCTO SI SE SELECCIONO CLIENTE CON DESCUENTO
                            if($scope.fDataPedido.cliente.idtipocliente){
                              console.log('Obteniendo descuento...');
                              /*
                              var parametros = {
                                idtipoproducto: rpta.datos[0].idtipoproducto,
                                idtipocliente: $scope.fDataPedido.cliente.idtipocliente,
                                idempresaadmin: rpta.datos[0].idempresaadmin
                              }
                              convenioServices.sObtenerDescuentoPorTipoProducto(parametros).then(function (rpta) { 
                                if(rpta.flag == 1){
                                  pTitle = 'OK!';
                                  pType = 'success';
                                  $scope.fDataPedido.temporal.idtipoclientedescuento = rpta.datos.idtipoclientedescuento;
                                  $scope.fDataPedido.temporal.porcentaje_dcto = rpta.datos.porcentaje_dcto;
                                  $scope.fDataPedido.temporal.descuento = (parseFloat($scope.fDataPedido.temporal.porcentaje_dcto * $scope.fDataPedido.temporal.precio * $scope.fDataPedido.temporal.cantidad )/100).toFixed(2);

                                }else if(rpta.flag == 0){
                                  var pTitle = 'Advertencia!';
                                  var pType = 'warning';
                                }else{
                                  alert('Algo salió realmente mal...');
                                }
                                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                              });
                              */
                            }
                          }
                          
                          
                        }else if(rpta.flag == 0){
                          var pTitle = 'Advertencia!';
                          var pType = 'warning';
                          var pMessage = rpta.message;
                        }else{
                          alert('Algo salió mal...');
                        }
                        pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3500 });
                      });

                      //$('#temporalProducto').val($scope.mySelectionPrincipioGrid[0].medicamento);
                    }else{
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'El Medicamento no esta disponible en el Almacen del Usuario', type: 'warning', delay: 2000 }); 
                    }
                  }, 1000);
                });

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsPrincipio.sort = null;
                    paginationOptionsPrincipio.sortName = null;
                  } else {
                    paginationOptionsPrincipio.sort = sortColumns[0].sort.direction;
                    paginationOptionsPrincipio.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationPrincipioServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsPrincipio.pageNumber = newPage;
                  paginationOptionsPrincipio.pageSize = pageSize;
                  paginationOptionsPrincipio.firstRow = (paginationOptionsPrincipio.pageNumber - 1) * paginationOptionsPrincipio.pageSize;
                  $scope.getPaginationPrincipioServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsPrincipio.search = true;
                  paginationOptionsPrincipio.searchColumn = { 
                    'me.idmedicamento' : grid.columns[1].filters[0].term,
                    'me.denominacion' : grid.columns[2].filters[0].term,
                    'pa.descripcion' : grid.columns[3].filters[0].term,
                    'stock_actual_malm' : grid.columns[4].filters[0].term
                  }
                  $scope.getPaginationPrincipioServerSide();
                });
              }
            };
            paginationOptionsPrincipio.sortName = $scope.gridOptionsPrincipioBusqueda.columnDefs[3].name;
            $scope.getPaginationPrincipioServerSide = function() {
              $scope.datosGrid = {
                paginate : paginationOptionsPrincipio,
                datos: $scope.fDataPedido
              };
              principioActivoServices.sListarBusquedaPrincipioActivo($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsPrincipioBusqueda.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsPrincipioBusqueda.data = rpta.datos;
              });
              $scope.mySelectionPrincipioGridGrid = [];
            };
            $scope.getPaginationPrincipioServerSide();

            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }               

          }, 
          resolve: {
            arrToModal : function () {
              return {
                getPaginationPrincipioServerSide : $scope.getPaginationPrincipioServerSide
              }
            }
          }
        });
      }
      $scope.btnBuscarCliente = function (size) {
        $modal.open({
          templateUrl: angular.patchURLCI+'cliente/ver_popup_busqueda_cliente',
          size: size || '',
          scope: $scope,
          controller: function ($scope, $modalInstance) { 
            $scope.titleForm = 'Búsqueda de Clientes';
            var paginationOptionsClienteEnVentas = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.mySelectionClienteGrid = [];
            
            $scope.gridOptionsClienteBusqueda = {
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
                { field: 'id', name: 'idcliente', displayName: 'ID', maxWidth: 50,  sort: { direction: uiGridConstants.ASC} },
                { field: 'num_documento', name: 'num_documento', displayName: 'N° Doc.', maxWidth: 120 },
                { field: 'nombres', name: 'nombres', displayName: 'Nombres', maxWidth: 200 },
                { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'Apellido Paterno', maxWidth: 200 },
                { field: 'apellido_materno', name: 'apellido_materno', displayName: 'Apellido Materno', maxWidth: 200 }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionClienteGrid = gridApi.selection.getSelectedRows();
                  $scope.fDataPedido.cliente = $scope.mySelectionClienteGrid[0]; //console.log($scope.fDataPedido.cliente);
                  $scope.fDataPedido.numero_documento = $scope.mySelectionClienteGrid[0].num_documento;
                  $modalInstance.dismiss('cancel');
                  setTimeout(function() {
                    $('#temporalProducto').focus(); //console.log('focus me',$('#temporalProducto'));
                  }, 1000);
                  
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
                    'cl.idcliente' : grid.columns[1].filters[0].term,
                    'num_documento' : grid.columns[2].filters[0].term,
                    'cl.nombres' : grid.columns[3].filters[0].term,
                    'apellido_paterno' : grid.columns[4].filters[0].term,
                    'apellido_materno' : grid.columns[5].filters[0].term
                  }
                  $scope.getPaginationClienteEnVentaServerSide();
                });
              }
            };
            $scope.navegateToCellListaBusquedaCliente = function( rowIndex, colIndex ) { 
              console.log(rowIndex, colIndex);
              $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsClienteBusqueda.data[rowIndex], $scope.gridOptionsClienteBusqueda.columnDefs[colIndex]); 
              
            };
            paginationOptionsClienteEnVentas.sortName = $scope.gridOptionsClienteBusqueda.columnDefs[0].name;
            $scope.getPaginationClienteEnVentaServerSide = function() {
              //$scope.$parent.blockUI.start();
              $scope.datosGrid = {
                paginate : paginationOptionsClienteEnVentas
              };
              clienteServices.sListarClientes($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsClienteBusqueda.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsClienteBusqueda.data = rpta.datos;
                 
                //$scope.$parent.blockUI.stop();
              });
              $scope.mySelectionClienteGrid = [];
            };
            $scope.getPaginationClienteEnVentaServerSide();

            shortcut.add("down",function() { 

              $scope.navegateToCellListaBusquedaCliente(0,0);
            });
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      // ========================> BUSCAR EMPRESA DEL CLIENTE - SOLO PARA FACTURAS 
      $scope.btnBuscarEmpresaCliente = function (size) {
        $modal.open({
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
                  $scope.fDataPedido.empresa = $scope.mySelectionEmpresaClienteGrid[0]; //console.log($scope.fDataPedido.cliente);
                  $scope.fDataPedido.ruc = $scope.mySelectionEmpresaClienteGrid[0].ruc_empresa;
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
      $scope.btnNuevaEmpresa = function (size) {
        $modal.open({
          templateUrl: angular.patchURLCI+'empresaCliente/ver_popup_formulario',
          size: size || '',
          backdrop: 'static',
          keyboard:false,
          controller: function ($scope, $modalInstance, arrToModal) {
            
            $scope.accion = 'reg';
            //$scope.getPaginationClienteEnVentaServerSide = getPaginationClienteEnVentaServerSide;
            $scope.fData = {};
            $scope.fDataPedido = arrToModal.fDataPedido;
            $scope.fDataPedido.empresa = {};
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
                  $scope.fDataPedido.ruc = rpta.ruc;
                  $scope.fDataPedido.empresa.id = rpta.idempresacliente;
                  console.log($scope.fDataPedido);
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
                fDataPedido : $scope.fDataPedido
              }
            }
          }
        });
      }
      $scope.grabar = function (param) { 
        var pParam = param || false; 
        $scope.fDataPedido.detalle = $scope.gridOptions.data;
        if( $scope.fDataPedido.detalle.length < 1 ){ 
          $('#temporalProducto').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 2000 }); 
          return false; 
        }
        
        if( !($scope.fDataPedido.estemporal) ){ 
          pedidoVentaFarmaciaServices.sRegistrarVenta($scope.fDataPedido).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
              $scope.isRegisterSuccess = true; 
              $scope.fDataPedido.idventaregister = rpta.idventaregister;
              $scope.fDataPedido.temporal.producto = null;
              $scope.fDataPedido.temporal.precio = null;
              $scope.fDataPedido.temporal.cantidad = 1;
              $scope.fDataPedido.temporal.descuento = null;

            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }else{
          pedidoVentaFarmaciaServices.sRegistrarVentaTemporal($scope.fDataPedido).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
              $scope.isRegisterSuccess = true; 
              $scope.fDataPedido.idventaregister = rpta.idventaregister;
              $scope.fDataPedido.temporal.producto = null;
              $scope.fDataPedido.temporal.precio = null;
              $scope.fDataPedido.temporal.cantidad = null;
              $scope.fDataPedido.temporal.descuento = null;

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
      $scope.nuevo = function () { 
        $route.reload(); 
      }

      $scope.mismoCliente = function () { 
        $scope.fDataPedido.detalle = [];
        $scope.fDataPedido.temporal.cantidad = 1;
        $scope.gridOptions.data = [];
        $scope.fDataPedido.subtotal = null;
        $scope.fDataPedido.igv = null;
        $scope.fDataPedido.total = null;
        $scope.fDataPedido.entrega = null;
        $scope.fDataPedido.vuelto = null;
        $scope.generarNumOrden();
        $scope.generarCodigoTicket();
        $('#temporalProducto').focus();
      }
    
      /* ============================ */
      /* ATAJOS DE TECLADO NAVEGACION */
      /* ============================ */ 
      shortcut.remove('F2');
      shortcut.add("F2",function($event) { 
          $scope.grabar(); 
      });
      shortcut.remove('F3');
      shortcut.add("F3",function($event) { 
        if($scope.isRegisterSuccess == true){ 
          $scope.nuevo(); 
        }else{
          $route.reload(); 
        }
        setTimeout(function() {
          $('#temporalProducto').focus(); // console.log($('#temporalProducto'));
        },1000);
      }); 
      shortcut.remove('F4');
      shortcut.add("F4",function(event) { 
        if($scope.isRegisterSuccess == true){ 
          $scope.imprimir(); 
        } 
      }); 
      shortcut.remove('F6');
      shortcut.add("F6",function() {  
          $scope.mismoCliente(); 
          $('#temporalProducto').focus();
      });
      shortcut.remove('F8');
      shortcut.add("F8",function() { 
        $scope.verPopupPrincipioActivo('lg');
      });
    }
  ])
  .service("pedidoVentaFarmaciaServices",function($http, $q) { 
    return({
        sRegistrarVenta: sRegistrarVenta, 
        sRegistrarVentaTemporal: sRegistrarVentaTemporal,
        sGenerarCodigoOrden: sGenerarCodigoOrden,
        sListarOrdenesVentaCajaCerrada: sListarOrdenesVentaCajaCerrada,
        sListarVentaPorId: sListarVentaPorId,
        sListarSolicitudesProducto: sListarSolicitudesProducto,
        sImprimirTicketVenta: sImprimirTicketVenta,
        sListaDetalleVentaColumna:sListaDetalleVentaColumna,
        slistarMedicamentoAlmacen:slistarMedicamentoAlmacen
        
    });

    function sRegistrarVenta(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/registrar_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarVentaTemporal(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/registrar_venta_temporal", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarCodigoOrden(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/generateCodigoOrdenPedido",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarOrdenesVentaCajaCerrada (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ventaFarmacia/listar_ordenes_ventas_cerradas", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentaPorId (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ventaFarmacia/listar_esta_venta_por_id", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSolicitudesProducto (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"venta/listar_solicitudes_de_historia", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListaDetalleVentaColumna(datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ventaFarmacia/lista_detalle_venta_por_columna", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirTicketVenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/imprimir_ticket_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function slistarMedicamentoAlmacen (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamento_almacen_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });