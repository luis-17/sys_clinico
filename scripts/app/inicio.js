angular.module('theme.inicio', ['theme.core.services'])
  .controller('inicioController', function($scope, $theme, $filter
    ,inicioServices
    ,avisoServices
    ,documentoInternoServices
    ,empleadoServices ){
      'use strict';
      shortcut.remove("F2"); 
      $scope.modulo = 'inicio'; 
      $scope.arrays = {};
      $scope.fDataFiltro = {};
      $scope.arrays.listaAvisos = [];
      $scope.arrays.listaCumpleaneros = [];
      $scope.arrays.listaTelefonica = [];
      $scope.arrays.listaDocumentosInterno = [];
      $scope.listarAvisos = function () {
        avisoServices.sListarAvisosIntranet().then(function (rpta) {
          $scope.arrays.listaAvisos = rpta.datos;
          //console.log($scope.arrays.listaAvisos);
        });
      }
      $scope.listarAvisos();
      $scope.listaMeses = [
        { 'id': 1, 'mes': 'Enero' },
        { 'id': 2, 'mes': 'Febrero' },
        { 'id': 3, 'mes': 'Marzo' },
        { 'id': 4, 'mes': 'Abril' },
        { 'id': 5, 'mes': 'Mayo' },
        { 'id': 6, 'mes': 'Junio' },
        { 'id': 7, 'mes': 'Julio' },
        { 'id': 8, 'mes': 'Agosto' },
        { 'id': 9, 'mes': 'Septiembre' },
        { 'id': 10, 'mes': 'Octubre' },
        { 'id': 11, 'mes': 'Noviembre' },
        { 'id': 12, 'mes': 'Diciembre' }
      ];
      var mes_actual = $filter('date')(new Date(),'M');
      //console.log(mes_actual);
      $scope.fDataFiltro.mesCbo = $scope.listaMeses[parseInt(mes_actual)-1];
      $scope.listarCumpleanos = function () {
        var paramDatos = {
          mes: $scope.fDataFiltro.mesCbo.id,
        } 
        empleadoServices.sListarCumpleaneros(paramDatos).then(function (rpta) {
          $scope.arrays.listaCumpleaneros = rpta.datos;
        });
      }
      $scope.listarCumpleanos();

      $scope.listarTelefonica = function () {
        empleadoServices.sListarAgendaTelefonica().then(function (rpta) {
          $scope.arrays.listaTelefonica = rpta.datos;
          // console.log($scope.arrays.listaTelefonica = rpta.datos);
        });
      }
      $scope.listarTelefonica();

      $scope.listarDocumentosInternos = function () {
        documentoInternoServices.sListarDocumentosInternoIntranet().then(function (rpta) { 
          $scope.arrays.listaDocumentosInterno = rpta.datos; 
        });
      }
      $scope.listarDocumentosInternos();
  })
  .service("inicioServices",function($http, $q) {
    return({
        //sLoginToSystem: sLoginToSystem
    });

    // function sLoginToSystem(datos) { 
    //   var request = $http({
    //         method : "post",
    //         url : angular.patchURLCI+"acceso/", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }
  });