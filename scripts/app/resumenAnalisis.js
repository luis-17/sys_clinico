angular.module('theme.resumenAnalisis', ['theme.core.services'])
  .controller('resumenAnalisisController', ['$scope', '$filter', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'ModalReporteFactory',
    'resumenAnalisisServices', 

    function($scope, $filter, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, ModalReporteFactory,
      resumenAnalisisServices

    ){ 
    'use strict';
    // shortcut.remove("F2"); 
    // $scope.modulo = 'liquidacion'; 
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 20,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.fBusqueda = {};
    // $scope.listaEspecialidades = []; 
    $scope.mySelectionGrid = []; 
    
    // $scope.fBusqueda.desde = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
    // var f=moment().format('YYYY-MM-DD'); 
    // f=moment().subtract('days',30); 
    
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');

    $scope.fBusqueda.historia = null;
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsRA.enableFiltering = !$scope.gridOptionsRA.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsRA.data[rowIndex], $scope.gridOptionsRA.columnDefs[colIndex]);
    };
    // especialidadServices.sListarEspecialidadPorAutocompletado().then(function (rpta) { 
    //   if(rpta.flag === 1){
    //     $scope.listaEspecialidades = rpta.datos;
    //     $scope.listaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'-- TODOS --'});
    //     $scope.fBusqueda.idespecialidad = 'ALL';

    //   }else if( rpta.flag === 0 ){
    //     $scope.listaEspecialidades = [];
    //   }
    // });

    $scope.gridOptionsRA = {
      paginationPageSizes: [20, 50, 100],
      paginationPageSize: 20,
      // minRowsToShow: 100,
      useExternalPagination: true,
      useExternalSorting: true,
      // useExternalFiltering : false, // se verá después
      enableGridMenu: true,
      enableRowSelection: true,
      // enableSelectAll: true,
      enableFiltering: true,
       enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'idanalisis', name: 'anal.idanalisis', displayName: 'ID', width: '6%', sort: { direction: uiGridConstants.ASC}},
        { field: 'seccion', name: 'seccion', displayName: 'Sección', width: '10%'},
        { field: 'analisis', name: 'descripcion_anal', displayName: 'Analisis'},
        { field: 'countIngresados', name: 'countIngresados', displayName: 'Registrados', width: '12%', type:'number', cellClass:'text-center', enableFiltering: false,
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleAnulados(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countAtendido', name: 'countAtendido', displayName: 'Con resultados', width: '12%', type:'number', cellClass:'text-center', enableFiltering: false, cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleAnulados(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countRestante', name: 'countRestante', displayName: 'Sin Resultados', width: '12%', type:'number', cellClass:'text-center', enableFiltering: false,
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleNC(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countEntregados', name: 'countEntregados', displayName: 'Entregados', width: '12%', type:'number', cellClass:'text-center', enableFiltering: false,
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleNC(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' }
        // { field: 'sumIngresos', name: 'sumIngresos', displayName: 'INGRESOS', width: '12%', type:'number', cellClass:'text-center', 
        //    cellClass:'text-center', cellTemplate:'<a href="" style="color: black; text-decoration: underline;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' }
      ], 
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){ 
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          }else {
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
          console.log(paginationOptions);
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = {
            'anal.idanalisis' : grid.columns[1].filters[0].term,
            's.descripcion_sec' : grid.columns[2].filters[0].term,
            'anal.descripcion_anal' : grid.columns[3].filters[0].term,
            
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptionsRA.columnDefs[0].name;
    $scope.getPaginationServerSide = function() { 
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      resumenAnalisisServices.sListarResumenAnalisis($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsRA.totalItems = rpta.totalRows;
        $scope.gridOptionsRA.data = rpta.datos;
        $scope.gridOptionsRA.countIngresados = rpta.countIngresados;
        $scope.gridOptionsRA.countAtendido = rpta.countAtendido;
        $scope.gridOptionsRA.countRestante = rpta.countRestante;
        $scope.gridOptionsRA.countEntregados = rpta.countEntregados;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    // $scope.btnConsultar = function () { 
    //   $scope.getPaginationServerSide();
    // }

    $scope.btnDetalle = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'resultadoAnalisis/ver_popup_detalle_resumen_analisis',
        size: size || '',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid) {
          $scope.mySelectionGrid = mySelectionGrid;
          console.log($scope.mySelectionGrid);
          console.log($scope.fBusqueda);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          /* DATA GRID */ 
          
          var paginationDetalle = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleGrid = [];
          $scope.gridOptionsDetalle = {
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
              { field: 'orden_lab', name: 'orden_lab', displayName: 'ORDEN LAB.', width: 120},
              { field: 'idhistoria', name: 'idhistoria', displayName: 'HIST.CLI.', width: '8%' },
              { field: 'paciente', name: 'apellido_paterno', displayName: 'PACIENTE', enableFiltering: false },
              { field: 'fecha_examen', name: 'fecha_examen', displayName: 'FEC. REGISTRO', width: '13%', enableFiltering: false, sort: { direction: uiGridConstants.ASC} },
              { field: 'fecha_atencion', name: 'fecha_atencion_det', displayName: 'FEC. RESULTADO', width: '13%', enableFiltering: false },
              { field: 'fecha_entrega', name: 'fecha_entrega', displayName: 'FEC. ENTREGA', width: '13%', enableFiltering: false },
              { field: 'estado', type: 'object', name: 'estado_ap', displayName: 'Estado', width: '15%', enableFiltering: false,
           cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
           
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              // gridApi.selection.on.rowSelectionChanged($scope,function(row){
              //   $scope.mySelectionDetalleGrid = gridApi.selection.getSelectedRows();
              // });
              // gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
              //   $scope.mySelectionDetalleGrid = gridApi.selection.getSelectedRows();
              // });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationDetalle.sort = null;
                  paginationDetalle.sortName = null;
                } else {
                  paginationDetalle.sort = sortColumns[0].sort.direction;
                  paginationDetalle.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationDetalle.pageNumber = newPage;
                paginationDetalle.pageSize = pageSize;
                paginationDetalle.firstRow = (paginationDetalle.pageNumber - 1) * paginationDetalle.pageSize;
                $scope.getPaginationDetalleServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationDetalle.search = true;
                // console.log(grid.columns);
                // console.log(grid.columns[1].filters[0].term);
                paginationDetalle.searchColumn = {
                  'ap.idanalisis' : grid.columns[0].filters[0].term,
                  'descripcion_anal' : grid.columns[1].filters[0].term,
                 
                }
                $scope.getPaginationDetalleServerSide();
              });
            }
          };
          paginationDetalle.sortName = $scope.gridOptionsDetalle.columnDefs[3].name;
          $scope.getPaginationDetalleServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationDetalle,
              datos : $scope.mySelectionGrid[0],
              rango : $scope.fBusqueda
            };
            resumenAnalisisServices.sListarDetalleResumenAnalisis($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalle.data = rpta.datos;
            });
            $scope.mySelectionDetalleGrid = [];
          };
          $scope.getPaginationDetalleServerSide();
          
          /* fin grilla */
          $scope.titleForm = $scope.mySelectionGrid[0].analisis;
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
          }
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          }
        }

      });
    };
    $scope.btnExportarListaExcel = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'RESUMEN ANALISIS DE LABORATORIO',
        datos:{
          filtro: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'excel',
          tituloAbv: 'RAL',
          titulo: 'RESUMEN ANALISIS DE LABORATORIO',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportes/report_resumen_analisis_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
  }])
  .service("resumenAnalisisServices",function($http, $q) {
    return({
        sListarResumenAnalisis: sListarResumenAnalisis,
        sListarDetalleResumenAnalisis: sListarDetalleResumenAnalisis
    });

    function sListarResumenAnalisis(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarResumenAnalisis", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleResumenAnalisis(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/lista_detalle_resumen_analisis", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });