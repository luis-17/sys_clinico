angular.module('theme.sede', ['theme.core.services'])
  .controller('sedeController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'sedeServices',  
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, sedeServices ){
    'use strict';
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
        { field: 'id', name: 'idsede', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'descripcion', name: 'descripcion', displayName: 'Descripción' },
        { field: 'hora_inicio_formato', name: 'hora_inicio_atencion', displayName: 'hora inicio atención',  maxWidth: 200, enableFiltering:false},
        { field: 'hora_fin_formato', name: 'hora_fin_atencion', displayName: 'hora fin atención', maxWidth: 200, enableFiltering:false },
        { field: 'intervalo_sede', name: 'intervalo_sede', displayName: 'Intervalo sede', maxWidth: 200, enableFiltering:false },
        { field: 'tiene_prog_cita', type: 'object', name: 'tiene_prog_cita', displayName: 'Programación Cita', width: '12%', enableFiltering: false, enableSorting: false, 
        enableColumnMenus: false, enableColumnMenu: false, cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.editarTieneProgCita(row.entity)" >'+
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
      sedeServices.sListarSedes($scope.datosGrid).then(function (rpta) {
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
        templateUrl: angular.patchURLCI+'sede/ver_popup_formulario',
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

          //console.log($scope.fData);
          //console.log($scope.fData.hora_inicio , $scope.fData.hora_fin);
          if($scope.fData.hora_inicio != null){
            var elem = $scope.fData.hora_inicio.split(':');
            $scope.fData.hora1 = new Date(1970, 0, 1, elem[0], elem[1], elem[2]);
          }
          
          if($scope.fData.hora_fin != null){
            elem = $scope.fData.hora_fin.split(':');
            $scope.fData.hora2 = new Date(1970, 0, 1, elem[0], elem[1], elem[2]);
          }          

          $scope.titleForm = 'Edición de sede';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {             
            $scope.fData.hora_inicio = ($scope.fData.hora1.toTimeString().split(' '))[0];
            $scope.fData.hora_fin = ($scope.fData.hora2.toTimeString().split(' '))[0];

            sedeServices.sEditar($scope.fData).then(function (rpta) { 
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
        templateUrl: angular.patchURLCI+'sede/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de sede';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            $scope.fData.hora_inicio = ($scope.fData.hora1.toTimeString().split(' '))[0];
            $scope.fData.hora_fin = ($scope.fData.hora2.toTimeString().split(' '))[0];
            sedeServices.sRegistrar($scope.fData).then(function (rpta) {
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
          sedeServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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

    $scope.editarTieneProgCita = function (row){
      //console.log(row);
      var value;
      if(row.tiene_prog_cita.boolBloqueo){
        value = 2;
      }else{
        value = 1;
      }

      var datos = row;
      datos.value = value;
      sedeServices.sUpdateTieneProgCita(datos).then(function (rpta) {
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
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva sede',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar sede',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular sede',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar sede',
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
  .service("sedeServices",function($http, $q) {
    return({
        sListarSedes: sListarSedes,
        sListarSedeCbo: sListarSedeCbo,
        sListarSedePorEmpresaCbo: sListarSedePorEmpresaCbo,
        sListarHorarioSede: sListarHorarioSede,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sConsultarDatosSede: sConsultarDatosSede,
        sUpdateTieneProgCita: sUpdateTieneProgCita
    });

    function sListarSedes(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/lista_sedes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedeCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/lista_sedes_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedePorEmpresaCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/lista_sedes_por_empresa_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarHorarioSede(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/lista_horario_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sConsultarDatosSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/consultar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sUpdateTieneProgCita (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"sede/update_tiene_prog_cita", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });