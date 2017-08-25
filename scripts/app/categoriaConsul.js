angular.module('theme.categoriaConsul', ['theme.core.services'])
  .controller('categoriaConsulController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'hotkeys','pinesNotifications', 
    'categoriaConsulServices',
    function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, hotkeys, pinesNotifications,categoriaConsulServices){
    'use strict';
 
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };

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
      columnDefs: [
        { field: 'id', name: 'idcategoriaconsul', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'descripcion', name: 'descripcion_cco ', displayName: 'Descripción' }
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
          //console.log(sortColumns);
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
              'idcategoriaconsul' : grid.columns[1].filters[0].term,
              'descripcion_cco' : grid.columns[2].filters[0].term
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
      //console.log(categoriaConsulServices);
      categoriaConsulServices.sListarCategoriaConsul($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos; 
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

     /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'CategoriaConsul/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Categoría consultorio';
          $scope.cancel = function () {
            //console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            categoriaConsulServices.sEditar($scope.fData).then(function (rpta) { 
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
          //console.log($scope.mySelectionGrid);
        }
       
      });
    }
    $scope.btnNuevo = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'CategoriaConsul/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.titleForm = 'Registro de Categoría consultorio';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
         

          $scope.aceptar = function () { 
            // console.log($scope.fData);
            categoriaConsulServices.sRegistrar($scope.fData).then(function (rpta) {
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
          //console.log($scope.mySelectionGrid);
        }
      });
    }    
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          categoriaConsulServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
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
        description: 'Nueva categoría consultorio',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar categoría consultorio',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular categoría consultorio',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar categoría consultorio',
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
  

    /*-------------------------------------------------------------------------*/
    /*------------------- AGREGAR SUB CATEGORIA ---------------------------------*/
    /*-------------------------------------------------------------------------*/

    $scope.btnAgregarSubCategoria = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'CategoriaConsul/ver_popup_formulario_subcategoria',
        size: size || '',
        scope: $scope,
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationSubCategoriaServerSide) {
          $scope.getPaginationSubCategoriaServerSide = getPaginationSubCategoriaServerSide;
          $scope.fDataAdd = {};
          $scope.titleForm = 'Registro de Subcategoría';
          var paginationSubAlmacenOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionSubCategoriaGrid = [];
          $scope.gridOptionsSubAlmacen = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableGridMenu: false,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            minRowsToShow: 8,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'id', name: 'idsubcategoriaconsul', displayName: 'ID', maxWidth: 60,enableCellEdit: false ,sort: { direction: uiGridConstants.ASC} },
              { field: 'descripcion', name: 'descripcion_scco', displayName: 'Descripcion', maxWidth: 200 ,cellClass:'ui-editCell' },
              { field: 'accion', displayName: 'Acción', maxWidth: 90, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnAnularItem(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionSubCategoriaGrid = gridApi.selection.getSelectedRows();
              });

              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionSubCategoriaGrid = gridApi.selection.getSelectedRows();
              });

              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                rowEntity.column = colDef.field;
                rowEntity.newvalue = newValue;

                //console.log(rowEntity);
                categoriaConsulServices.sEditarSubCategoriaInGrid(rowEntity).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success'; 
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  $scope.getPaginationSubCategoriaServerSide();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                });
                $scope.$apply();
              });

              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                getPaginationSubCategoriaServerSide.pageNumber = newPage;
                getPaginationSubCategoriaServerSide.pageSize = pageSize;
                getPaginationSubCategoriaServerSide.firstRow = (paginationSubAlmacenOptions.pageNumber - 1) * paginationSubAlmacenOptions.pageSize;
                $scope.getPaginationSubCategoriaServerSide();
              });
            }
          };
         
          $scope.getPaginationSubCategoriaServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationSubAlmacenOptions ,
              datos : $scope.mySelectionGrid[0].id
            };
            //console.log($scope.mySelectionGrid[0].id);
            categoriaConsulServices.sListarSubCategoriaConsul($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsSubAlmacen.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsSubAlmacen.data = rpta.datos;
            });
            $scope.mySelectionSubCategoriaGrid = [];
          };
          $scope.getPaginationSubCategoriaServerSide();
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
                   
          $scope.agregarItem = function () {
            $scope.fDataAdd.idcategoriaconsul = $scope.mySelectionGrid[0].id ;
            //console.log($scope.fDataAdd);
            categoriaConsulServices.sRegistrarSubCategoria($scope.fDataAdd).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                //$modalInstance.dismiss('cancel');
                $scope.getPaginationSubCategoriaServerSide();
                $scope.fDataAdd.descripcion_scco = null ;
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }

          $scope.btnAnularItem = function (row,mensaje) { 
              var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  categoriaConsulServices.sAnularSubCategoria(row.entity.id).then(function (rpta) {
                    if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success';
                        $scope.getPaginationSubCategoriaServerSide();
                      }else if(rpta.flag == 0){
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{
                        alert('Error inesperado');
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                  });
                }
              });
          }

       },
        resolve: {
          getPaginationSubCategoriaServerSide: function() {
            return $scope.getPaginationSubCategoriaServerSide;
          }
        }
      });
    }
    /*------------------   FIN AGREGAR SUBCATEGORIA     -------------------------------------*/


  }])
  .service("categoriaConsulServices",function($http, $q) {
    return({
        sListarCategoriaConsul : sListarCategoriaConsul,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sAnular: sAnular,
        sEditarSubCategoriaInGrid : sEditarSubCategoriaInGrid,
        sListarSubCategoriaConsul : sListarSubCategoriaConsul,
        sListarSubCategoriaAsistCbo: sListarSubCategoriaAsistCbo,
        sRegistrarSubCategoria : sRegistrarSubCategoria,  
        sAnularSubCategoria : sAnularSubCategoria,
        sListarCategoriaConsulCbo : sListarCategoriaConsulCbo,
        sListarSubCategoriaConsulCbo : sListarSubCategoriaConsulCbo
    });
    

    function sListarCategoriaConsul(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/lista_categoria_consul", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarSubCategoriaConsul(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/lista_subcategoria_consul", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarSubCategoriaAsistCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/lista_subcategoria_consul_asistencia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarSubCategoria(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/registrar_subcategoria", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sEditarSubCategoriaInGrid(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/editar_subcategoria_en_grid", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAnularSubCategoria(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/anular_subcategoria", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarCategoriaConsulCbo(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/lista_categorias_consul_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 

    function sListarSubCategoriaConsulCbo(pDatos) { 
      var datos = pDatos;
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"CategoriaConsul/lista_subcategoria_consul_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }     
    
  });