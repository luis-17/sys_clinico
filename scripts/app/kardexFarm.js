angular.module('theme.kardexFarm', ['theme.core.services'])
  .controller('kardexFarmController', ['$scope', '$filter', '$route', '$sce', '$interval', '$modal', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$bootbox',
    'kardexFarmServices',
    'almacenFarmServices',
    function($scope, $filter, $sce, $route, $interval, $modal, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      $bootbox,
      kardexFarmServices,
      almacenFarmServices ){ 
    'use strict';
    //$scope.$parent.reloadPage();
    shortcut.remove("F2");
    
    $scope.fBusqueda = {};
    $scope.fBusqueda.almacen = {};
    $scope.dateUIDesde = {} ;
    $scope.dateUIDesde.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
    $scope.dateUIDesde.datePikerOptions = {
      formatYear: 'yy',
      // startingDay: 1,
      'show-weeks': false
    };
    $scope.dateUIDesde.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIDesde.opened = true;
    };

    $scope.dateUIHasta = {} ;
    $scope.dateUIHasta.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIHasta.format = $scope.dateUIHasta.formats[0]; // formato por defecto
    $scope.dateUIHasta.datePikerOptions = {
      formatYear: 'yy',
      // startingDay: 1,
      'show-weeks': false
    };
    $scope.dateUIHasta.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIHasta.opened = true;
    };
    // $scope.fBusqueda.desde = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
    // var f=moment().format('YYYY-MM-DD');
    // f=moment().subtract('days',30);
    //$scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MMMM-yyyy');
    $scope.fBusqueda.desde = new Date();
    $scope.fBusqueda.hasta = new Date();
    // LISTAR ALMACENES
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { //console.log(rpta);
      $scope.listaAlmacenes = rpta.datos;
      $scope.fBusqueda.almacen = $scope.listaAlmacenes[0];
      // $scope.fBusqueda.idalmacen = $scope.fBusqueda.almacen.id;
      // $scope.fBusqueda.idsedeempresaadmin = $scope.fBusqueda.almacen.idsedeempresaadmin;
      $scope.listarSubAlmacenesAlmacen($scope.fBusqueda.almacen.id);
    });
    // LISTAR SUB-ALMACENES 
    $scope.listarSubAlmacenesAlmacen = function (idalmacen) { 
      var arrParams = {
        'idalmacen': idalmacen
      }
      almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rpta) {  
        $scope.listaSubAlmacen = rpta.datos;
        //$scope.listaSubAlmacen.splice(0,0,{ id : '0', descripcion:'-- Todos --'});
        $scope.fBusqueda.subalmacen = $scope.listaSubAlmacen[0];

        
      });
    }

  }])
  .service("kardexFarmServices",function($http, $q) {
    return({
        sListarTraslados: sListarTraslados,

    });

    function sListarTraslados(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"kardexFarm/lista_traslados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
  });