angular.module('theme.historialCitas', ['theme.core.services'])
  .controller('historialCitasController', ['$scope', '$filter', '$sce', '$route', '$interval', '$controller', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI', 
    'historialCitasServices',
    'especialidadServices',
    'progMedicoServices', 
    function($scope, $filter, $sce, $route, $interval, $controller, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI, 
      historialCitasServices,
      especialidadServices,
      progMedicoServices ){ 

    'use strict';
    shortcut.remove("F2");
    shortcut.remove("F8");
    shortcut.add("F8",function(){ 
      if($scope.mySelectionGridHC.length > 0){ 
        $scope.btnImprimirTicketManual(); 
        $('#fechaVenta').focus(); 
      }
    });

    $scope.modulo = 'historialCitas';
    // $scope.cajaAbiertaPorMiSession = false;
    // $scope.fCajaAbiertaSession = null;
    $scope.fBusquedaCitas = {};
    $scope.fBusquedaCitas.desde = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.fBusquedaCitas.desdeHora = '00';
    $scope.fBusquedaCitas.desdeMinuto = '00';
    $scope.fBusquedaCitas.hastaHora = 23;
    $scope.fBusquedaCitas.hastaMinuto = 59;
    $scope.fBusquedaCitas.hasta = $filter('date')(new Date(),'dd-MM-yyyy');
    $scope.mySelectionGridHC = [];
    // $scope.btnToggleFiltering = function(){ 
    //   $scope.gridOptionsCitas.enableFiltering = !$scope.gridOptionsCitas.enableFiltering;
    //   $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    // }; 
    // empresaAdminServices.sListarSedeEmpresaAdminCbo().then(function (rpta) { //console.log(rpta);
    //   $scope.listaSedeEmpresaAdmin = rpta.datos;
    //   $scope.fBusqueda.sedeempresa = $scope.fSessionCI.idsedeempresaadmin;
    // });

    // ESPECIALIDAD 
    especialidadServices.sListarEspecialidadesProgramCbo().then(function (rpta) { 
      $scope.listaEspecialidades = rpta.datos;
      $scope.listaEspecialidades.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
      $scope.fBusquedaCitas.especialidad = $scope.listaEspecialidades[0];
    });
    // CONVENIO 
    // convenioServices.sListarConvenioCbo().then(function (rpta) { 
    //   $scope.listaConvenios = rpta.datos;
    //   $scope.listaConvenios.splice(0,0,{ id : 'ALL', descripcion:'TODOS'});
    //   $scope.fBusqueda.convenio = $scope.listaConvenios[0];
    // });

    // TIPO DE ATENCION
    $scope.listaTipoAtencion = [];
    $scope.listaTipoAtencion[0]={ id : 'ALL', descripcion:'TODOS'};
    $scope.listaTipoAtencion[1]={ id : 'CM', descripcion:'CONSULTA MEDICA'};
    $scope.listaTipoAtencion[2]={ id : 'P', descripcion:'PROCEDIMIENTO'};
    $scope.fBusquedaCitas.tipoAtencion = $scope.listaTipoAtencion[0];

    /* GRID DE VENTAS */
    var paginationOptions = { 
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    }; 
    /*
            '<label tooltip-placement="left" tooltip="ATENDIDO" style="box-shadow: 1px 1px 0 black;" class="label label-info ml-xs">'+ 
            '<i ng-if="COL_FIELD.claseIconAtendido" class="fa {{ COL_FIELD.claseIconAtendido }}"></i> </label>'+ 
    */
    $scope.gridOptionsCitas = { 
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
      columnDefs: [ 
        { field: 'iddetalle', name: 'pa.iddetalle', displayName: 'ID DETALLE', width: '6%'},
        { field: 'idprogcita', name: 'pa.idprogcita', displayName: 'ID CITA', width: '9%', visible: false, enableFiltering: false },
        { field: 'orden', name: 'v.orden_venta', displayName: 'N° ORDEN', width: '9%', visible: false },
        { field: 'cliente', name: 'cliente', displayName: 'PACIENTE', width: '18%' },
        { field: 'tipodocumento', name: 'descripcion_td', displayName: 'TIPO DOC.', width: '7%', cellClass: 'bg-lightblue', sort: { direction: uiGridConstants.DESC}, visible: false },
        { field: 'ticket', name: 'ticket_venta', displayName: 'TICKET', width: '7%', sort: { direction: uiGridConstants.DESC} },
        { field: 'medico', name: 'medico', displayName: 'MEDICO PROGRAMADO', width: '15%' },
        { field: 'fecha_venta', name: 'fecha_venta', displayName: 'FECHA DE VENTA', width: '8%', enableFiltering: false, visible: false },
        { field: 'fecha_reg_cita', name: 'fecha_reg_cita', displayName: 'FECHA DE REGISTRO', width: '8%', enableFiltering: false },
        { field: 'fecha_atencion_cita', name: 'fecha_atencion_cita', displayName: 'FECHA DE ATENCIÓN', width: '8%', enableFiltering: false },
        { field: 'canal', name: 'descripcion_can', displayName: 'CANAL', width: '6%', visible: false, enableFiltering: false },
        { field: 'turno', name: 'turno', displayName: 'TURNO', width: '8%', enableFiltering: false, enableSorting: false },
        { field: 'si_adicional', name: 'si_adicional', displayName: 'ADICIONAL', width: '7%', enableFiltering: false, enableSorting: false, visible: false },
        { field: 'ambiente', name: 'amb.numero_ambiente', displayName: 'N° AMBIENTE', width: '5%' },
        { field: 'producto', name: 'pm.descripcion', displayName: 'PRODUCTO', width: '10%' },
        { field: 'medio', name: 'mp.descripcion_med', displayName: 'MEDIO DE PAGO', width: '10%', visible: false },
        { field: 'monto', name: 'total_detalle', displayName: 'MONTO', width: '5%', cellClass: 'bg-lightblue' }, 
        { field: 'estado', type: 'object', name: 'estado', displayName: ' ', width: '9%', enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, 
          cellTemplate:'<div class="">'+ 
            '<label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} ml-xs">'+ 
            '<i class="fa {{ COL_FIELD.claseIcon }}"></i> {{COL_FIELD.labelText}} </label>'+ 
            '</div>' 
        }
      ],
      onRegisterApi: function(gridApi) { 
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionGridHC = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionGridHC = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) { 
          console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptions.sort = null;
            paginationOptions.sortName = null;
          } else {
            paginationOptions.sort = sortColumns[0].sort.direction;
            paginationOptions.sortName = sortColumns[0].name;
          }
          $scope.getPaginationServerSide(true);
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) { 
          var grid = this.grid;
          paginationOptions.search = true; 
          paginationOptions.searchColumn = { 
            'de.iddetalle' : grid.columns[1].filters[0].term,
            'v.orden_venta' : grid.columns[3].filters[0].term,
            "CONCAT(c.nombres,' ',c.apellido_paterno,' ',c.apellido_materno)" : grid.columns[4].filters[0].term,
            'descripcion_td' : grid.columns[5].filters[0].term,
            'v.ticket_venta' : grid.columns[6].filters[0].term,
            "CONCAT(med.med_nombres,' ',med.med_apellido_paterno,' ',med.med_apellido_materno)" : grid.columns[7].filters[0].term,
            'descripcion_can' : grid.columns[10].filters[0].term,
            'amb.numero_ambiente' : grid.columns[14].filters[0].term,
            'pm.descripcion' : grid.columns[15].filters[0].term,
            'mp.descripcion_med' : grid.columns[16].filters[0].term,
            'total_detalle' : grid.columns[17].filters[0].term 
          }
          $scope.getPaginationServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) { 
          paginationOptions.pageNumber = newPage;
          paginationOptions.pageSize = pageSize;
          paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
          $scope.getPaginationServerSide(true);
        }); 
      }
    };
    paginationOptions.sortName = $scope.gridOptionsCitas.columnDefs[3].name;
    $scope.getPaginationServerSide = function(load) { 
      var loader = load || false;
      if( loader ){ 
        blockUI.start('Ejecutando proceso...');
      }
      var arrParams = {
        paginate : paginationOptions,
        datos : $scope.fBusquedaCitas
      };
      console.log($scope.fBusquedaCitas);
      historialCitasServices.sListarCitasHistorial(arrParams).then(function (rpta) { 
        $scope.gridOptionsCitas.totalItems = rpta.paginate.totalRows;
        // $scope.gridOptionsCitas.totalImporte = rpta.paginate.sumTotal;
        $scope.gridOptionsCitas.data = rpta.datos;
        if( loader ){ 
          blockUI.stop();
        }
      });
      $scope.mySelectionGridHC = [];
    };

    $scope.boolExterno= true;
    $controller('ventaController', { 
      $scope : $scope
    });

    $scope.btnModificarCita = function() {
      if($scope.mySelectionGridHC.length != 1){
        alert('Debe seleccionar un solo registro');
        return;
      }
      var datos = {
        cita: $scope.mySelectionGridHC[0]
      }
      historialCitasServices.sVerificaEstadoCita(datos).then(function (rpta) {
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
          $scope.seleccionarNuevaCita($scope.mySelectionGridHC[0]);
        }else if(rpta.flag == 0){
          var pTitle = 'Aviso!';
          var pType = 'warning'; 
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 }); 
        }else{
          alert('Error inesperado');
        }
      });

      $scope.seleccionarNuevaCita = function (rowPaciente){
        $scope.fDataVenta = {};
        $scope.fDataVenta.numero_documento = rowPaciente.numero_documento;

        $scope.fDataVenta.cliente={};
        $scope.fDataVenta.cliente.nombres = rowPaciente.nombres;
        $scope.fDataVenta.cliente.apellidos = rowPaciente.apellido_paterno + ' ' + rowPaciente.apellido_materno;
        $scope.fDataVenta.cliente.edad = rowPaciente.edad;
        
        $scope.fBusqueda = {};
        var ind = 0;
        angular.forEach($scope.listaEspecialidadesProgAsistencial, function(value, key) {
        if(value.id == rowPaciente.idespecialidad){
            ind = key;
          }            
        }); 
        $scope.fBusqueda.especialidad = $scope.listaEspecialidadesProgAsistencial[ind]; 

        $scope.genCupo = {};
        rowPaciente.paciente = rowPaciente.cliente;
        $scope.genCupo.oldCita = rowPaciente;

        $scope.genCupo.itemVenta = {};
        $scope.genCupo.itemVenta.producto = {};
        $scope.genCupo.itemVenta.producto.descripcion = rowPaciente.producto;

        
        var fnCallbackCambiarCita = function () {
          $scope.cambiarCita();
        }

        $scope.btnGenerarCupo(null,true, fnCallbackCambiarCita);
      }
      

      $scope.cambiarCita = function (){
        var datos = {
          oldCita: $scope.genCupo.oldCita,
          seleccion: $scope.genCupo.seleccion,
        }        
        console.log(datos); 
        progMedicoServices.sCambiarCita(datos).then(function (rpta) {
          if(rpta.flag == 1){
            pTitle = 'OK!';
            pType = 'success';
            $scope.getPaginationServerSide(true);
          }else if(rpta.flag == 0){
            var pTitle = 'Aviso!';
            var pType = 'warning'; 
             
          }else{
            alert('Error inesperado');
          }
          
          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
        });          
      }
    }
    $scope.btnSolicitudImprimirTicket = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea ENVIAR UNA SOLICITUD DE IMPRESION al Área de Sistemas?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          cajaActualServices.sEnviarSolicitudImpresion($scope.mySelectionGridHC).then(function (rpta) { 
            if(rpta.flag == 1){ 
              var pTitle = 'OK!'; 
              var pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationServerSide(true);
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            // $scope.getPaginationRIServerSide();
          }); 
        }
      });
    } 
    $scope.btnImprimirTicket = function (fila) { 
      var pMensaje = '¿Realmente desea realizar la re-impresión?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaActualServices.sImprimirTicketVenta(fila).then(function (rpta) { 
            if(rpta.flag == 1){
              var printContents = rpta.html;
              var popupWin = window.open('', 'windowName', 'width=300,height=300');
              popupWin.document.open()
              popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
              popupWin.document.close();
            }else {
              if(rpta.flag == 0) { // ALGO SALIÓ MAL
                var pTitle = 'Error';
                var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                var pType = 'danger';
              }
              if(rpta.flag == 2) { // FALTA APROBAR, ESTÁ EN ESPERA.
                var pTitle = 'Advertencia';
                var pText = 'La venta está en espera. Contacte con el Area de Sistemas, para proceder con la impresión';
                var pType = 'warning';
              }
              if(rpta.flag == 3) { // YA ESTA IMPRESO, NO SE PUEDE REIMPRIMIR
                var pTitle = 'Advertencia';
                var pText = 'Ya se imprimió el ticket. Solicite la reimpresión del ticket desde su Liquidación Actual.';
                var pType = 'warning';
              }
              if(rpta.flag == 4) { // SOLICITUD DE IMPRESION EN PROCESO, EL AREA DE SISTEMAS ESTÁ EVALUANDO LA SOLICITUD.
                var pTitle = 'Información';
                var pText = 'Solicitud de reimpresión <strong> en proceso </strong>. El Área de Sistemas está evaluando su solicitud.';
                var pType = 'info';
              }
              pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
            }
            // $scope.getPaginationRIServerSide();
          });
        }
      });
    }
    $scope.btnImprimirTicketManual = function () { 
      $modal.open({
        templateUrl: angular.patchURLCI+'venta/ver_popup_impresion_ticket_manual',
        size: 'sm',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Impresión Manual de Ticket';
          $scope.fVenta = $scope.mySelectionGridHC[0];
          $scope.fVenta.fecha_venta = '03-09-2016';
          $scope.fVenta.hora_venta = '07';
          $scope.fVenta.minuto_venta = '00';
          console.log('venta', $scope.fVenta);
          $scope.aceptar = function(){
            historialCitasServices.sImprimirTicketVentaManual($scope.fVenta).then(function (rpta) { 
              if(rpta.flag == 1){
                var printContents = rpta.html;
                var popupWin = window.open('', 'windowName', 'width=300,height=300');
                popupWin.document.open()
                popupWin.document.write('<html><head><link rel="stylesheet" type="text/css" href="assets/css/stylePrint.css" /></head><body onload="window.print()">' + printContents + '</html>');
                popupWin.document.close();
                $modalInstance.dismiss('cancel');
              }else {
                if(rpta.flag == 0) { // ALGO SALIÓ MAL
                  var pTitle = 'Error';
                  var pText = 'No se pudo realizar la impresión. Contacte con el Area de Sistemas.';
                  var pType = 'danger';
                }
                
                pinesNotifications.notify({ title: pTitle, text: pText, type: pType, delay: 3500 });
              }
              // $scope.getPaginationRIServerSide();
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      //console.log($scope.mySelectionGridHC); 
      if( $scope.mySelectionGridHC[0].estado.claseIconAtendido ){ 
        // console.log($scope.mySelectionGridHC[0].estado.claseIconAtendido);
        pinesNotifications.notify({ title: 'Advertencia.', text: 'La venta ya ha sido atendida, no se puede anular. Contacte con el área de sistemas.', type: 'warning', delay: 3500 }); 
        return false;
      }
      // return false; 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          cajaActualServices.sAnularVentaCajaActual($scope.mySelectionGridHC).then(function (rpta) { 
            if(rpta.flag == 1){
              pTitle = 'OK!';
              pType = 'success'; 
            }else if(rpta.flag == 0){
              var pTitle = 'Error!';
              var pType = 'danger';
            }else{
              alert('Algo salió mal...');
            }
            $scope.getPaginationVAServerSide();
            $scope.getPaginationServerSide(true);
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          }); 
        }
      });
    }
    $scope.btnVerDetalleVenta = function (fVenta,size) { 
      $modal.open({
        templateUrl: angular.patchURLCI+'venta/ver_popup_detalle_venta',
        size: size || 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.titleForm = 'Detalle de la Venta';
          $scope.fVenta = fVenta;
          var paginationOptionsDetalleVenta = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionDetalleVentaGrid = [];
          $scope.btnToggleFiltering = function(){
            $scope.gridOptionsDetalleVenta.enableFiltering = !$scope.gridOptionsDetalleVenta.enableFiltering;
            $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
          };
          $scope.gridOptionsDetalleVenta = {
            minRowsToShow: 6,
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: false,
            enableRowSelection: true,
            enableSelectAll: false,
            enableFullRowSelection: true,
            multiSelect: false,
            columnDefs: [ 
              { field: 'fecha_venta', name: 'fecha_venta', displayName: 'Fecha de Venta', width: '14%' },
              { field: 'especialidad', name: 'especialidad', displayName: 'Especialidad', width: '16%' },
              { field: 'tipoproducto', name: 'nombre_tp', displayName: 'Tipo de Producto', width: '14%' },
              { field: 'producto', name: 'producto', displayName: 'Producto/Servicio', width: '20%' },
              { field: 'precio_unitario', name: 'precio_unitario', displayName: 'Precio Unit.', width: '10%' },
              { field: 'cantidad', name: 'cantidad', displayName: 'Cant.', width: '6%' },
              { field: 'descuento', name: 'descuento_asignado', displayName: 'Dscto.', width: '8%', cellClass: 'bg-lightblue' },
              { field: 'total_detalle', name: 'total_detalle', displayName: 'Importe', width: '10%', cellClass: 'bg-lightblue' }
            ],
            onRegisterApi: function(gridApi) { // gridComboOptions
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionDetalleVentaGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                if (sortColumns.length == 0) {
                  paginationOptionsDetalleVenta.sort = null;
                  paginationOptionsDetalleVenta.sortName = null;
                } else {
                  paginationOptionsDetalleVenta.sort = sortColumns[0].sort.direction;
                  paginationOptionsDetalleVenta.sortName = sortColumns[0].name;
                }
                $scope.getPaginationDetalleVentaServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationOptionsDetalleVenta.pageNumber = newPage;
                paginationOptionsDetalleVenta.pageSize = pageSize;
                paginationOptionsDetalleVenta.firstRow = (paginationOptionsDetalleVenta.pageNumber - 1) * paginationOptionsDetalleVenta.pageSize;
                $scope.getPaginationDetalleVentaServerSide();
              });
            }
          };
          paginationOptionsDetalleVenta.sortName = $scope.gridOptionsDetalleVenta.columnDefs[0].name;
          $scope.getPaginationDetalleVentaServerSide = function() {
            //$scope.$parent.blockUI.start();
            $scope.datosGrid = {
              paginate: paginationOptionsDetalleVenta,
              datos: fVenta
            };
            //console.log($scope.mySelectionGridEE[0]);
            cajaActualServices.sListarDetalleVenta($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsDetalleVenta.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDetalleVenta.data = rpta.datos;
              //$scope.$parent.blockUI.stop();
            });
            $scope.mySelectionDetalleVentaGrid = [];
          };
          $scope.getPaginationDetalleVentaServerSide();

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }
    /* FIX TAB IN GRID */ 
    $scope.reloadGrid = function () { 
      $interval( function() { 
          $scope.gridApi.core.handleWindowResize();
          //$scope.gridApiAnulado.core.handleWindowResize();
          //$scope.gridApiProducto.core.handleWindowResize();
          //$scope.gridApiImpresionesVenta.core.handleWindowResize();
      }, 50, 5);
    }
    $scope.reloadGrid();
  }])
  .service("historialCitasServices",function($http, $q) {
    return({
        sListarCitasHistorial: sListarCitasHistorial,
        sImprimirTicketVentaManual: sImprimirTicketVentaManual,
        sVerificaEstadoCita: sVerificaEstadoCita,
    });

    function sListarCitasHistorial(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCitas/lista_historial_citas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }  

    function sVerificaEstadoCita(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCitas/verifica_estado_cita", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    

    function sImprimirTicketVentaManual(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"HistorialCitas/imprimir_ticket_venta_manual", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });