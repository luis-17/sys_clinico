angular.module('theme.gestionNotificacionesProg', ['theme.core.services'])
  .controller('gestionNotificacionesProgController', ['$scope', '$sce', '$uibModal', '$modal', '$controller', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI',
    'gestionNotificacionesProgServices', 
    'controlEventoServices', 
    function($scope, $sce, $uibModal, $modal, $controller, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI,
      gestionNotificacionesProgServices,
      controlEventoServices){
      'use strict';
      shortcut.remove("F2"); 
      $scope.modulo = 'gestionNotificacionesProg';
      var paginationOptions = {
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };           
      
      $scope.gridOptions = { 
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        minRowsToShow: 10,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: true,
        enableFiltering: false,
        enableFullRowSelection: true,
       //rowHeight: 100,
        multiSelect: true,
        columnDefs: [
          { field: 'idcontrolevento', name: 'idcontrolevento', displayName: 'ID', sort: { direction: uiGridConstants.DESC}, visible:true, width: '5%',},
          { field: 'fecha_evento', name: 'fecha_evento', displayName: 'FECHA', width: '10%',enableFiltering:false,},
          { field: 'descripcion_te', name: 'descripcion_te', displayName: 'TIPO EVENTO', width: '18%',},
          { field: 'texto_notificacion', name: 'texto_notificacion', displayName: 'TEXTO NOTIFICACIÓN', enableFiltering:true,  },
          { field: 'estado', name: 'estado', displayName: 'ESTADO', width:'13%', enableFiltering:false, 
            cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>' 
          }
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
              if(sortColumns[0].name == 'estado'){
                paginationOptions.sortName = 'estado_ce';
              }
            }
            $scope.getPaginationServerSide();
          });

          $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
            var grid = this.grid;
            paginationOptions.search = true;
            paginationOptions.searchColumn = {
              'ce.idcontrolevento' : grid.columns[1].filters[0].term,
              'te.descripcion_te' : grid.columns[3].filters[0].term,
              'texto_notificacion' : grid.columns[4].filters[0].term,
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

      $scope.btnToggleFiltering = function(){      
        $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
        $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
      };
      $scope.getPaginationServerSide = function (){
        var datos = {
          key_evento: 'key_prog_med',          
        }

        $scope.datosGrid = {
          datos: datos,
          paginate: paginationOptions
        }
        
        $scope.mySelectionGrid = [];
        controlEventoServices.sListarControlEvento($scope.datosGrid).then(function (rpta) {
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
        });                 
      }
      $scope.getPaginationServerSide();
      $scope.boolExterno = true;
      $controller('controlEventoController', { 
          $scope : $scope
        });

      $scope.btnNuevaNotificacion = function(){
        var fnCallBack = function(){
          $scope.getPaginationServerSide();
        }
        $scope.btnNuevo('key_prog_med',10, fnCallBack);
      }

      $scope.btnReenviarNotificacion = function(){
        if($scope.mySelectionGrid.length != 1){
          alert('Debe seleccionar un solo registro');
          return;
        }
        var fnCallBack = function(){
          $scope.getPaginationServerSide();
        }
        $scope.btnReenviar($scope.mySelectionGrid[0],'key_prog_med',fnCallBack);
      }

      $scope.btnAnular = function (){
        controlEventoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
          if(rpta.flag == 1){
            var pTitle = 'OK!';
            var pType = 'success'; 
            $scope.getPaginationServerSide();
          }else if(rpta.flag == 0){
            var pTitle = 'Aviso!';
            var pType = 'warning';                             
          }else{
            alert('Error inesperado');
          }
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
        });                 
      } 

      $scope.btnVisible = function(){
        var datos = {
          lista: $scope.mySelectionGrid,
          nuevo_estado: 1
        }

        controlEventoServices.sCambiarEstado(datos).then(function (rpta) {
          if(rpta.flag == 1){
            var pTitle = 'OK!';
            var pType = 'success'; 
            $scope.getPaginationServerSide();
            controlEventoServices.sGeneraNotificacionPusher(datos);
          }else if(rpta.flag == 0){
            var pTitle = 'Aviso!';
            var pType = 'warning';                             
          }else{
            alert('Error inesperado');
          }
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
        });
      } 

      $scope.btnOculta = function(){
        var datos = {
          lista: $scope.mySelectionGrid,
          nuevo_estado: 2
        }
        
        controlEventoServices.sCambiarEstado(datos).then(function (rpta) {
          if(rpta.flag == 1){
            var pTitle = 'OK!';
            var pType = 'success'; 
            $scope.getPaginationServerSide();
          }else if(rpta.flag == 0){
            var pTitle = 'Aviso!';
            var pType = 'warning';                             
          }else{
            alert('Error inesperado');
          }
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
        });
      }  
  }])
  .service("gestionNotificacionesProgServices",function($http, $q) {
    return({

    });

  })