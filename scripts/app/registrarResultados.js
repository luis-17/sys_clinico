angular.module('theme.registrarResultados', ['theme.core.services'])
  .controller('registrarResultadosController', ['$scope', '$route','$filter', '$sce', '$interval','$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys','registrarResultadosServices', 'atencionExamenAuxServices', 'reporteResultadosServices','ModalReporteFactory','blockUI',
    function($scope, $route, $filter, $sce, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , hotkeys, registrarResultadosServices, atencionExamenAuxServices, reporteResultadosServices, ModalReporteFactory, blockUI
      ){
    'use strict';
    $scope.fBusqueda = {};
    //$scope.fBusquedaPAC = {};
    $scope.fData = {};
    $scope.boolGenerar = false;
    $scope.fDataArrPrincipal = {};
    $scope.sumPorcentajes = 0;
    $scope.mySelectionClienteGrid = [];
    $scope.listaFiltroBusqueda = [
      { id:'PNO', descripcion:'POR N° DE ORDEN DE LABORATORIO' },
      { id:'OTRO', descripcion:'POR OTROS DATOS...' }
      // { id:'PH', descripcion:'POR N° DE HISTORIA' },
      // { id:'PP', descripcion:'POR PACIENTE' }
      
    ]; 
    $scope.fBusqueda.tipoBusqueda = $scope.listaFiltroBusqueda[0].id;
    $scope.fBusqueda.fechaexamen = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.showOrden = true;
    $scope.showHistoria = false;
    $scope.showPaciente = false;
    $scope.registroFormularioAMA = false;
    $scope.registroFormularioAP = false;
    $scope.pacEncontrado = false;
    $scope.onChangeFiltroBusqueda = function () { 
      if( $scope.fBusqueda.tipoBusqueda === 'PNO' ){ // N° ORDEN LABORATORIO
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
      if( $scope.fBusqueda.tipoBusqueda === 'OTRO' ){
        $scope.btnBuscarPaciente();
        $scope.fBusqueda.tipoBusqueda = 'PNO';
      }
    }
    // $scope.VerificarFiltro = function(){
    //   if( $scope.fBusqueda.tipoBusqueda === 'OTRO' ){
    //     $scope.btnBuscarPaciente();
    //   }
    // }
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 50,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    //$scope.isRegisterSuccess = false;
    $scope.mySelectionGrid = [];
    /* GRILLA DE ANALISIS  */ 
    $scope.fData = {};
    $scope.sel = false;
    $scope.fData.paciente = {};
    //$scope.fData.idmuestrapaciente = '-'
    $scope.gridOptions = {
      paginationPageSizes: [50, 100, 500, 1000],
      paginationPageSize: 50,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      data: [],
      columnDefs: [
        { field: 'producto', name: 'pm.descripcion', displayName: 'Producto',  sort: { direction: uiGridConstants.ASC}},
        { field: 'seccion', name: 'seccion', displayName: 'Sección' },
       
        { field: 'descripcion_anal', name: 'descripcion_anal', displayName: 'Analisis' },
        { field: 'numero_impresiones', name: 'numero_impresiones', displayName: 'Impresiones', maxWidth: 100 },
        { field: 'estado', type: 'object', name: 'estado_ap', displayName: 'Estado', maxWidth: 250, enableFiltering: false,
           cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
        //{ field: 'fecha_resultado', name: 'fecha_resultado', displayName: 'Fecha' },
       
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.reloadGrid = function () {
      $interval( function() { 
          //$scope.gridApiPAD.core.handleWindowResize();
          $scope.gridApi.core.handleWindowResize();
      }, 50, 5);
    }
    $scope.getPacienteAutocomplete = function (value) { 
      var params = { 
        searchText: value,
        searchColumn: "UPPER(CONCAT(cl.nombres,' ',cl.apellido_paterno,' ',cl.apellido_materno))",
        sensor: false,
        arrTipoProductos: [15]
      };
      return registrarResultadosServices.sListarPacientesAutoComplete(params).then(function(rpta) { 
        //console.log(rpta.datos);
        $scope.noResultsPACI = false; 
        if( rpta.flag === 0 ){ 
          $scope.noResultsPACI = true; 
        } 
        return rpta.datos; 
      });
    }
    $scope.btnBuscarPaciente = function(){
      $modal.open({
        templateUrl: angular.patchURLCI+'cliente/ver_popup_busqueda_cliente',
        size: 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance, mySelectionClienteGrid) {
          $scope.mySelectionClienteGrid = mySelectionClienteGrid;
          $scope.titleForm = 'Búsqueda de Pacientes';
          var paginationOptionsPacientes = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionClienteGrid = [];
          
          $scope.gridOptionsClienteBusqueda = {
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
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'orden_lab', name: 'orden_lab', displayName: 'Orden Lab', maxWidth: 100,  sort: { direction: uiGridConstants.ASC} },
              { field: 'idhistoria', name: 'idhistoria', displayName: 'Historia', maxWidth: 100 },
              { field: 'num_documento', name: 'num_documento', displayName: 'N° Doc.', maxWidth: 120 },
              { field: 'nombres', name: 'nombres', displayName: 'Nombres', maxWidth: 200 },
              { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'Apellido Paterno', maxWidth: 200 },
              { field: 'apellido_materno', name: 'apellido_materno', displayName: 'Apellido Materno', maxWidth: 200 }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionClienteGrid = gridApi.selection.getSelectedRows();
                //$scope.fBusquedaPAC = $scope.mySelectionClienteGrid[0]; 
                $scope.fBusqueda.orden_lab = $scope.mySelectionClienteGrid[0].orden_lab;
                
                
                $modalInstance.dismiss('cancel');
                setTimeout(function() {
                  // $('#idhistoria').focus();
                  
                }, 1000);
                // SE RECONSTRUYE LA FECHA Y EL NUMERO DE ORDEN
                var fecha = null;
                if( $scope.fSessionCI.id_empresa_admin == 38 ){ // medicina integral
                  $scope.fBusqueda.orden_lab = $scope.fBusqueda.orden_lab.slice(3);
                }
                fecha = $scope.fBusqueda.orden_lab.slice(0,2) + '-' + $scope.fBusqueda.orden_lab.slice(2,4) + '-' + '20' + $scope.fBusqueda.orden_lab.slice(4,6);
                $scope.fBusqueda.numeroOrden = $scope.fBusqueda.orden_lab.slice(7);
                $scope.fBusqueda.fechaexamen = fecha;
               
                $scope.btnConsultarPacientesAtencion();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsPacientes.sort = null;
                  paginationOptionsPacientes.sortName = null;
                } else {
                  paginationOptionsPacientes.sort = sortColumns[0].sort.direction;
                  paginationOptionsPacientes.sortName = sortColumns[0].name;
                }
                $scope.getPaginationPacienteServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsPacientes.pageNumber = newPage;
                paginationOptionsPacientes.pageSize = pageSize;
                paginationOptionsPacientes.firstRow = (paginationOptionsPacientes.pageNumber - 1) * paginationOptionsPacientes.pageSize;
                $scope.getPaginationPacienteServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationOptionsPacientes.search = true;
                // console.log(grid.columns);
                // console.log(grid.columns[1].filters[0].term);
                paginationOptionsPacientes.searchColumn = {
                  'orden_lab' : grid.columns[1].filters[0].term,
                  'h.idhistoria' : grid.columns[2].filters[0].term,
                  'num_documento' : grid.columns[3].filters[0].term,
                  'cl.nombres' : grid.columns[4].filters[0].term,
                  'apellido_paterno' : grid.columns[5].filters[0].term,
                  'apellido_materno' : grid.columns[6].filters[0].term
                }
                $scope.getPaginationPacienteServerSide();
              });
            }
          };
          $scope.navegateToCellListaBusquedaCliente = function( rowIndex, colIndex ) { 
            console.log(rowIndex, colIndex);
            $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsClienteBusqueda.data[rowIndex], $scope.gridOptionsClienteBusqueda.columnDefs[colIndex]); 
            
          };
          paginationOptionsPacientes.sortName = $scope.gridOptionsClienteBusqueda.columnDefs[0].name;
          $scope.getPaginationPacienteServerSide = function() {
            //$scope.$parent.blockUI.start();
            $scope.datosGrid = {
              paginate : paginationOptionsPacientes
            };
            registrarResultadosServices.sListarPacientesLaboratorio($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsClienteBusqueda.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsClienteBusqueda.data = rpta.datos;
            });
            $scope.mySelectionClienteGrid = [];
          };
          $scope.getPaginationPacienteServerSide();

          shortcut.add("down",function() { 

            $scope.navegateToCellListaBusquedaCliente(0,0);
          });
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        },
        resolve: {
          mySelectionClienteGrid: function() {
            return $scope.mySelectionClienteGrid;
          }
        }
      });
    }
    $scope.btnBusq = function () {
      console.log($scope.fBusqueda.orden_lab);
      var fecha = null;
      fecha = $scope.fBusqueda.orden_lab.slice(0,2) + '-' + $scope.fBusqueda.orden_lab.slice(2,4) + '-' + '20' + $scope.fBusqueda.orden_lab.slice(4,6);
      $scope.fBusqueda.numeroOrden = $scope.fBusqueda.orden_lab.slice(7);
      $scope.fBusqueda.fechaexamen = fecha;
     
     $scope.btnConsultarPacientesAtencion();
    }
    $scope.btnConsultarPacientesAtencion = function () {
      $scope.sel = false;
      $scope.boolGenerar = false;
      $scope.mySelectionGrid = {};
      var validateButton = false;
      if( $scope.fBusqueda.tipoBusqueda === 'PNO'){ // N° ORDEN 
        if( $scope.fBusqueda.numeroOrden && $scope.fBusqueda.fechaexamen){ 
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
        blockUI.start();
        registrarResultadosServices.sListarPacienteParaResultados($scope.fBusqueda).then(function (rpta) {
          blockUI.stop();  
          if(rpta.flag == 1){ 
            var pTitle = 'OK!';
            var pType = 'success';
            var message = rpta.message;

            //$scope.fBusqueda.numeroOrden = null;
            //$scope.fBusqueda.numeroHistoria = null;
            //$scope.fBusqueda.paciente = null;
            $scope.pacEncontrado = true;
            $scope.fData = rpta.datos;
            $scope.fDataArrPrincipal = rpta.arrSecciones;
            $scope.fDataAnal = rpta.arrAnalisis;
            $scope.gridOptions.data = $scope.fDataAnal;
            $scope.reloadGrid();

            pinesNotifications.notify({ title: pTitle, text: message, type: pType, delay: 3500 });
            //$('#p00').focus();
          }else if(rpta.flag == 0){
            $scope.fBusqueda.numeroOrden = null;
            $scope.fBusqueda.numeroHistoria = null;
            $scope.fBusqueda.paciente = null;
            $scope.pacEncontrado = false;
            $scope.fData = {};
            var pTitle = 'AVISO!';
            var pType = 'warning';
            var message = rpta.message;
            pinesNotifications.notify({ title: pTitle, text: message, type: pType, delay: 4000 });
          }else{
            alert('Se ha producido un problema. Contacte con el Area de Sistemas');
          }
          
          //pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
        });
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'Digite un patrón de búsqueda. El campo está vacío.', type: 'warning', delay: 3500 });
      }
      
    }
    // ***********************************************************
    $scope.btnIngresarSel = function() {
      angular.forEach($scope.mySelectionGrid, function(value, key) {
        angular.forEach($scope.fDataArrPrincipal,function(valueAP, keyAP){ 
          angular.forEach(valueAP.analisis,function(valueAnal, keyAnal){
            if( valueAP.idseccion == value.idseccion && valueAnal.idanalisispaciente == value.idanalisispaciente ){
              $scope.fDataArrPrincipal[keyAP].analisis[keyAnal].seleccionado = true;
              $scope.fDataArrPrincipal[keyAP].seleccionado = true;
            }
          });
        });
      });
      
      // COMBO - SE AISLA - BACTERIA
      registrarResultadosServices.sListarBacteriasCbo().then(function (rpta) {
        $scope.listaBacteria = rpta.datos;
        $scope.listaBacteria.splice(0,0,{ id : '--Seleccione Opcion--', descripcion:'--Seleccione Opción--'});
        //$scope.fData.idBacteria = $scope.listaBacteria[0].id;
      });
      // COMBO - RESULTADO - PARASITOS
      // registrarResultadosServices.sListarParasitosCbo().then(function (rpta) {
      //   $scope.listaParasito = rpta.datos;
      //   $scope.listaParasito.splice(0,0,{ id : '--Seleccione Opcion--', descripcion:'--Seleccione Opción--'});
      //   //$scope.fData.idBacteria = $scope.listaBacteria[0].id;
      // });

      // COMBO - ANTIBIOGRAMA
      $scope.listaCaracteristica = [ 
        { id : 'SENSIBLE', descripcion: 'SENSIBLE' }, 
        { id : 'INTERMEDIO', descripcion: 'INTERMEDIO' },
        { id : 'RESISTENTE', descripcion: 'RESISTENTE' }
      ];
      $scope.listaCaracteristica.splice(0,0,{ id : '--Seleccione Opcion--', descripcion:'--Seleccione Opción--'});
      // $scope.fData.parametro[] = $scope.listaCaracteristica[0].id;



      $scope.sumPorcentajes = 0;
      $scope.sel = true;
      $scope.boolGenerar = true;
    }
    $scope.generarResultado = function (){
      console.log('orden ',$scope.fData.orden_lab);
      var arrParam = {
        arrPrincipal : $scope.fDataArrPrincipal,
        orden_lab : $scope.fData.orden_lab,
      }
      registrarResultadosServices.sGenerarResultados(arrParam).then(function (rpta){
        $scope.fDataArrPrincipal = rpta.datos;
        if(rpta.flag == 1){
          var pTitle = 'Ok!';
          var pType = 'success';
        }else{
          var pTitle = 'Aviso!';
          var pType = 'warning';
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
      });
    }
    $scope.calcularFormulaParametro = function (idparametro,s,a,p){
      // s: indice de la seccion
      // a: indice del analisis
      // p: indice del parametro

      var analisis = $scope.fDataArrPrincipal[s].analisis[a];
      switch(idparametro) {
          case '328': // HEMATOCRITO
            analisis.parametros[1].resultado = (parseFloat(analisis.parametros[0].resultado) * 0.33).toFixed(1);
            break;
          case '637': // VOLUMEN EN 24 HORAS
            // CALCULO DE VOLUMEN MINUTO ( VolMinuto =  Vol24h / 1400 )
            analisis.parametros[1].resultado = (parseFloat(analisis.parametros[0].resultado) / 1440).toFixed(2);
            break;
          case '197': // CREATININA EN ORINA
            // CALCULO DE LA DEPURACION NO CORREGIDA ( DepNoCorr = CreatOrina * VolMinuto * 1.73 / CreatSangre)
            analisis.parametros[7].resultado = (parseFloat((analisis.parametros[3].resultado) * (analisis.parametros[1].resultado) * 1.73 / (analisis.parametros[2].resultado))).toFixed(2);
            break;
          case '577': // TALLA
            //CALCULO DE LA SUPERFICIE CORPORAL
            analisis.parametros[6].resultado = (parseFloat((analisis.parametros[4].resultado)*(analisis.parametros[5].resultado) / 7200)).toFixed(2);
            //CALCULO DE LA DEPURACION CORREGIDA
            analisis.parametros[8].resultado = (parseFloat((analisis.parametros[7].resultado)/(analisis.parametros[6].resultado))).toFixed(2);
            break;
          case '94': // BILIRRUBINA DIRECTA
            //CALCULO DE LA BILIRRUBINA INDIRECTA
            analisis.parametros[2].resultado = (parseFloat((analisis.parametros[0].resultado) - (analisis.parametros[1].resultado)) ).toFixed(2);
            break;
          case '34': // ALBUMINA
            // CALCULO DE GLOBULINAS ( Gb = ProteinaTotal - Albumina )
            analisis.parametros[2].resultado = (parseFloat((analisis.parametros[0].resultado) - (analisis.parametros[1].resultado)) ).toFixed(1);
            break;
          // - PERFIL LIPIDICO
          case '620': // TRIGLICERIDOS (Tg)
            // CALCULO DE VLDL ( VLDL = Tg / 5 )
            analisis.parametros[4].resultado = (parseFloat((analisis.parametros[1].resultado) / 5) ).toFixed(2);
            break;
          case '172': // HDL 
            // CALCULO DE LDL ( LDL = CT - HDL - VLDL )
            analisis.parametros[3].resultado = (parseFloat((analisis.parametros[0].resultado) - (analisis.parametros[2].resultado) - (analisis.parametros[4].resultado)) ).toFixed(2);
            break;
      }
    }
    $scope.calcularFormulaSubparametro = function (idsubparametro,s,a,p){
      // s: indice de la seccion
      // a: indice del analisis
      // p: indice del parametro
     
      var parametro = $scope.fDataArrPrincipal[s].analisis[a].parametros[p];
      switch(idsubparametro) {
          case '328': // HEMATOCRITO
            // CALCULO DE LA HEMOGLOBINA ( Hb = Ht * 0.33 )
            parametro.subparametros[1].resultado = (parseFloat(parametro.subparametros[0].resultado) * 0.33).toFixed(1);
            break;
          // - DEPURACION DE CREATININA
          case '637': // VOLUMEN EN 24 HORAS 
            // CALCULO DE VOLUMEN MINUTO ( VolMinuto =  Vol24h / 1400 )
            parametro.subparametros[1].resultado = (parseFloat(parametro.subparametros[0].resultado) / 1440).toFixed(2);
            break;
          case '197': // CREATININA EN ORINA
            // CALCULO DE LA DEPURACION NO CORREGIDA ( DepNoCorr = CreatOrina * VolMinuto * 1.73 / CreatSangre)
            parametro.subparametros[7].resultado = (parseFloat(parametro.subparametros[3].resultado) * (parametro.subparametros[1].resultado) * 1.73 / (parametro.subparametros[2].resultado)).toFixed(2);
            break;
          case '577': // TALLA
            // CALCULO DE LA SUPERFICIE CORPORAL ( SupCorp = PESO * TALLA / 7200 )
            parametro.subparametros[6].resultado = (parseFloat(parametro.subparametros[4].resultado)*(parametro.subparametros[5].resultado) / 7200).toFixed(2);
            // CALCULO DE LA DEPURACION CORREGIDA ( DepNoCorr / SupCorp)
            parametro.subparametros[8].resultado = (parseFloat(parametro.subparametros[7].resultado)/(parametro.subparametros[6].resultado)).toFixed(2);
            break;
          case '94': // BILIRRUBINA DIRECTA
            // CALCULO DE LA BILIRRUBINA INDIRECTA ( BI = BT - BD )
            parametro.subparametros[2].resultado = (parseFloat((parametro.subparametros[0].resultado) - (parametro.subparametros[1].resultado)) ).toFixed(2);
            break;
          case '34': // ALBUMINA
            // CALCULO DE GLOBULINAS ( Gb = ProteinaTotal - Albumina )
            parametro.subparametros[2].resultado = (parseFloat((parametro.subparametros[0].resultado) - (parametro.subparametros[1].resultado)) ).toFixed(1);
            break;
          // - PERFIL LIPIDICO
          case '620': // TRIGLICERIDOS (Tg)
            // CALCULO DE VLDL ( VLDL = Tg / 5 )
            parametro.subparametros[4].resultado = (parseFloat((parametro.subparametros[1].resultado) / 5) ).toFixed(2);
            break;
          case '172': // HDL 
            // CALCULO DE LDL ( LDL = CT - HDL - VLDL )
            parametro.subparametros[3].resultado = (parseFloat((parametro.subparametros[0].resultado) - (parametro.subparametros[2].resultado) - (parametro.subparametros[4].resultado)) ).toFixed(2);
            break;
      }
    }
    $scope.calcularSumaPorcentajes = function(s,a,p){
      var parametro = $scope.fDataArrPrincipal[s].analisis[a].parametros[p];

      var subpar0 = parseFloat(parametro.subparametros[0].resultado);
      var subpar1 = parseFloat(parametro.subparametros[1].resultado);
      var subpar2 = parseFloat(parametro.subparametros[2].resultado);
      var subpar3 = parseFloat(parametro.subparametros[3].resultado);
      var subpar4 = parseFloat(parametro.subparametros[4].resultado);
      var subpar5 = parseFloat(parametro.subparametros[5].resultado);
      var subpar6 = parseFloat(parametro.subparametros[6].resultado);
      var subpar7 = parseFloat(parametro.subparametros[7].resultado);
      
      if(parametro.subparametros[0].resultado == null || parametro.subparametros[0].resultado == '') subpar0 = 0;
      if(parametro.subparametros[1].resultado == null || parametro.subparametros[1].resultado == '') subpar1 = 0;
      if(parametro.subparametros[2].resultado == null || parametro.subparametros[2].resultado == '') subpar2 = 0;
      if(parametro.subparametros[3].resultado == null || parametro.subparametros[3].resultado == '') subpar3 = 0;
      if(parametro.subparametros[4].resultado == null || parametro.subparametros[4].resultado == '') subpar4 = 0;
      if(parametro.subparametros[5].resultado == null || parametro.subparametros[5].resultado == '') subpar5 = 0;
      if(parametro.subparametros[6].resultado == null || parametro.subparametros[6].resultado == '') subpar6 = 0;
      if(parametro.subparametros[7].resultado == null || parametro.subparametros[7].resultado == '') subpar7 = 0;

      $scope.sumPorcentajes = subpar0 + subpar1 + subpar2 + subpar3 + subpar4 + subpar5 + subpar6 + subpar7;
      //console.log($scope.fDataArrPrincipal[s].analisis[a].parametros[p]);
      if($scope.sumPorcentajes > 100){
        alert('Ha pasado el 100%. Por favor corrija los datos ingresados');
      }
    }
    $scope.btnVolver = function() {
      $scope.btnConsultarPacientesAtencion();
      $scope.gridApi.selection.clearSelectedRows();
      angular.forEach($scope.mySelectionGrid, function(value, key) {
        angular.forEach($scope.fDataArrPrincipal,function(valueAP, keyAP){ 
          angular.forEach(valueAP.analisis,function(valueAnal, keyAnal){
              $scope.fDataArrPrincipal[keyAP].analisis[keyAnal].seleccionado = false;
              $scope.fDataArrPrincipal[keyAP].seleccionado = false;
          });
        });
      });
      $scope.mySelectionGrid = [];
      $scope.reloadGrid();
      $scope.sel = false;
      $scope.boolGenerar = false;
      $scope.sumPorcentajes = 0;
    }
    $scope.btnNuevo = function() {
      $route.reload(); 
    }
    $scope.btnGuardar = function(){
      //console.log($scope.fData);
      $scope.fDatos = {};
      if($scope.fSessionCI.key_group == 'key_lab'){
        registrarResultadosServices.sRegistrarResultadosPaciente($scope.fDataArrPrincipal).then(function (rpta) {
          if(rpta.flag == 1){
            // registrar atencion medica
            //console.log('Registrado ok');
            $scope.fDatos.id = $scope.fData.idventa; //id de la venta
            $scope.fDatos.idhistoria = $scope.fData.idhistoria;
            $scope.fDatos.id_area_hospitalaria = 1;
            $scope.fDatos.tipoResultado = 1;
            $scope.fDatos.ex_informe = 'Informe de Laboratorio';
            $scope.fDatos.personal = {};
            $scope.fDatos.personal.id = null;
            // $scope.fDatos.personal.id = 176; // ENRIQUE ROJAS ORDOÑEZ DNI: 20073447
            $scope.fDatos.orden = $scope.fData.orden_venta;
            $scope.fDatos.ticket = $scope.fData.ticket;
            $scope.fDatos.iddetalle = null; // esto sirve para que en el php podamos guardar el iddetalle que esta en la seccion analisis
            $scope.fDatos.arrSecciones = $scope.fDataArrPrincipal // de aqui solo queremos el iddetalle
            console.log($scope.fDatos);
            //if(rpta.reg == 1){ // si es un registro
              registrarResultadosServices.sRegistrarAtencionLaboratorio($scope.fDatos).then(function (rpta1) { 
                if(rpta1.flag == 1) { 
                  console.log('Registrado Atencion Laboratorio ok');
                }
              });
            //}
            
           
            
            pTitle = 'OK!';
            pType = 'success';
            $scope.sel = false;
            // $scope.fBusqueda.numeroOrden = null;
            // $scope.fBusqueda.numeroHistoria = null;
            // $scope.fBusqueda.paciente = null;
            // $scope.pacEncontrado = false;
            // $scope.fData = {};
            // $scope.fDataAnal = {};
             $scope.mySelectionGrid = {};
            //$scope.btnVolver();
            $scope.btnConsultarPacientesAtencion();
          }else if(rpta.flag == 0){
            var pTitle = 'AVISO!';
            var pType = 'danger';
          }else{
            alert('Error inesperado');
          }
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
        });
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'Debe ser Personal de Laboratorio para poder registrar los datos', type: 'danger', delay: 3000 });
      }
      
    }
    $scope.btnImprimirSel = function (){
      // console.log($scope.mySelectionGrid);

      angular.forEach($scope.fDataArrPrincipal,function(valueAP, keyAP){ 
        angular.forEach(valueAP.analisis,function(valueAnal, keyAnal){ 
          $scope.fDataArrPrincipal[keyAP].analisis[keyAnal].seleccionado = false;
          $scope.fDataArrPrincipal[keyAP].seleccionado = false;
        });
      });
      console.log('Impresiones');
      console.log($scope.mySelectionGrid);
      console.log('fDataArrPrincipal');
      console.log($scope.fDataArrPrincipal);
      if( $scope.mySelectionGrid.length >= 1  ){ 
        angular.forEach($scope.mySelectionGrid, function(value, key) {
          angular.forEach($scope.fDataArrPrincipal,function(valueAP, keyAP){ 
            angular.forEach(valueAP.analisis,function(valueAnal, keyAnal){ 
              if( valueAP.idseccion == value.idseccion && valueAnal.idanalisispaciente == value.idanalisispaciente ){
                $scope.fDataArrPrincipal[keyAP].analisis[keyAnal].seleccionado = true;
                ++$scope.fDataArrPrincipal[keyAP].analisis[keyAnal].numero_impresiones;
                $scope.fDataArrPrincipal[keyAP].seleccionado = true;
                console.log('Impresiones');
                console.log($scope.fDataArrPrincipal[keyAP].analisis[keyAnal].numero_impresiones);
              }
            });
          });
        });
        $scope.fData.arrSecciones = $scope.fDataArrPrincipal; 
        
        // $scope.fBusqueda.titulo = $scope.selectedReport.name;
        // $scope.fBusqueda.tituloAbv = $scope.selectedReport.id;
        var arrParams = {
          titulo: 'RESULTADO DE LABORATORIO',
          datos:{
            resultado: $scope.fData,
            salida: 'pdf',
            tituloAbv: 'LAB-RL',
            titulo: 'RESULTADO DE LABORATORIO'
          },
          metodo: 'php'
        } 
        arrParams.url = angular.patchURLCI+'CentralReportesMPDF/report_resultado_laboratorio', 
        ModalReporteFactory.getPopupReporte(arrParams);
        // ---- ACTUALIZAR NUMERO DE IMPRESIONES ----
        if($scope.fSessionCI.key_group != 'key_sistemas'){
          registrarResultadosServices.sActualizarImpresiones($scope.fDataArrPrincipal).then(function (rpta) { 
            if(rpta.flag == 1) { 
              console.log('Actualizado el numero de impresiones ok');
              $scope.btnConsultarPacientesAtencion();
              $scope.sel = false;
              $scope.mySelectionGrid = {};
            }else{
              console.log('ocurrió un error');
            }
          });
        }
        //console.log($scope.fDataArrPrincipal);
        return false;

       
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'No se seleccionó ninguna orden.', type: 'warning', delay: 3500 });
      }
     
    }
    $scope.btnEntregarSel = function(){
      angular.forEach($scope.fDataArrPrincipal,function(valueAP, keyAP){ 
        angular.forEach(valueAP.analisis,function(valueAnal, keyAnal){ 
          $scope.fDataArrPrincipal[keyAP].analisis[keyAnal].seleccionado = false;
          $scope.fDataArrPrincipal[keyAP].seleccionado = false;
        });
      });

      if( $scope.mySelectionGrid.length >= 1  ){ 
        angular.forEach($scope.mySelectionGrid, function(value, key) {
          angular.forEach($scope.fDataArrPrincipal,function(valueAP, keyAP){ 
            angular.forEach(valueAP.analisis,function(valueAnal, keyAnal){ 
              if( valueAP.idseccion == value.idseccion && valueAnal.idanalisis == value.idanalisis ){
                $scope.fDataArrPrincipal[keyAP].analisis[keyAnal].seleccionado = true;
                $scope.fDataArrPrincipal[keyAP].seleccionado = true;
              }
            });
          });
        });
        $scope.fData.arrSecciones = $scope.fDataArrPrincipal;
        
        registrarResultadosServices.sEntregarResultados($scope.fData).then(function (rpta) { 
          if(rpta.flag == 1) { 
            console.log('Entrega de resultados de Laboratorio ok');
            pTitle = 'OK!';
            pType = 'success';
            $scope.sel = false;
            // $scope.fBusqueda.numeroOrden = null;
            // $scope.fBusqueda.numeroHistoria = null;
            // $scope.fBusqueda.paciente = null;
            // $scope.pacEncontrado = false;
            // $scope.fData = {};
            // $scope.fDataAnal = {};
            $scope.mySelectionGrid = {};
            //$scope.btnVolver();
            $scope.btnConsultarPacientesAtencion();
          }else if(rpta.flag == 0){
            var pTitle = 'AVISO!';
            var pType = 'danger'; 
          }
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
        });
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'No seleccionó ninguna orden.', type: 'warning', delay: 3500 });
      }
    }
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */ 
    shortcut.remove('F2');
    shortcut.add("F2",function($event) { 
        $scope.btnGuardar(); 
    }); 
    shortcut.remove('F3');
    shortcut.add("F3",function($event) {
      $route.reload(); 
    });
    shortcut.remove('F4');
    shortcut.add("F4",function($event) {
      if($scope.mySelectionGrid.length >= 1){ 
        $scope.btnImprimirSel(); 
      }
    }); 
  }])
  .service("registrarResultadosServices",function($http, $q) {
    return({
        sListarPacientesLaboratorio : sListarPacientesLaboratorio,
        sListarPacienteParaResultados : sListarPacienteParaResultados,
        sListarPacientesAutoComplete : sListarPacientesAutoComplete,
        sListarParametrosAnalisis : sListarParametrosAnalisis,
        sListarParametrosAnalisisRes : sListarParametrosAnalisisRes,
        sListarBacteriasCbo : sListarBacteriasCbo,
        sListarParasitosCbo : sListarParasitosCbo,
        sGenerarResultados : sGenerarResultados,
        sRegistrarResultadosPaciente : sRegistrarResultadosPaciente,
        sRegistrarAtencionLaboratorio : sRegistrarAtencionLaboratorio,
        sEntregarResultados : sEntregarResultados,
        sActualizarImpresiones : sActualizarImpresiones,
        
    });
    function sListarPacientesLaboratorio(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listar_pacientes_laboratorio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacienteParaResultados(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarPacientesParaResultados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacientesAutoComplete(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarPacientesAutocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarParametrosAnalisis(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarParametrosAnalisis", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarParametrosAnalisisRes(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarParametrosAnalisisRes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarBacteriasCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"urocultivo/lista_urocultivo_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarParasitosCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ParasitoHeces/lista_parasito_heces_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGenerarResultados(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/generar_resultados_sqlserver", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarResultadosPaciente(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/registrarResultadosPaciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarAtencionLaboratorio(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/registrar_atencion_laboratorio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEntregarResultados(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/entregar_resultados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarImpresiones(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/actualizar_impresiones", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });