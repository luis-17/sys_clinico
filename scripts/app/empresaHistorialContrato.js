angular.module('theme.empresaHistorialContrato', ['theme.core.services'])
  .controller('empresaHistorialContratoController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'hotkeys','pinesNotifications', 'blockUI',
    'empresaHistorialContratoServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, hotkeys, pinesNotifications,blockUI,categoriaConsulServices){
    'use strict';


  	}])
  .service("empresaHistorialContratoServices",function($http, $q) {
    return({
        sListarContratos: sListarContratos,
        sAgregarContrato: sAgregarContrato,
        sEditarContrato: sEditarContrato,
        sAnularContrato: sAnularContrato,
        sSubirArchivoContrato: sSubirArchivoContrato,
        sQuitarArchivoContrato: sQuitarArchivoContrato,
        sAgregarAdenda: sAgregarAdenda,
        sEditarAdenda: sEditarAdenda,
        sAnularAdenda: sAnularAdenda,
        sListarAdendasContratos: sListarAdendasContratos
    });    
    function sListarContratos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/lista_historial_contratos_linea", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarContrato(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/agregar_contrato_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sEditarContrato(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/editar_contrato_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }     
    function sAnularContrato(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/anular_contrato_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }   
    function sSubirArchivoContrato (pDatos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/subir_archivo_contrato", 
            data : pDatos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarArchivoContrato (pDatos) {
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/quitar_archivo_contrato", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAgregarAdenda(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/agregar_adenda_contrato_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sEditarAdenda(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/editar_adenda_contrato_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }     
    function sAnularAdenda(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/anular_adenda_contrato_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sListarAdendasContratos(pDatos){
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"EmpresaHistorialContrato/lista_historial_adendas_contratos_linea", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));      
    }   

  }); 