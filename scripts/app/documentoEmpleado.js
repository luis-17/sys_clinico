angular.module('theme.documentoEmpleado', ['theme.core.services'])
  .controller('documentoEmpleadoController', ['$scope', 'blockUI', '$sce', '$filter', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'documentoEmpleadoServices',
    'empleadoServices', 
    'especialidadServices', 
    'cargoServices', 
    'usuarioServices',
    'ModalReporteFactory',
    function($scope, blockUI, $sce, $filter, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
      documentoEmpleadoServices,
      empleadoServices, 
      especialidadServices, 
      cargoServices, 
      usuarioServices, 
      ModalReporteFactory ) { 
    // 'use strict'; 
    shortcut.remove("F2"); 
    $scope.modulo = 'documentoEmpleado';
    $scope.fBusqueda = {};
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
    $scope.dirImagesEmpleados = $scope.dirImages + "dinamic/empleado/";
    $scope.gridOptions = {
      minRowsToShow: 4,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: false,
      enableFiltering: true,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'id', name: 'idempleado', displayName: 'ID', width: 80, visible: false },
        { field: 'num_documento', name: 'numero_documento', displayName: 'N° DOC.',width: '6%' },
        { field: 'nombres', name: 'nombres', displayName: 'NOMBRES',  sort: { direction: uiGridConstants.ASC} }, 
        { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'APELLIDO PATERNO' },
        { field: 'apellido_materno', name: 'apellido_materno', displayName: 'APELLIDO MATERNO' },
        { field: 'cargo', name: 'descripcion_ca', displayName: 'CARGO',width: 200 },
        { field: 'empresa', name: 'empresa', displayName: 'EMPRESA',width: 240 },
        { field: 'soloEspecialidad', name: 'especialidad', displayName: 'ESPECIALIDAD',width: '10%' },
        { field: 'telefono', name: 'e.telefono', displayName: 'TELEFONO', type:'number',width: '8%'},
        { field: 'email', name: 'correo_electronico', displayName: 'E-MAIL',width: '15%'},
        { field: 'direccion', name: 'direccion', displayName: 'DIRECCION', enableFiltering: false, visible: false,width: 180},
        { field: 'fecha_nacimiento', name: 'fecha_nacimiento', displayName: 'FECHA NAC.', visible: false, enableFiltering: false,width: 100},
        { field: 'nombre_foto', name: 'nombre_foto', displayName: '',width: 70, enableFiltering: false, enableSorting: false, cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpleados + COL_FIELD }}" /> </div>' }
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
          //console.log($scope.mySelectionGrid); 
          if($scope.mySelectionGrid.length == 1){
            $scope.getPaginationServerSideDE();
          }else{
            $scope.gridOptionsDocumento.data = [];
          } 
          
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
            'e.idempleado' : grid.columns[1].filters[0].term,
            'e.numero_documento' : grid.columns[2].filters[0].term,
            'e.nombres' : grid.columns[3].filters[0].term,
            'e.apellido_paterno' : grid.columns[4].filters[0].term,
            'e.apellido_materno' : grid.columns[5].filters[0].term,
            'c.descripcion_ca' : grid.columns[6].filters[0].term,
            'em.descripcion' : grid.columns[7].filters[0].term,
            'esp.nombre' : grid.columns[8].filters[0].term,
            'e.telefono' : grid.columns[9].filters[0].term,
            'e.correo_electronico' : grid.columns[10].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[2].name;
    $scope.getPaginationServerSide = function() {
      // blockUI.start();
      var arrParams = {
        paginate : paginationOptions,
        datos: $scope.fBusqueda
      };
      empleadoServices.sListarEmpleados(arrParams).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        // blockUI.stop();
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    /* DOCUMENTOS */ 
    var paginationOptionsDE = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGridDE = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptionsDocumento.enableFiltering = !$scope.gridOptionsDocumento.enableFiltering;
      $scope.gridApiDE.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.dirImagesDocEmpleados = $scope.dirImages + "dinamic/empleado/documentacion/";
    $scope.dirIconoFormat = $scope.dirImages + "formato-imagen/";
    $scope.gridOptionsDocumento = { 
      message:'Seleccione un empleado para ver su documentación.',
      minRowsToShow: 6,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'iddocumentoempleado', displayName: 'ID', width: '5%',  sort: { direction: uiGridConstants.ASC} },
        { field: 'titulo', name: 'titulo_doc', displayName: 'TITULO',width: '22%' },
        { field: 'descripcion', name: 'descripcion_doc', displayName: 'DESCRIPCION',width: '30%' }, 
        { field: 'fecha_entrega', name: 'fecha_entrega', displayName: 'FECHA ENTREGA',width: '12%', enableFiltering: false },
        { field: 'fecha_subida', name: 'fecha_subida', displayName: 'FECHA SUBIDA',width: '10%', enableFiltering: false },
        { field: 'username', name: 'username', displayName: 'USUARIO SUBIDA',width: '10%', enableFiltering: false },
        { field: 'archivo', name: 'archivo', displayName: 'DESCARGAR',width: '8%', enableFiltering: false, enableSorting: false, type: 'object',
          cellTemplate:'<div><a target="_blank" href="{{ grid.appScope.dirImagesDocEmpleados + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </a> </div>' }
      ],
      onRegisterApi: function(gridApiDE) {
        $scope.gridApiDE = gridApiDE;
        gridApiDE.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridDE = gridApiDE.selection.getSelectedRows();
        });
        gridApiDE.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridDE = gridApiDE.selection.getSelectedRows();
        });
        $scope.gridApiDE.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsDE.sort = null;
            paginationOptionsDE.sortName = null;
          } else {
            paginationOptionsDE.sort = sortColumns[0].sort.direction;
            paginationOptionsDE.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideDE();
        });
        gridApiDE.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsDE.pageNumber = newPage;
          paginationOptionsDE.pageSize = pageSize;
          paginationOptionsDE.firstRow = (paginationOptionsDE.pageNumber - 1) * paginationOptionsDE.pageSize;
          $scope.getPaginationServerSideDE();
        });
        $scope.gridApiDE.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsDE.search = true;
          paginationOptionsDE.searchColumn = { 
            'iddocumentoempleado' : grid.columns[1].filters[0].term,
            'titulo_doc' : grid.columns[2].filters[0].term,
            'descripcion_doc' : grid.columns[3].filters[0].term
          }
          $scope.getPaginationServerSideDE();
        });
      }
    };
    paginationOptionsDE.sortName = $scope.gridOptionsDocumento.columnDefs[2].name;
    $scope.getPaginationServerSideDE = function() {
      blockUI.start();
      var arrParams = {
        paginate : paginationOptionsDE,
        datos: { idempleado: $scope.mySelectionGrid[0].id }

      };
      documentoEmpleadoServices.sListarDocumentosDeEmpleado(arrParams).then(function (rpta) {
        $scope.gridOptionsDocumento.totalItems = rpta.paginate.totalRows.contador;
        $scope.gridOptionsDocumento.data = rpta.datos;
        if( !$scope.gridOptionsDocumento.data.length ){
          $scope.gridOptionsDocumento.message = 'No se encontraron documentos del empleado.';
        }
        blockUI.stop();
      });
    };

    $scope.btnNuevo = function () { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'DocumentoEmpleado/ver_popup_formulario',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.titleForm = 'Registro de Archivos - Documentos'; 
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            blockUI.start('Subiendo los archivos...');
            var formData = new FormData();
            $scope.fData.idempleado = $scope.mySelectionGrid[0].id; 
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });
            // formData.append('parientes',JSON.stringify($scope.gridOptionsParientes.data));
            // formData.append('estudios',JSON.stringify($scope.gridOptionsEstudios.data));
            // formData.append('afp',JSON.stringify($scope.fData.afp));
            documentoEmpleadoServices.sRegistrar(formData).then(function (rpta) {
              if(rpta.flag === 1){
                pTitle = 'OK!';
                pType = 'success';
                //$scope.fData = {};
                $scope.cancel();
                $scope.btnNuevo();
                $scope.getPaginationServerSideDE();
              }else if(rpta.flag === 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
            blockUI.stop();
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          documentoEmpleadoServices.sAnular($scope.mySelectionGridDE).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSideDE();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }

    $scope.btnVerFicha= function (){
      $scope.fDataImprimir = {};
      $scope.fDataImprimir = angular.copy($scope.mySelectionGrid[0]);
      $scope.fDataImprimir.lugar_nacimiento = {
        'departamento' : '',
        'provincia' : '',
        'distrito' : ''
      }
      $scope.fDataImprimir.dirImagesEmpleados = $scope.dirImagesEmpleados;

      var arrParams = {
        titulo: 'FICHA DE DATOS DEL TRABAJADOR',
        datos:{
          resultado:  $scope.fDataImprimir,
          salida: 'pdf',
          tituloAbv: 'FIC-EMPL',
          titulo: 'FICHA DE DATOS DEL TRABAJADOR'
        },
        metodo: 'php'
      }

      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/ficha_datos_empleado',
      ModalReporteFactory.getPopupReporte(arrParams);
    }


  }])
  .service("documentoEmpleadoServices",function($http, $q) {
    return({
      sListarDocumentosDeEmpleado: sListarDocumentosDeEmpleado,
      sRegistrar: sRegistrar,
      sAnular: sAnular,
      sGuardarCV: sGuardarCV
    });
    function sListarDocumentosDeEmpleado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"DocumentoEmpleado/lista_documentos_de_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"DocumentoEmpleado/registrar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"DocumentoEmpleado/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sGuardarCV (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"DocumentoEmpleado/guardar_cv", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }

  });