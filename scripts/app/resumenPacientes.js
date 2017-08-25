angular.module('theme.resumenPacientes', ['theme.core.services'])
  .controller('resumenPacientesController', ['$scope', '$filter', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'resumenPacienteServices', 

    function($scope, $filter, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      resumenPacienteServices

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
      // useExternalSorting: true,
      // useExternalFiltering : false, // se verá después
      enableGridMenu: true,
      // enableRowSelection: true,
      // enableSelectAll: true,
      enableFiltering: true,
      // enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'idcliente', name: 'idcliente', displayName: 'ID', width: '4%', enableFiltering: false, visible: false },
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HISTORIA', width: '6%' },
        { field: 'num_documento', name: 'num_documento', displayName: 'Nº DOCUMENTO', width: '8%' },
        { field: 'nombres', name: 'c.nombres', displayName: 'NOMBRES', width: '15%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'apellido_paterno', name: 'c.apellido_paterno', displayName: 'APELLIDO PATERNO', width: '15%' },
        { field: 'apellido_materno', name: 'c.apellido_materno', displayName: 'APELLIDO MATERNO', width: '15%' },
        { field: 'countCancelados', name: 'countCancelados', displayName: 'PRODUCTOS VENDIDOS', width: '10%', type:'number', enableFiltering: false, 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countAtendido', name: 'countAtendido', displayName: 'PRODUCTOS ATENDIDOS', width: '10%', type:'number', enableFiltering: false, 
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleAnulados(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countRestante', name: 'countRestante', displayName: 'RESTANTES', width: '8%', type:'number', enableFiltering: false, 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleNC(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'sumIngresos', name: 'sumIngresos', displayName: 'INGRESOS', width: '10%', type:'number', enableFiltering: false, 
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black; text-decoration: underline;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' }
      ], 
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){ 
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
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
            'idhistoria' : grid.columns[2].filters[0].term,
            'c.num_documento' : grid.columns[3].filters[0].term,
            'c.nombres' : grid.columns[4].filters[0].term,
            'c.apellido_paterno' : grid.columns[5].filters[0].term,
            'c.apellido_materno' : grid.columns[6].filters[0].term
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
      resumenPacienteServices.sListarResumenPacientes($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsRP.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsRP.data = rpta.datos;
        $scope.gridOptionsRP.countCancelados = rpta.countCancelados;
        $scope.gridOptionsRP.countAtendido = rpta.countAtendido;
        $scope.gridOptionsRP.countRestante = rpta.countRestante;
        $scope.gridOptionsRP.sumIngresos = rpta.sumIngresos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    // $scope.btnConsultar = function () { 
    //   $scope.getPaginationServerSide();
    // }

    $scope.btnDetalle = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'atencionMedica/ver_popup_detalle_atenciones',
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
            sortName: null
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
              { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '136', sort: { direction: uiGridConstants.ASC} },
              { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', enableFiltering: false, width: '120' },
              { field: 'tipoproducto', name: 'nombre_tp', displayName: 'Tipo de Producto', width: '140' },
              { field: 'producto', name: 'producto', displayName: 'Producto/Servicio', width: '260' },
              { field: 'medico', name: "CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno)", displayName: 'PROF. ATENCIÓN', width: '260' },
              { field: 'importe', name: 'total_detalle', displayName: 'Importe', enableFiltering: false, width: '80' },
              { 
                field: 'estado', 
                displayName: 'Estado', 
                width: '170', 
                cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>', 
                cellClass:'text-center',
                enableColumnMenus: false, 
                enableColumnMenu: false,
                enableSorting: false,
                enableFiltering: false
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDetalleGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionDetalleGrid = gridApi.selection.getSelectedRows();
              });

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
                  'orden_venta' : grid.columns[1].filters[0].term,
                  'fecha_venta' : grid.columns[2].filters[0].term,
                  'nombre_tp' : grid.columns[3].filters[0].term,
                  'producto' : grid.columns[4].filters[0].term,
                  //  "concat_ws(' ',cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno)" : grid.columns[5].filters[0].term, 
                  "CONCAT_WS(' ',m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno)" : grid.columns[5].filters[0].term
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
            resumenPacienteServices.sListarDetalleAtencionesPacientes($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalle.data = rpta.datos;
              $scope.total = rpta.suma;
            });
            $scope.mySelectionDetalleGrid = [];
          };
          $scope.getPaginationDetalleServerSide();
          /* fin grilla */
          $scope.titleForm = 'PRODUCTOS DE ' + $scope.fData.nombres + ' ' + $scope.fData.apellido_paterno + ' ' + $scope.fData.apellido_materno + ' (Historia: ' + $scope.fData.idhistoria + ')'  ;
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
  .service("resumenPacienteServices",function($http, $q) {
    return({
        sListarResumenPacientes: sListarResumenPacientes,
        sListarDetalleAtencionesPacientes: sListarDetalleAtencionesPacientes
    });

    function sListarResumenPacientes(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_resumen_pacientes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleAtencionesPacientes(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_atenciones_por_paciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });