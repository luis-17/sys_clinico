angular.module('theme.egresosServicio', ['theme.core.services'])
  .controller('egresosServicioController', ['$scope','blockUI', '$filter', '$route', '$sce', '$interval', '$modal', '$uibModal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox', '$controller',
    'egresosServicioServices',
    'conceptoServices',
    'especialidadServices',
    'proveedorFarmaciaServices',
    'atencionMedicaAmbServices',
    'ModalReporteFactory',
    function($scope, blockUI, $filter, $sce, $route, $interval, $modal, $uibModal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, $bootbox, $controller,
      egresosServicioServices,
      conceptoServices,
      especialidadServices,
      proveedorFarmaciaServices,
      atencionMedicaAmbServices,
      ModalReporteFactory ){ 
    'use strict';

    shortcut.remove("F2");
    $scope.fDataES = {};
    $scope.listaEstadoOrden = [
      {'id' : 1, 'descripcion' : 'POR APROBAR'},
      {'id' : 2, 'descripcion' : 'APROBADO'}
    ];
    $controller('proveedorFarmaciaController', { 
        $scope : $scope
    });
    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/
    $scope.fBusqueda = {};
    $scope.modulo = 'egresosServicio';
    $scope.fBusqueda.almacen = {};
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
    $scope.btnToggleFilteringESA = function(){
      $scope.gridOptionsESAnulados.enableFiltering = !$scope.gridOptionsESAnulados.enableFiltering;
      $scope.gridApiAnulado.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringDES = function(){
      $scope.gridOptionsDetalleES.enableFiltering = !$scope.gridOptionsDetalleES.enableFiltering;
      $scope.gridApiDetalle.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTAR CONCEPTOS 
    var arrParams = { 
      categoria: 'SRV'
    }; 
    conceptoServices.sListarConceptoCbo(arrParams).then(function (rpta) { 
      $scope.metodos.listaConceptos = rpta.datos;
      $scope.metodos.listaConceptosForm = angular.copy($scope.metodos.listaConceptos);
      $scope.metodos.listaConceptos.splice(0,0,{id : '0', descripcion : '--Todos--'});
      $scope.metodos.listaConceptosForm.splice(0,0,{id : '0', descripcion : '--Seleccione Concepto--'});

      $scope.fBusqueda.concepto = $scope.metodos.listaConceptos[0];
      //$scope.getPaginationOCAServerSide(); 
    }); 
    especialidadServices.sListarEspecialidadesCbo().then(function (rpta) { 
      $scope.metodos.listaEspecialidades = rpta.datos;
      $scope.metodos.listaEspecialidades.splice(0,0,{id : '0', descripcion : '--Seleccione Especialidad--'}); 
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
        { field: 'factura', name: 'ticket_venta', displayName: 'Nº FACTURA', minWidth: '100', sort: { direction: uiGridConstants.DESC}},
        { field: 'razon_social', name: 'razon_social', displayName: 'RAZON SOCIAL', minWidth: '160' },
        { field: 'ruc', name: 'ruc', displayName: 'RUC', minWidth: '100' },
        { field: 'fecha_registro', name: 'fecha_movimiento', displayName: 'FECHA DE REGISTRO', minWidth: '140', enableFiltering: false  },
        { field: 'fecha_aprobacion', name: 'fecha_aprobacion', displayName: 'FECHA DE APROBACION', minWidth: '150', enableFiltering: false},
        { field: 'fecha_pago', name: 'fecha_pago', displayName: 'FECHA DE PAGO', minWidth: '140', enableFiltering: false},
        { field: 'periodo_asignado', name: 'periodo_asignado', displayName: 'PERIODO', minWidth: '130' /*,cellClass:'text-right'*/ },
        { field: 'total', name: 'total_a_pagar', displayName: 'IMPORTE', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'detraccion', name: 'detraccion', displayName: 'DETRACCIÓN', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'deposito', name: 'deposito', displayName: 'DEPÓSITO', minWidth: '110', enableFiltering: false,cellClass:'text-right' },
        { field: 'estado', type: 'object', name: 'estado_obj', displayName: ' ', minWidth: '100', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="ui-grid-cell-contents">' +
            '<label style="box-shadow: 1px 1px 0 black; display: block;" class="label {{ COL_FIELD.claseLabel }} "> <i class="{{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }}' + 
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
            'ticket_venta' : grid.columns[1].filters[0].term,
            'razon_social' : grid.columns[2].filters[0].term,
            'ruc' : grid.columns[3].filters[0].term,
            'periodo_asignado' : grid.columns[7].filters[0].term 
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
    paginationOptions.sortName = $scope.gridOptionsES.columnDefs[2].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = { 
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      // console.log($scope.fBusqueda);
      egresosServicioServices.sListarEgresosPorServicio(arrParams).then(function (rpta) { 
        $scope.gridOptionsES.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsES.data = rpta.datos;
        $scope.gridOptionsES.sumTotal = rpta.sumTotal;
        
      });
      $scope.mySelectionGridES = [];
    };
    
    /*==================================== BOTON PROCESAR =========================================================*/
    $scope.procesar = function(){
      if(!$scope.formEgresos.$invalid){
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
        templateUrl: angular.patchURLCI+'Egresos/ver_popup_filtro_periodo',
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
    /*===================================== MANTENIMIENTO =========================================================*/
    $scope.btnNuevoES = function(size) { 
      console.log('open mee');
      blockUI.start('Abriendo formulario...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Egresos/ver_popup_nuevo_egreso_servicio',
        size: size || 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.fDataES = {};
          $scope.fDataES.forma_pago = $scope.listaFormaPago[0].id;
          $scope.fDataES.estado_orden = $scope.listaEstadoOrden[0].id;
          $scope.fDataES.temporal = {};
          $scope.fDataES.proveedor = {};
          $scope.fDataES.fecha_emision = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.pRUC = /^\d{11}$/;
          $scope.fDataES.fecha_registro = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fDataES.fecha_aprobacion = null;
          $scope.fDataES.fecha_pago = null;
          $scope.fDataES.temporal.cantidad = 1;
          $scope.titleForm = 'Registro de Egresos';
          $scope.fDataES.orden_egreso = '[ ............... ]';
          $scope.fDataES.temporal.producto = [];
          $scope.fDataES.concepto = $scope.metodos.listaConceptosForm[0];
          $scope.fDataES.temporal.especialidad = $scope.metodos.listaEspecialidades[0];
          $scope.fDataES.temporal.anio = $filter('date')(new Date(),'yyyy'); 
          $scope.fDataES.temporal.mes = $scope.metodos.listaMeses[0];
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
              { field: 'id', displayName: 'ITEM.', width: '10%', enableCellEdit: false, enableSorting: false },
              { field: 'descripcion', displayName: 'DESCRIPCION', enableCellEdit: false, enableSorting: false,
              cellTooltip: 
                  function( row, col ) {
                    return row.entity.descripcion;
                  }
              },
              { field: 'importe', displayName: 'IMPORTE', width: '15%', enableCellEdit: false, enableSorting: false, cellClass:'text-right', visible:true },
              { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ]
            ,onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field;
                if(rowEntity.column == 'cantidad'){
                  if( !(rowEntity.cantidad >= 1) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
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
                $scope.calcularTotales();
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
          $scope.generarNumOrden = function () {
            //console.log($scope.fDataES);
            // $scope.fDataES.abvNumOrden = arrParams.categoria;
            egresosServicioServices.sGenerarCodigoOrden(arrParams).then(function (rpta) { 
              $scope.fDataES.orden_egreso = rpta.codigo_orden; 
            });
          };
          $scope.generarNumOrden();
          $scope.calcularTotales = function () { 
            var subtotal = 0;
            var igv = 0;
            var total = 0;
            angular.forEach($scope.gridOptions.data,function (value, key) { 
              total += parseFloat($scope.gridOptions.data[key].importe);
            });
            $scope.fDataES.subtotal_temp = (total / 1.18);
            $scope.fDataES.subtotal = redondear($scope.fDataES.subtotal_temp).toFixed(2);
            $scope.fDataES.igv = redondear($scope.fDataES.subtotal_temp * 0.18).toFixed(2);
            $scope.fDataES.total = redondear(total).toFixed(2);
          }
          $scope.mostrarBotonConsulta = function () { 
            blockUI.start('Ejecutando proceso...');
            $scope.mostrarEMAReporte = false;
            if( $scope.fDataES.concepto.id && $scope.fDataES.concepto.id == 1 ){
              $scope.mostrarEMAReporte = true;
            }
            $scope.fDataES.temporal.descripcion = $scope.fDataES.concepto.descripcion+' ';
            $('#temporalDescripcion').focus();
            blockUI.stop();
          } 
          $scope.consultarEMA = function () { 
            blockUI.start('Consultando Producción...');
            if( !($scope.fDataES.temporal.especialidad) || $scope.fDataES.temporal.especialidad.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la especialidad', type: 'warning', delay: 2000 });
              blockUI.stop();
              return false;
            }
            if( !($scope.fDataES.temporal.anio) ){ 
              $('#temporalAnio').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el año', type: 'warning', delay: 2000 });
              blockUI.stop();
              return false;
            }
            if( !($scope.fDataES.temporal.mes) || $scope.fDataES.temporal.mes.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el mes', type: 'warning', delay: 2000 });
              blockUI.stop();
              return false;
            }
            if( !($scope.fDataES.ruc) && !($scope.fDataES.razon_social) ){ 
              $('#ruc').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la empresa', type: 'warning', delay: 2000 });
              blockUI.stop();
              return false;
            }
            var arrParams = {
              'servicio': $scope.fDataES.temporal.especialidad,
              'ruc': $scope.fDataES.ruc,
              'mes': $scope.fDataES.temporal.mes,
              'anio': $scope.fDataES.temporal.anio,
              'ruc': $scope.fDataES.ruc
            };
            atencionMedicaAmbServices.sObtenerTotalesProduccion(arrParams).then(function (rpta) { 
              $scope.metodos.fProduccion = rpta.datos; 
              if(rpta.flag == 1){
                $scope.fDataES.temporal.importe = $scope.metodos.fProduccion[0].importe_tercero; 
              }else if(rpta.flag == 2){
                  blockUI.start('Abriendo formulario...');
                  $uibModal.open({ 
                    templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_produccion_multiple',
                    size: 'md',
                    backdrop: 'static',
                    keyboard:false,
                    scope: $scope,
                    controller: function ($scope, $modalInstance) {
                      blockUI.stop();
                      $scope.titleFormMP = 'PRODUCCIÓN MÚLTIPLE';
                      $scope.fDataPM = {};

                      $scope.gridOptionsMP = { 
                        enableRowSelection: true,
                        enableFullRowSelection: false,
                        data: $scope.metodos.fProduccion,
                        columnDefs: [
                          { field: 'item', displayName: 'COD.', width: '15%', enableSorting: false },
                          { field: 'descripcion', displayName: 'DESCRIPCIÓN', enableSorting: false},
                          { field: 'importe_tercero', displayName: 'IMPORTE', width: '25%', enableSorting: false, cellClass:'text-right', visible:true },
                        ]
                        ,onRegisterApi: function(gridApiMP) { 
                          $scope.gridApiMP = gridApiMP;
                          gridApiMP.selection.on.rowSelectionChanged($scope,function(row){ 
                            // console.log(row);
                            $scope.fDataES.temporal.importe = row.entity.importe_tercero;
                            $modalInstance.dismiss('cancel');
                            // $scope.mySelectionGridMP = gridApiMP.selection.getSelectedRows(); 
                          });
                        }
                      };

                      $scope.cancelMT = function () {
                        $modalInstance.dismiss('cancel');
                      }
                    }
                  });
              }else{
                pinesNotifications.notify({ title: 'Advertencia.', text: rpta.message, type: 'warning', delay: 2500 });
                return false;
              } 
            }); 
            blockUI.stop();
          }
          $scope.consultarReporteTercero = function() { 
            // blockUI.start('Consultando Producción...');
            if( !($scope.fDataES.temporal.especialidad) || $scope.fDataES.temporal.especialidad.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la especialidad', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataES.temporal.anio) ){ 
              $('#temporalAnio').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el año', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataES.temporal.mes) || $scope.fDataES.temporal.mes.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el mes', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataES.ruc) && !($scope.fDataES.razon_social) ){ 
              $('#ruc').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la empresa', type: 'warning', delay: 2000 });
              return false;
            }
            var arrDatos = {
              'tituloAbv': 'AM-LT',
              'titulo': 'LIQUIDACIÓN TERCEROS',
              'servicio': $scope.fDataES.temporal.especialidad,
              'ruc': $scope.fDataES.ruc,
              'mes': $scope.fDataES.temporal.mes,
              'anio': $scope.fDataES.temporal.anio,
              'ruc': $scope.fDataES.ruc
            };
            // $scope.fBusqueda.tituloAbv = 'AM-LT';
            var arrParams = {
              titulo: arrDatos.titulo,
              datos: arrDatos,
              metodo: 'php'
            }; 
            arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_liquidacion_terceros', 
            ModalReporteFactory.getPopupReporte(arrParams); 
          }
          $scope.btnBuscarProveedor = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'ProveedorFarmacia/ver_popup_busqueda_proveedor',
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
                    { field: 'id', name: 'idproveedor', displayName: 'ID', width: '10%',  sort: { direction: uiGridConstants.ASC} },
                    { field: 'ruc', name: 'ruc', displayName: 'RUC.', width: '15%' },
                    { field: 'razon_social', name: 'razon_social', displayName: 'Razón Social', width: '40%' },
                    { field: 'direccion_fiscal', name: 'direccion_fiscal', displayName: 'Dirección', width: '30%' },
                  ],
                  onRegisterApi: function(gridApi) { // gridComboOptions
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionProveedorGrid = gridApi.selection.getSelectedRows();
                      $scope.fDataES.proveedor = $scope.mySelectionProveedorGrid[0]; //console.log($scope.fDataES.Proveedor);
                      $scope.fDataES.ruc = $scope.mySelectionProveedorGrid[0].ruc;
                      $modalInstance.dismiss('cancel');
                      setTimeout(function() {
                        $('#temporalConceptoServ').focus(); //console.log('focus me',$('#temporalConceptoServ'));
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
                  var arrParams = {
                    paginate : paginationOptionsProveedor
                  };
                  proveedorFarmaciaServices.sListarProveedorFarmacia(arrParams).then(function (rpta) {
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
              proveedorFarmaciaServices.sListarEsteProveedorPorRuc($scope.fDataES).then(function (rpta) { 
                $scope.fDataES.proveedor = rpta.datos;
                if( rpta.flag === 1 ){
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al proveedor en el sistema.', type: 'success', delay: 2000 });
                }else{
                  $scope.btnNuevo("",$scope.fDataES);
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
            $scope.fDataES.proveedor = {};
          }

          $scope.agregarItem = function () { // descripcion
            $('#temporalConceptoServ').focus();
            if( !($scope.fDataES.concepto) || $scope.fDataES.concepto.id == '0' ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el concepto', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fDataES.temporal.descripcion) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha registrado la descripción', type: 'warning', delay: 2000 });
              return false;
            }
            if($scope.mostrarEMAReporte){ 
              if( !($scope.fDataES.temporal.especialidad) || $scope.fDataES.temporal.especialidad.id == '0' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la especialidad', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fDataES.temporal.anio) ){ 
                $('#temporalAnio').focus();
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el año', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fDataES.temporal.mes) || $scope.fDataES.temporal.mes.id == '0' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el mes', type: 'warning', delay: 2000 });
                return false;
              }
            }
            if( !($scope.fDataES.temporal.importe) || $scope.fDataES.temporal.importe < 1 ){
              //$scope.fDataES.temporal.importe = null;
              $('#temporalImporte').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No ha ingresado el importe', type: 'warning', delay: 2000 });
              return false;
            }
            var productNew = true;
            angular.forEach($scope.gridOptions.data, function(value, key) { 
              if(value.descripcion == $scope.fDataES.temporal.descripcion ){ 
                productNew = false;
              }
            });
            if( productNew === false ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El concepto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              //$scope.fDataES.temporal.cantidad = 1;
              $scope.fDataES.temporal.anio = $filter('date')(new Date(),'yyyy'); 
              $scope.fDataES.concepto = $scope.metodos.listaConceptosForm[0];
              $scope.fDataES.temporal.especialidad = $scope.metodos.listaEspecialidades[0];
              $scope.fDataES.temporal.mes = $scope.metodos.listaMeses[0];
              return false;
            } 
            $scope.arrTemporal = { 
              'id' : $scope.fDataES.concepto.id,
              'descripcion' : $scope.fDataES.temporal.descripcion,
              'importe' : $scope.fDataES.temporal.importe,
              'anio' : $scope.fDataES.temporal.anio,
              'mes' : $scope.fDataES.temporal.mes,
              'servicio' : $scope.fDataES.temporal.especialidad
            };
            $scope.gridOptions.data.push($scope.arrTemporal);
            $scope.calcularTotales(); 
            $scope.fDataES.temporal = {
              especialidad: $scope.metodos.listaEspecialidades[0],
              importe: null,
              descripcion: null,
              anio: $filter('date')(new Date(),'yyyy'),
              mes: $scope.metodos.listaMeses[0]
            };

            $scope.fDataES.temporal.descripcion = $scope.fDataES.concepto.descripcion+' ';
            //$scope.mostrarEMAReporte = false; 
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
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún concepto', type: 'warning', delay: 3000 }); 
              return false; 
            }
            //console.log('fDataES: ', $scope.fDataES);
            blockUI.start('Ejecutando proceso...');
            $scope.fDataES.fr_categoria_concepto_abv = 'SRV';
            // $scope.fDataES.idempresatercero = ;
            egresosServicioServices.sRegistrarEgresoServicio($scope.fDataES).then(function (rpta) { 
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
          
        }
      })  
    }
    $scope.btnVerDetalleES = function () { 
      blockUI.start('Abriendo detalle...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'Egresos/ver_popup_detalle_egreso',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.titleForm = 'Detalle de Egreso'; 
          $scope.fDataDetalle = $scope.mySelectionGridES[0];
          $scope.gridOptionsDetalleUnES = { 
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 50,
            multiSelect: false,
            columnDefs: [ 
              { field: 'item', displayName: 'ID', width: '10%' },
              { field: 'descripcion_concepto', displayName: 'DESCRIPCION', width: '60%' },
              { field: 'total_detalle', displayName: 'IMPORTE', width: '20%' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
            }
          };
          $scope.getPaginationDetalleEntradaServerSide = function() {
            var arrParams = {
              datos: {
                idmovimiento : $scope.mySelectionGridES[0].idmovimiento
              }
            };
            egresosServicioServices.sListarDetalleUnEgreso(arrParams).then(function (rpta) {
              $scope.gridOptionsDetalleUnES.data = rpta.datos;
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
        templateUrl: angular.patchURLCI+'Egresos/ver_popup_seguimiento_estados',
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
            egresosServicioServices.sListarSeguimientoEstados(arrParams).then(function (rpta) {
              $scope.listaSeguimiento = rpta.datos;
              $scope.classBtn = rpta.class_btn;
            });
          }
          $scope.listarSeguimientosEstados(); 
          $scope.cambiarEstadoEgreso = function (valorEstado,disabledClass) { 
            if( disabledClass == 'disabled' ){ 
              return false; 
            }
            var arrParams = {
                idmovimiento : $scope.fDataSeg.idmovimiento,
                num_estado: valorEstado
            };
            egresosServicioServices.sCambiarEstadoServicio(arrParams).then(function (rpta) { 
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
    $scope.btnAnularEgreso = function() {
      var pMensaje = '¿Realmente desea anular el egreso?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          egresosServicioServices.sAnularEgreso($scope.mySelectionGridES).then(function (rpta) {
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.getPaginationServerSide();
              // $scope.getPaginationOCAServerSide();
              $scope.getPaginationServerSideDetalle();
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

    /************** GRID DE DETALLES DEL EGRESO **************/
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
        { field: 'orden', name: 'orden_egreso', displayName: 'Nº ORDEN', width: '9%' },
        { field: 'razon_social', name: 'razon_social', displayName: 'RAZON SOCIAL', width: '16%'},
        { field: 'servicio_asignado', name: 'servicio_asignado', displayName: 'SERVICIO' },
        { field: 'fecha_aprobacion', name: 'fecha_movimiento', displayName: 'FECHA DE REGISTRO', width: '10%', enableFiltering: false },
        { field: 'periodo_asignado', name: 'periodo_asignado', displayName: 'PERIODO' },
        { field: 'descripcion_concepto', name: 'descripcion_concepto', displayName: 'DESCRIPCION' },
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
            'ticket_venta' : grid.columns[1].filters[0].term,
            "razon_social" : grid.columns[2].filters[0].term,
            'm.denominacion' : grid.columns[4].filters[0].term,
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
      egresosServicioServices.sListarDetallesEgresos($scope.datosGrid).then(function (rpta) {
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
  .service("egresosServicioServices",function($http, $q) {
    return({
        sListarEgresosPorServicio: sListarEgresosPorServicio,
        sListarDetallesEgresos: sListarDetallesEgresos,
        sListarDetalleUnEgreso: sListarDetalleUnEgreso,
        sGenerarCodigoOrden: sGenerarCodigoOrden,
        sRegistrarEgresoServicio: sRegistrarEgresoServicio,
        sListarSeguimientoEstados: sListarSeguimientoEstados,
        sCambiarEstadoServicio: sCambiarEstadoServicio,
        sAnularEgreso : sAnularEgreso
    });

    function sListarEgresosPorServicio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/lista_egresos_por_servicio ", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetallesEgresos(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/lista_detalle_egresos_por_servicio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleUnEgreso (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/lista_detalle_de_un_egreso",  
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSeguimientoEstados (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/listar_seguimiento_estados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarCodigoOrden (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/generar_codigo_orden", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarEgresoServicio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/registrar_egreso", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarEstadoServicio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/cambiar_estado_egreso", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularEgreso (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Egresos/anular_egreso", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });