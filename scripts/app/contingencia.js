angular.module('theme.contingencia', ['theme.core.services'])
  .controller('contingenciaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys'
    ,'contingenciaServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      contingenciaServices ){
    'use strict';
    
  }])
  .service("contingenciaServices",function($http, $q) {
    return({
      sListarContingencia: sListarContingencia,
      sRegistrarContingencia: sRegistrarContingencia,
      sListarContingenciaPorAutocompletado : sListarContingenciaPorAutocompletado,
      sListarContingenciaCbo: sListarContingenciaCbo
    });

    function sListarContingencia(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"contingencia/lista_contingencia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarContingencia (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"contingencia/registrar_contingencia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarContingenciaPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"contingencia/lista_contingencia_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarContingenciaCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"contingencia/lista_contingencia_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });