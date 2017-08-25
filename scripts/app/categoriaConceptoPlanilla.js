angular.module('theme.categoriaConceptoPlanilla', ['theme.core.services'])
  .controller('categoriaConceptoPlanillaController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'conceptoPlanillaServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , conceptoPlanillaServices
      ){
    'use strict';

  }])
  .service("categoriaConceptoPlanillaServices",function($http, $q) { 
    return({
      sListarCategoriaConceptosCbo:sListarCategoriaConceptosCbo,
    });

    function sListarCategoriaConceptosCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConceptoPlanilla/lista_categoria_concepto_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });