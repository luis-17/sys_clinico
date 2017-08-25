angular.module('theme.comprobanteWeb', ['theme.core.services'])
  .controller('comprobanteWebController', ['$scope', '$sce', '$uibModal', 'blockUI', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'comprobanteWebServices',  
    function($scope, $sce, $uibModal, blockUI, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, comprobanteWebServices ){
    'use strict';
    $scope.dirComprobantes = 'https://citasenlinea.villasalud.pe/comprobantesWeb/';

    $scope.listaEstado =[
      {estado_comprobante:2, descripcion:'POR EMITIR'},
      {estado_comprobante:1, descripcion:'EMITIDOS'},
      {estado_comprobante:0, descripcion:'TODOS'},
    ];
    $scope.filtroEstado = $scope.listaEstado[0];

    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.ASC,
      sortName: null,
      search: null,
    };
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
      enableSelectAll: false,
      enableFiltering: false,
      enableFullRowSelection: false,
      multiSelect: false,
      columnDefs: [
        { field: 'idusuariowebpago', name: 'idusuariowebpago', displayName: 'ID', maxWidth: 50,  sort: { direction: uiGridConstants.ASC,priority: 0 } },
        { field: 'cliente', name: 'cliente', displayName: 'Cliente' },
        { field: 'num_documento', name: 'num_documento', displayName: 'N° Documento', maxWidth: 120 },
        { field: 'orden_venta', name: 'orden_venta', displayName: 'N° Orden', maxWidth: 125},
        //{ field: 'especialidad', name: 'especialidad', displayName: 'Especialidad', maxWidth: 165},
        { field: 'fecha_pago', name: 'fecha_pago', displayName: 'Fecha de Pago',maxWidth: 155,enableFiltering:false, },
        { field: 'numero_comprobante', name: 'numero_comprobante', displayName: 'N° Comprobante', maxWidth: 125},
        { field: 'sub_total', name: 'sub_total', displayName: 'Sub Total (S/.)',width:'100',enableFiltering:false, },
        { field: 'total_igv', name: 'total_igv', displayName: 'IGV Total (18%)',width:'100',enableFiltering:false, },
        { field: 'total_a_pagar', name: 'total_a_pagar', displayName: 'Total (S/.)',width:'100',enableFiltering:false, },
        { field: 'estado_comprobante', type:'object', name: 'estado_comprobante', displayName: 'ESTADO', width:'100', enableSorting:false, enableFiltering:false, 
          cellTemplate:'<div class="ui-grid-cell-contents" ng-if="COL_FIELD.boolean == 1"><a target="_blank"  href="{{ grid.appScope.dirComprobantes + COL_FIELD.nombre_archivo }}"><label style="width: 120px;cursor:pointer;" class="label {{ COL_FIELD.clase }}">{{ COL_FIELD.string }}</label></a></div> <div class="ui-grid-cell-contents" ng-if="COL_FIELD.boolean == 2"><label style="width: 120px;"  class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label></div>' 
        },
        { field: 'estado_comprobante', type:'object', name:"mail", displayName: 'E-MAIL', width:'80', enableSorting:false, enableFiltering:false, 
          cellTemplate:'<div class="ui-grid-cell-contents" ng-if="COL_FIELD.boolean == 1"><button ng-click="grid.appScope.btnEnviaMail(row.entity);" class="btn btn-success" style="padding:0px 5px;"><i class="fa fa-envelope-o"></i></button></div> <div class="ui-grid-cell-contents" ng-if="COL_FIELD.boolean == 2"><button class="btn btn-default" style="padding:0px 5px;"><i class="fa fa-envelope-o"></i></button></div>' 
        },          
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
              'uwp.idusuariowebpago' : grid.columns[1].filters[0].term,
              "c.nombres || ' ' || c.apellido_paterno || ' ' || c.apellido_materno": grid.columns[2].filters[0].term,
              'c.num_documento' : grid.columns[3].filters[0].term,
              'cv.orden_venta' : grid.columns[4].filters[0].term,
              'esp.nombre' : grid.columns[5].filters[0].term,
            } 
            $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        filtros: {estado: $scope.filtroEstado}
      };
      comprobanteWebServices.sListaVentasWeb($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos; 
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();

    $scope.btnCargar = function(row){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ComprobanteWeb/ver_popup_formulario',
        size: '',
        scope: $scope,
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance ) {
          $scope.titleFormDet = 'Subir Comprobante de Pago Web';
          $scope.fDataSubida = {}; 
          $scope.fDataPago = row; 
          $scope.aceptarSubida = function (){
            blockUI.start('Ejecutando proceso...');
            $scope.fDataSubida.idpago = $scope.fDataPago.idusuariowebpago;
            $scope.fDataSubida.idventa = $scope.fDataPago.idventa;
            var formData = new FormData();                  
            angular.forEach($scope.fDataSubida,function (index,val) { 
              formData.append(val,index);
            });
            comprobanteWebServices.sSubirComprobanteWeb(formData).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.cancelSubida();
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              blockUI.stop();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }

          $scope.cancelSubida = function () { 
            $modalInstance.dismiss('cancelSubida'); 
          }
        }
      });
    }

    $scope.btnBorrar = function(row){
      var pMensaje = '¿Realmente desea borrar el Comprobante?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          blockUI.start('Ejecutando proceso...');
          var datos = {
            idusuariowebpago: row.idusuariowebpago,
            nombre_archivo:  row.nombre_archivo,
          }
          comprobanteWebServices.sBorrarComprobanteWeb(datos).then(function(rpta){
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
              $scope.getPaginationServerSide();
            }else if(rpta.flag == 0){
              var pTitle = 'Aviso!';
              var pType = 'warning';
            }else{
              alert('Error inesperado');
            }
            blockUI.stop();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
          });
        }
      });
    }

    $scope.btnEnviaMail = function(row){
      var pMensaje = '¿Esta seguro de envíar el correo?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          console.log('btnEnviaMail', row);
          blockUI.start('Ejecutando proceso...');
          comprobanteWebServices.sEnviaMailComprobanteWeb(row).then(function(rpta){
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success';
            }else if(rpta.flag == 0){
              var pTitle = 'Aviso!';
              var pType = 'warning';
            }else{
              alert('Error inesperado');
            }
            blockUI.stop();
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
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
        description: 'Nueva canal',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar canal',
        callback: function() {
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular canal',
        callback: function() {
          if( $scope.mySelectionGrid.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar canal',
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
  .service("comprobanteWebServices",function($http, $q) {
    return({
        sListaVentasWeb: sListaVentasWeb,
        sSubirComprobanteWeb: sSubirComprobanteWeb,
        sBorrarComprobanteWeb: sBorrarComprobanteWeb,
        sEnviaMailComprobanteWeb: sEnviaMailComprobanteWeb,
    });

    function sListaVentasWeb(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ComprobanteWeb/lista_ventas_web", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sSubirComprobanteWeb(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ComprobanteWeb/subir_comprobante_web", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sBorrarComprobanteWeb(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ComprobanteWeb/borrar_comprobante_web", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sEnviaMailComprobanteWeb(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ComprobanteWeb/enviar_mail_comprobante", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });