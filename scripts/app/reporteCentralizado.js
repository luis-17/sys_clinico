angular.module('theme.reporteCentralizado', ['theme.core.services'])
  .controller('reporteCentralizadoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'reporteCentralizadoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , reporteCentralizadoServices
      ){
    'use strict';
  }])
  .service("reporteCentralizadoServices",function($http, $q) {
    return({
        listarReportesDelUsuarioSession: listarReportesDelUsuarioSession,
        sListarReportesNoAgregadosAUsuario: sListarReportesNoAgregadosAUsuario,
        sListarReportesAgregadosAUsuario: sListarReportesAgregadosAUsuario,
        sAgregarReporte: sAgregarReporte,
        sQuitarReporte: sQuitarReporte
    });

    function listarReportesDelUsuarioSession(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/lista_reporte_de_usuario_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarReportesNoAgregadosAUsuario(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/lista_reportes_no_agregados_a_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarReportesAgregadosAUsuario(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/lista_reportes_agregados_a_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarReporte (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/agregar_reporte_a_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarReporte (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Usuario/quitar_reporte_a_usuario", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });