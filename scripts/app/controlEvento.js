angular.module('theme.controlEvento', ['theme.core.services'])
  .controller('controlEventoController', ['$scope', '$sce', '$uibModal', '$modal', '$controller', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI',
      'controlEventoServices', 
      'grupoServices',
    function($scope, $sce, $uibModal, $modal, $controller, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI,
      controlEventoServices,
      grupoServices){
    'use strict';

    $scope.init = function (){
      if(!$scope.boolExterno){
        $scope.modulo = 'controlEvento';
        $scope.boolExterno = false;        
      }
      $scope.repeat = false;
      console.log($scope.modulo,'$scope.modulo');
      console.log($scope.boolExterno,'$scope.boolExterno');      
    }
    $scope.init();    

    $scope.btnNuevo = function(origen,tipoEvento, fnCallBack, lista){
      var origen = origen || null;
      var tipoEvento = tipoEvento || null;
      blockUI.start('Abriendo formulario...');
      if(tipoEvento != 10 && $scope.modulo=='progMedico'){
        $scope.boolExterno = true;
        console.log('externo');
      }

      if($scope.boolExterno && (tipoEvento == 1 || tipoEvento == -1)){
        $scope.boolRepeat = true;
        console.log('repeat');
      }

      $modal.open({
        templateUrl: angular.patchURLCI+'ControlEvento/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};         
          $scope.titleForm = 'Registro de Notificación de Evento';

          if(origen == 'key_prog_med'){
            var datos = {
              campo:'permite_notificacion_pa',
              value:1
            }
            grupoServices.sListarGruposNotificaciones(datos).then(function (rpta) {              
              if($scope.boolExterno && $scope.boolRepeat){
                $scope.fData.listaGrupos = rpta.datos;
                $scope.fDataLista = [];
                angular.forEach(lista, function(value, key) {
                  var idtipoevento = tipoEvento;
                  if(value.idtipoevento){
                    idtipoevento = value.idtipoevento;
                  }
                  var array = {
                    'estado_ce':2,
                    'idtipoevento': idtipoevento,
                    'texto_notificacion':value.texto_notificacion,
                    'comentario':null,
                    'identificador':value.identificador,
                    'texto_log':value.texto_log,
                    'listaGrupos': angular.copy($scope.fData.listaGrupos),
                    'visible':true
                  };
                  $scope.fDataLista.push(array);
                });
                $scope.totalItems = $scope.fDataLista.length;
                //console.log($scope.fDataLista);
              }else{
                if(lista){
                  $scope.fData = lista[0];
                }else{
                  $scope.fData = {};
                }
                
                $scope.fData.listaGrupos = rpta.datos;
                $scope.fData.estado_ce = 2;   
                $scope.fData.idtipoevento = tipoEvento; 
              }
            }); 
          }else{
            grupoServices.sListarGruposCbo().then(function (rpta) {
              $scope.fData.listaGrupos = rpta.datos;
            });
          }         
          
          if(!$scope.boolExterno){  
            $scope.fData.estado_ce = 2;   
            $scope.fData.idtipoevento = tipoEvento;                                
            $scope.fData.comentario = null;                      
            $scope.fData.identificador = null;                      
            $scope.fData.texto_log = null;                                   
          }                               

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            if(tipoEvento == 2 || tipoEvento == 3){
              fnCallBack();
            }
          }

          $scope.btnGenerarNotificacion = function(){
            if(!$scope.boolExterno || ($scope.boolExterno && !$scope.boolRepeat)){
              $scope.fDataLista = [];
              $scope.fDataLista.push($scope.fData);
            }
            controlEventoServices.sRegistrarNotificacion($scope.fDataLista).then(function (rpta) {
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                if(tipoEvento == 10 || tipoEvento == 2 || tipoEvento == 3)
                  fnCallBack();
                $modalInstance.dismiss('cancel'); 
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';                             
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                            
            });
          }

          $scope.btnGenerarNotificacionItem = function(key, row){
            $scope.fData = row;            
            $scope.dataRow = [];
            $scope.dataRow.push($scope.fData);            
            controlEventoServices.sRegistrarNotificacion($scope.dataRow).then(function (rpta) {
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                $scope.fDataLista[key].visible=false;
                $scope.totalItems = $scope.totalItems-1;
                if($scope.totalItems == 0){
                  $scope.cancel();
                }
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';                             
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });                            
            });
          }

          blockUI.stop();
        }
      });
    }

    $scope.btnReenviar = function(row,origen,fnCallBack){
      var origen = origen || null;
      var tipoEvento = tipoEvento || null;
      blockUI.start('Abriendo formulario...');
      $modal.open({
        templateUrl: angular.patchURLCI+'ControlEvento/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {}; 
          $scope.fData = row;        
          $scope.titleForm = 'Reenvio de Notificación de Evento';
          if(origen == 'key_prog_med'){
            var datos = {
              campo:'permite_notificacion_pa',
              value:1,
              idcontrolevento: row.idcontrolevento
            }            
            controlEventoServices.sCargarGruposNotificacionDesdeUsuarios(datos).then(function (rpta) {
              $scope.fData.listaGrupos = rpta.datos;
              console.log($scope.fData.listaGrupos);
            });             
          }else{
            grupoServices.sListarGruposCbo().then(function (rpta) {
              $scope.fData.listaGrupos = rpta.datos;
            });
          }                      

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }

          $scope.btnGenerarNotificacion = function(){
            console.log($scope.fData);
            $scope.fDataLista = [];
            $scope.fDataLista.push($scope.fData);
            controlEventoServices.sRegistrarNotificacion($scope.fDataLista).then(function (rpta) {
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success'; 
                fnCallBack();
                $scope.cancel();
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';                             
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }

          blockUI.stop();
        }
      });
    }
    
  }])
  .service("controlEventoServices",function($http, $q) {
    return({
        sListarControlEvento: sListarControlEvento,
        sRegistrarNotificacion: sRegistrarNotificacion,
        sAnular: sAnular,
        sCambiarEstado: sCambiarEstado,
        sCargarGruposNotificacionDesdeUsuarios: sCargarGruposNotificacionDesdeUsuarios,
        sUpdateLeidoNotificacion:sUpdateLeidoNotificacion,
        sGeneraNotificacionPusher:sGeneraNotificacionPusher,
    });
    function sListarControlEvento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/listar_control_evento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sRegistrarNotificacion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/registrar_notificacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarEstado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/cambiar_estado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sCargarGruposNotificacionDesdeUsuarios (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/cargar_grupos_notificacion_desde_usuarios", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sUpdateLeidoNotificacion (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/update_leido_notificacion", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGeneraNotificacionPusher (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ControlEvento/genera_notificacion_pusher", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  })