angular.module('theme.solicitudFormula', ['theme.core.services'])
  .controller('solicitudFormulaController', ['$scope', '$route', '$routeParams', '$controller', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI',
    'ventaServices',
    'solicitudFormulaServices',
    'empleadoSaludServices',
    'medicamentoServices',
    'medicamentoAlmacenServices',
    'tipoDocumentoServices',
    'clienteServices',
    'empresasClienteServices',
    'liquidacionFarmServices',
    'almacenFarmServices',
    function($scope, $route, $routeParams, $controller, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys,
      blockUI,
      ventaServices,
      solicitudFormulaServices,
      empleadoSaludServices,
      medicamentoServices,
      medicamentoAlmacenServices,
      tipoDocumentoServices,
      clienteServices,
      empresasClienteServices,
      liquidacionFarmServices,
      almacenFarmServices
    ){
      'use strict';
      $scope.modulo = 'solicitudFormula';
      $scope.isRegisterSuccess = false;
      // $scope.cajaAbiertaPorMiSession = false;
      // $scope.fCajaAbiertaSession = null;
      // $scope.antesDeImprimir = true;
      setTimeout(function() {
          $('#txtNumeroDocumento').focus(); // console.log($('#temporalProducto'));
      },1000);
      $controller('clienteController', {
        $scope : $scope
      });      
      $scope.fDataVenta = {};
      $scope.fDataVenta.esEditable = false;
      $scope.fDataVenta.esMedLibre = false;
      $scope.fDataVenta.cliente = {};
      $scope.fDataVenta.cliente_afiliado = {};
      //$scope.fDataVenta.idsolicitudformula = null;
      $scope.fDataVenta.aleasDocumento = 'SOLICITUD';
      $scope.fDataVenta.idsolicitudformula = '-';
      $scope.fDataVenta.temporal = {
        producto: null,
        cliente: null
      };
      $scope.fDataVenta.medico = {
        id : null,
        descripcion: null
      }
      $scope.fDataVenta.total = null;
      $scope.fDataVenta.temporal.cantidad = 1;
      $scope.fDataVenta.temporal.siBonificacion = false;
      $scope.fDataVenta.estemporal = false;
      // LISTA DE CATEGORIAS
      $scope.listaCategoria = [ 
        { id: '', 'descripcion': '-seleccione una opcion--' }, 
        { id: 'C0001', 'descripcion': 'MAGISTRALES' }, 
        { id: 'CO122', 'descripcion': 'TERMOCOSMETICOS' },
        { id: 'CO123', 'descripcion': 'DERMOESTETICOS' }, 
      ];
      $scope.fDataVenta.temporal.categoria = $scope.listaCategoria[0].id;
      // LISTA USOS
      $scope.listaUsos = [ 
        { id: '', 'descripcion': '-seleccione una opcion--' }, 
        { id: 'USO INTERNO', 'descripcion': 'USO INTERNO' }, 
        { id: 'USO EXTERNO', 'descripcion': 'USO EXTERNO' },
      ];
      $scope.fDataVenta.temporal.uso = $scope.listaUsos[0].id;
      $scope.getPersonalMedicoAutocomplete = function (value) {
        var params = {
          search: value,
          // id: '84' // idempresaespecialidad de CORPORACION FARMACOLOGICA JJ SALUD SAC - Dermatologia
          sensor: false
        }
        return empleadoSaludServices.sListarPersonalSaludCbo(params).then(function(rpta) {
        // return empleadoSaludServices.sListarMedicoEmpresaEspecialidadAutocomplete(params).then(function(rpta) {
          $scope.noResultsMedico = false;
          if( rpta.flag === 0 ){
            $scope.noResultsMedico = true;
          }
          return rpta.datos;
        });
      }
      $scope.getSelectedMedico = function ($item, $model, $label) {
        $scope.fDataVenta.idmedico = $item.idmedico;
        setTimeout(function() {
          $('#temporalProducto').focus(); //console.log('focus me',$('#temporalProducto'));
        }, 300);
      };
      $scope.getClearInputMedico = function () { 
        if(!angular.isObject($scope.fDataVenta.medico) ){ 
          $scope.fDataVenta.idmedico = null; 
        }
      }
      $scope.obtenerDatosCliente = function () {
        if( $scope.fDataVenta.numero_documento ){
          clienteServices.sListarEsteClientePorNumDoc($scope.fDataVenta).then(function (rpta) {
            $scope.fDataVenta.cliente = rpta.datos[0];
            if( rpta.flag === 1 ){
              pinesNotifications.notify({ title: 'OK.', text: 'Se encontró al cliente en el sistema.', type: 'success', delay: 2000 });
            }else{
              $scope.btnNuevoCliente("xlg",$scope.fDataVenta.numero_documento);
            }
          });
        }
      }
      $scope.btnQuitarDeLaCesta = function (row) {
        var index = $scope.gridOptions.data.indexOf(row.entity);
        $scope.gridOptions.data.splice(index,1);
        $scope.calcularTotales();
      }
      $scope.calcularTotales = function () {      
        var totales = 0;
        var total_exonerado = 0;
        var igv_exonerado = 0;
        $scope.bool_exonerado = false;
        
        angular.forEach($scope.gridOptions.data,function (value, key) {  
          $scope.gridOptions.data[key].precio = (parseFloat(value.precioBase)).toFixed(2); //0
          $scope.gridOptions.data[key].valor = ($scope.gridOptions.data[key].precio * parseFloat($scope.gridOptions.data[key].cantidad)).toFixed(2); // 0
          $scope.gridOptions.data[key].total = ( parseFloat($scope.gridOptions.data[key].valor) - parseFloat($scope.gridOptions.data[key].descuento || 0)).toFixed(2);
          totales += parseFloat($scope.gridOptions.data[key].total);
        });
        $scope.fDataVenta.igv_exonerado = igv_exonerado.toFixed(2);
        $scope.fDataVenta.total_sin_redondeo = ( totales + total_exonerado ).toFixed(2);
        $scope.fDataVenta.igv = ( totales - (totales / 1.18) ).toFixed(2);
        $scope.fDataVenta.subtotal = ( $scope.fDataVenta.total_sin_redondeo - $scope.fDataVenta.igv ).toFixed(2);
        $scope.fDataVenta.total = redondear($scope.fDataVenta.total_sin_redondeo,1).toFixed(2);
        $scope.fDataVenta.redondeo = ($scope.fDataVenta.total - $scope.fDataVenta.total_sin_redondeo).toFixed(2);
        $scope.fDataVenta.suma_total = (totales).toFixed(2);
        console.log('Venta: ', $scope.fDataVenta);
      }
      $scope.limpiarCampos = function (){
        $scope.fDataVenta.cliente = {};
        $scope.fDataVenta.cliente_afiliado = {};
        $scope.fDataVenta.numero_documento_afiliado = null;
        $scope.fDataVenta.temporal = {};
        $scope.fDataVenta.temporal.cantidad = 1;
        $scope.fDataVenta.temporal.categoria = $scope.listaCategoria[0].id;
      }
      $scope.limpiarCamposProductoTemporal = function (){
        $scope.fDataVenta.temporal.cantidad = 1;
        $scope.fDataVenta.temporal.precio = null;
        $scope.fDataVenta.temporal.stockMinimo = null;
        $scope.fDataVenta.temporal.idmedicamentoalmacen = null;
        $scope.fDataVenta.temporal.excluye_igv = null;
        $scope.fDataVenta.temporal.siBonificacion = false; // si el medicamento tiene bonificacion
        $scope.fDataVenta.temporal.bonificacion = false; // lo que se elige en el control, false: precio por defecto; true: precio = 0
        $scope.fDataVenta.esEditable = false;
        $scope.fDataVenta.temporal.categoria = $scope.listaCategoria[0].id;
        $scope.fDataVenta.temporal.uso = $scope.listaUsos[0].id;
      }
      $scope.getProductoAutocomplete = function (value) {
        var params = {
          searchText: value,
          searchColumn: "denominacion",
          sensor: false
        }
        return medicamentoAlmacenServices.sListarPreparadosAlmacenVentaAutoComplete(params).then(function(rpta) {
          $scope.noResultsLPSC = false;
          if( rpta.flag === 0 ){
            $scope.noResultsLPSC = true;
          }
          return rpta.datos;
        });
      }
      $scope.getSelectedProducto = function (item, model) {
        var $stago = false ;
        var $ptitle ;
        var $ptext ;
        $scope.fDataVenta.temporal.producto.descripcion_stock = $scope.fDataVenta.temporal.producto.descripcion;      

        $scope.fDataVenta.temporal.precio = model.precioSF;
        $scope.fDataVenta.temporal.stockMinimo = model.stockMinimo;
        $scope.fDataVenta.temporal.idmedicamentoalmacen = model.idmedicamentoalmacen;
        $scope.fDataVenta.temporal.excluye_igv = model.excluye_igv;
        if(model.categoria){
          $scope.fDataVenta.temporal.categoria = model.categoria;
        }
        if(model.uso){
          $scope.fDataVenta.temporal.uso = model.uso;
        }

        if(model.si_bonificacion == 1){
          $scope.fDataVenta.temporal.siBonificacion = true;
          $scope.fDataVenta.temporal.precioDefault = model.precioSF;
        }

        if($stago == true ){
          pinesNotifications.notify({ title: $ptitle , text: $ptext , type: 'error', delay: 3000 });
          $scope.fDataVenta.temporal.cantidad = 1;
          $scope.fDataVenta.temporal.stockActual = 0;
          return;
        }
        setTimeout(function() {
          $('#temporalCantidad').focus(); //console.log('focus me',$('#temporalProducto'));
        }, 300);     
        // console.log('temporal ', $scope.fDataVenta.temporal);
      }
      $scope.agregarItem = function () {
        $('#temporalProducto').focus();
        $scope.fDataVenta.esEditable = false;
        if( !angular.isObject($scope.fDataVenta.temporal.producto) ){
          $scope.fDataVenta.temporal.producto = null;
          $('#temporalProducto').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado el producto', type: 'warning', delay: 2000 });
          return false;
        }
        //console.log($scope.fDataVenta,$scope.fDataVenta.temporal.cantidad);
        if( !($scope.fDataVenta.temporal.cantidad >= 1) ){ // console.log('especialidad');
          //$scope.fDataVenta.temporal.cantidad = null;
          $('#temporalCantidad').focus().select();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado una cantidad correcta', type: 'warning', delay: 2000 });
          return false;
        }
        if( ($scope.fDataVenta.temporal.categoria == '') ){ // console.log('especialidad');
          //$scope.fDataVenta.temporal.cantidad = null;
          // $('#temporalCantidad').focus().select();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado una categoria', type: 'warning', delay: 2000 });
          return false;
        }
        if( ($scope.fDataVenta.temporal.uso == '') ){ // console.log('especialidad');
          //$scope.fDataVenta.temporal.cantidad = null;
          // $('#temporalCantidad').focus().select();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha seleccionado un uso', type: 'warning', delay: 2000 });
          return false;
        }

        if(!$scope.fDataVenta.temporal.siBonificacion){
          if( !($scope.fDataVenta.temporal.precio > 0) ){ // console.log('especialidad');
            pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto tiene un precio no válido', type: 'warning', delay: 2000 });
            return false;
          }
        }
        var productNew = true;
        angular.forEach($scope.gridOptions.data, function(value, key) {
          if(value.id == $scope.fDataVenta.temporal.producto.id ){
            productNew = false;
          }
        });
        if( productNew === false ){
          pinesNotifications.notify({ title: 'Advertencia.', text: 'El producto ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
          $scope.fDataVenta.temporal.producto= null;
          $scope.fDataVenta.temporal.cantidad= 1;
          return false;
        }
        $scope.arrTemporal = {
          'id' : $scope.fDataVenta.temporal.producto.id,
          'idmedicamentoalmacen' : $scope.fDataVenta.temporal.idmedicamentoalmacen,
          'descripcion' : $scope.fDataVenta.temporal.producto.descripcion,
          'cantidad' : parseInt($scope.fDataVenta.temporal.cantidad),
          'precioBase' : ($scope.fDataVenta.temporal.precio),
          'precio' : ($scope.fDataVenta.temporal.producto.precio),
          'precio_costo' : ($scope.fDataVenta.temporal.producto.precio_costo),
          'idtipocliente' : $scope.fDataVenta.cliente.idtipocliente,
          'idreceta': null,
          'idrecetamedicamento': null,
          'categoria' : $scope.fDataVenta.temporal.categoria,
          'uso' : $scope.fDataVenta.temporal.uso,
        };      
        $scope.gridOptions.data.push($scope.arrTemporal);

        $scope.calcularTotales();

        $scope.fDataVenta.temporal = { 
          cantidad: 1
        }
        $scope.fDataVenta.temporal.categoria = $scope.listaCategoria[0].id;
        $scope.fDataVenta.temporal.uso = $scope.listaUsos[0].id;
      }
      // GRILLA PARA AGREGAR PREPARADOS A LA SOLICITUD
      $scope.mySelectionGrid = [];
      $scope.gridOptions = {
        paginationPageSize: 10,
        enableRowSelection: false,
        enableSelectAll: false,
        enableFiltering: false,
        enableFullRowSelection: false,
        data: null,
        rowHeight: 30,
        enableCellEditOnFocus: true,
        multiSelect: false,
        showColumnFooter: true,
        showGridFooter: true,
        columnDefs: [
          { field: 'id', displayName: 'Cod.', width: '5%', enableCellEdit: false, enableSorting: false },
          { field: 'descripcion', displayName: 'Descripción', enableCellEdit: false, enableSorting: false
            // ,cellTemplate:'<span class="ml-xs"> {{ COL_FIELD.descripcion }} <label ng-show="COL_FIELD.si_campania || COL_FIELD.si_solicitud" style="box-shadow: 1px 1px 0 black; margin-left: 8px; display: inline;" class="label {{COL_FIELD.clase}}"> {{COL_FIELD.tipo}} </label></span>'
          },
          { field: 'cantidad', displayName: 'CANT.', width: '6%', enableSorting: false,
              enableCellEdit: true,
              cellClass: 'ui-editCell',
          },
          { field: 'precio', displayName: 'PRECIO', width: '9%', enableCellEdit: false, enableSorting: false },      
          { field: 'total', displayName: 'TOTAL', width: '12%', enableCellEdit: false, enableSorting: false },
          { field: 'accion', displayName: '', width: '4%', enableCellEdit: false, enableSorting: false, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
        ]
        ,onRegisterApi: function(gridApiCombo) {
          $scope.gridApiCombo = gridApiCombo;
          gridApiCombo.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
            rowEntity.column = colDef.field;
            if(rowEntity.column == 'cantidad' && newValue != oldValue){
              console.log('Verificando..');
              if( !(rowEntity.cantidad >= 1) ){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                rowEntity.cantidad = oldValue;
                pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser mayor o igual a 1', type: pType, delay: 3500 });
                return;
              }
              if ( !(rowEntity.cantidad % 1 == 0) ) {
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                rowEntity.cantidad = oldValue;
                pinesNotifications.notify({ title: pTitle, text: 'La cantidad debe ser un numero entero', type: pType, delay: 3500 });
                return;
              }
              if($scope.fDataVenta.cliente.idtipocliente){
                console.log('Calc Dcto...');
                rowEntity.descuento = (parseFloat(rowEntity.porcentaje_dcto * rowEntity.precio * rowEntity.cantidad )/100).toFixed(2);
              }
            }
            else if(rowEntity.column == 'descuento' && newValue != oldValue){
              if( !(rowEntity.descuento >= 0) ){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                rowEntity.descuento = oldValue;
                pinesNotifications.notify({ title: pTitle, text: 'El descuento no es válido', type: pType, delay: 3500 });
                return;
              }
            }
            $scope.calcularTotales();
            $scope.$apply();
          });
        }
      };
      $scope.toggleColumnFooter = function() {
        $scope.gridOptions.showColumnFooter = !$scope.gridOptions.showColumnFooter;
        $scope.gridApi.core.notifyDataChange(uiGridConstants.dataChange.OPTIONS);
      };
      $scope.getTableHeight = function() {
         var rowHeight = 30; // your row height
         var headerHeight = 30; // your header height
         var footerHeight = 30; // your footer height
         return {
            height: ($scope.gridOptions.data.length * rowHeight + headerHeight + footerHeight + 60) + "px"
         };
      };
      $scope.btnBuscarCliente = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'cliente/ver_popup_busqueda_cliente',
          size: size || '',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Búsqueda de Clientes';
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
                { field: 'id', name: 'idcliente', displayName: 'ID', maxWidth: 50,  sort: { direction: uiGridConstants.ASC} },
                { field: 'num_documento', name: 'num_documento', displayName: 'N° Doc.', maxWidth: 120 },
                { field: 'nombres', name: 'nombres', displayName: 'Nombres', maxWidth: 200 },
                { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'Apellido Paterno', maxWidth: 200 },
                { field: 'apellido_materno', name: 'apellido_materno', displayName: 'Apellido Materno', maxWidth: 200 }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionClienteGrid = gridApi.selection.getSelectedRows();
                  $scope.fDataVenta.cliente = $scope.mySelectionClienteGrid[0]; //console.log($scope.fDataVenta.cliente);
                  $scope.fDataVenta.numero_documento = $scope.mySelectionClienteGrid[0].num_documento;
                  $modalInstance.dismiss('cancel');
                  setTimeout(function() {
                    $('#temporalMedico').focus(); //console.log('focus me',$('#temporalProducto'));
                  }, 300);

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
                  paginationOptionsClienteEnVentas.searchColumn = {
                    'cl.idcliente' : grid.columns[1].filters[0].term,
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
              $scope.datosGrid = {
                paginate : paginationOptionsClienteEnVentas
              };
              clienteServices.sListarClientes($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsClienteBusqueda.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsClienteBusqueda.data = rpta.datos;              
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
      $scope.btnBuscarProducto = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'medicamento/ver_popup_busqueda_medicamento',
          size: size || '',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.titleForm = 'Búsqueda de Productos';
            var paginationOptionsProductos = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 10,
              sort: uiGridConstants.ASC,
              sortName: null,
              search: null
            };
            $scope.mySelectionProductoGrid = [];
            $scope.btnEliminarFormula = function(row) {
              var pMensaje ='¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) {
                if(result){
                  console.log('dentro ',row.entity);
                  medicamentoServices.sEliminarFormula(row.entity).then(function (rpta) {
                    if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success';
                        $scope.getPaginationProductoEnVentaServerSide();
                      }else if(rpta.flag == 0){
                        var pTitle = 'Aviso!';
                        var pType = 'warning';
                      }else{
                        alert('Error inesperado');
                      }
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  });
                }
              });
            }
            $scope.gridOptionsMedicamentoBusqueda = {
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
                { field: 'id', name: 'm.idmedicamento', displayName: 'COD.', maxWidth: 50, enableCellEdit: false },
                { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO', minWidth: 100,  sort: { direction: uiGridConstants.ASC}, cellClass: 'ui-editCell', },
                { field: 'precio', name: 'precio_venta', displayName: 'PRECIO', maxWidth: 80, cellClass: 'text-right', enableFiltering: false, enableCellEdit: false },
                { field: 'accion', displayName: '', width: '4%', enableCellEdit: false,
                  enableSorting: false, enableFiltering: false,
                  cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnEliminarFormula(row)"> <i class="fa fa-trash"></i> </button>' }
              ],
              onRegisterApi: function(gridApi) { // gridComboOptions
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope,function(row){
                  $scope.mySelectionPrincipioGrid = gridApi.selection.getSelectedRows();
                  $modalInstance.dismiss('cancel');
                  $scope.fDataVenta.temporal.idmedicamentoalmacen = $scope.mySelectionPrincipioGrid[0].idmedicamentoalmacen;
                    $scope.fDataVenta.temporal.precio = $scope.mySelectionPrincipioGrid[0].precio_venta_sf;
                    $scope.fDataVenta.temporal.producto = {
                      'id': $scope.mySelectionPrincipioGrid[0].id,
                      'descripcion_stock': $scope.mySelectionPrincipioGrid[0].medicamento,
                      'descripcion': $scope.mySelectionPrincipioGrid[0].medicamento,
                      'precio':$scope.mySelectionPrincipioGrid[0].precio_venta_sf,
                    };
                });
                gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                  rowEntity.column = colDef.field;
                  rowEntity.newvalue = newValue;
                  rowEntity.oldvalue = oldValue;
                  //console.log(rowEntity);
                  
                 
                  if(rowEntity.newvalue != rowEntity.oldvalue){
                    medicamentoServices.sEditarFormula(rowEntity).then(function (rpta) { 
                      if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success'; 
                      }else if(rpta.flag == 0){
                        var pTitle = 'Error!';
                        var pType = 'danger';
                      }else{
                        alert('Error inesperado');
                      }
                      $scope.getPaginationProductoEnVentaServerSide();
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    });
                  }
                  $scope.$apply();
                });

                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsProductos.sort = null;
                    paginationOptionsProductos.sortName = null;
                  } else {
                    paginationOptionsProductos.sort = sortColumns[0].sort.direction;
                    paginationOptionsProductos.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationProductoEnVentaServerSide();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsProductos.pageNumber = newPage;
                  paginationOptionsProductos.pageSize = pageSize;
                  paginationOptionsProductos.firstRow = (paginationOptionsProductos.pageNumber - 1) * paginationOptionsProductos.pageSize;
                  $scope.getPaginationProductoEnVentaServerSide();
                });
                $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                  var grid = this.grid;
                  paginationOptionsProductos.search = true;
                  paginationOptionsProductos.searchColumn = {
                    'm.idmedicamento' : grid.columns[1].filters[0].term,
                    "denominacion" : grid.columns[2].filters[0].term,

                  }
                  $scope.getPaginationProductoEnVentaServerSide();
                });
              }
            };

            paginationOptionsProductos.sortName = $scope.gridOptionsMedicamentoBusqueda.columnDefs[1].name;
            $scope.getPaginationProductoEnVentaServerSide = function() {
              //$scope.$parent.blockUI.start();
              $scope.datosGrid = {
                paginate : paginationOptionsProductos,
                datos: $scope.fDataVenta
              };
              medicamentoAlmacenServices.sListarPreparadosAlmacenBusquedaVenta($scope.datosGrid).then(function (rpta) {
                $scope.gridOptionsMedicamentoBusqueda.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsMedicamentoBusqueda.data = rpta.datos;

              });
              $scope.mySelectionProductoGrid = [];
            };
            $scope.getPaginationProductoEnVentaServerSide();
            
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      $scope.btnNuevoProducto = function (size) {
        $uibModal.open({
          templateUrl: angular.patchURLCI+'medicamento/ver_popup_formulario',
          size: 'lg',
          backdrop: 'static',
          scope: $scope,
          controller: function ($scope, $modalInstance) {
            $scope.fData = {};
            $scope.accion = 'reg';
            $scope.titleForm = 'Registro de Formulas';
            
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
                { field: 'precio', name: 'precio', displayName: 'P. COSTO', width: 110, enableCellEdit: true, 
                  enableColumnMenus: false, enableColumnMenu: false, type: 'float', cellClass:'text-center ui-editCell',cellTemplate: '<span>{{ COL_FIELD }}</span>',enableSorting: false }
              ],
              onRegisterApi: function(gridApi) {
                $scope.gridApi = gridApi;
              }
            };          
            almacenFarmServices.sListarAlmacenesParaMedicamentoSession().then(function (rpta) {
              $scope.gridOptionsAlmacenes.data = rpta.datos;
            });
            $scope.fData.idtipoproducto = 22;//TIPO PRODUCTO
            $scope.fData.agregarMedicamento = 'si';
                      
            $scope.aceptar = function () {
              $scope.fData.almacenes = $scope.gridOptionsAlmacenes.data;
              $scope.fData.registro_por_solicitud = true;
              medicamentoServices.sRegistrar($scope.fData).then(function (rpta) {
                if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $modalInstance.dismiss('cancel');
                  var params = {
                    searchText: $scope.fData.medicamento,
                    searchColumn: 'denominacion',
                    sensor: false
                  }
                  medicamentoAlmacenServices.sListarPreparadosAlmacenVentaAutoComplete(params).then(function(rpta) {              
                    $scope.fDataVenta.temporal.producto = JSON.parse('{"descripcion": "' + rpta.datos[0].descripcion + '"}');
                    $scope.fDataVenta.temporal.producto.id = rpta.datos[0].id;
                    $scope.fDataVenta.temporal.producto.descripcion_stock = rpta.datos[0].descripcion;
                    $scope.fDataVenta.temporal.precio = rpta.datos[0].precioSF;
                    $scope.fDataVenta.temporal.producto.precio_costo = rpta.datos[0].precio_costo;
                    $scope.fDataVenta.temporal.stockMinimo = rpta.datos[0].stockMinimo;
                    $scope.fDataVenta.temporal.idmedicamentoalmacen = rpta.datos[0].idmedicamentoalmacen;
                    $scope.fDataVenta.temporal.excluye_igv = rpta.datos[0].excluye_igv;
                    if(rpta.datos[0].si_bonificacion == 1){
                      $scope.fDataVenta.temporal.siBonificacion = true;
                      $scope.fDataVenta.temporal.precioDefault = rpta.datos[0].precioSF;
                    }
                  });              
                  
                }else if(rpta.flag == 0){
                  var pTitle = 'ADVERTENCIA!';
                  var pType = 'warning';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              });
            }
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
          }
        });
      }
      

      $scope.grabar = function (param) {
        var pParam = param || false;
        $scope.fDataVenta.detalle = $scope.gridOptions.data;
        if( $scope.fDataVenta.detalle.length < 1 ){
          $('#temporalProducto').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún preparado', type: 'warning', delay: 2000 });
          return false;
        }
        if(!$scope.fDataVenta.esMedLibre && $scope.fDataVenta.idmedico == null){
          $('#temporalMedico').focus();
          pinesNotifications.notify({ title: 'Advertencia.', text: 'No se ha agregado ningún médico', type: 'warning', delay: 2000 });
          return false;
        }
        blockUI.start();
          solicitudFormulaServices.sRegistrarSolicitud($scope.fDataVenta).then(function (rpta) {
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.isRegisterSuccess = true;
              $scope.fDataVenta.idsolicitudformula = rpta.idsolicitudformula;
              $scope.fDataVenta.temporal.producto = null;
              $scope.fDataVenta.temporal.precio = null;
              $scope.fDataVenta.temporal.precio_costo = null;
              $scope.fDataVenta.temporal.cantidad = null;
              $scope.fDataVenta.temporal.descuento = null;

              $bootbox.alert(rpta.message, function() {
                $route.reload();
              });

            }else if(rpta.flag == 0){
              var pTitle = 'Advertencia!';
              var pType = 'warning';
            }else{
              alert('Algo salió mal...');
            }
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            blockUI.stop();
          });
      }
      $scope.nuevo = function () {
        $route.reload();
      }
      /* ============================ */
      /* ATAJOS DE TECLADO NAVEGACION */
      /* ============================ */
      shortcut.remove('F2');
      shortcut.add("F2",function($event) {
        console.log('f2');
        if($scope.formVenta.$invalid){
          alert('Existen campos que son obligatorios y debe llenarlos');
        }else{
          $scope.calcularTotales();
          $scope.grabar();
       
        }
      });
      shortcut.remove('F3');
      shortcut.add("F3",function($event) {
        if($scope.isRegisterSuccess == true){
          $scope.nuevo();
        }else{
          $route.reload();
        }
        setTimeout(function() {
          $('#temporalProducto').focus(); // console.log($('#temporalProducto'));
        },1000);
      });
    }
  ])
  .service("solicitudFormulaServices",function($http, $q) {
    return({
        sRegistrarSolicitud: sRegistrarSolicitud,
        sListarSolicitudesProducto: sListarSolicitudesProducto,
        sImprimirTicketVenta: sImprimirTicketVenta,
    });

    function sRegistrarSolicitud(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"solicitudFormula/registrar_solicitud",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarSolicitudesProducto (datos) {
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"venta/listar_solicitudes_de_historia",
        data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sImprimirTicketVenta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ventaFarmacia/imprimir_ticket_venta",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
  });