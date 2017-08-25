angular.module('theme.areaEmpresa', ['theme.core.services'])
  .controller('areaEmpresaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'areaEmpresaServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , areaEmpresaServices
      ){
    'use strict';
  }])
  .service("areaEmpresaServices",function($http, $q) {
    return({
        sListarAreaEmpresaCbo: sListarAreaEmpresaCbo
    });

    function sListarAreaEmpresaCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"AreaEmpresa/lista_area_empresa_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });