angular.module('theme.solicitudes', ['theme.core.services'])
  .controller('solicitudesController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox', '$controller',
    'solicitudProcedimientoServices',
    'solicitudExamenServices',
    'empleadoSaludServices',
    'clienteServices',
    'ModalReporteFactory',
    function($scope, $filter, $sce, $route, $interval, $modal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      $bootbox, $controller,
      solicitudProcedimientoServices,
      solicitudExamenServices,
      empleadoSaludServices,
      clienteServices,
      ModalReporteFactory){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");

    //$scope.patronFecha = '\d{2}-\d{2}-\d{4}';
    $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    $scope.pMinuto = /^[0-5][0-9]$/;
    $scope.fBusqueda = {};
    $scope.modulo = 'solicitudes';
    $scope.fData = {};
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.desdeHora = '00';
    $scope.fBusqueda.desdeMinuto = '00';
    $scope.fBusqueda.hastaHora = 23;
    $scope.fBusqueda.hastaMinuto = 59;
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGridIngr = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.btnToggleFilteringEA = function(){
      $scope.gridOptionsEA.enableFiltering = !$scope.gridOptionsEA.enableFiltering;
      $scope.gridApiAnulado.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    
    

    /* GRILLA PRINCIPAL */
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
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
      multiSelect: false,
      columnDefs: [ 
        { field: 'idsolicitudprocedimiento', name: 'idsolicitudprocedimiento', displayName: 'ID', width: '5%', visible: true },
        { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA SOLICITUD', width: '11%', enableFiltering: false,
          sort: { direction: uiGridConstants.DESC}},
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HISTORIA',width: '10%', visible:false},
        { field: 'paciente', name: 'apellido_paterno', displayName: 'PACIENTE'},
        { field: 'producto', name: 'descripcion', displayName: 'PRODUCTO',width: '15%' },
        { field: 'especialidad', name: 'nombre', displayName: 'ESPECIALIDAD',width: '12%' },
        { field: 'medico', name: 'med_apellido_paterno', displayName: 'MEDICO' },
        // { field: 'estado_sp', name: 'estado_sp', displayName: 'ESTADO' },

        { field: 'estado', type: 'object', name: 'estado_sp', displayName: 'ESTADO', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        }

      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridIngr = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridIngr = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            // POR DEFECTO ORDENAR POR: [0] => ID
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'idsolicitudprocedimiento' : grid.columns[1].filters[0].term,
            'idhistoria' : grid.columns[3].filters[0].term,
            "CONCAT(cl.apellido_paterno,' ',cl.apellido_materno,', ',cl.nombres)" : grid.columns[4].filters[0].term,
            'descripcion' : grid.columns[5].filters[0].term,
            'nombre' : grid.columns[6].filters[0].term,
            "CONCAT(med.med_apellido_paterno,' ',med.med_apellido_materno,', ',med.med_nombres)" : grid.columns[7].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });
        // $interval( function() {
        //   $scope.gridApi.core.handleWindowResize();
        // }, 10, 500);
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[1].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      console.log($scope.fBusqueda);
      solicitudProcedimientoServices.sListarSolicitudesProcedimientoSession(arrParams).then(function (rpta) { 
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        
      });
      $scope.mySelectionGridIngr = [];
    };
    
    /*==================================== BOTON PROCESAR =========================================================*/
    $scope.procesar = function(){
      if(!$scope.formSolicitud.$invalid){
        $scope.getPaginationServerSide();
        $scope.getPaginationEAServerSide();
      }else{
        pinesNotifications.notify({ title: 'Warning.', text: 'Rellene los campos obligatorios.', type: 'warning', delay: 3000 });
      }
      
    }
    /*=============================================================================================================*/
    $scope.btnNuevoProcedimiento = function(size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'solicitudProcedimiento/ver_popup_solicitud',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.isRegisterSuccess = false;
          $scope.fData = {};
          $scope.fData.fecha_solicitud = $filter('date')(new Date(),'dd-MM-yyyy');
          
          $scope.titleForm = 'Solicitud de Procedimiento';
          $scope.getMedicoAutocomplete = function (value) {
            var params = {
              search: value,
              sensor: false
            }
            return empleadoSaludServices.sListarMedicosAtencionTodos(params).then(function(rpta) { 
              $scope.noResultsLM = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLM = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedMedico = function ($item, $model, $label) {
              $scope.fData.idmedico = $item.idmedico;
          };
          $scope.getProcedimientoAutocomplete = function (value) {
            var params = {
              searchText: value,
              searchColumn: 'descripcion',
              sensor: false
            }
            return solicitudProcedimientoServices.sListarProcedimientoAutoComplete(params).then(function(rpta) { 
              $scope.noResultsLP = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLP = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedProcedimiento = function ($item, $model, $label) {
              $scope.fData.idproductomaster = $item.id;
          };
          $scope.obtenerClientePorHistoria = function(){
            if( $scope.fData.idhistoria ){ 
              clienteServices.sListarEsteClientePorHistoria($scope.fData).then(function (rpta) { 
                if( rpta.flag === 1 ){
                  $scope.fData.cliente = {
                    'id' : rpta.datos[0].idcliente,
                    'descripcion' : rpta.datos[0].apellidos + ', ' + rpta.datos[0].nombres,
                    'idhistoria' : rpta.datos[0].idhistoria
                  };
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
                }else{
                  pinesNotifications.notify({ title: 'AVISO.', text: 'El Nº de historia no existe en el sistema.', type: 'warning', delay: 2000 });
                }
              });
            }
          }
          $scope.getPacienteAutocomplete = function (value) {
            var params = {
              searchText: value,
              searchColumn: "UPPER(CONCAT(c.apellido_paterno,' ',c.apellido_materno,', ',c.nombres))",
              sensor: false
            }
            return clienteServices.sListarClienteHistoriaAutoComplete(params).then(function(rpta) { 
              $scope.noResultsLC = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLC = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedPaciente = function ($item, $model, $label) {
              $scope.fData.idhistoria = $item.idhistoria;
          };

          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function(){
            console.log('fData', $scope.fData);
            $scope.fData.cantidad = 1;
            $scope.fData.idatencionmedica = null;
            solicitudProcedimientoServices.sRegistrarSolicitudProcedimiento($scope.fData).then(function (rpta) { 
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
              $scope.getPaginationServerSide();
              $modalInstance.dismiss('cancel');
            });
          }
         
        }
      })  
    }
    $scope.btnAnularProcedimiento = function() {
      var pMensaje = '¿Realmente desea anular la entrada?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          solicitudProcedimientoServices.sAnularEntrada($scope.mySelectionGridIngr).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
    
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }
    $scope.btnExportarListaExcel = function(){
      var arrParams = {
        titulo: 'SOLICITUDES DE PROCEDIMIENTOS',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'excel',
          tituloAbv: 'SPC',
          titulo: 'SOLICITUDES DE PROCEDIMIENTOS',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportes/report_solicitudes_procedimiento_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    /************** GRID DE EXAMENES AUXILIARES **************/
    var paginationOptionsEA = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsEA = {
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
      multiSelect: false,
      columnDefs: [
        { field: 'idsolicitudexamen', name: 'idsolicitudexamen', displayName: 'ID', width: '5%', visible: true },
        { field: 'fecha_solicitud', name: 'fecha_solicitud', displayName: 'FECHA SOLICITUD', width: '11%', enableFiltering: false,
          sort: { direction: uiGridConstants.DESC}
        },
        { field: 'idhistoria', name: 'idhistoria', displayName: 'HISTORIA',width: '10%', visible:false},
        { field: 'paciente', name: 'apellido_paterno', displayName: 'PACIENTE'},
        { field: 'producto', name: 'descripcion', displayName: 'PRODUCTO',width: '15%' },
        { field: 'especialidad', name: 'nombre', displayName: 'ESPECIALIDAD',width: '12%' },
        { field: 'medico', name: 'med_apellido_paterno', displayName: 'MEDICO' },
        { field: 'estado', type: 'object', name: 'estado_sex', displayName: 'ESTADO', width: '5%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 
            '</div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApiAnulado = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridEA = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridEA = gridApi.selection.getSelectedRows();
        });

        $scope.gridApiAnulado.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsEA.sort = null;
            paginationOptionsEA.sortName = null;
          } else {
            paginationOptionsEA.sort = sortColumns[0].sort.direction;
            paginationOptionsEA.sortName = sortColumns[0].name;
          }
          $scope.getPaginationEAServerSide();
        });
        $scope.gridApiAnulado.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsEA.search = true; 
          paginationOptionsEA.searchColumn = { 
            'idsolicitudexamen' : grid.columns[1].filters[0].term,
            'idhistoria' : grid.columns[3].filters[0].term,
             "CONCAT(cl.apellido_paterno,' ',cl.apellido_materno,', ',cl.nombres)" : grid.columns[4].filters[0].term,
            'descripcion' : grid.columns[5].filters[0].term,
            'nombre' : grid.columns[6].filters[0].term,
            "CONCAT(med.med_apellido_paterno,' ',med.med_apellido_materno,', ',med.med_nombres)" : grid.columns[7].filters[0].term,

          }
          $scope.getPaginationEAServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsEA.pageNumber = newPage;
          paginationOptionsEA.pageSize = pageSize;
          paginationOptionsEA.firstRow = (paginationOptionsEA.pageNumber - 1) * paginationOptionsEA.pageSize;
          $scope.getPaginationEAServerSide();
        });
      }
    };
    paginationOptionsEA.sortName = $scope.gridOptionsEA.columnDefs[1].name;
    $scope.getPaginationEAServerSide = function() { 
      var arrParams = {
        paginate : paginationOptionsEA,
        datos : $scope.fBusqueda
      };
      solicitudExamenServices.sListarSolicitudesExamenSession(arrParams).then(function (rpta) {
        $scope.gridOptionsEA.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsEA.data = rpta.datos;
        $scope.gridOptionsEA.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGridEA = [];
    };
    $scope.btnNuevoExamen = function(size,tipoExamen) {
      $modal.open({
        templateUrl: angular.patchURLCI+'solicitudExamen/ver_popup_solicitud',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          //var tipoExamen = null;
          $scope.isRegisterSuccess = false;
          $scope.fData = {};
          // $scope.fData.idmedico = null;
          // $scope.fData.idproductomaster = null;
          // $scope.fData.medico = null;
          // $scope.fData.examen_auxiliar = null;
          // $scope.fData.idhistoria = null;
          // $scope.fData.cliente = null;
          // $scope.fData.indicaciones = null;
          $scope.fData.fecha_solicitud = $filter('date')(new Date(),'dd-MM-yyyy');
          switch(tipoExamen) {
            case 'I': $scope.fData.tipoExamen = 'Imagenología'; break;
            case 'PC': $scope.fData.tipoExamen = 'Laboratorio'; break;
            case 'AP': $scope.fData.tipoExamen = 'Anatomía Patológica'; break;
            default : 'c'; break;
          }
          $scope.titleForm = 'Solicitud de Examen Auxiliar de ' + $scope.fData.tipoExamen;
          $scope.getMedicoAutocomplete = function (value) {
            var params = {
              search: value,
              sensor: false
            }
            return empleadoSaludServices.sListarMedicosAtencionTodos(params).then(function(rpta) { 
              $scope.noResultsLM = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLM = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedMedico = function ($item, $model, $label) {
              $scope.fData.idmedico = $item.idmedico;
          };
          $scope.getExamenAutocomplete = function (value) {
            var params = {
              searchText: value,
              searchColumn: 'descripcion',
              tipoExamen: tipoExamen,
              sensor: false
            }
            return solicitudExamenServices.sListarExamenesAutoComplete(params).then(function(rpta) { 
              $scope.noResultsLE = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLE = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedExamen = function ($item, $model, $label) {
              $scope.fData.idproductomaster = $item.id;
          };
          $scope.obtenerClientePorHistoria = function(){
            if( $scope.fData.idhistoria ){ 
              clienteServices.sListarEsteClientePorHistoria($scope.fData).then(function (rpta) { 
                if( rpta.flag === 1 ){
                  $scope.fData.cliente = {
                    'id' : rpta.datos[0].idcliente,
                    'descripcion' : rpta.datos[0].apellidos + ', ' + rpta.datos[0].nombres,
                    'idhistoria' : rpta.datos[0].idhistoria
                  };
                  pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
                }else{
                  pinesNotifications.notify({ title: 'AVISO.', text: 'El Nº de historia no existe en el sistema.', type: 'warning', delay: 2000 });
                }
              });
            }
          }
          $scope.getPacienteAutocomplete = function (value) {
            var params = {
              searchText: value,
              searchColumn: "UPPER(CONCAT(c.apellido_paterno,' ',c.apellido_materno,', ',c.nombres))",
              sensor: false
            }
            return clienteServices.sListarClienteHistoriaAutoComplete(params).then(function(rpta) { 
              $scope.noResultsLC = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLC = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedPaciente = function ($item, $model, $label) {
              $scope.fData.idhistoria = $item.idhistoria;
          };

          $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function(){
            console.log('fData', $scope.fData);
            $scope.fData.idatencionmedica = null;
            solicitudExamenServices.sRegistrarSolicitudExamen($scope.fData).then(function (rpta) { 
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
              $scope.getPaginationEAServerSide();
              $modalInstance.dismiss('cancel');
              
            });
          }
         
        }
      })  
    }

    $scope.btnExportarListaEAExcel = function(){
      var arrParams = {
        titulo: 'SOLICITUDES DE EXAMENES AUXILIARES',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptionsEA,
          salida: 'excel',
          tituloAbv: 'SEA',
          titulo: 'SOLICITUDES DE EXAMENES AUXILIARES',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportes/report_solicitudes_examen_auxiliar_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    
  }])
  