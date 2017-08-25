angular.module('theme.guiaRemision', ['theme.core.services'])
  .controller('guiaRemisionController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'guiaRemisionServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , guiaRemisionServices
      ){
    'use strict';
  }])
  .service("guiaRemisionServices",function($http, $q) {
    return({
        sGenerarNumeroSerie: sGenerarNumeroSerie,
        sListarTrasladosParaGuiaLIMIT: sListarTrasladosParaGuiaLIMIT,
        sListarGuiasRemision: sListarGuiasRemision,
        sListarNumeroSerie: sListarNumeroSerie,
        sListarDetalleGuia: sListarDetalleGuia,
        sConsultarGuiaRemision: sConsultarGuiaRemision,
        sConsultarItemsParaGuiaRemision: sConsultarItemsParaGuiaRemision,
        sImprimirGuiaRemision: sImprimirGuiaRemision,
        sCantidadItemsGuias: sCantidadItemsGuias, 
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular
    });

    function sGenerarNumeroSerie(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/generar_numero_serie", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarTrasladosParaGuiaLIMIT(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/listar_traslado_guia_limite", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarGuiasRemision(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/lista_guias_remision", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarNumeroSerie(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/lista_numero_serie", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleGuia(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/listar_detalle_guia", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sConsultarGuiaRemision(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/consultar_guia_remision", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sConsultarItemsParaGuiaRemision(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/lista_items_detalle_traslados", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sImprimirGuiaRemision (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/imprimir_guia_remision",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCantidadItemsGuias(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/cantidad_items_guias", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/registrar", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/editar", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular(pDatos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"GuiaRemision/anular", 
            data : pDatos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });