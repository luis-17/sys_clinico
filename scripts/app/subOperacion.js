angular.module('theme.subOperacion', ['theme.core.services'])
  .controller('subOperacionController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'subOperacionServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , subOperacionServices
      ){
    'use strict';

  }])
  .service("subOperacionServices",function($http, $q) { 
    return({
        sListarSubOperacionesDeOp: sListarSubOperacionesDeOp
    });

    function sListarSubOperacionesDeOp(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Suboperacion/lista_sub_operaciones_de_op_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });