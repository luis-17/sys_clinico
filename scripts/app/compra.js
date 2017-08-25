angular.module('theme.compra', ['theme.core.services'])
  .controller('compraController', ['$scope','blockUI', '$filter', '$sce', '$route', '$interval', '$modal', '$uibModal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox', '$controller',
    'comprasServices',
    'operacionServices',
    'especialidadServices',
    'subOperacionServices',
    'atencionMedicaAmbServices',
    'tipoDocumentoServices',
    'centroCostoServices',
    'empresaServices',
    'ModalReporteFactory',
    'asientoContableServices',
    function($scope, blockUI, $filter, $sce, $route, $interval, $modal, $uibModal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, $bootbox, $controller,
      comprasServices,
      operacionServices,
      especialidadServices,
      subOperacionServices,
      atencionMedicaAmbServices,
      tipoDocumentoServices,
      centroCostoServices,
      empresaServices,
      ModalReporteFactory,
      asientoContableServices ){ 
    'use strict';

    shortcut.remove("F2");
    $scope.fDataES = {};

    
    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    $scope.modulo = 'compras';
    
    // $scope.fBusqueda.almacen = {}; listaTipoDocumento
    $scope.metodos = {};
    var hoy = new Date();
    var desde = hoy - 1209600000; // restamos 14 dias
    //var desde = hoy - 1296000000; // restamos 15 dias
    $scope.fBusqueda.desde = $filter('date')(desde,'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(hoy,'dd-MM-yyyy');
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
      'origen': 'compra'
    };
    operacionServices.sListarOperacionCbo(arrParamsOg).then(function (rpta) { 
      $scope.metodos.listaOperaciones = rpta.datos;
      $scope.metodos.listaOperacionesForm = angular.copy($scope.metodos.listaOperaciones);
      $scope.metodos.listaOperaciones.splice(0,0,{id : '0', descripcion : '--Todos--'});
      $scope.metodos.listaOperacionesForm.splice(0,0,{id : '0', descripcion : '--Seleccione Operación--'});

      $scope.fBusqueda.operacion = $scope.metodos.listaOperaciones[0];
      //$scope.getPaginationOCAServerSide(); 
    }); 

    // LISTAR SUBOPERACIONES 
    $scope.metodos.listarSubOperaciones = function (arrParams,callBack) {
      var callBack = callBack || function() { } 
      subOperacionServices.sListarSubOperacionesDeOp(arrParams).then(function (rpta) { 
        $scope.metodos.listaSubOperacionesForm = rpta.datos;
        //  $scope.metodos.listaSubOperacionesForm.splice(0,0,{id : '0', descripcion : '--Todos--'}); 
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
    $scope.metodos.listaMeses = [ 
      {'id' : 0, 'descripcion' : '--Seleccione Mes--'},
      {'id' : '01', 'descripcion' : 'ENERO'},
      {'id' : '02', 'descripcion' : 'FEBRERO'},
      {'id' : '03', 'descripcion' : 'MARZO'},
      {'id' : '04', 'descripcion' : 'ABRIL'},
      {'id' : '05', 'descripcion' : 'MAYO'},
      {'id' : '06', 'descripcion' : 'JUNIO'},
      {'id' : '07', 'descripcion' : 'JULIO'},
      {'id' : '08', 'descripcion' : 'AGOSTO'},
      {'id' : '09', 'descripcion' : 'SEPTIEMBRE'},
      {'id' : '10', 'descripcion' : 'OCTUBRE'},
      {'id' : '11', 'descripcion' : 'NOVIEMBRE'},
      {'id' : '12', 'descripcion' : 'DICIEMBRE'}
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
        { field: 'descripcion_td', name: 'descripcion_td', displayName: 'DOCUMENTO', minWidth: '200', width:'180'},
        { field: 'numero_documento', name: 'mo.numero_documento', displayName: 'Nº DOCUMENTO', minWidth: '120', width:'120'},
        { field: 'empresa', name: 'emp.descripcion', displayName: 'EMPRESA/PROVEEDOR', minWidth: '200' },
        { field: 'ruc', name: 'ruc_empresa', displayName: 'RUC', minWidth: '100' },
        { field: 'fecha_emision', name: 'fecha_emision', displayName: 'FECHA DE EMISIÓN', minWidth: '140', enableFiltering: false, sort: { direction: uiGridConstants.DESC} },
        { field: 'fecha_registro', name: 'fecha_registro', displayName: 'FECHA DE REGISTRO', minWidth: '140', enableFiltering: false, visible: false },
        { field: 'fecha_aprobacion', name: 'fecha_aprobacion', displayName: 'FECHA DE APROBACION', minWidth: '150', enableFiltering: false, visible:false },
        { field: 'fecha_pago', name: 'fecha_pago', displayName: 'FECHA DE PAGO', minWidth: '140', enableFiltering: false, visible:false },
        { field: 'periodo_asignado', name: 'periodo_asignado', displayName: 'PERIODO', minWidth: '130', enableFiltering: false, visible:false /*,cellClass:'text-right'*/ },
        { field: 'cuenta_contable', name: 'codigo_plan', displayName: 'CUENTA CONTABLE', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'sub_total', name: 'sub_total', displayName: 'SUBTOTAL', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'total_impuesto', name: 'total_impuesto', displayName: 'IMPUESTO', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'total_a_pagar', name: 'total_a_pagar', displayName: 'IMPORTE', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'detraccion', name: 'detraccion', displayName: 'DETRACCIÓN', minWidth: '110', enableFiltering: false,cellClass:'text-right', visible:false },
        { field: 'deposito', name: 'deposito', displayName: 'DEPÓSITO', minWidth: '110', enableFiltering: false,cellClass:'text-right', visible:false },
        { field: 'estado', type: 'object', name: 'estado_obj', displayName: ' ', minWidth: '100', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="ui-grid-cell-contents">' + 
            '<label style="box-shadow: 1px 1px 0 black; display: block;font-size: 12px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="{{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }}' + 
            '</label></div>' 
        }
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
            'td.descripcion_td' : grid.columns[1].filters[0].term,
            'mo.numero_documento' : grid.columns[2].filters[0].term,
            'emp.descripcion' : grid.columns[3].filters[0].term,
            'emp.ruc_empresa' : grid.columns[4].filters[0].term 
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
    paginationOptions.sortName = $scope.gridOptionsES.columnDefs[4].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = { 
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      // console.log($scope.fBusqueda);
      comprasServices.sListarCompras(arrParams).then(function (rpta) { 
        $scope.gridOptionsES.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsES.data = rpta.datos;
        $scope.gridOptionsES.sumTotal = rpta.sumTotal;
        
      });
      $scope.mySelectionGridES = [];
    };
    
    /*==================================== BOTON PROCESAR =========================================================*/
    $scope.procesar = function(){
      if(!$scope.formCompras.$invalid){
        $scope.getPaginationServerSide();
        $scope.getPaginationServerSideDetalle();
      }else{
        pinesNotifications.notify({ title: 'Warning.', text: 'Rellene los campos obligatorios.', type: 'warning', delay: 3000 });
      }
    }
    /*==================================== BOTON REPORTE E.M.A =========================================================*/
    $scope.verPopupReporteTerceros = function () { 
      blockUI.start('Abriendo formulario...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Compras/ver_popup_filtro_periodo',
        size: 'sm',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Filtros de Reporte'; 
          $scope.fBusquedaFiltro = {}; 
          $scope.fBusquedaFiltro.anio = $filter('date')(new Date(),'yyyy'); 
          //console.log($scope.metodos.listaMeses[0],'$scope.metodos.listaMeses[0]');
          $scope.fBusquedaFiltro.mes = $scope.metodos.listaMeses[0];
          $scope.cancel = function(){ 
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function(){ 
            // blockUI.start('Ejecutando proceso...');
            $scope.fBusquedaFiltro.titulo = 'REPORTE CONSOLIDADO DE TERCEROS';
            $scope.fBusquedaFiltro.tituloAbv = 'AM-CT';
            var arrParams = { 
              titulo: $scope.fBusquedaFiltro.titulo, 
              datos: $scope.fBusquedaFiltro, 
              metodo: 'php' 
            }; 
            arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_consolidado_terceros'; 
            ModalReporteFactory.getPopupReporte(arrParams); 
          }
        }
      });
    }
    // $scope.verReporteTerceros = function () { 
    //   $scope.fBusquedaPAH.titulo = 'REPORTE CONSOLIDADO DE TERCEROS';
    //   var arrParams = { 
    //     titulo: $scope.fBusquedaPAH.titulo, 
    //     datos: $scope.fBusquedaPAH, 
    //     metodo: 'php' 
    //   }; 
    //   arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_consolidado_terceros'; 
    //   ModalReporteFactory.getPopupReporte(arrParams); 
    // }
    /*================================ EXPORTAR EXCEL ============================================================*/
    $scope.exportarExcel = function(){
      console.log('$scope.exportarExcel');
      var datos = {
          datos: $scope.gridOptionsES.data || null,
          titulo: 'Listado de Compras',
          salida :'excel',
          filtros:$scope.fBusqueda,
      };   
      
      var arrParams = {          
        datos: datos,
        metodo: 'php',
      }

      arrParams.url = angular.patchURLCI+'Compras/exportar_excel';
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    /*===================================== MANTENIMIENTO =========================================================*/
    $scope.btnNuevoES = function(size) { 
      blockUI.start('Abriendo formulario...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Compras/ver_popup_nueva_compra',
        size: size || 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.fDataES = {};
          // $scope.fDataES.tipo_cambio_venta = 3.60; // AGREGAR DINAMISMO LUEGO 
          // $scope.fDataES.idtipocambio = 3; // AGREGAR DINAMISMO LUEGO 
          $scope.fDataES.forma_pago = $scope.listaFormaPago[0].id;
          // $scope.fDataES.estado_orden = $scope.listaEstadoOrden[0].id;
          $scope.fDataES.temporal = {};
          $scope.fDataES.proveedor = {};
          $scope.fDataES.fecha_emision = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.pRUC = /^\d{11}$/;
          $scope.fDataES.fecha_registro = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataES.fecha_aprobacion = null;
          $scope.fDataES.fecha_pago = null;
          $scope.fDataES.simbolo_monetario = 'S/. ';
          $scope.fDataES.temporal.cantidad = 1;
          $scope.titleForm = 'Registro de Compras';
          $scope.fDataES.orden_compra = '';
          $scope.fDataES.temporal.producto = [];
          $scope.fDataES.operacion = $scope.metodos.listaOperacionesForm[0];
          // $scope.fDataES.temporal.especialidad = $scope.metodos.listaEspecialidades[0];
          $scope.fDataES.temporal.anio = $filter('date')(new Date(),'yyyy'); 
          $scope.fDataES.temporal.mes = $scope.metodos.listaMeses[0];
          $scope.fDataES.temporal.centro_costo = {};
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
              { field: 'glosa', displayName: 'GLOSA', enableCellEdit: false, enableSorting: false,
                cellTooltip: 
                  function( row, col ) {
                    return row.entity.descripcion;
                  }
              },
              { field: 'importe_local_con_igv', displayName: 'IMPORTE', width: '10%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-right', visible:true },
              { field: 'importe', displayName: 'IMPORTE', width: '10%', enableCellEdit: true, enableSorting: false, cellClass:'ui-editCell text-right', visible:false },
              { field: 'accion', displayName: '', width: '5%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ]
            ,onRegisterApi: function(gridApiForm) { 
              $scope.gridApiForm = gridApiForm;
              gridApiForm.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field;
                if(rowEntity.column == 'importe'){ 
                  if( !(rowEntity.importe > 0) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.importe = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'El importe debe ser mayor a 0', type: pType, delay: 3500 });
                    return false;
                  }
                  rowEntity.importe_base = newValue;
                } 
                $scope.$apply();
                $scope.calcularTotales('sinimp');
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
          $scope.getComprasAutocomplete = function (value) {
            var params = {
              searchText: value,
              searchColumn: "numero_documento", // estemporal
              sensor: false,
              empresa: $scope.fDataES.proveedor,

            }
            return comprasServices.sListarCompraAutoComplete(params).then(function(rpta) {
              $scope.noResultsLE = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLE = true;
              }
              return rpta.datos;
            });
          }
          $scope.getSelectedCompra = function (item, model) { 
            // console.log('model',model,item); 
            $scope.fDataES.itemCompra = item;
            angular.forEach($scope.metodos.listaOperacionesForm,function(value, key){
              if(value.id == model.idoperacion ){
                // console.log('value',value);
                $scope.fDataES.operacion = $scope.metodos.listaOperacionesForm[key];
                $scope.getlistaSubOperaciones();
              }
            });

            var paramDatos = {
              datos: model,
              sensor: true,
            }
            // paramDatos.datos.tipodocumento = $scope.fDataES.tipodocumento;  
            comprasServices.sListarDetalleUnaCompra(paramDatos).then(function(rpta){ 
              // console.log('detalle', rpta.datos)
              $scope.gridOptions.data = rpta.datos;
              $scope.fDataES.inafecto = rpta.datos[0].inafecto; 
              $scope.fDataES.porc_doc_referencia = rpta.datos[0].porc_doc_referencia; 
              $scope.fDataES.doc_abreviatura_referencia = rpta.datos[0].doc_abreviatura_referencia; 
              $scope.fDataES.nombre_impuesto_referencia = rpta.datos[0].nombre_impuesto_referencia; 
              $scope.fDataES.codigo_plan_referencia = rpta.datos[0].codigo_plan_referencia; 
              $scope.fDataES.total_doc_referencia = rpta.datos[0].total_doc_referencia; 
              // $scope.calcularTotales('sinimp');
            });
          }
          $scope.btnRecargarGrilla = function(){
            var paramDatos = {
              datos: $scope.fDataES.numero_compra,
              sensor: true,
            }
            comprasServices.sListarDetalleUnaCompra(paramDatos).then(function(rpta){
              $scope.gridOptions.data = rpta.datos;
              $scope.calcularTotales('sinimp');
            });
          }
          $scope.limpiarGrilla = function(){
            $scope.gridOptions.data = [];
            $scope.fDataES.subtotal = '0.00';
            $scope.fDataES.impuesto = '0.00';
            $scope.fDataES.total = '0.00';
          }
          $scope.calcularTotales = function (modo) {  
            var modo = modo || 'sinimp'; 
            var subtotal = 0;
            var impuesto = 0;
            var total = 0;
            var paramImpInafecto = $scope.fDataES.tipodocumento.porcentaje / 100; 
            var paramImpInafectoMasUno = paramImpInafecto + 1; 
            if($scope.fDataES.inafecto){
              var paramImp = 0;              
            }else{
              var paramImp = $scope.fDataES.tipodocumento.porcentaje / 100;              
            }
            var paramImpMasUno = paramImp + 1; 
            // console.log($scope.fDataES,'$scope.fDataES'); fDataES.tipodocumento.id 
            /* NOTA DE CREDITO */
            if($scope.fDataES.tipodocumento.id == 7 || $scope.fDataES.tipodocumento.id == 14){ // subtotal impuesto 
              var porcentaje = parseFloat($scope.fDataES.porc_doc_referencia); 
              var totalDocReferencia = parseFloat($scope.fDataES.total_doc_referencia);
              //$scope.fDataES.total = totalDocReferencia;
              if($scope.fDataES.inafecto == 1){ 
                var paramImp = 0; 
                var paramImpMasUno = 1;
              }else{ 
                var paramImp = porcentaje / 100; 
                var paramImpMasUno = 1 + paramImp; 
              }
              $scope.fDataES.subtotal = redondear($scope.fDataES.total / paramImpMasUno).toFixed(2); 
              $scope.fDataES.impuesto =  redondear($scope.fDataES.total - $scope.fDataES.subtotal).toFixed(2); 
              //console.log($scope.fDataES.subtotal,$scope.fDataES.total,'$scope.fDataES.total',paramImpMasUno,'paramImpMasUno'); 
              return false; 
            }
            
            angular.forEach($scope.gridOptions.data,function (value, key) { 
              total += parseFloat($scope.gridOptions.data[key].importe_base); 
              if($scope.fDataES.inafecto){ 
                $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base).toFixed(2); 
                $scope.gridOptions.data[key].importe = redondear($scope.gridOptions.data[key].importe_base).toFixed(2); 
              }else{ 
                if( $scope.fDataES.tipodocumento.nombre_impuesto == 'RETENCION' ){ // SI ES RXH. 
                  $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base).toFixed(2); 
                  $scope.gridOptions.data[key].importe = redondear( $scope.gridOptions.data[key].importe_base * ((paramImp - 1) * -1) ).toFixed(2);
                }else if( $scope.fDataES.tipodocumento.nombre_impuesto == 'NOHAY' ){ // VALE Y OTROS 
                  $scope.gridOptions.data[key].importe = redondear($scope.gridOptions.data[key].importe_base).toFixed(2);
                  $scope.gridOptions.data[key].importe_local_con_igv = redondear($scope.gridOptions.data[key].importe_base).toFixed(2);
                }else{ // TODO LO DEMÁS 
                  $scope.gridOptions.data[key].importe_local_con_igv = redondear( $scope.gridOptions.data[key].importe_base ).toFixed(2);
                  $scope.gridOptions.data[key].importe = redondear($scope.gridOptions.data[key].importe_base / paramImpMasUno).toFixed(2);
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
                $scope.fDataES.total =  redondear(total).toFixed(2); 
                $scope.fDataES.subtotal = redondear( total / paramImpMasUno ).toFixed(2); 
                $scope.fDataES.impuesto = redondear( total - (total / paramImpMasUno) ).toFixed(2);  

              } 
            }else{ 
              $scope.fDataES.subtotal_temp = (total / paramImpMasUno);
              $scope.fDataES.subtotal = redondear($scope.fDataES.subtotal_temp).toFixed(2);
              $scope.fDataES.impuesto = redondear($scope.fDataES.subtotal_temp * paramImp).toFixed(2);
              $scope.fDataES.total = redondear(total).toFixed(2);
            }

            if($scope.fDataES.inafecto){ 
              $scope.fDataES.total_impuesto_inafecto = redondear( total - (total / paramImpInafectoMasUno) ).toFixed(2);  
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
          // watchs
          $scope.$watch('fDataES.moneda', function(newvalue,oldvalue) { 
            if(!(newvalue === oldvalue)){
              if( newvalue === 'dolares' ){
                $scope.fDataES.simbolo_monetario = 'US$';
              }else{
                $scope.fDataES.simbolo_monetario = 'S/.';
              }
            }
          });
          $scope.$watch('fDataES.suboperacion', function(newvalue,oldvalue) { 
            // console.log(newvalue,oldvalue,'newvalue,oldvalue'); 
            if(!(newvalue === oldvalue) && newvalue != null ){
              // $scope.fDataES.temporal.descripcion = newvalue.descripcion; 
              $scope.fDataES.temporal.cuenta = newvalue.codigo_plan; 
            }
          });
          $scope.$watch('fDataES.operacion', function(newvalue,oldvalue) { 
           if(!(newvalue === oldvalue) && newvalue != null){
              if( newvalue.id == 10 ){ 
                $scope.fDataES.temporal.cuentaDisabled = false;
              }else{
                $scope.fDataES.temporal.cuentaDisabled = true;
                $scope.fDataES.operacionDisabled = false;
                $scope.fDataES.subOperacionDisabled = false;
              }
            }
          }); 
          $scope.$watch('gridOptions.data',function(newvalue,oldvalue) { 
            if( newvalue.length > 0 ){ 
              $scope.fDataES.operacionDisabled = true;
              $scope.fDataES.subOperacionDisabled = true;
            }
          }); 
          $scope.$watch('fDataES.tipodocumento',function(newvalue='',oldvalue='') { 
            if( oldvalue.id == 7 || oldvalue.id == 14 ){
              $scope.fDataES.numero_compra = null;
              $scope.gridOptions.data = [];
              $scope.fDataES.inafecto = false; 
              $scope.fDataES.porc_doc_referencia = null; 
              $scope.fDataES.doc_abreviatura_referencia = null; 
              $scope.fDataES.nombre_impuesto_referencia = null;  
              $scope.fDataES.codigo_plan_referencia = null; 
              $scope.fDataES.total_doc_referencia = null; 

              $scope.fDataES.subtotal = '0.00';
              $scope.fDataES.impuesto = '0.00';
              $scope.fDataES.total = '0.00';
              $scope.fDataES.operacionDisabled = false;
              $scope.fDataES.subOperacionDisabled = false;
              $scope.fDataES.forma_pago = 1; 

              $scope.gridOptions.columnDefs[2].cellClass = 'ui-editCell text-right';
              $scope.gridOptions.columnDefs[2].enableCellEdit = true; 
              $scope.gridApiForm.core.notifyDataChange(uiGridConstants.dataChange.COLUMN); 
            }
            if( newvalue.id == 7 || newvalue.id == 14 ){
              $scope.fDataES.numero_compra = null;
              $scope.gridOptions.data = [];
              $scope.fDataES.subtotal = '0.00';
              $scope.fDataES.impuesto = '0.00';
              $scope.fDataES.total = '0.00';
              $scope.fDataES.operacionDisabled = true;
              $scope.fDataES.subOperacionDisabled = true;
              $scope.fDataES.forma_pago = 1;

              $scope.gridOptions.columnDefs[2].cellClass = '';
              $scope.gridOptions.columnDefs[2].enableCellEdit = false;
              $scope.gridApiForm.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
            }

            if($scope.fDataES.tipodocumento.id == 7 || $scope.fDataES.tipodocumento.id == 14){
              $scope.fDataES.operacion = $scope.metodos.listaOperacionesForm[0];
              $scope.fDataES.suboperacion = {};
              $scope.metodos.listaSubOperacionesForm = null;
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
            if($scope.gridOptions.data.length == 1){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Solo puede ingresar un item por compra', type: 'warning', delay: 3000 });
              return false;
            }
            $('#temporalConceptoServ').focus();
            if( !($scope.fDataES.operacion) || $scope.fDataES.operacion.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la operación', type: 'warning', delay: 3000 });
              return false;
            }
            if( !($scope.fDataES.temporal.descripcion) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha registrado la descripción', type: 'warning', delay: 3000 });
              return false;
            }
            if( !($scope.fDataES.temporal.importe) || !($scope.fDataES.temporal.importe > 0) ){
              //$scope.fDataES.temporal.importe = null;
              $('#temporalImporte').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado el importe, o no es mayor que cero', type: 'warning', delay: 3000 });
              return false;
            } 
            if($scope.fDataES.inafecto){ 
              var paramImp = 0; 
            }else{
              var paramImp = $scope.fDataES.tipodocumento.porcentaje / 100; 
            } 
            var paramImpMasUno = paramImp + 1; 
            $scope.arrTemporal = { 
              'codigo': $scope.fDataES.suboperacion.codigo_plan,
              'glosa' : $scope.fDataES.temporal.descripcion,
              'descripcion' : $scope.fDataES.temporal.descripcion,
              'importe_base': $scope.fDataES.temporal.importe,
              'importe' : $scope.fDataES.temporal.importe, 
              'importe_local_con_igv' : $scope.fDataES.temporal.importe * paramImpMasUno 
            };
            $scope.gridOptions.data.push($scope.arrTemporal); 
            $scope.calcularTotales('sinimp'); 
            $scope.fDataES.temporal = { 
              cuenta: $scope.fDataES.suboperacion.codigo_plan,
              descripcion: null,
              importe: null
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
            blockUI.start('Ejecutando proceso...');
            $scope.fDataES.fr_categoria_concepto_abv = 'SRV';
            // $scope.fDataES.idempresatercero = ;
            comprasServices.sRegistrarCompraServicio($scope.fDataES).then(function (rpta) { 
              blockUI.stop();
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                $scope.getPaginationServerSide();
                $scope.getPaginationServerSideDetalle();
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
          $controller('empresaController', {
            $scope : $scope
          });
        }
      })  
    }
    $scope.btnVerDetalleES = function () { 
      blockUI.start('Abriendo detalle...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Compras/ver_popup_detalle_compra',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Detalle de Compra'; 
          $scope.fDataDetalle = $scope.mySelectionGridES[0];
          $scope.gridOptionsDetalleUnES = { 
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 50,
            multiSelect: false,
            columnDefs: [ 

              { field: 'item', displayName: 'ID', width: '10%', visible:false },
              { field: 'glosa', displayName: 'GLOSA', minWidth: 130, enableCellEdit: false, enableSorting: false},
              { field: 'importe_local_con_igv', displayName: 'IMPORTE', width: '10%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          $scope.getPaginationDetalleEntradaServerSide = function() {
            var arrParams = {
              datos: {
                idmovimiento : $scope.mySelectionGridES[0].idmovimiento,
              },
              sensor: false

            };
            comprasServices.sListarDetalleUnaCompra(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleUnES.data = rpta.datos;
              var total = 0;
              angular.forEach($scope.gridOptionsDetalleUnES.data,function (value, key) { 
                //console.log($scope.gridOptionsDetalleUnES.data[key].num_importe_local);
                  total += parseFloat($scope.gridOptionsDetalleUnES.data[key].importe_local_con_igv);
                });
              $scope.fDataDetalle.total = redondear(total).toFixed(2);
            });
          };
          $scope.getPaginationDetalleEntradaServerSide();
          $scope.cancel = function(){ 
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnSeguimientoEstados = function () {
      blockUI.start('Abriendo Seguimiento de Estados...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Compras/ver_popup_seguimiento_estados',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Seguimiento de Estados'; 
          $scope.fDataSeg = $scope.mySelectionGridES[0]; 
          $scope.listaSeguimiento = []; 
          var arrParams = { 
            datos: { 
              idmovimiento : $scope.fDataSeg.idmovimiento 
            } 
          };
          $scope.listarSeguimientosEstados = function () {
            comprasServices.sListarSeguimientoEstados(arrParams).then(function (rpta) {
              $scope.listaSeguimiento = rpta.datos;
              $scope.classBtn = rpta.class_btn;
            });
          }
          $scope.listarSeguimientosEstados(); 
          $scope.cambiarEstadoCompra = function (valorEstado,disabledClass) { 
            if( disabledClass == 'disabled' ){ 
              return false; 
            }
            var arrParams = {
                idmovimiento : $scope.fDataSeg.idmovimiento,
                num_estado: valorEstado
            };
            comprasServices.sCambiarEstadoServicio(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.listarSeguimientosEstados(); 
                // $scope.getPaginationServerSide();
                // $scope.getPaginationOCAServerSide();
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
          $scope.cancel = function(){ 
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnAnularCompra = function() {
      var pMensaje = '¿Realmente desea anular el compra?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          comprasServices.sAnularCompra($scope.mySelectionGridES[0]).then(function (rpta) {
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.getPaginationServerSide();
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
          $scope.cancel = function(){ 
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    /************** GRID DE DETALLE DE COMPRAS **************/
    $scope.mySelectionGridDet = [];
    var paginationOptionsDET = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsDetalleES = { 
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
        { field: 'numero_documento', name: 'numero_documento', displayName: 'Nº FACTURA', width: '100'},
        { field: 'empresa', name: 'emp.descripcion', displayName: 'EMPRESA/PROVEEDOR', minWidth: '160' },
        { field: 'ruc', name: 'ruc_empresa', displayName: 'RUC', width: '100' },
        { field: 'fecha_registro', name: 'fecha_registro', displayName: 'FECHA DE REGISTRO', width: '140', enableFiltering: false, sort: { direction: uiGridConstants.DESC} },

        { field: 'glosa', name: 'glosa', displayName: 'GLOSA' },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE', width: '8%', enableFiltering: false } 
      ],
      onRegisterApi: function(gridApiDet) { 
        $scope.gridApiDetalle = gridApiDet;
        gridApiDet.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridDet = gridApiDet.selection.getSelectedRows();
        });
        gridApiDet.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridDet = gridApiDet.selection.getSelectedRows();
        });

        $scope.gridApiDetalle.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsDET.sort = null;
            paginationOptionsDET.sortName = null;
          } else {
            paginationOptionsDET.sort = sortColumns[0].sort.direction;
            paginationOptionsDET.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideDetalle();
        });
        $scope.gridApiDetalle.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsDET.search = true; 
          paginationOptionsDET.searchColumn = { 
            'mo.numero_documento' : grid.columns[1].filters[0].term,
            "emp.descripcion" : grid.columns[2].filters[0].term,
            'emp.ruc_empresa' : grid.columns[3].filters[0].term,
            'dmo.glosa' : grid.columns[5].filters[0].term,
          }
          $scope.getPaginationServerSideDetalle();
        });
        gridApiDet.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsDET.pageNumber = newPage;
          paginationOptionsDET.pageSize = pageSize;
          paginationOptionsDET.firstRow = (paginationOptionsDET.pageNumber - 1) * paginationOptionsDET.pageSize;
          $scope.getPaginationServerSideDetalle();
        });
      }
    };
    paginationOptionsDET.sortName = $scope.gridOptionsDetalleES.columnDefs[2].name;
    $scope.getPaginationServerSideDetalle = function() { // console.log('PV');
      $scope.datosGrid = { 
        paginate : paginationOptionsDET,
        datos : $scope.fBusqueda
      };
      comprasServices.sListarDetallesCompras($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsDetalleES.totalItems = rpta.paginate.totalRows; 
        $scope.gridOptionsDetalleES.data = rpta.datos;
      });
      $scope.mySelectionGridDet = [];
    };
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */ 
   
    shortcut.remove('F4');
    shortcut.add("F4",function(event) { 
      if($scope.mySelectionGridES.length == 1 ){ 
        $scope.btnImprimir($scope.mySelectionGridES[0].idmovimiento, $scope.mySelectionGridES[0].estado_movimiento); 
      } 
    }); 
    shortcut.remove('F6');
    shortcut.add("F6",function() { 
        $scope.mismoCliente(); 
        $('#temporalEspecialidad').focus();
    }); 
  }])
  .service("comprasServices",function($http, $q) {
    return({
        sListarCompras: sListarCompras,
        sListarCompraAutoComplete: sListarCompraAutoComplete,
        sListarDetallesCompras: sListarDetallesCompras,
        sListarDetalleUnaCompra: sListarDetalleUnaCompra,
        sGenerarCodigoOrden: sGenerarCodigoOrden,
        sRegistrarCompraServicio: sRegistrarCompraServicio,
        sListarSeguimientoEstados: sListarSeguimientoEstados,
        sCambiarEstadoServicio: sCambiarEstadoServicio,
        sAnularCompra : sAnularCompra,
    });

    function sListarCompras(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/lista_compras ", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCompraAutoComplete(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/listar_compra_autocomplete ", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetallesCompras(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/lista_detalle_compras", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleUnaCompra (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/lista_detalle_de_una_compra",  
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSeguimientoEstados (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/listar_seguimiento_estados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarCodigoOrden (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/generar_codigo_orden", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarCompraServicio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/registrar_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarEstadoServicio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/cambiar_estado_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularCompra (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Compras/anular_compra", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
  });