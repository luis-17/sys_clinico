angular.module('theme.historiasAnteriores', ['theme.core.services','ui.grid.edit'])
  .controller('historiasAnterioresController', ['$scope', '$route', '$filter', '$sce', '$interval', '$location', '$anchorScroll','$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', 
    'ModalReporteFactory',
    'historiasAnterioresServices', 'atencionMedicaAmbServices',
    
    function($scope, $route, $filter, $sce, $interval, $location, $anchorScroll, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, 
      ModalReporteFactory,
      historiasAnterioresServices, atencionMedicaAmbServices
      ){ 
      'use strict';
      $scope.gridOptionsDet = {};
      //$scope.fDataFicha = {};
      $scope.gridOptions1 = {
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        useExternalPagination: false,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: true,
        enableFiltering: false,
        enableFullRowSelection: true,
        multiSelect: false,
        minRowsToShow: 5,

        columnDefs: [
          { field: 'idcliente', name: 'idcliente', displayName: 'Cód. Paciente',  sort: { direction: uiGridConstants.ASC}, visible: false },
          { field: 'num_documento', name: 'num_documento', displayName: 'Nº Documento', maxWidth:120},
          { field: 'idhistoria', name: 'idhistoria', displayName: 'Historia Clínica'},
          { field: 'apellido_paterno', name: 'apellido_paterno', displayName: 'Apellido Paterno'},
          { field: 'apellido_materno', name: 'apellido_materno', displayName: 'Apellido Materno'},
          { field: 'nombres', name: 'nombres', displayName: 'Nombres'},
          { field: 'edadActual', name: 'edad', displayName: 'Edad Actual'}
          
        ],
        onRegisterApi: function(gridApi) {
          $scope.gridApi = gridApi;
          gridApi.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionGrid = gridApi.selection.getSelectedRows();
            console.log($scope.mySelectionGrid);
            $scope.verDetalle();
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
           // $scope.getPaginationServerSide();
          });
          gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
            paginationOptions.pageNumber = newPage;
            paginationOptions.pageSize = pageSize;
            paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
            //$scope.getPaginationServerSide();
          });
          $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
            var grid = this.grid;
            paginationOptions.search = true;
            // console.log(grid.columns);
            // console.log(grid.columns[1].filters[0].term);
            paginationOptions.searchColumn = {
              'idcliente' : grid.columns[1].filters[0].term,
              'apellido_paterno' : grid.columns[2].filters[0].term,
              'apellido_materno' : grid.columns[3].filters[0].term,
              'nombres' : grid.columns[4].filters[0].term,
            }
            //$scope.getPaginationServerSide();
          });
        }
      };
      $scope.fData = {}; 
      $scope.buscar = function(){
        $scope.gridOptionsDet.data = [];
        $scope.mySelectionGridDet = {};
        historiasAnterioresServices.sListarPacientes($scope.fData).then(function (rpta) {
          $scope.gridOptions1.data = rpta.datos;
        });
      }
      $scope.verDetalle = function(){
        $scope.mySelectionGridDet = {};
        $scope.gridOptionsDet = {
          paginationPageSizes: [10, 50, 100, 500, 1000],
          paginationPageSize: 10,
          useExternalPagination: false,
          useExternalSorting: true,
          useExternalFiltering : true,
          enableGridMenu: true,
          enableRowSelection: true,
          enableSelectAll: true,
          enableFiltering: false,
          enableFullRowSelection: true,
          multiSelect: false,
          minRowsToShow: 10,

          columnDefs: [
            { field: 'orden', name: 'orden_venta', displayName: 'Orden Venta', maxWidth: 200,  sort: { direction: uiGridConstants.ASC} },
            { field: 'fechaAtencion', name: 'fecha_atencion_det', displayName: 'Fecha de Atención',maxWidth: 200},
            { field: 'producto', name: 'descripcion', displayName: 'Producto'},
            { field: 'tipoproducto', name: 'tipoproducto', displayName: 'Tipo de Producto'},
            { field: 'especialidad', name: 'nombre', displayName: 'Especialidad'},
            { field: 'personalatencion["descripcion"]', name: 'personalatencion', displayName: 'Medico'}
            
          ],
          onRegisterApi: function(gridApi) {
            $scope.gridApi = gridApi;
            gridApi.selection.on.rowSelectionChanged($scope,function(row){
              $scope.mySelectionGridDet = gridApi.selection.getSelectedRows();
            });
            gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
              $scope.mySelectionGridDet = gridApi.selection.getSelectedRows();
            });

            // $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
            //   //console.log(sortColumns);
            //   if (sortColumns.length == 0) {
            //     paginationOptions.sort = null;
            //     paginationOptions.sortName = null;
            //   } else {
            //     paginationOptions.sort = sortColumns[0].sort.direction;
            //     paginationOptions.sortName = sortColumns[0].name;
            //   }
            //   $scope.getPaginationServerSideDet();
            // });
            // gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
            //   paginationOptions.pageNumber = newPage;
            //   paginationOptions.pageSize = pageSize;
            //   paginationOptions.firstRow = (paginationOptions.pageNumber - 1) * paginationOptions.pageSize;
            //   $scope.getPaginationServerSideDet();
            // });
            // $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
            //   var grid = this.grid;
            //   paginationOptions.search = true;
            //   // console.log(grid.columns);
            //   // console.log(grid.columns[1].filters[0].term);
            //   paginationOptions.searchColumn = {
            //     'orden_venta' : grid.columns[1].filters[0].term,
            //     'd.fecha_atencion_det' : grid.columns[2].filters[0].term,
            //     'pm.descripcion' : grid.columns[3].filters[0].term,
            //     'e.nombre' : grid.columns[4].filters[0].term,
                
                
            //   }
            //   $scope.getPaginationServerSideDet();
            // });

          }
        };
        $scope.getPaginationServerSideDet = function(){
          historiasAnterioresServices.sListarDetalleVentaPacientes($scope.mySelectionGrid).then(function (rpta) {
            $scope.gridOptionsDet.data = rpta.datos;
            //console.log(rpta.datos);
          }); 
        }
        $scope.getPaginationServerSideDet();
      }
      $scope.btnVerFichaAtencion = function (mySelectionGridDet){
        $modal.open({
          templateUrl: angular.patchURLCI+'AtencionMedica/ver_popup_ficha_atencion_ambulatoria',
          size: 'xlg',
          scope: $scope,
          //backdrop: 'static',
          //keyboard:false,
          controller: function ($scope, $modalInstance) { 
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
            $scope.fDataFicha = mySelectionGridDet[0];
            $scope.fDataFicha.cliente = $scope.mySelectionGrid[0].apellido_paterno + ' ' + $scope.mySelectionGrid[0].apellido_materno + ' ' + $scope.mySelectionGrid[0].nombres;
            $scope.fDataFicha.numero_documento = $scope.mySelectionGrid[0].num_documento;
            $scope.fDataFicha.sexo = $scope.mySelectionGrid[0].sexo;
            $scope.fDataFicha.idhistoria = $scope.mySelectionGrid[0].idhistoria;
            $scope.fDataFicha.boolSexo = $scope.mySelectionGrid[0].boolSexo;
            $scope.fDataFicha.personal = $scope.fDataFicha.personalatencion;
            console.log($scope.fDataFicha);
            $scope.titleForm = 'Ficha de Atención Médica'; 
            /* CARGAMOS LOS DIAGNOSTICOS DEL ACTO MEDICO */ 
            var arrParams = { 
              'idatencionmedica': $scope.fDataFicha.num_acto_medico
            }; 
            $scope.gridOptionsFichaDiagnostico = { 
              paginationPageSize: 10,
              enableRowSelection: false,
              enableSelectAll: false,
              enableFiltering: false,
              enableFullRowSelection: false,
              enableCellEditOnFocus: true,
              minRowsToShow: 10, 
              data: null,
              rowHeight: 30,
              multiSelect: false,
              columnDefs: [
                { field: 'codigo_diagnostico', displayName: 'Código', width: '14%' },
                { field: 'diagnostico', displayName: 'Descripción' },
                { field: 'tipo', displayName: 'Tipo', width: '18%', editableCellTemplate: 'ui-grid/dropdownEditor', cellFilter: 'mapGender', 
                  editDropdownValueLabel: 'gender', editDropdownOptionsArray: [ 
                    { id: 'DEFINITIVO', gender: 'DEFINITIVO' },
                    { id: 'PRESUNTIVO', gender: 'PRESUNTIVO' }
                  ]
                }
                // { field: 'accion', displayName: 'Acción', width: '15%', 
                //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' 
                // }
              ]
              ,onRegisterApi: function(gridApiFicha) { 
                $scope.gridApiFicha = gridApiFicha; 
              }
            };
            historiasAnterioresServices.sListarDiagnosticosDeAtencion($scope.fDataFicha.diagnosticos).then(function (rpta) { 
              $scope.gridOptionsFichaDiagnostico.data = rpta.datos; 
            });
            /* CARGAMOS LA RECETA DEL ACTO MEDICO */
            $scope.gridOptionsRecetaMedica = { 
              paginationPageSize: 10,
              enableRowSelection: false,
              enableSelectAll: false,
              enableFiltering: false,
              enableFullRowSelection: false,
              minRowsToShow: 10, 
              data: null,
              rowHeight: 30,
              multiSelect: false,
              columnDefs: [
                { field: 'medicamento', displayName: 'Medicamento', width: '35%',  type:'object' },
                { field: 'unidad', displayName: 'Medida' },
                { field: 'cantidad', displayName: 'Cantidad' },
                { field: 'indicaciones', displayName: 'Indicaciones', width: '30%' }
                // { field: 'accion', displayName: 'Acción', enableCellEdit: false, 
                //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCestaMED(row)"> <i class="fa fa-trash"></i> </button>' 
                // }
              ]
              ,onRegisterApi: function(gridApiReceta) { 
                $scope.gridApiReceta = gridApiReceta; 
              }
            };
            atencionMedicaAmbServices.sListarRecetasDeAtencion(arrParams).then(function (rpta) { 
              $scope.gridOptionsRecetaMedica.data = rpta.datos; 
            });
            // $scope.reloadGrid = function () { // console.log('click med');
            //   $interval( function() { 
            //       $scope.gridApiReceta.core.handleWindowResize();
            //   }, 50, 5);
            // }
            $scope.getTableHeight = function() { 
               var rowHeight = 30; // your row height 
               var headerHeight = 30; // your header height 
               return { 
                  height: ($scope.gridOptionsFichaDiagnostico.data.length * rowHeight + headerHeight + 30) + "px" 
               }; 
            }; 
            $scope.getTableHeightRM = function() { 
               var rowHeight = 30; // your row height 
               var headerHeight = 30; // your header height 
               return { 
                  height: ($scope.gridOptionsRecetaMedica.data.length * rowHeight + headerHeight + 30) + "px" 
               }; 
            }; 
          }
        });
      }
      $scope.btnImprimirFichaAtencion = function (mySelectionGridDet){
        $scope.fDataFicha = mySelectionGridDet[0];
        $scope.fDataFicha.cliente = $scope.mySelectionGrid[0].apellido_paterno + ' ' + $scope.mySelectionGrid[0].apellido_materno + ' ' + $scope.mySelectionGrid[0].nombres;
        $scope.fDataFicha.numero_documento = $scope.mySelectionGrid[0].num_documento;
        $scope.fDataFicha.sexo = $scope.mySelectionGrid[0].sexo;
        $scope.fDataFicha.idhistoria = $scope.mySelectionGrid[0].idhistoria;
        $scope.fDataFicha.boolSexo = $scope.mySelectionGrid[0].boolSexo;
        $scope.fDataFicha.personal = $scope.fDataFicha.personalatencion;
        var strControllerJS = 'CentralReportes';
        var strControllerPHP = 'CentralReportesMPDF';
        $scope.fDataFicha.titulo = 'ACTO MEDICO';
        var arrParams = {
          titulo: $scope.fDataFicha.titulo,
          datos: {
            resultado: $scope.fDataFicha,
            salida: 'pdf',
            tituloAbv: 'HC-ANT',
            titulo: $scope.fDataFicha.titulo + ' N ' + $scope.fDataFicha.num_acto_medico,
          },

          metodo: 'php'
        }; 
        var strController = arrParams.metodo == 'js' ? strControllerJS : strControllerPHP; 
        arrParams.url = angular.patchURLCI+strController+'/historias_clinicas_anteriores',
        ModalReporteFactory.getPopupReporte(arrParams); 
      }
    }
  ])
  .service("historiasAnterioresServices",function($http, $q) {
    return({
        sListarPacientes,
        sListarDetalleVentaPacientes,
        sListarDiagnosticosDeAtencion

    });
    function sListarPacientes (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedicaAnterior/lista_pacientes", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDetalleVentaPacientes (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedicaAnterior/lista_detalle_venta_paciente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarDiagnosticosDeAtencion (datos) {
      var request = $http({ 
            method : "post",
            url : angular.patchURLCI+"atencionMedicaAnterior/lista_diagnosticos_de_atencion_medica", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });