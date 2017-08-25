angular.module('theme.programacionesInfo', ['theme.core.services'])
  .controller('programacionesInfoController', ['$scope', '$sce', '$filter','$modal', '$controller','$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI', 
    'progMedicoServices',
    'ambienteServices', 
    'categoriaConsulServices',
    'canalServices', 
    'empleadoSaludServices',
    'sedeServices',
    'usuarioServices',
    'feriadoServices',
    'programacionAmbienteServices',
    'especialidadServices',
    'ModalReporteFactory',
    function($scope, $sce, $filter, $modal, $controller, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI, 
      progMedicoServices, 
      ambienteServices, 
      categoriaConsulServices, 
      canalServices,
      empleadoSaludServices,
      sedeServices,
      usuarioServices,
      feriadoServices,
      programacionAmbienteServices,
      especialidadServices,
      ModalReporteFactory
    ){ 
    'use strict'; 
    $scope.vistaPl = "VE";
    $scope.genCupo = {};
    $scope.fBusqueda = {};
    $scope.fBusqueda.desde = {};
    $scope.fBusqueda.especialidad = {};
    $scope.fBusqueda.medico = {};    
    $scope.listaEspecialidadesProgAsistencial = {};
    $scope.listaMedicosProgAsistencial = {};
    // TIPO DE ATENCION
    $scope.listaTipoAtencion = [];
    $scope.listaTipoAtencion[0]={ id : null, descripcion:'--Todos--'};
    $scope.listaTipoAtencion[1]={ id : 'CM', descripcion:'CONSULTA MEDICA'};
    $scope.listaTipoAtencion[2]={ id : 'P', descripcion:'PROCEDIMIENTO'};
    $scope.fBusqueda.tipoAtencion = $scope.listaTipoAtencion[0];
   
    $scope.titleFormGenCupo = 'Consulta de Citas'; 
    $scope.dateUIDesde = {} ;
    $scope.dateUIDesde.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
    $scope.dateUIDesde.datePikerOptions = {
      formatYear: 'yy',
      // startingDay: 1,
      'show-weeks': false
    }; 
    $scope.fBusqueda.desde =  $filter('date')(moment().toDate(),'dd-MM-yyyy'); 
    
    $scope.dateUIDesde.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIDesde.opened = true;
    };
    // LISTA DE CANALES
    canalServices.sListaCanalCbo().then(function (rpta) {
      $scope.listaCanalProgAsistencial = rpta.datos;
    });
    // LISTA DE ESPECIALIDADES
    $scope.listaMedicosProgAsistencial = [];
    // $scope.getDatosModalProgAsistencial = function(){
      especialidadServices.sListarEspecialidadesProgAsistencial().then(function (rpta) {
        $scope.listaEspecialidadesProgAsistencial = rpta.datos;
        $scope.listaEspecialidadesProgAsistencial.splice(0,0,{ id : null, descripcion:'--Todos--'}); 
        $scope.fBusqueda.especialidad = $scope.listaEspecialidadesProgAsistencial[0];    
        $scope.listaMedicosProgAsistencial.splice(0,0,{ id : null, medico:'--Seleccione una especialidad--'}); 
        $scope.fBusqueda.medico = $scope.listaMedicosProgAsistencial[0];  
      });
    // }
    // $scope.getDatosModalProgAsistencial();

    $scope.getMedicos = function(){
      if( $scope.fBusqueda.especialidad.id === null ){
        $scope.listaMedicosProgAsistencial = [];
        $scope.listaMedicosProgAsistencial.splice(0,0,{ id : null, medico:'--Seleccione una especialidad--'}); 
        $scope.fBusqueda.medico = $scope.listaMedicosProgAsistencial[0];
        $scope.getPlanning(false,false,false,'VE');  
      }else{
        $scope.datosGrid = {
          datos : { id : $scope.fBusqueda.especialidad.id } 
        };
        console.log("Grid :",$scope.datosGrid);
        empleadoSaludServices.sListarMedicosEspecialidadInfo($scope.datosGrid).then(function (rpta) {
          $scope.listaMedicosProgAsistencial = rpta.datos;
          $scope.listaMedicosProgAsistencial.splice(0,0,{ id : null, medico:'--Todos--'}); 
          $scope.fBusqueda.medico = $scope.listaMedicosProgAsistencial[0];  
          $scope.getPlanning(false,false,false,'VM');    
        });      
        
      }
    }    

    $scope.getPlanning =  function(valueNext, origen, valuePrev,valueView){ 
      $scope.fBusqueda.vista = valueView ;
      blockUI.start('Ejecutando proceso...');
      if(origen){
        $scope.fBusqueda.origen = origen;
      }else{
        $scope.fBusqueda.origen = false;
      }
      $scope.fBusqueda.next = valueNext;
      $scope.fBusqueda.prev = valuePrev;  
      // console.log("Busqueda :",$scope.fBusqueda);
      progMedicoServices.sPlanningHorasVistaInformes($scope.fBusqueda).then(function (rpta) { 
        $scope.fBusqueda.desde = rpta.fecha_consulta;
        $scope.genCupo.haySiguiente = rpta.haySiguiente;
        $scope.genCupo.hayAnterior = rpta.hayAnterior;
        if(rpta.flag == 0){
          $scope.genCupo.hayPlanning = false;
          $scope.genCupo.alerta = rpta.message;
        }else if(rpta.flag == 1){
          $scope.genCupo.hayPlanning = true;
          $scope.genCupo.planning = rpta.planning;
        } 
        blockUI.stop(); 
      }); 
    }

    $scope.getPlanning(false,false,false,'VE');  
  
    $scope.seleccionarCupo = function (cell){
      console.log("cell :",cell);
      if(!cell.habilitada){
        return false;
      }
      //console.log(cell);
      $modal.open({
        templateUrl: angular.patchURLCI+'Venta/ver_popup_seleccionar_cita', 
        size: 'lg',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.titleFormSelecCupo = 'Listado de Cupos';

          $scope.listaEstadosCupo = [
            {id: 1, descripcion: 'DISPONIBLE' },
            {id: 2, descripcion: 'TODOS' }
          ]; 
          $scope.fBusqueda.estado = $scope.listaEstadosCupo[1];           
          $scope.fBusqueda.canal = $scope.listaCanalProgAsistencial[0];
          $scope.fBusqueda.programacion = cell;
          $scope.genCupo.mySelectionGrid =[];
          $scope.gridOptionsCupos = { 
            paginationPageSizes: [10, 50, 100, 500, 1000, 10000],
            paginationPageSize: 100,
            minRowsToShow: 10,
            useExternalPagination: false,
            useExternalSorting: false,
            useExternalFiltering : false,
            enableGridMenu: true,
            enableRowSelection: true,
            enableFullRowSelection: true,
            enableSelectAll: false,
            enableFiltering: false,                  
            multiSelect: false,
            columnDefs: [ 
              { field: 'iddetalleprogmedico', name: 'iddetalleprogmedico', displayName: 'ID', width:'10%', visible:false, },
              { field: 'numero_cupo', name: 'numero_cupo', displayName: 'Nº CUPO', width:'10%' },
              { field: 'turno', name: 'turno', displayName: 'TURNO', width:'14%' },
              { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width:'32%' },
              { field: 'ticket', name: 'ticket', displayName: 'Nº TICKET', width:'15%' },              
              { field: 'si_adicional', name: 'tipo_cupo', displayName: 'ADICIONAL', width:'12%', /*type: 'object',*/ enableFiltering: false, enableSorting: false, 
                cellTemplate:'<div class="ui-grid-cell-contents text-center"><label ng-if="COL_FIELD" class="label label-warning"><i class="fa fa-check"></i></label></div>' 
              },
              { field: 'estado_cupo', type: 'object', name: 'estado_cupo_str', displayName: 'ESTADO', width:'12%', enableFiltering: false,
                cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>' 
              } 
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApiCupos = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.genCupo.mySelectionGrid = gridApi.selection.getSelectedRows();                      
                //$scope.aceptarGenCupo();
              });
            }
          };

          $scope.getListaCuposCanal = function (){
            progMedicoServices.sListarCuposCanal($scope.fBusqueda).then(function (rpta) {
              $scope.gridOptionsCupos.data = rpta.datos;
              $scope.gridOptionsCupos.totalItems = rpta.paginate.totalRows;                    
            });
          }
          $scope.getListaCuposCanal();

          /*$scope.aceptarGenCupo = function(){
            if( $scope.genCupo.mySelectionGrid.length == 1){
              //console.log($scope.genCupo.mySelectionGrid[0]);
              if($scope.genCupo.mySelectionGrid[0].estado_cupo.bool == 2){
                 if(($scope.modulo == 'progMedico' || $scope.modulo == 'historialCitas')  && $scope.boolExterno){
                  $uibModal.open({
                    templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_confirmar_accion',
                    size: '',
                    backdrop: 'static',
                    keyboard:false,
                    scope: $scope,
                    controller: function ($scope, $modalInstance) {
                      $scope.fDataModal = {};
                      $scope.fDataModal.tipo = 'reprogramar';                            
                      $scope.genCupo.mySelectionGrid[0].fecha_programada = $scope.fBusqueda.programacion.fecha;
                      $scope.genCupo.mySelectionGrid[0].fecha_str = $scope.fBusqueda.programacion.fecha_str;
                      $scope.genCupo.mySelectionGrid[0].ambiente = $scope.fBusqueda.programacion.ambiente.numero_ambiente; 
                      $scope.fDataModal.nuevaCita = $scope.genCupo.mySelectionGrid[0];
                      $scope.fDataModal.oldCita = $scope.genCupo.oldCita;
                      if(paramExterno && $scope.modulo == 'historialCitas'){
                        $scope.titleFormModal = 'MODIFICAR CITA';
                        $scope.fDataModal.mensaje = '¿Realmente desea modificar la cita?';
                        $scope.fDataModal.modifCita = true;
                        $scope.fDataModal.reprogCita = false;
                      }else{
                        $scope.titleFormModal = 'REPROGRAMAR CITA';
                        $scope.fDataModal.mensaje = '¿Realmente desea reprogramar la cita?';
                        $scope.fDataModal.modifCita = false;
                        $scope.fDataModal.reprogCita = true;
                      }
   
                      $scope.btnOk = function(){ 
                        $scope.btnCancel();                 
                        
                        $scope.genCupo.seleccion = angular.copy($scope.fDataModal.nuevaCita);
                        //console.log($scope.genCupo.seleccion);                        
                        $scope.cancelSelCupo();
                        $scope.cancel(); 
                        fnCallback();
                      }  

                      $scope.btnCancel = function(){
                        $modalInstance.dismiss('btnCancel');
                      } 
                    }
                  });
                }else{
                  if($scope.fDataVenta.temporal.especialidad.idespecialidad !== $scope.fBusqueda.especialidad.id){
                    var pTitle = 'Aviso!';
                    var pType = 'warning';
                    var pText = 'Solo puede seleccionar cupo para especialidad seleccionada en venta';
                    pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 });
                    return;
                  }
                  var encontro = false;
                  angular.forEach($scope.gridOptions.data, function(value, key) {
                    //console.log(value);
                    if( value.tiene_cupo && 
                        value.detalleCupo.iddetalleprogmedico == $scope.genCupo.mySelectionGrid[0].iddetalleprogmedico){
                        encontro = true;
                    }
                  });
                  if(!encontro){         
                    $scope.genCupo.mySelectionGrid[0].medico = $scope.fBusqueda.programacion.medico;
                    $scope.genCupo.mySelectionGrid[0].fecha_programada = $scope.fBusqueda.programacion.fecha;
                    $scope.genCupo.mySelectionGrid[0].fecha_str = $scope.fBusqueda.programacion.fecha_str;
                    $scope.genCupo.mySelectionGrid[0].ambiente = $scope.fBusqueda.programacion.ambiente.numero_ambiente;
                    $scope.genCupo.itemVenta.detalleCupo = angular.copy($scope.genCupo.mySelectionGrid[0]);
                    $scope.genCupo.itemVenta.tiene_cupo = true;                          
                    $scope.gridApiCombo.grid.refreshRows();
                    $scope.cancelSelCupo();
                    $scope.cancel();                                                    
                  }else{
                    var pTitle = 'Aviso!';
                    var pType = 'warning';
                    var pText = 'Cupo ha sido seleccionado para otro item';
                    pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 }); 
                  }
                } 
                                    
              }else{
                var pTitle = 'Aviso!';
                var pType = 'warning';
                var pText = 'Debe seleccionar un cupo disponible';
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 }); 
              }
            }
            
          }*/

          $scope.cancelSelCupo = function(){
            $modalInstance.dismiss('cancelSelCupo');
          }
        }
      });
    }
    $scope.verListaPacientesProc = function (cell){
      console.log( cell);
      if(!cell.habilitada){
        return false;
      }
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_pacientes_proc',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'LISTADO DE PACIENTES PARA PROCEDIMIENTO';
          $scope.fDataListaPaciente = {};
          $scope.fDataListaPaciente = cell;
          $scope.fDataListaPaciente.fecha_programada = cell.fecha;
          console.log( $scope.fDataListaPaciente);
          $scope.getListadoPacientes = function (){
            blockUI.start('Cargando información.');
            var arrParams = {
              idprogmedico : $scope.fDataListaPaciente.idprogmedico,
              tipo_atencion : 'P'
            }; 
            progMedicoServices.sListarPacientesProgramadosParaProc($scope.fDataListaPaciente).then(function (rpta) { 
              $scope.fDataListaPaciente.lista = rpta.datos; 
              $scope.gridOptionsPac.data =  $scope.fDataListaPaciente.lista;
              blockUI.stop();
            });
          }
          $scope.getListadoPacientes();
          
          $scope.gridOptionsPac = { 
            enablePagination: false,
            useExternalSorting: false,
            useExternalFiltering : false,
            enableGridMenu: true,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'orden_venta', name: 'orden_venta', displayName: 'ORDEN VENTA', width: 140, enableSorting:false},
              { field: 'ticket_venta', name: 'ticket_venta', displayName: 'TICKET', visible: false, enableSorting:false},
              { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width: 140, enableSorting:false},
              { field: 'fecha_atencion_v', name: 'fecha_atencion_v', displayName: 'FECHA ATENCIÓN', width: 140, enableSorting:false},
              { field: 'paciente', name: 'paciente', displayName: 'PACIENTE', enableSorting:false },
              /*{ field: 'medico', name: 'medico', displayName: 'MEDICO', visible: false, enableSorting:false },*/
              { field: 'estado', displayName: 'Estado', enableCellEdit: false, enableFiltering: false, width: 120,
                cellTemplate:'<button type="button" style="width: 85%;" class="btn btn-sm btn-success mt-sm center-block" ng-if="row.entity.estado.estado == 2"> VENDIDO </button>' +
                  '<button type="button" style="width: 85%;" class="btn btn-sm btn-info  mt-sm center-block" ng-if="row.entity.estado.estado == 1"> ATENDIDO </button>'+
                  '<button type="button" style="width: 85%;" class="btn btn-sm btn-danger mt-sm center-block" ng-if="row.entity.estado.estado == 3"> NOTA DE CRÉDITO </button>'
              }
            ],
          };

          $scope.cancelVerPacientes = function () {
            $modalInstance.dismiss('cancelVerPacientes');
          }  
        }
      });
    }
    $scope.hoverInHoras = function(cell) { 
      if(cell.headerHora){
        angular.forEach($scope.genCupo.planning.horas,function(val,key) { 
          if( val.timestamp >= cell.tmp_hora_inicio && val.timestamp < cell.tmp_hora_fin ){ 
            $scope.genCupo.planning.horas[key].classHoveredHora = ' hovered-hour'; 
            
          }
        });
        angular.forEach($scope.genCupo.planning.ambientes,function(val,key) { 
          if( cell.ambiente.idambiente == val.idambiente ){ 
            $scope.genCupo.planning.ambientes[key].classHoveredAmbiente = ' hovered-ambiente'; 
          } 
        });
      }
    }
    $scope.hoverOutHoras = function(cell) { 
      if(cell.headerHora){
        angular.forEach($scope.genCupo.planning.horas,function(val,key) { 
          if( val.timestamp >= cell.tmp_hora_inicio && val.timestamp < cell.tmp_hora_fin ){ 
            $scope.genCupo.planning.horas[key].classHoveredHora = ' '; 
          }
        });
        angular.forEach($scope.genCupo.planning.ambientes,function(val,key) { 
          if( cell.ambiente.idambiente == val.idambiente ){ 
            $scope.genCupo.planning.ambientes[key].classHoveredAmbiente = ' '; 
          }
        });
      }
    }

    $scope.cancel = function(){
      $modalInstance.dismiss('cancel');
      $scope.genCupo.planning = null;
    } 

  }]);
