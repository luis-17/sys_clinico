angular.module('theme.areasOrdenCompra', ['theme.core.services'])
  .controller('areasOrdenCompraController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'areasOrdenCompraServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , areasOrdenCompraServices
      ){
    'use strict';
  }])
  .service("areasOrdenCompraServices",function($http, $q) { 
    return({
        sListarAreasOrdenCompra: sListarAreasOrdenCompra,
        sListarAreasCbo: sListarAreasCbo
    });

    function sListarAreasOrdenCompra(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"AreasOrdenCompra/lista_areas_oc", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAreasCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"AreasOrdenCompra/lista_areas_oc_cbo", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });