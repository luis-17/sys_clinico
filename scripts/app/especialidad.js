angular.module('theme.especialidad', ['theme.core.services'])
  .controller('especialidadController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'especialidadServices',
    'tipoEspecialidadServices',
    'sedeServices' , 
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      especialidadServices,
      tipoEspecialidadServices,
      sedeServices  ){
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'especialidad';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };

    var paginationOptionsMed = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };

    $scope.demandaOptions = [
      { id: 'A', demanda: 'Alta' },
      { id: 'B', demanda: 'Baja' },
      { id: 'N', demanda: 'No asignada' }
    ];

    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };

    $scope.btnToggleFilteringMed = function(){
      $scope.gridOptionsMed.enableFiltering = !$scope.gridOptionsMed.enableFiltering;
      $scope.gridApiMed.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    
    $scope.fBusqueda = {};
    $scope.listaDemanda = angular.copy($scope.demandaOptions);
    $scope.listaDemanda.splice(0,0,{ id : '0', demanda:'-- Todas las demandas --'});  
    $scope.fBusqueda.demanda = '0';

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
        { field: 'id', name: 'idespecialidad', displayName: 'ID', width: '8%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'nombre', name: 'nombre', displayName: 'Especialidad' },
        { field: 'descripcion', name: 'descripcion', displayName: 'Tipo Especialidad', width: '12%' },
        { field: 'dias_libres', name: 'dias_libres', displayName: 'Días Libres', width: 120 },
        { field: 'estado_bloq', type: 'object', name: 'estado_bloq', displayName: 'Atención Día', width: '8%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.bloqueaDesbloquea(row)" >'+
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
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = {
            'idespecialidad' : grid.columns[1].filters[0].term,
            'nombre' : grid.columns[2].filters[0].term,
            'descripcion' : grid.columns[3].filters[0].term,
            'dias_libres' : grid.columns[4].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      especialidadServices.sListarEspecialidades($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    $scope.bloqueaDesbloquea = function(row){
      //alert('desblokeo');
      console.log('row', row.entity);
      especialidadServices.sBloqueaDesbloqueaEspecialidad(row.entity).then(function (rpta){
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
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'especialidad/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Especialidad';
          // TIPO ESPECIALIDAD  
          tipoEspecialidadServices.sListarTipoEspecialidadCbo().then(function (rpta) {
            $scope.listaTipoEspecialidad = rpta.datos;
            $scope.listaTipoEspecialidad.splice(0,0,{ id : '', descripcion:'--Seleccione Tipo de Especialidad--'});
          });
          $scope.cancel = function () {
            console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            especialidadServices.sEditar($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
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
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'especialidad/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de especialidad';
          // TIPO ESPECIALIDAD  
          tipoEspecialidadServices.sListarTipoEspecialidadCbo().then(function (rpta) {
            $scope.listaTipoEspecialidad = rpta.datos;
            $scope.listaTipoEspecialidad.splice(0,0,{ id : '', descripcion:'--Seleccione Tipo de Especialidad--'});
            $scope.fData.idtipoespecialidad = $scope.listaTipoEspecialidad[0].id;
          });
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            especialidadServices.sRegistrar($scope.fData).then(function (rpta) {
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
          especialidadServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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

    $scope.gridOptionsMed = {
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
        { field: 'id', name: 'a.idsedeespecialidad', displayName: 'ID', width: '8%',  sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
        { field: 'descripcion_sede', name: 'a.descripcion_sede', displayName: 'Sede', width: '15%', enableCellEdit: false },
        { field: 'nombre_esp', name: 'a.nombre_esp', displayName: 'Especialidad', enableCellEdit: false },
                
        { field: 'demanda', name: 'a.demanda', displayName: 'Demanda Especialidad',type: 'object', 
        cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label label-hand {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>',
        editableCellTemplate: 'ui-grid/dropdownEditor', cellFilter: 'mapDemanda', enableCellEdit: true,cellClass:'ui-editCell',
                editDropdownValueLabel: 'demanda', editDropdownOptionsArray: $scope.demandaOptions },
       /* { field: 'tiene_prog_cita', name: 'a.tiene_prog_cita', displayName: 'Prog. Asistencial',cellClass:'ui-editCell',enableCellEdit: true }, */ 
        { field: 'tiene_prog_cita', type: 'object', name: 'tiene_prog_cita', displayName: 'Prog. Asistencial', width: '8%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.editarProgAsistencial(row)" >'+
            '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="danger" ></switch>'+ 
            '</div>'
        },
        { field: 'tiene_venta_prog_cita', type: 'object', name: 'tiene_venta_prog_cita', displayName: 'Venta Prog. Asistencial', width: '8%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.editarVentaProgAsistencial(row)" >'+
            '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="danger" ></switch>'+ 
            '</div>'
        },
        { field: 'tiene_prog_proc', type: 'object', name: 'tiene_prog_proc', displayName: 'Prog. Procedimiento', width: '8%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.editarProgProc(row)" >'+
            '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="danger" ></switch>'+ 
            '</div>'
        },
        { field: 'tiene_venta_prog_proc', type: 'object', name: 'tiene_venta_prog_proc', displayName: 'Venta Prog. Procedimiento', width: '8%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false,
          cellClass:'text-center',
          cellTemplate:'<div tooltip-placement="left" uib-tooltip="{{ COL_FIELD.labelText }}" ng-click="grid.appScope.editarVentaProgProc(row)" >'+
            '<switch name="enabled" ng-model="COL_FIELD.boolBloqueo" class="danger" ></switch>'+ 
            '</div>'
        }          
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApiMed = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiMed.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsMed.sort = null;
            paginationOptionsMed.sortName = null;
          } else {
            paginationOptionsMed.sort = sortColumns[0].sort.direction;
            paginationOptionsMed.sortName = sortColumns[0].name;
          }
          $scope.getPaginationMedServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsMed.pageNumber = newPage;
          paginationOptionsMed.pageSize = pageSize;
          paginationOptionsMed.firstRow = (paginationOptionsMed.pageNumber - 1) * paginationOptionsMed.pageSize;
          $scope.getPaginationMedServerSide();
        });
        $scope.gridApiMed.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsMed.search = true;
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
           paginationOptionsMed.searchColumn = {
            'a.idsedeespecialidad' : grid.columns[1].filters[0].term,            
            'a.descripcion_sede' : grid.columns[2].filters[0].term,
            'a.nombre_esp' : grid.columns[3].filters[0].term,
          }
          $scope.getPaginationMedServerSide();
        });
        gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
          // console.log(rowEntity, colDef, newValue, oldValue); 
          rowEntity.column = colDef.field;
          rowEntity.anteriorValor = oldValue;
          //console.log(rowEntity);
          especialidadServices.sEditarDemandaInGrid(rowEntity).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Error inesperado');
            }
            $scope.getPaginationServerSide('no',true);
            if( rpta.flag != 2 ){ // si es 2 no muestro la alerta sino un modal
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            }
            
          });
          $scope.$apply();
          $scope.getPaginationMedServerSide();
        });
      }
    };

    paginationOptionsMed.sortName = $scope.gridOptionsMed.columnDefs[0].name;
    $scope.getPaginationMedServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptionsMed,
        datos : $scope.fBusqueda
      };
      //console.log($scope.datosGrid);
      especialidadServices.sListarDemandaEspecialidadSede($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsMed.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsMed.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationMedServerSide();

     $scope.editarProgAsistencial = function(row){
      console.log('row', row.entity);
      especialidadServices.sEditarProgAsistencialEspSede(row.entity).then(function (rpta){
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';          
        }else{
          alert('Error inesperado');
        }
        //$scope.fData = {};
        $scope.getPaginationMedServerSide();
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    };     
    $scope.editarVentaProgAsistencial = function(row){
      console.log('row', row.entity);
      especialidadServices.sEditarVentaProgAsistencialEspSede(row.entity).then(function (rpta){
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';          
        }else{
          alert('Error inesperado');
        }
        //$scope.fData = {};
        $scope.getPaginationMedServerSide();
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    };
    $scope.editarProgProc = function(row){
      console.log('row', row.entity);
      especialidadServices.sEditarProgProcEspSede(row.entity).then(function (rpta){
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';          
        }else{
          alert('Error inesperado');
        }
        //$scope.fData = {};
        $scope.getPaginationMedServerSide();
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    };     
    $scope.editarVentaProgProc = function(row){
      console.log('row', row.entity);
      especialidadServices.sEditarVentaProgProcEspSede(row.entity).then(function (rpta){
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';          
        }else{
          alert('Error inesperado');
        }
        //$scope.fData = {};
        $scope.getPaginationMedServerSide();
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    };

    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva especialidad',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar especialidad',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular especialidad',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar especialidad',
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
  .service("especialidadServices",function($http, $q) {
    return({
        sListarEspecialidades: sListarEspecialidades,
        sListarEspecialidadesCbo: sListarEspecialidadesCbo,
        sListarEspecialidadesBusqueda: sListarEspecialidadesBusqueda, 
        sListarEspecialidadesRestriccionesCbo: sListarEspecialidadesRestriccionesCbo, 
        sListarEspecialidadesEmpresaSedeDeSession: sListarEspecialidadesEmpresaSedeDeSession, 
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sListarEspecialidadPorAutocompletado: sListarEspecialidadPorAutocompletado,
        sListarSoloEspecialidadPorAutocompletado: sListarSoloEspecialidadPorAutocompletado,
        sListarEspecialidadesBloqueadasDia: sListarEspecialidadesBloqueadasDia,
        sListarEspecialidadesProgramCbo: sListarEspecialidadesProgramCbo,
        sBloqueaDesbloqueaEspecialidad: sBloqueaDesbloqueaEspecialidad,
        sListarDemandaEspecialidadSede:sListarDemandaEspecialidadSede,
        sEditarDemandaInGrid:sEditarDemandaInGrid,
        sEditarProgAsistencialEspSede: sEditarProgAsistencialEspSede,
        sListarEspecialidadesProgAsistencial: sListarEspecialidadesProgAsistencial,
        sEditarVentaProgAsistencialEspSede: sEditarVentaProgAsistencialEspSede, 
        sEditarProgProcEspSede: sEditarProgProcEspSede,
        sEditarVentaProgProcEspSede: sEditarVentaProgProcEspSede,      
    });

    function sListarEspecialidades(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesBusqueda (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_busqueda", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesRestriccionesCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_restricciones",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesEmpresaSedeDeSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_sedes_empresas_de_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSoloEspecialidadPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_solo_especialidades_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesBloqueadasDia (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_bloqueadas_dia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesProgramCbo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/lista_especialidades_con_programacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sBloqueaDesbloqueaEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/bloqueaDesbloqueaEspecialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDemandaEspecialidadSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/lista_demanda_especialidad_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarDemandaInGrid (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/editar_demanda_en_grid", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarProgAsistencialEspSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/editar_prog_asistencial_especialidad_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesProgAsistencial (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/lista_especialidades_prog_asistencial", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarVentaProgAsistencialEspSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/editar_venta_prog_asistencial_especialidad_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarProgProcEspSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/editar_prog_proc_especialidad_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarVentaProgProcEspSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Especialidad/editar_venta_prog_proc_especialidad_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  }).filter('mapDemanda', function() {
  var sizeHash = {
    'A': 'Alta',
    'B': 'Baja',
    'N': 'No asignada' ,
  };
  return function(input) {
    if (!input){
      return '';
    } else {
      return sizeHash[input];
    }
  };
});
