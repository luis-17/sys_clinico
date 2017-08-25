angular.module('theme.tipoDocumentoRRHH', ['theme.core.services'])
  .controller('tipoDocumentoRRHHController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'tipoDocumentoRRHHServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , tipoDocumentoRRHHServices
      ){
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'tipoDocumentoRRHH';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null
    };
  }])

  .service("tipoDocumentoRRHHServices",function($http, $q) {
    return({
        sListarTipoDocumento: sListarTipoDocumento,
    });
     
    function sListarTipoDocumento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_tipo_documento", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });