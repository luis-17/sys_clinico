angular.module('theme.medicamentoAlmacen', ['theme.core.services'])
  .controller('medicamentoAlmacenController', ['$scope', '$sce', 'blockUI', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', '$filter' ,'uiGridConstants', 'pinesNotifications', 'hotkeys', 
      'medicamentoServices',
      'medicamentoAlmacenServices',
      'almacenFarmServices',
      'ModalReporteFactory',
      'laboratorioServices',
  function($scope, $sce, blockUI, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, $filter, uiGridConstants, pinesNotifications, hotkeys, 
      medicamentoServices,
      medicamentoAlmacenServices,
      almacenFarmServices,
      ModalReporteFactory,
      laboratorioServices
    )
  {
    'use strict';
    $scope.modulo = 'medicamentoAlmacen';
    $scope.fBusqueda = {}; 
    $scope.fBusqueda.mostrarPartes = 'SP';
    $scope.fData = {};
    $scope.fDataAdd = {};
    $scope.metodos = {};
    $scope.temporal = {};
    $scope.fBusqueda.allStocks = true;
    $scope.listarSubAlmacenesAlmacen = function (idalmacen,modo) { 
      modo = modo || 'CG';
      var arrParams = {
        'idalmacen': idalmacen
      }
      //LISTAR SUBALMACENES PARA LOS MEDICAMENTOS 
      almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rpta) {
        $scope.listaSubAlmacen = rpta.datos; 
        $scope.fBusqueda.subalmacen = $scope.listaSubAlmacen[0];
        if( modo === 'CG' ){ //para el combo de la vista general
          $scope.getPaginationServerSide('si',true);
        }else if( modo === 'FA' ){ // para el combo del popup agregar medicamento
          $scope.fDataAdd.idsubalmacen = $scope.listaSubAlmacen[0].id;
          $scope.metodos.getPaginationMedServerSide();
        }
      });
    }
    //PREPARADOS
    $scope.listarSubAlmacenesAlmacenPreparado = function (idalmacen,modo) { 
      modo = modo || 'CG';
      var arrParams = {
        'idalmacen': idalmacen
      }
      //LISTAR SUBALMACENES PARA LOS PREPARADOS 
      almacenFarmServices.sListarSubAlmacenesDeAlmacenPreparadoCbo(arrParams).then(function (rpta) {
        $scope.listaSubAlmacenPreparado = rpta.datos;
        $scope.fBusqueda.subalmacenpreparado = $scope.listaSubAlmacenPreparado[0];
        if( modo === 'CG' ){ //para el combo de la vista general
          $scope.getPaginationPreServerSide('si',true);
        }else if( modo === 'FA' ){ // para el combo del popup agregar preparado
          $scope.fDataAdd.idsubalmacen = $scope.listaSubAlmacenPreparado[0].id;
          $scope.metodos.getPaginationPreServerSide();
        }
      });
    }
    // ALMACEN 
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { 
      $scope.listaAlmacen = rpta.datos; 
      // $scope.listaAlmacen.splice(0,0,{ id : '', descripcion:'-- Seleccione Presentacion --'}); 
      $scope.fBusqueda.almacen = $scope.listaAlmacen[0];
      $scope.listarSubAlmacenesAlmacen($scope.fBusqueda.almacen.id);
      $scope.listarSubAlmacenesAlmacenPreparado($scope.fBusqueda.almacen.id);
    });
    // LABORATORIO
    laboratorioServices.sListarlaboratorioCbo().then(function(rpta){
      $scope.listaLaboratorio =rpta.datos;
      $scope.listaLaboratorio.splice(0,0,{ id : '0', descripcion:'-- Todos --'}); 
      $scope.fBusqueda.laboratorio = $scope.listaLaboratorio[0];
    });
    $scope.mySelectionGrid = [];
    $scope.mySelectionPreparadoGrid = [];
    $scope.mySelectionMedicamentoGrid = [];
    $scope.mySelectionMedicamentoAddGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
      $scope.gridOptionsPre.enableFiltering = !$scope.gridOptionsPre.enableFiltering;
      $scope.gridApiPre.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    var paginationOptions = { 
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000, 5000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: 50, enableCellEdit: false, pinnedLeft:true,  sort: { direction: uiGridConstants.ASC} },
        { field: 'medicamento', name: 'med.denominacion', displayName: 'MEDICAMENTO', minWidth: 290/*, maxWidth: 300*/, enableCellEdit: false, pinnedLeft:true },
        { field: 'estadoMed', type: 'object', name: 'estado_fma', displayName: 'ESTADO', width: 140, enableFiltering: false, enableCellEdit: false, pinnedRight:true, 
          cellTemplate:'<label ng-click="grid.appScope.btnHabilitarDeshabilitar(row)" style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label label-hand {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' },
        { field: 'stock_inicial', name: 'stock_inicial', displayName: 'STOCK INICIAL', width: 130, cellClass:'ui-editCell' },
        { field: 'stock_entradas', name: 'stock_entradas', displayName: 'STOCK ENTRADAS', width: 136, cellClass:'ui-editCell' },
        { field: 'stock_salidas', name: 'stock_salidas', displayName: 'STOCK SALIDAS', width: 130, cellClass:'ui-editCell' },
        { field: 'stock_actual_malm', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: 130, enableCellEdit: false },
        { field: 'stock_minimo', name: 'stock_minimo', displayName: 'STOCK MINIMO', width: 130, enableCellEdit: true, cellClass:'ui-editCell' },
        { field: 'stock_critico', name: 'stock_critico', displayName: 'STOCK CRITICO', width: 130, enableCellEdit: true, cellClass:'ui-editCell' },
        { field: 'stock_maximo', name: 'stock_maximo', displayName: 'STOCK MAXIMO', width: 130, enableCellEdit: true, cellClass:'ui-editCell' },
        { field: 'precio_compra_str', name: 'precio_compra_str', displayName: 'P. COMPRA REF.', width: 100, enableCellEdit: false, visible: false/*, cellClass:'ui-editCell'*/ },
        { field: 'precio_ultima_compra', name: 'precio_ultima_compra', displayName: 'P. COMPRA ULT.', width: 100, enableCellEdit: false, enableFiltering: false, visible: false },
        { field: 'utilidad_porcentaje', name: 'utilidad_porcentaje', displayName: 'UTILIDAD %', width: 110, visible: false, cellClass:'ui-editCell' },
        { field: 'utilidad_valor_str', name: 'utilidad_valor_str', displayName: 'UTILIDAD S./', width: 110, visible: false, cellClass:'ui-editCell' },
        { field: 'precio_venta_str', name: 'precio_venta_str', displayName: 'P. VENTA', width: 100, visible: false ,cellClass:'ui-editCell' },
        { field: 'porcentaje_venta_kairos_str', name: 'porcentaje_venta_kairos_str', displayName: '% VENTA', width: 100, visible: false ,cellClass:'ui-editCell' },
        { field: 'precio_venta_kairos_str', name: 'precio_venta_kairos_str', displayName: 'P. KAIROS', width: 100, visible: false ,cellClass:'ui-editCell' },
        { field: 'costo_medio_malm', name: 'costo_medio_malm', displayName: 'C. MEDIO', width: 100, enableCellEdit: false },
        { field: 'costo_min_malm', name: 'costo_min_malm', displayName: 'C. MIN.', width: 100, enableCellEdit: false },
        { field: 'costo_max_malm', name: 'costo_max_malm', displayName: 'C. MAX.', width: 100, enableCellEdit: false }
      ],
      // columnDefs[].visible
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          if (sortColumns.length == 0) { 
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide('si',true);
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide('si',true);
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = { 
            'med.idmedicamento' : grid.columns[1].filters[0].term,
            'denominacion' : grid.columns[2].filters[0].term,
            'stock_inicial' : grid.columns[4].filters[0].term,
            'stock_entradas' : grid.columns[5].filters[0].term,
            'stock_salidas' : grid.columns[6].filters[0].term,
            'stock_actual_malm' : grid.columns[7].filters[0].term,
            'stock_minimo' : grid.columns[8].filters[0].term,
            'stock_critico' : grid.columns[9].filters[0].term,
            'stock_maximo' : grid.columns[10].filters[0].term,
            'precio_compra' : grid.columns[11].filters[0].term,
            'precio_ultima_compra' : grid.columns[12].filters[0].term,
            'utilidad_porcentaje' : grid.columns[13].filters[0].term,
            'precio_venta' : grid.columns[15].filters[0].term,
            'porcentaje_venta_kairos' : grid.columns[16].filters[0].term,
            'precio_venta_kairos' : grid.columns[17].filters[0].term 
          }; 
          $scope.getPaginationServerSide();
        });
        gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
          // console.log(rowEntity, colDef, newValue, oldValue); 
          rowEntity.column = colDef.field;
          rowEntity.anteriorValor = oldValue;
          medicamentoAlmacenServices.sEditarMedicamentoAlmacenInline(rowEntity).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else if(rpta.flag == 2){
              $scope.registrarMotivoMargenUtilidad(rowEntity, rpta.message);
            }else{
              alert('Error inesperado');
            }
            $scope.getPaginationServerSide('no',true);
            if( rpta.flag != 2 ){ // si es 2 no muestro la alerta sino un modal
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            }
            
          });
          $scope.$apply();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.mostrarStockOPrecio = function (ventaACliente) { 
      if($scope.fBusqueda.mostrarPartes == 'SS' || ( $scope.fBusqueda.mostrarPartes == 'SP' && !(ventaACliente) ) ){ 
        //OCULTAR PRECIO
        //$scope.gridOptions.columnDefs[10].visible = false; // p.venta_ref
        $scope.gridOptions.columnDefs[11].visible = false;
        $scope.gridOptions.columnDefs[12].visible = false;
        $scope.gridOptions.columnDefs[13].visible = false;
        $scope.gridOptions.columnDefs[14].visible = false;
        $scope.gridOptions.columnDefs[15].visible = false;
        $scope.gridOptions.columnDefs[16].visible = false;
        //MOSTRAR STOCK
        $scope.gridOptions.columnDefs[3].visible = true;
        $scope.gridOptions.columnDefs[4].visible = true;
        $scope.gridOptions.columnDefs[5].visible = true;
        $scope.gridOptions.columnDefs[6].visible = true;
        $scope.gridOptions.columnDefs[7].visible = true;
        $scope.gridOptions.columnDefs[8].visible = true;
        $scope.gridOptions.columnDefs[9].visible = true;
      }
      if($scope.fBusqueda.mostrarPartes == 'SP' && ventaACliente ){ 
        //MOSTRAR PRECIO
        // $scope.gridOptions.columnDefs[10].visible = true;
        $scope.gridOptions.columnDefs[11].visible = true;
        $scope.gridOptions.columnDefs[12].visible = true;
        $scope.gridOptions.columnDefs[13].visible = true;
        $scope.gridOptions.columnDefs[14].visible = true;
        $scope.gridOptions.columnDefs[15].visible = true;
        $scope.gridOptions.columnDefs[16].visible = true;
        //OCULTAR STOCK
        $scope.gridOptions.columnDefs[3].visible = false;
        $scope.gridOptions.columnDefs[4].visible = false;
        $scope.gridOptions.columnDefs[5].visible = false;
        $scope.gridOptions.columnDefs[6].visible = false;
        $scope.gridOptions.columnDefs[7].visible = false;
        $scope.gridOptions.columnDefs[8].visible = false;
        $scope.gridOptions.columnDefs[9].visible = false;
      }
      $scope.gridApi.grid.refresh();
    }
    $scope.btnHabilitarDeshabilitar = function (row) { 
      medicamentoAlmacenServices.sHabilitarDeshabilitarMedicAlmacen(row.entity).then(function (rpta) { 
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
        }else if(rpta.flag == 0){
          var pTitle = 'Error!';
          var pType = 'danger';
        }else{
          alert('Error inesperado');
        }
        $scope.getPaginationServerSide('si',true);
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
      });
    }
    $scope.getPaginationServerSide = function(verificacion,loader) { 
      var loader = loader || false;
      var verificacion = verificacion || 'si';
      var arrParams = {
        paginate : paginationOptions,
        datos: $scope.fBusqueda
      };
      if(verificacion == 'si'){
        if( loader ){
          blockUI.start('Ejecutando proceso...');
        }
        medicamentoAlmacenServices.sVerificarSubalmacen(arrParams).then(function (rpta1) { 
          $scope.mostrarStockOPrecio(rpta1.venta_a_cliente);
          $scope.gridApi.grid.refresh();
          $scope.venta_a_cliente = rpta1.venta_a_cliente;
          medicamentoAlmacenServices.sListarMedicamentoAlmacen(arrParams).then(function (rpta) { 
            $scope.gridOptions.totalItems = rpta.paginate.totalRows;
            $scope.gridOptions.data = rpta.datos;
            if( loader ){
              blockUI.stop();
            }
          });
        });
        $scope.mySelectionGrid = [];
      }else if(verificacion == 'no'){
        medicamentoAlmacenServices.sListarMedicamentoAlmacen(arrParams).then(function (rpta) { 
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
        });
      }else{
        alert('Inténtelo otra vez, presionando CTRL + F5');
      }
    };
    $scope.registrarMotivoMargenUtilidad = function(rowEntity, mensaje){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'MedicamentoAlmacen/ver_popup_motivo_margen_utilidad',
        size: '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          console.log('rowEntity:', rowEntity);
          $scope.temporal = rowEntity;
          $scope.titleForm = 'Motivo por cambio de Precio de Venta';
          $scope.mensaje = mensaje;
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            medicamentoAlmacenServices.sEditarMedicamentoAlmacenInline($scope.temporal).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Error inesperado');
            }
            $scope.getPaginationServerSide('no',true);
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
            $modalInstance.dismiss('cancel');
          }

        }
      });
    }
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnAgregarMedicamento = function () { 
      // console.log('open mee');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'MedicamentoAlmacen/ver_popup_agregar_medicamentos_al_almacen',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.accion = 'reg';
          $scope.titleForm = 'Agregar Medicamento al Almacen'; 
          $scope.fDataAdd.idalmacen = angular.copy($scope.fBusqueda.almacen.id);
          $scope.fDataAdd.idsubalmacen = angular.copy($scope.fBusqueda.subalmacen.id);
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          var paginationOptionsMed = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsMedicamentos = { 
            paginationPageSizes: [10, 50, 100, 500, 1000, 5000], 
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
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: 50 },
              { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', width: 350,  sort: { direction: uiGridConstants.ASC} },
              { field: 'presentacion', name: 'presentacion', displayName: 'PRESENTACION', width: 126 },
              { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: 220 },
              { field: 'estadoGen', type: 'object', name: 'generico', displayName: 'TIPO', width: 160, enableFiltering: false, 
                cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' 
              }
            ],
            onRegisterApi: function(gridApiMedicamento) {
              $scope.gridApiMedicamento = gridApiMedicamento;
              gridApiMedicamento.selection.on.rowSelectionChanged($scope,function(row){ 
                $scope.mySelectionMedicamentoGrid = gridApiMedicamento.selection.getSelectedRows(); 
                angular.forEach($scope.mySelectionMedicamentoGrid, function (value, key) { 
                  var tempCopy = angular.copy(value);
                  var boolNoAgregar = false;
                  angular.forEach($scope.gridOptionsAddMedicamento.data, function (valueDet, keyDet) {
                    if( valueDet.idmedicamento == value.idmedicamento ){
                      boolNoAgregar = true;
                    }
                  })
                  if( !(boolNoAgregar) ) { 
                    $scope.gridOptionsAddMedicamento.data.push( tempCopy );
                  }
                });
              });
              gridApiMedicamento.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionMedicamentoGrid = gridApiMedicamento.selection.getSelectedRows(); 
                angular.forEach($scope.mySelectionMedicamentoGrid, function (value, key) { 
                  var tempCopy = angular.copy(value);
                  var boolNoAgregar = false;
                  angular.forEach($scope.gridOptionsAddMedicamento.data, function (valueDet, keyDet) {
                    if( valueDet.idmedicamento == value.idmedicamento ){
                      boolNoAgregar = true;
                    }
                  })
                  if( !(boolNoAgregar) ) { 
                    $scope.gridOptionsAddMedicamento.data.push( tempCopy );
                  }
                });
              });
              $scope.gridApiMedicamento.core.on.sortChanged($scope, function(grid, sortColumns) {
                // console.log(sortColumns);
                if (sortColumns.length == 0) { 
                  paginationOptionsMed.sort = null;
                  paginationOptionsMed.sortName = null;
                } else {
                  paginationOptionsMed.sort = sortColumns[0].sort.direction;
                  paginationOptionsMed.sortName = sortColumns[0].name;
                }
                $scope.metodos.getPaginationMedServerSide();
              });
              gridApiMedicamento.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsMed.pageNumber = newPage;
                paginationOptionsMed.pageSize = pageSize;
                paginationOptionsMed.firstRow = (paginationOptionsMed.pageNumber - 1) * paginationOptionsMed.pageSize;
                $scope.metodos.getPaginationMedServerSide();
              });
              $scope.gridApiMedicamento.core.on.filterChanged( $scope, function(grid, searchColumns) { 
                var grid = this.grid;
                paginationOptionsMed.search = true;
                paginationOptionsMed.searchColumn = {
                  'idmedicamento' : grid.columns[1].filters[0].term,
                  'denominacion' : grid.columns[2].filters[0].term,
                  '(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END)' : grid.columns[3].filters[0].term,
                  'nombre_lab' : grid.columns[4].filters[0].term
                  
                }
                $scope.metodos.getPaginationMedServerSide();
              });
            }
          }
          paginationOptionsMed.sortName = $scope.gridOptionsMedicamentos.columnDefs[1].name;
          $scope.metodos.getPaginationMedServerSide = function() {
            var arrParams = {
              paginate : paginationOptionsMed,
              datos: $scope.fDataAdd
            };
            medicamentoAlmacenServices.sListarMedicamentosSinEsteAlmacen(arrParams).then(function (rpta) {
              $scope.gridOptionsMedicamentos.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsMedicamentos.data = rpta.datos;
            });
            $scope.mySelectionMedicamentoGrid = [];
            // $scope.gridOptionsAddMedicamento.data = [];
          };
          
          $scope.getTableHeight = function () {
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return {
                height: ($scope.gridOptionsAddMedicamento.data.length * rowHeight + headerHeight + 30) + "px"
             };
          }
          $scope.gridOptionsAddMedicamento = { 
            minRowsToShow: 10,
            paginationPageSize: 10,
            columnDefs: [ 
              { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: 56, visible: false, sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
              { field: 'codigo_barra', name: 'codigo_barra', displayName: 'COD. BARRA', width: 120, cellClass:'ui-editCell' /*, enableCellEdit: false*/ },
              { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO AGREGADO', enableCellEdit: false },
              { field: 'precio', name: 'precio', displayName: 'PRECIO S./', width: 86, cellClass:'ui-editCell' },
              { field: 'accion', name:'accion', displayName: '', width: 40, 
                cellTemplate:'<div class="">'+
                '<button type="button" class="btn btn-sm btn-danger inline-block m-xs" ng-click="grid.appScope.btnQuitarDeLaCesta(row)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>' , enableCellEdit: false
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi; 
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
                if( colDef.field == 'codigo_barra' ){ 
                  var arrEditCell = { 
                    'codigo_barra' : newValue,
                    'idmedicamento' : rowEntity.idmedicamento
                  }
                  medicamentoServices.sEditarCodigoBarraMedicamento(arrEditCell).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success'; 
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                  $scope.$apply();
                }else{
                  return;
                }
                
              });
            }
          }; 
          $scope.btnQuitarDeLaCesta = function (row) { 
            // var arrParams = row.entity;
            var index = $scope.gridOptionsAddMedicamento.data.indexOf(row.entity); 
            $scope.gridOptionsAddMedicamento.data.splice(index,1); 
          }
          $scope.aceptar = function () { 
            $scope.fDataAdd.medicamentos = $scope.gridOptionsAddMedicamento.data; 
            medicamentoAlmacenServices.sAgregarMedicamentoAAlmacen($scope.fDataAdd).then(function (rpta) { 
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
              $scope.getPaginationServerSide();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }
          $scope.metodos.getPaginationMedServerSide();
        }
      });
    }
    $scope.btnAnularMedicamento = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          medicamentoAlmacenServices.sAnularMedicamentoAlmacen($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide('si',true);
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

    $scope.btnAnularPreparado = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          medicamentoAlmacenServices.sAnularMedicamentoAlmacen($scope.mySelectionGridPreparado).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationPreServerSide('si',true);
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
      /* ============================= */
     /* KARDEX E HISTORIAL DE PRECIOS */
    /* ============================= */
    $scope.btnVerKardex = function () { 
      blockUI.start('Ejecutando proceso...');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'MedicamentoAlmacen/ver_popup_kardex',
        size: 'xlg',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          blockUI.stop();
          $scope.listaAlmacenes = angular.copy($scope.listaAlmacen);
          $scope.fDataKardex = {};
          $scope.fDataKardex = angular.copy($scope.mySelectionGrid[0]);
          $scope.titleForm = 'KARDEX VALORIZADO'
          $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
          /*
          var hoy = new Date();
          var desde = hoy - 1209600000; // restamos 14 dias
          $scope.fBusqueda.desde = $filter('date')(desde,'dd-MM-yyyy');
          $scope.fBusqueda.hasta = $filter('date')(hoy,'dd-MM-yyyy');
          */
          var arrParams = {
            datos: $scope.mySelectionGrid[0],
            boolVenta: $scope.venta_a_cliente
          };
          blockUI.start('Cargando datos');
          medicamentoAlmacenServices.sListarDetalleKardex(arrParams).then(function (rpta) {
            $scope.listaMovimientos = rpta.datos;
            blockUI.stop();
          });
          $scope.procesar = function(){
            blockUI.start('Cargando datos');
            var arrParams = {
              datos: $scope.mySelectionGrid[0],
              busqueda: $scope.fBusqueda
            };
            medicamentoAlmacenServices.sListarDetalleKardex(arrParams).then(function (rpta) {
              $scope.listaMovimientos = rpta.datos;
              blockUI.stop();
            });
          }
          $scope.btnExportarPdf = function(){
            $scope.fBusqueda.producto = $scope.fDataKardex.medicamento;
            var arrParams = {
              titulo: 'KARDEX VALORIZADO',
              datos:{
                lista: $scope.listaMovimientos,
                busqueda: $scope.fBusqueda,
                salida: 'pdf',
                tituloAbv: 'kardex',
                titulo: 'KARDEX VALORIZADO'
              },
              metodo: 'php'
            }
            arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_kardex',
            ModalReporteFactory.getPopupReporte(arrParams);
          }
          $scope.btnExportarExcel = function(){
            console.log('Excel');
            $scope.fBusqueda.producto = $scope.fDataKardex.medicamento;
            var arrParams = {
              titulo: 'KARDEX VALORIZADO',
              datos:{
                lista: $scope.listaMovimientos,
                busqueda: $scope.fBusqueda,
                salida: 'excel',
                tituloAbv: 'kardex',
                titulo: 'KARDEX VALORIZADO'
              },
              metodo: 'js'
            }
            // console.log('url ', angular.patchURLCI,'CentralReportes/report_kardex');
            arrParams.url = angular.patchURLCI+'CentralReportes/report_kardex',
            ModalReporteFactory.getPopupReporte(arrParams);
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.getPaginationServerSide('si',true);
          }
        }
      });
    }
    $scope.btnVeHistorialPrecios = function () { 
      $uibModal.open({
        templateUrl: angular.patchURLCI+'MedicamentoAlmacen/ver_popup_historial_precios',
        size: 'lg',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.listaAlmacenes = angular.copy($scope.listaAlmacen);
          $scope.fDataHistorial = {};
          $scope.fDataHistorial = angular.copy($scope.mySelectionGrid[0]);
          $scope.titleForm = 'HISTORIAL DE PRECIOS'
          
          /*
          var hoy = new Date();
          var desde = hoy - 1209600000; // restamos 14 dias
          $scope.fBusqueda.desde = $filter('date')(desde,'dd-MM-yyyy');
          $scope.fBusqueda.hasta = $filter('date')(hoy,'dd-MM-yyyy');
          */
          var arrParams = {
            datos: $scope.mySelectionGrid[0]
          };
          medicamentoAlmacenServices.sListarHistorial(arrParams).then(function (rpta) {
            $scope.listaHistorial = rpta.datos;
            $scope.fDataHistorial.precio_actual = rpta.datos[0].precio_venta;
            $scope.fDataHistorial.precio_inicial = rpta.precio_inicial;
            if(rpta.datos[0].precio_venta_anterior == null){
              $scope.listaHistorial = null;
            }
          });
          
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.getPaginationServerSide('si',true);
          }
        }
      });
    }

    //  GRID PARA GESTION DE PREPARADOS
    var paginationOptionsPre = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsPre = { 
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
        { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: 80, sort: { direction: uiGridConstants.DESC}, enableCellEdit: false, pinnedLeft:true },
        { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', minWidth: 290/*, maxWidth: 300*/,  enableCellEdit: false, pinnedLeft:false },
        { field: 'stock_inicial', name: 'stock_inicial', displayName: 'STOCK INICIAL', width: 130, cellClass:'ui-editCell' },
        { field: 'stock_entradas', name: 'stock_entradas', displayName: 'STOCK ENTRADAS', width: 136, cellClass:'ui-editCell' },
        { field: 'stock_salidas', name: 'stock_salidas', displayName: 'STOCK SALIDAS', width: 130, cellClass:'ui-editCell' },
        { field: 'stock_actual_malm', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: 130, enableCellEdit: false },
        { field: 'precio_compra_sf', name: 'precio_compra_sf', displayName: 'P. COMPRA', width: 100, visible: true,enableCellEdit: false, cellClass:'text-right' },
        { field: 'precio_venta_sf', name: 'precio_venta_sf', displayName: 'P. VENTA', width: 100, visible: true,enableCellEdit: false, cellClass:'text-right'  },
        { field: 'utilidad_porcentaje', name: 'utilidad_porcentaje', displayName: 'UTILIDAD %', width: 110, visible: true,enableCellEdit: false, cellClass:'text-right' },
        { field: 'estadoMed', type: 'object', name: 'estado_fma', displayName: 'ESTADO', width: 140, enableFiltering: false, enableCellEdit: false, pinnedRight:false, 
          cellTemplate:'<label ng-click="grid.appScope.btnHabilitarDeshabilitar(row)" style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label label-hand {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }        
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiPre = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridPreparado = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridPreparado = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiPre.core.on.sortChanged($scope, function(grid, sortColumns) {
          if (sortColumns.length == 0) {
            paginationOptionsPre.sort = null;
            paginationOptionsPre.sortName = null;
          } else {
            paginationOptionsPre.sort = sortColumns[0].sort.direction;
            paginationOptionsPre.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPreServerSide();
        });
        $scope.gridApiPre.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptionsPre.search = true; 
          paginationOptionsPre.searchColumn = {
            'med.idmedicamento' : grid.columns[1].filters[0].term,
            'denominacion' : grid.columns[2].filters[0].term,
            'precio_compra_sf' : grid.columns[3].filters[0].term,
            'precio_venta_sf' : grid.columns[4].filters[0].term,
            // 'estadoMed' : grid.columns[4].filters[0].term            
          }
          $scope.getPaginationPreServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsPre.pageNumber = newPage;
          paginationOptionsPre.pageSize = pageSize;
          paginationOptionsPre.firstRow = (paginationOptionsPre.pageNumber - 1) * paginationOptionsPre.pageSize;
          $scope.getPaginationPreServerSide();
        });
      }
    };
    paginationOptionsPre.sortName = $scope.gridOptionsPre.columnDefs[0].name;
    $scope.getPaginationPreServerSide = function(verificacion,loader) { 
      var loader = loader || false;
      var verificacion = verificacion || 'si';
      var arrParams = {
        paginate : paginationOptionsPre,
        datos: $scope.fBusqueda
      };
      if(verificacion == 'si'){
        if( loader ){
          blockUI.start('Ejecutando proceso...');
        }
        medicamentoAlmacenServices.sListarPreparadoAlmacen(arrParams).then(function (rpta) { 
        $scope.gridOptionsPre.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsPre.data = rpta.datos;
        if( loader ){
          blockUI.stop();
        }
      });
        $scope.mySelectionGridPreparado = [];
      }else if(verificacion == 'no'){
        medicamentoAlmacenServices.sListarPreparadoAlmacen(arrParams).then(function (rpta) { 
          $scope.gridOptionsPre.totalItems = rpta.paginate.totalRows;
          $scope.gridOptionsPre.data = rpta.datos;
        });
      }else{
        alert('Inténtelo otra vez, presionando CTRL + F5');
      }
    };
      /* ============= */
     /* EXPORTACIONES */
    /* ============= */
    $scope.btnExportarListaPdf = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'INVENTARIO',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'pdf',
          tituloAbv: 'INV',
          titulo: 'INVENTARIO',
        },
        metodo: 'php'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_inventario_farmacia',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnExportarListaExcel = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'INVENTARIO',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'excel',
          tituloAbv: 'INV',
          titulo: 'INVENTARIO',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_inventario_farmacia_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    $scope.numberFormat = function(monto, decimales){
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
  }])
  .service("medicamentoAlmacenServices",function($http, $q) {
    return({
        sListarMedicamentosAlmacenVentaAutoComplete: sListarMedicamentosAlmacenVentaAutoComplete,
        sListarPreparadosAlmacenVentaAutoComplete: sListarPreparadosAlmacenVentaAutoComplete,
        sListarMedicamentoAlmacen: sListarMedicamentoAlmacen,
        sListarMedicamentoSubAlmacenVenta: sListarMedicamentoSubAlmacenVenta,
        sListarPreparadoAlmacen: sListarPreparadoAlmacen,
        sListarMedicamentosAlmacenBusquedaVenta: sListarMedicamentosAlmacenBusquedaVenta,
        sAgregarMedicamentoAAlmacen: sAgregarMedicamentoAAlmacen,
        sListarMedicamentosSinEsteAlmacen: sListarMedicamentosSinEsteAlmacen,
        sEditarMedicamentoAlmacenInline: sEditarMedicamentoAlmacenInline,
        sHabilitarDeshabilitarMedicAlmacen: sHabilitarDeshabilitarMedicAlmacen,
        sVerificarSubalmacen : sVerificarSubalmacen,
        sAnularMedicamentoAlmacen : sAnularMedicamentoAlmacen,
        sListarDetalleKardex : sListarDetalleKardex,
        sListarStocks:sListarStocks,
        sListarHistorial: sListarHistorial,
        sListarPreparadosAlmacenBusquedaVenta: sListarPreparadosAlmacenBusquedaVenta,
    });
    function sListarMedicamentosAlmacenVentaAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamento_almacen_venta_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPreparadosAlmacenVentaAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_preparado_almacen_venta_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentoAlmacen(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamentos_almacen",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentoSubAlmacenVenta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamento_subalmacen_venta",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPreparadoAlmacen(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_preparados_almacen",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentosAlmacenBusquedaVenta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamento_almacen_busqueda_venta",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPreparadosAlmacenBusquedaVenta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_preparados_almacen_busqueda_venta",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentosSinEsteAlmacen (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamentos_sin_este_almacen",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarMedicamentoAAlmacen(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/agregar_medicamento_a_almacen",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarMedicamentoAlmacenInline(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/editar_medicamento_almacen_inline",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitarDeshabilitarMedicAlmacen(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/habilitar_deshabilitar_medicamento_almacen",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularMedicamentoAlmacen(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/anular_medicamento_almacen",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sVerificarSubalmacen(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/verificar_subalmacen_venta_a_cliente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleKardex(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_kardex",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarStocks(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/cargar_stocks_medicamento",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarHistorial(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/cargar_historial_precios",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });