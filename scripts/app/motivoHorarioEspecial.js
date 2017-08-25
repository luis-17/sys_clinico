angular.module('theme.motivoHorarioEspecial', ['theme.core.services'])
  .controller('motivoHorarioEspecialController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'motivoHorarioEspecialServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , motivoHorarioEspecialServices
      ){
    'use strict';
    $scope.fData = {};
    $scope.fData.temporal = {};
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null
    };
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
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idmotivohe', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'descripcion_mh', displayName: 'Descripción' },
        { field: 'agregarAJefes', name: 'agregar_a_jefes', displayName: 'Agregar a jefes', width: 120,
          cellTemplate: '<div class="text-center" ng-if="COL_FIELD"> SI </div><div class="text-center" ng-if="!COL_FIELD"> NO </div>'
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
            'idmotivohe' : grid.columns[1].filters[0].term,
            'descripcion_mh' : grid.columns[2].filters[0].term,
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
      motivoHorarioEspecialServices.sListarMotivoHorarioEspecial($scope.datosGrid).then(function (rpta) {
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
      $uibModal.open({
        templateUrl: angular.patchURLCI+'MotivoHorarioEspecial/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
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
          $scope.titleForm = 'Edición de Motivo Horario Especial';
          $scope.gridOptionsSubMotivoAdd = {
            minRowsToShow: 7,
            paginationPageSize: 50,
            enableSelectAll: false,
            multiSelect: false,
            data: [],
            columnDefs: [ 
              { field: 'submotivo', name: 'descripcion_smh', displayName: 'SubMotivo', },
              
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApiSM) {
              $scope.gridApiSM = gridApiSM; 
            }
          };
          motivoHorarioEspecialServices.sListarSubMotivoHorarioEspecialCbo($scope.fData).then(function (rpta) {
            // $scope.gridOptions.totalItems = rpta.paginate.totalRows;
            $scope.gridOptionsSubMotivoAdd.data = rpta.datos;
          });
          $scope.agregarSubMotivoItem = function () { 
            if( $scope.fData.temporal.submotivo.length < 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: Sub Motivo', type: 'warning', delay: 2500 });
              return false;
            }
            $scope.arrTemporal = { 
                'submotivo': $scope.fData.temporal.submotivo,
                'es_nuevo' : true,
            };
            $scope.gridOptionsSubMotivoAdd.data.push($scope.arrTemporal);
            $scope.fData.temporal = {};
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            if( row.entity.es_nuevo ){
              var index = $scope.gridOptionsSubMotivoAdd.data.indexOf(row.entity); 
              $scope.gridOptionsSubMotivoAdd.data.splice(index,1); 
            }else{
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                
                if(result){
                  var index = $scope.gridOptionsSubMotivoAdd.data.indexOf(row.entity); 
                  $scope.gridOptionsSubMotivoAdd.data.splice(index,1);

                  var paramDatos = {
                    'idsubmotivo' : row.entity.id,
                  }
                  motivoHorarioEspecialServices.sAnularSubMotivo(paramDatos).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });

                  });
                }
              });
            }
            
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            $scope.fData.submotivos = $scope.gridOptionsSubMotivoAdd.data;
            motivoHorarioEspecialServices.sEditar($scope.fData).then(function (rpta) { 
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
      $uibModal.open({
        templateUrl: angular.patchURLCI+'MotivoHorarioEspecial/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.submotivos = {};
          $scope.fData.temporal = {};
          $scope.fData.agregarAJefes = false;
          $scope.titleForm = 'Registro de Motivo Horario Especial';

          $scope.gridOptionsSubMotivoAdd = {
            minRowsToShow: 7,
            paginationPageSize: 50,
            enableSelectAll: false,
            multiSelect: false,
            data: [],
            columnDefs: [ 
              { field: 'submotivo', name: 'descripcion_smh', displayName: 'SubMotivo', },
              
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApiSM) {
              $scope.gridApiSM = gridApiSM; 
            }
          };
          $scope.agregarSubMotivoItem = function () { 
            if( $scope.fData.temporal.submotivo.length < 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: Sub Motivo', type: 'warning', delay: 2500 });
              return false;
            }
            $scope.arrTemporal = { 
                'submotivo': $scope.fData.temporal.submotivo, 
            };
            $scope.gridOptionsSubMotivoAdd.data.push($scope.arrTemporal);
            $scope.fData.temporal = {};

            // setTimeout(function() {
              $('#submotivo').focus(); 
            // }, 1000);
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            var index = $scope.gridOptionsSubMotivoAdd.data.indexOf(row.entity); 
            $scope.gridOptionsSubMotivoAdd.data.splice(index,1); 
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            $scope.fData.submotivos = $scope.gridOptionsSubMotivoAdd.data;
            motivoHorarioEspecialServices.sRegistrar($scope.fData).then(function (rpta) {
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
          motivoHorarioEspecialServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
  .service("motivoHorarioEspecialServices",function($http, $q) {
    return({
      sListarMotivoHorarioEspecialCbo: sListarMotivoHorarioEspecialCbo,
      sListarSubMotivoHorarioEspecialCbo: sListarSubMotivoHorarioEspecialCbo,
      sListarMotivoHorarioEspecial: sListarMotivoHorarioEspecial,
      sRegistrar: sRegistrar,
      sEditar: sEditar,
      sAnular: sAnular,
      sAnularSubMotivo : sAnularSubMotivo,
    }); 
    function sListarMotivoHorarioEspecialCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"MotivoHorarioEspecial/lista_motivo_horario_especial_cbo", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubMotivoHorarioEspecialCbo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"MotivoHorarioEspecial/lista_sub_motivo_horario_especial_cbo", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMotivoHorarioEspecial(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MotivoHorarioEspecial/lista_motivo_horario_especial", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MotivoHorarioEspecial/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MotivoHorarioEspecial/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MotivoHorarioEspecial/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularSubMotivo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MotivoHorarioEspecial/anular_submotivo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });