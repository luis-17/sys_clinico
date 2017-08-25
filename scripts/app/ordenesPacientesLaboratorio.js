angular.module('theme.ordenesPacientesLaboratorio', ['theme.core.services'])
  .controller('ordenesPacientesLaboratorioController', ['$scope', '$filter', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'resumenPacienteLaboratorioServices', 

    function($scope, $filter, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      resumenPacienteLaboratorioServices

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
    
    
    
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');

    $scope.fBusqueda.historia = null;
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsRP.enableFiltering = !$scope.gridOptionsRP.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsRP.data[rowIndex], $scope.gridOptionsRP.columnDefs[colIndex]);
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

    $scope.gridOptionsRP = {
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
        { field: 'fecha_recepcion', name: 'fecha_recepcion', displayName: 'FECHA RECEPC.', width: '10%', sort: { direction: uiGridConstants.ASC}},
        { field: 'orden_lab', name: 'orden_lab', displayName: 'ORDEN LAB.', width: '10%'},
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HISTORIA', width: '6%' },
        { field: 'num_documento', name: 'num_documento', displayName: 'Nº DOCUMENTO', width: '8%' },
        { field: 'nombres', name: 'c.nombres', displayName: 'NOMBRES', width: '20%'},
        { field: 'apellido_paterno', name: 'c.apellido_paterno', displayName: 'APELLIDO PATERNO', width: '15%' },
        { field: 'apellido_materno', name: 'c.apellido_materno', displayName: 'APELLIDO MATERNO', width: '15%' },
        
        { field: 'ordenventa', name: 'ordenventa', displayName: 'ORDEN VENTA', width: '10%', visible: false},
        // { field: 'prioridad', type: 'object', name: 'prioridad', displayName: 'PRIORIDAD', maxWidth: 250,
        //   cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>',
        //   filter: {
        //     term: '0',
        //     type: uiGridConstants.filter.SELECT,
        //     selectOptions: [ { value: '0', label: 'Todos' }, { value: '1', label: 'Normal'}, { value: '2', label: 'Urgente' } ]
        //   }
        // }
        { field: 'prioridad', type: 'object', name: 'prioridad', displayName: 'PRIORIDAD', maxWidth: 200, enableFiltering: false,
           cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
            'mp.fecha_recepcion' : grid.columns[1].filters[0].term,
            'mp.orden_lab' : grid.columns[2].filters[0].term,
            'mp.idhistoria' : grid.columns[3].filters[0].term,
            'cl.num_documento' : grid.columns[4].filters[0].term,
            'cl.nombres' : grid.columns[5].filters[0].term,
            'cl.apellido_paterno' : grid.columns[6].filters[0].term,
            'cl.apellido_materno' : grid.columns[7].filters[0].term,
            'mp.ordenventa' : grid.columns[8].filters[0].term,
            //'mp.prioridad' : grid.columns[9].filters[0].term,
            'mp.prioridad' : grid.columns[9].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptionsRP.columnDefs[0].name;
    $scope.getPaginationServerSide = function() { 
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      resumenPacienteLaboratorioServices.sListarResumenPacientes($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsRP.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsRP.data = rpta.datos;
        // $scope.gridOptionsRP.countCancelados = rpta.countCancelados;
        // $scope.gridOptionsRP.countAtendido = rpta.countAtendido;
        // $scope.gridOptionsRP.countRestante = rpta.countRestante;
        // $scope.gridOptionsRP.sumIngresos = rpta.sumIngresos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    // $scope.btnConsultar = function () { 
    //   $scope.getPaginationServerSide();
    // }

    $scope.btnDetalle = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'atencionMuestra/ver_popup_detalle_orden_laboratorio',
        // templateUrl: angular.patchURLCI+'atencionMedica/ver_popup_detalle_atenciones',
        size: size || '',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid) {
          //console.log($scope.fBusqueda);
          $scope.mySelectionGrid = mySelectionGrid;
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
              { field: 'idanalisis', name: 'idanalisis', displayName: 'ID', width: '136', sort: { direction: uiGridConstants.ASC}, visible: false },
              { field: 'producto', name: 'descripcion', displayName: 'Producto', enableFiltering: false },
              { field: 'seccion', name: 'seccion', displayName: 'Sección', enableFiltering: false },
              { field: 'examen', name: 'descripcion_anal', displayName: 'Examen', enableFiltering: false },
              { field: 'estado', type: 'object', name: 'estado_ap', displayName: 'Estado', maxWidth: 250, enableFiltering: false,
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
          paginationDetalle.sortName = $scope.gridOptionsDetalle.columnDefs[0].name;
          $scope.getPaginationDetalleServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationDetalle,
              datos : $scope.mySelectionGrid[0],
              rango : $scope.fBusqueda
            };
            resumenPacienteLaboratorioServices.sListarExamenesOrden($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalle.data = rpta.datos;
              $scope.total = rpta.suma;
            });
            $scope.mySelectionDetalleGrid = [];
          };
          $scope.getPaginationDetalleServerSide();
          console.log($scope.fData);
          /* fin grilla */
          $scope.titleForm = 'EXAMENES DE LA ORDEN: <span style="color: red">' + $scope.fData.orden_lab + '</span>';
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
  }])
  .service("resumenPacienteLaboratorioServices",function($http, $q) {
    return({
        sListarResumenPacientes: sListarResumenPacientes,
        sListarExamenesOrden: sListarExamenesOrden
    });

    function sListarResumenPacientes(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/listarOrdenLaboratorio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarExamenesOrden(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/lista_examenes_por_orden", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });