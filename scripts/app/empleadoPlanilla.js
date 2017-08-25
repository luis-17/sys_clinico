angular.module('theme.empleadoPlanilla', ['theme.core.services'])
  .controller('empleadoPlanillaController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'empleadoPlanillaServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , empleadoPlanillaServices
      ){
    'use strict';
  
    

  }])
  .service("empleadoPlanillaServices",function($http, $q) {
    return({
        sListarEmpleadosPlanilla: sListarEmpleadosPlanilla,
    });
    function sListarEmpleadosPlanilla(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"EmpleadoPlanilla/lista_empleados_planilla", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    
  });
  