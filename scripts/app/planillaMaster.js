angular.module('theme.planillaMaster', ['theme.core.services'])
  .controller('planillaMasterController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'planillaMasterServices', 'empresaServices', 'conceptoPlanillaServices', 'categoriaConceptoPlanillaServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , planillaMasterServices
      , empresaServices
      , conceptoPlanillaServices
      , categoriaConceptoPlanillaServices
      ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null,
    };

    $scope.fBusqueda = {};

    // CARGAR LISTA CATEGORIA CONCEPTO PLANILLA
    categoriaConceptoPlanillaServices.sListarCategoriaConceptosCbo().then(function(rpta){
      $scope.listaCategoriaConceptos = rpta.datos;
      $scope.listaCategoriaConceptos.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
      $scope.categoria_concepto = $scope.listaCategoriaConceptos[0];
    });

    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
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
      multiSelect: false,
      columnDefs: [
        { field: 'id', name: 'idplanillamaster', displayName: 'ID', maxWidth: 100, minWidth: 100,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion_empresa', name: 'emp.descripcion', displayName: 'Empresa', maxWidth: 400, minWidth: 400 },
        { field: 'descripcion', name: 'descripcion_plm', displayName: 'Descripción' },        
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
            'idplanillamaster' : grid.columns[1].filters[0].term,
            'emp.descripcion' : grid.columns[2].filters[0].term,
            'descripcion_plm' : grid.columns[3].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        empresa: $scope.fBusqueda.empresa
      };
      planillaMasterServices.sListarPlanillasMaster($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };

    empresaServices.sListarEmpresasSoloAdminCbo().then(function(rpta){
      $scope.listaEmpresaAdmin = rpta.datos;
      $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'-- Todas --'});
      $scope.fBusqueda.empresa = $scope.listaEmpresaAdmin[0];
      $scope.getPaginationServerSide();
    });    
    
    /* MANTENIMIENTO */
      $scope.btnNuevo = function(size){
        $uibModal.open({
          templateUrl: angular.patchURLCI+'PlanillaMaster/ver_popup_planilla_master',
          size: size || ' ',
          backdrop: 'static',
          keyboard:false,
          controller: function ($scope, $modalInstance, getPaginationServerSide) {
            $scope.getPaginationServerSide = getPaginationServerSide;
            $scope.fData = {};
            $scope.titleForm = 'Registro de planilla master';

            empresaServices.sListarEmpresasSoloAdminCbo().then(function(rpta){
              $scope.listaEmpresaAdmin = rpta.datos;
              $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'-- Seleccionar --'});
              $scope.fData.empresa = $scope.listaEmpresaAdmin[0];
            });

            $scope.asignarDescripcion = function () {
              $scope.fData.descripcion = "PLANILLA " + $scope.fData.empresa.descripcion_corta;
              $scope.getPaginationServerSide();
            }

            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.fData = {};
              $scope.getPaginationServerSide();
            }

            $scope.aceptar = function () {             
              planillaMasterServices.sRegistrarPlanillasMaster($scope.fData).then(function (rpta) {
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';
                  $scope.cancel();
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
            }
          }, 
          resolve: {
            getPaginationServerSide: function() {
              return $scope.getPaginationServerSide;
            },
          }
        });
      } 

      $scope.btnEditar = function(size){
        $uibModal.open({
          templateUrl: angular.patchURLCI+'PlanillaMaster/ver_popup_planilla_master',
          size: size || ' ',
          backdrop: 'static',
          keyboard:false,
          controller: function ($scope, $modalInstance, getPaginationServerSide, mySelectionGrid) {
            $scope.getPaginationServerSide = getPaginationServerSide;
            $scope.fData = mySelectionGrid;
            $scope.titleForm = 'Edición de planilla master';
            $scope.typeEdit = true;

            empresaServices.sListarEmpresasSoloAdminCbo().then(function(rpta){
              $scope.listaEmpresaAdmin = rpta.datos;

               var objIndex = $scope.listaEmpresaAdmin.filter(function(obj) {
                return obj.id == $scope.fData.idempresa;
              }).shift(); 
              $scope.fData.empresa = objIndex;
            });

            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
              $scope.fData = {};
              $scope.getPaginationServerSide();
            }

            $scope.aceptar = function () { 
              planillaMasterServices.sEditarPlanillasMaster($scope.fData).then(function (rpta) {
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';                
                  $scope.cancel();
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
            }

          }, 
          resolve: {
            getPaginationServerSide: function() {
              return $scope.getPaginationServerSide;
            },
            mySelectionGrid: function() {
              return $scope.mySelectionGrid[0];
            },
          }
        });
      }

      $scope.btnAnular = function (mensaje) { 
        var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
        $bootbox.confirm(pMensaje, function(result) {
          if(result){
            planillaMasterServices.sAnular($scope.mySelectionGrid[0]).then(function (rpta) {
              if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationServerSide();
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        });
      }
    /* CONFIGURACION */
      $scope.btnConfigPlanilla = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'ConceptoPlanilla/ver_popup_concepto_planilla',
          size: size || 'xlg',
          backdrop: 'static',
          keyboard:false,
          scope:$scope,
          controller: function ($scope, $modalInstance, getPaginationServerSide) {
            $scope.getPaginationServerSide = getPaginationServerSide;
            $scope.fData = {};
            $scope.titleForm = 'Configuración de Planilla Master';
            $scope.planilla=$scope.mySelectionGrid[0];
            $scope.cargar_listas = function(item){
              $scope.getPaginationConceptosServerSideAdd();
              $scope.getPaginationConceptosServerSide();
            }

            /* DATA GRID CONCEPTOS */
              var paginationConceptosOptions = {
                pageNumber: 1,
                firstRow: 0,
                pageSize: 10,
                sort: uiGridConstants.ASC,
                sortName: null
              };
              $scope.gridOptionsConceptos = {
                paginationPageSizes: [10, 50, 100, 500, 1000],
                paginationPageSize: 10,
                useExternalPagination: true,
                useExternalSorting: true,
                enableGridMenu: false,
                enableRowSelection: false,
                enableSelectAll: false,
                enableFiltering: true,
                enableFullRowSelection: true,
                multiSelect: false,
                columnDefs: [
                  { field: 'codigo_plame', displayName: 'COD.', name: 'codigo_plame', maxWidth: 60,  sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
                  { field: 'id', displayName: 'ID', name: 'idconcepto', maxWidth: 60, enableCellEdit: false, visible:false },
                  { field: 'descripcion', name: 'descripcion_co', displayName: 'Descripción', enableCellEdit: false },
                  { field: 'accion', name:'accion', displayName: 'ACCION', width: 85, 
                    cellTemplate:'<div class="" style="text-align:center;">'+
                    '<button type="button" class="btn btn-sm btn-primary m-xs"'+
                    ' ng-click="grid.appScope.btnAgregarConceptos(row.entity)" title="AGREGAR">'+
                    ' <i class="ti ti-arrow-right"></i></button> </div>' , enableCellEdit: false, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false }
                ],
                onRegisterApi: function(gridApiConceptos) {
                  $scope.gridApiConceptos = gridApiConceptos;
                  $scope.gridApiConceptos.core.on.sortChanged($scope, function(grid, sortColumns) {
                    if (sortColumns.length == 0) {
                      paginationConceptosOptions.sort = null;
                      paginationConceptosOptions.sortName = null;
                    } else {
                      paginationConceptosOptions.sort = sortColumns[0].sort.direction;
                      paginationConceptosOptions.sortName = sortColumns[0].name;
                    }
                    $scope.getPaginationConceptosServerSide();
                  });
                  gridApiConceptos.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                    paginationConceptosOptions.pageNumber = newPage;
                    paginationConceptosOptions.pageSize = pageSize;
                    paginationConceptosOptions.firstRow = (paginationConceptosOptions.pageNumber - 1) * paginationConceptosOptions.pageSize;
                    $scope.getPaginationConceptosServerSide();
                  });
                  $scope.gridApiConceptos.core.on.filterChanged( $scope, function(grid, searchColumns) {
                    var grid = this.grid;
                    paginationConceptosOptions.search = true;
                    paginationConceptosOptions.searchColumn = {
                      'c.codigo_plame' : grid.columns[0].filters[0].term,
                      'c.idconcepto' : grid.columns[1].filters[0].term,
                      'c.descripcion_co' : grid.columns[2].filters[0].term,
                    }
                    $scope.getPaginationConceptosServerSide();
                  });
                }
              };

              paginationConceptosOptions.sortName = $scope.gridOptionsConceptos.columnDefs[0].name;
              $scope.getPaginationConceptosServerSide = function() { 
                
                $scope.datosGrid = {
                  paginate : paginationConceptosOptions,
                  datos : $scope.mySelectionGrid,
                  cat_concepto: $scope.categoria_concepto['id']
                };
                
                conceptoPlanillaServices.sListarConceptosNoAgregados($scope.datosGrid).then(function (rpta) {
                  $scope.gridOptionsConceptos.totalItems = rpta.paginate.totalRows;
                  $scope.gridOptionsConceptos.data = rpta.datos;
                });
              };

              $scope.getPaginationConceptosServerSide();

            /* DATA GRID ADD CONCEPTOS */
              var paginationConceptosOptionsAdd = {
                pageNumber: 1,
                firstRow: 0,
                pageSize: 10,
                sort: uiGridConstants.ASC,
                sortName: null
              };
              $scope.gridOptionsConceptosAdd = {
                paginationPageSizes: [10, 50, 100, 500, 1000],
                paginationPageSize: 10,
                useExternalPagination: true,
                useExternalSorting: true,
                enableGridMenu: false,
                enableRowSelection: false,
                enableSelectAll: false,
                enableFiltering: true,
                enableFullRowSelection: false,
                enableCellEditOnFocus: true,
                multiSelect: false,
                columnDefs: [
                  { field: 'codigo_plame', displayName: 'COD.', name: 'codigo_plame', maxWidth: 60,  sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
                  { field: 'idconcepto', displayName: 'ID', name: 'idconcepto', maxWidth: 60, enableCellEdit: false, visible:false },
                  { field: 'descripcion', name: 'descripcion_co', displayName: 'Descripción', enableCellEdit: false },
                  // { field: 'valor_referencial', name: 'valor_referencial', displayName: 'Valor Ref.', enableCellEdit: true, enableFiltering: false, 
                  //   enableSorting: false, enableColumnMenus: false, enableColumnMenu: false , cellClass:'ui-editCell text-center', maxWidth: 90}, 
                  { field: 'estado_bloq', type: 'object', name: 'estado_bloq', displayName: 'Estado', width: '12%', enableFiltering: false, enableSorting: false, 
                    enableColumnMenus: false, enableColumnMenu: false, cellClass:'text-center',
                    cellTemplate:'<div class="list-conceptos" tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.bloqueaDesbloquea(row)" >'+
                    '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="success"></switch></div>'
                  },
                  { field: 'accion', name:'accion', displayName: 'ACCION', width: 80, 
                    cellTemplate:'<div class="" style="text-align:center;"><button type="button"'+
                    ' class="btn btn-sm btn-danger m-xs" ng-click="grid.appScope.btnQuitarConceptos(row.entity)"'+
                    ' title="QUITAR"> <i class="fa fa-trash"></i></button></div>' , enableCellEdit: false, enableFiltering: false, 
                    enableSorting: false, enableColumnMenus: false, enableColumnMenu: false 
                  }
                ],
                onRegisterApi: function(gridApi) {
                  $scope.gridApi = gridApi;
                  gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                    rowEntity.column = colDef.field;
                    if(rowEntity.column == 'valor_referencial'){
                      if( !(rowEntity.valor_referencial >= 1) ){
                        var pTitle = 'Aviso!';
                        var pType = 'warning';
                        rowEntity.valor_referencial = oldValue;
                        pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                        return false;
                      }else{
                        conceptoPlanillaServices.sActualizarValorReferencial(rowEntity).then(function (rpta) {
                          if(rpta.flag == 1){
                            pTitle = 'OK!';
                            pType = 'success';
                          }else if(rpta.flag == 0){
                            var pTitle = 'Aviso!';
                            var pType = 'warning';
                          }else{
                            alert('Error inesperado');
                          }

                          $scope.getPaginationConceptosServerSideAdd();
                          $scope.getPaginationConceptosServerSide();
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                        }); 
                      }
                    }
                    $scope.$apply();
                  });
                  $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                    if (sortColumns.length == 0) {
                      paginationConceptosOptionsAdd.sort = null;
                      paginationConceptosOptionsAdd.sortName = null;
                    } else {
                      paginationConceptosOptionsAdd.sort = sortColumns[0].sort.direction;
                      paginationConceptosOptionsAdd.sortName = sortColumns[0].name;
                    }
                    $scope.getPaginationConceptosServerSideAdd();
                  });
                  gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                    paginationConceptosOptionsAdd.pageNumber = newPage;
                    paginationConceptosOptionsAdd.pageSize = pageSize;
                    paginationConceptosOptionsAdd.firstRow = (paginationConceptosOptionsAdd.pageNumber - 1) * paginationConceptosOptionsAdd.pageSize;
                    $scope.getPaginationConceptosServerSideAdd();
                  });
                  $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                    var grid = this.grid;
                    paginationConceptosOptionsAdd.search = true;
                    paginationConceptosOptionsAdd.searchColumn = {
                      'c.codigo_plame' : grid.columns[0].filters[0].term,
                      'c.idconcepto' : grid.columns[1].filters[0].term,
                      'c.descripcion_co' : grid.columns[2].filters[0].term,
                    }
                    
                    $scope.getPaginationConceptosServerSideAdd();
                  });
                }
              };

              paginationConceptosOptionsAdd.sortName = $scope.gridOptionsConceptos.columnDefs[0].name;
              $scope.getPaginationConceptosServerSideAdd = function() { 
                $scope.datosGrid = {
                  paginate : paginationConceptosOptionsAdd,
                  datos : $scope.mySelectionGrid,
                  cat_concepto: $scope.categoria_concepto['id']
                };
                
                conceptoPlanillaServices.sListarConceptosAgregados($scope.datosGrid).then(function (rpta) {
                  $scope.gridOptionsConceptosAdd.totalItems = rpta.paginate.totalRows;
                  $scope.gridOptionsConceptosAdd.data = rpta.datos;
                });
              };
              $scope.getPaginationConceptosServerSideAdd();
              $scope.bloqueaDesbloquea = function(row){
                //alert('desblokeo');             
                conceptoPlanillaServices.sBloqueaDesbloqueaConcepto(row.entity).then(function (rpta){
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    $scope.getPaginationConceptosServerSideAdd();
                    $scope.getPaginationConceptosServerSide();
                  }else if(rpta.flag == 0){
                    var pTitle = 'Aviso!';
                    var pType = 'warning';
                    $scope.getPaginationServerSide();
                  }else{
                    alert('Error inesperado');
                  }
                  //$scope.fData = {};
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });
              };

            /* BOTONES */
              $scope.btnAgregarConceptos = function (rolGroupId,mensaje) {
                var datos={
                  idplanillamaster: $scope.planilla['id'],
                  idconcepto: rolGroupId['id']
                };
                conceptoPlanillaServices.sAgregarConcepto(datos).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    $scope.getPaginationConceptosServerSideAdd();
                    $scope.getPaginationConceptosServerSide();
                  }else if(rpta.flag == 0){
                    var pTitle = 'Aviso!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
      
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  
                });
              }
              $scope.btnQuitarConceptos = function (rolGroupId,mensaje) {
                var pMensaje = '¿Realmente desea anular el concepto?';
                $bootbox.confirm(pMensaje, function(result) {
                  if(result){
                    var datos={
                      idplanillamaster: $scope.planilla['id'],
                      idconcepto: rolGroupId['idconcepto'],
                      id: rolGroupId['id']
                    };

                    conceptoPlanillaServices.sQuitarConcepto(datos).then(function (rpta) { 
                      if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success';
                        $scope.getPaginationConceptosServerSideAdd();
                        $scope.getPaginationConceptosServerSide();
                      }else if(rpta.flag == 0){
                        var pTitle = 'Aviso!';
                        var pType = 'warning';
                      }else{
                        alert('Error inesperado');
                      }

                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    });
                  }
                });  
              }
              
              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }
          },
          resolve: {
            getPaginationServerSide: function() {
              return $scope.getPaginationServerSide;
            }
          }
        });
      }
    
  }])
  .service("planillaMasterServices",function($http, $q) {
    return({
        sListarPlanillasMaster: sListarPlanillasMaster,
        sRegistrarPlanillasMaster: sRegistrarPlanillasMaster,
        sEditarPlanillasMaster: sEditarPlanillasMaster,
        sAnular:sAnular,
        sListarPlanillasMasterCbo: sListarPlanillasMasterCbo,
       
    });
    function sListarPlanillasMaster(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"PlanillaMaster/lista_planillas_master", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }      

    function sRegistrarPlanillasMaster(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"PlanillaMaster/registrar", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }  

    function sEditarPlanillasMaster(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"PlanillaMaster/editar", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    
    function sAnular(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"PlanillaMaster/anular", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }     

    function sListarPlanillasMasterCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"PlanillaMaster/lista_planillas_master_cbo", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }  
 
  });
  