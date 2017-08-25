angular.module('theme.generarAnalisis', ['theme.core.services'])
  .controller('generarAnalisisController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys','generarAnalisisServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , hotkeys, generarAnalisisServices
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
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idmuestrapaciente', displayName: 'COD. MUESTRA', maxWidth: 120 },
        { field: 'tipomuestra', name: 'descripcion', displayName: 'MUESTRA'},
        { field: 'nombres', name: 'nombres', displayName: 'Nombres' },
        { field: 'apellidos', name: 'apellidos', displayName: 'Apellidos' },
        { field: 'fecha_recepcion', name: 'fecha_recepcion', displayName: 'Fecha' },
        { field: 'prioridad', type: 'object', name: 'prioridad', displayName: 'Prioridad', maxWidth: 250, enableFiltering: false,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>',sort: { direction: uiGridConstants.DESC} }
        // { field: 'estado', type: 'object', name: 'estado_mp', displayName: 'Estado', maxWidth: 250, enableFiltering: false,
        //   cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
              'idmuestrapaciente' : grid.columns[1].filters[0].term,
              'nombres' : grid.columns[2].filters[0].term
              
            }
            $scope.getPaginationServerSide();
          });

      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[5].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      generarAnalisisServices.sListarMuestrasPorAtender($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    // ***********************************************************
    $scope.btnGenerar = function(size){
      $modal.open({
        templateUrl: angular.patchURLCI+'atencionMuestra/ver_popup_formulario',
        size: size || '',
        //backdrop: 'static',
        //keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};

          
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Generar Analisis';
          $scope.fData.paciente = $scope.fData.apellidos + ', ' + $scope.fData.nombres; 
          $scope.gridOptionsAn = {
            paginationPageSizes: [10, 50, 100],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            data: [],
            multiSelect: true,
            columnDefs: [
              { field: 'idproductomaster', name: 'idproductomaster', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
              { field: 'producto', name: 'producto', displayName: 'Producto',minWidth: 180 },
              { field: 'analisis', name: 'descripcion_anal', displayName: 'Análisis',minWidth: 180 }, 
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApiPar = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionGridAn = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                  $scope.mySelectionGridAn = gridApi.selection.getSelectedRows();
              });

            }
          };

           $scope.getPaginationServerSideAn = function() {
            $scope.datosGrid = {
              data : $scope.fData
            };
            generarAnalisisServices.sListarAnalisisParaAgregar($scope.datosGrid.data).then(function (rpta) {

              $scope.gridOptionsAn.data = rpta.datos;
            });
            $scope.mySelectionGridAn = [];
          };
          $scope.getPaginationServerSideAn();
          $scope.getTableHeight = function() { 
            var rowHeight = 30; // your row height 
            var headerHeight = 30; // your header height 
            return { 
               height: ($scope.gridOptionsAn.data.length * rowHeight + headerHeight + 60) + "px" 
            }; 
          };
          $scope.aceptar = function(){
            
            $scope.fData.arrAnalisis = $scope.mySelectionGridAn;
            console.log($scope.fData);
            generarAnalisisServices.sRegistrarAnalisisPaciente($scope.fData).then(function (rpta) {
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
              //$scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $scope.getPaginationServerSide();
            });
            // $modalInstance.dismiss('cancel');


          }
          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
        },
        resolve:{
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          },
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }
    $scope.btnRechazarMuestra = function(){
      if( $scope.mySelectionGrid.length == 1 ){
        $scope.fData = $scope.mySelectionGrid[0];
      }else{
        alert('Seleccione una sola fila');
      }
      var pMensaje = 'Ingrese Motivo del Rechazo';
      $bootbox.prompt(pMensaje, function(result) {
        if(result){
          $scope.fData.motivorechazo = result;
          //console.log($scope.fData);
          //alert(result);
          generarAnalisisServices.sRechazarMuestraPaciente($scope.fData).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                //$modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                $scope.getPaginationServerSide();
              }else{
                alert('Error inesperado');
              }
              //$scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $scope.getPaginationServerSide();
          });
        }
      });
      
    }

  }])
  .service("generarAnalisisServices",function($http, $q) {
    return({
        sListarMuestrasPorAtender,
        sListarAnalisisParaAgregar,
        sRegistrarAnalisisPaciente,
        sRechazarMuestraPaciente
        
    });

    function sListarMuestrasPorAtender(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/listarMuestrasPorAtender", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAnalisisParaAgregar(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/listarAnalisisParaAgregar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarAnalisisPaciente(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/registrarAnalisisPaciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRechazarMuestraPaciente(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/rechazarMuestraPaciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });