angular.module('theme.empleadoSalud', ['theme.core.services'])
  .controller('empleadoSaludController', ['$scope', '$sce', '$filter', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 
    'empleadoServices', 
    'empleadoSaludServices',
    'situacionAcademicaServices',
    function($scope, $sce, $filter, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, 
      empleadoServices,
      empleadoSaludServices,
      situacionAcademicaServices
    ) { 
    // 'use strict';
    shortcut.remove("F2"); $scope.modulo = 'empleadoSalud';
    $scope.fBusqueda = {};
    $scope.listaTercero = [
      { id: 'all', descripcion: 'TODOS' },
      { id: 1, descripcion: 'PERSONAL - TERCEROS' },
      { id: 2, descripcion: 'PERSONAL - PROPIOS' }
    ];
    $scope.fBusqueda.tercero = 2;
    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null
    };
    $scope.mySelectionGrid = [];
    $scope.btnToggleFiltering = function(){
      $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.dirImagesEmpleados = $scope.dirImages + "dinamic/empleado/";
    $scope.gridOptions = {
      rowHeight: 36,
      paginationPageSizes: [10, 50, 100, 500, 1000],
      paginationPageSize: 10,
      useExternalPagination: true,
      useExternalSorting: true,
      enableGridMenu: true,
      enableRowSelection: true,
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: true,
      columnDefs: [
        { field: 'id', name: 'idmedico', displayName: 'ID', width: 80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'num_documento', name: 'numero_documento', displayName: 'N° Doc.' },
        { field: 'nombre', name: 'nombres', displayName: 'Nombre', width: 160 }, 
        { field: 'apellidos', name: 'apellido_paterno', displayName: 'Apellidos' },
        { field: 'telefono', name: 'telefono', displayName: 'Teléfono', type:'number', enableFiltering: false},
        { field: 'email', name: 'correo_electronico', displayName: 'E-mail', enableFiltering: false },
        // { field: 'rne', name: 'reg_nac_especialista', displayName: 'R.N.E.' },
        { field: 'colegiatura', name: 'colegiatura_profesional', displayName: 'N° Colegiatura' },
        { field: 'nombre_foto', name: 'nombre_foto', displayName: 'Foto', enableFiltering: false, enableSorting: false, cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpleados + COL_FIELD }}" /> ' }
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
        $scope.gridApi.core.on.filterChanged($scope, function(grid, searchColumns){
          var grid = this.grid;
          paginationOptions.search = true;
          paginationOptions.searchColumn = {
            'idmedico' : grid.columns[1].filters[0].term,
            'numero_documento' : grid.columns[2].filters[0].term,
            'nombres' : grid.columns[3].filters[0].term,
            'apellido_paterno' : grid.columns[4].filters[0].term,
            // 'reg_nac_especialista' : grid.columns[7].filters[0].term,
            'colegiatura_profesional' : grid.columns[7].filters[0].term
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      //$scope.$parent.blockUI.start();
      $scope.datosGrid = {
        paginate : paginationOptions,
        datos: $scope.fBusqueda
      };
      empleadoSaludServices.sListarPersonalSalud($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
        //$scope.$parent.blockUI.stop();
      });
      $scope.mySelectionGrid = [];
    };
    $scope.getPaginationServerSide();
    $scope.btnConsultarEspecialidad = function () { 
      $modal.open({
        templateUrl: angular.patchURLCI+'empleado/ver_popup_agregar_especialidad',
        size: 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance, arrToModal ) {
          $scope.mySelectionGrid = arrToModal.mySelectionGrid;
          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.fDataAdd = {};

          if( $scope.mySelectionGrid.length == 1 ){ 
            $scope.fDataAdd.idmedico = $scope.mySelectionGrid[0].id;
            $scope.datosGrid = { 
              datos : $scope.mySelectionGrid[0]
            };
          }else{
            alert('Seleccione una sola fila'); return false; 
          }
          /* DATA GRID ESPECIALIDADES NO ASIGNADAS */ 
          var paginationEspecialidadOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionEspecialidadesGrid = [];
          $scope.gridOptionsEspecialidades = {
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
              { field: 'id', name: 'idempresaespecialidad', displayName: 'ID', maxWidth: 80, visible: false},
              { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', maxWidth: 180 },
              { field: 'especialidad', name: 'nombre', displayName: 'ESPECIALIDAD',  sort: { direction: uiGridConstants.ASC}  } 
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApi = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionEspecialidadesGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionEspecialidadesGrid = gridApi.selection.getSelectedRows();
              });

              $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationEspecialidadOptions.sort = null;
                  paginationEspecialidadOptions.sortName = null;
                } else {
                  paginationEspecialidadOptions.sort = sortColumns[0].sort.direction;
                  paginationEspecialidadOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationEspecialidadesServerSide();
              });
              gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationEspecialidadOptions.pageNumber = newPage;
                paginationEspecialidadOptions.pageSize = pageSize;
                paginationEspecialidadOptions.firstRow = (paginationEspecialidadOptions.pageNumber - 1) * paginationEspecialidadOptions.pageSize;
                $scope.getPaginationEspecialidadesServerSide();
              });
              $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationEspecialidadOptions.search = true;
                paginationEspecialidadOptions.searchColumn = { 
                  'emes.idempresaespecialidad' : grid.columns[1].filters[0].term,
                  'em.descripcion' : grid.columns[2].filters[0].term,
                  'esp.nombre' : grid.columns[3].filters[0].term,

                }
                $scope.getPaginationEspecialidadesServerSide();
              });
            }
          };
          paginationEspecialidadOptions.sortName = $scope.gridOptionsEspecialidades.columnDefs[2].name;

          $scope.getPaginationEspecialidadesServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationEspecialidadOptions,
              datos : $scope.mySelectionGrid[0]
            };
            empleadoSaludServices.sListarEspecialidadesNoAgregadosAMedico($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsEspecialidades.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsEspecialidades.data = rpta.datos;
              
            });
            $scope.mySelectionEspecialidadesGrid = [];
          };
          $scope.getPaginationEspecialidadesServerSide();
          $scope.situacionOptions = [];
          situacionAcademicaServices.sListarSituacionAcademicaCbo().then(function (rpta){
            angular.forEach(rpta.datos, function (val,index) {
              $scope.arrTemporal = {
                'id': val.id,
                'descripcion': val.descripcion,
              }
              $scope.situacionOptions.push($scope.arrTemporal);

            });
          });
          /* DATA GRID ESPECIALIDADES ASIGNADAS */
          $scope.mySelectionEspecialidadesAddGrid = [];
          $scope.gridOptionsEspecialidadesAdd = {
            // paginationPageSizes: [10, 50, 100, 500, 1000],
            // paginationPageSize: 10,
            // useExternalPagination: true,
            useExternalSorting: false,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: false,
            enableFullRowSelection: true,
            multiSelect: true,
            columnDefs: [
              { field: 'id', name: 'idempresamedico', displayName: 'ID', maxWidth: 50, visible:false },
              { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', maxWidth: 180 },
              { field: 'especialidad', name: 'nombre', displayName: 'ESPECIALIDAD',  sort: { direction: uiGridConstants.ASC} },
              { field: 'rne', name: 'reg_nacional_esp', displayName: 'R.N.E.', width: 80, enableFiltering: false, enableCellEdit: true,
                cellClass:'ui-editCell'},
              { field: 'situacion', displayName: 'Sit. Academ.', width: 90, editableCellTemplate: 'ui-grid/dropdownEditor',
                editDropdownIdLabel: 'id', editDropdownValueLabel: 'descripcion',
                editDropdownOptionsArray: $scope.situacionOptions,
                cellFilter: 'griddropdown:this', cellClass:'ui-editCell'
              },
              { field: 'estado', type: 'object', name: 'estado', displayName: '', width: 50, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
                cellTemplate:'<div class="text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i></label></div>' 
              },
              // { field: 'accion', name:'accion', displayName: 'ACCION', maxWidth: 70, 
              // cellTemplate:'<div class="">'+
              //   '<button type="button" class="btn btn-sm btn-danger inline-block m-xs" ng-click="grid.appScope.btnQuitarDeLaCesta(row)" title="QUITAR"> <i class="fa fa-trash"></i></button>'+ 
              //   '</div>', cellClass:'text-center'
              // }
            ],
            onRegisterApi: function(gridApi) {
              $scope.gridApiAdd = gridApi;
              gridApi.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionEspecialidadesAddGrid = gridApi.selection.getSelectedRows();
              });
              gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionEspecialidadesAddGrid = gridApi.selection.getSelectedRows();
              });

              gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){
                rowEntity.column = colDef.field;
                var paramDatos = rowEntity;
                paramDatos.idmedico = $scope.fDataAdd.idmedico;
                if( rowEntity.column === 'rne' || rowEntity.column === 'situacion' ){
                  empleadoSaludServices.sAgregarSituacionRNE(paramDatos).then(function (rpta){
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                    }else if(rpta.flag == 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'warning';
                      
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                    $scope.getPaginationEspecialidadesAddServerSide();
                  });
                }
                
              });
            }
          };
          $scope.getPaginationEspecialidadesAddServerSide = function() {
            empleadoSaludServices.sListarEspecialidadesDelPersonalSalud($scope.datosGrid).then(function (rpta) { 
              $scope.gridOptionsEspecialidadesAdd.data = rpta.datos;
            });
          }
          $scope.getPaginationEspecialidadesAddServerSide();

          $scope.titleFormAdd = 'Especialidades';

          $scope.agregarEspecialidadesACesta = function () { // ESTO SE AGREGA DIRECTAMENTE A LA BD

            if( $scope.mySelectionEspecialidadesGrid.length == 0 ){ 
              console.log('no hay seleccionados');
              var pTitle = 'Advertencia!';
              var pType = 'warning';
              pinesNotifications.notify({ title: pTitle, text: 'Seleccione al menos una especialidad', type: pType, delay: 3000 });
              return false; 
            }
            console.log('agregando a la bd');
            $scope.fDataAdd.especialidades = $scope.mySelectionEspecialidadesGrid;
            empleadoSaludServices.sAgregarEspecialidad($scope.fDataAdd).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                // $modalInstance.dismiss('cancel');
                empleadoSaludServices.sAgregarSituacionRneInicialmente($scope.fDataAdd).then(function (rpta) {
                  console.log('$scope.fDataAdd',$scope.fDataAdd);
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                    
                  }
                });
              }else if(rpta.flag == 0){
                var pTitle = 'Advertencia!';
                var pType = 'warning';
                
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              $scope.getPaginationServerSide();
              $scope.getPaginationEspecialidadesServerSide();
              $scope.getPaginationEspecialidadesAddServerSide();
            });
          }
          $scope.anularEspecialidad = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                $scope.fDataAdd.especialidades = $scope.mySelectionEspecialidadesAddGrid;
                empleadoSaludServices.sAnularEspecialidadMedico($scope.fDataAdd).then(function (rpta) {
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  $scope.getPaginationServerSide();
                  $scope.getPaginationEspecialidadesServerSide();
                  $scope.getPaginationEspecialidadesAddServerSide();
                });
              }
            });
          }
          $scope.habilitarEspecialidad = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //var rowEliminar = row.entity; // empresa-especialidad-medico

                empleadoSaludServices.sHabilitarEspecialidadMedico($scope.mySelectionEspecialidadesAddGrid).then(function (rpta) {
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  $scope.getPaginationServerSide();
                  $scope.getPaginationEspecialidadesServerSide();
                  $scope.getPaginationEspecialidadesAddServerSide();
                });
              }
            });
          }
          $scope.deshabilitarEspecialidad = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //var rowEliminar = row.entity; // empresa-especialidad-medico

                empleadoSaludServices.sDesHabilitarEspecialidadMedico($scope.mySelectionEspecialidadesAddGrid).then(function (rpta) {
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Advertencia!';
                    var pType = 'warning';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
                  $scope.getPaginationServerSide();
                  $scope.getPaginationEspecialidadesServerSide();
                  $scope.getPaginationEspecialidadesAddServerSide();
                });
              }
            });
          }


          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataAdd = {};
          }
          // $scope.aceptar = function () { 
          //   $scope.fDataAdd.especialidades = $scope.mySelectionEspecialidadesGrid;
          //   empleadoSaludServices.sAgregarEspecialidad($scope.fDataAdd).then(function (rpta) { 
          //     if(rpta.flag == 1){
          //       pTitle = 'OK!';
          //       pType = 'success';
          //       $modalInstance.dismiss('cancel');
          //     }else if(rpta.flag == 0){
          //       var pTitle = 'Error!';
          //       var pType = 'danger';
                
          //     }else{
          //       alert('Error inesperado');
          //     }
          //     $scope.fDataAdd = {};
          //     pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          //     $scope.getPaginationServerSide();
          //   });
          // }
          //console.log($scope.mySelectionGrid);
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
    
  }])
  .service("empleadoSaludServices",function($http, $q) {
    return({
        sListarPersonalSalud: sListarPersonalSalud,
        sListarPersonalSaludCbo: sListarPersonalSaludCbo,
        sListarEspecialidadesNoAgregadosAMedico: sListarEspecialidadesNoAgregadosAMedico,
        sListarEspecialidadesDelPersonalSalud: sListarEspecialidadesDelPersonalSalud,
        sListarMedicoEmpresaEspecialidad: sListarMedicoEmpresaEspecialidad,
        sListarMedicoEmpresaEspecialidadAutocomplete: sListarMedicoEmpresaEspecialidadAutocomplete,
        sListarMedicoNoAgregEmpresaAutocomplete: sListarMedicoNoAgregEmpresaAutocomplete,
        sListarMedicosDeEmpresaEspecialidad: sListarMedicosDeEmpresaEspecialidad,
        sListarMedicosDeEspecialidad: sListarMedicosDeEspecialidad,
        sListarMedicosAtencionTodos: sListarMedicosAtencionTodos,
        sAgregarEspecialidad: sAgregarEspecialidad,

        sListarMedicosEspecialidadAutocomplete : sListarMedicosEspecialidadAutocomplete,
        sListarMedicosEspecialidad : sListarMedicosEspecialidad,
        sListarMedicosEspecialidadInfo : sListarMedicosEspecialidadInfo,

        sAnularEspecialidadMedico: sAnularEspecialidadMedico,
        sAgregarSituacionRNE: sAgregarSituacionRNE,
        sAgregarSituacionRneInicialmente: sAgregarSituacionRneInicialmente,
        sAgregarMedicoAEmpresaEsp: sAgregarMedicoAEmpresaEsp,
        sAnularEspecialidadMedico: sAnularEspecialidadMedico,
        sAnularMedicoAEmpresaEsp: sAnularMedicoAEmpresaEsp,
        sHabilitarEspecialidadMedico: sHabilitarEspecialidadMedico,
        sDesHabilitarEspecialidadMedico: sDesHabilitarEspecialidadMedico,
        sListarMedicosFiltroAutocomplete:sListarMedicosFiltroAutocomplete,
    });
    function sListarPersonalSalud(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_salud", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarPersonalSaludCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_salud_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesNoAgregadosAMedico (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_especialidades_no_agregados_a_medico", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesDelPersonalSalud (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_especialidades_personal_salud", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicoEmpresaEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_empresa_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicoEmpresaEspecialidadAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_empresa_especialidad_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicoNoAgregEmpresaAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medico_no_agreg_empresa_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosDeEmpresaEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_empresa_especialidad_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosDeEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosAtencionTodos (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Empleado/lista_medicos_atencion_todos_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/agregar_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarSituacionRNE (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/agregar_situacion_rne", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarSituacionRneInicialmente (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/agregar_situacion_rne_inicialmente", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarMedicoAEmpresaEsp (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/agregar_medico_a_empresa_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularEspecialidadMedico (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/anular_especialidad_medico", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sAnularMedicoAEmpresaEsp (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/anular_medico_de_empresa_especialidad",
             data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosEspecialidadAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_especialidad_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosFiltroAutocomplete (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_filtro_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }

    function sListarMedicosEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_especialidad_prog", 

            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosEspecialidadInfo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_medicos_especialidad_prog_info", 

            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sHabilitarEspecialidadMedico (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/habilitar_especialidad_medico", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));

    }
    function sDesHabilitarEspecialidadMedico (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/deshabilitar_especialidad_medico", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  });

