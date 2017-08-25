angular.module('theme.tipoVia', ['theme.core.services'])
  .controller('tipoViaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'tipoViaServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , tipoViaServices
      ){
    'use strict';
  }])
  .service("tipoViaServices",function($http, $q) {
    return({
        sListarTipoViaCbo: sListarTipoViaCbo
    });

    function sListarTipoViaCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TipoVia/lista_tipo_via_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });