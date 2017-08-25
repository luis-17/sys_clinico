angular.module('theme.historialContrato', ['theme.core.services'])
  .controller('historialContratoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'historialContratoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , cargoServices
      ){
    'use strict';

  }])
  .service("historialContratoServices",function($http, $q) {
    return({
      sListarContratosDeEmpleado: sListarContratosDeEmpleado,
      sListarHistorialContratosLinea: sListarHistorialContratosLinea,
      sAgregarContratoDeEmpleado: sAgregarContratoDeEmpleado,
      sSubirArchivoContrato: sSubirArchivoContrato,
      sQuitarDocumentoContrato: sQuitarDocumentoContrato,
      sEditarContrato: sEditarContrato,
      sAnularContrato: sAnularContrato
    });

    function sListarContratosDeEmpleado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"HistorialContrato/lista_historial_contratos", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarHistorialContratosLinea (pDatos) {
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"HistorialContrato/lista_historial_contratos_linea", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarContratoDeEmpleado (pDatos) {
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"HistorialContrato/agregar_contrato", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sSubirArchivoContrato (pDatos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialContrato/subir_archivo_contrato", 
            data : pDatos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarDocumentoContrato (pDatos) {
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"HistorialContrato/quitar_archivo_contrato", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarContrato (pDatos) {
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"HistorialContrato/editar_contrato", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularContrato (pDatos) {
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"HistorialContrato/anular", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });