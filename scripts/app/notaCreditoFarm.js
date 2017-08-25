angular.module('theme.notaCreditoFarm', ['theme.core.services'])
  .controller('notaCreditoFarmController', ['$scope', '$filter', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'notaCreditoFarmServices', 
    'empresaAdminServices', 
    'cajaServices', 
    'ventaServices',
    'ventaFarmaciaServices', 
    function($scope, $filter, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      notaCreditoFarmServices, 
      empresaAdminServices,
      cajaServices,
      ventaServices,
      ventaFarmaciaServices ){ 
    'use strict';
    shortcut.remove("F2"); $scope.modulo = 'notaCredito';
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null
    };
    $scope.fBusqueda = {};
    $scope.mySelectionGrid = [];

    $scope.dateUIDesde = {} ;
    $scope.dateUIDesde.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
    $scope.dateUIDesde.datePikerOptions = {
      formatYear: 'yy',
      // startingDay: 1,
      'show-weeks': false
    };
    $scope.dateUIDesde.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIDesde.opened = true;
    };

    $scope.dateUIHasta = {} ;
    $scope.dateUIHasta.formats = ['dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
    $scope.dateUIHasta.format = $scope.dateUIHasta.formats[0]; // formato por defecto
    $scope.dateUIHasta.datePikerOptions = {
      formatYear: 'yy',
      // startingDay: 1,
      'show-weeks': false
    };
    $scope.dateUIHasta.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIHasta.opened = true;
    };
    // $scope.fBusqueda.desde = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
    // var f=moment().format('YYYY-MM-DD');
    // f=moment().subtract('days',30);
    $scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusqueda.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    $scope.listarSedeEmpresaAdmin = function (){
      empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { 
        $scope.listaSedeEmpresaAdmin = rpta.datos;
        $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
        cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rpta) { 
          $scope.listaCajaMaster = rpta.datos;
          $scope.getPaginationServerSide();
        });
      });
    }
    $scope.listarSedeEmpresaAdmin();
    $scope.gridOptions = {
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      minRowsToShow: 8,
      useExternalPagination: true,
      useExternalSorting: true,
      useExternalFiltering : true, // se verá después
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      data : [] ,
      columnDefs: [
        { field: 'fecha_movimiento', name: 'fm.fecha_movimiento', displayName: 'FECHA EMISIÓN', width: '12%',  sort: { direction: uiGridConstants.DESC}, enableFiltering: false }, 
        { field: 'orden_venta', name: 'fm1.orden_venta', displayName: 'ORDEN VENTA', width: '15%' },
        { field: 'ticket_venta', name: 'ticket_venta', displayName: 'TICKET VENTA', width: '12%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width: '12%', enableFiltering: false },
        { field: 'numero_caja', name: 'numero_caja', displayName: 'N° CAJA', width: '8%' },
        { field: 'usuario', name: 'username', displayName: 'CAJERO', width: '10%' },
        { field: 'ticket', name: 'ticket', displayName: 'TICKET N.C.', width: '10%' }, 
        { field: 'monto', name: 'monto', displayName: 'MONTO', width: '10%', cellClass: 'bg-lightblue' }, 
        { field: 'tipo_nota_credito', displayName : 'Tipo' , filter: {
          term: 0,
          type: uiGridConstants.filter.SELECT,
          selectOptions: [{ value:0 , label:'TODOS'} , { value: 1, label: 'Nota Credito' }, { value: 2, label: 'Extorno' }]
        }}

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
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { // ESTO SE VERÁ DESPUES 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'fm1.orden_venta' : grid.columns[2].filters[0].term,
            'fm1.ticket_venta' : grid.columns[3].filters[0].term,
            'cm.numero_caja' : grid.columns[5].filters[0].term,
            'u.username' : grid.columns[6].filters[0].term,
            'fm.ticket_venta' : grid.columns[7].filters[0].term,
            'fm.total_a_pagar' : grid.columns[8].filters[0].term,
            'fm.tipo_nota_credito' : grid.columns[9].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
        
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      console.log('fBusqueda = ',$scope.fBusqueda);
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      notaCreditoFarmServices.sListarNotasCredito($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        $scope.gridOptions.suma_total = rpta.suma_total;
      });
      $scope.mySelectionGrid = [];
    };

    $scope.btnNuevo = function (size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'notaCreditoFarm/ver_popup_formulario',
        size: size || '',
        controller: function ($scope, $modalInstance, getPaginationServerSide) { 
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.titleForm = 'Registro de Nota de Crédito';
          // BLOQUEAR SI NO HAY CAJA ABIERTA 
          $scope.cajaAbiertaPorMiSession = false;
          $scope.fCajaAbiertaSession = null;
          $scope.fData.monto = null;

          var paginationOptionsDetalleVenta = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.gridOptionsDetalleVenta = {
            paginationPageSize: 10,
            useExternalPagination: false,
            useExternalSorting: false,
            useExternalFiltering : false,
            enableGridMenu: false,
            // enableRowSelection: true,
            // enableSelectAll: true,
            enableFiltering: false,
            minRowsToShow: 4,
            // enableFullRowSelection: true,
            enableCellEditOnFocus: true,
            data: [],
            // multiSelect: true,
            columnDefs: [
              { field: 'cantidad_original', name: 'cantidad_original', displayName: 'Cant.Or.', minWidth: 60,visible:false },
              { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', minWidth: 60, enableCellEdit: true, cellClass:'ui-editCell'  },
              { field: 'medicamento', name: 'medicamento', displayName: 'Medicamento',minWidth: 280 },
              { field: 'precio', name: 'precio', displayName: 'Precio',minWidth: 100 },
              { field: 'monto_sf', name: 'monto', displayName: 'Monto',minWidth: 100 },
              { field: 'accion', displayName: 'Acción', maxWidth: 95, cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              // gridApi.selection.on.rowSelectionChanged($scope,function(row){
              //     $scope.mySelectionGridDetalleVenta = gridApi.selection.getSelectedRows();
              //     $scope.calcularMontoNC($scope.mySelectionGridDetalleVenta);
              // });
              // gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
              //     $scope.mySelectionGridDetalleVenta = gridApi.selection.getSelectedRows();
              // });
              gridApi.edit.on.afterCellEdit($scope,function (rowEntity, colDef, newValue, oldValue){ 
                rowEntity.column = colDef.field;
                
                if(rowEntity.column == 'cantidad'){
                  if( parseInt(rowEntity.cantidad) > parseInt(rowEntity.cantidad_original) ){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    rowEntity.cantidad = oldValue;
                    pinesNotifications.notify({ title: pTitle, text: 'Cantidad de Nota de credito no puede ser mayor a la cantidad de la venta', type: pType, delay: 3500 });
                    return false;
                  }else{
                    rowEntity.monto_sf = parseFloat(rowEntity.cantidad * rowEntity.precio).toFixed(2);
                    $scope.calcularMontoNC($scope.gridOptionsDetalleVenta.data);
                  }
                }
                $scope.$apply();
              });

            }
          };
          $scope.getPaginationServerSideDetalleVenta = function() {
            $scope.datosGrid = {
              paginate : paginationOptionsDetalleVenta,
              datos : $scope.fData.orden
            };
            ventaFarmaciaServices.sListaDetalleVentaColumna($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalleVenta.data = rpta.datos;
              $scope.calcularMontoNC($scope.gridOptionsDetalleVenta.data);
            });
            // $scope.mySelectionGridDetalleVenta = [];
            //$scope.fData.monto = null;
            
          };
          $scope.limpiarGrid = function(){
            $scope.gridOptionsDetalleVenta.data = [];
            $scope.fData.monto = null;
          }
          cajaServices.sGetFarmaciaCajaActualUsuario().then(function (rpta) { 
            if(rpta.flag === 1) {
              $scope.cajaAbiertaPorMiSession = true;
              $scope.fCajaAbiertaSession = rpta.datos;
            }
          });
          
          $scope.getOrdenesVentaAutocomplete = function (value) { 
            var params = {
              search: value,
              sensor: false
            }
            return ventaFarmaciaServices.sListarOrdenesVenta(params).then(function(rpta) { 
              $scope.noResultsOV = false;
              if( rpta.flag === 0 ){
                $scope.noResultsOV = true;
              }
              return rpta.datos; 
            });
          }
          $scope.generarCodigoTicket = function () { 
              var arrParams = {
                idtipodocumento : 7, // NOTA DE CREDITO 
                idmodulo: 3 // FARMACIA
              }
              ventaServices.sGenerarCodigoTicket(arrParams).then(function (rpta) { 
                $scope.fData.ticket = rpta.ticket;
                $scope.fData.serie = rpta.serie;
                $scope.fData.numero_serie = rpta.numero_serie;
              });
            //}
          }

          $scope.calcularMontoNC = function (valores) {
            console.log('$scope.fData.orden',$scope.fData.orden);
            if ($scope.fData.orden.es_preparado == 2 ){
              var totales = 0; 
              angular.forEach(valores,function (value, key) { 
                // var valor = String( valores[key].precio);
                // var number = valor.replace(/S\/./g,"");
                var number = value.precio * value.cantidad;
                totales += parseFloat(number);
              });
              $scope.fData.monto = redondear(totales,1).toFixed(2);
              if($scope.fData.monto == '0.00'){
                $scope.fData.monto = null;
              }  
            }else{
              $scope.fData.monto = $scope.fData.orden.saldo_format
            }
            
          }
          $scope.btnQuitarDeLaCesta = function (row) {
            if($scope.fData.orden.es_preparado == 1){
               pinesNotifications.notify({ title: 'Advertencia.', text: 'No se permite eliminar ningún preparado', type: 'warning', delay: 2000 });
               return false;
            }
            var index = $scope.gridOptionsDetalleVenta.data.indexOf(row.entity); 
            $scope.gridOptionsDetalleVenta.data.splice(index,1);
            $scope.calcularMontoNC($scope.gridOptionsDetalleVenta.data);
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            $scope.fData.detalle = $scope.gridOptionsDetalleVenta.data;
            var valor = String( $scope.fData.orden.saldo);
            var number = valor.replace(/S\/./g,"");
            if(parseFloat($scope.fData.monto) <= 0 ){ // console.log('especialidad');
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El monto no puede ser menor ó igual a Cero', type: 'warning', delay: 2000 });
              return false;
            }
            if( parseFloat($scope.fData.monto)  >  parseFloat(number) ){ // console.log('especialidad');
              pinesNotifications.notify({ title: 'Advertencia.', text: 'El monto no puede ser mayor al Saldo del Ticket', type: 'warning', delay: 2000 });
              return false;
            }
            var pMensaje = 'Ud. va a crear una Nota de Crédito con el monto de S./' + $scope.fData.monto +' ¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                notaCreditoFarmServices.sRegistrar($scope.fData).then(function (rpta) { 
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
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                });
              }
            });  
          }
          $scope.generarCodigoTicket();
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
          notaCreditoFarmServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
  }])
  .service("notaCreditoFarmServices",function($http, $q) {
    return({
        sListarNotasCredito: sListarNotasCredito, 
        sRegistrar: sRegistrar,
        sAnular: sAnular
    });

    function sListarNotasCredito(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NotaCreditoFarm/lista_nota_credito", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NotaCreditoFarm/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NotaCreditoFarm/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });