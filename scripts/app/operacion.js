angular.module('theme.operacion', ['theme.core.services'])
  .controller('operacionController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'operacionServices',  
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, operacionServices ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search:null
    };
    $scope.mySelectionGrid = [];
    $scope.listaTipo = [ 
      { id: 1 , descripcion: 'GASTO' },
      { id: 2 , descripcion: 'COMPRA' }
    ];

    $scope.fBusqueda = {};
    $scope.fBusqueda.tipo = $scope.listaTipo[0];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idoperacion', displayName: 'ID', minWidth: 100, width: '15%' , sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'descripcion_op', displayName: 'DESCRIPCIÓN' },
        { field: "tipo", name: 'tipo', displayName: 'TIPO', width:'15%' ,enableFiltering: false }
        // { field: 'categoria_abv', name: 'categoria_abv', displayName: 'CAT. ABRV', width:'15%' }
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
            'idoperacion' : grid.columns[1].filters[0].term,
            'descripcion_op' : grid.columns[2].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      operacionServices.sListarOperaciones($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'operacion/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.Change = false;
          $scope.fDatos = {};
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = angular.copy($scope.mySelectionGrid[0]);
            $scope.fDatos.after = angular.copy($scope.mySelectionGrid[0].descripcion);
            if($scope.mySelectionGrid[0].tipo_operacion == 1 ){
              $scope.fData.tipo_operacion =  $scope.listaTipo[0];  
            }else{
              $scope.fData.tipo_operacion =  $scope.listaTipo[1]; 
            }
            $scope.fData.tipo.id =  $scope.mySelectionGrid[0].tipo_operacion          
          }else{
            alert('Seleccione una sola fila');
          }

          $scope.titleForm = 'Edición de operación';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            $scope.fDatos.later = $scope.fData.descripcion;
            if($scope.fDatos.later != $scope.fDatos.after){
              $scope.fData.Change = true;
            }
            operacionServices.sEditar($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.fData = {};
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              //$scope.fData = {};
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
      $modal.open({
        templateUrl: angular.patchURLCI+'operacion/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.tipo_operacion = $scope.listaTipo[0];
          $scope.titleForm = 'Registro de operación';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            operacionServices.sRegistrar($scope.fData).then(function (rpta) {
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
          operacionServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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

    $scope.btnSubOperaciones = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'operacion/ver_popup_subOperaciones',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fDataSO = {};
          $scope.idOperacion = {};
          $scope.operacionesSO = {};
          $scope.operacionesSO.editarSOBool = false;
          $scope.fDataSO.Change_des = false;
          $scope.fDataSO.Change_cod = false;
          //console.log($scope.mySelectionGrid);
          $scope.idOperacion = $scope.mySelectionGrid[0].id;  
          $scope.titleOperacion = $scope.mySelectionGrid[0].descripcion;
          console.log("SO",$scope.idOperacion);        

          $scope.titleForm = 'Mantenimiento de Sub Operaciones';
          var paginationOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.DESC,
            sortName: null
          };          

          $scope.gridOptionsSubOperaciones = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            //enableGridMenu: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'id', name: 'idsuboperacion', displayName: 'ID', minWidth: 90, width: '10%' , sort: { direction: uiGridConstants.DESC} },
              { field: 'descripcion', name: 'descripcion_sop', displayName: 'DESCRIPCIÓN' , width : '57%' },
              { field: 'codigo', name: 'codigo_plan', displayName: 'CODIGO' , width : '17%' },
              { field: 'accion', displayName: '', width: 60, cellTemplate:'<button type="button" title="ELIMINAR" class="btn btn-sm btn-danger center-block ml-xs" style="display:inline" ng-click="grid.appScope.btnAnularSO(row.entity)"> <i class="fa fa-trash"></i> </button><button type="button" title="EDITAR" style="display:inline" class="btn btn-sm btn-warning center-block ml-xs" ng-click="grid.appScope.btnEditSO(row.entity)"> <i class="fa fa-pencil"></i></button>'}
            ],
            onRegisterApi: function(gridApiSO) { 
              $scope.gridApi = gridApiSO;
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationOptions.sort = null;
                  paginationOptions.sortName = null;
                } else {
                  paginationOptions.sort = sortColumns[0].sort.direction;
                  paginationOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSideSO();
              });
              gridApiSO.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptions.pageNumber = newPage;
                paginationOptions.pageSize = pageSize;
                paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
                $scope.getPaginationServerSideSO();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationOptions.search = true;
                paginationOptions.searchColumn = { 
                  'idsuboperacion' : grid.columns[1].filters[0].term,
                  'descripcion_op' : grid.columns[2].filters[0].term,
                  'codigo_plan' : grid.columns[3].filters[0].term                  
                }
                $scope.getPaginationServerSideSO();
              });
            }
          };
          paginationOptions.sortName = $scope.gridOptionsSubOperaciones.columnDefs[0].name;
          $scope.getPaginationServerSideSO = function() {
            $scope.datosGrid = {
              paginate : paginationOptions,
              id : $scope.idOperacion
            };
            operacionServices.sListarSubOperaciones($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsSubOperaciones.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsSubOperaciones.data = rpta.datos;                             
            });
          };
          $scope.getPaginationServerSideSO();
          $scope.btnEditSO = function(row){
              $scope.fDataSO = angular.copy(row); 
              $scope.fDataSO.after_descripcion = angular.copy(row.descripcion); 
              $scope.fDataSO.after_codigo = angular.copy(row.codigo); 
              $scope.cambiarValores(true,'ui-editPanel-SO');             
          }
          $scope.cambiarValores = function(value, clase){
            $scope.operacionesSO.editarSOBool = value;           
            $scope.operacionesSO.classEditPanel = clase;           
          }
          $scope.cancelarSO = function (){
            $scope.fDataSO = {};
            $scope.cambiarValores(false,'');
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.grabarSO = function () { 
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  $scope.fDataSO.idoperacion = $scope.idOperacion;
                  operacionServices.sRegistrarSO($scope.fDataSO).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.fDataSO = {};
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    $scope.getPaginationServerSideSO();
                  });                  
                }
              });
          }
          $scope.grabareditSO = function(){
            $scope.fDataSO.later_descripcion = $scope.fDataSO.descripcion;
            $scope.fDataSO.later_codigo = $scope.fDataSO.codigo;
            if($scope.fDataSO.later_descripcion != $scope.fDataSO.after_descripcion){
              $scope.fDataSO.Change_des = true;
            }              
            if($scope.fDataSO.later_codigo != $scope.fDataSO.after_codigo){
              $scope.fDataSO.Change_cod = true;
            }              
            var pMensaje = '¿Realmente desea realizar la acción?';

            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                $scope.fDataSO.idoperacion = $scope.idOperacion;
                operacionServices.sEditarSO($scope.fDataSO).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    //$scope.fData = {};
                    $scope.fDataSO = {};
                    //$modalInstance.dismiss('cancel');
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  //$scope.fData = {};
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  //$scope.getPaginationServerSide();
                  $scope.getPaginationServerSideSO();
                  $scope.cambiarValores(false,'');
                });                  
              }
            });
          }
          $scope.btnAnularSO = function(row){
            $scope.fDataSO = angular.copy(row);
            var pMensaje = '¿Realmente desea realizar la acción?';

            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                operacionServices.sAnularSO($scope.fDataSO).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    //$scope.fData = {};

                    $scope.fDataSO = {};
                    //$modalInstance.dismiss('cancel');
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  //$scope.fData = {};
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  //$scope.getPaginationServerSide();
                  $scope.getPaginationServerSideSO();

                });                  
              }
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

    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva operacion',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar operacion',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular operacion',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar operacion',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      })
      .add ({ 
        combo: 's',
        description: 'Selección y Navegación',
        callback: function() {
          $scope.navegateToCell(0,0);
        }
      });
  }])
  .service("operacionServices",function($http, $q) {
    return({
        sListarOperaciones: sListarOperaciones,
        sListarOperacionCbo: sListarOperacionCbo,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sListarSubOperaciones : sListarSubOperaciones,
        sRegistrarSO : sRegistrarSO,
        sEditarSO : sEditarSO ,
        sAnularSO : sAnularSO
    });

    function sListarOperaciones(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/lista_operaciones", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarOperacionCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/lista_operaciones_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarSO (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/registrar_so", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarSO (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/editar_so", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularSO (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/anular_so", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }      
    function sListarSubOperaciones(datos){
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"operacion/lista_operaciones_so", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));      
    } 


  });