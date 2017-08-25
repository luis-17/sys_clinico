angular.module('theme.solicitudCitt', ['theme.core.services'])
  .controller('solicitudCittController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys'
    ,'solicitudCitt',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      solicitudCitt ){
    'use strict';
    
  }])
  .service("solicitudCittServices",function($http, $q) {
    return({
      sListarCittDePaciente: sListarCittDePaciente,
      sAnularSolicitudCitt: sAnularSolicitudCitt,
      sRegistrarSolicitudCitt: sRegistrarSolicitudCitt,
      sObtenerProductoCITT: sObtenerProductoCITT
      //sEditarCantidadSolicitudCitt: sEditarCantidadSolicitudCitt
    });

    function sListarCittDePaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudCitt/lista_citt_de_paciente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerProductoCITT(datos){
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudCitt/obtener_producto_citt", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarSolicitudCitt (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudCitt/registrar_solicitud_citt", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularSolicitudCitt (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudCitt/anular_solicitud_citt", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });