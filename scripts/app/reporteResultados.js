angular.module('theme.reporteResultados', ['theme.core.services'])
  .controller('reporteResultadosController', ['$scope', '$filter','$sce', '$interval', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications'
    ,'hotkeys'
    ,'reporteResultadosServices'
    ,'ModalReporteFactory'
    ,function($scope, $filter, $sce, $interval, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      ,hotkeys
      ,reporteResultadosServices
      ,ModalReporteFactory
      ){
    'use strict';
    $scope.fBusqueda = {};
    $scope.fBusquedaPAD = {};
    $scope.fData = {};
    
    $scope.listaFiltroBusqueda = [
      { id:'PNO', descripcion:'POR N° DE ORDEN DE LABORATORIO' },
      { id:'PH', descripcion:'POR N° DE HISTORIA' },
      { id:'PP', descripcion:'POR PACIENTE' } 
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
    }

    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.isRegisterSuccess = false;
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    $scope.fData = {};
    $scope.fData.paciente = {};
    $scope.fData.idmuestrapaciente = '-'
    $scope.gridOptions = {
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
      data: [],
      columnDefs: [
        { field: 'seccion', name: 'seccion', displayName: 'Sección',  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion_anal', name: 'descripcion_anal', displayName: 'Analisis' },
        { field: 'fecha_resultado', name: 'fecha_resultado', displayName: 'Fecha de Resultado' },
        { field: 'estado', type: 'object', name: 'estado_ap', displayName: 'Estado', maxWidth: 250, enableFiltering: false,
           cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
          }else {
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
      return reporteResultadosServices.sListarPacientesResAutoComplete(params).then(function(rpta) { 
        console.log(rpta.datos);
        $scope.noResultsPACI = false; 
        if( rpta.flag === 0 ){ 
          $scope.noResultsPACI = true; 
        } 
        return rpta.datos; 
      });
    }
    $scope.btnConsultarPacientesAtencion = function () { 
      var validateButton = false;
      if( $scope.fBusqueda.tipoBusqueda === 'PNO' ){ // N° ORDEN 
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
        // PACIENTES SIN ATENDER 
        $scope.gridOptions.data = [];
        //console.log($scope.fBusqueda);
        reporteResultadosServices.sListarPacienteConResultados($scope.fBusqueda).then(function (rpta) { 
          console.log(rpta.arrSecciones);
          if(rpta.flag == 1){ 
            var pTitle = 'OK!';
            var pType = 'success';
            $scope.fBusqueda.numeroOrden = null;
            $scope.fBusqueda.numeroHistoria = null;
            $scope.fBusqueda.paciente = null;
            $scope.pacEncontrado = true;
            $scope.fData = rpta.datos;
            $scope.fDataArrPrincipal = rpta.arrSecciones;
            $scope.gridOptions.data = rpta.arrAnalisis;

            // console.log(rpta.arrAnalisis);
            // $('#p00').focus();
          }else if(rpta.flag == 0){
            $scope.fBusqueda.numeroOrden = null;
            $scope.fBusqueda.numeroHistoria = null;
            $scope.fBusqueda.paciente = null;
            $scope.pacEncontrado = false;
            $scope.fData = {};
            var pTitle = 'Error!';
            var pType = 'danger';
          }else{
            alert('Se ha producido un problema. Contacte con el Area de Sistemas');
          }
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });
        });
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'Digite un patrón de búsqueda. El campo está vacío.', type: 'warning', delay: 3500 });
      }
    }
    $scope.btnImprimirSel = function (){
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
        console.log($scope.fDataArrPrincipal); return false; 
      }else{
        pinesNotifications.notify({ title: 'Advertencia', text: 'No seleccionó ninguna orden.', type: 'warning', delay: 3500 });
      } 
    }
    
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */ 
    shortcut.remove('F4');
    shortcut.add("F4",function(event) { 
      if($scope.mySelectionGrid.length >= 1){ 
        $scope.btnImprimirSel(); 
      } 
    });
    
  }])
  .service("reporteResultadosServices",function($http, $q) {
    return({
        sListarPacienteConResultados,
        sListarPacientesResAutoComplete,
        sImprimirResultadosPaciente,
        sImprimirResultadoSelPaciente
        
       
    });
    function sListarPacienteConResultados(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarPacientesParaResultados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacientesResAutoComplete(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/listarPacientesResAutocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirResultadosPaciente(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/imprimirResultadosPaciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirResultadoSelPaciente(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"resultadoAnalisis/imprimirResultadoSelPaciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });