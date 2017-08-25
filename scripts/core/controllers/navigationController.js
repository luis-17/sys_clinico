// angular.appRoot =
angular
  .module('theme.core.navigation_controller', ['theme.core.services'])
  .controller('NavigationController', ['$scope', '$location', '$routeParams', '$timeout', 'hotkeys', function($scope, $location, $routeParams, $timeout, hotkeys) {
    'use strict';
    
    $scope.$watch('searchQuery', function(newVal, oldVal) {
      var currentPath = '#' + $location.path();
      if (newVal === '') {
        for (var i = $scope.highlightedItems.length - 1; i >= 0; i--) {
          if ($scope.selectedItems.indexOf($scope.highlightedItems[i]) < 0) {
            if ($scope.highlightedItems[i] && $scope.highlightedItems[i] !== currentPath) {
              $scope.highlightedItems[i].selected = false;
            }
          }
        }
        $scope.highlightedItems = [];
      } else
      if (newVal !== oldVal) {
        for (var j = $scope.highlightedItems.length - 1; j >= 0; j--) {
          if ($scope.selectedItems.indexOf($scope.highlightedItems[j]) < 0) {
            $scope.highlightedItems[j].selected = false;
          }
        }
        $scope.highlightedItems = [];
        highlightItems($scope.menu, newVal.toLowerCase());
      }
    });

    /* ================================= */
    /* ATAJOS DE TECLADO NAVEGACION MENU */
    /* ================================= */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+i',
        description: 'Inicio',
        callback: function() {
          $scope.$parent.goToUrl('/');
          $scope.select($scope.menu[1]); //Inicio
        }
      })
      .add({
        combo: 'alt+u',
        description: 'Usuarios',
        callback: function() {
          $scope.$parent.goToUrl('/usuario');
          $scope.select($scope.menu[2].children[0]); //Usuarios
        }
      })
      .add({
        combo: 'alt+g',
        description: 'Grupos',
        callback: function() {
          $scope.$parent.goToUrl('/grupo');
          $scope.select($scope.menu[2].children[1]); //Grupos
        }
      })
      .add({
        combo: 'alt+r',
        description: 'Roles',
        callback: function() {
          $scope.$parent.goToUrl('/rol');
          $scope.select($scope.menu[2].children[2]); //Roles
        }
      })
      .add({
        combo: 'alt+e',
        description: 'Empleados',
        callback: function() {
          $scope.$parent.goToUrl('/cliente');
          $scope.select($scope.menu[3].children[0]); //Empleados
        }
      })
      .add({
        combo: 'alt+c',
        description: 'Clientes',
        callback: function() {
          $scope.$parent.goToUrl('/cliente');
          $scope.select($scope.menu[4].children[0]); //Clientes
        }
      })
      .add({
        combo: 'alt+b',
        description: 'Nueva Venta',
        callback: function() {
          $scope.$parent.goToUrl('/nueva-venta');
          $scope.select($scope.menu[6].children[0]); //Clientes
        }
      });
  }]);