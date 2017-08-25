angular.module('theme.ambiente', ['theme.core.services'])
  .controller('ambienteController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'ambienteServices', 'categoriaConsulServices', 'sedeServices',  
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, ambienteServices, categoriaConsulServices, sedeServices ){
    'use strict';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [ //
        { field: 'id', name: 'idambiente', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'numero_ambiente', name: 'numero_ambiente', displayName: 'Número Ambiente' },
        { field: 'piso', name: 'piso', displayName: 'Piso' },
        { field: 'comentario', name: 'comentario', displayName: 'Comentario' },
        { field: 'descripcion_sede', name: 'descripcion_sede', displayName: 'Sede' },
        { field: 'descripcion_cco', name: 'descripcion_cco', displayName: 'Categoría Consultorio' },
        { field: 'descripcion_scco', name: 'descripcion_scco', displayName: 'Subcategoría Consultorio' },
        { field: 'estado_amb', type: 'object', name: 'estado_amb', displayName: 'Estado', maxWidth: 250, enableFiltering: false, enableSorting: false ,
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
      ],

      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide();
        });

        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
            var grid = this.grid;
            paginationOptions.search = true;
            
            paginationOptions.searchColumn = {
              'idambiente' : grid.columns[1].filters[0].term,
              'numero_ambiente' : grid.columns[2].filters[0].term,
              'piso' : grid.columns[3].filters[0].term,
              'comentario' : grid.columns[4].filters[0].term,
              'se.descripcion' : grid.columns[5].filters[0].term,
              'cat.descripcion_cco' : grid.columns[6].filters[0].term,
              'subcat.descripcion_scco' : grid.columns[7].filters[0].term
            } 
            $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions
      };
      ambienteServices.sListarAmbiente($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos; 
        $scope.descripcion_sede = rpta.descripcion_sede;
        $scope.idsede = rpta.idsede;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

     /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'Ambiente/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fDataAdd = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de ambiente';
          console.log($scope.mySelectionGrid[0]);

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataAdd = {};
          }

          $scope.fDataAdd.comentario = $scope.mySelectionGrid[0].comentario;  
          $scope.fDataAdd.sede = $scope.descripcion_sede;      
          $scope.fDataAdd.idsede = $scope.idsede;  
          /*$scope.getCargaSede = function() {
            sedeServices.sListarSedeCbo().then(function (rpta) {
              $scope.listaSede = rpta.datos;
              $scope.listaSede.splice(0,0,{ id : '0', descripcion:'--Seleccione sede --'});
              $scope.fDataAdd.sede = $scope.mySelectionGrid[0].idsede;    
            });
          }
          $scope.getCargaSede();*/

          $scope.getCargaCategoriaConsul = function() {
            categoriaConsulServices.sListarCategoriaConsulCbo().then(function (rpta) {
              $scope.listaCategoriaConsul = rpta.datos;
              $scope.listaCategoriaConsul.splice(0,0,{ id : '0', descripcion:'--Seleccione la categoría --'});
              var ind = 0;
              angular.forEach($scope.listaCategoriaConsul, function(value, key) {
                if(value.id == $scope.mySelectionGrid[0].idcategoriaconsul){
                  ind = key;
                }
              });
              $scope.fDataAdd.categoriaConsul =  $scope.listaCategoriaConsul[ind];
              $scope.cargaSubCategoriaConsul($scope.mySelectionGrid[0].idcategoriaconsul);             
            });
          }
          $scope.getCargaCategoriaConsul();

          $scope.cargaSubCategoriaConsul = function(item) {
            $scope.itemConsulta = { 'idCategoria' : item};
             categoriaConsulServices.sListarSubCategoriaConsulCbo($scope.itemConsulta).then(function (rpta) {
              $scope.listaSubCategoriaConsul = rpta.datos;
              $scope.listaSubCategoriaConsul.splice(0,0,{ id : '0', descripcion:'--Seleccione la subcategoría --'});

              var ind = 0;
              angular.forEach($scope.listaSubCategoriaConsul, function(value, key) {
                if(value.id == $scope.mySelectionGrid[0].idsubcategoriaconsul){
                  ind = key;
                }
              });
              $scope.fDataAdd.subCategoriaConsul =  $scope.listaSubCategoriaConsul[ind];
            });
          }
          
          $scope.evaluaCategoria = function (item) {
            if(item == 0){
              pinesNotifications.notify({ title: "Error ", text: "Debe seleccionar una categoría de consultorio", type: 'error', delay: 2500 });
              $scope.cargaSubCategoriaConsul();
            }
          }   
          
          $scope.aceptar = function () {
            var flag = 1;
            //console.log($scope.fDataAdd); 
            if($scope.fDataAdd.sede == 0){
              //alert('Seleccione una Sede');
              pinesNotifications.notify({ title: "Error ", text: "Seleccione una Sede", type: 'error', delay: 2500 });
              flag = 0;
            }else if($scope.fDataAdd.categoriaConsul == 0){
              //alert('Seleccione una Categoría');
              pinesNotifications.notify({ title: "Error ", text: "Seleccione una Categoría", type: 'error', delay: 2500 });
              flag = 0;
            }else if($scope.fDataAdd.subCategoriaConsul == 0 ){
              //alert('Seleccione una Subcategoría');
              pinesNotifications.notify({ title: "Error ", text: "Seleccione una Subcategoría", type: 'error', delay: 2500 });
              flag = 0;
            }
            
            if(flag != 0){
              ambienteServices.sEditar($scope.fDataAdd).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $modalInstance.dismiss('cancel');
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';                
                }else{
                  alert('Error inesperado');
                }
                $scope.fData = {};
                $scope.getPaginationServerSide();
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
            }    
          }
        }
       
      });
    }

    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'Ambiente/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fDataAdd = {};
          $scope.fDataAdd.sede = $scope.descripcion_sede;
          $scope.fDataAdd.idsede = $scope.idsede;
          $scope.titleForm = 'Registro de ambiente';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.fDataAdd.comentario = '';
          /*$scope.getCargaSede = function() {
            sedeServices.sListarSedeCbo().then(function (rpta) {
              $scope.listaSede = rpta.datos;
              $scope.listaSede.splice(0,0,{ id : '0', descripcion:'--Seleccione sede --'});
              $scope.fDataAdd.sede = $scope.listaSede[0].id;    
            });
          }
          $scope.getCargaSede();*/

          $scope.getCargaCategoriaConsul = function() {
            categoriaConsulServices.sListarCategoriaConsulCbo().then(function (rpta) {
              $scope.listaCategoriaConsul = rpta.datos;
              $scope.listaCategoriaConsul.splice(0,0,{ id : '0', descripcion:'--Seleccione la categoría --'});
              $scope.fDataAdd.categoriaConsul = $scope.listaCategoriaConsul[0]; 
            });
          }
          $scope.getCargaCategoriaConsul();

          $scope.cargaSubCategoriaConsul = function (item) {
            $scope.itemConsulta = { 'idCategoria' : item};
             categoriaConsulServices.sListarSubCategoriaConsulCbo($scope.itemConsulta).then(function (rpta) {
              $scope.listaSubCategoriaConsul = rpta.datos;
              $scope.listaSubCategoriaConsul.splice(0,0,{ id : '0', descripcion:'--Seleccione la subcategoría --'});
              $scope.fDataAdd.subCategoriaConsul = $scope.listaSubCategoriaConsul[0];   
            });
          }

          $scope.evaluaCategoria = function (item) {
            if(item == 0){
              pinesNotifications.notify({ title: "Error ", text: "Debe seleccionar una categoría de consultorio", type: 'error', delay: 2500 });
              $scope.getCargaSubCategoriaConsul();
            }
          }    

         $scope.aceptar = function () { 
             var flag = 1;
            //console.log($scope.fDataAdd); 
            if($scope.fDataAdd.sede == 0){
              //alert('Seleccione una Sede');
              pinesNotifications.notify({ title: "Error ", text: "Seleccione una Sede", type: 'error', delay: 2500 });
              flag = 0;
            }else if($scope.fDataAdd.categoriaConsul.id == 0){
              //alert('Seleccione una Categoría');
              pinesNotifications.notify({ title: "Error ", text: "Seleccione una Categoría", type: 'error', delay: 2500 });
              flag = 0;
            }else if($scope.fDataAdd.subCategoriaConsul.id == 0 ){
              //alert('Seleccione una Subcategoría');
              pinesNotifications.notify({ title: "Error ", text: "Seleccione una Subcategoría", type: 'error', delay: 2500 });
              flag = 0;
            }
            
            if(flag != 0){
              ambienteServices.sRegistrar($scope.fDataAdd).then(function (rpta) {
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $modalInstance.dismiss('cancel');
                  $scope.getPaginationServerSide();
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
            }
          }
          
        }
      });
    }

    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ambienteServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }
    
    $scope.btnHabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ambienteServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }  
    
    $scope.btnDeshabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          ambienteServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }  
  
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nuevo ambiente',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar ambiente',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular ambiente',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar ambiente',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      })
      .add ({ 
        combo: 's',
        description: 'Selección y Navegación',
        callback: function() {
          $scope.navegateToCell(0,0);
        }
      });
  }])
  .service("ambienteServices",function($http, $q) {
    return({
        sListarAmbienteCbo: sListarAmbienteCbo,
        sListarAmbiente: sListarAmbiente,
        sRegistrar: sRegistrar,
        sAnular: sAnular,
        sHabilitar: sHabilitar,
        sDeshabilitar: sDeshabilitar,
        sEditar: sEditar,
        sListarAmbientePorSede: sListarAmbientePorSede,
        sListarAmbientePorSedeSession: sListarAmbientePorSedeSession
    });

    function sListarAmbienteCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/lista_ambiente_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarAmbiente(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/lista_ambiente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarAmbientePorSede (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/lista_ambiente_por_sede", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAmbientePorSedeSession(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Ambiente/lista_ambiente_por_sede_session", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });