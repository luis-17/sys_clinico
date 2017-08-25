angular.module('theme.resumenAtencion', ['theme.core.services'])
  .controller('resumenAtencionController', ['$scope', '$filter', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'resumenAtencionServices', 
    'especialidadServices', 
    'historialVentaFarmServices',
    'empresaAdminServices',
    'blockUI',
    function($scope, $filter, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      resumenAtencionServices, 
      especialidadServices, 
      historialVentaFarmServices,
      empresaAdminServices,
      blockUI 
    ){ 
    'use strict';
    // shortcut.remove("F2"); 
    // $scope.modulo = 'liquidacion'; 
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 100,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.fBusqueda = {};
    $scope.fBusquedaFarm = {};
    $scope.listaEspecialidades = []; 
    $scope.mySelectionGrid = []; 
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusquedaFarm.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
    });
    // $scope.fBusqueda.desde = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
    // var f=moment().format('YYYY-MM-DD'); 
    // f=moment().subtract('days',30); 
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');

    $scope.fBusquedaFarm.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusquedaFarm.desdeHora = '00';
    $scope.fBusquedaFarm.desdeMinuto = '00';
    $scope.fBusquedaFarm.hastaHora = 23;
    $scope.fBusquedaFarm.hastaMinuto = 59;
    $scope.fBusquedaFarm.hasta = $filter('date')(new Date(),'dd-MM-yyyy');

    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsRA.enableFiltering = !$scope.gridOptionsRA.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsRA.data[rowIndex], $scope.gridOptionsRA.columnDefs[colIndex]);
    };
    especialidadServices.sListarEspecialidadPorAutocompletado().then(function (rpta) { 
      if(rpta.flag === 1){
        $scope.listaEspecialidades = rpta.datos;
        $scope.listaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'-- TODOS --'});
        $scope.fBusqueda.idespecialidad = 'ALL';

      }else if( rpta.flag === 0 ){
        $scope.listaEspecialidades = [];
      }
    });

    $scope.gridOptionsRA = {
      paginationPageSizes: [100, 500, 1000],
      paginationPageSize: 100,
      // minRowsToShow: 100,
      // useExternalPagination: true,
      // useExternalSorting: true,
      // useExternalFiltering : false, // se verá después
      enableGridMenu: true,
      // enableRowSelection: true,
      // enableSelectAll: true,
      // enableFiltering: false,
      // enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'idespecialidad', name: 'idespecialidad', displayName: 'ID', width: '4%', enableFiltering: false }, 
        { field: 'especialidad', name: 'e.nombre', displayName: 'ESPECIALIDAD', width: '30%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'countCancelados', name: 'countCancelados', displayName: 'VENDIDOS', width: '12%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countAtendido', name: 'countAtendido', displayName: 'ATENDIDOS', width: '12%', type:'number', cellClass:'text-center', 
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleAnulados(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'countRestante', name: 'countRestante', displayName: 'RESTANTES', width: '12%', type:'number', cellClass:'text-center', 
          cellClass:'text-center', cellTemplate:'<a href="" style="color: black;" ng-click="grid.appScope.verDetalleNC(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' },
        { field: 'sumIngresos', name: 'sumIngresos', displayName: 'INGRESOS', width: '12%', type:'number', cellClass:'text-center', 
           cellClass:'text-center', cellTemplate:'<a href="" style="color: black; text-decoration: underline;" ng-click="grid.appScope.verDetalleVentas(row.entity);" > <strong> {{ COL_FIELD }} </strong> </a>' }
      ], 
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){ 
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptionsRA.columnDefs[0].name; // getPaginationServerSide
    $scope.getPaginationServerSide = function() { 
      blockUI.start('Ejecutando proceso...');

      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };

      // var arrParams = {
      //   paginate : paginationOptions,
      //   datos : $scope.fBusqueda
      // }
      resumenAtencionServices.sListarResumenAtenciones(arrParams).then(function (rpta) {
        // $scope.gridOptionsRA.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsRA.data = rpta.datos;
        $scope.gridOptionsRA.countCancelados = rpta.countCancelados;
        $scope.gridOptionsRA.countAtendido = rpta.countAtendido;
        $scope.gridOptionsRA.countRestante = rpta.countRestante;
        $scope.gridOptionsRA.sumIngresos = rpta.sumIngresos;
        blockUI.stop();
      });
      $scope.mySelectionGrid = [];
    };
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
          console.log($scope.fBusqueda);
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
            enableFiltering: true,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '136', sort: { direction: uiGridConstants.ASC} },
              { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', enableFiltering: false, width: '150' },
              { field: 'tipoproducto', name: 'nombre_tp', displayName: 'Tipo de Producto', width: '240' },
              { field: 'producto', name: 'producto', displayName: 'Producto/Servicio', width: '280' },
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
                  'producto' : grid.columns[4].filters[0].term
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
            resumenAtencionServices.sListarDetalleProductosPorEspecialidad($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalle.data = rpta.datos;
              $scope.total = rpta.suma;
            });
            $scope.mySelectionDetalleGrid = [];
          };
          $scope.getPaginationDetalleServerSide();
          /* fin grilla */
          $scope.titleForm = 'PRODUCTOS DE ' + $scope.fData.especialidad;
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

    $scope.getTotalesFarmServerSide = function () { 
      $scope.gridOptionsFarm = {};
      blockUI.start('Ejecutando proceso...');
      var arrParams = { 
        //paginate : paginationOptions,
        datos : $scope.fBusquedaFarm
      };
      historialVentaFarmServices.sListarVentasHistorial(arrParams).then(function (rpta) { 
        $scope.gridOptionsFarm.countVentas = rpta.paginate.totalRows;
        $scope.gridOptionsFarm.sumIngresos = rpta.paginate.sumTotal;
        blockUI.stop();
      });
    }
  }])
  .service("resumenAtencionServices",function($http, $q) {
    return({
        sListarResumenAtenciones: sListarResumenAtenciones,
        sListarDetalleAperturaCajas: sListarDetalleAperturaCajas,
        sListarDetalleProductosPorEspecialidad: sListarDetalleProductosPorEspecialidad
    });

    function sListarResumenAtenciones(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_resumen_atenciones", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleAperturaCajas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"caja/lista_detalle_apertura_cajas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleProductosPorEspecialidad(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_productos_por_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });