angular.module('theme.programacionAmbiente', ['theme.core.services'])
  .controller('programacionAmbienteController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 'blockUI', 
    'programacionAmbienteServices',
    'ambienteServices',
    'sedeServices',
    'feriadoServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, blockUI, 
      programacionAmbienteServices,
      ambienteServices,
      sedeServices,
      feriadoServices ){
    'use strict';
       

    //BOTON PROCESAR
    $scope.btnProcesar = function (){
      blockUI.start('Cargando planing...'); 
      $scope.datos={};      
      if($scope.fBusqueda.desde == null || $scope.fBusqueda.desde == ''){
        $scope.fBusqueda.desde = new Date();
      }

      if($scope.fBusqueda.hasta == null || $scope.fBusqueda.hasta == ''){
        $scope.fBusqueda.hasta = new Date();
        $scope.fBusqueda.hasta.setDate($scope.fBusqueda.hasta.getDate()+10);
      }

      $scope.datos.fecha1 = new Date($scope.fBusqueda.desde).toLocaleDateString('en-GB', {  
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                }).split('/').join('-');          
      $scope.datos.idsede = $scope.fBusqueda.sede.id;
      $scope.datos.horaInicio = $scope.fBusqueda.sede.hora_inicio;
      $scope.datos.horaFin = $scope.fBusqueda.sede.hora_final;

      if($scope.tipo_planning === 'dias'){
        $scope.datos.fecha2 = new Date($scope.fBusqueda.hasta).toLocaleDateString('en-GB', {  
            day : 'numeric',
            month : 'numeric',
            year : 'numeric'
        }).split('/').join('-');
        //console.log($scope.datos);
        programacionAmbienteServices.sListarPlanningAmbienteDias($scope.datos).then(function (rpta) {      
          //console.log(rpta.planning);         
          $scope.planning = rpta.planning; 
          $scope.ver_planning1 = true; 
          blockUI.stop();         
        });        
      }else if($scope.tipo_planning === 'horas'){
        programacionAmbienteServices.sListarPlanningAmbienteHoras($scope.datos).then(function (rpta) {      
          //console.log(rpta.planning);         
          $scope.planning = rpta.planning; 
          $scope.ver_planning2 = true;  
          blockUI.stop();       
        });
      }
    }
    
    $scope.inicio = function(){
      $scope.fBusqueda = {};
      $scope.color_clase_dia = 'primary';
      $scope.color_clase_mes = 'default text-gray';
      $scope.planning={};
      $scope.ver_planning1=true;
      $scope.mostrar_fecha2 = true;
      $scope.ver_planning2=false;
      $scope.tipo_planning='dias';

      $scope.verDia = function(){
        $scope.color_clase_dia = 'primary';
        $scope.color_clase_mes = 'default text-gray';
        $scope.mostrar_fecha2 = true;
        $scope.planning={};
        $scope.ver_planning1=false;
        $scope.ver_planning2=false;
        $scope.tipo_planning='dias';
        $scope.btnProcesar();
      }

      $scope.verMes = function(){
        $scope.color_clase_dia = 'default text-gray';
        $scope.color_clase_mes = 'primary';
        $scope.mostrar_fecha2  = false;
        $scope.planning={};
        $scope.ver_planning1=false;
        $scope.ver_planning2=false;
        $scope.tipo_planning='horas';
        $scope.btnProcesar();
      }
      
      $scope.fBusqueda.desde = new Date();
      $scope.dateUIDesde = {} ;
      $scope.dateUIDesde.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
      $scope.dateUIDesde.format = $scope.dateUIDesde.formats[0]; // formato por defecto
      $scope.dateUIDesde.datePikerOptions = {
        formatYear: 'yy',
        // startingDay: 1,
        'show-weeks': false
      };

      $scope.fBusqueda.hasta = new Date();
      $scope.fBusqueda.hasta.setDate($scope.fBusqueda.hasta.getDate()+10);
      $scope.dateUIHasta = {} ;
      $scope.dateUIHasta.formats = ['dd-MM-yyyy','dd-MMMM-yyyy','yyyy/MM/dd','dd.MM.yyyy','shortDate'];
      $scope.dateUIHasta.format = $scope.dateUIHasta.formats[0]; // formato por defecto
      $scope.dateUIHasta.datePikerOptions = {
        formatYear: 'yy',
        // startingDay: 1,
        'show-weeks': false
      };
 
      // SEDES
      sedeServices.sListarSedeCbo().then(function (rpta) {
        $scope.listaSede = rpta.datos;
        // $scope.listaSede.splice(0,0,{ id : '0', descripcion:'--Seleccione sede --'});
        $scope.fBusqueda.sede = $scope.listaSede[0];  
        $scope.btnProcesar();  
      });      
    }
    $scope.inicio();

    $scope.dateUIDesde.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIDesde.opened = true;
    };

    $scope.dateUIHasta.openDP = function($event) {
      //console.log($event);
      $event.preventDefault();
      $event.stopPropagation();
      $scope.dateUIHasta.opened = true;
    }; 

    $scope.verDetalleAmbiente = function (item){
      var clase = item.class.split(' ')[0];
      if(clase === 'cell-amb'){
        $scope.datos={};
        $scope.datos.fecha = item.fecha_formato;
        $scope.datos.idambiente = item.idambiente;
        $scope.datos.horaInicio = $scope.fBusqueda.sede.hora_inicio;
        $scope.datos.horaFin = $scope.fBusqueda.sede.hora_final;
        $scope.datos.tipo = item.tipo_evento;
        $scope.datos.ambiente = item.ambiente;
        $scope.datos.fecha = item.fecha_formato;        
        $scope.datos.categoria = item.categoria;        
        $scope.datos.subcategoria = item.subcategoria;        
        console.log(item);
        programacionAmbienteServices.sListarPlanningDetalleAmbienteDias($scope.datos).then(function (rpta) {          
          $scope.planning_detalle = rpta.planning_detalle; 
          $scope.anterior =[];

          //alert("ver modal");
          $uibModal.open({
            templateUrl: angular.patchURLCI+'programacionAmbiente/ver_popup_detalle_dias',
            size: 'lg',
            backdrop: 'static',
            scope: $scope,
            controller: function ($scope, $modalInstance) {
              $scope.titleForm = 'Detalle de Programación de Ambiente';     
              $scope.detCancel = function () {
                $modalInstance.dismiss('cancel');
              }

              $scope.verDetalleItemAmbiente = function (cell){
                //console.log(cell);
                //console.log( $scope.anterior);
                var clase;
                if( $scope.anterior.length != 0 ){
                   clase = $scope.anterior.clase; 
                   $scope.anterior.clase = clase.replace("selected", "");
                }
                $scope.anterior = cell;
                cell.clase = cell.clase + ' selected';
                $scope.datos.responsable = cell.responsable;
                $scope.datos.comentario = cell.comentario;
              }
            }
          });       

        });        
      }      
    }


    $scope.getFeriados = function(){
      var fecha = new Date();
      $scope.anyo = fecha.getFullYear();
      $scope.datosGrid = {
        anyo : $scope.anyo 
      };
      feriadoServices.sListarferiadosCbo($scope.datosGrid).then(function (rpta) {
        $scope.listaFeriados = rpta.datos;
        //console.log($scope.listaFeriados);
      });
    }
    $scope.getFeriados();

    // BOTON NUEVO
    $scope.btnNuevo = function () {
      // blockUI.start('Cargando Formulario');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'programacionAmbiente/ver_popup_formulario',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fDataAdd = {} ;
          $scope.listaHoras1 = [];
          $scope.fDataAdd.activeDate = null;
          $scope.fDataAdd.arrFechas = [];
          $scope.boolAmbiente = false;
          $scope.boolEditar = false;
          $scope.accion = 'reg';
          $scope.titleForm = 'Programación de Ambiente Físico';

          $scope.customCalls = 1;
          $scope.llamadasClase = 1;
          
          $scope.disabledNuevo = function(date, mode) {
            var holidays = $scope.listaFeriados;
            var isHoliday = false;
            var fecha = new Date(date).toLocaleDateString('zh-Hans-CN', {  
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                });            
            for(var i = 0; i < holidays.length ; i++) {              
              var feriado = (holidays[i].split('-0').join('/')).split('-').join('/');
              //console.log(feriado);
              if(feriado === fecha){
                isHoliday = true;
              }
            }
            return (mode === 'day' && (date.getDay() === 0 || isHoliday || moment(fecha).isBefore( moment().toDate().toLocaleDateString('zh-Hans-CN', { 
                                              day : 'numeric',
                                              month : 'numeric',
                                              year : 'numeric'
                                          }))
                                      )
            );
          };

          /*var tomorrow = new Date();
          tomorrow.setDate(tomorrow.getDate() + 1);
          $scope.events = [{
            date: tomorrow,
            status: 'feriado'
          }];

          $scope.getDayClass = function(date, mode) {
            $scope.customCalls++;
            if (mode === 'day') {
              var holidays = $scope.listaFeriados;
              var dayToCheck = new Date(date).setHours(0,0,0,0);
              for(var i = 0; i < holidays.length ; i++) {
                if(areDatesEqual(holidays[i], date)){
                  $scope.llamadasClase++;
                }
              }
              // for (var i = 0; i < holidays.length; i++) {
              //   var currentDay = holidays[i].setHours(0,0,0,0);

              //   if (dayToCheck === currentDay) {
              //      // console.log('feriado');

              //     return 'feriado';
              //   }else{
              //     return '';
              //   }
              // }
            }
            return '';
          }*/

          $scope.fDataAdd.horas1 = [];
          // $scope.fDataAdd.horas2 = [];
          $scope.textoSeleccion = 'Seleccionar';
          // AMBIENTES
          $scope.getCargaAmbiente = function() {
            ambienteServices.sListarAmbienteCbo($scope.fBusqueda.sede).then(function (rpta) {
              $scope.listaAmbiente = rpta.datos;
              $scope.listaAmbiente.splice(0,0,{ id : '0', descripcion:'-- Seleccione ambiente --'});
              $scope.fDataAdd.ambiente = $scope.listaAmbiente[0];    
            });
          }
          $scope.getCargaAmbiente();
          // CARGAR HORARIO DE LA SEDE
          $scope.getCargaHorario = function(){
            var paramDatos = {
              'idsede' : $scope.fBusqueda.sede.id,
            }
            sedeServices.sListarHorarioSede(paramDatos).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.listaHoras1 = rpta.datos;
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              }else{
                alert('Error inesperado');
              }
            });
          }
          $scope.getCargaHorario();

          // $scope.fDataAdd.horas1 = $scope.listaHoras1[1];
          $scope.cambiarAmbiente = function(){
            if( $scope.fDataAdd.ambiente.id == '0' ){
              console.log('No ha seleccionado Ambiente');
              $scope.boolAmbiente = false;
            }else{
              console.log('Ambiente seleccionado');
              $scope.boolAmbiente = true;
            }
          }
          $scope.seleccionarHoras = function (){
            $scope.fDataAdd.horas1 = [];
            // $scope.fDataAdd.horas2 = [];
            if($scope.boolTodos){
              angular.forEach($scope.listaHoras1, function(val){
                $scope.fDataAdd.horas1.push( val );
              });
              // angular.forEach($scope.listaHoras2, function(val2){
              //   $scope.fDataAdd.horas2.push( val2 );
              // });
              $scope.textoSeleccion = 'Deseleccionar';
            }else{
              $scope.fDataAdd.horas1 = [];
              // $scope.fDataAdd.horas2 = [];
              $scope.textoSeleccion = 'Seleccionar';
            }
            // $scope.fDataAdd.horas1.push($scope.listaHoras1[1]);
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }

          $scope.aceptar = function (){
            // $scope.fDataAdd.horasSeleccionadas = $scope.fDataAdd.horas1 + $scope.fDataAdd.horas2;
            // $scope.fDataAdd.horasSeleccionadas = angular.copy($scope.fDataAdd.horas1);
            // angular.forEach($scope.fDataAdd.horas2, function(val){
            //   $scope.fDataAdd.horasSeleccionadas.push( val );
            // });
            //angular.extend($scope.fDataAdd.horasSeleccionadas, $scope.fDataAdd.horas2);

            programacionAmbienteServices.sRegistrar($scope.fDataAdd).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.btnProcesar();

              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }

        }, 
        resolve: {
        }
      });
    }
    // BOTON EDITAR
    $scope.btnEditar = function () {
      // blockUI.start('Cargando Formulario');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'programacionAmbiente/ver_popup_formulario',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.fDataAdd = {} ;
          $scope.listaHoras1 = [];
          $scope.fDataAdd.activeDateEdit = null;
          $scope.fDataAdd.arrFechas = [];
          $scope.boolAmbiente = false;
          $scope.boolEditar = true;
          $scope.accion = 'edit';
          $scope.titleForm = 'Programación de Ambiente Físico';

          // CONFIGURACION DEL DATEPICKER
          $scope.disabled = function(date, mode) {
            var holidays = $scope.listaFeriados;
            var isHoliday = false;
            var fecha = new Date(date).toLocaleDateString('zh-Hans-CN', {  
                    day : 'numeric',
                    month : 'numeric',
                    year : 'numeric'
                });            
            for(var i = 0; i < holidays.length ; i++) {              
              var feriado = (holidays[i].split('-0').join('/')).split('-').join('/');
              //console.log(feriado);
              if(feriado === fecha){
                isHoliday = true;
              }
            }
            return (mode === 'day' && (date.getDay() === 0 || isHoliday));
          };


          $scope.fDataAdd.horas1 = [];
          // $scope.fDataAdd.horas2 = [];
          $scope.textoSeleccion = 'Seleccionar';
          // AMBIENTES
          $scope.getCargaAmbiente = function() {
            ambienteServices.sListarAmbienteCbo($scope.fBusqueda.sede).then(function (rpta) {
              $scope.listaAmbiente = rpta.datos;
              $scope.listaAmbiente.splice(0,0,{ id : '0', descripcion:'-- Seleccione ambiente --'});
              $scope.fDataAdd.ambiente = $scope.listaAmbiente[0];    
            });
          }
          $scope.getCargaAmbiente();
          // CARGAR HORARIO DE LA SEDE
          $scope.getCargaHorario = function(){
            var paramDatos = {
              'idsede' : $scope.fBusqueda.sede.id,
            }
            sedeServices.sListarHorarioSede(paramDatos).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.listaHoras1 = rpta.datos;
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              }else{
                alert('Error inesperado');
              }
            });
          }
          $scope.getCargaHorario();

          // $scope.fDataAdd.horas1 = $scope.listaHoras1[1];
          $scope.cambiarAmbiente = function(){
            $scope.fDataAdd.horas1 = [];
            $scope.fDataAdd.activeDateEdit = null;
            $scope.fDataAdd.arrFechas = [];
            $scope.gridOptionsDetalleHoras.data = [];
            if( $scope.fDataAdd.ambiente.id == '0' ){
              console.log('No ha seleccionado Ambiente');
              $scope.boolAmbiente = false;
            }else{
              console.log('Ambiente seleccionado ', $scope.fDataAdd.ambiente.id );
              $scope.boolAmbiente = true;
            }
          }
          $scope.seleccionDia = function(){
            console.log('sel ', $scope.fDataAdd.activeDateEdit);
            $scope.gridOptionsDetalleHoras.data = [];
            if( !$scope.boolAmbiente ){
              $scope.fDataAdd.activeDateEdit = null;
            }else{
              $scope.getPaginationServerSide();
            }
          }
          $scope.seleccionarHoras = function (){
            $scope.fDataAdd.horas1 = [];
            // $scope.fDataAdd.horas2 = [];
            if($scope.boolTodos){
              angular.forEach($scope.listaHoras1, function(val){
                $scope.fDataAdd.horas1.push( val );
              });
              // angular.forEach($scope.listaHoras2, function(val2){
              //   $scope.fDataAdd.horas2.push( val2 );
              // });
              $scope.textoSeleccion = 'Deseleccionar';
            }else{
              $scope.fDataAdd.horas1 = [];
              // $scope.fDataAdd.horas2 = [];
              $scope.textoSeleccion = 'Seleccionar';
            }
            // $scope.fDataAdd.horas1.push($scope.listaHoras1[1]);
          }
          //  GRILLA DE DETALLE DE HORAS
          var paginationOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 12,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionGrid = [];
          $scope.gridOptionsDetalleHoras = {
            rowHeight: 26,
            paginationPageSizes: [12, 50, 100, 500, 1000],
            paginationPageSize: 12,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: false,
            enableSelectAll: false,
            enableFiltering: false,
            enableFullRowSelection: false,
            multiSelect: false,
            columnDefs: [
              { field: 'idambiente', name: 'idambiente', displayName: 'ID', width: 80, visible: false, enableSorting: false,enableCellEdit: false},
              { field: 'ambiente', name: 'ambiente', displayName: 'AMBIENTE', visible: false, enableSorting: false,enableCellEdit: false },
              { field: 'fecha_evento', name: 'fecha_evento', displayName: 'FECHA', width: 90, visible: false, enableSorting: false,enableCellEdit: false }, 
              { field: 'hora_evento', name: 'hora_evento', displayName: 'HORA', width: 90,enableCellEdit: false,  sort: { direction: uiGridConstants.ASC}  },
              { field: 'comentario', name: 'comentario', displayName: 'COMENTARIO',cellClass:'ui-editCell' },
              { field: 'accion', name:'accion', displayName: 'ACCION', maxWidth: 70, enableSorting: false,enableCellEdit: false, 
              cellTemplate:'<div class="">'+
                '<button type="button" class="btn btn-sm btn-danger inline-block m-xs" ng-click="grid.appScope.btnQuitarDeLaCesta(row)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
                '</div>', cellClass:'text-center'
              }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              // gridApi.selection.on.rowSelectionChanged($scope,function(row){
              //   $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
              // });
              // gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
              //   $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
              // });
              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                rowEntity.column = colDef.field;
                rowEntity.newvalue = newValue;
                //console.log(rowEntity);
                if(rowEntity.column == 'comentario'){
                  programacionAmbienteServices.sEditar(rowEntity).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success'; 
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    $scope.getPaginationServerSide();
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  });  
                }

                
                $scope.$apply();
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
            }
          };
          paginationOptions.sortName = $scope.gridOptionsDetalleHoras.columnDefs[3].name;
          $scope.getPaginationServerSide = function() {
            //$scope.$parent.blockUI.start();
            $scope.datosGrid = {
              paginate : paginationOptions,
              datos: $scope.fDataAdd
            };

            programacionAmbienteServices.sListarHorasDiaAmbiente($scope.datosGrid).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                //$scope.fDataAdd.comentario = rpta.datos[0].comentario;
                $scope.gridOptionsDetalleHoras.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsDetalleHoras.data = rpta.datos;
              }else if(rpta.flag == 0){
                $scope.fDataAdd.horas1 = [];
                var pTitle = 'Aviso!';
                var pType = 'warning';
                console.log(rpta.message);
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              }else{
                alert('Error inesperado');
              }
              // pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });

            $scope.mySelectionGrid = [];
          };

          $scope.getTableHeight = function() {
             var rowHeight = 26; // your row height 
             var headerHeight = 25; // your header height 
             return {
                // height: ($scope.gridOptions.data.length * rowHeight + headerHeight + 40) + "px"
                height: (6 * rowHeight + headerHeight + 10) + "px"
             };
          };
          $scope.agregarHorasACesta = function (){
            programacionAmbienteServices.sAgregarHorasDiaAmbiente($scope.fDataAdd).then(function (rpta) {
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                //$modalInstance.dismiss('cancel');
                $scope.btnProcesar();
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
          $scope.btnQuitarDeLaCesta = function (row) {
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //$scope.fData.idparametro = row.entity.id;
                console.log(row.entity);
                
                programacionAmbienteServices.sAnularHoraDiaAmbiente(row.entity).then(function (rpta) {
                  if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.getPaginationServerSide();
                      var index = $scope.gridOptionsDetalleHoras.data.indexOf(row.entity); 
                      console.log(index);
                      $scope.gridOptionsDetalleHoras.data.splice(index,1);

                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado. Contacte con el Area de Sistemas');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                });
              }
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.btnProcesar();
          }
          $scope.aceptar = function (){
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                programacionAmbienteServices.sAgregarHorasDiaAmbiente($scope.fDataAdd).then(function (rpta) {
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
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                });
              }
            });
          }
        }, 
        resolve: {
        }
      });
    }
  }])
  .service("programacionAmbienteServices",function($http, $q) {
    return({
        sListarHorasDiaAmbiente: sListarHorasDiaAmbiente,
        sRegistrar: sRegistrar,
        sAgregarHorasDiaAmbiente: sAgregarHorasDiaAmbiente,
        sEditar: sEditar,
        sAnularHoraDiaAmbiente: sAnularHoraDiaAmbiente,
        sListarPlanningAmbienteDias: sListarPlanningAmbienteDias,
        sListarPlanningDetalleAmbienteDias: sListarPlanningDetalleAmbienteDias,
        sListarPlanningAmbienteHoras: sListarPlanningAmbienteHoras,
        sVerificarDisponibilidadAmbiente: sVerificarDisponibilidadAmbiente
    });
    function sListarHorasDiaAmbiente (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/lista_horas_dia_ambiente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarHorasDiaAmbiente (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/agregar_horas_dia_ambiente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) { // solo edita comentario
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularHoraDiaAmbiente (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPlanningAmbienteDias (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/listar_plannig_dias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarPlanningDetalleAmbienteDias (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/listar_plannig_detalle_dias", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sListarPlanningAmbienteHoras (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/listar_plannig_horas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sVerificarDisponibilidadAmbiente (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ProgramacionAmbiente/verificar_disponibilidad_ambiente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    
  });