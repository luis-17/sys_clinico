angular.module('theme.empresaAdmin', ['theme.core.services'])
  .controller('empresaAdminController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',
    'empresaAdminServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys
      , empresaAdminServices
      ){
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'empresaAdmin';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null
    };

    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    $scope.dirImagesEmpresas = $scope.dirImages + "dinamic/empresa/";
    $scope.gridOptions = {
      rowHeight: 36,
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
        { field: 'id', name: 'idempresaadmin', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'razon_social', name: 'razon_social', displayName: 'Razón Social' },
        { field: 'nombre_legal', name: 'nombre_legal', displayName: 'Nombre Legal' }, 
        { field: 'ruc', name: 'ruc', displayName: 'RUC', maxWidth: 100 },
        
        // { field: 'redes_sociales', name: 'redes_sociales', displayName: 'Redes',
        //   cellTemplate:'<div style="text-align:center">'+
        //     '<a href="{{COL_FIELD.facebook}}" target="_blank" class="btn btn-social btn-facebook-alt" ng-show="{{COL_FIELD.facebook}}">'+
        //       '<i class="ti ti-facebook"></i>'+
        //     '</a>'+
        //     '<a href="{{COL_FIELD.twitter}}" target="_blank" class="btn btn-social btn-twitter-alt" ng-show="{{COL_FIELD.twitter}}">'+
        //       '<i class="ti ti-twitter"></i>'+
        //     '</a>'+
        //     '<a href="{{COL_FIELD.youtube}}" target="_blank" class="btn btn-social btn-youtube-alt" ng-show="{{COL_FIELD.youtube}}">'+
        //       '<i class="ti ti-youtube"></i>'+
        //     '</a>'+
        //   '</div>'
        // },
        { field: 'redes_sociales', name: 'redes_sociales', displayName: 'Redes',
          cellTemplate: '<div class="text-center">'+
            '<span ng-if="value.url != null " ng-repeat="(key, value) in COL_FIELD">'+ 
            '<a href="{{ value.url }}" target="_blank" class="btn btn-social btn-{{ value.nombre_red }}-alt" target="_blank" >'+
            '<i class="{{ value.clase }}"></i></a></span>'+
            '</div>' 
        },

        
        { field: 'nombre_logo', name: 'nombre_logo', displayName: 'Logo', enableFiltering: false, enableSorting: false, cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpresas + COL_FIELD }}" /> ' }
       
        
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
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      empresaAdminServices.sListarEmpresaAdmin($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'empresaAdmin/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide,dirImages) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.dirImages = dirImages;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Empresa Administradora';
          //$scope.fData.cantidad_puntos = 2;
          // $scope.fData.cantidad_soles = null;
          $scope.fData.tipo_precio = parseInt($scope.mySelectionGrid[0].tipo_precio);
          //console.log($scope.fData.tipo_precio);

          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            //$modalInstance.dismiss('backdrop click');
            $scope.fData = {};
            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            var formData = new FormData();
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });
            // var arrParams = {
            //   'datos': $scope.fData
            // }
            empresaAdminServices.sEditar(formData).then(function (rpta) {
            //empresaAdminServices.sEditar(arrParams).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Oops! Error inesperado...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1500 });
            });

          }
          //console.log($scope.mySelectionGrid);
        }, 
        resolve: {
          dirImages : function () {
            return $scope.dirImages;
          }, 
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
        templateUrl: angular.patchURLCI+'empresaAdmin/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Empresa Administradora';
          
          //$scope.fData.tipo_precio = $scope.listaTipoPrecio[0];
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            var formData = new FormData();
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });

            empresaAdminServices.sRegistrar(formData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Oops! Error inesperado...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1500 });
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
          empresaAdminServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
    $scope.btnAgregarSede = function () {
      $modal.open({
        templateUrl: angular.patchURLCI+'empresaAdmin/ver_popup_agregar_sede',
        size: 'lg',
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
          $scope.fDataSede = {};
          var rptaAdd ={};

          /* DATA GRID */ 
          var paginationSedeOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null
          };
          // $scope.mySelectionSedeGrid = [];
          $scope.gridOptionsSedes = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            //useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            multiSelect: false,
            columnDefs: [
              { field: 'idsede', name: 'idsede', displayName: 'COD.', maxWidth: 60,  sort: { direction: uiGridConstants.ASC} },
              { field: 'sede', name: 'descripcion', displayName: 'SEDE' },
              
              { field: 'accion', name:'accion', displayName: 'ACCION', width: 85, 
                cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-primary m-xs" ng-click="grid.appScope.btnAgregarSedesAEmpresa(row.entity)" title="AGREGAR"> <i class="ti ti-arrow-right"></i></button>'+ 
                '</div>' , enableCellEdit: false
              }

            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationSedeOptions.sort = null;
                  paginationSedeOptions.sortName = null;
                } else {
                  paginationSedeOptions.sort = sortColumns[0].sort.direction;
                  paginationSedeOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationSedesServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationSedeOptions.pageNumber = newPage;
                paginationSedeOptions.pageSize = pageSize;
                paginationSedeOptions.firstRow = (paginationSedeOptions.pageNumber - 1) * paginationSedeOptions.pageSize;
                $scope.getPaginationServerSide();
              });

            }
          };
          paginationSedeOptions.sortName = $scope.gridOptionsSedes.columnDefs[0].name;
          $scope.getPaginationSedesServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationSedeOptions,
              datos : $scope.mySelectionGrid[0],
            };
            empresaAdminServices.sListarSedesNoAgregadosAEmpresa($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsSedes.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsSedes.data = rpta.datos;
            });
            // $scope.mySelectionSedeGrid = [];
          };
          $scope.getPaginationSedesServerSide();
          $scope.titleFormAdd = 'Agregar Sedes';
          /*--------- AGREGAR SEDES ---------------*/
          // $scope.mySelectionSedeGridAdd = [];
          var paginationSedeOptionsAdd = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null
          };
          $scope.gridOptionsAddSedes = { 
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            multiSelect: false,
            columnDefs: [
              { field: 'idsede', name: 'idsede', displayName: 'COD.', maxWidth: 60,  sort: { direction: uiGridConstants.ASC} },
              { field: 'sede', name: 'descripcion', displayName: 'SEDE' },
              
              { field: 'accion', name:'accion', displayName: 'ACCION', width: 85, 
              cellTemplate:'<div class="" style="text-align:center;">'+
                '<button type="button" class="btn btn-sm btn-danger m-xs" ng-click="grid.appScope.quitarSedeDeEmpresa(row.entity)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>' , enableCellEdit: false
              }
            ],
            onRegisterApi: function(gridApi1) {
              $scope.gridApi1 = gridApi1;
              $scope.gridApi1.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationSedeOptionsAdd.sort = null;
                  paginationSedeOptionsAdd.sortName = null;
                } else {
                  paginationSedeOptionsAdd.sort = sortColumns[0].sort.direction;
                  paginationSedeOptionsAdd.sortName = sortColumns[0].name;
                }
                $scope.getPaginationSedesAddServerSide();
              });
              gridApi1.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationSedeOptionsAdd.pageNumber = newPage;
                paginationSedeOptionsAdd.pageSize = pageSize;
                paginationSedeOptionsAdd.firstRow = (paginationSedeOptionsAdd.pageNumber - 1) * paginationSedeOptionsAdd.pageSize;
                $scope.getPaginationServerSide();
              });
            }
          }; 
          paginationSedeOptionsAdd.sortName = $scope.gridOptionsAddSedes.columnDefs[0].name;
          $scope.getPaginationSedesAddServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationSedeOptionsAdd,
              datos : $scope.mySelectionGrid[0],
            };

            empresaAdminServices.sListarSedesAgregadosAEmpresa($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsAddSedes.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsAddSedes.data = rpta.datos;
            });
            // $scope.mySelectionSedeGridAdd = [];
          };
          $scope.getPaginationSedesAddServerSide();
          /*------------------------------------------*/

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataAdd = {};
          }

          $scope.btnAgregarSedesAEmpresa = function (rowSede) {
            rowSede['idempresaadmin'] = $scope.mySelectionGrid[0].id;
            //var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              empresaAdminServices.sAgregarSede(rowSede).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationSedesAddServerSide();
                  $scope.getPaginationSedesServerSide();
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
          $scope.quitarSedeDeEmpresa = function (rowSede) {
            //var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              empresaAdminServices.sQuitarSedeDeEmpresa(rowSede).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationSedesAddServerSide();
                  $scope.getPaginationSedesServerSide();
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
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add ({
        combo: 'alt+n',
        description: 'Nueva Empresa Administradora',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar Empresa Administradora',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular Empresa Administradora',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar',
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
  .service('empresaAdminServices',function($http, $q) {
    return({
        sListarEmpresaAdminVentaCbo: sListarEmpresaAdminVentaCbo, 
        sListarEmpresaAdminPorSedeCbo: sListarEmpresaAdminPorSedeCbo,
        sListarSedeEmpresaAdminCbo: sListarSedeEmpresaAdminCbo,
        sListarSedeEmpresaAdmin: sListarSedeEmpresaAdmin,
        sListarSedeEmpresaAdminPrecio : sListarSedeEmpresaAdminPrecio,
        sListarSedeEmpresaAdminUsuario : sListarSedeEmpresaAdminUsuario,
        sListarEmpresaAdmin: sListarEmpresaAdmin,
        sListarEmpresaporCodigo: sListarEmpresaporCodigo,
        sListarSedesNoAgregadosAEmpresa: sListarSedesNoAgregadosAEmpresa,
        sListarSedesAgregadosAEmpresa: sListarSedesAgregadosAEmpresa,
        sAgregarSede: sAgregarSede,
        sQuitarSedeDeEmpresa: sQuitarSedeDeEmpresa,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
    });
    function sListarEmpresaAdminVentaCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_empresa_admin_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresaAdminPorSedeCbo(pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_empresa_admin_por_sede_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedeEmpresaAdminCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_sede_empresa_admin_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedeEmpresaAdmin(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_sede_empresa_admin", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedeEmpresaAdminPrecio(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_sede_empresa_admin_precio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedeEmpresaAdminUsuario(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_sede_empresa_admin_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresaAdmin(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_empresa_admin", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresaporCodigo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/lista_empresa_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedesNoAgregadosAEmpresa(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/lista_sedes_no_agregadas_a_empresa_admin", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedesAgregadosAEmpresa(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/lista_sedes_agregadas_a_empresa_admin", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarSede(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/agregar_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarSedeDeEmpresa(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/quitar_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/registrar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/editar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresaAdmin/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });