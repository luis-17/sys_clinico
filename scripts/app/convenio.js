angular.module('theme.convenio', ['theme.core.services'])
  .controller('convenioController', ['$scope', '$filter', '$controller', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications',  'hotkeys',
    'convenioServices',
    'empresaAdminServices',
    function($scope, $filter, $controller, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys
      , convenioServices,
      empresaAdminServices
      ){
    'use strict';
    shortcut.remove("F2");
    $scope.modulo = 'convenio';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.fBusqueda = {}
    empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
      $scope.listaSedeEmpresaAdmin = rpta.datos;
      $scope.listaSedeEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'Todas las Sedes'});
      $scope.fBusqueda.sedeempresa = $scope.listaSedeEmpresaAdmin[0].id;
      //$scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
      $scope.getPaginationServerSide();
    }); 
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
      enableSelectAll: false,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'idtipocliente', name: 'idtipocliente', displayName: 'COD.', maxWidth: 80,  sort: { direction: uiGridConstants.ASC} },
        { field: 'descripcion', name: 'descripcion_tc', displayName: 'TÍTULO' },
        { field: 'contrato', name: 'numero_contrato', displayName: 'Nº CONTRATO' },
        { field: 'fec_inicial', name: 'fecha_inicial', displayName: 'FECHA INICIO' },
        { field: 'fec_vigencia', name: 'fecha_vigencia', displayName: 'FECHA FIN' },
        { field: 'sede_empresa', name: 'razon_social', displayName: 'Empresa / Sede', minWidth: 200}
        //{ field: 'estado', name: 'estado_tc', displayName: 'Estado', maxWidth: 250, cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label label-default ">{{COL_FIELD}}</label>'}
        
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
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = {
            'idtipocliente' : grid.columns[1].filters[0].term,
            'descripcion_tc' : grid.columns[2].filters[0].term
            //'porcentaje' : grid.columns[3].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda,
      };
      convenioServices.sListarConvenio($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
         
        
      });
      $scope.mySelectionGrid = [];
    };
    $scope.btnVerClientesConvenio = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'convenio/ver_popup_clientes_convenio',
        // templateUrl: angular.patchURLCI+'atencionMedica/ver_popup_detalle_atenciones',
        size: 'lg',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid) {
          //console.log($scope.fBusqueda);
          $scope.mySelectionGrid = mySelectionGrid;
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }

          $controller('clienteController', { 
            $scope : $scope
          });
          $scope.modulo = 'convenio';
          /* DATA GRID */ 
          var paginationClientesConvenio = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionPacientesConvenioGrid = [];
          $scope.gridOptionsDetalle = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: true,
            enableFullRowSelection: true,
            multiSelect: true,
            columnDefs: [
              { field: 'idcliente', name: 'idcliente', displayName: 'ID', width: '80', sort: { direction: uiGridConstants.ASC}, visible: false },
              { field: 'num_documento', name: 'num_documento', displayName: 'DNI', width: '80' },
              { field: 'nombres', name: 'nombres', displayName: 'NOMBRES' },
              { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'APELLIDO PATERNO' },
              { field: 'apellido_materno', name: 'apellido_materno', displayName: 'APELLIDO MATERNO' },
              { field: 'edad', name: 'edad', displayName: 'EDAD', width: '90' },
              { field: 'sexo', name: 'sexo', displayName: 'SEXO', width: '90' },
           //    { field: 'estado', type: 'object', name: 'estado_ap', displayName: 'Estado', maxWidth: 250, enableFiltering: false,
           // cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
           
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionPacientesConvenioGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionPacientesConvenioGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationClientesConvenio.sort = null;
                  paginationClientesConvenio.sortName = null;
                } else {
                  paginationClientesConvenio.sort = sortColumns[0].sort.direction;
                  paginationClientesConvenio.sortName = sortColumns[0].name;
                }
                $scope.getPaginationClientesConvenioServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationClientesConvenio.pageNumber = newPage;
                paginationClientesConvenio.pageSize = pageSize;
                paginationClientesConvenio.firstRow = (paginationClientesConvenio.pageNumber - 1) * paginationClientesConvenio.pageSize;
                $scope.getPaginationClientesConvenioServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationClientesConvenio.search = true;
                // console.log(grid.columns);
                // console.log(grid.columns[1].filters[0].term);
                paginationClientesConvenio.searchColumn = {
                  'idcliente' : grid.columns[1].filters[0].term,
                  'num_documento' : grid.columns[2].filters[0].term,
                  'nombres' : grid.columns[3].filters[0].term,
                  'apellido_paterno' : grid.columns[4].filters[0].term,
                  'apellido_materno' : grid.columns[5].filters[0].term,
                  "DATE_PART('YEAR',AGE(fecha_nacimiento))" : grid.columns[6].filters[0].term,
                  'sexo' : grid.columns[7].filters[0].term,

                }
                $scope.getPaginationClientesConvenioServerSide();
              });
            }
          };
          paginationClientesConvenio.sortName = $scope.gridOptionsDetalle.columnDefs[0].name;
          $scope.getPaginationClientesConvenioServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationClientesConvenio,
              datos : $scope.mySelectionGrid[0],
            };
            convenioServices.sListarClienteConvenio($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalle.data = rpta.datos;
            });
            $scope.mySelectionPacientesConvenioGrid = [];
          };
          $scope.getPaginationClientesConvenioServerSide();
          console.log($scope.fData);
          /* fin grilla */
          $scope.titleForm = 'CLIENTES DEL CONVENIO: <span style="color: red">' + $scope.fData.descripcion + '</span>';
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
          }

          $scope.getClienteNoAgregAutocomplete = function (value) {
            var params = {
              search: value,
              idtipocliente: $scope.fData.idtipocliente,
              sensor: false
            }
            return convenioServices.sClienteNoAgreConvenioAutocompletar(params).then(function(rpta) { 
              $scope.noResultsLCliente = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLCliente = true;
              }
              return rpta.datos; 
            });
          }

          $scope.getSelectedPaciente = function ($item, $model, $label){
            $scope.fDataAdd.cliente = $item.paciente;
            $scope.fDataAdd.idcliente = $item.idcliente;     
            //console.log($scope.fDataAdd);       
          }

          $scope.btnAgregarClienteConvenio = function (){
            if($scope.fDataAdd.idcliente == null){
              pinesNotifications.notify({ title: 'Advertencia!', text: 'Debe seleccionar un cliente', type: 'warning', delay: 3000 }); 
              return;
            }

            var datos = {
              idcliente: $scope.fDataAdd.idcliente,
              idtipocliente: $scope.fData.idtipocliente,
            }

            convenioServices.sUpdateClienteConvenio(datos).then(function(rpta) { 
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.fDataAdd = {};
                $scope.getPaginationClientesConvenioServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';                      
              }else{
                alert('Error inesperado');
              }
              //$scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });                
            });
          }

          $scope.btnNuevoClienteConvenio = function (){
            var fnCallBack = function(){
              $scope.getPaginationClientesConvenioServerSide();
            }
            $scope.btnNuevoCliente('xlg', null, $scope.fData.idtipocliente, fnCallBack);
          }
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          }
        }

      });
    };
    $scope.btnVerProductosConvenio = function () {
      $uibModal.open({
        templateUrl: angular.patchURLCI+'convenio/ver_popup_productos_convenio',
        // templateUrl: angular.patchURLCI+'atencionMedica/ver_popup_detalle_atenciones',
        size: 'xlg',
        // backdrop: 'static',
        // keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance,mySelectionGrid) {
          //console.log($scope.fBusqueda);
          $scope.mySelectionGrid = mySelectionGrid;
          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fData = $scope.mySelectionGrid[0];
          }else{
            alert('Seleccione una sola fila');
          }

          $scope.fBusqueda.soloDecimales = false;
          $scope.itemsTemporales = [];
          /* DATA GRID PRODUCTOS CARGADOS*/ 
          var paginationProductosConvenio = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10000,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };

          $scope.estadoOptions = [
            { id: 1, estado: 'HABILITADO' },
            { id: 2, estado: 'DESHABILITADO' }, 
          ];

          $scope.mySelectionProductosConvenioGrid = [];
          $scope.gridOptionsDetalle = {
            paginationPageSizes: [500, 1000, 10000],
            paginationPageSize: 10000,
            useExternalPagination: true,
            useExternalSorting: false,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: true,
            enableFullRowSelection: false,
            multiSelect: true,
            columnDefs: [
              { field: 'idproducto', name: 'idproductomaster', displayName: 'ID', width: '70', sort: { direction: uiGridConstants.ASC}, visible: false, enableCellEdit: false  },
              { field: 'producto', name: 'producto', displayName: 'PRODUCTO / SERVICIO',width: '170', enableCellEdit: false  },
              { field: 'precio_regular', name: 'precio_sede', displayName: 'P.REGULAR',cellClass:'text-right', width: '80',enableCellEdit: false },
              { field: 'precio_convenio', name: 'precio_variable', displayName: 'P.CONVENIO', width: '80', cellClass:'text-right bg-lightblue ui-editCell',
                cellTemplate:'<p style="color: red;padding:3px;" ng-if="COL_FIELD % 1 != 0">{{ COL_FIELD }}</p> <p ng-if="COL_FIELD % 1 == 0" style="padding:3px;">{{ COL_FIELD }}</p>'
              },
              { field: 'porcentaje', name: 'porcentaje', displayName: '%', width: '60',cellClass:'text-right ui-editCell', enableFiltering: true,
                cellTemplate:'<p style="color: red;padding:3px;" ng-if="COL_FIELD < 0">{{ COL_FIELD }}</p> <p ng-if="COL_FIELD >= 0" style="padding:3px;">{{ COL_FIELD }}</p>'
              },
              { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD', width: '150',enableCellEdit: false  },
              { field: 'tipo_producto', name: 'tipo_producto', displayName: 'TIPO DE PRODUCTO', width: '120', visible:false,enableCellEdit: false  },
              { field: 'estado', name: 'estado', displayName: 'ESTADO', width:'100', enableFiltering:false, cellClass:'ui-editCell',
                cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>',
                editableCellTemplate: 'ui-grid/dropdownEditor', cellFilter: 'mapEstado', editDropdownValueLabel: 'estado', editDropdownOptionsArray: $scope.estadoOptions
              }          
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionProductosConvenioGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionProductosConvenioGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationProductosConvenio.sort = null;
                  paginationProductosConvenio.sortName = null;
                } else {
                  paginationProductosConvenio.sort = sortColumns[0].sort.direction;
                  paginationProductosConvenio.sortName = sortColumns[0].name;
                }
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationProductosConvenio.pageNumber = newPage;
                paginationProductosConvenio.pageSize = pageSize;
                paginationProductosConvenio.firstRow = (paginationProductosConvenio.pageNumber - 1) * paginationProductosConvenio.pageSize;
                $scope.getPaginationProductosConvenioServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationProductosConvenio.search = true;
                
                paginationProductosConvenio.searchColumn = {
                  'idproductomaster' : grid.columns[1].filters[0].term,
                  'pm.descripcion' : grid.columns[2].filters[0].term,
                  '(pps.precio_sede)::NUMERIC' : grid.columns[3].filters[0].term,
                  '(cps.precio_variable)::NUMERIC' : grid.columns[4].filters[0].term,
                  "(CASE WHEN (pps.precio_sede)::NUMERIC = 0 THEN 0 ELSE ( 1 - ( (cps.precio_variable)::NUMERIC / (pps.precio_sede)::NUMERIC ) )*100 END )" : grid.columns[5].filters[0].term,
                  'e.nombre' : grid.columns[6].filters[0].term,
                  'tp.nombre_tp' : grid.columns[7].filters[0].term,
                }

                $scope.getPaginationProductosConvenioServerSide();
              });
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){ 
                // console.log(rowEntity, colDef, newValue, oldValue);                 

                if( colDef.name === 'estado'){
                  var data = {
                    idconvenioproductosede: rowEntity.idconvenioproductosede,
                    estado: newValue,
                  }                  
                  convenioServices.sCambiarEstadoProductoConvenio(data).then(function (rpta){                    
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      $scope.getPaginationProductosConvenioServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';                      
                    }else{
                      alert('Error inesperado');
                    }
                    //$scope.fData = {};
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                }

                if(colDef.name === 'porcentaje'){
                  if(newValue!= null && !isNaN(newValue) && newValue >= 0){
                    rowEntity.porcentaje = parseFloat(newValue);                    
                    var precio_regular = parseFloat(rowEntity.precio_regular.replace("S/.", ""));
                    rowEntity.precio_convenio = precio_regular - ((precio_regular * rowEntity.porcentaje) / 100); 
                    rowEntity.precio_convenio = rowEntity.precio_convenio.toFixed(2);
                    rowEntity.porcentaje = rowEntity.porcentaje.toFixed(2);
                    $scope.gridApi.core.refresh();
                  }else{
                    rowEntity.porcentaje = oldValue;
                  }
                }

                if(colDef.name === 'precio_variable'){
                  //console.log(rowEntity.precio_convenio);
                  if(newValue!= null && !isNaN(newValue) && newValue >= 0){
                    if(newValue == 0){
                      rowEntity.porcentaje = 0; 
                    }else{
                      rowEntity.precio_convenio = parseFloat(newValue);                      
                      var precio_regular = parseInt(rowEntity.precio_regular.replace("S/.", ""));
                      /*precio_regular = parseInt(precio_regular.replace('.','*').replace(',','.').replace('*',',')); */
                      rowEntity.porcentaje = 100 - ((rowEntity.precio_convenio *100) /  precio_regular); 
                      rowEntity.porcentaje = rowEntity.porcentaje.toFixed(2);  
                      rowEntity.precio_convenio = rowEntity.precio_convenio.toFixed(2);                   
                      $scope.gridApi.core.refresh();
                    }
                  }else{
                    rowEntity.precio_convenio = oldValue;
                  }
                }

                //console.log(rowEntity);
                $scope.gridApi.core.refresh();
                $scope.$apply();
              });
            }
          };
          paginationProductosConvenio.sortName = $scope.gridOptionsDetalle.columnDefs[0].name;
          $scope.getPaginationProductosConvenioServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationProductosConvenio,
              datos : $scope.mySelectionGrid[0],
              soloDecimales : $scope.fBusqueda.soloDecimales,
            };
            convenioServices.sListarProductoConvenio($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalle.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalle.data = rpta.datos;              
              angular.forEach($scope.itemsTemporales, function(producto, key) {
                if($scope.fBusqueda.soloDecimales){
                  if(producto.precio_convenio % 1 != 0){
                    $scope.gridOptionsDetalle.data.splice(0,0,producto);
                  }
                }else{
                  $scope.gridOptionsDetalle.data.splice(0,0,producto);
                }
                $scope.gridTodos =   $scope.gridOptionsDetalle.data;              
              });
              $scope.gridOptionsDetalle.totalItems = parseInt($scope.gridOptionsDetalle.totalItems) + ($scope.itemsTemporales.length);
              
            });
            $scope.mySelectionProductosConvenioGrid = [];
          };
          $scope.getPaginationProductosConvenioServerSide();

          //console.log($scope.fData);
          /* fin grilla */
          $scope.titleForm = 'PRODUCTOS DEL CONVENIO: <span style="color: red">' + $scope.fData.descripcion + '</span>';
          
          /* DATA GRID PRODUCTOS PARA AGREGAR*/ 
          var paginationProductos = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10000,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          
          $scope.mySelectionProductosGrid = [];
          $scope.gridOptionsProd = {
            paginationPageSizes: [500, 1000, 10000],
            paginationPageSize: 10000,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: true,
            enableFullRowSelection: true,
            multiSelect: true,
            columnDefs: [
              { field: 'idproducto', name: 'idproductomaster', displayName: 'ID', width: '70', sort: { direction: uiGridConstants.ASC}, visible: false, enableCellEdit: false  },
              { field: 'producto', name: 'producto', displayName: 'PRODUCTO / SERVICIO',width: '170', enableCellEdit: false  },
              { field: 'precio_regular', name: 'precio_sede', displayName: 'P.REGULAR',cellClass:'text-right', width: '80',enableCellEdit: false },
              { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD', width: '150',enableCellEdit: false  },
              { field: 'tipo_producto', name: 'tipo_producto', displayName: 'TIPO DE PRODUCTO', width: '120', visible:false,enableCellEdit: false  },        
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApiProductos = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionProductosGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionProductosGrid = gridApi.selection.getSelectedRows();
              });
              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationProductos.sort = null;
                  paginationProductos.sortName = null;
                } else {
                  paginationProductos.sort = sortColumns[0].sort.direction;
                  paginationProductos.sortName = sortColumns[0].name;
                }
                $scope.getPaginationProductosServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationProductos.pageNumber = newPage;
                paginationProductos.pageSize = pageSize;
                paginationProductos.firstRow = (paginationProductos.pageNumber - 1) * paginationProductos.pageSize;
                $scope.getPaginationProductosServerSide();
              });
              $scope.gridApiProductos.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationProductos.search = true;
                // console.log(grid.columns);
                // console.log(grid.columns[1].filters[0].term);
                paginationProductos.searchColumn = {
                  'idproductomaster' : grid.columns[1].filters[0].term,
                  'pm.descripcion' : grid.columns[2].filters[0].term,
                  '(pps.precio_sede)::NUMERIC' : grid.columns[3].filters[0].term,  
                  'e.nombre' : grid.columns[4].filters[0].term,
                  'tp.nombre_tp' : grid.columns[5].filters[0].term,
                }
                $scope.getPaginationProductosServerSide();
              });
            }
          };

          paginationProductos.sortName = $scope.gridOptionsProd.columnDefs[0].name;
          $scope.getPaginationProductosServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationProductos,
              datos : $scope.mySelectionGrid[0],              
            };
            convenioServices.sListarProductoNoEstanConvenio($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsProd.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsProd.data = rpta.datos;              
            });
            $scope.mySelectionProductosGrid = [];
          };
          $scope.getPaginationProductosServerSide();

          $scope.agregarTodos  = function (){
            angular.forEach($scope.gridOptionsProd.data, function(producto, key) {
              var encontro = false;
              angular.forEach($scope.gridOptionsDetalle.data, function(productoConvenio, ind) {                
                if(productoConvenio.idproductopreciosede === producto.idproductopreciosede){
                  encontro = true;
                  //console.log('encontro');
                  return;
                }
              });
              if(!encontro){                
                //console.log('se agrega', producto.producto);
                producto.estado = {
                  bool: 1,
                  string: 'HABILITADO',
                  clase: 'label-success'
                }

                producto.temporal = true;
                producto.idproducto = null;
                $scope.gridOptionsDetalle.data.splice(0,0,producto);
                $scope.itemsTemporales.push(producto);
                $scope.gridOptionsDetalle.totalItems = parseInt($scope.gridOptionsDetalle.totalItems) + 1;
              }
            });            
          }

          $scope.agregarSeleccionados  = function (){
            if($scope.mySelectionProductosGrid.length < 1){
              pinesNotifications.notify({ title: 'Aviso!', text: 'Debe seleccionar al menos 1 producto', type: 'warning', delay: 3000 });
              return;
            }
            angular.forEach($scope.mySelectionProductosGrid, function(producto, key) {
              var encontro = false;
              angular.forEach($scope.gridOptionsDetalle.data, function(productoConvenio, ind) {                
                if(productoConvenio.idproductopreciosede === producto.idproductopreciosede){
                  encontro = true;
                  //console.log('encontro');
                  return;
                }
              });
              if(!encontro){                
                //console.log('se agrega', producto.producto);
                producto.estado = {
                  bool: 1,
                  string: 'HABILITADO',
                  clase: 'label-success'
                }

                producto.temporal = true;
                producto.idproducto = null;
                $scope.gridOptionsDetalle.data.splice(0,0,producto);
                $scope.itemsTemporales.push(producto);   
                $scope.gridOptionsDetalle.totalItems = parseInt($scope.gridOptionsDetalle.totalItems) + 1;             
              }
            });
          }

          $scope.guardarProductos = function(){
            var datos = {
              listaProductos : $scope.gridOptionsDetalle.data,
              cliente: $scope.fData
            }
            convenioServices.sGuardarProductosConvenio(datos).then(function (rpta){                    
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.getPaginationProductosConvenioServerSide();
                $scope.itemsTemporales = [];
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';                      
              }else{
                alert('Error inesperado');
              }
              //$scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }

          $scope.btnAnularProducto = function(){
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                convenioServices.sAnularProducto($scope.mySelectionProductosConvenioGrid).then(function (rpta){                    
                  if(rpta.flag == 1){
                    var pTitle = 'OK!';
                    var pType = 'success';
                    $scope.getPaginationProductosConvenioServerSide();
                    $scope.getPaginationProductosServerSide();
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';                      
                  }else{
                    alert('Error inesperado');
                  }
                  //$scope.fData = {};
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });
              }
            });
          }

          $scope.btnHabilitarProducto = function (){
            var datos = {
              listaProductos: $scope.mySelectionProductosConvenioGrid,
              estado: 1
            }
            convenioServices.sCambiarEstadoListaProductosConvenio(datos).then(function (rpta){                    
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.getPaginationProductosConvenioServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';                      
              }else{
                alert('Error inesperado');
              }
              //$scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }

          $scope.btnDeshabilitarProducto = function (){
            var datos = {
              listaProductos: $scope.mySelectionProductosConvenioGrid,
              estado: 2
            }
            convenioServices.sCambiarEstadoListaProductosConvenio(datos).then(function (rpta){                    
              if(rpta.flag == 1){
                var pTitle = 'OK!';
                var pType = 'success';
                $scope.getPaginationProductosConvenioServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';                      
              }else{
                alert('Error inesperado');
              }
              //$scope.fData = {};
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }

          $scope.btnUpdatePorcentajeConvenio = function(){
            console.log('$scope.fData', $scope.fData);                                
            if($scope.fData.porcentaje!= null && !isNaN($scope.fData.porcentaje) && $scope.fData.porcentaje >= 0 && $scope.fData.porcentaje != ''){
              //$scope.getPaginationProductosConvenioServerSide();
              angular.forEach($scope.gridOptionsDetalle.data, function(producto, key) {                  
                var precio_regular = parseFloat(producto.precio_regular);
                producto.porcentaje = parseFloat($scope.fData.porcentaje).toFixed(2); 
                if($scope.fData.porcentaje == 0){
                  producto.precio_convenio = precio_regular.toFixed(2);
                }else{                  
                  producto.precio_convenio = precio_regular - ((precio_regular * producto.porcentaje) / 100); 
                  producto.precio_convenio = producto.precio_convenio.toFixed(2);

                  if(producto.precio_convenio % 1 != 0){
                    console.log('hay que redondear');
                    producto.precio_convenio = Math.floor(producto.precio_convenio = producto.precio_convenio).toFixed(2);
                    producto.porcentaje = (100 - ((producto.precio_convenio * 100 ) / precio_regular)).toFixed(2);
                  }
                }
              });
            }else{
              pinesNotifications.notify({ title: 'Aviso', text: 'Debe ingresar un valor numerico en el campo porcentaje', type: 'warning', delay: 3000 });
            }
          }

          $scope.checkVerPreciosConDecimal = function(){
            if($scope.fBusqueda.soloDecimales){ 
              $scope.gridTodos = $scope.gridOptionsDetalle.data;
              var esNumeroDecimal = function(elemento) {
                return elemento.precio_convenio % 1 != 0;
              } 
              var filtrados = $scope.gridOptionsDetalle.data.filter(esNumeroDecimal);
              $scope.gridOptionsDetalle.data = filtrados;
               $scope.gridOptionsDetalle.totalItems = filtrados.length;
            }else{
              $scope.gridOptionsDetalle.data = $scope.gridTodos;
              $scope.gridOptionsDetalle.totalItems = $scope.gridTodos.length;
            }
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
          }
        }, 
        resolve: {
          mySelectionGrid: function() {
            return $scope.mySelectionGrid;
          }
        }

      });
    };
    
      /* ============= */
     /* MANTENIMIENTO */
    /* ============= */
    $scope.btnRegEdit = function (modo) {
      $scope.modo = modo;
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Convenio/ver_popup_convenio',
        size: '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide) {
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.idtipocliente = null;
          console.log('Modo: ', $scope.modo);
          if( $scope.modo == 'Reg' ){
            $scope.titleForm = 'Registro de Convenio';
            $scope.fData.fec_inicial = $filter('date')(new Date(),'dd-MM-yyyy');
            $scope.fData.fec_vigencia = $filter('date')(new Date(),'dd-MM-yyyy');
            // LISTAR Empresas
            empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
              $scope.listaEmpresasAdmin = rpta.datos;
              $scope.fData.sede_convenio = $scope.listaEmpresasAdmin[0].id;
            });
          }else{
            if( $scope.mySelectionGrid.length == 1 ){
              $scope.fData = $scope.mySelectionGrid[0];
              // LISTAR Empresas
              empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
                $scope.listaEmpresasAdmin = rpta.datos;

              });
              console.log('fData: ',$scope.fData);
            }else{
              alert('Seleccione una sola fila');
            }
            $scope.titleForm = 'Edición de Convenio';
          }
       
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            //$scope.fData.detalle = $scope.gridOptions.data;
            console.log('editar o registrar ', $scope.modo);
            
            convenioServices.sRegEditConvenio($scope.fData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
                $modalInstance.dismiss('cancel');
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
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
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          convenioServices.sAnularConvenio($scope.mySelectionGrid).then(function (rpta) {
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
      .add ({
        combo: 'alt+n',
        description: 'Nuevo Tipo Cliente',
        callback: function() {
          $scope.btnRegEdit("Reg");
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar Tipo Cliente',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnRegEdit("Edit");
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular Tipo Cliente',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar Tipo Cliente',
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
  .service("convenioServices",function($http, $q) {
    return({
        sListarConvenio: sListarConvenio,
        sListarClienteConvenio: sListarClienteConvenio,
        sListarProductoConvenio: sListarProductoConvenio,
        sListarProductoNoEstanConvenio:sListarProductoNoEstanConvenio,
        sListarConvenioCbo: sListarConvenioCbo,
        sRegEditConvenio: sRegEditConvenio,
        sAnularConvenio: sAnularConvenio,
        sCambiarEstadoProductoConvenio: sCambiarEstadoProductoConvenio,
        sCambiarEstadoListaProductosConvenio: sCambiarEstadoListaProductosConvenio,
        sGuardarProductosConvenio:sGuardarProductosConvenio,
        sAnularProducto: sAnularProducto,
        sClienteNoAgreConvenioAutocompletar:sClienteNoAgreConvenioAutocompletar,
        sUpdateClienteConvenio:sUpdateClienteConvenio
       });
    function sListarConvenio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/lista_convenio",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarClienteConvenio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/lista_cliente_convenio",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProductoConvenio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/lista_producto_convenio",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarProductoNoEstanConvenio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/lista_producto_no_estan_convenio",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarConvenioCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/lista_convenio_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegEditConvenio (datos) {
      var request = $http({
            method : "post",
             url : angular.patchURLCI+"Convenio/registrarEditarConvenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularConvenio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/anularConvenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCambiarEstadoProductoConvenio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/cambiar_estado_producto_convenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }     
    function sCambiarEstadoListaProductosConvenio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/cambiar_estado_lista_productos_convenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sGuardarProductosConvenio (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/guardar_productos_convenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularProducto (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/anular_producto", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sClienteNoAgreConvenioAutocompletar(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/lista_cliente_no_agre_convenio_autocompletar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sUpdateClienteConvenio(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Convenio/update_cliente_convenio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  }).filter('mapEstado', function() {
  var sizeHash = {
    1: 'HABILITADO',
    2: 'DESHABILITADO',
  };
  return function(input) {
    if (!input){
      return '';
    } else {
      return sizeHash[input];
    }
  };
});