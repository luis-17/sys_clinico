angular.module('theme.atencionMuestra', ['theme.core.services'])
  .controller('atencionMuestraController', ['$scope', '$route','$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys','atencionMuestraServices', 'tipoMuestraServices', 'empleadoSaludServices',
    function($scope, $route, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications
      , hotkeys, atencionMuestraServices, tipoMuestraServices,empleadoSaludServices
      ){
    'use strict';
   

    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.isRegisterSuccess = false;
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    // $scope.navegateToCell = function( rowIndex, colIndex ) {
    //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    // };
    $scope.fData = {};
    $scope.fData.medico = null;
    $scope.fData.paciente = {};
    $scope.fData.idmuestrapaciente = '-'
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: false,
      multiSelect: false,
      data: [],
      columnDefs: [
        { field: 'idproductomaster', name: 'idproductomaster', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'producto', name: 'producto', displayName: 'Producto' },
        { field: 'analisis', name: 'analisis', displayName: 'Análisis' },
        { field: 'seccion', name: 'seccion', displayName: 'Sección' },
        { field: 'cantidad', name: 'cantidad', displayName: 'Cantidad',maxWidth: 150 },
       
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        // gridApi.selection.on.rowSelectionChanged($scope,function(row){
        //   $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        // });
        // gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
        //   $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
        // });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          }else {
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

      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    
    $scope.obtenerDatosPacienteHistoria = function () { 

      if( $scope.fData.paciente.idhistoria){ 
        atencionMuestraServices.sObtenerPacientePorHistoria($scope.fData).then(function (rpta) { 
          if( rpta.flag === 1 ){
            $scope.fData.paciente = rpta.datos;
            $scope.fData.ordenes = rpta.ordenes;
            $scope.fData.ordenventa = $scope.fData.ordenes[0];
            $scope.gridOptions.data = $scope.fData.ordenes[0].productos;
            pinesNotifications.notify({ title: 'OK.', text: rpta.message, type: 'success', delay: 2000 });
          }else if( rpta.flag === 0 ){
            pinesNotifications.notify({ title: 'ERROR.', text: rpta.message, type: 'warning', delay: 4000 });
          }
        });
      }
    }
    $scope.btnBuscarCliente = function (size) {
      $modal.open({
        templateUrl: angular.patchURLCI+'cliente/ver_popup_busqueda_cliente',
        size: size || '',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Búsqueda de Pacientes';
          var paginationOptionsClienteEnVentas = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionClienteGrid = [];
          
          $scope.gridOptionsClienteBusqueda = {
            rowHeight: 36,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: false,
            enableSelectAll: true,
            enableFiltering: true,
            // enableRowHeaderSelection: false, // fila cabecera 
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'idhistoria', name: 'idhistoria', displayName: 'Historia', maxWidth: 100,  sort: { direction: uiGridConstants.ASC} },
              { field: 'num_documento', name: 'num_documento', displayName: 'N° Doc.', maxWidth: 120 },
              { field: 'nombres', name: 'nombres', displayName: 'Nombres', maxWidth: 200 },
              { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'Apellido Paterno', maxWidth: 200 },
              { field: 'apellido_materno', name: 'apellido_materno', displayName: 'Apellido Materno', maxWidth: 200 }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionClienteGrid = gridApi.selection.getSelectedRows();
                $scope.fData.paciente = $scope.mySelectionClienteGrid[0]; //console.log($scope.fData.cliente);
                //$scope.fData.numero_documento = $scope.mySelectionClienteGrid[0].num_documento;
                $modalInstance.dismiss('cancel');
                setTimeout(function() {
                  $('#idhistoria').focus();
                  console.log($scope.fData.paciente);
                }, 1000);
                $scope.obtenerDatosPacienteHistoria();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsClienteEnVentas.sort = null;
                  paginationOptionsClienteEnVentas.sortName = null;
                } else {
                  paginationOptionsClienteEnVentas.sort = sortColumns[0].sort.direction;
                  paginationOptionsClienteEnVentas.sortName = sortColumns[0].name;
                }
                $scope.getPaginationClienteEnVentaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsClienteEnVentas.pageNumber = newPage;
                paginationOptionsClienteEnVentas.pageSize = pageSize;
                paginationOptionsClienteEnVentas.firstRow = (paginationOptionsClienteEnVentas.pageNumber - 1) * paginationOptionsClienteEnVentas.pageSize;
                $scope.getPaginationClienteEnVentaServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationOptionsClienteEnVentas.search = true;
                // console.log(grid.columns);
                // console.log(grid.columns[1].filters[0].term);
                paginationOptionsClienteEnVentas.searchColumn = { 
                  'h.idhistoria' : grid.columns[1].filters[0].term,
                  'num_documento' : grid.columns[2].filters[0].term,
                  'cl.nombres' : grid.columns[3].filters[0].term,
                  'apellido_paterno' : grid.columns[4].filters[0].term,
                  'apellido_materno' : grid.columns[5].filters[0].term
                }
                $scope.getPaginationClienteEnVentaServerSide();
              });
            }
          };
          $scope.navegateToCellListaBusquedaCliente = function( rowIndex, colIndex ) { 
            console.log(rowIndex, colIndex);
            $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptionsClienteBusqueda.data[rowIndex], $scope.gridOptionsClienteBusqueda.columnDefs[colIndex]); 
            
          };
          paginationOptionsClienteEnVentas.sortName = $scope.gridOptionsClienteBusqueda.columnDefs[0].name;
          $scope.getPaginationClienteEnVentaServerSide = function() {
            //$scope.$parent.blockUI.start();
            $scope.datosGrid = {
              paginate : paginationOptionsClienteEnVentas
            };
            atencionMuestraServices.sListarPacientesLaboratorio($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsClienteBusqueda.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsClienteBusqueda.data = rpta.datos;
               
              //$scope.$parent.blockUI.stop();
            });
            $scope.mySelectionClienteGrid = [];
          };
          $scope.getPaginationClienteEnVentaServerSide();

          shortcut.add("down",function() { 

            $scope.navegateToCellListaBusquedaCliente(0,0);
          });
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    // TIPOS DE MUESTRA
    tipoMuestraServices.sListarTipoMuestraCbo().then(function (rpta) {
      $scope.listaTipoMuestra = rpta.datos;
      $scope.listaTipoMuestra.splice(0,0,{ id : '', descripcion:'--Seleccione Tipo de Muestra--'});
      $scope.fData.idtipomuestra = $scope.listaTipoMuestra[0].id;
    });
    // PRIORIDAD 0:Normal; 1:Alta; 2:Muy Alta
    $scope.listaBoolPrioridad = [ 
        { id : 1, descripcion: 'NORMAL' },
        { id : 2, descripcion: 'URGENTE' }
      ];
    $scope.fData.prioridad = $scope.listaBoolPrioridad[0].id;
    $scope.cargarProductos = function (){
      //console.log($scope.fData.ordenventa.productos);
      $scope.gridOptions.data = $scope.fData.ordenventa.productos;
    }
    $scope.getPersonalMedicoAutocomplete = function (value) { 
      var params = {
        search: value,
        sensor: false
      }
      return empleadoSaludServices.sListarPersonalSaludCbo(params).then(function(rpta) { 
        $scope.noResultsMEDRESP = false; 
        if( rpta.flag === 0 ){ 
          $scope.noResultsMEDRESP = true; 
        } 
        return rpta.datos; 
      });
    }
    $scope.limpiarCamposMenosHistoria = function(){
      $scope.fData.ordenes = {};
      $scope.fData.paciente.nombres = null;
      $scope.fData.paciente.apellidos = null;
      $scope.fData.paciente.num_documento = null;
      $scope.fData.paciente.edad = null;
      $scope.fData.paciente.sexo = null;
      $scope.fData.paciente.idcliente = null;
      $scope.fData.paciente.apellido_paterno = null;
      $scope.fData.paciente.apellido_materno = null;
      $scope.fData.medico = null;
      $scope.gridOptions.data = [];
      $scope.fData.orden_lab = '-'
    }
   
    $scope.grabar = function (){
      $scope.fDatos = {
        'idcliente' : $scope.fData.paciente.idcliente,
        'idhistoria' : $scope.fData.paciente.idhistoria,
        'medico' : $scope.fData.medico,
        'ordenventa' : $scope.fData.ordenventa.ordenventa,
        'arrAnalisis' : $scope.fData.ordenventa.productos,
        'prioridad' : $scope.fData.prioridad
      }
      //console.log($scope.fDatos);
      atencionMuestraServices.sRegistrarMuestra($scope.fDatos).then(function (rpta) {
        //console.log(rpta);
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
          $scope.isRegisterSuccess = true;
          
        }else if(rpta.flag == 0){
          var pTitle = 'Error!';
          var pType = 'danger';
        }else{
          alert('Error inesperado');
        }
        $scope.fData.idtipomuestra = $scope.listaTipoMuestra[0].id;
        $scope.fData.observaciones = null;
        $scope.fData.orden_lab = rpta.orden_lab;
        pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
      });
    }
    $scope.nuevo = function (){
      $route.reload(); 
      //console.log('reload...');
      setTimeout(function() {
        $('#idhistoria').focus();
      },1000);
     
    }
    $scope.imprimir = function(){
      alert('Seccion en construcción.');
    }
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */ 
    shortcut.remove('F2');
    shortcut.add("F2",function($event) { 
        $scope.grabar(); 
    }); 
    shortcut.remove('F3');
    shortcut.add("F3",function($event) {
      $route.reload(); 
      console.log('reload...');
      setTimeout(function() {
        $('#idhistoria').focus();
      },1000);
    }); 
    shortcut.remove('F4');
    shortcut.add("F4",function(event) { 
      if($scope.isRegisterSuccess == true){ 
        $scope.imprimir(); 
      } 
    }); 
  }])
  .service("atencionMuestraServices",function($http, $q) {
    return({
        sListarPacientesLaboratorio,
        sObtenerPacientePorOrden,
        sObtenerPacientePorHistoria,
        sRegistrarMuestra
       
    });
    function sListarPacientesLaboratorio(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/listar_pacientes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerPacientePorOrden(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/obtener_paciente_orden", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerPacientePorHistoria(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"atencionMuestra/obtener_paciente_historia", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarMuestra(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
            method : "post",
            // url : angular.patchURLCI+"atencionMuestra/registrar_muestra", 
            url : angular.patchURLCI+"atencionMuestra/registrarAnalisisPaciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });