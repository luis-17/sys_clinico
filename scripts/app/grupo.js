  angular.module('theme.grupo', ['theme.core.services'])
  .controller('grupoController', ['$scope', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'grupoServices',  
    function($scope, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, grupoServices ){
    'use strict';
    $scope.fDataRol = {};
    
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
     
    };
    $scope.getTemplateRoles = function (value) {
      return value.iconoRol + value.rol;
    };
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null
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
      //rowHeight: 210,
      multiSelect: true,
      columnDefs: [ 
        { field: 'id', name: 'g.idgroup', displayName: 'COD.', maxWidth: 100,  sort: { direction: uiGridConstants.ASC} },
        { field: 'nombre', name: 'g.name', displayName: 'GRUPO', maxWidth: 260 },
        { field: 'descripcion', name: 'g.description', displayName: 'DESCRIPCIÓN', enableFiltering:false },
        //{ field: 'roles', name: 'roles', type: 'object', displayName: 'Roles del grupo', minWidth: 700, enableFiltering: false, enableSorting: false, 
        //  cellTemplate: '<span style="box-shadow: 1px 1px 0 black;" class="label label-info mr-xs ml-sm mt-xs" ng-repeat="(key, value) in COL_FIELD">'+
        //    ' <i class="ti {{ value.iconoRol }}"></i> {{ value.rol }} <a class="btn-xs text-gray" ng-click="grid.appScope.quitarRolDeGrupo(value)">X</a> </span>  ' 
        //} 
        { field: 'notificacion_pa', type: 'object', name: 'notificacion_pa', displayName: 'Notificación P.A.', width: '8%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.editarNotificacionPa(row.entity)" >'+
            '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="danger" ></switch>'+ 
            '</div>'
        }, 
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
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = { 
            'g.idgroup' : grid.columns[1].filters[0].term,
            'g.name' : grid.columns[2].filters[0].term,

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
      grupoServices.sListarGrupos($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
         
        angular.forEach($scope.gridOptions.data, function (index,val) {
          
        });
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'grupo/ver_popup_formulario',
        size: size || '',
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de grupo';
          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            grupoServices.sEditar($scope.fData).then(function (rpta) { 
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
        templateUrl: angular.patchURLCI+'grupo/ver_popup_formulario',
        size: size || '',
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de grupo';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            grupoServices.sRegistrar($scope.fData).then(function (rpta) {
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
          grupoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
    $scope.btnAgregarRol = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'grupo/ver_popup_agregar_rol',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fDataAdd = {};
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fDataAdd.groupId = $scope.mySelectionGrid[0].id;
          }else{
            alert('Seleccione una sola fila'); return false; 
          }
          $scope.fDataRol = {};
          var rptaAdd ={};
          $scope.listarModulos = function () { 
            grupoServices.sListarModulosCbo().then(function (rpta) {  
              angular.copy(rpta, rptaAdd);
              $scope.listaModulo = rpta.datos;
              $scope.fDataRol.idmodulo = $scope.listaModulo[0].idmodulo;

              $scope.listaModuloAdd = rptaAdd.datos;
              $scope.listaModuloAdd.splice(0,0,{ idmodulo : '0', descripcion_mod:'-- Todos --'});
              $scope.fDataRol.idmoduloAdd = $scope.listaModuloAdd[0].idmodulo;

            });
          }
          $scope.listarModulos();
          /* DATA GRID */ 
          var paginationRolOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null
          };
          $scope.mySelectionRolGrid = [];
          $scope.gridOptionsRoles = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            //useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              // { field: 'id', displayName: 'ID', name: 'idrol', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
              { field: 'orden', name: 'orden', displayName: 'Item', maxWidth: 60,  sort: { direction: uiGridConstants.ASC} },
              { field: 'icono', displayName: 'Icono', name: 'icono_rol', maxWidth: 80, enableFiltering: false, enableSorting: false, cellTemplate:'<div class="text-center"><i style="font-size:18px;" class="{{ COL_FIELD }} " ></i></div>' }, 
              // { field: 'descripcion', displayName: 'Descripción', name: 'descripcion_rol' } 
              { field: 'rol', name: 'rol', displayName: 'Rol' },
              { field: 'subrol', name: 'subrol', displayName: 'Sub Rol' },
              { field: 'accion', name:'accion', displayName: 'ACCION', width: 85, 
                cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-primary m-xs" ng-click="grid.appScope.btnAgregarRolesAGrupo(row.entity)" title="AGREGAR"> <i class="ti ti-arrow-right"></i></button>'+ 
                '</div>' , enableCellEdit: false
              }

            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionRolGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionRolGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationRolOptions.sort = null;
                  paginationRolOptions.sortName = null;
                } else {
                  paginationRolOptions.sort = sortColumns[0].sort.direction;
                  paginationRolOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationRolesServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationRolOptions.pageNumber = newPage;
                paginationRolOptions.pageSize = pageSize;
                paginationRolOptions.firstRow = (paginationRolOptions.pageNumber - 1) * paginationRolOptions.pageSize;
                $scope.getPaginationRolesServerSide();
              });
            }
          };
          paginationRolOptions.sortName = $scope.gridOptionsRoles.columnDefs[0].name;
          $scope.getPaginationRolesServerSide = function(idmodulo) { 
            $scope.datosGrid = {
              paginate : paginationRolOptions,
              //datos : $scope.mySelectionGrid[0],
              id : $scope.mySelectionGrid[0].id,
              idgroup : $scope.mySelectionGrid[0].idgroup,
              modulo : idmodulo
            };
            grupoServices.sListarRolesNoAgregadosAGrupo($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsRoles.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsRoles.data = rpta.datos;
            });
            $scope.mySelectionRolGrid = [];
          };
          $scope.getPaginationRolesServerSide(1);
          $scope.titleFormAdd = 'Agregar roles';
          /*--------- AGREGAR ROLES ---------------*/
          $scope.mySelectionRolGridAdd = [];
          var paginationRolOptionsAdd = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null
          };
          $scope.gridOptionsAddRoles = { 
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'orden', name: 'orden', displayName: 'Item', maxWidth: 60,  sort: { direction: uiGridConstants.ASC} },
              { field: 'icono', displayName: 'Icono', name: 'icono_rol', maxWidth: 80, enableFiltering: false, enableSorting: false, cellTemplate:'<div class="text-center"><i style="font-size:18px;" class="{{ COL_FIELD }} " ></i></div>' }, 
              { field: 'rol', name: 'rol', displayName: 'Rol' },
              { field: 'subrol', name: 'subrol', displayName: 'Sub Rol' },
              { field: 'accion', name:'accion', displayName: 'ACCION', width: 85, 
              cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-danger m-xs" ng-click="grid.appScope.quitarRolDeGrupo(row.entity)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>' , enableCellEdit: false
              }
            ],
            onRegisterApi: function(gridApi1) {
              $scope.gridApi1 = gridApi1;
              gridApi1.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionRolGridAdd = gridApi1.selection.getSelectedRows();
              });
              gridApi1.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionRolGridAdd = gridApi1.selection.getSelectedRows();
              });
            }
          }; 

          $scope.getPaginationRolesAddServerSide = function(idmodulo) { 
            $scope.datosGrid = {
              paginate : paginationRolOptionsAdd,
              //datos : $scope.mySelectionGrid[0],
              id : $scope.mySelectionGrid[0].id,
              idgroup : $scope.mySelectionGrid[0].idgroup,
              modulo : idmodulo
            };

            grupoServices.sListarRolesAgregadosAGrupo($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsAddRoles.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsAddRoles.data = rpta.datos;
            });
            $scope.mySelectionRolGridAdd = [];
          };
          $scope.getPaginationRolesAddServerSide(0);
          /*------------------------------------------*/

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataAdd = {};
          }

          $scope.btnAgregarRolesAGrupo = function (rolGroupId,mensaje) {
            rolGroupId['groupId'] = $scope.mySelectionGrid[0].idgroup;
            //var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              grupoServices.sAgregarRol(rolGroupId).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationRolesAddServerSide($scope.fDataRol.idmoduloAdd);
                  $scope.getPaginationRolesServerSide($scope.fDataRol.idmodulo);
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                $scope.getPaginationServerSide();
              });

          }
          $scope.quitarRolDeGrupo = function (rolGroupId,mensaje) {
            //var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              grupoServices.sQuitarRolDeGrupo(rolGroupId).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationRolesAddServerSide($scope.fDataRol.idmoduloAdd);
                  $scope.getPaginationRolesServerSide($scope.fDataRol.idmodulo);
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
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

    $scope.editarNotificacionPa = function (row){
      //console.log(row);
      var value;
      if(row.notificacion_pa.boolBloqueo){
        value = 2;
      }else{
        value = 1;
      }

      var datos = row;
      datos.value = value;
      grupoServices.sUpdatePermiteNotificacionPa(datos).then(function (rpta) {
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

  }])
  .service("grupoServices",function($http, $q) {
    return({
        sListarGrupos: sListarGrupos,
        sListarGruposCbo: sListarGruposCbo,
        sListarGruposNotificaciones: sListarGruposNotificaciones,
        sListarRolesNoAgregadosAGrupo: sListarRolesNoAgregadosAGrupo,
        sListarRolesAgregadosAGrupo : sListarRolesAgregadosAGrupo,
        sAgregarRol: sAgregarRol,
        sQuitarRolDeGrupo: sQuitarRolDeGrupo,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sListarModulosCbo : sListarModulosCbo,
        sUpdatePermiteNotificacionPa: sUpdatePermiteNotificacionPa
    });

    function sListarGrupos(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/lista_grupos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarGruposCbo () {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/lista_grupos_cbo"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarGruposNotificaciones (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/lista_grupos_notificaciones",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarModulosCbo () {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/lista_modulos_cbo"
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarRolesNoAgregadosAGrupo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/lista_roles_no_agregados_al_grupo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarRolesAgregadosAGrupo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/lista_roles_agregados_al_grupo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAgregarRol (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/agregar_rol", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarRolDeGrupo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/quitar_rol_de_grupo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sUpdatePermiteNotificacionPa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"grupo/update_permite_notificacion_pa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });