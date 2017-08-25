angular.module('theme.parametro', ['theme.core.services'])
  .controller('parametroController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys','parametroServices',
    'empresaAdminServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , hotkeys, parametroServices,
      empresaAdminServices
      ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.fBusqueda = {};
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // LISTA DE EMPRESAS-SEDES
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) {
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      // $scope.getPaginationServerSide();  
    });
    // LISTA MODO DE MEDIDA
      $scope.listadoUnidadTiempo = [
        {id: '1', descripcion: 'DIAS'},
        {id: '2', descripcion: 'MESES'},
        {id: '3', descripcion: 'AÑOS'}
      ];
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
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
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'par.idparametro', displayName: 'COD.', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'descripcion_par', displayName: 'PARAMETRO' },
        { field: 'combo', name: 'nombre_combo', displayName: 'SELECTOR', width: 120 },
        { field: 'texto_agregado', name: 'texto_adicional', displayName: 'TEXTO AGREGADO' },
        { field: 'descripcion_adicional', name: 'descripcion_adicional', displayName: 'DESCRIPCION' },

        // { field: 'valorNormalHombres', name: 'valor_normal_h', displayName: 'Valor Normal Hombres',minWidth: 180 },
        // { field: 'valorNormalMujeres', name: 'valor_normal_m', displayName: 'Valor Normal Mujeres',minWidth: 180 },
        // { field: 'valorAmbos', type: 'object', name: 'valor_ambos', displayName: 'Ambos?',maxWidth: 80,
        // cellTemplate:'<div class="text-center">{{ COL_FIELD.string }}</div>' },
        { field: 'separador', name: 'separador', displayName: 'Agrupador', maxWidth: 90, enableFiltering: false,
          cellTemplate:'<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 0"> NO </div>' },
        { field: 'estado', type: 'object', name: 'estado_par', displayName: 'Estado', maxWidth: 200, enableFiltering: false,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
            var grid = this.grid;
            paginationOptions.search = true;
            // console.log(grid.columns);
            // console.log(grid.columns[1].filters[0].term);
            paginationOptions.searchColumn = {
              'par.idparametro' : grid.columns[1].filters[0].term,
              'descripcion_par' : grid.columns[2].filters[0].term,
              'nombre_combo' : grid.columns[3].filters[0].term,
              'texto_adicional' : grid.columns[4].filters[0].term,
              'descripcion_adicional' : grid.columns[5].filters[0].term,

            }
            $scope.getPaginationServerSide();
          });

      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      console.log('Pagination');
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      parametroServices.sListarparametro($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'parametro/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.fData = {};
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Parámetro';
          
          $scope.fData.temporal = {};
          $scope.fData.temporal.desde = null;
          $scope.fData.temporal.hasta = null;
          $scope.fData.temporal.tipo_rango = $scope.listadoUnidadTiempo[0];
          $scope.fData.temporal.valor_etario_h = null;
          $scope.fData.temporal.valor_etario_m = null;
          /* DATA GRID */ 
            var paginationValoresOptions = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsValores = {
              paginationPageSizes: [10, 50, 100, 500, 1000],
              paginationPageSize: 10,
              minRowsToShow: 5,
              useExternalPagination: true,
              useExternalSorting: true,
              enableFiltering: false,
              columnDefs: [
                { field: 'min_rango', name: 'min_rango', displayName: 'DESDE',width: 80, enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false }, 
                { field: 'max_rango', name: 'max_rango', displayName: 'HASTA',width: 80, enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false } ,
                // { field: 'tipo_rango', name: 'tipo_rango', displayName: 'TIEMPO',width: 90, enableCellEdit: false, cellClass:'left', enableSorting: false } ,
                { field: 'tipo_rango', displayName: 'UND. TIEMPO', width: 120, enableCellEdit: true,
                  editableCellTemplate: 'ui-grid/dropdownEditor',
                  editDropdownIdLabel: 'id', editDropdownValueLabel: 'descripcion',
                  editDropdownOptionsArray: $scope.listadoUnidadTiempo,
                  cellFilter: 'griddropdown:this', cellClass:'ui-editCell'
                },
                { field: 'descripcion', name: 'descripcion', displayName: 'DESCRIPCION', width: 140, enableCellEdit: false, cellClass:'left', enableSorting: false } ,
                { field: 'valor_etario_h', name: 'valor_etario_h', displayName: 'VALOR NORMAL HOMBRES', enableCellEdit: true, cellClass:'ui-editCell' },
                { field: 'valor_etario_m', name: 'valor_etario_m', displayName: 'VALOR NORMAL MUJERES', enableCellEdit: true, cellClass:'ui-editCell' },
                { field: 'accion', displayName: '', maxWidth: 60,
                cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarValorEtario(row)" tooltip-placement="left" tooltip="Eliminar"> <i class="fa fa-trash"></i> </button>',
                enableSorting: false }
              ],
              onRegisterApi: function(gridApi) {
                $scope.gridApi = gridApi;
                gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                  
                  rowEntity.column = colDef.field;
                  //console.log(rowEntity.column);
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  if( !(rowEntity.min_rango == null || rowEntity.min_rango == '' || rowEntity.min_rango >= 0) ||
                      !(rowEntity.max_rango == null || rowEntity.max_rango == '' || rowEntity.max_rango >= 0)){
                    var pMessage = 'Ingrese valores numéricos';
                    pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3500 });
                    if(rowEntity.column == 'min_rango'){
                      rowEntity.min_rango = oldValue;
                    }else if(rowEntity.column == 'max_rango'){
                      rowEntity.max_rango = oldValue;
                    }
                    return false;
                  }
                  if( (rowEntity.min_rango >= rowEntity.max_rango) && (rowEntity.min_rango >= 1) && (rowEntity.max_rango >= 0) && !(rowEntity.max_rango == null || rowEntity.max_rango == '' ) ){
                    if(rowEntity.column == 'min_rango'){
                      rowEntity.min_rango = oldValue;
                    }else if(rowEntity.column == 'max_rango'){
                      rowEntity.max_rango = oldValue;
                    }
                    var pMessage = 'El campo HASTA debe ser mayor que el campo DESDE';
                    pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 3500 });
                    return false;
                  }
                  if( rowEntity.column == 'min_rango' || rowEntity.column == 'max_rango' || rowEntity.column == 'tipo_rango' ){
                    rowEntity.descripcion = $scope.generarDescripcionRango(rowEntity);
                  }
                  $scope.$apply();
                });
                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationValoresOptions.sort = null;
                    paginationValoresOptions.sortName = null;
                  } else {
                    paginationValoresOptions.sort = sortColumns[0].sort.direction;
                    paginationValoresOptions.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationValoresServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationValoresOptions.pageNumber = newPage;
                  paginationValoresOptions.pageSize = pageSize;
                  paginationValoresOptions.firstRow = (paginationValoresOptions.pageNumber - 1) * paginationValoresOptions.pageSize;
                  $scope.getPaginationValoresServerSide();
                });
              }
            };
            paginationValoresOptions.sortName = $scope.gridOptionsValores.columnDefs[0].name;
            $scope.getPaginationValoresServerSide = function() { 
              $scope.datosGrid = { 
                paginate : paginationValoresOptions,
                datos : $scope.mySelectionGrid[0],
              };
              parametroServices.sListarValoresParametro($scope.datosGrid).then(function (rpta) { 
                //$scope.gridOptionsValores.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsValores.data = rpta.datosJSon;
                $scope.fData.valorNormalHombres = rpta.datos.valorNormalHombres;
                $scope.fData.valorNormalMujeres = rpta.datos.valorNormalMujeres;
              });
              // $scope.mySelectionSedeEmpresaGrid = [];
            };
            $scope.getPaginationValoresServerSide();
          // BOTONES
          $scope.generarDescripcionRango = function(row){
            console.log('row.tipo_rango',row.tipo_rango);
            var descripcionRango = '';
            var unidadTiempo = '';
            if(row.tipo_rango == '1')
              unidadTiempo = (row.max_rango == 1) ? 'día' : 'días';
            else if(row.tipo_rango == '2')
              unidadTiempo = (row.max_rango == 1) ? 'mes' : 'meses';
            else if(row.tipo_rango == '3')
              unidadTiempo = (row.max_rango == 1) ? 'año' : 'años';

            if( (row.min_rango == 0 || row.min_rango == '' || row.min_rango == null) && (row.max_rango >= 1) ){
              descripcionRango = 'Menores de ' + row.max_rango + ' ' + unidadTiempo;
            }
            else if( (row.max_rango == '' || row.max_rango == null) && row.min_rango >= 1 ){
              descripcionRango = 'Mayores de ' + row.min_rango + ' ' + unidadTiempo;
            }
            else if( (row.min_rango >= 1) && (row.max_rango >= 1) ){
              if( row.min_rango < row.max_rango ){
                descripcionRango = 'Entre ' + row.min_rango + ' y ' + row.max_rango + ' ' + unidadTiempo;
              }
            }
            return descripcionRango;
          }
          $scope.agregarValorEtario = function(){
            console.log('$scope.fData.temporal',$scope.fData.temporal);
            $('#temporalDesde').focus();
            if( !($scope.fData.temporal.desde >= 0) && $scope.fData.temporal.desde != null){
              $scope.fData.temporal.desde = null;
              $('#temporalDesde').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un rango válido', type: 'warning', delay: 2000 });
              return false;
            }
            if( !($scope.fData.temporal.hasta >= 0) && $scope.fData.temporal.hasta != null){
              $scope.fData.temporal.hasta = null;
              $('#temporalHasta').focus();
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese un rango válido', type: 'warning', delay: 2000 });
              return false;
            }
            if( ($scope.fData.temporal.desde == 0 || $scope.fData.temporal.desde == '' || $scope.fData.temporal.desde == null) &&
                ($scope.fData.temporal.hasta >= 1) ){
                  $scope.fData.temporal.descripcion = 'Menores de ' + $scope.fData.temporal.hasta + ' ' + $scope.fData.temporal.tipo_rango.descripcion;
            }
            else if( ($scope.fData.temporal.hasta == 0 || $scope.fData.temporal.hasta == '' || $scope.fData.temporal.hasta == null) &&
                ($scope.fData.temporal.desde >= 1) ){
                  $scope.fData.temporal.descripcion = 'Mayores de ' + $scope.fData.temporal.desde + ' ' + $scope.fData.temporal.tipo_rango.descripcion;
            }
            else if( ($scope.fData.temporal.desde >= 1) && ($scope.fData.temporal.hasta >= 1) ){
              if( $scope.fData.temporal.desde < $scope.fData.temporal.hasta ){
                $scope.fData.temporal.descripcion = 'Entre ' + $scope.fData.temporal.desde + ' y ' + $scope.fData.temporal.hasta + ' ' + $scope.fData.temporal.tipo_rango.descripcion;
              }else{
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                pinesNotifications.notify({ title: pTitle, text: 'El campo HASTA debe ser mayor que el campo DESDE', type: pType, delay: 3500 });
                return false;
              }
            }
            var tipoRango = null;
            if( $scope.fData.temporal.desde != null || $scope.fData.temporal.hasta != null ){
              tipoRango = $scope.fData.temporal.tipo_rango.id;
            }
            $scope.arrTemporal = { 
              'min_rango' : $scope.fData.temporal.desde,
              'max_rango' : $scope.fData.temporal.hasta,
              'tipo_rango' : tipoRango,
              'descripcion' : $scope.fData.temporal.descripcion,
              'valor_etario_h' : $scope.fData.temporal.valor_etario_h,
              'valor_etario_m' : $scope.fData.temporal.valor_etario_m,

            };
            
            $scope.gridOptionsValores.data.push($scope.arrTemporal);
            $scope.fData.temporal = {
              desde : null,
              hasta : null,
              tipo_rango : $scope.fData.temporal.tipo_rango,
              valor_etario_h : null,
              valor_etario_m : null,
            };
          }
          $scope.btnQuitarValorEtario = function (row) {
            var arrParams = row.entity; 
            var index = $scope.gridOptionsValores.data.indexOf(row.entity); 
            $scope.gridOptionsValores.data.splice(index,1); 
          }
          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};

            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            $scope.fData.arrValores = $scope.gridOptionsValores.data;
            parametroServices.sEditar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                $scope.getPaginationServerSide();
              }else{
                alert('Error inesperado');
              }
              $scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $scope.getPaginationServerSide();
            });
          }
          //console.log($scope.mySelectionGrid);
        },
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          },
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }
    $scope.btnNuevo = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'parametro/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.separador = '0';
          $scope.fData.valorAmbos = {};
          $scope.fData.valorAmbos.bool = '0';
          $scope.titleForm = 'Registro de Parámetro';
          // LISTA MODO DE MEDIDA
          $scope.listadoUnidadTiempo = [
            {id: '1', descripcion: 'DIAS'},
            {id: '2', descripcion: 'MESES'},
            {id: '3', descripcion: 'AÑOS'}
          ];
          // $scope.navegateToCell = function( rowIndex, colIndex ) {
          //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
          // };
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
            multiSelect: true,
            columnDefs: [
              { field: 'id', name: 'par.idparametro', displayName: 'COD.', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
              { field: 'descripcion', name: 'descripcion_par', displayName: 'PARAMETRO' },
              { field: 'combo', name: 'nombre_combo', displayName: 'SELECTOR', width: 120 },
              { field: 'texto_agregado', name: 'texto_adicional', displayName: 'TEXTO AGREGADO' },
              { field: 'descripcion_adicional', name: 'descripcion_adicional', displayName: 'DESCRIPCION' },

              // { field: 'valorNormalHombres', name: 'valor_normal_h', displayName: 'Valor Normal Hombres',minWidth: 180 },
              // { field: 'valorNormalMujeres', name: 'valor_normal_m', displayName: 'Valor Normal Mujeres',minWidth: 180 },
              // { field: 'valorAmbos', type: 'object', name: 'valor_ambos', displayName: 'Ambos?',maxWidth: 80,
              // cellTemplate:'<div class="text-center">{{ COL_FIELD.string }}</div>' },
              { field: 'separador', name: 'separador', displayName: 'Agrupador', maxWidth: 90, enableFiltering: false,
                cellTemplate:'<div class="text-center" ng-if="COL_FIELD == 1"> SI </div><div class="text-center" ng-if="COL_FIELD == 0"> NO </div>' },
              { field: 'estado', type: 'object', name: 'estado_par', displayName: 'Estado', maxWidth: 200, enableFiltering: false,
                cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationOptions.sort = null;
                  paginationOptions.sortName = null;
                } else {
                  paginationOptions.sort = sortColumns[0].sort.direction;
                  paginationOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptions.pageNumber = newPage;
                paginationOptions.pageSize = pageSize;
                paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
                $scope.getPaginationServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptions.search = true;
                  // console.log(grid.columns);
                  // console.log(grid.columns[1].filters[0].term);
                  paginationOptions.searchColumn = {
                    'par.idparametro' : grid.columns[1].filters[0].term,
                    'descripcion_par' : grid.columns[2].filters[0].term,
                    'nombre_combo' : grid.columns[3].filters[0].term,
                    'texto_adicional' : grid.columns[4].filters[0].term,
                    'descripcion_adicional' : grid.columns[5].filters[0].term,

                  }
                  $scope.getPaginationServerSide();
                });

            }
          };
          paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            parametroServices.sRegistrar($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
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
          //console.log($scope.mySelectionGrid);
        },
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          parametroServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
    /* ============================ */
    /* HABILITAR Y DESHABILITAR     */
    /* ============================ */

   $scope.btnHabilitar = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          parametroServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
    $scope.btnDeshabilitar = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          parametroServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva Parámetro',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({
        combo: 'e',
        description: 'Editar Parámetro',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({
        combo: 'del',
        description: 'Anular Parámetro',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnDeshabilitar();
          }
        }
      })
      .add ({
        combo: 'b',
        description: 'Buscar Parámetro',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      });
      // .add ({
      //   combo: 's',
      //   description: 'Selección y Navegación',
      //   callback: function() {
      //     $scope.navegateToCell(0,0);
      //   }
      // });

  }])
  .service("parametroServices",function($http, $q) {
    return({
        sListarparametroCbo: sListarparametroCbo,
        sListarparametro: sListarparametro,
        sListarValoresParametro: sListarValoresParametro,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sHabilitar: sHabilitar,
        sDeshabilitar: sDeshabilitar,
        sAnular: sAnular,
    });

    function sListarparametroCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/lista_parametro_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarparametro(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/lista_parametro",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarValoresParametro(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/listar_valores_parametro",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/registrar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/editar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/habilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/deshabilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"parametro/anular",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });