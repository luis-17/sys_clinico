angular.module('theme.marcarAsistencia', ['theme.core.services'])
  .controller('marcarAsistenciaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', '$filter', 'uiGridConstants', 'pinesNotifications', 
    'asistenciaServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, $filter, uiGridConstants, pinesNotifications, 
    asistenciaServices 
      ){
    'use strict';
    $scope.fBusqueda = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fData = {};
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/;
    $scope.modulo = 'marcaAsistencia';



    $scope.fData.fecha = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fData.hora = $filter('date')(new Date(),'HH');
    $scope.fData.minuto = $filter('date')(new Date(),'mm');
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){ 
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    /* GRILLA PRINCIPAL */ 
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };

    $scope.clock = "Cargando hora..."; // initialise the time variable
    $scope.tickInterval = 1000 //ms

    var tick = function() {
        $scope.clock = Date.now() // get the current time
        $timeout(tick, $scope.tickInterval); // reset the timer
    }

    // Start the timer
    $timeout(tick, $scope.tickInterval);

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
      multiSelect: false,
      columnDefs: [ 
        { field: 'id', name: 'idasistencia', displayName: 'ID', width: '6%' },
        { field: 'fecha', name: 'fecha', displayName: 'FECHA', width: '12%',enableFiltering: false },
        { field: 'hora', name: 'hora', displayName: 'HORA', width: '12%' },
        { field: 'diferencia', name: 'diferencia_tiempo', displayName: 'DIF.', width: '8%', cellTemplate: '<span class="ui-grid-cell-contents">{{ COL_FIELD }}</span>' },
        { field: 'tipo', name: 'tipo_asistencia', displayName: 'TIPO', width: '10%' },
        { field: 'personal', name: 'personal', displayName: 'PERSONAL', width: '30%' },
        { field: 'estado', name: 'es.descripcion', displayName: 'ESTADO', width: '11%' ,enableFiltering: false,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
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
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            // POR DEFECTO ORDENAR POR: [6] => fecha_movimiento
            paginationOptions.sort = sortColumns[1].sort.direction;
            paginationOptions.sortName = sortColumns[1].name;
          }
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          // paginationOptions.searchColumn = { 
          //   'idasistencia' : grid.columns[1].filters[0].term,
          //   //"fecha" : grid.columns[2].filters[0].term,
          //   'hora' : grid.columns[3].filters[0].term,
          //   'diferencia_tiempo' : grid.columns[4].filters[0].term,
          //   'tipo_asistencia' : grid.columns[5].filters[0].term,
          //   'CONCAT(emp.nombres,' ',emp.apellido_paterno,' ',emp.apellido_materno)' : grid.columns[6].filters[0].term
          //   //'hora' : grid.columns[7].filters[0].term
          // }; 
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
    paginationOptions.sortName = $scope.gridOptions.columnDefs[3].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      asistenciaServices.sListarAsistencias(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.$watch('fData.codigo', function(newvalue,oldvalue) { 
      if(newvalue){
        if(!$scope.formAsistencia.$invalid) {
          if( newvalue.length == 8 ){
            $scope.fData.modulo = $scope.modulo;
            asistenciaServices.sRegistrar($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.getPaginationServerSide();
                $scope.fData = {};
                $scope.fData.fecha = $filter('date')(new Date(),'dd-MM-yyyy');
                $scope.fData.hora = $filter('date')(new Date(),'HH');
                $scope.fData.minuto = $filter('date')(new Date(),'mm');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ 
                title: pTitle, 
                text: rpta.message, 
                type: pType, 
                delay: 5000,
                min_height: '140px', 
                width: '500px'
              });
              setTimeout(function() { 
                $('.ui-pnotify > .alert.ui-pnotify-container.alert-success.ui-pnotify-shadow').addClass('ui-pnotify-size-md')
                $('#fData.codigo').focus(); 
              },300);
              
            });
          }  
        }else{
          pinesNotifications.notify({ title: 'Error', text: 'Ingrese valores correctos', type: 'error', delay: 1000 });
        }
      }
      
    });
    // $scope.onChangeRegistrarAsistencia = function () { 
    //   console.log($scope.fData.codigo, ' dasgfjhjdfsjui');
      
    // }
  }])
  .service("asistenciaServices",function($http, $q) {
    return({
        sListarAsistencias: sListarAsistencias,
        sListarAsistenciasDeEmpleado: sListarAsistenciasDeEmpleado,
        sActualizarMarcaciones: sActualizarMarcaciones,
        sActualizarMarcacionesEspeciales: sActualizarMarcacionesEspeciales,
        sRegistrar: sRegistrar
    });

    function sListarAsistencias(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Asistencia/lista_asistencias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAsistenciasDeEmpleado (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Asistencia/lista_asistencias_de_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarMarcaciones (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Asistencia/actualizar_marcaciones", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarMarcacionesEspeciales (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Asistencia/actualizar_marcaciones_especiales", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Asistencia/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });