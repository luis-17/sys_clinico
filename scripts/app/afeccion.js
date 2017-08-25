angular.module('theme.afeccion', ['theme.core.services'])
  .controller('afeccionController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys'
    ,'afeccion',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      afeccion ){
    'use strict';
    
  }])
  .service("afeccionServices",function($http, $q) {
    return({
      sListarAfeccionesDePaciente: sListarAfeccionesDePaciente,
      sRegistrarAfeccion : sRegistrarAfeccion,
      sRegistrarAfeccionEdit : sRegistrarAfeccionEdit,
      sAnularAfeccion : sAnularAfeccion
    });

    function sListarAfeccionesDePaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"afeccion/lista_afecciones_de_paciente",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarAfeccion(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"afeccion/registrar_afeccion",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarAfeccionEdit(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"afeccion/registrar_afeccion_edicion",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularAfeccion(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"afeccion/anular_afeccion",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });