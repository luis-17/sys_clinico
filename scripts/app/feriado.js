angular.module('theme.feriado', ['theme.core.services'])
  .controller('feriadoController', ['$scope', '$filter', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'uibDateParser',
    'feriadoServices',
    function($scope, $filter, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout,
      uiGridConstants, pinesNotifications, uibDateParser
      , feriadoServices
      ){
    'use strict';
    $scope.fBusqueda = {};
    $scope.fData = {};
    $scope.fData.temporal = {};
    $scope.fData.feriadoDate = null;
    $scope.fData.temporal.arrFechas = [];
    $scope.fData.temporal.arrFeriados = [];
    $scope.listaAnyo = [
      {id:1, descripcion: '2016'},
      {id:2, descripcion: '2017'},
      {id:3, descripcion: '2018'},
      {id:4, descripcion: '2019'},
      {id:5, descripcion: '2020'},
      {id:6, descripcion: '2021'},
      {id:7, descripcion: '2022'},
      {id:8, descripcion: '2023'},
      {id:9, descripcion: '2024'},
      {id:10, descripcion: '2025'},

    ];
    $scope.fBusqueda.anyo = $scope.listaAnyo[0];
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
        { field: 'id', name: 'idferiado', displayName: 'ID', maxWidth: 80, visible: false },
        { field: 'fecha', name: 'fecha', displayName: 'FECHA', width:"20%",  sort: { direction: uiGridConstants.ASC} },
        { field: 'fecha_unix', name: 'fecha_unix', displayName: 'FECHA UNIX', width:"10%", visible:false },
        { field: 'descripcion', name: 'descripcion', displayName: 'DESCRIPCION', enableCellEdit: false, },
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        /*gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){
          feriadoServices.sEditar(rowEntity).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else if(rpta.flag == 2){
              
            }else{
              alert('Error inesperado');
            }
            $scope.getPaginationServerSide();
          });
          $scope.$apply();
        });*/
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
    paginationOptions.sortName = $scope.gridOptions.columnDefs[1].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda,
      };
      feriadoServices.sListarferiados($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
         
        
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    /***** PASCUA *****/
    $scope.getPascua = function() {
      $scope.datosGrid = {
        datos : $scope.fBusqueda,
      };
      feriadoServices.sObtenerPascua($scope.datosGrid).then(function (rpta) {
        $scope.pascua = rpta.datos;
        console.log('Jueves Santo: ', $scope.pascua); 
        
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPascua();
      /* ============= */
     /* MANTENIMIENTO */
    /* ============= */
    $scope.btnNuevo = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'feriado/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          //$scope.fData = {};
          $scope.optionsDP = {};
          $scope.fData.temporal.arrFechas = [];
          $scope.fData.temporal.arrFeriados = [];
          var year = $scope.fBusqueda.anyo.descripcion;
          $scope.fData.feriadoDate = new Date(year, '0', '1');
          $scope.optionsDP = {
            // customClass: getDayClass,
            minDate: new Date(year, '0', '1'),
            maxDate: new Date(year, '11', '31'),
            showWeeks: false,
            formatMonth: 'MMMM',
            startingDay: 1,
            maxMode: 'month',
            datepickerMode: 'month',
          }
          /*
          angular.forEach($scope.gridOptions.data, function (value, key) {
            var item = null;
            var arrObj = {};
            arrObj = {
                id: value['id'],
                fecha: $filter('date')(value['fecha_unix'],'dd-MM-yyyy'),
                nuevo: false
              }
            $scope.fData.temporal.arrFechas.push(value['fecha_unix']);
            //item = $filter('date')(value['fecha_unix'],'dd-MM-yyyy');
            // $scope.fData.temporal.arrFeriados.push(item);
            $scope.fData.temporal.arrFeriados.push(arrObj);
          });*/
          console.log('calendario ',$scope.fData.temporal.arrFechas);
          console.log('inputs', $scope.fData.temporal.arrFeriados);
          $scope.titleForm = 'Registro de feriados de ' + year;
          $scope.disabled = function(date, mode) {
            return (mode === 'day' && (date.getDay() === 0));
            // return (mode === 'day' && (date.getDay() === 0 || date.getDay() === 6));
          };
          $scope.selectedDates = function () {
            var item = null;
            
            $scope.fData.temporal.arrFeriados = [];
            // var value = null;
            // value = $scope.fData.temporal.arrFechas.slice(-1)[0];
            // item = $filter('date')(value,'dd-MM-yyyy');

            // $scope.fData.temporal.arrFeriados.push(item);
            angular.forEach($scope.fData.temporal.arrFechas, function (value, key) {
              item = $filter('date')(value,'dd-MM-yyyy');
              $scope.fData.temporal.arrFeriados.push(item);
            });
          }
          
          $scope.eliminarFeriado = function (index) {
            console.log('Eliminando... ', index);
            $scope.fData.temporal.arrFechas.splice(index,1);
            $scope.fData.temporal.arrFeriados.splice(index,1);
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            feriadoServices.sRegistrar($scope.fData.temporal.arrFeriados).then(function (rpta) {
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
    $scope.btnEditar = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'feriado/ver_popup_formulario',
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
          $scope.titleForm = 'Edición de feriado';
          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            feriadoServices.sEditar($scope.fData).then(function (rpta) { 
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
    
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          feriadoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
  
    /* datepicker prueba*/

    $scope.today = function() {
      $scope.dt = new Date();
    };
    $scope.today();

    $scope.clear = function() {
      $scope.dt = null;
    };

    // Disable weekend selection
    $scope.disabled = function(date, mode) {
      return (mode === 'day' && (date.getDay() === 0));
      // return (mode === 'day' && (date.getDay() === 0 || date.getDay() === 6));
    };

    $scope.toggleMin = function() {
      $scope.minDate = $scope.minDate ? null : new Date('2016','0','1');
    };
    $scope.toggleMin();

    $scope.open = function($event) {
      $event.preventDefault();
      $event.stopPropagation();

      $scope.opened = true;
    };

    $scope.dateOptions = {
      formatYear: 'yy',
      startingDay: 1
    };
    $scope.options = {
      minDate: new Date('2016','0','1'),
      maxDate: new Date('2016','11','31'),
      showWeeks: false,
      minMode: 'day',
      maxMode: 'month',
      datepickerMode: 'month',
    }
    $scope.initDate = new Date('2016','0','1');
    $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
    $scope.format = $scope.formats[0];
  }])
  .service("feriadoServices",function($http, $q) {
    return({
        sListarferiados: sListarferiados,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sObtenerPascua: sObtenerPascua,
        sListarferiadosCbo: sListarferiadosCbo,
    });
    function sListarferiados(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"feriado/lista_feriados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"feriado/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"feriado/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"feriado/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerPascua (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"feriado/obtener_pascua", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarferiadosCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"feriado/lista_feriados_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });
  