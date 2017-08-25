angular.module('theme.atencionCITT', ['theme.core.services'])
  .controller('atencionCittController', ['$scope', '$route', '$filter', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'atencionCittServices', 
    'atencionMedicaAmbServices',
    'solicitudExamenServices', 
    'clienteServices',
    'empleadoSaludServices',
    function($scope, $route, $filter, $sce, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      atencionCittServices,
      atencionMedicaAmbServices,
      solicitudExamenServices,
      clienteServices,
      empleadoSaludServices
    ){ 
    'use strict';
    $scope.tabs = { 
      'estadoAtencionMedica': 'enabled',
    };
    $scope.isRegisterSuccess = false; 
    $scope.fBusqueda = {};
    $scope.fBusquedaPAD = {};
    $scope.fData = {}; // ATENCION MEDICA 
    $scope.listaFiltroBusqueda = [ 
      { id:'PH', descripcion:'POR N° DE HISTORIA' },
      { id:'PP', descripcion:'POR PACIENTE' },
      { id:'PNO', descripcion:'POR N° DE ORDEN' }
    ]; 
    $scope.fBusqueda.tipoBusqueda = $scope.listaFiltroBusqueda[0].id;
    $scope.listaTipoResultado = [
      { id:1, descripcion:'NORMAL' },
      { id:2, descripcion:'PATOLOGICO' }
    ];
    $scope.showOrden = false;
    $scope.showHistoria = true;
    $scope.showPaciente = false;
    $scope.registroFormularioAMA = false;
    $scope.registroFormularioAP = false;

    $scope.reloadGrid = function () {
      $interval( function() { 
          $scope.gridApiPAD.core.handleWindowResize();
          $scope.gridApi.core.handleWindowResize();
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
    $scope.btnConsultarPacientesAtencion = function () { 
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
      }
      if( validateButton ){ 
        // PACIENTES SIN ATENDER 
        $scope.fBusqueda.arrTipoProductos = [13]; // DOCUMENTOS 
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
    $scope.btnToggleFilteringPAD = function(){ 
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
        arrTipoProductos: [13]
      };
      return clienteServices.sListarClienteVentaAutoComplete(params).then(function(rpta) { 
        $scope.noResultsLPACI = false; 
        if( rpta.flag === 0 ){ 
          $scope.noResultsLPACI = true; 
        } else if( rpta.flag === 2 ){
          pinesNotifications.notify({ title: 'Advertencia', text: rpta.message, type: 'warning', delay: 2000 });
        }
        return rpta.datos; 
      });
    }
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
    $scope.listaTipoAtencionMedica = [ 
      { 'id': 'ALL', 'descripcion': '--TODOS--' }, 
      { 'id': 'CM', 'descripcion': 'CONSULTA MEDICA' }, 
      { 'id': 'P', 'descripcion': 'PROCEDIMIENTO' }, 
      { 'id': 'EA', 'descripcion': 'EXAMEN AUXILIAR' }, 
      { 'id': 'DO', 'descripcion': 'DOCUMENTO' } 
    ]; 
    $scope.fBusquedaPAD.idTipoAtencion = 'DO';
    $scope.btnRegresarAlInicio = function () {
      //$route.reload();
      $scope.registroFormularioAMA = false; 
      $scope.registroFormularioAP = false; 
      $scope.reloadGrid(); 
      $scope.fBusqueda.paciente = null; 
      $scope.fBusqueda.numeroOrden = null; 
    }

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
    // OTRAS ATENCIONES DEL PACIENTE 
    $scope.getPaginationServerSideOAT = function () { 
      $scope.fBusquedaOAT.idhistoria = $scope.fData.idhistoria; 
      atencionCittServices.sListarHistorialDePaciente($scope.fBusquedaOAT).then(function (rpta) { 
        $scope.gridOptionsOAT.data = rpta.datos; 
      }); 
    }; 

    /* ==================================================== */ 
    /*       FORMULARIO DE RESULTADOS DE CITT    */ 
    /* ==================================================== */ 
    $scope.btnAtenderAlPaciente = function (estadoAtendido, mySelectionAtencionGrid) { 
      //$scope.mySelectionGrid = [];
      var pEstadoAtendido = estadoAtendido || false;
      //$scope.gridOptionsExamenAuxiliar.data = [];
      if(pEstadoAtendido === false){ // EN REGISTRAR, VALIDAR SI ES ATENCION DEL DIA O NO 
        if($scope.mySelectionGrid[0].situacion.autorizado === 2){ // NO AUTORIZADO 
          pinesNotifications.notify({ title: 'Bloqueo de Atención', text: 'Esta atención ha sido bloqueada.', type: 'danger', delay: 3000 }); 
          return false;
        }
        var mySelectionAtencionGrid = $scope.mySelectionGrid;
      }
      console.log(mySelectionAtencionGrid);
      if( mySelectionAtencionGrid[0].idtipoproducto == 13 ){ // CITT 
        $scope.registroFormularioAMA = true; 
        $scope.registroFormularioAP = false;
        $scope.documentoCitt = false;
        $scope.titleForm = 'Resultados del CITT';
       
        if(pEstadoAtendido && pEstadoAtendido === 'si'){ 
          $scope.fData = mySelectionAtencionGrid[0];
          $scope.fData.boolNumActoMedico = true;
          // SI EL DOCUMENTO ES UN DESCANSO MEDICO (CITT) ACTIVAR FLAG 'documentoCitt'
          if(mySelectionAtencionGrid[0].producto.indexOf('DESCANSO MEDICO') !== -1){
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
          
        }else{ 
          $scope.fData = $scope.mySelectionGrid[0];
          // SI EL DOCUMENTO ES UN DESCANSO MEDICO (CITT) ACTIVAR FLAG 'documentoCitt'
          if(mySelectionAtencionGrid[0].producto.indexOf('DESCANSO MEDICO') !== -1){
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
          
          $scope.fData.num_acto_medico = '-- SIN REGISTRAR --';
          $scope.fData.boolNumActoMedico = false;
          $scope.fData.id_area_hospitalaria = 1;
          $scope.fData.area_hospitalaria = 'CONSULTA EXTERNA';
          $scope.fData.fechaAtencion = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fData.tipoResultado = $scope.listaTipoResultado[0].id;
        }
        $scope.formSolicitudExamenAux = false;
        $scope.grabarAtencionCITT = function () { 
          //$scope.fData.fInputs.gridDiagnostico = $scope.gridOptionsDiagnostico.data;
          if( $scope.fData.boolNumActoMedico ){ // ================================= EDITAR 
            // console.log('editar'); 
            atencionCittServices.sEditarAtencionCitt($scope.fData).then(function (rpta) { 
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
            atencionCittServices.sRegistrarAtencionCitt($scope.fData).then(function (rpta) { 
              $scope.fData.boolNumActoMedico = false; 
              if(rpta.flag == 1) { 
                var pTitle = 'OK!';
                var pType = 'success';
                if( rpta.idatencionmedica ){
                  $scope.isRegisterSuccess = true;
                  $scope.fData.num_acto_medico = rpta.idatencionmedica;
                  $scope.fData.boolNumActoMedico = true;
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
          }
        }
        $scope.imprimir = function () {
          var f = new Date();
          var hora = f.getHours();
          var minuto = f.getMinutes();
          var segundo = f.getSeconds();
          var str_segundo = new String (segundo) 
          if (str_segundo.length == 1) 
              segundo = "0" + segundo 

          var str_minuto = new String (minuto) 
          if (str_minuto.length == 1) 
              minuto = "0" + minuto 

          var str_hora = new String (hora) 
          if (str_hora.length == 1) 
              hora = "0" + hora 

          var horaImprimible = hora + ":" + minuto + ":" + segundo 
          if( $scope.fData.num_acto_medico ){
            atencionCittServices.sImprimirDescansoMedico($scope.fData).then(function (rpta) {
              console.log(rpta.html);
              var html = rpta.html;
            });
          }
        }
      }else{ // PROCEDIMIENTO CLINICO 
        alert('ESTA ATENCION NO ES POR EXAMEN AUXILIAR.');
      }
    } 
  }])
  .service("atencionCittServices",function($http, $q) {
    return({
      sRegistrarAtencionCitt: sRegistrarAtencionCitt,
      sEditarAtencionCitt: sEditarAtencionCitt,
      sImprimirDescansoMedico: sImprimirDescansoMedico
    });
   
    function sRegistrarAtencionCitt (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/registrar_atencion_documentos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sEditarAtencionCitt (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/editar_atencion_documentos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirDescansoMedico (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/imprimir_descanso_medico", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });