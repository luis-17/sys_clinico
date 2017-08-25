angular.module('theme.ventaFarmacia', ['theme.core.services'])
  .controller('ventaFarmaciaController', ['$scope', '$route', '$routeParams', '$controller', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',
    'ventaServices',
    'ventaFarmaciaServices',
    'empleadoSaludServices',
    'medicamentoServices',
    'medicamentoAlmacenServices',
    'tipoDocumentoServices',
    'medioPagoServices',
    'especialidadServices',
    'precioServices',
    'clienteServices',
    'empresasClienteServices',
    'cajaServices',
    'cajaActualServices',
    'campaniaServices',
    'solicitudCittServices',
    'solicitudExamenServices',
    'solicitudProcedimientoServices',
    'principioActivoServices',
    'liquidacionFarmServices',
    'almacenFarmServices',
    'pedidoVentaFarmaciaServices',
    'recetaMedicaServices',
    function($scope, $route, $routeParams, $controller, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys,
      ventaServices,
      ventaFarmaciaServices,
      empleadoSaludServices,
      medicamentoServices,
      medicamentoAlmacenServices,
      tipoDocumentoServices,
      medioPagoServices,
      especialidadServices,
      precioServices,
      clienteServices,
      empresasClienteServices,
      cajaServices,
      cajaActualServices,
      campaniaServices,
      solicitudCittServices,
      solicitudExamenServices,
      solicitudProcedimientoServices,
      principioActivoServices,
      liquidacionFarmServices,
      almacenFarmServices ,
      pedidoVentaFarmaciaServices,
      recetaMedicaServices
    ){
      'use strict';
      console.log('Inicio de Venta farmacia');
      $scope.modulo = 'venta';
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
      setTimeout(function() {
          $('#temporalProducto').focus(); // console.log($('#temporalProducto'));
      },1000);
      $controller('clienteController', {
        $scope : $scope
      });
      $scope.fDataVenta = {};
      $scope.fDataVenta.esEditable = false;
      $scope.fDataVenta.cliente = {};
      $scope.fDataVenta.cliente_afiliado = {};
      $scope.fDataVenta.idventaregister = null;
      $scope.fDataVenta.aleasDocumento = 'TICKET';
      $scope.fDataVenta.ticket = '[ ............... ]';
      $scope.fDataVenta.temporal = {
        //especialidad : null,
        producto: null,
        cliente: null
      };
      $scope.fDataVenta.medico = {
        id : null,
        descripcion: null
      }
      $scope.fDataVenta.total = null;
      $scope.fDataVenta.temporal.cantidad = 1;
      $scope.fDataVenta.temporal.siBonificacion = false;
      $scope.fDataVenta.estemporal = false;
      $scope.fDataVenta.esPreparado = false;
      $scope.fDataVenta.boolSolicitud = false;
      $scope.ventaNormal = true;
      // $scope.fDataVenta.boolPagoMixto = false; esclienteexterno 
      liquidacionFarmServices.sObtenerParametrosConfig().then(function (rpta){
        if(rpta.datos.modo_venta_far == 'VP'){
          // console.log(rpta.datos,' (Venta por Pedido)');
          $scope.ventaNormal = false;
        }
      });
      $scope.elegirConvenio = function(){
        //console.log('convenio');
        $scope.gridOptions.data = [];
        if( $scope.fDataVenta.convenio ){
          $scope.boolConvenio = true;
        }else{
          $scope.boolConvenio = false;
        }
      }
      $scope.getPersonalMedicoAutocomplete = function (value) {
        var params = {
          search: value,
          sensor: false
        }
        return empleadoSaludServices.sListarPersonalSaludCbo(params).then(function(rpta) { return rpta.datos; });
      }
      $scope.abrirCaja = function () {
        // console.log('agf');
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
                idmodulo: 3 // farmacia
              }
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
                    $scope.fData.idmodulo = 3;
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

      tipoDocumentoServices.sListarTipoDocumentoVentaCbo().then(function (rpta) {
        $scope.listaTipoDocumento = rpta.datos;
        $scope.listaTipoDocumento.splice(0,0,{ id : '0', descripcion:'--Seleccione Tipo de Documento--'});

        $scope.fDataVenta.idtipodocumento = $scope.listaTipoDocumento[1].id; //
        $scope.generarCodigoTicket();
      });
      medioPagoServices.sListarTodoMedioPagoVentaCbo().then(function (rptaMaster) {
        $scope.listaMedioPago = rptaMaster.datos;
        //$scope.listaMedioPago.splice(0,0,{ id : '', descripcion:'--Seleccione Medio de Pago--'});
        $scope.fDataVenta.idmediopago = $scope.listaMedioPago[0].id;

        precioServices.sListarPrecioCbo().then(function (rpta) {
          //$scope.listaPrecios = rpta.datos;
          $scope.listaPrecios = [];
          if( $scope.fDataVenta.idmediopago == 1){ // SI ES AL CONTADO, SACAR TARJETA
            angular.forEach(rpta.datos,function (value,key) {
              if( value.id != 1 ){ // SI ES DIFERENTE A TIPO DE PRECIO CON TARJETA VISA
                $scope.listaPrecios.push(value);
              }
            });
            $scope.listaPrecios.splice(0,0,{ id : '', descripcion:'PRECIO POR DEFECTO', tipo_precio:'0', porcentaje:'0' });
          }
          if( $scope.fDataVenta.idmediopago == 2 || $scope.fDataVenta.idmediopago == 5){ // SI ES TARJETA VISA
            angular.forEach(rpta.datos,function (value,key) {
              if( value.id == 1 ){ // SI ES IGUAL A TIPO DE PRECIO CON TARJETA VISA
                $scope.listaPrecios.push(value);
              }
            })
          }

          $scope.fDataVenta.precio = $scope.listaPrecios[0];
        });
      });
      $scope.fDataVenta.esclienteexterno = false;
      $scope.personalSaludDisabled = false; 
      $scope.onChangeClienteExterno = function() { 
        if( $scope.fDataVenta.esclienteexterno === true ){ 
          $scope.personalSaludDisabled = true; 
        }else{
          $scope.personalSaludDisabled = false; 
        } 
      }
      $scope.onChangeMedioPago = function () {
        console.log('Deshabilitado hasta nuevo aviso');
         // limpia los montos de los medios de pago
        angular.forEach($scope.fDataVenta.pagoMixto, function(value, key) {
          value.monto = null;
        }); 
      }
      $scope.onChangeTipoDocumento = function () {
        console.log('Cambiando tipo de documento...');
        $scope.generarCodigoTicket();
        $scope.fDataVenta.ruc = null;
        $scope.fDataVenta.a_cuenta = null;
        $scope.fDataVenta.saldo = null;
        $scope.fDataVenta.entrega = null;
        $scope.fDataVenta.vuelto = null;
        // limpia los montos de los medios de pago
        angular.forEach($scope.fDataVenta.pagoMixto, function(value, key) {
          value.monto = null;
        });
      }
      $scope.generarNumOrden = function () {
        ventaFarmaciaServices.sGenerarCodigoOrden().then(function (rpta) {
          $scope.fDataVenta.orden = rpta.codigo_orden;
          $scope.fDataVenta.idcaja = rpta.idcaja;
          $scope.fDataVenta.idcajamaster = rpta.idcajamaster;
        });
      }
      $scope.generarNumOrden();
      $scope.generarCodigoTicket = function () {
        if( $scope.fDataVenta.idtipodocumento ){
          //console.log($scope.fDataVenta.idtipodocumento);
          $scope.fDataVenta.idmodulo = 3; // FARMACIA
          ventaServices.sGenerarCodigoTicket($scope.fDataVenta).then(function (rpta) {
            $scope.fDataVenta.ticket = rpta.ticket;
            $scope.fDataVenta.serie = rpta.serie;
            $scope.fDataVenta.numero_serie = rpta.numero_serie;
            if( $scope.fDataVenta.idtipodocumento == '1' ){ // BOLETA
              $scope.fDataVenta.aleasDocumento = 'TICKET';
            }
            else if( $scope.fDataVenta.idtipodocumento == '2' ){ // FACTURA
              $scope.fDataVenta.aleasDocumento = 'FACT.';
              // alert('Cargara Las Empresas...')
             $scope.btnBuscarEmpresaCliente('lg');
            }
            else if( $scope.fDataVenta.idtipodocumento == '6' ){ // RECIBO
              $scope.fDataVenta.aleasDocumento = 'REC.';
            }
            else if( $scope.fDataVenta.idtipodocumento == '3' ){ // OPERACION
              $scope.fDataVenta.aleasDocumento = 'OPE.';
            }
            else if( $scope.fDataVenta.idtipodocumento == '12' ){ // COMPROBANTE DE CAJA
              $scope.fDataVenta.aleasDocumento = 'C.C.';
            }
          });
        }
      }
      $scope.obtenerDatosCliente = function () {
        if( $scope.fDataVenta.numero_documento ){
          clienteServices.sListarEsteClientePorNumDoc($scope.fDataVenta).then(function (rpta) {
            $scope.fDataVenta.cliente = rpta.datos[0];
            if( rpta.flag === 1 ){
              pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
            }else{
              $scope.btnNuevoCliente("xlg",$scope.fDataVenta.numero_documento);
            }
          });
        }
      }
      $scope.btnQuitarDeLaCesta = function (row) {
        if($scope.fDataVenta.esPreparado){
           pinesNotifications.notify({ title: 'Advertencia.', text: 'No se permite eliminar ningún preparado', type: 'warning', delay: 2000 });
           return false;
        }
        var index = $scope.gridOptions.data.indexOf(row.entity);
        $scope.gridOptions.data.splice(index,1);
        $scope.calcularTotales();
        $scope.calcularVuelto();
      }
      $scope.agregarItem = function () {
        $('#temporalProducto').focus();
        $scope.fDataVenta.esEditable = false;
        if( !angular.isObject($scope.fDataVenta.temporal.producto) ){
          $scope.fDataVenta.temporal.producto = null;
          $('#temporalProducto').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
          return false;
        }
        //console.log($scope.fDataVenta,$scope.fDataVenta.temporal.cantidad);
        if( !($scope.fDataVenta.temporal.cantidad >= 1) ){ // console.log('especialidad');
          //$scope.fDataVenta.temporal.cantidad = null;
          $('#temporalCantidad').focus().select();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado una cantidad correcta', type: 'warning', delay: 2000 });
          return false;
        }
        if( $scope.fDataVenta.estemporal ){ // si no es venta sin stock
          var diferencia = $scope.fDataVenta.temporal.stockActual - $scope.fDataVenta.temporal.cantidad;
          console.log('diferencia', diferencia);
          if( diferencia >= 0 ){
            $('#temporalCantidad').focus().select();
            pinesNotifications.notify({ title: 'MEDICAMENTO CON STOCK', text: 'El Medicamento tiene stock ' + diferencia + '. No es permitido en este tipo de venta.', type: 'warning', delay: 3000 });
            return false;
            }
        }else if( !$scope.fDataVenta.esPreparado){ // y si no es preparado
          if( !($scope.fDataVenta.temporal.cantidad <= $scope.fDataVenta.temporal.stockActual) ){ // console.log('especialidad');
            //$scope.fDataVenta.temporal.cantidad = null;
            $('#temporalCantidad').focus().select();
            pinesNotifications.notify({ title: 'STOCK MENOR', text: 'El STOCK es menor a la cantidad ingresada.', type: 'warning', delay: 3000 });
            return false;
          }
        }


        if(!$scope.fDataVenta.temporal.siBonificacion){
          if( !($scope.fDataVenta.temporal.precio > 0) ){ // console.log('especialidad');
            pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto tiene un precio no válido', type: 'warning', delay: 2000 });
            return false;
          }
        }
        var productNew = true;
        angular.forEach($scope.gridOptions.data, function(value, key) {
          if(value.id == $scope.fDataVenta.temporal.producto.id ){
            productNew = false;
          }
        });
        if( productNew === false ){
          pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
          $scope.fDataVenta.temporal.producto= null;
          $scope.fDataVenta.temporal.cantidad= 1;
          return false;
        }
        $scope.arrTemporal = {
          'id' : $scope.fDataVenta.temporal.producto.id,
          'idmedicamentoalmacen' : $scope.fDataVenta.temporal.idmedicamentoalmacen,
          'descripcion' : $scope.fDataVenta.temporal.producto.descripcion,
          'cantidad' : parseInt($scope.fDataVenta.temporal.cantidad),
          'precioBase' : ($scope.fDataVenta.temporal.precio),
          'precio' : ($scope.fDataVenta.temporal.producto.precio),
          'descuento' : $scope.fDataVenta.temporal.descuento || '0.00',
          //'valor' : parseFloat(($scope.fDataVenta.temporal.precio)) - parseFloat($scope.fDataVenta.temporal.descuento || 0),
          'idtipocliente' : $scope.fDataVenta.cliente.idtipocliente || null,
          'porcentaje_dcto' : $scope.fDataVenta.temporal.porcentaje_dcto || '0.00',
          'idtipoclientedescuento' : $scope.fDataVenta.temporal.idtipoclientedescuento || null,
          'excluye_igv' : $scope.fDataVenta.temporal.excluye_igv,
          'idreceta': null,
          'idrecetamedicamento': null,
        };
        // $scope.arrTemporal.valor = $scope.arrTemporal.valor.toFixed(2);
        // $scope.arrTemporal.total = (parseFloat($scope.arrTemporal.valor) * parseFloat($scope.arrTemporal.cantidad)).toFixed(2);
        $scope.gridOptions.data.push($scope.arrTemporal);

        $scope.calcularTotales();
        $scope.calcularVuelto();


        $scope.fDataVenta.temporal = { 
          cantidad: 1
        }
      }
      $scope.agregarDescuentoPuntos = function (){
        var productNew = true;
        angular.forEach($scope.gridOptions.data, function(value, key) {
          if(value.id == 0 ){
            productNew = false;
          }
        });
        if( productNew === false ){
          pinesNotifications.notify({ title: 'Advertencia.', text: 'Ya se agregó el descuento por Puntos.', type: 'warning', delay: 2000 });
          $scope.fDataVenta.temporal.producto= null;
          $scope.fDataVenta.temporal.cantidad= 1;
          return false;
        } 
        $scope.arrTemporal = { 
          'id' : 0,
          'idmedicamentoalmacen' : 0,
          'descripcion' : 'DESCUENTO POR PUNTOS',
          'cantidad' : 1,
          'precioBase' : 0,
          'precio' : 0,
          'descuento' : '5.00',
          'valor' : '0.00',
          'idtipocliente' : null,
          'porcentaje_dcto' : 0,
          'idtipoclientedescuento' : null,
          //'total' : -5.00
        };
        $scope.arrTemporal.total = (parseFloat($scope.arrTemporal.valor) * parseFloat($scope.arrTemporal.cantidad)).toFixed(2);
        $scope.gridOptions.data.push($scope.arrTemporal);

        $scope.calcularTotales();
        //$scope.calcularVuelto();
        $scope.fDataVenta.temporal = {
          cantidad: 1
        }
      }
      $scope.btnAfiliar = function (idcliente){
        clienteServices.sAfiliarPuntos(idcliente).then(function (rpta) {
          if( rpta.flag === 1 ){
            var ptitle = 'OK!';
            var ptext = rpta.message;
            var ptype = 'success';
            $scope.fDataVenta.cliente.si_afiliado_puntos = '1';
          }else if( rpta.flag === 0 ){
            var ptitle = 'ADVERTENCIA!';
            var ptext = rpta.message;
            var ptype = 'warning';
          }else{
            var ptitle = 'OCURRIÓ UN ERROR!';
            var ptext = 'Contacte con el Area de Sistemas.';
            var ptype = 'error';
          }
          pinesNotifications.notify({ title: ptitle , text: ptext , type: ptype, delay: 3000 });
        });
      }
      $scope.btnComprobarAfiliacion = function (dni){
        console.log('Comprobando...');
        $scope.fDataVenta.cliente_afiliado = {};
        var paramDatos = {
          num_documento : dni
        };
        clienteServices.sComprobarAfiliacionPuntos(paramDatos).then(function (rpta) {
          if( rpta.flag === 1 ){
            $scope.fDataVenta.cliente_afiliado = rpta.datos;
            console.log('total ', $scope.fDataVenta.total);
            if( $scope.fDataVenta.cliente_afiliado.puntos_acumulados >= 1000 && $scope.fDataVenta.total >= 5){
              //$bootbox.alert('<b>FELICITACIONES!</b> <br>UD. TIENE ' + $scope.fDataVenta.cliente_afiliado.puntos_acumulados + ' PUNTOS');
              $bootbox.dialog({
                message: 'EL CLIENTE A ACUMULADO ' + $scope.fDataVenta.cliente_afiliado.puntos_acumulados + ' PUNTOS<br> DESEA CANJEAR 1,000 PUNTOS POR S/.5.00 DE DESCUENTO?',
                title: 'CANJEAR PUNTOS DE DESCUENTO',
                buttons: {
                  success: {
                    label: 'CANJEAR PUNTOS!',
                    className: 'btn-success',
                    callback: function() {
                      console.log('Canjendo puntos...');
                      $scope.agregarDescuentoPuntos();
                    }
                  },
                  danger: {
                    label: 'AHORA NO!',
                    className: 'btn-danger',
                    // callback: function() {
                    //   alert('');
                    // }
                  }
                }
              });
            }else{
              $scope.mensaje = rpta.message;
              $scope.clase = 'text-success';
            }


          }else if( rpta.flag === 0 ){
            $scope.mensaje = rpta.message;
            $scope.clase = 'text-danger';
            //$bootbox.alert('<b>AVISO!</b> <br>'+ rpta.message);
          }else{
            var ptitle = 'OCURRIÓ UN ERROR.!';
            var ptext = 'Contacte con el Area de Sistemas.';
            var ptype = 'error';
            pinesNotifications.notify({ title: ptitle , text: ptext , type: ptype, delay: 3000 });
          }
        });
      }
      $scope.calcularDescuento = function (){
        if($scope.fDataVenta.cliente.idtipocliente && $scope.fDataVenta.temporal.precio > 0){
          console.log('Calculando nuevo descuento... ', $scope.fDataVenta.temporal.porcentaje_dcto, '%');
          $scope.fDataVenta.temporal.descuento = (parseFloat($scope.fDataVenta.temporal.porcentaje_dcto * $scope.fDataVenta.temporal.precio * $scope.fDataVenta.temporal.cantidad )/100).toFixed(2);
        }
      }
      $scope.calcularTotales = function () {
        if($scope.fDataVenta.idmediopago == 6){
          angular.forEach($scope.fDataVenta.pagoMixto, function(val,index) {
            val.monto = null;
          });
        }
        
        var totales = 0;
        var total_exonerado = 0;
        var igv_exonerado = 0;
        $scope.bool_exonerado = false;
        if( !$scope.fDataVenta.precio.tipo_precio ) {
          $scope.fDataVenta.precio = $scope.listaPrecios[0];
        }
        angular.forEach($scope.gridOptions.data,function (value, key) {
          var porcentaje = 1;
          if($scope.fDataVenta.precio.tipo_precio !== '0') {
            // console.log($scope.fDataVenta.precio);
            porcentaje = ( parseFloat($scope.fDataVenta.precio.porcentaje) / 100 ) + 1;
          }
          $scope.gridOptions.data[key].precio = (parseFloat(value.precioBase) * porcentaje ).toFixed(2); //0
          $scope.gridOptions.data[key].valor = ($scope.gridOptions.data[key].precio * parseFloat($scope.gridOptions.data[key].cantidad)).toFixed(2); // 0
          $scope.gridOptions.data[key].total = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento || 0)).toFixed(2);
          // $scope.gridOptions.data[key].total = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento || 0) ).toFixed(2);

          if( $scope.gridOptions.data[key].excluye_igv == 1 ){ // si esta exonerado lo calculamos aparte
            total_exonerado += parseFloat($scope.gridOptions.data[key].total);
            igv_exonerado += parseFloat($scope.gridOptions.data[key].total) * 0.18 / 1.18;
            $scope.bool_exonerado = true;
          }else{
            totales += parseFloat($scope.gridOptions.data[key].total);
          }

        });
        $scope.fDataVenta.igv_exonerado = igv_exonerado.toFixed(2);
        $scope.fDataVenta.total_sin_redondeo = ( totales + total_exonerado ).toFixed(2);
        $scope.fDataVenta.igv = ( totales - (totales / 1.18) ).toFixed(2);
        $scope.fDataVenta.subtotal = ( $scope.fDataVenta.total_sin_redondeo - $scope.fDataVenta.igv ).toFixed(2);
        $scope.fDataVenta.total = redondear($scope.fDataVenta.total_sin_redondeo,1).toFixed(2);
        $scope.fDataVenta.redondeo = ($scope.fDataVenta.total - $scope.fDataVenta.total_sin_redondeo).toFixed(2);
        if( $scope.fDataVenta.esPreparado && $scope.fDataVenta.pago_a_cuenta ){
          $scope.fDataVenta.total_saldo = (totales + parseFloat($scope.fDataVenta.pago_a_cuenta)).toFixed(2);

        }
        console.log('Venta: ', $scope.fDataVenta);
      }
      $scope.calcularSaldo = function (){
        if( $scope.fDataVenta.idmediopago == 6 ){ // pago mixto
          // limpia los montos de los medios de pago
          angular.forEach($scope.fDataVenta.pagoMixto, function(value, key) {
            value.monto = null;
          });
        }
        console.log('Calculado Saldo...');
        if($scope.fDataVenta.total !== 0){
          var saldo= parseFloat($scope.fDataVenta.total) - parseFloat($scope.fDataVenta.a_cuenta);
          $scope.fDataVenta.saldo = saldo;
        }
        $scope.calcularVuelto();
      }
      $scope.calcularVuelto = function (){
        console.log('Calculado Vuelto...');
        if($scope.fDataVenta.esPreparado && $scope.fDataVenta.idtipodocumento == 12){
          if($scope.fDataVenta.a_cuenta !== 0){
            var vuelto= parseFloat($scope.fDataVenta.entrega) - parseFloat($scope.fDataVenta.a_cuenta);
            $scope.fDataVenta.vuelto = vuelto;
          } 
        }else if( $scope.fDataVenta.esPreparado && $scope.fDataVenta.pago_a_cuenta){
          if($scope.fDataVenta.total_saldo !== 0){
            var vuelto= parseFloat($scope.fDataVenta.entrega) - parseFloat($scope.fDataVenta.total_saldo);
            $scope.fDataVenta.vuelto = vuelto;
          } 
        }
        else{
          if($scope.fDataVenta.total !== 0){
            var vuelto= parseFloat($scope.fDataVenta.entrega) - parseFloat($scope.fDataVenta.total);
            $scope.fDataVenta.vuelto = vuelto;
          }
        }
      }
      $scope.limpiarCampos = function (){
        $scope.fDataVenta.cliente = {};
        $scope.fDataVenta.cliente_afiliado = {};
        $scope.fDataVenta.numero_documento_afiliado = null;
        $scope.fDataVenta.temporal = {};
        $scope.fDataVenta.temporal.cantidad = 1;
        // $scope.gridOptions.data = []; Maribel me agradecerá hacer este cambio xD 
        // $scope.fDataVenta.igv_exonerado = null;
        // $scope.fDataVenta.total = null;
        // $scope.fDataVenta.igv = null;
        // $scope.fDataVenta.total_sin_redondeo = null;
        // $scope.fDataVenta.redondeo = null;
        // $scope.fDataVenta.subtotal = null;
      }
      $scope.limpiarCamposProductoTemporal = function (){
        $scope.fDataVenta.temporal.cantidad = 1;
        $scope.fDataVenta.temporal.precio = null;
        $scope.fDataVenta.temporal.stockMinimo = null;
        $scope.fDataVenta.temporal.idmedicamentoalmacen = null;
        $scope.fDataVenta.temporal.excluye_igv = null;
        $scope.fDataVenta.temporal.siBonificacion = false; // si el medicamento tiene bonificacion
        $scope.fDataVenta.temporal.bonificacion = false; // lo que se elige en el control, false: precio por defecto; true: precio = 0
        $scope.fDataVenta.esEditable = false;
      }
      $scope.limpiaDatosMedicamento = function(){
        // console.log('ya no hace nada');
        $scope.fDataVenta.temporal = {};
        $scope.fDataVenta.temporal.cantidad = 1;
      }
      $scope.listarSubAlmacenesAlmacenVenta = function () {
        almacenFarmServices.sListarSubAlmacenesVentaDeAlmacenCbo().then(function (rpta) {
          $scope.listaSubAlmacenVenta = rpta.datos;
          //$scope.fDataVenta.idsubalmacen = $scope.fSessionCI.idsubalmacenfarmacia;
          $scope.fDataVenta.idsubalmacen = $scope.listaSubAlmacenVenta[0].id  ;
          //console.log('fSessionCI', $scope.fSessionCI);
        });
      }
      $scope.listarSubAlmacenesAlmacenVenta();

      $scope.getProductoAutocomplete = function (value) {
        var params = { 
          searchText: value,
          searchColumn: "(COALESCE(denominacion,'') || ' ' || COALESCE(descripcion,''))", // estemporal
          sensor: false,
          subalmacen: $scope.fDataVenta.idsubalmacen,
          boolPreparado: $scope.fDataVenta.esPreparado,
          idcliente: $scope.fDataVenta.cliente.id 
        }; 
        return medicamentoAlmacenServices.sListarMedicamentosAlmacenVentaAutoComplete(params).then(function(rpta) {
          $scope.noResultsLPSC = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLPSC = true;
          }
          return rpta.datos;
        });
      }
      $scope.getSelectedProducto = function (item, model) {
        var $stago = false ;
        var $ptitle ;
        var $ptext ;
        $scope.fDataVenta.temporal.producto.descripcion_stock = $scope.fDataVenta.temporal.producto.descripcion;
        if(!$scope.fDataVenta.estemporal && !$scope.fDataVenta.esPreparado){ // si no es venta temporal
          if( model.stockActual <= 0 ){
            $stago = true ;
            $ptitle = 'STOCK AGOTADO !!!';
            $ptext = 'No se ha encontrado STOCK para el producto.';
          }
        }
        $scope.fDataVenta.temporal.precio = model.precioSF;
        $scope.fDataVenta.temporal.stockMinimo = model.stockMinimo;
        $scope.fDataVenta.temporal.idmedicamentoalmacen = model.idmedicamentoalmacen;
        $scope.fDataVenta.temporal.excluye_igv = model.excluye_igv;
        if(model.si_bonificacion == 1){
          $scope.fDataVenta.temporal.siBonificacion = true;
          $scope.fDataVenta.temporal.precioDefault = model.precioSF;
        }

        if($stago == true ){
          pinesNotifications.notify({ title: $ptitle , text: $ptext , type: 'error', delay: 3000 });
          $scope.fDataVenta.temporal.cantidad = 1;
          $scope.fDataVenta.temporal.stockActual = 0;
          return;
        }
        if( model.edicion_precio_en_venta == 1 ){
          $scope.fDataVenta.esEditable = true;

        }
        // OBTENER PORCENTAJE DE DESCUENTO DEL PRODUCTO SI SE SELECCIONO CLIENTE CON DESCUENTO
        if($scope.fDataVenta.cliente.idtipocliente){
          console.log('Obteniendo descuento...');

        }
        if(!$scope.fDataVenta.esPreparado){
          $scope.fDataVenta.temporal.stockActual = model.stockActual;
          if( model.stockActual <= model.stockMinimo && !$scope.fDataVenta.estemporal){
            pinesNotifications.notify({ title: 'STOCK MINIMO.'+model.stockMinimo, text: 'El producto se está agotando.', type: 'warning', delay: 3000 });
          }
        }
        // else{
        //   $scope.fDataVenta.temporal.stockActual = model.stockTemporal;
        // }
        console.log('temporal ', $scope.fDataVenta.temporal);
      }
      $scope.$watch('fDataVenta.temporal.bonificacion', function(newvalue,oldvalue) {
        if($scope.fDataVenta.temporal.siBonificacion){
          if(newvalue){
              $scope.fDataVenta.temporal.precio = 0.00;
          }else{
            if(oldvalue){
              $scope.fDataVenta.temporal.precio = $scope.fDataVenta.temporal.precioDefault;
            }
          }
        }
      });
      //$scope.cambiarPrecioBonificacion = function(){ console.log('shg');
        // if( $scope.fDataVenta.temporal.bonificacion ){
        //   $scope.fDataVenta.temporal.precio = 0.00;
        // }else{
        //   $scope.fDataVenta.temporal.precio = $scope.fDataVenta.temporal.precioDefault;
        // }
      //}
      // GRILLA - CESTA DE PRODUCTOS
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
            { field: 'cantidad', displayName: 'CANT.', width: '6%', enableSorting: false,
                enableCellEdit: true,
                cellEditableCondition: function ($scope) {
                  console.log('idtipocliente: ', $scope.row.entity.idtipocliente);
                  if($scope.row.entity.id == 0){
                    return false; 
                  }else{
                    return true; // editable solo si no es venta de formulas
                  }
                },
                cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                  if(row.entity.id == 0 ){
                    return false;
                  }else{
                    return 'ui-editCell'; // se aplica colorcito amarillo solo si es editable
                  }
                }
            },
            { field: 'precio', displayName: 'PRECIO', width: '9%', enableCellEdit: false, enableSorting: false },
            { field: 'valor', displayName: 'VALOR', aggregationType: uiGridConstants.aggregationTypes.sum, width: '9%', enableCellEdit: false, enableSorting: false },
            { field: 'descuento', displayName: 'DESCUENTO', width: '9%', aggregationType: uiGridConstants.aggregationTypes.sum,
                enableCellEdit: true,
                cellEditableCondition: function ($scope) {
                  console.log('idtipocliente: ', $scope.row.entity.idtipocliente);
                  if($scope.row.entity.idtipocliente || $scope.row.entity.id == 0 ){
                    return false;
                  }else{
                    return true; // editable solo si el cliente no tiene asignado un tipocliente y editable solo si no es venta de formulas
                  }
                },
                cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                  if(row.entity.idtipocliente || row.entity.id == 0 ){
                    return false;
                  }else{
                    return 'ui-editCell'; // se aplica colorcito amarillo solo si es editable
                  }
                },
                enableSorting: false
            },
            { field: 'total', displayName: 'TOTAL', width: '12%', enableCellEdit: false, enableSorting: false },
            { field: 'excluye_igv', displayName: 'INAFECTO', width: '8%', enableCellEdit: false, enableSorting: false,
                cellTemplate: '<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 2"> NO </div>'
                  },
            { field: 'accion', displayName: '', width: '4%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
          ]
          ,onRegisterApi: function(gridApiCombo) {
            $scope.gridApiCombo = gridApiCombo;
            gridApiCombo.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
              rowEntity.column = colDef.field;
              if(rowEntity.column == 'cantidad' && newValue != oldValue){
                console.log('Verificando..');
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
                if($scope.fDataVenta.cliente.idtipocliente){
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
              if( $scope.fDataVenta.a_cuenta > 0 ){
                $scope.calcularSaldo();
              }else{
                $scope.calcularVuelto();
              }
              // $scope.calcularVuelto();
              $scope.$apply();
            });
          }
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
      $scope.btnBuscarCliente = function (size) {
        $uibModal.open({
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
                  $scope.fDataVenta.cliente = $scope.mySelectionClienteGrid[0]; //console.log($scope.fDataVenta.cliente);
                  $scope.fDataVenta.numero_documento = $scope.mySelectionClienteGrid[0].num_documento;
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
      $scope.btnBuscarProducto = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'medicamento/ver_popup_busqueda_medicamento',
          size: size || '',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Búsqueda de Productos';
            var paginationOptionsProductos = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.mySelectionProductoGrid = [];

            $scope.gridOptionsMedicamentoBusqueda = {
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
              enableCellEdit: false,
              multiSelect: false,
              columnDefs: [
                { field: 'id', name: 'm.idmedicamento', displayName: 'COD.', maxWidth: 50 },
                { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', minWidth: 100,  sort: { direction: uiGridConstants.ASC} },
                { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO' },
                { field: 'stock', name: 'stock_actual_malm', displayName: 'STOCK', maxWidth: 80, cellClass: 'text-right', enableFiltering: false  },
                { field: 'stock_central', name: 'stock_central', displayName: 'STOCK CENTRAL', maxWidth: 100, cellClass: 'text-right', enableFiltering: false },
                { field: 'precio', name: 'precio_venta', displayName: 'PRECIO', maxWidth: 80, cellClass: 'text-right', enableFiltering: false },
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionPrincipioGrid = gridApi.selection.getSelectedRows();
                  $modalInstance.dismiss('cancel');
                  setTimeout(function() {
                    var arrParam = {
                      'subalmacen' : $scope.fDataVenta.idsubalmacen,
                      'idmedicamentoalmacen' : $scope.mySelectionPrincipioGrid[0].idmedicamentoalmacen
                    }
                    pedidoVentaFarmaciaServices.slistarMedicamentoAlmacen(arrParam).then(function (rpta) {
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
                          $scope.fDataVenta.temporal.producto = {
                            'id': rpta.datos[0].id,
                            'descripcion_stock': rpta.datos[0].descripcion,
                            'descripcion': rpta.datos[0].descripcion,
                            'precio':rpta.datos[0].precioSF
                          };
                          $scope.fDataVenta.temporal.idmedicamentoalmacen = rpta.datos[0].idmedicamentoalmacen;
                          $scope.fDataVenta.temporal.stockActual = rpta.datos[0].stockActual;
                          $scope.fDataVenta.temporal.stockMinimo = rpta.datos[0].stockMinimo;
                          $scope.fDataVenta.temporal.precio = rpta.datos[0].precioSF;
                          $('#temporalCantidad').focus();
                          // OBTENER PORCENTAJE DE DESCUENTO DEL PRODUCTO SI SE SELECCIONO CLIENTE CON DESCUENTO
                          if($scope.fDataVenta.cliente.idtipocliente){
                            console.log('Obteniendo descuento...');

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

                  }, 1000);

                });

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsProductos.sort = null;
                    paginationOptionsProductos.sortName = null;
                  } else {
                    paginationOptionsProductos.sort = sortColumns[0].sort.direction;
                    paginationOptionsProductos.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationProductoEnVentaServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsProductos.pageNumber = newPage;
                  paginationOptionsProductos.pageSize = pageSize;
                  paginationOptionsProductos.firstRow = (paginationOptionsProductos.pageNumber - 1) * paginationOptionsProductos.pageSize;
                  $scope.getPaginationProductoEnVentaServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsProductos.search = true;
                  // console.log(grid.columns);
                  // console.log(grid.columns[1].filters[0].term);
                  paginationOptionsProductos.searchColumn = {
                    'm.idmedicamento' : grid.columns[1].filters[0].term,
                    "( COALESCE (denominacion, '') || ' ' || COALESCE (descripcion, '') )" : grid.columns[2].filters[0].term,
                    'nombre_lab' : grid.columns[3].filters[0].term

                  }
                  $scope.getPaginationProductoEnVentaServerSide();
                });
              }
            };

            paginationOptionsProductos.sortName = $scope.gridOptionsMedicamentoBusqueda.columnDefs[1].name;
            $scope.getPaginationProductoEnVentaServerSide = function() {
              //$scope.$parent.blockUI.start();
              $scope.datosGrid = {
                paginate : paginationOptionsProductos,
                datos: $scope.fDataVenta
              };
              medicamentoAlmacenServices.sListarMedicamentosAlmacenBusquedaVenta($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsMedicamentoBusqueda.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsMedicamentoBusqueda.data = rpta.datos;

              });
              $scope.mySelectionProductoGrid = [];
            };
            $scope.getPaginationProductoEnVentaServerSide();

            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      $scope.verPopupStocks = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'medicamentoAlmacen/ver_popup_formulario_stocks',
          size: size || '',
          // backdrop: 'static',
          // keyboard:false,
          scope: $scope,
          controller: function ($scope, $modalInstance, arrToModal) {
            $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
            $scope.titleForm = 'Consulta de Stocks';


            var paginationOptionsStocks = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              // sort: uiGridConstants.ASC,
              // sortName: null,
              search: null
            };
            $scope.gridOptionsStocks = {
              //rowHeight: 36,
              paginationPageSizes: [10, 50, 100, 500, 1000],
              paginationPageSize: 10,
              useExternalPagination: true,
              useExternalSorting: false,
              enableGridMenu: false,
              enableRowSelection: false,
              enableSorting: false,
              enableSelectAll: true,
              enableFiltering: false,
              columnDefs: [
                { field: 'sede', name: 'sede', displayName: 'SEDE', width: '15%' },
                { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: '18%' },
                { field: 'almacen', name: 'almacen', displayName: 'ALMACEN' },
                { field: 'subalmacen', name: 'subalmacen', displayName: 'SUBALMACEN', width: '13%' },
                { field: 'stock', name: 'stock', displayName: 'STOCK', width: '8%', cellClass: 'text-right' },
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsStocks.pageNumber = newPage;
                  paginationOptionsStocks.pageSize = pageSize;
                  paginationOptionsStocks.firstRow = (paginationOptionsStocks.pageNumber - 1) * paginationOptionsStocks.pageSize;
                  $scope.getPaginationStockServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsStocks.search = true;
                  paginationOptionsStocks.searchColumn = {
                    'sede' : grid.columns[1].filters[0].term,
                    'empresa' : grid.columns[2].filters[0].term,
                    'almacen' : grid.columns[3].filters[0].term,
                    'subalmacen' : grid.columns[4].filters[0].term,
                    'stock' : grid.columns[5].filters[0].term
                  }
                  $scope.getPaginationStockServerSide();
                });
              }
            };
            //paginationOptionsStocks.sortName = $scope.gridOptionsStocks.columnDefs[0].name;
            $scope.getPaginationStockServerSide = function() {
              $scope.datosGrid = {
                paginate : paginationOptionsStocks,
                datos: $scope.fDataVenta.temporal.producto
              };
              medicamentoAlmacenServices.sListarStocks($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsStocks.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsStocks.data = rpta.datos;
              });
            };

            $scope.getPaginationStockServerSide();

            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              //$scope.fDataVenta.temporal.principios = null;
            }

          },
          resolve: {
            arrToModal : function () {
              return {
                getPaginationStockServerSide : $scope.getPaginationStockServerSide
              }
            }
          }
        });
      }
      $scope.verPopupPrincipioActivo = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'principioActivo/ver_popup_formulario_principio_activo',
          size: size || '',
          // backdrop: 'static',
          // keyboard:false,
          scope: $scope,
          controller: function ($scope, $modalInstance, arrToModal) {
            $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
            if( !$scope.fDataVenta.esPreparado ){
              $scope.titleForm = 'Búsqueda de Medicamentos Similares Por Principio Activo';
            }else{
              $scope.titleForm = 'Lista de componentes de preparado';
            }
            

            var paginationOptionsPrincipio = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 100,
              sort: uiGridConstants.DESC,
              sortName: null,
              search: null
            };
            $scope.mySelectionPrincipioGrid = [];

            $scope.gridOptionsPrincipioBusqueda = {
              //rowHeight: 36,
              paginationPageSizes: [20, 50, 100, 500, 1000],
              paginationPageSize: 100,
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
                { field: 'id', name: 'm.idmedicamento', displayName: 'COD.', maxWidth: 50 },
                { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', minWidth: 100 },
                { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO' },
                { field: 'stock', name: 'stock_actual_malm', displayName: 'STOCK', maxWidth: 80, cellClass: 'text-right', enableFiltering: false  },
                { field: 'stock_central', name: 'stock_central', displayName: 'STOCK CENTRAL', maxWidth: 100, cellClass: 'text-right', enableFiltering: false },
                { field: 'precio', name: 'precio_venta', displayName: 'PRECIO', maxWidth: 80, cellClass: 'text-right', enableFiltering: false },
                // { field: 'medicamentoalm', name: 'ma.idmedicamento', displayName: 'Disponible', maxWidth: 150 ,enableFiltering: false, enableSorting: true, sort: { direction: uiGridConstants.ASC},
                //   cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
                //  }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionPrincipioGrid = gridApi.selection.getSelectedRows();
                  $modalInstance.dismiss('cancel');
                  setTimeout(function() {
                    var arrParam = {
                      'subalmacen' : $scope.fDataVenta.idsubalmacen,
                      'idmedicamentoalmacen' : $scope.mySelectionPrincipioGrid[0].idmedicamentoalmacen
                    }
                    pedidoVentaFarmaciaServices.slistarMedicamentoAlmacen(arrParam).then(function (rpta) {
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
                          $scope.fDataVenta.temporal.producto = {
                            'id': rpta.datos[0].id,
                            'descripcion_stock': rpta.datos[0].descripcion,
                            'descripcion': rpta.datos[0].descripcion,
                            'precio':rpta.datos[0].precioSF
                          };
                          $scope.fDataVenta.temporal.idmedicamentoalmacen = rpta.datos[0].idmedicamentoalmacen;
                          $scope.fDataVenta.temporal.stockActual = rpta.datos[0].stockActual;
                          $scope.fDataVenta.temporal.stockMinimo = rpta.datos[0].stockMinimo;
                          $scope.fDataVenta.temporal.precio = rpta.datos[0].precioSF;
                          $('#temporalCantidad').focus();
                          // OBTENER PORCENTAJE DE DESCUENTO DEL PRODUCTO SI SE SELECCIONO CLIENTE CON DESCUENTO
                          if($scope.fDataVenta.cliente.idtipocliente){
                            console.log('Obteniendo descuento...');
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
                    'm.idmedicamento' : grid.columns[1].filters[0].term,
                    '( COALESCE (denominacion, \'\') || \' \' || COALESCE (descripcion, \'\') )' : grid.columns[2].filters[0].term,
                  }

                  $scope.getPaginationPrincipioServerSide();
                });
              }
            };
            paginationOptionsPrincipio.sortName = $scope.gridOptionsPrincipioBusqueda.columnDefs[4].name;
            $scope.getPaginationPrincipioServerSide = function() {
              $scope.datosGrid = {
                paginate : paginationOptionsPrincipio,
                datos: $scope.fDataVenta
              };
              //console.log('fDataVenta.temporal =>',$scope.fDataVenta.temporal);
              principioActivoServices.sListarBusquedaPrincipioActivo($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsPrincipioBusqueda.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsPrincipioBusqueda.data = rpta.datos;
                $scope.fDataVenta.temporal.producto.stock_central = rpta.producto_principal.stock_central;
                $scope.fDataVenta.temporal.principios = rpta.principios;
                if(rpta.datos != ''){


                  // $scope.fDataVenta.temporal.producto.stock_actual_malm = rpta.producto_principal.stock_actual_malm;
                  // $scope.fDataVenta.temporal.producto.precio_venta = rpta.producto_principal.precio_venta;

                }else{
                  console.log('No hay datos...');
                  //$scope.fDataVenta.temporal.producto.medicamento = $scope.fDataVenta.temporal.producto.descripcion;
                }


              });
              $scope.mySelectionPrincipioGrid = [];
            };
            if( !$scope.fDataVenta.esPreparado ){
              $scope.getPaginationPrincipioServerSide();
            }
            
            //$scope.getPaginationPrincipioServerSide();

            // GRILLA DE COMPONENTES
            var paginationOptionsComponente = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 100,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.mySelectionPrincipioGrid = [];

            $scope.gridOptionsComponentes = {
              //rowHeight: 36,
              paginationPageSizes: [20, 50, 100, 500, 1000],
              paginationPageSize: 100,
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
                { field: 'id', name: 'idprincipioactivo', displayName: 'COD.', maxWidth: 50, sort: { direction: uiGridConstants.ASC}  },
                { field: 'descripcion', name: 'descripcion', displayName: 'COMPONENTE'},
                { field: 'abreviatura', name: 'abreviatura', displayName: 'ABREVIATURA', minWidth: 100 },
                
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  
                });

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsComponente.sort = null;
                    paginationOptionsComponente.sortName = null;
                  } else {
                    paginationOptionsComponente.sort = sortColumns[0].sort.direction;
                    paginationOptionsComponente.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationComponenteServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsComponente.pageNumber = newPage;
                  paginationOptionsComponente.pageSize = pageSize;
                  paginationOptionsComponente.firstRow = (paginationOptionsComponente.pageNumber - 1) * paginationOptionsComponente.pageSize;
                  $scope.getPaginationComponenteServerSide();
                });
                
              }
            };
            paginationOptionsComponente.sortName = $scope.gridOptionsComponentes.columnDefs[0].name;
            $scope.getPaginationComponenteServerSide = function() {
              $scope.datosGrid = {
                paginate : paginationOptionsComponente,
                datos: $scope.fDataVenta.temporal.producto
              };
              console.log('datos -> ', $scope.datosGrid);
              principioActivoServices.sListarprincipioActivoxMed($scope.datosGrid).then(function (rpta) {
                // $scope.gridOptionsComponentes.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsComponentes.data = rpta.datos;
                
                if(rpta.datos != ''){


                  // $scope.fDataVenta.temporal.producto.stock_actual_malm = rpta.producto_principal.stock_actual_malm;
                  // $scope.fDataVenta.temporal.producto.precio_venta = rpta.producto_principal.precio_venta;

                }else{
                  console.log('No hay datos...');
                  //$scope.fDataVenta.temporal.producto.medicamento = $scope.fDataVenta.temporal.producto.descripcion;
                }


              });
              $scope.mySelectionPrincipioGrid = [];
            };
            if( $scope.fDataVenta.esPreparado ){
              $scope.getPaginationComponenteServerSide();
            }
            
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.fDataVenta.temporal.principios = null;
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
              enableRowSelection: true,
              enableSelectAll: false,
              enableFiltering: true,
              // enableRowHeaderSelection: false, // fila cabecera
              enableFullRowSelection: false,
              multiSelect: false,
              columnDefs: [
                { field: 'id', name: 'idempresacliente', displayName: 'ID', width: '10%', visible:false  },
                { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC', width: '12%', sort: { direction: uiGridConstants.ASC}},
                { field: 'descripcion', name: 'descripcion', displayName: 'Empresa' },
                { field: 'telefono', name: 'telefono', displayName: 'Teléfono', width: '12%' }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionEmpresaClienteGrid = gridApi.selection.getSelectedRows();
                  $scope.fDataVenta.empresa = $scope.mySelectionEmpresaClienteGrid[0]; //console.log($scope.fDataVenta.cliente);
                  $scope.fDataVenta.ruc = $scope.mySelectionEmpresaClienteGrid[0].ruc_empresa;
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
                    'telefono' : grid.columns[4].filters[0].term,

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
            $scope.fDataVenta = arrToModal.fDataVenta;
            $scope.fDataVenta.empresa = {};
            $scope.fData.pertenece_salud_ocup = 2;
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
                  $scope.fDataVenta.ruc = rpta.ruc;
                  $scope.fDataVenta.empresa.id = rpta.idempresacliente;
                  console.log($scope.fDataVenta);
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
                fDataVenta : $scope.fDataVenta
              }
            }
          }
        });
      }
      $scope.btnAgregarReceta = function () {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'RecetaMedica/ver_popup_agregar_receta',
          size: 'lg',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Agregar Receta';
            $scope.fBusqueda = null;
            var paginationOptionsRecetaMedicaEnVentas = { 
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsRecetaBusqueda = {
              rowHeight: 36,
              paginationPageSizes: [10, 50, 100],
              paginationPageSize: 10,
              useExternalPagination: true,
              useExternalSorting: true,
              enableGridMenu: false,
              enableRowSelection: true,
              enableSelectAll: false,
              enableFiltering: false,
              // enableRowHeaderSelection: false, // fila cabecera
              enableFullRowSelection: false,
              multiSelect: false,
              showColumnFooter: true,
              //showGridFooter: true,
              columnDefs: [
                { field: 'id', name: 'idmedicamento', displayName: 'ID', width: '10%', visible:true  },
                { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', sort: { direction: uiGridConstants.ASC}},
                { field: 'precio_venta_sf', name: 'precio', displayName: 'PRECIO', width: '10%', cellClass:'text-right' },
                { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '10%', cellClass:'text-center'  },
                { field: 'valor', name: 'valor', displayName: 'VALOR', aggregationType: uiGridConstants.aggregationTypes.sum, width: '10%', cellClass:'text-center'  },
                { field: 'stockActual', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: '14%',
                  cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                      if(row.entity.stockActual == 0){
                        return 'text-danger text-bold text-right';
                      }else{
                        return 'text-right';
                      }
                  }
                },
                { field: 'estado', type: 'object', name: 'estado', displayName: '', width: '6%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
                  cellTemplate:'<div class=" text-center"><label tooltip-placement="bottom" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>'
                }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsRecetaMedicaEnVentas.sort = null;
                    paginationOptionsRecetaMedicaEnVentas.sortName = null;
                  } else {
                    paginationOptionsRecetaMedicaEnVentas.sort = sortColumns[0].sort.direction;
                    paginationOptionsRecetaMedicaEnVentas.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationRecetaMedicaEnVentaServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsRecetaMedicaEnVentas.pageNumber = newPage;
                  paginationOptionsRecetaMedicaEnVentas.pageSize = pageSize;
                  paginationOptionsRecetaMedicaEnVentas.firstRow = (paginationOptionsRecetaMedicaEnVentas.pageNumber - 1) * paginationOptionsRecetaMedicaEnVentas.pageSize;
                  $scope.getPaginationRecetaMedicaEnVentaServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsRecetaMedicaEnVentas.search = true;
                  // console.log(grid.columns);
                  // console.log(grid.columns[1].filters[0].term);
                  paginationOptionsRecetaMedicaEnVentas.searchColumn = {
                    'idmedicamento' : grid.columns[1].filters[0].term,
                    'denominacion' : grid.columns[2].filters[0].term,
                    'cantidad' : grid.columns[3].filters[0].term,
                  }
                  $scope.getPaginationRecetaMedicaEnVentaServerSide();
                });
              }
            };
            $scope.getPaginationRecetaMedicaEnVentaServerSide = function(){
              $scope.gridOptionsRecetaBusqueda.data = [];
              $scope.fBusqueda.idsubalmacen = $scope.fDataVenta.idsubalmacen;
              $scope.datosGrid = {
                paginate : paginationOptionsRecetaMedicaEnVentas,
                datos : $scope.fBusqueda,
              };
              recetaMedicaServices.sListarRecetaPorId($scope.datosGrid).then(function (rpta) {
                if(rpta.flag == 1){
                  $scope.gridOptionsRecetaBusqueda.data = rpta.datos;
                  $scope.gridOptionsRecetaBusqueda.totalItems = rpta.paginate.totalRows;
                }else if(rpta.flag == 0){
                  var pTitle = 'Advertencia!';
                  var pType = 'danger';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                }else{
                  alert('Algo salió mal...');
                }


              });
            }
            $scope.aceptar = function () {
              $modalInstance.dismiss('cancel');
              $scope.fDataVenta.cliente.idcliente = $scope.gridOptionsRecetaBusqueda.data[0].idcliente;
              $scope.fDataVenta.numero_documento = $scope.gridOptionsRecetaBusqueda.data[0].num_documento;
              $scope.fDataVenta.medico = {
                id: $scope.gridOptionsRecetaBusqueda.data[0].idmedico,
                descripcion: $scope.gridOptionsRecetaBusqueda.data[0].medico
              }

              clienteServices.sListarEsteCliente($scope.fDataVenta.cliente).then(function (rpta) {
                $scope.fDataVenta.cliente = rpta.datos[0];
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
                }
                var cestaVacia = true;
                var productNew = true;
                if( $scope.gridOptions.data.length > 0 ){
                  cestaVacia = false;
                }

                angular.forEach($scope.gridOptionsRecetaBusqueda.data, function(value, item){
                  $scope.fDataVenta.temporal = {}
                  if(!cestaVacia){
                    console.log('ojo Ya hay medicamentos en la cesta');
                    angular.forEach ($scope.gridOptions.data, function(value2, item2){
                      if( value.id == value2.id){
                        productNew = false;
                      }
                    });
                  }
                  if( value.cantidad <= value.stockActual && productNew && value.atendido == 2){
                    // console.log('cliente.', $scope.fDataVenta.cliente);
                    // OBTENER PORCENTAJE DE DESCUENTO DEL PRODUCTO SI SE SELECCIONO CLIENTE CON DESCUENTO
                    /*if($scope.fDataVenta.cliente.idtipocliente){

                      console.log('Obteniendo descuento...'); 

                    }else{*/
                      $scope.arrTemporal = {
                        'id' : value.id,
                        'idmedicamentoalmacen' : value.idmedicamentoalmacen,
                        'descripcion' : value.medicamento,
                        'cantidad' : parseInt(value.cantidad),
                        'precioBase' : parseFloat(value.precio_venta_sf),
                        'precio' : parseFloat(value.precio_venta_sf),
                        'precio_sin_convenio' : parseFloat(value.precio_sin_convenio),
                        'descuento' : $scope.fDataVenta.temporal.descuento || '0.00',
                        'idtipocliente' : $scope.fDataVenta.cliente.idtipocliente || null,
                        'porcentaje_dcto' : $scope.fDataVenta.temporal.porcentaje_dcto || '0.00',
                        'idtipoclientedescuento' : $scope.fDataVenta.temporal.idtipoclientedescuento || null,
                        'excluye_igv' : value.excluye_igv,
                        'idreceta': value.idreceta,
                        'idrecetamedicamento': value.idrecetamedicamento,
                        'hay_descuento': value.hay_descuento, 
                        'tiene_convenio_detalle': value.tiene_convenio_detalle, 
                        'tiene_convenio_detalle_efectivo': value.tiene_convenio_detalle_efectivo 
                      };

                      $scope.gridOptions.data.push($scope.arrTemporal);
                    /*}*/
                  }
                });
                $scope.calcularTotales();
                console.log($scope.gridOptions.data,'$scope.gridOptions.data');
              }); // fin del servicio



              // $scope.gridOptions.data = $scope.gridOptionsRecetaBusqueda.data;
            }
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      $scope.btnCargarSolicitudFormula = function () {
        if($scope.gridOptions.data.length>0){
          var pTitle = 'AVISO!';
          var pType = 'warning';
          var pMessage = 'Ya tiene una solicitud cargada en la cesta. Elimine la cesta para agregar nueva solicitud ';
          pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
          return false;
        }
        $uibModal.open({
          templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_cargar_receta_preparados',
          size: 'lg',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Agregar Solicitud de Formulas';
            
            $scope.fDataVenta.boolSolicitud = true;
            $scope.fBusqueda = {};
            // $scope.fBusqueda.boolSolicitud = true;
            var paginationOptionsRecetaPreparadosEnVentas = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 50,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsRecetaPreparados = {
              rowHeight: 30,
              paginationPageSizes: [50, 100],
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              enableGridMenu: false,
              enableRowSelection: true,
              enableSelectAll: false,
              enableFiltering: false,
              minRowsToShow: 5,
              // enableRowHeaderSelection: false, // fila cabecera
              enableFullRowSelection: false,
              multiSelect: false,
              showColumnFooter: false,
              //showGridFooter: true,
              columnDefs: [
                { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: '10%', visible:true  },
                { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', sort: { direction: uiGridConstants.ASC}},
                { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '10%', cellClass:'text-center'  },
                { field: 'precio_unitario', name: 'precio_unitario', displayName: 'P.U.', width: '10%', cellClass:'text-center'  },
                { field: 'total_detalle', name: 'total_detalle', displayName: 'TOTAL', width: '10%', cellClass:'text-right' },
                
                // { field: 'stockActual', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: '14%',
                //   cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                //       if(row.entity.stockActual == 0){
                //         return 'text-danger text-bold text-right';
                //       }else{
                //         return 'text-right';
                //       }
                //   }
                // },
                { field: 'estado', type: 'object', name: 'estado', displayName: '', width: '6%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
                  cellTemplate:'<div class=" text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>'
                }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsRecetaPreparadosEnVentas.sort = null;
                    paginationOptionsRecetaPreparadosEnVentas.sortName = null;
                  } else {
                    paginationOptionsRecetaPreparadosEnVentas.sort = sortColumns[0].sort.direction;
                    paginationOptionsRecetaPreparadosEnVentas.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationRecetaPreparadosEnVentaServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsRecetaPreparadosEnVentas.pageNumber = newPage;
                  paginationOptionsRecetaPreparadosEnVentas.pageSize = pageSize;
                  paginationOptionsRecetaPreparadosEnVentas.firstRow = (paginationOptionsRecetaPreparadosEnVentas.pageNumber - 1) * paginationOptionsRecetaPreparadosEnVentas.pageSize;
                  $scope.getPaginationRecetaPreparadosEnVentaServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsRecetaPreparadosEnVentas.search = true;
                  // console.log(grid.columns);
                  // console.log(grid.columns[1].filters[0].term);
                  paginationOptionsRecetaPreparadosEnVentas.searchColumn = {
                    'idmedicamento' : grid.columns[1].filters[0].term,
                    'denominacion' : grid.columns[2].filters[0].term,
                    'cantidad' : grid.columns[3].filters[0].term,
                  }
                  $scope.getPaginationRecetaPreparadosEnVentaServerSide();
                });
              }
            };
            $scope.getPaginationRecetaPreparadosEnVentaServerSide = function(){
              $scope.gridOptionsRecetaPreparados.data = [];
              $scope.fBusqueda.idsubalmacen = $scope.fDataVenta.idsubalmacen;
              $scope.datosGrid = {
                paginate : paginationOptionsRecetaPreparadosEnVentas,
                datos : $scope.fBusqueda,
              };
              ventaFarmaciaServices.sListarSolicitudFormulaPorId($scope.datosGrid).then(function (rpta) {
                if(rpta.flag == 1){
                  $scope.gridOptionsRecetaPreparados.data = rpta.datos;
                  $scope.gridOptionsRecetaPreparados.totalItems = rpta.paginate.totalRows;
                  $scope.gridOptionsRecetaPreparados.sumTotal = rpta.sumTotal;
                  // if($scope.gridOptionsRecetaPreparados.sumTotal > 0 ){
                  //   $scope.gridOptionsRecetaPreparados.aCuenta = $scope.gridOptionsRecetaPreparados.data[0].a_cuenta;
                  // }

                }else if(rpta.flag == 0){
                  var pTitle = 'AVISO!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                }else{
                  alert('Huyy algo salió mal...');
                }

              });
              setTimeout(function() {
                $('#aceptar').focus();
              }, 500);
            }
            $scope.numberFormat = function(monto, decimales){
              console.log(monto);
              monto += ''; // por si pasan un numero en vez de un string
              monto = parseFloat(monto.replace(/[^0-9\.\-]/g, '')); // elimino cualquier cosa que no sea numero o punto
              decimales = decimales || 0; // por si la variable no fue pasada
              // si no es un numero o es igual a cero retorno el mismo cero
              if (isNaN(monto) || monto === 0) 
                  return parseFloat(0).toFixed(decimales);
              // si es mayor o menor que cero retorno el valor formateado como numero
              monto = '' + monto.toFixed(decimales);
              var monto_partes = monto.split('.'),
                  regexp = /(\d+)(\d{3})/;
              while (regexp.test(monto_partes[0]))
                  monto_partes[0] = monto_partes[0].replace(regexp, '$1' + ',' + '$2');
              return monto_partes.join('.');
            }
            $scope.aceptar = function () {
             
              // $scope.fDataVenta.medico = {
              //   id: $scope.gridOptionsRecetaPreparados.data[0].idmedico,
              //   descripcion: $scope.gridOptionsRecetaPreparados.data[0].medico
              // }
              var prodDisponible = false;
              angular.forEach($scope.gridOptionsRecetaPreparados.data, function(value, item){
                if( value.estado_detalle_sol == 1 ){
                  prodDisponible = true;
                }
              });
              // validar si hay algun producto de la solicitud que esta disponible
              if( !prodDisponible ){
                var pTitle = 'AVISO!';
                var pType = 'warning';
                var pMessage = 'La solicitud ya no está disponible.';
                pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
                return false;
              }
              $scope.fDataVenta.cliente.idcliente = $scope.gridOptionsRecetaPreparados.data[0].idcliente;
              $scope.fDataVenta.numero_documento = $scope.gridOptionsRecetaPreparados.data[0].num_documento;
              $scope.fDataVenta.idsolicitudformula = $scope.fBusqueda.idsolicitudformula;
              $scope.fDataVenta.medico = {
                'id' : $scope.gridOptionsRecetaPreparados.data[0].idmedico,
                'descripcion' : $scope.gridOptionsRecetaPreparados.data[0].medico,
                'codigo_jj' : $scope.gridOptionsRecetaPreparados.data[0].codigo_jj,
                'fecha_asigna_codigo_jj' : $scope.gridOptionsRecetaPreparados.data[0].fecha_asigna_codigo_jj,
              };
              
              clienteServices.sListarEsteCliente($scope.fDataVenta.cliente).then(function (rpta) {
                $scope.fDataVenta.cliente = rpta.datos[0];
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
                }
                var cestaVacia = true;
                if( $scope.gridOptions.data.length > 0 ){
                  cestaVacia = false;
                }

                angular.forEach($scope.gridOptionsRecetaPreparados.data, function(value, item){
                  $scope.fDataVenta.temporal = {}
                  if( value.estado_detalle_sol == 1 ){
                    $scope.arrTemporal = {
                      'id' : value.idmedicamento,
                      'idmedicamentoalmacen' : value.idmedicamentoalmacen,
                      'descripcion' : value.medicamento,
                      'cantidad' : parseInt(value.cantidad),
                      'precioBase' : parseFloat(value.precio_unitario_sf),
                      'precio' : parseFloat(value.precio_unitario_sf),
                      'descuento' : $scope.fDataVenta.temporal.descuento || '0.00',
                      'idtipocliente' : null,
                      'porcentaje_dcto' : $scope.fDataVenta.temporal.porcentaje_dcto || '0.00',
                      'idtipoclientedescuento' : $scope.fDataVenta.temporal.idtipoclientedescuento || null,
                      'excluye_igv' : 2,
                      'idreceta': null,
                      'idrecetamedicamento': null,
                      'iddetallesolicitud': value.iddetallesolicitud,
                      'idformula_jj': value.idformula_jj,
                      'fecha_asigna_idformula_jj': value.fecha_asigna_idformula_jj,
                      'categoria_jj': value.categoria,
                    };
                    $scope.gridOptions.data.push($scope.arrTemporal);
                  }
                  
                });
                $scope.calcularTotales();
                if( $scope.gridOptions.data.length == 0 ){
                  $scope.fDataVenta.cliente.idcliente = null
                  $scope.fDataVenta.numero_documento = null;
                  $scope.fDataVenta.idsolicitudformula = null;
                  var pTitle = 'AVISO!';
                  var pType = 'warning';
                  var pMessage = 'La solicitud ya no está disponible.';
                  pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
                }
                console.log('data ', $scope.gridOptions.data);
              }); // fin del servicio
              $modalInstance.dismiss('cancel');


              // $scope.gridOptions.data = $scope.gridOptionsRecetaPreparados.data;
            }
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      $scope.btnAgregarVentaParcial = function (){
        if($scope.gridOptions.data.length>0){
          var pTitle = 'AVISO!';
          var pType = 'warning';
          var pMessage = 'Ya tiene una solicitud cargada en la cesta. Elimine la cesta para agregar nueva solicitud ';
          pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
          return false;
        }
        $uibModal.open({
          templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_cargar_receta_preparados',
          size: 'lg',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Cargar Venta a Cuenta';
            $scope.fBusqueda = {};
            $scope.fDataVenta.boolSolicitud = false;
            var paginationOptionsRecetaPreparadosEnVentas = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsRecetaPreparados = {
              rowHeight: 30,
              paginationPageSizes: [10, 50, 100],
              paginationPageSize: 10,
              useExternalPagination: true,
              useExternalSorting: true,
              enableGridMenu: false,
              enableRowSelection: true,
              enableSelectAll: false,
              enableFiltering: false,
              minRowsToShow: 5,
              // enableRowHeaderSelection: false, // fila cabecera
              enableFullRowSelection: false,
              multiSelect: false,
              showColumnFooter: false,
              //showGridFooter: true,
              columnDefs: [
                { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: '10%', visible:true  },
                { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', sort: { direction: uiGridConstants.ASC}},
                { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '10%', cellClass:'text-center'  },
                { field: 'precio_unitario', name: 'precio_unitario', displayName: 'P.U.', width: '10%', cellClass:'text-center'  },
                { field: 'total_detalle', name: 'total_detalle', displayName: 'TOTAL', width: '10%', cellClass:'text-right' },
                
                // { field: 'stockActual', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: '14%',
                //   cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
                //       if(row.entity.stockActual == 0){
                //         return 'text-danger text-bold text-right';
                //       }else{
                //         return 'text-right';
                //       }
                //   }
                // },
                { field: 'estado', type: 'object', name: 'estado', displayName: '', width: '6%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, visible:false,
                  cellTemplate:'<div class=" text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> </label></div>'
                }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsRecetaPreparadosEnVentas.sort = null;
                    paginationOptionsRecetaPreparadosEnVentas.sortName = null;
                  } else {
                    paginationOptionsRecetaPreparadosEnVentas.sort = sortColumns[0].sort.direction;
                    paginationOptionsRecetaPreparadosEnVentas.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationRecetaPreparadosEnVentaServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsRecetaPreparadosEnVentas.pageNumber = newPage;
                  paginationOptionsRecetaPreparadosEnVentas.pageSize = pageSize;
                  paginationOptionsRecetaPreparadosEnVentas.firstRow = (paginationOptionsRecetaPreparadosEnVentas.pageNumber - 1) * paginationOptionsRecetaPreparadosEnVentas.pageSize;
                  $scope.getPaginationRecetaPreparadosEnVentaServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsRecetaPreparadosEnVentas.search = true;
                  // console.log(grid.columns);
                  // console.log(grid.columns[1].filters[0].term);
                  paginationOptionsRecetaPreparadosEnVentas.searchColumn = {
                    'idmedicamento' : grid.columns[1].filters[0].term,
                    'denominacion' : grid.columns[2].filters[0].term,
                    'cantidad' : grid.columns[3].filters[0].term,
                  }
                  $scope.getPaginationRecetaPreparadosEnVentaServerSide();
                });
              }
            };
            $scope.getPaginationRecetaPreparadosEnVentaServerSide = function(){
              $scope.gridOptionsRecetaPreparados.data = [];
              $scope.fBusqueda.idsubalmacen = $scope.fDataVenta.idsubalmacen;
              $scope.datosGrid = {
                paginate : paginationOptionsRecetaPreparadosEnVentas,
                datos : $scope.fBusqueda,
              };
              ventaFarmaciaServices.sListarVentaFormulaACuenta($scope.datosGrid).then(function (rpta) {
                if(rpta.flag == 1){
                  $scope.gridOptionsRecetaPreparados.data = rpta.datos;
                  $scope.gridOptionsRecetaPreparados.totalItems = rpta.paginate.totalRows;
                  $scope.gridOptionsRecetaPreparados.sumTotal = rpta.sumTotal;
                  if($scope.gridOptionsRecetaPreparados.data[0].saldo == null ){
                    $scope.gridOptionsRecetaPreparados.aCuenta = $scope.gridOptionsRecetaPreparados.sumTotal;
                  }else{
                    $scope.gridOptionsRecetaPreparados.aCuenta = parseFloat($scope.gridOptionsRecetaPreparados.sumTotal) - parseFloat($scope.gridOptionsRecetaPreparados.data[0].saldo);
                  }

                }else if(rpta.flag == 0){
                  var pTitle = 'AVISO!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                }else{
                  alert('Huyy algo salió mal...');
                }
              });
              setTimeout(function() {
                $('#aceptar').focus();
              }, 500);
            }
            $scope.numberFormat = function(monto, decimales){
              console.log(monto);
              monto += ''; // por si pasan un numero en vez de un string
              monto = parseFloat(monto.replace(/[^0-9\.\-]/g, '')); // elimino cualquier cosa que no sea numero o punto
              decimales = decimales || 0; // por si la variable no fue pasada
              // si no es un numero o es igual a cero retorno el mismo cero
              if (isNaN(monto) || monto === 0) 
                  return parseFloat(0).toFixed(decimales);
              // si es mayor o menor que cero retorno el valor formateado como numero
              monto = '' + monto.toFixed(decimales);
              var monto_partes = monto.split('.'),
                  regexp = /(\d+)(\d{3})/;
              while (regexp.test(monto_partes[0]))
                  monto_partes[0] = monto_partes[0].replace(regexp, '$1' + ',' + '$2');
              return monto_partes.join('.');
            }
            $scope.aceptar = function () {
             
              // $scope.fDataVenta.medico = {
              //   id: $scope.gridOptionsRecetaPreparados.data[0].idmedico,
              //   descripcion: $scope.gridOptionsRecetaPreparados.data[0].medico
              // }
              // var prodDisponible = false;
              // angular.forEach($scope.gridOptionsRecetaPreparados.data, function(value, item){
              //   if( value.estado_detalle_sol == 1 ){
              //     prodDisponible = true;
              //   }
              // });
              // // validar si hay algun producto de la solicitud que esta disponible
              // if( !prodDisponible ){
              //   var pTitle = 'AVISO!';
              //   var pType = 'warning';
              //   var pMessage = 'La solicitud ya no está disponible.';
              //   pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
              //   return false;
              // }
              $scope.fDataVenta.cliente.idcliente = $scope.gridOptionsRecetaPreparados.data[0].idcliente;
              $scope.fDataVenta.numero_documento = $scope.gridOptionsRecetaPreparados.data[0].num_documento;
              $scope.fDataVenta.idsolicitudformula = $scope.fBusqueda.idsolicitudformula;
              $scope.fDataVenta.idventaorigen = $scope.gridOptionsRecetaPreparados.data[0].idmovimiento;
              $scope.fDataVenta.pago_a_cuenta = '-'+$scope.gridOptionsRecetaPreparados.aCuenta;
              // $scope.fDataVenta.boolSolicitud = false;

              clienteServices.sListarEsteCliente($scope.fDataVenta.cliente).then(function (rpta) {
                $scope.fDataVenta.cliente = rpta.datos[0];
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
                }
                var cestaVacia = true;
                if( $scope.gridOptions.data.length > 0 ){
                  cestaVacia = false;
                }

                angular.forEach($scope.gridOptionsRecetaPreparados.data, function(value, item){
                  $scope.fDataVenta.temporal = {}
                  // if( value.estado_detalle_sol == 1 ){
                    $scope.arrTemporal = {
                      'id' : value.idmedicamento,
                      'idmedicamentoalmacen' : value.idmedicamentoalmacen,
                      'descripcion' : value.medicamento,
                      'cantidad' : parseInt(value.cantidad),
                      'precioBase' : parseFloat(value.precio_unitario_sf),
                      'precio' : parseFloat(value.precio_unitario_sf),
                      'descuento' : $scope.fDataVenta.temporal.descuento || '0.00',
                      'idtipocliente' : null,
                      'porcentaje_dcto' : $scope.fDataVenta.temporal.porcentaje_dcto || '0.00',
                      'idtipoclientedescuento' : $scope.fDataVenta.temporal.idtipoclientedescuento || null,
                      'excluye_igv' : 2,
                      'idreceta': null,
                      'idrecetamedicamento': null,
                      'iddetallesolicitud': value.iddetallesolicitud,
                    };
                    $scope.gridOptions.data.push($scope.arrTemporal);
                  // }
                  
                });
                $scope.calcularTotales();
                // if( $scope.gridOptions.data.length == 0 ){
                //   $scope.fDataVenta.cliente.idcliente = null
                //   $scope.fDataVenta.numero_documento = null;
                //   $scope.fDataVenta.idsolicitudformula = null;
                //   var pTitle = 'AVISO!';
                //   var pType = 'warning';
                //   var pMessage = 'La solicitud ya no está disponible.';
                //   pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
                // }
                console.log('data ', $scope.gridOptions.data);
              }); // fin del servicio
              $modalInstance.dismiss('cancel');


              // $scope.gridOptions.data = $scope.gridOptionsRecetaPreparados.data;
            }
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      // =========================
      $scope.grabar = function (param) {
        var pParam = param || false;
        $scope.fDataVenta.detalle = $scope.gridOptions.data;
        if( $scope.fDataVenta.detalle.length < 1 ){
          $('#temporalProducto').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 2000 });
          return false;
        }
        // if( !$scope.fDataVenta.estemporal){
          ventaFarmaciaServices.sRegistrarVenta($scope.fDataVenta).then(function (rpta) {
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.isRegisterSuccess = true;
              $scope.fDataVenta.idventaregister = rpta.idventaregister;
              $scope.fDataVenta.temporal.producto = null;
              $scope.fDataVenta.temporal.precio = null;
              $scope.fDataVenta.temporal.cantidad = null;
              $scope.fDataVenta.temporal.descuento = null;

            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });

        // }
        /* esto ya no se considera
        else{
          //console.log($scope.fDataVenta);
          //console.log('DATOS');
          //return;
          ventaFarmaciaServices.sRegistrarVentaTemporal($scope.fDataVenta).then(function (rpta) {
            //console.log($scope.fDataVenta);
            //return;
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.isRegisterSuccess = true;
              $scope.fDataVenta.idventaregister = rpta.idventaregister;
              $scope.fDataVenta.temporal.producto = null;
              $scope.fDataVenta.temporal.precio = null;
              $scope.fDataVenta.temporal.cantidad = null;
              $scope.fDataVenta.temporal.descuento = null;

            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });

        }*/
      }
      $scope.nuevo = function () {
        $route.reload();
      }
      $scope.imprimir = function () {
        if( $scope.fDataVenta.idventaregister  ){

          var arrParams = {
            'id': $scope.fDataVenta.idventaregister,
            'es_preparado': $scope.fDataVenta.esPreparado
          }
          console.log('es_preparado' + $scope.fDataVenta.esPreparado);
          ventaFarmaciaServices.sImprimirTicketVenta(arrParams).then(function (rpta) {
            if(rpta.flag == 1){
              var printContents = rpta.html;
              var popupWin = window.open('', 'windowName', 'width=500,height=500');
              popupWin.document.open()
              popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
              popupWin.document.close();

              //  CERRADO DE VENTANA POPUP DESPUES DE IMPRIMIR
              setTimeout(function() {
                popupWin.close();
                $scope.nuevo();
              },1000);

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
      $scope.mismoCliente = function () {
        $scope.fDataVenta.detalle = [];
        $scope.fDataVenta.temporal.cantidad = 1;
        $scope.gridOptions.data = [];
        $scope.fDataVenta.subtotal = null;
        $scope.fDataVenta.igv = null;
        $scope.fDataVenta.total = null;
        $scope.fDataVenta.entrega = null;
        $scope.fDataVenta.vuelto = null;
        $scope.generarNumOrden();
        $scope.generarCodigoTicket();
        if($scope.fDataVenta.idmediopago == 6){
          angular.forEach($scope.fDataVenta.pagoMixto, function(val,index) {
            val.monto = null;
          });
        }
        $('#temporalProducto').focus();
      }

      medioPagoServices.sListarmedioPagoVentaCbo().then(function (rptaMaster) {
        $scope.fDataVenta.pagoMixto = rptaMaster.datos;
        // $scope.fDataVenta.mediopago = $scope.listaMedioPago[0];
      });
      $scope.calcularPagoMixto = function (index){
        if($scope.fDataVenta.esPreparado && $scope.fDataVenta.idtipodocumento == 12){ // 1er pago
          if(index == 0){
            $scope.fDataVenta.pagoMixto[1].monto = (parseFloat($scope.fDataVenta.a_cuenta) - parseFloat($scope.fDataVenta.pagoMixto[0].monto)).toFixed(2);
          }
          if(index == 1){
            $scope.fDataVenta.pagoMixto[0].monto = (parseFloat($scope.fDataVenta.a_cuenta) - parseFloat($scope.fDataVenta.pagoMixto[1].monto)).toFixed(2);
          } 
        }
        else if($scope.fDataVenta.esPreparado && $scope.fDataVenta.pago_a_cuenta){ // 2do pago
          if(index == 0){
            $scope.fDataVenta.pagoMixto[1].monto = (parseFloat($scope.fDataVenta.total_saldo) - parseFloat($scope.fDataVenta.pagoMixto[0].monto)).toFixed(2);
          }
          if(index == 1){
            $scope.fDataVenta.pagoMixto[0].monto = (parseFloat($scope.fDataVenta.total_saldo) - parseFloat($scope.fDataVenta.pagoMixto[1].monto)).toFixed(2);
          } 
        }else{
          if(index == 0){
            $scope.fDataVenta.pagoMixto[1].monto = (parseFloat($scope.fDataVenta.total) - parseFloat($scope.fDataVenta.pagoMixto[0].monto)).toFixed(2);
          }
          if(index == 1){
            $scope.fDataVenta.pagoMixto[0].monto = (parseFloat($scope.fDataVenta.total) - parseFloat($scope.fDataVenta.pagoMixto[1].monto)).toFixed(2);
          }
        }
        
      }
      // $scope.multiPago = function () {
      //   $uibModal.open({
      //     templateUrl: angular.patchURLCI+'ventaFarmacia/ver_popup_multi_pago',
      //     size: 'sm',
      //     scope: $scope,
      //     controller: function ($scope, $modalInstance) {
      //       $scope.titleForm = 'Formulario Multi Pago';
      //       $scope.fDataTemporal = {};
      //       $scope.fDataTemporal.pagoMixto = $scope.fDataVenta.pagoMixto
      //       medioPagoServices.sListarmedioPagoVentaCbo().then(function (rptaMaster) {
      //         $scope.fDataVenta.pagoMixto = rptaMaster.datos;
      //         //$scope.listaMedioPago.splice(0,0,{ id : '', descripcion:'--Seleccione Medio de Pago--'});
      //         $scope.fDataVenta.mediopago = $scope.listaMedioPago[0];

      //       });
      //       $scope.calcularPagoMixto = function (){
      //         $scope.fDataVenta.totalMixto = 0;
      //         var contador = 0;
      //         angular.forEach($scope.fDataTemporal.pagoMixto, function(val,index) {
      //           console.log('val ', val);
      //           console.log('index ', index);
      //           if( val.monto && val.monto != 0 ){
      //             $scope.fDataVenta.totalMixto += parseFloat(val.monto);
      //             contador++;
      //           }
      //         });
      //         var pTitle = 'Advertencia!';
      //         var pType = 'warning';
      //         if( parseFloat($scope.fDataVenta.total) != $scope.fDataVenta.totalMixto){
      //           var pMessage = 'Los montos no coinciden. Verifique los montos a pagar.'
      //         }else if( contador < 2 ){
      //           var pMessage = 'Ingrese mas de un medio de pago mayor a cero.'
      //         }else{
      //           $scope.fDataVenta.boolPagoMixto = true;
      //           $modalInstance.dismiss('cancel');
      //         }
      //         if(!$scope.fDataVenta.boolPagoMixto){ // si no esta correcto lanzar un aviso
      //           pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3000 });
      //         }
              
      //       }
      //       $scope.cancel = function () {
      //         $modalInstance.dismiss('cancel');
      //       }          
      //     }
      //   });
      // }

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
      shortcut.remove('F7');
      shortcut.add("F7",function() {
          if( $scope.fDataVenta.temporal.producto ){
            if( $scope.fDataVenta.temporal.producto.id ){
              $scope.verPopupStocks('lg');
            }else{
              console.log('Desactivado sin id');
            }

          }else{
            console.log('Desactivado sin producto');
          }
      });
      shortcut.remove('F8');
      shortcut.add("F8",function() {
          if( $scope.fDataVenta.temporal.producto ){
            if( $scope.fDataVenta.temporal.producto.id ){
              $scope.verPopupPrincipioActivo('lg');
            }else{
              console.log('Desactivado sin id');
            }

          }else{
            console.log('Desactivado sin producto');
          }
      });
      shortcut.remove('F9');
      shortcut.add("F9",function() {
          if( $scope.fDataVenta.esPreparado ){
            $scope.btnCargarSolicitudFormula();
          }else{
            console.log('Desactivado no es preparado');
          }
      });
      shortcut.remove('F10');
      shortcut.add("F10",function() {
          if( $scope.fDataVenta.esPreparado ){
            $scope.btnAgregarVentaParcial();
          }else{
            console.log('Desactivado no es preparado');
          }
      });
    }
  ])
  .service("ventaFarmaciaServices",function($http, $q) {
    return({
        sRegistrarVenta: sRegistrarVenta,
        sRegistrarVentaTemporal: sRegistrarVentaTemporal,
        sGenerarCodigoOrden: sGenerarCodigoOrden,
        sListarOrdenesVentaCajaCerrada: sListarOrdenesVentaCajaCerrada,
        sListarOrdenesVenta : sListarOrdenesVenta,
        sListarVentaPorId: sListarVentaPorId,
        sListarSolicitudesProducto: sListarSolicitudesProducto,
        sImprimirTicketVenta: sImprimirTicketVenta,
        sListaDetalleVentaColumna:sListaDetalleVentaColumna,
        sListarPagoMixto : sListarPagoMixto,
        sEditarPagoMixto : sEditarPagoMixto,
        sListarVentaFormulaACuenta: sListarVentaFormulaACuenta,
        sListarSolicitudFormulaPorId: sListarSolicitudFormulaPorId,
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
    function sGenerarCodigoOrden() {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/generateCodigoOrdenSalida"
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
    function sListarOrdenesVenta (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ventaFarmacia/listar_ordenes_ventas",
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
    function sListarPagoMixto (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/listar_pago_mixto",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarPagoMixto (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/editar_pago_mixto",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentaFormulaACuenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/lista_detalle_venta_formula", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSolicitudFormulaPorId (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"SolicitudFormula/lista_solicitud_formula_por_id", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });