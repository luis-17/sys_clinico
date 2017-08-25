angular.module('theme.conceptoPlanilla', ['theme.core.services'])
  .controller('conceptoPlanillaController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'conceptoPlanillaServices',
    'categoriaConceptoPlanillaServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , conceptoPlanillaServices
      , categoriaConceptoPlanillaServices
      ){
    'use strict';
    $scope.fData = {};
    $scope.fData.temporal = {};
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.listaNivelReporte = [
      {id:'TOTAL', descripcion:'TOTAL'},
      {id:'CC', descripcion:'CENTRO COSTO'},
      {id:'EMPL', descripcion:'EMPLEADO'},
    ];
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
      enableCellEditOnFocus: true,
      columnDefs: [
        { field: 'id', name: 'c.idconcepto', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC}, enableCellEdit: false, },
        { field: 'descripcion', name: 'c.descripcion_co', displayName: 'Descripción', enableCellEdit: true, },
        { field: 'descripcion_categoria_concepto', name: 'cc.descripcion', displayName: 'Categoría', enableCellEdit: true, },
        { field: 'abreviatura', name: 'c.abreviatura', displayName: 'Abreviatura', enableCellEdit: true, },
        { field: 'codigo_plan', name: 'c.codigo_plan', displayName: 'Código Plan', enableCellEdit: true, },
        { field: 'codigo_plame', name: 'c.codigo_plame', displayName: 'Código Plame', enableCellEdit: true, },
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
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = { 
            'c.idconcepto' : grid.columns[1].filters[0].term,
            'c.descripcion_co' : grid.columns[2].filters[0].term,
            'cc.descripcion' : grid.columns[3].filters[0].term,
            'c.abreviatura' : grid.columns[4].filters[0].term,
            'c.codigo_plan' : grid.columns[5].filters[0].term,
            'c.codigo_plame' : grid.columns[6].filters[0].term,
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
      conceptoPlanillaServices.sListarConceptos($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    
      /* ============= */
     /* MANTENIMIENTO */
    /* ============= */
    $scope.btnNuevo = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ConceptoPlanilla/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.si_snp = false;
          $scope.fData.si_spp = false;
          $scope.fData.si_5cat = false;
          $scope.fData.si_essalud = false;
          $scope.fData.si_sctr = false;
          $scope.fData.si_senati = false;
          $scope.fData.es_calculable = false;
          $scope.fData.nivel_reporte = $scope.listaNivelReporte[0];
      
          $scope.titleForm = 'Registro de Concepto';

          categoriaConceptoPlanillaServices.sListarCategoriaConceptosCbo().then(function(rpta){
            $scope.listaCategoriaConceptos = rpta.datos;
            $scope.listaCategoriaConceptos.splice(0,0,{ id : '0', descripcion:'--Seleccione la categoría --'});
            $scope.fData.categoria_concepto = $scope.listaCategoriaConceptos[0];
          });
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            conceptoPlanillaServices.sRegistrar($scope.fData).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          //console.log($scope.mySelectionGrid);
        }, 
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }

    $scope.btnEditar = function (size) {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ConceptoPlanilla/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid,getPaginationServerSide) {
          $scope.mySelectionGrid = mySelectionGrid;
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          // $scope.fData.nivel_reporte = $scope.listaNivelReporte[0];
          //console.log($scope.mySelectionGrid);
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];            
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.titleForm = 'Edición de Concepto';

          categoriaConceptoPlanillaServices.sListarCategoriaConceptosCbo().then(function(rpta){
            $scope.listaCategoriaConceptos = rpta.datos;
            $scope.listaCategoriaConceptos.splice(0,0,{ id : '0', descripcion:'--Seleccione la categoría --'});
            $scope.fData.categoria_concepto = $scope.listaCategoriaConceptos[0];

            var objIndex = $scope.listaCategoriaConceptos.filter(function(obj) {
              return obj.id == $scope.fData.idcategoriaconcepto;
            }).shift(); 
            $scope.fData.categoria_concepto = objIndex;

            var objIndex2 = $scope.listaNivelReporte.filter(function(obj) {
              return obj.id == $scope.fData.nivel_reporte;
            }).shift(); 
            $scope.fData.nivel_reporte = objIndex2;
          });
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () {
            conceptoPlanillaServices.sEditar($scope.fData).then(function (rpta) {
              if(rpta.flag != 2){
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
              }else{
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
              }
              $scope.getPaginationServerSide();
              $scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
          //console.log($scope.mySelectionGrid);
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          },
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }
        }
      });
    }

    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          conceptoPlanillaServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
  }])
  .service("conceptoPlanillaServices",function($http, $q) { 
    return({
      sListarConceptos:sListarConceptos,
      sListarConceptosAgregados:sListarConceptosAgregados,
      sListarConceptosNoAgregados:sListarConceptosNoAgregados,
      sRegistrar:sRegistrar,
      sEditar: sEditar,
      sAnular: sAnular,
      sAgregarConcepto:sAgregarConcepto,
      sQuitarConcepto:sQuitarConcepto,
      sBloqueaDesbloqueaConcepto:sBloqueaDesbloqueaConcepto,
      sActualizarValorReferencial:sActualizarValorReferencial,
    });

    function sListarConceptos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConceptoPlanilla/lista_conceptos", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sListarConceptosAgregados(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ConceptoPlanilla/lista_conceptos_agregados", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarConceptosNoAgregados(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ConceptoPlanilla/lista_conceptos_no_agregados", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }  
    function sRegistrar(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConceptoPlanilla/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sEditar(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConceptoPlanilla/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConceptoPlanilla/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarConcepto(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConceptoPlanilla/agregar_concepto", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sQuitarConcepto(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ConceptoPlanilla/quitar_concepto", 
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sBloqueaDesbloqueaConcepto(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ConceptoPlanilla/bloquea_desbloquea_concepto", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarValorReferencial(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"ConceptoPlanilla/actualizar_valor_referencial", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }

  });