angular.module('theme.cronJob', ['theme.core.services'])
  .controller('cronJobController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'cronJobServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , cronJobServices
      ){
    'use strict';
  }])
  .service("cronJobServices",function($http, $q) { 
    return({
        sMarcarAsistenciaHuellero: sMarcarAsistenciaHuellero
    });

    function sMarcarAsistenciaHuellero(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EnvioCorreoCronJob/marcar_asistencia_desde_huellero_master", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });