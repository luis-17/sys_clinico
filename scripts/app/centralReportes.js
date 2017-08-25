angular.module('theme.centralReportes', ['theme.core.services','ngAnimate', 'ui.bootstrap','isteven-multi-select'])
  .controller('centralReportesController', ['$scope', '$route', '$filter', '$sce', '$interval', '$location', '$anchorScroll','$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ModalReporteFactory',
    'atencionMedicaAmbServices', 
    'empresaAdminServices',
    'empresaServices',
    'usuarioServices',
    'cajaServices',
    'tipoDocumentoServices',
    'empleadoSaludServices',
    'especialidadServices',
    'empleadoServices',
    'productoServices',
    'sedeServices',
    'ubigeoServices',
    'reporteCentralizadoServices',
    'profesionServices',
    'condicionVentaServices',
    'almacenFarmServices',
    'medicamentoAlmacenServices',
    'cronJobServices',
    'laboratorioServices',
    'tipoProductoServices',
    function($scope, $route, $filter, $sce, $interval, $location, $anchorScroll, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ModalReporteFactory,
      atencionMedicaAmbServices,
      empresaAdminServices,
      empresaServices,
      usuarioServices,
      cajaServices,
      tipoDocumentoServices,
      empleadoSaludServices,
      especialidadServices,
      empleadoServices,
      productoServices,
      sedeServices,
      ubigeoServices,
      reporteCentralizadoServices,
      profesionServices,
      condicionVentaServices,
      almacenFarmServices,
      medicamentoAlmacenServices,
      cronJobServices,
      laboratorioServices,
      tipoProductoServices
    ){ 
    'use strict';
    $scope.fBusqueda = {};
    $scope.fBusqueda.fecha = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy'); 
    $scope.fBusqueda.anio = $filter('date')(new Date(),'yyyy');
    $scope.oneAtATime = true; 
    $scope.contRangoAnos = true;
    $scope.fBusqueda.allEmpleados = true;
    $scope.fBusqueda.allStocks = true;
    $scope.fBusqueda.boolStock = false; // desahilitado el checkbox allStock
    $scope.fBusqueda.horaDesdeManana = '08';
    $scope.fBusqueda.horaHastaManana = '13';
    $scope.fBusqueda.minutoDesdeManana = '00';
    $scope.fBusqueda.minutoHastaManana = '00';
    $scope.fBusqueda.horaDesdeTarde = '13';
    $scope.fBusqueda.horaHastaTarde = '19';
    $scope.fBusqueda.minutoDesdeTarde = '00';
    $scope.fBusqueda.minutoHastaTarde = '00'; 

    $scope.fBusqueda.anioDesde = '2016';
    $scope.fBusqueda.anioHasta = $filter('date')(new Date(),'yyyy');;

    $scope.fBusqueda.mostrarTodasSolicitudes = true;
    $scope.fBusqueda.soloEmpActivos = true;

    //CONFIGURACIÓN PARA EL DATEPICKER
    $scope.dateUIDesde = {} ;
    $scope.dateUIDesde.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
    $scope.dateUIDesde.datePikerOptions = {
      formatYear: 'yy',
      'show-weeks': false
    };
    $scope.dateUIDesde.openDP = function($event) {
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIDesde.opened = true;
    };

    $scope.dateUIHasta = {} ;
    $scope.dateUIHasta.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIHasta.format = $scope.dateUIHasta.formats[0]; // formato por defecto
    $scope.dateUIHasta.datePikerOptions = {
      formatYear: 'yy',
      'show-weeks': false
    };
    $scope.dateUIHasta.openDP = function($event) {
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIHasta.opened = true;
    };
    $scope.fBusqueda.desdeEncuesta = new Date();
    $scope.fBusqueda.hastaEncuesta = new Date();
    //FIN DE LA CONFIGURACIÓN

    $scope.fBusqueda.tipoCuadro = 'reporte';
    $scope.fBusqueda.salida = 'pdf';
    $scope.fBusqueda.tiposalida = 'pdf';
    $scope.showDivEmptyData = false;
    $scope.listaEstadisticas = [];
    reporteCentralizadoServices.listarReportesDelUsuarioSession().then(function (rpta) { 
      $scope.listaEstadisticas = rpta.datos;
      // console.log('lista: ', $scope.listaEstadisticas);
      if(rpta.flag == 0){ 
        $scope.showDivEmptyData = true;
      }
    });
    $scope.selectedReport = {};
     // USUARIOS
    $scope.listarUsuariosCajaCbo = function (){
      var arrParams = { 
        idtiporeporte: $scope.selectedReport.idtiporeporte
      };
      usuarioServices.sListarUsuariosCajaCbo(arrParams).then(function (rpta) { 
        $scope.listaUsuariosCaja = rpta.datos;
        $scope.fBusqueda.usuario = $scope.listaUsuariosCaja[0];
        $scope.listarTodasCajasMasterUsuarioCbo();
      });
    }
    // CAJAS
    $scope.listarTodasCajasMasterUsuarioCbo = function (){
      if (!/^\d{2}-\d{2}-\d{4}$/.test($scope.fBusqueda.fecha)){
        // console.log('No es fecha ', $scope.fBusqueda.fecha);
        return;
      }
      if($scope.selectedReport.idtiporeporte == '11'){
        var arrParams = {
          idmodulo: 3, // farmacia
          usuario: $scope.fBusqueda.usuario,
          fecha: $scope.fBusqueda.fecha
        }
      }else{
        var arrParams = {
          idmodulo: 1, // hospital 
          usuario: $scope.fBusqueda.usuario,
          fecha: $scope.fBusqueda.fecha
        }
      }
      
      cajaServices.sListarTodasCajasMasterUsuarioCbo(arrParams).then(function (rpta) { 
        $scope.listaCajas = rpta.datos;
        $scope.listaCajas.splice(0,0,{ id : '0', descripcion:'-- seleccione --'});
        $scope.fBusqueda.caja = $scope.listaCajas[0];
      });  
    }
    
    $scope.listaEmpresaOSede = [ 
      {"id": 'PS',"descripcion": 'POR SEDE'},
      {"id": 'PE',"descripcion": 'POR EMPRESA'},
    ]; 
    $scope.fBusqueda.porEmpresaOSede = $scope.listaEmpresaOSede[0]; 

    $scope.listaFiltroTipoRango = [ 
      {"id": '1',"descripcion": '1 AÑO'},
      {"id": '2',"descripcion": 'RANGO DE AÑOS'},
    ]; 

    $scope.listaTipoAtencion = [  
      { 'id': 'CM', 'descripcion': 'CONSULTA MEDICA' }, 
      { 'id': 'P', 'descripcion': 'PROCEDIMIENTO' } 
    ];
    $scope.selectReport = function (row) { 
      $scope.contRangoAnos = true;
      //console.log('selectedReport ', row);
      $scope.selectedReport = row; 
      // console
      var desde30 = moment().subtract(30,'days'); 
      if(row.id=="AS-APE"){
        $scope.fBusqueda.allEmpresas = false;
        $scope.fBusqueda.allEmpleados = true;
        
        $scope.fBusqueda.desde = $filter('date')(desde30.toDate(),'dd-MM-yyyy');
      } 
      else if(row.id=="AS-REMP" || row.id=="AS-REMT"){
        $scope.fBusqueda.allEmpresas = true;
        $scope.fBusqueda.allEmpleados = false;
        $scope.fBusqueda.desde = $filter('date')(desde30.toDate(),'dd-MM-yyyy');
      }
      else if(row.id=="CE-CNC"){
        // $scope.contRangoAnos = false;
        $scope.fBusqueda.idTipoRango = '1';
      }
      //VALIDACIÓN AGREGADA PARA EVITAR LAS DEMASIADAS CONSULTAS PARA OBTENER "lista_usuarios_caja"
      else if(row.id == "VT-DCP" || row.id == "VT-DC" || row.id == "FAR_VT-DC"){
        $scope.listarUsuariosCajaCbo();
      }
      else if(row.id == "CE-PRPT" || row.id == "CE-ERT"){
        $scope.fBusqueda.tiposalida = 'grafico';
      }
      else if(row.id=="FAR-IVM" || row.id=="FAR-IMU" ){ // INVENTARIOS PARA LIZ
        console.log('selectedReport',$scope.selectedReport);
        $scope.getPaginationServerSide();
      }
      else if(row.id=="FAR-SCV" ){
        $scope.fBusqueda.boolStock = true; // habilitado checkbox allStock para cambiar a stocks positivos o todos
      }
      else if(row.id=="FAR-SMPA" ){
        $scope.fBusqueda.boolStock = false; // Deshabilitado checkbox allStock (solo stocks positivos)
      }else if(row.id=="FAR-MMV" ){
        $scope.fBusqueda.formula_derma = true; // Habilitado checkbox Filtro Formulas dermatológicas
      }else if(row.id=="CE-PCE"){
        $scope.fBusqueda.idTipoRango = '1';
        $scope.fBusqueda.idTipoAtencion = 'CM';
      }else if(row.id=="FAR-CMMA"){
        $scope.fBusqueda.anioDesdeCbo = $scope.listaAnos[0].id;
        $scope.fBusqueda.anioHastaCbo = $scope.listaAnos[1].id;
      }
      //$scope.listarTodasCajasMasterUsuarioCbo();
    } 

    $scope.changeToGraphic = function () { 
      if( $scope.fBusqueda.tipoCuadro == 'grafico' && $scope.selectedReport.id == 'CE-CNC' ){
        if($scope.fBusqueda.idTipoRango == '1'){
          $scope.fBusqueda.pacienteNC = $scope.listaPacientesNC[3];
          $scope.fBusqueda.pacienteNCDisabled = true;
          $scope.listaPacientesNC[3]['isDisabled'] = false;
        }else{
          $scope.fBusqueda.pacienteNC = $scope.listaPacientesNC[0];
          $scope.fBusqueda.pacienteNCDisabled = false;
          $scope.listaPacientesNC[3]['isDisabled'] = true;
        }
        
        // $scope.contRangoAnos = false;
      }else if( $scope.fBusqueda.tipoCuadro == 'reporte' && $scope.selectedReport.id == 'CE-CNC' ){ 
        $scope.fBusqueda.pacienteNC = $scope.listaPacientesNC[0];
        $scope.fBusqueda.pacienteNCDisabled = false;
        $scope.listaPacientesNC[3]['isDisabled'] = false;
        // $scope.contRangoAnos = true;
      }
    }

    // PRODUCTOS QUE REQUIEREN INDICADORES 
    productoServices.sListarProductosIndicadores().then(function (rpta) { 
      $scope.listadoProductos = rpta.datos;
    });

    // TIPO PRODUCTO CM: consulta medica; P: procedimiento; EA: examen auxiliar;  DO: documentos 
    $scope.listaTipoAtencionMedica = [ 
      { 'id': 'ALL', 'descripcion': '--TODOS--' }, 
      { 'id': 'CM', 'descripcion': 'CONSULTA MEDICA' }, 
      { 'id': 'P', 'descripcion': 'PROCEDIMIENTO' }, 
      { 'id': 'EA', 'descripcion': 'EXAMEN AUXILIAR' }, 
      { 'id': 'DO', 'descripcion': 'DOCUMENTO' } 
    ]; 
    $scope.listaAnos = [ 
      { 'id': '2016', 'ano': '2016' },
      { 'id': '2017', 'ano': '2017' },
      { 'id': '2018', 'ano': '2018' },
      { 'id': '2019', 'ano': '2019' },
      { 'id': '2020', 'ano': '2020' }
    ]; 
    $scope.fBusqueda.anioDesdeCbo = $scope.listaAnos[1].id;
    $scope.fBusqueda.anioHastaCbo = $scope.listaAnos[1].id;
    $scope.listaMeses = [
      { 'id': 1, 'mes': 'Enero' },
      { 'id': 2, 'mes': 'Febrero' },
      { 'id': 3, 'mes': 'Marzo' },
      { 'id': 4, 'mes': 'Abril' },
      { 'id': 5, 'mes': 'Mayo' },
      { 'id': 6, 'mes': 'Junio' },
      { 'id': 7, 'mes': 'Julio' },
      { 'id': 8, 'mes': 'Agosto' },
      { 'id': 9, 'mes': 'Septiembre' },
      { 'id': 10, 'mes': 'Octubre' },
      { 'id': 11, 'mes': 'Noviembre' },
      { 'id': 12, 'mes': 'Diciembre' }
    ]; 
    $scope.fBusqueda.mesDesdeCbo = $scope.listaMeses[0].id;
    $scope.fBusqueda.mesHastaCbo = $scope.listaMeses[0].id;
    // console.log($scope.fBusqueda.desdeMes)
    $scope.listaTipoReporte = [
      { 'id': 'v', 'descripcion': 'VENTAS' },
      { 'id': 'p', 'descripcion': 'PRESTACIONES' },
      { 'id': 'tp', 'descripcion': 'TICKET PROMEDIO' }
    ]; 
    $scope.fBusqueda.mes = $scope.listaMeses[0]; 
    $scope.fBusqueda.ventaPrestacion = $scope.listaTipoReporte[0].id; 
    $scope.fBusqueda.idTipoAtencion = 'ALL'; 

    // EMPRESAS 
    empresaServices.sListarEmpresasCbo().then(function (rpta) { 
      $scope.listadoEmpresas = angular.copy(rpta.datos);
      $scope.listaEmpresas = rpta.datos;
      $scope.fBusqueda.empresa = $scope.listaEmpresas[0];
    });
    // UNIDADES DE NEGOCIO
    $scope.listaUnidadesNegocio = [
      { 'id': 'hos', 'descripcion': 'HOSPITAL' },
      { 'id': 'far', 'descripcion': 'FARMACIA' }
    ];
    $scope.fBusqueda.unidadNegocio = $scope.listaUnidadesNegocio[0];
    // SEDES 
    sedeServices.sListarSedeCbo().then(function (rpta) { 
      $scope.listaSedes = rpta.datos;
      $scope.fBusqueda.sede = $scope.listaSedes[0];
      $scope.cargarEmpresaAdminPorSede($scope.fBusqueda.sede);
    });
    // EMPRESAS ADMIN POR SEDE
    $scope.cargarEmpresaAdminPorSede = function($sede){
      empresaAdminServices.sListarEmpresaAdminPorSedeCbo($sede).then(function (rpta) { 
        // $scope.listadoEmpresas = angular.copy(rpta.datos);
        $scope.listaEmpresasAdminSede = rpta.datos;
        $scope.listaEmpresasAdminSede.splice(0,0,{ id : '0', descripcion:'--Todas--'});
        $scope.fBusqueda.empresaAdmin = $scope.listaEmpresasAdminSede[0];
      });  
    }
    // SOLO EMPRESAS ADMIN
    // $scope.cargarEmpresaAdmin = function(){
      empresaServices.sListarEmpresasSoloAdminCbo().then(function (rpta) { 
        // $scope.listadoEmpresas = angular.copy(rpta.datos);
        $scope.listaEmpresasAdmin = rpta.datos;
        $scope.listaEmpresasAdmin.splice(0,0,{ id : '0', descripcion:'-- Seleccione una opción --'});
        $scope.fBusqueda.empresaSoloAdmin = $scope.listaEmpresasAdmin[0];
      });
    // }
    // CONDICIONES DE VENTA - FARMACIA  
    condicionVentaServices.sListarCondicionesCboReporte().then(function (rpta) { 
      $scope.listadoCondicionVenta = rpta.datos;
    });
    // MODALIDAD DE MAS Y MENOS VENDIDOS
    $scope.listaModalidades = [
      { 'id': 'cantidad', 'descripcion': 'POR CANTIDAD' },
      { 'id': 'monto', 'descripcion': 'POR MONTO' },
      //{ 'id': 'denominacion', 'descripcion': 'AMBOS' }
    ];
    $scope.fBusqueda.modalidad = $scope.listaModalidades[0];
    // MODALIDAD DE TIPO DE PRODUCTO
    $scope.listaModalidadTipo = [
      { 'id': 'ALL', 'descripcion': 'TODOS' },
      { 'id': '2', 'descripcion': 'SOLO MEDICAMENTOS' },
      { 'id': '1', 'descripcion': 'SOLO FORMULAS' },
    ];
    $scope.fBusqueda.modalidadTipo = $scope.listaModalidadTipo[0];
    // MODALIDAD DE MESES O DIAS
    $scope.listaModalidadTiempo = [
      { 'id': 'dias', 'descripcion': 'POR DIAS Y HORAS' },
      { 'id': 'meses', 'descripcion': 'POR MESES' },
      //{ 'id': 'denominacion', 'descripcion': 'AMBOS' }
    ];
    $scope.fBusqueda.modalidadTiempo = $scope.listaModalidadTiempo[0];
    // TOP DE MEDICAMENTOS MAS Y MENOS VENDIDOS
    $scope.listaTops = [
      // { 'id': '5', 'descripcion': '5' },
      { 'id': '10', 'descripcion': '10' },
      { 'id': '20', 'descripcion': '20' },
      { 'id': '50', 'descripcion': '50' },
      { 'id': '0', 'descripcion': 'Todos' }
    ];
    $scope.fBusqueda.top = $scope.listaTops[0];
    // PROFESIONES 
    profesionServices.sListarProfesionesCbo().then(function (rpta) { 
      $scope.listadoProfesiones = rpta.datos;
    });

    // TIPOS DE CONTRATO 
    $scope.listadoTipoContrato = [
      { id: 'EN PLANILLA', descripcion: 'EN PLANILLA',name: '<b>EN PLANILLA</b>' },
      { id: 'POR LOCACION DE SERVICIOS', descripcion: 'POR LOCACION DE SERVICIOS',name: '<b>POR LOCACION DE SERVICIOS</b>' },
      { id: 'PRACTICANTE', descripcion: 'PRACTICANTE',name: '<b>PRACTICANTE</b>' },
      { id: 'OTROS', descripcion: 'OTROS',name: '<b>OTROS</b>' }
    ];

    // RANGO DE EDADES - MENORES DE EDAD 
    $scope.listadoRangoEdad = [
      { id: '0-5', descripcion: 'DE 0 A 5',name: '<b>DE 0 A 5</b>' },
      { id: '5-10', descripcion: 'DE 5 A 10',name: '<b>DE 5 A 10</b>' },
      { id: '10-15', descripcion: 'DE 10 A 15',name: '<b>DE 10 A 15</b>' },
      { id: '15-18', descripcion: 'DE 15 A 18',name: '<b>DE 15 A 18</b>' }
    ]; 

    // NUEVO - CONTINUADOR 
    $scope.listaPacientesNC = [ 
      { id: 'none', descripcion: '--Seleccione--' },
      { id: 'PN', descripcion: 'PACIENTE NUEVO', 'isDisabled': false },
      { id: 'PC', descripcion: 'PACIENTE CONTINUADOR', 'isDisabled': false },
      { id: 'ALL', descripcion: 'AMBOS', 'isDisabled': false }
    ]; 
    $scope.fBusqueda.pacienteNC = $scope.listaPacientesNC[0];

    // LOGICA PACIENTES NUEVO Y CONTINUADOR 
    $scope.listaLogicaPacientesNC = [ 
      // { id: 'none', descripcion: '--Seleccione--' },
      { id: 'R', descripcion: 'PACIENTES REGISTRADOS EN EL SISTEMA, EN DETERMINADA FECHA' }, // SE CONSIDERA P.N, 
      { id: 'RV', descripcion: 'PACIENTES REGISTRADOS EN EL SISTEMA, Y COMPRARON AL MENOS UN TICKET' }, // SE CONSIDERA P.N, 
      { id: 'RVA', descripcion: 'PACIENTES REGISTRADOS EN EL SISTEMA, COMPRARON AL MENOS UN TICKET Y SE ATENDIERON' } // SE CONSIDERA P.N, 
    ]; 
    $scope.fBusqueda.logicaPacienteNC = $scope.listaLogicaPacientesNC[1];

    //LISTA DE TABLETS Y AGRUPACIÓN POR DÍA Y MES
    $scope.listaTablets = [ 
      { id: 'ALL', descripcion: 'TODAS LAS TABLETS' },
      { id: '1', descripcion: 'TABLET 1' },
      { id: '2', descripcion: 'TABLET 2' },
      { id: '3', descripcion: 'TABLET 3' },
      { id: '4', descripcion: 'TABLET 4' },
      { id: '5', descripcion: 'TABLET 5' },
      { id: '6', descripcion: 'TABLET 6' }
    ]; 
    $scope.fBusqueda.tablet = $scope.listaTablets[0];

    $scope.listaAgrupar = [ 
      { id: 'dia', descripcion: 'DÍA' },
      { id: 'mes', descripcion: 'MES' }
    ]; 
    $scope.fBusqueda.agrupar = $scope.listaAgrupar[0];
    $scope.listaPreguntas = [
    {id: '1', descripcion: 'CÓMO TE ATENDIÓ CAJA HOY?'},
    {id: '2', descripcion: 'CÓMO TE ATENDIÓ TU MEDICO HOY?'},
    {id: '3', descripcion: '¿CÓMO TE ATENDIÓ INFORMES HOY?'}];

    $scope.listaLogicaGraficoCumplProg = [ 
      { id: 'APG', descripcion: 'PROGRAMADO VS ATENDIDO - TODO' },  
      { id: 'APM', descripcion: 'PROGRAMADO VS ATENDIDO - MAÑANA' }, 
      { id: 'APT', descripcion: 'PROGRAMADO VS ATENDIDO - TARDE' }, 
      { id: 'AMAT', descripcion: 'ATENDIDO MAÑANA VS ATENDIDO TARDE' }, 
      { id: 'PCT', descripcion: '% CUMPLIMIENTO - TURNO' }, 
    ];
    $scope.fBusqueda.logicaGraficoCumplProg = $scope.listaLogicaGraficoCumplProg[0];

    $scope.fBusqueda.pregunta = $scope.listaPreguntas[0];

    // LABORATORIO
    laboratorioServices.sListarlaboratorioCbo().then(function(rpta){
      $scope.listaLaboratorio =rpta.datos;
      $scope.listaLaboratorio.splice(0,0,{ id : '0', descripcion:'-- Todos --'}); 
      $scope.fBusqueda.laboratorio = $scope.listaLaboratorio[0];
    });

    // TIPO PRODUCTO
    var paramDatos = { modulo: 3 } //Farmacia
    tipoProductoServices.sListarTipoProductoCbo(paramDatos).then(function (rpta){
      $scope.listaBusquedaTipoProductos = rpta.datos;
      $scope.listaBusquedaTipoProductos.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
      $scope.fBusqueda.tipoProducto = $scope.listaBusquedaTipoProductos[0];
    });

    $scope.listaTurno = [
      { 'id': '0', 'descripcion': '-- Todos --' },
      { 'id': '1', 'descripcion': 'MAÑANA' },
      { 'id': '2', 'descripcion': 'TARDE' },
    ];
    $scope.fBusqueda.turno = $scope.listaTurno[0];
    // ==========================UBIGEO ===========================
      $scope.getDepartamentoAutocomplete = function (value) {
        var params = {
          search: value,
          sensor: false
        }
        return ubigeoServices.sListarDepartamentoPorAutocompletado(params).then(function(rpta) { 
          $scope.noResultsLD = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLD = true;
          }
          return rpta.datos; 
        });
      }
      $scope.obtenerDepartamentoPorCodigo = function () {
        if( !($scope.fBusqueda.iddepartamento) ){
          $scope.fBusqueda.iddepartamento = '14';
        }
        if( $scope.fBusqueda.iddepartamento ){
          var arrData = {
            'codigo': $scope.fBusqueda.iddepartamento
          }
          ubigeoServices.sListarDepartamentoPorCodigo(arrData).then(function (rpta) {
            if( rpta.flag == 1){
              $scope.fBusqueda.iddepartamento = rpta.datos.id;
              $scope.fBusqueda.departamento = rpta.datos.descripcion;
              $('#fDatadepartamento').focus();
            }
          });
        }
      }
      $scope.getSelectedDepartamento = function ($item, $model, $label) {
          $scope.fBusqueda.iddepartamento = $item.id;
          $scope.fBusqueda.idprovincia = null;
          $scope.fBusqueda.provincia = null;
          $scope.fBusqueda.iddistrito = null;
          $scope.fBusqueda.distrito = null;
      };

      $scope.getProvinciaAutocomplete = function (value) {
        var params = {
          search: value,
          id: $scope.fBusqueda.iddepartamento,
          sensor: false
        }
        return ubigeoServices.sListarProvinciaPorAutocompletado(params).then(function(rpta) { 
          $scope.noResultsLP = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLP = true;
          }
          return rpta.datos; 
        });
      }
      $scope.obtenerProvinciaPorCodigo = function () {
        if( !($scope.fBusqueda.idprovincia) ){
          $scope.fBusqueda.idprovincia = '01';
        }
        if( $scope.fBusqueda.idprovincia ){
          var arrData = {
            'codigo': $scope.fBusqueda.idprovincia,
            'iddepartamento': $scope.fBusqueda.iddepartamento
          }
          ubigeoServices.sListarProvinciaDeDepartamentoPorCodigo(arrData).then(function (rpta) {
            if( rpta.flag == 1){
              $scope.fBusqueda.idprovincia = rpta.datos.id;
              $scope.fBusqueda.provincia = rpta.datos.descripcion;
              $scope.listarDistritos();
              $('#fDataprovincia').focus();
            }
          });
        }
      }
      $scope.getSelectedProvincia = function ($item, $model, $label) {
          $scope.fBusqueda.idprovincia = $item.id;
          $scope.fBusqueda.iddistrito = null;
          $scope.fBusqueda.distrito = null;
          $scope.listarDistritos();
      };
      //Cargamos distritos 
      $scope.listarDistritos = function () { 
        if( $scope.fBusqueda.idprovincia && $scope.fBusqueda.iddepartamento ){ 
          var params = {
            // search: null,
            id_dpto: $scope.fBusqueda.iddepartamento,
            id_prov: $scope.fBusqueda.idprovincia,
            sensor: false
          }; 
          ubigeoServices.sListarDistritoPorAutocompletado(params).then(function (rpta) { 
            $scope.listaDistritos = rpta.datos;
            $scope.fBusqueda.distrito = $scope.listaDistritos[0];
          });
        }
      }
      
      $scope.obtenerDepartamentoPorCodigo();
      $scope.obtenerProvinciaPorCodigo();
    // ===========================FIN UBIGEO=======================
    $scope.getEmpleadoAutocomplete = function (value) { 
      var params = {
        search: value, 
        sensor: false,
        empresa: $scope.fBusqueda.empresa
      }
      return empleadoServices.sListarEmpleadosCbo(params).then(function(rpta) { 
        $scope.noResultsLE = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLE = true;
        }
        return rpta.datos; 
      });
    }
    // SEDES - EMPRESAS ADMIN 
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { 
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin; 
      
    });

    // TIPO DOCUMENTO 
    tipoDocumentoServices.sListarTipoDocumentoVentaCbo().then(function (rpta) { 
      $scope.listaTipoDoc = rpta.datos;
      $scope.listaTipoDoc.splice(0,0,{ id : 'all', descripcion:'-- TODOS --'});
      //console.log($scope.listaTipoDoc,'$scope.listaTipoDoc');
      //$scope.fBusqueda.tipodocumento = $scope.listaTipoDoc[0];
    });
    // EMPRESA/ESPECIALIDAD 
    especialidadServices.sListarEspecialidadesRestriccionesCbo().then(function (rpta) { 
      $scope.listaEmpresaEspecialidades = rpta.datos;
      if( !($scope.fSessionCI.key_group == 'key_salud') && !($scope.fSessionCI.key_group == 'key_dir_esp') ){ 
        $scope.listaEmpresaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
      }
      $scope.fBusqueda.empresaespecialidad = $scope.listaEmpresaEspecialidades[0];
      $scope.getListaMedicos();
    });
    // EMPRESA/ESPECIALIDAD AMARRADO A EMPRESA ADMIN
    $scope.getEmpresaEspecialidad = function(empresaSoloAdmin){
      console.log(empresaSoloAdmin);
      especialidadServices.sListarEspecialidadesRestriccionesCbo(empresaSoloAdmin).then(function (rpta) { 
        $scope.listaEmpEspecialidadPorEmpresa = rpta.datos;
        if( !($scope.fSessionCI.key_group == 'key_salud') && !($scope.fSessionCI.key_group == 'key_dir_esp') ){ 
          $scope.listaEmpEspecialidadPorEmpresa.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
        }
        $scope.fBusqueda.empresaespecialidad = $scope.listaEmpEspecialidadPorEmpresa[0];
      });
      
    }
    // ESPECIALIDAD 
    especialidadServices.sListarEspecialidadesCbo().then(function (rpta) { 
      $scope.listadoEspecialidades = angular.copy(rpta.datos); // PARA SELECT MULTIPLE -- para corregir que obedezca a la sede
      $scope.listaEspecialidadSolicitud = angular.copy(rpta.datos); // PARA SOLICITUDES(GERENCIA COMERC) -- para corregir que obedezca a la sede
      //console.log($scope.listadoEspecialidades,'$scope.listadoEspecialidades');
      $scope.listaEspecialidades = rpta.datos;
      if( !($scope.fSessionCI.key_group == 'key_salud') && !($scope.fSessionCI.key_group == 'key_dir_esp') ){ 
        $scope.listaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
      }
      $scope.fBusqueda.especialidad = $scope.listaEspecialidades[0];
      $scope.fBusqueda.especialidadSolicitud = $scope.listaEspecialidadSolicitud[0];
      $scope.getListaMedicosSoloEsp();
    });

    if( !($scope.fSessionCI.key_group == 'key_salud') ){ 
      $scope.contFiltroMedico = true;
    }else{
      $scope.contFiltroMedico = false;
    }
    // ALMACENES FARMACIA 
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { 
      $scope.listaAlmacenes = rpta.datos;
      $scope.fBusqueda.almacen = $scope.listaAlmacenes[0]; 
      $scope.getListaSubAlmacenes();
    }); 
    // SUBALMACENES FARMACIA 
    $scope.getListaSubAlmacenes = function () {
      almacenFarmServices.sListarSubAlmacenVentaPorIdAlmacenCbo($scope.fBusqueda.almacen.id).then(function (rpta) {
        if(rpta.flag == 1){
          $scope.listaSubAlmacenes = rpta.datos;
        }else{
          $scope.listaSubAlmacenes = [];
          $scope.listaSubAlmacenes.splice(0,0,{ id : '0', descripcion:rpta.message});
        }
        $scope.fBusqueda.subalmacen = $scope.listaSubAlmacenes[0];
        if($scope.selectedReport.id == "FAR-IVM" || $scope.selectedReport.id == "FAR-IMU"){
          $scope.gridOptions.data = [];
          if(rpta.flag == 1){
            $scope.getPaginationServerSide();
          }
        }
      });
    }
    $scope.getListaMedicos = function () { 
      if($scope.contFiltroMedico){
        if( $scope.fBusqueda.empresaespecialidad.id == 'ALL' ){
          $scope.listaMedicos = [ 
            { 'idmedico': 'ALL', 'medico': '--TODOS--' } 
          ]; 
          $scope.fBusqueda.medico = $scope.listaMedicos[0];
        }else{
          empleadoSaludServices.sListarMedicosDeEmpresaEspecialidad($scope.fBusqueda.empresaespecialidad).then(function (rpta) { 
            $scope.listaMedicos = rpta.datos;
            //$scope.listaMedicos.splice(0,0,{ idmedico : 'ALL', medico:'-- TODOS --'});
            if( !($scope.fSessionCI.key_group == 'key_salud') ){ 
              $scope.listaMedicos.splice(0,0,{ idmedico : 'ALL', medico:'TODOS'});
            }
            $scope.fBusqueda.medico = $scope.listaMedicos[0];
          });
        }
      } 
    }
    $scope.getListaMedicosSoloEsp = function () {
      if($scope.contFiltroMedico){
        if( $scope.fBusqueda.especialidad.id == 'ALL' ){
          $scope.listaMedicos = [ 
            { 'idmedico': 'ALL', 'medico': '--TODOS--' } 
          ]; 
          $scope.fBusqueda.medico = $scope.listaMedicos[0];
        }else{
          empleadoSaludServices.sListarMedicosDeEspecialidad($scope.fBusqueda.especialidad).then(function (rpta) { 
            $scope.listaMedicos = rpta.datos;
            //$scope.listaMedicos.splice(0,0,{ idmedico : 'ALL', medico:'-- TODOS --'});
            if( !($scope.fSessionCI.key_group == 'key_salud') ){ 
              $scope.listaMedicos.splice(0,0,{ idmedico : 'ALL', medico:'TODOS'});
            }
            $scope.fBusqueda.medico = $scope.listaMedicos[0];
          });
        }
      }
    }
    // $scope.getListaSoloMedicosAtencionAutoComplete = function (value) {
    //   var params = {
    //     search: value, 
    
    //     sensor: false
    //   }
    //   return empleadoSaludServices.sListarMedicosAtencionTodos(params).then(function(rpta) { 
    //     $scope.noResultsLE = false;
    //     if( rpta.flag === 0 ){
    //       $scope.noResultsLE = true;
    //     }
    //     return rpta.datos; 
    //   });
    // }
    // GRILLA DE MEDICAMENTOS PARA REPORTE DE INVENTARIO DE MEDICAMENTOS - CONTABILIDAD
      var paginationOptions = { 
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.ASC,
        sortName: null,
        search: null
      };
      $scope.gridOptions = {
        rowHeight: 25,
        paginationPageSizes: [10, 50, 100, 500, 1000, 5000],
        paginationPageSize: 10,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: false,
        enableFiltering: true,
        enableFullRowSelection: true,
        multiSelect: false,
        columnDefs: [
          { field: 'idmedicamento', name: 'idmedicamento', displayName: 'ID', width: 80 },
          { field: 'medicamento', name: 'medicamento', displayName: 'PRODUCTO',  sort: { direction: uiGridConstants.ASC} }
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
              'med.idmedicamento' : grid.columns[1].filters[0].term,
              'med.denominacion' : grid.columns[2].filters[0].term,
            }
            $scope.getPaginationServerSide();
          });
        }
      }
      paginationOptions.sortName = $scope.gridOptions.columnDefs[1].name;
      $scope.getPaginationServerSide = function() {
        $scope.datosGrid = {
          paginate : paginationOptions,
          datos: $scope.fBusqueda,
        };
        medicamentoAlmacenServices.sListarMedicamentoSubAlmacenVenta($scope.datosGrid).then(function (rpta) {
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
        });
        $scope.mySelectionGrid = [];
      };
    // BOTON PROCESAR
    $scope.btnConsultarReporte = function () { 
      var strControllerJS = 'CentralReportes';
      var strControllerPHP = 'CentralReportesMPDF'; 
      switch ( $scope.selectedReport.id ) { 
        // ADMINISTRACION
          case 'VT-RC':
            $scope.fBusqueda.titulo = 'RESUMEN DE CAJAS';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_resumen_cajas'; 
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'VT-RCTD':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_tipo_documento_caja'; 
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'VT-DC':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja_excel';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'VT-DCF': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja_fechas_excel';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'VT-DCP':
            $scope.fBusqueda.titulo = 'DETALLADO DE CAJAS POR PRODUCTO';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            } 
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_producto_caja',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'VT-CIE': // CUADRO DE INGRESOS POR ESPECIALIDADES
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+strController+'/report_ingresos_por_especialidad';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'AM-FAM':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_ficha_atencion',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'AM-LA':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            if( $scope.fBusqueda.salida == 'excel' ){ 
              arrParams.url = angular.patchURLCI+'CentralReportes/report_listado_atenciones'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else{
              alert('Reporte aun no implementado para PDF'); 
              return false; 
            }
            
            break;
          case 'AM-PEPM':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = { 
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_produccion_medicos',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'AM-CPM':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }; 
            if( $scope.fBusqueda.salida == 'pdf' ){ 
              arrParams.url = angular.patchURLCI+'CentralReportes/report_consolidado_medico'; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_consolidado_medico_excel';
            }
            
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'AM-CPE':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = { 
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+strController+'/report_consolidado_especialidad';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+strController+'/report_consolidado_especialidad_excel';
            }
            //arrParams.url = angular.patchURLCI+strController+'/report_consolidado_especialidad',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'AM-LT':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            // estos es importante para que resetear la salida a pdf
            $scope.fBusqueda.salida = 'pdf';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }; 
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_liquidacion_terceros', 
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
        // GERENCIA COMERCIAL Y MARKETING
          case 'CE-CNC': // CLIENTES NUEVOS Y CONTINUADORES 
            $scope.fBusqueda.titulo = $scope.selectedReport.name; 
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var titulo = '';
            if( $scope.fBusqueda.unidadNegocio.id == 'far' ){
              alert('Aún no implementado para Farmacia.'); return false;
            }
            if( $scope.fBusqueda.pacienteNC.id == 'none' ){
              pinesNotifications.notify({ title: 'Advertencia', text: 'No se han llenado todos los campos.', type: 'warning', delay: 3000 });
              return;
            }
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_cliente_nuevo_continuador'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){
              if($scope.fBusqueda.idTipoRango == '1'){
                titulo = 'CLIENTES NUEVOS Y CONTINUADORES ('+$scope.fBusqueda.anio+')';
              }else{
                if($scope.fBusqueda.pacienteNC.id == 'PN' )
                  titulo = 'CLIENTES NUEVOS (' + $scope.fBusqueda.anioDesde + ' - ' + $scope.fBusqueda.anioHasta + ')';
                else if($scope.fBusqueda.pacienteNC.id == 'PC' )
                  titulo = 'CLIENTES CONTINUADORES (' + $scope.fBusqueda.anioDesde + ' - ' + $scope.fBusqueda.anioHasta + ')';
              }
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: titulo,
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'CANTIDAD'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: ''
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_cliente_nuevo_continuador'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break;
          case 'CE-CDU': // CONCENTRACIÓN DE USO 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            if( $scope.fBusqueda.unidadNegocio.id == 'far' ){
              alert('Aún no implementado para Farmacia.'); return false;
            }
            if( !($scope.fBusqueda.anioDesde) || !($scope.fBusqueda.anioHasta) ){
              pinesNotifications.notify({ title: 'Advertencia', text: 'No se han llenado todos los campos.', type: 'warning', delay: 3000 });
              return;
            }
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_concentracion_de_uso'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){ 
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'CONCENTRACIÓN DE USO',
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'INDICADOR'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: ''
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_concentracion_de_uso'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break;
          case 'CE-REV': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_venta_mes_anio'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){ 
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'ESTADISTICA DE VENTAS ANUALES',
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'Monto en S/.'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: 'S/.'
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              };
              // if( $scope.fBusqueda.sedeempresa == 9 ){
              //   alert('Reporte Gráfico aun no implementado.'); return false; 
              // }
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_venta_mes_anio'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break; 
          case 'CE-REPR': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            if( $scope.fBusqueda.unidadNegocio.id == 'far' ){
              alert('Aún no implementado para Farmacia.'); return false;
            }
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_prestacion_mes_anio'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){ 
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'ESTADISTICA DE PRESTACIONES ANUALES',
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'Cantidad'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: ''
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_prestacion_mes_anio'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break; 
          case 'CE-RETP': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            if( $scope.fBusqueda.unidadNegocio.id == 'far' ){
              alert('Aún no implementado para Farmacia.'); return false;
            }
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_ticket_promedio_mes_anio'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){ 
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'ESTADISTICA DE TICKET PROMEDIO',
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'Promedio'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: ''
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_ticket_promedio_mes_anio'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break;
          case 'CE-REES': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_especialidad_mes_anio'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){ 
              var strText = 'PRESTACIONES';
              var strValuePrefix = '';
              var strTextY = 'Cantidad';
              if( $scope.fBusqueda.ventaPrestacion == 'v' ){
                strText = 'VENTAS';
                strValuePrefix = 'S/. ';
                strTextY = 'Monto en S/.';
              }
              if( $scope.fBusqueda.ventaPrestacion == 'tp' ){
                strText = 'TICKET PROMEDIO';
                var strTextY = 'Promedio';
              }
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'ESTADISTICA DE '+strText+' POR ESPECIALIDAD - '+$scope.fBusqueda.especialidad.descripcion,
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: strTextY
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: strValuePrefix
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_especialidad_mes_anio'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break; 
          case 'CE-REEDS': // ESPECIALIDAD MENSUAL DETALLADO
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tiposalida == 'pdf' ){
              // estos es importante para que resetear la salida a pdf
              $scope.fBusqueda.salida = 'pdf';
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_especialidad_detallado_mes_anio'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tiposalida == 'grafico' ){ 
              // console.log($scope.fBusqueda.tiposalida);
              var structureGraphic = { 
                chart: {
                  type: 'column'
                },
                title: {
                  text: 'PRODUCTOS VENDIDOS POR ESPECIALIDAD: '+$scope.fBusqueda.especialidad.descripcion,
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {
                  crosshair: true
                },
                yAxis: {
                  title: {
                      text: 'Monto en S/.'
                  },
                  min: 0
                },
                tooltip: { 
                  headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                  pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                      '<td style="padding:0"><b>S./ {point.y:.2f} </b></td></tr>',
                  footerFormat: '</table>',
                  shared: true,
                  useHTML: true
                },
                plotOptions: {
                  column: {
                      pointPadding: 0.2,
                      borderWidth: 0
                  }
                }
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_estadistico_especialidad_detallado_mes_anio'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }else{
              alert('Reporte en Excel aún no implementado.'); return false; 
            }
            break; 
          case 'CE-REVD': // VENTAS DIARIO
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            if( $scope.fBusqueda.tipoCuadro === 'grafico' ){ 
              alert('Gráfico aún no implementado.'); return false;
            }
            // estos es importante para que resetear la salida a pdf
            $scope.fBusqueda.salida = 'pdf';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            arrParams.url = angular.patchURLCI+strController+'/report_estadistico_venta_dia_mes', 
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-RVD': // VENTAS & ATENCIONES DETALLADO
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            if( $scope.fBusqueda.salida == 'pdf' ){
              alert('Reporte en Pdf aún no implementado.'); return false; 
            }
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            //var strController = arrParams.metodo == 'js' ; 
            arrParams.url = angular.patchURLCI+'CentralReportes/report_detalle_por_producto_fechas_marketing_excel', 
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-CPE': // CONSOLIDADO DE PRODUCTOS POR ESPECIALIDAD
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+strController+'/report_consolidado_ventas_especialidad';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+strController+'/report_consolidado_ventas_especialidad_excel';
            }
            //arrParams.url = angular.patchURLCI+strController+'/report_consolidado_especialidad',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-IOM': // INDICADORES DE ÓRDENES POR MÉDICO
            $scope.fBusqueda.titulo = $scope.selectedReport.name; 
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            // estos es importante para que resetear la salida a pdf
            $scope.fBusqueda.salida = 'pdf';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_indicadores_ordenes_medico';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              console.log('en proceso');
            }
            //arrParams.url = angular.patchURLCI+strController+'/report_consolidado_especialidad',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-RSOL': // REPORTE DE SOLICITUDES 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            
            // console.log($scope.fBusqueda.especialidad);
            // if( $scope.fBusqueda.especialidad.id == 'ALL' ){
            //   alert('Debe ingresar una especialidad. Este reporte no acepta la opción TODOS'); return false; 
            // }
            $scope.fBusqueda.listaMedicos = $scope.listaMedicos;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_solicitudes_medico';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_solicitudes_medico_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-RSME': // REPORTE DE SOLICITUDES x MEDICO Y x ESPECIALIDADx
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            
            // console.log($scope.fBusqueda.especialidad);
            // if( $scope.fBusqueda.especialidad.id == 'ALL' ){
            //   alert('Debe ingresar una especialidad. Este reporte no acepta la opción TODOS'); return false; 
            // }
            $scope.fBusqueda.listaMedicos = $scope.listaMedicos;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_solicitudes_medico_especialidad';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_solicitudes_medico_especialidad_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-RSEX': // REPORTE DE SOLICITUDES - PACIENTE EXTERNO 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_solicitudes_paciente_externo';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            //arrParams.url = angular.patchURLCI+strController+'/report_consolidado_especialidad',
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'CE-PRPT': //PORCENTAJE DE RESPUESTAS POR PREGUNTA Y TABLET         
            if($scope.fBusqueda.desdeEncuesta == undefined || $scope.fBusqueda.hastaEncuesta == undefined){            
              alert('Ingrese una fecha válida'); return false;
            }
            $scope.fBusqueda.fDesdeEncuesta = moment($scope.fBusqueda.desdeEncuesta).format('DD-MM-YYYY');
            $scope.fBusqueda.fHastaEncuesta = moment($scope.fBusqueda.hastaEncuesta).format('DD-MM-YYYY');
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;          
            if( $scope.fBusqueda.tiposalida == 'grafico' ){ 
              var structureGraphic = {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'PORCENTAJE DE RESPUESTAS POR PREGUNTA Y TABLET'
                },
                subtitle: {
                  text: 'GRAFICO SOBRE LA CALIDAD DE ATENCIÓN'
                },
                tooltip: {
                    pointFormat: '<b>{series.name}: {point.percentage:.1f}% </b><br> Cant. de Personas {point.y} de {series.total} </b> '
                },
                credits: {
                  text:'Hospital Villa Salud'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true
                        },
                        showInLegend: true
                    }
                }

              }
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              };
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_porcentaje_encuesta'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            } else if( $scope.fBusqueda.tiposalida == 'pdf' ){
              alert('Reporte en PDF aun no implementado.'); return false;
            } else if( $scope.fBusqueda.tiposalida == 'excel' ){
              alert('Reporte en EXCEL aun no implementado.'); return false;
            }
            break;
          case 'CE-ERT': //EVOLUCIÓN DE LAS RESPUESTAS EN EL TIEMPO
            if($scope.fBusqueda.desdeEncuesta == undefined || $scope.fBusqueda.hastaEncuesta == undefined){            
                alert('Ingrese una fecha válida'); return false;
            }
            $scope.fBusqueda.fDesdeEncuesta = moment($scope.fBusqueda.desdeEncuesta).format('DD-MM-YYYY');
            $scope.fBusqueda.fHastaEncuesta = moment($scope.fBusqueda.hastaEncuesta).format('DD-MM-YYYY');
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;          
            if( $scope.fBusqueda.tiposalida == 'grafico' ){ 
              var structureGraphic = {
                chart: {
                    type: 'line'
                },
                title: {
                  text: 'EVOLUCION DE LAS RESPUESTAS EN EL TIEMPO',
                  x: -20 //center
                },
                subtitle: {
                  text: 'EVOLUCION DE LAS RESPUESTAS EN EL TIEMPO',
                  x: -20
                },
                xAxis: {
                  categories: []
                },
                yAxis: {
                  title: {
                      text: 'Cantidad'
                  },
                  plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                  }]
                },
                  tooltip: {
                    valueSuffix: ' atenciones '
                },
                credits: {
                  text:'Hospital Villa Salud'
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                }
              }
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              };
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_evolucion_encuesta'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            } else if( $scope.fBusqueda.tiposalida == 'pdf' ){
              alert('Reporte en PDF aun no implementado.'); return false;
            } else if( $scope.fBusqueda.tiposalida == 'excel' ){
              alert('Reporte en EXCEL aun no implementado.'); return false;
            }
            break; 
          case 'MK-LCA': // LISTADO DE CAMPAÑAS
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.salida = 'excel';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            var strController = strControllerPHP; 
            //console.log('$scope.fBusqueda',$scope.fBusqueda);
            arrParams.url = angular.patchURLCI+'CentralReportes/report_listado_campanias';           
            
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'MK-RPE': // REPORTE DE PACIENTES POR ESPECIALIDAD
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            
            // console.log($scope.fBusqueda.especialidad);
            if( $scope.fBusqueda.empresaAdmin.id == '0' ){
              alert('Debe seleccionar una empresa. Este reporte no acepta la opción --Todas--'); return false; 
            }
            if( $scope.fBusqueda.especialidad.id == 'ALL' ){
              alert('Debe seleccionar una especialidad. Este reporte no acepta la opción TODOS'); return false; 
            }
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_pacientes_especialidad';
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_pacientes_especialidad_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
        // ASISTENCIA
          case 'AS-APE': 
            cronJobServices.sMarcarAsistenciaHuellero(); // se implementa actualizacion de marcacion cada vez q se abra el reporte
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_asistencia_por_empleado'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_asistencia_por_empleado_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'AS-REMP':
            cronJobServices.sMarcarAsistenciaHuellero(); // se implementa actualizacion de marcacion cada vez q se abra el reporte
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_ranking_empleado_asistencia/puntual'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'AS-REMT':
            cronJobServices.sMarcarAsistenciaHuellero(); // se implementa actualizacion de marcacion cada vez q se abra el reporte
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_ranking_empleado_asistencia/tardanza'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'AS-REMF':
            cronJobServices.sMarcarAsistenciaHuellero(); // se implementa actualizacion de marcacion cada vez q se abra el reporte
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_ranking_empleado_faltas'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'AS-REMHE':
            cronJobServices.sMarcarAsistenciaHuellero(); // se implementa actualizacion de marcacion cada vez q se abra el reporte
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_ranking_empleado_horas_extra'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
        // RR.HH 
          case 'RH-CDD':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_empleados_por_distrito'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'RH-CPO':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_empleados_por_profesion'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'RH-MER':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_medicos_por_especialidad_rne'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_medicos_por_especialidad_rne_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;

            // $scope.fBusqueda.titulo = $scope.selectedReport.name;
            // $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            // if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
            //   var arrParams = {
            //     titulo: $scope.fBusqueda.titulo,
            //     datos: $scope.fBusqueda,
            //     metodo: 'php'
            //   } 
            //   arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_medicos_por_especialidad_rne'; 
            //   ModalReporteFactory.getPopupReporte(arrParams); 
            // }
            // break
          case 'RH-CEP':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_empleados_por_empresa_tercero'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'RH-CTC':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_empleados_por_tipo_contrato'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'RH-AEHT':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_empleados_por_rango_edad'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'RH-LEMP': // REPORTE GENERAL DE EMPLEADOS 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              };
              arrParams.datos.salida = 'excel';
              arrParams.url = angular.patchURLCI+'CentralReportes/report_empleados_lista_general'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
          case 'RH-RVC': // REPORTE DE VENCIMIENTO DE CONTRATOS
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_empleado_contrato_vence_mes'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_empleado_contrato_vence_mes_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
           case 'RH-PLAN': // REPORTE DE PLANILLAS
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              };
              arrParams.datos.salida = 'excel';
              arrParams.url = angular.patchURLCI+'CentralReportes/report_planillas'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break
        // FARMACIA
          case 'FAR_VT-RC': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_resumen_ventas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              //arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_farm_detalle_por_venta_caja_fechas_excel';
              alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR_VT-DC':
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              }
              if( $scope.fBusqueda.salida == 'pdf' ){
                arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_detalle_por_venta_caja_farmacia';
              }else if( $scope.fBusqueda.salida == 'excel' ){
                // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja_excel';
                arrParams.url = null;
              }
               
              ModalReporteFactory.getPopupReporte(arrParams); 
            }
            break;
          case 'FAR_VT-DCF': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_farm_detalle_por_venta_caja_fechas_excel';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR_VT-MED': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicos_en_venta_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicos_en_venta_fechas_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR_MED-M': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicos_medicamento_detalle_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){ 
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicos_medicamento_detalle_fechas_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR_VT-CV': // VENTAS DE MEDICAMENTOS POR CONDICION DE VENTA
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if($scope.fBusqueda.condicionVentaSeleccionadas.length == 0 ){
              alert('Debe seleccionar al menos una condición de venta');
              return false;
            }
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,  
              datos: $scope.fBusqueda,
              metodo: 'php'
            }; 
            if( $scope.fBusqueda.salida == 'pdf' ){ 
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_ventas_medicamentos_por_condicion_venta'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){ 
              // arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicamentos_de_recetas_retenidas_excel';
              alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-SCV': // STOCK POR CONDICION DE VENTA
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if($scope.fBusqueda.condicionVentaSeleccionadas.length == 0 ){
              alert('Debe seleccionar al menos una condición de venta');
              return false;
            }
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,  
              datos: $scope.fBusqueda,
              metodo: 'php'
            }; 
            if( $scope.fBusqueda.salida == 'pdf' ){ 
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_stock_medicamentos_por_condicion_venta'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){ 
              // arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/';
              alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-MMV': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.ordenamiento = 'DESC';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicamentos_vendidos_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicamentos_vendidos_fechas_excel';
              //alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-MNV': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.ordenamiento = 'ASC';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicamentos_vendidos_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicamentos_vendidos_fechas_excel';
              //alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-MMC': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.ordenamiento = 'DESC';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicamentos_comprados_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicamentos_comprados_fechas_excel';
              //alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-LMV': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.ordenamiento = 'DESC';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_laboratorios_vendidos_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_laboratorios_vendidos_fechas_excel';
              //alert('Reporte en EXCEL aun no implementado.'); return false;
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-CPF': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.ordenamiento = 'DESC';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_compras_proveedor_fechas'; 
              //alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_compras_proveedor_fechas_excel';
              //alert('Reporte en EXCEL aun no implementado.'); return false;
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-REVD': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            
            arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_estadistico_venta_farmacia_dia_mes', 
            ModalReporteFactory.getPopupReporte(arrParams); 
            break;
          case 'FAR-SMPA': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_stock_monetizado'; 
              // alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_stock_monetizado_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-TARIF': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_tarifario_farmacia'; 
              // alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_tarifario_farmacia_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-VM': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              var arrParams = {
                titulo: $scope.fBusqueda.titulo,
                datos: $scope.fBusqueda,
                metodo: 'php'
              } 
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_estadistico_venta_mes_anio'; 
              ModalReporteFactory.getPopupReporte(arrParams); 
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){ 
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'ESTADISTICA DE VENTAS ANUALES',
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'Monto en S/.'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: 'S/.'
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              };
              if( $scope.fBusqueda.sedeempresa == 9 ){
                alert('Reporte Gráfico aun no implementado.'); return false; 
              }
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_estadistico_venta_mes_anio'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            break; 
          case 'FAR_FORM': // FORMULAS PAGADAS Y PENDIENTES DE PAGO
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            // var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'excel' ){
              // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja'; 
              // arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_preparados_pagados_excel'; 
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_pedido_formulas_jj_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_preparados_pagados';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR_RVF': // LISTADO DE VENTA DE FORMULAS
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            // var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_resumen_venta_formulas'; 
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_preparados_pagados';
              alert('Reporte en PDF aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-IVM': // INVENTARIO VALORIZADO DE MEDICAMENTOS
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            if( $scope.mySelectionGrid.length != 1 ){
              alert('Seleccione un medicamento para procesar el reporte.'); return false;
            }else{
              $scope.fBusqueda.medicamento = $scope.mySelectionGrid[0];
              $scope.fBusqueda.salida = 'excel';
            }
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_inventario_valorizado_medicamento_excel';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-IMU': // INVENTARIO DE MEDICAMENTOS EN UNIDADES FISICAS
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            if( $scope.mySelectionGrid.length != 1 ){
              alert('Seleccione un medicamento para procesar el reporte.'); return false;
            }else{
              $scope.fBusqueda.medicamento = $scope.mySelectionGrid[0];
              $scope.fBusqueda.salida = 'excel';
            }
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'js'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+strController+'/report_detalle_por_venta_caja'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_inventario_medicamento_unidades_excel';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'FAR-FVC': // FORMULAS VENDIDAS - COSTO
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            // var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_formulas_vendidas_costo_excel'; 
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_formulas_vendidas_costo';
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
        // LOGISTICA
          case 'LOG-OC': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_ordenes_compra'; 
              // alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_ordenes_compra_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'LOG-IAF': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_ingresos_almacen'; 
              // alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_ingresos_almacen_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
          case 'LOG-TRAS': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id; 
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_traslados'; 
              // alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_traslados_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
        // HOSPITAL

          
          case 'HOS-TARIF': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            } 
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
            if( $scope.fBusqueda.salida == 'pdf' ){
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_tarifario_sede'; 
              // alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_tarifario_sede_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
            break;
        
          // PROGRAMACION ASISTENCIAL - DIRECCION MEDICA
          case 'PA-EMAS': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.salida = 'excel';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            /*
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_ingresos_almacen'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_ingresos_almacen_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            */
            arrParams.url = angular.patchURLCI+'CentralReportes/report_especialidades_medicos_por_emas_excel';

            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'PA-RP': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+'CentralReportes/report_ingresos_almacen'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_programaciones_excel';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'PA-RCP': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            console.log($scope.fBusqueda.tiposalida);
            $scope.fBusqueda.salida = $scope.fBusqueda.tiposalida;
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.tiposalida == 'pdf' ){
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.tiposalida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_cumplimiento_programaciones_excel';
              ModalReporteFactory.getPopupReporte(arrParams);            
            }else if( $scope.fBusqueda.tiposalida == 'grafico' ){
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: 'CUMPLIMIENTO PROGRAMACION CITAS - SEDE: '+ $scope.fBusqueda.sede.descripcion +' ESPECIALIDAD: ' + $scope.fBusqueda.especialidad.descripcion + ' MÉDICO: ' + $scope.fBusqueda.medico.medico,
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'CANTIDAD'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: ''
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportes/report_cumplimiento_programaciones_grafico'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
            
          break;
          case 'W-VW': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;            
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'pdf' ){
              // arrParams.url = angular.patchURLCI+'CentralReportes/report_ingresos_almacen'; 
              alert('Reporte en PDF aun no implementado.'); return false; 
            }else if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/report_detalle_ventas_web';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'W-RU': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.salida = 'excel';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportes/reporte_registro_usuarios_web';
              // alert('Reporte en EXCEL aun no implementado.'); return false; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'CE-PCE': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            var tipo = '';
            if($scope.fBusqueda.idTipoAtencion == 'CM'){
              tipo = 'PRODUCCION DE CONSULTAS EXTERNA';
            }else{
              tipo = 'PRODUCCION DE PROCEDIMIENTOS';
            }

            var arrParams = {
              titulo: tipo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.tipoCuadro == 'reporte' ){
              
              if($scope.fBusqueda.idTipoRango == 2 && ($scope.fBusqueda.anioHasta - $scope.fBusqueda.anioDesde) > 3 ){
                pinesNotifications.notify({ title: 'Advertencia.', text: 'Ingrese como máximo un rango de 4 años', type: 'warning', delay: 3000 }); 
              }else{
                arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_produccion_consulta_externa';
                ModalReporteFactory.getPopupReporte(arrParams); 
              }
               
            }else if( $scope.fBusqueda.tipoCuadro == 'grafico' ){
              
              var structureGraphic = { 
                chart: {
                  type: 'line'
                },
                title: {
                  text: tipo +' - SEDE: '+ $scope.fBusqueda.sede.descripcion +' - ESPECIALIDAD: ' + $scope.fBusqueda.especialidad.descripcion,
                  x: -20 //center
                },
                subtitle: {
                  text: 'Fuente: Villa Salud',
                  x: -20
                },
                xAxis: {},
                yAxis: {
                  title: {
                      text: 'CANTIDAD'
                  },
                  plotLines: [{
                      value: 0,
                      width: 1,
                      color: '#808080'
                  }]
                },
                tooltip: {
                  valuePrefix: ''
                },
                legend: {
                  layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                },
              }; 
              var arrParams = { 
                datos: $scope.fBusqueda,
                structureGraphic: structureGraphic
              }; 
              arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_produccion_consulta_externa_GRAPH'; 
              ModalReporteFactory.getPopupGraph(arrParams); 
            }
             
          break;
          case 'FAR-CMMA': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.salida = 'excel';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_consumo_medicamentos_mes_anio'; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'FAR-VUC': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.salida = 'excel';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_ventas_usuario_caja'; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          case 'FAR-VUCD': 
            $scope.fBusqueda.titulo = $scope.selectedReport.name;
            $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
            $scope.fBusqueda.salida = 'excel';
            var arrParams = {
              titulo: $scope.fBusqueda.titulo,
              datos: $scope.fBusqueda,
              metodo: 'php'
            }
            var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP;
            if( $scope.fBusqueda.salida == 'excel' ){
              arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_ventas_usuario_caja_detalle'; 
            }
            ModalReporteFactory.getPopupReporte(arrParams);
          break;
          // NINGUN REPORTE SELECCIONADO
          default: 
            pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione un reporte', type: 'warning', delay: 2000 }); 


      }
    }
  }]) 