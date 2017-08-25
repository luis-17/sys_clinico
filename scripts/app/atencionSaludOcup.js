angular.module('theme.atencionSaludOcup', ['theme.core.services'])
  .controller('atencionSaludOcupController', ['$scope', '$sce', '$filter', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'atencionSaludOcupServices',
    'empresasClienteServices',
    'clienteServices',
    'empleadoSaludServices',
    'ModalReporteFactory',
    function($scope, $sce, $filter, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
      atencionSaludOcupServices,
      empresasClienteServices,
      clienteServices,
      empleadoSaludServices,
      ModalReporteFactory ) { 
    // 'use strict'; 
    $scope.dirDocumentoSaludOcupIPP = $scope.dirImages + "dinamic/saludOcupacional/informesPorPaciente/";
    $scope.dirDocumentoSaludOcupIPV = $scope.dirImages + "dinamic/saludOcupacional/informesPorVenta/";
    shortcut.remove("F2"); 
    $scope.modulo = 'atencionSaludOcup';
    $scope.fBusquedaAT = {};
    $scope.fBusquedaAT.empresa = [];

    var desde = moment().subtract(30,'days'); 
    $scope.fBusquedaAT.desde = $filter('date')(desde.toDate(),'dd-MM-yyyy');
    $scope.fBusquedaAT.desdeHora = '00';
    $scope.fBusquedaAT.desdeMinuto = '00';
    $scope.fBusquedaAT.hastaHora = 23;
    $scope.fBusquedaAT.hastaMinuto = 59;
    $scope.fBusquedaAT.hasta = $filter('date')(new Date(),'dd-MM-yyyy');

    /* LISTA DE EMPRESAS SALUD OCUP. */
    empresasClienteServices.sListarEmpresasSoloSaludOcup().then(function (rpta) { 
      $scope.listaEmpresas = rpta.datos;
      $scope.fBusquedaAT.empresa = $scope.listaEmpresas[0];
      $scope.getPaginationServerSide();
    });

    //=============================================================
    // AUTOCOMPLETADO EMPRESA CLIENTE 
    //=============================================================
    $scope.getClienteAutocomplete = function (value) {
      var params = $scope.fBusquedaAT;
      params.search = value;
      params.sensor = false;

      return clienteServices.sListarClientesOcupacionalConPerfilAutocomplete(params).then(function(rpta) { 
        $scope.noResultsEmpresaCliente = false;
        if( rpta.flag === 0 ){
          $scope.noResultsEmpresaCliente = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedCliente = function ($item, $model, $label) {
        $scope.fBusquedaAT.idcliente = $item.id;
    };
    $scope.getClearInputCliente = function () { 
      if(!angular.isObject($scope.fBusquedaAT.cliente) ){ 
        $scope.fBusquedaAT.idcliente = null; 
      }
    }

    var paginationOptionsAT = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 100,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFilteringAT = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    //$scope.dirImagesEmpleados = $scope.dirImages + "dinamic/empleado/";
    $scope.gridOptions = {
      minRowsToShow: 4,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 100,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: false,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'iddetalle', name: 'iddetalle', displayName: 'ID', width: 80, visible: false },
        { field: 'orden', name: 'orden_venta', displayName: 'N° ORDEN', width: 140  },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width: 90,enableFiltering: false  },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: 100, cellClass: 'bg-lightblue' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: 120, sort: { direction: uiGridConstants.DESC} },
        { field: 'empresa', name: 'empresa', displayName: 'EMPRESA/CLIENTE',width: 260 },
        { field: 'producto', name: 'producto', displayName: 'PERFIL/PRODUCTO',width: 200 },
        { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD',width: 80 },
        { field: 'precio_unit', name: 'precio_unitario', displayName: 'PRECIO UNIT.',width: 100 },
        { field: 'total_detalle', name: 'total_detalle', displayName: 'IMPORTE',width: 110 },
        { field: 'informe_texto_so', name: 'informe_texto_so', displayName: 'INFORME MANUAL',width: 300 }, 
        { field: 'archivo', name: 'archivo', displayName: 'INFORME PDF',width: 90, enableFiltering: false, enableSorting: false, type: 'object',
          cellTemplate:'<div ng-show="COL_FIELD.documento"><a target="_blank" href="{{ grid.appScope.dirDocumentoSaludOcupIPV + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </div>' }
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
          // console.log($scope.mySelectionGrid); 
          if($scope.mySelectionGrid.length == 1){
            $scope.getPaginationServerSideAT();
          }else{
            $scope.gridOptionsAtencion.data = [];
          } 
          $scope.mySelectionGridDE = [];
        });
        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsAT.sort = null;
            paginationOptionsAT.sortName = null;
          } else {
            paginationOptionsAT.sort = sortColumns[0].sort.direction;
            paginationOptionsAT.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsAT.pageNumber = newPage;
          paginationOptionsAT.pageSize = pageSize;
          paginationOptionsAT.firstRow = (paginationOptionsAT.pageNumber - 1) * paginationOptionsAT.pageSize;
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsAT.search = true;
          paginationOptionsAT.searchColumn = { 
            'iddetalle' : grid.columns[1].filters[0].term,
            'orden_venta' : grid.columns[2].filters[0].term,
            //'fecha_venta' : grid.columns[3].filters[0].term,
            'descripcion_td' : grid.columns[4].filters[0].term,
            'ticket_venta' : grid.columns[5].filters[0].term,
            'ec.descripcion' : grid.columns[6].filters[0].term,
            'pm.descripcion' : grid.columns[7].filters[0].term,
            'cantidad' : grid.columns[8].filters[0].term,
            'precio_unitario' : grid.columns[9].filters[0].term,
            'total_detalle' : grid.columns[10].filters[0].term 
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptionsAT.sortName = $scope.gridOptions.columnDefs[4].name;
    $scope.getPaginationServerSide = function() {
      // $scope.$parent.blockUI.start();
      console.log($scope.fBusquedaAT,'$scope.fBusquedaAT');
      var arrParams = {
        paginate : paginationOptionsAT,
        datos: $scope.fBusquedaAT
      };
      atencionSaludOcupServices.sListarVentasSaludOcupacion(arrParams).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    
    /* ATENCIONES */ 
    var paginationOptionsDE = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGridDE = [];
    $scope.btnToggleFilteringDE = function(){
      $scope.gridOptionsAtencion.enableFiltering = !$scope.gridOptionsAtencion.enableFiltering;
      $scope.gridApiDE.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    
    $scope.dirIconoFormat = $scope.dirImages + "formato-imagen/";
    $scope.gridOptionsAtencion = { 
      message:'Seleccione un perfil para ver sus atenciones.',
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
        { field: 'idatencionocupacional', name: 'idatencionocupacional', displayName: 'ID', width: '4%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'cliente', name: 'cliente', displayName: 'CLIENTE', width: '18%' },
        { field: 'fecha_atencion', name: 'fecha_atencion', displayName: 'FECHA REGISTRO',width: '10%',enableFiltering: false }, // fecha en que se registra la atencion 
        { field: 'producto', name: 'producto', displayName: 'PERFIL',width: '16%'},
        { field: 'empresa', name: 'empresa', displayName: 'EMPRESA/CLIENTE',width: '18%'},
        { field: 'informe', name: 'informe', displayName: 'INFORME MANUAL',width: '25%' }, 
        { field: 'archivo', name: 'archivo', displayName: 'INFORME PDF',width: '6%', enableFiltering: false, enableSorting: false, type: 'object',
          cellTemplate:'<div ng-show="COL_FIELD.documento"><a target="_blank" href="{{ grid.appScope.dirDocumentoSaludOcupIPP + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </div>' }
      ],
      onRegisterApi: function(gridApiDE) {
        $scope.gridApiDE = gridApiDE;
        gridApiDE.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridDE = gridApiDE.selection.getSelectedRows();
        });
        gridApiDE.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridDE = gridApiDE.selection.getSelectedRows();
        });

        $scope.gridApiDE.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsDE.sort = null;
            paginationOptionsDE.sortName = null;
          } else {
            paginationOptionsDE.sort = sortColumns[0].sort.direction;
            paginationOptionsDE.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideAT();
        });
        gridApiDE.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsDE.pageNumber = newPage;
          paginationOptionsDE.pageSize = pageSize;
          paginationOptionsDE.firstRow = (paginationOptionsDE.pageNumber - 1) * paginationOptionsDE.pageSize;
          $scope.getPaginationServerSideAT();
        });
        $scope.gridApiDE.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsDE.search = true;
          paginationOptionsDE.searchColumn = { 
            'idatencionocupacional' : grid.columns[1].filters[0].term,
            "CONCAT(cl.nombres || ' ' || cl.apellido_paterno || ' ' || cl.apellido_materno)" : grid.columns[3].filters[0].term,
            'descripcion_doc' : grid.columns[3].filters[0].term
          }
          $scope.getPaginationServerSideAT();
        });
      }
    };
    paginationOptionsDE.sortName = $scope.gridOptionsAtencion.columnDefs[2].name;
    $scope.getPaginationServerSideAT = function() {
      $scope.$parent.blockUI.start();
      var arrParams = {
        paginate : paginationOptionsDE,
        datos: { iddetalle: $scope.mySelectionGrid[0].iddetalle }

      };
      atencionSaludOcupServices.sListarAtencionesDePerfilSaludOcupacional(arrParams).then(function (rpta) {
        $scope.gridOptionsAtencion.totalItems = rpta.paginate.totalRows.contador;
        $scope.gridOptionsAtencion.data = rpta.datos;
        if( !$scope.gridOptionsAtencion.data.length ){
          $scope.gridOptionsAtencion.message = 'No se encontraron atenciones médicas en el perfil seleccionado.';
        }
        $scope.$parent.blockUI.stop();
      });
    };

    $scope.btnAgregarAtencionMedica = function () { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'AtencionSaludOcup/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.fData.cliente = {};
          $scope.titleForm = 'Atención - Salud Ocupacional'; 
          var arrCliente = {};
          arrCliente.idcliente = $scope.fBusquedaAT.idcliente;
          clienteServices.sListarEsteCliente(arrCliente).then(function (rpta) {
            $scope.fData.cliente = rpta.datos[0];
            $scope.fData.fechaAtencion = $filter('date')(new Date(),'dd-MM-yyyy'); 
            $scope.fData.orden = $scope.mySelectionGrid[0].orden;
            $scope.fData.idventa = $scope.mySelectionGrid[0].idventa;
            $scope.fData.iddetalle = $scope.mySelectionGrid[0].iddetalle; 
            $scope.fData.idproductomaster = $scope.mySelectionGrid[0].idproductomaster;
            $scope.fData.perfil = $scope.mySelectionGrid[0].producto;
            $scope.fData.idempresacliente = $scope.fBusquedaAT.empresa.idempresacliente;
          });
          
          //=============================================================
          // AUTOCOMPLETADO MEDICOS 
          //=============================================================
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
          $scope.getSelectedMedico = function ($item, $model, $label) { 
              $scope.fData.idmedico = $item.id;
          };
          $scope.getClearInputMedico = function () { 
            if(!angular.isObject($scope.fData.medico) ){ 
              $scope.fData.idmedico = null; 
            }
          }
    
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            var formData = new FormData();
            //$scope.fData.idempleado = $scope.mySelectionGrid[0].id; 
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });
            console.log($scope.fData,'$scope.fData');
            formData.append('cliente',JSON.stringify($scope.fData.cliente));
            atencionSaludOcupServices.sRegistrar(formData).then(function (rpta) { 
              if(rpta.flag === 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.cancel();
                $scope.getPaginationServerSideAT();
                $scope.fBusquedaAT.idcliente = null;
                $scope.fBusquedaAT.cliente = null;
              }else if(rpta.flag === 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }
      });
    }
    $scope.btnImprimirFicha = function () { 
      var arrParams = {
        titulo: 'FICHA DE ATENCION',
        url: angular.patchURLCI+'CentralReportesMPDF/report_ficha_atencion_salud_ocup',
        datos: {
          filas : $scope.mySelectionGridDE,
          titulo: 'FICHA DE ATENCION',
          tituloAbv: 'AM-FASO'
        },
        metodo: 'php'
      }; 
      ModalReporteFactory.getPopupReporte(arrParams); 
    }
    $scope.btnSubirInformeGeneral = function () { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'AtencionSaludOcup/ver_popup_subir_informe_general',
        size: 'lg',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.fData.orden = $scope.mySelectionGrid[0].orden;
          $scope.fData.perfil = $scope.mySelectionGrid[0].producto;
          //$scope.fData.cliente = {};
          $scope.titleForm = 'INFORME GENERAL DEL SERVICIO'; 
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            var formData = new FormData();
            $scope.fData.idventa = $scope.mySelectionGrid[0].idventa; 
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });
            atencionSaludOcupServices.sActualizarInformeGeneral(formData).then(function (rpta) { 
              if(rpta.flag === 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.cancel();
                $scope.getPaginationServerSide();
                // $scope.getPaginationServerSideAT();
                $scope.fBusquedaAT.idcliente = null;
                $scope.fBusquedaAT.cliente = null;
              }else if(rpta.flag === 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          atencionSaludOcupServices.sAnular($scope.mySelectionGridDE).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSideAT();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
  }])
  .service("atencionSaludOcupServices",function($http, $q) {
    return({
      sListarVentasSaludOcupacion: sListarVentasSaludOcupacion,
      sListarAtencionesDePerfilSaludOcupacional: sListarAtencionesDePerfilSaludOcupacional,
      sRegistrar: sRegistrar,
      sActualizarInformeGeneral: sActualizarInformeGeneral,
      sAnular: sAnular
    });
    function sListarVentasSaludOcupacion(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AtencionSaludOcup/listar_ventas_salud_ocupacional", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sListarAtencionesDePerfilSaludOcupacional(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AtencionSaludOcup/listar_atenciones_de_perfil_salud_ocupacional", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AtencionSaludOcup/registrar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarInformeGeneral (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AtencionSaludOcup/actualizar_informe_general", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AtencionSaludOcup/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });