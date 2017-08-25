angular.module('theme.pariente', ['theme.core.services'])
  .controller('parienteController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'parienteServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , parienteServices
      ){
    'use strict';
  }])
  .service("parienteServices",function($http, $q) {
    return({
        sListarParientes: sListarParientes,
        sAgregarPariente: sAgregarPariente,
        sEditarPariente: sEditarPariente,
        sAnularPariente: sAnularPariente
    });
    function sListarParientes(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Pariente/lista_parientes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarPariente (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Pariente/agregar_pariente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarPariente (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Pariente/editar_pariente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularPariente (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Pariente/anular_pariente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });