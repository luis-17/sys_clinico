angular.module('theme.campania', ['theme.core.services'])
  .controller('campaniaController', ['$scope', '$sce', '$filter', '$modal', '$interval','$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'campaniaServices', 
    'productoServices', 
    'tipoProductoServices',
    'empresaAdminServices',
    'especialidadServices', 
    function($scope, $sce, $filter, $modal, $interval,$bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys,
      campaniaServices, 
      productoServices,
      tipoProductoServices,
      empresaAdminServices,
      especialidadServices
       ){ 
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'campania';
    var hoy = new Date();
    $scope.fBusqueda = {};
    // $scope.fBusqueda.desde = $filter('date')(hoy,'dd-MM-yyyy');
    // $scope.fBusqueda.hasta = $filter('date')(hoy,'dd-MM-yyyy');
    // $scope.fBusqueda.desdeHora = '00';
    // $scope.fBusqueda.desdeMinuto = '00';
    // $scope.fBusqueda.hastaHora = '23';
    // $scope.fBusqueda.hastaMinuto = '59';
    // $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
    // $scope.pHora = /^([0-1][0-9]|[2][0-3])$/;
    // $scope.pMinuto = /^[0-5][0-9]$/;
    $scope.fData={};
    // EMPRESA SEDE 
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      $scope.sede_empresa_admin = $scope.fSessionCI.idsedeempresaadmin;
      $scope.getPaginationServerSide();
      $scope.getPaginationServerSideDetalle();
    });
   
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    // GRILLA PRINCIPAL
    $scope.mySelectionGrid = [];
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
      multiSelect: false,
      data:[],
      columnDefs: [
        { field: 'id', name: 'camp.idcampania', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'tipo', displayName : 'Tipo' , width: 120, filter: {
          term: 0,
          type: uiGridConstants.filter.SELECT,
          selectOptions: [{ value:0 , label:'TODOS'} , { value: 1, label: 'CAMPAÑA' }, { value: 2, label: 'CUPON' }]
        }},
        { field: 'especialidad', name: 'e.nombre', displayName: 'Especialidad' }, 
        { field: 'campania', name: 'camp.descripcion', displayName: 'Campania',minWidth: 400 },
        { field: 'fecha_inicio', name: 'fc.fecha_inicio', displayName: 'Fec.Inicio' , enableFiltering: false, enableSorting: false, maxWidth: 100 },
        { field: 'fecha_final', name: 'fc.fecha_final', displayName: 'Fec. Final' , enableFiltering: false, enableSorting: false, maxWidth: 100 },
        { field: 'estado', type: 'object', name: 'camp.estado', displayName: 'Estado', maxWidth: 120, enableFiltering: false, enableSorting: false, 
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 110px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = { 
            'camp.idcampania' : grid.columns[1].filters[0].term,
            'camp.tipo_campania' : grid.columns[2].filters[0].term,
            'e.nombre' : grid.columns[3].filters[0].term,
            'camp.descripcion' : grid.columns[4].filters[0].term,
            'fc.fecha_inicio' : grid.columns[5].filters[0].term,
            'fc.fecha_final' : grid.columns[6].filters[0].term
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
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        idcampania : 0,
        idpaquete :0,
        datos : $scope.fBusqueda,
      };
      campaniaServices.sListarCampanias($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };
    // $scope.getPaginationServerSide();
    

    /* ===================================*/
    /*         CREA Y EDITA CAMPAÑA       */
    /* ===================================*/
    $scope.btnRegEdit = function (accion) 
    {
      $scope.accion = accion;
      $modal.open({
        templateUrl: angular.patchURLCI+'campania/ver_popup_formulario',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.mySelectionGrid = arrToModal.mySelectionGrid;
          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.fData = {};
          $scope.listaTipoCampania = [
            { id: 1 , descripcion:'CAMPAÑA' },
            { id: 2 , descripcion:'CUPON' }
          ];
          var arrFechasVenta = {};   // Array para las fechas venta
          var arrFechasAtencion = {};  // Array para las fechas atencion
          var arrVentasAfter = [];     // Guardamos los datos que traemos de la BD en Edicion
          var arrAtencionAfter = [];   // Guardamos los datos que traemos de la BD en Edicion
          $scope.errfec = {} ;
          $scope.errfec.valor = true ;       // Icon para ver si se llenaron las fechas

          if( $scope.accion == 'reg' ){
            $scope.titleForm = 'Registro de Campaña';
            //var hoy = new Date();
            //$scope.fData.fecha_inicio = $filter('date')(hoy,'dd-MM-yyyy');
            //$scope.fData.fecha_final = $filter('date')(hoy,'dd-MM-yyyy');
            //$scope.fData.desdeHora = '00';
            //$scope.fData.desdeMinuto = '00';
            //$scope.fData.hastaHora = '23';
            //$scope.fData.hastaMinuto = '59';
            $scope.fData.sedeempresa = angular.copy($scope.fBusqueda.sedeempresa);
            $scope.fData.tipocampania = $scope.listaTipoCampania[0].id;
          }else{
            
            if( $scope.mySelectionGrid.length == 1 ){ 
              $scope.fData = $scope.mySelectionGrid[0]; // se llena el scope.fData para mostrar los datos en el form
              $scope.fData.fechasventa = [];
              $scope.fData.fechasatencion = [];
            }else{
              alert('Seleccione una sola fila');
            }
            $scope.titleForm = 'Edición de Campaña';            
          }

          $scope.getEspecialidadAutocomplete = function (value) {
            var params = {
              search: value,
              sensor: false
            }
            return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
              $scope.noResultsLEspecialidad = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLEspecialidad = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedEspecialidad = function ($item, $model, $label){
            $scope.fData.idespecialidad = $item.id;
          };

          $scope.getCampaniasAutocomplete = function (val) {
            var params = {
              search: val,
              sensor: false
            }
            return campaniasServices.sListarCampaniasCbo(params).then(function(rpta) {
              var data = rpta.datos.map(function(e) { 
                return e.nombre;
              });
              return data;
            });
          }
          // grilla de paquetes
          $scope.gridOptionsPaqueteAdd = {
            minRowsToShow: 7,
            paginationPageSize: 50,
            enableSelectAll: false,
            multiSelect: false,
            data: [],
            columnDefs: [ 
              { field: 'id', name: 'idpaquete', displayName: 'ID', width: 70, visible: true },
              { field: 'paquete', name: 'descripcion', displayName: 'PAQUETE', },
              { field: 'monto_total', name: 'monto_total', displayName: 'MONTO', width: 90, cellClass:"text-right"},
              { field: 'accion2', displayName: '', width: 120, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-info center-block" ng-click="grid.appScope.btnAddProducto(row)" ng-show="row.entity.id"> <i class="fa fa-plus"></i>PRODUCTOS </button>', },
              { field: 'accion', displayName: '', width: 60, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' },
              
            ],
            onRegisterApi: function(gridApiPq) {
              $scope.gridApiPq = gridApiPq; 
            }
          };
          $scope.getPaginationServerSidePq = function() {
            console.log("pagination");
            $scope.datosGrid = {
              datos : $scope.fData.id
            };
            campaniaServices.sListarPaqueteCbo($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsPaqueteAdd.data = rpta.datos;              
            });

            campaniaServices.sListarFechas($scope.datosGrid).then(function (rpta) {
              angular.forEach(rpta.datos,function (value,key){
                var objfecha = new Date(value.fecha);
                if(value.tipo_fecha == 1){
                  arrVentasAfter.push(objfecha.getTime()+18000000);  // Aumento 18000000 para cuadrar el dia del calendario
                }else{
                  arrAtencionAfter.push(objfecha.getTime()+18000000); // Aumento 18000000 para cuadrar el dia del calendario
                }
              });
              arrFechasVenta = angular.copy(arrVentasAfter) ;
              arrFechasAtencion = angular.copy(arrAtencionAfter);
              $scope.errfec.valor = (arrFechasAtencion.length > 0 && arrFechasVenta.length > 0) ? false : true ;
            });
          }

          if($scope.accion == 'reg'){
            $scope.gridOptionsPaqueteAdd.columnDefs[3].visible = false; 
          }else{            
            $scope.getPaginationServerSidePq();           
          }
          $scope.agregarPaqueteItem = function () { 
            if( $scope.fData.temporal.paquete.length < 1 ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha llenado el campo: Paquete', type: 'warning', delay: 3500 });
              return false;
            }

            var productNew = true;
            angular.forEach($scope.gridOptionsPaqueteAdd.data, function(value, key) { 
              if(value.paquete == $scope.fData.temporal.paquete ){ 
                productNew = false;
              }
            });
            if( productNew === false ){ 
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El paquete ya ha sido agregado a la cesta.', type: 'warning', delay: 3000 });
              $scope.fData.temporal.paquete= null;
              return false;
            }

            $scope.arrTemporal = { 
                'paquete': $scope.fData.temporal.paquete,
                'es_nuevo' : true,
            };
            $scope.gridOptionsPaqueteAdd.data.push($scope.arrTemporal);
            $scope.fData.temporal = {};
            // setTimeout(function() {
            $('#paquete').focus(); 
            // }, 1000);
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            if( row.entity.es_nuevo ){
              var index = $scope.gridOptionsPaqueteAdd.data.indexOf(row.entity); 
              $scope.gridOptionsPaqueteAdd.data.splice(index,1);

            }else{
              console.log('No es nuevo');
              console.log(row.entity.id);
              
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){
                  var paramDatos = {
                    'idpaquete' : row.entity.id,
                  }
                  campaniaServices.sAnularPaquete(paramDatos).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      var index = $scope.gridOptionsPaqueteAdd.data.indexOf(row.entity); 
                      $scope.gridOptionsPaqueteAdd.data.splice(index,1);
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    $scope.getPaginationServerSide();
                    $scope.getPaginationServerSidePq(); // grilla de paquetes
                  });
                }
              });
              
            }

          }
          // MODAL SECUNDARIO
          $scope.btnAddProducto = function (rowPq) { 
            // $scope.idpaqueteseleccionado = rowPq.entity.id;
            // $scope.paqueteseleccionado = rowPq.entity.descripcion;
            var idpaqueteseleccionado = rowPq.entity.id;
            var paqueteSeleccionado = rowPq.entity.descripcion;
            var montoSeleccionado = rowPq.entity.monto_total;
            $modal.open({
              templateUrl: angular.patchURLCI+'campania/ver_popup_formulario_paquete_detalle',
              size: 'md',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance, getPaginationServerSidePq) { // console.log(arrToModal); 
                // $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
                // $scope.getPaginationServerSidePq = arrToModal.getPaginationServerSidePq;
                $scope.getPaginationServerSidePq = getPaginationServerSidePq;
                $scope.fDataProd = {};
                $scope.fDataProd.idpaquete = idpaqueteseleccionado;
                $scope.fDataProd.monto_total = montoSeleccionado;
                $scope.fBusqueda={};

                $scope.titleForm = 'Productos de ' + paqueteSeleccionado;
                
                var paginationOptionsProd = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.gridOptionsProductoAdd = {
                  minRowsToShow: 7,
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  useExternalFiltering : true,
                  enableGridMenu: true,
                  // enableRowSelection: true,
                  enableSelectAll: true,
                  enableFiltering: false,
                  enableFullRowSelection: true,
                  enableCellEditOnFocus: true,
                  data: [],
                  multiSelect: true,
                  columnDefs: [
                    { field: 'id', name: 'd.iddetallepaquete', displayName: 'ID', minWidth: 60,  sort: { direction: uiGridConstants.ASC}, visible: false},
                    { field: 'especialidad', name: 'e.nombre', displayName: 'Especialidad',minWidth: 180 },
                    { field: 'descripcion', name: 'pm.descripcion', displayName: 'Producto',minWidth: 180 },
                    { field: 'precio', name: 'd.precio', displayName: 'Precio',minWidth: 90, enableCellEdit: true, cellClass:'ui-editCell text-right' },
                    { field: 'accion', displayName: 'Acción', maxWidth: 95, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
                  ],
                  onRegisterApi: function(gridApi) {
                    $scope.gridApi3 = gridApi;
                    gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                      rowEntity.column = colDef.field;
                      if(rowEntity.column == 'precio'){
                        if( !(rowEntity.precio >= 0) ){
                          var pTitle = 'Advertencia!';
                          var pType = 'warning';
                          rowEntity.precio = oldValue;
                          pinesNotifications.notify({ title: pTitle, text: 'El Precio debe ser mayor o igual a 0', type: pType, delay: 3500 });
                          return false;
                        }
                      }
                      $scope.calcularTotales();
                      $scope.$apply();
                    });
                  }
                };
                $scope.getPaginationServerSideProd = function() {
                  $scope.datosGrid = {
                    idpaquete : $scope.fDataProd.idpaquete
                  };
                  campaniaServices.sListarDetallePaqueteId($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsProductoAdd.data = rpta.datos;
                    if($scope.gridOptionsProductoAdd.data.length)
                      $scope.fDataVenta.temporal.monto_total = rpta.datos[0].monto;
                  });
                  // $scope.mySelectionGridProd = [];
                };
                $scope.getPaginationServerSideProd();
               
                $scope.getEspecialidadAutocomplete = function (value) {
                  var params = {
                    search: value,
                    sensor: false
                  }
                  return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
                    $scope.noResultsLEspecialidad = false;
                    if( rpta.flag === 0 ){
                      $scope.noResultsLEspecialidad = true;
                    }
                    return rpta.datos; 
                  });
                }

                $scope.getSelectedEspecialidad = function ($item, $model, $label) {
                    $scope.fDataProd.idespecialidad = $item.id;
                };
                $scope.getProductoAutocomplete = function (value) { 
                  if( $scope.fDataVenta.temporal.especialidad === null || angular.isUndefined($scope.fDataVenta.temporal.especialidad) ) { 
                    pinesNotifications.notify({ title: 'Advertencia', text: 'No seleccionó ninguna especialidad.', type: 'danger', delay: 3500 });
                    $scope.fDataVenta.temporal = { 
                      especialidad : null,
                      producto: null,
                      cantidad: 1
                    };
                    return false;
                  }
                  var params = {
                    search: value, 
                    especialidadId: $scope.fDataVenta.temporal.especialidad.id,
                    sensor: false,
                    idsedeempresaadmin: $scope.fData.sedeempresa
                  }
                  return productoServices.sListarProductosSedeEmpresaAdminCboCampania(params).then(function(rpta) { 
                    $scope.noResultsLPSC = false;
                    if( rpta.flag === 0 ){
                      $scope.noResultsLPSC = true;
                    }
                    return rpta.datos; 
                  });
                } 

                $scope.getSelectedProducto = function (item, model) {
                    $scope.fDataVenta.temporal.precio = model.precioSF; 
                }

                $scope.fDataVenta={};
                $scope.fDataVenta.temporal={}; 
                // -----------------------------------
                // NUEVO PRODUCTO DE CAMPAÑA
                // -----------------------------------
                $scope.btnNuevoProducto = function (size) {
                  $modal.open({
                    templateUrl: angular.patchURLCI+'producto/ver_popup_formulario',
                    size: size || 'md',
                    backdrop: 'static',
                    keyboard:false,
                    scope: $scope,
                    controller: function ($scope, $modalInstance) {
                      //$scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
                      $scope.fData = {};
                      $scope.accion = 'reg';
                      $scope.fData.newProduct = true ;
                      // =============================================================
                      // AUTOCOMPLETADO SOLO ESPECIALIDAD 
                      // =============================================================
                      $scope.getSoloEspecialidadAutocomplete = function (value) {
                        var params = {
                          search: value,
                          sensor: false
                        }
                        return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
                          $scope.noResultsSoloEspecialidad = false;
                          if( rpta.flag === 0 ){
                            $scope.noResultsSoloEspecialidad = true;
                          }
                          return rpta.datos; 
                        });
                      }
                      $scope.getSelectedSoloEspecialidad = function ($item, $model, $label) {
                          $scope.fData.idespecialidad = $item.id;
                      };
                      $scope.getClearInputSoloEspecialidad = function () {
                        if(!angular.isObject($scope.fData.especialidad)){ 
                          $scope.fData.idespecialidad = null;
                        }
                      }
                      /* DATA GRID */ 
                      var paginationSedeEmpresaOptions = {
                        pageNumber: 1,
                        firstRow: 0,
                        pageSize: 10,
                        sort: uiGridConstants.ASC,
                        sortName: null,
                        search: null
                      };
                      $scope.gridOptionsSedeEmpresa = {
                        paginationPageSizes: [10, 50, 100, 500, 1000],
                        paginationPageSize: 10,
                        minRowsToShow: 5,
                        useExternalPagination: true,
                        useExternalSorting: true,
                        enableFiltering: false,
                        columnDefs: [
                          { field: 'razon_social', name: 'razon_social', displayName: 'EMPRESA ADMIN',sort: { direction: uiGridConstants.ASC} },
                          { field: 'descripcion', name: 'descripcion', displayName: 'SEDE', width: '25%' },
                          { field: 'precio_sede', name: 'precio_sede', displayName: 'PRECIO SEDE',width: '12%', enableCellEdit: true, cellClass:'ui-editCell', enableSorting: false } 
                        ],
                        onRegisterApi: function(gridApi) {
                          $scope.gridApi = gridApi;
                          $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                            if (sortColumns.length == 0) {
                              paginationSedeEmpresaOptions.sort = null;
                              paginationSedeEmpresaOptions.sortName = null;
                            } else {
                              paginationSedeEmpresaOptions.sort = sortColumns[0].sort.direction;
                              paginationSedeEmpresaOptions.sortName = sortColumns[0].name;
                            }
                            $scope.getPaginationSedeEmpresaServerSide();
                          });
                          gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                            paginationSedeEmpresaOptions.pageNumber = newPage;
                            paginationSedeEmpresaOptions.pageSize = pageSize;
                            paginationSedeEmpresaOptions.firstRow = (paginationSedeEmpresaOptions.pageNumber - 1) * paginationSedeEmpresaOptions.pageSize;
                            $scope.getPaginationSedeEmpresaServerSide();
                          });
                        }
                      };
                      paginationSedeEmpresaOptions.sortName = $scope.gridOptionsSedeEmpresa.columnDefs[0].name;
                      $scope.getPaginationSedeEmpresaServerSide = function() { 
                        $scope.datosGrid = { 
                          paginate : paginationSedeEmpresaOptions
                        };
                        empresaAdminServices.sListarSedeEmpresaAdmin($scope.datosGrid).then(function (rpta) { 
                          $scope.gridOptionsSedeEmpresa.totalItems = rpta.paginate.totalRows;
                          $scope.gridOptionsSedeEmpresa.data = rpta.datos;              
                        });
                        // $scope.mySelectionSedeEmpresaGrid = [];
                      };
                      $scope.getPaginationSedeEmpresaServerSide();
                      /* END DATA GRID */

                      $scope.titleForm = 'Registro de Producto';
                      var params = {
                        'modulo' : '1', // clinica
                      }          
                        // TIPO PRODUCTO
                      tipoProductoServices.sListarTipoProductoCbo(params).then(function (rpta) {
                        $scope.listaTiposProducto = rpta.datos;
                        $scope.listaTiposProducto.splice(0,0,{ id : '', descripcion:'--Seleccione tipo de Producto--'});
                        $scope.fData.idtipoproducto = $scope.listaTiposProducto[0].id;            
                      });

                      $scope.fData.solo_para_campania = true ;

                      $scope.getProductosAutocomplete = function (val) {
                        var params = {
                          search: val,
                          sensor: false
                        }
                        return productoServices.sListarProductosCboCampania(params).then(function(rpta) {
                          var data = rpta.datos.map(function(e) { 
                            return e.nombre;
                          });
                          return data;
                        });
                      }
                      $scope.cancel = function () {
                        $modalInstance.dismiss('cancel');
                      }
                      $scope.aceptar = function () {
                        $scope.fData.detalle = $scope.gridOptionsSedeEmpresa.data;
                        angular.forEach($scope.fData.detalle,function(value,key){
                          if(value.idsedeempresaadmin == $scope.sede_empresa_admin){
                            
                            if(typeof(value.precio_sede) == "undefined"){
                              $scope.fData.detalle[key].precio_sede = "0" ;
                            }
                          }
                        });

                        productoServices.sRegistrar($scope.fData).then(function (rpta) {
                          if(rpta.flag == 1){ 
                            pTitle = 'OK!';
                            pType = 'success';

                            productoServices.sListarProductosSedeEmpresaAdminCboCampaniaID(rpta.idproductomaster).then(function(product) { 
                              $scope.fDataVenta.temporal.producto = product.datos[0];
                              $scope.fDataVenta.temporal.especialidad = {"idespecialidad": product.datos[0].idespecialidad , "especialidad" : product.datos[0].especialidad };
                              $("#temporalProducto").val(product.datos[0].descripcion);  
                              $("#temporalPrecio").val(product.datos[0].precioSF);
                              $timeout(function() {
                                $("#temporalEspecialidad").val(product.datos[0].especialidad);
                              });

                            });

                            $modalInstance.dismiss('cancel');
                          }else if(rpta.flag == 0){
                            var pTitle = 'Error!';
                            var pType = 'danger';
                          }else if(rpta.flag == 2){
                            var pTitle = 'Advertencia!';
                            var pType = 'warning';}
                          else{
                            alert('Error inesperado');
                          }
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
                        });
                      }
                    }
                  });
                }
                // FIN NUEVO PRODUCTO
                $scope.agregarItem = function (mensaje) {
                  $('#temporalEspecialidad').focus();
                  if(!angular.isObject($scope.fDataVenta.temporal.especialidad) ){ 
                    $scope.fDataVenta.temporal.especialidad = null; 
                    pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado la especialidad', type: 'warning', delay: 3000 }); 
                    return false; 
                  }
                  if(!angular.isObject($scope.fDataVenta.temporal.producto) ){ 
                    $scope.fDataVenta.temporal.producto = null;
                    $('#temporalProducto').focus();
                    pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 3000 });
                    return false;
                  }
                  var productNew = true;
                  angular.forEach($scope.gridOptionsProductoAdd.data, function(value, key) { 
                    if(value.id == $scope.fDataVenta.temporal.producto.id ){ 
                      productNew = false;
                    }
                  });
                  if( productNew === false ){ 
                    pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 3000 });
                    $scope.fDataVenta.temporal.producto = null;
                    $scope.fDataVenta.temporal.cantidad = 1;
                    return false;
                  }
                  console.log("data temporal: ",$scope.fDataVenta.temporal.producto);

                  $scope.arrTemporal = { 
                  'id' : null,
                  'idproductomaster' : $scope.fDataVenta.temporal.producto.id,
                  'descripcion' : $scope.fDataVenta.temporal.producto.descripcion,
                  'especialidad' : $scope.fDataVenta.temporal.producto.especialidad,
                  'idespecialidad' : $scope.fDataVenta.temporal.producto.idespecialidad,
                  'precio' : ($scope.fDataVenta.temporal.producto.precioSF),
                  'boolProductoNuevo' : true,
                  } 
                  $scope.gridOptionsProductoAdd.data.push($scope.arrTemporal);

                  $scope.fDataVenta.temporal.producto = null;
                  $scope.fDataVenta.temporal.precio = null;
                  $scope.fDataVenta.temporal.especialidad = null;
                  $scope.calcularTotales(); 
                             
                }
                $scope.btnQuitarDeLaCesta = function (row,mensaje) { 
                  if( row.entity.boolProductoNuevo ){
                    var index = $scope.gridOptionsProductoAdd.data.indexOf(row.entity); 
                    $scope.gridOptionsProductoAdd.data.splice(index,1); 
                    $scope.calcularTotales();
                  }
                  else
                  {       
                    $scope.fDataAnular = {};
                    $scope.fDataAnular = row.entity;
                    
                    $scope.fDataAnular.idpaquete = $scope.idpaqueteseleccionado;
                    var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
                    $bootbox.confirm(pMensaje, function(result) {
                      if(result){
                        var index = $scope.gridOptionsProductoAdd.data.indexOf(row.entity); 
                        $scope.gridOptionsProductoAdd.data.splice(index,1); 
                        $scope.calcularTotales();
                        $scope.fDataAnular.monto_total = $scope.fDataProd.monto_total;
                        campaniaServices.sAnularDetalle($scope.fDataAnular).then(function (rpta) {
                          if(rpta.flag == 1){
                              pTitle = 'OK!';
                              pType = 'success';
                              $scope.getPaginationServerSideProd();
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
                }                
                $scope.calcularTotales = function () {
                  console.log('calculando totales');
                  var totales = 0;
                  angular.forEach($scope.gridOptionsProductoAdd.data,function (value, key) { 
                    totales += parseFloat($scope.gridOptionsProductoAdd.data[key].precio);
                  });
                  $scope.fDataProd.monto_total = totales.toFixed(2);
                  $scope.fDataVenta.temporal.monto_total = totales.toFixed(2); 
                }
                // BOTONES
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  // $scope.fDataProd.idpaquete = $scope.idpaqueteseleccionado;
                  $scope.fDataProd.detalle = $scope.gridOptionsProductoAdd.data;
                  if( $scope.fDataProd.detalle.length < 1 ){ 
                    $('#temporalEspecialidad').focus();
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 5000 }); 
                    return false; 
                  }
                  campaniaServices.sRegistrarDetalle($scope.fDataProd).then(function (rpta) {
                    if(rpta.flag == 1){ 
                      pTitle = 'OK!';
                      pType = 'success';
                      $modalInstance.dismiss('cancel');
                      $scope.getPaginationServerSidePq();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else if(rpta.flag == 2){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';}
                    else{
                      alert('Error inesperado');
                    }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 5000 });
                  });            
                }
              }, 
              resolve: {
                getPaginationServerSidePq: function() {
                  return $scope.getPaginationServerSidePq;
                }
              }
            });
          }

          // MODAL SECUNDARIO -- REGISTRO DE FECHAS 
          $scope.btnRegistroFechas = function (accion) { 
            $scope.accionfec = accion ;
            $modal.open({ 
              templateUrl: angular.patchURLCI+'campania/ver_popup_formulario_fechas',
              size: 'md',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                $scope.fDataTemp = {};
                $scope.fDataTemp.temporal = {};
                $scope.fDataTemp.activeDateV = null;
                $scope.fDataTemp.activeDateA = null;
                $scope.fDataTemp.temporal.arrFechasVen = [];
                $scope.fDataTemp.temporal.arrFechasAte = [];

                $scope.titleForm = 'Registro de Fechas de Campañas';

                if(arrFechasVenta.length > 0){
                  $scope.fDataTemp.temporal.arrFechasVen = arrFechasVenta ;
                }
                if(arrFechasAtencion.length > 0){
                  $scope.fDataTemp.temporal.arrFechasAte = arrFechasAtencion ;
                }

                $scope.cancel = function () {   // SALIR DEL MODAL
                  if ($scope.accionfec == 'edit'){
                    arrFechasVenta = angular.copy(arrVentasAfter);
                    arrFechasAtencion = angular.copy(arrAtencionAfter);
                  }
                  $modalInstance.dismiss('cancel');
                }

                $scope.aceptarfechas = function () {  // GRABAR FECHAS EN EL TEMPORAL
                  arrFechasVenta = $scope.fDataTemp.temporal.arrFechasVen;
                  arrFechasAtencion = $scope.fDataTemp.temporal.arrFechasAte;
                  function comparar(a, b) {
                   return a - b;
                  }
                  var ultiventa = [];
                  var ultiatencion = [];
                  if($scope.accionfec != 'edit'){
                    $scope.fData.fechasventa = [];
                    $scope.fData.fechasatencion = [];
                  }
                  ultiventa = arrFechasVenta.sort(comparar);
                  ultiatencion = arrFechasAtencion.sort(comparar);

                  if(ultiventa[ultiventa.length-1] > ultiatencion[ultiatencion.length-1]){
                    if($scope.accionfec == 'edit'){
                      arrFechasVenta = angular.copy(arrVentasAfter);
                      arrFechasAtencion = angular.copy(arrAtencionAfter);
                    }
                    var pTitle = 'Error!';
                    var pType = 'danger';
                    pinesNotifications.notify({ title: pTitle, text: 'La ultima fecha de venta no puede ser mayor a la ultima fecha de Atención.', type: pType, delay: 5000 });
                    return;
                  }

                  if(arrFechasVenta.length > 0 ){
                    angular.forEach(arrFechasVenta,function (value,key) {
                      $scope.arrTempFechas = {
                        'fecha' : moment(value).format("DD-MM-YYYY"),
                        'tipo_fecha' : 1
                      }
                      $scope.fData.fechasventa.push($scope.arrTempFechas);
                    });                    
                  }
                  if(arrFechasAtencion.length > 0){
                    angular.forEach(arrFechasAtencion,function (value,key) {
                      $scope.arrTempFechas2 = {
                        'fecha' : moment(value).format("DD-MM-YYYY"),
                        'tipo_fecha' : 2
                      }
                      $scope.fData.fechasatencion.push($scope.arrTempFechas2);
                    });                                      
                  }

                  if(arrFechasVenta.length > 0 && arrFechasAtencion.length > 0 ){
                    $scope.errfec.valor = false;
                  }else{
                    $scope.errfec.valor = true;
                  }

                  if($scope.accionfec == 'edit'){
                    var fecha = new Date();
                    var paramDatosFec = {
                      'anyo' : fecha.getFullYear(),
                      'idcampania' : $scope.fData.id ,
                      'fechasventa' : $scope.fData.fechasventa ,
                      'fechasatencion' : $scope.fData.fechasatencion
                    }                 
                    console.log("datos fecha:",paramDatosFec);
                    campaniaServices.sEditarFechas(paramDatosFec).then(function (rpta){
                      if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success';
                        //$scope.getPaginationServerSide();
                        arrFechasVenta = {};   // Array para las fechas venta
                        arrFechasAtencion = {};  // Array para las fechas atencion
                        arrVentasAfter = [];     // Guardamos los datos que traemos de la BD en Edicion
                        arrAtencionAfter = [];   // Guardamos los datos que traemos de la BD en Edicion
                        $scope.getPaginationServerSidePq();
                        $modalInstance.dismiss('cancel');
                      }else if(rpta.flag == 0){
                        var pTitle = 'Error!';
                        var pType = 'danger';
                        arrFechasVenta = angular.copy(arrVentasAfter);
                        arrFechasAtencion = angular.copy(arrAtencionAfter);
                        if(arrFechasVenta.length > 0 && arrFechasAtencion.length > 0 ){
                          $scope.errfec.valor = false;
                        }else{
                          $scope.errfec.valor = true;
                        }
                      }else{
                        alert('Error inesperado'); return false;
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 5000 });                      

                    });
                  }  
                  $modalInstance.dismiss('cancel');
                }
                //console.log($scope.mySelectionGrid);
              }
            });
          }

          // BOTONES DEL PIE DE PAGINA DEL MODAL PRINCIPAL PARA REGISTRAR O EDITAR
          $scope.cancel = function () {
            $scope.getPaginationServerSide();
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function (modo) {
            //console.log("datos generales :",$scope.fData);
            //return;
            $scope.fData.paquetes = $scope.gridOptionsPaqueteAdd.data;
            if($scope.accion == 'reg'){
              campaniaServices.sRegistrar($scope.fData).then(function (rpta) { 
                if(rpta.flag == 1){ 
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.accion = 'edit';
                  $scope.fData.id = rpta.idcampania;
                  $scope.gridOptionsPaqueteAdd.columnDefs[3].visible = true;
                  $scope.getPaginationServerSide();
                  if(modo == 'g'){
                    $scope.getPaginationServerSidePq();
                    $scope.gridApiPq.grid.refresh();
                  }else if(modo == 'gc'){
                    $modalInstance.dismiss('cancel');
                  }
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else if(rpta.flag == 2){
                  var pTitle = 'Advertencia!';
                  var pType = 'warning';}
                else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
              });
            }else if($scope.accion == 'edit'){
              campaniaServices.sEditar($scope.fData).then(function (rpta) { 
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationServerSide();
                  if(modo == 'g'){
                    $scope.getPaginationServerSidePq();
                    $scope.gridApiPq.grid.refresh();
                  }else if(modo == 'gc'){
                    $modalInstance.dismiss('cancel');
                  }
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                  $scope.getPaginationServerSide();
                }else{
                  alert('Error inesperado'); return false;
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              });
            }
          }
        }, 
        resolve: {
          arrToModal : function () {
            return {
              mySelectionGrid : $scope.mySelectionGrid,
              getPaginationServerSide : $scope.getPaginationServerSide
            }
          }
        }
      });
    }
        
    /* =======================================*/
    /*      Anula la campaña                  */
    /* =======================================*/

    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          campaniaServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationServerSideDetalle();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
          });
        }
      });
    }
    /* =======================================*/
    /*      Habilita la campaña               */
    /* =======================================*/
    $scope.btnHabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          campaniaServices.sHabilitar($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationServerSideDetalle();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
          });
        }
      });
    }
    /* =======================================*/
    /*      Deshabilita la campaña            */
    /* =======================================*/
    $scope.btnDeshabilitar = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          campaniaServices.sDeshabilitar($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $scope.getPaginationServerSideDetalle();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
          });
        }
      });
    }

    /* =======================================*/
    /*      Clonar la campaña            */
    /* =======================================*/
    $scope.btnClonar = function ()     {
      //$scope.accion = accion;
      $modal.open({
        templateUrl: angular.patchURLCI+'campania/ver_popup_formulario_clonar',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, arrToModal) {
          $scope.mySelectionGrid = arrToModal.mySelectionGrid;
          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.fData = {};
          $scope.CampaniaGlobal = {};
          var ClonaPaquetes = {};
          $scope.CampaniaGlobal.paquetes = {};
          $scope.CampaniaGlobal.paquetes.productos = {};

          $scope.listaTipoCampania = [
            { id: 1 , descripcion:'CAMPAÑA' },
            { id: 2 , descripcion:'CUPON' }
          ];
          var arrFechasVenta = {};   // Array para las fechas venta
          var arrFechasAtencion = {};  // Array para las fechas atencion
          var arrVentasAfter = [];     // Guardamos los datos que traemos de la BD en Edicion
          var arrAtencionAfter = [];   // Guardamos los datos que traemos de la BD en Edicion
          $scope.errfec = {} ;
          $scope.errfec.valor = true ;       // Icon para ver si se llenaron las fechas
            
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
            $scope.fData.fechasventa = [];
            $scope.fData.fechasatencion = []; 
            $scope.fData.campania = null ;
          }else{
            alert('Seleccione una sola fila');
          }
          // llenamos los datos de la campaña
          $scope.CampaniaGlobal = $scope.mySelectionGrid[0];

          $scope.titleForm = 'Clonación de Campaña';            
          // grilla de paquetes
          $scope.mySelectionPaq = [];
          $scope.gridOptionsPaqueteAdd = {
            minRowsToShow: 7,
            paginationPageSize: 50,
            enableSelectAll: true,
            //enableFullRowSelection: true,
            enableHorizontalScrollbar: 0,
            multiSelect: true,
            data: [],
            columnDefs: [ 
              { field: 'id', name: 'idpaquete', displayName: 'ID', width: 70, visible: false },
              { field: 'paquete', name: 'descripcion', displayName: 'PAQUETE', },
              { field: 'monto_total', name: 'monto_total', displayName: 'MONTO', width: 90, cellClass:"text-right"},
              { field: 'accion2', displayName: '', width: 120, enableCellEdit: false, cellTemplate:'<button type="button" class="btn btn-sm btn-info center-block" ng-click="grid.appScope.btnProductoVer(row)" ng-show="row.entity.id"> <i class="fa fa-plus"></i>PRODUCTOS </button>' }
              
            ],
            onRegisterApi: function(gridApiPq) {
              $scope.gridApiPq = gridApiPq; 
              gridApiPq.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionPaq = gridApiPq.selection.getSelectedRows();
                $scope.SelectPaquetes();
              });
              gridApiPq.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionPaq = gridApiPq.selection.getSelectedRows();
                $scope.SelectPaquetes();               
              });  

              $scope.selectAllPaquetes = function(){
                $scope.gridApiPq.selection.selectAllRows();                 
              }
            }
          };

          $scope.SelectPaquetes = function(){
            angular.forEach($scope.CampaniaGlobal.paquetes, function(valuePa, keyPa){
              $scope.CampaniaGlobal.paquetes[keyPa].boolSeleccionado = false ; 
              if($scope.mySelectionPaq.length > 0){
                angular.forEach($scope.mySelectionPaq, function(valuePq,keyPq){
                  if($scope.CampaniaGlobal.paquetes[keyPa].id == $scope.mySelectionPaq[keyPq].id){
                    $scope.CampaniaGlobal.paquetes[keyPa].boolSeleccionado = true ;
                  };                
                });                
              }
            });            
          }

          $scope.getPaginationServerSidePq = function() {
            $scope.datosGrid = {
              datos : $scope.fData.id
            };
            campaniaServices.sListarPaqueteCbo($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsPaqueteAdd.data = rpta.datos;  
              $scope.CampaniaGlobal.paquetes = rpta.datos ;
              angular.forEach(rpta.datos, function(value, key){
                $scope.datosGrid = {
                  idpaquete : value.id
                };
                campaniaServices.sListarDetallePaqueteId($scope.datosGrid).then(function (rpta) {
                  $scope.CampaniaGlobal.paquetes[key].productos = rpta.datos;
                });                                
              });

              $timeout(function() {
                $scope.selectAllPaquetes(); 
              });           
            });
          }
           
          $scope.getPaginationServerSidePq();           
          // MODAL SECUNDARIO
          $scope.btnProductoVer = function (rowPq) { 
            var idpaqueteseleccionado = rowPq.entity.id;
            var paqueteSeleccionado = rowPq.entity.descripcion;
            var montoSeleccionado = rowPq.entity.monto_total;
            $modal.open({
              templateUrl: angular.patchURLCI+'campania/ver_popup_formulario_clonar_paquete_detalle',
              size: 'md',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance, getPaginationServerSidePq) { // console.log(arrToModal); 
                $scope.getPaginationServerSidePq = getPaginationServerSidePq;
                $scope.fDataProd = {};
                $scope.fDataProd.idpaquete = idpaqueteseleccionado;
                $scope.fDataProd.monto_total = montoSeleccionado;
                $scope.fBusqueda={};

                $scope.titleForm = 'Productos de ' + paqueteSeleccionado;
                
                var paginationOptionsProd = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.mySelectionProd = [];
                $scope.gridOptionsProductoAdd = {
                  minRowsToShow: 7,
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  useExternalFiltering : true,
                  enableGridMenu: false,
                  // enableRowSelection: true,
                  enableSelectAll: true,
                  enableFiltering: false,
                  enableFullRowSelection: true,
                  enableCellEditOnFocus: true,
                  enableHorizontalScrollbar: 0,
                  data: [],
                  multiSelect: true,
                  columnDefs: [
                    { field: 'id', name: 'd.iddetallepaquete', displayName: 'ID', minWidth: 60,  sort: { direction: uiGridConstants.ASC}, visible: false},
                    { field: 'especialidad', name: 'e.nombre', displayName: 'Especialidad',minWidth: 180 },
                    { field: 'descripcion', name: 'pm.descripcion', displayName: 'Producto',minWidth: 220 },
                    { field: 'precio', name: 'd.precio', displayName: 'Precio',maxWidth: 80, enableCellEdit: true, cellClass:'ui-editCell text-right' }
                  ],
                  onRegisterApi: function(gridApi) {
                    $scope.gridApi3 = gridApi;

                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionProd = gridApi.selection.getSelectedRows();
                      $scope.calcularTotales();
                      });
                    gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                      $scope.mySelectionProd = gridApi.selection.getSelectedRows();
                      $scope.calcularTotales();
                    });                    
                    gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                      rowEntity.column = colDef.field;
                      if(rowEntity.column == 'precio'){
                        if( !(rowEntity.precio >= 0) ){
                          var pTitle = 'Advertencia!';
                          var pType = 'warning';
                          rowEntity.precio = oldValue;
                          pinesNotifications.notify({ title: pTitle, text: 'El Precio debe ser mayor o igual a 0', type: pType, delay: 3500 });
                          return false;
                        }
                      }
                      $scope.calcularTotales();
                      $scope.$apply();
                    });
                  }
                };

                $scope.selectAllProductos = function(){
                  if(ClonaPaquetes.length > 0){
                    angular.forEach($scope.gridOptionsProductoAdd.data, function(valueGd,keyGd){
                      angular.forEach(ClonaPaquetes, function(valueCl,keyCl){
                        if(valueGd.idpaquete == valueCl.id){
                          angular.forEach(valueCl.productos , function(valuePr,keyPr){
                            if(valueGd.idproductomaster == valuePr.idproductomaster){
                              if(valuePr.boolSelect){
                                $scope.gridApi3.selection.selectRow($scope.gridOptionsProductoAdd.data[keyGd]);
                              }
                            }
                          })
                        }
                      });
                    });

                  }else{
                    $scope.gridApi3.selection.selectAllRows(); 
                  }
                }

                $scope.getPaginationServerSideProd = function() {
                  $scope.datosGrid = {
                    idpaquete : $scope.fDataProd.idpaquete
                  };
                  campaniaServices.sListarDetallePaqueteId($scope.datosGrid).then(function (rpta) {
                    $scope.gridOptionsProductoAdd.data = rpta.datos;
                    console.log("uigrid:", $scope.gridOptionsProductoAdd.data);
                    if($scope.gridOptionsProductoAdd.data.length)
                      $scope.fDataVenta.temporal.monto_total = rpta.datos[0].monto;

                    $timeout(function() {
                      $scope.selectAllProductos(); 
                    });
                  });
                  // $scope.mySelectionGridProd = [];
                };

                $scope.getPaginationServerSideProd();

                $scope.fDataVenta={};
                $scope.fDataVenta.temporal={}; 
                
                $scope.calcularTotales = function () {
                  var totales = 0;
                  angular.forEach($scope.mySelectionProd,function (value, key) { 
                    totales += parseFloat($scope.mySelectionProd[key].precio);
                  });
                  $scope.fDataProd.monto_total = totales.toFixed(2);
                  $scope.fDataVenta.temporal.monto_total = totales.toFixed(2); 
                }
                // BOTONES
                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                }
                $scope.aceptar = function () {
                  // $scope.fDataProd.idpaquete = $scope.idpaqueteseleccionado;

                  // 1- cargamos todos los filas producto al inicio de la grilla
                  // 1.1 busco en toda los rows desde los seleccionados
                  // 1.2 cambio de estado                  
                  // 2- cuando grabamos, debemos cambiar el estado de los productos (boolSelection = FALSE o TRUE)
                  // 3- luego cargamos esos datos en la fila

                  $scope.fDataProd.detalle = $scope.gridOptionsProductoAdd.data;
                  if( $scope.fDataProd.detalle.length < 1 ){ 
                    $('#temporalEspecialidad').focus();
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún servicio/producto', type: 'warning', delay: 3000 }); 
                    return false; 
                  }
                  if($scope.mySelectionProd.length < 1){
                    $('#temporalEspecialidad').focus();
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado ningún servicio/producto', type: 'warning', delay: 3000 }); 
                    return false;                    
                  }
                  angular.forEach($scope.CampaniaGlobal.paquetes, function(valuePq, keyPq){
                    if( $scope.CampaniaGlobal.paquetes[keyPq].id == idpaqueteseleccionado ){
                      angular.forEach($scope.CampaniaGlobal.paquetes[keyPq].productos, function(valuePr, keyPr){
                        $scope.CampaniaGlobal.paquetes[keyPq].productos[keyPr].boolSelect = false ;
                      })                      
                    }
                  });

                  angular.forEach($scope.CampaniaGlobal.paquetes, function(valuePa, keyPa){
                    angular.forEach($scope.mySelectionProd, function(valuePr,keyPr){
                      if($scope.CampaniaGlobal.paquetes[keyPa].id == $scope.mySelectionProd[keyPr].idpaquete){
                        angular.forEach($scope.CampaniaGlobal.paquetes[keyPa].productos,function(valuePro,keyPro){
                          if($scope.CampaniaGlobal.paquetes[keyPa].productos[keyPro].idproductomaster === $scope.mySelectionProd[keyPr].idproductomaster){
                            $scope.CampaniaGlobal.paquetes[keyPa].productos[keyPro].boolSelect = true ;
                          }
                        });
                      }
                    })
                  });
                  
                  angular.forEach($scope.gridOptionsPaqueteAdd.data, function(value,key){
                    if(value.id == idpaqueteseleccionado){
                      $scope.gridOptionsPaqueteAdd.data[key].monto_total = $scope.fDataProd.monto_total;
                    }
                  });
                  ClonaPaquetes = angular.copy($scope.CampaniaGlobal.paquetes);
                  $modalInstance.dismiss('cancel');
                }
              }, 
              resolve: {
                getPaginationServerSidePq: function() {
                  return $scope.getPaginationServerSidePq;
                }
              }
            });
          }
          // MODAL SECUNDARIO -- REGISTRO DE FECHAS 
          $scope.btnRegistroFechasClo = function () { 
            $modal.open({ 
              templateUrl: angular.patchURLCI+'campania/ver_popup_formulario_fechas',
              size: 'md',
              backdrop: 'static',
              keyboard:false,
              scope: $scope,
              controller: function ($scope, $modalInstance) {
                $scope.fDataTemp = {};
                $scope.fDataTemp.temporal = {};
                $scope.fDataTemp.activeDateV = null;
                $scope.fDataTemp.activeDateA = null;
                $scope.fDataTemp.temporal.arrFechasVen = [];
                $scope.fDataTemp.temporal.arrFechasAte = [];

                $scope.titleForm = 'Registro de Fechas de Campañas';

                if(arrFechasVenta.length > 0){
                  $scope.fDataTemp.temporal.arrFechasVen = arrFechasVenta ;
                }
                if(arrFechasAtencion.length > 0){
                  $scope.fDataTemp.temporal.arrFechasAte = arrFechasAtencion ;
                }

                $scope.cancel = function () {   // SALIR DEL MODAL
                  $modalInstance.dismiss('cancel');
                }

                $scope.aceptarfechas = function () {  // GRABAR FECHAS EN EL TEMPORAL
                  arrFechasVenta = $scope.fDataTemp.temporal.arrFechasVen;
                  arrFechasAtencion = $scope.fDataTemp.temporal.arrFechasAte;

                  function comparar(a, b) {
                   return a - b;
                  }
                  var ultiventa = [];
                  var ultiatencion = [];
                  ultiventa = arrFechasVenta.sort(comparar);
                  ultiatencion = arrFechasAtencion.sort(comparar);

                  if(ultiventa[ultiventa.length-1] > ultiatencion[ultiatencion.length-1]){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                    pinesNotifications.notify({ title: pTitle, text: 'La ultima fecha de venta no puede ser mayor a la ultima fecha de Atención.', type: pType, delay: 4000 });
                    return;
                  }

                  if(arrFechasVenta.length > 0 ){
                    angular.forEach(arrFechasVenta,function (value,key) {
                      $scope.arrTempFechas = {
                        'fecha' : moment(value).format("DD-MM-YYYY"),
                        'tipo_fecha' : 1
                      }
                      $scope.fData.fechasventa.push($scope.arrTempFechas);
                    });                    
                  }
                  if(arrFechasAtencion.length > 0){
                    angular.forEach(arrFechasAtencion,function (value,key) {
                      $scope.arrTempFechas2 = {
                        'fecha' : moment(value).format("DD-MM-YYYY"),
                        'tipo_fecha' : 2
                      }
                      $scope.fData.fechasatencion.push($scope.arrTempFechas2);
                    });                                      
                  }

                  if(arrFechasVenta.length > 0 && arrFechasAtencion.length > 0 ){
                    $scope.errfec.valor = false;
                  }else{
                    $scope.errfec.valor = true;
                  }

                  $modalInstance.dismiss('cancel');
                }
                //console.log($scope.mySelectionGrid);
              }
            });
          }
          // BOTONES DEL PIE DE PAGINA DEL MODAL PRINCIPAL
          $scope.cancel = function () {
            $scope.getPaginationServerSide();
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptarClonar = function () {  // GRABA TODA LA CLONACION
            //$scope.fData.paquetes = $scope.gridOptionsPaqueteAdd.data;
            var fecha = new Date();
            $scope.CampaniaGlobal.fechasventa = $scope.fData.fechasventa;
            $scope.CampaniaGlobal.fechasatencion = $scope.fData.fechasatencion;
            $scope.CampaniaGlobal.anyo = fecha.getFullYear();
            console.log("data_total :",$scope.CampaniaGlobal);

            if ($scope.mySelectionPaq.length < 1){
              var pTitle = 'Error!';
              var pType = 'danger';
              pinesNotifications.notify({ title: pTitle, text: 'No se seleccionó ningun Paquete ... revise la información', type: pType, delay: 4000 }); 
              return;             
            }
            campaniaServices.sClonar($scope.CampaniaGlobal).then(function (rpta) { 
              if(rpta.flag == 1){ 
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide(); 
                $modalInstance.dismiss('cancel');                                
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else if(rpta.flag == 2){
                var pTitle = 'Advertencia!';
                var pType = 'warning';}
              else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 4000 });
            });

          }
        }, 
        resolve: {
          arrToModal : function () {
            return {
              mySelectionGrid : $scope.mySelectionGrid,
              getPaginationServerSide : $scope.getPaginationServerSide
            }
          }
        }
      });
    }

    //SCOPE DEL DETALLE
    $scope.btnToggleFilteringDetalle = function(){
      $scope.gridOptionsDetalle.enableFiltering = !$scope.gridOptionsDetalle.enableFiltering;
      $scope.gridApi2.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.mySelectionDetalle = [];
    var paginationOptionsDetalle = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.gridOptionsDetalle = {
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
        { field: 'id', name: 'd.iddetallepaquete', displayName: 'ID', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'campania', name: 'c.descripcion', displayName: 'Campaña' ,minWidth: 180 }, 
        { field: 'paquete', name: 'pq.descripcion', displayName: 'Paquete' ,minWidth: 180,}, 
        { field: 'producto', name: 'pm.descripcion', displayName: 'Producto',minWidth: 180 },
        { field: 'precio', name: 'd.precio', displayName: 'Precio',maxWidth: 100 },
        { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', maxWidth: 120, enableFiltering: false, enableSorting: false, 
          cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 110px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi2 = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionDetalle = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionDetalle = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi2.core.on.sortChanged($scope, function(grid, sortColumns) {
          if (sortColumns.length == 0) {
            paginationOptionsDetalle.sort = null;
            paginationOptionsDetalle.sortName = null;
          } else {
            paginationOptionsDetalle.sort = sortColumns[0].sort.direction;
            paginationOptionsDetalle.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSideDetalle();
        });
        $scope.gridApi2.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsDetalle.search = true;
          paginationOptionsDetalle.searchColumn = { 
            'd.iddetallepaquete' : grid.columns[1].filters[0].term,
            'c.descripcion' : grid.columns[2].filters[0].term,
            'pq.descripcion' : grid.columns[3].filters[0].term,
            'pm.descripcion' : grid.columns[4].filters[0].term,
            'd.precio' : grid.columns[5].filters[0].term
          }
          $scope.getPaginationServerSideDetalle();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsDetalle.pageNumber = newPage;
          paginationOptionsDetalle.pageSize = pageSize;
          paginationOptionsDetalle.firstRow = (paginationOptionsDetalle.pageNumber - 1) * paginationOptionsDetalle.pageSize;
          $scope.getPaginationServerSideDetalle();
        });
      }
    };
    paginationOptionsDetalle.sortName = $scope.gridOptionsDetalle.columnDefs[0].name;
    var vardata={};
    vardata.idcamp=0 ;
    vardata.idpaq=0 ;
    $scope.getPaginationServerSideDetalle = function() {
      $scope.datosGrid = {
        paginate : paginationOptionsDetalle,
        vardatos : vardata,
        datos : $scope.fBusqueda,
      };
      campaniaServices.sListarDetalleCampanias($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsDetalle.data = rpta.datos;
      });
      $scope.mySelectionDetalle = [];
    };
    // $scope.getPaginationServerSideDetalle();
    // fin del scope.
    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva campaña',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar campaña',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular campaña',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
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
  .service("campaniaServices",function($http, $q) {
    return({
        sListarCampanias: sListarCampanias,
        sListarDetalleCampanias: sListarDetalleCampanias,
        sListarCampaniasCbo:  sListarCampaniasCbo,
        sRegistrarDetalle : sRegistrarDetalle,
        sEditarDetalle : sEditarDetalle,
        sListarDetallePaqueteId : sListarDetallePaqueteId,
        sListarPaqueteCbo : sListarPaqueteCbo ,
        sListarFechas : sListarFechas ,
        sEditarFechas : sEditarFechas ,        
        sAnularDetalle : sAnularDetalle,
        sRegistrarDetallePaquete : sRegistrarDetallePaquete,
        sRegistrar: sRegistrar,
        sEditar: sEditar,
        sClonar : sClonar,
        sDeshabilitar: sDeshabilitar,
        sHabilitar: sHabilitar,
        sAnular: sAnular,
        /* luis */
        sListarCampaniasPaqueteCbo: sListarCampaniasPaqueteCbo,
        sListarCampaniasPaqueteDetalle: sListarCampaniasPaqueteDetalle,
        /*Ruben*/
        sAnularPaquete : sAnularPaquete,
    });

    function sListarCampanias(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_campanias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleCampanias(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_detalle_campanias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetallePaqueteId(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_detalle_paquetes_id", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarCampaniasCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_campanias_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarPaqueteCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_paquetes_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarFechas (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_fechas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sClonar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/clonar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sRegistrarDetalle (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/registrar_detalle", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarDetalle (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/editar_detalle", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarFechas (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/editar_fechas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sRegistrarDetallePaquete (datos) { // YA NO DEBE USARSE
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/registrar_detalle_paquete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/habilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/deshabilitar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    /* luis */
    function sListarCampaniasPaqueteCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_campanias_paquetes_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCampaniasPaqueteDetalle (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/lista_campanias_paquetes_detalle", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAnularDetalle (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/anular_detalle", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    /*Ruben*/
    function sAnularPaquete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"campania/anular_paquete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });
