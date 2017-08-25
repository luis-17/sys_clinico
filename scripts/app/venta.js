angular.module('theme.venta', ['theme.core.services'])
  .controller('ventaController', ['$scope', '$route', '$routeParams', '$controller', '$uibModal', '$sce', '$filter', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI',
      'ventaServices', 
      'empleadoSaludServices', 
      'productoServices', 
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
      'progMedicoServices',
      'canalServices',
    function($scope, $route, $routeParams, $controller, $uibModal, $sce, $filter, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI,
      ventaServices,
      empleadoSaludServices,
      productoServices,
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
      progMedicoServices,
      canalServices ){ 
      
      'use strict'; 
    $scope.init = function (){
      if(($scope.modulo === "historialCitas" || $scope.modulo === "progMedico") && $scope.boolExterno === true){
        console.log($scope.boolExterno, '$scope.boolExterno');      
        console.log($scope.modulo, '$scope.modulo');
      }else{
        console.log('entro por aquí');
        $scope.modulo = 'venta'; 
        
        $scope.isRegisterSuccess = false;
        $scope.cajaAbiertaPorMiSession = false;
        $scope.fCajaAbiertaSession = null;
        $scope.antesDeImprimir = true;
        cajaServices.sGetCajaActualUsuario().then(function (rpta) { 
          if(rpta.flag === 1) {
            $scope.cajaAbiertaPorMiSession = true;
            $scope.fCajaAbiertaSession = rpta.datos;
          }
        });      
      
        // console.log($('#txtNumeroDocumento'));
        setTimeout(function() {
            $('#txtNumeroDocumento').focus(); // console.log($('#txtNumeroDocumento'));
        },1000);
        $controller('clienteController', { 
          $scope : $scope
        });
        // $controller('empresaClienteController', { 
        //   $scope : $scope
        // });
        $scope.fDataVenta = {};
        $scope.fDataVenta.cliente = {};
        $scope.fDataVenta.cliente.actualizado = false;
        $scope.fDataVenta.idventaregister = null;
        $scope.fDataVenta.pacienteExterno = false; 
        $scope.fDataVenta.aleasDocumento = 'TICKET';
        $scope.fDataVenta.ticket = '[ ............... ]';
        $scope.fDataVenta.temporal = {
          especialidad : null,
          producto: null,
          cliente: null
        };
        $scope.boolConvenio = false;
        $scope.fDataVenta.temporal.cantidad = 1;
        $scope.fDataVenta.temporal.boolCantidad = false; // por defecto habilitado
        $scope.fDataVenta.temporal.precioModificado = 2;
        $scope.fDataVenta.temporal.boolEdicionPrecio = true; // por defecto deshabilitado
        // $scope.listaConvenios = [];
        // $scope.listaConvenios.push({id:'', descripcion:'--Seleccione Convenio--'});
        // $scope.listaConvenios.push({id:'1', descripcion:'VIRGEN DE LAS MERCEDES'});
        // $scope.fDataVenta.convenio = $scope.listaConvenios[0].id;
        $scope.fDataVenta.convenio = false;

        tipoDocumentoServices.sListarTipoDocumentoVentaCbo().then(function (rpta) {
          $scope.listaTipoDocumento = rpta.datos;
          $scope.listaTipoDocumento.splice(0,0,{ id : '0', descripcion:'--Seleccione Tipo de Documento--'});
          $scope.fDataVenta.idtipodocumento = $scope.listaTipoDocumento[1].id; // 
          $scope.generarCodigoTicket();
        });
        medioPagoServices.sListarmedioPagoVentaCbo().then(function (rptaMaster) {
          $scope.listaMedioPago = rptaMaster.datos;
          //$scope.listaMedioPago.splice(0,0,{ id : '', descripcion:'--Seleccione Medio de Pago--'});
          $scope.fDataVenta.idmediopago = $scope.listaMedioPago[0].id;
          precioServices.sListarPrecioCbo().then(function (rpta) { 
            //$scope.listaPrecios = rpta.datos; 
            $scope.listaPrecios = [];
            if( $scope.fDataVenta.idmediopago == 1 ){ // SI ES AL CONTADO, SACAR TARJETA 
              angular.forEach(rpta.datos,function (value,key) {
                if( value.id != 1 ){ // SI ES DIFERENTE A TIPO DE PRECIO CON TARJETA VISA 
                  $scope.listaPrecios.push(value);
                }
              });
              $scope.listaPrecios.splice(0,0,{ id : '', descripcion:'PRECIO POR DEFECTO', tipo_precio:'0', porcentaje:'0' });
            }
            if( $scope.fDataVenta.idmediopago == 2 ){ // SI ES TARJETA VISA 
              angular.forEach(rpta.datos,function (value,key) {
                if( value.id == 1 ){ // SI ES IGUAL A TIPO DE PRECIO CON TARJETA VISA
                  $scope.listaPrecios.push(value);
                }
              })
            }
            
            $scope.fDataVenta.precio = $scope.listaPrecios[0]; 
          });
        });

        $scope.generarNumOrden = function () { 
          ventaServices.sGenerarCodigoOrden().then(function (rpta) { 
            $scope.fDataVenta.orden = rpta.codigo_orden; 
            $scope.fDataVenta.idcaja = rpta.idcaja;
            $scope.fDataVenta.idcajamaster = rpta.idcajamaster;
            //console.log($scope.fDataVenta.orden);
          });
        } 
        
        $scope.generarCodigoTicket = function () { 
          if( $scope.fDataVenta.idtipodocumento ){ 
            //console.log($scope.fDataVenta.idtipodocumento); 
            $scope.fDataVenta.idmodulo = 1; // HOSPITAL 
            ventaServices.sGenerarCodigoTicket($scope.fDataVenta).then(function (rpta) { 
              $scope.fDataVenta.ticket = rpta.ticket;
              $scope.fDataVenta.serie = rpta.serie;
              $scope.fDataVenta.numero_serie = rpta.numero_serie;
              if( $scope.fDataVenta.idtipodocumento == '1' ){ // BOLETA 
                $scope.fDataVenta.aleasDocumento = 'TICKET';
              }
              if( $scope.fDataVenta.idtipodocumento == '2' ){ // FACTURA  
                $scope.fDataVenta.aleasDocumento = 'FACT.';
                // alert('Cargara Las Empresas...')
               $scope.btnBuscarEmpresaCliente('lg');
              }
              if( $scope.fDataVenta.idtipodocumento == '6' ){ // RECIBO 
                $scope.fDataVenta.aleasDocumento = 'REC.';
              }
              if( $scope.fDataVenta.idtipodocumento == '3' ){ // OPERACION  
                $scope.fDataVenta.aleasDocumento = 'OPE.';
              }
            });
          }
        }

        $scope.generarNumOrden();
      }
    }
    $scope.init();

    $scope.elegirConvenio = function(){
        //console.log('convenio');
        $scope.limpiarTemporal();
        $scope.limpiarTemporal2();
        $scope.gridOptions.data = [];
        if( $scope.fDataVenta.convenio ){
          $scope.boolConvenio = true;
        }else{
          $scope.boolConvenio = false;
        }
    }
    $scope.cambiarChkPacienteExt = function() { 
      console.log($scope.fDataVenta.pacienteExterno,'$scope.fDataVenta.pacienteExterno'); 
      if( $scope.fDataVenta.pacienteExterno === true ){ 
        $scope.fDataVenta.medico = null; 
      } 
    }
    $scope.getPersonalMedicoAutocomplete = function (value) { 
      var params = {
        search: value,
        sensor: false,
        habilita_externo: true // habilita el autocompletado de medico externo
      }
      return empleadoSaludServices.sListarPersonalSaludCbo(params).then(function(rpta) { return rpta.datos; });
    }
    $scope.getEspecialidadAutocomplete = function (value) {
      var params = {
        search: value,
        sensor: false
      }
      return especialidadServices.sListarEspecialidadesEmpresaSedeDeSession(params).then(function(rpta) { 
        $scope.noResultsLEESS = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLEESS = true; // getSelectedProducto
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedEspecialidad = function($item, $model, $label){
      if( $scope.fDataVenta.temporal.especialidad.idespecialidad == 21 ){
        $scope.fDataVenta.temporal.cantidad = 1;
        $scope.fDataVenta.temporal.boolCantidad = true; // se deshabilita para laboratorio
      }else{
        $scope.fDataVenta.temporal.boolCantidad = false;
      }       
    }
    $scope.getProductoAutocomplete = function (value) { 
      // console.log('boolConvenio ', $scope.boolConvenio);
      if( $scope.fDataVenta.temporal.especialidad === null || angular.isUndefined($scope.fDataVenta.temporal.especialidad) ) { 
        // console.log('load me');
        pinesNotifications.notify({ title: 'Advertencia', text: 'No seleccionó ninguna especialidad.', type: 'danger', delay: 2500 });
        $scope.fDataVenta.temporal = { 
          especialidad : null,
          producto: null,
          cantidad: 1
        };
        return false;
      }
      var params = {
        search: value, 
        especialidadId: $scope.fDataVenta.temporal.especialidad.idespecialidad,
        idtipocliente: $scope.fDataVenta.cliente.idtipocliente || null,
        sensor: false
      }
      if( $scope.boolConvenio ){
        return productoServices.sListarProductosConvenioCbo(params).then(function(rpta) { 
          $scope.noResultsLPSC = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLPSC = true;
          }
          return rpta.datos; 
        });
      }else{
        return productoServices.sListarProductosSessionCbo(params).then(function(rpta) { 
          $scope.noResultsLPSC = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLPSC = true;
          }
          return rpta.datos; 
        });
      }
    }
    
    $scope.onChangeMedioPago = function () { 
      precioServices.sListarPrecioCbo().then(function (rpta) {
        //$scope.listaPrecios = rpta.datos; 
        $scope.listaPrecios = [];
        if( $scope.fDataVenta.idmediopago == 1 ){ // SI ES AL CONTADO, SACAR TARJETA 
          angular.forEach(rpta.datos,function (value,key) {
            if( value.id != 1 ){ // // SI ES DIFERENTE A TIPO DE PRECIO CON TARJETA VISA 
              $scope.listaPrecios.push(value);
            }
          });
          $scope.listaPrecios.splice(0,0,{ id : '', descripcion:'PRECIO POR DEFECTO', tipo_precio:'0', porcentaje:'0' });
        }
        if( $scope.fDataVenta.idmediopago == 2 ){ // SI ES TARJETA VISA 
          //console.log($scope.fDataVenta);
          //console.log($scope.listaPrecios);
          angular.forEach(rpta.datos,function (value,key) { 
            if( value.id == 1 ){ // SI ES IGUAL A TIPO DE PRECIO CON TARJETA VISA 
              $scope.listaPrecios.push(value);
            }
          });
          //console.log($scope.listaPrecios);
        }
        $scope.fDataVenta.precio = $scope.listaPrecios[0];
      });
    }

    $scope.obtenerDatosCliente = function () { 
      if( $scope.fDataVenta.numero_documento ){ 
        clienteServices.sListarEsteClientePorNumDoc($scope.fDataVenta).then(function (rpta) { 
          $scope.fDataVenta.cliente = rpta.datos[0];          
          if( rpta.flag === 1 ){
            $scope.fDataVenta.cliente.actualizado = false;
            pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
          }else{
            $scope.btnNuevoCliente("xlg",$scope.fDataVenta.numero_documento);
          }
        });
      }
    }
    $scope.getSelectedProducto = function (item, model) {
      if($scope.boolConvenio && model.estado_cps == 2){
        $scope.fDataVenta.temporal.producto = null;
        return;
      }

      $scope.fDataVenta.temporal.precio = model.precioSF;
      $scope.fDataVenta.temporal.precio_costo = model.precio_costo;
      $scope.fDataVenta.temporal.precioOriginal = parseFloat(model.precioSF);
      if( model.edicion_precio_en_venta == 1 )
        $scope.fDataVenta.temporal.boolEdicionPrecio = false;
    }
    
    $scope.agregarItem = function () { 
      $('#temporalProducto').focus();

      if( !angular.isObject($scope.fDataVenta.temporal.especialidad) ){ // console.log('especialidad');
        $scope.fDataVenta.temporal.especialidad = null; 
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la especialidad', type: 'warning', delay: 2000 }); 
        return false; 
      }
      if( !angular.isObject($scope.fDataVenta.temporal.producto) ){ // console.log('especialidad');
        $scope.fDataVenta.temporal.producto = null;
        $('#temporalProducto').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
        return false;
      }
      if( !($scope.fDataVenta.temporal.precio >= 0) ){ // console.log('especialidad');
        $scope.fDataVenta.temporal.cantidad = null;
        $('#temporalPrecio').focus().select();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un precio válido', type: 'warning', delay: 2000 });
        return false;
      }
      if( !($scope.fDataVenta.temporal.cantidad >= 1) ){ // console.log('especialidad');
        $scope.fDataVenta.temporal.cantidad = null;
        $('#temporalCantidad').focus().select();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la cantidad', type: 'warning', delay: 2000 });
        return false;
      }
      var productNew = true; 
      var productNewIg = true;
      angular.forEach($scope.gridOptions.data, function(value, key) { 
        if(value.id == $scope.fDataVenta.temporal.producto.id ){ 
          productNew = false;
        }
        if(value.producto.idtipoproducto == '12' || value.producto.idtipoproducto == '16' 
          && $scope.fDataVenta.temporal.producto.idtipoproducto == '12' || $scope.fDataVenta.temporal.producto.idtipoproducto == '16'){ 
          productNewIg = false;
        }
      });
      if( productNew === false ){ 
        pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
        $scope.fDataVenta.temporal.producto= null;
        $scope.fDataVenta.temporal.cantidad= 1;
        return false;
      }
      // if( productNewIg === false ){ 
      //   pinesNotifications.notify({ title: 'Advertencia.', text: 'No se puede agregar otro producto programable', type: 'warning', delay: 3000 });
      //   $scope.fDataVenta.temporal.producto= null;
      //   $scope.fDataVenta.temporal.cantidad= 1;
      //   return false;
      // }
      if( $scope.fDataVenta.temporal.precioModificado == 2 ){
        $scope.fDataVenta.temporal.precioOriginal = null;
      }

      // console.log('agregarItem', $scope.fDataVenta);
      $scope.arrTemporal = { 
        'id' : $scope.fDataVenta.temporal.producto.id,
        'descripcion' : $scope.fDataVenta.temporal.producto.descripcion,
        'producto' : {
          'descripcion': $scope.fDataVenta.temporal.producto.descripcion,
          'si_campania': false,
          'idtipoproducto': $scope.fDataVenta.temporal.producto.idtipoproducto,
          'tipo_producto': $scope.fDataVenta.temporal.producto.tipo_producto
        },
        // 'idempresaespecialidad' : $scope.fDataVenta.temporal.producto.idempresaespecialidad,
        // 'idsede' : $scope.fDataVenta.temporal.producto.idsede,
        // 'idempresa' : $scope.fDataVenta.temporal.producto.idempresa,
        'idespecialidad' : $scope.fDataVenta.temporal.producto.idespecialidad,
        'cantidad' : $scope.fDataVenta.temporal.cantidad,
        'precioBase' : ($scope.fDataVenta.temporal.precio),
        'precio' : ($scope.fDataVenta.temporal.precio),
        'descuento' : $scope.fDataVenta.temporal.descuento || '0.00',
        'precio_modificado' : $scope.fDataVenta.temporal.precioModificado,
        'precio_original' : $scope.fDataVenta.temporal.precioOriginal,
        'tiene_prog_cita' : $scope.fDataVenta.temporal.especialidad.tiene_prog_cita,
        'tiene_venta_prog_cita' : $scope.fDataVenta.temporal.especialidad.tiene_venta_prog_cita,
        'tiene_prog_proc' : $scope.fDataVenta.temporal.especialidad.tiene_prog_proc,
        'tiene_venta_prog_proc' : $scope.fDataVenta.temporal.especialidad.tiene_venta_prog_proc,
        'detalleCupo' : null,
        'tiene_cupo' : false,
        'precio_costo' : ($scope.fDataVenta.temporal.precio_costo),
        //'valor' : parseFloat(($scope.fDataVenta.temporal.precio).slice(4)) - parseFloat($scope.fDataVenta.temporal.descuento || 0)
      }; 
      // $scope.arrTemporal.valor = $scope.arrTemporal.valor.toFixed(2);
      // $scope.arrTemporal.total = (parseFloat($scope.arrTemporal.valor) * parseFloat($scope.arrTemporal.cantidad)).toFixed(2);
      // console.log($scope.arrTemporal);
      $scope.gridOptions.data.push($scope.arrTemporal);
      $scope.fDataVenta.temporal.producto = null;
      $scope.fDataVenta.temporal.cantidad = 1;
      $scope.fDataVenta.temporal.precioModificado = 2;
      $scope.fDataVenta.temporal.boolEdicionPrecio = true;
      $scope.fDataVenta.temporal.precio = null;
      $scope.calcularTotales(); 
      $scope.calcularVuelto(); 
      console.log($scope.fDataVenta);
    }
    $scope.btnMostrarListadoCampanias = function (size) { 
      if(!$scope.fDataVenta.temporal.especialidad){ 
        pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione primero una especialidad.', type: 'warning', delay: 3500 });
        $('#temporalEspecialidad').focus();
        return false;
      }
      $scope.fDataCampania = {};
      $modal.open({
        templateUrl: angular.patchURLCI+'venta/ver_popup_agregar_campania',
        size: size || 'lg',
        //backdrop: 'static',
        scope: $scope,
        //keyboard:false,
        controller: function ($scope, $modalInstance) { 
          // $scope.fDataCampania = {};
          $scope.listaCampaniaPaquete = [];
          $scope.titleFormAddCampania = 'Listado de Campañas'; 
          var arrParams = { 
            datos : {
              'especialidad': $scope.fDataVenta.temporal.especialidad
            }
          }; 
          campaniaServices.sListarCampaniasPaqueteCbo(arrParams).then(function (rpta) { 
            $scope.listaCampaniaPaquete = rpta.datos; 
            $scope.listaCampaniaPaquete.splice(0,0,{ id : 'all', descripcion:'--Seleccione campaña--'}); 
            console.log($scope.listaCampaniaPaquete, 'as' ,$scope.listaCampaniaPaquete[0]); 
            $scope.fDataCampania.campaniaPaquete = $scope.listaCampaniaPaquete[0]; 
          });
          $scope.mySelectionDETPAQGrid = [];
          $scope.gridOptionsDETPAQ = { 
            paginationPageSizes: [50, 100, 500, 1000],
            paginationPageSize: 50,
            minRowsToShow: 8,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: true,
            data: [],
            columnDefs: [ 
              { field: 'campania', name: 'c.descripcion', displayName: 'CAMPAÑA', width: '18%' }, 
              { field: 'paquete', name: 'p.descripcion', displayName: 'PAQUETE', width: '16%' }, 
              { field: 'especialidad', name: 'e.nombre', displayName: 'ESPECIALIDAD', width: '18%' }, 
              { field: 'producto', name: 'pm.descripcion', displayName: 'PRODUCTO' }, 
              { field: 'precio', name: 'denominacion', displayName: 'PRECIO', width: '12%' }
            ],
            onRegisterApi: function(gridApiDETPAQ) {
              $scope.gridApiDETPAQ = gridApiDETPAQ;
              gridApiDETPAQ.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDETPAQGrid = gridApiDETPAQ.selection.getSelectedRows();
              });
              gridApiDETPAQ.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionDETPAQGrid = gridApiDETPAQ.selection.getSelectedRows();
              });
            }
          };
          $scope.getPaginationDETPAQServerSide = function() { 
            if( !$scope.fDataCampania.campaniaPaquete || $scope.fDataCampania.campaniaPaquete.id === 'all' ){ 
              $scope.gridOptionsDETPAQ.data = [];
              return false;
            }
            var arrParams = { 
              datos : {
                'especialidad': $scope.fDataVenta.temporal.especialidad,
                'campaniapaquete': $scope.fDataCampania.campaniaPaquete
              }
            }; 
            //console.log(arrParams); return false; 
            campaniaServices.sListarCampaniasPaqueteDetalle(arrParams).then(function (rpta) { 
              $scope.gridOptionsDETPAQ.data = rpta.datos;
            });
            $scope.mySelectionDETPAQGrid = [];
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            if( $scope.mySelectionDETPAQGrid.length < 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione por lo menos un producto de la campaña', type: 'warning', delay: 3500 });
              //$('#temporalEspecialidad').focus();
              return false;
            }
            // console.log($scope.gridOptions.data, $scope.mySelectionDETPAQGrid); return false;
            var arrDatosFormateados = []; 
            var arrEspecialidadIds = []; 
            //angular.forEach()
            var boolDiferenteEspecialidad = false; 
            angular.forEach($scope.mySelectionDETPAQGrid,function (value, key) { 
              // console.log(value); return false; 
              var strClase;
              if(value.idtipocampania == 1){ // campania 
                strClase = 'label-warning'; 
              }else if(value.idtipocampania == 2){ // cupon 
                strClase = 'label-info'; 
              }

              var tiene_prog_cita; var tiene_venta_prog_cita;
              var tiene_prog_proc; var tiene_venta_prog_proc;
              if(value.idtipoproducto == 12 || value.idtipoproducto == 16){
                tiene_prog_cita = value.tiene_prog_cita;
                tiene_venta_prog_cita = value.tiene_venta_prog_cita;
                tiene_prog_proc = value.tiene_prog_proc;
                tiene_venta_prog_proc = value.tiene_venta_prog_proc;
              }else{
                tiene_prog_cita = false;
                tiene_venta_prog_cita = false;
                tiene_prog_proc = false;
                tiene_venta_prog_proc = false;
              }

              var arrTemporal = {
                'id' : value.idproductomaster,
                'descripcion': value.producto,
                'producto' : { 
                  'descripcion': value.producto,
                  'si_campania': true,
                  'clase': strClase,
                  'tipo': value.tipocampania,
                  'idtipoproducto': value.idtipoproducto, 
                  'tipo_producto': value.tipoproducto,    
                }, 
                'idespecialidad' : value.idespecialidad,
                'cantidad' : 1,
                'precioBase' : (value.precio).slice(4),
                'precio' : (value.precio).slice(4),
                'descuento' : '0.00',
                'valor' : parseFloat((value.precio).slice(4)) - parseFloat(value.descuento || 0),
                'si_campania' : true,
                'idcampania' : value.idcampania,
                'idpaquete' : value.idpaquete,
                'idtipocampania' : value.idtipocampania,
                'tiene_prog_cita' : tiene_prog_cita,
                'tiene_venta_prog_cita' : tiene_venta_prog_cita,
                'tiene_prog_proc' : tiene_prog_proc,
                'tiene_venta_prog_proc' : tiene_venta_prog_proc,
                'detalleCupo' : null,
                'tiene_cupo' : false,
              };
              // console.log(arrTemporal);
              arrTemporal.valor = arrTemporal.valor.toFixed(2);
              arrTemporal.total = (parseFloat(arrTemporal.valor) * parseFloat(arrTemporal.cantidad)).toFixed(2);
              arrDatosFormateados.push(arrTemporal);
              arrEspecialidadIds.push(value.idespecialidad);
              if( $scope.mySelectionDETPAQGrid[0].idespecialidad !== value.idespecialidad){ 
                boolDiferenteEspecialidad = true;
              } 
            }); 
            if( boolDiferenteEspecialidad ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se puede ingresar productos de especialidades diferentes.', type: 'warning', delay: 3500 });
              //$('#temporalEspecialidad').focus();
              return false;
            }
            /* SE INSERTAN LOS DATOS A LA CESTA DE VENTAS */
            angular.forEach(arrDatosFormateados,function (value, key) {
              $scope.gridOptions.data.push(value);
            });
            // console.log(arrDatosFormateados,'arrDatosFormateados');
            $scope.calcularTotales(); 
            $scope.calcularVuelto(); 
            $modalInstance.dismiss('cancel');
          }
          //console.log($scope.mySelectionGrid);
        }
      });
    }
    $scope.btnMostrarListadoSolicitudes = function (size) {
      if(!$scope.fDataVenta.cliente.idhistoria){ 
        pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione primero a un paciente.', type: 'warning', delay: 3500 });
        // $('#temporalEspecialidad').focus(); 
        return false;
      }
      $scope.fDataSolicitud = {};
      $modal.open({
        templateUrl: angular.patchURLCI+'venta/ver_popup_agregar_solicitud', 
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleFormAddSolicitud = 'Listado de Solicitudes'; 
          $scope.listaProductosSolicitud = [ 
            { 'id': 'PR', 'descripcion': 'PROCEDIMIENTOS' }, 
            { 'id': 'EA', 'descripcion': 'EXAMENES AUXILIARES' }, 
            { 'id': 'DO', 'descripcion': 'DOCUMENTOS' } 
          ]; 
          $scope.fDataSolicitud.tipoSolicitud = $scope.listaProductosSolicitud[0]; 
          /* ============================== */
          /* LISTADO DE PRODUCTOS SOLICITUDES */
          /* ============================== */
          var paginationOptionsDETSOL = { 
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDETSOLGrid = [];
          $scope.btnToggleFiltering = function(){ 
            $scope.gridOptionsDETSOL.enableFiltering = !$scope.gridOptionsDETSOL.enableFiltering; 
            $scope.gridApiSOLDET.core.notifyDataChange( uiGridConstants.dataChange.COLUMN ); 
          };
          var pColumnsDef = [ 
              { field: 'id', name: 'id', displayName: 'N° SOLICITUD', width: '10%', enableCellEdit: false }, 
              { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PROD.', width: '12%', enableCellEdit: false }, 
              { field: 'especialidad', name: 'es.nombre', displayName: 'ESPECIALIDAD', width: '20%', enableCellEdit: false }, 
              { field: 'producto', name: 'producto', displayName: 'PROCEDIMIENTO', width: '25%', enableCellEdit: false }, 
              { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: false }, 
              { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA SOLICITUD', width: '14%', enableCellEdit: false }, 
              { field: 'fecha_realizacion', name: 'fecha_realizacion', displayName: 'FECHA RESULTADO', width: '14%', enableCellEdit: false },
              { field: 'observacion', name: 'observacion', displayName: 'OBSERVACIONES', width: '25%', enableCellEdit: false, visible: false }
          ];
          $scope.gridOptionsDETSOL = { 
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            minRowsToShow: 6,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: true,
            data: null,
            columnDefs: pColumnsDef,
            onRegisterApi: function(gridApiSOLDET) {
              $scope.gridApiSOLDET = gridApiSOLDET;
              gridApiSOLDET.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDETSOLGrid = gridApiSOLDET.selection.getSelectedRows();
              });
              gridApiSOLDET.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionDETSOLGrid = gridApiSOLDET.selection.getSelectedRows();
              });
              $scope.gridApiSOLDET.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsDETSOL.sort = null;
                  paginationOptionsDETSOL.sortName = null;
                } else {
                  paginationOptionsDETSOL.sort = sortColumns[0].sort.direction;
                  paginationOptionsDETSOL.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDETSOLServerSide();
              });
              $scope.gridApiSOLDET.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
                // console.log(newPage, pageSize);
                paginationOptionsDETSOL.pageNumber = newPage;
                paginationOptionsDETSOL.pageSize = pageSize;
                paginationOptionsDETSOL.firstRow = (paginationOptionsDETSOL.pageNumber - 1) * paginationOptionsDETSOL.pageSize;
                $scope.getPaginationDETSOLServerSide();
              });
            }
          };
          paginationOptionsDETSOL.sortName = $scope.gridOptionsDETSOL.columnDefs[0].name;
          $scope.getPaginationDETSOLServerSide = function() { 
            // console.log($scope.fDataSolicitud.tipoSolicitud.id); 
            $scope.datosGrid = { 
              paginate : paginationOptionsDETSOL,
              datos : $scope.fDataSolicitud 
            }; 
            $scope.datosGrid.datos.idhistoria = $scope.fDataVenta.cliente.idhistoria; 
            $scope.datosGrid.datos.atendido = 'no'; 
            if( $scope.fDataSolicitud.tipoSolicitud.id === 'PR' ){ 
              $scope.gridOptionsDETSOL.columnDefs = [ 
                { field: 'id', name: 'id', displayName: 'N° SOLICITUD', width: '10%', enableCellEdit: false }, 
                { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PROD.', width: '12%', enableCellEdit: false }, 
                { field: 'especialidad', name: 'es.nombre', displayName: 'ESPECIALIDAD', width: '20%', enableCellEdit: false }, 
                { field: 'producto', name: 'producto', displayName: 'PROCEDIMIENTO', width: '25%', enableCellEdit: false }, 
                { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: false }, 
                { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA SOLICITUD', width: '14%', enableCellEdit: false }, 
                { field: 'fecha_realizacion', name: 'fecha_realizacion', displayName: 'FECHA RESULTADO', width: '14%', enableCellEdit: false, visible: false },
                { field: 'observacion', name: 'observacion', displayName: 'OBSERVACIONES', width: '25%', enableCellEdit: false, visible: false } 
              ]; 
              
              //$scope.datosGrid.datos.tipoSolicitud = $scope.fDataSolicitud.tipoSolicitud;
              solicitudProcedimientoServices.sListarProcedimientosDePaciente($scope.datosGrid).then(function (rpta) { 
                $scope.gridOptionsDETSOL.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsDETSOL.data = rpta.datos;
              });
            }else if( $scope.fDataSolicitud.tipoSolicitud.id === 'EA' ){ 
              $scope.gridOptionsDETSOL.columnDefs = [ 
                  { field: 'id', name: 'id', displayName: 'N° SOLICITUD', width: '10%', enableCellEdit: false }, 
                  { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PROD.', width: '12%', enableCellEdit: false }, 
                  { field: 'especialidad', name: 'es.nombre', displayName: 'ESPECIALIDAD', width: '20%', enableCellEdit: false }, 
                  { field: 'producto', name: 'pm.descripcion', displayName: 'EXAMEN', width: '25%', enableCellEdit: false }, 
                  { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: false }, 
                  { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA SOLICITUD', width: '14%', enableCellEdit: false }, 
                  { field: 'fecha_realizacion', name: 'fecha_realizacion', displayName: 'FECHA RESULTADO', width: '14%', enableCellEdit: false, visible: false },
                  { field: 'indicaciones', name: 'indicaciones', displayName: 'INDICACIONES', width: '25%', enableCellEdit: false, visible: false }
              ]; 
              solicitudExamenServices.sListarSolicitudesExamenDePaciente($scope.datosGrid).then(function (rpta) { 
                $scope.gridOptionsDETSOL.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsDETSOL.data = rpta.datos;
              }); 
            }else if( $scope.fDataSolicitud.tipoSolicitud.id === 'DO' ){
              $scope.gridOptionsDETSOL.columnDefs = [ 
                  { field: 'id', name: 'id', displayName: 'N° SOLICITUD', width: '10%', enableCellEdit: false }, 
                  { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PROD.', width: '12%', enableCellEdit: false }, 
                  { field: 'especialidad', name: 'es.nombre', displayName: 'ESPECIALIDAD', width: '20%', enableCellEdit: false }, 
                  { field: 'producto', name: 'pm.descripcion', displayName: 'DOCUMENTO', width: '25%', enableCellEdit: false }, 
                  { field: 'cantidad', name: 'cantidad', displayName: 'CANT.', width: '6%', enableCellEdit: false }, 
                  { field: 'fecha_otorgamiento', name: 'fec_otorgamiento', displayName: 'FECHA SOLICITUD', width: '14%', enableCellEdit: false }, 
                  { field: 'dias', name: 'total_dias', displayName: 'Dias', width: '14%', enableCellEdit: false },
                  { field: 'indicaciones', name: 'indicaciones', displayName: 'INDICACIONES', width: '25%', enableCellEdit: false, visible: false }
              ]; 
              solicitudCittServices.sListarCittDePaciente($scope.datosGrid).then(function (rpta) { 
                $scope.gridOptionsDETSOL.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsDETSOL.data = rpta.datos;
              }); 
            }
          };
          $scope.getPaginationDETSOLServerSide();

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            if( $scope.mySelectionDETSOLGrid.length < 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione por lo menos un producto de la solicitud', type: 'warning', delay: 3500 });
              //$('#temporalEspecialidad').focus();
              return false;
            }

            var arrDatosFormateados = []; 
            var arrEspecialidadIds = []; 
            //angular.forEach()
            var boolDiferenteEspecialidad = false; 
            angular.forEach($scope.mySelectionDETSOLGrid,function (value, key) {
              var tiene_prog_cita; var tiene_venta_prog_cita;
              var tiene_prog_proc; var tiene_venta_prog_proc;
              // console.log(value.idtipoproducto,'value.idtipoproducto'); 
              if(value.idtipoproducto == 12 || value.idtipoproducto == 16 ){
                tiene_prog_cita = value.tiene_prog_cita;
                tiene_venta_prog_cita = value.tiene_venta_prog_cita;
                tiene_prog_proc = value.tiene_prog_proc;
                tiene_venta_prog_proc = value.tiene_venta_prog_proc;
              }else{
                tiene_prog_cita = false;
                tiene_venta_prog_cita = false;
                tiene_prog_proc = false;
                tiene_venta_prog_proc = false;
              }

              var arrTemporal = {
                'id' : value.idproducto,
                'descripcion': value.producto,
                'producto' : { 
                  'descripcion': value.producto,
                  'si_solicitud': 1,
                  'clase': 'label-primary',
                  'tipo': 'SOLICITUD',
                  'idtipoproducto': value.idtipoproducto, 
                  // 'tipo_producto': value.tipoproducto,  
                  'tipo_producto': 'solicitud'
                }, 
                'idespecialidad' : value.idespecialidad,
                'cantidad' : value.cantidad,
                'precioBase' : (value.precio).slice(4),
                'precio' : (value.precio).slice(4),
                'descuento' : '0.00',
                'valor' : parseFloat((value.precio).slice(4)) - parseFloat(value.descuento || 0),
                'si_solicitud' : 1,
                'idsolicitud' : value.id,
                'tiposolicitud' : value.tiposolicitud,
                'tiene_prog_cita' : tiene_prog_cita,
                'tiene_venta_prog_cita' : tiene_venta_prog_cita,
                'tiene_prog_proc' : tiene_prog_proc,
                'tiene_venta_prog_proc' : tiene_venta_prog_proc,
                // IMPORTANTE PARA SABER A CUAL DE LAS TRES TABLAS IR 
                // 1: examen auxiliar; 2: procedimiento; 3:documento 
              };
              // console.log(arrTemporal,'arrTemporalarrTemporalarrTemporal'); 
              arrTemporal.valor = arrTemporal.valor.toFixed(2);
              arrTemporal.total = (parseFloat(arrTemporal.valor) * parseFloat(arrTemporal.cantidad)).toFixed(2);
              arrDatosFormateados.push(arrTemporal);
              arrEspecialidadIds.push(value.idespecialidad);
              if( $scope.mySelectionDETSOLGrid[0].idespecialidad !== value.idespecialidad){ 
                boolDiferenteEspecialidad = true;
              }
            }); 
            if( boolDiferenteEspecialidad ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se puede ingresar productos de especialidades diferentes.', type: 'warning', delay: 3500 });
              //$('#temporalEspecialidad').focus();
              return false;
            }
            /* SE INSERTAN LOS DATOS A LA CESTA DE VENTAS */
            angular.forEach(arrDatosFormateados,function (value, key) {
              $scope.gridOptions.data.push(value);
            }); 
            console.log($scope.gridOptions.data,'$scope.gridOptions.data');
            $scope.calcularTotales(); 
            $scope.calcularVuelto(); 
            $modalInstance.dismiss('cancel');
          }
          //console.log($scope.mySelectionGrid);
        }
      });
    }
    $scope.calcularTotales = function () { 
      var totales = 0; 
      if( !$scope.fDataVenta.precio.tipo_precio ) { 
        $scope.fDataVenta.precio = $scope.listaPrecios[0];
      }
      angular.forEach($scope.gridOptions.data,function (value, key) { 
        var porcentaje = 1;
        if($scope.fDataVenta.precio.tipo_precio !== '0') { 
          // console.log($scope.fDataVenta.precio); 
          porcentaje = ( parseFloat($scope.fDataVenta.precio.porcentaje) / 100 ) + 1;
        }
        $scope.gridOptions.data[key].precio = (parseFloat(value.precioBase) * porcentaje ).toFixed(2); 
        $scope.gridOptions.data[key].valor = ($scope.gridOptions.data[key].precio * parseFloat($scope.gridOptions.data[key].cantidad)).toFixed(2);
        $scope.gridOptions.data[key].total = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento || 0) ).toFixed(2);
        totales += parseFloat($scope.gridOptions.data[key].total);
      });
      $scope.fDataVenta.total = totales.toFixed(2);
      $scope.fDataVenta.igv = ( totales - (totales / 1.18) ).toFixed(2);
      $scope.fDataVenta.subtotal = ($scope.fDataVenta.total - $scope.fDataVenta.igv).toFixed(2);
    }
    $scope.calcularVuelto = function (){
      var vuelto = 0;
      if($scope.fDataVenta.total !== 0){
        vuelto= parseFloat($scope.fDataVenta.entrega) - parseFloat($scope.fDataVenta.total); 
        $scope.fDataVenta.vuelto = vuelto; 
      }
    }
    $scope.limpiarCampos = function (){
      if($scope.boolConvenio){
        $scope.gridOptions.data = [];
        $scope.boolConvenio = false;
        $scope.fDataVenta.convenio = false;
        $scope.fDataVenta.temporal.producto = null;
        $scope.fDataVenta.temporal.precio = null;
      }
      $scope.fDataVenta.cliente = {};
    }
    $scope.limpiarTemporal = function (){ 
      $scope.fDataVenta.temporal.producto = null;
      $scope.fDataVenta.temporal.precio = null;
      $scope.fDataVenta.temporal.precioModificado = 2;
      $scope.fDataVenta.temporal.boolEdicionPrecio = true;
    }
    $scope.limpiarTemporal2 = function (){ 
      $scope.fDataVenta.temporal.precio = null;
      $scope.fDataVenta.temporal.boolEdicionPrecio = true;
    }
    $scope.precioEditado = function (){
      if( $scope.fDataVenta.temporal.precio != $scope.fDataVenta.temporal.precioOriginal ){
        console.log('Precio cambiado', $scope.fDataVenta.temporal.precio);
        $scope.fDataVenta.temporal.precioModificado = 1;
      }else{
        console.log('Precio original', $scope.fDataVenta.temporal.precio);
        $scope.fDataVenta.temporal.precioModificado = 2;
      }
      
    }
    $scope.btnQuitarDeLaCesta = function (row) { 
      var index = $scope.gridOptions.data.indexOf(row.entity); 
      $scope.gridOptions.data.splice(index,1); 
      $scope.calcularTotales();
      $scope.calcularVuelto();
    } 

    $scope.getDatosModalProgAsistencial = function(){
      especialidadServices.sListarEspecialidadesProgAsistencial().then(function (rpta) {
        $scope.listaEspecialidadesProgAsistencial = rpta.datos;
      });

      canalServices.sListaCanalCbo().then(function (rpta) {
        $scope.listaCanalProgAsistencial = rpta.datos;
      });
    }
    $scope.getDatosModalProgAsistencial();
    $scope.verCupoSelec = function(row){
      $modal.open({
        templateUrl: angular.patchURLCI+'venta/ver_popup_detalle_cita',
        size: 'sm',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.titleFormCita = 'Detalle de Cita';
          $scope.rowCupo = row.entity.detalleCupo;
          //console.log(row.entity);

          $scope.cancelDetCita = function () {
            $modalInstance.dismiss('cancelDetCita');
            $scope.rowCupo = null;
          }
        }
      });
    }

    $scope.btnGenerarCupo = function(row, paramExterno, fnCallback) {  
      $scope.boolExterno = false;
      if(paramExterno && ($scope.modulo == 'progMedico' || $scope.modulo == 'historialCitas')){
        $scope.boolExterno = true;        
      }   
      if(!$scope.boolExterno && !$scope.fDataVenta.cliente.idhistoria){ 
          pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione primero a un paciente.', type: 'warning', delay: 3500 });
        // $('#temporalEspecialidad').focus(); 
        return false;
      }      
      
      $modal.open({
        templateUrl: angular.patchURLCI+'Venta/ver_popup_agregar_cita', 
        size: 'xlg',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {          
          if(!$scope.boolExterno){
            $scope.genCupo = {};
            $scope.genCupo.itemVenta = row.entity; 
            $scope.fBusqueda = {}; 
            var ind = 0;
            angular.forEach($scope.listaEspecialidadesProgAsistencial, function(value, key) {
              if(value.id == $scope.fDataVenta.temporal.especialidad.idespecialidad){
                ind = key;
              }            
            }); 
            $scope.fBusqueda.especialidad = $scope.listaEspecialidadesProgAsistencial[ind]; 
          }

          $scope.titleFormGenCupo = 'Consulta de Citas'; 
          $scope.dateUIDesde = {} ;
          $scope.dateUIDesde.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
          $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
          $scope.dateUIDesde.datePikerOptions = {
            formatYear: 'yy',
            // startingDay: 1,
            'show-weeks': false
          }; 
          $scope.fBusqueda.desde =  $filter('date')(moment().toDate(),'dd-MM-yyyy'); 
          
          $scope.dateUIDesde.openDP = function($event) {
            //console.log($event);
            $event.preventDefault();
            $event.stopPropagation();
            $scope.dateUIDesde.opened = true;
          };

          $scope.getPlanning =  function(valueNext, origen, valuePrev){ 
            console.log("planning :",$scope.fBusqueda);
            blockUI.start('Ejecutando proceso...');
            if(origen){
              $scope.fBusqueda.origen = origen;
            }else{
              $scope.fBusqueda.origen = false;
            }
            $scope.fBusqueda.next = valueNext;
            $scope.fBusqueda.prev = valuePrev;
            console.log("caja :",$scope.fBusqueda);
            progMedicoServices.sPlanningHorasGeneraCita($scope.fBusqueda).then(function (rpta) {
              $scope.fBusqueda.desde = rpta.fecha_consulta;
              $scope.genCupo.haySiguiente = rpta.haySiguiente;
              $scope.genCupo.hayAnterior = rpta.hayAnterior;
              if(rpta.flag == 0){
                $scope.genCupo.hayPlanning = false;
                $scope.genCupo.alerta = rpta.message;
              }else if(rpta.flag == 1){
                $scope.genCupo.hayPlanning = true;
                $scope.genCupo.planning = rpta.planning;
              } 
              blockUI.stop(); 
            }); 
          }
          $scope.getPlanning(false,false,false);      

          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.genCupo.planning = null;
          } 
          $scope.seleccionarCupo = function (cell){
            if(!cell.habilitada){
              return false;
            }
            //console.log(cell);
            $modal.open({
              templateUrl: angular.patchURLCI+'Venta/ver_popup_seleccionar_cita', 
              size: 'lg',
              backdrop: 'static',
              scope: $scope,
              keyboard:false,
              controller: function ($scope, $modalInstance) {
                $scope.titleFormSelecCupo = 'Listado de Cupos';

                $scope.listaEstadosCupo = [
                  {id: 1, descripcion: 'DISPONIBLE' },
                  {id: 2, descripcion: 'TODOS' }
                ]; 
                $scope.fBusqueda.estado = $scope.listaEstadosCupo[0]; 
                $scope.fBusqueda.canal = $scope.listaCanalProgAsistencial[0];
                $scope.fBusqueda.programacion = cell;
                $scope.genCupo.mySelectionGrid =[];
                $scope.gridOptionsCupos = { 
                  paginationPageSizes: [10, 50, 100, 500, 1000, 10000],
                  paginationPageSize: 100,
                  minRowsToShow: 10,
                  useExternalPagination: false,
                  useExternalSorting: false,
                  useExternalFiltering : false,
                  enableGridMenu: true,
                  enableRowSelection: true,
                  enableFullRowSelection: true,
                  enableSelectAll: false,
                  enableFiltering: false,                  
                  multiSelect: false,
                  columnDefs: [ 
                    { field: 'iddetalleprogmedico', name: 'iddetalleprogmedico', displayName: 'ID', width:'10%', visible:false, },
                    { field: 'numero_cupo', name: 'numero_cupo', displayName: 'Nº CUPO', width:'10%' },
                    { field: 'turno', name: 'turno', displayName: 'TURNO', width:'14%' },
                    { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width:'32%' },
                    { field: 'ticket', name: 'ticket', displayName: 'Nº TICKET', width:'15%' },
                    { field: 'si_adicional', name: 'tipo_cupo', displayName: 'ADICIONAL', width:'12%', /*type: 'object',*/ enableFiltering: false, enableSorting: false, 
                      cellTemplate:'<div class="ui-grid-cell-contents text-center"><label ng-if="COL_FIELD" class="label label-warning"><i class="fa fa-check"></i></label></div>' 
                    },
                    { field: 'estado_cupo', type: 'object' , name: 'estado_cupo_str', displayName: 'ESTADO', width:'12%', enableFiltering: false,
                      cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>' 
                    } 
                  ],
                  onRegisterApi: function(gridApi) {
                    $scope.gridApiCupos = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.genCupo.mySelectionGrid = gridApi.selection.getSelectedRows();                      
                      $scope.aceptarGenCupo();
                    });
                  }
                };

                $scope.getListaCuposCanal = function (){
                  progMedicoServices.sListarCuposCanal($scope.fBusqueda).then(function (rpta) {
                    $scope.gridOptionsCupos.data = rpta.datos;
                    $scope.gridOptionsCupos.totalItems = rpta.paginate.totalRows;                    
                  });
                }
                $scope.getListaCuposCanal();

                $scope.aceptarGenCupo = function(){
                  if( $scope.genCupo.mySelectionGrid.length == 1){
                    //console.log($scope.genCupo.mySelectionGrid[0]);
                    if($scope.genCupo.mySelectionGrid[0].estado_cupo.bool == 2){
                       if(($scope.modulo == 'progMedico' || $scope.modulo == 'historialCitas')  && $scope.boolExterno){
                        $uibModal.open({
                          templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_confirmar_accion',
                          size: '',
                          backdrop: 'static',
                          keyboard:false,
                          scope: $scope,
                          controller: function ($scope, $modalInstance) {
                            $scope.fDataModal = {};
                            $scope.fDataModal.tipo = 'reprogramar';                            
                            $scope.genCupo.mySelectionGrid[0].fecha_programada = $scope.fBusqueda.programacion.fecha;
                            $scope.genCupo.mySelectionGrid[0].fecha_str = $scope.fBusqueda.programacion.fecha_str;
                            $scope.genCupo.mySelectionGrid[0].ambiente = $scope.fBusqueda.programacion.ambiente.numero_ambiente; 
                            $scope.fDataModal.nuevaCita = $scope.genCupo.mySelectionGrid[0];
                            $scope.fDataModal.oldCita = $scope.genCupo.oldCita;
                            if(paramExterno && $scope.modulo == 'historialCitas'){
                              $scope.titleFormModal = 'MODIFICAR CITA';
                              $scope.fDataModal.mensaje = '¿Realmente desea modificar la cita?';
                              $scope.fDataModal.modifCita = true;
                              $scope.fDataModal.reprogCita = false;
                            }else{
                              $scope.titleFormModal = 'REPROGRAMAR CITA';
                              $scope.fDataModal.mensaje = '¿Realmente desea reprogramar la cita?';
                              $scope.fDataModal.modifCita = false;
                              $scope.fDataModal.reprogCita = true;
                            }
                            
                            //console.log($scope.fDataModal.oldCita);
                            //console.log($scope.fDataModal.nuevaCita);
         
                            $scope.btnOk = function(){ 
                              $scope.btnCancel();                 
                              
                              $scope.genCupo.seleccion = angular.copy($scope.fDataModal.nuevaCita);
                              console.log("nuevaCita",$scope.genCupo.seleccion);                        
                              $scope.cancelSelCupo();
                              $scope.cancel(); 
                              fnCallback();
                            }  

                            $scope.btnCancel = function(){
                              $modalInstance.dismiss('btnCancel');
                            } 
                          }
                        });
                      }else{
                        if($scope.fDataVenta.temporal.especialidad.idespecialidad !== $scope.fBusqueda.especialidad.id){
                          var pTitle = 'Aviso!';
                          var pType = 'warning';
                          var pText = 'Solo puede seleccionar cupo para especialidad seleccionada en venta';
                          pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 });
                          return;
                        }
                        var encontro = false;
                        angular.forEach($scope.gridOptions.data, function(value, key) {
                          //console.log(value);
                          if( value.tiene_cupo && 
                              value.detalleCupo.iddetalleprogmedico == $scope.genCupo.mySelectionGrid[0].iddetalleprogmedico){
                              encontro = true;
                          }
                        });
                        if(!encontro){         
                          $scope.genCupo.mySelectionGrid[0].medico = $scope.fBusqueda.programacion.medico;
                          $scope.genCupo.mySelectionGrid[0].fecha_programada = $scope.fBusqueda.programacion.fecha;
                          $scope.genCupo.mySelectionGrid[0].fecha_str = $scope.fBusqueda.programacion.fecha_str;
                          $scope.genCupo.mySelectionGrid[0].ambiente = $scope.fBusqueda.programacion.ambiente.numero_ambiente;
                          $scope.genCupo.itemVenta.detalleCupo = angular.copy($scope.genCupo.mySelectionGrid[0]);
                          $scope.genCupo.itemVenta.tiene_cupo = true;                          
                          $scope.gridApiCombo.grid.refreshRows();
                          $scope.cancelSelCupo();
                          $scope.cancel();                                                    
                        }else{
                          var pTitle = 'Aviso!';
                          var pType = 'warning';
                          var pText = 'Cupo ha sido seleccionado para otro item';
                          pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 }); 
                        }
                      } 
                                          
                    }else{
                      var pTitle = 'Aviso!';
                      var pType = 'warning';
                      var pText = 'Debe seleccionar un cupo disponible';
                      pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 }); 
                    }
                  }
                  
                }

                $scope.cancelSelCupo = function(){
                  $modalInstance.dismiss('cancelSelCupo');
                }
              }
            });
          }
          $scope.hoverInHoras = function(cell) { 
            if(cell.headerHora){
              angular.forEach($scope.genCupo.planning.horas,function(val,key) { 
                if( val.timestamp >= cell.tmp_hora_inicio && val.timestamp < cell.tmp_hora_fin ){ 
                  $scope.genCupo.planning.horas[key].classHoveredHora = ' hovered-hour'; 
                  
                }
              });
              angular.forEach($scope.genCupo.planning.ambientes,function(val,key) { 
                if( cell.ambiente.idambiente == val.idambiente ){ 
                  $scope.genCupo.planning.ambientes[key].classHoveredAmbiente = ' hovered-ambiente'; 
                } 
              });
            }
          }
          $scope.hoverOutHoras = function(cell) { 
            if(cell.headerHora){
              angular.forEach($scope.genCupo.planning.horas,function(val,key) { 
                if( val.timestamp >= cell.tmp_hora_inicio && val.timestamp < cell.tmp_hora_fin ){ 
                  $scope.genCupo.planning.horas[key].classHoveredHora = ' '; 
                }
              });
              angular.forEach($scope.genCupo.planning.ambientes,function(val,key) { 
                if( cell.ambiente.idambiente == val.idambiente ){ 
                  $scope.genCupo.planning.ambientes[key].classHoveredAmbiente = ' '; 
                }
              });
            }
          }
        }
      });
    }

    $scope.btnGenerarCupoProc = function(row, paramExterno, fnCallback) {    
      if( !$scope.fDataVenta.cliente.idhistoria){ 
          pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione primero a un paciente.', type: 'warning', delay: 3500 });
        // $('#temporalEspecialidad').focus(); 
        return false;
      }       
      console.log('row',row);    
      console.log('fDataVenta',$scope.fDataVenta);
      $modal.open({
        templateUrl: angular.patchURLCI+'Venta/ver_popup_agregar_cita_proc', 
        size: 'xlg',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.fBusqueda = {};
          $scope.genCupo = {};
          $scope.genCupo.itemVenta = row.entity;  
          var ind = 0;
          angular.forEach($scope.listaEspecialidadesProgAsistencial, function(value, key) {
            if(value.id == $scope.fDataVenta.temporal.especialidad.idespecialidad){
              ind = key;
            }            
          }); 
          $scope.fBusqueda.especialidad = $scope.listaEspecialidadesProgAsistencial[ind];          
          $scope.titleFormGenCupo = 'Consulta de Procedimientos'; 
          $scope.dateUIDesde = {} ;
          $scope.dateUIDesde.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
          $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
          $scope.dateUIDesde.datePikerOptions = {
            formatYear: 'yy',
            'show-weeks': false
          }; 
          $scope.fBusqueda.desde =  $filter('date')(moment().toDate(),'dd-MM-yyyy');
          $scope.fBusqueda.producto =  $scope.genCupo.itemVenta.producto; 
          
          $scope.dateUIDesde.openDP = function($event) {
            //console.log($event);
            $event.preventDefault();
            $event.stopPropagation();
            $scope.dateUIDesde.opened = true;
          };

          $scope.genCupo.mySelectionGrid =[];
          $scope.gridOptionsProc = { 
            useExternalPagination: false,
            useExternalSorting: false,
            useExternalFiltering : false,
            enableGridMenu: true,
            enableRowSelection: true,
            enableFullRowSelection: true,
            enableSelectAll: false,
            enableFiltering: false,                  
            multiSelect: false,
            columnDefs: [ 
              { field: 'idprogmedico', name: 'idprogmedico', displayName: 'ID', width:'10%'},
              { field: 'fecha_programada', name: 'fecha_programada', displayName: 'FECHA'},
              { field: 'medico', name: 'medico', displayName: 'MEDICO', width:'30%'},
              { field: 'numero_ambiente', name: 'numero_ambiente', displayName: 'AMBIENTE'},
              { field: 'hora_inicio', name: 'hora_inicio', displayName: 'HORA INICIO', width:'15%'},
              { field: 'hora_fin', name: 'hora_fin', displayName: 'HORA FIN'} 
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApiCupos = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.genCupo.mySelectionGrid = gridApi.selection.getSelectedRows();                      
                $scope.genCupo.itemVenta.detalleCupo = angular.copy($scope.genCupo.mySelectionGrid[0]);
                $scope.genCupo.itemVenta.tiene_cupo = true;                          
                $scope.gridApiCombo.grid.refreshRows();
                $scope.cancel(); 
              });
            }
          };

          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.genCupo.planning = null;
          }

          $scope.getPlanning =  function(valueNext, origen, valuePrev){ 
            console.log("planning :",$scope.fBusqueda);
            blockUI.start('Ejecutando proceso...');
            if(origen){
              $scope.fBusqueda.origen = origen;
            }else{
              $scope.fBusqueda.origen = false;
            }
            $scope.fBusqueda.next = valueNext;
            $scope.fBusqueda.prev = valuePrev;
            console.log("caja :",$scope.fBusqueda);
            progMedicoServices.sListaProgramacionProc($scope.fBusqueda).then(function (rpta) {
              console.log('rpta', rpta);
              $scope.fBusqueda.desde = rpta.fecha_consulta;
              $scope.genCupo.haySiguiente = rpta.haySiguiente;
              $scope.genCupo.hayAnterior = rpta.hayAnterior;
              $scope.gridOptionsProc.data = rpta.datos; 

              blockUI.stop(); 
            }); 
          } 

          $scope.getPlanning(false,'ini',false);
        }
      });
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
      columnDefs: [
        { field: 'id', displayName: 'Item', width: '5%', enableCellEdit: false },
        { field: 'producto', width: '40%', displayName: 'Descripción', type: 'object', enableCellEdit: false, 
          cellTemplate:'<span class="ml-xs"> {{ COL_FIELD.descripcion }} <label ng-show="COL_FIELD.si_campania || COL_FIELD.si_solicitud" style="box-shadow: 1px 1px 0 black; margin-left: 8px; display: inline;" class="label {{COL_FIELD.clase}}"> {{COL_FIELD.tipo}} </label></span>'
        },
        { field: 'cantidad', displayName: 'Cant.', width: '6%', enableCellEdit: false },
        { field: 'precio', displayName: 'Precio', width: '9%', enableCellEdit: false },
        { field: 'valor', displayName: 'Valor', width: '9%', enableCellEdit: false },
        { field: 'descuento', displayName: 'Descuento', width: '9%', enableCellEdit: true },
        { field: 'total', displayName: 'Total', width: '12%', enableCellEdit: false }, 
        { field: 'accion', displayName: 'Acción', width: '10%', enableCellEdit: false, 
          cellTemplate:'<div class="block text-center ui-grid-cell-contents">' + 
            '<button type="button" ng-if="row.entity.producto.idtipoproducto == 12 && row.entity.tiene_prog_cita == 1 && row.entity.tiene_venta_prog_cita == 1 && row.entity.detalleCupo == null" class="btn btn-sm btn-info mr-xs" ng-click="grid.appScope.btnGenerarCupo(row)"> GENERAR CUPO </button>' + 
            '<button type="button" ng-if="row.entity.producto.idtipoproducto == 16 && row.entity.tiene_prog_proc == 1 && row.entity.tiene_venta_prog_proc == 1 && row.entity.detalleCupo == null" class="btn btn-sm btn-info-p mr-xs" ng-click="grid.appScope.btnGenerarCupoProc(row)"> GENERAR PROC. </button>' +
            '<button data-pulsate="{glow:false}" type="button" style="font-size:12px;z-index:9999;" class="btn btn-sm btn-info" ng-if="row.entity.producto.idtipoproducto == 12 && row.entity.tiene_prog_cita == 1 && row.entity.tiene_venta_prog_cita == 1 && row.entity.detalleCupo != null" ng-click="grid.appScope.verCupoSelec(row);"> #Cupo: {{row.entity.detalleCupo.numero_cupo}} </button>'+  
            '<button data-pulsate="{glow:false}" type="button" style="font-size:12px;z-index:9999;" class="btn btn-sm btn-info-p" ng-if="row.entity.producto.idtipoproducto == 16 && row.entity.tiene_prog_proc == 1 && row.entity.tiene_venta_prog_proc == 1 && row.entity.detalleCupo != null" ng-click="grid.appScope.verCupoSelec(row);"> #Prog: nº {{row.entity.detalleCupo.idprogmedico}} </button>'+
            '<button type="button" style="border-left:1px solid #fafafa!important;margin-left: -6px;" ng-if="row.entity.producto.idtipoproducto == 12 && row.entity.tiene_prog_cita == 1 && row.entity.tiene_venta_prog_cita == 1 && row.entity.detalleCupo != null" tooltip="Quitar Cupo" tooltip-placement="left" class="btn btn-sm btn-info" ng-click="grid.appScope.anularCupoSelec(row);"> <i class="fa fa-times"></i> </button> ' + 
            '<button type="button" style="border-left:1px solid #fafafa!important;margin-left: -6px;" ng-if="row.entity.producto.idtipoproducto == 16 && row.entity.tiene_prog_proc == 1 && row.entity.tiene_venta_prog_proc == 1 && row.entity.detalleCupo != null" tooltip="Quitar Cupo" tooltip-placement="left" class="btn btn-sm btn-info-p" ng-click="grid.appScope.anularCupoSelec(row);"> <i class="fa fa-times"></i> </button> ' + 
            '<button type="button" class="btn btn-sm btn-danger " ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button></div>' 
        }
      ]

      ,onRegisterApi: function(gridApiCombo) { 
        $scope.gridApiCombo = gridApiCombo; 
        gridApiCombo.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
          $scope.calcularTotales(); 
          $scope.calcularVuelto(); 
          $scope.$apply();
        }); 
      }
    };
    $scope.anularCupoSelec = function(row){
      row.entity.detalleCupo = null;
      row.entity.tiene_cupo = false;
      $scope.gridApiCombo.grid.refreshRows();
    }
    $scope.getTableHeight = function() {
       var rowHeight = 30; // your row height 
       var headerHeight = 30; // your header height 
       return {
          height: ($scope.gridOptions.data.length * rowHeight + headerHeight + 30) + "px"
       };
    };
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
              $scope.limpiarCampos();
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionClienteGrid = gridApi.selection.getSelectedRows();
                $scope.fDataVenta.cliente = $scope.mySelectionClienteGrid[0]; console.log($scope.fDataVenta.cliente);
                $scope.fDataVenta.numero_documento = $scope.mySelectionClienteGrid[0].num_documento;                
                $scope.fDataVenta.cliente.actualizado = false;
                $modalInstance.dismiss('cancel');
                setTimeout(function() {
                  $('#txtNumeroDocumento').focus(); console.log('focus me',$('#txtNumeroDocumento'));
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
              { field: 'descripcion', name: 'descripcion', displayName: 'Empresa' },
              
              
              
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
      $modal.open({
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
                
                // if( $scope.modulo === 'venta' ){
                $scope.fDataVenta.ruc = rpta.ruc;
                $scope.fDataVenta.empresa.id = rpta.idempresacliente;

                // }else{
                //   $scope.getPaginationEmpresaClienteServerSide();
                // }
                console.log($scope.fDataVenta);
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'danger';
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
    // =========================

    $scope.grabar = function (param) { 
      var pParam = param || false;          

      $scope.fDataVenta.detalle = $scope.gridOptions.data;
      if( $scope.fDataVenta.detalle.length < 1 ){ 
        $('#temporalEspecialidad').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 2000 }); 
        return false; 
      } 

      ventaServices.sRegistrarVenta($scope.fDataVenta).then(function (rpta) { 
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success'; 
          $scope.isRegisterSuccess = true; 
          $scope.fDataVenta.idventaregister = rpta.idventaregister;
          $scope.fDataVenta.temporal.producto = null;
          $scope.fDataVenta.temporal.precio = null;
          $scope.fDataVenta.temporal.cantidad = null;
          $scope.fDataVenta.temporal.descuento = null;

          angular.forEach($scope.gridOptions.data, function(value, key) {
            value.tiene_cupo = false;
            value.detalleCupo = null;
          });

          // $scope.fDataVenta = {}; 
          // $route.reload();

        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'danger';
        }else{
          alert('Algo salió mal...');
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    }
    $scope.nuevo = function () { 
      $route.reload(); 
    }
    $scope.imprimir = function () { 
      if( $scope.fDataVenta.idventaregister  ){
        
        var arrParams = {
          'id': $scope.fDataVenta.idventaregister
        }
        ventaServices.sListarVentaPorId(arrParams).then(function (rptaMaster) { 
          if( rptaMaster.flag == 1 ){ // VALIDAR QUE NO ESTÉ EN ESPERA 
            cajaActualServices.sImprimirTicketVenta(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                var printContents = rpta.html;
                var popupWin = window.open('', 'windowName', 'width=500,height=500');
                popupWin.document.open()
                popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
                popupWin.document.close();
              }else { 
                if(rpta.flag == 0) { // ALGO SALIÓ MAL
                  var pTitle = 'Error!';
                  var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                  var pType = 'error';
                }
                if(rpta.flag == 2) { // FALTA APROBAR, ESTÁ EN ESPERA.
                  var pTitle = 'Advertencia';
                  var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
                  var pType = 'warning';
                }
                if(rpta.flag == 3) { // YA ESTA IMPRESO, NO SE PUEDE REIMPRIMIR
                  var pTitle = 'Advertencia';
                  var pText = 'Ya se imprimió el ticket. Solicite la reimpresión del ticket';
                  var pType = 'warning';
                }
                if(rpta.flag == 4) { // SOLICITUD DE IMPRESION EN PROCESO, EL AREA DE SISTEMAS ESTÁ EVALUANDO LA SOLICITUD.
                  var pTitle = 'Información';
                  var pText = 'Solicitud de reimpresión <strong> en proceso </strong>. El Área de Sistemas está evaluando su solicitud.';
                  var pType = 'info';
                }
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
              }
            });
          }else{
            pinesNotifications.notify({ title: 'Advertencia', text: 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión', type: 'warning', delay: 3500 });
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
      $('#temporalEspecialidad').focus();
    }

    $scope.verificaUsuario = function(){
      if($scope.fDataVenta.numero_documento == null || $scope.fDataVenta.numero_documento == ''){
        $('#txtNumeroDocumento').focus();
        pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado cliente', type: 'warning', delay: 2000 }); 
        return false; 
      }

      if(!$scope.fDataVenta.cliente.actualizado){
        $scope.actualizaDatosCliente('');
        return;
      }else{
        $scope.grabar();
      }  
    }

    $scope.actualizaDatosCliente = function(size){
      $modal.open({
        templateUrl: angular.patchURLCI+'cliente/ver_popup_actualiza_cliente',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'Actualizar Datos de Clientes';
          
          $scope.aceptar = function(){
            clienteServices.sActualizarDatosCliente($scope.fDataVenta.cliente).then(function(rpta){
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success'; 
                $modalInstance.dismiss('cancel');
                $scope.fDataVenta.cliente.actualizado = true;
                $scope.grabar();
              }else if(rpta.flag == 0){
                $scope.fDataVenta.cliente.actualizado = false;
                var pTitle = 'Advertencia!';
                var pType = 'danger';
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              }else{
                alert('Algo salió mal...');
              }
            });
          }
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    
    /* ============================ */ 
    /* ATAJOS DE TECLADO NAVEGACION */ 
    /* ============================ */ 
    // shortcut.remove({ 
    //   // type: 'hold',
    //   mask: 'F2'
    //   // list: 'another'
    // });
    shortcut.remove('F2');
    shortcut.add("F2",function($event) { 
      // console.log($scope.modulo);
      // if($scope.modulo === 'venta'){ $event.preventDefault();
        //$event.preventDefault();
        $scope.verificaUsuario(); 
        // $scope.grabar(); 
        //$event.stopPropagation();
      // } 
      //shortcut.stop();
    }); 
    shortcut.remove('F3');
    shortcut.add("F3",function($event) { 
      //console.log($event);
      //$event.preventDefault();
      if($scope.isRegisterSuccess == true){ 
        $scope.nuevo(); 
      }else{
        $route.reload(); 

      }
      setTimeout(function() {
        $('#txtNumeroDocumento').focus(); // console.log($('#txtNumeroDocumento'));
      },1000);
       // 
      //$event.stopPropagation();
      //shortcut.stop();
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
        $('#temporalEspecialidad').focus();
    });
    shortcut.remove('F7');
    shortcut.add("F7",function() { 
        $scope.goToUrl('/historial-citas'); 
    });
  }])
  .service("ventaServices",function($http, $q) { 
    return({
        sRegistrarVenta: sRegistrarVenta, 
        sGenerarCodigoOrden: sGenerarCodigoOrden,
        sGenerarCodigoTicket: sGenerarCodigoTicket,
        sListarOrdenesVentaCajaCerrada: sListarOrdenesVentaCajaCerrada,
        sListarVentaPorId: sListarVentaPorId,
        sListarSolicitudesProducto: sListarSolicitudesProducto
    });

    function sRegistrarVenta(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/registrar_venta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarCodigoOrden() { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"venta/generateCodigoOrden" 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarCodigoTicket (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"venta/generateCodigoTicket", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarOrdenesVentaCajaCerrada (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"venta/listar_ordenes_ventas_cerradas", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVentaPorId (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"venta/listar_esta_venta_por_id", 
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
  });