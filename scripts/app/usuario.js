angular.module('theme.usuario', ['theme.core.services','isteven-multi-select'])
  .controller('usuarioController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'usuarioServices', 
    'grupoServices',
    'sedeServices',
    'empresaAdminServices',
    'reporteCentralizadoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
        usuarioServices,
        grupoServices,
        sedeServices,
        empresaAdminServices,
        reporteCentralizadoServices
      ){ 
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'usuario';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
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
        { field: 'id', name: 'idusers', displayName: 'ID', minWidth: 50, width:'10%', sort: { direction: uiGridConstants.ASC} },
        { field: 'usuario', name: 'username', displayName: 'USERNAME', minWidth:80, width: '30%' },
        { field: 'empleado', name: 'empleado', displayName: 'EMPLEADO', minWidth: 200 }, 
        { field: 'email', name: 'email', displayName: 'E-MAIL', width: 220 },
        { field: 'grupo', name: 'name', displayName: 'GRUPO', width: 150 }, 
        { field: 'estado', type: 'object', name: 'estado_usuario', displayName: 'Estado', enableFiltering: false, enableSorting: false, width: 250, cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
            'u.idusers' : grid.columns[1].filters[0].term,
            'username' : grid.columns[2].filters[0].term,
            "CONCAT(em.nombres,' ',em.apellido_paterno,' ',em.apellido_materno)" : grid.columns[3].filters[0].term,
            'email' : grid.columns[4].filters[0].term,
            'name' : grid.columns[5].filters[0].term,
            // 'ea.razon_social' : grid.columns[6].filters[0].term
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
      usuarioServices.sListarUsuarios($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        // console.log('filas: '+rpta.paginate.totalRows);
        $scope.gridOptions.data = rpta.datos;
         
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function () {
      $modal.open({
        templateUrl: angular.patchURLCI+'usuario/ver_popup_formulario',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fDataUsuario = {};
          $scope.userTemporal = {};
          $scope.userTemporal.empresa = {};
          $scope.boolForm = 'edit'; 
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fDataUsuario = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de usuario';

          // ******* LISTA DE GRUPOS ******* 
          grupoServices.sListarGruposCbo().then(function (rpta) {
            $scope.listaGrupos = rpta.datos;
            $scope.fDataUsuario.groupId = $scope.mySelectionGrid[0].groupId;
            $scope.fDataUsuario.vista_sede_empresa = $scope.mySelectionGrid[0].vista_sede_empresa;
            $scope.cambiarVistaSedeEmpresa(); 
            $scope.logicaDeSedes(); 
          });
          
          $scope.cambiarVistaSedeEmpresa = function() { 
            // console.log();
            console.log($scope.fDataUsuario.vista_sede_empresa,'$scope.fDataUsuario.vista_sede_empresa'); 
            angular.forEach($scope.listaGrupos,function(row,key) { 
              // console.log(row.id,);
              if( row.id === $scope.fDataUsuario.groupId ){ 
                console.log(row.id === $scope.fDataUsuario.groupId,'row.id === $scope.fDataUsuario.groupId');
                $scope.fDataUsuario.vista_sede_empresa = angular.copy(row.vista_sede_empresa); 
                // if( $scope.fDataUsuario.vista_sede_empresa == 1 ){ 
                //   $scope.userTemporal.empresa.descripcion = '-'; 
                // }
                if( !($scope.fDataUsuario.vista_sede_empresa == 1) ){ 
                  $scope.gridOptionsEmpresaSede.columnDefs.splice(0, 0, { 
                    field: 'empresa', 
                    name: 'empresa', 
                    displayName: 'EMPRESA', 
                    width: 200, 
                    enableCellEdit: false, 
                    enableSorting: false 
                  }); 
                }else{
                  if( $scope.gridOptionsEmpresaSede.columnDefs[0].field == 'empresa' ){ 
                    $scope.gridOptionsEmpresaSede.columnDefs.splice(0, 1);
                  }
                }
                $scope.logicaDeSedes();
                console.log('salí');
                // return;
              }
            });
          }
          // ******* LISTA SOLO SEDES ******* 
          $scope.cargarSoloSedes = function(){ 
            sedeServices.sListarSedeCbo().then(function (rpta) {
              $scope.listaSede = rpta.datos;
              $scope.userTemporal.sede = $scope.listaSede[0];
            });  
          };

          $scope.logicaDeSedes = function() {
            /* LOGICA DE SEDES */ 
            $scope.fDataUsuario.siEmpresa = true;
            $scope.fDataUsuario.siSedeDeEmpresa = true;
            $scope.fDataUsuario.siSoloSede = false;
            //if( $scope.fSessionCI.key_group == 'key_rrhh' || $scope.fSessionCI.key_group == 'key_rrhh_asistente' || $scope.fSessionCI.key_group == 'key_gerencia' ){ 
            if( $scope.fDataUsuario.vista_sede_empresa == 1 ){ 
              //console.log('entréee');
              $scope.fDataUsuario.siEmpresa = false;
              $scope.fDataUsuario.siSedeDeEmpresa = false;
              $scope.fDataUsuario.siSoloSede = true;
              $scope.cargarSoloSedes();
            }else{
              // ******* LISTA DE EMPRESAS ADMIN ******* 
              empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) {
                $scope.listaEmpresaAdmin = rpta.datos;
                $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'--Seleccione Empresa--'});
                $scope.userTemporal.empresa = $scope.listaEmpresaAdmin[0];
                $scope.cargarSedes($scope.userTemporal.empresa);
              });
              // ******* LISTA DE SEDES *******
              $scope.cargarSedes = function(empresaadmin){
                if(empresaadmin.id == '0'){
                  $scope.listaSede = [];
                  $scope.listaSede.push({ id : '0', descripcion:'--Primero seleccione Empresa--'});
                  $scope.userTemporal.sede = $scope.listaSede[0];
                }else{
                  sedeServices.sListarSedePorEmpresaCbo(empresaadmin).then(function (rpta) {
                    $scope.listaSede = rpta.datos;
                    //$scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
                    $scope.userTemporal.sede = $scope.listaSede[0];
                  });  
                }
              };
            }
          }
          
          /* GRILLA EMPRESA  - SEDE  btnEditar */
          $scope.estadoOptions = [
            { id: 1, estado: 'HABILITADO' },
            { id: 2, estado: 'DESHABILITADO' }
          ];

          $scope.gridOptionsEmpresaSede = {
            paginationPageSizes: [10, 50, 100],
            minRowsToShow: 9,
            paginationPageSize: 50,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            // enableRowSelection: true,
            // enableSelectAll: true,
            enableFiltering: false,
            // enableFullRowSelection: true,
            // multiSelect: true,
            data: null,
            rowHeight: 30,
            columnDefs: [
              //{ field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: 200, enableCellEdit: false, enableSorting: false },
              { field: 'sede', name: 'sede', displayName: 'SEDE', width: 160, enableCellEdit: false, enableSorting: false },
              { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', enableFiltering: false, enableSorting: false , width: 130,                
                cellFilter: 'mapEstado', enableCellEdit: true,cellClass:'ui-editCell', editableCellTemplate: 'ui-grid/dropdownEditor',
                editDropdownValueLabel: 'estado', editDropdownOptionsArray: $scope.estadoOptions,
                cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 5px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
              },
              
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, enableSorting: false,  
                cellTemplate:'<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>'
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;              
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){                
                //console.log(rowEntity, colDef, newValue, oldValue); 
                rowEntity.column = colDef.field;
                rowEntity.anteriorValor = oldValue;
                if(rowEntity.anteriorValor.bool == 1){ 
                  rowEntity.estado = {"bool":2,clase:"label-default", string:"DESHABILITADO" };
                }

                if(rowEntity.anteriorValor.bool == 2){
                  rowEntity.estado = {"bool":1,clase:"label-success", string:"HABILITADO" };
                }

                //console.log(rowEntity); 
                $scope.$apply();
              });
            }
          };
          $scope.getPaginationServerSideSede = function () {
            var arrParams = {
              //paginate : paginationOptionsSede,
              datos: $scope.mySelectionGrid[0]
            };
            empresaAdminServices.sListarSedeEmpresaAdminUsuario(arrParams).then(function (rpta) {
              //$scope.gridOptionsEmpresaSede.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsEmpresaSede.data = rpta.datos;
            });
          }
          $scope.getPaginationServerSideSede();
          $scope.agregarSedeACesta = function (){
            var sedeNew = true;
            angular.forEach($scope.gridOptionsEmpresaSede.data, function(value, key) { 
              if( value.idsedeempresaadmin == $scope.userTemporal.sede.id ){ 
                sedeNew = false;
              }
            });
            if( !sedeNew ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La sede ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              return false;
            }
            if( $scope.userTemporal.sede.id == '0' ){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Debe seleccionar una Empresa / Sede', type: 'warning', delay: 2000 });
              return false;
            }

            var arrTemporal = {
              'empresa': $scope.userTemporal.empresa.descripcion || '-',
              'sede': $scope.userTemporal.sede.descripcion,
              'idsede': $scope.userTemporal.sede.idsede,
              'idsedeempresaadmin': $scope.userTemporal.sede.id || '1',
              'es_temporal': true
            };
            // console.log('array ', arrTemporal);
            $scope.gridOptionsEmpresaSede.data.push(arrTemporal); 
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            // var index = $scope.gridOptionsEmpresaSede.data.indexOf(row.entity); 
            // $scope.gridOptionsEmpresaSede.data.splice(index,1); 
            // return;
            if( row.entity.es_temporal && row.entity.es_temporal === true ){
              var index = $scope.gridOptionsEmpresaSede.data.indexOf(row.entity); 
              $scope.gridOptionsEmpresaSede.data.splice(index,1); 
              return;
            }
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) { 
              if(result){
                // console.log(row.entity.idusersporsede);
                usuarioServices.sQuitarSedeDeUsuario(row.entity).then(function (rpta) {
                  if(rpta.flag == 1){
                    var pTitle = 'OK!';
                    var pType = 'success';
                    $scope.getPaginationServerSideSede();
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
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataUsuario = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            var paramDatos = { 
              'dataUsuario' : $scope.fDataUsuario,
              'sedesEmpresa' : $scope.gridOptionsEmpresaSede.data
            };
             
            usuarioServices.sEditar(paramDatos).then(function (rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                
              }else{
                alert('Error inesperado');
              }
              $scope.getPaginationServerSide();
              $scope.fDataUsuario = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });

          }
          //console.log($scope.mySelectionGrid); agregarSedeACesta
        }
      });
    }
    $scope.btnNuevo = function () {
      $modal.open({
        templateUrl: angular.patchURLCI+'usuario/ver_popup_formulario',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide,listarGrupos) { 
          $scope.fDataUsuario = {};
          $scope.userTemporal = {};
          $scope.userTemporal.empresa = {};
          $scope.userTemporal.sede = {};
          $scope.boolForm = 'reg';
          $scope.titleForm = 'Registro de usuario';
          $scope.getPaginationServerSide = getPaginationServerSide;
          // ******* LISTA DE GRUPOS *******
          grupoServices.sListarGruposCbo().then(function (rpta) {
            $scope.listaGrupos = rpta.datos;
            $scope.listaGrupos.splice(0,0,{ id : '', descripcion:'--Seleccione Grupo--'});
            $scope.fDataUsuario.groupId = $scope.listaGrupos[0].id; 
            //$scope.cambiarVistaSedeEmpresa();
          });

          // ******* LISTA SOLO SEDES ******* 
          $scope.cargarSoloSedes = function(){ 
            sedeServices.sListarSedeCbo().then(function (rpta) {
              $scope.listaSede = rpta.datos;
              $scope.userTemporal.sede = $scope.listaSede[0];
            });  
          };
          $scope.logicaDeSedes = function() { 
            /* LOGICA DE SEDES */ 
            $scope.fDataUsuario.siEmpresa = true;
            $scope.fDataUsuario.siSedeDeEmpresa = true;
            $scope.fDataUsuario.siSoloSede = false;
            // if( $scope.fSessionCI.key_group == 'key_rrhh' || $scope.fSessionCI.key_group == 'key_rrhh_asistente' || $scope.fSessionCI.key_group == 'key_gerencia' ){ 
            if( $scope.fDataUsuario.vista_sede_empresa == 1 ){ 
              //console.log('entréee');
              $scope.fDataUsuario.siEmpresa = false;
              $scope.fDataUsuario.siSedeDeEmpresa = false;
              $scope.fDataUsuario.siSoloSede = true;
              $scope.cargarSoloSedes();
            }else{ 
              // ******* LISTA DE EMPRESAS ADMIN ******* 
              empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) {
                $scope.listaEmpresaAdmin = rpta.datos;
                $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'--Seleccione Empresa--'});
                $scope.userTemporal.empresa = $scope.listaEmpresaAdmin[0];
                $scope.cargarSedes($scope.userTemporal.empresa);
              });
              // ******* LISTA DE SEDES *******
              $scope.cargarSedes = function(empresaadmin){
                if(empresaadmin.id == '0'){
                  $scope.listaSede = [];
                  $scope.listaSede.push({ id : '0', descripcion:'--Primero seleccione Empresa--'});
                  $scope.userTemporal.sede = $scope.listaSede[0];
                }else{
                  sedeServices.sListarSedePorEmpresaCbo(empresaadmin).then(function (rpta) {
                    $scope.listaSede = rpta.datos;
                    //$scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
                    $scope.userTemporal.sede = $scope.listaSede[0];
                  });  
                }
              };
            }
          }
          $scope.logicaDeSedes();

          /* GRILLA EMPRESA - SEDE a.uiGrid */ 
          $scope.gridOptionsEmpresaSede = { 
            paginationPageSizes: [10, 50, 100],
            minRowsToShow: 9,
            paginationPageSize: 50,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            // enableRowSelection: true, gridOptionsEmpresaSede
            // enableSelectAll: true,
            enableFiltering: false,
            // enableFullRowSelection: true,
            // multiSelect: true,
            data: null,
            rowHeight: 30,
            columnDefs: [
              //{ field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: 200, enableCellEdit: false, enableSorting: false },
              { field: 'sede', name: 'sede', displayName: 'SEDE', width: 200, enableCellEdit: false, enableSorting: false },
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, enableSorting: false,
                cellTemplate:'<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>'
              }
            ]
          };
          // if( $scope.fDataUsuario.vista_sede_empresa === 1 ){ 
          //   $scope.gridOptionsEmpresaSede.columnDefs.splice(0, 0, { 
          //     field: 'empresa', 
          //     name: 'empresa', 
          //     displayName: 'EMPRESA', 
          //     width: 200, 
          //     enableCellEdit: false, 
          //     enableSorting: false 
          //   });
          // }
          $scope.agregarSedeACesta = function (){ 
            // console.log($scope.userTemporal,'$scope.userTemporal');
            if( !($scope.fDataUsuario.vista_sede_empresa) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Primero se tiene que seleccionar un grupo.', type: 'warning', delay: 2000 });
              return;
            }
            if( $scope.fDataUsuario.vista_sede_empresa === 1 ){ 
              $scope.userTemporal.empresa.descripcion = '-'; 
            }
            var sedeNew = true;
            angular.forEach($scope.gridOptionsEmpresaSede.data, function(value, key) { 
              if( value.idsedeempresaadmin == $scope.userTemporal.sede.id ){ 
                sedeNew = false;
              }
            });
            if( !sedeNew ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La sede ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
              return false;
            }
            if( $scope.userTemporal.sede.id == '0' ){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Debe seleccionar una Empresa / Sede', type: 'warning', delay: 2000 });
              return false;
            }
            var arrTemporal = {
              'empresa': $scope.userTemporal.empresa.descripcion || '-',
              'sede': $scope.userTemporal.sede.descripcion,
              'idsede': $scope.userTemporal.sede.idsede,
              'idsedeempresaadmin': $scope.userTemporal.sede.id || '1',
              'es_temporal': true
            }; 
            $scope.gridOptionsEmpresaSede.data.push(arrTemporal); 
            
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            var index = $scope.gridOptionsEmpresaSede.data.indexOf(row.entity); 
            $scope.gridOptionsEmpresaSede.data.splice(index,1); 
            return;
          }

          $scope.cambiarVistaSedeEmpresa = function() { 
            // console.log();
            // console.log($scope.fDataUsuario.vista_sede_empresa,'$scope.fDataUsuario.vista_sede_empresa'); 
            angular.forEach($scope.listaGrupos,function(row,key) {
              if( row.id === $scope.fDataUsuario.groupId ){ 
                console.log('entré');
                $scope.fDataUsuario.vista_sede_empresa = angular.copy(row.vista_sede_empresa); 
                // if( $scope.fDataUsuario.vista_sede_empresa == 1 ){ 
                //   $scope.userTemporal.empresa.descripcion = '-'; 
                // }
                if( !($scope.fDataUsuario.vista_sede_empresa == 1) ){ 
                  $scope.gridOptionsEmpresaSede.columnDefs.splice(0, 0, { 
                    field: 'empresa', 
                    name: 'empresa', 
                    displayName: 'EMPRESA', 
                    width: 200, 
                    enableCellEdit: false, 
                    enableSorting: false 
                  }); 
                }else{
                  if( $scope.gridOptionsEmpresaSede.columnDefs[0].field == 'empresa' ){ 
                    $scope.gridOptionsEmpresaSede.columnDefs.splice(0, 1);
                  }
                }
                $scope.logicaDeSedes();
                console.log('salí');
                //return;
              }
            });
          }
          $scope.cambiarVistaSedeEmpresa();
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            //console.log('scope ', $scope.fDataUsuario);
            var paramDatos = {
              'dataUsuario' : $scope.fDataUsuario,
              'sedesEmpresa' : $scope.gridOptionsEmpresaSede.data
            }
            usuarioServices.sRegistrar(paramDatos).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }, 
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          },
          listarGrupos : function () {
            return $scope.listarGrupos;
          }
        }
      });
    }
    $scope.btnDeshabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          usuarioServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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
    $scope.btnHabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          usuarioServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
    // $scope.quitarSedeDeUsuario = function (id,mensaje) {
    //   var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
    //   $bootbox.confirm(pMensaje, function(result) {
    //     if(result){
    //       usuarioServices.sQuitarSedeDeUsuario(id).then(function (rpta) { 
    //         if(rpta.flag == 1){
    //           pTitle = 'OK!';
    //           pType = 'success';
    //         }else if(rpta.flag == 0){
    //           var pTitle = 'Error!';
    //           var pType = 'danger';
    //         }else{
    //           alert('Error inesperado');
    //         }
    //         pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
    //         $scope.getPaginationServerSide();
    //       });
    //     }
    //   });
    // }
    $scope.btnCambiarPassword = function (size){
      $modal.open({
        templateUrl: angular.patchURLCI+'usuario/ver_popup_password',
        size: size || 'sm',
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fDataUsuario = {};
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fDataUsuario = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Cambiar Contraseña';
          $scope.aceptar = function (){
            if($scope.fDataUsuario.claveConfirmar != $scope.fDataUsuario.claveNueva){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
              var pMessage = 'Las contraseñas no son iguales';
              $scope.fDataUsuario.claveNueva = null;
              $scope.fDataUsuario.claveConfirmar = null;
              setTimeout(function() {
                $('#nuevoPass').focus();
              }, 500);
              pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 1000 });
              return;
            }else{
              usuarioServices.sverificaPassword($scope.fDataUsuario).then(function (rpta) {
                // console.log('Rpta: ',rpta);
                if(rpta.flag == 1){
                  var pTitle = 'Ok!';
                  var pType = 'success';
                  $modalInstance.dismiss('cancel');
                  $scope.getPaginationServerSide();
                }
                else if(rpta.flag == 2){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  $scope.fDataUsuario.clave = null;
                  setTimeout(function() {
                    $('#clave').focus();
                  }, 500);
                }
                else if(rpta.flag == 0){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  
                }else{
                  alert('Error inesperado');
                }
                
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              });
            }
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataUsuario = {};
            $scope.getPaginationServerSide();
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
    /* COMBOS */
    $scope.listarGrupos = function (callback) {
      var pCallback = callback || function () { }
    }
    

    /* ADMINISTRAR PERMISOS PARA REPORTES POR USUARIO */ 
    $scope.btnAdministrarReportes = function () {
      $modal.open({ 
        templateUrl: angular.patchURLCI+'Usuario/ver_popup_administracion_reportes',
        size: 'xlg',
        scope: $scope,
        controller: function ($scope,$modalInstance) { 
          $scope.fDataUsuario = {}; 
          $scope.titleFormAR = 'Administración de Reportes.'; 

          /* GRILLA DE REPORTES QUE AÚN NO HAN SIDO AGREGADOS */ 
          var paginationReportesOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 500,
            sort: uiGridConstants.DESC,
            sortName: null
          };
          
          $scope.gridOptionsReportes = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 500,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            //enableSelectAll: true,
            //enableFiltering: false,
            //enableFullRowSelection: true,
            //multiSelect: false,
            columnDefs: [
              { field: 'id', displayName: 'ID', name: 'idreporte', width: 50,  sort: { direction: uiGridConstants.DESC}, visible:false },
              { field: 'categoria', name: 'descripcion_trp', displayName: 'CATEGORIA', width: 166 },
              { field: 'abreviatura', name: 'abreviatura_rp', displayName: 'ABREVIATURA', visible:false, width: 80 },
              { field: 'nombre', name: 'nombre_rp', displayName: 'REPORTE', width: 320 },
              { field: 'accion', name:'accion', displayName: '', width: 50, 
                cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-primary m-xs" ng-click="grid.appScope.btnAgregarReporteAUsuario(row.entity)" title="AGREGAR"> <i class="ti ti-arrow-right"></i></button>'+ 
                '</div>'
              }
            ],
            onRegisterApi: function(gridApiReporte) {
              $scope.gridApiReporte = gridApiReporte;
              $scope.gridApiReporte.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationReportesOptions.sort = null;
                  paginationReportesOptions.sortName = null;
                } else {
                  paginationReportesOptions.sort = sortColumns[0].sort.direction;
                  paginationReportesOptions.sortName = sortColumns[0].name;
                }
                console.log('sortChanged');
                $scope.getPaginationReportesServerSide();
              });
              gridApiReporte.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationReportesOptions.pageNumber = newPage;
                paginationReportesOptions.pageSize = pageSize;
                paginationReportesOptions.firstRow = (paginationReportesOptions.pageNumber - 1) * paginationReportesOptions.pageSize;
                console.log('paginationChanged',newPage, pageSize);

                $scope.getPaginationReportesServerSide();
              });
            }
          };
          paginationReportesOptions.sortName = $scope.gridOptionsReportes.columnDefs[0].name;
          $scope.getPaginationReportesServerSide = function() { 
            console.log('repite doble');
            var arrParams = {
              paginate : paginationReportesOptions,
              datos : $scope.mySelectionGrid[0]
            };
            reporteCentralizadoServices.sListarReportesNoAgregadosAUsuario(arrParams).then(function (rpta) {
              $scope.gridOptionsReportes.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsReportes.data = rpta.datos;
            });
          };
          

          /* GRILLA DE REPORTES QUE HAN SIDO AGREGADOS AL USUARIO */ 
          var paginationReportesOptionsAddU = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 500,
            sort: uiGridConstants.DESC,
            sortName: null
          };
          
          $scope.gridOptionsReportesAddU = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 500,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            //enableSelectAll: true,
            //enableFiltering: false,
            //enableFullRowSelection: true,
            //multiSelect: false,
            columnDefs: [
              { field: 'id', displayName: 'ID', name: 'idreporte', width: 50,  sort: { direction: uiGridConstants.DESC}, visible:false },
              { field: 'categoria', name: 'descripcion_trp', displayName: 'CATEGORIA', width: 150 },
              { field: 'abreviatura', name: 'abreviatura_rp', displayName: 'ABREVIATURA', visible:false, width: 80 },
              { field: 'nombre', name: 'nombre_rp', displayName: 'REPORTE', width: 320 },
              { field: 'accion', name:'accion', displayName: '', width: 50, 
                cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-danger m-xs" ng-click="grid.appScope.btnAnularReporteDeUsuario(row.entity)" title="ELIMINAR"> <i class="ti ti-trash"></i></button>'+ 
                '</div>'
              }
            ],
            onRegisterApi: function(gridApiReporteAdd) {
              $scope.gridApiReporteAdd = gridApiReporteAdd;
              // gridApiReporteAdd.selection.on.rowSelectionChanged($scope,function(row){
              //   $scope.mySelectionReporteGrid = gridApiReporteAdd.selection.getSelectedRows();
              // });
              // gridApiReporteAdd.selection.on.rowSelectionChangedBatch($scope,function(rows){
              //   $scope.mySelectionReporteGrid = gridApiReporteAdd.selection.getSelectedRows();
              // });

              $scope.gridApiReporteAdd.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationReportesOptionsAddU.sort = null;
                  paginationReportesOptionsAddU.sortName = null;
                } else {
                  paginationReportesOptionsAddU.sort = sortColumns[0].sort.direction;
                  paginationReportesOptionsAddU.sortName = sortColumns[0].name;
                }
                $scope.getPaginationReportesAddUServerSide();
              });
              gridApiReporteAdd.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationReportesOptionsAddU.pageNumber = newPage;
                paginationReportesOptionsAddU.pageSize = pageSize;
                paginationReportesOptionsAddU.firstRow = (paginationReportesOptionsAddU.pageNumber - 1) * paginationReportesOptionsAddU.pageSize;
                $scope.getPaginationReportesAddUServerSide();
              });
            }
          };
          paginationReportesOptionsAddU.sortName = $scope.gridOptionsReportesAddU.columnDefs[0].name;
          $scope.getPaginationReportesAddUServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationReportesOptionsAddU,
              datos : $scope.mySelectionGrid[0]
            };
            reporteCentralizadoServices.sListarReportesAgregadosAUsuario($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsReportesAddU.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsReportesAddU.data = rpta.datos;
            });
            
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.btnAgregarReporteAUsuario = function (fila) { 
            // console.log('agregar');
            var arrParams = { 
              idreporte: fila.id,
              iduser: $scope.mySelectionGrid[0].id
            };
            reporteCentralizadoServices.sAgregarReporte(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationReportesAddUServerSide();
                $scope.getPaginationReportesServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              //$scope.getPaginationServerSide();
            });
          }
          $scope.btnAnularReporteDeUsuario = function (fila) { 
            var arrParams = {
              idusersporreporte: fila.idusersporreporte
            };
            reporteCentralizadoServices.sQuitarReporte(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationReportesAddUServerSide();
                $scope.getPaginationReportesServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              //$scope.getPaginationServerSide();
            });
          }
          $scope.getPaginationReportesServerSide();
          $scope.getPaginationReportesAddUServerSide();
        }
      });
    }
    /* RESTABLECER CONTASEÑA*/
    $scope.btnResetPassword = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          usuarioServices.sResetPassword($scope.mySelectionGrid).then(function (rpta) {
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
  }])
  .service("usuarioServices",function($http, $q) {
    return({
        sListarUsuarios: sListarUsuarios,
        sListarUsuariosCbo: sListarUsuariosCbo,
        sListarUsuariosCajaCbo: sListarUsuariosCajaCbo,
        sListarSedesNoAgregadosAUsuario: sListarSedesNoAgregadosAUsuario,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAgregarSede: sAgregarSede,
        sQuitarSedeDeUsuario: sQuitarSedeDeUsuario,
        sHabilitar: sHabilitar,
        sDeshabilitar: sDeshabilitar,
        sverificaPassword: sverificaPassword,
        sConfirmarPassword:sConfirmarPassword,
        sResetPassword:sResetPassword,
        sUserEmpleadoAutocomplete:sUserEmpleadoAutocomplete,
    });

    function sListarUsuarios(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/lista_usuarios", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarUsuariosCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/lista_usuario_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarUsuariosCajaCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/listar_usuarios_caja_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedesNoAgregadosAUsuario (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/lista_sedes_no_agregados_a_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/agregar_sede_a_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarSedeDeUsuario (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/quitar_sede_de_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sverificaPassword (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/verifica_password", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sConfirmarPassword (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/confirmar_password", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sResetPassword (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/reset_password", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sUserEmpleadoAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"usuario/cargar_user_empleado_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  }).filter('mapEstado', function() {
  var sizeHash = {
    1: 'HABILITADO',
    2: 'DESHABILITADO'
  };
  return function(input) {
    if (!input){
      return '';
    } else {
      return sizeHash[input];
    }
  };
});