angular.module('theme.recetaMedica', ['theme.core.services'])
  .controller('recetaMedicaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys'
    ,'recetaMedica',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      recetaMedica ){
    'use strict';
    
  }])
  .service("recetaMedicaServices",function($http, $q) {
    return({
      sListarRecetaPorId: sListarRecetaPorId,
      sListarRecetasDePaciente: sListarRecetasDePaciente,
      sListarUltimasRecetasDePaciente: sListarUltimasRecetasDePaciente,
      sRegistrarRecetaMedica: sRegistrarRecetaMedica,
      sEliminarMedicamentoDeReceta: sEliminarMedicamentoDeReceta,
    });
    function sListarRecetaPorId(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"recetaMedica/lista_receta_por_id", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarRecetasDePaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"recetaMedica/lista_recetas_de_paciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarUltimasRecetasDePaciente(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"recetaMedica/lista_ultimas_recetas_de_paciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarRecetaMedica (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"recetaMedica/registrar_receta_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarMedicamentoDeReceta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"recetaMedica/anular_receta_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });