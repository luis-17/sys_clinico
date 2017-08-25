angular.module('theme.atencionExamenAux', ['theme.core.services'])
  .controller('atencionExamenAuxController', ['$scope', '$route', '$filter', '$uibModal', '$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'blockUI', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'atencionExamenAuxServices', 
    'atencionMedicaAmbServices',
    'afeccionServices',
    'solicitudExamenServices', 
    'clienteServices',
    'empleadoSaludServices',
    function($scope, $route, $filter, $uibModal, $sce, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, blockUI, uiGridConstants, pinesNotifications, hotkeys, 
      atencionExamenAuxServices,
      atencionMedicaAmbServices,
      afeccionServices,
      solicitudExamenServices,
      clienteServices,
      empleadoSaludServices
    ){ 
    'use strict';
    // console.log('load controller'); 
    $scope.tabs = { 
      'estadoAtencionMedica': 'enabled',
      'estadoSubidaDoc': 'disabled',
    };
    $scope.obj = {};
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

    $scope.reloadGrid = function () { // console.log('click med');
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
    $scope.reloadGrid();
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
        $scope.fBusqueda.arrTipoProductos = [11,14,15]; // EXAMENES AUXILIARES 
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
        arrTipoProductos: [11,14,15]
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
    $scope.fBusquedaPAD.idTipoAtencion = 'EA';
    $scope.btnRegresarAlInicio = function () {
      //$route.reload();
      $scope.registroFormularioAMA = false; 
      $scope.registroFormularioAP = false; 
      $scope.reloadGrid(); 
      $scope.fBusqueda.paciente = null;
      $scope.fBusqueda.numeroOrden = null;
      $scope.gridOptionsDocumento.data = [];
      $scope.mySelectionGridDEA = [];
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
      atencionExamenAuxServices.sListarHistorialDePaciente($scope.fBusquedaOAT).then(function (rpta) { 
        $scope.gridOptionsOAT.data = rpta.datos; 
      }); 
    }; 

    /* ==================================================== */ 
    /*       FORMULARIO DE RESULTADOS DE EXAMEN AUXILIAR    */ 
    /* ==================================================== */ 
    $scope.btnAtenderAlPaciente = function (estadoAtendido, mySelectionAtencionGrid) { 
      //$scope.mySelectionGrid = [];
      var pEstadoAtendido = estadoAtendido || false;
      $scope.mySelectionGridDEA = [];
      console.log('pEstadoAtendido ', pEstadoAtendido);
      if(pEstadoAtendido === false){ // EN REGISTRAR, VALIDAR SI ES ATENCION DEL DIA O NO 
        if($scope.mySelectionGrid[0].situacion.autorizado === 2){ // NO AUTORIZADO 
          pinesNotifications.notify({ title: 'Bloqueo de Atención', text: 'Esta atención ha sido bloqueada.', type: 'danger', delay: 3000 }); 
          return false;
        }
        var mySelectionAtencionGrid = $scope.mySelectionGrid;
      }
      if( mySelectionAtencionGrid[0].idtipoproducto == 11 || mySelectionAtencionGrid[0].idtipoproducto == 14 || mySelectionAtencionGrid[0].idtipoproducto == 15 ){ // EXAMEN AUXILIAR 
        $scope.registroFormularioAMA = true; 
        $scope.registroFormularioAP = false; 
        $scope.titleForm = 'Resultados del Examen Auxiliar'; 
        // console.log($scope.registroFormularioAMA);
        if(pEstadoAtendido && pEstadoAtendido === 'si'){
          console.log('atendido ', pEstadoAtendido);
          $scope.fData = mySelectionAtencionGrid[0];
          $scope.fData.boolNumActoMedico = true; 
          $scope.tabs = { 
            'estadoAtencionMedica': 'enabled',
            'estadoSubidaDoc': 'enabled',
          };
          $scope.getPaginationServerSideDEA();
        }else{ 

          $scope.fData = $scope.mySelectionGrid[0];
          $scope.fData.num_acto_medico = '-- SIN REGISTRAR --';
          $scope.fData.boolNumActoMedico = false;
          $scope.fData.id_area_hospitalaria = 1;
          $scope.fData.area_hospitalaria = 'CONSULTA EXTERNA';
          $scope.fData.fechaAtencion = $filter('date')(new Date(),'dd-MM-yyyy'); 
          $scope.fData.tipoResultado = $scope.listaTipoResultado[0].id;
          $scope.tabs = { 
            'estadoAtencionMedica': 'enabled',
            'estadoSubidaDoc': 'disabled',
          };
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
             
            
          });
          $scope.mySelectionGridAfe = [];
        };
        $scope.getPaginationServerSideAfe();

        /************************************************/
        $scope.formSolicitudExamenAux = false;
        $scope.grabarAtencionExamenAux = function () {
         
          var arrParam = {
            datos : $scope.fData,
            archivos : $scope.archivos
          }
          if( $scope.fData.boolNumActoMedico ){ // ========================== EDITAR 
            // console.log('editar'); 
            atencionExamenAuxServices.sEditarAtencionExamenAux(arrParam).then(function (rpta) { 
              $scope.fData.boolNumActoMedico = true; 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                if( rpta.idatencionmedica ){ 
                  $scope.fData.num_acto_medico = rpta.idatencionmedica;
                  $scope.tabs = { 
                    'estadoAtencionMedica': 'enabled',
                    'estadoSubidaDoc': 'enabled',
                  };
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
          }else{ // ======================================== REGISTRAR 
            // console.log('registrar');
            atencionExamenAuxServices.sRegistrarAtencionExamenAux($scope.fData).then(function (rpta) { 
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
                    'estadoSubidaDoc': 'enabled',
                  };
                  console.log('tab', $scope.tabs);
                  
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
      }else{ // PROCEDIMIENTO CLINICO 
        alert('ESTA ATENCION NO ES POR EXAMEN AUXILIAR.');
      }
    }
    $scope.numberFormat = function(monto, decimales){
      monto += ''; // por si pasan un numero en vez de un string
      monto = parseFloat(monto.replace(/[^0-9\.\-]/g, '')); // elimino cualquier cosa que no sea numero o punto
      decimales = decimales || 0; // por si la variable no fue pasada
      // si no es un numero o es igual a cero retorno el mismo cero
      if (isNaN(monto) || monto === 0) 
          return parseFloat(0).toFixed(decimales);
      // si es mayor o menor que cero retorno el valor formateado como numero
      monto = '' + monto.toFixed(decimales);
      var monto_partes = monto.split('.'),
          regexp = /(\d+)(\d{3})/;
      while (regexp.test(monto_partes[0]))
          monto_partes[0] = monto_partes[0].replace(regexp, '$1' + ',' + '$2');
      return monto_partes.join('.');
    }
    $scope.subidaDoc = function(){
     
     // console.log('files', $scope.obj.flow.files);
      //$scope.fData.fInputs.gridDiagnostico = $scope.gridOptionsDiagnostico.data;
      // $scope.archivos = [];
      // angular.forEach($scope.obj.flow.files, function(value,index){
      //   $scope.archivos.push(value.file.name);
      // });
      var formData = new FormData();
      
      // angular.forEach($scope.fData,function (index,val) { 
      //   formData.append(val,index);
      // });
      formData.append('num_acto_medico',$scope.fData.num_acto_medico);
      formData.append('archivo',$scope.fData.archivo);
      console.log('data', formData);
      // var arrParam = {
      //   datos: $scope.formData,
      //   archivos: $scope.archivos,
      // }
      atencionExamenAuxServices.sSubirArchivosAtencionExamenAux(formData).then(function (rpta) {
        if(rpta.flag == 1){
          var pTitle = 'OK!';
          var pType = 'success';
          $scope.getPaginationServerSideDEA();
        }else if(rpta.flag == 0){
          var pTitle = 'Error!';
          var pType = 'danger';
        }else{
          alert('Se ha producido un problema. Contacte con el Area de Sistemas');
        }
        
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
        $scope.fData.archivo = null;
      });
    }
    /* DOCUMENTOS */ 
    var paginationOptionsDEA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGridDEA = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsDocumento.enableFiltering = !$scope.gridOptionsDocumento.enableFiltering;
      $scope.gridApiDE.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.dirDocExamenesAux = $scope.dirImages + "dinamic/atencion_medica/examenes_auxiliares/";
    $scope.dirIconoFormat = $scope.dirImages + "formato-imagen/";
    $scope.gridOptionsDocumento = { 
      message:'No se encontraron documentos.',
      minRowsToShow: 6,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'idatencionarchivo', name: 'idatencionarchivo', displayName: 'ID', width: '5%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'titulo', name: 'titulo', displayName: 'TITULO DEL DOCUMENTO', enableFiltering: true },
        { field: 'fecha_subida', name: 'fecha_subida', displayName: 'FECHA SUBIDA',width: '10%', enableFiltering: false },
        { field: 'username', name: 'username', displayName: 'USUARIO SUBIDA',width: '10%', enableFiltering: true },
        { field: 'archivo', name: 'archivo', displayName: 'DESCARGAR',width: '8%', enableFiltering: false, enableSorting: false, type: 'object',
          cellTemplate:'<div><a target="_blank" href="{{ grid.appScope.dirDocExamenesAux + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </a> </div>' }
      ],
      onRegisterApi: function(gridApiDE) {
        $scope.gridApiDE = gridApiDE;
        gridApiDE.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridDEA = gridApiDE.selection.getSelectedRows();
        });
        gridApiDE.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridDEA = gridApiDE.selection.getSelectedRows();
        });
        $scope.gridApiDE.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsDEA.sort = null;
            paginationOptionsDEA.sortName = null;
          } else {
            paginationOptionsDEA.sort = sortColumns[0].sort.direction;
            paginationOptionsDEA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideDEA();
        });
        gridApiDE.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsDEA.pageNumber = newPage;
          paginationOptionsDEA.pageSize = pageSize;
          paginationOptionsDEA.firstRow = (paginationOptionsDEA.pageNumber - 1) * paginationOptionsDEA.pageSize;
          $scope.getPaginationServerSideDEA();
        });
        $scope.gridApiDE.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsDEA.search = true;
          paginationOptionsDEA.searchColumn = { 
            'idatencionarchivo' : grid.columns[1].filters[0].term,
            'titulo' : grid.columns[2].filters[0].term,
            'fecha_subida' : grid.columns[3].filters[0].term
          }
          $scope.getPaginationServerSideDEA();
        });
      }
    };
    paginationOptionsDEA.sortName = $scope.gridOptionsDocumento.columnDefs[2].name;
    $scope.getPaginationServerSideDEA = function() {
      //blockUI.start();
      var arrParams = {
        paginate : paginationOptionsDEA,
        datos: { idatencionmedica: $scope.fData.num_acto_medico }

      };
      atencionExamenAuxServices.sListarDocumentosAtencionExamenAuxiliar(arrParams).then(function (rpta) {
        $scope.gridOptionsDocumento.totalItems = rpta.paginate.totalRows.contador;
        $scope.gridOptionsDocumento.data = rpta.datos;
        if( !$scope.gridOptionsDocumento.data.length ){
          $scope.gridOptionsDocumento.message = 'No se encontraron documentos.';
        }
        //blockUI.stop();
      });
    };

    $scope.btnNuevo = function () {
      $scope.mySelectionGridDEA = [];
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'atencionMedica/ver_popup_subida_documentos_formulario',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.fDataDEA = {};
          $scope.titleForm = 'Registro de Archivos - Documentos'; 
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            blockUI.start('Subiendo los archivos...');
            var formData = new FormData();
            
            formData.append('num_acto_medico',$scope.fData.num_acto_medico);
            formData.append('titulo',$scope.fDataDEA.titulo);
            formData.append('archivo',$scope.fDataDEA.archivo);
            console.log('data', formData);
            // var arrParam = {
            //   datos: $scope.formData,
            //   archivos: $scope.archivos,
            // }
            atencionExamenAuxServices.sSubirArchivosAtencionExamenAux(formData).then(function (rpta) {
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.cancel();
                $scope.btnNuevo();
                $scope.getPaginationServerSideDEA();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Se ha producido un problema. Contacte con el Area de Sistemas');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              $scope.fData.archivo = null;
            });


            
            blockUI.stop();
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          atencionExamenAuxServices.sAnularArchivoAtencionExamenAux($scope.mySelectionGridDEA).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSideDEA();
                
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
          $scope.mySelectionGridDEA = [];
        }
      });
    }
    //$scope.getPaginationServerSideDEA();
  }])
  .service("atencionExamenAuxServices",function($http, $q) {
    return({
        // sListarPacientesSinAtender: sListarPacientesSinAtender,
        // sListarPacientesAtendidos: sListarPacientesAtendidos,
        // sListarHistorialDePaciente: sListarHistorialDePaciente,
        sRegistrarAtencionExamenAux: sRegistrarAtencionExamenAux,
        sEditarAtencionExamenAux: sEditarAtencionExamenAux,
        sSubirArchivosAtencionExamenAux: sSubirArchivosAtencionExamenAux,
        sAnularArchivoAtencionExamenAux: sAnularArchivoAtencionExamenAux,
        sListarDocumentosAtencionExamenAuxiliar: sListarDocumentosAtencionExamenAuxiliar,
    });
    // function sListarPacientesSinAtender(datos) { 
    //   var request = $http({ 
    //         method : "post",
    //         url : angular.patchURLCI+"atencionMedica/lista_pacientes_no_atendidos", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }
    // function sListarPacientesAtendidos (datos) {
    //   var request = $http({ 
    //         method : "post",
    //         url : angular.patchURLCI+"atencionMedica/lista_pacientes_atendidos_del_dia", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }
    // function sListarHistorialDePaciente (datos) { 
    //   var request = $http({ 
    //         method : "post",
    //         url : angular.patchURLCI+"atencionMedica/lista_historial_pacientes", 
    //         data : datos 
    //   }); 
    //   return (request.then( handleSuccess,handleError )); 
    // }
    function sRegistrarAtencionExamenAux (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/registrar_atencion_examen_auxiliar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sEditarAtencionExamenAux (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/editar_atencion_examen_auxiliar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sSubirArchivosAtencionExamenAux (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/subir_archivos_atencion_examen_auxiliar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularArchivoAtencionExamenAux (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMedica/anular_archivos_atencion_examen_auxiliar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDocumentosAtencionExamenAuxiliar (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedica/lista_archivos_atencion_examen_auxiliar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });