angular.module('theme.categoriaPersonalSalud', ['theme.core.services'])
  .controller('categoriaPersonalController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'hotkeys','pinesNotifications', 
    'categoriaPersonalSaludServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, hotkeys, pinesNotifications,categoriaConsulServices){
    'use strict';


  	}])
  .service("categoriaPersonalSaludServices",function($http, $q) {
    return({
        sListarCategoriaPersonalSaludCbo: sListarCategoriaPersonalSaludCbo,
    });
    

    function sListarCategoriaPersonalSaludCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaPersonalSalud/lista_categoria_personal_salud_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }   

  }); 