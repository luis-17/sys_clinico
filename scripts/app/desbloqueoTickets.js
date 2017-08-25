angular.module('theme.desbloqueoTickets', ['theme.core.services','uiSwitch'])
  .controller('desbloqueoTicketsController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys',
    'desbloqueoTicketsServices',
    'especialidadServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys,
      desbloqueoTicketsServices,
      especialidadServices ){
    'use strict';
    $scope.fData = {};
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.enabled = false;
    $scope.onOff = true;
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    /* LISTADO DE EMPRESA-ESPECIALIDADES */
    especialidadServices.sListarEspecialidadesBloqueadasDia().then(function (rpta) { 
      $scope.listaEspecialidades = rpta.datos;
      $scope.listaEspecialidades.splice(0,0,{ id : '', descripcion:'-- Seleccione Especialidad --'});

      $scope.fData.idespecialidad = $scope.listaEspecialidades[0].id;

    });
    
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: true,
      enableFullRowSelection: false,
      multiSelect: true,
      columnDefs: [
        { field: 'idventa', name: 'idventa', displayName: 'ID.VENTA', width:'8%', visible: false },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width:110,
          enableFiltering: false, sort: { direction: uiGridConstants.DESC}},
        { field: 'orden_venta', name: 'orden_venta', displayName: 'Nº ORDEN', width:120},
        
         
        { field: 'idhistoria', name: 'idhistoria', displayName: 'Nº HISTORIA', width:90, cellClass:'text-right' },
        { field: 'paciente', name: 'paciente', displayName: 'PACIENTE', minWidth:140},
        // { field: 'nombres', name: 'nombres', displayName: 'NOMBRES'},
        // { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'APELLIDO PATERNO'},
        // { field: 'apellido_materno', name: 'apellido_materno', displayName: 'APELLIDO MATERNO'},
        { field: 'edadActual', name: 'edad', displayName: 'EDAD', width:80, visible: false},
        { field: 'producto', name: 'descripcion', displayName: 'PRODUCTO', minWidth:140},
        
        { field: 'estado', type: 'object', name: 'paciente_atendido_det', displayName: 'ESTADO',
          enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          minWidth: 120, width:120,
          cellTemplate:
            '<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
        },
        { field: 'estado_bloq', type: 'object', name: 'estado_bloq', displayName: ' ',minWidth:60, width: 60, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.bloqueaDesbloquea(row)" ng-if="COL_FIELD.display">'+
            '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="danger"></switch>'+ 
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
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = {
            'idventa' : grid.columns[1].filters[0].term,
            'orden_venta' : grid.columns[3].filters[0].term,
            'idhistoria' : grid.columns[4].filters[0].term,
            "concat_ws(' ',cl.idcliente, cl.nombres, cl.apellido_paterno, cl.apellido_materno)" : grid.columns[5].filters[0].term,
            "DATE_PART('YEAR',AGE(fecha_nacimiento))" : grid.columns[6].filters[0].term,
            'pm.descripcion' : grid.columns[7].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[2].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fData
      };
      desbloqueoTicketsServices.sListarVentaPacientesBloqueados($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;

      });
      $scope.mySelectionGrid = [];
    };
    $scope.bloqueaDesbloquea = function(row){
      //alert('desblokeo');
      console.log('row', row.entity);
      desbloqueoTicketsServices.sBloqueaDesbloqueaVentaPaciente(row.entity).then(function (rpta){
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
          $scope.getPaginationServerSide();
        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';
          $scope.getPaginationServerSide();
        }else{
          alert('Error inesperado');
        }
        //$scope.fData = {};
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    };
    
  }])
  .service("desbloqueoTicketsServices",function($http, $q) {
    return({
        sListarVentaPacientesBloqueados: sListarVentaPacientesBloqueados,
        sBloqueaDesbloqueaVentaPaciente: sBloqueaDesbloqueaVentaPaciente,
        
    });

    function sListarVentaPacientesBloqueados(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"DesbloqueoTickets/lista_pacientes_bloqueados_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sBloqueaDesbloqueaVentaPaciente(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"DesbloqueoTickets/bloquea_desbloquea_venta_paciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
  });