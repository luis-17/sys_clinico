angular.patchURL = dirWebRoot;
angular.patchURLCI = dirWebRoot+'ci.php/';
angular.dirViews = angular.patchURL+'/application/views/';
function handleError( response ) {
    if ( ! angular.isObject( response.data ) || ! response.data.message ) {
        return( $q.reject( "An unknown error occurred." ) );
    }
    return( $q.reject( response.data.message ) );
}
function handleSuccess( response ) {
    return( response.data );
}
function redondear(num, decimal){
  var decimal = decimal || 2;
  if (isNaN(num) || num === 0){
    return parseFloat(0);
  }
  var factor = Math.pow(10,decimal);
  return Math.round(num * factor ) / factor;
}

function newNotificacion(body,icon,title,tag) {
  var options = {
      body: body,
      icon: icon,
      tag: tag
  }

  var n = new Notification(title,options); 
  //console.log('se creo', n); 
}

/*comentado hasta ver la forma q se pueda acceder en las vistas*/
/*function numberFormat(monto, decimales){

  monto += ''; // por si pasan un numero en vez de un string
  monto = parseFloat(monto.replace(/[^0-9\.\-]/g, '')); // elimino cualquier cosa que no sea numero o punto
  decimales = decimales || 0; // por si la variable no fue pasada
  // si no es un numero o es igual a cero retorno el mismo cero
  if (isNaN(monto) || monto === 0) 
      return parseFloat(0).toFixed(decimales);
  // si es mayor o menor que cero retorno el valor formateado como numero
  monto = '' + monto.toFixed(decimales);
  var monto_partes = monto.split('.'),
      regexp = /(\d+)(\d{3})/;
  while (regexp.test(monto_partes[0]))
      monto_partes[0] = monto_partes[0].replace(regexp, '$1' + ',' + '$2');
  return monto_partes.join('.');
}*/
appRoot = angular.module('theme.core.main_controller', ['theme.core.services', 'blockUI'])
  .controller('MainController', ['$scope', '$route', '$uibModal', '$document', '$theme', '$timeout',
   'progressLoader', 'wijetsService', '$routeParams', '$location','$controller','rootServices', 'usuarioServices', 
   'blockUI', 'uiGridConstants', 'pinesNotifications','cronJobServices','controlEventoServices','progMedicoServices',
   'grupoServices','centroCostoServices', 'empleadoServices', 'cargoServices', 'empresaAdminServices',
    function($scope, $route, $uibModal, $document, $theme, $timeout, progressLoader, wijetsService, $routeParams, $location, 
    $controller,rootServices, usuarioServices, blockUI, uiGridConstants, pinesNotifications, cronJobServices, controlEventoServices,
    progMedicoServices, grupoServices, centroCostoServices, empleadoServices, cargoServices, empresaAdminServices) {
    //'use strict';

    $scope.fAlert = {};
    $scope.arrMain = {};
    $scope.fSessionCI = {};
    $scope.fSessionCI.listaEspecialidadesSession = [];
    $scope.fSessionCI.listaNotificaciones = {};

    $scope.arrMain.sea = {};
    //$scope.listaEspecialidadesSession = [];
    $scope.localLang = {
      selectAll       : "Seleccione todo",
      selectNone      : "Quitar todo",
      reset           : "Resetear todo",
      search          : "Escriba aquí para buscar...",
      nothingSelected : "No hay items seleccionados"
    };
    $scope.layoutFixedHeader = $theme.get('fixedHeader');
    $scope.layoutPageTransitionStyle = $theme.get('pageTransitionStyle');
    $scope.layoutDropdownTransitionStyle = $theme.get('dropdownTransitionStyle');
    $scope.layoutPageTransitionStyleList = ['bounce',
      'flash',
      'pulse',
      'bounceIn',
      'bounceInDown',
      'bounceInLeft',
      'bounceInRight',
      'bounceInUp',
      'fadeIn',
      'fadeInDown',
      'fadeInDownBig',
      'fadeInLeft',
      'fadeInLeftBig',
      'fadeInRight',
      'fadeInRightBig',
      'fadeInUp',
      'fadeInUpBig',
      'flipInX',
      'flipInY',
      'lightSpeedIn',
      'rotateIn',
      'rotateInDownLeft',
      'rotateInDownRight',
      'rotateInUpLeft',
      'rotateInUpRight',
      'rollIn',
      'zoomIn',
      'zoomInDown',
      'zoomInLeft',
      'zoomInRight',
      'zoomInUp'
    ];
    $scope.dirImages = angular.patchURL+'/assets/img/';
    $scope.layoutLoading = true;
    $scope.blockUI = blockUI;
    $scope.getLayoutOption = function(key) {
      return $theme.get(key);
    };

    $scope.setNavbarClass = function(classname, $event) {
      $event.preventDefault();
      $event.stopPropagation();
      $theme.set('topNavThemeClass', classname);
    };

    $scope.setSidebarClass = function(classname, $event) {
      $event.preventDefault();
      $event.stopPropagation();
      $theme.set('sidebarThemeClass', classname);
    };

    $scope.$watch('layoutFixedHeader', function(newVal, oldval) {
      if (newVal === undefined || newVal === oldval) {
        return;
      }
      $theme.set('fixedHeader', newVal);
    });
    $scope.$watch('layoutLayoutBoxed', function(newVal, oldval) {
      if (newVal === undefined || newVal === oldval) {
        return;
      }
      $theme.set('layoutBoxed', newVal);
    });
    $scope.$watch('layoutLayoutHorizontal', function(newVal, oldval) {
      if (newVal === undefined || newVal === oldval) {
        return;
      }
      $theme.set('layoutHorizontal', newVal);
    });
    $scope.$watch('layoutPageTransitionStyle', function(newVal) {
      $theme.set('pageTransitionStyle', newVal);
    });
    $scope.$watch('layoutDropdownTransitionStyle', function(newVal) {
      $theme.set('dropdownTransitionStyle', newVal);
    });
    $scope.$watch('layoutLeftbarCollapsed', function(newVal, oldVal) {
      if (newVal === undefined || newVal === oldVal) {
        return;
      }
      $theme.set('leftbarCollapsed', newVal);
    });
    //$theme.set('leftbarCollapsed', false);
    $scope.toggleLeftBar = function() {
      $theme.set('leftbarCollapsed', !$theme.get('leftbarCollapsed'));
    };

    $scope.$on('themeEvent:maxWidth767', function(event, newVal) {
      $timeout(function() {
          $theme.set('leftbarCollapsed', newVal);
      });
    });
    $scope.$on('themeEvent:changed:fixedHeader', function(event, newVal) {
      $scope.layoutFixedHeader = newVal;
    });
    $scope.$on('themeEvent:changed:layoutHorizontal', function(event, newVal) {
      $scope.layoutLayoutHorizontal = newVal;
    });
    $scope.$on('themeEvent:changed:layoutBoxed', function(event, newVal) {
      $scope.layoutLayoutBoxed = newVal;
    });
    $scope.$on('themeEvent:changed:leftbarCollapsed', function(event, newVal) {
      $scope.layoutLeftbarCollapsed = newVal;
    });

    $scope.toggleSearchBar = function($event) {
      $event.stopPropagation();
      $event.preventDefault();
      $theme.set('showSmallSearchBar', !$theme.get('showSmallSearchBar'));
    };
    $scope.isLoggedIn = false;
    $scope.logOut = function() {
      $scope.isLoggedIn = false;
    };
    $scope.logIn = function() {
      $scope.isLoggedIn = true;
    };
    $scope.$on('$routeChangeStart', function() {
      rootServices.sGetSessionCI().then(function (response) {
        if(response.flag == 1){
          if ($location.path() === '') {
            return $location.path('/');
          }
        }else{
          $scope.goToUrl('/login');
        }
      });
      progressLoader.start();
      progressLoader.set(50);
    });
    $scope.$on('$routeChangeSuccess', function() {
      progressLoader.end();
      if ($scope.layoutLoading) {
        $scope.layoutLoading = false;
      }
      wijetsService.make();
    });

    $scope.dateUI = {} ;
    $scope.dateUI.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUI.format = $scope.dateUI.formats[0]; // formato por defecto
    $scope.dateUI.datePikerOptions = {
      formatYear: 'yy',
      format: 'dd-MMMM-yyyy',
      startingDay: 1,
      'show-weeks': false
    };
    $scope.dateUI.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUI.opened = true;
    };
    $scope.closeAlert = function () {
      $scope.fAlert = {};
    }
    $scope.alerts = [];
    $scope.goToUrl = function ( path ) {
      $location.path( path );
      //console.log(path);
    };
    $scope.btnLogoutToSystem = function () {
      rootServices.sLogoutSessionCI().then(function () {
        $scope.fSessionCI = {};
        $scope.listaUnidadesNegocio = {};
        $scope.listaModulos = {};
        $scope.logOut();
        $scope.goToUrl('/login');
      });
    } 
    
    $scope.getValidateSession = function (idEmpresaMedico) {
      var idEmpresaMedico = idEmpresaMedico || null;
      rootServices.sGetSessionCI().then(function (response) {

        if(response.flag == 1){
          // CARGAR ESPECIALIDADES SI ES MÉDICO
          $scope.fSessionCI = response.datos;
          console.log($scope.fSessionCI.key_group);
          if(idEmpresaMedico === null){
            // SI ES USUARIO SALUD CARGAMOS ESPECIALIDADES
            if( $scope.fSessionCI.key_group === 'key_salud' || 
                $scope.fSessionCI.key_group == 'key_dir_esp' || 
                $scope.fSessionCI.key_group == 'key_lab' || 
                $scope.fSessionCI.key_group == 'key_salud_caja' || 
                $scope.fSessionCI.key_group == 'key_salud_ocup' || 
                $scope.fSessionCI.key_group == 'key_coord_salud' || 
                $scope.fSessionCI.key_group == 'key_dir_salud' 
              ) { 
              // var arrData = {
              //   'iduser' : $scope.fSessionCI.idusers,
              //   'idmedico' : $scope.fSessionCI.idmedico,
              // };
              // console.log(idEmpresaMedico,'idEmpresaMedico');
              $scope.getListaEmpresasAdminMatrizSession(); 
              setTimeout(function(argument) {
                rootServices.sListarEspecialidadesSession().then(function (rpta) {
                  $scope.fSessionCI.listaEspecialidadesSession = rpta.datos;
                  var lista = $scope.fSessionCI.listaEspecialidadesSession;
                  for (var i = lista.length - 1; i >= 0; i--) {
                    if(lista[i].idempresaespecialidad == $scope.fSessionCI.idempresaespecialidad){
                      $scope.fSessionCI.idempresamedico = lista[i].id;
                      $scope.fSessionCI.empresa = lista[i].empresa;
                      $scope.fSessionCI.idempresa = lista[i].idempresa;
                      // console.log('session', $scope.fSessionCI.empresa);
                      break;
                    }
                  };
                });
              },500); 
              
              
            }else{
              $scope.getListaSedes();
            }
          }else{
            $scope.fSessionCI.idempresamedico = idEmpresaMedico;
          }
          $scope.logIn();
          
          //$scope.getMenu();
          $scope.getMenuFavorito();
          $scope.getUnidadesNegocios();
          $scope.getModulos();
          $scope.getRolesExternos();

          $scope.getNotificaciones();
          $scope.getNotificacionesEventos(true);
          if( $scope.fSessionCI.key_group === 'key_rrhh' || $scope.fSessionCI.key_group === 'key_rrhh_asistente'){
            $scope.getNotificacionesColegiatura();
            $scope.getNotificacionesContrato();
          }
          
          $scope.ejecutarSegundoPlano();
          if( $location.path() == '/login' ){
            $scope.goToUrl('/');
          }
          // console.log($scope.fSessionCI);
        }else{
          $scope.fSessionCI = {};
          $scope.logOut();
          $scope.goToUrl('/login');
        }
      });
    }

    $scope.getListaSedes = function(){ 
      
      //$scope.mostrarEmpresaAdmin = false;
      setTimeout(function () {
        $scope.mostrarSede = false;
        $scope.mostrarSedeEmpresa = false;
        $scope.mostrarEmpresaEspecialidad = false;
        $scope.mostrarEmpresaAdmin = false;
        rootServices.sListarSedeEmpresaAdminSession().then(function (rpta) { 
          $scope.listaSedeEmpresaAdminSession = rpta.datos; 
          /*  
            LÓGICA MULTISEDE: 
              1: Sólo verá combo: 
                -sede 
                -empresa tercera.
              2: Sólo verá combo: 
                -sede/empresa_admin.
              3: No verá ningún combo.
              4: Sólo verá combo: 
                -sede/empresa_admin
                -empresa tercera.
          */
          // if($scope.fSessionCI.vista_sede_empresa == 1){ 
          //   $scope.mostrarSede = false; 
          //   $scope.mostrarEmpresaAdmin = true;
          //   $scope.mostrarSedeEmpresa = false;
          //   $scope.mostrarEmpresaEspecialidad = true;
            
          // }
          if($scope.fSessionCI.vista_sede_empresa == 2){ 
            $scope.mostrarSede = false;
            $scope.mostrarSedeEmpresa = true;
            $scope.mostrarEmpresaEspecialidad = false;
          }
          if($scope.fSessionCI.vista_sede_empresa == 3){ 
            $scope.mostrarSede = false;
            $scope.mostrarSedeEmpresa = false;
            $scope.mostrarEmpresaEspecialidad = false;
          }
          if($scope.fSessionCI.vista_sede_empresa == 4){ 
            $scope.mostrarSede = false;
            $scope.mostrarSedeEmpresa = true;
            $scope.mostrarEmpresaEspecialidad = true;
          }
          var lista = $scope.listaSedeEmpresaAdminSession;
          var maxIndice = lista.length - 1;
          // console.log(lista,$scope.fSessionCI.idsede,'lista,$scope.fSessionCI.idsede'); 
          // return false;
          // if($scope.fSessionCI.vista_sede_empresa == 1){
          //   for (var i = 0 ; i <= maxIndice; i++) {
          //     if(lista[i].idsede == $scope.fSessionCI.idsede){ 
          //       $scope.arrMain.sea = lista[i];  
          //       break;
          //     }
          //   };
          // }else{
          for (var i = 0 ; i <= maxIndice; i++) { 
            if(lista[i].idsedeempresaadmin == $scope.fSessionCI.idsedeempresaadmin){
              $scope.arrMain.sea = lista[i];
              break;
            }
          };
          // }
          
        });
      }, 500); 
    }
    $scope.getListaEmpresasAdminMatrizSession = function() { 
      //console.log('ingrese a getListaEmpresasAdminMatrizSession');
      $scope.mostrarSede = false;
      $scope.mostrarSedeEmpresa = false;
      $scope.mostrarEmpresaEspecialidad = true;
      $scope.mostrarEmpresaAdmin = true;
      setTimeout(function () {
        rootServices.sListarEmpresasAdminMatrizSession().then(function (rpta) { 
          //console.log('ingrese a rootServices.sListarEmpresasAdminMatrizSession');
          $scope.listaEmpresaAdminMatrizSession = rpta.datos; 
          var lista = $scope.listaEmpresaAdminMatrizSession;
          var maxIndice = lista.length - 1;
          for (var i = 0 ; i <= maxIndice; i++) {
            if(lista[i].id_empresa_admin == $scope.fSessionCI.id_empresa_admin){ 
              $scope.arrMain.sea = lista[i]; 
              break;
            }
          };
        });
      }, 500); 
      
    }
    $scope.getNotificaciones = function () {
      rootServices.sListarNotificaciones().then(function (rpta) {
        $scope.fSessionCI.listaNotificaciones.datos = rpta.datos;
        $scope.fSessionCI.listaNotificaciones.contador = rpta.contador;
      });
    } 
    $scope.getNotificacionesColegiatura = function () {
      $scope.fSessionCI.listaNotificacionesColegiatura = {};   
      rootServices.sListarNotificacionesColegiatura().then(function (rpta) {
        console.log(rpta);
        $scope.fSessionCI.listaNotificacionesColegiatura.datos = rpta.datos;
        $scope.fSessionCI.listaNotificacionesColegiatura.contador = rpta.contador;

      });
    }
    $scope.getNotificacionesContrato = function () {
      $scope.fSessionCI.listaNotificacionesContrato = {};
      rootServices.sListarNotificacionesContrato().then(function (rpta) {
        console.log(rpta);
        $scope.fSessionCI.listaNotificacionesContrato.datos = rpta.datos;
        $scope.fSessionCI.listaNotificacionesContrato.contador = rpta.contador;
      });
    }   
    $scope.getNotificacionesEventos = function (firtsTime) {
      $scope.fSessionCI.listaNotificacionesEventos = {};
      rootServices.sListarNotificacionesEventos().then(function (rpta) {
        $scope.fSessionCI.listaNotificacionesEventos.datos = rpta.datos;
        $scope.fSessionCI.listaNotificacionesEventos.noLeidas = rpta.noLeidas;
        $scope.fSessionCI.listaNotificacionesEventos.contador = rpta.contador;
        if(firtsTime && $location.path() == '/'){ 
          console.log('window.Notification',window.Notification);
          console.log('window.mozNotification',window.mozNotification);
          console.log('window.webkitNotifications',window.webkitNotifications);
          console.log('window.notifications', window.notifications);

          var Notificacion = window.Notification || window.mozNotification || window.webkitNotification;
          if(Notificacion){
            if(Notification.permission != 'granted'){
              Notification.requestPermission();
            }

            //notificación por cada no leida
            var title = "Notificación Programación Asistencial";
            var icon = $scope.dirImages +'dinamic/empresa/' + $scope.fSessionCI.nombre_logo;
            angular.forEach( $scope.fSessionCI.listaNotificacionesEventos.noLeidas, function(value, key) {              
              //if(key == 1)
                newNotificacion(value.notificacion,icon,title, value.idcontroleventousuario);
              
              /*instance.onclick = function () {
                // Something to do
              };
              instance.onerror = function () {
                // Something to do
              };
              instance.onshow = function () {
                // Something to do
              };
              instance.onclose = function () {
                // Something to do
              };*/
              //setTimeout(instance.close.bind(instance), 4000);
            });
          }
        }                
      });
    }
    $scope.viewDetalleNotificacionEvento = function (fila){
      controlEventoServices.sUpdateLeidoNotificacion(fila).then(function (rpta) {
        if(rpta.flag == 1){
          $scope.getNotificacionesEventos(false);
          $uibModal.open({
            templateUrl: angular.patchURLCI+'ControlEvento/ver_popup_notificacion_evento',
            size: '',
            backdrop: 'static',
            keyboard:false,
            scope: $scope,
            controller: function ($scope, $modalInstance) { 
              $scope.titleForm = 'DETALLE DE NOTIFICACIÓN';               
              $scope.fData = fila;
              $scope.boolExterno = true;
              $scope.paramModulo = 'root';
              $controller('progMedicoController', { 
                $scope : $scope
              });

              $scope.viewDetalleProgramacion = function (fila){
                var fnCallback = function(){
                  $scope.cancel();
                }
                var view = null;
                if(fila.idtipoevento == 2){
                  view = 'notificacion_anulado';
                }
                var datos = {
                  ids: fila.identificador,
                  reprog: false,
                  view:view
                }
                progMedicoServices.sListarEstasProgramaciones(datos).then(function (rpta) {
                  //fila.idprogmedico = fila.identificador;
                  if(rpta.flag == 1){
                    $scope.btnVerListaPacientes('notificacion', rpta.datos[fila.identificador], fnCallback);
                  }else if(rpta.flag == 2){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    pinesNotifications.notify({ title: pTitle, text: 'No se ha encontrado programación con ese ID', type: pType, delay: 2500 });
                  } else{
                    alert('Error inesperado');
                  }                 
                });                
              }

              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }              
            }
          });
        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
        }else{
          alert('Error inesperado');
        }
      });
    }
    $scope.functionPusher = function (){
      // Enable pusher logging - don't include this in production
      Pusher.log = function(message) {
          if (window.console && window.console.log) {
              window.console.log(message);
          }
      };

      var datos = {
        campo: 'permite_notificacion_pa',
        value: 1
      }
      grupoServices.sListarGruposNotificaciones(datos).then(function(rpta){
        function permiteNotificacion(valor) {
          return $scope.fSessionCI.key_group == valor.key_group;
        }
        var filtered = rpta.datos.filter(permiteNotificacion);
        // console.log(filtered);
        if(filtered.length > 0){
          rootServices.sGetParametrosPusher().then(function(rpta){
            var pusherConfig = rpta.datos;
            //configurando Pusher cliente
            var pusher = new Pusher(pusherConfig.app_key);
            
            //configurando canal
            var channel = pusher.subscribe('test_channel');
            channel.bind('my_event', function(data) {
                //recepcion de evento
                var siPertenece = data.keysgrupos.filter(function(obj) {
                  return obj == $scope.fSessionCI.key_group;
                }).shift(); 

                //console.log(data);
                /*console.log($scope.fSessionCI);*/
                
                //console.log(siPertenece);
                if(siPertenece && data.estado_ce == 1){
                  var title = "Notificación Programación Asistencial";
                  var options = {
                      body: data.texto_notificacion,
                      icon: $scope.dirImages +'dinamic/empresa/' + $scope.fSessionCI.nombre_logo,
                      tag: data.idcontrolevento
                  }

                  new Notification(title,options);
                  $scope.getNotificacionesEventos(false);
                }            
            });           
          }); 
        }
      });
    }
    setTimeout(function() { 
      $scope.functionPusher(); 
    },1500); 

    $scope.getMenuFavorito = function(){
      rootServices.sFavoritos($scope.fSessionCI).then(function (rpta) {
        $scope.menuFavoritos = rpta.datos;
      });
    }
    $scope.boolModulo = false;
    $scope.cambiarModulo =function(){
      console.log('cambiando');
    }
    // unidad de negocio
    $scope.getUnidadesNegocios = function(){
      rootServices.sUnidadesNegocio($scope.fSessionCI).then(function (rpta) {
        $scope.listaUnidadesNegocio = rpta.datos;
        $scope.fSessionCI.idunidadnegocio = $scope.listaUnidadesNegocio[0].id;
        $scope.unidadNegocio = $scope.listaUnidadesNegocio[0].descripcion;
        $scope.unidadnegocio_abrev = $scope.listaUnidadesNegocio[0].abreviatura;
        $scope.getMenuUnidadNegocio();
      });
    }
    $scope.selUnidadNegocio = function(item){
      //console.log('item ',item);
      $scope.boolModulo = false;
      if(item.id != $scope.fSessionCI.idunidadnegocio){
        $scope.fSessionCI.idunidadnegocio = item.id;
        $scope.unidadNegocio = item.descripcion;
        $scope.unidadnegocio_abrev = item.abreviatura
        $scope.getMenuUnidadNegocio();
        
        $scope.goToUrl('/modulo-bienvenida');
      }
    }
    $scope.getMenuUnidadNegocio = function(){
      blockUI.start('Ejecutando proceso...');
      rootServices.sRolesUnidadNegocio($scope.fSessionCI).then(function (rpta){
        $scope.menuUnidadNegocio = rpta.datos;
        var setParent = function(children, parent) {
          angular.forEach(children, function(child) {
            child.parent = parent;
            if (child.children !== undefined) {
              setParent(child.children, child);
            }
          });
        };

        $scope.findItemByUrl = function(children, url) { 
          var children = children || null; 
          if(children){
            for (var i = 0, length = children.length; i < length; i++) {
              if (children[i].url && children[i].url.replace('#', '') === url) {
                return children[i];
              }
              if (children[i].children !== undefined) {
                var item = $scope.findItemByUrl(children[i].children, url);
                if (item) {
                  return item;
                }
              }
            }
          }
          
        };

        setParent($scope.menuUnidadNegocio, null);

        $scope.openItems = []; $scope.selectedItems = []; $scope.selectedFromNavMenu = false;

        $scope.select = function(item) {
          // close open nodes
          if (item.open) {
            item.open = false;
            return;
          }
          for (var i = $scope.openItems.length - 1; i >= 0; i--) {
            $scope.openItems[i].open = false;
          }
          $scope.openItems = [];
          var parentRef = item;
          while (parentRef !== null) {
            parentRef.open = true;
            $scope.openItems.push(parentRef);
            parentRef = parentRef.parent;
          }

          // handle leaf nodes
          if (!item.children || (item.children && item.children.length < 1)) {
            $scope.selectedFromNavMenu = true;
            for (var j = $scope.selectedItems.length - 1; j >= 0; j--) {
              $scope.selectedItems[j].selected = false;
            }
            $scope.selectedItems = [];
            parentRef = item;
            while (parentRef !== null) {
              parentRef.selected = true;
              $scope.selectedItems.push(parentRef);
              parentRef = parentRef.parent;
            }
          }
        };
        blockUI.stop();
      });
      $scope.highlightedItems = [];
      var highlight = function(item) {
        var parentRef = item;
        while (parentRef !== null) {
          if (parentRef.selected) {
            parentRef = null;
            continue;
          }
          parentRef.selected = true;
          $scope.highlightedItems.push(parentRef);
          parentRef = parentRef.parent;
        }
      };
      $scope.findItemByUrl = function(children, url) { 
        var children = children || null; 
        if(children){
          for (var i = 0, length = children.length; i < length; i++) {
            if (children[i].url && children[i].url.replace('#', '') === url) {
              return children[i];
            }
            if (children[i].children !== undefined) {
              var item = $scope.findItemByUrl(children[i].children, url);
              if (item) {
                return item;
              }
            }
          }
        }
      };
      $scope.$on('$routeChangeSuccess', function() {
        if ($scope.selectedFromNavMenu === false) {
          var item = $scope.findItemByUrl($scope.menu, $location.path());
          // var item = [];
          if (item) {
            $timeout(function() {
              $scope.select(item);
            });
          }
        }
        $scope.selectedFromNavMenu = false;
        $scope.searchQuery = '';
      });
    }
    // modulos
    $scope.getModulos = function(){
      rootServices.sModulos($scope.fSessionCI).then(function (rpta) { 
        if( rpta.flag == 1){ 
          $scope.listaModulos = rpta.datos;
          $scope.fSessionCI.idmodulo = $scope.listaModulos[0].id;
          $scope.modulo = $scope.listaModulos[0].descripcion;
          $scope.mod_abrev = $scope.listaModulos[0].abreviatura;
          $scope.getMenu();
        }
        
      });
    }
    $scope.selModulo = function(item){
      //console.log('item ',item);
      $scope.boolModulo = true;
      if(item.id != $scope.fSessionCI.idmodulo){
        $scope.fSessionCI.idmodulo = item.id;
        $scope.modulo = item.descripcion;
        $scope.mod_abrev = item.abreviatura
        $scope.getMenu();
        
        $scope.goToUrl('/modulo-bienvenida');
      }
    }
    $scope.getMenu = function(){
      blockUI.start('Ejecutando proceso...');
      rootServices.sRoles($scope.fSessionCI).then(function (rpta){
        $scope.menu = rpta.datos;
        var setParent = function(children, parent) {
          angular.forEach(children, function(child) {
            child.parent = parent;
            if (child.children !== undefined) {
              setParent(child.children, child);
            }
          });
        };

        $scope.findItemByUrl = function(children, url) {
          for (var i = 0, length = children.length; i < length; i++) {
            if (children[i].url && children[i].url.replace('#', '') === url) {
              return children[i];
            }
            if (children[i].children !== undefined) {
              var item = $scope.findItemByUrl(children[i].children, url);
              if (item) {
                return item;
              }
            }
          }
        };

        setParent($scope.menu, null);

        $scope.openItems = []; $scope.selectedItems = []; $scope.selectedFromNavMenu = false;

        $scope.select = function(item) {
          // close open nodes
          if (item.open) {
            item.open = false;
            return;
          }
          for (var i = $scope.openItems.length - 1; i >= 0; i--) {
            $scope.openItems[i].open = false;
          }
          $scope.openItems = [];
          var parentRef = item;
          while (parentRef !== null) {
            parentRef.open = true;
            $scope.openItems.push(parentRef);
            parentRef = parentRef.parent;
          }

          // handle leaf nodes
          if (!item.children || (item.children && item.children.length < 1)) {
            $scope.selectedFromNavMenu = true;
            for (var j = $scope.selectedItems.length - 1; j >= 0; j--) {
              $scope.selectedItems[j].selected = false;
            }
            $scope.selectedItems = [];
            parentRef = item;
            while (parentRef !== null) {
              parentRef.selected = true;
              $scope.selectedItems.push(parentRef);
              parentRef = parentRef.parent;
            }
          }
        };
        blockUI.stop();
      });
      $scope.highlightedItems = [];
      var highlight = function(item) {
        var parentRef = item;
        while (parentRef !== null) {
          if (parentRef.selected) {
            parentRef = null;
            continue;
          }
          parentRef.selected = true;
          $scope.highlightedItems.push(parentRef);
          parentRef = parentRef.parent;
        }
      };
      $scope.$on('$routeChangeSuccess', function() {
        if ($scope.selectedFromNavMenu === false) {
          var item = $scope.findItemByUrl($scope.menu, $location.path());
          if (item) {
            $timeout(function() {
              $scope.select(item);
            });
          }
        }
        $scope.selectedFromNavMenu = false;
        $scope.searchQuery = '';
      });
    }
    // Roles exiliados
    
    $scope.getRolesExternos = function(){
      // blockUI.start('Ejecutando proceso...');
      rootServices.sRolesExternos($scope.fSessionCI).then(function (rpta){
        $scope.menuExterno = rpta.datos;
        var setParent = function(children, parent) {
          angular.forEach(children, function(child) {
            child.parent = parent;
            if (child.children !== undefined) {
              setParent(child.children, child);
            }
          });
        };

        $scope.findItemByUrl = function(children, url) {
          console.log(children,'children');
          for (var i = 0, length = children.length; i < length; i++) {
            if (children[i].url && children[i].url.replace('#', '') === url) {
              return children[i];
            }
            if (children[i].children !== undefined) {
              var item = $scope.findItemByUrl(children[i].children, url);
              if (item) {
                return item;
              }
            }
          }
        };

        setParent($scope.menuExterno, null);

        $scope.openItems = []; $scope.selectedItems = []; $scope.selectedFromNavMenu = false;

        $scope.select = function(item) {
          // close open nodes
          if (item.open) {
            item.open = false;
            return;
          }
          for (var i = $scope.openItems.length - 1; i >= 0; i--) {
            $scope.openItems[i].open = false;
          }
          $scope.openItems = [];
          var parentRef = item;
          while (parentRef !== null) {
            parentRef.open = true;
            $scope.openItems.push(parentRef);
            parentRef = parentRef.parent;
          }

          // handle leaf nodes
          if (!item.children || (item.children && item.children.length < 1)) {
            $scope.selectedFromNavMenu = true;
            for (var j = $scope.selectedItems.length - 1; j >= 0; j--) {
              $scope.selectedItems[j].selected = false;
            }
            $scope.selectedItems = [];
            parentRef = item;
            while (parentRef !== null) {
              parentRef.selected = true;
              $scope.selectedItems.push(parentRef);
              parentRef = parentRef.parent;
            }
          }
        };
        // blockUI.stop();
      });
      $scope.highlightedItems = [];
      var highlight = function(item) {
        var parentRef = item;
        while (parentRef !== null) {
          if (parentRef.selected) {
            parentRef = null;
            continue;
          }
          parentRef.selected = true;
          $scope.highlightedItems.push(parentRef);
          parentRef = parentRef.parent;
        }
      };
      $scope.$on('$routeChangeSuccess', function() {
        if ($scope.selectedFromNavMenu === false) {
          var item = $scope.findItemByUrl($scope.menu, $location.path());
          if (item) {
            $timeout(function() {
              $scope.select(item);
            });
          }
        }
        $scope.selectedFromNavMenu = false;
        $scope.searchQuery = '';
      });
    }
    $scope.addPageFavorite = function(){
      var paramDatos = {
        url: '#' + $location.path(),
        iduser: $scope.fSessionCI['idusers'],
        idgroup: $scope.fSessionCI['idgroup']
      }
      rootServices.sAgregarAFavoritos(paramDatos).then(function (rpta) {
        if(rpta.flag == 1){
          var pTitle = 'Ok!';
          var pType = 'success';
          $scope.getMenuFavorito();
        }
        else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
        }else{
          alert('Error inesperado');
        }
        //pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    }
    $scope.eliminarFavorito = function(item){
      console.log('Eliminando... ', item);
      rootServices.sEliminarDeFavoritos(item).then(function (rpta) {
        if(rpta.flag == 1){
          var pTitle = 'Ok!';
          var pType = 'success';
          $scope.getMenuFavorito();
        }
        else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';
        }else{
          alert('Error inesperado');
        }
        //pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    }
    
    $scope.onChangeSessionSede = function(){ 
      var arrData = {
        'datos' : $scope.arrMain.sea,
        'session' : $scope.fSessionCI
      }
      blockUI.start('Ejecutando proceso...');
      rootServices.sCambiarSedeSession(arrData).then(function (rpta) {
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
          $scope.getValidateSession();
          $scope.reloadPage();
        }else if(rpta.flag == 0){
          var pTitle = 'Error!';
          var pType = 'warning';
        }else{
          alert('Contacte con el Área de Sistemas');
        }
        blockUI.stop();
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
      });
    }
    $scope.onChangeSessionEspecialidad = function () { 
      var arrData = {
        'idempresamedico' : $scope.fSessionCI.idempresamedico,
        'idsedeempresaadmin' : $scope.fSessionCI.idsedeempresaadmin
      };
      if( $scope.fSessionCI.vista_sede_empresa == 1 ){ 
        arrData.idsede = $scope.fSessionCI.idsede;
      }
      blockUI.start('Ejecutando proceso...');
      rootServices.sRecargarUsuarioSession(arrData).then(function (rpta) {
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
          $scope.getValidateSession(rpta.datos.idempresamedico);

        }else if(rpta.flag == 0){
          var pTitle = 'Advertencia!';
          var pType = 'warning';
          $scope.getValidateSession(rpta.datos.idempresamedico);
        }else{
          alert('Contacte con el Área de Sistemas');
        }
        blockUI.stop();
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
      });
      $scope.reloadPage();
    }
    $scope.getInfoEmpresa = function () {
      rootServices.sGetEmpresaActiva().then(function (response) {
        if(response.flag == 1){
          $scope.fEmpresa = response.datos;
        }
      });
    }
    $scope.reloadPage = function () {
      $route.reload();
    }
    /* ARRAYS GENERALES */
    $scope.listaSexos = [
      { id:'', descripcion:'--Seleccione sexo--' },
      { id:'M', descripcion:'Masculino' },
      { id:'F', descripcion:'Femenino' }
    ];
    $scope.parseCurrency = function(num) {
      var pNum = num;
      return parseFloat( pNum.replace( 'S/. ' , '') );
    }

    /* CAMBIAR CLAVE */
    $scope.btnCambiarMiClave = function (size){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'usuario/ver_popup_password',
        size: size || 'sm',
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'Cambiar Contraseña';
          $scope.aceptar = function (){
            if($scope.fDataUsuario.claveConfirmar != $scope.fDataUsuario.claveNueva){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
              var pMessage = 'Las contraseñas no son iguales';
              $scope.fDataUsuario.claveNueva = null;
              $scope.fDataUsuario.claveConfirmar = null;
              setTimeout(function() {
                $('#nuevoPass').focus();
              }, 500);
              pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 1000 });
              return;
            }else{
              $scope.fDataUsuario.miclave = 'si';
              usuarioServices.sverificaPassword($scope.fDataUsuario).then(function (rpta) {
                //console.log('Rpta: ',rpta);
                if(rpta.flag == 1){
                  var pTitle = 'Ok!';
                  var pType = 'success';
                  $modalInstance.dismiss('cancel');
                  //$scope.getPaginationServerSide();
                }
                else if(rpta.flag == 2){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';
                  $scope.fDataUsuario.clave = null;
                  setTimeout(function() {
                    $('#clave').focus();
                  }, 500);
                }
                else if(rpta.flag == 0){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';

                }else{
                  alert('Error inesperado');
                }

                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              });
            }
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataUsuario = {};
            //$scope.getPaginationServerSide();
          }
        }
      });
    }
    /* VER TIPO DE CAMBIO */
    $scope.btnTipoCambio = function (size){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'CentroCosto/ver_popup_tipo_cambio',
        size: size || 'sm',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'Tipo de Cambio';
          $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
          $scope.fDataCambio = {};
          centroCostoServices.sListarTipoCambio().then(function (rpta) {
            $scope.fDataCambio = rpta.datos;
            $scope.fDataCambio.oldFecha = angular.copy($scope.fDataCambio.fecha_cambio);
            $scope.fDataCambio.oldCompra = angular.copy($scope.fDataCambio.compra);
            $scope.fDataCambio.oldVenta = angular.copy($scope.fDataCambio.venta);
          });
          $scope.aceptar = function (){
            blockUI.start('Ejecutando...');
            centroCostoServices.sRegistrarTipoCambio($scope.fDataCambio).then(function (rpta) {
              blockUI.stop();
              if(rpta.flag == 1){
                var pTitle = 'Ok!';
                var pType = 'success';
                console.log('arrTipoCambio',rpta.datos);
                $scope.fSessionCI.tc_compra = rpta.datos.compra;
                $scope.fSessionCI.tc_venta = rpta.datos.venta;
              }
              else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';

              }else{
                alert('Error inesperado');
              }

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $modalInstance.dismiss('cancel');
            });
            
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataCambio = {};
          }
        }
      });
    }

    /* SERVICIOS EN 2DO PLANO */ 
    $scope.ejecutarSegundoPlano = function () { 
      setTimeout(function () { 
        // console.log($scope.fSessionCI.key_group,'$scope.fSessionCI.key_group');
        if($scope.fSessionCI.key_group == 'key_sistemas' && $scope.fSessionCI.real_time_huella == 1 ){ 
          setInterval(function () {
            //$scope.ejecutarSegundoPlano();
            cronJobServices.sMarcarAsistenciaHuellero().then(function (rpta) {
              // console.log('registrando marcaciones en tiempo real');
            });
          },6000);
        }
      },5000);
    }

    /* ACTUALIZAR COLEGIATURA */
    $scope.btnActualizarNotificacionesColegiatura = function (row){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Configuracion/ver_popup_notificaciones_colegiatura',
        size: 'sm',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          console.log(row);
          $scope.titleForm = 'ACTUALIZAR COLEGIATURA';
          $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
          $scope.fDataAviso = {};
          $scope.fDataAviso = row;
          
          $scope.aceptar = function (){
            console.log($scope.fDataAviso);
            empleadoServices.sActualizarFechaCaducidad($scope.fDataAviso).then(function (rpta) {
              console.log(rpta);
              if(rpta.flag == 1){
                var pTitle = 'Ok!';
                var pType = 'success';
                $scope.getNotificacionesColegiatura();
                $modalInstance.dismiss('cancel');
              }
              else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              
            });           
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataCambio = {};
          }
        }
      });
    }

    /* ACTUALIZAR CONTRATO */
    $scope.btnActualizarNotificacionesContrato = function (row){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Configuracion/ver_popup_notificaciones_contrato',
        size: '',
        scope: $scope,
        controller: function ($scope, $modalInstance) {

          $scope.titleForm = 'ACTUALIZAR CONTRATO';
          $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
          $scope.fDataAviso = {};
          $scope.fDataAviso = row;

          // ** LISTA DE EMPRESAS ADMIN ** 
          empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) {
            $scope.listaEmpresaAdmin = rpta.datos;
            angular.forEach($scope.listaEmpresaAdmin, function (val,key) {
              if( val.id == $scope.fDataAviso.idempresaadmin ){                
                $scope.fDataAviso.empresaadmin = $scope.listaEmpresaAdmin[key];
              }
            });
          });

          $scope.listaCondicionLaboral = [
            { id: 'NONE', descripcion: '--Seleccione Condición Laboral--' },
            { id: 'EN PLANILLA', descripcion: 'EN PLANILLA' },
            { id: 'POR LOCACION DE SERVICIOS', descripcion: 'POR LOCACION DE SERVICIOS' },
            { id: 'PRACTICANTE', descripcion: 'PRACTICANTE' },
            { id: 'OTROS', descripcion: 'OTROS' }
          ];
          $scope.fDataAviso.condicion_laboral = $scope.listaCondicionLaboral[0];

          //=============================================================
          // AUTOCOMPLETADO CARGO - edicion
          //=============================================================
            $scope.getCargoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return cargoServices.sListarCargoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLCargo = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLCargo = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedCargo = function ($item, $model, $label) {
              $scope.fDataAviso.idcargo = $item.id;
            };
            $scope.getClearInputCargo = function () { 
              if(!angular.isObject($scope.fDataAviso.cargo) ){ 
                $scope.fDataAviso.idcargo = null; 
              }
            } 
          
          $scope.aceptar = function (){
            console.log($scope.fDataAviso);
            empleadoServices.sActualizarContrato($scope.fDataAviso).then(function (rpta) {
              console.log(rpta);
              if(rpta.flag == 1){
                var pTitle = 'Ok!';
                var pType = 'success';
                $scope.getNotificacionesContrato();
                $modalInstance.dismiss('cancel');
              }
              else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              
            });           
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataCambio = {};
          }
        }
      });
    }

    /* END */
  }])
  .service("rootServices", function($http, $q) {
    return({
        sGetSessionCI: sGetSessionCI,
        sLogoutSessionCI: sLogoutSessionCI,
        sListarEspecialidadesSession: sListarEspecialidadesSession,
        sListarEmpresasAdminMatrizSession: sListarEmpresasAdminMatrizSession,
        sListarSedeEmpresaAdminSession: sListarSedeEmpresaAdminSession,
        sListarNotificaciones: sListarNotificaciones,
        sListarNotificacionesColegiatura:sListarNotificacionesColegiatura,
        sListarNotificacionesContrato:sListarNotificacionesContrato,
        sListarNotificacionesEventos: sListarNotificacionesEventos,
        sGetEmpresaActiva: sGetEmpresaActiva,
        sGetParametrosPusher:sGetParametrosPusher,
        sRecargarUsuarioSession: sRecargarUsuarioSession,
        sCambiarSedeSession: sCambiarSedeSession,
        sFavoritos: sFavoritos,
        sAgregarAFavoritos: sAgregarAFavoritos,
        sEliminarDeFavoritos: sEliminarDeFavoritos,
        sModulos: sModulos,
        sUnidadesNegocio: sUnidadesNegocio,
        sRoles: sRoles,
        sGraphicData: sGraphicData, 
        sRolesUnidadNegocio: sRolesUnidadNegocio,
        sRolesExternos: sRolesExternos       
    });
    function sGetSessionCI() {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"acceso/getSessionCI"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sLogoutSessionCI() {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"acceso/logoutSessionCI"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"acceso/lista_especialidades_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasAdminMatrizSession(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Acceso/lista_empresa_admin_matriz_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSedeEmpresaAdminSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"acceso/lista_sede_empresa_admin_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarNotificaciones (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/lista_notificaciones",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarNotificacionesColegiatura (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/lista_notificaciones_colegiatura",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarNotificacionesContrato (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/lista_notificaciones_contrato",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarNotificacionesEventos (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/lista_notificaciones_eventos",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGetEmpresaActiva () {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/getEmpresaActiva"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGetParametrosPusher () {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"configuracion/getParametrosPusher"
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRecargarUsuarioSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"acceso/recargar_usuario_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarSedeSession (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"acceso/cambiar_sede_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sFavoritos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/lista_roles_favoritos_usuario",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarAFavoritos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/agregar_rol_a_favoritos",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarDeFavoritos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/eliminar_rol_de_favorito",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sModulos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/lista_modulos_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sUnidadesNegocio(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/lista_unidades_negocio_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRoles(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/lista_roles_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRolesUnidadNegocio(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/lista_roles_unidad_negocio_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRolesExternos(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"rol/lista_roles_externos_session",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sGraphicData (url,datos) {
      var request = $http({
            method : "post",
            url : url,
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });
/* DIRECTIVAS */
appRoot.
  directive('ngEnter', function() {
    return function(scope, element, attrs) {
      element.bind("keydown", function(event) {

          if(event.which === 13) {
            //event.preventDefault();
            scope.$apply(function(){
              scope.$eval(attrs.ngEnter);
            });
            //event.stopPropagation();
          }
          //event.stopPropagation();
          //event.preventDefault();
      });
    };
  })
  .directive('scroller', function() {
    return {
      restrict: 'A',
      link: function(scope,elem,attrs){
          $(elem).on('scroll', function(evt){ 
            // PROGRAMACION DE AMBIENTES 
            $('.planning .sidebar .table').css('margin-top', -$(this).scrollTop());
            $('.planning .header .table').css('margin-left', -$(this).scrollLeft());
            // PROGRAMACION DE MEDICOS 
            $('.planning-medicos .fixed-row').css('margin-left', -$(this).scrollLeft());
            $('.planning-medicos .fixed-column').css('margin-top', -$(this).scrollTop()); 

            $('.planning-medicos .fixed-row .cell-planing.ambiente').css('left', $(this).scrollLeft()); 
            
          });
      }
    }
  })
  .directive('resetscroller', function() {
    return {
      restrict: 'A',
      link: function(scope,elem,attrs){
          $(elem).on('click', function(evt){ 
            // PROGRAMACION DE AMBIENTES 
            $('.planning .sidebar .table').css('margin-top', 0);
            $('.planning .header .table').css('margin-left', 0);
            $('.planning .body').scrollLeft(0);
            $('.planning .body').scrollTop(0);
            // PROGRAMACION DE MEDICOS 
            $('.planning-medicos .fixed-row').css('margin-left', -$(this).scrollLeft());
            $('.planning-medicos .fixed-column').css('margin-top', -$(this).scrollTop()); 

            $('.planning-medicos .fixed-row .cell-planing.ambiente').css('left', $(this).scrollLeft()); 
            
          });
      }
    }
  })
  .directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
          var model = $parse(attrs.fileModel);
          var modelSetter = model.assign;
          element.bind('change', function(){
            scope.$apply(function(){
                modelSetter(scope, element[0].files[0]);
            });
          });
        }
    };
  }])
  .directive('focusMe', function($timeout, $parse) {
    return {
      link: function(scope, element, attrs) {
        var model = $parse(attrs.focusMe);

        scope.$watch(model, function(pValue) {
            value = pValue || 0;
            $timeout(function() {
              element[value].focus();
              // console.log(element[value]);
            });
        });
      }
    };
  })
  .directive('stringToNumber', function() {
    return {
      require: 'ngModel',
      link: function(scope, element, attrs, ngModel) {
        // console.log(scope);
        ngModel.$parsers.push(function(value) {
          // console.log('p '+value);
          return '' + value;
        });
        ngModel.$formatters.push(function(value) {
          // console.log('f '+value);
          return parseFloat(value, 10);
        });
      }
    };
  })
  .directive('enterAsTab', function () {
    return function (scope, element, attrs) {
      element.bind("keydown keypress", function (event) {
        if(event.which === 13 || event.which === 40) {
          event.preventDefault();
          var fields=$(this).parents('form:eq(0),body').find('input, textarea, select');
          var index=fields.index(this);
          if(index > -1 &&(index+1) < (fields.length - 1))
            fields.eq(index+1).focus();
        }
        if(event.which === 38) {
          event.preventDefault();
          var fields=$(this).parents('form:eq(0),body').find('input, textarea, select');
          var index=fields.index(this);
          if((index-1) > -1 && index < fields.length)
            fields.eq(index-1).focus();
        }
      });
    };
  })
  .directive('hcChart', function () {
      return {
          restrict: 'E',
          template: '<div></div>',
          scope: {
              options: '='
          },
          link: function (scope, element) {
            // scope.$watch(function () {
            //   return attrs.chart;
            // }, function () {
            //     if (!attrs.chart) return;
            //     var charts = JSON.parse(attrs.chart);
            //     $(element[0]).highcharts(charts);                
                Highcharts.chart(element[0], scope.options);
            // });

          }
      };
  })
  .directive('smartFloat', function() {
    var FLOAT_REGEXP = /^\-?\d+((\.|\,)\d+)?$/;
    return {
      require: 'ngModel',
      link: function(scope, elm, attrs, ctrl) {
        ctrl.$parsers.unshift(function(viewValue) {
          if (FLOAT_REGEXP.test(viewValue)) {
            ctrl.$setValidity('float', true);
            if(typeof viewValue === "number")
              return viewValue;
            else
              return parseFloat(viewValue.replace(',', '.'));
          } else {
            ctrl.$setValidity('float', false);
            return undefined;
          }
        });
      }
    };
  })
  .config(function(blockUIConfig) {
    blockUIConfig.message = 'Cargando datos...';
    blockUIConfig.delay = 0;
    blockUIConfig.autoBlock = false;
    //i18nService.setCurrentLang('es');
  })
  .filter('getRowSelect', function() {
    return function(arraySelect, item) {
      var fSelected = {};
      angular.forEach(arraySelect,function(val,index) {
        if( val.id == item ){
          fSelected = val;
        }
      })
      return fSelected;
    }
  })
  .filter('numberFixedLen', function () {
    return function (n, len) {
      var num = parseInt(n, 10);
      len = parseInt(len, 10);
      if (isNaN(num) || isNaN(len)) {
        return n;
      }
      num = ''+num;
      while (num.length < len) {
        num = '0'+num;
      }
      return num;
    };
  })
  .filter('griddropdown', function() {
    return function (input, context) {
      var map = context.col.colDef.editDropdownOptionsArray;
      var idField = context.col.colDef.editDropdownIdLabel;
      var valueField = context.col.colDef.editDropdownValueLabel;
      var initial = context.row.entity[context.col.field];
      if (typeof map !== "undefined") {
        for (var i = 0; i < map.length; i++) {
          if (map[i][idField] == input) {
            return map[i][valueField];
          }
        }
      } else if (initial) {
        return initial;
      }
      return input;
    };
  })
  .factory("ModalReporteFactory", function($modal,$http,blockUI,rootServices){
    var interfazReporte = {
      getPopupReporte: function(arrParams){ //console.log(arrParams.datos.salida,' as');
        if( arrParams.datos.salida == 'pdf' || angular.isUndefined(arrParams.datos.salida) ){
          $modal.open({
            templateUrl: angular.patchURLCI+'CentralReportes/ver_popup_reporte',
            size: 'xlg',
            controller: function ($scope,$modalInstance,arrParams) {
              $scope.titleModalReporte = arrParams.titulo;
              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }
              blockUI.start('Preparando reporte');
              $http.post(arrParams.url, arrParams.datos)
                .success(function(data, status) {
                  blockUI.stop();
                  if( arrParams.metodo == 'php' ){
                    $('#frameReporte').attr("src", data.urlTempPDF);
                  //}else if( arrParams.metodo == 'js' ){
                  }else{
                    var docDefinition = data.dataPDF
                    pdfMake.createPdf(docDefinition).getBuffer(function(buffer) {
                      var blob = new Blob([buffer]);
                      var reader = new FileReader();
                      reader.onload = function(event) {
                        var fd = new FormData();
                        fd.append('fname', 'temp.pdf');
                        fd.append('data', event.target.result);
                        $.ajax({
                          type: 'POST',
                          url: angular.patchURLCI+'CentralReportes/guardar_pdf_en_temporal', // Change to PHP filename
                          data: fd,
                          processData: false,
                          contentType: false
                        }).done(function(data) {
                          $('#frameReporte').attr("src", data.urlTempPDF);
                        });
                      };
                      reader.readAsDataURL(blob);
                    });
                  }
                })
                .error(function(data, status){
                  blockUI.stop();
                });
            },
            resolve: {
              arrParams: function() {
                return arrParams;
              }
            }
          });
        }else if( arrParams.datos.salida == 'excel' ){
          blockUI.start('Preparando reporte');
          $http.post(arrParams.url, arrParams.datos)
            .success(function(data, status) {
              blockUI.stop();
              if(data.flag == 1){
                //window.open = arrParams.urlTempEXCEL;
                window.location = data.urlTempEXCEL;
              }
          });
        }
      },
      getPopupGraph: function(arrParams) {
        if( arrParams.datos.tipoCuadro == 'grafico' || arrParams.datos.tiposalida == 'grafico' || angular.isUndefined(arrParams.datos.tipoCuadro) ){
          $modal.open({
            templateUrl: angular.patchURLCI+'CentralReportes/ver_popup_grafico',
            size: 'xlg',
            controller: function ($scope,$modalInstance,arrParams) {
              $scope.metodos = {};
              $scope.titleModalGrafico = arrParams.datos.titulo;
              $scope.metodos.listaColumns = false;
              $scope.metodos.listaData = false;

              $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
              }
              blockUI.start('Preparando gráfico');
              rootServices.sGraphicData(arrParams.url, arrParams.datos).then(function (data) {
                $scope.metodos.chartOptions = arrParams.structureGraphic;
                //console.log(data.series[0]);
                $scope.metodos.chartOptions.chart.events = {
                    load: function () {
                      var thes = this;
                      setTimeout(function () {
                          thes.setSize($("#chartOptions").parent().width(), $("#chartOptions").parent().height());
                      }, 10);
                    }
                  };
                if( data.tipoGraphic == 'line' || data.tipoGraphic == 'bar'){
                  $scope.metodos.chartOptions.xAxis.categories = data.xAxis;
                  $scope.metodos.chartOptions.series = data.series;
                }
                //TRANSICIÓN DE LAS GRÁFICAS DE REPORTES DE ENCUESTA QUE SE ENCUENTRA EN LA INTRANET
                if( data.tipoGraphic == 'pie' ){//
                  var arrData = [];
                  var tamanio = 300;
                  //SE RECORRE data.series PARA OBTENER TODAS LAS PREGUNTAS CON SUS RESPECTIVOS DATOS
                  angular.forEach(data.series, function(value, key) {
                    arrData.push({name: value.descripcion_pr, colorByPoint: true, size: 200, center: [tamanio, null], showInLegend: true, data: data.series[key].respuestas,
                      total: data.series[key].totalPorPie});
                    tamanio = tamanio + 300;                    
                  });
                  //console.log(arrData);
                  $scope.metodos.chartOptions.series = arrData;
                }
                if (data.tipoGraphic == 'line_encuesta'){//EL TIPO DE GRÁFICA PARA ESTE CASO ES ESPECIAL PORQUE
                  var arrData = [];
                  $scope.metodos.chartOptions.xAxis.categories = data.xAxis;
                  $scope.metodos.chartOptions.title.text = (data.series[0].descripcion).toUpperCase();
                  angular.forEach(data.series[0].respuestas , function(value, key) {
                    arrData.push({name: data.series[0].respuestas[key].name, data: data.series[0].respuestas[key].data});
                  });
                  $scope.metodos.chartOptions.series = arrData;
                  //console.log(arrData);
                }                
                if( data.tieneTabla == true ){
                  $scope.metodos.listaColumns = data.columns;
                  $scope.metodos.listaData = data.tablaDatos;
                  $scope.metodos.contTablaDatos = false;
                  $scope.metodos.linkText = 'VER TABLA DE DATOS';
                  $scope.linkVerTablaDatos = function () {
                    if( $scope.metodos.contTablaDatos === true ){
                      $scope.metodos.contTablaDatos = false;
                      $scope.metodos.linkText = 'VER TABLA DE DATOS';
                    }else{
                      $scope.metodos.contTablaDatos = true;
                      $scope.metodos.linkText = 'OCULTAR TABLA DE DATOS';
                    }

                  }
                }
                blockUI.stop();
              });
            },
            resolve: {
              arrParams: function() {
                return arrParams;
              }
            }
          });
        }
      }
    }
    return interfazReporte;
  });

  // Prevent the backspace key from navigating back.
$(document).unbind('keydown').bind('keydown', function (event) {
  var doPrevent = false;
  if (event.keyCode === 8) {
    var d = event.srcElement || event.target;
    if((d.tagName.toUpperCase() === 'INPUT' &&
         (
             d.type.toUpperCase() === 'TEXT' ||
             d.type.toUpperCase() === 'PASSWORD' ||
             d.type.toUpperCase() === 'FILE' ||
             d.type.toUpperCase() === 'SEARCH' ||
             d.type.toUpperCase() === 'EMAIL' ||
             d.type.toUpperCase() === 'NUMBER' ||
             d.type.toUpperCase() === 'TEL' ||
             d.type.toUpperCase() === 'DATE' )
        ) ||
        d.tagName.toUpperCase() === 'TEXTAREA'
    ){
      doPrevent = d.readOnly || d.disabled;
    }
    else {
        doPrevent = true;
    }
  }

  if (doPrevent) {
      event.preventDefault();
  }
});
   