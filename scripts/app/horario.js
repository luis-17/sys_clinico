angular.module('theme.horario', ['theme.core.services'])
  .controller('horarioController', function($scope, $theme, horarioServices ){
    //'use strict';
    shortcut.remove("F2"); 
    
  })
  .service("horarioServices",function($http, $q) {
    return({
        sListarHorariosCbo: sListarHorariosCbo
    });

    function sListarHorariosCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"horario/lista_horario_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });