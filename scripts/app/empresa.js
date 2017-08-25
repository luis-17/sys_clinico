angular.module('theme.empresa', ['theme.core.services'])
  .controller('empresaController', ['$scope', '$sce', '$uibModal', '$modal', '$controller', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'hotkeys', '$routeParams', 'blockUI',
      'empresaServices', 
      'sedeServices',
      'bancoServices',
      'situacionAcademicaServices',
      'empleadoSaludServices',
      'empleadoServices',
      'empresaHistorialContratoServices',
      'categoriaPersonalSaludServices',
    function($scope, $sce, $uibModal, $modal, $controller, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, hotkeys, $routeParams, blockUI,
      empresaServices,
      sedeServices,
      bancoServices,
      situacionAcademicaServices,
      empleadoSaludServices,
      empleadoServices,
      empresaHistorialContratoServices,
      categoriaPersonalSaludServices ){
    'use strict';
    $scope.metodosEmp = {
      listaBancos: []
    };

    if( $routeParams.modulo == 'contabilidad' ){
      $scope.desdeModulo = 'ct';
    }
    // if($scope.desdeModulo === 'ct'){
        
    // }
    // console.log($routeParams.modulo,'$routeParams.modulo');
    /* LISTADO DE BANCOS */ 
    bancoServices.sListarBancosCbo().then(function (rpta) { 
      $scope.metodosEmp.listaBancos = rpta.datos;
      $scope.metodosEmp.listaBancos.splice(0,0,{ id : '', descripcion:'--Seleccione Banco--'});
      // $scope.fData.idsede = $scope.listaBancos[0].id;
    });
    $scope.initEmpresa = function () {
      shortcut.remove("F2");
      $scope.modulo = 'empresa';
      

      //LISTA EMPRESAS TAB 1
      var paginationOptionsTab1 = {
        pageNumber: 1,
        firstRow: 0,
        pageSize: 10,
        sort: uiGridConstants.DESC,
        sortName: null,
        search: null
      };    

      $scope.estadoOptions = [
        { id: 1, estado: 'HABILITADO' },
        { id: 2, estado: 'DESHABILITADO' }
      ];

      $scope.tipoEmpresa = [
        { id: 0, tipoEmpresa: '-- Ver Todas las empresa --' },
        { id: 1, tipoEmpresa: 'Empresas Administradoras' },
        { id: 2, tipoEmpresa: 'Empresas Asociadas' }
      ];
      $scope.filterTipoEmpresa = $scope.tipoEmpresa[0];

      $scope.gridOptionsTab1 = { 
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        minRowsToShow: 10,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: true,
        enableFiltering: false,
        enableFullRowSelection: true,
       //rowHeight: 100,
        multiSelect: false,
        columnDefs: [
          { field: 'idempresa', name: 'idempresa', displayName: 'ID', width: 60,  enableCellEdit: false,sort: { direction: uiGridConstants.DESC} },
          { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', enableCellEdit: false,visible: true},
          { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC', width: 90, enableCellEdit: false,visible: true },
          { field: 'domicilio_fiscal', name: 'domicilio_fiscal', displayName: 'DOMICILIO FISCAL', width: 250, enableFiltering: false,enableCellEdit: false,visible: false },
          { field: 'representante_legal', name: 'representante_legal', displayName: 'REPRESENTANTE', width: 120,enableFiltering: false,enableCellEdit: false, visible: false },
          { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: 100, enableCellEdit: false,visible: true },
          { field: 'banco["descripcion"]', name: 'banco', displayName: 'BANCO', width: 130, enableCellEdit: false,visible: true },
          { field: 'cuenta', name: 'num_cuenta', displayName: 'Nº CUENTA', width: 150, enableCellEdit: false,visible: true },
          { field: 'cuenta_detraccion', name: 'cuenta_detraccion', displayName: 'CTA. DETRACCION', width: 150, enableCellEdit: false,visible: true },
          { field: 'tipo_empresa', name: 'tipo_empresa', displayName: 'EMPRESA ADMINISTRADORA', width: 150, enableCellEdit: false, enableFiltering: false,
            enableSorting: false, visible: true,
            cellTemplate:'<div style="text-align: center;"><label class="label  {{ COL_FIELD.clase }} "><i class="{{ COL_FIELD.icono }}"></i></label></div>'
          },
          { field: 'estado', type: 'object', name: 'estado', displayName: 'Estado', enableFiltering: false, enableSorting: false , width: 130,                
            cellFilter: 'mapEstado', enableCellEdit: true,cellClass:'ui-editCell', editableCellTemplate: 'ui-grid/dropdownEditor',
            editDropdownValueLabel: 'estado', editDropdownOptionsArray: $scope.estadoOptions,
            cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 5px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
          },        
        ],
        onRegisterApi: function(gridApi) {
          $scope.gridApiTab1 = gridApi;
          gridApi.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionGridTab1 = gridApi.selection.getSelectedRows();
          });
          gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionGridTab1 = gridApi.selection.getSelectedRows();
          });

          $scope.gridApiTab1.core.on.sortChanged($scope, function(grid, sortColumns) {
            //console.log(sortColumns);
            if (sortColumns.length == 0) {
              paginationOptionsTab1.sort = null;
              paginationOptionsTab1.sortName = null;
            } else {
              paginationOptionsTab1.sort = sortColumns[0].sort.direction;
              paginationOptionsTab1.sortName = sortColumns[0].name;
            }
            $scope.getPaginationTab1ServerSide();
          });
          gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
            paginationOptionsTab1.pageNumber = newPage;
            paginationOptionsTab1.pageSize = pageSize;
            paginationOptionsTab1.firstRow = (paginationOptionsTab1.pageNumber - 1) * paginationOptionsTab1.pageSize;
            $scope.getPaginationTab1ServerSide();
          });
          $scope.gridApiTab1.core.on.filterChanged( $scope, function(grid, searchColumns) {
            var grid = this.grid;
            paginationOptionsTab1.search = true;
            // console.log(grid.columns);
            // console.log(grid.columns[1].filters[0].term);
            paginationOptionsTab1.searchColumn = {
              'e.idempresa' : grid.columns[1].filters[0].term,
              'e.descripcion' : grid.columns[2].filters[0].term,
              'e.ruc_empresa' : grid.columns[3].filters[0].term,
              'e.telefono' : grid.columns[6].filters[0].term,
              'ba.descripcion_banco' : grid.columns[7].filters[0].term,
              'e.num_cuenta' : grid.columns[8].filters[0].term,
              'e.num_cuenta_detraccion' : grid.columns[9].filters[0].term,
            }
            $scope.getPaginationTab1ServerSide();
          });

          gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){                
            //console.log(rowEntity);           
            var datos = {
              idempresa:rowEntity.idempresa,
              nuevo_estado:newValue
            };

            empresaServices.sCambiarEstadoEmpresa(datos).then(function (rpta) {
              if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';                
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';                
              }else{
                alert('Error inesperado');
              }
              $scope.getPaginationTab1ServerSide();
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });

            $scope.$apply();
          });
        }
      };
      paginationOptionsTab1.sortName = $scope.gridOptionsTab1.columnDefs[0].name;   
      $scope.mySelectionGridTab1 = [];
      $scope.btnToggleFilteringTab1 = function(){
        $scope.gridOptionsTab1.enableFiltering = !$scope.gridOptionsTab1.enableFiltering;
        $scope.gridApiTab1.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
      };

      $scope.getPaginationTab1ServerSide = function() {
        $scope.datosGrid = {
          paginate : paginationOptionsTab1, 
          tipoEmpresa: $scope.filterTipoEmpresa
        };
        empresaServices.sListarEmpresas($scope.datosGrid).then(function (rpta) {
          $scope.gridOptionsTab1.totalItems = rpta.paginate.totalRows;
          $scope.gridOptionsTab1.data = rpta.datos;
        });
        $scope.mySelectionGridTab1 = [];
      };
      $scope.getPaginationTab1ServerSide();

      $scope.btnAnular = function (mensaje) { 
        var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
        $bootbox.confirm(pMensaje, function(result) {
          if(result){
            $scope.mySelectionGridTab1[0].nuevo_estado = 0;
            empresaServices.sCambiarEstadoEmpresa($scope.mySelectionGridTab1[0]).then(function (rpta) {
              if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationTab1ServerSide();
                }else if(rpta.flag == 0){
                  var pTitle = 'Aviso!';
                  var pType = 'warning';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }
        });
      }

      //LISTA EMPRESAS TAB 2

        var paginationOptions = {
          pageNumber: 1,
          firstRow: 0,
          pageSize: 10,
          sort: uiGridConstants.DESC,
          sortName: null,
          search: null
        };

        // $scope.navegateToCell = function( rowIndex, colIndex ) {
        //   $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
        // };
        $scope.gridOptions = { 
          paginationPageSizes: [10, 50, 100, 500, 1000],
          paginationPageSize: 10,
          minRowsToShow: 10,
          useExternalPagination: true,
          useExternalSorting: true,
          useExternalFiltering : true,
          enableGridMenu: true,
          enableRowSelection: true,
          enableSelectAll: true,
          enableFiltering: false,
          enableFullRowSelection: true,
         //rowHeight: 100,
          multiSelect: false,
          columnDefs: [
            { field: 'idempresa', name: 'idempresa', displayName: 'ID', width: 60, enableCellEdit: false, sort: { direction: uiGridConstants.DESC} },
            { field: 'idempresadetalle', name: 'idempresadetalle', displayName: 'idempresadetalle', width: 60, enableCellEdit: false, enableFiltering: false, visible: false },
            { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', enableCellEdit: false,},
            { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC', width: 90, enableCellEdit: false,visible: true },
            { field: 'domicilio_fiscal', name: 'domicilio_fiscal', displayName: 'DOMICILIO FISCAL', width: 250,enableCellEdit: false, enableFiltering: false, visible: false },
            { field: 'representante_legal', name: 'representante_legal', displayName: 'REPRESENTANTE', width: 120,enableCellEdit: false, enableFiltering: false, visible: false },
            { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: 100, enableCellEdit: false, visible: true },
            { field: 'banco["descripcion"]', name: 'banco', displayName: 'BANCO', width: 130, enableCellEdit: false, visible: true },
            { field: 'cuenta', name: 'num_cuenta', displayName: 'Nº CUENTA', width: 150,enableCellEdit: false,  visible: true },
            { field: 'cuenta_detraccion', name: 'cuenta_detraccion', displayName: 'CTA. DETRACCION', width: 150, visible: true },
            // { field: 'estado_ed', type: 'object', name: 'estado_ed', displayName: 'Estado', enableFiltering: false, enableSorting: false , width: 130,                
            //   cellFilter: 'mapEstado', enableCellEdit: true,cellClass:'ui-editCell', editableCellTemplate: 'ui-grid/dropdownEditor',
            //   editDropdownValueLabel: 'estado', editDropdownOptionsArray: $scope.estadoOptions,
            //   cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 5px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
            // },
            // { field: 'accion', displayName: 'Acción', maxWidth: 70, enableFiltering: false, enableSorting: false, enableCellEdit: false,
            //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnAnularItem(row)"> <i class="fa fa-trash"></i> </button>' 
            // }
            { field: 'estado_ema', type: 'object', name: 'estado', displayName: '', width: 50, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
              cellTemplate:'<div class="text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i></label></div>' 
            },
          ],
          onRegisterApi: function(gridApi) {
            $scope.gridApi = gridApi;
            gridApi.selection.on.rowSelectionChanged($scope,function(row){
              $scope.mySelectionGridTab2 = gridApi.selection.getSelectedRows();
            });
            gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
              $scope.mySelectionGridTab2 = gridApi.selection.getSelectedRows();
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
                //console.log(grid.columns);
                // console.log(grid.columns[1].filters[0].term);
                paginationOptions.searchColumn = {
                  'e.idempresa' : grid.columns[1].filters[0].term,
                  'e.descripcion' : grid.columns[3].filters[0].term,
                  'e.ruc_empresa' : grid.columns[4].filters[0].term,
                  'e.telefono' : grid.columns[7].filters[0].term,
                  'ba.descripcion_banco' : grid.columns[8].filters[0].term,
                  'e.num_cuenta' : grid.columns[9].filters[0].term,
                  'e.num_cuenta_detraccion' : grid.columns[10].filters[0].term,
                }
                $scope.getPaginationServerSide();
              });
            /*
            gridApi.edit.on.afterCellEdit($scope,function(rowEntity, colDef, newValue, oldValue){                
              //console.log(newValue); 
              var datos = {
                idempresadetalle:rowEntity.idempresadetalle,
                nuevo_estado:newValue
              };
              //console.log(datos);
              empresaServices.sCambiarEstadoEmpresaDet(datos).then(function (rpta) {
                if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';                
                  }else if(rpta.flag == 0){
                    var pTitle = 'Aviso!';
                    var pType = 'warning';                
                  }else{
                    alert('Error inesperado');
                  }
                  $scope.getPaginationServerSide();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });

              $scope.$apply();
            });
            */
          }
        };
        $scope.btnCambiarEstadoEmpresaDet = function(estado){
          var pMensaje = '¿Realmente desea realizar la acción?';
          $bootbox.confirm(pMensaje, function(result) {
            if(result){
              var datos = {
                idempresadetalle:$scope.mySelectionGridTab2[0].idempresadetalle,
                nuevo_estado:estado
              };
              empresaServices.sCambiarEstadoEmpresaDet(datos).then(function (rpta) {
                if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';                
                  }else if(rpta.flag == 0){
                    var pTitle = 'Aviso!';
                    var pType = 'warning';                
                  }else{
                    alert('Error inesperado');
                  }
                  $scope.getPaginationServerSide();
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
            }
          });
        }
        $scope.accion22 = 'edit';

      //grid tab2 dr medico
      $scope.gridOptionsDrMed = { 
        paginationPageSizes: [10, 50, 100, 500, 1000],
        paginationPageSize: 10,
        minRowsToShow: 10,
        useExternalPagination: true,
        useExternalSorting: true,
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: true,
        enableFiltering: false,
        enableFullRowSelection: true,
       //rowHeight: 100,
        multiSelect: false,
        columnDefs: [
          { field: 'idempresa', name: 'idempresa', displayName: 'ID', width: 60, enableCellEdit: false, sort: { direction: uiGridConstants.DESC} },
          { field: 'idempresadetalle', name: 'idempresadetalle', displayName: 'idempresadetalle', width: 60, enableCellEdit: false, enableFiltering: false, visible: false },
          { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', enableCellEdit: false,},
          { field: 'ruc_empresa', name: 'ruc_empresa', displayName: 'RUC', width: 90, enableCellEdit: false,visible: true },
          { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: 100, enableCellEdit: false, visible: true },
          { field: 'estado_ed', type: 'object', name: 'estado_ed', displayName: 'Estado', enableFiltering: false, enableSorting: false , width: 130,                
            enableCellEdit: false,
            cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 5px auto; display: block; width: 100px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
          },
        ],
        onRegisterApi: function(gridApi) {
          $scope.gridApi = gridApi;
          gridApi.selection.on.rowSelectionChanged($scope,function(row){
            $scope.mySelectionGridTab2 = gridApi.selection.getSelectedRows();
          });
          gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
            $scope.mySelectionGridTab2 = gridApi.selection.getSelectedRows();
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
            //console.log(grid.columns);
            // console.log(grid.columns[1].filters[0].term);
            paginationOptions.searchColumn = {
              'e.idempresa' : grid.columns[1].filters[0].term,
              'e.descripcion' : grid.columns[3].filters[0].term,
              'e.ruc_empresa' : grid.columns[4].filters[0].term,
              'e.telefono' : grid.columns[5].filters[0].term,
            }
            $scope.getPaginationServerSide();
          });
        }
      };
      
      empresaServices.sListarEmpresasAdminCbo().then(function (rpta) {       
        $scope.empresaAdmin = rpta.datos[0];
        $scope.estilo_tabs = {};
        
        if($scope.empresaAdmin.key_group === 'key_sistemas' 
            || $scope.empresaAdmin.key_group === 'key_legal'  
          ){
          $scope.estilo_tabs.ver_tab1 = true;
          $scope.estilo_tabs.clasetab1 = 'active';
          $scope.estilo_tabs.clasetab2 = '';
        }else{
          $scope.estilo_tabs.ver_tab1 = false;
          $scope.estilo_tabs.clasetab1 = '';
          $scope.estilo_tabs.clasetab2 = 'active';
        }
        
        //console.log(rpta.datos);
        $scope.getPaginationServerSide = function() {
          $scope.datosGrid = {
            paginate : paginationOptions, 
            datos : {idempresaadmin: $scope.empresaAdmin.idempresa }
          };
          empresaServices.sListarEmpresas($scope.datosGrid).then(function (rpta) {

            if($scope.estilo_tabs.ver_tab1){
              $scope.gridOptions.totalItems = rpta.paginate.totalRows;
              $scope.gridOptions.data = rpta.datos;
              paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
              //console.log('ver grid 1 ',rpta);  
            }else{
              $scope.gridOptionsDrMed.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsDrMed.data = rpta.datos;
              paginationOptions.sortName = $scope.gridOptionsDrMed.columnDefs[0].name; 
              //console.log('ver grid 2 ',rpta); 
            }
            
          });
       
          $scope.mySelectionGridTab2 = [];
        };  
        $scope.getPaginationServerSide();
      });

      $scope.mySelectionGridTab2 = [];
      $scope.btnToggleFiltering = function(){
        if($scope.estilo_tabs.ver_tab1){
          $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
        }else{
          $scope.gridOptionsDrMed.enableFiltering = !$scope.gridOptions.enableFiltering;
        }
        $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
      };
    }

    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */
    

    $scope.btnNuevo = function (size,verEmpresaAdmin,ruc) { 
      console.log($scope.fDataES,'$scope.fDataES'); 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'empresa/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.accion = 'reg';
          $scope.verEmpresaAdmin = verEmpresaAdmin;

          //$scope.getPaginationServerSide = getPaginationServerSide;
          $scope.fData = {};
          $scope.fData.ruc_empresa = ruc || null;          
          if(verEmpresaAdmin){
            $scope.fData.esnueva = false;   
            $scope.titleForm = 'Asignación de empresa';         
            //EMPRESA ADMIN
            $scope.fData.empresaAdmin = $scope.empresaAdmin;                                    
          }else{
            $scope.fData.esnueva = true;            
            $scope.fData.idempresaadmin = null;
            $scope.titleForm = 'Registro de empresa';   
          }
          $scope.fData.es_empresa_admin = false;
          $scope.fData.tiene_contrato = false;
          // SEDE    
          /*sedeServices.sListarSedeCbo().then(function (rpta) {
            $scope.listaSede = rpta.datos;
            $scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
            $scope.fData.idsede = $scope.listaSede[0].id;
          });*/
          $scope.fData.banco = $scope.metodosEmp.listaBancos[0];          
          /* AUTOCOMPLETE EMPRESAS */ 
            $scope.getEmpresasAutocomplete = function(val) { 
              var params = {
                search: val,
                sensor: false
              }
              return empresaServices.sListarEmpresasCbo(params).then(function(rpta) {
                $scope.noResultsLM = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLM = true;
                }
                return rpta.datos;
              });
            };

            $scope.getSelectedEmpresa = function ($item, $model, $label) {
                $scope.fData.idempresa = $item.id;
                $scope.fData.ruc_empresa = $item.ruc_empresa;
                $scope.fData.domicilio_fiscal = $item.domicilio_fiscal;
                $scope.fData.representante_legal = $item.representante_legal;
                $scope.fData.telefono = $item.telefono;              
                $scope.fData.cuenta = $item.num_cuenta;
                $scope.fData.cuenta_detraccion = $item.num_cuenta_detraccion;
                $scope.fData.idbanco = $item.idbanco;              
                var indice = 0;
                angular.forEach($scope.metodosEmp.listaBancos,function (val,idx) { 
                  if( val.id == $scope.fData.idbanco ){
                    indice = idx;
                  }
                });
                $scope.fData.banco = $scope.metodosEmp.listaBancos[indice];
                $scope.fData.empresa = $item.descripcion;
                $scope.fData.nombre_corto = $item.nombre_corto;
            };
          // BOTONES
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
            $scope.aceptar = function () { 
              if(verEmpresaAdmin){
                $scope.fData.idempresaadmin = $scope.fData.empresaAdmin.idempresa;
                
                empresaServices.sRegistrarEmpresaDet($scope.fData).then(function (rpta) {
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    $modalInstance.dismiss('cancel');
                    $scope.getPaginationServerSide();
                    
                  }else if(rpta.flag == 0){
                    var pTitle = 'AVISO!!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                });
              }else{
                //console.log($scope.fData);
                empresaServices.sRegistrar($scope.fData).then(function (rpta) {
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                    $modalInstance.dismiss('cancel');
                    if($scope.modulo == 'empresa'){
                      console.log('by empresa');
                      $scope.getPaginationTab1ServerSide();
                    }else if( $scope.modulo == 'egresos' || $scope.modulo == 'compras' ){
                      console.log('by egresos');
                      console.log('es',$scope.fDataES);
                      var arrDatos = {
                        'id' : rpta.idempresa,
                      }
                      empresaServices.sListarEmpresaporCodigo(arrDatos).then(function (rpta) { 
                        $scope.fDataES.proveedor = rpta.datos;
                        $scope.fDataES.ruc = rpta.datos.ruc;
                        console.log('proveedor',$scope.fDataES.proveedor);
                      });
                      // setTimeout(function() {
                      //   $('#temporalProducto').focus();
                      // }, 1000);
                    }
                    
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                });
              }            
            }

          $scope.limpiarCampos =  function(){
            $scope.fData.empresa = null;
            $scope.fData.nombre_corto = null;
            $scope.fData.ruc_empresa = null;
            $scope.fData.domicilio_fiscal = null;
            $scope.fData.representante_legal = null;
            $scope.fData.telefono = null;
            $scope.fData.banco = $scope.metodosEmp.listaBancos[0];
            $scope.fData.cuenta = null;
            $scope.fData.cuenta_detraccion = null;
          }
        }
      });
    }
    $scope.btnEditar = function (size,verEmpresaAdmin) {

      $uibModal.open({
        templateUrl: angular.patchURLCI+'empresa/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
         
          $scope.accion = 'edit';
          $scope.verEmpresaAdmin = verEmpresaAdmin;
          $scope.fData = {};
          
          if($scope.modulo == 'egresos' || $scope.modulo == 'compras' || $scope.modulo == 'cajaChica'){
            $scope.fData=$scope.fDataES.proveedor;
            $scope.fData.esnueva = true;
          }else{
            if(verEmpresaAdmin){
              if( $scope.mySelectionGridTab2.length == 1 ){ 
                $scope.fData = $scope.mySelectionGridTab2[0];
              }else{
                alert('Seleccione una sola fila');
              }

              $scope.fData.empresaAdmin = $scope.empresaAdmin;
              $scope.fData.esnueva = true;
            }else{
              if( $scope.mySelectionGridTab1.length == 1 ){ 
                $scope.fData = $scope.mySelectionGridTab1[0];
              }else{
                alert('Seleccione una sola fila');
              }
              $scope.fData.esnueva = true;
            } 
          }
          
          $scope.titleForm = 'Edición de empresa';
          // SEDE    
          sedeServices.sListarSedeCbo().then(function (rpta) {
            $scope.listaSede = rpta.datos;
            $scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
          });
          
          var indice = 0;
          angular.forEach($scope.metodosEmp.listaBancos,function (val,idx) { 
            if( val.id == $scope.fData.banco.id ){
              indice = idx;
            }
          });
          $scope.fData.banco = $scope.metodosEmp.listaBancos[indice];

          $scope.cancel = function () { 
            $modalInstance.dismiss('cancel');
            if($scope.modulo == 'empresa'){
              if(verEmpresaAdmin){
                $scope.getPaginationServerSide();
              }else{
                $scope.getPaginationTab1ServerSide();
              }
            }
          }
          $scope.aceptar = function () { 
            empresaServices.sEditar($scope.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.fData = {};
                var arrDatos = {
                  'id' : rpta.idempresa,
                }
                
                if($scope.modulo == 'egresos' || $scope.modulo == 'compras' || $scope.modulo == 'cajaChica'){
                  empresaServices.sListarEmpresaporCodigo(arrDatos).then(function (rpta) { 
                    $scope.fDataES.proveedor = rpta.datos;
                    $scope.fDataES.ruc = rpta.datos.ruc;
                  });
                }
              }else if(rpta.flag == 0){
                var pTitle = 'AVISO!';
                var pType = 'danger';                
              }else{
                alert('Error inesperado');
              }              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });              
              if($scope.modulo == 'empresa'){ 
                if(verEmpresaAdmin){
                  $scope.getPaginationServerSide();
                }else{
                  $scope.getPaginationTab1ServerSide();
                }
              }
            }); 
          }

          $scope.validacionEmpresaAdmin = function(){
            empresaServices.sValidacionEmpresaAdmin($scope.fData).then(function (rpta) { 
              if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                $scope.fData.es_empresa_admin = true;
              }else if(rpta.flag != 1){
                alert('Error inesperado');
              }             
                           
            });
          }
        }
      });
    }

    $scope.btnAnularItem = function(row){
      var pMensaje = '¿Realmente desea realizar la acción?';
      //console.log(row);
      $bootbox.confirm(pMensaje, function(result) {  
        if(result){
          var datos = {
            idempresadetalle : row.entity.idempresadetalle,
            nuevo_estado: 0
          };
          empresaServices.sCambiarEstadoEmpresaDet(datos).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
          });
        }
      });
    }

    $scope.btnConsultarEspecialidad = function () {
      $modal.open({
        templateUrl: angular.patchURLCI+'empresa/ver_popup_agregar_especialidad',
        size: 'xlg',
        scope: $scope,
        controller: function ($scope, $modalInstance,arrToModal ) {
          $scope.mySelectionGridTab2 = arrToModal.mySelectionGridTab2;
          $scope.getPaginationServerSide = arrToModal.getPaginationServerSide;
          $scope.fDataAdd = {};
          $scope.fDataAddMed = {};
          $scope.titleFormAdd = 'Servicios por empresa';
          $controller('empleadoController', { 
            $scope : $scope
          });
          if( $scope.mySelectionGridTab2.length == 1 ){ 
            $scope.fDataAdd.idempresa = $scope.mySelectionGridTab2[0].idempresa;
            $scope.fDataAddMed.idempresa = $scope.mySelectionGridTab2[0].idempresa;
            $scope.fDataAdd.idempresadetalle = $scope.mySelectionGridTab2[0].idempresadetalle;
            $scope.fDataAddMed.idempresadetalle = $scope.mySelectionGridTab2[0].idempresadetalle;

            // $scope.fDataAdd.sedeId = $scope.mySelectionGridTab2[0].idsede;
          }else{
            alert('Seleccione una sola fila'); return false; 
          }

          // ******** DATA GRID: ESPECIALIDADES DE LA EMPRESA ******* //
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
            enableFiltering: false,
            enableFullRowSelection: false,
            multiSelect: false,
            enableCellEdit: false,
            columnDefs: [
              { field: 'id', name: 'esp.idespecialidad', displayName: 'ID', maxWidth: 80, visible: false },
              { field: 'nombre', name: 'nombre', displayName: 'Servicio',  sort: { direction: uiGridConstants.ASC} },
              { field: 'porcentaje', name: 'porcentaje', displayName: '%', maxWidth: 60, visible:  $scope.estilo_tabs.ver_tab1 , cellClass:'ui-editCell', enableCellEdit: true, },
              // { field: 'accion', displayName: '', maxWidth: 60,
              //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.quitarEspecialidadDeEmpresa(row)" tooltip-placement="left" tooltip="Eliminar"> <i class="fa fa-trash"></i> </button>',
              //   enableSorting: false }
              { field: 'estado', type: 'object', name: 'estado', displayName: '', width: 50, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
                cellTemplate:'<div class="text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i></label></div>' 
              },
            ],
            onRegisterApi: function(gridApiAgrEsp) {
              $scope.gridApiAgrEsp = gridApiAgrEsp;
              gridApiAgrEsp.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionEspecialidadesGrid = gridApiAgrEsp.selection.getSelectedRows();
                $scope.getPaginationMedicoServerSide();
              });
              gridApiAgrEsp.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionEspecialidadesGrid = gridApiAgrEsp.selection.getSelectedRows();
              });
              gridApiAgrEsp.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                rowEntity.column = colDef.field;
                rowEntity.newvalue = newValue;
                //console.log(rowEntity);
                if(rowEntity.column == 'porcentaje'){
                  empresaServices.sEditarPorcentaje(rowEntity).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success'; 
                    }else if(rpta.flag == 0){
                      var pTitle = 'AVISO!';
                      var pType = 'warning';
                    }else{
                      alert('Error inesperado');
                    }
                    $scope.getPaginationEspecialidadesServerSide();
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
                  });
                }
                $scope.$apply();
              });
              $scope.gridApiAgrEsp.core.on.sortChanged($scope, function(grid, sortColumns) {
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
              gridApiAgrEsp.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationEspecialidadOptions.pageNumber = newPage;
                paginationEspecialidadOptions.pageSize = pageSize;
                paginationEspecialidadOptions.firstRow = (paginationEspecialidadOptions.pageNumber - 1) * paginationEspecialidadOptions.pageSize;
                $scope.getPaginationEspecialidadesServerSide();
                $scope.getPaginationMedicoServerSide();
              });
              // $scope.gridApiAgrEsp.core.on.filterChanged( $scope, function(grid, searchColumns) {
              //   var grid = this.grid;
              //   paginationEspecialidadOptions.search = true;
              //   paginationEspecialidadOptions.searchColumn = {
              //     'esp.idespecialidad' : grid.columns[1].filters[0].term,
              //     'nombre' : grid.columns[2].filters[0].term,
              //   }
              //   $scope.getPaginationEspecialidadesServerSide();
              // });
            }
          };
          paginationEspecialidadOptions.sortName = $scope.gridOptionsEspecialidades.columnDefs[1].name;
          $scope.getPaginationEspecialidadesServerSide = function() { 
            $scope.datosGrid = {
              paginate : paginationEspecialidadOptions,
              datos : $scope.mySelectionGridTab2[0]
            };
            //console.log($scope.datosGrid);
            empresaServices.sListarEspecialidadesEmpresa($scope.datosGrid).then(function (rpta) {
              $scope.gridOptionsEspecialidades.totalItems = rpta.paginate.totalRows;
              $scope.gridOptionsEspecialidades.data = rpta.datos;
            });
            $scope.mySelectionEspecialidadesGrid = [];
            $scope.mySelectionMedicoGrid = [];
            // $scope.gridOptionsMedicos = {}; // necesario limpiar la grilla de medicos para cuando se cambia la paginacion
            // $scope.gridOptionsMedicos.data = [];
            // $scope.gridOptionsMedicos.totalItems = null;
          };
          $scope.getPaginationEspecialidadesServerSide();
            // =============================================================
            //    AUTOCOMPLETADO ESPECIALIDADES NO AGREGADAS A LA EMPRESA
            // =============================================================
          $scope.getEspecialidadNoAgregAutocomplete = function (value) {
            var params = {
              search: value,
              idempresa: $scope.fDataAdd.idempresa,
              idempresadetalle: $scope.fDataAdd.idempresadetalle,
              sensor: false
            }
            return empresaServices.sListarEspecialidadNoAgregEmpresaAutocompletado(params).then(function(rpta) { 
              $scope.noResultsLEspecialidad = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLEspecialidad = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedEspecialidad = function ($item, $model, $label) {
              $scope.fDataAdd.idespecialidad = $item.id;
          };
          $scope.limpiaId = function (){
            $scope.noResultsLEspecialidad = false;
            $scope.fDataAdd.idespecialidad = null;
          }
          // =============================================================
          $scope.agregarEspecialidad = function () {
            if( !$scope.fDataAdd.idespecialidad ){
              var pTitle = 'AVISO!';
              var pType = 'warning';
              pinesNotifications.notify({ title: pTitle, text: 'Seleccione un servicio para agregar', type: pType, delay: 3000 });
              return false;
            }
            //$scope.fDataAdd.idempresadetalle = $scope.mySelectionGridTab2[0].idempresadetalle;
            empresaServices.sAgregarEspecialidadAEmpresa($scope.fDataAdd).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              $scope.getPaginationEspecialidadesServerSide();
              $scope.getPaginationMedicoServerSide();
              $scope.boolDatos = false;
              $scope.fDataAdd.idespecialidad = null;
              $scope.fDataAdd.especialidad = null;
            });
          }
          $scope.quitarEspecialidadDeEmpresa = function () {
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //console.log(row.entity);
                empresaServices.sQuitarEspecialidadDeEmpresa($scope.mySelectionEspecialidadesGrid[0]).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Error!';
                    var pType = 'danger';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  $scope.getPaginationEspecialidadesServerSide();
                  $scope.getPaginationMedicoServerSide();
                  $scope.boolDatos = false;
                });
              }
            });
          }
          $scope.habilitarEspecialidadEnEmpresa = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                empresaServices.sHabilitarEspecialidadDeEmpresa($scope.mySelectionEspecialidadesGrid[0]).then(function (rpta) {
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
                  $scope.getPaginationEspecialidadesServerSide();
                  $scope.getPaginationMedicoServerSide();
                  $scope.boolDatos = false;
                });
              }
            });
          }
          $scope.deshabilitarEspecialidadEnEmpresa = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //var rowEliminar = row.entity; // empresa-especialidad-medico

                empresaServices.sDeshabilitarEspecialidadDeEmpresa($scope.mySelectionEspecialidadesGrid[0]).then(function (rpta) {
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
                  $scope.getPaginationEspecialidadesServerSide();
                  $scope.getPaginationMedicoServerSide();
                  $scope.boolDatos = false;
                });
              }
            });
          }
          /* CARGAR CATEGORIAS DE PERSONAL DE SALUD */
          $scope.categoriaPSOptions = [];
          categoriaPersonalSaludServices.sListarCategoriaPersonalSaludCbo().then(function (rpta){
            angular.forEach(rpta.datos, function (val,index) {
              $scope.arrTemporal = {
                'id': val.id,
                'descripcion': val.descripcion,
              }
              $scope.categoriaPSOptions.push($scope.arrTemporal);
            });
            $scope.gridOptionsMedicos.columnDefs[2].editDropdownOptionsArray = $scope.categoriaPSOptions;
          });

          // ******** DATA GRID: MEDICOS DE LA ESPECIALIDAD SELECCIONADA ******* //
          $scope.situacionOptions = [];
          $scope.boolDatos = false;
          $scope.datosGrid.idespecialidad = 0;
          situacionAcademicaServices.sListarSituacionAcademicaPorEspecialidad($scope.datosGrid).then(function (rpta){
            angular.forEach(rpta.datos, function (val,index) {
              $scope.arrTemporal = {
                'id': val.id,
                'descripcion': val.descripcion,
              }
              $scope.situacionOptions.push($scope.arrTemporal);
            });
            $scope.gridOptionsMedicos.columnDefs[4].editDropdownOptionsArray = $scope.situacionOptions;
          });
          
          var paginationMedicoOptions = {
            pageNumber: 1,
            firstRow: 0,
            pageSize: 10,
            sort: uiGridConstants.ASC,
            sortName: null,
            search: null
          };
          $scope.mySelectionMedicoGrid = [];
          $scope.gridOptionsMedicos = {
            paginationPageSizes: [10, 50, 100, 500, 1000],
            paginationPageSize: 10,
            useExternalPagination: true,
            useExternalSorting: true,
            enableGridMenu: true,
            enableRowSelection: true,
            enableSelectAll: true,
            enableFiltering: true,
            enableFullRowSelection: false,
            multiSelect: false,
            enableCellEdit: false,
            columnDefs: [
              { field: 'idmedico', name: 'm.idmedico', displayName: 'ID', maxWidth: 80, visible: false },
              { field: 'medico', name: 'medico', displayName: 'PROFESIONAL', minWidth: 150, sort: { direction: uiGridConstants.ASC} },
              { field: 'categoria_ps', name: 'descripcion_cps', displayName: 'CATEGORÍA', enableCellEdit: true, enableFiltering: false, 
                editableCellTemplate: 'ui-grid/dropdownEditor',
                editDropdownIdLabel: 'id', editDropdownValueLabel: 'descripcion',
                editDropdownOptionsArray: $scope.situacionOptions,
                cellFilter: 'griddropdown:this', cellClass:'ui-editCell'
              },
              { field: 'colegiatura', name: 'colegiatura_profesional', displayName: 'COLEGIATURA', width: 80, enableCellEdit: true,
                cellClass:'ui-editCell'},
              { field: 'rne', name: 'reg_nacional_esp', displayName: 'R.N.E.', width: 80, enableFiltering: false, enableCellEdit: true,
                cellClass:'ui-editCell'},
              { field: 'situacion', displayName: 'SIT. ACADEMICA', width: 110, enableCellEdit: true, enableFiltering: false,
                editableCellTemplate: 'ui-grid/dropdownEditor',
                editDropdownIdLabel: 'id', editDropdownValueLabel: 'descripcion',
                editDropdownOptionsArray: $scope.situacionOptions,
                cellFilter: 'griddropdown:this', cellClass:'ui-editCell'
              },
              { field: 'estado', type: 'object', name: 'estado', displayName: '', width: 50, enableFiltering: false, enableSorting: false, enableColumnMenus: false, enableColumnMenu: false, enableCellEdit: false, 
                cellTemplate:'<div class="text-center"><label tooltip-placement="left" tooltip="{{ COL_FIELD.labelText }}" style="box-shadow: 1px 1px 0 black; margin: 6px auto; min-width: 18px;" class="label {{ COL_FIELD.claseLabel }} "> <i class="fa {{ COL_FIELD.claseIcon }}"></i></label></div>' 
              },
            ],
            onRegisterApi: function(gridApiMed) {
              $scope.gridApiMed = gridApiMed;
              gridApiMed.selection.on.rowSelectionChanged($scope,function(row){
                $scope.mySelectionMedicoGrid = gridApiMed.selection.getSelectedRows();
              });
              gridApiMed.selection.on.rowSelectionChangedBatch($scope,function(rows){
                $scope.mySelectionMedicoGrid = gridApiMed.selection.getSelectedRows();
              });
              gridApiMed.edit.on.afterCellEdit($scope,function(rowEntity, colDef , newValue, oldValue){ 
                rowEntity.column = colDef.field;
                var paramDatos = rowEntity;
                // paramDatos.idmedico = $scope.fDataAdd.idmedico;
                if( rowEntity.column === 'rne' || rowEntity.column === 'situacion' || rowEntity.column === 'colegiatura' || rowEntity.column === 'categoria_ps' ){ 
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
                    $scope.getPaginationMedicoServerSide();
                  });
                }
                $scope.$apply();
              });
              $scope.gridApiMed.core.on.sortChanged($scope, function(grid, sortColumns) {
                //console.log(sortColumns);
                if (sortColumns.length == 0) {
                  paginationMedicoOptions.sort = null;
                  paginationMedicoOptions.sortName = null;
                } else {
                  paginationMedicoOptions.sort = sortColumns[0].sort.direction;
                  paginationMedicoOptions.sortName = sortColumns[0].name;
                }
                $scope.getPaginationMedicoServerSide();
              });
              gridApiMed.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                paginationMedicoOptions.pageNumber = newPage;
                paginationMedicoOptions.pageSize = pageSize;
                paginationMedicoOptions.firstRow = (paginationMedicoOptions.pageNumber - 1) * paginationMedicoOptions.pageSize;
                $scope.getPaginationMedicoServerSide();
              });
              $scope.gridApiMed.core.on.filterChanged( $scope, function(grid, searchColumns) {
                var grid = this.grid;
                paginationMedicoOptions.search = true;
                paginationMedicoOptions.searchColumn = {
                  'm.idmedico' : grid.columns[1].filters[0].term,
                  "concat_ws(' ', m.med_nombres,m.med_apellido_paterno,m.med_apellido_materno)" : grid.columns[2].filters[0].term,
                  'colegiatura_profesional_emp' : grid.columns[4].filters[0].term,
                }
                $scope.getPaginationMedicoServerSide();
              });
            }
          };
          paginationMedicoOptions.sortName = $scope.gridOptionsMedicos.columnDefs[1].name;
          $scope.getPaginationMedicoServerSide = function() {
            $scope.gridOptionsMedicos.data = [];
            $scope.mySelectionMedicoGrid = [];
            if( $scope.mySelectionEspecialidadesGrid.length == 1 ){
              $scope.datosGrid = {
                paginate : paginationMedicoOptions,
                datos : $scope.mySelectionEspecialidadesGrid[0]
              };

              empleadoSaludServices.sListarMedicoEmpresaEspecialidad($scope.datosGrid).then(function (rpta) {                
                if( rpta.flag == 1){
                  $scope.gridOptionsMedicos.totalItems = rpta.paginate.totalRows;
                  $scope.gridOptionsMedicos.data = rpta.datos;
                  $scope.boolDatos = false;
                  $scope.datosGrid = {
                    idespecialidad : $scope.mySelectionEspecialidadesGrid[0].id
                  };
                  situacionAcademicaServices.sListarSituacionAcademicaPorEspecialidad($scope.datosGrid).then(function (rpta){
                    $scope.situacionOptions = [];
                    angular.forEach(rpta.datos, function (val,index) {
                      $scope.arrTemporal = {
                        'id': val.id,
                        'descripcion': val.descripcion,
                      }
                      $scope.situacionOptions.push($scope.arrTemporal);
                    });
                    $scope.gridOptionsMedicos.columnDefs[4].editDropdownOptionsArray = $scope.situacionOptions;
                  });
                }else{
                  $scope.boolDatos = true;
                }
              });

              
            }else{
              $scope.gridOptionsMedicos.data = [];
              $scope.boolDatos = false;
            }
          };
            // =============================================================
           // AUTOCOMPLETADO MEDICOS NO AGREGADOS A LA EMPRESA - ESPECIALIDAD
          // =============================================================
          $scope.getMedicoNoAgregAutocomplete = function (value) {
            var params = {
              search: value,
              idempresaespecialidad: $scope.mySelectionEspecialidadesGrid[0].idempresaespecialidad,
              sensor: false
            }
            return empleadoSaludServices.sListarMedicoNoAgregEmpresaAutocomplete(params).then(function(rpta) { 
              $scope.noResultsLMedicos = false;
              if( rpta.flag === 0 ){
                $scope.noResultsLMedicos = true;
              }
              return rpta.datos; 
            });
          }
          $scope.getSelectedMedico = function ($item, $model, $label) {
              $scope.fDataAddMed.idmedico = $item.idmedico;
              //console.log('seleccion ',$scope.mySelectionEspecialidadesGrid[0]);
          };
          $scope.limpiaIdMedico = function (){
            $scope.noResultsLMedicos = false;
            $scope.fDataAddMed.idmedico = null;
          }
          
          // =============================================================
          $scope.agregarMedico = function () {
            if( !$scope.fDataAddMed.idmedico ){
              var pTitle = 'AVISO!';
              var pType = 'warning';
              pinesNotifications.notify({ title: pTitle, text: 'Seleccione un médico para agregar', type: pType, delay: 3000 });
              return false;
            }
            $scope.fDataAddMed.id = $scope.mySelectionEspecialidadesGrid[0].idempresaespecialidad;
            $scope.fDataAddMed.idespecialidad = $scope.mySelectionEspecialidadesGrid[0].id;
            empleadoSaludServices.sAgregarMedicoAEmpresaEsp($scope.fDataAddMed).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
                
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              $scope.getPaginationMedicoServerSide();
              $scope.fDataAddMed.idmedico = null;
              $scope.fDataAddMed.medico = null;
            });
          }
          $scope.quitarMedicoDeEmpresa = function () {
            var pMensaje = '¿Realmente desea realizar la acción?. Al realizar esta acción se eliminará toda la información correspondiente al Profesional. Se sugiere utilizar la opción "DESHABILITAR" ';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                $scope.fDataAddMed.medicos = $scope.mySelectionMedicoGrid;
                empleadoSaludServices.sAnularMedicoAEmpresaEsp($scope.fDataAddMed).then(function (rpta) { 
                  if(rpta.flag == 1){
                    pTitle = 'OK!';
                    pType = 'success';
                  }else if(rpta.flag == 0){
                    var pTitle = 'Aviso!';
                    var pType = 'warning';
                  }else{
                    alert('Error inesperado');
                  }
                  pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  $scope.getPaginationMedicoServerSide();
                });
              }
            });
          }
          $scope.habilitarMedicoEnEmpresa = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //var rowEliminar = row.entity; // empresa-especialidad-medico

                empleadoSaludServices.sHabilitarEspecialidadMedico($scope.mySelectionMedicoGrid).then(function (rpta) {
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
                 $scope.getPaginationMedicoServerSide();
                });
              }
            });
          }
          $scope.deshabilitarMedicoEnEmpresa = function (row,mensaje) {
            var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                //var rowEliminar = row.entity; // empresa-especialidad-medico

                empleadoSaludServices.sDesHabilitarEspecialidadMedico($scope.mySelectionMedicoGrid).then(function (rpta) {
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
                  $scope.getPaginationMedicoServerSide();
                });
              }
            });
          }
          // =============================================================
          $scope.btnNuevoEmplSalud = function (){
            $scope.fDataAddMed.idmedico = null;
            $scope.fDataAddMed.medico = null;
            
            var paramDatos = {
              'idempresa' : $scope.mySelectionGridTab2[0].idempresa,
              'empresa' : $scope.mySelectionGridTab2[0].empresa,
              'idempresaespecialidad' : $scope.mySelectionEspecialidadesGrid[0].idempresaespecialidad,
              'especialidad' : $scope.mySelectionEspecialidadesGrid[0].nombre + ' - ' + $scope.mySelectionGridTab2[0].empresa,
            }
            $scope.btnNuevoEmpleado(paramDatos);
          }

          $scope.btnEditarMedico = function(){
            //console.log($scope.mySelectionMedicoGrid[0]);
            var arrParams = {
              paginate : {
                pageNumber: 1,
                firstRow: 0,
                pageSize: 10,
                sort: uiGridConstants.DESC,
                sortName: null,
                search : true,
                searchColumn : {'e.numero_documento' : $scope.mySelectionMedicoGrid[0].numero_documento}
              },
              datos: {modulo:'empresa'}
            };            
            empleadoServices.sListarEmpleados(arrParams).then(function (rpta) {
              $scope.mySelectionGrid = rpta.datos;
              if(rpta.flag==1){
                $scope.mySelectionEspecialidadesGrid = [];
                $scope.mySelectionMedicoGrid = [];
                $scope.gridOptionsMedicos.data = [];
                $scope.btnEditar('xlg', true);
                $scope.getPaginationEspecialidadesServerSide();                
              }                           
            });
          }          

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataAdd = {};
            $scope.fDataAddMed = {};
          }
        }, 
        resolve: {
          arrToModal : function () {
            return {
              mySelectionGridTab2 : $scope.mySelectionGridTab2,
              getPaginationServerSide : $scope.getPaginationServerSide
            }
          }
        }
      });
    }

    $scope.btnGestionContrato = function (){
      $scope.contratos = {};
      $scope.contratos.editarContratoBool = false;
      $scope.contratos.fData = {};
      $scope.dirContratosEma = 'assets/img/dinamic/ema/contratos/';
      $scope.dirIconoFormat = 'assets/img/formato-imagen/';
      $modal.open({
        templateUrl: angular.patchURLCI+'empresa/ver_popup_gestion_contratos',
        size: 'xlg',
        scope: $scope,
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance ) {
          $scope.titleFormAdd = 'Gestión de Contratos';
          $scope.contratos.empresaadmin = $scope.empresaAdmin.empresa;
          $scope.contratos.empresa = $scope.mySelectionGridTab2[0].empresa;

          $scope.agregarContrato = function(){
            $scope.contratos.fData.idempresadetalle = $scope.mySelectionGridTab2[0].idempresadetalle;
            //console.log($scope.contratos.fData);
            empresaHistorialContratoServices.sAgregarContrato($scope.contratos.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                //$modalInstance.dismiss('cancel');
                $scope.getListaContratos();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                
              }else{
                alert('Error inesperado');
              }
              $scope.contratos.fData = {};
              $scope.contratos.fData.contrato_actual = 1;
              $scope.contratos.fData.contrato_formal = 1;
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }    
          

          $scope.cancel = function () { 
            $modalInstance.dismiss('cancel'); 
          }

          $scope.getListaContratos = function(){
            
            $scope.contratos.fData.idempresadetalle = $scope.mySelectionGridTab2[0].idempresadetalle;
            $scope.contratos.fData.contrato_actual = 1;
            $scope.contratos.fData.contrato_formal = 1;            
            $scope.contratos.fData.idempresa = $scope.mySelectionGridTab2[0].idempresa;
            $scope.contratos.fData.idempresaadmin =  $scope.empresaAdmin.idempresa;

            $scope.contratos.datos = {
              idempresadetalle: $scope.contratos.fData.idempresadetalle
            }

            empresaHistorialContratoServices.sListarContratos($scope.contratos).then(function (rpta) { 
              $scope.contratos.listaHistorial = rpta.datos;
            });
          }
          $scope.getListaContratos();

          $scope.editarContrato = function(row){            
            $scope.contratos.fData = row;
            $scope.cambiarValores(true,'ui-editPanel');            
          }

          $scope.salirActualizarContrato = function(){
            $scope.contratos.fData = {};
            $scope.contratos.fData.contrato_actual = 1;
            $scope.contratos.fData.contrato_formal = 1;            
            $scope.cambiarValores(false,''); 
          }

          $scope.cambiarValores = function(value, clase){
            $scope.contratos.editarContratoBool = value;           
            $scope.contratos.classEditPanel = clase;           
          }

          $scope.actualizarContrato = function(){
            console.log($scope.contratos.fData);
            empresaHistorialContratoServices.sEditarContrato($scope.contratos.fData).then(function (rpta) { 
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';                
                $scope.getListaContratos();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
                
              }else{
                alert('Error inesperado');
              }
              $scope.contratos.fData = {};
              $scope.contratos.fData.contrato_actual = 1;
              $scope.contratos.fData.contrato_formal = 1;              
              $scope.cambiarValores(false,'');
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }

          $scope.anularContrato = function (row) { 
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                empresaHistorialContratoServices.sAnularContrato(row).then(function (rpta) {
                  if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.getListaContratos();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Aviso!';
                      var pType = 'warning';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                });
              }
            });
          }

          $scope.agregarAdenda = function (row) { 
            console.log("row:",row);
          /*--------- Agregar Adenda --------*/
            $modal.open({
              templateUrl: angular.patchURLCI+'empresa/ver_popup_Agregar_Adenda',
              size: 'lg',
              scope: $scope,
              backdrop: 'static',
              keyboard:false,
              controller: function ($scope, $modalInstance ) {
                $scope.titleFormAdd = 'Gestión de Adendas';
                $scope.fAdenda = {} ;
                $scope.adendas = {} ;
                $scope.adendas.editarAdendaBool = false ;
                $scope.adendas.edit = true ;
                $scope.adendas.btnedit = true ;


                var paginationAdendasOptions = {
                  pageNumber: 1,
                  firstRow: 0,
                  pageSize: 10,
                  sort: uiGridConstants.ASC,
                  sortName: null,
                  search: null
                };
                $scope.mySelectionAdendaGrid = [];
                $scope.gridOptionsAdendas = { 
                  paginationPageSizes: [10, 50, 100, 500, 1000],
                  paginationPageSize: 10,
                  minRowsToShow: 10,
                  useExternalPagination: true,
                  useExternalSorting: true,
                  useExternalFiltering : true,
                  //enableGridMenu: true,
                  enableRowSelection: true,
                  enableSelectAll: true,
                  enableFiltering: false,
                  enableFullRowSelection: true,
                 //rowHeight: 100,
                  multiSelect: false,
                  columnDefs: [
                    { field: 'idempresahistorialcontrato', displayName: 'ID', width: "12%", enableCellEdit: false, enableFiltering: false },
                    { field: 'fecha_fin', displayName: 'Fecha Adenda', width: "25%", enableCellEdit: false, enableFiltering: false } ,
                    { field: 'condiciones', displayName: 'Condiciones', width: "56%", enableCellEdit: false, enableFiltering: false }                    
                  ],
                  onRegisterApi: function(gridApi) {
                    $scope.gridApi = gridApi;
                    gridApi.selection.on.rowSelectionChanged($scope,function(row){
                      $scope.mySelectionAdendaGrid = gridApi.selection.getSelectedRows();
                      $scope.salirActualizarAdenda();
                    });
                    gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
                      $scope.mySelectionAdendaGrid = gridApi.selection.getSelectedRows();
                    });

                    $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                      if (sortColumns.length == 0) {
                        paginationAdendasOptions.sort = null;
                        paginationAdendasOptions.sortName = null;
                      } else {
                        paginationAdendasOptions.sort = sortColumns[0].sort.direction;
                        paginationAdendasOptions.sortName = sortColumns[0].name;
                      }
                      $scope.getPaginationAdendaServerSide();
                    });
                    gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                      paginationAdendasOptions.pageNumber = newPage;
                      paginationAdendasOptions.pageSize = pageSize;
                      paginationAdendasOptions.firstRow = (paginationAdendasOptions.pageNumber - 1) * paginationAdendasOptions.pageSize;
                      $scope.getPaginationAdendaServerSide();
                    });
                  }
                };                

                $scope.getPaginationAdendaServerSide = function() { 
                  empresaHistorialContratoServices.sListarAdendasContratos(row.idcontrato).then(function (rpta) {
                    $scope.gridOptionsAdendas.totalItems = rpta.paginate.totalRows;
                    $scope.gridOptionsAdendas.data = rpta.datos;
                  });
                  $scope.mySelectionAdendaGrid = [];
                };
                $scope.getPaginationAdendaServerSide();

                $scope.cambiarValoresAdendas = function(clase){
                  //$scope.contratos.editarContratoBool = value;           
                  $scope.adendas.classEditPanel = clase; 
                }
                $scope.btnNewAdenda = function(){
                  $scope.gridApi.selection.clearSelectedRows();
                  $scope.fAdenda = {};
                  $scope.adendas.edit = false ;
                  $scope.fAdenda.fecha_fin = row.fecha_fin ;
                }  

                $scope.btnEditarAdenda = function(){
                  $scope.adendas.editarAdendaBool = true ;
                  $scope.fAdenda = $scope.mySelectionAdendaGrid[0]; 
                  $scope.adendas.edit = false ;   
                  $scope.adendas.btnedit = false;   
                  $scope.cambiarValoresAdendas('ui-editPanel');                              
                }

                $scope.btnAnularAdenda = function(){
                  var pMensaje = '¿Realmente desea realizar la acción?';
                  $bootbox.confirm(pMensaje, function(result) {
                    if(result){
                      empresaHistorialContratoServices.sAnularAdenda($scope.mySelectionAdendaGrid).then(function (rpta) {
                        if(rpta.flag == 1){
                            pTitle = 'OK!';
                            pType = 'success';
                            $scope.getPaginationAdendaServerSide();
                          }else if(rpta.flag == 0){
                            var pTitle = 'Aviso!';
                            var pType = 'warning';
                          }else{
                            alert('Error inesperado');
                          }
                          $scope.adendas.editarAdendaBool = false ;
                          $scope.adendas.edit = true ;                    
                          $scope.fAdenda = {};                          
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                      });
                    }
                  });                  
                }

                $scope.agregarAdenda = function(){
                  $scope.fAdenda.idempresahistorialcontrato = row.idcontrato ;
                  empresaHistorialContratoServices.sAgregarAdenda($scope.fAdenda).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      //$modalInstance.dismiss('cancel');
                      $scope.getPaginationAdendaServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                      
                    }else{
                      alert('Error inesperado');
                    }
                    $scope.adendas.editarAdendaBool = false ;
                    $scope.adendas.edit = true ; 
                    $scope.adendas.btnedit = true;  
                    $scope.cambiarValoresAdendas('');                 
                    $scope.fAdenda = {};
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                  });                  
                }  
                
                $scope.salirActualizarAdenda = function(){
                  $scope.adendas.editarAdendaBool = false ;
                  $scope.adendas.edit = true ;
                  $scope.adendas.btnedit = true;
                  $scope.cambiarValoresAdendas('');
                  $scope.fAdenda = {} ;
                }

                $scope.cancel = function () { 
                  $modalInstance.dismiss('cancel'); 
                }

                $scope.actualizarAdenda = function(){           
                  empresaHistorialContratoServices.sEditarAdenda($scope.fAdenda).then(function (rpta) { 
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      //$modalInstance.dismiss('cancel');
                      $scope.getPaginationAdendaServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                      
                    }else{
                      alert('Error inesperado');
                    }
                    $scope.adendas.editarAdendaBool = false ;
                    $scope.adendas.edit = true ;  
                    $scope.adendas.btnedit = true ;
                    $scope.cambiarValoresAdendas('');
                    $scope.fAdenda = {};
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                  });                              
                }                        
              }
            });
          /*---------------------------------*/
          }          

          $scope.quitarDocumento = function (row) { 
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) {
              if(result){
                empresaHistorialContratoServices.sQuitarArchivoContrato(row).then(function (rpta) {
                  if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $scope.getListaContratos();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Aviso!';
                      var pType = 'warning';
                    }else{
                      alert('Error inesperado');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                });
              }
            });
          }

          $scope.subirContrato = function(row){
             $modal.open({
              templateUrl: angular.patchURLCI+'empresa/ver_popup_subir_contratos',
              size: '',
              scope: $scope,
              backdrop: 'static',
              keyboard:false,
              controller: function ($scope, $modalInstance ) {
                $scope.titleFormDet = 'Subir Contrato de Empresa';
                $scope.fDataSubida = {}; 
                $scope.fDataContrato = row; 
                $scope.fDataSubida.idcontrato = row.idcontrato; 
                $scope.cancelSubida = function () { 
                  $modalInstance.dismiss('cancelSubida'); 
                }

                $scope.aceptarSubida = function (){
                  blockUI.start('Ejecutando proceso...');
                  var formData = new FormData();                  
                  angular.forEach($scope.fDataSubida,function (index,val) { 
                    formData.append(val,index);
                  });
                  empresaHistorialContratoServices.sSubirArchivoContrato(formData).then(function (rpta) {
                    if(rpta.flag == 1){
                        pTitle = 'OK!';
                        pType = 'success';
                        $scope.getListaContratos();
                        $scope.cancelSubida();
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
              }
            });
          }
          
        }
      });
    }

    /* ============================ */
    /* ATAJOS DE TECLADO NAVEGACION */
    /* ============================ */
    hotkeys.bindTo($scope)
      .add({
        combo: 'alt+n',
        description: 'Nueva empresa',
        callback: function() {
          $scope.btnNuevo();
        }
      })
      .add ({ 
        combo: 'e',
        description: 'Editar empresa',
        callback: function() {
          if( $scope.mySelectionGridTab1.length == 1 ){
            $scope.btnEditar();
          }
        }
      })
      .add ({ 
        combo: 'del',
        description: 'Anular empresa',
        callback: function() {
          if( $scope.mySelectionGridTab1.length > 0 ){
            $scope.btnAnular();
          }
        }
      })
      .add ({ 
        combo: 'b',
        description: 'Buscar empresa',
        callback: function() {
          $scope.btnToggleFiltering();
        }
      })
      // .add ({ 
      //   combo: 's',
      //   description: 'Selección y Navegación',
      //   callback: function() {
      //     $scope.navegateToCell(0,0);
      //   }
      // });
  }])
  .service("empresaServices",function($http, $q) {
    return({
        sListarEmpresas: sListarEmpresas,
        sListarEmpresasCbo: sListarEmpresasCbo,
        sListarEmpresasSoloAdminCbo: sListarEmpresasSoloAdminCbo,
        sListarEspecialidadNoAgregEmpresaAutocompletado: sListarEspecialidadNoAgregEmpresaAutocompletado,
        sListarEspecialidadesEmpresa: sListarEspecialidadesEmpresa,
        sListarEmpresasDeEspecialidad: sListarEmpresasDeEspecialidad,
        sListarEmpresaporCodigo : sListarEmpresaporCodigo,
        sListarEmpresaPorRuc : sListarEmpresaPorRuc,
        sRegistrar: sRegistrar,
        sAgregarEspecialidadAEmpresa: sAgregarEspecialidadAEmpresa,
        sQuitarEspecialidadDeEmpresa: sQuitarEspecialidadDeEmpresa,
        sDeshabilitarEspecialidadDeEmpresa: sDeshabilitarEspecialidadDeEmpresa,
        sHabilitarEspecialidadDeEmpresa: sHabilitarEspecialidadDeEmpresa,
        sEditar: sEditar,
        sAnular: sAnular,
        sEditarPorcentaje: sEditarPorcentaje,
        sListarMedicosEmpresaEspecialidad: sListarMedicosEmpresaEspecialidad,
        sListarEmpresasAdminCbo:sListarEmpresasAdminCbo,
        sRegistrarEmpresaDet: sRegistrarEmpresaDet,
        sCambiarEstadoEmpresa: sCambiarEstadoEmpresa,
        sCambiarEstadoEmpresaDet: sCambiarEstadoEmpresaDet,
        sValidacionEmpresaAdmin: sValidacionEmpresaAdmin,

        sListarProveedores: sListarProveedores 
    });

    function sListarEmpresas(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresas", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresas_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasSoloAdminCbo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresas_solo_admin_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresaporCodigo(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresa_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresaPorRuc(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresa_por_ruc", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    // function sListarEspecialidadesNoAgregadosAEmpresa (datos) {
    //   var request = $http({
    //         method : "post",
    //         url : angular.patchURLCI+"empresa/lista_especialidades_no_agregados_a_empresa", 
    //         data : datos
    //   });
    //   return (request.then( handleSuccess,handleError ));
    // }
    function sListarEspecialidadNoAgregEmpresaAutocompletado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_especialidades_no_agregados_a_empresa_autocompletado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEspecialidadesEmpresa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_especialidades_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasDeEspecialidad(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresas_de_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/registrar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarEspecialidadAEmpresa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/agregar_especialidad_a_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarEspecialidadDeEmpresa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/quitar_especialidad_de_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDeshabilitarEspecialidadDeEmpresa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/deshabilitar_especialidad_de_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sHabilitarEspecialidadDeEmpresa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/habilitar_especialidad_de_empresa", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/editar", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarPorcentaje (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/editar_porcentaje", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarMedicosEmpresaEspecialidad (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_medico_empresa_especialidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpresasAdminCbo (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/lista_empresas_admin_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarEmpresaDet (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/registrar_empresa_det", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sCambiarEstadoEmpresa (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/cambiar_estado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sCambiarEstadoEmpresaDet (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/cambiar_estado_empresa_det",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sValidacionEmpresaAdmin (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empresa/validacion_empresa_admin", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarProveedores(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"Empresa/listar_proveedores_contabilidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
  }).filter('mapEstado', function() {
  var sizeHash = {
    1: 'HABILITADO',
    2: 'DESHABILITADO'
  };
  return function(input) {
    if (!input){
      return '';
    } else {
      return sizeHash[input];
    }
  };
});