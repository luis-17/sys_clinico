angular.module('theme.medicamento', ['theme.core.services'])
  .controller('medicamentoController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
      'medicamentoServices',
      'laboratorioServices',
      'medidaConcentracionServices',
      'presentacionFarmaciaServices',
      'condicionVentaServices',
      'viaAdministracionServices',
      'formaFarmaceuticaServices',
      'principioActivoServices',
      'almacenFarmServices',
      'tipoProductoServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      medicamentoServices,
      laboratorioServices,
      medidaConcentracionServices,
      presentacionFarmaciaServices,
      condicionVentaServices,
      viaAdministracionServices,
      formaFarmaceuticaServices,
      principioActivoServices,
      almacenFarmServices,
      tipoProductoServices ){
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'medicamento';
    $scope.fBusqueda = {
      generico: 'all',
      busquedaTipoProducto: {id: '0'}
    }; 
    $scope.fData = {};
    $scope.fData.generico = 2;
    $scope.fData.agregarMedicamento = 'no';
    $scope.fData.excluyeigv = false;
    $scope.metodos = {}; 
    $scope.listaTipoMedic = [ 
      { id: 'all', 'descripcion': 'TODOS' }, 
      { id: 2, 'descripcion': 'DE MARCA' }, 
      { id: 1, 'descripcion': 'GENERICO' }
    ];
    var paramDatos = {
            modulo: 3 //Farmacia
          }
          tipoProductoServices.sListarTipoProductoCbo(paramDatos).then(function (rpta){
            //console.log('Lista Tipos de Productos: ', rpta.datos);
            $scope.listaBusquedaTipoProductos = rpta.datos;
            $scope.listaBusquedaTipoProductos.splice(0,0,{ id : '0', descripcion:'TODOS'});
            $scope.fBusqueda.busquedaTipoProducto = $scope.listaBusquedaTipoProductos[0];
          });
    // LABORATORIOS 
    $scope.getLaboratoriosAutocomplete = function (value) { 
      var params = {
        search: value,
        sensor: false
      }
      return laboratorioServices.sListarlaboratorioPorAutocompletado(params).then(function(rpta) { 
        $scope.noResultsLD = false;
        if( rpta.flag === 0 ){
          $scope.noResultsLD = true;
        }
        return rpta.datos; 
      });
    }
    $scope.getSelectedLaboratorio = function ($item, $model, $label) { console.log($item,$model, $label);
        $scope.fData.idlaboratorio = $item.id; 
    };
    // PRESENTACION - GENERICO 
    /*
    $scope.ListarPresentacionGenerico = function() {
      medicamentoServices.sListarPresentacionGenericoCbo().then(function (rpta) {
        $scope.listaPresentacionGenerico = rpta.datos; 
        $scope.listaPresentacionGenerico.splice(0,0,{ id : '', descripcion:'-- Seleccione Presentacion --'}); 
        $scope.fData.idpresentacion = $scope.listaPresentacionGenerico[0].id;
      });
    }
    */
    // PRESENTACION - DE MARCA 
    $scope.ListarPresentacionMarca = function() {
      presentacionFarmaciaServices.sListarPresentacionDeMarcaCbo().then(function (rpta) { 
        $scope.listaPresentacionMarca = rpta.datos;
        $scope.listaPresentacionMarca.splice(0,0,{ id : '', descripcion:'-- Seleccione Presentacion --'}); 
        // $scope.fData.idpresentacion = $scope.listaPresentacionMarca[0].id;
        
      });
    }

    // MEDIDAS DE CONCENTRACION 
    $scope.ListarMedidasConcentracion = function() {
      medidaConcentracionServices.sListarMedidasConcentracionCbo().then(function (rpta) {
        $scope.listaMedidasConcentracion = rpta.datos;
        $scope.listaMedidasConcentracion.splice(0,0,{ id : '', descripcion:'-- Seleccione Medida de Concentración --'}); 
        // $scope.fData.idmedidaconcentracion = $scope.listaMedidasConcentracion[0].id;
      });
    }

    // CONDICION DE VENTA 
    $scope.ListarCondicionVenta = function() {
      condicionVentaServices.sListarCondicionesVenta().then(function (rpta) {
        $scope.listaCondicionesVenta = rpta.datos;
        $scope.listaCondicionesVenta.splice(0,0,{ id : '', descripcion:'-- Seleccione Condicion de Venta --'}); 
        // $scope.fData.idcondicionventa = $scope.listaCondicionesVenta[0].id;
      });
    }

    // VIAS DE ADMINISTRACION 
    $scope.ListarViasAdministracion = function() {
      viaAdministracionServices.sListarViasAdministracion().then(function (rpta) {
        $scope.listaViasAdministracion = rpta.datos;
        $scope.listaViasAdministracion.splice(0,0,{ id : '', descripcion:'-- Seleccione Vía de Administración --'}); 
        // $scope.fData.idviaadministracion = $scope.listaViasAdministracion[0].id;
      });
    }

    // FORMA FARMACEUTICA 
    $scope.ListarFormaFarmaceutica = function() {
      formaFarmaceuticaServices.sListarFormasFarmaceuticasCbo().then(function (rpta) {
        $scope.listaFormasFarmaceuticas = rpta.datos;
        $scope.listaFormasFarmaceuticas.splice(0,0,{ id : '', descripcion:'-- Seleccione Forma Farmaceutica --'}); 
        // $scope.fData.idformafarmaceutica = $scope.listaFormasFarmaceuticas[0].id;
      }); 
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: true,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idmedicamento', displayName: 'ID', width: 60, sort: { direction: uiGridConstants.DESC} },
        { field: 'codigo_barra', name: 'codigo_barra', displayName: 'COD. BARRA', width: 96,  visible:false },
        { field: 'medicamento', name: 'medicamento', displayName: 'Medicamento', minWidth:200},
        { field: 'presentacion', name: 'presentacion', displayName: 'Presentación', width: 110 },
        { field: 'laboratorio', name: 'nombre_lab', displayName: 'Laboratorio', width: 220 },
        { field: 'medidaconcentracion', name: 'descripcion_mc', displayName: 'Med. Concentración', width: 140 },
        { field: 'condicionventa', name: 'descripcion_cv', displayName: 'Condic. Venta', width: 140 },
        { field: 'viaadministracion', name: 'descripcion_va', displayName: 'Vía Admin.', width: 140 },
        { field: 'formafarmaceutica', name: 'descripcion_ff', displayName: 'Forma Farm.', width: 130 },
        // { field: 'estadoGen', type: 'object', name: 'generico', displayName: 'Tipo', width: 160, enableFiltering: false, 
        //   cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' },
        { field: 'estadoMed', type: 'object', name: 'estado_med', displayName: 'Estado', width: 120, enableFiltering: false, 
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
              'idmedicamento' : grid.columns[1].filters[0].term,
              'codigo_barra' : grid.columns[2].filters[0].term,
              'denominacion' : grid.columns[3].filters[0].term,
              '(CASE WHEN generico = 1 THEN idunidadmedida ELSE pr.descripcion_pres END)' : grid.columns[4].filters[0].term,
              'nombre_lab' : grid.columns[5].filters[0].term,
              'descripcion_mc' : grid.columns[6].filters[0].term,
              'descripcion_cv' : grid.columns[7].filters[0].term,
              'descripcion_va' : grid.columns[8].filters[0].term,
              'descripcion_ff' : grid.columns[9].filters[0].term
            } 
            $scope.getPaginationServerSide();
          });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      var arrParams = {
        paginate : paginationOptions,
        datos: $scope.fBusqueda
      };
      medicamentoServices.sListarmedicamento(arrParams).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
      console.log($scope.fBusqueda.busquedaTipoProducto);
      /*if($scope.fBusqueda.busquedaTipoProducto == 0){
        $scope.fData.idtipoproducto = listaTipoProductos[0].id;
      }*/
    };
    $scope.getPaginationServerSide();
    // $scope.ListarPresentacionGenerico();
    $scope.ListarPresentacionMarca();
    $scope.ListarMedidasConcentracion();
    $scope.ListarCondicionVenta();
    $scope.ListarFormaFarmaceutica();
    $scope.ListarViasAdministracion();

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    $scope.btnEditar = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'medicamento/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.accion = 'edit';

          $scope.fData = {};
          $scope.getSelectedLaboratorio = function ($item, $model, $label) { 
              $scope.fData.idlaboratorio = $item.id; 
          };
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }
          $scope.fData.agregarMedicamento = 'si';
          $scope.titleForm = 'Edición de Medicamento'; 

          $scope.getTableHeight = function (argument) {
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return {
                height: ($scope.gridOptionsAlmacenes.data.length * rowHeight + headerHeight + 30) + "px"
             };
          }
          $scope.gridOptionsAlmacenes = {
            paginationPageSizes: [10, 50],
            minRowsToShow: 4,
            paginationPageSize: 10,
            enableCellEditOnFocus: true,
            enableFiltering: false,
            columnDefs: [ 
              { field: 'id', name: 'idalmacen', displayName: 'ID', width: 60,  sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
              { field: 'sede', name: 'sede', displayName: 'SEDE', width: 150, enableCellEdit: false, visible: false },
              { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: 168, enableCellEdit: false },
              { field: 'almacen', name: 'nombre_alm', displayName: 'ALMACEN', enableCellEdit: false  },
              { field: 'subalmacen', name: 'nombre_salm', displayName: 'SUBALMACEN', enableCellEdit: false  },
              { field: 'precio', name: 'precio', displayName: 'P. VENTA', width: 110, enableCellEdit: true, 
                enableColumnMenus: false, enableColumnMenu: false, type: 'float', cellClass:'text-center ui-editCell',cellTemplate: '<span>{{ COL_FIELD }}</span>',enableSorting: false }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
            }
          }; 
          console.log($scope.fData);

          var arrParams = { 
            'idmedicamento': $scope.fData.id
          }; 
          // almacenFarmServices.sListarAlmacenesEdicionSession(arrParams).then(function (rpta) {
          //   $scope.gridOptionsAlmacenes.data = rpta.datos; 
          // });

          almacenFarmServices.sListarAlmacenesParaMedicamentoSession(arrParams).then(function (rpta) {
            $scope.gridOptionsAlmacenes.data = rpta.datos;
          });

          // LISTA DE TIPOS DE PRODUCTOS DEL MODULO FARMACIA
          var paramDatos = {
            modulo: 3 //Farmacia
          }
          tipoProductoServices.sListarTipoProductoCbo(paramDatos).then(function (rpta){
            console.log('Lista Tipos de Productos: 2', rpta.datos);
            $scope.listaTipoProductos = rpta.datos;
            //$scope.fData.idtipoproducto = $scope.listaTipoProductos[0].id;
          });
          /********* MODULOS PARA INGRESO DIRECTO DESDE EL MODAL ************/
          $scope.nuevaPresentacionMarca = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'presentacionFarmacia/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro Presentacion de Marca';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  presentacionFarmaciaServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarPresentacionMarca();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevoLaboratorio = function (size) {
            $scope.fData.laboratorio = null ;
            $uibModal.open({
              templateUrl: angular.patchURLCI+'Laboratorio/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Laboratorio';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  laboratorioServices.sRegistrar($scope.fData).then(function (rpta) {
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
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaMedida = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'MedidaConcentracion/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro Medida de Concentracion';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  medidaConcentracionServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarMedidasConcentracion();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaCondicionVenta = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'CondicionVenta/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Condicion Venta';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  condicionVentaServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarCondicionVenta();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaViaAdministracion = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'ViaAdministracion/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Via de Administracion';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  viaAdministracionServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarViasAdministracion();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaFormaFarmaceutica = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'FormaFarmaceutica/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Forma Farmaceutica';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  formaFarmaceuticaServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarFormaFarmaceutica();
                      $modalInstance.dismiss('cancel');
                      //$scope.getPaginationServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          /********* FIN DE MODULOS DE INGRESO DIRECTO DESDE EL MODAL ******/
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }
          $scope.aceptar = function () { 
            $scope.fData.almacenes = $scope.gridOptionsAlmacenes.data; 
            medicamentoServices.sEditar($scope.fData).then(function (rpta) {
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
              $scope.getPaginationServerSide();
              $scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
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
    $scope.btnNuevo = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'medicamento/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        //keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.accion = 'reg';
          $scope.titleForm = 'Registro de Producto';
          //$scope.ListarPresentacionMarca();
          $scope.fData.idpresentacion = $scope.listaPresentacionMarca[0].id;
          $scope.fData.idmedidaconcentracion = $scope.listaMedidasConcentracion[0].id;
          $scope.fData.idcondicionventa = $scope.listaCondicionesVenta[0].id;
          $scope.fData.idviaadministracion = $scope.listaViasAdministracion[0].id;
          $scope.fData.idformafarmaceutica = $scope.listaFormasFarmaceuticas[0].id;          
          
          $scope.getTableHeight = function (argument) {
            var rowHeight = 30; // your row height 
            var headerHeight = 30; // your header height 
            return {
              height: ($scope.gridOptionsAlmacenes.data.length * rowHeight + headerHeight + 30) + "px"
            };
          }
          $scope.gridOptionsAlmacenes = {
            paginationPageSizes: [10, 50],
            minRowsToShow: 4,
            paginationPageSize: 10,
            enableCellEditOnFocus: true,
            enableFiltering: false,
            columnDefs: [ 
              { field: 'id', name: 'idalmacen', displayName: 'ID', width: 60,  sort: { direction: uiGridConstants.ASC}, enableCellEdit: false },
              { field: 'sede', name: 'sede', displayName: 'SEDE', width: 150, enableCellEdit: false, visible: false },
              { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: 168, enableCellEdit: false },
              { field: 'almacen', name: 'nombre_alm', displayName: 'ALMACEN', enableCellEdit: false  },
              { field: 'subalmacen', name: 'nombre_salm', displayName: 'SUBALMACEN', enableCellEdit: false  },
              { field: 'precio', name: 'precio', displayName: 'P. VENTA', width: 110, enableCellEdit: true, 
                enableColumnMenus: false, enableColumnMenu: false, type: 'float', cellClass:'text-center ui-editCell',cellTemplate: '<span>{{ COL_FIELD }}</span>',enableSorting: false }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
            }
          };          
          almacenFarmServices.sListarAlmacenesParaMedicamentoSession().then(function (rpta) {
            $scope.gridOptionsAlmacenes.data = rpta.datos;
          });
          // LISTA DE TIPOS DE PRODUCTOS DEL MODULO FARMACIA
          var paramDatos = {
            modulo: 3 //Farmacia
          }
          tipoProductoServices.sListarTipoProductoCbo(paramDatos).then(function (rpta){
            console.log('Lista Tipos de Productosss: ', rpta.datos);
            $scope.listaTipoProductos = rpta.datos;
            //$scope.fData.idtipoproducto = $scope.listaTipoProductos[0].id;
            if($scope.fBusqueda.busquedaTipoProducto.id == 0){
            $scope.fData.idtipoproducto = $scope.listaTipoProductos[0].id;
          } else {
            $scope.fData.idtipoproducto = $scope.fBusqueda.busquedaTipoProducto.id;
          }
          });
          /********* MODULOS PARA INGRESO DIRECTO DESDE EL MODAL ************/
          $scope.nuevaPresentacionMarca = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'presentacionFarmacia/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro Presentacion de Marca';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  presentacionFarmaciaServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarPresentacionMarca();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevoLaboratorio = function (size) {
            $scope.fData.laboratorio = null ;
            $uibModal.open({
              templateUrl: angular.patchURLCI+'Laboratorio/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Laboratorio';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  laboratorioServices.sRegistrar($scope.fData).then(function (rpta) {
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
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaMedida = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'MedidaConcentracion/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro Medida de Concentracion';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  medidaConcentracionServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarMedidasConcentracion();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaCondicionVenta = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'CondicionVenta/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Condicion Venta';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  condicionVentaServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarCondicionVenta();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaViaAdministracion = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'ViaAdministracion/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Via de Administracion';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  viaAdministracionServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarViasAdministracion();
                      $modalInstance.dismiss('cancel');
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          $scope.nuevaFormaFarmaceutica = function (size) {
            $uibModal.open({
              templateUrl: angular.patchURLCI+'FormaFarmaceutica/ver_popup_formulario',
              size: size || '',
              backdrop: 'static',
              keyboard:false,
              scope:$scope,
              controller: function ($scope, $modalInstance, getPaginationServerSide) {
                $scope.getPaginationServerSide = getPaginationServerSide;
                $scope.fData = {};
                $scope.titleForm = 'Registro de Forma Farmaceutica';
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  formaFarmaceuticaServices.sRegistrar($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.ListarFormaFarmaceutica();
                      $modalInstance.dismiss('cancel');
                      //$scope.getPaginationServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });
                }
              },
              resolve: {
                getPaginationServerSide: function() {
                  return $scope.getPaginationServerSide;
                }
              }
            });
          }
          /********* FIN DE MODULOS DE INGRESO DIRECTO DESDE EL MODAL ******/
          
          $scope.aceptar = function () { 
            $scope.fData.almacenes = $scope.gridOptionsAlmacenes.data;
            medicamentoServices.sRegistrar($scope.fData).then(function (rpta) {
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
              angular.forEach($scope.listaBusquedaTipoProductos, function(value, key) {
                if($scope.fData.idtipoproducto == value.id){
                  $scope.fBusqueda.busquedaTipoProducto = $scope.listaBusquedaTipoProductos[key];
                }
              });
              $scope.getPaginationServerSide();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

    $scope.btnPrincipioActivo = function () { 
      $uibModal.open({
        templateUrl: angular.patchURLCI+'medicamento/ver_popup_agregar_principio_activo',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.accion = 'reg';
          $scope.titleForm = 'Agregar Principio Activo al Medicamento : ';
          $scope.titleMedicamento = $scope.mySelectionGrid[0].medicamento;
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          var paginationOptionsMed = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsMedicamentos = { 
            paginationPageSizes: [10, 50, 100, 500, 1000], 
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: true,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [
              { field: 'id', name: 'idprincipioactivo', displayName: 'ID', width: 70 },
              { field: 'descripcion', name: 'descripcion', displayName: 'PRINCIPIO ACTIVO', width: 390,  sort: { direction: uiGridConstants.ASC} },
              { field: 'accion', displayName: 'Acción', maxWidth: 95, enableFiltering: false, enableSorting: false ,cellTemplate:'<button type="button" class="btn btn-sm btn-primary center-block" ng-click="grid.appScope.btnAgregarPrincipio(row,mensaje)" title="AGREGAR"> <i class="fa fa-sign-out"></i> </button>' }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) { 
                  paginationOptionsMed.sort = null;
                  paginationOptionsMed.sortName = null;
                } else {
                  paginationOptionsMed.sort = sortColumns[0].sort.direction;
                  paginationOptionsMed.sortName = sortColumns[0].name;
                }
                $scope.getPaginationServerSideMed();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsMed.pageNumber = newPage;
                paginationOptionsMed.pageSize = pageSize;
                paginationOptionsMed.firstRow = (paginationOptionsMed.pageNumber - 1) * paginationOptionsMed.pageSize;
                $scope.getPaginationServerSideMed();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsMed.search = true;
                  paginationOptionsMed.searchColumn = {
                    'idprincipioactivo' : grid.columns[0].filters[0].term,
                    'descripcion' : grid.columns[1].filters[0].term
                  }
                  $scope.getPaginationServerSideMed();
                });

            }
          };
          paginationOptionsMed.sortName = $scope.gridOptionsMedicamentos.columnDefs[1].name;
          $scope.getPaginationServerSideMed = function() {
            console.log("datos "+ $scope.mySelectionGrid[0]);
            var arrParams = {
              paginate : paginationOptionsMed,
              datos : $scope.fData = $scope.mySelectionGrid[0]
            };
            principioActivoServices.sListarSinprincipioActivoxMed(arrParams).then(function (rpta) {
              $scope.gridOptionsMedicamentos.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsMedicamentos.data = rpta.datos;
            });
            $scope.mySelectionGridMed = [];
          };
          $scope.getPaginationServerSideMed();

          $scope.btnAgregarPrincipio = function (row,mensaje) { 
                var arrParams = {
                  idprincipio : row.entity.id,
                  idmedicamento : $scope.mySelectionGrid[0].id
                };
                principioActivoServices.sRegistrarprincipioMed(arrParams).then(function (rpta) {
                  if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.getPaginationServerSideMed();
                      $scope.getPaginationServerSidePriMed();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                });
          }

          $scope.getTableHeight = function (argument) {
             var rowHeight = 30; // your row height 
             var headerHeight = 30; // your header height 
             return {
                height: ($scope.gridOptionsAddMedicamento.data.length * rowHeight + headerHeight + 30) + "px"
             };
          }
          $scope.gridOptionsAddMedicamento = { 
            minRowsToShow: 10,
            paginationPageSize: 10,
            columnDefs: [ 
              { field: 'id', name: 'idmedicamento', displayName: 'ID', width: 70,  sort: { direction: uiGridConstants.ASC} },
              { field: 'descripcion', name: 'descripcion', displayName: 'Principio Activo' },
              { field: 'accion', name:'accion', displayName: 'ACCION', width: 95, 
              cellTemplate:'<div class="">'+
                '<button type="button" class="btn btn-sm btn-danger inline-block m-xs" ng-click="grid.appScope.btnQuitarDeLaCesta(row,mensaje)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>' 
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
            }
          }; 
          $scope.getPaginationServerSidePriMed = function() {
            var arrParams = {
              datos : $scope.fData = $scope.mySelectionGrid[0]
            };
            principioActivoServices.sListarprincipioActivoxMed(arrParams).then(function (rpta) {
              $scope.gridOptionsAddMedicamento.data = rpta.datos;
            });
          };
          $scope.getPaginationServerSidePriMed();

          $scope.btnQuitarDeLaCesta = function (row,mensaje) { 
            var arrParams = {
              id : row.entity.id,
            };
            principioActivoServices.sAnularprincipioMed(arrParams).then(function (rpta) {
              if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationServerSideMed();
                  $scope.getPaginationServerSidePriMed();
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          medicamentoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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

    /* ============================ */
    /* HABILITAR Y DESHABILITAR     */
    /* ============================ */
    $scope.btnHabilitar = function (mensaje) {
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          medicamentoServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
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
          medicamentoServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
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
        description: 'Nuevo Medicamento',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({
        combo: 'e',
        description: 'Editar Medicamento',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({
        combo: 'del',
        description: 'Anular Medicamento',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnDeshabilitar();
          }
        }
      })
      .add ({
        combo: 'b',
        description: 'Buscar Medicamento',
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
  .service("medicamentoServices",function($http, $q) {
    return({
        sListarMedicamentosAutoCompleteParaFarmacia: sListarMedicamentosAutoCompleteParaFarmacia,
        sListarmedicamento: sListarmedicamento,
        sListarMedicamentoPorCodigo: sListarMedicamentoPorCodigo,
        sListarMedicamentosAutoComplete: sListarMedicamentosAutoComplete,
        sListarPresentacionGenericoCbo: sListarPresentacionGenericoCbo,
        sRegistrar: sRegistrar,
        sEditarCodigoBarraMedicamento: sEditarCodigoBarraMedicamento,
        sEditar: sEditar,
        sHabilitar: sHabilitar,
        sDeshabilitar: sDeshabilitar,
        sAnular: sAnular,
        sEditarFormula: sEditarFormula,
        sEliminarFormula: sEliminarFormula,
    });
    
    function sListarMedicamentosAutoCompleteParaFarmacia (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/lista_medicamento_autocomplete_para_farmacia",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarmedicamento(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/lista_medicamento",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentosAutoComplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/lista_medicamento_autocomplete",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicamentoPorCodigo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/carga_medicamento_por_codigo",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
     function sListarPresentacionGenericoCbo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/lista_medida_cbo",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/registrar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/editar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarCodigoBarraMedicamento (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Medicamento/actualizar_codigo_barra_producto",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/habilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/deshabilitar",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/anular",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarFormula (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/editar_formula",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEliminarFormula (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamento/eliminar_formula",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

  });