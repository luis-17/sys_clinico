angular.module('theme.asientoContable', ['theme.core.services'])
  .controller('asientoContableController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'asientoContableServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , asientoContableServices
      ){
    'use strict';
  }])
  .service("asientoContableServices",function($http, $q) {
    return({
        sListarAsientoContableEngreso: sListarAsientoContableEngreso,
        sListarAsientoContablePlanilla: sListarAsientoContablePlanilla
    });

    function sListarAsientoContableEngreso(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"asientoContable/listar_asiento_contable_egreso", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAsientoContablePlanilla(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"asientoContable/listar_asiento_contable_planilla", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });