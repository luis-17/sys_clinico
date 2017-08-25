angular.module('theme.solicitudExamen', ['theme.core.services'])
  .controller('solicitudExamenController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys'
    ,'solicitudExamen',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      solicitudExamen ){
    'use strict';
    
  }])
  .service("solicitudExamenServices",function($http, $q) {
    return({
      sListarSolicitudesExamenSession: sListarSolicitudesExamenSession,
      sListarSolicitudesExamenDePaciente: sListarSolicitudesExamenDePaciente, 
      sListarExamenesAutoComplete: sListarExamenesAutoComplete,
      sRegistrarSolicitudExamen: sRegistrarSolicitudExamen,
      sEliminarSolicitudExamenAux: sEliminarSolicitudExamenAux
    });
    function sListarSolicitudesExamenSession(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudExamen/lista_solicitudes_examen_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSolicitudesExamenDePaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudExamen/lista_solicitudes_examen_de_paciente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarExamenesAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudExamen/lista_examen_auxiliar_de_especialidad_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarSolicitudExamen (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudExamen/registrar_solicitud_examen_auxiliar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarSolicitudExamenAux (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudExamen/anular_solicitud_examen_auxiliar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });