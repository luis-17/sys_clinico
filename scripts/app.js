angular
  .module('themesApp', [
    'theme',
    'theme.demos',
  ])
  .config(['$provide', '$routeProvider', function($provide, $routeProvider) {
    'use strict';
    console.log($routeProvider); 
    $routeProvider
      .when('/', {
        templateUrl: angular.dirViews+'inicio.php'
      })
      .when('/:templateFile', { 
        templateUrl: function(param) { 
          return angular.dirViews + param.templateFile + '.php';
        },
        hotkeys: [
          ['a', 'Sort by price', 'sort(price)']
        ]
      })
      .when('/:templateFile/:modulo', { 
        templateUrl: function(param) { 
          return angular.dirViews + param.templateFile + '.php';
        },
        hotkeys: [
          ['a', 'Sort by price', 'sort(price)']
        ]
      })
      .when('#', {
        templateUrl: angular.dirViews+'inicio.php',
      })
      .otherwise({
        redirectTo: '/'
      });
  }])
  .directive('demoOptions', function () {
    return {
      restrict: 'C',
      link: function (scope, element, attr) {
        element.find('.demo-options-icon').click( function () { 
          //element.toggleClass('active');
          $('.demo-options.content').toggleClass('active');
          console.log($('.demo-options.content'));
        });
      }
    };
  })