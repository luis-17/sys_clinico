angular.module('theme.atencionMedicaAmb', ['theme.core.services','ui.grid.edit','luegg.directives'])
  .controller('atencionMedicaAmbController', ['$scope', '$route', '$filter', '$sce', '$interval', '$location', '$anchorScroll','$modal','$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI',
    'atencionMedicaAmbServices', 
    'diagnosticoServices', 
    'solicitudProcedimientoServices',
    'afeccionServices',
    'clienteServices',
    'recetaMedicaServices',
    'contingenciaServices',
    'medicamentoServices',
    'almacenFarmServices',
    'solicitudExamenServices',
    'solicitudCittServices',
    'tipoProductoServices',
    'odontogramaServices',
    'progMedicoServices',
    'ModalReporteFactory',
    'especialidadServices',
    function($scope, $route, $filter, $sce, $interval, $location, $anchorScroll, $modal, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI,
      atencionMedicaAmbServices,
      diagnosticoServices,
      solicitudProcedimientoServices,
      afeccionServices,
      clienteServices,
      recetaMedicaServices,
      contingenciaServices,
      medicamentoServices,
      almacenFarmServices,
      solicitudExamenServices,
      solicitudCittServices,
      tipoProductoServices,
      odontogramaServices,
      progMedicoServices,
      ModalReporteFactory,
      especialidadServices
    ){ 
    'use strict';
    // console.log('load controller'); 
    $scope.initAtencionMed = function () {
      $scope.tabs = { 
        'estadoAtencionMedica': 'enabled',
        'estadoProcedimiento': 'disabled',
        'estadoReceta': 'disabled',
        'estadoExamenAuxiliar': 'disabled',
        'estadoOtrasAtenciones': 'enabled',
        'estadoCitt': 'disabled',
        'estadoReferencias': 'disabled'
      };

      $scope.fBusqueda = {};
      $scope.fBusquedaPAD = {};
      $scope.fData = {}; // ATENCION MEDICA 

      $scope.fDataProc = {}; // SOLICITUD DE PROCEDIMIENTO
      $scope.fDataREC = {}; // RECETA 
      $scope.fDataREC.fTemporal = {}; 
      $scope.fData.fTemporalDiag = {}; 
      $scope.fDataAUX = {}; // EXAMEN AUXILIAR  
      $scope.fDataCitt={}; // CITT
      $scope.fDataAfe = {} // AFECCIONES
      $scope.glued = false;
      $scope.showOrden = false;
      $scope.showHistoria = false;
      $scope.showPaciente = false;
      $scope.registroFormularioAMA = false;
      $scope.registroFormularioAP = false;
      $scope.gridOptionsDiagnostico = false;
      var datos = {
        tipo_atencion: 'CM'
      }
      atencionMedicaAmbServices.sVerificarTieneProgramacion(datos).then(function (rpta) { 
        $scope.tieneProgramacion = rpta.datos;
        // console.log('$scope.resultado',$scope.resultado);
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
      // Lista de Especialidades
      especialidadServices.sListarEspecialidadesCbo().then(function (rpta) { 
        $scope.listaEspecialidades = rpta.datos;
        $scope.listaEspecialidades.splice(0,0,{id : '0', descripcion : '--Seleccione Especialidad--'});    
      });
      // $scope.reloadGrid = function () { 
      //   $interval( function() { 
      //       $scope.gridApiPAD.core.handleWindowResize();
      //       $scope.gridApiPROC.core.handleWindowResize();
      //       $scope.gridApi.core.handleWindowResize();
      //       $scope.gridApiOAT.core.handleWindowResize(); // OTRAS ATENCIONES 
      //   }, 50, 5);
      // }
      // $scope.reloadGrid();
      $scope.verAccionDiagnostico = function () {
        console.log('Dia');
        $scope.gridOptionsDiagnostico.columnDefs[3].visible = true;
        $scope.formSolicitudCitt = false;
      }
      $scope.AgregaDias= function($datos) {
        var datosfec = {};
        datosfec.fecha_inicio=$scope.fDataCitt.fecha_inicio ;
        datosfec.dias = parseInt($scope.fDataCitt.dias) - 1;
        atencionMedicaAmbServices.sAgregarDias(datosfec).then(function (rpta) { 
          $scope.fDataCitt.fecha_final = rpta;
        });
      }
      $scope.onChangeFiltroBusqueda = function () { 
        if( $scope.fBusqueda.tipoBusqueda === 'PNO' ){ // N° ORDEN 
          $scope.showOrden = true;
          $scope.showHistoria = false;
          $scope.showPaciente = false;
        }
        else if( $scope.fBusqueda.tipoBusqueda === 'PP' ){ // PACIENTE 
          $scope.showOrden = false;
          $scope.showHistoria = false;
          $scope.showPaciente = true;
        }
        else if( $scope.fBusqueda.tipoBusqueda === 'PH' ){ // HISTORIA 
          $scope.showOrden = false;
          $scope.showHistoria = true;
          $scope.showPaciente = false;
        }
        else if( $scope.fBusqueda.tipoBusqueda === 'PPG' ){ // PROGRAMACION 
          $scope.showOrden = false;
          $scope.showHistoria = false;
          $scope.showPaciente = false;
          $scope.verPacientesPorProgramacion();
        }
      }
      $scope.listaBoolAfeccion = [ 
        { id : 1, descripcion: 'ENFERMEDAD' }, 
        { id : 2, descripcion: 'ALERGIA' }
      ];
      $scope.fDataAfe.tipoAfeccion = $scope.listaBoolAfeccion[0].id;
      // MODAL PARA PACIENTES PROGRAMADOS
      $scope.verPacientesPorProgramacion = function(){
        $uibModal.open({
          templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_por_programacion',
          size: 'lg',
          scope: $scope,
          controller: function ($scope, $modalInstance) { 
            // lista de programaciones
            $scope.fData = {};
            $scope.fCountTotales = {};
            var datos ={
              tipo_atencion_medica: 'CM' 
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
            });
            $scope.titleForm = 'Pacientes Programados para Consultas';
            $scope.fData.fecha = $filter('date')(new Date(),'dd-MMMM-yyyy');
            var paginationOptionsProg = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 100,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsProg = {
              rowHeight: 36,
              paginationPageSizes: [100, 500, 1000],
              paginationPageSize: 500,
              useExternalPagination: false,
              useExternalSorting: false,
              enableGridMenu: false,
              enableRowSelection: false,
              enableSelectAll: false,
              enableFiltering: false,
              enableFullRowSelection: false,
              multiSelect: false,
              columnDefs: [
                { field: 'numero_cupo', name: 'numero_cupo', displayName: 'N° CUPO', width: 70, enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    var clase = '';
                    if (row.entity.si_adicional == 1) {
                      clase = clase + 'text-red ';
                    }

                    if(row.entity.idcanal == 3){
                      clase = clase + 'paciente-web ';
                    }

                    return clase;
                  }
                },
                { field: 'turno', name: 'turno', displayName: 'TURNO', width: 140, enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }
                },
                { field: 'paciente', name: 'paciente', displayName: 'PACIENTE', enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }
                },
                { field: 'celular', name: 'celular', displayName: 'CELULAR', width: 120, visible: false, enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }
                },
                { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', visible: false, enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }
                },
                { field: 'producto', name: 'descripcion', displayName: 'PRODUCTO', visible: false, enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }
                },
                { field: 'email', name: 'email', displayName: 'EMAIL', visible: false, enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }
                },
                { field: 'estado_cita_str', type:'object', name: 'estado_cita_str', displayName: 'ESTADO', width:'100', enableSorting:false,
                  cellClass: function(grid, row, col, rowRenderIndex, colRenderIndex) {
                    if(row.entity.idcanal == 3)
                      return 'paciente-web';
                  }, 
                  cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>', visible:false
                }, 
                { field: 'accion', displayName: 'Acción', enableCellEdit: false, enableFiltering: false, width: 120,
                  cellTemplate:'<button type="button" style="width: 90%;" class="btn btn-sm btn-info mt-sm center-block" ng-click="grid.appScope.btnCargarPacienteAtendido(row)" ng-if="row.entity.estado_cita_str.estado == 5"> VER ATENCION </button>' +
                    '<button type="button" style="width: 90%;" class="btn btn-sm btn-warning mt-sm center-block" ng-click="grid.appScope.btnCargarPaciente(row)" ng-if="row.entity.estado_cita_str.estado == 2"> ATENDER </button>' +
                    '<button type="button" style="width: 90%;" class="btn btn-sm btn-danger mt-sm center-block" ng-click="grid.appScope.btnCargarPaciente(row)" ng-if="row.entity.estado_cita_str.estado == 3"> NOTA DE CRÉDITO </button>'
                }
              ],
              onRegisterApi: function(gridApi) { 
                $scope.gridApi = gridApi; 
              }
            }; 
            paginationOptionsProg.sortName = $scope.gridOptionsProg.columnDefs[0].name;

            $scope.getPaginationProgServerSide = function() {
              blockUI.start();
              var arrParams = { 
                idprogmedico : $scope.fData.programacion,
                tipo_atencion : 'CM'
              };
              console.log($scope.fData.programacion);
              progMedicoServices.sListarPacientesProgramadosParaConsulta(arrParams).then(function (rpta) { 
                if (rpta.flag == 1 ){
                  $scope.gridOptionsProg.data = rpta.datos;
                }
                $scope.fCountTotales = rpta.contadores;  
                blockUI.stop();
              });
            };

            $scope.btnCargarPaciente = function(row){ 
              if(row.entity.estado_cita_str.estado === 3){ // NO AUTORIZADO 
                pinesNotifications.notify({ title: 'Bloqueo de Atención', text: 'Esta atención ha sido bloqueada.', type: 'danger', delay: 3000 }); 
                return false;
              }
              blockUI.start('Cargando información.');
              atencionMedicaAmbServices.sListarPacienteProgramadoSinAtender(row.entity).then(function (rpta) { 
                $scope.resultado = rpta.datos;
                $scope.mySelectionGrid = rpta.datos;
                $scope.btnAtenderAlPaciente('no',$scope.resultado);
                $modalInstance.dismiss('cancel');
                blockUI.stop();
              });
            }
            $scope.btnCargarPacienteAtendido = function(row){
              blockUI.start('Cargando información.');
              atencionMedicaAmbServices.sListarPacienteProgramadoAtendido(row.entity).then(function (rpta) { 
                $scope.resultado = rpta.datos;
                $scope.btnAtenderAlPaciente('si',$scope.resultado);
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

      /* GRILLA DE PACIENTES POR ATENDER */ 
      $scope.mySelectionGrid = [];
      $scope.gridOptionsPPA = { 
        paginationPageSizes: [20, 50, 100],
        paginationPageSize: 20,
        enableRowSelection: true,
        enableGridMenu: true,
        minRowsToShow: 6,
        data: [],
        enableFiltering: false,
        enableFullRowSelection: true,
        enableSelectAll: false,
        multiSelect: false,
        columnDefs: [
          { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '10%' },
          { field: 'ticket', name: 'ticket_venta', displayName: 'N° TICKET', width: '12%', visible: false },
          { field: 'idhistoria', name: 'idhistoria', displayName: 'HIST.', width: '5%' },
          { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '25%' },
          { field: 'edad', name: 'edad', displayName: 'EDAD', width: '5%' },
          { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width: '12%' },
          { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PRODUCTO', width: '12%' },
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
          // if( $scope.fBusqueda.paciente ){ 
          //   validateButton = true;
          // }
          $scope.verPacientesPorProgramacion();
          return;
        }
        if( validateButton ){ 
          // PACIENTES SIN ATENDER 
          $scope.fBusqueda.arrTipoProductos = [12]; // CONSULTAS 
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
      $scope.btnToggleFilteringPAD = function(){ console.log('ing me');
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
        $scope.fBusquedaPAD.idespecialidad = $scope.fSessionCI.idespecialidad;
        atencionMedicaAmbServices.sListarPacientesAtendidos($scope.fBusquedaPAD).then(function (rpta) { 
          $scope.gridOptionsPAD.data = rpta.datos;
        });
      };
      $scope.getPaginationServerSidePAD();
      $scope.getPacienteAutocomplete = function (value) { 
        var params = { 
          searchText: value,
          searchColumn: "UPPER(CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno))",
          sensor: false,
          arrTipoProductos: [12]
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
      /* TIPO PRODUCTO */ 
      // tipoProductoServices.sListarTipoProductoCbo().then(function (rpta) { 
        /*
          CM: consulta medica; P: procedimiento; EA: examen auxiliar;  DO: documentos
        */
      $scope.listaTipoAtencionMedica = [ 
        { 'id': 'ALL', 'descripcion': '--TODOS--' }, 
        { 'id': 'CM', 'descripcion': 'CONSULTA MEDICA' }, 
        { 'id': 'P', 'descripcion': 'PROCEDIMIENTO' }, 
        { 'id': 'EA', 'descripcion': 'EXAMEN AUXILIAR' }, 
        { 'id': 'DO', 'descripcion': 'DOCUMENTO' } 
      ]; 
      $scope.fBusquedaPAD.idTipoAtencion = 'CM';

      $scope.btnRegresarAlInicio = function () { 
        //$route.reload();
        $scope.registroFormularioAMA = false; 
        $scope.registroFormularioAP = false; 
        // $scope.reloadGrid(); 
        $scope.verAccionDiagnostico();
        $scope.fBusqueda.paciente = null;
        $scope.fBusqueda.numeroOrden = null;
        if($scope.fBusqueda.tipoBusqueda == 'PPG'){
          $scope.verPacientesPorProgramacion();
        }
      }
      $scope.gridOptionsDiagnostico = { 
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
          { field: 'codigo_diagnostico', displayName: 'Código', maxWidth: 90, enableCellEdit: false },
          { field: 'diagnostico', displayName: 'Descripción', enableCellEdit: false },
          { field: 'tipo', displayName: 'Tipo', maxWidth: 120, editableCellTemplate: 'ui-grid/dropdownEditor', width: '20%', cellFilter: 'mapGender', 
            editDropdownValueLabel: 'gender', editDropdownOptionsArray: [ 
              { id: 'DEFINITIVO', gender: 'DEFINITIVO' },
              { id: 'PRESUNTIVO', gender: 'PRESUNTIVO' }
            ]
          },
          {   field: 'accion', displayName: 'Acción', maxWidth: 95, enableCellEdit: false, 
              cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
                    
        ],
        onRegisterApi: function(gridApiCombo) { 
          $scope.gridApiCombo = gridApiCombo; 
        }
      }; 
      $scope.btnQuitarDeLaCesta = function (row) { 
        var arrParams = row.entity;
        if($scope.fData.boolNumActoMedico){ // SI HAY UNA ATENCION MEDICA CREADA 
          arrParams.actoMedico = $scope.fData.num_acto_medico;
          var pMensaje = '¿Realmente desea realizar la acción?';
          $bootbox.confirm(pMensaje, function(result) {
            if(result){
              atencionMedicaAmbServices.sEliminarDiagnosticoDeAtencionMedica(arrParams).then(function (rpta) { 
                if(rpta.flag == 1){ 
                      var pTitle = 'OK!';
                      var pType = 'success';
                      var index = $scope.gridOptionsDiagnostico.data.indexOf(row.entity); 
                    $scope.gridOptionsDiagnostico.data.splice(index,1); 
                  }else if(rpta.flag == 0){ 
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{ 
                    alert('Error inesperado'); 
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              });
            }
          });

        }else{
          var index = $scope.gridOptionsDiagnostico.data.indexOf(row.entity); 
          $scope.gridOptionsDiagnostico.data.splice(index,1); 
        }
      }
      $scope.gotoBottom = function() { 
          $location.hash('final');
          $anchorScroll();
      };
      $scope.agregarDiagnosticoACesta = function () { 
        if( !$scope.fData.fTemporalDiag.diagnostico){ 
          $scope.fData.fTemporalDiag = {}; 
          return false; 
        }
        $scope.glued = true;
        var diagnosticoNew = true;
        angular.forEach($scope.gridOptionsDiagnostico.data, function(value, key) { 
          if(value.id == $scope.fData.fTemporalDiag.iddiagnostico ){ 
            diagnosticoNew = false;
          }
        });
        if( diagnosticoNew === false ){ 
          pinesNotifications.notify({ title: 'Advertencia.', text: 'El diagnóstico ya ha sido agregado.', type: 'warning', delay: 2000 });
          $scope.fData.fTemporalDiag = {}; 
          return false; 
        } 
        $scope.gridOptionsDiagnostico.data.push(angular.copy($scope.fData.fTemporalDiag)); 
        $scope.fData.fTemporalDiag = {}; 
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
          { field: 'id', name: 'idsolicitudprocedimiento', displayName: 'N° Solicitud', width: '5%',  sort: { direction: uiGridConstants.DESC}, enableCellEdit: false }, 
          { field: 'producto', name: 'producto', displayName: 'Procedimiento', width: '25%', enableCellEdit: false }, 
          { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '6%', enableCellEdit: true }, 
          { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'Fecha Solicitud', width: '18%', enableCellEdit: false }, 
          { field: 'fecha_realizacion', name: 'fecha_realizacion', displayName: 'Fecha Realizado', width: '18%', enableCellEdit: false }, 
          { field: 'acto_medico', name: 'idatencionmedica', displayName: 'Acto Médico', width: '10%', enableCellEdit: false }, 
          { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', width: '13%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
            cellTemplate:'<div class="text-center"><label tooltip-placement="left" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i> {{ COL_FIELD.labelText }} </label></div>' 
          },
          { field: 'accion', displayName: 'Acción', enableCellEdit: false, 
            cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnAnularSolicitudProcedimiento(row)"> <i class="fa fa-trash"></i> </button>' 
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

    /* ================== */
    /* LISTADO DE RECETAS */
    /* ================== */
      var paginationOptionsREC = { 
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      $scope.mySelectionRECGrid = [];
      $scope.btnToggleFiltering = function(){ 
        $scope.gridOptionsRecetaMedica.enableFiltering = !$scope.gridOptionsRecetaMedica.enableFiltering; 
        $scope.gridApiREC.core.notifyDataChange( uiGridConstants.dataChange.COLUMN ); 
      };
      $scope.gridOptionsRecetaMedica = { 
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
        multiSelect: false,
        data: null,
        columnDefs: [ 
          
          { field: 'fecha', name: 'fecha_receta', displayName: 'Fecha', width: '14%', enableCellEdit: false }, 
          { field: 'acto_medico', name: 'idatencionmedica', displayName: 'Acto Médico', width: '10%', enableCellEdit: false }, 
          { field: 'idreceta', name: 'idreceta', displayName: 'N° Receta', width: '8%', enableCellEdit: false }, 
          { field: 'medicamento', name: 'medicamento', displayName: 'Medicamento', enableCellEdit: false }, 
          //{ field: 'presentacion', name: 'presentacion', displayName: 'Presentación', width: '10%', enableCellEdit: false },
          { field: 'formafarmaceutica', name: 'formafarmaceutica', displayName: 'Forma Farm.', width: '10%', enableCellEdit: false }, 
          { field: 'cantidad', name: 'cantidad', displayName: 'Cantidad', width: '10%', enableCellEdit: false },
          { field: 'accion', displayName: 'Acción', enableCellEdit: false, width: '8%', 
            cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnAnularMedicamentoReceta(row)"> <i class="fa fa-trash"></i> </button>' 
          }
        ],
        onRegisterApi: function(gridApiREC) {
          $scope.gridApiREC = gridApiREC;
          gridApiREC.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionRECGrid = gridApiREC.selection.getSelectedRows();
          });
          gridApiREC.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionRECGrid = gridApiREC.selection.getSelectedRows();
          });
          $scope.gridApiREC.core.on.sortChanged($scope, function(grid, sortColumns) {
            if (sortColumns.length == 0) {
              paginationOptionsREC.sort = null;
              paginationOptionsREC.sortName = null;
            } else {
              paginationOptionsREC.sort = sortColumns[0].sort.direction;
              paginationOptionsREC.sortName = sortColumns[0].name;
            }
            $scope.getPaginationRECServerSide();
          });
          $scope.gridApiREC.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
            console.log(newPage, pageSize);
            paginationOptionsREC.pageNumber = newPage;
            paginationOptionsREC.pageSize = pageSize;
            paginationOptionsREC.firstRow = (paginationOptionsREC.pageNumber - 1) * paginationOptionsREC.pageSize;
            $scope.getPaginationRECServerSide();
          });
        }
      };
      paginationOptionsREC.sortName = $scope.gridOptionsRecetaMedica.columnDefs[0].name;
      $scope.getPaginationRECServerSide = function() { 
        $scope.datosGrid = { 
          paginate : paginationOptionsREC,
          datos : $scope.fBusquedaREC 
        }; 
        $scope.datosGrid.datos.idhistoria = angular.copy($scope.fData.idhistoria);
        recetaMedicaServices.sListarRecetasDePaciente($scope.datosGrid).then(function (rpta) { 
          $scope.gridOptionsRecetaMedica.totalItems = rpta.paginate.totalRows;
          $scope.gridOptionsRecetaMedica.data = rpta.datos;
        });
        $scope.mySelectionRECGrid = [];
      };
    /* AGREGADO DE MEDICAMENTOS */
      $scope.gridOptionsMedicamentosAdd = { 
        paginationPageSize: 10,
        enableRowSelection: false,
        enableSelectAll: false,
        enableFiltering: false,
        enableFullRowSelection: false,
        enableCellEditOnFocus: true,
        data: null,
        rowHeight: 30,
        multiSelect: false,
        columnDefs: [
          { field: 'medicamento.medicamento', displayName: 'Medicamento', width: '35%', enableCellEdit: false, type:'object' },
          //{ field: 'medicamento.presentacion', displayName: 'Presentación', enableCellEdit: false, visible:false },
          { field: 'medicamento.formafarmaceutica', displayName: 'Forma Farm.', enableCellEdit: false, visible:true },
          { field: 'cantidad', displayName: 'Cantidad', enableCellEdit: false },
          { field: 'indicacion', displayName: 'Indicaciones', width: '30%', enableCellEdit: false },
          { field: 'accion', displayName: 'Acción', enableCellEdit: false, 
            cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCestaMED(row)"> <i class="fa fa-trash"></i> </button>' 
          }
        ]
        ,onRegisterApi: function(gridApiMEDADD) { 
          $scope.gridApiMEDADD = gridApiMEDADD; 
        }
      }; 

   

    /* ============================== */
    /* LISTADO DE EXAMENES AUXILIARES */
    /* ============================== */
      var paginationOptionsAUX = { 
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      $scope.mySelectionAUXGrid = [];
      $scope.btnToggleFiltering = function(){ 
        $scope.gridOptionsExamenAuxiliar.enableFiltering = !$scope.gridOptionsExamenAuxiliar.enableFiltering; 
        $scope.gridApiAUX.core.notifyDataChange( uiGridConstants.dataChange.COLUMN ); 
      };
      $scope.gridOptionsExamenAuxiliar = { 
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        minRowsToShow: 6,
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
          { field: 'id', name: 'idsolicitudexamen', displayName: 'N° SOLICITUD', width: '8%', enableCellEdit: false }, 
          { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO EXAMEN', width: '12%', enableCellEdit: false }, 
          { field: 'especialidad', name: 'es.nombre', displayName: 'ESPECIALIDAD', width: '20%', enableCellEdit: false }, 
          { field: 'producto', name: 'pm.descripcion', displayName: 'EXAMEN', width: '25%', enableCellEdit: false }, 
          { field: 'indicaciones', name: 'indicaciones', displayName: 'INDICACIONES', width: '25%', enableCellEdit: false, visible: false }, 
          { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA SOLICITUD', width: '12%', enableCellEdit: false }, 
          { field: 'fecha_realizacion', name: 'fecha_realizacion', displayName: 'FECHA RESULTADO', width: '12%', enableCellEdit: false },
          { field: 'accion', displayName: 'ACCION', enableCellEdit: false, width: '6%', 
            cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnAnularExamenAuxiliar(row)"> <i class="fa fa-trash"></i> </button>' 
          }
        ],
        onRegisterApi: function(gridApiAUX) {
          $scope.gridApiAUX = gridApiAUX;
          gridApiAUX.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionAUXGrid = gridApiAUX.selection.getSelectedRows();
          });
          gridApiAUX.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionAUXGrid = gridApiAUX.selection.getSelectedRows();
          });
          $scope.gridApiAUX.core.on.sortChanged($scope, function(grid, sortColumns) {
            if (sortColumns.length == 0) {
              paginationOptionsAUX.sort = null;
              paginationOptionsAUX.sortName = null;
            } else {
              paginationOptionsAUX.sort = sortColumns[0].sort.direction;
              paginationOptionsAUX.sortName = sortColumns[0].name;
            }
            $scope.getPaginationAUXServerSide();
          });
          $scope.gridApiAUX.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
            console.log(newPage, pageSize);
            paginationOptionsAUX.pageNumber = newPage;
            paginationOptionsAUX.pageSize = pageSize;
            paginationOptionsAUX.firstRow = (paginationOptionsAUX.pageNumber - 1) * paginationOptionsAUX.pageSize;
            $scope.getPaginationAUXServerSide();
          });
        }
      };
      paginationOptionsAUX.sortName = $scope.gridOptionsExamenAuxiliar.columnDefs[0].name;
      $scope.getPaginationAUXServerSide = function() { 
        $scope.datosGrid = { 
          paginate : paginationOptionsAUX,
          datos : $scope.fBusquedaAUX 
        }; 
        $scope.datosGrid.datos.idhistoria = angular.copy($scope.fData.idhistoria);
        solicitudExamenServices.sListarSolicitudesExamenDePaciente($scope.datosGrid).then(function (rpta) { 
          $scope.gridOptionsExamenAuxiliar.totalItems = rpta.paginate.totalRows;
          $scope.gridOptionsExamenAuxiliar.data = rpta.datos;
        });
        $scope.mySelectionAUXGrid = [];
      };
    /* ==========================*/
    /* LISTADO DE CITT           */
    /* ==========================*/
      var paginationOptionsCITT = { 
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      $scope.mySelectionCITTGrid = [];
        $scope.btnToggleFiltering = function(){ 
        $scope.gridOptionsCitt.enableFiltering = !$scope.gridOptionsCitt.enableFiltering; 
        $scope.gridApiCITT.core.notifyDataChange( uiGridConstants.dataChange.COLUMN ); 
      };
      $scope.gridOptionsCitt = { 
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
          { field: 'id', name: 'idsolicitudcitt', displayName: 'ID', width: '5%',  sort: { direction: uiGridConstants.DESC}, enableCellEdit: false }, 
          { field: 'tipoatencion', name: 'tipoatencion', displayName: 'Tipo Atencion', width: '20%', enableCellEdit: false }, 
          { field: 'contingencia', name: 'contingencia', displayName: 'Contingencia', width: '19%', enableCellEdit: true }, 
          { field: 'fecha_otorgamiento', name: 'fecha_otorgamiento', displayName: 'Fec.Otorg.', width: '12%', enableCellEdit: false }, 
          { field: 'fecha_inicio', name: 'fecha_inicio', displayName: 'Fec.Inicio', width: '12%', enableCellEdit: false }, 
          { field: 'dias', name: 'dias', displayName: 'Dias', width: '8%', enableCellEdit: false }, 
          { field: 'acto_medico', name: 'idatencionmedica', displayName: 'Acto Médico', width: '10%', enableCellEdit: false }, 
          { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', width: '9%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
            cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCestaCITT(row)"> <i class="fa fa-trash"></i> </button>' 
          }
        ],
        onRegisterApi: function(gridApiCITT) {
          $scope.gridApiCITT = gridApiCITT;
          gridApiCITT.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionCITTGrid = gridApiCITT.selection.getSelectedRows();
          });
          gridApiCITT.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionCITTGrid = gridApiCITT.selection.getSelectedRows();
          });
          $scope.gridApiCITT.core.on.sortChanged($scope, function(grid, sortColumns) {
            if (sortColumns.length == 0) {
              paginationOptionsCITT.sort = null;
              paginationOptionsCITT.sortName = null;
            } else {
              paginationOptionsCITT.sort = sortColumns[0].sort.direction;
              paginationOptionsCITT.sortName = sortColumns[0].name;
            }
            $scope.getPaginationCITTServerSide();
          });
          $scope.gridApiCITT.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
            //console.log(newPage, pageSize);
            paginationOptionsCITT.pageNumber = newPage;
            paginationOptionsCITT.pageSize = pageSize;
            paginationOptionsCITT.firstRow = (paginationOptionsCITT.pageNumber - 1) * paginationOptionsCITT.pageSize;
            $scope.getPaginationCITTServerSide();
          });
        }
      };
      paginationOptionsCITT.sortName = $scope.gridOptionsCitt.columnDefs[0].name;
      $scope.getPaginationCITTServerSide = function() {
        $scope.datosGrid = { 
          paginate : paginationOptionsCITT, 
          datos : $scope.fBusquedaCITT 
        }; 
        $scope.datosGrid.datos.idhistoria = angular.copy($scope.fData.idhistoria);
        console.log($scope.datosGrid);

        solicitudCittServices.sListarCittDePaciente($scope.datosGrid).then(function (rpta) { 
          console.log(rpta);
          $scope.gridOptionsCitt.totalItems = rpta.paginate.totalRows;
          $scope.gridOptionsCitt.data = rpta.datos;
        });
        $scope.mySelectionCITTGrid = [];
      };
    /* ============================================*/
    /*  ODONTOGRAMA INICIAL                        */
    /* ============================================*/
      $scope.btnOdontogramaInicial = function(size,historia){
        $uibModal.open({
          templateUrl: angular.patchURLCI+'atencionOdontologica/ver_odontograma',
          size: size || '',
          scope: $scope,
          controller: function ($scope, $modalInstance, arrToModal) {
            // $scope.fData = {};
            $scope.titleForm = 'Odontograma Inicial';
            var params = {
              idhistoria: historia,
              tipo_odontograma: 'inicial'

            }

            odontogramaServices.sListarOdontogramaInicial(params).then(function (rpta) {
              $scope.listaOdontograma = rpta.datos;
              $scope.fData.piezasPerdidasPermanentes =  $scope.listaOdontograma.perdidaspermanentes;
              $scope.fData.piezasCariadasPermanentes = $scope.listaOdontograma.cariespermanentes;
              $scope.fData.piezasObturadasPermanentes = $scope.listaOdontograma.obturadaspermanentes;
              $scope.fData.piezasPerdidasDeciduas = $scope.listaOdontograma.perdidasdeciduas;
              $scope.fData.piezasCariadasDeciduas = $scope.listaOdontograma.cariesdeciduas;
              $scope.fData.piezasObturadasDeciduas = $scope.listaOdontograma.obturadasdeciduas;
              $scope.fData.observaciones = $scope.listaOdontograma.observaciones;

              $scope.fData.totalPermanentes = parseInt($scope.fData.piezasPerdidasPermanentes) + parseInt($scope.fData.piezasCariadasPermanentes) + parseInt($scope.fData.piezasObturadasPermanentes);
              $scope.fData.totalDeciduas = parseInt($scope.fData.piezasPerdidasDeciduas) + parseInt($scope.fData.piezasCariadasDeciduas) + parseInt($scope.fData.piezasObturadasDeciduas);
              $scope.fData.totalPiezasEvaluadas = parseInt($scope.fData.totalPermanentes) + parseInt($scope.fData.totalDeciduas);
            });
            odontogramaServices.sListarEstadoDentalCbo().then(function (rpta) {
              $scope.listaEstadoDental = rpta.datos;
              $scope.fData.estadopiezadental = $scope.listaEstadoDental[0];
              
            });
          

            $scope.marcarPieza = function(cuadrante,pieza, index){
              var estado = $scope.fData.estadopiezadental;
              var arrPieza = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id];
              // var mandibulaSup = ['11', '12', '13', '14',  '15', '16', '17', '18', '21', '22', '23', '24',  '25', '26', '27', '28'];
              // var mandibulaInf = ['31', '32', '33', '34',  '35', '36', '37', '38', '41', '42', '43', '44',  '45', '46', '47', '48'];
              if(estado.tipo == 1){ // verifica si es un estado que abarca todo la pieza dental

                if((estado.id == 11 || estado.id == 12) && (cuadrante == 0 || cuadrante == 1)){
                  if(arrPieza.zonas[0].estados.length === 0){
                    // for( i in mandibulaSup){
                    //   pd = mandibulaSup[i];
                    //   $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pd].marca = 1;

                    //   $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pd].zonas[0].estados.splice(0,0,estado);
                    // }
                     for (var pd = 11; pd <= 18; pd++) {
                      $scope.listaOdontograma.cuadrantes[0].piezas[pd].marca = 1;

                      $scope.listaOdontograma.cuadrantes[0].piezas[pd].zonas[0].estados.splice(0,0,estado);
                    };
                    for (var pd = 21; pd <= 28; pd++) {
                      $scope.listaOdontograma.cuadrantes[1].piezas[pd].marca = 1;
                      $scope.listaOdontograma.cuadrantes[1].piezas[pd].zonas[0].estados.splice(0,0,estado);
                    };
                  }

                }else if((estado.id == 11 || estado.id == 12) && (cuadrante == 6 || cuadrante == 7)){
                  if(arrPieza.zonas[0].estados.length === 0){
                    
                    for (var pd = 31; pd <= 38; pd++) {
                      $scope.listaOdontograma.cuadrantes[7].piezas[pd].marca = 1;
                      $scope.listaOdontograma.cuadrantes[7].piezas[pd].zonas[0].estados.splice(0,0,estado);
                    };
                    for (var pd = 41; pd <= 48; pd++) {
                      $scope.listaOdontograma.cuadrantes[6].piezas[pd].marca = 1;
                      $scope.listaOdontograma.cuadrantes[6].piezas[pd].zonas[0].estados.splice(0,0,estado);
                    };
                  }
                }
                else if(estado.id != 11 && estado.id != 12){
                  if(arrPieza.zonas[0].estados.length === 0){
                    arrPieza.marca = 1;
                    arrPieza.zonas[0].estados.splice(0,0,estado);
                    if(arrPieza.zonas[0].estados[0].descripcion == 'AUSENTE'){
                      if(cuadrante == 0 || cuadrante == 1 || cuadrante == 6 || cuadrante == 7){
                        $scope.fData.piezasPerdidasPermanentes++;
                      }else{
                        $scope.fData.piezasPerdidasDeciduas++;
                      };
                    };
                  }
                }

              }else if(estado.tipo == 2){ // verifica si es un estado que implica una zona de la pieza
                $scope.marcarZonaPieza(cuadrante,pieza,index,estado);
              }
              $scope.calcularTotales(); 
            }
            $scope.desmarcar = function (cuadrante, pieza) {
              var listaEstado = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id].zonas[0].estados[0];
              var arrPieza = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id];
              if((listaEstado.id == 11 || listaEstado.id == 12) && (cuadrante == 0 || cuadrante == 1)){
                for (var pd = 11; pd <= 18; pd++) {
                  $scope.listaOdontograma.cuadrantes[0].piezas[pd].marca = 0;
                  // for (var i = 0; i <= 4; i++) {
                  //   $scope.listaOdontograma.cuadrantes[0].piezas[pd].zonas[i].estados.splice(0,1);
                  // };
                  $scope.listaOdontograma.cuadrantes[0].piezas[pd].zonas[0].estados.splice(0,1);
                };
                for (var pd = 21; pd <= 28; pd++) {
                  $scope.listaOdontograma.cuadrantes[1].piezas[pd].marca = 0;
                  // for (var i = 0; i <= 4; i++) {
                  //   $scope.listaOdontograma.cuadrantes[1].piezas[pd].zonas[i].estados.splice(0,1);
                  // };
                  $scope.listaOdontograma.cuadrantes[1].piezas[pd].zonas[0].estados.splice(0,1);
                };
              }else if((listaEstado.id == 11 || listaEstado.id == 12) && (cuadrante == 6 || cuadrante == 7)){
                for (var pd = 31; pd <= 38; pd++) {
                  $scope.listaOdontograma.cuadrantes[7].piezas[pd].marca = 0;
                  // for (var i = 0; i <= 4; i++) {
                  //   $scope.listaOdontograma.cuadrantes[7].piezas[pd].zonas[i].estados.splice(0,1);
                  // };
                  $scope.listaOdontograma.cuadrantes[7].piezas[pd].zonas[0].estados.splice(0,1);
                };
                for (var pd = 41; pd <= 48; pd++) {
                  $scope.listaOdontograma.cuadrantes[6].piezas[pd].marca = 0;
                  // for (var i = 0; i <= 4; i++) {
                  //   $scope.listaOdontograma.cuadrantes[6].piezas[pd].zonas[i].estados.splice(0,1);
                  // };
                  $scope.listaOdontograma.cuadrantes[6].piezas[pd].zonas[0].estados.splice(0,1);
                };
              }else if(listaEstado.id != 11 && listaEstado.id != 12){
                arrPieza.marca = 0;
                if(arrPieza.zonas[0].estados[0].descripcion == 'AUSENTE'){
                      if(cuadrante == 0 || cuadrante == 1 || cuadrante == 6 || cuadrante == 7){
                        $scope.fData.piezasPerdidasPermanentes--;
                      }else{
                        $scope.fData.piezasPerdidasDeciduas--;
                      }
                    }
                for (var i = 0; i <= 4; i++) {
                  arrPieza.zonas[i].estados.splice(0,1);
                };
              }
              $scope.calcularTotales();
            }
            $scope.marcarZonaPieza = function(cuadrante,pieza,index,estado){
              $uibModal.open({
                templateUrl: angular.patchURLCI+'odontograma/ver_pieza_dental',
                size: 'sm',
                scope: $scope,
                controller: function ($scope, $modalInstance, arrToModal){
                  // $scope.fData = {};
                  $scope.cuadrante = cuadrante;
                  $scope.pieza = pieza;
                  $scope.estado = estado;

                  $scope.titleForm = 'Editar Pieza Dental: ' + pieza.id;
                  var boolEdicion = '';
                  if(pieza.marca == 0){
                    boolEdicion = false;
                  }else {
                    boolEdicion = true;
                  }

                  $scope.marcarZona = function(cuadrante,pieza,index,estado){
                    var arrZona = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id].zonas[index];
                    if(arrZona.estados.length > 0){
                      arrZona.estados.splice(0,1);
                    }else{
                      arrZona.estados.splice(0,0,estado);
                      // console.log($scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id].zonas[index].estados[0].simbolo);
                    }
                  };

                  $scope.aceptar = function(cuadrante,pieza){
                    var arrPieza = $scope.listaOdontograma.cuadrantes[cuadrante].piezas[pieza.id];
                    var caries = false;
                    var obturada = false;
                    
                    for (var i = 0; i <= 4; i++) {
                      if(arrPieza.zonas[i].estados.length > 0 ){
                        arrPieza.marca = 1;
                        if(arrPieza.zonas[i].estados[0].descripcion == 'CARIADA'){
                          caries = true;
                          obturada = false;
                          break;
                        }else if(arrPieza.zonas[i].estados[0].descripcion == 'OBTURADA'){
                          obturada = true;
                        }
                      }else{
                        arrPieza.marca = 0;
                      }
                    };
                    if(!boolEdicion){
                      if(caries){
                        if(cuadrante == 0 || cuadrante == 1 || cuadrante == 6 || cuadrante == 7){
                          $scope.fData.piezasCariadasPermanentes++;
                        }else{
                          $scope.fData.piezasCariadasDeciduas++;
                        }
                      }
                      if(obturada){
                        if(cuadrante == 0 || cuadrante == 1 || cuadrante == 6 || cuadrante == 7){
                          $scope.fData.piezasObturadasPermanentes++;
                        }else{
                          $scope.fData.piezasObturadasDeciduas++;
                        }
                      }
                    }else{
                      if(arrPieza.marca == 0){
                        console.log('Se descontará un indice');
                      }
                    }
                    
                    $scope.calcularTotales();
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
            $scope.calcularTotales = function(){
              $scope.fData.totalPermanentes = parseInt($scope.fData.piezasPerdidasPermanentes) + parseInt($scope.fData.piezasCariadasPermanentes) + parseInt($scope.fData.piezasObturadasPermanentes);
              $scope.fData.totalDeciduas = parseInt($scope.fData.piezasPerdidasDeciduas) + parseInt($scope.fData.piezasCariadasDeciduas) + parseInt($scope.fData.piezasObturadasDeciduas);
              $scope.fData.totalPiezasEvaluadas = parseInt($scope.fData.totalPermanentes) + parseInt($scope.fData.totalDeciduas);
            };

            $scope.cancel = function () {

              $modalInstance.dismiss('cancel');

            }

            $scope.guardarOdontograma = function () {
              var now = $filter('date')(new Date(),'dd-MM-yyyy');
              if( $scope.fData.num_acto_medico != '-- SIN REGISTRAR --'){
                $scope.listaOdontograma.idatencionmedica = $scope.fData.num_acto_medico;
              }else{
                $scope.listaOdontograma.idatencionmedica = null;
              }
              $scope.listaOdontograma.idhistoria = $scope.fData.idhistoria;
              $scope.listaOdontograma.perdidaspermanentes = $scope.fData.piezasPerdidasPermanentes;
              $scope.listaOdontograma.cariespermanentes = $scope.fData.piezasCariadasPermanentes;
              $scope.listaOdontograma.obturadaspermanentes = $scope.fData.piezasObturadasPermanentes;
              $scope.listaOdontograma.perdidasdeciduas = $scope.fData.piezasPerdidasDeciduas;
              $scope.listaOdontograma.cariesdeciduas = $scope.fData.piezasCariadasDeciduas;
              $scope.listaOdontograma.obturadasdeciduas = $scope.fData.piezasObturadasDeciduas;
              $scope.listaOdontograma.observaciones = $scope.fData.observaciones;

              if( $scope.listaOdontograma.idodontograma != null && $scope.listaOdontograma.fecha_creacion == now){
               // ================================= EDITAR (verificamos si el odontograma es de hoy)
                console.log('editar'); 

                odontogramaServices.sEditar($scope.listaOdontograma).then(function (rpta) { 
                  // $scope.fData.boolOdontogramaNuevo = false;
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
                });
              }else{ // registra odontograma
                $scope.listaOdontograma.numodontograma++; // debe aumentar
                $scope.listaOdontograma.fecha_creacion = now;
                $scope.listaOdontograma.tipo_odontograma = 1;
                

                odontogramaServices.sRegistrar($scope.listaOdontograma).then(function (rpta) {
                  if(rpta.flag === 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    if( rpta.idodontograma ){ 
                      $scope.listaOdontograma.idodontograma = rpta.idodontograma;
                      $scope.fData.idodontograma = $scope.listaOdontograma.idodontograma;
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
      } // fin btnOdontogramaInicial

    /* ==================================================== */
    /* FORMULARIO DE ATENCION MEDICA CON TODAS LAS PESTAÑAS */
    /* ==================================================== */ 
    $scope.btnAtenderAlPaciente = function (estadoAtendido, mySelectionAtencionGrid) { 
      var pEstadoAtendido = estadoAtendido || false;
      var mySelectionAtencionGrid = mySelectionAtencionGrid || false;
      $scope.fData = {}
      //console.log($scope.mySelectionGrid); return false; 
      $scope.gridOptionsProcedimientos.data = [];
      $scope.gridOptionsDiagnostico.data = [];
      $scope.gridOptionsRecetaMedica.data = [];
      $scope.gridOptionsMedicamentosAdd.data = [];
      $scope.gridOptionsCitt.data = [];
      
      if(pEstadoAtendido === false){
        var mySelectionAtencionGrid = $scope.mySelectionGrid;
      }

      if(mySelectionAtencionGrid === false){
        console.log('myselecc false', mySelectionAtencionGrid[0]);
        mySelectionAtencionGrid = $scope.mySelectionGrid;
      }

      if(pEstadoAtendido === false){ // EN REGISTRAR, VALIDAR SI ES ATENCION DEL DIA O NO 
        if(mySelectionAtencionGrid[0].situacion.autorizado === 2){ // NO AUTORIZADO 
          pinesNotifications.notify({ title: 'Bloqueo de Atención', text: 'Esta atención ha sido bloqueada.', type: 'danger', delay: 3000 }); 
          return false;
        }
        
      }
      console.log('paciente ',mySelectionAtencionGrid[0]);
      if( mySelectionAtencionGrid[0].idtipoproducto == 12 ){ // CONSULTA MÉDICA  idprogcita
        $scope.registroFormularioAMA = true;
        $scope.registroFormularioAP = false;
        if(pEstadoAtendido && pEstadoAtendido === 'si'){ 
          $scope.fData = mySelectionAtencionGrid[0];
          //console.log($scope.fData);
          $scope.fData.fTemporalDiag = {};
          $scope.fData.boolNumActoMedico = true;
          $scope.tabs = { 
            'estadoAtencionMedica': 'enabled',
            'estadoProcedimiento': 'enabled',
            'estadoReceta': 'enabled',
            'estadoExamenAuxiliar': 'enabled',
            'estadoOtrasAtenciones': 'enabled',
            'estadoCitt': 'enabled',
            'estadoReferencias': 'enabled'
          }; 
          $scope.titleForm = 'Edición de Atención Médica'; 
          /* CARGAMOS LOS DIAGNOSTICOS */ 
          var arrParams = {
            'idatencionmedica': $scope.fData.num_acto_medico
          };
          atencionMedicaAmbServices.sListarDiagnosticosDeAtencion(arrParams).then(function (rpta) { 
            $scope.gridOptionsDiagnostico.columnDefs[3].visible = true;
            $scope.gridOptionsDiagnostico.data = rpta.datos; 
          });
        }else{ 
          $scope.fData = mySelectionAtencionGrid[0];
          // console.log('data: ----->', $scope.fData.especialidad);
          $scope.fData.num_acto_medico = '-- SIN REGISTRAR --';
          $scope.fData.boolNumActoMedico = false;
          $scope.fData.fInputs = {};
          $scope.fData.id_area_hospitalaria = 1;
          $scope.fData.area_hospitalaria = 'CONSULTA EXTERNA';
          $scope.fData.fechaAtencion = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.titleForm = 'Registro de Atención Médica'; 
          $scope.fData.fInputs.gestando = 2; // NO 
          $scope.fData.fInputs.atencion_control = 2; // NO 
          $scope.gridOptionsDiagnostico.data = []; 
        }
        $scope.listaBoolGestando = [ 
          { id : 1, descripcion: 'SI' }, 
          { id : 2, descripcion: 'NO' }
        ];
        $scope.listaBoolAtencionControl = [ 
          { id : 1, descripcion: 'SI' }, 
          { id : 2, descripcion: 'NO' }
        ];
        $scope.formSolicitudProcedimiento = false;
        $scope.formRecetaMedica = false;
        $scope.contSolicitudExamenAuxiliar = false;
        $scope.getTableHeight = function() { 
           var rowHeight = 30; // your row height 
           var headerHeight = 30; // your header height 
           return { 
              height: ($scope.gridOptionsDiagnostico.data.length * rowHeight + headerHeight + 30) + "px" 
           }; 
        };
        $scope.getTableHeightMED = function() { 
           var rowHeight = 30; // your row height 
           var headerHeight = 30; // your header height 
           return {
              height: ($scope.gridOptionsMedicamentosAdd.data.length * rowHeight + headerHeight + 30) + "px"
           };
        };
        $scope.calculateSemanaGestacion = function () { 
          var arrData = { 
            'fur' : $scope.fData.fInputs.fur
          }
          atencionMedicaAmbServices.sCalcularSemanaGestacion(arrData).then(function (rpta) { 
            if( rpta.flag == 1 ){
              $scope.fData.fInputs.semana_gestacion = rpta.datos.semanasTranscurridas;
            }
          });
          atencionMedicaAmbServices.sCalcularFPP(arrData).then(function (rpta) { 
            if( rpta.flag == 1 ){
              $scope.fData.fInputs.fpp = rpta.datos.fpp;
            }
          });
        }
        $scope.calculateIMC = function () { 
          // console.log($scope.fData.fInputs.fur.length);
          var arrData = { 
            'peso' : $scope.fData.fInputs.peso, 
            'talla' : $scope.fData.fInputs.talla
          }; 
          atencionMedicaAmbServices.sCalcularIMC(arrData).then(function (rpta) { 
            if( rpta.flag == 1 ){ 
              $scope.fData.fInputs.imc = rpta.datos.imc;
            }
          });
        } 
        $scope.getDiagnosticosAutocomplete = function (value) {
          var params = {
            searchText: value,
            searchColumn: 'descripcion_cie',
            sensor: false
          }
          return diagnosticoServices.sListarDiagnosticoAutoComplete(params).then(function(rpta) {
            $scope.noResultsLEESS = false;
            if( rpta.flag === 0 ){
              $scope.noResultsLEESS = true;
            }
            return rpta.datos; 
          });
        }
        $scope.VerDiagnosticos = function(size){
          //console.log('click');
          $uibModal.open({
            templateUrl: angular.patchURLCI+'diagnostico/ver_popup_CIE10',
            size: size || 'lg',
            scope: $scope,
            controller: function ($scope, $modalInstance) { 
              $scope.fData.fTemporalDiag = {};
              $scope.titleForm = 'Diagnostico CIE-10';
              var paginationOptionsCIE10 = {
                pageNumber: 1,
                firstRow: 0,
                pageSize: 10,
                sort: uiGridConstants.ASC,
                sortName: null,
                search: null
              };
              $scope.mySelectionCIE10 = [];
              $scope.gridOptionsCIE10 = {
                rowHeight: 36,
                paginationPageSizes: [10, 50, 100, 500, 1000],
                paginationPageSize: 10,
                useExternalPagination: true,
                useExternalSorting: true,
                enableGridMenu: false,
                enableRowSelection: false,
                enableSelectAll: true,
                enableFiltering: true,
                enableFullRowSelection: true,
                multiSelect: false,
                columnDefs: [
                  { field: 'id', name: 'iddiagnosticocie', displayName: 'ID', maxWidth: 80, visible: false,
                    sort: { direction: uiGridConstants.ASC} },
                  { field: 'codigo', name: 'codigo_cie', displayName: 'Código', width: '10%' },
                  { field: 'descripcion', name: 'descripcion_cie', displayName: 'Descripción' }
                ],
                onRegisterApi: function(gridApi) { // gridComboOptions
                  $scope.gridApi = gridApi;
                  gridApi.selection.on.rowSelectionChanged($scope,function(row){
                    $scope.mySelectionCIE10 = gridApi.selection.getSelectedRows();
                    $scope.fData.fTemporalDiag.codigo_diagnostico = $scope.mySelectionCIE10[0].codigo;
                    $scope.fData.fTemporalDiag.diagnostico = $scope.mySelectionCIE10[0].descripcion
                    $scope.fData.fTemporalDiag.id = $scope.mySelectionCIE10[0].id;
                    $scope.fData.fTemporalDiag.tipo = 'PRESUNTIVO';

                    $modalInstance.dismiss('cancel');
                    setTimeout(function() {
                      $('#codDiagnostico').focus(); 
                    }, 1000);
                  });

                  $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                    if (sortColumns.length == 0) {
                      paginationOptionsCIE10.sort = null;
                      paginationOptionsCIE10.sortName = null;
                    } else {
                      paginationOptionsCIE10.sort = sortColumns[0].sort.direction;
                      paginationOptionsCIE10.sortName = sortColumns[0].name;
                    }
                    $scope.getPaginationCIE10ServerSide();
                  });
                  gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                    paginationOptionsCIE10.pageNumber = newPage;
                    paginationOptionsCIE10.pageSize = pageSize;
                    paginationOptionsCIE10.firstRow = (paginationOptionsCIE10.pageNumber - 1) * paginationOptionsCIE10.pageSize;
                    $scope.getPaginationCIE10ServerSide();
                  });
                  $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                    var grid = this.grid;
                    paginationOptionsCIE10.search = true;
                    paginationOptionsCIE10.searchColumn = { 
                      'iddiagnosticocie' : grid.columns[1].filters[0].term,
                      'codigo_cie' : grid.columns[2].filters[0].term,
                      'descripcion_cie' : grid.columns[3].filters[0].term
                    }
                    $scope.getPaginationCIE10ServerSide();
                    //console.log('gridOptionsCIE10.data: ',$scope.gridOptionsCIE10.data);
                  });
                }
              }; 
              paginationOptionsCIE10.sortName = $scope.gridOptionsCIE10.columnDefs[0].name;
              $scope.getPaginationCIE10ServerSide = function() {
                //$scope.$parent.blockUI.start();
                var arrParams = {
                  paginate : paginationOptionsCIE10
                };
                diagnosticoServices.sListardiagnosticoGrillaModal(arrParams).then(function (rpta) {
                  $scope.gridOptionsCIE10.totalItems = rpta.paginate.totalRows;
                  $scope.gridOptionsCIE10.data = rpta.datos;
                });
                $scope.mySelectionCIE10 = [];
              };
              $scope.getPaginationCIE10ServerSide();

              shortcut.add("down",function() { 
                $scope.navegateToCellListaBusquedaCIE10(0,0);
              });
              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }
            }
          });
        }
        $scope.onSelectDiagnostico = function (item, model, label) { 
          $scope.fData.fTemporalDiag.diagnostico = item.descripcion; 
          $scope.fData.fTemporalDiag.codigo_diagnostico = item.codigo;
          $scope.fData.fTemporalDiag.id = item.id;
          $scope.fData.fTemporalDiag.tipo = 'PRESUNTIVO'; 
        }
        $scope.onChangeGetDiagnostico = function () {
          if( $scope.fData.fTemporalDiag.codigo_diagnostico.length == 0 ){
            $scope.fData.fTemporalDiag.diagnostico = null;
          }
          if( $scope.fData.fTemporalDiag.codigo_diagnostico.length > 2 ){ 
            var arrData = {
              'codigo': $scope.fData.fTemporalDiag.codigo_diagnostico
            };
            diagnosticoServices.sGetDiagnosticoPorCodigo(arrData).then(function (rpta) { 
              if( rpta.flag == 1 ){ 
                $scope.fData.fTemporalDiag.codigo_diagnostico = rpta.datos.codigo;
                $scope.fData.fTemporalDiag.diagnostico = rpta.datos.descripcion_cie; 
                $scope.fData.fTemporalDiag.id = rpta.datos.id; 
                $scope.fData.fTemporalDiag.tipo = 'PRESUNTIVO'; 
              }else{
                $scope.fData.fTemporalDiag.diagnostico = null; 
                $scope.fData.fTemporalDiag.id = null;
                $scope.fData.fTemporalDiag.tipo = null; 
              }
            });
          }
        }
        $scope.grabarAtencionMedica = function () {  
          $scope.fData.fInputs.gridDiagnostico = $scope.gridOptionsDiagnostico.data;
          if( $scope.fData.boolNumActoMedico ){ // ================================= EDITAR 
            // console.log('editar'); 
            atencionMedicaAmbServices.sEditarAtencionMedicaAmb($scope.fData).then(function (rpta) { 
              $scope.fData.boolNumActoMedico = true;
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                if( rpta.idatencionmedica ){ 
                  $scope.fData.num_acto_medico = rpta.idatencionmedica;
                  $scope.tabs = { 
                    'estadoAtencionMedica': 'enabled',
                    'estadoProcedimiento': 'enabled',
                    'estadoReceta': 'enabled',
                    'estadoExamenAuxiliar': 'enabled',
                    'estadoOtrasAtenciones': 'enabled',
                    'estadoCitt': 'enabled',
                    'estadoReferencias': 'enabled'
                  }; 
                  //$scope.obtenerAtencionMedicaPorId($scope.fData.num_acto_medico);
                  $scope.gridOptionsPPA.data = [];
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Se ha producido un problema. Contacte con el Area de Sistemas');
                return false; 
              }
              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }else{ // ================================================================= REGISTRAR 

            atencionMedicaAmbServices.sRegistrarAtencionMedicaAmb($scope.fData).then(function (rpta) { 
              $scope.fData.boolNumActoMedico = false;
              if(rpta.flag == 1) { // return false; 
                var pTitle = 'OK!';
                var pType = 'success';
                if( rpta.idatencionmedica ){ 
                  $scope.fData.num_acto_medico = rpta.idatencionmedica;
                  $scope.fData.boolNumActoMedico = true;
                  $scope.tabs = { 
                    'estadoAtencionMedica': 'enabled',
                    'estadoProcedimiento': 'enabled',
                    'estadoReceta': 'enabled',
                    'estadoExamenAuxiliar': 'enabled',
                    'estadoOtrasAtenciones': 'enabled',
                    'estadoCitt': 'enabled',
                    'estadoReferencias': 'enabled'
                  }; 
                  //$scope.obtenerAtencionMedicaPorId($scope.fData.num_acto_medico);
                  $scope.gridOptionsPPA.data = [];
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Se ha producido un problema. Contacte con el Area de Sistemas'); 
                return false; 
              }
              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }

        /* =============================== */
        /*     PESTAÑA DE SOLICITUD DE PROCEDIMIENTO    */
        /* =============================== */
          var desde = moment().subtract(30,'days'); 
          $scope.fBusquedaPROC = {}; 
          $scope.fBusquedaPROC.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fBusquedaPROC.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy'); 
          
          $scope.getProcedimientoAutocomplete = function (value) { 
            var params = { 
              searchText: value,
              searchColumn: 'p.descripcion',
              sensor: false,
              especialidad: $scope.fDataProc.especialidad
            }
            return solicitudProcedimientoServices.sListarProcedimientoAutoComplete(params).then(function(rpta) { 
              $scope.noResultsLPAC = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLPAC = true;
              }
              return rpta.datos; 
            });
          }
          $scope.btnVerFormRegistrarProc = function () { 
            $scope.fDataProc = {};
            $scope.formSolicitudProcedimiento = true;
            $scope.fDataProc.especialidad = $scope.listaEspecialidades[0];
          }
          $scope.btnAnularSolicitudProcedimiento = function (row) {
            var arrParams = row.entity; 
            if($scope.fData.boolNumActoMedico){ // SI HAY UNA ATENCION MEDICA CREADA 
              //arrParams.actoMedico = $scope.fData.num_acto_medico;
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  solicitudProcedimientoServices.sEliminarSolicitudProcedimiento(arrParams).then(function (rpta) { 
                    if(rpta.flag == 1){ 
                        var pTitle = 'OK!';
                        var pType = 'success';
                      }else if(rpta.flag == 0){ 
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{ 
                        alert('Error inesperado'); 
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 }); 
                      $scope.getPaginationPROCServerSide();
                  });
                }
              });
            }
          }
          $scope.registrarProcedimientoEnAtencion = function () { 
            $scope.fDataProc.idhistoria = angular.copy($scope.fData.idhistoria);
            $scope.fDataProc.idatencionmedica = angular.copy($scope.fData.num_acto_medico);
            solicitudProcedimientoServices.sRegistrarSolicitudProcedimiento($scope.fDataProc).then(function (rpta) { 
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
              $scope.formSolicitudProcedimiento = false;
            });
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
          $scope.btnImprimirRecetaPdf = function(){
            console.log('fBusqueda: ', $scope);
            var arrParams = {
              titulo: 'RECETA N° ' + $scope.mySelectionRECGrid[0].idreceta,
              datos:{
                id: $scope.mySelectionRECGrid[0].idreceta,
                idatencionmedica: $scope.mySelectionRECGrid[0].acto_medico,
                salida: 'pdf',
                tituloAbv: 'AM-REC',
                titulo: 'RECETA N° '+ $scope.mySelectionRECGrid[0].idreceta,
              },
              metodo: 'php'
            }
            console.log('arrParams: ', arrParams);
            arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_imprimir_receta_PDF',
            ModalReporteFactory.getPopupReporte(arrParams);
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
              var long = 0; var width = 0;
              angular.forEach(rpta.datos, function(value, key) {
                if(value.long > long){
                  long = value.long;
                  width = long + '%';
                  $('#formReceta .dropdown-menu').width(width);
                }
              });
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
                $scope.getUltimasRecetas();
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
        /*        PESTAÑA DE SOLICITUD DE EXAMENES AUXILIARES         */
        /* ============================================= */
          var desde = moment().subtract(30,'days'); 
          $scope.fBusquedaAUX = {}; 
          $scope.fBusquedaAUX.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fBusquedaAUX.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy'); 
          $scope.listaTipoExamen = [ 
            { id : 'ALL', descripcion: 'TODOS' }, 
            { id : 'I', descripcion: 'IMAGENOLOGIA' }, 
            { id : 'PC', descripcion: 'LABORATORIO' }, 
            { id : 'AP', descripcion: 'ANATOMIA PATOLÓGICA' } 
          ]; 
          $scope.fBusquedaAUX.tipoExamen = 'ALL';           
          $scope.btnVerFormRegistrarExamenAux = function (tipoExamen) { 
            $scope.fDataAUX = {};
            if( tipoExamen === 'I' ){ // IMAGENOLOGIA 
              $scope.fDataAUX.tipoExamen = 'IMAGENOLOGIA';
            }else if( tipoExamen === 'PC' ){ // PATOLOGIA CLINICA O LABORATORIO 
              $scope.fDataAUX.tipoExamen = 'LABORATORIO';
            }else if( tipoExamen === 'AP' ){ // ANATOMIA PATOLOGICA 
              $scope.fDataAUX.tipoExamen = 'ANATOMIA PATOLOGICA';
            } 
            $scope.contSolicitudExamenAuxiliar = true;
            $scope.fDataAUX.abvTipoExamen = tipoExamen;
            $scope.fDataAUX.especialidad = $scope.listaEspecialidades[0];
          }
          $scope.getExamenAuxiliarAutocomplete = function (value) { 
            var params = { 
              searchText: value,
              searchColumn: 'p.descripcion',
              sensor: false,
              tipoExamen: $scope.fDataAUX.abvTipoExamen,
              especialidad: $scope.fDataAUX.especialidad
            }
            return solicitudExamenServices.sListarExamenesAutoComplete(params).then(function(rpta) { 
              $scope.noResultsAUX = false;
              if( rpta.flag === 0 ){
                $scope.noResultsAUX = true;
              }
              return rpta.datos; 
            });
          }
          $scope.registrarExamenAuxiliarEnAtencion = function () { 
            $scope.fDataAUX.idhistoria = angular.copy($scope.fData.idhistoria);
            $scope.fDataAUX.idatencionmedica = angular.copy($scope.fData.num_acto_medico);
            solicitudExamenServices.sRegistrarSolicitudExamen($scope.fDataAUX).then(function (rpta) { 
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
              $scope.getPaginationAUXServerSide();
              $scope.contSolicitudExamenAuxiliar = false;
            });
          }
          $scope.btnRegresarAlListadoAux = function () {
            $scope.contSolicitudExamenAuxiliar = false;
          }
          $scope.btnAnularExamenAuxiliar = function ( row ) { // console.log(row);
            var arrParams = row.entity; 
            if($scope.fData.boolNumActoMedico){ // SI HAY UNA ATENCION MEDICA CREADA 
              //arrParams.actoMedico = $scope.fData.num_acto_medico;
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){ 
                  solicitudExamenServices.sEliminarSolicitudExamenAux(arrParams).then(function (rpta) { 
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
                      $scope.getPaginationAUXServerSide();
                  });
                }
              });
            }
          }
        /* ============================================= */ 
        /*         PESTAÑA DE OTRAS ATENCIONES           */ 
        /* ============================================= */ 
          var desde = moment().subtract(30,'days'); 
          $scope.fBusquedaOAT = {}; 
          $scope.fBusquedaOAT.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fBusquedaOAT.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy');
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
                { field: 'area_hospitalaria', name: 'descripcion_aho', displayName: 'AREA HOSP.', width: '10%' },
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
            
            $scope.getPaginationServerSideOAT = function () { 
              $scope.fBusquedaOAT.idhistoria = $scope.fData.idhistoria; 
              atencionMedicaAmbServices.sListarHistorialDePaciente($scope.fBusquedaOAT).then(function (rpta) { 
                $scope.gridOptionsOAT.data = rpta.datos; 
              }); 
            }; 
            // $scope.getPaginationServerSideOAT();
          /* BOTON FICHA DE ATENCION */
          $scope.btnVerFichaAtencion = function (mySelectionAtencionFichaGrid) { 
            $uibModal.open({
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
                // $scope.getTableHeightRM = function() { 
                //    var rowHeight = 30; // your row height 
                //    var headerHeight = 30; // your header height 
                //    return { 
                //       height: ($scope.gridOptionsFichaReceta.data.length * rowHeight + headerHeight + 30) + "px" 
                //    }; 
                // };
              }
            });
          }
        /* =============================== */
        /*     PESTAÑA DE CITT             */
        /* =============================== */
          $scope.fBusquedaCITT = {};
          $scope.btnVerFormRegistrarCitt = function () { 
            $scope.gridOptionsDiagnostico.columnDefs[3].visible = false;
            $scope.fDataCitt = {};
            $scope.fDataCitt.fecha_otorgamiento = $filter('date')(new Date(),'dd-MM-yyyy');
            $scope.fDataCitt.fecha_inicio = $filter('date')(new Date(),'dd-MM-yyyy');
            $scope.formSolicitudCitt = true;
            var arrParams = {
              'idespecialidad': $scope.fData.idespecialidad
            };
            solicitudCittServices.sObtenerProductoCITT(arrParams).then(function(rpta){
              // console.log(rpta.datos[0].producto);
              $scope.fDataCitt.producto = rpta.datos[0].producto;
              $scope.fDataCitt.idespecialidad = rpta.datos[0].idespecialidad;
              $scope.fDataCitt.idproducto = rpta.datos[0].idproducto;
            });
            contingenciaServices.sListarContingenciaCbo().then(function (rpta) {
            $scope.listaContingencias = rpta.datos;
            $scope.fDataCitt.idcontingencia = $scope.listaContingencias[0].id;
          });
          }
          $scope.registrarCittEnAtencion = function () { 
            //$scope.fDataCitt.idhistoria = angular.copy($scope.fData.idhistoria);
            $scope.fDataCitt.idtipoatencion = '1';
            $scope.fDataCitt.idatencionmedica = angular.copy($scope.fData.num_acto_medico);
            console.log($scope.fDataCitt);
            
            solicitudCittServices.sRegistrarSolicitudCitt($scope.fDataCitt).then(function (rpta) { 
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
              $scope.getPaginationCITTServerSide();
              $scope.formSolicitudCitt = false;
            });
          }
          $scope.btnRegresarAlListadoCitt = function () {
            $scope.formSolicitudCitt = false;
          }
          $scope.btnQuitarDeLaCestaCITT = function (row,mensaje) { 
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  solicitudCittServices.sAnularSolicitudCitt(row.entity).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.getPaginationCITTServerSide();
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

        

        /*=================================================*/
        /*  AFECCIONES MEDICAS made by ALEX & RUBEN        */
        /*=================================================*/
          var paginationOptionsAfe = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null
          };
          $scope.mySelectionGridAfe = [];
          $scope.gridOptionsAfe = { 
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
              { field: 'id', name: 'idhistoriaafeccion', displayName: 'ID', maxWidth: 100,  sort: { direction: uiGridConstants.ASC} },
              { field: 'tipoafeccion', name: 'tipoafeccion', displayName: 'Tipo', maxWidth: 260 },
              { field: 'descripcion', name: 'description', displayName: 'Descripción' },
              { field: 'accion', displayName: 'Acción', maxWidth: 95, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCestaAfe(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApiAfe = gridApi;
            }
          };
          paginationOptionsAfe.sortName = $scope.gridOptionsAfe.columnDefs[0].name;
          $scope.getPaginationServerSideAfe = function() {
            $scope.datosGrid = {
              paginate : paginationOptionsAfe,
              datos : $scope.fData.idhistoria
            };
            //console.log($scope.datosGrid);
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

          $scope.btnNuevaAfeccion = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'atencionMedica/ver_popup_formulario_afecciones',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance, arrToModal) {

                $scope.getPaginationServerSideAfe = arrToModal.getPaginationServerSideAfe;
                $scope.fDataAfe.temporal={};
                $scope.GridActiva = false ;
                if($scope.gridOptionsAfe.totalItems==0)
                {
                  $scope.accion = 'reg';
                  $scope.titleForm = 'Registro de Afecciones Médicas';
                }
                else
                {
                  $scope.accion = 'edit';
                  $scope.titleForm = 'Actualizar Afecciones Médicas';
                }
                $scope.listaBoolAfeccion = [ 
                  { id : 1, descripcion: 'ENFERMEDAD' }, 
                  { id : 2, descripcion: 'ALERGIA' }
                ];
                $scope.fDataAfe.temporal.tipoAfeccion = $scope.listaBoolAfeccion[0].id;
                $scope.agregarItemAfeccion = function (mensaje) {
                  var texto= $scope.fDataAfe.temporal.tipoAfeccion==1 ? 'ENFERMEDAD' : 'ALERGIA' ;
                  $scope.GridActiva = true ;
                  if($scope.accion=='reg')      // Ingreso
                  {
                    $scope.arrTemporal = { 
                    'id' : $scope.fData.idhistoria,
                    'idtipoafeccion' : $scope.fDataAfe.temporal.tipoAfeccion,
                    'tipoafeccion' : texto,
                    'descripcion' : $scope.fDataAfe.temporal.descripcion
                    } 
                    //console.log($scope.fDataAfe.temporal);
                    $scope.gridOptionsAfe.data.push($scope.arrTemporal);
                  }
                  else                      //  Edicion
                  {
                    $scope.arrTemporal = { 
                    'id' :  $scope.fData.idhistoria,
                    'idtipoafeccion' : $scope.fDataAfe.temporal.tipoAfeccion,
                    'descripcion' : $scope.fDataAfe.temporal.descripcion 
                    } ;
                    var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
                    $bootbox.confirm(pMensaje, function(result) {
                      if(result){
                        afeccionServices.sRegistrarAfeccionEdit($scope.arrTemporal).then(function (rpta) {
                          if(rpta.flag == 1){ 
                            pTitle = 'OK!';
                            pType = 'success';
                            $modalInstance.dismiss('cancel');
                            $scope.getPaginationServerSideAfe();
                          }else if(rpta.flag == 0){
                            var pTitle = 'Error!';
                            var pType = 'danger';
                          }else if(rpta.flag == 2){
                            var pTitle = 'Advertencia!';
                            var pType = 'warning';}
                          else{
                            alert('Error inesperado');
                          }
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
                        });
                        //$scope.getPaginationServerSideAfe();
                      }
                    });
                  }
                  $scope.fDataAfe.temporal.descripcion = null;           
                }
                $scope.btnQuitarDeLaCestaAfe = function (row,mensaje) { 
                  if($scope.accion=='reg')
                  {
                    var index = $scope.gridOptionsAfe.data.indexOf(row.entity); 
                    $scope.gridOptionsAfe.data.splice(index,1); 
                  }
                  else
                  {
                    var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
                    $bootbox.confirm(pMensaje, function(result) {
                      if(result){
                        //console.log(row.entity.id);
                        //return;
                        afeccionServices.sAnularAfeccion(row.entity).then(function (rpta) {
                          if(rpta.flag == 1){ 
                            pTitle = 'OK!';
                            pType = 'success';
                            $modalInstance.dismiss('cancel');
                            $scope.getPaginationServerSideAfe();
                          }else if(rpta.flag == 0){
                            var pTitle = 'Error!';
                            var pType = 'danger';
                          }else if(rpta.flag == 2){
                            var pTitle = 'Advertencia!';
                            var pType = 'warning';}
                          else{
                            alert('Error inesperado');
                          }
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
                        });
                        //$scope.getPaginationServerSideAfe();
                      }
                    });
                  }
                }

                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }               
                $scope.aceptar = function () {
                  if($scope.accion=='reg'){
                        afeccionServices.sRegistrarAfeccion($scope.gridOptionsAfe.data).then(function (rpta) {
                          if(rpta.flag == 1){ 
                            pTitle = 'OK!';
                            pType = 'success';
                            $modalInstance.dismiss('cancel');
                            $scope.getPaginationServerSideAfe();
                          }else if(rpta.flag == 0){
                            var pTitle = 'Error!';
                            var pType = 'danger';
                          }else if(rpta.flag == 2){
                            var pTitle = 'Advertencia!';
                            var pType = 'warning';}
                          else{
                            alert('Error inesperado');
                          }
                        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
                        });
                        //$scope.getPaginationServerSideAfe();
                      };
                }
                // >>>>>>> modulo de afecciones
              }, 
              resolve: {
                arrToModal : function () {
                  return {
                    getPaginationServerSideAfe : $scope.getPaginationServerSideAfe
                  }
                }
              }
            });
          }

          // ULTIMAS RECETAS DEL PACIENTE
          $scope.getUltimasRecetas = function() {
            recetaMedicaServices.sListarUltimasRecetasDePaciente($scope.fData).then(function (rpta) { 
              $scope.listadoUltRecetas = rpta.datos;
            });
          }
          // ULTIMOS EXAMENES AUXILIARES
          $scope.getUltimoExamenes = function() {
            atencionMedicaAmbServices.sListarUltimosExamenes($scope.fData).then(function (rpta) { 
              $scope.listadoUltExamenes = rpta.datos;
              // console.log('examenes',$scope.listadoUltExamenes[0]);
            });
          }
          $scope.getUltimasRecetas();
          $scope.getUltimoExamenes();


      } else { // PROCEDIMIENTO CLINICO 
        alert('ESTA ATENCION NO ES POR CONSULTA MEDICA, CONTACTE CON EL AREA DE SISTEMAS.');
      }
    }
  }])
  .service("atencionMedicaAmbServices",function($http, $q) {
    return({
        sListarPacientesSinAtender: sListarPacientesSinAtender,
        sListarPacientesAtendidos: sListarPacientesAtendidos,
        sListarPacienteProgramadoSinAtender: sListarPacienteProgramadoSinAtender,
        sListarPacienteProgramadoAtendido: sListarPacienteProgramadoAtendido,
        sListarHistorialDePaciente: sListarHistorialDePaciente,
        sListarDiagnosticosDeAtencion : sListarDiagnosticosDeAtencion,
        sListarRecetasDeAtencion: sListarRecetasDeAtencion,
        sCalcularSemanaGestacion: sCalcularSemanaGestacion,
        sRegistrarAtencionMedicaAmb: sRegistrarAtencionMedicaAmb,
        sEditarAtencionMedicaAmb: sEditarAtencionMedicaAmb,
        sCalcularIMC: sCalcularIMC,
        sCalcularFPP: sCalcularFPP,
        sEliminarDiagnosticoDeAtencionMedica: sEliminarDiagnosticoDeAtencionMedica,
        sAgregarDias : sAgregarDias,
        sListarSubAlmacenesVentaSedeCbo: sListarSubAlmacenesVentaSedeCbo,
        sListarMedicamentosAlmacenBusquedaVenta: sListarMedicamentosAlmacenBusquedaVenta,
        sObtenerTotalesProduccion: sObtenerTotalesProduccion,
        sImprimirReceta:sImprimirReceta,
        sVerificarTieneProgramacion:sVerificarTieneProgramacion,
        sListarUltimosExamenes:sListarUltimosExamenes,
    });
    function sListarPacientesSinAtender(datos) { 
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_pacientes_no_atendidos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacientesAtendidos (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_pacientes_atendidos_del_dia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacienteProgramadoSinAtender (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_paciente_programado_sin_atender", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacienteProgramadoAtendido (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_paciente_programado_atendido_del_dia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarHistorialDePaciente (datos) { 
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_historial_pacientes", 
            data : datos 
      }); 
      return (request.then( handleSuccess,handleError )); 
    } 
    function sListarDiagnosticosDeAtencion (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_diagnosticos_de_atencion_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarRecetasDeAtencion (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_recetas_de_atencion_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCalcularSemanaGestacion (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/calcular_semana_gestacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCalcularIMC (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/calcular_IMC", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCalcularFPP (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/calcular_FPP", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarAtencionMedicaAmb (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/registrar_atencion_medica_ambulatoria", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sEditarAtencionMedicaAmb (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/editar_atencion_medica_ambulatoria", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarDiagnosticoDeAtencionMedica (datos) { 
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/eliminar_diagnostico_atencion_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarDias (datos) { 
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/agregar_dias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubAlmacenesVentaSedeCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AlmacenFarmacia/lista_sub_almacenes_venta_sede_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentosAlmacenBusquedaVenta(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"MedicamentoAlmacen/lista_medicamento_almacen_busqueda_atencion_medica",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerTotalesProduccion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/obtener_totales_produccion_terceros",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirReceta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/imprimir_receta",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sVerificarTieneProgramacion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"especialidad/verificar_tiene_programacion",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarUltimosExamenes (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_ultimos_examenes_de_paciente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  })
  .filter('mapGender', function() {
    var genderHash = { 
      'DEFINITIVO': 'DEFINITIVO',
      'PRESUNTIVO': 'PRESUNTIVO'
    };
    return function(input) {
      if (!input){
        return '';
      } else {
        return genderHash[input];
      }
    }
  });