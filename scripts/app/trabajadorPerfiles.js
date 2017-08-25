angular.module('theme.trabajadorPerfiles', ['theme.core.services'])
  .controller('trabajadorPerfilesController', ['$scope', '$sce', '$filter', '$uibModal', '$controller', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications',
     'clienteServices',
     'empresasClienteServices',
     'productoServices',
     'trabajadorPerfilesServices',
    function($scope, $sce, $filter, $uibModal, $controller, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
      clienteServices,
      empresasClienteServices,
      productoServices,
      trabajadorPerfilesServices
    ) { 
    // 'use strict';
    shortcut.remove("F2"); $scope.modulo = 'trabajadorPerfiles'; 

    $controller('clienteController', { 
      $scope : $scope
    });

    $scope.fBusqueda = {};
    // $scope.fData = {};
    /* LISTA DE EMPRESAS SALUD OCUP. */
    empresasClienteServices.sListarEmpresasSoloSaludOcup().then(function (rpta) { 
      $scope.listaEmpresas = rpta.datos;
      $scope.fBusqueda.empresa = $scope.listaEmpresas[0];
    });
    /* LISTA DE PERFILES SALUD OCUP. */
    productoServices.sListarPerfilesSaludOcup().then(function (rpta) {
      $scope.listaPerfiles = rpta.datos;
      $scope.listaPerfiles.splice(0,0,{ id : 'all', descripcion:'--Todos--'});
      $scope.fBusqueda.perfil = $scope.listaPerfiles[0];
    });

    //=============================================================
    // AUTOCOMPLETADO EMPRESA CLIENTE  
    //=============================================================
    $scope.getClienteAutocomplete = function (value) {
      var params = $scope.fBusqueda;
      params.search = value;
      params.sensor = false;

      return clienteServices.sListarClientesOcupacionalAutocomplete(params).then(function(rpta) { 
        $scope.noResultsEmpresaCliente = false;
        if( rpta.flag === 0 ){
          $scope.noResultsEmpresaCliente = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedCliente = function ($item, $model, $label) {
        $scope.fBusqueda.idcliente = $item.id;
    };
    $scope.getClearInputCliente = function () { 
      if(!angular.isObject($scope.fBusqueda.cliente) ){ 
        $scope.fBusqueda.idcliente = null; 
      }
    }

    var paginationOptionsTP = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 100,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGridTP = [];
    /* GRILLA DE EMPLEADOS */ 
    $scope.dirImagesEmpleados = $scope.dirImages + "dinamic/empleado/";
    $scope.gridOptionsTP = {
      // rowHeight: 48,
      minRowsToShow: 8,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 100,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableSelectAll: false,
      enableFiltering: true,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [ 
        { field: 'id', name: 'pc.idproductocliente', displayName: 'ID.',width: '4%' },
        { field: 'num_documento', name: 'num_documento', displayName: 'N° DOC.',width: '9%' },
        { field: 'cliente', name: "CONCAT(cl.nombres || ' ' || cl.apellido_paterno || ' ' || cl.apellido_materno)", displayName: 'TRABAJADOR',width: '26%', sort: { direction: uiGridConstants.DESC } }, 
        { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC EMPRESA',width: '10%' },
        { field: 'empresa', name: 'empresa', displayName: 'EMPRESA',width: '20%' },
        // { field: 'nombre_foto', name: 'nombre_foto', displayName: 'FOTO',width: 60, enableFiltering: false, enableSorting: false, 
        //   cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpleados + COL_FIELD }}" /> </div>' },
        { field: 'producto', name: 'descripcion', displayName: 'PERFIL/PRODUCTO',width: '20%' },
        { field: 'precio', name: 'precio', displayName: 'TARIFA REF.',width: '8%' }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridTP = gridApi.selection.getSelectedRows(); 
        });
        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsTP.sort = null;
            paginationOptionsTP.sortName = null;
          } else {
            paginationOptionsTP.sort = sortColumns[0].sort.direction;
            paginationOptionsTP.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideTP();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsTP.pageNumber = newPage;
          paginationOptionsTP.pageSize = pageSize;
          paginationOptionsTP.firstRow = (paginationOptionsTP.pageNumber - 1) * paginationOptionsTP.pageSize;
          $scope.getPaginationServerSideTP();
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsTP.search = true;
          paginationOptionsTP.searchColumn = { 
            //'idempleado' : grid.columns[1].filters[0].term,
            'numero_documento' : grid.columns[1].filters[0].term,
            "CONCAT(cl.nombres || ' ' || cl.apellido_paterno || ' ' || cl.apellido_materno)" : grid.columns[2].filters[0].term,
            'em.descripcion' : grid.columns[3].filters[0].term,
            'ec.descripcion' : grid.columns[4].filters[0].term,
            'precio' : grid.columns[5].filters[0].term
          }; 
          $scope.getPaginationServerSideTP();
        });
      }
    };
    paginationOptionsTP.sortName = $scope.gridOptionsTP.columnDefs[1].name;
    $scope.getPaginationServerSideTP = function() {
      // $scope.fBusqueda.modulo = 'asist';
      var arrParams = { 
        paginate : paginationOptionsTP,
        datos: $scope.fBusqueda
      };
      trabajadorPerfilesServices.sListarPerfilesDeTrabajadores(arrParams).then(function (rpta) {
        $scope.gridOptionsTP.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsTP.data = rpta.datos;
      });
      $scope.mySelectionGridTP = [];
      // $scope.gridOptionsHorario.data = [];
    };
    $scope.agregarClienteAPerfil = function () {
      trabajadorPerfilesServices.sAgregarClienteAPerfil($scope.fBusqueda).then(function (rpta) { 
        if(rpta.flag == 1){
          var pTitle = 'OK!';
          var pType = 'success';
        }else if(rpta.flag == 0){
          var pTitle = 'Error!';
          var pType = 'danger';
        }else{
          alert('Algo salió mal...');
        }
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
        $scope.getPaginationServerSideTP();
      });
    }
    $scope.btnAnular = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          trabajadorPerfilesServices.sAnular($scope.mySelectionGridTP).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSideTP();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }
  }])
  .service("trabajadorPerfilesServices",function($http, $q) {
    return({
        sListarPerfilesDeTrabajadores: sListarPerfilesDeTrabajadores,
        sAgregarClienteAPerfil: sAgregarClienteAPerfil,
        sAnular: sAnular
    });

    function sListarPerfilesDeTrabajadores(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrabajadorPerfil/lista_perfiles_trabajadores", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarClienteAPerfil (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrabajadorPerfil/agregar_cliente_a_perfil", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"TrabajadorPerfil/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });