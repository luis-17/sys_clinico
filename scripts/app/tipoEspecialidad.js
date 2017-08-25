angular.module('theme.tipoEspecialidad', ['theme.core.services'])
  .controller('tipoEspecialidadController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'tipoEspecialidadServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , tipoEspecialidadServices
      ){
    'use strict';
  }])
  .service("tipoEspecialidadServices",function($http, $q) {
    return({
        sListarTipoEspecialidadCbo : sListarTipoEspecialidadCbo
    });

    function sListarTipoEspecialidadCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"tipoEspecialidad/lista_tipo_especialidad_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });