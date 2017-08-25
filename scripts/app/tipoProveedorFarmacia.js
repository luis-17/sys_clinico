angular.module('theme.tipoProveedorFarmacia', ['theme.core.services'])
  .controller('tipoProveedorFarmaciaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'tipoProveedorFarmaciaServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, tipoProveedorFarmaciaServices ){
    'use strict';

  }])
  .service("tipoProveedorFarmaciaServices",function($http, $q) {
    return({
      sListarTipoProveedorCbo: sListarTipoProveedorCbo
    });
    function sListarTipoProveedorCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"tipoProveedorFarmacia/lista_tipo_proveedor_farmacia_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });