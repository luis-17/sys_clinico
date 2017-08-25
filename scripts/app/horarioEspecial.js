angular.module('theme.horarioEspecial', ['theme.core.services'])
  .controller('horarioEspecialController', ['$scope', '$sce', '$filter', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications',
     'empleadoServices',
     'horarioEspecialServices',
     'motivoHorarioEspecialServices',
     'asistenciaServices',
     'ModalReporteFactory',
    function($scope, $sce, $filter, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
      empleadoServices,
      horarioEspecialServices,
      motivoHorarioEspecialServices,
      asistenciaServices,
      ModalReporteFactory
    ) { 
    // 'use strict';
    shortcut.remove("F2"); $scope.modulo = 'horarioEspecial'; 
    $scope.fBusqueda = {};
    $scope.fBusquedaMC = {};
    $scope.fDataMC = {};
    $scope.fData = {};
    $scope.fData.temporal = {};
    $scope.fData.activeDate = null;
    $scope.fData.temporal.arrFechas = [];

    /* LISTA DE MOTIVOS */
    motivoHorarioEspecialServices.sListarMotivoHorarioEspecialCbo().then(function (rpta) {
      $scope.listaMotivoHE = rpta.datos;
      $scope.listaMotivoHE.splice(0,0,{ id : '', descripcion:'--Seleccione Motivo--'});
      $scope.fData.temporal.motivo = $scope.listaMotivoHE[0];
    });
    /* LISTA DE SUB-MOTIVOS */ 
    $scope.listaSubMotivoHE = [{ id : '', descripcion:'--Seleccione Sub-Motivo--'}];
    $scope.fData.temporal.submotivo = $scope.listaSubMotivoHE[0];
    $scope.listarSubMotivos = function () { 
      var arrParams = {
        'idmotivo': $scope.fData.temporal.motivo.id
      }; 
      motivoHorarioEspecialServices.sListarSubMotivoHorarioEspecialCbo(arrParams).then(function (rpta) { 
        if( rpta ){ 
          $scope.listaSubMotivoHE = rpta.datos;
          // $scope.listaSubMotivoHE.splice(0,0,{ id : '', descripcion:'--Seleccione Sub-Motivo--'});listarSubMotivos
          $scope.fData.temporal.submotivo = $scope.listaSubMotivoHE[0];
        }
        
      });
    }
    
    $scope.listaTipoAsistencia = [ 
      { id: 'NA' , descripcion: 'NO ASISTIRÁ ESTE DIA' },
      { id: 'SA' , descripcion: 'SI ASISTIRÁ ESTE DIA' }
    ];
    $scope.listaTercero = [ 
      { id: 'all', descripcion: 'TODOS' },
      { id: 1, descripcion: 'PERSONAL - TERCEROS' },
      { id: 2, descripcion: 'PERSONAL - PROPIOS' }
    ];
    $scope.fBusqueda.tercero = 2;
    $scope.fData.temporal.asistencia = $scope.listaTipoAsistencia[0];
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 50,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGridEM = [];
    $scope.mySelectionGridHE = [];
    $scope.mySelectionGridMC = [];
    /* GRILLA DE EMPLEADOS */ 
    $scope.dirImagesEmpleados = $scope.dirImages + "dinamic/empleado/";
    $scope.gridOptionsEmpleado = {
      // rowHeight: 48,
      minRowsToShow: 8,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 50,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableSelectAll: false,
      enableFiltering: true,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'num_documento', name: 'e.numero_documento', displayName: 'N° DOC.',width: 100, visible: false },
        { field: 'personal', name: "CONCAT(e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno)", displayName: 'PERSONAL',width: 250, sort: { direction: uiGridConstants.DESC } }, 
        { field: 'nombre_foto', name: 'nombre_foto', displayName: 'FOTO',width: 60, enableFiltering: false, enableSorting: false, 
          cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpleados + COL_FIELD }}" /> </div>' },
        { field: 'cargo', name: 'c.descripcion_ca', displayName: 'CARGO',width: 180 },
        { field: 'empresa', name: 'empresa', displayName: 'EMPRESA',width: 180 },
        { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD',width: 160 }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridEM = gridApi.selection.getSelectedRows(); 
          $scope.getPaginationServerSideHE();
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
          $scope.getPaginationServerSideEM();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSideEM();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = { 
            //'idempleado' : grid.columns[1].filters[0].term,
            'e.numero_documento' : grid.columns[1].filters[0].term,
            "CONCAT(e.nombres || ' ' || e.apellido_paterno || ' ' || e.apellido_materno)" : grid.columns[2].filters[0].term,
            'c.descripcion_ca' : grid.columns[4].filters[0].term,
            'em.descripcion' : grid.columns[5].filters[0].term,
            'esp.nombre' : grid.columns[6].filters[0].term
          }; 
          $scope.getPaginationServerSideEM();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptionsEmpleado.columnDefs[0].name;
    $scope.getPaginationServerSideEM = function() {
      $scope.fBusqueda.modulo = 'asist';
      var arrParams = { 
        paginate : paginationOptions,
        datos: $scope.fBusqueda
      };
      empleadoServices.sListarEmpleados(arrParams).then(function (rpta) {
        $scope.gridOptionsEmpleado.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsEmpleado.data = rpta.datos;
      });
      $scope.mySelectionGridEM = [];
      $scope.gridOptionsHorario.data = [];
    };
    $scope.nextControlForm = function (lengthString) {
      var lengthString = lengthString || 1;
      var myElement = document.activeElement;
      if( $(myElement).val().length >= lengthString ){ 
        var allControls = $('#contNextControl').find('input, textarea, select');
        var index = allControls.index(myElement);
        if(index > -1 &&(index+1) < (allControls.length - 1)){
          allControls.eq(index+1).focus();
          allControls.eq(index+1).select();
        }
      }
    }
    /* GRILLA DE HORARIOS ESPECIALES */ 
      var paginationOptionsHG = {
        pageNumber: 1,
        firstRow: 0,
        pageSize: 50,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };
      $scope.gridOptionsHorario = { 
        minRowsToShow: 9,
        paginationPageSizes: [10, 50],
        paginationPageSize: 50,
        enableSelectAll: true,
        enableFullRowSelection: true,
        multiSelect: true,
        data: [],
        columnDefs: [
          { field: 'id', name: 'idhorarioempleado', displayName: 'ID',width: 40 },
          { field: 'fecha_especial', name: 'fecha_especial', displayName: 'HORARIO ESP.',width: 130 },
          { field: 'motivo', name: 'descripcion', displayName: 'MOTIVO',width: 140 },
          { field: 'entrada', name: 'hora_entrada', displayName: 'ENTRADA',width: 130 },
          { field: 'salida', name: 'hora_salida', displayName: 'SALIDA',width: 130 },
          { field: 'tiempo_tolerancia', name: "tiempo_tolerancia", displayName: 'TOLERANCIA',width: 110 },
          { field: 'horas_trabajadas', name: "horas_trabajadas", displayName: 'HORAS TRAB.',width: 100 }
        ],
        onRegisterApi: function(gridApiHE) {
          $scope.gridApiHE = gridApiHE;
          gridApiHE.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionGridHE = gridApiHE.selection.getSelectedRows();
          });
          gridApiHE.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionGridHE = gridApiHE.selection.getSelectedRows();
          });
        }
      };
      $scope.getPaginationServerSideHE = function () { 
        if( $scope.mySelectionGridEM.length > 0 ){ 
          var arrParams = { 
            datos: $scope.mySelectionGridEM[0]
          };
          horarioEspecialServices.sListarHorarioEspecialEmpleado(arrParams).then(function (rpta) {
            $scope.gridOptionsHorario.data = rpta.datos; 
          });
          $scope.mySelectionGridHE = [];
        }else{
          $scope.gridOptionsHorario.data = [];
        }
      }
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */ 
    $scope.btnNuevo = function (size) { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'HorarioEspecial/ver_popup_formulario',
        size: 'lg',
        //backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'CREACION DEL HORARIO ESPECIAL'; 
          $scope.tieneHorarios = false;
          var paginationOptionsHEAdd = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsHorarioAdd = {
            minRowsToShow: 7,
            paginationPageSizes: [5, 10, 50],
            paginationPageSize: 10,
            enableSelectAll: false,
            multiSelect: false,
            data: [],
            columnDefs: [ 
              { field: 'fecha_especial', name: 'fecha_especial', displayName: 'HORARIO ESP.',width: 100, cellClass:'text-center' },
              { field: 'motivo', name: 'descripcion', displayName: 'MOTIVO' },
              { field: 'entrada', name: 'hora_entrada', displayName: 'ENTRADA',width: 100, cellClass:'text-center' },
              { field: 'salida', name: 'hora_salida', displayName: 'SALIDA',width: 100, cellClass:'text-center' },
              { field: 'tiempo_tolerancia', name: "tiempo_tolerancia", displayName: 'TOLERANCIA',width: 100, cellClass:'text-right' },
              { field: 'horas_trabajadas', name: "horas_trabajadas", displayName: 'HORAS TRAB.',width: 100, cellClass:'text-center' },
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApiHG) {
              $scope.gridApiHG = gridApiHG; 
            }
          };

          $scope.gridOptionsHorarioAdd.data = $scope.gridOptionsHorario.data; 

          if($scope.gridOptionsHorario.data.length > 0){
            $scope.tieneHorarios = true;
          }
          $scope.fData.temporal.entradaDisabled = false;
          $scope.fData.temporal.salidaDisabled = false;
          $scope.agregarHorarioItem = function () { 
            // console.log($scope.fData.temporal.arrFechas.length, 'dasf');
            if( !($scope.fData.temporal.arrFechas) || !($scope.fData.temporal.arrFechas.length) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado ninguna fecha', type: 'warning', delay: 3000 }); 
              return false; 
            }
            if( !($scope.fData.temporal.motivo.id) ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el motivo', type: 'warning', delay: 3000 }); 
              return false; 
            }
            if( $scope.fData.temporal.asistencia.id == 'SA' ){ 
            // if( $scope.fData.temporal.motivo.id == 9 ){ 
              if( $scope.fData.temporal.hora_desde_entrada.length < 1 ){ 
              //if( !($scope.fData.temporal.hora_desde_entrada)  ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: DESDE ENTRADA', type: 'warning', delay: 2500 });
                return false;
              }
              if( ($scope.fData.temporal.hora_entrada.length < 1 || $scope.fData.temporal.minuto_entrada.length < 1) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: ENTRADA', type: 'warning', delay: 2500 });
                return false;
              }
              if( $scope.fData.temporal.hora_hasta_salida.length < 1 ){ 
              // if( !($scope.fData.temporal.hora_hasta_salida) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: HASTA SALIDA', type: 'warning', delay: 2500 });
                return false;
              }
              if( $scope.fData.temporal.hora_salida.length < 1 || $scope.fData.temporal.minuto_salida.length < 1 ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: SALIDA', type: 'warning', delay: 2500 });
                return false;
              }
              if( !($scope.fData.temporal.minuto_tolerancia) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: TOLERANCIA', type: 'warning', delay: 2500 });
                return false;
              }
            }
            
            $('#temporalHorario').focus();
            var strEntrada = '-';
            var strDesdeEntrada = '-';
            var strDesdeSalida = '-';
            var strHastaEntrada = '-';
            var strHastaSalida = '-';
            var strSalida = '-';
            var strHorasTrabajadas = '-';
            var strTiempoTolerancia = '-';
            // if( $scope.fData.temporal.motivo.id == 9 ){ 
            if( $scope.fData.temporal.asistencia.id == 'SA' ){ 
              strDesdeEntrada = $scope.fData.temporal.hora_desde_entrada + ':' + $scope.fData.temporal.minuto_desde_entrada;
              strDesdeSalida = $scope.fData.temporal.hora_desde_salida + ':' + $scope.fData.temporal.minuto_desde_salida;

              strHastaEntrada = $scope.fData.temporal.hora_hasta_entrada + ':' + $scope.fData.temporal.minuto_hasta_entrada;
              strHastaSalida = $scope.fData.temporal.hora_hasta_salida + ':' + $scope.fData.temporal.minuto_hasta_salida;

              strEntrada = $scope.fData.temporal.hora_entrada + ':' + $scope.fData.temporal.minuto_entrada;
              strSalida = $scope.fData.temporal.hora_salida + ':' + $scope.fData.temporal.minuto_salida;

              strHorasTrabajadas = $scope.fData.temporal.horas_trabajadas;
              strTiempoTolerancia = $scope.fData.temporal.minuto_tolerancia;
            }

            angular.forEach($scope.fData.temporal.arrFechas,function (value,key) { 
              // console.log(value); 
              $scope.arrTemporal = { 
                'fecha_especial' : moment(value).format("DD-MM-YYYY"),
                'fecha_especial_sf' : null,
                'idmotivo' : $scope.fData.temporal.motivo.id,
                'idsubmotivo' : $scope.fData.temporal.submotivo.id,
                'motivo' : $scope.fData.temporal.motivo.descripcion+' - '+$scope.fData.temporal.submotivo.descripcion,
                'asistencia' : $scope.fData.temporal.asistencia.id,
                'entrada' : strEntrada,
                'salida' : strSalida,
                'desde_entrada' : strDesdeEntrada,
                'desde_salida' : strDesdeSalida,
                'hasta_entrada' : strHastaEntrada,
                'hasta_salida' : strHastaSalida,
                'horas_trabajadas' : strHorasTrabajadas,
                'tiempo_tolerancia': strTiempoTolerancia 
              }; 
              angular.forEach($scope.gridOptionsHorarioAdd.data, function(valueDet, keyDet) { 
                if( moment(value).format("DD-MM-YYYY") == valueDet.fecha_especial && valueDet.fecha_especial_sf === null ){ 
                  if (keyDet > -1) { 
                    $scope.gridOptionsHorarioAdd.data.splice(keyDet, 1);
                  }
                }
              });
              $scope.gridOptionsHorarioAdd.data.unshift($scope.arrTemporal);
            });
            //console.log($scope.gridOptionsHorarioAdd.data);
            $scope.fData.temporal = {
              asistencia: $scope.listaTipoAsistencia[0],
              motivo: $scope.listaMotivoHE[0],
              arrFechas: []
            }; 
            
            //$scope.fData.temporal
          }
          $scope.btnQuitarDeLaCesta = function (row) { 
            if( $scope.tieneHorarios && (row.entity.fecha_especial_sf) ){
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){ 
                  row.entity.modo = 'inline';
                  horarioEspecialServices.sEliminarHorarioEspecialDeEmpleado(row.entity).then(function (rpta) { 
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
                    $scope.getPaginationServerSideDetHE(); 
                    $scope.getPaginationServerSideHE();
                  });
                }
              });
            }else{
              var index = $scope.gridOptionsHorarioAdd.data.indexOf(row.entity); 
              $scope.gridOptionsHorarioAdd.data.splice(index,1); 
            }
          }
          $scope.getPaginationServerSideDetHE = function () { 
            if( $scope.mySelectionGridEM.length > 0 ){ 
              var arrParams = { 
                datos: $scope.mySelectionGridEM[0]
              };
              horarioEspecialServices.sListarHorarioEspecialEmpleado(arrParams).then(function (rpta) {
                $scope.gridOptionsHorarioAdd.data = rpta.datos; 
              });
              $scope.mySelectionGridHG = [];
            } 
          }
          $scope.generateHorasTrab = function () { 
            //console.log('validar dfs');
            //if( ($scope.fData.temporal.hora_entrada) && ($scope.fData.temporal.minuto_entrada) && ($scope.fData.temporal.hora_salida) && ($scope.fData.temporal.minuto_salida) ){ 
              //console.log('validar');
              var fechaEntrada  = moment().format('DD-MM-YYYY')+' '+$scope.fData.temporal.hora_entrada+':'+$scope.fData.temporal.minuto_entrada;
              var fechaSalida = moment().format('DD-MM-YYYY')+' '+$scope.fData.temporal.hora_salida+':'+$scope.fData.temporal.minuto_salida;

              $scope.fData.temporal.horas_trabajadas = moment.utc(moment(fechaSalida,"DD/MM/YYYY HH:mm:ss").diff(moment(fechaEntrada,"DD/MM/YYYY HH:mm:ss"))).format("HH:mm:ss");
              //console.log(intervalHora, 'intervalHora');
              //$scope.fData.temporal.horas_trabajadas = 8;
            //}
            if($scope.fData.temporal.hora_entrada != null && $scope.fData.temporal.minuto_entrada != null){
              $scope.fData.temporal.hora_desde_entrada = $scope.fData.temporal.hora_entrada-2;
              $scope.fData.temporal.minuto_desde_entrada = $scope.fData.temporal.minuto_entrada;
              $scope.fData.temporal.hora_hasta_entrada = $scope.fData.temporal.hora_entrada+2;
              $scope.fData.temporal.minuto_hasta_entrada = $scope.fData.temporal.minuto_entrada;
            }else{
              $scope.fData.temporal.hora_desde_entrada = null;
              $scope.fData.temporal.minuto_desde_entrada = null;
              $scope.fData.temporal.hora_hasta_entrada = null;
              $scope.fData.temporal.minuto_hasta_entrada = null;
            }
            if($scope.fData.temporal.hora_salida != null && $scope.fData.temporal.minuto_salida != null){
              $scope.fData.temporal.hora_desde_salida = $scope.fData.temporal.hora_salida-2;
              $scope.fData.temporal.minuto_desde_salida = $scope.fData.temporal.minuto_salida;
              $scope.fData.temporal.hora_hasta_salida = $scope.fData.temporal.hora_salida+2;
              $scope.fData.temporal.minuto_hasta_salida = $scope.fData.temporal.minuto_salida;
            }else{
              $scope.fData.temporal.hora_desde_salida = null;
              $scope.fData.temporal.minuto_desde_salida = null;
              $scope.fData.temporal.hora_hasta_salida = null;
              $scope.fData.temporal.minuto_hasta_salida = null;
            }
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.getPaginationServerSideHE();
          } 
          $scope.aceptar = function () { 
            $scope.fData.idempleado = $scope.mySelectionGridEM[0].id;
            $scope.fData.arrHorarios = $scope.gridOptionsHorarioAdd.data;
            horarioEspecialServices.sRegistrarHorarioEspecial($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSideHE();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
            });
          }
        }
      });
    }
    $scope.btnActualizarMarcado = function () { 
      $uibModal.open({
        templateUrl: angular.patchURLCI+'HorarioEspecial/ver_popup_actualizar_marcacion',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'ACTUALIZAR MARCACIÓN';  
          $scope.fBusquedaMC.desde = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fBusquedaMC.desdeHora = '00';
          $scope.fBusquedaMC.desdeMinuto = '00';
          $scope.fBusquedaMC.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
          $scope.fBusquedaMC.hastaHora = 23;
          $scope.fBusquedaMC.hastaMinuto = 59;
          var paginationOptionsMC = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 100,
            sort: uiGridConstants.DESC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsMC = { 
            minRowsToShow: 9,
            paginationPageSizes: [100, 500, 1000],
            paginationPageSize: 100,
            enableSelectAll: true,
            enableFullRowSelection: true,
            useExternalPagination: true,
            useExternalSorting: true,
            multiSelect: true,
            enableGridMenu: true,
            data: [],
            columnDefs: [ 
              { field: 'id', name: 'idasistencia', displayName: 'ID', width: '8%' },
              { field: 'fecha', name: 'fecha', displayName: 'FECHA', width: '18%',enableFiltering: false, sort: { direction: uiGridConstants.DESC } }, 
              { field: 'hora', name: 'hora', displayName: 'HORA', width: '18%' },
              { field: 'diferencia', name: 'diferencia_tiempo', displayName: 'DIF.', width: '15%', cellTemplate: '<div class="ui-grid-cell-contents">{{ COL_FIELD }}</div>' },
              { field: 'tipo', name: 'tipo_asistencia', displayName: 'TIPO', width: '16%' },
              { field: 'personal', name: 'personal', displayName: 'PERSONAL', width: '30%', visible:false },
              { field: 'estado', name: 'es.descripcion', displayName: 'ESTADO', width: '22%' ,enableFiltering: false,
                cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 160px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
              }
            ],
            onRegisterApi: function(gridApiMC) {
              $scope.gridApiMC = gridApiMC;
              gridApiMC.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionGridMC = gridApiMC.selection.getSelectedRows();
              });
              gridApiMC.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionGridMC = gridApiMC.selection.getSelectedRows(); 
              });

              $scope.gridApiMC.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationOptionsMC.sort = null;
                  paginationOptionsMC.sortName = null;
                } else {
                  paginationOptionsMC.sort = sortColumns[0].sort.direction;
                  paginationOptionsMC.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSideMC();
              });
              gridApiMC.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsMC.pageNumber = newPage;
                paginationOptionsMC.pageSize = pageSize;
                paginationOptionsMC.firstRow = (paginationOptionsMC.pageNumber - 1) * paginationOptionsMC.pageSize;
                $scope.getPaginationServerSideMC();
              });
            }
          };
          paginationOptionsMC.sortName = $scope.gridOptionsMC.columnDefs[1].name;
          $scope.getPaginationServerSideMC = function () { 
              var arrParams = { 
                paginate : paginationOptionsMC, 
                datos: { 
                  'idempleado' : $scope.mySelectionGridEM[0].id,
                  'filtros':  $scope.fBusquedaMC
                }
              };
              asistenciaServices.sListarAsistenciasDeEmpleado(arrParams).then(function (rpta) {
                $scope.gridOptionsMC.data = rpta.datos; 
                $scope.gridOptionsMC.totalItems = rpta.paginate.totalRows;
              });
          }
          $scope.getPaginationServerSideMC();
          $scope.aceptar = function () { 
            // $scope.fData.idempleado = $scope.mySelectionGridEM[0].id;
            $scope.fDataMC.asistencias = $scope.mySelectionGridMC;
            asistenciaServices.sActualizarMarcacionesEspeciales($scope.fDataMC).then(function (rpta) { 
              if(rpta.flag == 1){ 
                var pTitle = 'OK!';
                var pType = 'success';
                //$modalInstance.dismiss('cancel');
                $scope.getPaginationServerSideMC();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          } 
        }
      });
    }
    $scope.btnVerFechaEspecial = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'HorarioEspecial/ver_popup_fecha_especial',
        size: 'lg',
        //backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'FECHA ESPECIAL'; 
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          } 
        }
      });
    }

    $scope.btnVerFichaEmpleado = function (){
      $scope.fDataImprimir = {};
      $scope.fDataImprimir = angular.copy($scope.mySelectionGridEM[0]);
      $scope.fDataImprimir.lugar_nacimiento = {
        'departamento' : '',
        'provincia' : '',
        'distrito' : ''
      }
      $scope.fDataImprimir.dirImagesEmpleados = $scope.dirImagesEmpleados;

      var arrParams = {
        titulo: 'FICHA DE DATOS DEL TRABAJADOR',
        datos:{
          resultado:  $scope.fDataImprimir,
          salida: 'pdf',
          tituloAbv: 'FIC-EMPL',
          titulo: 'FICHA DE DATOS DEL TRABAJADOR'
        },
        metodo: 'php'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/ficha_datos_empleado',
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    $scope.getPaginationServerSideEM();
  }])
  .service("horarioEspecialServices",function($http, $q) {
    return({
        sListarHorarioEspecialEmpleado: sListarHorarioEspecialEmpleado,
        sRegistrarHorarioEspecial: sRegistrarHorarioEspecial,
        sEliminarHorarioEspecialDeEmpleado: sEliminarHorarioEspecialDeEmpleado
    });

    function sListarHorarioEspecialEmpleado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HorarioEspecial/lista_horario_especial_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarHorarioEspecial (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HorarioEspecial/registrar_horario_especial", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarHorarioEspecialDeEmpleado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HorarioEspecial/eliminar_horario_especial_de_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });