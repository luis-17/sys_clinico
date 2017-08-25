angular.module('theme.almacenFarm', ['theme.core.services'])
  .controller('almacenFarmController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'almacenFarmServices', 'empresaAdminServices' , 'tipoSubalmacenServices' ,
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , almacenFarmServices , empresaAdminServices , tipoSubalmacenServices
      ){
    'use strict';
    /*------------------------------------------------------------------------------------------------*/
    var arrayTipoSubAlm = {};
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    $scope.getListaTipoSubAlmacen = function(){
      tipoSubalmacenServices.sListartipoSubalmacenCbo().then(function (rpta) {
        arrayTipoSubAlm = rpta.datos;
      });
    }
    $scope.getListaTipoSubAlmacen();

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
        { field: 'id', name: 'idalmacen', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'almacen', name: 'nombre_alm', displayName: 'Almacen' },
        { field: 'sede', name: 'sede', displayName: 'Sede' },
        { field: 'empresa', name: 'empresa', displayName: 'Empresa' },
        { field: 'estado', type: 'object', name: 'estado_alm', displayName: 'Estado', maxWidth: 250, enableFiltering: false, enableSorting: false ,
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
            paginationOptions.searchColumn = {
              'idalmacen' : grid.columns[1].filters[0].term,
              'nombre_alm' : grid.columns[2].filters[0].term,
              's.descripcion' : grid.columns[3].filters[0].term,
              'ea.razon_social' : grid.columns[4].filters[0].term
            }
            $scope.getPaginationServerSide();
          });

      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      almacenFarmServices.sListarAlmacenesSession($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'almacenFarmacia/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) { 
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fDataAdd = {};
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fDataAdd =  $scope.mySelectionGrid[0] ;
            $scope.fDataAdd.nombre_alm = $scope.mySelectionGrid[0].almacen;
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Almacen';
          /*---------------------------------------------------------------------------*/
          var paginationEmpresaSedeOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionEmpresaSedeGrid = [];
          $scope.gridOptionsEmpresaSede = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'idsedeempresaadmin', name: 'idsedeempresaadmin', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
              { field: 'razon_social', name: 'empresa', displayName: 'Empresa', maxWidth: 250 },
              { field: 'descripcion', name: 'sede', displayName: 'Sede', maxWidth: 250 }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionEmpresaSedeGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionEmpresaSedeGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationEmpresaSedeOptions.sort = null;
                  paginationEmpresaSedeOptions.sortName = null;
                } else {
                  paginationEmpresaSedeOptions.sort = sortColumns[0].sort.direction;
                  paginationEmpresaSedeOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationEmpresaSedeServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationEmpresaSedeOptions.pageNumber = newPage;
                paginationEmpresaSedeOptions.pageSize = pageSize;
                paginationEmpresaSedeOptions.firstRow = (paginationEmpresaSedeOptions.pageNumber - 1) * paginationEmpresaSedeOptions.pageSize;
                $scope.getPaginationEmpresaSedeServerSide();
              });

            }
          };
          paginationEmpresaSedeOptions.sortName = $scope.gridOptionsEmpresaSede.columnDefs[0].name;
          $scope.getPaginationEmpresaSedeServerSide = function() { 
            var vemp = 0 ;
            $scope.datosGrid = {
              paginate : paginationEmpresaSedeOptions
            };
            empresaAdminServices.sListarSedeEmpresaAdmin($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsEmpresaSede.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsEmpresaSede.data = rpta.datos;
            });
            $timeout(function() {
              angular.forEach($scope.gridOptionsEmpresaSede.data, function(value, key){
                  if($scope.fDataAdd.idsedeempresaadmin == value.idsedeempresaadmin ){
                    vemp = key ;
                    return;
                  }
              });
              if($scope.gridApi.selection.selectRow){
                $scope.gridApi.selection.selectRow($scope.gridOptionsEmpresaSede.data[vemp]);
              }
            },1000);
            $scope.mySelectionEmpresaSedeGrid = [];
          };
          $scope.getPaginationEmpresaSedeServerSide();
          /*--------------------------------------------------------------------------------------*/

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataAdd = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            if(!angular.isObject($scope.mySelectionEmpresaSedeGrid[0])){ // console.log('especialidad');
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado ninguna Sede Empresa', type: 'warning', delay: 2000 });
                return false;
            }else {
              $scope.fDataAdd.idsedeempresaadmin = $scope.mySelectionEmpresaSedeGrid[0].idsedeempresaadmin;
            }
            almacenFarmServices.sEditarAlmacen($scope.fDataAdd).then(function (rpta) {
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
      $modal.open({
        templateUrl: angular.patchURLCI+'almacenFarmacia/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Almacen';
          /*---------------------------------------------------------------------------*/
          var paginationEmpresaSedeOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionEmpresaSedeGrid = [];
          $scope.gridOptionsEmpresaSede = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'idsedeempresaadmin', name: 'idsedeempresaadmin', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
              { field: 'razon_social', name: 'razon_social', displayName: 'Empresa', maxWidth: 250 },
              { field: 'descripcion', name: 'descripcion', displayName: 'Sede', maxWidth: 250 }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionEmpresaSedeGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionEmpresaSedeGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationEmpresaSedeOptions.sort = null;
                  paginationEmpresaSedeOptions.sortName = null;
                } else {
                  paginationEmpresaSedeOptions.sort = sortColumns[0].sort.direction;
                  paginationEmpresaSedeOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationEmpresaSedeServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationEmpresaSedeOptions.pageNumber = newPage;
                paginationEmpresaSedeOptions.pageSize = pageSize;
                paginationEmpresaSedeOptions.firstRow = (paginationEmpresaSedeOptions.pageNumber - 1) * paginationEmpresaSedeOptions.pageSize;
                $scope.getPaginationEmpresaSedeServerSide();
              });
            }
          };
          paginationEmpresaSedeOptions.sortName = $scope.gridOptionsEmpresaSede.columnDefs[0].name;
          $scope.getPaginationEmpresaSedeServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationEmpresaSedeOptions
            };
            empresaAdminServices.sListarSedeEmpresaAdmin($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsEmpresaSede.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsEmpresaSede.data = rpta.datos;
            });
            $scope.mySelectionEmpresaSedeGrid = [];
          };
          $scope.getPaginationEmpresaSedeServerSide();
          /*--------------------------------------------------------------------------------------*/
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            if(!angular.isObject($scope.mySelectionEmpresaSedeGrid[0])){ // console.log('especialidad');
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado ninguna Sede Empresa', type: 'warning', delay: 2000 });
                return false;
            }else {
              $scope.fDataAdd.idsedeempresaadmin = $scope.mySelectionEmpresaSedeGrid[0].idsedeempresaadmin;
            }
            almacenFarmServices.sRegistrarAlmacen($scope.fDataAdd).then(function (rpta) {
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
          almacenFarmServices.sAnularAlmacen($scope.mySelectionGrid).then(function (rpta) {
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
          almacenFarmServices.sHabilitarAlmacen($scope.mySelectionGrid).then(function (rpta) {
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
          almacenFarmServices.sDeshabilitarAlmacen($scope.mySelectionGrid).then(function (rpta) {
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
    
    /*-------------------------------------------------------------------------*/
    /*------------------- AGREGAR SUB ALMACEN ---------------------------------*/
    /*-------------------------------------------------------------------------*/

    $scope.btnAgregarSubAlmacen = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'almacenFarmacia/ver_popup_formulario_subalmacen',
        size: size || '',
        scope: $scope,
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationSubAlmacenServerSide) {
          $scope.getPaginationSubAlmacenServerSide = getPaginationSubAlmacenServerSide;
          $scope.fDataAdd = {};
          $scope.titleForm = 'Registro de Sub Almacen';
          var paginationSubAlmacenOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionSubAlmacenGrid = [];
          $scope.gridOptionsSubAlmacen = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: false,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            minRowsToShow: 8,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'id', name: 'idsubalmacen', displayName: 'ID', maxWidth: 60,enableCellEdit: false ,sort: { direction: uiGridConstants.ASC} },
              { field: 'nombre_salm', name: 'nombre_salm', displayName: 'Descripcion', maxWidth: 200 ,cellClass:'ui-editCell' },
              { field: 'es_principal', name: 'es_principal', displayName: 'Principal', maxWidth: 150 ,enableCellEdit: false },
              { field: 'descripcion_tsa', name: 'descripcion_tsa', displayName: 'Tipo', maxWidth: 200 ,editableCellTemplate: 'ui-grid/dropdownEditor' , editDropdownValueLabel: 'descripcion', editDropdownOptionsArray: arrayTipoSubAlm },
              { field: 'accion', displayName: 'Acción', maxWidth: 90, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionSubAlmacenGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionSubAlmacenGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                rowEntity.column = colDef.field;
                rowEntity.newvalue = newValue;
                //console.log(rowEntity);
                if(rowEntity.es_principal == 'SI'){
                  $scope.getPaginationSubAlmacenServerSide();
                  pinesNotifications.notify({ title: "Error ", text: "No puede editar este subAlmacen por ser Principal", type: 'error', delay: 1500 });
                  return;
                }
                if(rowEntity.newvalue == rowEntity.nombre_salm && rowEntity.column=='descripcion_tsa'){
                  $scope.getPaginationSubAlmacenServerSide();
                  return;
                }
                if(rowEntity.newvalue == 1){
                  $scope.getPaginationSubAlmacenServerSide();
                  pinesNotifications.notify({ title: "Error ", text: "No puede cambiar este subAlmacen a Central", type: 'error', delay: 1500 });
                  return;
                }
                //console.log(rowEntity);
                //return;
                almacenFarmServices.sEditarSubAlmacenInGrid(rowEntity).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success'; 
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  $scope.getPaginationSubAlmacenServerSide();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                });
                $scope.$apply();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationSubAlmacenOptions.pageNumber = newPage;
                paginationSubAlmacenOptions.pageSize = pageSize;
                paginationSubAlmacenOptions.firstRow = (paginationSubAlmacenOptions.pageNumber - 1) * paginationSubAlmacenOptions.pageSize;
                $scope.getPaginationSubAlmacenServerSide();
              });
            }
          };
          paginationSubAlmacenOptions.sortName = $scope.gridOptionsSubAlmacen.columnDefs[0].name;
          $scope.getPaginationSubAlmacenServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationSubAlmacenOptions ,
              datos : $scope.mySelectionGrid[0].id
            };
            //console.log($scope.mySelectionGrid[0].id);
            almacenFarmServices.sListarSubAlmacenes($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsSubAlmacen.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsSubAlmacen.data = rpta.datos;
            });
            $scope.mySelectionSubAlmacenGrid = [];
          };
          $scope.getPaginationSubAlmacenServerSide();
          $scope.getCargaTipoSubAlmacen = function() {
            tipoSubalmacenServices.sListartipoSubalmacenCbo().then(function (rpta) {
              $scope.listaTipoSubAlmacen = rpta.datos;
              $scope.listaTipoSubAlmacen.splice(0,0,{ id : '0', descripcion:'--Seleccione el tipo --'});
              $scope.fDataAdd.tiposubalmacen = $scope.listaTipoSubAlmacen[0].id;    
            });
          }
          $scope.getCargaTipoSubAlmacen();

          $scope.evalua = function (item) {
            if(item == 1){
              pinesNotifications.notify({ title: "Error ", text: "Debe seleccionar otro tipo de SubAlmacen ", type: 'error', delay: 1000 });
              $scope.getCargaTipoSubAlmacen();
            }
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.agregarItem = function () {
            if($scope.fDataAdd.tiposubalmacen == 0){
              pinesNotifications.notify({ title: "Error ", text: "Debe seleccionar un tipo de SubAlmacen ", type: 'error', delay: 1000 });
              return;
            }
            $scope.fDataAdd.idalmacen = $scope.mySelectionGrid[0].id ;
            almacenFarmServices.sRegistrarSubAlmacen($scope.fDataAdd).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                //$modalInstance.dismiss('cancel');
                $scope.getPaginationSubAlmacenServerSide();
                $scope.fDataAdd.nombre_salm = null ;
                $scope.getCargaTipoSubAlmacen();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }

          $scope.btnQuitarDeLaCesta = function (row,mensaje) { 
              var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  if(row.entity.es_principal == 'SI'){
                    pinesNotifications.notify({ title: 'Error', text: 'No puede anular un almacen Principal', type: 'error', delay: 1000 });
                    return;
                  }
                  almacenFarmServices.sAnularSubAlmacen(row.entity.id).then(function (rpta) {
                    if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success';
                        $scope.getPaginationSubAlmacenServerSide();
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
        },
        resolve: {
          getPaginationSubAlmacenServerSide: function() {
            return $scope.getPaginationSubAlmacenServerSide;
          }
        }
      });
    }
    /*------------------   FIN AGREGAR SUBALMACEN     -------------------------------------*/

  }])
  .service("almacenFarmServices",function($http, $q) {
    return({
        sListarAlmacenesSession : sListarAlmacenesSession,
        sListarAlmacenesParaMedicamentoSession: sListarAlmacenesParaMedicamentoSession,
        sListarAlmacenesEdicionSession : sListarAlmacenesEdicionSession,
        sListarAlmacenesDestinoDeEmpresaCbo: sListarAlmacenesDestinoDeEmpresaCbo,
        sListarAlmacenesCboSession : sListarAlmacenesCboSession,
        sListarSubAlmacenesDeAlmacenCbo : sListarSubAlmacenesDeAlmacenCbo,
        sListarSubAlmacenesDeAlmacenPreparadoCbo: sListarSubAlmacenesDeAlmacenPreparadoCbo,
        sListarSubAlmacenesVentaDeAlmacenCbo: sListarSubAlmacenesVentaDeAlmacenCbo,
        sListarSubAlmacenVentaPorIdAlmacenCbo: sListarSubAlmacenVentaPorIdAlmacenCbo,
        sListarSubAlmacenesDeAlmacenExceptoCbo : sListarSubAlmacenesDeAlmacenExceptoCbo,
        sRegistrarAlmacen : sRegistrarAlmacen,
        sEditarAlmacen : sEditarAlmacen,
        sAnularAlmacen : sAnularAlmacen,
        sHabilitarAlmacen : sHabilitarAlmacen,
        sDeshabilitarAlmacen : sDeshabilitarAlmacen,
        sListarSubAlmacenes : sListarSubAlmacenes,
        sRegistrarSubAlmacen : sRegistrarSubAlmacen,
        sEditarSubAlmacenInGrid : sEditarSubAlmacenInGrid,
        sAnularSubAlmacen : sAnularSubAlmacen

    });

    function sListarAlmacenesSession(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_almacenes_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAlmacenesParaMedicamentoSession(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_almacenes_para_medicamento_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAlmacenesEdicionSession (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_almacenes_edicion_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAlmacenesDestinoDeEmpresaCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_almacenes_destino_de_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAlmacenesCboSession (pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_almacenes_cbo_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenesDeAlmacenCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_sub_almacenes_de_almacen_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenesDeAlmacenPreparadoCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_sub_almacenes_de_almacen_preparado_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenesVentaDeAlmacenCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_sub_almacenes_para_venta_por_almacen_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenVentaPorIdAlmacenCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_sub_almacenes_venta_por_id_almacen_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenesDeAlmacenExceptoCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_sub_almacenes_de_almacen_excepto_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/registrar_almacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/editar_almacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/anular_almacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitarAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/habilitar_almacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitarAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/deshabilitar_almacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenes(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_subalmacenes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarSubAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/registrar_subalmacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarSubAlmacenInGrid (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/editar_subalmacen_en_grid", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularSubAlmacen (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/anular_subalmacen", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });