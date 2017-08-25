angular.module('theme.cajaChica', ['theme.core.services'])
  .controller('cajaChicaController', ['$scope', '$route', 'blockUI','$rootScope','$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', '$filter', '$interval','uiGridConstants', 'pinesNotifications', '$controller',
    'bancoServices',
    'cajaChicaServices',
    'operacionServices',
    'subOperacionServices',
    'especialidadServices',
    'tipoDocumentoServices',
    'ModalReporteFactory',
    'empresaServices',
    'asientoContableServices',
    'historialCajaChicaServices',
    function($scope, $route, blockUI, $rootScope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, $filter, $interval, uiGridConstants, pinesNotifications, $controller
      , bancoServices
      , cajaChicaServices
      , operacionServices
      , subOperacionServices
      , especialidadServices 
      , tipoDocumentoServices
      , ModalReporteFactory
      , empresaServices
      , asientoContableServices 
      , historialCajaChicaServices
      ){
    'use strict'; 
    $scope.modulo = 'cajaChica'; 
    $scope.cajaAbiertaPorMiSession = false;
    $scope.metodos = {}; 
    $scope.arr = {}; 
    $scope.dirImagesSemaforo = $scope.dirImages + 'semaforos/'; // 
    $scope.getCajaActualUsuario = function () {
      cajaChicaServices.sVerificarCajaChicaUsuario().then(function(rpta){ 
        if(rpta.flag == 1){
          $scope.cajaAbiertaPorMiSession = true;
          $scope.arr.cajaChica = rpta.cajaChica;
          console.log('$scope.arr.cajaChica', $scope.arr.cajaChica);
          $scope.getPaginationServerSide();
        }else if(rpta.flag == 0){ 
          $scope.cajaAbiertaPorMiSession = false;
          $scope.btnAperturaCaja();    
        }
      }); 
    }
    $scope.getCajaActualUsuario(); 

    // MODAL
    $scope.btnAperturaCaja = function(url){  
      cajaChicaServices.sVerificarCajaChicaUsuario().then(function(rpta){
        if(rpta.flag == 1){
          $route.reload();
          console.log('caja abierta'); 
        }else if(rpta.flag==0){
          $uibModal.open({ 
            templateUrl: angular.patchURLCI+'CajaChica/ver_popup_formulario',
            size: '',
            backdrop: 'static',
            keyboard:false,
            scope: $scope,
            controller: function ($scope, $modalInstance) {
              $scope.titleForm = 'Apertura de Caja Chica';
              $scope.fData = {};              
              $scope.listaCajas = [];

              $scope.cargarCajaChicaDisponible = function(){
                cajaChicaServices.sListarCajaChicaDisponibleCbo().then(function(rpta){
                  if(rpta.flag == 1){
                    $scope.listaCajas = rpta.datos;                     
                    //$scope.listaCajas.splice(0,0,{ id : '', nombre:'-- Seleccionar Caja --'});
                  }else{
                    console.log('rpta',rpta);
                    $scope.listaCajas = [];
                    $scope.listaCajas.splice(0,0,{ id : '', nombre:'-- No hay caja asignada --'});
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                  }
                  $scope.cajaChica = $scope.listaCajas[0];
                  $scope.fData = angular.copy($scope.listaCajas[0]);
                  $scope.fData.fecha_apertura = $filter('date')(moment().toDate(),'dd-MM-yyyy')
                  $scope.cargarSaldoAnterior($scope.fData);
                });
              }
              $scope.cargarCajaChicaDisponible();

              $scope.cargarSaldoAnterior = function(caja){
                $scope.fData.saldo_anterior = null;
                $scope.fData.monto_inicial = null;
                cajaChicaServices.sListarSaldoAnterior(caja).then(function(rpta){
                  var pTitle;
                  var pType;
                  if(rpta.flag==1){
                    $scope.fData.saldo_anterior = rpta.datos;
                    $scope.calcularMontoInicio();
                  }else if(rpta.flag==0){
                    $scope.fData.saldo_anterior = rpta.datos;
                    $scope.calcularMontoInicio();
                    pTitle = 'Advertencia!';
                    pType = 'warning';
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 }); 
                  }else if(rpta.flag==2){
                    $scope.calcularMontoInicio();
                  }else{
                    alert('Error inesperado');
                  }
                });
              }
              $scope.calcularMontoInicio = function(){
                $scope.fData.monto_inicial = parseFloat($scope.fData.saldo_anterior) + parseFloat($scope.fData.monto_cheque);
                console.log('calculando...');
              }
              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }

              $scope.aceptar = function(){
                if($scope.cajaChica.id == ''){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  pinesNotifications.notify({ title: pTitle, text: 'No ha seleccionado una Caja', type: pType, delay: 2500 });
                  return false;
                }
                blockUI.start('Ejecutando proceso...');
                $scope.fData.cajaChica = $scope.cajaChica;
                cajaChicaServices.sRegistrarAperturaCaja($scope.fData).then(function(rpta){
                  blockUI.stop();
                  var pTitle;
                  var pType;
                  if(rpta.flag==1){
                    pTitle = 'Información!';
                    pType = 'info';     
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                    $route.reload();
                    console.log('abriendo caja');
                  }else if(rpta.flag==2){ // cuando la caja ya estaba abierta y no era necesaria aperturar
                    pTitle = 'Advertencia!';
                    pType = 'warning';
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 }); 
                    $route.reload();
                  }else if(rpta.flag==0){
                    pTitle = 'Advertencia!';
                    pType = 'warning';
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 }); 
                    $scope.cargarCajaChicaDisponible();
                  }else{
                    alert('Error inesperado');
                  }
                      
                });
              }
            }
          });
        }
      });
    }

    $scope.fDataES = {};
    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    
    $scope.mySelectionGridES = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsES.enableFiltering = !$scope.gridOptionsES.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringDES = function(){
      $scope.gridOptionsDetalleES.enableFiltering = !$scope.gridOptionsDetalleES.enableFiltering;
      $scope.gridApiDetalle.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };

    // LISTAR OPERACIONES 
    var arrParamsOg = { 
      'origen': 'cajaChica'
    };
    operacionServices.sListarOperacionCbo(arrParamsOg).then(function (rpta) { 
      $scope.metodos.listaOperaciones = rpta.datos;
      $scope.metodos.listaOperacionesForm = angular.copy($scope.metodos.listaOperaciones);
      $scope.fBusqueda.operacion = $scope.metodos.listaOperaciones[0];
    }); 

    // LISTAR SUBOPERACIONES 
    $scope.metodos.listarSubOperaciones = function (arrParams,callBack) {
      var callBack = callBack || function() { } 
      subOperacionServices.sListarSubOperacionesDeOp(arrParams).then(function (rpta) { 
        $scope.metodos.listaSubOperacionesForm = rpta.datos;
        $scope.metodos.listaSubOperacionesForm.splice(0,0,{id : '0', descripcion : '--Seleccione Sub-Operación--'}); 
        $scope.fBusqueda.suboperacion = $scope.metodos.listaSubOperacionesForm[0];
        callBack(); 
      }); 
    }

    // LISTAR TIPOS DE DOCUMENTO CONTABLES 
    $scope.metodos.listarTipoDocumentos = function(arrParams,callBack) {
      var callBack = callBack || function() { } 
      var arrParams = arrParams || null;
      tipoDocumentoServices.sListarTipoDocumentoContabilidad(arrParams).then(function (rpta) { 
        $scope.metodos.arrTipoDocumentos = rpta.datos;
        callBack(); 
      }); 
    }

    // LISTA FORMA DE PAGO
    $scope.listaFormaPago = [ 
      {'id' : 1, 'descripcion' : 'AL CONTADO'},
      {'id' : 2, 'descripcion' : 'CREDITO'} 
      // {'id' : 3, 'descripcion' : 'LETRAS'}
    ];
    $scope.listaMoneda = [
      {'id' : 1, 'descripcion' : 'S/.'},
      {'id' : 2, 'descripcion' : 'US$'}
    ];
    /* GRILLA PRINCIPAL */
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsES = { 
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
        { field: 'idmovimiento', name: 'idmovimiento', displayName: 'ID', width: '70', sort: { direction: uiGridConstants.DESC}},
        { field: 'numero_documento', name: 'numero_documento', displayName: 'Nº DOCUMENTO', width: '120'},
        { field: 'descripcion_td', name: 'descripcion_td', displayName: 'DOCUMENTO', width: '130'},
        { field: 'empresa', name: 'emp.descripcion', displayName: 'EMPRESA', minWidth: '220'},
        { field: 'fecha_registro', name: 'fecha_registro', displayName: 'FECHA DE REGISTRO', width: '160', enableFiltering: false },
        { field: 'fecha_emision', name: 'fecha_emision', displayName: 'FECHA DE EMISION', width: '160', enableFiltering: false },
        { field: 'glosa', name: 'glosa', displayName: 'GLOSA', minWidth: '100'},
        { field: 'importe_local', name: 'importe_local', displayName: 'IMPORTE', width: '120', cellClass:'text-right', enableFiltering: false}, 
        { field: 'estado_color_obj', type: 'object', name: 'estado', displayName: ' ', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          width: 50, cellTemplate:'<div class="ui-grid-cell-contents text-center" title="{{ COL_FIELD.label }}">' + '<img style="width: 20px;" class="" ng-src="{{grid.appScope.dirImagesSemaforo + COL_FIELD.nombre_img}}" alt="{{COL_FIELD.label}}" />' + '</div>' 
        }
        // { field: 'estado', type: 'object', name: 'estado_obj', displayName: ' ', minWidth: '100', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
        //   width: '140', cellTemplate:'<div class="ui-grid-cell-contents">' + 
        //     '<label style="box-shadow: 1px 1px 0 black; display: block;font-size: 12px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="{{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }}' + 
        //     '</label></div>' 
        // }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){ 
          $scope.mySelectionGridES = gridApi.selection.getSelectedRows();
        }) ;
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridES = gridApi.selection.getSelectedRows();
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
            'mo.idmovimiento' : grid.columns[1].filters[0].term,
            'mo.numero_documento' : grid.columns[2].filters[0].term,
            'td.descripcion_td' : grid.columns[3].filters[0].term,
            'dmo.glosa' : grid.columns[7].filters[0].term 
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
    paginationOptions.sortName = $scope.gridOptionsES.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = { 
        paginate : paginationOptions,
        datos : $scope.fBusqueda,
        cajaChica:$scope.arr.cajaChica,
      };
      // console.log($scope.fBusqueda);
      cajaChicaServices.sListarMovimientos(arrParams).then(function (rpta) { 
        $scope.gridOptionsES.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsES.data = rpta.datos;
        $scope.gridOptionsES.sumTotal = rpta.sumTotal; 
      });
      $scope.mySelectionGridES = [];
    };    

    /*============================================== EXPORTAR EXCEL ==========================================*/
    $scope.exportarExcel = function(){
      console.log('$scope.exportarExcel');
      var datos = {
          datos: $scope.gridOptionsES.data || null,
          titulo: 'Movimientos de Caja Chica',
          salida :'excel',
          filtros:$scope.arr.cajaChica,
      };   
      
      var arrParams = {          
        datos: datos,
        metodo: 'php',
      }

      arrParams.url = angular.patchURLCI+'CajaChica/exportar_excel';
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    /*===================================== MANTENIMIENTO =========================================================*/
    $scope.btnNuevoES = function(size) { 
      blockUI.start('Abriendo formulario...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'CajaChica/ver_popup_nuevo_movimiento',
        size: size || 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.fDataES = {};
          $scope.fDataES.forma_pago = $scope.listaFormaPago[0].id;
          $scope.fDataES.guia_remision = $scope.arr.cajaChica.codigo_cc + '-' + $scope.arr.cajaChica.nombre_cc;
          $scope.fDataES.temporal = {};
          $scope.fDataES.proveedor = {};
          $scope.fDataES.fecha_emision = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.pRUC = /^\d{11}$/;
          $scope.fDataES.fecha_registro = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataES.fecha_aprobacion = null;
          $scope.fDataES.fecha_pago = null;
          $scope.fDataES.simbolo_monetario = 'S/. ';
          $scope.fDataES.temporal.cantidad = 1;
          $scope.titleForm = 'Registro de Movimientos';
          $scope.fDataES.orden_compra = '';
          $scope.fDataES.temporal.producto = [];
          $scope.fDataES.operacion = $scope.metodos.listaOperacionesForm[0];
          $scope.fDataES.temporal.centro_costo = {};
          $scope.fDataES.temporal.idcentrocosto = $scope.arr.cajaChica.idcentrocostocaja;
          $scope.fDataES.temporal.cuentaDisabled = true;
          $scope.fDataES.moneda = 'soles';
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
              { field: 'codigo', displayName: 'N° CUENTA', width: '8%', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCION', enableCellEdit: false, enableSorting: false,
              cellTooltip: 
                  function( row, col ) {
                    return row.entity.descripcion;
                  }
              },
              { field: 'importe', displayName: 'IMPORTE', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'accion', displayName: '', width: '5%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' },
              { field: 'idcentrocosto', displayName: 'ID C.C.', enableCellEdit: false, enableSorting: false, visible:false, },
            ]
            ,onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field;
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
                $scope.calcularTotales('sinimp');
                $scope.$apply();
              });
            }
          };
          $scope.getTableHeight = function() {
             var rowHeight = 26; // your row height 
             var headerHeight = 25; // your header height 
             return { 
                height: (6 * rowHeight + headerHeight + 20) + "px"
             };
          };
          
          $scope.calcularTotales = function (modo) { 
            var modo = modo || 'sinimp'; 
            var subtotal = 0;
            var impuesto = 0;
            var total = 0;
            
            if($scope.fDataES.inafecto){
              var paramImp = 0;              
            }else{
              var paramImp = $scope.fDataES.tipodocumento.porcentaje / 100;              
            }
            var paramImpMasUno = paramImp + 1; 
            angular.forEach($scope.gridOptions.data,function (value, key) { 
              total += parseFloat($scope.gridOptions.data[key].importe_base); 
              if($scope.fDataES.inafecto){ 
                  $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base).toFixed(2); 
                  $scope.gridOptions.data[key].importe = redondear($scope.gridOptions.data[key].importe_base).toFixed(2); 
              }else{ 
                
                if( $scope.fDataES.tipodocumento.nombre_impuesto == 'RETENCION' ){ // SI ES RXH. 
                  $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base).toFixed(2); 
                  $scope.gridOptions.data[key].importe = redondear( $scope.gridOptions.data[key].importe_base * ((paramImp - 1) * -1) ).toFixed(2); 
                }else if( $scope.fDataES.tipodocumento.nombre_impuesto == 'NOHAY' ){ 
                  $scope.gridOptions.data[key].importe = redondear($scope.gridOptions.data[key].importe_base).toFixed(2);
                  $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base).toFixed(2);
                }else{ 

                  $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base * paramImpMasUno).toFixed(2);
                  $scope.gridOptions.data[key].importe = redondear( $scope.gridOptions.data[key].importe_base ).toFixed(2); 
                } 
              }
            });
            if( modo === 'sinimp' ){
              if( $scope.fDataES.tipodocumento.nombre_impuesto == 'RETENCION' ){ 
                $scope.fDataES.total = redondear(total).toFixed(2); 
                $scope.fDataES.subtotal = redondear($scope.fDataES.total * ((paramImp - 1) * -1) ).toFixed(2);  
                $scope.fDataES.impuesto = redondear( total - $scope.fDataES.total * ((paramImp - 1) * -1) ).toFixed(2); 
              }else if( $scope.fDataES.tipodocumento.nombre_impuesto == 'NOHAY' ){ 
                $scope.fDataES.subtotal = redondear(total).toFixed(2);
                $scope.fDataES.impuesto = 0;
                $scope.fDataES.total = redondear(total).toFixed(2); 
              }else{
                $scope.fDataES.subtotal = redondear(total).toFixed(2);
                $scope.fDataES.impuesto = redondear(total * paramImp).toFixed(2);
                $scope.fDataES.total = redondear( total + (total * paramImp) ).toFixed(2); 
              }
              
            }else{ 
              $scope.fDataES.subtotal_temp = (total / paramImpMasUno);
              $scope.fDataES.subtotal = redondear($scope.fDataES.subtotal_temp).toFixed(2);
              $scope.fDataES.impuesto = redondear($scope.fDataES.subtotal_temp * paramImp).toFixed(2);
              $scope.fDataES.total = redondear(total).toFixed(2);
            } 
            if($scope.fDataES.inafecto){ 
              var paramImp = $scope.fDataES.tipodocumento.porcentaje / 100;
              var paramImpMasUno = paramImp + 1;
              if( modo === 'sinimp' ){
                if( $scope.fDataES.tipodocumento.nombre_impuesto == 'RETENCION'  ){
                  
                }else{
                  $scope.fDataES.total_impuesto_inafecto = redondear(total * paramImp).toFixed(2);
                } 
              }else{
                var subtotal_temp = (total / paramImpMasUno);
                var subtotal = redondear(subtotal_temp).toFixed(2);
                $scope.fDataES.total_impuesto_inafecto = redondear(subtotal * paramImp).toFixed(2);                
              } 
            } 
          }
          $scope.getlistaSubOperaciones = function () { 
            blockUI.start('Ejecutando proceso...');
            $scope.mostrarEMAReporte = false;
            if( $scope.fDataES.operacion.id && $scope.fDataES.operacion.id == 9 ){
              $scope.mostrarEMAReporte = true;
            }
            $('#temporalDescripcion').focus();
            var arrParams = {
              'idoperacion': $scope.fDataES.operacion.id 
            }; 
            var callBack = function() {
              $scope.fDataES.suboperacion = $scope.metodos.listaSubOperacionesForm[0]; 
            }
            $scope.metodos.listarSubOperaciones(arrParams,callBack);
            blockUI.stop();
          } 
          $scope.getlistaSubOperaciones();
          
          $scope.$watch('fDataES.suboperacion', function(newvalue,oldvalue) { 
            // console.log(newvalue,oldvalue,'newvalue,oldvalue'); 
            if(!(newvalue === oldvalue)){
              // $scope.fDataES.temporal.descripcion = newvalue.descripcion; 
              $scope.fDataES.temporal.cuenta = newvalue.codigo_plan; 
            }
          });
          $scope.$watch('fDataES.operacion', function(newvalue,oldvalue) { 
            // console.log(newvalue,'newvalue operacion');
            if(!(newvalue === oldvalue)){
              if( newvalue.id == 10 ){ 
                $scope.fDataES.temporal.cuentaDisabled = false;
              }else{
                $scope.fDataES.temporal.cuentaDisabled = true;
              }
            }
          }); 
          $scope.$watch('gridOptions.data',function(newvalue,oldvalue) { 
            console.log(newvalue,oldvalue,'observable ');
            if( newvalue.length > 0 ){ 
              $scope.fDataES.operacionDisabled = true;
              $scope.fDataES.subOperacionDisabled = true;
            }else{
              $scope.fDataES.operacionDisabled = false;
              $scope.fDataES.subOperacionDisabled = false;
            }
          },true);

          var callBack = function() {
            $scope.fDataES.tipodocumento = $scope.metodos.arrTipoDocumentos[1]; 
          };
          $scope.metodos.listarTipoDocumentos(null,callBack);
          $scope.btnBuscarProveedor = function (size) { 
            $uibModal.open({
              templateUrl: angular.patchURLCI+'Empresa/ver_popup_busqueda_proveedor',
              size: size || '',
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                $scope.titleForm = 'Búsqueda de Empresas';
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
                  enableFullRowSelection: true,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'idempresa', name: 'e.idempresa', displayName: 'ID', width: '10%',  sort: { direction: uiGridConstants.ASC} },
                    { field: 'ruc', name: 'e.ruc_empresa', displayName: 'RUC.', width: '30%' },
                    { field: 'razon_social', name: 'e.descripcion', displayName: 'Razón Social', width: '60%' }
                  ],
                  onRegisterApi: function(gridApi) { // gridComboOptions
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionProveedorGrid = gridApi.selection.getSelectedRows();
                      $scope.fDataES.proveedor = $scope.mySelectionProveedorGrid[0]; //console.log($scope.fDataES.Proveedor);
                      $scope.fDataES.ruc = $scope.mySelectionProveedorGrid[0].ruc;
                      $modalInstance.dismiss('cancel');
                      setTimeout(function() {
                        $('#facturaES').focus(); //console.log('focus me',$('#temporalConceptoServ'));
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
                        'e.idempresa' : grid.columns[1].filters[0].term,
                        'e.ruc_empresa' : grid.columns[2].filters[0].term,
                        'e.descripcion' : grid.columns[3].filters[0].term,
                      }
                      $scope.getPaginationProveedorSide();
                    });
                  }
                };
                paginationOptionsProveedor.sortName = $scope.gridOptionsProveedorBusqueda.columnDefs[0].name;
                $scope.getPaginationProveedorSide = function() { 
                  var arrParams = {
                    paginate : paginationOptionsProveedor
                  };
                  empresaServices.sListarProveedores(arrParams).then(function (rpta) {
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
            if( $scope.fDataES.ruc ){ 
              empresaServices.sListarEmpresaPorRuc($scope.fDataES).then(function (rpta) { 
                $scope.fDataES.proveedor = rpta.datos;
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al proveedor en el sistema.', type: 'success', delay: 2000 });
                }else{
                  $scope.btnNuevo("",false,$scope.fDataES.ruc);
                }
              });
            }
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            var index = $scope.gridOptions.data.indexOf(row.entity); 
            $scope.gridOptions.data.splice(index,1);
            $scope.calcularTotales('sinimp'); 
          }
          $scope.limpiarCampos = function (){
            $scope.fDataES.proveedor = {};
          }

          $scope.agregarItem = function () { // descripcion 
            $('#temporalConceptoServ').focus(); 

            if( $scope.gridOptions.data.length == 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'En caja chica solo se permite un item por operación.', type: 'warning', delay: 3000 });
              return false;
            }

            if( !($scope.fDataES.operacion) || $scope.fDataES.operacion.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la Operación', type: 'warning', delay: 3000 });
              return false;
            }
            if( !($scope.fDataES.operacion) || $scope.fDataES.suboperacion.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la Sub-Operación', type: 'warning', delay: 3000 });
              return false;
            }
            if( !($scope.fDataES.temporal.descripcion) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha registrado la descripción', type: 'warning', delay: 3000 });
              return false;
            }

            if( !($scope.fDataES.temporal.importe) || $scope.fDataES.temporal.importe < 1 ){
              //$scope.fDataES.temporal.importe = null;
              $('#temporalImporte').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado el importe,o es menor a 1', type: 'warning', delay: 3000 });
              return false;
            }
            var paramImp = $scope.fDataES.tipodocumento.porcentaje / 100; 
            var paramImpMasUno = paramImp + 1;  
            $scope.arrTemporal = { 
              'codigo': $scope.fDataES.suboperacion.codigo_plan,
              'descripcion' : $scope.fDataES.temporal.descripcion,
              'idcentrocosto': $scope.arr.cajaChica.idcentrocostocaja,
              'importe_base': $scope.fDataES.temporal.importe,
              'importe' : $scope.fDataES.temporal.importe,
              'importe_local_con_igv' : $scope.fDataES.temporal.importe * paramImpMasUno 
            };
            $scope.gridOptions.data.push($scope.arrTemporal); 
            $scope.calcularTotales('sinimp'); 
            $scope.fDataES.temporal = { 
              cuenta: $scope.fDataES.suboperacion.codigo_plan,
              descripcion: null,
              importe: null,
            }; 
          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function(){ 
            if( !($scope.fDataES.ruc) && !($scope.fDataES.razon_social) ){ 
              $('#ruc').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la empresa', type: 'warning', delay: 2000 });
              return false;
            }
            $scope.fDataES.detalle = $scope.gridOptions.data;
            if( $scope.fDataES.detalle.length < 1 ){ 
              $('#temporalConceptoServ').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún item.', type: 'warning', delay: 3000 }); 
              return false; 
            }
            //console.log('fDataES: ', $scope.fDataES);
            blockUI.start('Ejecutando proceso...');
            $scope.fDataES.fr_categoria_concepto_abv = 'SRV';
            // $scope.fDataES.idempresatercero = ;
            cajaChicaServices.sRegistrarMovCajaChica($scope.fDataES).then(function (rpta) { 
              console.log(rpta,'rpta');
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                $scope.getPaginationServerSide(); 
                $scope.getCajaActualUsuario(); 
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              blockUI.stop();
            });
          }
          $controller('empresaController', {
            $scope : $scope
          });
        }
      })  
    }
    $scope.btnAnularMovimiento = function() {
      var pMensaje = '¿Realmente desea anular el compra?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaChicaServices.sAnularMovimiento($scope.mySelectionGridES[0]).then(function (rpta) {
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.getPaginationServerSide(); 
              $scope.getCajaActualUsuario(); 
              // $scope.getPaginationServerSideDetalle();
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

    $scope.btnLiquidarCaja = function(){ 
      blockUI.start('Abriendo formulario...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'CajaChica/ver_popup_liquidacion',
        size: '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Liquidación de Caja Chica';

          $scope.aceptar = function(){
            cajaChicaServices.sLiquidarCajaChica($scope.arr.cajaChica).then(function (rpta) { 
              console.log(rpta,'rpta');
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                $scope.getPaginationServerSide(); 
                $scope.getCajaActualUsuario(); 
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
           
          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
          }

        }
      });
    }

    $scope.btnAsientosContables = function () {
      blockUI.start('Abriendo Asientos Contables...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Egresos/ver_popup_asientos_contables',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Asiento Contable'; 
          $scope.fDataDetalle = $scope.mySelectionGridES[0];
          var arrParams = {
            datos:  $scope.mySelectionGridES[0].idmovimiento
          };

          asientoContableServices.sListarAsientoContableEngreso(arrParams).then(function (rpta) {
            $scope.fDataDetalle.data = rpta.datos;
            $scope.fDataDetalle.fecha_emision=rpta.datos[0].fecha_emision;
            $scope.fDataDetalle.glosa=rpta.datos[0].glosa;
          });
          console.log('$scope.fDataDetalle',$scope.fDataDetalle);
          console.log('$scope.fBusqueda',$scope.fBusqueda);
          $scope.cancel = function(){ 
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    $scope.btnAbrirConversacion = function(rowDet) { 
      blockUI.start('Abriendo formulario...');
      $scope.fDataCV = angular.copy(rowDet); 
      $scope.dirImagesSemaforo = $scope.dirImages + 'semaforos/'; // 
      // console.log($scope.fDataCV,'$scope.fDataCV'); dirImagesSemaforo
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'HistorialCajaChica/ver_popup_abir_conversacion',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          blockUI.stop(); 
          $scope.fDataCom = {}; 
          $scope.titleFormCV = 'Conversación/Estados'; 
          var arrParams = {
            idmovimiento: $scope.fDataCV.idmovimiento
          };
          $scope.getListaEstadosComentarios = function(argument) {
            historialCajaChicaServices.sListarComentariosDeMovimiento(arrParams).then(function(rpta) {
              $scope.listaComentario = rpta.datos.comentarios;
            });
          }
          $scope.getListaEstadosComentarios(); 

          $scope.cambiarEstadoColor = function() { 
            if( $scope.fSessionCI.key_group == 'key_sistemas' || $scope.fSessionCI.key_group == 'key_gerencia' ){ 
              if( $scope.fDataCV.estado_color_obj.label_cambio == 'APROBADO' ){
                $scope.fDataCV.estado_color_obj.label_cambio = 'OBSERVADO';
                $scope.fDataCV.estado_color_obj.nombre_img_cambio = 'amarillo.png';
                $scope.fDataCV.estado_color_obj.flag = 2;
              }else if( $scope.fDataCV.estado_color_obj.label_cambio == 'OBSERVADO' ){
                $scope.fDataCV.estado_color_obj.label_cambio = 'ANULADO';
                $scope.fDataCV.estado_color_obj.nombre_img_cambio = 'rojo.png';
                $scope.fDataCV.estado_color_obj.flag = 3;
              }else if( $scope.fDataCV.estado_color_obj.label_cambio == 'ANULADO' ){
                $scope.fDataCV.estado_color_obj.label_cambio = 'APROBADO';
                $scope.fDataCV.estado_color_obj.nombre_img_cambio = 'verde.png';
                $scope.fDataCV.estado_color_obj.flag = 1;
              }
            }else{
              return false; 
            }
            
          }
          console.log($scope.mySelectionGridES[0],'$scope.mySelectionGridES[0]');
          $scope.btnAgregarItem = function() { 
            var arrParams = {
              estado_color_obj: $scope.fDataCV.estado_color_obj,
              comentario: $scope.fDataCom.comentario_text,
              idmovimiento: $scope.fDataCV.idmovimiento,
              idaperturacajachica: $scope.mySelectionGridES[0].idaperturacajachica,
              fila: $scope.fDataCV
            }; 
            blockUI.start('Ejecutando proceso...');
            historialCajaChicaServices.sAgregarComentarioEstado(arrParams).then(function(rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                $scope.fDataCV.estado_color_obj.label = $scope.fDataCV.estado_color_obj.label_cambio;
                $scope.fDataCV.estado_color_obj.nombre_img = $scope.fDataCV.estado_color_obj.nombre_img_cambio;
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              
              var arrParams = { 
                idaperturacajachica: $scope.mySelectionGridES[0].idaperturacajachica 
              } 
              historialCajaChicaServices.sListarEstaAperturaCajaChica(arrParams).then(function(rpta) {
                $scope.fData.importe_total = rpta.fData.importe_total; 
                $scope.fData.monto_inicial = rpta.fData.monto_inicial; 
                $scope.fData.saldo = rpta.fData.saldo; 
                $scope.fData.saldo_numeric = rpta.fData.saldo_numeric; 
                $scope.fData.importe_total_numeric = rpta.fData.importe_total_numeric;
                $scope.mySelectionGridES[0] = $scope.fData; 
              }); 

              $scope.getListaEstadosComentarios();

              $scope.fDataCom.comentario_text = null; 

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              blockUI.stop();
            });
          }
          $scope.cancelDet2 = function(){ 
            
            $modalInstance.dismiss('cancel');

          }

        }
      }); 
    }
    /* ============================ */ 
    /* ATAJOS DE TECLADO NAVEGACION */ 
    /* ============================ */ 
   
    shortcut.remove('F4'); 
    shortcut.remove('F6'); 
  }])
  .service("cajaChicaServices",function($http, $q) {
    return({
        sListarCajaChicaDisponibleCbo: sListarCajaChicaDisponibleCbo,
        sListarSaldoAnterior: sListarSaldoAnterior,
        sVerificaCcEmpleado:sVerificaCcEmpleado,
        sRegistrarAperturaCaja: sRegistrarAperturaCaja,
        sLiquidarCajaChica: sLiquidarCajaChica,
        sVerificarCajaChicaUsuario: sVerificarCajaChicaUsuario,
        sRegistrarMovCajaChica: sRegistrarMovCajaChica,
        sListarMovimientos:sListarMovimientos,
        sListarDetallesMovimientos:sListarDetallesMovimientos,
        sAnularMovimiento:sAnularMovimiento,
        sCerrarCajaChica:sCerrarCajaChica 
    });
    function sListarCajaChicaDisponibleCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/lista_caja_chica_disponible_cbo", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSaldoAnterior(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/lista_saldo_anterior", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sVerificaCcEmpleado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Empleado/verifica_cc_empleado", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sRegistrarAperturaCaja(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/registrar_apertura_caja_chica", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }      
    function sLiquidarCajaChica(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/liquidar_caja_chica", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }      
    function sVerificarCajaChicaUsuario(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/verifica_caja_chica_usuario", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }      
    function sRegistrarMovCajaChica(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/regitrar_mov_caja_chica", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sListarMovimientos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/listar_movimientos", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetallesMovimientos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/listar_detalle_movimientos", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularMovimiento(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"CajaChica/anular_movimiento", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCerrarCajaChica(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CajaChica/cerrar_caja_chica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }      
  });
  