angular.module('theme.atencionProcedimiento', ['theme.core.services','ui.grid.edit'])
  .controller('atencionProcedimientoController', ['$scope', '$route', '$controller', '$filter', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$uibModal', 'blockUI',
    'atencionProcedimientoServices', 
    'solicitudProcedimientoServices',
    'afeccionServices',
    'atencionMedicaAmbServices',
    'odontogramaServices',
    'medicamentoServices',
    'recetaMedicaServices',
    'clienteServices',
    'almacenFarmServices',
    'progMedicoServices',
    function($scope, $route, $controller, $filter, $sce, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, $uibModal, blockUI,
      atencionProcedimientoServices,
      solicitudProcedimientoServices,
      afeccionServices,
      atencionMedicaAmbServices,
      odontogramaServices,
      medicamentoServices,
      recetaMedicaServices,
      clienteServices,
      almacenFarmServices,
      progMedicoServices
    ){ 
    'use strict';
    // console.log('load controller'); 
    $scope.tabs = { 
      'estadoAtencionMedica': 'enabled',
      'estadoReceta': 'disabled',
      'estadoOtrasAtenciones': 'enabled'
    };
    $controller('atencionMedicaAmbController', { 
      $scope : $scope
    });
    $scope.fBusqueda = {};
    $scope.fBusquedaPAD = {};
    $scope.fData = {}; // ATENCIÓN MÉDICA  
    $scope.showOrden = false;
    $scope.showHistoria = false;
    $scope.showPaciente = false;
    $scope.registroFormularioAMA = false;
    $scope.registroFormularioAP = false;

    var datos = {
      tipo_atencion: 'P'
    }
    atencionMedicaAmbServices.sVerificarTieneProgramacion(datos).then(function (rpta) { 
      $scope.tieneProgramacion = rpta.datos;
      // console.log('rpta.datos',rpta.datos);
      if($scope.tieneProgramacion){
        $scope.listaFiltroBusqueda = [ 
          { id:'PPG', descripcion:'POR PROGRAMACION' },
          { id:'PP', descripcion:'POR PACIENTE' },
          { id:'PH', descripcion:'POR N° DE HISTORIA' },
          { id:'PNO', descripcion:'POR N° DE ORDEN' },
        ];
        $scope.verPacientesPorProgramacion();
      }else{
        $scope.listaFiltroBusqueda = [ 
          { id:'PP', descripcion:'POR PACIENTE' },
          { id:'PH', descripcion:'POR N° DE HISTORIA' },
          { id:'PNO', descripcion:'POR N° DE ORDEN' },
        ];
        $scope.showPaciente = true;
      }
      $scope.fBusqueda.tipoBusqueda = $scope.listaFiltroBusqueda[0].id;
      // SI ESTÁ ACTIVO EL MEDICO PARA GENERAR CONSULTAS A DESTIEMPO, ENTONCES... 
      if( rpta.fMedico.desbloq_por_sys_medico == 1 ){
        $scope.tieneProgramacion = false;
      }
    });

    $scope.reloadGrid = function () { console.log('click med');
      $interval( function() { 
          $scope.gridApiPAD.core.handleWindowResize();
          //$scope.gridApiPROC.core.handleWindowResize();
          $scope.gridApi.core.handleWindowResize();
          $scope.gridApiOAT.core.handleWindowResize(); // OTRAS ATENCIONES 
      }, 50, 5);
    }

    $scope.onChangeFiltroBusqueda = function () { 
      if( $scope.fBusqueda.tipoBusqueda === 'PNO' ){ // N° ORDEN 
        $scope.showOrden = true;
        $scope.showHistoria = false;
        $scope.showPaciente = false;
      }
      if( $scope.fBusqueda.tipoBusqueda === 'PP' ){ // PACIENTE 
        $scope.showOrden = false;
        $scope.showHistoria = false;
        $scope.showPaciente = true;
      }
       if( $scope.fBusqueda.tipoBusqueda === 'PH' ){ // HISTORIA 
        $scope.showOrden = false;
        $scope.showHistoria = true;
        $scope.showPaciente = false;
      }
      if( $scope.fBusqueda.tipoBusqueda === 'PPG' ){ // PROGRAMACION 
        $scope.showOrden = false;
        $scope.showHistoria = false;
        $scope.showPaciente = false;
        $scope.verPacientesPorProgramacion();
      }
    }
    /* GRILLA DE PACIENTES POR ATENDER */ 
    $scope.mySelectionGrid = [];
    $scope.gridOptionsPPA = { 
      paginationPageSizes: [20, 50, 100],
      paginationPageSize: 20,
      enableRowSelection: true,
      minRowsToShow: 6,
      data: [],
      enableFiltering: false,
      enableFullRowSelection: true,
      enableSelectAll: false,
      multiSelect: false,
      columnDefs: [
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '12%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'N° TICKET', width: '12%', visible: false },
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HIST.', width: '5%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA.', width: '12%' },
        { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '22%' },
        { field: 'edad', name: 'edad', displayName: 'EDAD', width: '5%', visible: false },
        { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PRODUCTO', width: '16%' },
        { field: 'producto', name: 'pm.descripcion', displayName: 'PRODUCTO/SERVICIO', width: '20%' },
        { field: 'situacion', type: 'object', name: 'situacion', displayName: 'ESTADO', width: '10%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="text-center"><label tooltip-placement="left" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
        } 
      ], 
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

      }
    };
    //$scope.reloadGrid();
    // MODAL PARA PACIENTES PROGRAMADOS
    $scope.verPacientesPorProgramacion = function(){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_por_programacion_procedimiento',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          // lista de programaciones
          $scope.fData = {};
          $scope.fCountTotales = {};
          // $scope.$parent.blockUI.start();
          var datos ={
            tipo_atencion_medica: 'P' 
          };
          progMedicoServices.sListarProgramacionMedicoCbo(datos).then(function (rpta) {
            $scope.listaProgramaciones = [];
            if(rpta.flag == 1){
              $scope.listaProgramaciones = rpta.datos;
              $scope.fData.programacion = $scope.listaProgramaciones[0].id;
              $scope.getPaginationProgServerSide();
            }else{
              $scope.listaProgramaciones.splice(0,0,{ id : '0', descripcion:'-- No tiene Programaciones --'});
              $scope.fData.programacion = $scope.listaProgramaciones[0].id;
            }
            // $scope.$parent.blockUI.stop(); 
          });
          $scope.titleForm = 'Pacientes Programados';
          $scope.fData.fecha = $filter('date')(new Date(),'dd-MMMM-yyyy');
          var paginationOptionsProg = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 100,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          // $scope.mySelectionProg = [];
          $scope.gridOptionsProg = {
            rowHeight: 36,
            paginationPageSizes: [100, 500, 1000],
            paginationPageSize: 100,
            useExternalPagination: false,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: true,
            enableFullRowSelection: false,
            multiSelect: false,
            columnDefs: [
              { field: 'orden_venta', name: 'orden_venta', displayName: 'ORDEN VENTA', width: 140, enableSorting:false},
              { field: 'ticket_venta', name: 'ticket_venta', displayName: 'TICKET', width: 150, enableSorting:false},
              { field: 'paciente', name: 'paciente', displayName: 'PACIENTE', enableSorting:false },  
              { field: 'estado', displayName: 'Acción', enableCellEdit: false, enableFiltering: false, width: 130,
                cellTemplate:'<button type="button" style="width: 85%;" class="btn btn-sm btn-warning mt-xs center-block" ng-click="grid.appScope.btnCargarPaciente(row)" ng-if="row.entity.estado.estado == 2"> ATENDER </button>' +
                  '<button type="button" style="width: 85%;" class="btn btn-sm btn-info mt-xs center-block" ng-click="grid.appScope.btnCargarPacienteAtendido(row)" ng-if="row.entity.estado.estado == 1"> VER ATENCION </button>' +
                  '<button type="button" style="width: 85%;" class="btn btn-sm btn-danger mt-xs center-block" ng-click="grid.appScope.btnCargarPaciente(row)" ng-if="row.entity.estado.estado == 3"> NOTA DE CRÉDITO </button>'
              }
              
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
             
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsProg.sort = null;
                  paginationOptionsProg.sortName = null;
                } else {
                  paginationOptionsProg.sort = sortColumns[0].sort.direction;
                  paginationOptionsProg.sortName = sortColumns[0].name;
                }
                $scope.getPaginationProgServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsProg.pageNumber = newPage;
                paginationOptionsProg.pageSize = pageSize;
                paginationOptionsProg.firstRow = (paginationOptionsProg.pageNumber - 1) * paginationOptionsProg.pageSize;
                $scope.getPaginationProgServerSide();
              });
            }
          }; 
          paginationOptionsProg.sortName = $scope.gridOptionsProg.columnDefs[0].name;
          $scope.getPaginationProgServerSide = function() {
            blockUI.start();
            var arrParams = {
              paginate : paginationOptionsProg,
              idprogmedico : $scope.fData.programacion,
              tipo_atencion : 'P'
            };
            progMedicoServices.sListarPacientesProgramadosParaProc(arrParams).then(function (rpta) { // contadores
              if (rpta.flag == 1 ){
                $scope.gridOptionsProg.data = rpta.datos; 
              }
              $scope.fCountTotales = rpta.contadores; 
              blockUI.stop();
            });
            // $scope.mySelectionProg = [];
          };
          /* $scope.getPaginationProgServerSide();*/
          $scope.btnCargarPaciente = function(row){ 
           
            if(row.entity.estado.estado === 3){ // NO AUTORIZADO 
              pinesNotifications.notify({ title: 'Bloqueo de Atención', text: 'Esta atención ha sido bloqueada.', type: 'danger', delay: 3000 }); 
              return false;
            }
              
            blockUI.start('Cargando información.');
            row.entity['origen_venta'] = 'C';
            atencionMedicaAmbServices.sListarPacienteProgramadoSinAtender(row.entity).then(function (rpta) { 
              $scope.resultado = rpta.datos;
              console.log('resultado',rpta.datos[0]);
              $scope.btnAtenderAlPacienteProc('no',$scope.resultado);
              $modalInstance.dismiss('cancel');
              blockUI.stop();
            });
          }
          $scope.btnCargarPacienteAtendido = function(row){ 
            blockUI.start('Cargando información.');
            row.entity['origen_venta'] = 'C';
            atencionMedicaAmbServices.sListarPacienteProgramadoAtendido(row.entity).then(function (rpta) { 
              $scope.resultado = rpta.datos;
              console.log('resultado',rpta.datos[0]);
              $scope.btnAtenderAlPacienteProc('si',$scope.resultado);
              $modalInstance.dismiss('cancel');
              blockUI.stop();
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    $scope.btnConsultarPacientesAtencion = function () { 
      $scope.mySelectionGrid = [];
      var validateButton = false;
      if( $scope.fBusqueda.tipoBusqueda === 'PNO' ){ // N° ORDEN 
        if( $scope.fBusqueda.numeroOrden ){ 
          validateButton = true;
        }
      }else if( $scope.fBusqueda.tipoBusqueda === 'PH' ){ // N° Historia 
        if( $scope.fBusqueda.numeroHistoria ){ 
          validateButton = true;
        }
      }else if( $scope.fBusqueda.tipoBusqueda === 'PP' ){ // PACIENTE 
        if( $scope.fBusqueda.paciente ){ 
          validateButton = true;
        }
      }else if( $scope.fBusqueda.tipoBusqueda === 'PPG' ){ // PACIENTE 
        $scope.verPacientesPorProgramacion();
        return;
      }
      if( validateButton ){ 
        // PACIENTES SIN ATENDER 
        $scope.fBusqueda.arrTipoProductos = [16]; // PROCEDIMIENTOS  
        atencionMedicaAmbServices.sListarPacientesSinAtender($scope.fBusqueda).then(function (rpta) { 
          if( rpta.flag === 1 ){
            $scope.gridOptionsPPA.data = rpta.datos; 
          }else if( rpta.flag === 2 ){ 
            pinesNotifications.notify({ title: 'Advertencia', text: rpta.message, type: 'warning', delay: 2000 });
          }
        });
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'Digite un patrón de búsqueda. El campo está vacío.', type: 'warning', delay: 2500 });
      }
    }


    /* GRILLA DE PACIENTES ATENDIDOS DEL DIA */ 
    $scope.btnToggleFiltering = function(){ 
      $scope.gridOptionsPAD.enableFiltering = !$scope.gridOptionsPAD.enableFiltering;
      $scope.gridApiPAD.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.mySelectionPADGrid = [];
    $scope.gridOptionsPAD = {
      paginationPageSizes: [50, 100],
      paginationPageSize: 50,
      enableRowSelection: true,
      minRowsToShow: 9,
      data: [],
      enableFiltering: false,
      enableFullRowSelection: true,
      enableSelectAll: false,
      multiSelect: false,
      columnDefs: [
        { field: 'num_acto_medico', name: 'idatencionmedica', displayName: 'N° ACT. MED.', width: '7%' },
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HIST.', width: '5%' },
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '12%', visible: false },
        { field: 'ticket', name: 'ticket_venta', displayName: 'N° TICKET', width: '12%', visible: false },
        { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '20%' },
        { field: 'edad', name: 'edad', displayName: 'EDAD', width: '5%', enableFiltering: false, visible: false },
        { field: 'especialidad', name: 'e.nombre', displayName: 'ESPECIALIDAD', width: '15%' },
        { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PRODUCTO', width: '15%' },
        { field: 'producto', name: 'pm.descripcion', displayName: 'PRODUCTO/SERVICIO', width: '18%' },
        { field: 'fecha_atencion', name: 'fecha_atencion', displayName: 'FECHA ATENCION', width: '9%' },
        { field: 'situacion', type: 'object', name: 'situacion', displayName: 'ESTADO', width: '7%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="text-center"><label tooltip-placement="left" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
        } 
      ], 
      onRegisterApi: function(gridApiPAD) {
        $scope.gridApiPAD = gridApiPAD;
        gridApiPAD.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionPADGrid = gridApiPAD.selection.getSelectedRows();
        });

      }
    };
    // PACIENTES ATENDIDOS DEL DIA 
    $scope.getPaginationServerSidePAD = function () {
      blockUI.start();
      atencionMedicaAmbServices.sListarPacientesAtendidos($scope.fBusquedaPAD).then(function (rpta) { 
        $scope.gridOptionsPAD.data = rpta.datos;
        blockUI.stop();
      });
    };
    //$scope.getPaginationServerSidePAD();
    $scope.getPacienteAutocomplete = function (value) { 
      var params = { 
        searchText: value,
        searchColumn: "UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno))",
        sensor: false,
        arrTipoProductos: [16]
      };
      return clienteServices.sListarClienteVentaAutoComplete(params).then(function(rpta) { 
        $scope.noResultsLPACI = false; 
        if( rpta.flag === 0 ){ 
          $scope.noResultsLPACI = true; 
        }else if( rpta.flag === 2 ){
          pinesNotifications.notify({ title: 'Advertencia', text: rpta.message, type: 'warning', delay: 2000 });
        }
        return rpta.datos; 
      });
    }
    $scope.listaTipoAtencionMedica = [ 
      { 'id': 'ALL', 'descripcion': '--TODOS--' }, 
      { 'id': 'CM', 'descripcion': 'CONSULTA MEDICA' }, 
      { 'id': 'P', 'descripcion': 'PROCEDIMIENTO' }, 
      { 'id': 'EA', 'descripcion': 'EXAMEN AUXILIAR' }, 
      { 'id': 'DO', 'descripcion': 'DOCUMENTO' } 
    ]; 
    $scope.fBusquedaPAD.idTipoAtencion = 'P';
    $scope.btnRegresarAlInicio = function () {
      //$route.reload();
      $scope.registroFormularioAMA = false; 
      $scope.registroFormularioAP = false; 
      $scope.reloadGrid(); 
      $scope.fBusqueda.paciente = null;
      $scope.fBusqueda.numeroOrden = null;
      if($scope.fBusqueda.tipoBusqueda == 'PPG'){
        $scope.verPacientesPorProgramacion();
      }
    }

    /* LISTADO DE PROCEDIMIENTOS */
    var paginationOptionsPROC = { 
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionPROCGrid = [];
    $scope.btnToggleFiltering = function(){ 
      $scope.gridOptionsProcedimientos.enableFiltering = !$scope.gridOptionsProcedimientos.enableFiltering; 
      $scope.gridApiPROC.core.notifyDataChange( uiGridConstants.dataChange.COLUMN ); 
    };
    $scope.gridOptionsProcedimientos = { 
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      minRowsToShow: 8,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      data: null,
      columnDefs: [ 
        { field: 'id', name: 'idsolicitudprocedimiento', displayName: 'ID', width: '5%',  sort: { direction: uiGridConstants.DESC}, enableCellEdit: false }, 
        { field: 'producto', name: 'producto', displayName: 'Procedimiento', width: '25%', enableCellEdit: false }, 
        { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '6%', enableCellEdit: true }, 
        { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'Fecha Solicitud', width: '18%', enableCellEdit: false }, 
        { field: 'fecha_realizacion', name: 'fecha_realizacion', displayName: 'Fecha Realizado', width: '18%', enableCellEdit: false }, 
        { field: 'acto_medico', name: 'idatencionmedica', displayName: 'Acto Médico', width: '10%', enableCellEdit: false }, 
        { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', width: '13%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
          cellTemplate:'<div class="text-center"><label tooltip-placement="left" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
        }
      ],
      onRegisterApi: function(gridApiPROC) {
        $scope.gridApiPROC = gridApiPROC;
        gridApiPROC.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionPROCGrid = gridApiPROC.selection.getSelectedRows();
        });
        gridApiPROC.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionPROCGrid = gridApiPROC.selection.getSelectedRows();
        });
        $scope.gridApiPROC.core.on.sortChanged($scope, function(grid, sortColumns) {
          if (sortColumns.length == 0) {
            paginationOptionsPROC.sort = null;
            paginationOptionsPROC.sortName = null;
          } else {
            paginationOptionsPROC.sort = sortColumns[0].sort.direction;
            paginationOptionsPROC.sortName = sortColumns[0].name;
          }
          $scope.getPaginationPROCServerSide();
        });
        $scope.gridApiPROC.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
          console.log(newPage, pageSize);
          paginationOptionsPROC.pageNumber = newPage;
          paginationOptionsPROC.pageSize = pageSize;
          paginationOptionsPROC.firstRow = (paginationOptionsPROC.pageNumber - 1) * paginationOptionsPROC.pageSize;
          $scope.getPaginationPROCServerSide();
        });

        $scope.gridApiPROC.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
          var arrParams = {
            'id': rowEntity.id,
            'cantidad': newValue
          }; 
          solicitudProcedimientoServices.sEditarCantidadSolicitudProcedimiento(arrParams).then(function (rpta) { 
            if(rpta.flag == 1){ 
              var pTitle = 'OK!';
              var pType = 'success';
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Se ha producido un problema. Contacte con el Area de Sistemas');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            $scope.getPaginationPROCServerSide();
          });
          // rowEntity.id;
          $scope.$apply();
        });
      }
    };
    paginationOptionsPROC.sortName = $scope.gridOptionsProcedimientos.columnDefs[0].name;
    $scope.getPaginationPROCServerSide = function() {
      $scope.datosGrid = { 
        paginate : paginationOptionsPROC, 
        datos : $scope.fBusquedaPROC 
      }; 
      $scope.datosGrid.datos.idhistoria = angular.copy($scope.fData.idhistoria);
      solicitudProcedimientoServices.sListarProcedimientosDePaciente($scope.datosGrid).then(function (rpta) { 
        $scope.gridOptionsProcedimientos.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsProcedimientos.data = rpta.datos;
      });
      $scope.mySelectionPROCGrid = [];
    };

    /* GRILLA DE HISTORIAL DE ATENCIONES MEDICAS DE PACIENTE */ 
    $scope.btnToggleFiltering = function(){ 
      $scope.gridOptionsOAT.enableFiltering = !$scope.gridOptionsOAT.enableFiltering;
      $scope.gridApiOAT.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.mySelectionOATGrid = [];
    $scope.gridOptionsOAT = {
      paginationPageSizes: [50, 100],
      paginationPageSize: 50,
      enableRowSelection: true,
      minRowsToShow: 9,
      data: [],
      enableFiltering: false,
      enableFullRowSelection: true,
      enableSelectAll: false,
      multiSelect: false,
      columnDefs: [
        { field: 'num_acto_medico', name: 'idatencionmedica', displayName: 'N° ACT. MED.', width: '8%' },
        { field: 'fecha_atencion', name: 'fecha_atencion', displayName: 'FECHA ATENCION', width: '10%' },
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HIST.', width: '6%', visible: false },
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '12%', visible: false },
        { field: 'ticket', name: 'ticket_venta', displayName: 'N° TICKET', width: '12%', visible: false },
        { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '20%', visible: false },
        { field: 'area_hospitalaria', name: 'descripcion_aho', displayName: 'AREA HOSP.', width: '15%' },
        { field: 'especialidad', name: 'e.nombre', displayName: 'ESPECIALIDAD', width: '15%' },
        { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PRODUCTO', width: '15%' },
        { field: 'producto', name: 'pm.descripcion', displayName: 'PRODUCTO/SERVICIO', width: '18%' },
        { field: 'sede_empresa_admin', name: 'sede_empresa_admin', displayName: 'EMPRESA/SEDE', width: '25%' },
        
      ], 
      onRegisterApi: function(gridApiOAT) {
        $scope.gridApiOAT = gridApiOAT;
        gridApiOAT.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionOATGrid = gridApiOAT.selection.getSelectedRows();
        });

      }
    };
    // OTRAS ATENCIONES DEL PACIENTE 
    $scope.getPaginationServerSideOAT = function () { 
      $scope.fBusquedaOAT.idhistoria = $scope.fData.idhistoria; 
      atencionMedicaAmbServices.sListarHistorialDePaciente($scope.fBusquedaOAT).then(function (rpta) { 
        $scope.gridOptionsOAT.data = rpta.datos; 
      }); 
    }; 

    /* ==================================================== */
    /* FORMULARIO DE ATENCION MEDICA CON TODAS LAS PESTAÑAS */
    /* ==================================================== */ 
    $scope.btnAtenderAlPacienteProc = function (estadoAtendido, mySelectionAtencionGrid) { 
      var pEstadoAtendido = estadoAtendido || false;
      $scope.gridOptionsProcedimientos.data = [];
      if(pEstadoAtendido === false){
        var mySelectionAtencionGrid = $scope.mySelectionGrid;
      }
      if(pEstadoAtendido === false){ // EN REGISTRAR, VALIDAR SI ES ATENCION DEL DIA O NO 
        if(mySelectionAtencionGrid[0].situacion.autorizado === 2){ // NO AUTORIZADO 
          pinesNotifications.notify({ title: 'Bloqueo de Atención', text: 'Esta atención ha sido bloqueada.', type: 'danger', delay: 3000 }); 
          return false;
        }
      }

      if( mySelectionAtencionGrid[0].idtipoproducto == 16 ){ // PROCEDIMIENTO CLINICO 
        $scope.registroFormularioAMA = true; 
        $scope.registroFormularioAP = false; 
        // console.log($scope.registroFormularioAMA);
        if(pEstadoAtendido && pEstadoAtendido === 'si'){ 
          $scope.fData = mySelectionAtencionGrid[0];
          $scope.fData.boolNumActoMedico = true; 
          $scope.titleForm = 'Edición del Procedimiento Realizado'; 
          $scope.tabs = { 
            'estadoAtencionMedica': 'enabled',
            'estadoReceta': 'enabled',
            'estadoOtrasAtenciones': 'enabled'
          };
        }else{ 
          $scope.fData = mySelectionAtencionGrid[0];
          $scope.fData.num_acto_medico = '-- SIN REGISTRAR --';
          $scope.fData.boolNumActoMedico = false;
          $scope.fData.id_area_hospitalaria = 1;
          $scope.fData.area_hospitalaria = 'CONSULTA EXTERNA';
          $scope.fData.fechaAtencion = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.titleForm = 'Registro del Procedimiento Realizado'; 
        }
        $scope.formSolicitudProcedimiento = false;
        $scope.grabarAtencionProcedimiento = function () { 
          if( $scope.fData.boolNumActoMedico ){ // ================================= EDITAR 
            // console.log('editar'); 
            atencionProcedimientoServices.sEditarAtencionProcedimiento($scope.fData).then(function (rpta) { 
              $scope.fData.boolNumActoMedico = true; 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                if( rpta.idatencionmedica ){ 
                  $scope.fData.num_acto_medico = rpta.idatencionmedica; 
                  $scope.gridOptionsPPA.data = []; 
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Se ha producido un problema. Contacte con el Area de Sistemas');
              }
              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }else{ // ================================================================= REGISTRAR 
            // console.log('registrar');
            atencionProcedimientoServices.sRegistrarAtencionProcedimiento($scope.fData).then(function (rpta) { 
              $scope.fData.boolNumActoMedico = false; 
              if(rpta.flag == 1) { 
                var pTitle = 'OK!';
                var pType = 'success';
                if( rpta.idatencionmedica ){ 
                  $scope.fData.num_acto_medico = rpta.idatencionmedica;
                  $scope.fData.boolNumActoMedico = true;
                  $scope.gridOptionsPPA.data = [];
                  $scope.tabs = { 
                    'estadoAtencionMedica': 'enabled',
                    'estadoReceta': 'enabled',
                    'estadoOtrasAtenciones': 'enabled'
                  };
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){ 
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Se ha producido un problema. Contacte con el Area de Sistemas');
              }
              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }
        /* =============================== */
        /*     BOTONES DE ODONTOGRAMA      */
        /* =============================== */
          $scope.boolOdontologia = false;
          if($scope.fSessionCI.idespecialidad == 28){ // Si es de la especialidad de odontologia mostrará los botones
            $scope.boolOdontologia = true;
          }
        /* ============================================*/
        /*  ODONTOGRAMA DE PROCEDIMIENTOS              */
        /* ============================================*/
          $scope.btnVerOdontogramaProc = function (size) { 
            $modal.open({
              templateUrl: angular.patchURLCI+'atencionOdontologica/ver_odontograma_procedimiento',
              size: size || '',
              scope: $scope,
              controller: function ($scope, $modalInstance, arrToModal) {
                // $scope.fData = {};
                $scope.titleForm = 'Odontograma de Procedimientos';
                var params = {
                  idhistoria: $scope.fData.idhistoria,
                  tipo_odontograma: 'procedimiento'
                }
                odontogramaServices.sListarOdontogramaInicial(params).then(function (rpta) {
                  $scope.listaOdontograma = rpta.datos;
                });
                odontogramaServices.sListarProcedimientosCbo().then(function (rpta) {
                  $scope.listaCategorias = rpta.datos;
                  $scope.fData.categoria = $scope.listaCategorias[0];
                  
                });
                $scope.marcarProcedimientoPieza = function(cuadrante,pieza,flag){
                  var estado = $scope.fData.categoria;
                  var arrPieza = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id];
                  var conCaries = false;
                  var estados_para_exodoncia = ['4', '13', '14', '15', '17', '20'];
                  var deciduas_pulp = ['54', '55','64', '65','74', '75','84', '85'];
                  var piezas_sellante = ['54', '55','64', '65','74', '75','84', '85', '16', '26', '36', '46'];
                  if(pieza.marca == 1){ // aplica marcacion de procedimiento

                    for (var i = 0; i < 5; i++) {
                      if(pieza.zonas[i].estados.length){
                        if(pieza.zonas[i].estados[0].id == 2){
                          conCaries = true;
                          break;
                        }
                      }
                    };
                    console.log(conCaries);

                    if(conCaries){
                      if(estado.descripcion == 'ENDODONCIA' && (cuadrante == 0 || cuadrante == 1 || cuadrante == 6 || cuadrante == 7)){
                        arrPieza.zonas[0].estados.splice(0,0,estado);
                        arrPieza.marca = 2;
                      }else if(estado.descripcion == 'PULPECTOMIA' || estado.descripcion == 'PULPOCTOMIA'){
                        if(deciduas_pulp.indexOf(pieza.id) != -1){
                          arrPieza.zonas[0].estados.splice(0,0,estado);
                          arrPieza.marca = 2;
                        }
                      }else if(estado.descripcion == 'OBTURACION'){
                        // alert('SE ABRIRA EL MODAL Y MARCARA POR ZONAS');
                        $scope.marcarZonaPiezaPr(cuadrante,pieza,estado);
                      }
                    }else{
                      // if(pieza.zonas[0].estados[0].id != 1){ // si no es pieza ausente
                        if(estado.descripcion == 'EXODONCIA' && estados_para_exodoncia.indexOf(pieza.zonas[0].estados[0].id) != -1 ){
                          arrPieza.zonas[0].estados.splice(0,0,estado);
                          arrPieza.marca = 2;
                        }
                      // }
                    }
                    
                  }else if(pieza.marca == 2){ // desmarca la pieza y coloca marca = 1
                    if(flag){
                      arrPieza.zonas[0].estados.splice(0,1);
                      arrPieza.marca = 1;
                    }else{
                      if(estado.descripcion == 'OBTURACION'){
                        // alert('SE ABRIRA EL MODAL Y MARCARA POR ZONAS');
                        $scope.marcarZonaPiezaPr(cuadrante,pieza,estado);
                      }
                    }
                    
                  }else{
                    if(estado.descripcion == 'SELLANTE' && piezas_sellante.indexOf(pieza.id) != -1){
                      arrPieza.zonas[0].estados.splice(0,0,estado);
                      arrPieza.marca = 2;
                    }
                  }
                };

                $scope.marcarZonaPiezaPr = function(cuadrante,pieza,estado){
                  $modal.open({
                    templateUrl: angular.patchURLCI+'odontograma/ver_pieza_dental',
                    size: 'sm',
                    scope: $scope,
                    controller: function ($scope, $modalInstance, arrToModal){
                      // $scope.fData = {};
                      $scope.cuadrante = cuadrante;
                      $scope.pieza = pieza;
                      $scope.estado = estado;

                      $scope.titleForm = 'Editar Pieza Dental: ' + pieza.id;
                      $scope.marcarZona = function(cuadrante,pieza,index,estado){
                        var arrZona = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id].zonas[index];
                        if(arrZona.estados.length == 1){
                          arrZona.estados.splice(0,0,estado);
                        }else if(arrZona.estados.length == 2){
                          arrZona.estados.splice(0,1);

                          // console.log($scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id].zonas[index].estados[0].simbolo);
                        }
                      };

                      $scope.aceptar = function(cuadrante,pieza){
                        var arrPieza = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id];

                        arrPieza.marca = 1;
                        for (var i = 0; i <= 4; i++) {
                          if(arrPieza.zonas[i].estados.length == 2 ){
                            arrPieza.marca = 2;

                          };
                        };

                        $modalInstance.close();
                      };

                    },
                    resolve: {
                      arrToModal: function() {
                        return {
                          pieza : $scope.pieza
                        }
                      }
                    }
                  });
                  // pinesNotifications.notify({ title: pieza, text: 'Se edita Pieza '+pieza, type: 'success', delay: 3000 });
                }
                $scope.guardarOdontogramaProcedimientos = function () {
                  $scope.listaOdontograma.tipo_odontograma = 2;
                  odontogramaServices.sRegistrar($scope.listaOdontograma).then(function (rpta) {
                    if(rpta.flag === 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      if( rpta.idodontograma ){ 
                        $scope.listaOdontograma.idodontograma = rpta.idodontograma;
                        $scope.fData.boolOdontogramaNuevo = false;
                      }
                    }else if(rpta.flag === 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Algo no salió bien...');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                }
              },
              resolve: {
                arrToModal: function() {
                  return {
                    fData : $scope.fData
                  }
                }
              }
            });
          }
        /* =============================== */
        /*     PESTAÑA DE PROCEDIMIENTO    */
        /* =============================== */
          var desde = moment().subtract(30,'days'); 

          $scope.fBusquedaPROC = {}; 
          $scope.fBusquedaPROC.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fBusquedaPROC.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy'); 
          $scope.getProcedimientoAutocomplete = function (value) { 
            var params = { 
              searchText: value,
              searchColumn: 'p.descripcion',
              sensor: false
            }
            return solicitudProcedimientoServices.sListarProcedimientoAutoComplete(params).then(function(rpta) { 
              $scope.noResultsLPAC = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLPAC = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getTableHeightMED = function() { 
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return {
                height: ($scope.gridOptionsMedicamentosAdd.data.length * rowHeight + headerHeight + 30) + "px"
             };
          };
          $scope.btnVerFormRegistrarProc = function () { 
            $scope.fDataProc = {};
            $scope.formSolicitudProcedimiento = true;
          }

          $scope.btnRegresarAlListadoProc = function () {
            $scope.formSolicitudProcedimiento = false;
          }

        /* ================================ */
        /*        PESTAÑA DE RECETA         */
        /* ================================ */
          var desde = moment().subtract(30,'days'); 
          $scope.fBusquedaREC = {}; 
          $scope.fBusquedaREC.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fBusquedaREC.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy');

          $scope.btnBusquedaMedicamentos = function () { 
            $uibModal.open({
              templateUrl: angular.patchURLCI+'medicamento/ver_popup_busqueda_medicamento_atencion_medica',
              size: 'lg',
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                $scope.titleForm = 'Búsqueda de Medicamentos';
                $scope.listarSubAlmacenesVenta = function (){
                  // var arrParams = {
                  //   'idalmacen': null
                  // }
                  // almacenFarmServices.sListarSubAlmacenesVentaSedeCbo().then(function (rpta) { 
                  atencionMedicaAmbServices.sListarSubAlmacenesVentaSedeCbo().then(function (rpta) { 
                    $scope.listaSubAlmacen = rpta.datos; 
                    $scope.fBusqueda.idsubalmacen = $scope.listaSubAlmacen[0].id;
                    $scope.getPaginationProductoEnVentaServerSide();
                  });
                };
                $scope.listarSubAlmacenesVenta();

                var paginationOptionsProductos = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.gridOptionsMedicamentoBusqueda = {
                  rowHeight: 36,
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  enableGridMenu: false,
                  enableRowSelection: false,
                  enableSelectAll: true,
                  enableFiltering: true,
                  // enableRowHeaderSelection: false, // fila cabecera 
                  //enableFullRowSelection: true,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'id', name: 'm.idmedicamento', displayName: 'COD.', maxWidth: 80 },
                    { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', minWidth: 100,  sort: { direction: uiGridConstants.ASC} },
                    { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', visible: false },
                    { field: 'stock', name: 'stock_actual_malm', displayName: 'STOCK', maxWidth: 80, cellClass: 'text-center', enableFiltering: false  }
                  ],
                  onRegisterApi: function(gridApi) { // gridComboOptions
                    $scope.gridApi = gridApi;
                    $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                      if (sortColumns.length == 0) {
                        paginationOptionsProductos.sort = null;
                        paginationOptionsProductos.sortName = null;
                      } else {
                        paginationOptionsProductos.sort = sortColumns[0].sort.direction;
                        paginationOptionsProductos.sortName = sortColumns[0].name;
                      }
                      $scope.getPaginationProductoEnVentaServerSide();
                    });
                    gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                      paginationOptionsProductos.pageNumber = newPage;
                      paginationOptionsProductos.pageSize = pageSize;
                      paginationOptionsProductos.firstRow = (paginationOptionsProductos.pageNumber - 1) * paginationOptionsProductos.pageSize;
                      $scope.getPaginationProductoEnVentaServerSide();
                    });
                    $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                      var grid = this.grid;
                      paginationOptionsProductos.search = true;
                      // console.log(grid.columns);
                      // console.log(grid.columns[1].filters[0].term);
                      paginationOptionsProductos.searchColumn = { 
                        'm.idmedicamento' : grid.columns[0].filters[0].term,
                        "( COALESCE (denominacion, '') || ' ' || COALESCE (descripcion, '') )" : grid.columns[1].filters[0].term,
                        'nombre_lab' : grid.columns[2].filters[0].term
                      }
                      $scope.getPaginationProductoEnVentaServerSide();
                    });
                  }
                };
                paginationOptionsProductos.sortName = $scope.gridOptionsMedicamentoBusqueda.columnDefs[1].name;
                $scope.getPaginationProductoEnVentaServerSide = function() {
                  //$scope.$parent.blockUI.start();
                  $scope.datosGrid = {
                    paginate : paginationOptionsProductos,
                    datos: $scope.fBusqueda
                  };
                  atencionMedicaAmbServices.sListarMedicamentosAlmacenBusquedaVenta($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsMedicamentoBusqueda.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsMedicamentoBusqueda.data = rpta.datos;
                  });
                  //$scope.mySelectionProductoGrid = [];
                };
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
              }
            });
          } 
          $scope.btnVerFormRegistrarReceta = function () { 
            $scope.fDataREC = {};
            $scope.fDataREC.fTemporal = {};
            $scope.formRecetaMedica = true;
            $scope.fDataREC.fTemporal.cantidad = 1;
          }
          $scope.btnImprimirReceta = function () {
            // console.log('Imprimiendo...', $scope.mySelectionRECGrid[0].idreceta);
            /* --- MODO HTML --- */
            var arrParams = {
              'id': $scope.mySelectionRECGrid[0].idreceta,
              'idatencionmedica': $scope.mySelectionRECGrid[0].acto_medico
            }
            atencionMedicaAmbServices.sImprimirReceta(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                var printContents = rpta.html;
                var popupWin = window.open('', 'windowName', 'width=1270,height=847');
                popupWin.document.open()
                popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
                popupWin.document.close();
              }else { 
                if(rpta.flag == 0) { // ALGO SALIÓ MAL
                  var pTitle = 'Error';
                  var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                  var pType = 'warning';
                }
                
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
              }
            });
          }
          $scope.getMedicamentoAutocomplete = function (value) {
            var params = { 
              searchText: value,
              searchColumn: "UPPER(CONCAT(m.denominacion,' ',m.descripcion,' ',m.idunidadmedida))",
              sensor: false
            };
            return medicamentoServices.sListarMedicamentosAutoComplete(params).then(function(rpta) { 
              $scope.noResultsMEDI = false; 
              if( rpta.flag === 0 ){ 
                $scope.noResultsMEDI = true; 
              } 
              return rpta.datos; 
            });
          }
          $scope.getSelectedMedicamento = function (item, model) { 
            $scope.fDataREC.fTemporal.medicamento.medicamento_stock = $scope.fDataREC.fTemporal.medicamento.medicamento;
            
          } // 
          $scope.agregarMedicamentoAReceta = function () {
            // console.log($scope.fDataREC.fTemporal, ' aaa '); // return false; 
            if( !$scope.fDataREC.fTemporal.medicamento ){
              $scope.fDataREC.fTemporal = {};
              $scope.fDataREC.fTemporal.cantidad = 1;
              $('#fTemporalmedicamento').focus();
              return false;
            }
            // console.log(angular.isObject($scope.fDataREC.fTemporal.medicamento));
            if( !angular.isObject($scope.fDataREC.fTemporal.medicamento) ) { 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione un medicamento del listado para agregar a la receta.', type: 'warning', delay: 2500 });
              $scope.fDataREC.fTemporal = {}; 
              $scope.fDataREC.fTemporal.cantidad = 1; 
              $('#fTemporalmedicamento').focus(); 
              return false; 
            } 
            var medicamentoNew = true;
            angular.forEach($scope.gridOptionsMedicamentosAdd.data, function(value, key) { 
              //console.log(value,$scope.fDataREC.fTemporal.medicamento.id); return false; 
              if(value.medicamento.id == $scope.fDataREC.fTemporal.medicamento.id ){ 
                medicamentoNew = false;
              }
            });
            if( medicamentoNew === false ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El medicamento ya ha sido agregado.', type: 'warning', delay: 2500 });
              $scope.fDataREC.fTemporal = {}; 
              $scope.fDataREC.fTemporal.cantidad = 1; 
              $('#fTemporalmedicamento').focus(); 
              return false; 
            }
            if( !(parseInt($scope.fDataREC.fTemporal.cantidad) > 0) ){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La cantidad debe ser un número mayor de 0.', type: 'warning', delay: 2500 });
              //$scope.fDataREC.fTemporal = {}; 
              $scope.fDataREC.fTemporal.cantidad = 1; 
              $('#fTemporalCantidad').focus(); 
              return false;
            }
            if( parseInt($scope.fDataREC.fTemporal.cantidad) <= 0 ){
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La cantidad debe ser numérica.', type: 'warning', delay: 2500 });
              //$scope.fDataREC.fTemporal = {}; 
              $scope.fDataREC.fTemporal.cantidad = 1; 
              $('#fTemporalCantidad').focus(); 
              return false;
            }
            /*
            if( parseInt($scope.fDataREC.fTemporal.cantidad) > parseInt($scope.fDataREC.fTemporal.medicamento.stock) ){
              console.log('cantidad', $scope.fDataREC.fTemporal.cantidad);
              console.log('stock', $scope.fDataREC.fTemporal.medicamento.stock);
              pinesNotifications.notify({ title: 'Advertencia.', text: 'La cantidad supera el stock.', type: 'warning', delay: 2500 });
              //$scope.fDataREC.fTemporal = {}; 
              $scope.fDataREC.fTemporal.cantidad = 1; 
              $('#fTemporalCantidad').focus(); 
              return false;
            }*/

            $scope.gridOptionsMedicamentosAdd.data.push(angular.copy($scope.fDataREC.fTemporal)); 
            $scope.fDataREC.fTemporal = {}; 
            $scope.fDataREC.fTemporal.cantidad = 1; 
            $('#fTemporalmedicamento').focus();
          }
          $scope.btnQuitarDeLaCestaMED = function (row) {
            var arrParams = row.entity; 
            var index = $scope.gridOptionsMedicamentosAdd.data.indexOf(row.entity); 
            $scope.gridOptionsMedicamentosAdd.data.splice(index,1); 
          }
          $scope.btnAnularMedicamentoReceta = function (row) {
            var arrParams = row.entity; 
            if($scope.fData.boolNumActoMedico){ // SI HAY UNA ATENCION MEDICA CREADA 
              //arrParams.actoMedico = $scope.fData.num_acto_medico;
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  recetaMedicaServices.sEliminarMedicamentoDeReceta(arrParams).then(function (rpta) { 
                    if(rpta.flag == 1){ 
                        var pTitle = 'OK!';
                        var pType = 'success';
                        // var index = $scope.gridOptionsDiagnostico.data.indexOf(row.entity); 
                        // $scope.gridOptionsDiagnostico.data.splice(index,1); 
                      }else if(rpta.flag == 0){ 
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{ 
                        alert('Error inesperado'); 
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 }); 
                      $scope.getPaginationRECServerSide();
                  });
                }
              });
            }
          }
          $scope.registrarRecetaMedica = function () { 
            //$scope.fDataREC.idhistoria = angular.copy($scope.fData.idhistoria);
            $scope.fDataREC.idatencionmedica = angular.copy($scope.fData.num_acto_medico);
            $scope.fDataREC.idhistoria = angular.copy($scope.fData.idhistoria);
            $scope.fDataREC.detalle = angular.copy($scope.gridOptionsMedicamentosAdd.data); 

            recetaMedicaServices.sRegistrarRecetaMedica($scope.fDataREC).then(function (rpta) { 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.gridOptionsMedicamentosAdd.data = [];
                $scope.getPaginationRECServerSide();
                $scope.formRecetaMedica = false;
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Se ha producido un problema. Contacte con el Area de Sistemas');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 }); 
            });
          }
          $scope.btnRegresarAlListadoReceta = function () { 
            $scope.formRecetaMedica = false;
          }

        /* ============================================= */ 
        /*         PESTAÑA DE OTRAS ATENCIONES           */ 
        /* ============================================= */ 
          var desde = moment().subtract(30,'days'); 
          $scope.fBusquedaOAT = {}; 
          $scope.fBusquedaOAT.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fBusquedaOAT.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy'); 
          $scope.getPaginationServerSideOAT(); 
          $scope.btnVerFichaAtencion = function (mySelectionAtencionFichaGrid) { 
            $modal.open({
              templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_ficha_atencion_ambulatoria',
              size: 'xlg',
              scope: $scope,
              backdrop: 'static',
              keyboard:false,
              controller: function ($scope, $modalInstance) {
                
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.fDataFicha = mySelectionAtencionFichaGrid[0];
                $scope.titleForm = 'Ficha de Atención Médica'; 
                /* CARGAMOS LOS DIAGNOSTICOS */ 
                var arrParams = { 
                  'idatencionmedica': $scope.fDataFicha.num_acto_medico
                }; 
                $scope.gridOptionsFichaDiagnostico = { 
                  paginationPageSize: 10,
                  enableRowSelection: false,
                  enableSelectAll: false,
                  enableFiltering: false,
                  enableFullRowSelection: false,
                  enableCellEditOnFocus: true,
                  // minRowsToShow: 6, 
                  data: null,
                  rowHeight: 30,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'codigo_diagnostico', displayName: 'Código', width: '14%' },
                    { field: 'diagnostico', displayName: 'Descripción' },
                    { field: 'tipo', displayName: 'Tipo', width: '18%', editableCellTemplate: 'ui-grid/dropdownEditor', cellFilter: 'mapGender', 
                      editDropdownValueLabel: 'gender', editDropdownOptionsArray: [ 
                        { id: 'DEFINITIVO', gender: 'DEFINITIVO' },
                        { id: 'PRESUNTIVO', gender: 'PRESUNTIVO' }
                      ]
                    }
                  ]
                  ,onRegisterApi: function(gridApiFicha) { 
                    $scope.gridApiFicha = gridApiFicha; 
                  }
                };
                atencionMedicaAmbServices.sListarDiagnosticosDeAtencion(arrParams).then(function (rpta) { 
                  $scope.gridOptionsFichaDiagnostico.data = rpta.datos; 
                });
                                /* CARGAMOS LAS RECETAS MEDICAS */
                var arrParams = { 
                  'idatencionmedica': $scope.fDataFicha.num_acto_medico
                }; 
                $scope.gridOptionsFichaReceta = { 
                  paginationPageSize: 10,
                  enableRowSelection: false,
                  enableSelectAll: false,
                  enableFiltering: false,
                  enableFullRowSelection: false,
                  enableCellEditOnFocus: true,
                  // minRowsToShow: 6, 
                  data: null,
                  rowHeight: 30,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'idreceta', name: 'idreceta', displayName: 'N° Receta', width: '8%', enableCellEdit: false }, 
                    { field: 'medicamento', name: 'medicamento', displayName: 'Medicamento', width: '30%', enableCellEdit: false }, 
                    { field: 'indicaciones', name: 'indicaciones', displayName: 'Indicaciones', enableCellEdit: false },
                    { field: 'cantidad', name: 'cantidad', displayName: 'Cantidad', width: '10%', enableCellEdit: false },
                  ]
                  ,onRegisterApi: function(gridApiFicha) { 
                    $scope.gridApiFicha = gridApiFicha; 
                  }
                };
                atencionMedicaAmbServices.sListarRecetasDeAtencion(arrParams).then(function (rpta) { 
                  $scope.gridOptionsFichaReceta.data = rpta.datos; 
                });
                $scope.getTableHeight = function() { 
                   var rowHeight = 30; // your row height 
                   var headerHeight = 30; // your header height 
                   return { 
                      height: ($scope.gridOptionsFichaReceta.data.length * rowHeight + headerHeight + 30) + "px" 
                   }; 
                };
                $scope.reloadGrid = function () { // console.log('click med');
                  $interval( function() { 
                      $scope.gridApiFicha.core.handleWindowResize();
                  }, 50, 5);
                }
              }
            });
          }

        /*=================================================*/
        /*  AFECCIONES MEDICAS                             */
        /*=================================================*/
          var paginationOptionsAfe = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null
          };
          $scope.gridOptionsAfe={};
          $scope.getPaginationServerSideAfe = function() {
            $scope.datosGrid = {
              paginate : paginationOptionsAfe,
              datos : $scope.fData.idhistoria
            };
            console.log($scope.datosGrid);
            afeccionServices.sListarAfeccionesDePaciente($scope.datosGrid).then(function (rpta) {
              console.log(rpta);
              $scope.gridOptionsAfe.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsAfe.data = rpta.datos;
               
              angular.forEach($scope.gridOptionsAfe.data, function (index,val) {
                
              });
            });
            $scope.mySelectionGridAfe = [];
          };
          $scope.getPaginationServerSideAfe();

      }else{ // PROCEDIMIENTO CLINICO 
        alert('ESTA ATENCION NO ES POR PROCEDIMIENTO.');
      }
    } 
  }])
  .service("atencionProcedimientoServices",function($http, $q) {
    return({
        sRegistrarAtencionProcedimiento: sRegistrarAtencionProcedimiento,
        sEditarAtencionProcedimiento: sEditarAtencionProcedimiento
    });
    function sRegistrarAtencionProcedimiento (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/registrar_atencion_procedimiento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sEditarAtencionProcedimiento (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/editar_atencion_procedimiento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });