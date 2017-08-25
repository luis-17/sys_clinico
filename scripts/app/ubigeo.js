angular.module('theme.ubigeo', ['theme.core.services'])
  .controller('ubigeoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'ubigeoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , ubigeoServices
      ){
    'use strict';
  }])
  .service("ubigeoServices",function($http, $q) {
    return({
        sListarDepartamentos: sListarDepartamentos,
        sListarDepartamentoPorCodigo: sListarDepartamentoPorCodigo,
        sListarProvinciasDeDepartamento: sListarProvinciasDeDepartamento,
        sListarProvinciaDeDepartamentoPorCodigo: sListarProvinciaDeDepartamentoPorCodigo,
        sListarDistritosDeProvincia: sListarDistritosDeProvincia,
        sListarDistritosDeProvinciaPorCodigo: sListarDistritosDeProvinciaPorCodigo,
        sListarDepartamentoPorAutocompletado: sListarDepartamentoPorAutocompletado,
        sListarProvinciaPorAutocompletado: sListarProvinciaPorAutocompletado,
        sListarDistritoPorAutocompletado: sListarDistritoPorAutocompletado
    });

    function sListarDepartamentos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_departamentos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDepartamentoPorCodigo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_departamento_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProvinciasDeDepartamento(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_provincias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProvinciaDeDepartamentoPorCodigo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_provincia_departamento_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDistritosDeProvincia (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_distritos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDistritosDeProvinciaPorCodigo (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_distrito_provincia_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDepartamentoPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_dptos_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProvinciaPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_prov_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDistritoPorAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ubigeo/lista_distr_por_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });