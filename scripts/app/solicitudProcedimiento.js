angular.module('theme.solicitudProcedimiento', ['theme.core.services'])
  .controller('solicitudProcedimientoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys'
    ,'solicitudProcedimiento',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      solicitudProcedimiento ){
    'use strict';
    
  }])
  .service("solicitudProcedimientoServices",function($http, $q) {
    return({
      sListarSolicitudesProcedimientoSession: sListarSolicitudesProcedimientoSession,
      sListarProcedimientosDePaciente: sListarProcedimientosDePaciente,
      sListarProcedimientoAutoComplete: sListarProcedimientoAutoComplete,
      sRegistrarSolicitudProcedimiento: sRegistrarSolicitudProcedimiento,
      sEditarCantidadSolicitudProcedimiento: sEditarCantidadSolicitudProcedimiento,
      sEliminarSolicitudProcedimiento: sEliminarSolicitudProcedimiento
    });
    function sListarSolicitudesProcedimientoSession(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudProcedimiento/lista_solicitudes_procedimiento_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProcedimientosDePaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudProcedimiento/lista_procedimientos_de_paciente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProcedimientoAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudProcedimiento/lista_procedimiento_de_especialidad_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarSolicitudProcedimiento (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudProcedimiento/registrar_solicitud_procedimiento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarCantidadSolicitudProcedimiento (datos) { 
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudProcedimiento/editar_cantidad_solicitud_procedimiento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarSolicitudProcedimiento (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"solicitudProcedimiento/anular_solicitud_procedimiento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });