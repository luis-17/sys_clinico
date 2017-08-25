angular.module('theme.notaCredito', ['theme.core.services'])
  .controller('notaCreditoController', ['$scope', '$filter', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'notaCreditoServices', 
    'empresaAdminServices',
    'cajaServices',
    'ventaServices', 
    function($scope, $filter, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      notaCreditoServices, 
      empresaAdminServices,
      cajaServices,
      ventaServices ){ 
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
    //$scope.fBusqueda.desde = $filter('date')(new Date(),'dd-MMMM-yyyy');
    $scope.fBusqueda.desde = new Date();
    $scope.fBusqueda.hasta = new Date();
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.navegateToCell = function( rowIndex, colIndex ) {
      $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
    };
    $scope.listarSedeEmpresaAdmin = function () {
      empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { 
        $scope.listaSedeEmpresaAdmin = rpta.datos;
        $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
        // cajaServices.sListarCajasCbo($scope.fBusqueda).then(function (rpta) { 
        //   $scope.listaCajaMaster = rpta.datos;
        // });
        $scope.getPaginationServerSide();
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
      columnDefs: [
        { field: 'fecha_emision', name: 'fecha_creacion_nc', displayName: 'FECHA EMISIÓN', width: '12%',  sort: { direction: uiGridConstants.DESC}, enableFiltering: false }, 
        { field: 'orden', name: 'orden_venta', displayName: 'ORDEN VENTA', width: '15%' },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET VENTA', width: '12%', visible: true },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA VENTA', width: '12%', enableFiltering: false },
        { field: 'numero_caja', name: 'numero_caja', displayName: 'N° CAJA', width: '8%' },
        { field: 'usuario', name: 'username', displayName: 'CAJERO' },
        { field: 'ticket_nc', name: 'ticket_nc', displayName: 'TICKET N.C.', width: '10%' }, 
        { field: 'monto', name: 'monto', displayName: 'MONTO', width: '10%', cellClass: 'bg-lightblue' }, 
        { field: 'tipo_documento', name: 'descripcion_td', displayName: 'TIPO. DOC. ORIGEN', width: '15%', visible: false }, 
        { field: 'sede', name: 'sede', displayName: 'SEDE', width: '15%', visible: false }, 
        { field: 'empresa_admin', name: 'empresa_admin', displayName: 'EMPRESA', width: '15%', visible: false }, 
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
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { // ESTO SE VERÁ DESPUES 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'orden_venta' : grid.columns[2].filters[0].term,
            'ticket_venta' : grid.columns[3].filters[0].term,
            'numero_caja' : grid.columns[5].filters[0].term,
            'username' : grid.columns[6].filters[0].term,
            'ticket_nc' : grid.columns[7].filters[0].term,
            'nc.monto' : grid.columns[8].filters[0].term,
            'descripcion_td' : grid.columns[9].filters[0].term,
            's.descripcion' : grid.columns[10].filters[0].term,
            'ea.razon_social' : grid.columns[11].filters[0].term
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
      $scope.fBusqueda.fHasta = moment($scope.fBusqueda.hasta).format('DD-MM-YYYY');
      console.log('Hasta: ', $scope.fBusqueda.fHasta);
      console.log('moment: ', $scope.fBusqueda.fHasta );
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos : $scope.fBusqueda
      };
      notaCreditoServices.sListarNotasCredito($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        $scope.gridOptions.sumTotal = rpta.sumTotal;
      });
      $scope.mySelectionGrid = [];
    };
    // $scope.btnConsultar = function () { 
    //   $scope.getPaginationServerSide();
    // }
    $scope.btnNuevo = function (size) { 
      $modal.open({ 
        templateUrl: angular.patchURLCI+'notaCredito/ver_popup_formulario',
        size: size || '',
        controller: function ($scope, $modalInstance, getPaginationServerSide) { 
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          //$scope.fData.monto = 0;
          $scope.titleForm = 'Registro de Nota de Crédito';
          // BLOQUEAR SI NO HAY CAJA ABIERTA 
          $scope.cajaAbiertaPorMiSession = false;
          $scope.fCajaAbiertaSession = null;
          cajaServices.sGetCajaActualUsuario().then(function (rpta) { 
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
            return ventaServices.sListarOrdenesVentaCajaCerrada(params).then(function(rpta) { 
              $scope.noResultsOV = false;
              if( rpta.flag === 0 ){
                $scope.noResultsOV = true;
              }
              return rpta.datos; 
            });
          }
          $scope.generarCodigoTicket = function () { 
            //if( $scope.fDataVenta.idtipodocumento ){ 
              //console.log($scope.fDataVenta.idtipodocumento);
              var arrParams = {
                idtipodocumento : 7, // NOTA DE CREDITO 
                idmodulo: 1 // HOSPITAL 
              }
              ventaServices.sGenerarCodigoTicket(arrParams).then(function (rpta) { 
                $scope.fData.ticket = rpta.ticket;
                $scope.fData.serie = rpta.serie;
                $scope.fData.numero_serie = rpta.numero_serie;
              });
            //}
          }
          $scope.onSelect =  function(item, model, label){
            console.log(item, model, label);
            $scope.getPaginationDtServerSide();
          }
          $scope.mySelectionGridDt = [];
          $scope.gridOptionsDt = {
            minRowsToShow: 4,
            useExternalPagination: true,
            useExternalSorting: true,
            useExternalFiltering : true, // se verá después
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: true,
            columnDefs: [
              { field: 'iddetalle', name: 'iddetalle', displayName: 'ID', width: '15%'}, 
              { field: 'descripcion', name: 'descripcion', displayName: 'DESCRIPCIÓN'},
              { field: 'total_detalle', name: 'total_detalle', displayName: 'TOTAL', width: '20%'},
              { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '12%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
                cellTemplate:'<div class="">'+

                  '<label tooltip-placement="left" tooltip="VENDIDO" style="box-shadow: 1px 1px 0 black;" class="label label-success ml-xs">'+ 
                  '<i class="fa fa-check"></i> </label>'+ 

                  '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+ 
                  '<i ng-if="row.entity.estado.claseIcon != null" class="fa {{ COL_FIELD.claseIcon }}"></i> </label>'+ 

                  '</div>' 
              }
            ], 
            onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){

                if(row.entity.estado.atendido == 1 || row.entity.estado.nota_credito == 1){
                  row.isSelected = false;
                  row.visible = false;
                  var pTitle = 'Error!';
                  var pType = 'danger';
                  var pMessage = '';
                  if(row.entity.estado.atendido == 1){
                    pMessage = 'El item ' + row.entity.iddetalle + ' ya fue atendido';
                  }else{
                    pMessage = 'El item ' + row.entity.iddetalle + ' posee nota de crédito';
                  }
                  pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
                }else{

                  $scope.mySelectionGridDt = gridApi.selection.getSelectedRows();
                  var total = 0;
                  angular.forEach($scope.mySelectionGridDt, function(value, key) {
                    total = parseFloat(total) + parseFloat(value.total); 
                  });   
                  if(total > 0){
                    $scope.fData.monto = "S/. " + total.toFixed(2);
                    $scope.fData.monto_format = total.toFixed(2);
                  }else{
                    $scope.fData.monto = null;
                  }
                  $scope.fData.detalle = $scope.mySelectionGridDt;
                }
                
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
               
                $scope.mySelectionGridDt = [];
                var total = 0;
                angular.forEach(rows, function(value, key) {
                  if(value.entity.estado.atendido == 1 || value.entity.estado.nota_credito == 1){
                    if (value.isSelected && value.visible){
                      value.isSelected = false;
                      value.visible = false;
                      var pTitle = 'Error!';
                      var pType = 'danger';
                      var pMessage = '';
                      if(value.entity.estado.atendido == 1){
                        pMessage = 'El item ' + value.entity.iddetalle + ' ya fue atendido';
                      }else{
                        pMessage = 'El item ' + value.entity.iddetalle + ' posee nota de crédito';
                      }
                      pinesNotifications.notify({ title: pTitle, text: pMessage, type: pType, delay: 2000 });
                    }
                  }else{
                    total = parseFloat(total) + parseFloat(value.entity.total);
                    $scope.mySelectionGridDt.push(value.entity);
                  }
                  
                });

                if(total > 0){
                  $scope.fData.monto = "S/. " + total.toFixed(2);
                  $scope.fData.monto_format = total.toFixed(2);
                }else{
                  $scope.fData.monto = null;
                }
                $scope.fData.detalle = $scope.mySelectionGridDt;
              });
            }    
          };
          $scope.getPaginationDtServerSide = function() {
            notaCreditoServices.sListarDetalleVentaNC($scope.fData.orden).then(function (rpta) {
              //$scope.gridOptions.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDt.data = rpta.datos;
              console.log(rpta.datos);
            });
            $scope.mySelectionGridDt = [];
          };
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () {
            notaCreditoServices.sRegistrar($scope.fData).then(function (rpta) { 
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
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
          notaCreditoServices.sAnular($scope.mySelectionGrid).then(function (rpta) {
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
  .service("notaCreditoServices",function($http, $q) {
    return({
        sListarNotasCredito: sListarNotasCredito,
        sListarDetalleVentaNC: sListarDetalleVentaNC, 
        sRegistrar: sRegistrar,
        sAnular: sAnular
    });

    function sListarNotasCredito(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"notaCredito/lista_nota_credito", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"notaCredito/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"notaCredito/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    // function sListarDetalleAperturaCajas(datos) { 
    //   var request = $http({
    //         method : "post",
    //         url : angular.patchURLCI+"notaCredito/lista_nota_credito", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }
    function sListarDetalleVentaNC(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"notaCredito/lista_detalle_venta_nc", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });