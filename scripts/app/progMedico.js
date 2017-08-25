angular.module('theme.progMedico', ['theme.core.services'])
  .controller('progMedicoController', ['$scope', '$sce', '$filter','$modal', '$controller','$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI', 
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
    
    $scope.fArr = {};
    $scope.fConsulta = {}; 
    $scope.fBusqueda = {}; 
    $scope.fPlanning = { 
      'data': {
        'cabecera': {}
      }
    }; 
    $scope.fToggle = { 
      'colorHora' : 'primary',
      'textoHora' : 'VISTA POR HORA',
      'colorDia' : 'default text-gray',
      'textoDia' : 'VISTA POR DIA',
      'porDefecto' : 'VISTA POR DIA',
      'porDefectoAbv' : 'VD'
    };

    $scope.fArr.listaEstado = [
      {id:1, descripcion: 'REGISTRADO'},
      {id:2, descripcion: 'CANCELADO'},
    ];
    $scope.fArr.listaMostrarTodosPl = [ 
      {id:1, descripcion: 'MOSTRAR TODOS LOS AMBIENTES'},
      {id:2, descripcion: 'MOSTRAR SOLO AMBIENTES CON PROGRAMACIÓN'}
    ]; 

    $scope.fBusqueda.itemEstado = $scope.fArr.listaEstado[0];
    $scope.fBusqueda.filtroAmbientes = $scope.fArr.listaMostrarTodosPl[0];
    $scope.listaEstadosRegistro = [
      {id:1, descripcion: 'ACTIVO'},
      {id:2, descripcion: 'INACTIVO'} 
    ];
    // LISTADOS 
    // AMBIENTE 
    $scope.fArr.fnListarAmbientes = function(id) { 
      var id = id || null;
      ambienteServices.sListarAmbientePorSedeSession().then(function (rpta) {
        $scope.fArr.listaAmbiente =  rpta.datos;   
        $scope.fArr.listaAmbiente.splice(0,0,{ id : 'none', descripcion:'-- Seleccione ambiente--'}); 
        if( id ){ 
          var objIndex = $scope.fArr.listaAmbiente.filter(function(obj) {
            return obj.id == id;
          }).shift(); 
          $scope.fConsulta.ambiente = objIndex;
          //$scope.fConsulta.ambiente = $scope.fArr.listaAmbiente[objIndex.id];
        }
        
      });
    }
    // ESPECIALIDAD
    $scope.fArr.fnListarEspecialidades = function(id) { 
      var id = id || null;
      especialidadServices.sListarEspecialidadesCbo().then(function (rpta) {
        $scope.fArr.listaEspecialidades =  rpta.datos;   
        $scope.fArr.listaEspecialidades.splice(0,0,{ id : 'none', descripcion:'-- Seleccione especialidad--'}); 
        if( id ){ 
          var objIndex = $scope.fArr.listaEspecialidades.filter(function(obj) {
            return obj.id == id;
          }).shift(); 
          $scope.fConsulta.especialidad = objIndex;
          //$scope.fConsulta.ambiente = $scope.fArr.listaAmbiente[objIndex.id];
        }
      });
    }
    // PROGRAMACIONES
    $scope.fArr.fnListarProgramaciones = function(ids,esReprogramacion,tipoAtencion,callback,cbSubAmbientes) { 
      
      var arrParams = { 
        ids: ids,
        reprog : esReprogramacion, 
        itemEstado: $scope.fBusqueda.itemEstado,
        tipoAtencion: tipoAtencion,
      }; 
      progMedicoServices.sListarEstasProgramaciones(arrParams).then(function (rpta) { 
        $scope.fArr.listaProgramaciones =  rpta.datos; 
        callback();
        cbSubAmbientes();
      });
    }

    $scope.filtrarConsProc = function(value,tipo){
      
      if(tipo == 'P' && value){
        if($scope.fBusqueda.consultas){
          $scope.fBusqueda.tipoAtencion = 'all'; 
        }else{
          $scope.fBusqueda.tipoAtencion = tipo;
        }       
      }else if(tipo == 'P' && !value){
        if($scope.fBusqueda.consultas){
          $scope.fBusqueda.tipoAtencion = 'CM'; 
        }else{
          $scope.fBusqueda.tipoAtencion = 'CM';
          $scope.fBusqueda.consultas = true;
        }
      }

      if(tipo == 'CM' && value){
        if($scope.fBusqueda.procedimientos){
          $scope.fBusqueda.tipoAtencion = 'all'; 
        }else{
          $scope.fBusqueda.tipoAtencion = tipo;
        }       
      }else if(tipo == 'CM' && !value){
        if($scope.fBusqueda.procedimientos){
          $scope.fBusqueda.tipoAtencion = 'P'; 
        }else{
          $scope.fBusqueda.tipoAtencion = 'P';
          $scope.fBusqueda.procedimientos = true;
        }
      }

      $scope.listarPlaningMedicos();
    }

    var fechaHasta = moment().add(7,'days'); 
    $scope.fBusqueda.desde =  $filter('date')(moment().toDate(),'dd-MM-yyyy'); 
    $scope.fBusqueda.hasta =  $filter('date')(fechaHasta.toDate(),'dd-MM-yyyy'); 

    $scope.getCategoriaConsul = function () { 
       categoriaConsulServices.sListarCategoriaConsulCbo().then(function (rpta) {
        $scope.fArr.listaCategoriaConsul = rpta.datos;
        $scope.fArr.listaCategoriaConsul.splice(0,0,{ id : '0', descripcion:'--VER TODOS --'});
        $scope.fBusqueda.itemAmbiente = $scope.fArr.listaCategoriaConsul[2]; //asistencia por defecto
        $scope.listarPlaningMedicos();
      });
    }
    
    $scope.verPlaningPorHora = function(){ 
      $scope.fToggle.colorHora = 'primary';
      $scope.fToggle.colorDia = 'default text-gray';
      $scope.fToggle.porDefecto = 'VISTA POR HORA';
      $scope.fToggle.porDefectoAbv = 'VH';
      $scope.fBusqueda.tipoPlaning = angular.copy($scope.fToggle.porDefectoAbv);
    }
    $scope.verPlaningPorDia = function(){ 
      $scope.fToggle.colorHora = 'default text-gray';
      $scope.fToggle.colorDia = 'primary';
      $scope.fToggle.porDefecto = 'VISTA POR DIA';
      $scope.fToggle.porDefectoAbv = 'VD';
      $scope.fBusqueda.tipoPlaning = angular.copy($scope.fToggle.porDefectoAbv); 
    }
    
    $scope.cambiarFechas = function() { 
      var activeDate = moment($scope.fBusqueda.activeDate).format('DD-MM-YYYY'); 
      var fechaHasta = moment($scope.fBusqueda.activeDate).add(7,'days'); 
      fechaHasta = $filter('date')(fechaHasta.toDate(),'dd-MM-yyyy'); 
      $scope.fBusqueda.desde = activeDate;
      $scope.fBusqueda.hasta = fechaHasta;
      $scope.listarPlaningMedicos(); 
    }

    $scope.verProgramacion = function(fila, filaDet, section, esReprogramacion, fnCallback) {
      
      var url;
      if(section.tipoAtencion == 'P'){
        url= angular.patchURLCI+'ProgMedico/ver_popup_programacion_proc';
      }else{
        url= angular.patchURLCI+'ProgMedico/ver_popup_programacion_cons';
      }
      
      $modal.open({ 
        templateUrl: url,
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'PROGRAMACIÓN DE MÉDICOS'; 
          // toggles
          $scope.isNavCollapsed = true;
          $scope.isCollapsed = false;

          $scope.fArr.fnListarAmbientes(fila.idambiente);
          $scope.fArr.fnListarEspecialidades(section.idespecialidad);
          $scope.fConsulta.fecha = filaDet.fecha;

          if(esReprogramacion){
            $scope.esReprogramacion = true;
          }else{
            $scope.esReprogramacion = false;
            $scope.boolExterno = true;
            $controller('controlEventoController', { 
              $scope : $scope
            });
          }
                    
          // CARGAR PROGRAMACIONES 
          var fnCallbackProg = function() { 
            //console.log($scope.fArr.listaProgramaciones,'$scope.fArr.listaProgramaciones'); fData 
            angular.forEach($scope.fArr.listaProgramaciones, function(val, key) { 
              var objIndex = $scope.fArr.listaProgramaciones[key]['ambientes'].filter(function(obj) { 
                return obj.id == $scope.fArr.listaProgramaciones[key]['ambiente']['id'];
              }).shift(); 
              //console.log(objIndex);
              $scope.fArr.listaProgramaciones[key]['ambiente'] = objIndex; 
            });
          }; 
          // $scope.getCargaSubCategoriaConsul();   
          $scope.getCargaSubCategoriaConsul = function () {                     
            angular.forEach($scope.fArr.listaProgramaciones, function(val, key) { 
              if($scope.fArr.listaProgramaciones[key]['subcategoriarenom']['id'] !=  null){
                angular.forEach($scope.fArr.listaProgramaciones[key]['subcategorias'], function(sub, ind) { 
                  if($scope.fArr.listaProgramaciones[key]['subcategorias'][ind]['id'] == $scope.fArr.listaProgramaciones[key]['subcategoriarenom']['id']){
                    $scope.fArr.listaProgramaciones[key]['subcategoriarenom'] = $scope.fArr.listaProgramaciones[key]['subcategorias'][ind]; 
                    return;
                  }
                });                
              }else{
                $scope.fArr.listaProgramaciones[key]['subcategoriarenom'] = $scope.fArr.listaProgramaciones[key]['subcategorias'][1]; 
              }
            });                                         
          }
          $scope.fArr.fnListarProgramaciones(section.idprogramaciones,$scope.esReprogramacion,section.tipoAtencion,fnCallbackProg,$scope.getCargaSubCategoriaConsul); 
          console.log("farr ",$scope.fArr);          
          // $scope.contCuposDeCanal = false; fArr.listaProgramaciones
          // $scope.textContCupos = 'VER CUPOS'; 
          $scope.switchRenombrado = function(index) { 
            if( $scope.fArr.listaProgramaciones[index]['ambiente'].idcategoriaconsul == 2 ){ // ASISTENCIAL
              $scope.fArr.listaProgramaciones[index]['si_renombrado_scc'] = false;
            }else{ // ADMINISTRATIVO 
              $scope.fArr.listaProgramaciones[index]['si_renombrado_scc'] = true;
            } 
          }
          $scope.calcularTurno = function(index,campoEdit) { 

            var horaInicio = $scope.fArr.listaProgramaciones[index].hora_inicio_edit;
            var minutoInicio = $scope.fArr.listaProgramaciones[index].minuto_inicio_edit;

            var horaFin = $scope.fArr.listaProgramaciones[index].hora_fin_edit;
            var minutoFin = $scope.fArr.listaProgramaciones[index].minuto_fin_edit;

            var horaMasMinutoInicio = horaInicio+':'+minutoInicio+':00';
            var horaMasMinutoFin = horaFin+':'+minutoFin+':00';
            // hallar diferencia en minutos 
            var mHoraInicio = moment(horaMasMinutoInicio,'HH:mm:ss');
            var mHoraFin = moment(horaMasMinutoFin,'HH:mm:ss');
            var diferenciaMinutos = mHoraFin.diff(mHoraInicio,'minutes'); 

            //  
            if(campoEdit === 'hora_inicio' || campoEdit === 'hora_fin' ){ 
              // cambia cant. de cupos 
              $scope.fArr.listaProgramaciones[index].total_cupos_master = (diferenciaMinutos) / ($scope.fArr.listaProgramaciones[index].intervalo_hora_int); 
            }
            if( campoEdit === 'intervalo' ){ 
              // cambia de cupos por hora 
              $scope.fArr.listaProgramaciones[index].cupos_por_hora = (60 / $scope.fArr.listaProgramaciones[index].intervalo_hora_int);
              // cambia cant. de cupos 
              $scope.fArr.listaProgramaciones[index].total_cupos_master = (diferenciaMinutos) / ($scope.fArr.listaProgramaciones[index].intervalo_hora_int); 
            }
            if( campoEdit === 'cupos_por_hora' ){
              // cambia el intervalo 
              $scope.fArr.listaProgramaciones[index].intervalo_hora_int =   60 / $scope.fArr.listaProgramaciones[index].cupos_por_hora; 
              // cambia cant. de cupos 
              $scope.fArr.listaProgramaciones[index].total_cupos_master = (diferenciaMinutos) / ($scope.fArr.listaProgramaciones[index].intervalo_hora_int); 
            } 
            if( campoEdit === 'cant_cupos' ){ 
              // cambio de cupos por hora 
              $scope.fArr.listaProgramaciones[index].cupos_por_hora = ($scope.fArr.listaProgramaciones[index].total_cupos_master) / (diferenciaMinutos / 60);
              // cambia el intervalo 
              $scope.fArr.listaProgramaciones[index].intervalo_hora_int = 60 / $scope.fArr.listaProgramaciones[index].cupos_por_hora ; 
            }
          } 
          $scope.editarProgramacion = function(index, gestionar, fila) { 
            var gestionar = gestionar || false;
            blockUI.start('Cargando planing...'); 

            if(!esReprogramacion){
              var arrData = angular.copy($scope.fArr.listaProgramaciones);
              angular.forEach(arrData,function(val,key) { 
                if( !(key === index) ){
                  delete(arrData[key]);
                }
              }); 
              var arrParams = { 
                'datos': arrData,
                'gestionar': gestionar,
                'tipoAtencion': section.tipoAtencion
              };
   
              progMedicoServices.sEditar(arrParams).then(function (rpta) {
                
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success'; 

                  var fnCallbackProg = function() { 
                    //console.log($scope.fArr.listaProgramaciones,'$scope.fArr.listaProgramaciones'); fData 
                    angular.forEach($scope.fArr.listaProgramaciones, function(val, key) { 
                      var objIndex = $scope.fArr.listaProgramaciones[key]['ambientes'].filter(function(obj) { 
                        return obj.id == $scope.fArr.listaProgramaciones[key]['ambiente']['id'];
                      }).shift(); 
                      //console.log(objIndex);
                      $scope.fArr.listaProgramaciones[key]['ambiente'] = objIndex; 
                    });
                  }; 
                  // $scope.getCargaSubCategoriaConsul();   
                  $scope.getCargaSubCategoriaConsul = function () {                     
                    angular.forEach($scope.fArr.listaProgramaciones, function(val, key) { 
                      if($scope.fArr.listaProgramaciones[key]['subcategoriarenom']['id'] !=  null){
                        angular.forEach($scope.fArr.listaProgramaciones[key]['subcategorias'], function(sub, ind) { 
                          if($scope.fArr.listaProgramaciones[key]['subcategorias'][ind]['id'] == $scope.fArr.listaProgramaciones[key]['subcategoriarenom']['id']){
                            $scope.fArr.listaProgramaciones[key]['subcategoriarenom'] = $scope.fArr.listaProgramaciones[key]['subcategorias'][ind]; 
                            return;
                          }
                        });                
                      }else{
                        $scope.fArr.listaProgramaciones[key]['subcategoriarenom'] = $scope.fArr.listaProgramaciones[key]['subcategorias'][1]; 
                      }
                    });                                         
                  }

                  $scope.fArr.fnListarProgramaciones(section.idprogramaciones,null,section.tipoAtencion,fnCallbackProg,$scope.getCargaSubCategoriaConsul); 

                  if(gestionar){
                    console.log('fila',fila);
                    $scope.verGestionCupos(fila);
                  }else{
                    if(rpta.notificaciones.length > 0){
                      //console.log(rpta.notificaciones);
                      $scope.modulo = 'progMedico';
                      $scope.btnNuevo('key_prog_med', -1, null, rpta.notificaciones);
                    }
                    
                  }

                }else if(rpta.flag == 0){ 
                  var pTitle = 'Error!';
                  var pType = 'error';
                }else{ 
                  alert('Error inesperado');
                } 
                var pText = rpta.message;
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 }); 
                blockUI.stop();
                $scope.listarPlaningMedicos(); 
              });
            }else{
              var arrData = angular.copy($scope.fArr.listaProgramaciones[index]);
              //console.log(arrData);
              progMedicoServices.sRegistrarReprogramacion(arrData).then(function (rpta) {
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success'; 
                  $scope.cancel();
                  fnCallback();
                  if(rpta.flagMail == 1){
                    pinesNotifications.notify({ title: pTitle, text: rpta.messageMail, type: pType, delay:3000 });
                  }else{
                    pinesNotifications.notify({ title: 'Aviso!', text: rpta.messageMail, type: 'warning', delay:3000 });
                  }
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'error';
                }else if(rpta.flag == 2){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                }else{
                  alert('Error inesperado');
                }                 
                var pText = rpta.message;
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 });  
                $scope.listarPlaningMedicos(); 
                blockUI.stop();
              });
            }
          }

          $scope.verCuposDeCanal = function(fcanal,indexCanal,indexProg) {
            if( $scope.fArr.listaProgramaciones[indexProg].canales[indexCanal].contCuposDeCanal === false ){
              $scope.fArr.listaProgramaciones[indexProg].canales[indexCanal].textContCupos = 'OCULTAR CUPOS';
              $scope.fArr.listaProgramaciones[indexProg].canales[indexCanal].contCuposDeCanal = true;
            }else{ 
              $scope.fArr.listaProgramaciones[indexProg].canales[indexCanal].textContCupos = 'VER CUPOS';
              $scope.fArr.listaProgramaciones[indexProg].canales[indexCanal].contCuposDeCanal = false;
            }
          }
          // $scope. 
          $scope.cancel = function () { 
            $modalInstance.dismiss('cancel'); 
            $scope.isCollapsed = false;
            
          }

          $scope.getSelectedMedicoReprogramacion = function ($item, $model, $label, index) {
            //console.log($item);
            if($item.tiene_prog_cita == 1 && $scope.fData.tipoAtencion == 'CM' || $item.tiene_prog_proc == 1 && $scope.fData.tipoAtencion == 'P'){
              $scope.fArr.listaProgramaciones[index].medico = $item.medico;
              $scope.fArr.listaProgramaciones[index].idmedico = $item.idmedico;           
              $scope.fArr.listaProgramaciones[index].empresa = $item.empresa;           
              $scope.fArr.listaProgramaciones[index].idempresa = $item.idempresa;           
              $scope.fArr.listaProgramaciones[index].idempresamedico = $item.idempresamedico;  
            }else{
              $scope.fArr.listaProgramaciones[index].medico = null;
              $scope.fArr.listaProgramaciones[index].idmedico = null;
              var pTitle = 'Aviso!';
              var pType = 'warning';         
              var pText = 'Debe HABILITAR programación asistencial para la especialidad: ' + $scope.fArr.listaProgramaciones[index].especialidad + ' en la Sede: ' + $scope.sede.descripcion ;
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
            }                    
          };

          $scope.anularProgramacion = function (fila, size){
            $modal.open({ 
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_anular_programacion',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                $scope.titleForm = 'ANULAR PROGRAMACIÓN DE MÉDICO'; 
                $scope.fDataAnular = {};
                $scope.fDataAnular = fila;
                $scope.cancelAnular = function () {
                  $modalInstance.dismiss('cancelAnular');
                } 
                $scope.guardarAnular = function () {                  
                  progMedicoServices.sAnularProc($scope.fDataAnular).then(function (rpta) {
                    //console.log('Rpta: ',rpta);
                    if(rpta.flag == 1){
                      var pTitle = 'Ok!';
                      var pType = 'success';
                      $modalInstance.dismiss('guardarAnular');                
                      var fnCallBack = function(){
                        $scope.cancel(); 
                        $scope.listarPlaningMedicos();
                      }  
                      $scope.modulo='progMedico';
                      $scope.btnNuevo('key_prog_med', 2, fnCallBack, rpta.notificaciones);               
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning'; 
                      $modalInstance.dismiss('guardarAnular');                       
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                  });           
                }
              }
            });
          }

          $scope.cancelarProgramacion = function(fila, size){
            var datos = {
              idprogmedico : fila.idprogmedico,
            };
            progMedicoServices.sVerificarCuposProgramacion(datos).then(function (rpta) { 
               if(rpta.flag == 1){
                $modal.open({ 
                  templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_cancelar_programacion',
                  size: size || '',
                  backdrop: 'static',
                  keyboard:false,
                  scope: $scope,
                  controller: function ($scope, $modalInstance) {
                    $scope.titleForm = 'CANCELAR PROGRAMACIÓN DE MÉDICO';
                    $scope.fDataCancelar = {};
                    $scope.fDataCancelar = fila;
                    $scope.cancelar = function () {
                      $modalInstance.dismiss('cancelar');
                    } 
                    $scope.guardarCancelar = function () {                  
                      //console.log($scope.fDataCancelar);             
                      progMedicoServices.sCancelar($scope.fDataCancelar).then(function (rpta) {
                        //console.log('Rpta: ',rpta);
                        if(rpta.flag == 1){
                          var pTitle = 'Ok!';
                          var pType = 'success';
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                          if(rpta.flagMail == 1){
                            pinesNotifications.notify({ title: pTitle, text: rpta.messageMail, type: pType, delay:3000 });
                          }else{
                            pinesNotifications.notify({ title: 'Aviso!', text: rpta.messageMail, type: 'warning', delay:3000 });
                          }
                          $modalInstance.dismiss('guardarCancelar');  

                          var fnCallBack = function(){
                            $scope.cancel(); 
                            $scope.listarPlaningMedicos();
                          }             
                          
                          $scope.modulo='progMedico';
                          $scope.btnNuevo('key_prog_med', 3, fnCallBack, rpta.notificaciones);             
                        }else if(rpta.flag == 0){
                          var pTitle = 'Advertencia!';
                          var pType = 'warning'; 
                          $modalInstance.dismiss('guardarCancelar'); 
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });                      
                        }else{
                          alert('Error inesperado');
                        }                        
                      });           
                    }
                  }
                });
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });                       
              }else{
                alert('Error inesperado');
              } 
            });            
          }

          $scope.gestionarCupos = function(index, fila){
            $scope.editarProgramacion(index, true, fila);
          }

          $scope.verGestionCupos = function(fila){
            $scope.fDataGestion = fila;
            console.log($scope.fDataGestion);
            $modal.open({
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_gestion_cupos',
              size: 'lg',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                $scope.titleForm = 'GESTIÓN DE CUPOS';
              
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
                  minRowsToShow: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  useExternalFiltering : true,
                  enableGridMenu: true,
                  enableRowSelection: true,
                  enableSelectAll: true,
                  enableFiltering: false,
                  enableFullRowSelection: true,
                  showColumnFooter: true,
                  minRowsToShow:6,
                 //rowHeight: 100,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'idcanal', name: 'idcanal', displayName: 'ID', sort: { direction: uiGridConstants.DESC}, visible:false, },
                    { field: 'idprogmedico', name: 'idprogmedico', displayName: 'ID', visible:false, },
                    { field: 'idcanalprogmedico', name: 'idcanalprogmedico', displayName: 'ID', visible:false, },
                    { field: 'descripcion', name: 'descripcion', displayName: 'CANAL', visible:true, enableCellEdit:false, },
                    { field: 'total_cupos', name: 'total_cupos', displayName: 'TOTAL CUPOS', visible:true, enableCellEdit: true,cellClass:'ui-editCell', aggregationType: uiGridConstants.aggregationTypes.sum},
                    { field: 'cupos_disponibles', name: 'cupos_disponibles', displayName: 'DISPONIBLES', visible:true, enableCellEdit:false,  aggregationType: uiGridConstants.aggregationTypes.sum},
                    { field: 'cupos_ocupados', name: 'cupos_ocupados', displayName: 'OCUPADOS', visible:true, enableCellEdit:false,  aggregationType: uiGridConstants.aggregationTypes.sum},
                    { field: 'total_cupos_adicionales', name: 'total_cupos_adicionales', displayName: 'CUPOS ADICIONALES', enableCellEdit: true,cellClass:'ui-editCell', aggregationType: uiGridConstants.aggregationTypes.sum},
                    { field: 'cupos_adicionales_disponibles', name: 'cupos_adicionales_disponibles', displayName: 'ADIC. DISP', visible:true, enableCellEdit:false,  aggregationType: uiGridConstants.aggregationTypes.sum},
                    { field: 'cupos_adicionales_ocupados', name: 'cupos_adicionales_ocupados', displayName: 'ADIC. OCUPADOS', visible:true, enableCellEdit:false,  aggregationType: uiGridConstants.aggregationTypes.sum},
                  ],  
                  onRegisterApi: function(gridApi) {
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
                    });
                    gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                      $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
                    });

                    gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){  
                      //console.log(colDef); 
                      var pTitle = 'Advertencia!';
                      var pType = 'warning'; 
                      
                      if(colDef.name=='total_cupos'){
                        if(newValue!= null && !isNaN(newValue) && newValue >= 0){
                          rowEntity.total_cupos = parseInt(newValue);  
                          var totalCuposAsignados = 0;
                          angular.forEach($scope.gridOptions.data, function(value, key) {
                            if(value.idcanalprogmedico != rowEntity.idcanalprogmedico){
                              totalCuposAsignados = parseInt(totalCuposAsignados) + parseInt(value.total_cupos);
                            }
                          });

                          //console.log(totalCuposAsignados);
                          if(rowEntity.total_cupos <= ($scope.fDataGestion.total_cupos_master ) ){                          
                            if(totalCuposAsignados + rowEntity.total_cupos  <= $scope.fDataGestion.total_cupos_master){
                              if(rowEntity.total_cupos >= rowEntity.cupos_ocupados){
                                rowEntity.cupos_disponibles = parseInt(rowEntity.total_cupos) - parseInt(rowEntity.cupos_ocupados);
                                $scope.$apply();
                              }else{
                                rowEntity.total_cupos = parseInt(oldValue); 
                                pinesNotifications.notify({ title: pTitle, text: 'El número de cupos debe ser igual o mayor al total de cupos ocupados en el canal.', type: pType, delay: 3000 });
                              }                        
                            }else{                          
                              rowEntity.total_cupos = parseInt(oldValue); 
                              pinesNotifications.notify({ title: pTitle, text: 'La suma de cupos por canal debe der igual al Total de cupos.', type: pType, delay: 3000 });
                            }
                          }else{
                            rowEntity.total_cupos = parseInt(oldValue); 
                            pinesNotifications.notify({ title: pTitle, text: 'El número de cupo asignado debe ser menor al total de cupos.', type: pType, delay: 3000 });
                          }
                        }else{
                          rowEntity.total_cupos = parseInt(oldValue); 
                        }
                      }          
                     
                      if(colDef.name== 'total_cupos_adicionales'){
                        if(newValue!= null && !isNaN(newValue) && newValue >= 0){
                          rowEntity.total_cupos_adicionales = parseInt(newValue);  
                          var totalCuposAsignados = 0;
                          angular.forEach($scope.gridOptions.data, function(value, key) {
                            if(value.idcanalprogmedico != rowEntity.idcanalprogmedico){
                              totalCuposAsignados = parseInt(totalCuposAsignados) + parseInt(value.total_cupos_adicionales);
                            }
                          });

                          //console.log(totalCuposAsignados);
                          if(rowEntity.total_cupos_adicionales <= ($scope.fDataGestion.cupos_adicionales ) ){                          
                            if(totalCuposAsignados + rowEntity.total_cupos_adicionales  <= $scope.fDataGestion.cupos_adicionales){
                              if(rowEntity.total_cupos_adicionales >= rowEntity.cupos_adicionales_ocupados){
                                rowEntity.cupos_adicionales_disponibles = parseInt(rowEntity.total_cupos_adicionales) - parseInt(rowEntity.cupos_adicionales_ocupados);
                                $scope.$apply();
                              }else{
                                rowEntity.total_cupos_adicionales = parseInt(oldValue); 
                                pinesNotifications.notify({ title: pTitle, text: 'El número de cupos debe ser igual o mayor al total de cupos ocupados en el canal.', type: pType, delay: 3000 });
                              }                        
                            }else{                          
                              rowEntity.total_cupos_adicionales = parseInt(oldValue); 
                              pinesNotifications.notify({ title: pTitle, text: 'La suma de cupos por canal debe der igual al Total de cupos.', type: pType, delay: 3000 });
                            }
                          }else{
                            rowEntity.total_cupos_adicionales = parseInt(oldValue); 
                            pinesNotifications.notify({ title: pTitle, text: 'El número de cupo asignado debe ser menor al total de cupos.', type: pType, delay: 3000 });
                          }
                        }else{
                          rowEntity.total_cupos_adicionales = parseInt(oldValue); 
                        }
                      }                      
                    });
                  }
                };
                
                $scope.getPaginationGestionCuposServerSide = function (){                  
                  progMedicoServices.sCargarCuposPorCanales($scope.fDataGestion).then(function (rpta) { 
                    //console.log(rpta);
                    $scope.gridOptions.data = rpta.datos;
                    $scope.gridOptions.totalItems = rpta.paginate.totalRows;
                    $scope.fDataGestion.total_cupos_ocupados = rpta.total_cupos_ocupados;
                    $scope.fDataGestion.total_cupos_disponibles = rpta.total_cupos_disponibles;
                    $scope.fDataGestion.total_cupos_adi_ocupados = rpta.total_cupos_adi_ocupados;
                    $scope.fDataGestion.total_cupos_adi_disponibles = rpta.total_cupos_adi_disponibles;
                  });                            
                }
                $scope.getPaginationGestionCuposServerSide();

                $scope.guardarGestion = function(){
                  //console.log($scope.gridOptions.data);
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';      
                  var totalCuposAsignados = 0;
                  var totalCuposAsignadosAdi = 0;
                  angular.forEach($scope.gridOptions.data, function(value, key) {
                    totalCuposAsignados = parseInt(totalCuposAsignados) + parseInt(value.total_cupos);                    
                    totalCuposAsignadosAdi = parseInt(totalCuposAsignadosAdi) + parseInt(value.total_cupos_adicionales);                    
                  });
                  if(totalCuposAsignados != $scope.fDataGestion.total_cupos_master ||  totalCuposAsignadosAdi != $scope.fDataGestion.cupos_adicionales){
                    pinesNotifications.notify({ title: pTitle, text: 'La suma de cupos por canal debe der igual al Total de cupos.', type: pType, delay: 3000 });
                  }else{
                    progMedicoServices.sGuardarGestionCupos($scope.gridOptions.data).then(function (rpta) { 
                      //console.log(rpta);
                      if(rpta.flag==1){
                        var fila = {
                          idambiente : $scope.fDataGestion.idambiente,
                        };
                        var filaDet = {
                          fecha : $scope.fDataGestion.fecha_programada,
                        };
                        var section ={
                          idespecialidad : $scope.fDataGestion.idespecialidad,
                          idprogramaciones : $scope.fDataGestion.idprogmedico,
                          tipoAtencion: $scope.fDataGestion.tipo_atencion
                        };
                        console.log('section.tipoAtencion',$scope.fDataGestion.tipoAtencion);
                        $scope.cancelGestion();
                        $scope.cancel();
                        $scope.verProgramacion(fila, filaDet, section);
                        var pTitle = 'Ok!';
                        var pType = 'success';
                        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                      }else if(rpta.flag == 0){
                        var pTitle = 'Advertencia!';
                        var pType = 'warning';
                        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3500 });                       
                      }else{
                        alert('Error inesperado');
                      } 
                      
                    });  
                  }
                }

                $scope.cancelGestion = function () {
                  $modalInstance.dismiss('cancelGestion');
                }
              }
            }); 
          }

          $scope.verListaPacientes = function(fila){ 
            var fnCallbackVerProg = function () {
              $scope.cancel();
              $scope.verProgramacion(fila, filaDet, section, false, null);
              $scope.verListaPacientes(fila);
              return true;
            }
            $scope.btnVerListaPacientesConsulta("editar",fila, fnCallbackVerProg);
          }

          $scope.verListaPacientesProc = function(fila){ 
            var fnCallbackVerProg = function () {
              $scope.cancel();
              $scope.verProgramacion(fila, filaDet, section, false, null);
              $scope.verListaPacientesProc(fila);
              return true;
            }
            $scope.btnVerListaPacientesProc(fila, fnCallbackVerProg);
          }
        }
      });
    }


    $scope.verProgramacionHora = function (item){
      var fila = {
        idambiente : item.idambiente,
      };
      var filaDet = {
        fecha : item.fecha,
      };
      var section ={
        idespecialidad : item.idespecialidad,
        idprogramaciones : item.idprogmedico,
      };

      $scope.verProgramacion(fila, filaDet, section, false, null);       
    } 
      
    $scope.listarPlaningMedicos = function() { 
      blockUI.start('Cargando planing...');
      if($scope.fBusqueda.tipoPlaning === 'VD'){
        progMedicoServices.sListarPlaningMedicos($scope.fBusqueda).then(function (rpta) {
          $scope.fPlanning.data = rpta.datos;
          blockUI.stop();
        });
      }

      if($scope.fBusqueda.tipoPlaning === 'VH'){        
        progMedicoServices.sListarPlaningHorasMedicos($scope.fBusqueda).then(function (rpta) {
          $scope.planning = rpta.planning;
          blockUI.stop();
        });
      }
    }
    
    $scope.getCambioShow = function (value){
      $scope.confirmacion = value;
    }
    $scope.fDataUsuario = {};
    $scope.fDataUsuario.clave = null;
    $scope.loadModalConfirmacion = function(size){
      $modal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_confirmacion',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Confirmar contraseña';
          $scope.modalCancel = function () {
            $modalInstance.dismiss('modalCancel');
            $scope.goToUrl('/');
          } 
          $scope.modalAceptar = function () {
            usuarioServices.sConfirmarPassword($scope.fDataUsuario).then(function (rpta) {
              //console.log('Rpta: ',rpta);
              if(rpta.flag == 1){
                var pTitle = 'Ok!';
                var pType = 'success';
                $modalInstance.dismiss('modalAceptar');
                $scope.getCambioShow(true);
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                $scope.fDataUsuario.clave = null;                        
              }else{
                alert('Error inesperado');
              }

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });            
          }
        }
      });  
    }   

    $scope.getFeriados = function(){
      var fecha = new Date();
      $scope.anyo = fecha.getFullYear();
      $scope.datosGrid = {
        anyo : $scope.anyo 
      };
      feriadoServices.sListarferiadosCbo($scope.datosGrid).then(function (rpta) {
        $scope.listaFeriados = rpta.datos;
        //console.log($scope.listaFeriados);
      });
    }
    
    // $scope.verificarCrearNuevo = function (planingCell, ambienteCell) { 
    //   if(!planingCell.es_feriado){
    //     $scope.btnNuevo(planingCell,'dia', ambienteCell);        
    //   }
    // }    

    $scope.verificarCrearNuevoHoras= function (planingCell) { 
      if(!planingCell.hora.es_feriado){
        $scope.btnNuevo(planingCell,'hora');        
      }
    }

    $scope.getSede = function() {       
      sedeServices.sConsultarDatosSede().then(function (rpta) {
        $scope.sede =  rpta.datos;
      });
    }
    
    $scope.getMedicoBusquedaAutocomplete = function (value) {
      var params = {
        search: value,
        sensor: false,
        idsede: $scope.sede.idsede,
      }
      return empleadoSaludServices.sListarMedicosFiltroAutocomplete(params).then(function(rpta) { 
        $scope.noResultsLM = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLM = true;
        }
        return rpta.datos; 
      });
    }

    $scope.getSelectedMedicoBusqueda = function ($item, $model, $label) {
      $scope.fBusqueda.itemMedico = $item;
      console.log($scope.fBusqueda.itemMedico);
      $scope.listarPlaningMedicos();
    }

    $scope.getEspecialidadBusquedaAutocomplete = function (value) {
      var params = {
        search: value,
        sensor: false,
      }
      return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
        $scope.noResultsLM = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLM = true;
        }
        return rpta.datos; 
      });
    }

    
    $scope.getSelectedEspecialidadBusqueda = function ($item, $model, $label) {
      $scope.fBusqueda.itemEspecialidad = $item;
      console.log($scope.fBusqueda.itemEspecialidad);
      $scope.listarPlaningMedicos();
    }

    $scope.getMedicoAutocomplete = function (value, esReprogramacion, idespecialidad) {
      var params = {
        search: value,
        sensor: false,
        idsede: $scope.sede.idsede,
        esReprogramacion: esReprogramacion,
        idespecialidad: idespecialidad
      }
      return empleadoSaludServices.sListarMedicosEspecialidadAutocomplete(params).then(function(rpta) { 
        $scope.noResultsLM = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLM = true;
        }
        return rpta.datos; 
      });
    }
    
    $scope.loadModalSelectTipoAtencion  = function(planingCell, origen, ambienteCell){ 
      if(planingCell.es_feriado){ 
        return false; 
      }
      $modal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_select_tipo_atencion',
        size: 'md',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleFormModal = 'Seleccionar Tipo de Atención'; 
          $scope.tipoAtencion = 'CM'; 
          $scope.modalCancel = function () { 
            $modalInstance.dismiss('modalCancel');
          } 
          $scope.modalAceptar = function () { 
            $modalInstance.dismiss('modalCancel');
            var tipoAtencion = $scope.tipoAtencion || null;
            $scope.btnNuevo(planingCell, origen, ambienteCell,tipoAtencion); 
          }
        }
      });  
    }  
    // Abrimos el formulario para nueva programacion 
    $scope.btnNuevo = function (planingCell, origen, ambienteCell, tipoAtencion) { 
      // console.log(tipoAtencion,'tipoAtencion'); 
      var planingCell = planingCell || null;
      var tipoAtencion = tipoAtencion || 'CM';
      var ambienteCell = ambienteCell || null;
      blockUI.start('Abriendo formulario...');
      $modal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};         
          $scope.titleForm = 'Registro de Programación de Médico';
          $scope.fData.arrFechas = [];
          $scope.fData.tipoAtencion = tipoAtencion; 
          $scope.fData.activeDate = null;
          $scope.fData.arrHoras = null;
          $scope.fData.listaBloquesHoras=null; 
          $scope.fData.renombrar = false; 
          $scope.fData.comentario = ''; 
          $scope.fData.idmedico = null; 
          $scope.fData.alertaAmbientesMsj = [];
          $scope.fData.activoRegistro = $scope.listaEstadosRegistro[1];

          $scope.boolExterno = true;
          $controller('controlEventoController', { 
            $scope : $scope
          });

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }

          $scope.getAmbienteHoras = function(cbBindeoDeDatos) { 
            $scope.fData.intervalo = $scope.sede.intervalo; 
            ambienteServices.sListarAmbientePorSede($scope.sede.idsede).then(function (rpta) {
              $scope.fData.listaAmbienteSede =  rpta.datos; 
              $scope.fData.listaAmbienteSede.splice(0,0,{ id : 0, idsubcategoriaconsul:0, descripcion_amb:'-- SELECCIONE AMBIENTE--'});  
              $scope.fData.ambiente = $scope.fData.listaAmbienteSede[0]; 
              cbBindeoDeDatos(planingCell,ambienteCell); 
            });
            //horas console 
            progMedicoServices.sCargaHoras($scope.sede).then(function (rpta) {
              $scope.fData.listaHoras = rpta.datos; 
            });
          }
          
          $scope.disabled = function(date, mode) { 
            var holidays = $scope.listaFeriados;
            var isHoliday = false;
            var fecha = new Date(date).toLocaleDateString('zh-Hans-CN', { 
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                });            
            for(var i = 0; i < holidays.length ; i++) {              
              var feriado = (holidays[i].split('-0').join('/')).split('-').join('/');
              //console.log(feriado);
              if(feriado === fecha){
                isHoliday = true;
              }
            }
            return (mode === 'day' && (date.getDay() === 0 || isHoliday || moment(fecha).isBefore( moment().toDate().toLocaleDateString('zh-Hans-CN', { 
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                }) )  ));
          };

          $scope.verCuposDeCanal = function() {
            
          }
          $scope.getCargaCategoriaConsul = function() {
            categoriaConsulServices.sListarCategoriaConsulCbo().then(function (rpta) {
              $scope.fData.listaCategoriaConsul = rpta.datos;
              $scope.fData.listaCategoriaConsul.splice(0,1);
              $scope.fData.categoriaConsul = $scope.fData.listaCategoriaConsul[0];   //ASISTENCIAL POR DEFECTO
              $scope.getCargaSubCategoriaConsul($scope.fData.categoriaConsul.id);            
            });
          }
          $scope.getCargaCategoriaConsul();

          $scope.getCargaSubCategoriaConsul = function (item) {
            $scope.itemConsulta = { 'idCategoria' : item};
             categoriaConsulServices.sListarSubCategoriaConsulCbo($scope.itemConsulta).then(function (rpta) {
              $scope.fData.listaSubCategoriaConsul = rpta.datos;
              $scope.fData.listaSubCategoriaConsul.splice(0,0,{ id : '0', descripcion:'-- Seleccione la subcategoría --'});
              $scope.fData.subCategoriaConsul = $scope.fData.listaSubCategoriaConsul[2];  //CONSULTORIO POR DEFECTO 
            });
          }
          
          $scope.getListaCanales = function (item) {
            var paginationCanalOptions = { 
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };

            $scope.datosGrid = {
              paginate : paginationCanalOptions,
              totalCupos : 0
            };
            canalServices.sListarCanal($scope.datosGrid).then(function (rpta) {
              $scope.fData.listaCanales = rpta.datos;
            });
          }  
          $scope.getListaCanales();  

          $scope.gridOptions = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            minRowsToShow:5,
            useExternalPagination: false,
            useExternalSorting: false,
            enableGridMenu: false,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableSorting: false,
            enableFullRowSelection: false,
            enableColumnMenus: false,
            multiSelect: false,
            onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                rowEntity.column = colDef.field;                
                console.log(rowEntity);
                if(colDef.field == 'cupos_adicionales'){ 
                  if(newValue!= null && !isNaN(newValue) && newValue >= 0){
                    rowEntity.cupos_adicionales = parseInt(newValue);  
                    rowEntity.canales[0].cant_cupos_adic_canal = rowEntity.cupos_adicionales;
                  }
                  else{
                    rowEntity.cupos_adicionales = parseInt(oldValue);
                  }                     
                }else{
                  if(newValue!= null && !isNaN(newValue) && newValue > 0){
                    if(colDef.field == 'msj_cupos'){ //cambio total cant cupos 
                      rowEntity.total_cupos = parseInt(newValue); 
                      rowEntity.intervalo = (rowEntity.total_horas*60) / rowEntity.total_cupos;
                      rowEntity.total_cupos_hora = rowEntity.total_cupos / rowEntity.total_horas;                 
                    }

                    if(colDef.field == 'intervalo'){ //cambio intervalo 
                      rowEntity.intervalo = parseInt(newValue); 
                      rowEntity.total_cupos = (rowEntity.total_horas*60) / rowEntity.intervalo;
                      rowEntity.msj_cupos = rowEntity.total_cupos;
                      rowEntity.total_cupos_hora = rowEntity.total_cupos / rowEntity.total_horas;                   
                    }

                    if(colDef.field == 'total_cupos_hora'){ //cambio cant cupos por hora 
                      rowEntity.total_cupos_hora = parseInt(newValue); 
                      rowEntity.total_cupos = rowEntity.total_cupos_hora * rowEntity.total_horas;
                      rowEntity.intervalo = (rowEntity.total_horas * 60) / rowEntity.total_cupos;  
                      rowEntity.msj_cupos = rowEntity.total_cupos;                 
                    }

                    if(rowEntity.canales != null && rowEntity.canales.length > 0){     
                      var canales = angular.copy(rowEntity.canales);
                      canales.forEach(function(value){
                        value.cant_cupos_canal = (rowEntity.total_cupos * value.porcentaje_canal) / 100;
                      });

                      rowEntity.canales = angular.copy(canales);
                     }                  
                  }else{
                    if(colDef.field == 'msj_cupos'){  
                      rowEntity.msj_cupos = parseInt(oldValue);                 
                    }

                    if(colDef.field == 'intervalo'){  
                      rowEntity.intervalo = parseInt(oldValue);                 
                    }

                    if(colDef.field == 'total_cupos_hora'){ 
                      rowEntity.total_cupos_hora = parseInt(oldValue);             
                    }                  
                  }
                }                
                $scope.$apply();
              });
            }              
          }; 
          if($scope.fData.tipoAtencion == 'CM'){ // CONSULTA 
            $scope.gridOptions.columnDefs = [ 
              { field: 'id', name: 'id', displayName: 'ID', maxWidth: 40, enableCellEdit: false },
              { field: 'fecha', name: 'fecha', displayName: 'Fecha', maxWidth: 100, enableCellEdit: false},
              { field: 'turno', name: 'turno', displayName: 'Turno', width:220, enableCellEdit: false },
              { field: 'total_horas', name: 'total_horas', displayName: 'Cant. horas', width:100, enableCellEdit: false },
              { field: 'intervalo', name: 'intervalo', displayName: 'Intervalo', cellClass:'ui-editCell'},
              { field: 'msj_cupos', name: 'msj_cupos', displayName: 'cant. cupos', cellClass:'ui-editCell' },              
              { field: 'total_cupos_hora', name: 'total_cupos_hora', displayName: 'cupos/hora', cellClass:'ui-editCell'},
              { field: 'cupos_adicionales', name: 'cupos_adicionales', displayName: 'Cupos Ad.', cellClass:'ui-editCell'},              
              { field: 'accion', displayName: '', width: 80, enableCellEdit: false, 
                cellTemplate:'<button type="button" class="btn btn-sm btn-info center-block" ng-click="grid.appScope.btnConfCanal(row)" > CANALES </button>' }
            ];
          }else if($scope.fData.tipoAtencion == 'P'){ // PROCEDIMIENTO 
            $scope.gridOptions.columnDefs = [ 
              { field: 'id', name: 'id', displayName: 'ID', maxWidth: 40, enableCellEdit: false },
              { field: 'fecha', name: 'fecha', displayName: 'Fecha', maxWidth: 100, enableCellEdit: false},
              { field: 'turno', name: 'turno', displayName: 'Turno', width:220, enableCellEdit: false },
              // { field: 'total_horas', name: 'total_horas', displayName: 'Cant. horas', width:100, enableCellEdit: false },
              // { field: 'intervalo', name: 'intervalo', displayName: 'Intervalo', cellClass:'ui-editCell'},
              // { field: 'msj_cupos', name: 'msj_cupos', displayName: 'cant. cupos', cellClass:'ui-editCell' }, 
              // { field: 'total_cupos_hora', name: 'total_cupos_hora', displayName: 'cupos/hora', cellClass:'ui-editCell'},
              // { field: 'cupos_adicionales', name: 'cupos_adicionales', displayName: 'Cupos Ad.', cellClass:'ui-editCell'},              
              // { field: 'accion', displayName: '', width: 80, enableCellEdit: false, 
              //   cellTemplate:'<button type="button" class="btn btn-sm btn-info center-block" ng-click="grid.appScope.btnConfCanal(row)" > CANALES </button>' }
            ];
          }
          
          $scope.updateGridProgramas = function () { 
            var list = []; 
            $scope.fData.alertaAmbientesMsj = []; 
            console.log(planingCell, ambienteCell,'planingCell, ambienteCell');
            console.log($scope.fData.arrFechas,'$scope.fData.arrFechas');  
            console.log($scope.fData.ambiente,'$scope.fData.ambiente');   
            progMedicoServices.sCalcularBloquesHoras($scope.fData.arrHoras).then(function (rpta) {
              $scope.fData.listaBloquesHoras = rpta.datos; 
              //console.log($scope.fData.listaBloquesHoras );
              $scope.fData.arrFechas.sort();
              var turno = {};

              angular.forEach($scope.fData.arrFechas,function (item,indice) { 
                var fecha = new Date(item).toLocaleDateString('en-GB', {  
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                }).split(' ').join('/');

                var fecha_item = new Date(item).toLocaleDateString('en-GB', {  
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                }).split('/').join('-');

                var ambiente = {
                  id: $scope.fData.ambiente.id,
                } 
                $scope.consulta = {};
                $scope.consulta.ambiente = ambiente;
                $scope.consulta.fecha_evento = fecha_item;
                $scope.consulta.fecha_formato = fecha;

                programacionAmbienteServices.sVerificarDisponibilidadAmbiente($scope.consulta).then(function (rpta){
                  //console.log(rpta.message, rpta.flag);
                  if(rpta.flag==0){
                    $scope.fData.alertaAmbientes = true;
                    var array = {value:rpta.message};
                    $scope.fData.alertaAmbientesMsj.push(array);
                  }

                  if($scope.fData.alertaAmbientesMsj.length == 0){
                    $scope.fData.alertaAmbientes = false;
                  }
                });
                if ($scope.fData.listaBloquesHoras.length>0){
                  angular.forEach($scope.fData.listaBloquesHoras,function (bloque, index) {  
                    var total_cupos = ((bloque.cantidad_horas * 60) / $scope.fData.intervalo);
                    angular.forEach($scope.fData.listaCanales,function (canal, i) {                
                      canal.cant_cupos_canal = (total_cupos * canal.porcentaje_canal)/100;                     
                    });

                    var listCanales = angular.copy($scope.fData.listaCanales); 
                    //console.log($scope.fData.listaCanales);
                    turno = {'id': indice+index+1, 
                             'fecha': fecha, 
                             'fecha_item' : fecha_item,
                             'hora_inicio': bloque.inicio,
                             'hora_fin':bloque.fin,                         
                             'turno': bloque.formato_inicio + ' - ' + bloque.formato_fin,
                             'intervalo':$scope.fData.intervalo,
                             'total_horas': bloque.cantidad_horas,
                             'total_cupos_hora': total_cupos / bloque.cantidad_horas,
                             'total_cupos': total_cupos,
                             'msj_cupos':  total_cupos,
                             'cupos_adicionales' : 0,
                             'canales' : listCanales
                           };
                    list.push(turno);           

                    
                  });
                }else{
                   turno = {'id': indice+1, 
                           'fecha': fecha, 
                           'fecha_item' : fecha_item,
                           'hora_inicio': null,
                           'hora_fin': null,                         
                           'turno':'Seleccione Horario',
                           'intervalo':$scope.fData.intervalo,
                           'total_horas': 0,
                           'total_cupos_hora': 0,
                           'total_cupos': 0,
                           'msj_cupos': 0,  
                           'cupos_adicionales' : 0,
                           'canales' : []
                         };
                    list.push(turno);
                }
              });
              $scope.gridOptions.data = list; 
            });            
          } 

          $scope.getSelectedMedico = function ($item, $model, $label) {
            console.log($item); console.log($scope.fData.tipoAtencion); 
            if($item.tiene_prog_cita == 1 && $scope.fData.tipoAtencion == 'CM' || $item.tiene_prog_proc == 1 && $scope.fData.tipoAtencion == 'P'){
              $scope.fData.idmedico = $item.idmedico;
              $scope.fData.medico = $item.medico;
              $scope.fData.idempresa = $item.idempresa;
              $scope.fData.idespecialidad = $item.idespecialidad;
              $scope.fData.empresa = $item.empresa;
              $scope.fData.especialidad = $item.especialidad;
              $scope.fData.idempresamedico = $item.idempresamedico;
            }else{
              $scope.fData.medico = null;
              var pTitle = 'Aviso!';
              var pType = 'warning';         
              var pText = 'Debe HABILITAR programación asistencial para la especialidad: ' + $item.especialidad + ' en la Sede: ' + $scope.sede.descripcion ;
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
            }            
          };

          $scope.btnConfCanal = function (row) { 
            $modal.open({              
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_canales',
              size: 'lg',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                $scope.titleForm = 'GESTIÓN DE CUPOS';
                var paginationCanalOptions = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.total_cupos = row.entity.total_cupos;
                $scope.fDataGestion = row.entity;
                $scope.mySelectionCanalGrid = [];
                $scope.gridOptionsCanal = {
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  useExternalFiltering : true,
                  enableGridMenu: false,
                  enableRowSelection: true,
                  enableSelectAll: true,
                  enableFiltering: false,
                  minRowsToShow: 8,
                  enableFullRowSelection: true,
                  multiSelect: false,
                  showGridFooter: true,
                  showColumnFooter: true,
                  columnDefs: [
                    { field: 'id', name: 'idcanal', displayName: 'ID', maxWidth: 60,enableCellEdit: false ,sort: { direction: uiGridConstants.ASC} },
                    { field: 'descripcion', name: 'descripcion_can', displayName: 'Descripcion', enableCellEdit: false },
                    { field: 'cant_cupos_canal', name: 'cant_cupos_canal', displayName: 'Cant. Cupos', cellClass:'ui-editCell', aggregationType: uiGridConstants.aggregationTypes.sum  },
                    { field: 'cant_cupos_adic_canal', name: 'cant_cupos_adic_canal', displayName: 'Cant. Cupos Adicionales', cellClass:'ui-editCell', aggregationType: uiGridConstants.aggregationTypes.sum  }
                  ],
                  onRegisterApi: function(gridApi) {
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionCanalGrid = gridApi.selection.getSelectedRows();
                    });

                    gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                      $scope.mySelectionCanalGrid = gridApi.selection.getSelectedRows();
                    });

                    gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                      if(!(newValue!= null && !isNaN(newValue) && newValue >= 0)){
                        if(colDef.field == 'cant_cupos_canal'){  
                          rowEntity.cant_cupos_canal = parseInt(oldValue);                 
                        }

                        if(colDef.field == 'cant_cupos_adic_canal'){  
                          rowEntity.cant_cupos_adic_canal = parseInt(oldValue);                 
                        }
                      }                                            
                      $scope.$apply();
                    });
                  }
                };
                paginationCanalOptions.sortName = $scope.gridOptionsCanal.columnDefs[0].name;
                $scope.getPaginationCanalServerSide = function() { 
                  $scope.gridOptionsCanal.data = row.entity.canales; 
                  $scope.gridOptionsCanal.totalItems = row.entity.canales.length;           
                  $scope.mySelectionCanalGrid = [];
                };
                $scope.getPaginationCanalServerSide();

                $scope.aceptarCanal = function () { 
                var temp = 0;      
                var temp2 = 0;      
                  angular.forEach($scope.gridOptionsCanal.data,function (canal, i) {                
                    temp = temp + parseInt(canal.cant_cupos_canal);      
                    temp2 = temp2 + parseInt(canal.cant_cupos_adic_canal);    
                  });
                  if($scope.total_cupos == temp && $scope.fDataGestion.cupos_adicionales == temp2){
                    row.entity.canales = angular.copy($scope.gridOptionsCanal.data); 
                    $modalInstance.dismiss('aceptarCanal');
                    //console.log($scope.gridOptions);
                  }else{
                    var pTitle = 'Aviso!';
                    var pType = 'warning';         
                    var pText = 'La suma de todos los cupos y cupos adicionales por canal, debe ser igual a la cantidad de cupos de la programación';
                    pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
                  }                  
                }
              }
            });  
          }

          $scope.btnBuscar = function (size) {
            $modal.open({
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_medico',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,

              controller: function ($scope, $modalInstance) {
                $scope.titleForm = 'Seleccionar Médico'; 
                $scope.mySelectionMedicoGrid = [];   

                var paginationMedicoOptions = { 
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                }; 
                $scope.gridOptionsMedico = {
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  useExternalFiltering : true,
                  enableGridMenu: false,
                  enableRowSelection: true,
                  enableSelectAll: false,
                  enableFiltering: true,
                  minRowsToShow: 8,
                  enableFullRowSelection: true,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'idmedico', name: 'idmedico', displayName: 'ID', maxWidth: 60,sort: { direction: uiGridConstants.ASC} },
                    { field: 'medico', name: "med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres", displayName: 'Médico' },
                    { field: 'idespecialidad', name: 'esp.idespecialidad', displayName: 'idespecialidad', visible:false  },
                    { field: 'especialidad', name: 'esp.nombre', displayName: 'Especialidad'  },
                    { field: 'idempresa', name: 'em.idempresa', displayName: 'idempresa', visible:false  },
                    { field: 'empresa', name: 'em.descripcion', displayName: 'empresa' , enableFiltering:false },
                    { field: 'idempresamedico', name: 'emme.idempresamedico', displayName: 'idempresamedico', visible:false},
                    { field: 'tiene_prog_cita', name: 'seesp.tiene_prog_cita', displayName: 'tiene_prog_cita', visible:false}

                  ],
                  onRegisterApi: function(gridApi) {
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionMedicoGrid = gridApi.selection.getSelectedRows();
                    });

                    gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                      $scope.mySelectionMedicoGrid = gridApi.selection.getSelectedRows();
                    });

                    gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                      paginationMedicoOptions.pageNumber = newPage;
                      paginationMedicoOptions.pageSize = pageSize;
                      paginationMedicoOptions.firstRow = (paginationMedicoOptions.pageNumber - 1) * paginationMedicoOptions.pageSize;

                      console.log(paginationMedicoOptions);

                      $scope.getPaginationMedicoServerSide();
                      
                    });
                    $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
                        var grid = this.grid;
                        paginationMedicoOptions.search = true;                        
                        paginationMedicoOptions.searchColumn = {
                          'm.idmedico' : grid.columns[1].filters[0].term,
                          "med_apellido_paterno || ' ' || med_apellido_materno || ', ' || med_nombres" : grid.columns[2].filters[0].term,
                          'esp.nombre' : grid.columns[4].filters[0].term,
                          'em.descripcion' : grid.columns[6].filters[0].term                          
                        } 
                        $scope.getPaginationMedicoServerSide();                        
                    });
                  }
                };            

                $scope.getPaginationMedicoServerSide = function() { 
                  $scope.datosGrid = {
                    paginate : paginationMedicoOptions,
                    datos :  {idsede: $scope.sede.idsede}
                  };
                  //console.log(paginationMedicoOptions);
                  empleadoSaludServices.sListarMedicosEspecialidad($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsMedico.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsMedico.data = rpta.datos;
                  });
                  $scope.mySelectionMedicoGrid = [];
                };
                $scope.getPaginationMedicoServerSide();

                $scope.aceptarMedico = function () {
                  if($scope.mySelectionMedicoGrid.length==1 ){
                    //console.log($scope.mySelectionMedicoGrid[0]);
                    if( $scope.mySelectionMedicoGrid[0].tiene_prog_cita == 1){
                      $scope.fData.idmedico = $scope.mySelectionMedicoGrid[0].idmedico;
                      $scope.fData.medico = $scope.mySelectionMedicoGrid[0].medico;
                      $scope.fData.idempresa = $scope.mySelectionMedicoGrid[0].idempresa;
                      $scope.fData.idespecialidad = $scope.mySelectionMedicoGrid[0].idespecialidad;
                      $scope.fData.empresa = $scope.mySelectionMedicoGrid[0].empresa;
                      $scope.fData.especialidad = $scope.mySelectionMedicoGrid[0].especialidad;
                      $scope.fData.idempresamedico = $scope.mySelectionMedicoGrid[0].idempresamedico;
                    }else{
                      $scope.fData.medico = null;
                      var pTitle = 'Aviso!';
                      var pType = 'warning';         
                      var pText = 'Debe HABILITAR programación asistencial para la especialidad: ' + 
                      $scope.mySelectionMedicoGrid[0].especialidad + ' en la Sede: ' + $scope.sede.descripcion ;
                      pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });            
                    }                  

                    $scope.mySelectionMedicoGrid = [];
                  }
                  
                  $modalInstance.dismiss('aceptarMedico');
                }
              }
            });  
          }

          $scope.btnGuardar = function(limpiar){
            var pTitle = 'Aviso!';
            var pType = 'warning'; 
            var pText = '';
            if($scope.fData.idmedico == null){ 
              pText = 'Debe seleccionar Médico';
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 2500 });   
            }else if($scope.fData.ambiente.id == null || $scope.fData.ambiente.id == 0){                        
              pText = 'Debe seleccionar Ambiente';
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 2500 });   
            }else if($scope.fData.renombrar && ($scope.fData.categoriaConsul=='0' || $scope.fData.subCategoriaConsul=='0')){       
              pText = 'Debe seleccionar Categoría y Subcategoría para Renombrar';
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 2500 });   
            }else if($scope.fData.arrFechas == null || $scope.fData.arrFechas.length < 1 ){  
              pText = 'Debe seleccionar Días de programación';
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 2500 });   
            }else if($scope.fData.arrHoras == null || $scope.fData.arrHoras.length < 1 ){  
              pText = 'Debe seleccionar Horarios de programación';
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 2500 });   
            }else{ 
              blockUI.start('Grabando información...');
              $scope.fDataAdd = {};
              $scope.fDataAdd.programas = $scope.gridOptions.data;
              $scope.fDataAdd.idmedico = $scope.fData.idmedico;
              $scope.fDataAdd.medico = $scope.fData.medico;
              $scope.fDataAdd.idempresamedico = $scope.fData.idempresamedico;
              $scope.fDataAdd.idespecialidad = $scope.fData.idespecialidad;
              $scope.fDataAdd.especialidad = $scope.fData.especialidad;
              $scope.fDataAdd.idsede = $scope.sede.idsede;
              $scope.fDataAdd.idambiente = $scope.fData.ambiente.id;
              $scope.fDataAdd.ambiente = $scope.fData.ambiente.numero_ambiente;
              $scope.fDataAdd.idsubcategoriaconsul = $scope.fData.ambiente.idsubcategoriaconsul;
              $scope.fDataAdd.renombrar = $scope.fData.renombrar;
              if( $scope.fData.renombrar){
                $scope.fDataAdd.idcategoriaconsulrenom =  $scope.fData.categoriaConsul.id;
                $scope.fDataAdd.idsubcategoriaconsulrenom =  $scope.fData.subCategoriaConsul.id;
              }
              $scope.fDataAdd.comentario = $scope.fData.comentario;
              $scope.fDataAdd.activo = $scope.fData.activoRegistro.id;
              $scope.fDataAdd.tipoAtencion = $scope.fData.tipoAtencion;
              console.log("fData: ",$scope.fDataAdd);
              progMedicoServices.sRegistrar($scope.fDataAdd).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success'; 
                  $scope.fData.arrHoras = null;
                  $scope.gridOptions.data = [];

                  if(limpiar){
                    $scope.fDataAdd = {};
                    $scope.fData.arrFechas = [];
                    $scope.fData.activeDate = null;
                    $scope.fData.arrHoras = null;
                    $scope.fData.listaBloquesHoras=null;
                    $scope.fData.renombrar = false; 
                    $scope.fData.comentario = '';
                    $scope.fData.activoRegistro = $scope.listaEstadosRegistro[1];
                    $scope.fData.subCategoriaConsul = '0';
                    $scope.fData.categoriaConsul = '0';
                    $scope.fData.idambiente = '0-0';
                    $scope.fData.idmedico = null;
                    $scope.fData.medico =null;
                    $scope.fData.idempresa = null;
                    $scope.fData.idespecialidad = null;
                    $scope.fData.empresa = null;
                    $scope.fData.especialidad = null;
                    $scope.fData.idempresamedico = null;
                    $scope.gridOptions.data = [];
                  } 
                  
                  if(rpta.flagMail == 1){
                    pinesNotifications.notify({ title: pTitle, text: rpta.messageMail, type: pType, delay:3000 });
                  }else{
                    pinesNotifications.notify({ title: 'Aviso!', text: rpta.messageMail, type: 'warning', delay:3000 });
                  }
                  console.log(rpta.notificaciones);
                  $scope.modulo = 'progMedico';
                  $scope.btnNuevo('key_prog_med',1, null, rpta.notificaciones);
                }else if(rpta.flag == 0){
                  pTitle = 'Error!';
                  pType = 'error';
                }else{
                  alert('Error inesperado');
                }                
                pText = rpta.message;
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay:3000 });  
                  
                $scope.listarPlaningMedicos(); 
                blockUI.stop(); 
              });
            }           
          } 

          $scope.activarRenombrar = function(){
            //console.log($scope.fData.ambiente);
            if($scope.fData.ambiente.id != '0'){
              if($scope.fData.ambiente.descripcion_cco.toUpperCase()!='ASISTENCIAL'){
                $scope.fData.renombrar = true;
                $scope.fData.habilitado = true;
              }else{
                $scope.fData.renombrar = false;
                $scope.fData.habilitado = false;
              }              
            }else{
              $scope.fData.renombrar = false;
              $scope.fData.habilitado = false;
            }
          }

          var cbBindeoDeDatos = function(planingCell,ambienteCell) { 
            if( planingCell && ambienteCell ){ 
              var strFecha = moment(planingCell.fecha).valueOf(); 
              var objIndex = $scope.fData.listaAmbienteSede.filter(function(obj) {
                return obj.id == ambienteCell.idambiente;
              }).shift(); 
              $scope.fData.ambiente = objIndex; 
              $scope.fData.arrFechas = [strFecha]; 
              //console.log(strFecha,planingCell.fecha, 'strFecha,planingCell.fecha');
              if( $scope.fData.ambiente && $scope.fData.arrFechas ){ 
                $scope.updateGridProgramas(); 
              } 
            } 
          } 
          $scope.getAmbienteHoras(cbBindeoDeDatos); 
          blockUI.stop();
        }
      });
    }
    $scope.verTodasProgramaciones = function (){
      $modal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_programaciones',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'LISTADO DE PROGRAMACIONES';
          $scope.grid = {
            fecha_desde : $scope.fBusqueda.desde,
            fecha_hasta : $scope.fBusqueda.hasta
          };

          $scope.estadoOptions = [
            { id: 1, estado: 'REGISTRADO' },
            { id: 2, estado: 'CANCELADO / REPROGRAMADO' },
            { id: 0, estado: 'ANULADO' },
          ];
          $scope.grid.estado = $scope.estadoOptions[1];

          $scope.dateUIDesde = {} ;
          $scope.dateUIDesde.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
          $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
          $scope.dateUIDesde.datePikerOptions = {
            formatYear: 'yy',
            // startingDay: 1,
            'show-weeks': false
          };

          $scope.dateUIHasta = {} ;
          $scope.dateUIHasta.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
          $scope.dateUIHasta.format = $scope.dateUIHasta.formats[0]; // formato por defecto
          $scope.dateUIHasta.datePikerOptions = {
            formatYear: 'yy',
            // startingDay: 1,
            'show-weeks': false
          };

          $scope.dateUIDesde.openDP = function($event) {
            //console.log($event);
            $event.preventDefault();
            $event.stopPropagation();
            $scope.dateUIDesde.opened = true;
          };

          $scope.dateUIHasta.openDP = function($event) {
            //console.log($event);
            $event.preventDefault();
            $event.stopPropagation();
            $scope.dateUIHasta.opened = true;
          };

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
            minRowsToShow: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: true,
            enableFullRowSelection: true,
           //rowHeight: 100,
            multiSelect: false,
            columnDefs: [
              { field: 'idprogmedico', name: 'idprogmedico', displayName: 'ID', sort: { direction: uiGridConstants.DESC}, visible:true, width: 60,},
              { field: 'fecha_programada', name: 'fecha_programada', displayName: 'FECHA', width: 80,},
              { field: 'hora_inicio', name: 'hora_inicio', displayName: 'HORA INICIO', enableFiltering:false, width: 100, },
              { field: 'hora_fin', name: 'hora_fin', displayName: 'HORA FIN', enableFiltering:false, width: 100, },
              { field: 'medico', name: 'medico', displayName: 'MÉDICO',  },
              { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD', width:220, },
              { field: 'ambiente.descripcion', name: 'ambiente', displayName: 'AMBIENTE', width: 90, },
              { field: 'total_cupos_ocupados', name: 'total_cupos_ocupados', displayName: 'TOTAL CUPOS O.', enableFiltering:false, width:120, },
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationOptions.search = true;
                paginationOptions.searchColumn = {
                  'prm.idprogmedico' : grid.columns[1].filters[0].term,
                  'prm.fecha_programada' : grid.columns[2].filters[0].term,
                  "med.med_nombres || ' '  || med.med_apellido_paterno || ' ' || med.med_apellido_materno" : grid.columns[5].filters[0].term,
                  'esp.nombre' : grid.columns[6].filters[0].term,
                  'am.numero_ambiente' : grid.columns[7].filters[0].term,

                }
                $scope.getPaginationProgramacionesServerSide();
              });    

              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptions.pageNumber = newPage;
                paginationOptions.pageSize = pageSize;
                paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
                $scope.getPaginationProgramacionesServerSide();
              });          
            }
          };
          
          $scope.getPaginationProgramacionesServerSide = function (){
            $scope.datosGrid = {
              estado_prm: $scope.grid.estado.id,
              fecha_desde: $scope.grid.fecha_desde,
              fecha_hasta: $scope.grid.fecha_hasta
            };

            var datos = {
              datos : $scope.datosGrid,
              paginate : paginationOptions
            }
            $scope.mySelectionGrid = [];
            if($scope.datosGrid.fecha_desde != null && $scope.datosGrid.fecha_hasta != null){
              progMedicoServices.sListarProgramacionesPorEstado(datos).then(function (rpta) {
                //console.log(rpta.datos);
                $scope.gridOptions.totalItems = rpta.paginate.totalRows;
                $scope.gridOptions.data = rpta.datos;
              });
            }            
          }
          $scope.getPaginationProgramacionesServerSide();

          $scope.btnVerComentario= function () {
            $modal.open({
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_comentario',
              size: '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                $scope.titleForm = 'MOTIVO DE ANULACIÓN/CANCELACIÓN';
                $scope.datosGrid = {
                  idprogmedico: $scope.mySelectionGrid[0].idprogmedico,
                };
                progMedicoServices.sCargarDatosComentario($scope.datosGrid).then(function (rpta) {
                  $scope.fComentario = rpta.datos[0];
                });

                $scope.cancelComent = function () {
                  $modalInstance.dismiss('cancelComent');
                }
              }
            });
          }

          $scope.btnReprogramar = function (){
            var fila = {
              idambiente : $scope.mySelectionGrid[0].idambiente,
            };
            var filaDet = {
              fecha : $scope.mySelectionGrid[0].fecha_programada,
            };
            var section ={
              idespecialidad : $scope.mySelectionGrid[0].idespecialidad,
              idprogramaciones : $scope.mySelectionGrid[0].idprogmedico,
            };
            var fnCallbackReprogramacion = function () {
              $modalInstance.dismiss('cancelVerTodas');
            }
            $scope.verProgramacion(fila, filaDet, section, true, fnCallbackReprogramacion);       
          }    

          $scope.cancelVerTodas = function () {
            $modalInstance.dismiss('cancelVerTodas');
          }

          $scope.verListaPacientes = function(){
            $scope.btnVerListaPacientesConsulta('lista', $scope.mySelectionGrid[0]);
          }
        }
      }); 
    }

    $scope.btnExportarExcel = function (){
      if($scope.fBusqueda.tipoPlaning === 'VD'){
        //console.log($scope.fPlanning.data);
        var datos = $scope.fBusqueda; 
        datos.planning = $scope.fPlanning.data;
        datos.salida = 'excel';
        datos.titulo = 'PROGRAMACIÓN DE MÉDICOS';          

        var arrParams = {          
          datos: datos,
          metodo: 'php',
        }

        arrParams.url = angular.patchURLCI+'ProgMedico/generar_excel_vista_dias';
        ModalReporteFactory.getPopupReporte(arrParams); 
      }

      if($scope.fBusqueda.tipoPlaning === 'VH'){
        //console.log($scope.planning);
        var datos = $scope.fBusqueda; 
        datos.planning = $scope.planning;
        datos.salida = 'excel';
        datos.titulo = 'PROGRAMACIÓN DE MÉDICOS';     
        
        var arrParams = {          
          datos: datos,
          metodo: 'php',
        }

        arrParams.url = angular.patchURLCI+'ProgMedico/generar_excel_vista_horas';
        ModalReporteFactory.getPopupReporte(arrParams); 
      }
    }

    $scope.btnVerListaPacientesProc = function (row, fnCallback){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_pacientes_proc',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'LISTADO DE PACIENTES PARA PROCEDIMIENTO';
          console.log("row", row);
          $scope.fDataListaPaciente = {};
          $scope.fDataListaPaciente = row;
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

          $scope.exportExcelPacientes = function (){
            //console.log($scope.fPlanning.data);
            var datos = $scope.fDataListaPaciente; 
            datos.salida = 'excel';
            datos.titulo = 'LISTA DE PACIENTES';          

            var arrParams = {          
              datos: datos,
              metodo: 'php',
            }

            arrParams.url = angular.patchURLCI+'ProgMedico/generar_excel_lista_pacientes_proc';
            ModalReporteFactory.getPopupReporte(arrParams); 
          }     

        }
      });
    }

    $scope.btnVerListaPacientesConsulta = function (param, row, fnCallback){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_pacientes',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          if(param != 'notificacion'){
            $scope.boolExterno= true;
            $controller('ventaController', { 
              $scope : $scope
            });
            $scope.getDatosModalProgAsistencial();
            $scope.titleForm = 'LISTADO DE PACIENTES';
          }
          
          // console.log('entro a ver pacientes');
          $scope.fDataListaPaciente = {};
          $scope.fDataListaPaciente = row;
          $scope.listaMotivo = [
            {id:0, descripcion: '--SELECCIONE MOTIVO--'}, 
            {id:1, descripcion: 'CANCELACION POR PARTE DEL MEDICO'}, /*cancela cupo y reprograma cita*/
            {id:2, descripcion: 'CANCELACION POR PARTE DEL PACIENTE'}, /*reprograma cupo y reprograma cita*/
          ];
         
          $scope.getListadoPacientes = function (){
            progMedicoServices.sListarPacientes($scope.fDataListaPaciente).then(function (rpta) {
              $scope.fDataListaPaciente.lista = rpta.datos; 
              $scope.gridOptionsPac.data =  $scope.fDataListaPaciente.lista;
              $scope.gridOptionsPac.totalItems = $scope.fDataListaPaciente.lista.length;
            });
          }
          $scope.getListadoPacientes();  

          $scope.gridOptionsPac = { 
            paginationPageSizes: [100, 500, 1000],
            paginationPageSize: 100,
            minRowsToShow: 12,
            useExternalPagination: false,
            useExternalSorting: false,
            useExternalFiltering : false,
            enableGridMenu: true,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: true,
            //rowHeight: 100,
            multiSelect: false,
            columnDefs: [
              { field: 'numero_cupo', name: 'numero_cupo', displayName: 'N° CUPO', width: 70, enableSorting:false},
              { field: 'turno', name: 'turno', displayName: 'TURNO', width: 140, enableSorting:false},
              { field: 'paciente', name: 'paciente', displayName: 'PACIENTE', enableSorting:false },
              { field: 'celular', name: 'celular', displayName: 'CELULAR', width: 120, enableSorting:false },
              { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', visible: false, enableSorting:false},
              { field: 'email', name: 'email', displayName: 'EMAIL', visible: false, enableSorting:false},
              { field: 'si_adicional', name: 'tipo_cupo', displayName: 'ADIC.', width:'60', /*type: 'object',*/ enableFiltering: false, enableSorting: false, 
                cellTemplate:'<div class="ui-grid-cell-contents text-center"><label ng-if="COL_FIELD" class="label label-warning"><i class="fa fa-check"></i></label></div>' 
              },
              { field: 'estado_cita_str', type:'object', name: 'estado_cita_str', displayName: 'ESTADO', width:'100', enableSorting:false, 
                cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>' 
              }, 
              // { field: 'estado_cita_str', name: 'estado_cita_str', displayName: 'ESTADO', width: 120, enableSorting:false}, 
              { field: 'accion', name:'accion', displayName: '', width: '60', enableCellEdit: false, enableSorting:false, 
                cellTemplate:'<div class="block text-left m-xs">' 
                  + '<button type="button" ng-if="(row.entity.idprogcita != null && row.entity.estado_cita == 2  && row.entity.estado_prm == 1)" class="btn btn-sm btn-success ml-xs" ng-click="grid.appScope.cancelaReprogramaCita(row.entity)" uib-popover="Gestionar cita" popover-trigger="mouseenter" popover-placement="left"> <i class="fa fa-calendar"></i> </button>'
                  + '<button type="button" ng-if="row.entity.estado_cita == 3" class="btn btn-sm btn-success ml-xs" ng-click="grid.appScope.cambiarCita(row.entity)" uib-popover="Reprogramar cita" popover-trigger="mouseenter" popover-placement="left"> <i class="fa fa-calendar"></i> </button>'
                  + '<button type="button" ng-if="row.entity.es_ultimo && (row.entity.idprogcita == null || row.entity.estado_cita == 4) && row.entity.estado_prm == 1" class="btn btn-sm btn-danger ml-xs" ng-click="grid.appScope.cancelarCupo(row.entity)" uib-popover="Cancelar cupo" popover-trigger="mouseenter" popover-placement="left"> <i class="fa fa-times"></i> </button>'
                  + '<button type="button" ng-if="(row.entity.estado_cita == 3 || row.entity.estado_cita == 4) && row.entity.motivo_cancelacion != null" class="btn btn-sm btn-info ml-xs" uib-popover-html="row.entity.htmlPopover" popover-trigger="mouseenter" popover-placement="left" > <i class="fa fa-info-circle"></i> </button>'
                  +'</div>' 
              }
            ],
          };

          if(param == 'notificacion'){            
            angular.forEach($scope.gridOptionsPac.columnDefs, function(value, key) {
              if(value.name === 'accion'){
                $scope.gridOptionsPac.columnDefs.splice(key, 1);
                return;
              }
            });
            //$scope.paramNotificacion = true;
            $scope.titleForm = 'DETALLE DE PROGRAMACIÓN';
          }

          $scope.exportExcelPacientes = function (){
            //console.log($scope.fPlanning.data);
            var datos = $scope.fDataListaPaciente; 
            datos.salida = 'excel';
            datos.titulo = 'LISTA DE PACIENTES';          

            var arrParams = {          
              datos: datos,
              metodo: 'php',
            }

            arrParams.url = angular.patchURLCI+'ProgMedico/generar_excel_lista_pacientes';
            //console.log(arrParams);
            ModalReporteFactory.getPopupReporte(arrParams); 
          }     

          $scope.cancelVerPacientes = function () {
            $modalInstance.dismiss('cancelVerPacientes');
          }

          $scope.cancelaReprogramaCita = function(row){
            $uibModal.open({
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_motivo_accion',
              size: '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                $scope.titleFormModal = 'GESTION DE LA CITA';
                $scope.fDataListaPaciente.motivo = $scope.listaMotivo[0];

                $scope.btnCancelar = function(){
                  var datos = {
                    cupo: row,
                    motivo: $scope.fDataListaPaciente.motivo
                  }

                  progMedicoServices.sVerificaCupoReprogramar(datos).then(function (rpta) {
                    if(rpta.flag == 1){
                      $scope.btnCancelMotivo();
                      row.descripcion_motivo = $scope.fDataListaPaciente.descripcion_motivo;
                      $scope.cancelarCupo(row);
                    }else if(rpta.flag == 0){
                      var pTitle = 'Aviso!';
                      var pType = 'warning'; 
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });             
                    }else{
                      alert('Error inesperado');
                    }
                  });
                }

                $scope.btnOkMotivo = function(){
                  var datos = {
                    cupo: row,
                    motivo: $scope.fDataListaPaciente.motivo,                    
                  }

                  progMedicoServices.sVerificaCupoReprogramar(datos).then(function (rpta) {
                    if(rpta.flag == 1){
                      $scope.btnCancelMotivo();
                      $scope.seleccionarNuevaCita(row);
                    }else if(rpta.flag == 0){
                      var pTitle = 'Aviso!';
                      var pType = 'warning'; 
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });             
                    }else{
                      alert('Error inesperado');
                    }
                  });
                }  

                $scope.btnCancelMotivo = function(){
                  $modalInstance.dismiss('btnCancelMotivo');
                  $scope.fDataListaPaciente.descripcion_motivo=null;
                } 
              }
            });
          }          

          $scope.cambiarCita= function(row){
            // console.log('entro por cambiar cita'); 
            $scope.fDataListaPaciente.motivo = {id:3, descripcion:'REPROGRAMACION POR CANCELACION TOTAL'};
            $scope.seleccionarNuevaCita(row);
          }

          $scope.cancelarCupo = function (row){
            $uibModal.open({
              templateUrl: angular.patchURLCI+'ProgMedico/ver_popup_confirmar_accion',
              size: '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {                
                $scope.fDataModal = {};
                $scope.fDataModal.tipo = 'cancelar';
                $scope.fDataModal.cupo = row;
                //console.log($scope.fDataListaPaciente);
                if($scope.fDataModal.cupo.idprogcita != null){
                  $scope.titleFormModal = 'CANCELAR CITA';
                  $scope.fDataModal.mensaje = '¿REALMENTE DESEA CANCELAR LA CITA?'; 
                  $scope.fDataModal.boton = 'SI,DESEO CANCELAR LA CITA'; 
                }else{
                  $scope.titleFormModal = 'CANCELAR CUPO';
                  $scope.fDataModal.mensaje = '¿REALMENTE DESEA CANCELAR EL CUPO?';
                  $scope.fDataModal.boton = 'SI,DESEO CANCELAR EL CUPO'; 
                }
                
                $scope.btnOk = function(){
                  $scope.btnCancel();
                  row.especialidad = $scope.fDataListaPaciente.especialidad;
                  row.medico = $scope.fDataListaPaciente.medico;
                  row.ambiente = $scope.fDataListaPaciente.ambiente.numero_ambiente;
                  progMedicoServices.sCancelarCupo(row).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      if(param == 'editar'){
                        $scope.cancelVerPacientes();
                        fnCallback();                                               
                      }else{
                        $scope.getListadoPacientes();
                      }
                    }else if(rpta.flag == 0){
                      var pTitle = 'Aviso!';
                      var pType = 'warning'; 
                      $scope.getListadoPacientes();               
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                }  

                $scope.btnCancel = function(){
                  $modalInstance.dismiss('btnCancel');
                } 
              }
            });
          }

          $scope.seleccionarNuevaCita = function (rowPaciente){            
            //console.log(rowPaciente);            
            //datos del encabezado planning  
            $scope.fDataVenta = {};
            $scope.fDataVenta.numero_documento = rowPaciente.num_documento;

            $scope.fDataVenta.cliente={};
            $scope.fDataVenta.cliente.nombres = rowPaciente.nombres;
            $scope.fDataVenta.cliente.apellidos = rowPaciente.apellido_paterno + ' ' + rowPaciente.apellido_materno;
            $scope.fDataVenta.cliente.edad = rowPaciente.edad;
            
            $scope.fBusqueda = {};
            var ind = 0;
            angular.forEach($scope.listaEspecialidadesProgAsistencial, function(value, key) {
            if(value.id == $scope.fDataListaPaciente.idespecialidad){
                ind = key;
              }            
            }); 
            $scope.fBusqueda.especialidad = $scope.listaEspecialidadesProgAsistencial[ind]; 

            $scope.genCupo = {};
            $scope.genCupo.oldCita = rowPaciente;
            $scope.genCupo.oldCita.medico = $scope.fDataListaPaciente.medico;
            $scope.genCupo.oldCita.especialidad = $scope.fDataListaPaciente.especialidad;
            $scope.genCupo.oldCita.empresa = $scope.fDataListaPaciente.empresa;
            $scope.genCupo.oldCita.numero_ambiente = $scope.fDataListaPaciente.ambiente.numero_ambiente;
            $scope.genCupo.oldCita.fecha_programada = $scope.fDataListaPaciente.fecha_programada;
            $scope.genCupo.oldCita.intervalo_hora_int = $scope.fDataListaPaciente.intervalo_hora_int;
            //console.log($scope.fDataListaPaciente);

            $scope.genCupo.itemVenta = {};
            $scope.genCupo.itemVenta.producto = {};
            $scope.genCupo.itemVenta.producto.descripcion = rowPaciente.descripcion_producto;
            
            var fnCallbackReprogramacionCita = function () {
              $scope.reprogramarCita();
            }

            $scope.btnGenerarCupo(null,true, fnCallbackReprogramacionCita);            
          }

          $scope.reprogramarCita = function (){                 
            var datos = {
              oldCita: $scope.genCupo.oldCita,
              seleccion: $scope.genCupo.seleccion,
              motivo: $scope.fDataListaPaciente.motivo,
              descripcion_motivo: $scope.fDataListaPaciente.descripcion_motivo,
            }
            
            progMedicoServices.sReprogramarCita(datos).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                if(param == 'editar'){
                  $scope.cancelVerPacientes();
                  fnCallback();
                }else{
                  $scope.getListadoPacientes();
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning'; 
                $scope.getListadoPacientes();               
              }else{
                alert('Error inesperado');
              }
              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });                
          }
        }
      });
    }

    $scope.init = function(){
      if($scope.boolExterno && $scope.paramModulo != 'progMedico'){
        console.log('$scope.boolExterno',$scope.boolExterno);
        console.log('$scope.paramModulo ',$scope.paramModulo);
      }else{
        console.log("programnacion arranca");
        $scope.modulo = 'progMedico';
        $scope.getCategoriaConsul();
        $scope.verPlaningPorDia(); 
        $scope.fBusqueda.tipoPlaning = angular.copy($scope.fToggle.porDefectoAbv);    
        $filter('date')(moment($scope.fBusqueda.tipoPlaning).toDate(),'dd-MM-yyyy');
        $scope.$watch('fToggle.porDefectoAbv', function(newvalue,oldvalue) { 
          $scope.fBusqueda.tipoPlaning = newvalue;
          $scope.listarPlaningMedicos();    
        }); 
        $scope.listarPlaningMedicos(); 
        $scope.getCambioShow(true);
        //$scope.loadModalConfirmacion('sm');
        $scope.getFeriados();
        $scope.getSede(); 
      }
    }
    $scope.init();

    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva Programación',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar ambiente',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular ambiente',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar ambiente',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      })
      .add ({ 
        combo: 's',
        description: 'Selección y Navegación',
        callback: function() {
          $scope.navegateToCell(0,0);
        }
      });
  }])
  .service("progMedicoServices",function($http, $q) {
    return({
        sListarPlaningMedicos:sListarPlaningMedicos,
        sListarEstasProgramaciones: sListarEstasProgramaciones,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sCargaHoras:sCargaHoras,
        sCalcularBloquesHoras:sCalcularBloquesHoras,
        sAnular: sAnular,
        sAnularProc: sAnularProc,
        sListarProgramacionesPorEstado: sListarProgramacionesPorEstado, 
        sCargarDatosComentario: sCargarDatosComentario,
        sVerificarCuposProgramacion: sVerificarCuposProgramacion,
        sRegistrarReprogramacion: sRegistrarReprogramacion,
        sCancelar: sCancelar,
        sCargarCuposPorCanales: sCargarCuposPorCanales,
        sGuardarGestionCupos: sGuardarGestionCupos,
        sListarPlaningHorasMedicos: sListarPlaningHorasMedicos,
        sListarPacientes: sListarPacientes,
        sPlanningHorasGeneraCita: sPlanningHorasGeneraCita,
        sPlanningHorasVistaInformes: sPlanningHorasVistaInformes,
        sPlanningHorasGeneraCitaMedico : sPlanningHorasGeneraCitaMedico,
        sListarCuposCanal: sListarCuposCanal,
        sCancelarCupo: sCancelarCupo,
        sReprogramarCita:sReprogramarCita,
        sVerificaCupoReprogramar: sVerificaCupoReprogramar,
        sCambiarCita: sCambiarCita,
        sListarProgramacionMedicoCbo: sListarProgramacionMedicoCbo,
        sListarPacientesProgramadosParaConsulta: sListarPacientesProgramadosParaConsulta,
        sListarPacientesProgramadosParaProc: sListarPacientesProgramadosParaProc,
        sListaProgramacionProc:sListaProgramacionProc,
    });
    function sListarPlaningMedicos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_planing_medicos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarEstasProgramaciones(datos) {
      console.log(datos);
      var url;
      if(datos.tipoAtencion == 'P'){
        url = angular.patchURLCI+"ProgMedico/listar_estas_programaciones_proc";
      }else{
        url = angular.patchURLCI+"ProgMedico/listar_estas_programaciones";
      }
      var request = $http({
            method : "post",
            url : url, 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCargaHoras (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_horas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCalcularBloquesHoras (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/calcular_bloques_horas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar(datos) {
      var url;
      if(datos.tipoAtencion == 'P'){
        url = angular.patchURLCI+"ProgMedico/editarProc";
      }else{
        url = angular.patchURLCI+"ProgMedico/editar";
      }
      var request = $http({
            method : "post",
            url : url, 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularProc(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/anularProc", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarProgramacionesPorEstado(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_programaciones_por_estado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sCargarDatosComentario(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/cargar_datos_comentarios", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sVerificarCuposProgramacion(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/verificar_cupos_programacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sRegistrarReprogramacion(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/registrar_reprogramacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sCancelar(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/cancelar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sCargarCuposPorCanales(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/cargar_cupos_por_canales", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sGuardarGestionCupos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/guardar_gestion_cupos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPlaningHorasMedicos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_planing_horas_medicos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarPacientes(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_pacientes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }     
    function sPlanningHorasGeneraCita(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/planing_horas_genera_consulta", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sPlanningHorasVistaInformes(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/planing_horas_genera_consulta_informes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sPlanningHorasGeneraCitaMedico(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/planing_horas_genera_consulta_medico", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarCuposCanal(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_cupos_canal", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sCancelarCupo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/cancelar_cupo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sReprogramarCita(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/reprogramar_cita", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sVerificaCupoReprogramar(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/verifica_cupo_reprogramar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarCita(datos){
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/cambiar_cita", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sListarProgramacionMedicoCbo(datos){
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/cargar_programacion_medico_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPacientesProgramadosParaConsulta(datos){
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_pacientes_por_programacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sListarPacientesProgramadosParaProc(datos){
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_pacientes_por_programacion_porc", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListaProgramacionProc(datos){
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgMedico/listar_programaciones_proc_fecha", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
  }); 