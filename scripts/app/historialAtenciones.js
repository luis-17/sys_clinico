angular.module('theme.historialAtenciones', ['theme.core.services','ui.grid.edit'])
  .controller('historialAtencionesController', ['$scope', '$route', 'blockUI', '$filter', '$sce', '$interval', '$location', '$anchorScroll','$modal','$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ModalReporteFactory',
    'historialAtencionesServices', 
    'diagnosticoServices',
    'especialidadServices', 
    'atencionMedicaAmbServices', 
    'atencionExamenAuxServices',
    'atencionProcedimientoServices',
    'atencionCittServices',
    'empleadoSaludServices', 
    'solicitudProcedimientoServices',
    'recetaMedicaServices',
    'medicamentoServices',
    'solicitudExamenServices',
    'solicitudCittServices',
    'empresaServices',
    function($scope, $route, blockUI, $filter, $sce, $interval, $location, $anchorScroll, $modal, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ModalReporteFactory,
      historialAtencionesServices,
      diagnosticoServices,
      especialidadServices,
      atencionMedicaAmbServices,
      atencionExamenAuxServices,
      atencionProcedimientoServices,
      atencionCittServices,
      empleadoSaludServices,
      solicitudProcedimientoServices,
      recetaMedicaServices,
      medicamentoServices,
      solicitudExamenServices,
      solicitudCittServices,
      empresaServices
    ){ 
    'use strict'; 
    $scope.fBusquedaPAH = {}; 
    $scope.fData = {}; 
    $scope.fBusquedaPAH.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusquedaPAH.desdeHora = '00';
    $scope.fBusquedaPAH.desdeMinuto = '00';
    $scope.fBusquedaPAH.hastaHora = '23';
    $scope.fBusquedaPAH.hastaMinuto = '59';
    $scope.fBusquedaPAH.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
    $scope.fBusquedaPAH.tipoCuadro = 'reporte';
    $scope.fBusquedaPAH.salida = 'pdf';
    /* GRILLA DE PACIENTES ATENDIDOS HISTORIAL */ 
    // $scope.btnToggleFilteringPAH = function(){ 
    //   $scope.gridOptionsPAH.enableFiltering = !$scope.gridOptionsPAH.enableFiltering;
    //   $scope.gridApiPAH.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    // };
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 50,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionPAHGrid = [];
    $scope.gridOptionsPAH = {
      paginationPageSizes: [10, 50, 100, 500, 1000, 10000, 100000],
      paginationPageSize: 50,
      enableRowSelection: true,
      minRowsToShow: 9,
      data: [],
      enableFiltering: true,
      enableFullRowSelection: true,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableSelectAll: true,
      multiSelect: true,
      enableGridMenu: true,
      columnDefs: [
        { field: 'num_acto_medico', name: 'a.idatencionmedica', displayName: 'N° ACT. MED.', width: '6%',  sort: { direction: uiGridConstants.DESC} },
        { field: 'idhistoria', name: 'a.idhistoria', displayName: 'HIST.', width: '4%' },
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '9%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'N° TICKET', width: '12%', visible: false },
        { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '18%' },
        { field: 'edad', name: 'edad', displayName: 'EDAD', width: '5%', enableFiltering: true, visible: false },
        { field: 'empresa', name: 'a.empresa', displayName: 'EMPRESA', width: '15%', visible: false },
        { field: 'especialidad', name: 'a.especialidad', displayName: 'ESPECIALIDAD', width: '15%', visible: false },
        { field: 'personalatencion.descripcion', name: 'medicoatencion', displayName: 'MEDICO', type:'object', width: '16%', enableFiltering: false },
        { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PRODUCTO', width: '12%' },
        { field: 'producto', name: 'a.producto', displayName: 'PRODUCTO/SERVICIO', width: '18%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width: '8%', enableFiltering: false, visible: false },
        { field: 'fecha_atencion', name: 'fecha_atencion', displayName: 'FECHA ATENCION', width: '8%', enableFiltering: false }
        
      ], 
      onRegisterApi: function(gridApiPAH) {
        $scope.gridApiPAH = gridApiPAH;
        gridApiPAH.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionPAHGrid = gridApiPAH.selection.getSelectedRows();
        });
        $scope.gridApiPAH.core.on.sortChanged($scope, function(grid, sortColumns) { 
          // console.log(sortColumns);
          if (sortColumns.length == 0) { 
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSidePAH(true);
        });
        $scope.gridApiPAH.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'a.idatencionmedica' : grid.columns[1].filters[0].term,
            'a.idhistoria' : grid.columns[2].filters[0].term,
            'a.orden_venta' : grid.columns[3].filters[0].term,
            'a.ticket_venta' : grid.columns[4].filters[0].term,
            "a.cliente" : grid.columns[5].filters[0].term,
            "DATE_PART('YEAR',AGE(a.fecha_atencion,a.fecha_nacimiento))" : grid.columns[6].filters[0].term,
            'a.empresa' : grid.columns[7].filters[0].term,
            'a.especialidad' : grid.columns[8].filters[0].term,
            'a.nombre_tp' : grid.columns[10].filters[0].term,
            'a.producto' : grid.columns[11].filters[0].term,
            'a.total_detalle_sf' : grid.columns[12].filters[0].term
          }
          $scope.getPaginationServerSidePAH();
        });
        gridApiPAH.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSidePAH(true);
        });
      }
    };
    setTimeout(function() {
      if( !($scope.fSessionCI.key_group == 'key_salud') && !($scope.fSessionCI.key_group == 'key_lab')){ 
        $scope.gridOptionsPAH.columnDefs.push( 
          { field: 'importe', name: 'total_detalle', displayName: 'IMPORTE', width: '6%' }
        )
      }
    }, 500);
    
    $scope.gridOptionsPAH.totalItems = 0;
    $scope.gridOptionsPAH.totalImporte = '0.00';
    $scope.limpiarGrilla = function(){
      $scope.gridOptionsPAH.data = [];
      $scope.gridOptionsPAH.totalItems = 0;
      $scope.gridOptionsPAH.totalImporte = '0.00';
    }
    // PACIENTES ATENDIDOS HISTORIAL 
    paginationOptions.sortName = $scope.gridOptionsPAH.columnDefs[0].name;
    $scope.getPaginationServerSidePAH = function (loader) { 
      var loader = loader || false;
      $scope.fBusquedaPAH.idespecialidad = $scope.fSessionCI.idespecialidad;
      var arrParam = {
        paginate : paginationOptions,
        datos : $scope.fBusquedaPAH
      }; 
      if( loader ){
        blockUI.start('Ejecutando proceso...');
      }
      historialAtencionesServices.sListarPacientesAtendidos(arrParam).then(function (rpta) { 
        $scope.gridOptionsPAH.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsPAH.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptionsPAH.data = rpta.datos;
        if( loader ){
          blockUI.stop();
        }
      });
      $scope.mySelectionPAHGrid = [];
    };
    $scope.imprimirProduccionMedico = function () { 
      var strControllerJS = 'CentralReportes';
      var strControllerPHP = 'CentralReportesMPDF';
      $scope.fBusquedaPAH.titulo = 'REPORTE DE PRODUCCION DE MEDICOS';
      var arrParams = {
        titulo: $scope.fBusquedaPAH.titulo,
        // url: angular.patchURLCI+'CentralReportes/report_produccion_medicos',
        datos: $scope.fBusquedaPAH,
        metodo: 'php'
      }; 
      var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
      arrParams.url = angular.patchURLCI+strController+'/report_produccion_medicos',
      ModalReporteFactory.getPopupReporte(arrParams); 
    } 
    /* TIPO PRODUCTO CM: consulta medica; P: procedimiento; EA: examen auxiliar;  DO: documentos */
    $scope.listaTipoAtencionMedica = [ 
      { 'id': 'ALL', 'descripcion': '--TODOS--' }, 
      { 'id': 'CM', 'descripcion': 'CONSULTA MEDICA' }, 
      { 'id': 'P', 'descripcion': 'PROCEDIMIENTO' }, 
      { 'id': 'EA', 'descripcion': 'EXAMEN AUXILIAR' }, 
      { 'id': 'DO', 'descripcion': 'DOCUMENTO' } 
    ]; 
    // console.log($scope.$parent.fSessionCI,'s');
    $scope.listaMedicos = [ 
      { 'idmedico': 'ALL', 'medico': '--TODOS--' } 
    ];
    setTimeout(function() { // NO MUESTRA EL COMBO DE MEDICOS SI ES SALUD O LABORATORIO
      if( ($scope.fSessionCI.key_group == 'key_salud') || ($scope.fSessionCI.key_group == 'key_lab')){ 
        $scope.contFiltroMedico = false;
      }else{
        $scope.contFiltroMedico = true;
      }
    }, 500);
    $scope.fBusquedaPAH.idTipoAtencion = 'ALL'; 
    $scope.fBusquedaPAH.medico = $scope.listaMedicos[0]; 

    /* LISTADO DE EMPRESA-ESPECIALIDADES */
    especialidadServices.sListarEspecialidadesRestriccionesCbo().then(function (rpta) {
      $scope.listaEmpresaEspecialidades = rpta.datos;
      if( !($scope.fSessionCI.key_group == 'key_salud') && !($scope.fSessionCI.key_group == 'key_dir_esp')  
        && !($scope.fSessionCI.key_group == 'key_lab') ){ 
        $scope.listaEmpresaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
      }
      $scope.fBusquedaPAH.empresaespecialidad = $scope.listaEmpresaEspecialidades[0];
      setTimeout(function() {
        $scope.getListaMedicos();
      }, 600);
    });
    $scope.getListaMedicos = function () {
      if($scope.contFiltroMedico){
        if( $scope.fBusquedaPAH.empresaespecialidad.id == 'ALL' ){
          $scope.listaMedicos = [ 
            { 'idmedico': 'ALL', 'medico': '--TODOS--' } 
          ]; 
          $scope.fBusquedaPAH.medico = $scope.listaMedicos[0];
        }else{
          empleadoSaludServices.sListarMedicosDeEmpresaEspecialidad($scope.fBusquedaPAH.empresaespecialidad).then(function (rpta) { 
            $scope.listaMedicos = rpta.datos;
            //$scope.listaMedicos.splice(0,0,{ idmedico : 'ALL', medico:'-- TODOS --'});

            if( !($scope.fSessionCI.key_group == 'key_salud') && !($scope.fSessionCI.key_group == 'key_lab')){

              $scope.listaMedicos.splice(0,0,{ idmedico : 'ALL', medico:'TODOS'});
            }
            $scope.fBusquedaPAH.medico = $scope.listaMedicos[0];
          });
        }
        
      }
    }

    $scope.btnCambiarEmpresa = function (myRow) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_cambiar_empresa',
        size: 'md',
        scope: $scope,
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Cambio de Empresa/EMA';
          $scope.fData = {};
          // LISTADO DE EMPRESAS. 
          var arrDatos = {
            'datos': myRow
          }; 
          empresaServices.sListarEmpresasDeEspecialidad(arrDatos).then(function (rpta) { 
            $scope.listaEMES = rpta.datos;
            $scope.listaEMES.splice(0,0,{ id : '0', descripcion:'-- Seleccione --'});
            $scope.fData.empresa = $scope.listaEMES[0];
          }); 
          //console.log(myRow);
          $scope.aceptar = function () {
            var arrParams = {
              'idatencionmedica': myRow.num_acto_medico,
              'idventa': myRow.id,
              'empresa': $scope.fData.empresa_especialidad,
              'motivo': $scope.fData.motivo  
            };
            historialAtencionesServices.sCambiarEmpresa(arrParams).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';                
              }else{
                alert('Error inesperado');
              }
              $scope.fData = {};
              $scope.getPaginationServerSidePAH(true);
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnVerFichaAtencion = function (mySelectionAtencionFichaGrid) {
      
      $modal.open({
        templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_ficha_atencion_ambulatoria',
        size: 'xlg',
        scope: $scope,
        // backdrop: 'static',
        // keyboard:false,
        controller: function ($scope, $modalInstance) { 
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          // $scope.fDataFicha = {};
          $scope.fDataFicha = mySelectionAtencionFichaGrid[0];
          $scope.titleForm = 'Ficha de Atención Médica';
          /* CARGAMOS LOS DATOS DE LA ATENCION MEDICA */
          $scope.cargarAtencionMedica = function (){
            blockUI.start('Ejecutando proceso...');
            historialAtencionesServices.sListarAtencionMedicaPorId(mySelectionAtencionFichaGrid[0]).then(function (rpta) { 
              $scope.fDataFicha = rpta.datos[0];

              blockUI.stop();
            });  
          }
          $scope.cargarAtencionMedica();
          /* CARGAMOS LOS DIAGNOSTICOS DEL ACTO MEDICO */ 
          var arrParams = { 
            'idatencionmedica': mySelectionAtencionFichaGrid[0].num_acto_medico
            // 'idatencionmedica': $scope.fDataFicha.num_acto_medico
          }; 
          $scope.gridOptionsFichaDiagnostico = { 
            paginationPageSize: 10,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            enableCellEditOnFocus: true,
            minRowsToShow: 10, 
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
              // { field: 'accion', displayName: 'Acción', width: '15%', 
              //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' 
              // }
            ]
            ,onRegisterApi: function(gridApiFicha) { 
              $scope.gridApiFicha = gridApiFicha; 
            }
          };
          atencionMedicaAmbServices.sListarDiagnosticosDeAtencion(arrParams).then(function (rpta) { 
            $scope.gridOptionsFichaDiagnostico.data = rpta.datos; 
          });
          /* CARGAMOS LA RECETA DEL ACTO MEDICO */
          $scope.gridOptionsFichaReceta = { 
            paginationPageSize: 10,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            minRowsToShow: 10, 
            data: null,
            rowHeight: 30,
            multiSelect: false,
            columnDefs: [
              { field: 'medicamento', displayName: 'Medicamento', width: '35%',  type:'object' },
              { field: 'unidad', displayName: 'Medida' },
              { field: 'cantidad', displayName: 'Cantidad' },
              { field: 'indicaciones', displayName: 'Indicaciones', width: '30%' }
              // { field: 'accion', displayName: 'Acción', enableCellEdit: false, 
              //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCestaMED(row)"> <i class="fa fa-trash"></i> </button>' 
              // }
            ]
            ,onRegisterApi: function(gridApiReceta) { 
              $scope.gridApiReceta = gridApiReceta; 
            }
          };
          atencionMedicaAmbServices.sListarRecetasDeAtencion(arrParams).then(function (rpta) { 
            $scope.gridOptionsFichaReceta.data = rpta.datos; 
          });
          // $scope.reloadGrid = function () { // console.log('click med');
          //   $interval( function() { 
          //       $scope.gridApiReceta.core.handleWindowResize();
          //   }, 50, 5);
          // }
          $scope.getTableHeight = function() { 
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return { 
                height: ($scope.gridOptionsFichaDiagnostico.data.length * rowHeight + headerHeight + 30) + "px" 
             }; 
          }; 
          $scope.getTableHeightRM = function() { 
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return { 
                height: ($scope.gridOptionsFichaReceta.data.length * rowHeight + headerHeight + 30) + "px" 
             }; 
          }; 
        }
      });
    }
    $scope.btnEditarFichaAtencion = function () {
      // VALIDAR QUE NO PUEDA EDITAR SI ES MEDICO O DIRECTOR MEDICO 
      console.log('weqjdkb');
      blockUI.start('Abriendo formulario...');
      historialAtencionesServices.sValidarPermisosEditAtencion($scope.mySelectionPAHGrid[0]).then(function (rpta) { 
        blockUI.stop();
        if(rpta.flag == 2){
          var pTitle = 'Error!';
          var pType = 'danger';
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          return false; 
        }else{
          $uibModal.open({
            templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_formulario_atencion_ambulatoria',
            size: 'xlg',
            scope: $scope,
            backdrop: 'static',
            keyboard:false,
            controller: function ($scope, $modalInstance) {
              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }
              $scope.titleForm = 'Edición de la Atención Médica'; 
              $scope.glued = false;
              $scope.fData = [];
              /* CARGAMOS LOS DATOS DE LA ATENCION MEDICA */
              $scope.cargarAtencionMedica = function (){
                blockUI.start('Ejecutando proceso...');
                historialAtencionesServices.sListarAtencionMedicaPorId($scope.mySelectionPAHGrid[0]).then(function (rpta) { 
                  $scope.fData = rpta.datos[0];

                  blockUI.stop();
                });  
              }
              if( $scope.mySelectionPAHGrid.length == 1 ){
                $scope.fData = $scope.mySelectionPAHGrid[0];
                $scope.cargarAtencionMedica();
              }else{
                alert('No seleccionó una sola fila.');
                return;
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
              if( $scope.mySelectionPAHGrid[0].idtipoproducto == 12 ){ 
                $scope.fDataProc = {}; // SOLICITUD DE PROCEDIMIENTO
                $scope.fDataREC = {}; // RECETA 
                $scope.fDataREC.fTemporal = {}; 
                $scope.fData.fTemporalDiag = {}; 
                $scope.fDataAUX = {}; // EXAMEN AUXILIAR  
                $scope.fDataCitt={}; // CITT
                $scope.fDataAfe = {} // AFECCIONES
            
                $scope.listaBoolGestando = [ 
                  { id : 1, descripcion: 'SI' }, 
                  { id : 2, descripcion: 'NO' }
                ];
                $scope.listaBoolAtencionControl = [ 
                  { id : 1, descripcion: 'SI' }, 
                  { id : 2, descripcion: 'NO' }
                ];
                // CONSULTA MÉDICA 
                $scope.registroFormularioAMA = true;
                $scope.registroFormularioAP = false;

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
                /* CARGAMOS LOS DIAGNOSTICOS DEL ACTO MEDICO */ 
                var arrParams = {
                  'idatencionmedica': $scope.mySelectionPAHGrid[0].num_acto_medico
                  // 'idatencionmedica': $scope.fData.num_acto_medico
                };
                atencionMedicaAmbServices.sListarDiagnosticosDeAtencion(arrParams).then(function (rpta) { 
                  $scope.gridOptionsDiagnostico.columnDefs[3].visible = true;
                  $scope.gridOptionsDiagnostico.data = rpta.datos; 
                });
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
                    'fur' : $scope.fData.fur
                  }
                  atencionMedicaAmbServices.sCalcularSemanaGestacion(arrData).then(function (rpta) { 
                    if( rpta.flag == 1 ){
                      $scope.fData.semana_gestacion = rpta.datos.semanasTranscurridas;
                    }
                  });
                  atencionMedicaAmbServices.sCalcularFPP(arrData).then(function (rpta) { 
                    if( rpta.flag == 1 ){
                      $scope.fData.fpp = rpta.datos.fpp;
                    }
                  });
                }
                $scope.calculateIMC = function () { 
                  // console.log($scope.fData.fur.length);
                  var arrData = { 
                    'peso' : $scope.fData.peso, 
                    'talla' : $scope.fData.talla
                  }; 
                  atencionMedicaAmbServices.sCalcularIMC(arrData).then(function (rpta) { 
                    if( rpta.flag == 1 ){ 
                      $scope.fData.imc = rpta.datos.imc;
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
                  $scope.fData.fInputs = {};
                  $scope.fData.fInputs.gridDiagnostico = $scope.gridOptionsDiagnostico.data;
                  $scope.fData.desdeHistorial = 'si';
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
                        // $scope.gridOptionsPPA.data = [];
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
                }
                
                /*  */ 
                $scope.btnQuitarDeLaCesta = function (row) { 
                  var arrParams = row.entity;
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

                /* =============================== */
                /*     PESTAÑA DE PROCEDIMIENTO    */
                /* =============================== */
                var desde = moment().subtract(30,'days'); 

                $scope.fBusquedaPROC = {}; 
                $scope.fBusquedaPROC.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
                $scope.fBusquedaPROC.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy'); 

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
                $scope.btnVerFormRegistrarProc = function () { 
                  $scope.fDataProc = {};
                  $scope.formSolicitudProcedimiento = true;
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
                  multiSelect: true,
                  data: null,
                  columnDefs: [ 
                    
                    { field: 'fecha', name: 'fecha_receta', displayName: 'Fecha', width: '14%', enableCellEdit: false }, 
                    { field: 'acto_medico', name: 'idatencionmedica', displayName: 'Acto Médico', width: '10%', enableCellEdit: false }, 
                    { field: 'idreceta', name: 'idreceta', displayName: 'N° Receta', width: '8%', enableCellEdit: false }, 
                    { field: 'medicamento', name: 'medicamento', displayName: 'Medicamento', enableCellEdit: false }, 
                    { field: 'presentacion', name: 'presentacion', displayName: 'Presentación', width: '10%', enableCellEdit: false },
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
                    { field: 'medicamento.idunidadmedida', displayName: 'Presentación', enableCellEdit: false },
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

                $scope.btnVerFormRegistrarReceta = function () { 
                  $scope.fDataREC = {};
                  $scope.fDataREC.fTemporal = {};
                  $scope.formRecetaMedica = true;
                  $scope.fDataREC.fTemporal.cantidad = 1;
                }
                $scope.btnImprimirReceta = function () {
                  console.log('Imprimiendo...', $scope.mySelectionRECGrid[0].idreceta);
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


                      /*setTimeout(function() {
                        popupWin.close();
                        
                      },1000);*/
                      
                    }else { 
                      if(rpta.flag == 0) { // ALGO SALIÓ MAL
                        var pTitle = 'Error';
                        var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                        var pType = 'warning';
                      }
                      
                      pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
                    }
                  });
                  /*
                  var arrParams = {
                      titulo: 'REPORTE DE INGRESO AL ALMACEN',
                      datos:{
                        resultado: $scope.mySelectionRECGrid[0].idreceta,
                        salida: 'pdf',
                        tituloAbv: 'AM-RCTA',
                        titulo: 'RECETA POR ATENCION MEDICA'
                      },
                      metodo: 'php'
                  }
                  //console.log('arrParams: ', arrParams);
                  arrParams.url = angular.patchURLCI+'CentralReportesMPDF/receta_atencion_medica',
                  ModalReporteFactory.getPopupReporte(arrParams);*/
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
                $scope.agregarMedicamentoAReceta = function () { 
                  console.log($scope.fDataREC.fTemporal, ' aaa '); // return false; 
                  if( !$scope.fDataREC.fTemporal.medicamento ){ 
                    $scope.fDataREC.fTemporal = {}; 
                    $scope.fDataREC.fTemporal.cantidad = 1; 
                    $('#fTemporalmedicamento').focus(); 
                    return false; 
                  } 
                  console.log(angular.isObject($scope.fDataREC.fTemporal.medicamento)); 
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
                  $scope.gridOptionsMedicamentosAdd.data.push(angular.copy($scope.fDataREC.fTemporal)); 
                  //console.log($scope.fDataREC.fTemporal,$scope.gridOptionsMedicamentosAdd.columnDefs); 
                  $scope.fDataREC.fTemporal = {}; 
                  $scope.fDataREC.fTemporal.cantidad = 1; 
                  $('#fTemporalmedicamento').focus();
                  //console.log($scope.fDataREC.fTemporal); 
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
                /*        PESTAÑA DE EXAMENES AUXILIARES         */
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
                }
                $scope.getExamenAuxiliarAutocomplete = function (value) { 
                  var params = { 
                    searchText: value,
                    searchColumn: 'p.descripcion',
                    sensor: false,
                    tipoExamen: $scope.fDataAUX.abvTipoExamen
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
                }         // CONSULTA 
              }
              
              if( $scope.mySelectionPAHGrid[0].idtipoproducto == 11 || $scope.mySelectionPAHGrid[0].idtipoproducto == 14 || $scope.mySelectionPAHGrid[0].idtipoproducto == 15 ){ // E.A.
                $scope.tabs = { 
                  'estadoAtencionMedica': 'enabled',
                  'estadoOtrasAtenciones': 'enabled'
                };
                $scope.getPersonalMedicoAutocomplete = function (value) { 
                  var params = {
                    search: value,
                    sensor: false
                  }
                  return empleadoSaludServices.sListarPersonalSaludCbo(params).then(function(rpta) { 
                    $scope.noResultsMEDRESP = false; 
                    if( rpta.flag === 0 ){ 
                      $scope.noResultsMEDRESP = true; 
                    } 
                    return rpta.datos; 
                  });
                }
                $scope.listaTipoResultado = [
                  { id:1, descripcion:'NORMAL' },
                  { id:2, descripcion:'PATOLOGICO' }
                ];
                $scope.registroFormularioAMA = true; 
                $scope.registroFormularioAP = false; 
                $scope.titleForm = 'Edición del Examen Auxiliar'; 
                // console.log($scope.registroFormularioAMA);
                // if(pEstadoAtendido && pEstadoAtendido === 'si'){ 
                //   $scope.fData = mySelectionAtencionGrid[0];
                //   $scope.fData.boolNumActoMedico = true; 
                // }

                $scope.formSolicitudExamenAux = false;
                $scope.grabarAtencionExamenAux = function () { 
                  //$scope.fData.fInputs.gridDiagnostico = $scope.gridOptionsDiagnostico.data; 
                    // console.log('editar'); 
                    atencionExamenAuxServices.sEditarAtencionExamenAux($scope.fData).then(function (rpta) { 
                      $scope.fData.boolNumActoMedico = true; 
                      if(rpta.flag == 1){ 
                        var pTitle = 'OK!';
                        var pType = 'success';
                        if( rpta.idatencionmedica ){ 
                          $scope.fData.num_acto_medico = rpta.idatencionmedica; 
                          // $scope.gridOptionsPPA.data = []; 
                        }
                      }else if(rpta.flag == 0){
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{
                        alert('Se ha producido un problema. Contacte con el Area de Sistemas');
                      }
                      
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    });
                }
              }

              if( $scope.mySelectionPAHGrid[0].idtipoproducto == 16 ){ // PROCEDIMIENTO 
                $scope.tabs = { 
                  'estadoAtencionMedica': 'enabled',
                  'estadoOtrasAtenciones': 'enabled'
                };
                $scope.registroFormularioAMA = true; 
                $scope.registroFormularioAP = false; 

                //$scope.fData = mySelectionAtencionGrid[0];
                $scope.fData.boolNumActoMedico = true; 
                $scope.titleForm = 'Edición del Procedimiento Realizado'; 

                $scope.formSolicitudProcedimiento = false;
                $scope.grabarAtencionProcedimiento = function () { 
                  //if( $scope.fData.boolNumActoMedico ){ // ================================= EDITAR 
                    // console.log('editar'); 
                    atencionProcedimientoServices.sEditarAtencionProcedimiento($scope.fData).then(function (rpta) { 
                      $scope.fData.boolNumActoMedico = true; 
                      if(rpta.flag == 1){ 
                        var pTitle = 'OK!';
                        var pType = 'success';
                        if( rpta.idatencionmedica ){ 
                          $scope.fData.num_acto_medico = rpta.idatencionmedica; 
                          //$scope.gridOptionsPPA.data = []; 
                        }
                      }else if(rpta.flag == 0){
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{
                        alert('Se ha producido un problema. Contacte con el Area de Sistemas');
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    });
                  //}
                }
              }

              if( $scope.mySelectionPAHGrid[0].idtipoproducto == 13 ){ // DOCUMENTOS  
                $scope.tabs = { 
                  'estadoAtencionMedica': 'enabled',
                  'estadoOtrasAtenciones': 'enabled'
                };
                $scope.registroFormularioAMA = true; 
                $scope.registroFormularioAP = false;
                $scope.documentoCitt = false;
                $scope.titleForm = 'Resultados del CITT';
               
                //if(pEstadoAtendido && pEstadoAtendido === 'si'){ 
                  //$scope.fData = mySelectionAtencionGrid[0];
                  $scope.fData.boolNumActoMedico = true;
                  // SI EL DOCUMENTO ES UN DESCANSO MEDICO (CITT) ACTIVAR FLAG 'documentoCitt'
                  if($scope.fData.producto.indexOf('DESCANSO MEDICO') !== -1){
                    $scope.documentoCitt = true;
                    $scope.fData.tipodocumento = 1; // 1: CITT; 2: Otro documento;

                    var datosfec = {};
                    datosfec.fecha_inicio=$scope.fData.fecha_iniciodescanso ;
                    datosfec.dias = $scope.fData.dias;
                    atencionMedicaAmbServices.sAgregarDias(datosfec).then(function (rpta) { 
                      $scope.fData.fecha_final = rpta;
                    });
                  }else{
                    $scope.documentoCitt = false;
                    $scope.fData.tipodocumento = 2; // 1: CITT; 2: Otro documento;
                  }
                //}
                $scope.formSolicitudExamenAux = false;
                $scope.grabarAtencionCITT = function () { 
                  //$scope.fData.fInputs.gridDiagnostico = $scope.gridOptionsDiagnostico.data;
                  //if( $scope.fData.boolNumActoMedico ){ // ================================= EDITAR 
                    // console.log('editar'); 
                    atencionCittServices.sEditarAtencionCitt($scope.fData).then(function (rpta) { 
                      $scope.fData.boolNumActoMedico = true; 
                      if(rpta.flag == 1){ 
                        var pTitle = 'OK!';
                        var pType = 'success';
                        if( rpta.idatencionmedica ){
                          $scope.fData.num_acto_medico = rpta.idatencionmedica; 
                          //$scope.gridOptionsPPA.data = []; 
                        }
                      }else if(rpta.flag == 0){
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{
                        alert('Se ha producido un problema. Contacte con el Area de Sistemas');
                      }
                      
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    });
                  //}
                }
              }

              /*PARA TODOS LO TIPOS DE PRODUCTO */
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
                  { field: 'num_acto_medico', name: 'idatencionmedica', displayName: 'N° ACT. MED.', width: '12%' },
                  { field: 'fecha_atencion', name: 'fecha_atencion', displayName: 'FECHA ATENCION', width: '12%' },
                  { field: 'idhistoria', name: 'idhistoria', displayName: 'HIST.', width: '6%', visible: false },
                  { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: '12%', visible: false },
                  { field: 'ticket', name: 'ticket_venta', displayName: 'N° TICKET', width: '12%', visible: false },
                  { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '20%', visible: false },
                  { field: 'area_hospitalaria', name: 'descripcion_aho', displayName: 'AREA HOSP.', width: '20%' },
                  { field: 'especialidad', name: 'e.nombre', displayName: 'ESPECIALIDAD', width: '15%' },
                  { field: 'tipo_producto', name: 'nombre_tp', displayName: 'TIPO PRODUCTO', width: '15%' },
                  { field: 'producto', name: 'pm.descripcion', displayName: 'PRODUCTO/SERVICIO', width: '18%' }
                  
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
                        // { field: 'accion', displayName: 'Acción', width: '15%', 
                        //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' 
                        // }
                      ]
                      ,onRegisterApi: function(gridApiFicha) { 
                        $scope.gridApiFicha = gridApiFicha; 
                      }
                    };
                    atencionMedicaAmbServices.sListarDiagnosticosDeAtencion(arrParams).then(function (rpta) { 
                      $scope.gridOptionsFichaDiagnostico.data = rpta.datos; 
                    });
                    $scope.reloadGrid = function () { // console.log('click med');
                      $interval( function() { 
                          $scope.gridApiFicha.core.handleWindowResize();
                      }, 50, 5);
                    }
                    /* CARGAMOS LA RECETA DEL ACTO MEDICO */
                    $scope.gridOptionsFichaReceta = { 
                      paginationPageSize: 10,
                      enableRowSelection: false,
                      enableSelectAll: false,
                      enableFiltering: false,
                      enableFullRowSelection: false,
                      minRowsToShow: 10, 
                      data: null,
                      rowHeight: 30,
                      multiSelect: false,
                      columnDefs: [
                        { field: 'medicamento', displayName: 'Medicamento', width: '35%',  type:'object' },
                        { field: 'unidad', displayName: 'Medida' },
                        { field: 'cantidad', displayName: 'Cantidad' },
                        { field: 'indicaciones', displayName: 'Indicaciones', width: '30%' }
                        // { field: 'accion', displayName: 'Acción', enableCellEdit: false, 
                        //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCestaMED(row)"> <i class="fa fa-trash"></i> </button>' 
                        // }
                      ]
                      ,onRegisterApi: function(gridApiReceta) { 
                        $scope.gridApiReceta = gridApiReceta; 
                      }
                    };
                    atencionMedicaAmbServices.sListarRecetasDeAtencion(arrParams).then(function (rpta) { 
                      $scope.gridOptionsFichaReceta.data = rpta.datos; 
                    });
                    // $scope.reloadGrid = function () { // console.log('click med');
                    //   $interval( function() { 
                    //       $scope.gridApiReceta.core.handleWindowResize();
                    //   }, 50, 5);
                    // }
                    $scope.getTableHeight = function() { 
                       var rowHeight = 30; // your row height 
                       var headerHeight = 30; // your header height 
                       return { 
                          height: ($scope.gridOptionsFichaDiagnostico.data.length * rowHeight + headerHeight + 30) + "px" 
                       }; 
                    }; 
                    $scope.getTableHeightRM = function() { 
                       var rowHeight = 30; // your row height 
                       var headerHeight = 30; // your header height 
                       return { 
                          height: ($scope.gridOptionsFichaReceta.data.length * rowHeight + headerHeight + 30) + "px" 
                       }; 
                    };
                  }
                });
              }
            }
          });
        }
      }); 
    }
    $scope.btnImprimirFichaAtencion = function (mySelectionAtencionFichaGrid) { 
      var arrParams = {
        titulo: 'FICHA DE ATENCION',
        url: angular.patchURLCI+'CentralReportesMPDF/report_ficha_atencion',
        datos: {
          filas : mySelectionAtencionFichaGrid,
          titulo: 'FICHA DE ATENCION',
          tituloAbv: 'AM-FAM'
        },
        metodo: 'php'
      }; 
      ModalReporteFactory.getPopupReporte(arrParams); 
    }
    $scope.btnAnularAtencion = function () { 
      var pMensaje = '¿Realmente desea ELIMINAR LA ATENCION Y RESTAURAR LA VENTA A NO ATENDIDO?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          historialAtencionesServices.sAnular($scope.mySelectionPAHGrid).then(function (rpta) {
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.getPaginationServerSidePAH(true);
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
  }])
  .service("historialAtencionesServices",function($http, $q) {
    return({
        sListarPacientesAtendidos: sListarPacientesAtendidos,
        sListarAtencionMedicaPorId : sListarAtencionMedicaPorId,
        sValidarPermisosEditAtencion: sValidarPermisosEditAtencion, 
        sAnular: sAnular,
        sCambiarEmpresa: sCambiarEmpresa
    });
    function sListarPacientesAtendidos (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedicaHistorial/lista_atencion_medica_historial", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAtencionMedicaPorId (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedicaHistorial/lista_atencion_medica_por_id", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sValidarPermisosEditAtencion(datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedicaHistorial/validar_permisos_edicion_atencion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedicaHistorial/anular_atencion_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarEmpresa(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedicaHistorial/cambiar_empresa_de_atencion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });