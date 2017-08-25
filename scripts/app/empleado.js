angular.module('theme.empleado', ['theme.core.services'])
  .controller('empleadoController', ['$scope', '$location', '$anchorScroll', '$sce', '$filter', '$modal', '$uibModal','$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'blockUI',
    'grupoServices', 
    'empleadoServices', 
    'sedeServices', 
    'especialidadServices', 
    'cargoServices', 
    'usuarioServices', 
    'clienteServices',
    'almacenFarmServices',
    'empresaServices',
    'empresaAdminServices',
    'ModalReporteFactory',
    'parienteServices',
    'afpServices',
    'ubigeoServices',
    'areaEmpresaServices',
    'profesionServices',
    'historialContratoServices',
    'tipoDocumentoRRHHServices',
    'documentoEmpleadoServices',
    'categoriaPersonalSaludServices',
    'centroCostoServices',
    'bancoServices',
    function($scope, $location, $anchorScroll, $sce, $filter, $modal, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, blockUI,
      grupoServices,
      empleadoServices, 
      sedeServices, 
      especialidadServices, 
      cargoServices, 
      usuarioServices, 
      clienteServices,
      almacenFarmServices,
      empresaServices,
      empresaAdminServices,
      ModalReporteFactory,
      parienteServices,
      afpServices,
      ubigeoServices,
      areaEmpresaServices,
      profesionServices,
      historialContratoServices,
      tipoDocumentoRRHHServices,
      documentoEmpleadoServices,
      categoriaPersonalSaludServices,
      centroCostoServices,
      bancoServices) { 
    // 'use strict'; 
    shortcut.remove("F2"); 
    $scope.dirContratosEmpleados = $scope.dirImages + "dinamic/empleado/documentacion/contratos/";
    $scope.dirCVEmpleados = $scope.dirImages + "dinamic/empleado/documentacion/cv/";
    $scope.dirIconoFormat = $scope.dirImages + "formato-imagen/";
    $scope.fBusqueda = {};
    $scope.collapse = {};
    $scope.collapse.collapsedAbv = 'DG';
    //$scope.collapse.collapsedAbv = null;
    $scope.collapse.isCollapsedDG = 'DG';
    $scope.collapse.isCollapsedDF = 'DF';
    $scope.collapse.isCollapsedDL = 'DL';
    $scope.collapse.isCollapsedDE = 'DE';
    $scope.listaActivo = [
      { id: 'all', descripcion: 'TODOS' },
      { id: 1, descripcion: 'PERSONAL - ACTIVO' },
      { id: 2, descripcion: 'PERSONAL - CESADO' }
    ];
    $scope.listaTercero = [
      { id: 'all', descripcion: 'TODOS' },
      { id: 1, descripcion: 'PERSONAL - EMA' },
      { id: 2, descripcion: 'PERSONAL - PROPIOS' }
    ];
    $scope.listaOperadores = [ 
      { id: 'NONE', descripcion: 'Operador' },
      { id: 'CLARO', descripcion: 'CLARO' },
      { id: 'MOVISTAR', descripcion: 'MOVISTAR' },
      { id: 'ENTEL', descripcion: 'ENTEL' },
      { id: 'BITEL', descripcion: 'BITEL' }
    ];
    $scope.listaEstadoCivil = [
      { id: 1, descripcion: 'SOLTERO' },
      { id: 2, descripcion: 'CASADO' },
      { id: 3, descripcion: 'VIUDO' },
      { id: 4, descripcion: 'DIVORCIADO' },
      { id: 5, descripcion: 'CONVIVIENTE REG.' },
      { id: 6, descripcion: 'CONVIVIENTE NO REG.' }
    ];   
    $scope.listaVive = [
      { id: 1, descripcion: 'SI' },
      { id: 2, descripcion: 'NO' }
    ];
    $scope.listaSexo = [
      { id: 'NONE', descripcion: 'Seleccione Sexo' },
      { id: 'M', descripcion: 'MASCULINO' },
      { id: 'F', descripcion: 'FEMENINO' }
    ];
    $scope.listaParentesco = [ 
      { id: 'NONE', descripcion:'Seleccione Parentesco' },
      { id: 'PADRE', descripcion: 'PADRE' },
      { id: 'MADRE', descripcion: 'MADRE' },
      { id: 'HIJO(A)', descripcion: 'HIJO(A)' },
      { id: 'CONYUGUE', descripcion: 'CONYUGUE' },
      { id: 'CONVIVIENTE', descripcion: 'CONVIVIENTE' },
      { id: 'PRIMO(A)', descripcion: 'PRIMO(A)' },
      { id: 'TIO(A)', descripcion: 'TIO(A)' },
      { id: 'HERMANO(A)', descripcion: 'HERMANO(A)' },
      { id: 'OTROS', descripcion: 'OTROS' }
    ];
    $scope.listaRegPensionario = [
      { id: 'NONE', descripcion: '--Seleccione Régimen--' },
      { id: 'AFP', descripcion: 'AFP' },
      { id: 'ONP', descripcion: 'ONP' }
    ]; 
    $scope.listaCondicionLaboral = [
      { id: 'NONE', descripcion: '--Seleccione Condición Laboral--' },
      { id: 'EN PLANILLA', descripcion: 'EN PLANILLA' },
      { id: 'POR LOCACION DE SERVICIOS', descripcion: 'POR LOCACION DE SERVICIOS' },
      { id: 'PRACTICANTE', descripcion: 'PRACTICANTE' },
      { id: 'OTROS', descripcion: 'OTROS' }
    ];
    $scope.listComisionAFP = [
      { id: 'NONE', descripcion: '-- Seleccione --' },
      { id: 'FLUJO', descripcion: 'COMISIÓN POR FLUJO' },
      { id: 'MIXTA', descripcion: 'COMISIÓN MIXTA' }
    ]; 
    afpServices.sListarAFPcbo().then(function (rpta) { 
      $scope.listaAFP = rpta.datos;
      $scope.listaAFP.splice(0,0,{ id : 'all', descripcion:'-- Seleccione --'});
    });
    areaEmpresaServices.sListarAreaEmpresaCbo().then(function (rpta) { 
      $scope.listaAreaEmpresa = rpta.datos;
      $scope.listaAreaEmpresa.splice(0,0,{ id : 'all', descripcion:'-- Seleccione --'});
    });
    centroCostoServices.sListarCategoriaSubCatCentroCostoCbo().then(function (rpta) { 
      $scope.listaSubCatCentroCosto = rpta.datos;
      $scope.listaSubCatCentroCosto.splice(0,0,{ id : 'all', descripcion:'-- Seleccione --'});
      $scope.listaCentroCosto = [];
      $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione 1º Cat/SubCategoria --'});
    });

    tipoDocumentoRRHHServices.sListarTipoDocumento().then(function (rpta) { 
      $scope.listaTipoDocumento = rpta.datos;
    });
    
    categoriaPersonalSaludServices.sListarCategoriaPersonalSaludCbo().then(function (rpta) { 
      $scope.listaCategoriaPersonalSalud = rpta.datos;
      $scope.listaCategoriaPersonalSalud.splice(0,0,{ idcategoriapersonalsalud : 'NONE', descripcion_cps:'-- Seleccione --'});
    });
    /* LISTADO DE BANCOS */
    bancoServices.sListarBancosCbo().then(function (rpta) { 
      $scope.listaBancos = rpta.datos;
      $scope.listaBancos.splice(0,0,{ id : '', descripcion:'--Seleccione Banco--'});
      // $scope.fData.idsede = $scope.listaBancos[0].id;
    });

    $scope.metodos = {};
    empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) { 
      $scope.metodos.listaEmpresaAdmin = rpta.datos;
      $scope.metodos.listaEmpresaAdmin.splice(0,0,{ id : 'NONE', descripcion:'-- Seleccione --'});
    });
    // profesionServices.sListarProfesionesCbo().then(function (rpta) { 
    //   $scope.listaProfesiones = rpta.datos;
    //   $scope.listaProfesiones.splice(0,0,{ id : 'all', descripcion:'-- Seleccione --'});
    // });
    $scope.listaTipoEstudio = [
      { id: 1, descripcion: 'BASICO' },
      { id: 2, descripcion: 'TECNICO' },
      { id: 3, descripcion: 'UNIVERSITARIO' }
    ];
    $scope.listaEstudioCompleto = [
      { id: 0, descripcion: '--Seleccione opcion--' },
      { id: 1, descripcion: 'COMPLETO' },
      { id: 2, descripcion: 'INCOMPLETO' },
    ];
    $scope.listaGradoAcademico = [
      { id: 'NONE', descripcion: '--Seleccione Grado Académico--' },
      { id: 'BACHILLERATO', descripcion: 'BACHILLERATO' },
      { id: 'TITULO PROFESIONAL', descripcion: 'TITULO PROFESIONAL' },
      { id: 'MAGISTER', descripcion: 'MAGISTER' },
      { id: 'DOCTOR', descripcion: 'DOCTOR' }
      // { id: 'ESTUDIANTE', descripcion: 'ESTUDIANTE' },
    ];
    $scope.fBusqueda.tercero = 2;
    $scope.fBusqueda.activo = 1;

    $scope.initEmpleado = function () {
      $scope.modulo = 'empleado';
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
        useExternalFiltering : true,
        enableGridMenu: true,
        enableRowSelection: true,
        enableSelectAll: true,
        enableFiltering: false,
        enableFullRowSelection: true,
        multiSelect: false, // se le quitó el multiselect para que anule solo uno y verifique si tiene asistencia y/ atencion medica.
        columnDefs: [
          { field: 'id', name: 'e.idempleado', displayName: 'ID', width: 80,  sort: { direction: uiGridConstants.DESC}, visible: false },
          { field: 'tipo_documento', name: 'td.descripcion_rtd', displayName: 'TIPO DOC', width: 80, visible: false },
          { field: 'num_documento', name: 'e.numero_documento', displayName: 'N° DOC.',width: 80 },
          { field: 'nombres', name: 'e.nombres', displayName: 'NOMBRES',minWidth: 140 }, 
          { field: 'apellido_paterno', name: 'e.apellido_paterno', displayName: 'APELLIDO PATERNO',minWidth: 140 },
          { field: 'apellido_materno', name: 'e.apellido_materno', displayName: 'APELLIDO MATERNO',minWidth: 140 },
          { field: 'cargo', name: 'c.descripcion_ca', displayName: 'CARGO',width: 200 },
          { field: 'cargo_sup', name: 'cj.descripcion_ca', displayName: 'CARGO DE JEFE INMEDIATO',minWidth: 200 },
          { field: 'empresa', name: 'empresa', displayName: 'EMPRESA',width: 220 },
          { field: 'soloEspecialidad', name: 'especialidad', displayName: 'ESPECIALIDAD',width: '10%', visible: false },
          { field: 'telefono', name: 'e.telefono', displayName: 'TELEFONO', type:'number',width: '7%'},
          { field: 'email', name: 'e.correo_electronico', displayName: 'E-MAIL',width: '12%'},
          // { field: 'jefe_inmediato', name: 'e.jefe_inmediato', displayName: 'JEFE INMEDIATO',minWidth: 240, visible: false}, 
          { field: 'direccion', name: 'e.direccion', displayName: 'DIRECCION', enableFiltering: false, visible: false,width: 180},
          { field: 'fecha_nacimiento', name: 'e.fecha_nacimiento', displayName: 'FECHA NAC.', visible: false, enableFiltering: false,width: 100},
          { field: 'nombre_foto', pinnedRight:true, name: 'e.nombre_foto', displayName: '',width: 70, enableFiltering: false, enableSorting: false, cellTemplate:'<img style="height:inherit;" class="center-block" ng-src="{{ grid.appScope.dirImagesEmpleados + COL_FIELD }}" /> </div>' },
          { field: 'nombre_cv', name: 'e.nombre_cv', pinnedRight:true, displayName: 'C.V', width: '60', type: 'object', enableFiltering: false, enableSorting: true,
            cellTemplate:'<div ng-show="COL_FIELD.hay_cv"> <a target="_blank" href="{{ grid.appScope.dirCVEmpleados + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </a> </div>' },
          { field: 'archivo', pinnedRight:true, displayName: 'C.A.', width: '86', type: 'object', enableFiltering: false, enableSorting: false,
            cellTemplate:'<div ng-show="COL_FIELD.hay_archivo"> <a target="_blank" href="{{ grid.appScope.dirContratosEmpleados + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </a> </div>' },
          { field: 'estado_activo', type: 'object', name: 'si_activo', displayName: 'ESTADO', width: '10%', enableFiltering: false, enableSorting: false, 
            cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>' }
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
            paginationOptions.searchColumn = {              
              'e.idempleado' : grid.columns[1].filters[0].term,
              'td.descripcion_rtd' : grid.columns[2].filters[0].term,
              'e.numero_documento' : grid.columns[3].filters[0].term,
              'e.nombres' : grid.columns[4].filters[0].term,
              'e.apellido_paterno' : grid.columns[5].filters[0].term,
              'e.apellido_materno' : grid.columns[6].filters[0].term,
              'c.descripcion_ca' : grid.columns[7].filters[0].term,
              'cj.descripcion_ca' : grid.columns[8].filters[0].term,
              'em.descripcion' : grid.columns[9].filters[0].term,
              'esp.nombre' : grid.columns[10].filters[0].term,
              'e.telefono' : grid.columns[11].filters[0].term,
              'e.correo_electronico' : grid.columns[12].filters[0].term
              //"concat_ws(' ',  ej.nombres, ej.apellido_paterno, ej.apellido_materno)" : grid.columns[11].filters[0].term,
            }
            $scope.getPaginationServerSide();
          });
        }
      };
      paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
      $scope.getPaginationServerSide = function() {
        // $scope.$parent.blockUI.start();
        var arrParams = {
          paginate : paginationOptions,
          datos: $scope.fBusqueda
        };
        empleadoServices.sListarEmpleados(arrParams).then(function (rpta) {
          $scope.gridOptions.totalItems = rpta.paginate.totalRows;
          $scope.gridOptions.data = rpta.datos;
          // $scope.$parent.blockUI.stop();
        });
        $scope.mySelectionGrid = [];
      };
      $scope.getPaginationServerSide();
      //$scope.collapse.collapsedAbv =
      $scope.collapse.toggleEmpleado = function (abv) { 
        var abv = abv || 'DG';
        if($scope.collapse.collapsedAbv == abv){ 
          // $scope.collapse = {}; getSelectedCargoSup
          $scope.collapse.collapsedAbv = null;
        }else{ 
          $scope.collapse.collapsedAbv = (abv);
        } 
        $scope.classEditPanel = null;
      }
    }
    /* ============= */
    /* MANTENIMIENTO */
    /* ============= */ 
    $scope.btnEditar = function (size, param) {
      blockUI.start('Cargando Formulario');
      if(param && $scope.modulo == 'empresa' ){
         $scope.boolExterior = true;
      }else{
         $scope.boolExterior = false;
      }
      
      $modal.open({
        templateUrl: angular.patchURLCI+'empleado/ver_popup_formulario',
        size: size || '',
        backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.accion = 'edit';
          $scope.classEditPanel = null;
          $scope.editarContratoBool = false;
          $scope.titleForm = 'Edición de empleado';
          $scope.fData = {};
          /*$scope.cambiarTipoDoc = function() {
            if( $scope.fData.tipoDocumento.longitud_numero  ){
              $scope.minLengthNumDoc = 8;
            }
          }*/
          //$scope.fData.idcargo = $scope.mySelectionGrid[0].idcargo;
          $scope.clearSelectRegPensionario = function () {
            $scope.fData.comision_afp = $scope.listComisionAFP[0];
            $scope.fData.afp = $scope.listaAFP[0];
            $scope.fData.cuspp = null;
          }

          $scope.clearSelectCondLaboral = function () {
            $scope.clearSelectRegPensionario();
            $scope.fData.reg_pensionario = $scope.listaRegPensionario[0].id;
          }

          $scope.updateAfp = function(){
            //console.log($scope.fData.reg_pensionario);
            if($scope.fData.reg_pensionario != 'AFP'){
              $scope.fData.afp = $scope.listaAFP[0];
            }
          }

          $scope.getCargaAlmacenes = function() { 
            almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) {
              $scope.listaAlmacen = rpta.datos;
              $scope.listaAlmacen.splice(0,0,{ id : 'all', descripcion:'--Seleccione el Almacen--'});
            });
          }
          $scope.cargarCentroCosto = function(idsubcatcentrocosto,modoCambio){
            centroCostoServices.sListarCentroCostoCbo(idsubcatcentrocosto).then(function (rpta) { 
              $scope.listaCentroCosto = rpta.datos;
              $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione --'});
              if(modoCambio){
                $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
              }
            });
          }

          $scope.getCargaAlmacenes(); 
          $scope.OnChangeAlmacen=function(idalm){
            var arrParams = {
              idalmacen : idalm
            };
            almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo(arrParams).then(function (rpta) {
              $scope.listaSubalmacen = rpta.datos;
              $scope.listaSubalmacen.splice(0,0,{ id : 'oll', descripcion:'--Seleccione el SubAlmacen--'});
            });
          };
          
          if( $scope.mySelectionGrid.length == 1 ){
            $scope.fData = $scope.mySelectionGrid[0];
            console.log(  $scope.fData );            
            angular.forEach($scope.listaTipoDocumento, function (val,key) {
              if( val.id == $scope.mySelectionGrid[0].idtipodocumentorh ){                
                $scope.fData.tipoDocumento = $scope.listaTipoDocumento[key];
              }
            });
            if(!$scope.fData.idcentrocosto){
              $scope.fData.idsubcatcentrocosto = $scope.listaSubCatCentroCosto[0].id;
              $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;

            }else{
              $scope.cargarCentroCosto($scope.fData.idsubcatcentrocosto,false);
            }
            if(!$scope.fData.condicion_laboral){
              $scope.fData.condicion_laboral = $scope.listaCondicionLaboral[0].id;
            }
            if(!$scope.fData.reg_pensionario){
              $scope.fData.reg_pensionario = $scope.listaRegPensionario[0].id;
            }
            if(!$scope.fData.idbanco){
              $scope.fData.banco = $scope.listaBancos[0];
              console.log('lista, ', $scope.listaBancos[0]);
            }else{
              var objIndex = $scope.listaBancos.filter(function(obj) {
                return obj.id == $scope.fData.idbanco;
              }).shift(); 
              $scope.fData.banco = objIndex;
            }
            var indexArr = 0;
            angular.forEach($scope.listaAFP, function (val,key) { 
              if( val.id == $scope.mySelectionGrid[0].afp.id ){ 
                indexArr = key;
              }
            });
            $scope.fData.afp = $scope.listaAFP[indexArr];
            

            indexArr = 'NONE';
            angular.forEach($scope.listComisionAFP, function (val,key) { 
              if( val.id == $scope.mySelectionGrid[0].comision_afp.id ){ 
                indexArr = key;
              }
            });
            $scope.fData.comision_afp = $scope.listComisionAFP[indexArr];


            console.log('selection, ', $scope.mySelectionGrid[0].idbanco);
            console.log('banco ',$scope.fData.banco);
          
            if( !($scope.fData.cuspp) ||  $scope.fData.cuspp == 'null'){
                console.log("cuspp",$scope.mySelectionGrid[0].cuspp);
              $scope.fData.cuspp = '';
            }
            /*if( !($scope.fData.operador_movil) ){
              $scope.fData.operador_movil = 'NONE';
            }*/
            if( !($scope.fData.sexo) ){
              $scope.fData.sexo = 'NONE';
            }
            //console.log($scope.fData.area_empresa);
            if( !(angular.isObject($scope.fData.area_empresa)) || !($scope.fData.area_empresa.id) ){
              $scope.fData.area_empresa = $scope.listaAreaEmpresa[0];
            }else{
              var indexArr = 0;
              angular.forEach($scope.listaAreaEmpresa,function (val,key) { 
                if( val.id == $scope.mySelectionGrid[0].area_empresa.id ){ 
                  indexArr = key;
                }
              });
              $scope.fData.area_empresa = $scope.listaAreaEmpresa[indexArr];
            }
            
            $scope.OnChangeAlmacen($scope.mySelectionGrid[0].idalmacenfarmacia);

            indexArr = 0;
            angular.forEach($scope.listaCategoriaPersonalSalud, function (val,key) { 
              if( val.idcategoriapersonalsalud == $scope.mySelectionGrid[0].idcategoriapersonalsalud ){ 
                indexArr = key;
              }
            });
            $scope.fData.categoriaPersonalSalud = $scope.listaCategoriaPersonalSalud[indexArr];
          }else{
            alert('Seleccione una sola fila');
          }

          $scope.fData.conTemporal = { 
            idcargo : null
          };
          $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
          $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
          // ============================================================
          // UBIGEO - edicion
          // ============================================================
            $scope.getDepartamentoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return ubigeoServices.sListarDepartamentoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLD = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLD = true;
                }
                return rpta.datos; 
              });
            }
            $scope.obtenerDepartamentoPorCodigo = function () {
              if( $scope.fData.iddepartamento ){
                var arrData = {
                  'codigo': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddepartamento = rpta.datos.id;
                    $scope.fData.departamento = rpta.datos.descripcion;
                    $('#fDatadepartamento').focus();
                  }
                });
              }
            }
            $scope.getSelectedDepartamento = function ($item, $model, $label) {
                $scope.fData.iddepartamento = $item.id;
                $scope.fData.idprovincia = null;
                $scope.fData.provincia = null;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getProvinciaAutocomplete = function (value) {
              var params = {
                search: value,
                id: $scope.fData.iddepartamento,
                sensor: false
              }
              return ubigeoServices.sListarProvinciaPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLP = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLP = true;
                }
                return rpta.datos; 
              });
            }
            $scope.obtenerProvinciaPorCodigo = function () {
              if( $scope.fData.idprovincia ){
                var arrData = {
                  'codigo': $scope.fData.idprovincia,
                  'iddepartamento': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarProvinciaDeDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.idprovincia = rpta.datos.id;
                    $scope.fData.provincia = rpta.datos.descripcion;
                    $('#fDataprovincia').focus();
                  }
                });
              }
            }
            $scope.getSelectedProvincia = function ($item, $model, $label) {
                $scope.fData.idprovincia = $item.id;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getDistritoAutocomplete = function (value) {
              console.log($scope.fData.idprovincia);
              var params = {
                search: value,
                id_dpto: $scope.fData.iddepartamento,
                id_prov: $scope.fData.idprovincia,
                sensor: false
              }
              return ubigeoServices.sListarDistritoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLDis = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLDis = true;
                }
                return rpta.datos; 
              });
            }
            $scope.obtenerDistritoPorCodigo = function () {
              if( $scope.fData.iddistrito ){
                var arrData = {
                  'codigo': $scope.fData.iddistrito,
                  'iddepartamento': $scope.fData.iddepartamento,
                  'idprovincia': $scope.fData.idprovincia
                }
                ubigeoServices.sListarDistritosDeProvinciaPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddistrito = rpta.datos.id;
                    $scope.fData.distrito = rpta.datos.descripcion;
                    $('#fDatadistrito').focus();
                  }
                });
              }
            }
            $scope.getSelectedDistrito = function ($item, $model, $label) {
                $scope.fData.iddistrito = $item.id;
            };
          $scope.fData.parTemporal = {};
          $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[0];
          $scope.fData.parTemporal.vive = $scope.listaVive[0];
          $scope.fData.parTemporal.parentesco = 'NONE';

          // ================
          // PARIENTES - edicion
          // ================
            $scope.editarParienteBool = false;
            var paginationOptionsPAR = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 50,
              sort: uiGridConstants.DESC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsParientes = {
              paginationPageSizes: [10, 50, 100, 500, 1000],
              minRowsToShow: 9,
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              useExternalFiltering : true,
              enableGridMenu: true,
              enableSorting: false,
              enableSelectAll: true,
              enableFiltering: false,
              data: null,
              rowHeight: 30,
              columnDefs: [ 
                { field: 'pariente', name: 'nombres', displayName: 'APELLIDOS Y NOMBRES', width: '25%' },
                { field: 'parentesco', name: 'parentesco', displayName: 'PARENTESCO', width: '15%' },
                { field: 'fecha_nac', name: 'fecha_nac', displayName: 'FECHA NAC.', width: '10%' },
                { field: 'ocupacion', name: 'ocupacion', displayName: 'OCUPACIÓN', width: '20%' },
                { field: 'estado_civil', name: 'estado_civil', displayName: 'ESTADO CIVIL', width: '14%' },
                { field: 'vive', name: 'vive', displayName: 'VIVE', width: '10%' },
                { field: 'notificar_emergencia', name: 'notificar_emergencia', displayName: 'NOTIF. EMERG.', width: '10%', visible:false },
                { field: 'direccion', name: 'direccion', displayName: 'DIRECCION', width: '18%', visible:false },
                { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: '18%', visible:false },
                { field: 'accion', displayName: '', width: 80, 
                  cellTemplate:'<button type="button" class="btn btn-sm btn-warning" ng-click="grid.appScope.btnEditarPariente(row)"> <i class="fa fa-edit"></i> </button>' + 
                  '<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>'
                }
              ]
            };
            $scope.getPaginationServerSidePAR = function () {
              var arrParams = {
                paginate : paginationOptionsPAR,
                datos: $scope.mySelectionGrid[0]
              };
              parienteServices.sListarParientes(arrParams).then(function (rpta) {
                $scope.gridOptionsParientes.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsParientes.data = rpta.datos;
              });
            }
            $scope.getPaginationServerSidePAR();
            $scope.btnEditarPariente = function (row) { 
              //console.log('fila ', row.entity); actualizarPariente
              $scope.editarParienteBool = true;
              $scope.classEditPanel = 'ui-editPanel';
              $scope.fData.parTemporal.nombres = row.entity.nombres;
              $scope.fData.parTemporal.ap_paterno = row.entity.ap_paterno;
              $scope.fData.parTemporal.ap_materno = row.entity.ap_materno;
              $scope.fData.parTemporal.parentesco = row.entity.parentesco;
              $scope.fData.parTemporal.ocupacion = row.entity.ocupacion;
              $scope.fData.parTemporal.fecha_nacimiento = row.entity.fecha_nac;
              angular.forEach($scope.listaEstadoCivil, function(value, key){
                //console.log('value ', value);
                //console.log('key ', key); agregarParienteACesta
                if(row.entity.estado_civil_num == value.id){
                  $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[key];
                  // return;
                }
              });
              if(row.entity.vive == 'SI'){
                $scope.fData.parTemporal.vive = $scope.listaVive[0];
              }else{
                $scope.fData.parTemporal.vive = $scope.listaVive[1];
              }
              $scope.fData.parTemporal.notificar_emergencia = row.entity.notificar_emergencia;
              $scope.fData.parTemporal.direccion = row.entity.direccion;
              $scope.fData.parTemporal.telefono = row.entity.telefono;
              $scope.fData.parTemporal.idpariente = row.entity.idpariente;
            }
            $scope.actualizarPariente = function (){
              var paramDatos = {
                datos: $scope.fData.parTemporal,
              }  
              parienteServices.sEditarPariente(paramDatos).then(function (rpta) {
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';
                  $scope.getPaginationServerSidePAR();
                  $scope.fData.parTemporal = {};
                  $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[0];
                  $scope.fData.parTemporal.vive = $scope.listaVive[0];
                  $scope.fData.parTemporal.parentesco = 'NONE';
                  $scope.editarParienteBool = false; 
                  $scope.classEditPanel = null;
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              });
            }
            $scope.btnQuitarDeLaCesta = function (row) {
              if( row.entity.es_temporal && row.entity.es_temporal === true ){
                var index = $scope.gridOptionsParientes.data.indexOf(row.entity); 
                $scope.gridOptionsParientes.data.splice(index,1); 
                $scope.classEditPanel = null;
                return;
              }
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){ 
                  $scope.classEditPanel = null;
                  // console.log(row.entity.idpariente);
                  parienteServices.sAnularPariente(row.entity).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      $scope.getPaginationServerSidePAR();

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
            $scope.agregarParienteACesta = function (){ 
              $('#temporalNombrePar').focus();
              if( !($scope.fData.parTemporal.nombres) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Nombres está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.parTemporal.ap_paterno) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Apellido Paterno  está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.parTemporal.ap_materno) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Apellido Materno está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.parTemporal.parentesco) || $scope.fData.parTemporal.parentesco == 'NONE' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Parentesco está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              blockUI.start('Procesando Información...'); 
              var arrTemporal = { 
                'pariente' : $scope.fData.parTemporal.nombres+' '+$scope.fData.parTemporal.ap_paterno+' '+$scope.fData.parTemporal.ap_materno,
                'nombres': $scope.fData.parTemporal.nombres,
                'ap_paterno': $scope.fData.parTemporal.ap_paterno,
                'ap_materno': $scope.fData.parTemporal.ap_materno,
                'parentesco' : $scope.fData.parTemporal.parentesco,
                'fecha_nac' : $scope.fData.parTemporal.fecha_nacimiento,
                'ocupacion' : $scope.fData.parTemporal.ocupacion,
                'estado_civil_obj' : $scope.fData.parTemporal.estado_civil,
                'vive_obj' : $scope.fData.parTemporal.vive,
                'estado_civil' : $scope.fData.parTemporal.estado_civil.descripcion,
                'vive' : $scope.fData.parTemporal.vive.descripcion,
                'direccion' : $scope.fData.parTemporal.direccion,
                'telefono' : $scope.fData.parTemporal.telefono,
                'notificar_emergencia': $scope.fData.parTemporal.notificar_emergencia,
                'es_temporal': true
              }; 
              arrTemporal.idempleado = $scope.mySelectionGrid[0].id;

              parienteServices.sAgregarPariente(arrTemporal).then(function (rpta) { 
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Algo salió mal...');
                }
                blockUI.stop();
                $scope.getPaginationServerSidePAR();
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
              // $scope.fData.conTemporal = {
              //   idcargo : null
              // };
              // $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
              // $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];

              // $scope.gridOptionsParientes.data.push(arrTemporal); 

              $scope.fData.parTemporal = {};
              $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[0];
              $scope.fData.parTemporal.vive = $scope.listaVive[0];
              $scope.fData.parTemporal.parentesco = 'NONE';
            }

          // ================
          // CONTRATOS - edicion 
          // ================
            var paginationOptionsHC = {
              pageNumber: 1,
              firstRow: 0,
              pageSize: 50,
              sort: uiGridConstants.DESC,
              sortName: null,
              search: null
            };
            $scope.gridOptionsContrato = { 
              paginationPageSizes: [10, 50, 100, 500, 1000],
              minRowsToShow: 9,
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              useExternalFiltering : true,
              enableGridMenu: true,
              enableSorting: false,
              enableSelectAll: true,
              enableFiltering: false,
              data: null,
              rowHeight: 30,
              columnDefs: [ 
                { field: 'codigo', name: 'id', displayName: 'COD.', width: '50' },
                { field: 'empresa', name: 'razon_social', displayName: 'EMPRESA', width: '170' },
                { field: 'cargo', name: 'descripcion_ca', displayName: 'CARGO', width: '170' },
                { field: 'condicion_laboral', name: 'condicion_laboral', displayName: 'COND. LABORAL', width: '140' },
                { field: 'fecha_ing', name: 'hc.fecha_ingreso', displayName: 'FECHA ING.', width: '90' },
                { field: 'fecha_ini_contrato', name: 'hc.fecha_inicio_contrato', displayName: 'FEC. INI. CONT.', width: '120', sort: { direction: uiGridConstants.DESC} },
                { field: 'fecha_fin_contrato', name: 'hc.fecha_fin_contrato', displayName: 'FEC. FIN CONT.', width: '120' },
                { field: 'vigente_string', name: 'hc.contrato_actual', displayName: 'VIGENTE', width: '80', pinnedRight:true, cellClass: 'text-center' },
                { field: 'archivo', /*, name: ''*/ pinnedRight:true, displayName: '', width: '50', type: 'object', visible:false,
                  cellTemplate:'<div ng-show="COL_FIELD.hay_archivo"> <a target="_blank" href="{{ grid.appScope.dirContratosEmpleados + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </a> </div>' },
                { field: 'accion', /*, name: ''*/pinnedRight:true, displayName: '', width: 80, enableCellEdit: false, enableSorting: false, 
                  cellTemplate:'<div class="ui-grid-cell-contents"><button type="button" class="btn btn-sm btn-warning mr-xs" ng-click="grid.appScope.btnEditarContrato(row)"> <i class="fa fa-edit"></i> </button>' + 
                  '<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCestaHC(row)"> <i class="fa fa-trash"></i> </button></div>' 
                } 
              ],
              onRegisterApi: function(gridApi) { 
                $scope.gridApi = gridApi;
                $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
                  if (sortColumns.length == 0) {
                    paginationOptionsHC.sort = null;
                    paginationOptionsHC.sortName = null;
                  } else {
                    paginationOptionsHC.sort = sortColumns[0].sort.direction;
                    paginationOptionsHC.sortName = sortColumns[0].name;
                  }
                  $scope.getPaginationServerSideHC();
                });
                gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
                  paginationOptionsHC.pageNumber = newPage;
                  paginationOptionsHC.pageSize = pageSize;
                  paginationOptionsHC.firstRow = (paginationOptionsHC.pageNumber - 1) * paginationOptionsHC.pageSize;
                  $scope.getPaginationServerSideHC();
                });
              }
            };
            paginationOptionsHC.sortName = $scope.gridOptionsContrato.columnDefs[5].name;
            $scope.getPaginationServerSideHC = function () {
              var arrParams = {
                paginate : paginationOptionsHC,
                datos: $scope.mySelectionGrid[0]
              };
              historialContratoServices.sListarContratosDeEmpleado(arrParams).then(function (rpta) {
                $scope.gridOptionsContrato.totalItems = rpta.paginate.totalRows;
                $scope.gridOptionsContrato.data = rpta.datos;
              });
            }
            $scope.getPaginationServerSideHC();

            $scope.fData.conTemporal.contrato_vigente = 1;
            $scope.agregarContratoACesta = function (){ 
              // console.log($scope.fData.conTemporal.empresaadmin.id,'$scope.fData.conTemporal.empresaadmin.id');
              if( !($scope.fData.conTemporal.empresaadmin.id) || $scope.fData.conTemporal.empresaadmin.id == 'NONE' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Empresa está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.condicion_laboral.id) || $scope.fData.conTemporal.condicion_laboral.id == 'NONE' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Condición Laboral está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.idcargo)  ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Cargo está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.fecha_ingreso) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Fecha Ingreso está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.fecha_inicio_contrato) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Fecha Inicio Contrato está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.fecha_fin_contrato) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Fecha Fin Contrato está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              blockUI.start('Ejecutando proceso...');
              var arrTemporal = { 
                'empresa' : $scope.fData.conTemporal.empresaadmin.descripcion,
                'empresa_obj' : $scope.fData.conTemporal.empresaadmin,
                'cargo': $scope.fData.conTemporal.cargo.descripcion,
                'idcargo': $scope.fData.conTemporal.idcargo,
                'cargo_obj': $scope.fData.conTemporal.cargo,
                'condicion_laboral_obj': $scope.fData.conTemporal.condicion_laboral,
                'condicion_laboral': $scope.fData.conTemporal.condicion_laboral.descripcion,
                'fecha_ing': $scope.fData.conTemporal.fecha_ingreso,
                'fecha_cese': $scope.fData.conTemporal.fecha_cese,
                'fecha_ini_contrato': $scope.fData.conTemporal.fecha_inicio_contrato,
                'fecha_fin_contrato' : $scope.fData.conTemporal.fecha_fin_contrato,
                'sueldo' : $scope.fData.conTemporal.sueldo,
                'vigente' : $scope.fData.conTemporal.contrato_vigente == 1 ? 'SI' : 'NO',
                'vigenteBool' : $scope.fData.conTemporal.contrato_vigente
              }; 

              //$scope.gridOptionsContrato.data.push(arrTemporal); sAgregarContratoDeEmpleado 
              arrTemporal.idempleado = $scope.mySelectionGrid[0].id;
              historialContratoServices.sAgregarContratoDeEmpleado(arrTemporal).then(function (rpta) { 
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';
                  $scope.fData.conTemporal = {
                    idcargo : null
                  };
                  $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
                  $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
                  $scope.fData.conTemporal.contrato_vigente = 1;
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Algo salió mal...');
                }
                blockUI.stop();
                $scope.getPaginationServerSideHC();
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
              
            }
            $scope.btnEditarContrato = function (row) { 
              blockUI.start('Procesando...');
              $scope.editarContratoBool = true;
              $scope.classEditPanel = 'ui-editPanel';
              var indexLEA = 0;
              angular.forEach($scope.metodos.listaEmpresaAdmin,function (val, key) { 
                if( angular.equals(row.entity.empresa_obj, val) ){
                  indexLEA = key;
                }
              });
              var indexLCL = 0;
              angular.forEach($scope.listaCondicionLaboral,function (val, key) { 
                if( angular.equals(row.entity.condicion_laboral_obj, val) ){ 
                  indexLCL = key;
                }
              });
              $scope.fData.conTemporal.idempleado = $scope.mySelectionGrid[0].id;
              $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[indexLEA];
              $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[indexLCL];
              $scope.fData.conTemporal.idcargo = row.entity.idcargo;
              $scope.fData.conTemporal.cargo = row.entity.cargo_obj;
              $scope.fData.conTemporal.fecha_ingreso = row.entity.fecha_ing;
              $scope.fData.conTemporal.fecha_cese = row.entity.fecha_cese;
              $scope.fData.conTemporal.fecha_inicio_contrato = row.entity.fecha_ini_contrato;
              $scope.fData.conTemporal.fecha_fin_contrato = row.entity.fecha_fin_contrato;
              $scope.fData.conTemporal.sueldo = row.entity.sueldo;
              $scope.fData.conTemporal.contrato_vigente = row.entity.vigente;
              $scope.fData.conTemporal.idrow = row.entity.$$hashKey;
              $scope.fData.conTemporal.codigo = row.entity.codigo;
              blockUI.stop();
            }
            $scope.actualizarContrato = function () { 
              blockUI.start('Procesando...');
              $scope.fData.conTemporal.empresa_obj = $scope.fData.conTemporal.empresaadmin;
              historialContratoServices.sEditarContrato($scope.fData.conTemporal).then(function (rpta) { 
                if(rpta.flag == 1){ 
                  var pTitle = 'OK!';
                  var pType = 'success';
                  $scope.getPaginationServerSideHC();
                  $scope.fData.conTemporal = {
                    idcargo : null
                  };
                  $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
                  $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
                  $scope.fData.conTemporal.contrato_vigente = 1;

                  $scope.editarContratoBool = false;
                  $scope.classEditPanel = '';
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
                blockUI.stop();
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              });
            }
            $scope.btnQuitarDeLaCestaHC = function (row) { 
              blockUI.start('Procesando...');
              $scope.editarContratoBool = false;
              $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
              $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
              $scope.classEditPanel = null;
              blockUI.stop();
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){ 
                  blockUI.start('Procesando...');
                  historialContratoServices.sAnularContrato(row.entity).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Error inesperado');
                    }
                    $scope.getPaginationServerSideHC();
                    blockUI.stop();
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                  });
                }
              });
              // return;
            }
          
          // ================
          // ESTUDIOS - edicion  
          // ================ 
            $scope.fData.estTemporal = {};
            $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[0];
            $scope.fData.estTemporal.estudio_completo = $scope.listaEstudioCompleto[0].id;
            $scope.cargarNivelEstudio = function(tipo, row){ 
              var row = row || false;
              empleadoServices.sListarNivelEstudio(tipo).then(function (rpta) { 
                $scope.listaNivelEstudio = rpta.datos;
                if($scope.editarEstudioBool){
                  if(row){
                    angular.forEach($scope.listaNivelEstudio, function(value, key){
                      if(row.entity.id == value.id){
                        $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[key];
                      }
                    });
                  }else{
                    $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
                  }
                }else{
                  $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
                }
              });
            }
            $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id);
            $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
            $scope.gridOptionsEstudios = {
              paginationPageSizes: [10, 50, 100, 500, 1000],
              minRowsToShow: 9,
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              useExternalFiltering : true,
              enableGridMenu: true,
              enableSelectAll: true,
              enableFiltering: false,
              data: null,
              rowHeight: 30,
              columnDefs: [ 
                { field: 'nivel_estudio', name: 'descripcion_ne', displayName: 'NIVEL  DE ESTUDIO', width: '10%', enableCellEdit: false, enableSorting: false },
                { field: 'centro_estudio', name: 'centro_estudio', displayName: 'CENTRO DE ESTUDIOS', width: '20%', enableCellEdit: false, enableSorting: false },
                { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD', width: '24%', enableCellEdit: false, enableSorting: false },
                { field: 'fecha_desde', name: 'fecha_desde', displayName: 'FECHA DESDE', width: '9%', enableCellEdit: true, enableSorting: false },
                { field: 'fecha_hasta', name: 'fecha_hasta', displayName: 'FECHA HASTA', width: '9%', enableCellEdit: true, enableSorting: false },
                { field: 'estudio_completo', name: 'estudio_completo', displayName: 'COMPLETO / INCOMPLETO', width: '10%', enableCellEdit: false, 
                    enableSorting: false,
                    cellTemplate: '<div class="text-center ui-grid-cell-contents" ng-if="COL_FIELD == 1">COMPLETO</div><div class="text-center text-red" ng-if="COL_FIELD == 2">INCOMPLETO</div>'
                },
                { field: 'grado_academico', name: 'grado_academico', displayName: 'GRADO ACADEMICO OBTENIDO', width: '12%', enableCellEdit: false, enableSorting: false },
                { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, 
                  cellTemplate:'<button type="button" class="btn btn-sm btn-warning mr" ng-click="grid.appScope.btnEditarEstudio(row)"> <i class="fa fa-edit"></i> </button>' + 
                  '<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCestaEST(row)"> <i class="fa fa-trash"></i> </button>'
                }
               
              ]
            };
            $scope.getPaginationServerSideEST = function () {
              var arrParams = {
                datos: $scope.mySelectionGrid[0]
              };
              empleadoServices.sCargarEstudiosEmpleado(arrParams).then(function (rpta) {
                $scope.gridOptionsEstudios.data = rpta.datos;
              });
            }
            $scope.getPaginationServerSideEST();
            $scope.btnEditarEstudio = function (row) { 
              $scope.editarEstudioBool = true;
              $scope.classEditPanel = 'ui-editPanel';
              var tipo_est = parseInt(row.entity.tipo_nivel);
              angular.forEach($scope.listaTipoEstudio, function(value, key){
                if(row.entity.tipo_nivel == value.id){
                  $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[key];
                }
              });
              $scope.listaNivelEstudio = $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id, row);
              $scope.fData.estTemporal.centro_estudio = row.entity.centro_estudio;
              $scope.fData.estTemporal.fecha_desde = row.entity.fecha_desde;
              $scope.fData.estTemporal.fecha_hasta = row.entity.fecha_hasta;
              $scope.fData.estTemporal.estudio_completo = parseInt(row.entity.estudio_completo);
              $scope.fData.estTemporal.especialidad = row.entity.especialidad;
              $scope.fData.estTemporal.grado_academico = row.entity.grado_academico;
              $scope.fData.estTemporal.iddetalleestudio = row.entity.iddetalleestudio;
            }
            $scope.actualizarEstudio = function (){
              var paramDatos = {
                datos: $scope.fData.estTemporal,
              } 
              empleadoServices.sEditarEstudio(paramDatos).then(function (rpta) {
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';
                  $scope.getPaginationServerSideEST();
                  $scope.fData.estTemporal = {};
                  $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[0];
                  $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id);
                  $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
                  $scope.fData.estTemporal.estudio_completo = $scope.listaEstudioCompleto[0].id;
                  $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
                  $scope.editarEstudioBool = false;
                  $scope.classEditPanel = '';
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Error inesperado');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2000 });
              });
            }
            $scope.agregarEstudioACesta = function (){ 
              
              //$('#temporalNombrePar').focus();
              if($scope.fData.estTemporal.tipo_estudio.id ==  1){
                $scope.fData.estTemporal.grado_academico = null;
                $scope.fData.estTemporal.especialidad = null;
              }
              if( !($scope.fData.estTemporal.centro_estudio) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo "Centro de Estudios" está vacio', type: 'warning', delay: 2000 });
                $('#centro_estudio').focus();
                return false;
              }
              if( !($scope.fData.estTemporal.fecha_desde) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo "Fecha Desde" está vacio', type: 'warning', delay: 2000 });
                $('#fecha_desde').focus();
                return false;
              }
              if( !($scope.fData.estTemporal.fecha_hasta) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo "Fecha Hasta" está vacio', type: 'warning', delay: 2000 });
                $('#fecha_hasta').focus();
                return false;
              }
              if( $scope.fData.estTemporal.estudio_completo == 0 ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione una opcion: Completo o Incompleto', type: 'warning', delay: 2000 });
                return false;
              }
              blockUI.start('Procesando Información...'); 
              var arrTemporal = { 
                'id': $scope.fData.estTemporal.nivel_estudio.id,
                'nivel_estudio': $scope.fData.estTemporal.nivel_estudio.descripcion,
                'centro_estudio': $scope.fData.estTemporal.centro_estudio,
                'especialidad' : $scope.fData.estTemporal.especialidad,
                'fecha_desde' : $scope.fData.estTemporal.fecha_desde,
                'fecha_hasta' : $scope.fData.estTemporal.fecha_hasta,
                'estudio_completo' : $scope.fData.estTemporal.estudio_completo,
                'grado_academico' : $scope.fData.estTemporal.grado_academico,
                'es_temporal': true
              }; 
              // $scope.gridOptionsEstudios.data.push(arrTemporal); 
              arrTemporal.idempleado = $scope.mySelectionGrid[0].id;
              empleadoServices.sAgregarEstudioDeEmpleado(arrTemporal).then(function (rpta) { 
                if(rpta.flag == 1){
                  var pTitle = 'OK!';
                  var pType = 'success';
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Algo salió mal...');
                }
                blockUI.stop();
                $scope.getPaginationServerSideEST();
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });

              $scope.fData.estTemporal = {};
              $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[0];
              $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id);
              $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
              $scope.fData.estTemporal.estudio_completo = $scope.listaEstudioCompleto[0].id;
              $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
              // blockUI.stop(); 
            }
            $scope.btnQuitarDeLaCestaEST = function (row) {
              if( row.entity.es_temporal && row.entity.es_temporal === true ){
                var index = $scope.gridOptionsEstudios.data.indexOf(row.entity); 
                $scope.gridOptionsEstudios.data.splice(index,1); 
                $scope.classEditPanel = '';
                return;
              }
              var pMensaje = '¿Realmente desea realizar la acción?';
              $bootbox.confirm(pMensaje, function(result) { 
                if(result){ 
                  empleadoServices.sAnularEstudio(row.entity).then(function (rpta) {
                    if(rpta.flag == 1){
                      var pTitle = 'OK!';
                      var pType = 'success';
                      $scope.getPaginationServerSideEST();
                      $scope.classEditPanel = '';
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
          
          //=============================================================
          // USUARIOS - edicion
          //=============================================================
            $scope.verPopupListaUsuarios = function (size) { 
              $modal.open({
                templateUrl: angular.patchURLCI+'configuracion/ver_popup_combo',
                size: size || '',
                controller: function ($scope, $modalInstance, arrToModal) {
                  $scope.fData = arrToModal.fData;
                  $scope.mySelectionGrid = arrToModal.mySelectionGrid;
                  usuarioServices.sListarUsuariosCbo().then(function (rpta) {
                    $scope.fpc = {};
                    $scope.fpc.titulo = ' Usuario.';
                    $scope.fpc.lista = rpta.datos;
                    //$scope.selected = 0;
                    $scope.selected = $scope.fData.idusers || null;
                    $scope.fpc.selectedItem = function (row) { 
                      $scope.selected = row.id;
                      $scope.fData.idusuario = row.id;
                      $scope.fData.usuario = row.descripcion;
                      $modalInstance.dismiss('cancel');
                    }
                    $scope.fpc.buscar = function () { 
                      $scope.fpc.nameColumn = 'username';
                      $scope.fpc.lista = null;
                      usuarioServices.sListarUsuariosCbo($scope.fpc).then(function (rpta) {
                        $scope.fpc.lista = rpta.datos;
                      });
                    }
                  });
                },
                resolve: {
                  arrToModal: function() {
                    return {
                      fData : $scope.fData,
                      //mySelectionGrid: mySelectionGrid
                    }
                  }
                }
              });
            }
          // ---------------------------------------------------------------
          // AGREGAMOS UN USUARIO NUEVO DESDE AQUI PARA NO TENER QUE SALIR
          // ---------------------------------------------------------------
            $scope.nuevoUsuario = function (size){ 
              $modal.open({
                templateUrl: angular.patchURLCI+'usuario/ver_popup_formulario',
                size: size || '',
                backdrop: 'static',
                keyboard:false,
                scope: $scope,
                controller: function ($scope, $modalInstance) { 
                  $scope.fDataUsuario = {};
                  $scope.fDataUsuario.grupoId = null;
                  $scope.userTemporal = {};
                  $scope.userTemporal.empresa = {};
                  $scope.boolForm = 'reg';
                  $scope.titleForm = 'Registro de usuario';
                  grupoServices.sListarGruposCbo().then(function (rpta) {
                    $scope.listaGrupos = rpta.datos;
                    $scope.listaGrupos.splice(0,0,{ id : '', descripcion:'--Seleccione grupo--'});
                    $scope.fDataUsuario.grupoId = $scope.listaGrupos[0].id;

                  });

                  // ******* LISTA SOLO SEDES ******* 
                  $scope.cargarSoloSedes = function(){ 
                    sedeServices.sListarSedeCbo().then(function (rpta) {
                      $scope.listaSede = rpta.datos;
                      $scope.userTemporal.sede = $scope.listaSede[0];
                    });  
                  };
                  /* LOGICA DE SEDES */ 
                  $scope.fDataUsuario.siEmpresa = true;
                  $scope.fDataUsuario.siSedeDeEmpresa = true;
                  $scope.fDataUsuario.siSoloSede = false;
                  if( $scope.fSessionCI.key_group == 'key_rrhh' || $scope.fSessionCI.key_group == 'key_rrhh_asistente' || $scope.fSessionCI.key_group == 'key_gerencia' ){ 
                    //console.log('entréee');
                    $scope.fDataUsuario.siEmpresa = false;
                    $scope.fDataUsuario.siSedeDeEmpresa = false;
                    $scope.fDataUsuario.siSoloSede = true;
                    $scope.cargarSoloSedes();
                  }else{
                    // ******* LISTA DE EMPRESAS ADMIN ******* 
                    empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) {
                      $scope.listaEmpresaAdmin = rpta.datos;
                      $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'--Seleccione Empresa--'});
                      $scope.userTemporal.empresa = $scope.listaEmpresaAdmin[0];
                      $scope.cargarSedes($scope.userTemporal.empresa);
                    });
                    // ******* LISTA DE SEDES *******
                    $scope.cargarSedes = function(empresaadmin){
                      if(empresaadmin.id == '0'){
                        $scope.listaSede = [];
                        $scope.listaSede.push({ id : '0', descripcion:'--Primero seleccione Empresa--'});
                        $scope.userTemporal.sede = $scope.listaSede[0];
                      }else{
                        sedeServices.sListarSedePorEmpresaCbo(empresaadmin).then(function (rpta) {
                          $scope.listaSede = rpta.datos;
                          //$scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
                          $scope.userTemporal.sede = $scope.listaSede[0];
                        });  
                      }
                    };
                  }

                  // ******* LISTA DE EMPRESAS ADMIN *******
                  // empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) {
                  //   $scope.listaEmpresaAdmin = rpta.datos;
                  //   $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'--Seleccione Empresa--'});
                  //   $scope.userTemporal.empresa = $scope.listaEmpresaAdmin[0];
                  //   $scope.cargarSedes($scope.userTemporal.empresa);
                  // });
                  // ******* LISTA DE SEDES *******
                  // $scope.cargarSedes = function(empresaadmin){
                  //   if(empresaadmin.id == '0'){
                  //     $scope.listaSede = [];
                  //     $scope.listaSede.push({ id : '0', descripcion:'--Primero seleccione Empresa--'});
                  //     $scope.userTemporal.sede = $scope.listaSede[0];
                  //   }else{
                  //     sedeServices.sListarSedePorEmpresaCbo(empresaadmin).then(function (rpta) {
                  //       $scope.listaSede = rpta.datos;
                  //       //$scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
                  //       $scope.userTemporal.sede = $scope.listaSede[0];
                  //     });  
                  //   }
                  // };
                  /* GRILLA EMPRESA  - SEDE  */
                  $scope.gridOptionsEmpresaSede = {
                    paginationPageSizes: [10, 50, 100],
                    minRowsToShow: 9,
                    paginationPageSize: 50,
                    useExternalPagination: true,
                    useExternalSorting: true,
                    useExternalFiltering : true,
                    enableGridMenu: true,
                    enableFiltering: false,
                    data: null,
                    rowHeight: 30,
                    columnDefs: [
                      { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: '40%', enableCellEdit: false, enableSorting: false },
                      { field: 'sede', name: 'sede', displayName: 'SEDE', width: '40%', enableCellEdit: false, enableSorting: false },
                      { field: 'accion', displayName: 'ACCIÓN', width: '20%', enableCellEdit: false, enableSorting: false,
                        cellTemplate:'<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>'
                      }
                    ]
                  };
                  $scope.getPaginationServerSideSede = function () {
                    var arrParams = {
                      //paginate : paginationOptionsSede,
                      datos: $scope.mySelectionGrid[0]
                    };
                    empresaAdminServices.sListarSedeEmpresaAdminUsuario(arrParams).then(function (rpta) {
                      //$scope.gridOptionsEmpresaSede.totalItems = rpta.paginate.totalRows;
                      $scope.gridOptionsEmpresaSede.data = rpta.datos;
                    });
                  }
                  $scope.getPaginationServerSideSede();
                  $scope.agregarSedeACesta = function (){
                    var sedeNew = true;
                    angular.forEach($scope.gridOptionsEmpresaSede.data, function(value, key) { 
                      if( value.idsedeempresaadmin == $scope.userTemporal.sede.id ){ 
                        sedeNew = false;
                      }
                    });
                    if( !sedeNew ){ 
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'La sede ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
                      return false;
                    }
                    if( $scope.userTemporal.sede.id == '0' ){
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'Debe seleccionar una Empresa / Sede', type: 'warning', delay: 2000 });
                      return false;
                    }
                    var arrTemporal = {
                      'empresa': $scope.userTemporal.empresa.descripcion,
                      'sede': $scope.userTemporal.sede.descripcion,
                      'idsede': $scope.userTemporal.sede.idsede,
                      'idsedeempresaadmin': $scope.userTemporal.sede.id,
                      'es_temporal': true
                    };
                    console.log('array ', arrTemporal);
                    $scope.gridOptionsEmpresaSede.data.push(arrTemporal); 
                  }
                  $scope.btnQuitarDeLaCesta = function (row) { 
                    if( row.entity.es_temporal && row.entity.es_temporal === true ){
                      var index = $scope.gridOptionsEmpresaSede.data.indexOf(row.entity); 
                      $scope.gridOptionsEmpresaSede.data.splice(index,1); 
                      return;
                    }
                    var pMensaje = '¿Realmente desea realizar la acción?';
                    $bootbox.confirm(pMensaje, function(result) { 
                      if(result){
                        console.log(row.entity.idusersporsede);
                        usuarioServices.sQuitarSedeDeUsuario(row.entity).then(function (rpta) {
                          if(rpta.flag == 1){
                            var pTitle = 'OK!';
                            var pType = 'success';
                            $scope.getPaginationServerSideSede();
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
                  $scope.cancel = function () {
                    $modalInstance.dismiss('cancel');
                  }
                  $scope.aceptar = function () {
                    var paramDatos = {
                      'dataUsuario' : $scope.fDataUsuario,
                      'sedesEmpresa' : $scope.gridOptionsEmpresaSede.data
                    }
                    usuarioServices.sRegistrar(paramDatos).then(function (rpta) {
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
                      $scope.fData.idusuario = rpta.idusuario;
                      $scope.fData.usuario = rpta.usuario;
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    });
                  }
                }
              });
            }
          //=============================================================
          // AUTOCOMPLETADO PROFESION - edicion
          //=============================================================
            $scope.getProfesionAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return profesionServices.sListarProfesionPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsProfesion = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsProfesion = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedProfesion = function ($item, $model, $label) {
                $scope.fData.idprofesion = $item.id;
            };
            $scope.getClearInputProfesion = function () { 
              if(!angular.isObject($scope.fData.profesion) ){ 
                $scope.fData.idprofesion = null; 
              }
            }
          //=============================================================
          // AUTOCOMPLETADO EMPLEADO JEFE - edicion
          //=============================================================
            $scope.getJefeAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return empleadoServices.sListarEmpleadoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsJefe = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsJefe = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedJefe = function ($item, $model, $label) {
                $scope.fData.idempleadojefe = $item.id;
            };
            $scope.getClearInputJefe = function () { 
              if(!angular.isObject($scope.fData.jefe) ){ 
                $scope.fData.idempleadojefe = null; 
              }
            }
          // ================
          // ASIGNAR SEDE - edicion
          // ================
            $scope.getSedeAutocomplete = function (value) {
              var params = {
                nameColumn: 'descripcion',
                search: value,
                sensor: false
              }
              return sedeServices.sListarSedeCbo(params).then(function(rpta) { 
                $scope.noResultsLD = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLD = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedSede = function ($item, $model, $label) { 
                $scope.fData.idsede = $item.id;
            };
            $scope.getClearInputSede = function () { 
              if(!angular.isObject($scope.fData.sede) ){ 
                $scope.fData.idsede = null; 
              }
            }

          //=============================================================
          // AUTOCOMPLETADO CARGO - edicion
          //=============================================================
            $scope.getCargoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return cargoServices.sListarCargoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLCargo = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLCargo = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedCargo = function ($item, $model, $label) {
                $scope.fData.idcargo = $item.id;
            };
            $scope.getSelectedCargoHC = function ($item, $model, $label) {
              $scope.fData.conTemporal.idcargo = $item.id;
            };
            $scope.getClearInputCargo = function () { 
              if(!angular.isObject($scope.fData.cargo) ){ 
                $scope.fData.idcargo = null; 
              }
            }
            $scope.getClearInputCargoHC = function () { 
              if(!angular.isObject($scope.fData.conTemporal.cargo) ){ 
                $scope.fData.conTemporal.idcargo = null; 
              }
            }
          //=============================================================
          // AUTOCOMPLETADO CARGO DEL SUPERIOR - edicion
          //=============================================================
            $scope.getCargoSupAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              };
              return cargoServices.sListarCargoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLCargoSup = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLCargoSup = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedCargoSup = function ($item, $model, $label) { 
                $scope.fData.idcargosup = $item.id;
            };
            $scope.getClearInputCargoSup = function () { 
              if(!angular.isObject($scope.fData.cargo_sup) ){ 
                $scope.fData.idcargosup = null; 
              }
            }
          // =============================================================
          // AUTOCOMPLETADO EMPRESA - edicion
          // =============================================================
            $scope.getEmpresaAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return empresaServices.sListarEmpresasCbo(params).then(function(rpta) { 
                $scope.noResultsEmpresa = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsEmpresa = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedEmpresa = function ($item, $model, $label) {
                $scope.fData.idempresa = $item.id;
            };
            $scope.getClearInputEmpresa = function () {
              if(!angular.isObject($scope.fData.empresa)){
                $scope.fData.idempresa = null;
              }
            }

          // =============================================================
          // AUTOCOMPLETADO SOLO ESPECIALIDAD - edicion
          // =============================================================
            $scope.getSoloEspecialidadAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsSoloEspecialidad = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsSoloEspecialidad = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedSoloEspecialidad = function ($item, $model, $label) {
                $scope.fData.idespecialidad = $item.id;
            };
            $scope.getClearInputSoloEspecialidad = function () {
              if(!angular.isObject($scope.fData.soloEspecialidad)){ 
                $scope.fData.idespecialidad = null;
              }
            }
          //=============================================================
          // FIN AUTOCOMPLETADO
          //=============================================================
          $scope.cancel = function () {
            // console.log('load me');
            $modalInstance.dismiss('cancel');
            $scope.fData = {}; 
            $scope.getPaginationServerSide(); 
          }
          $scope.aceptar = function () {
            $scope.fData.fecha_nacimiento = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
            console.log($scope.fData);
            var formData = new FormData();
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });
            formData.append('parientes',JSON.stringify($scope.gridOptionsParientes.data));
            formData.append('estudios',JSON.stringify($scope.gridOptionsEstudios.data));
            formData.append('afp',JSON.stringify($scope.fData.afp));
            formData.append('comision_afp',JSON.stringify($scope.fData.comision_afp));
            formData.append('tipoDocumento',JSON.stringify($scope.fData.tipoDocumento));
            formData.append('area_empresa',JSON.stringify($scope.fData.area_empresa));
            formData.append('categoriaPersonalSalud',JSON.stringify($scope.fData.categoriaPersonalSalud));
            formData.append('banco',JSON.stringify($scope.fData.banco));
            blockUI.start('Ejecutando proceso...');
            empleadoServices.sEditar(formData).then(function (rpta) {
              blockUI.stop();
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $modalInstance.dismiss('cancel');
                $scope.fData = {};
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              $scope.getPaginationServerSide();
              
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
            });
          }
          blockUI.stop(); 
          //console.log($scope.mySelectionGrid);
          //blockUI.stop();
        }, 
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          },
          dirImages : function () {
            return $scope.dirImages;
          }, 
          dateUI: function() {
            return $scope.dateUI;
          }
        }
      });
    } // fin editar
    $scope.btnNuevoEmpleado = function (datos) {
      blockUI.start('Cargando Formulario');
      $uibModal.open({
        templateUrl: angular.patchURLCI+'empleado/ver_popup_formulario',
        size: 'xlg',
        backdrop: 'static',
        scope: $scope,
        controller: function ($scope, $modalInstance, getPaginationServerSide,dateUI) { 
          $scope.accion = 'reg';
          $scope.getPaginationServerSide = getPaginationServerSide;
          $scope.dateUI = dateUI;
          $scope.fData = {};
          $scope.fData.estTemporal = {};
          $scope.fData.parTemporal = {};
          $scope.editarContratoBool = false;
          $scope.fData.estado_civil = 1;
          //$scope.fData.operador_movil = 'NONE';
          $scope.fData.reg_pensionario = $scope.listaRegPensionario[0].id;
          $scope.fData.nombre_foto = 'noimage.jpg';
          $scope.fData.afp = $scope.listaAFP[0];
          $scope.fData.comision_afp = $scope.listComisionAFP[0];
          $scope.fData.area_empresa = $scope.listaAreaEmpresa[0];
          $scope.fData.banco = $scope.listaBancos[0];
          $scope.fData.idsubcatcentrocosto = $scope.listaSubCatCentroCosto[0].id;
          $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
          $scope.fData.condicion_laboral = $scope.listaCondicionLaboral[0].id;
          $scope.fData.sexo = 'NONE';
          $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[0];
          $scope.fData.parTemporal.vive = $scope.listaVive[0];
          $scope.fData.parTemporal.parentesco = 'NONE';
          $scope.fData.tipoDocumento = $scope.listaTipoDocumento[0];
          $scope.fData.categoriaPersonalSalud = $scope.listaCategoriaPersonalSalud[0];

          $scope.cargarCentroCosto = function(idsubcatcentrocosto){
            centroCostoServices.sListarCentroCostoCbo(idsubcatcentrocosto).then(function (rpta) { 
              $scope.listaCentroCosto = rpta.datos;
              $scope.listaCentroCosto.splice(0,0,{ id : '', descripcion:'-- Seleccione --'});
              $scope.fData.idcentrocosto = $scope.listaCentroCosto[0].id;
            });
          }

          $scope.clearSelectRegPensionario = function () {
            $scope.fData.comision_afp = $scope.listComisionAFP[0];
            $scope.fData.afp = $scope.listaAFP[0];
            $scope.fData.cuspp = null;
          }
          $scope.clearSelectCondLaboral = function () {
            $scope.clearSelectRegPensionario();
            $scope.fData.reg_pensionario = $scope.listaRegPensionario[0].id;
          }

          $scope.fData.conTemporal = {
            idcargo : null
          };
          $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
          $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
          $scope.classEditPanel = null;
          $scope.editarContratoBool = false;
          $scope.boolExterior = false;
          $scope.titleForm = 'Registro de Empleado';
          if( $scope.modulo == 'empresa' ){ //  Cuando el formulario se carga desde empresa.js
            $scope.boolExterior = true;
            console.log('empresa cargada por defecto', datos);
            $scope.fData.idempresa = datos.idempresa;
            $scope.fData.empresa = datos.empresa;
            $scope.fData.idempresaespecialidad = datos.idempresaespecialidad;
            $scope.fData.especialidad = datos.especialidad;
            $scope.fData.personalSalud = true;
            $scope.fData.tercero_propio = true;
          }

          $scope.updateAfp = function(){
            //console.log($scope.fData.reg_pensionario);
            if($scope.fData.reg_pensionario != 'AFP'){
              $scope.fData.afp = $scope.listaAFP[0];
            }
          }
          //=============================================================
          // AUTOCOMPLETADO PROFESION editarParienteBool - Nuevo
          //=============================================================
            $scope.getProfesionAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return profesionServices.sListarProfesionPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsProfesion = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsProfesion = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedProfesion = function ($item, $model, $label) {
                $scope.fData.idprofesion = $item.id;
            };
            $scope.getClearInputProfesion = function () { 
              if(!angular.isObject($scope.fData.profesion) ){ 
                $scope.fData.idprofesion = null; 
              }
            }
          //=============================================================
          // AUTOCOMPLETADO CARGO - Nuevo
          //=============================================================
            $scope.getCargoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return cargoServices.sListarCargoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLCargo = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLCargo = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedCargo = function ($item, $model, $label) {
              $scope.fData.idcargo = $item.id;
            };
            $scope.getSelectedCargoHC = function ($item, $model, $label) {
              $scope.fData.conTemporal.idcargo = $item.id;
            };
            $scope.getClearInputCargo = function () { 
              if(!angular.isObject($scope.fData.cargo) ){ 
                $scope.fData.idcargo = null; 
              }
              if(!angular.isObject($scope.fData.conTemporal.cargo) ){ 
                $scope.fData.conTemporal.idcargo = null; 
              }
            }
          //=============================================================
          // AUTOCOMPLETADO CARGO DEL SUPERIOR - Nuevo
          //=============================================================
            $scope.getCargoSupAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              };
              return cargoServices.sListarCargoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLCargoSup = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLCargoSup = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedCargoSup = function ($item, $model, $label) { 
                $scope.fData.idcargosup = $item.id;
            };
            $scope.getClearInputCargoSup = function () { 
              if(!angular.isObject($scope.fData.cargo_sup) ){ 
                $scope.fData.idcargosup = null; 
              }
            }
          // =============================================================
          // AUTOCOMPLETADO EMPRESA - Nuevo
          // =============================================================
            $scope.getEmpresaAutocomplete = function (value) { 
              // console.log(value,'value');
              var params = {
                search: value,
                sensor: false
              }
              return empresaServices.sListarEmpresasCbo(params).then(function(rpta) { 
                $scope.noResultsEmpresa = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsEmpresa = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedEmpresa = function ($item, $model, $label) {
                $scope.fData.idempresa = $item.id;
                if( $scope.modulo == 'empresa' ){
                  console.log('empresa seleccionada');

                }
            };
            $scope.getClearInputEmpresa = function () {
              if(!angular.isObject($scope.fData.empresa)){
                $scope.fData.idempresa = null;
              }
            }
          // =============================================================
          // AUTOCOMPLETADO SOLO ESPECIALIDAD - Nuevo 
          // =============================================================
            $scope.getSoloEspecialidadAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return especialidadServices.sListarSoloEspecialidadPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsSoloEspecialidad = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsSoloEspecialidad = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedSoloEspecialidad = function ($item, $model, $label) {
                $scope.fData.idespecialidad = $item.id;
            };
            $scope.getClearInputSoloEspecialidad = function () {
              //if($scope.fData.soloEspecialidad.length < 1){
              if(!angular.isObject($scope.fData.soloEspecialidad)){
                $scope.fData.idespecialidad = null;
              }
            }
          // =============================================================
          // AUTOCOMPLETADO ESPECIALIDAD - Nuevo 
          // =============================================================
            $scope.getEspecialidadAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return especialidadServices.sListarEspecialidadPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLEspecialidad = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLEspecialidad = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedEspecialidad = function ($item, $model, $label) {
                $scope.fData.idempresaespecialidad = $item.idempresaespecialidad;
            };

          $scope.verPopupListaUsuarios = function (size) { 
            $modal.open({
              templateUrl: angular.patchURLCI+'Configuracion/ver_popup_combo',
              size: size || '',
              controller: function ($scope, $modalInstance, arrToModal) {
                $scope.fData = arrToModal.fData;
                usuarioServices.sListarUsuariosCbo().then(function (rpta) {
                  $scope.fpc = {};
                  $scope.fpc.titulo = ' Usuario.';
                  $scope.fpc.lista = rpta.datos;
                  //$scope.selected = 0;
                  $scope.selected = $scope.fData.idusers || null;
                  $scope.fpc.selectedItem = function (row) { 
                    $scope.selected = row.id;
                    $scope.fData.idusuario = row.id;
                    $scope.fData.usuario = row.descripcion;
                    $modalInstance.dismiss('cancel');
                  }
                  $scope.fpc.buscar = function () { 
                    $scope.fpc.nameColumn = 'username';
                    $scope.fpc.lista = null;
                    usuarioServices.sListarUsuariosCbo($scope.fpc).then(function (rpta) {
                      $scope.fpc.lista = rpta.datos;
                    });
                  }
                });
              },
              resolve: {
                arrToModal: function() {
                  return {
                    fData : $scope.fData
                  }
                }
              }
            });
          };

          // ============================================================
          // UBIGEO - Nuevo 
          // ============================================================
            $scope.getDepartamentoAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return ubigeoServices.sListarDepartamentoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLD = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLD = true;
                }
                return rpta.datos; 
              });
            }
            $scope.obtenerDepartamentoPorCodigo = function () {
              if( $scope.fData.iddepartamento ){
                var arrData = {
                  'codigo': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddepartamento = rpta.datos.id;
                    $scope.fData.departamento = rpta.datos.descripcion;
                    $('#fDatadepartamento').focus();
                  }
                });
              }
            }
            $scope.getSelectedDepartamento = function ($item, $model, $label) {
                $scope.fData.iddepartamento = $item.id;
                $scope.fData.idprovincia = null;
                $scope.fData.provincia = null;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getProvinciaAutocomplete = function (value) {
              var params = {
                search: value,
                id: $scope.fData.iddepartamento,
                sensor: false
              }
              return ubigeoServices.sListarProvinciaPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLP = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLP = true;
                }
                return rpta.datos; 
              });
            }
            $scope.obtenerProvinciaPorCodigo = function () {
              if( $scope.fData.idprovincia ){
                var arrData = {
                  'codigo': $scope.fData.idprovincia,
                  'iddepartamento': $scope.fData.iddepartamento
                }
                ubigeoServices.sListarProvinciaDeDepartamentoPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.idprovincia = rpta.datos.id;
                    $scope.fData.provincia = rpta.datos.descripcion;
                    $('#fDataprovincia').focus();
                  }
                });
              }
            }
            $scope.getSelectedProvincia = function ($item, $model, $label) {
                $scope.fData.idprovincia = $item.id;
                $scope.fData.iddistrito = null;
                $scope.fData.distrito = null;
            };
            $scope.getDistritoAutocomplete = function (value) {
              var params = {
                search: value,
                id_dpto: $scope.fData.iddepartamento,
                id_prov: $scope.fData.idprovincia,
                sensor: false
              }
              return ubigeoServices.sListarDistritoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsLDis = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLDis = true;
                }
                return rpta.datos; 
              });
            }
            $scope.obtenerDistritoPorCodigo = function () {
              if( $scope.fData.iddistrito ){
                var arrData = {
                  'codigo': $scope.fData.iddistrito,
                  'iddepartamento': $scope.fData.iddepartamento,
                  'idprovincia': $scope.fData.idprovincia
                }
                ubigeoServices.sListarDistritosDeProvinciaPorCodigo(arrData).then(function (rpta) {
                  if( rpta.flag == 1){
                    $scope.fData.iddistrito = rpta.datos.id;
                    $scope.fData.distrito = rpta.datos.descripcion;
                    $('#fDatadistrito').focus();
                  }
                });
              }
            }
            $scope.getSelectedDistrito = function ($item, $model, $label) {
                $scope.fData.iddistrito = $item.id;
            };
          //=============================================================
          // AUTOCOMPLETADO EMPLEADO JEFE - Nuevo 
          //=============================================================
            $scope.getJefeAutocomplete = function (value) {
              var params = {
                search: value,
                sensor: false
              }
              return empleadoServices.sListarEmpleadoPorAutocompletado(params).then(function(rpta) { 
                $scope.noResultsJefe = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsJefe = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedJefe = function ($item, $model, $label) {
                $scope.fData.idempleadojefe = $item.id;
            };
            $scope.getClearInputJefe = function () { 
              if(!angular.isObject($scope.fData.jefe) ){ 
                $scope.fData.idempleadojefe = null; 
              }
            }
          // ================
          // ASIGNAR SEDE - Nuevo 
          // ================
            $scope.getSedeAutocomplete = function (value) {
              var params = {
                nameColumn: 'descripcion',
                search: value,
                sensor: false
              }
              return sedeServices.sListarSedeCbo(params).then(function(rpta) { 
                $scope.noResultsLD = false;
                if( rpta.flag === 0 ){
                  $scope.noResultsLD = true;
                }
                return rpta.datos; 
              });
            }
            $scope.getSelectedSede = function ($item, $model, $label) { 
                $scope.fData.idsede = $item.id;
            };
            $scope.getClearInputSede = function () { 
              if(!angular.isObject($scope.fData.sede) ){ 
                $scope.fData.idsede = null; 
              }
            }
            
            $scope.getCargaAlmacenes = function() {
              almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) {
                $scope.listaAlmacen = rpta.datos;
                $scope.listaAlmacen.splice(0,0,{ id : '0', descripcion:'--Seleccione el Almacen--'});
                $scope.fData.idalmacenfarmacia = $scope.listaAlmacen[0].id;    
              });
            }
            $scope.getCargaAlmacenes();    
            
            $scope.OnChangeAlmacen=function(idalm){
              $scope.datosGrid = {
                idalmacen : idalm
              };
              almacenFarmServices.sListarSubAlmacenesDeAlmacenCbo($scope.datosGrid).then(function (rpta) {
                $scope.listaSubalmacen = rpta.datos;
                $scope.listaSubalmacen.splice(0,0,{ id : '0', descripcion:'--Seleccione el SubAlmacen--'});
                $scope.fData.idsubalmacenfarmacia = $scope.listaSubalmacen[0].id; 
              });
            };
          // ================
          // PARIENTES - Nuevo
          // ================
            $scope.gridOptionsParientes = {
              paginationPageSizes: [10, 50, 100, 500, 1000],
              minRowsToShow: 9,
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              useExternalFiltering : true,
              enableGridMenu: true,
              enableFiltering: false,
              data: null,
              rowHeight: 30,
              columnDefs: [ 
                { field: 'pariente', name: 'nombres', displayName: 'APELLIDOS Y NOMBRES', width: '25%', enableCellEdit: false, enableSorting: false },
                { field: 'parentesco', name: 'parentesco', displayName: 'PARENTESCO', width: '15%', enableCellEdit: false, enableSorting: false },
                { field: 'fecha_nac', name: 'fecha_nac', displayName: 'FECHA NAC.', width: '10%', enableCellEdit: true, enableSorting: false },
                { field: 'ocupacion', name: 'ocupacion', displayName: 'OCUPACIÓN', width: '20%', enableCellEdit: false, enableSorting: false },
                { field: 'estado_civil', name: 'estado_civil', displayName: 'ESTADO CIVIL', width: '14%', enableCellEdit: false, enableSorting: false },
                { field: 'vive', name: 'vive', displayName: 'VIVE', width: '10%', enableCellEdit: false, enableSorting: false },
                { field: 'notificar_emergencia', name: 'notificar_emergencia', displayName: 'NOTIF. EMERG.', width: '10%', enableCellEdit: false, enableSorting: false, visible:false },
                { field: 'direccion', name: 'direccion', displayName: 'DIRECCION', width: '18%', enableCellEdit: false, enableSorting: false, visible:false },
                { field: 'telefono', name: 'telefono', displayName: 'TELEFONO', width: '18%', enableCellEdit: false, enableSorting: false, visible:false },
                // { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, 
                //   cellTemplate:'<button type="button" class="btn btn-sm btn-danger center-block" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>' 
                // }
                { field: 'accion', displayName: '', width: 80, enableCellEdit: false, enableSorting: false, 
                  cellTemplate:'<button type="button" class="btn btn-sm btn-warning mr" ng-click="grid.appScope.btnEditarPariente(row)"> <i class="fa fa-edit"></i> </button>' + 
                  '<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>'
                }
              ]
            };
            $scope.btnEditarPariente = function (row) { 
              //console.log('fila ', row.entity); 
              $scope.classEditPanel = 'ui-editPanel';
              $scope.editarParienteBool = true;
              $scope.fData.parTemporal.nombres = row.entity.nombres;
              $scope.fData.parTemporal.ap_paterno = row.entity.ap_paterno;
              $scope.fData.parTemporal.ap_materno = row.entity.ap_materno;
              $scope.fData.parTemporal.parentesco = row.entity.parentesco;
              $scope.fData.parTemporal.ocupacion = row.entity.ocupacion;
              $scope.fData.parTemporal.fecha_nacimiento = row.entity.fecha_nac;
              angular.forEach($scope.listaEstadoCivil, function(value, key){
                if(row.entity.estado_civil_obj == value){
                  $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[key];
                }
              });
              if(row.entity.vive == 'SI'){
                $scope.fData.parTemporal.vive = $scope.listaVive[0];
              }else{
                $scope.fData.parTemporal.vive = $scope.listaVive[1];
              }
              $scope.fData.parTemporal.notificar_emergencia = row.entity.notificar_emergencia;
              $scope.fData.parTemporal.direccion = row.entity.direccion;
              $scope.fData.parTemporal.telefono = row.entity.telefono;
              $scope.fData.estTemporal.idrow = row.entity.$$hashKey;
            }
            $scope.actualizarPariente = function (){ 
              angular.forEach($scope.gridOptionsParientes.data, function(value, key) { 
                if(value.$$hashKey == $scope.fData.estTemporal.idrow ){ 
                  $scope.gridOptionsParientes.data[key].pariente = $scope.fData.parTemporal.nombres+' '+$scope.fData.parTemporal.ap_paterno+' '+$scope.fData.parTemporal.ap_materno;
                  $scope.gridOptionsParientes.data[key].nombres = $scope.fData.parTemporal.nombres;
                  $scope.gridOptionsParientes.data[key].ap_paterno = $scope.fData.parTemporal.ap_paterno;
                  $scope.gridOptionsParientes.data[key].ap_materno = $scope.fData.parTemporal.ap_materno;
                  $scope.gridOptionsParientes.data[key].parentesco = $scope.fData.parTemporal.parentesco;
                  $scope.gridOptionsParientes.data[key].fecha_nac = $scope.fData.parTemporal.fecha_nacimiento;
                  $scope.gridOptionsParientes.data[key].ocupacion = $scope.fData.parTemporal.ocupacion;
                  $scope.gridOptionsParientes.data[key].estado_civil_obj = $scope.fData.parTemporal.estado_civil;
                  $scope.gridOptionsParientes.data[key].vive_obj = $scope.fData.parTemporal.vive;
                  $scope.gridOptionsParientes.data[key].estado_civil = $scope.fData.parTemporal.estado_civil.descripcion;
                  $scope.gridOptionsParientes.data[key].vive = $scope.fData.parTemporal.vive.descripcion;
                  $scope.gridOptionsParientes.data[key].direccion = $scope.fData.parTemporal.direccion;
                  $scope.gridOptionsParientes.data[key].telefono = $scope.fData.parTemporal.telefono;
                  $scope.gridOptionsParientes.data[key].notificar_emergencia = $scope.fData.parTemporal.notificar_emergencia;

                  // console.log('modificado');
                }
              });
              $scope.fData.parTemporal = {};
              $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[0];
              $scope.fData.parTemporal.vive = $scope.listaVive[0];
              $scope.fData.parTemporal.parentesco = 'NONE';
              $scope.editarParienteBool = false;
              $scope.classEditPanel = null;
            }
            $scope.agregarParienteACesta = function (){
              $('#temporalNombrePar').focus();
              if( !($scope.fData.parTemporal.nombres) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Nombres está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.parTemporal.ap_paterno) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Apellido Paterno  está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.parTemporal.ap_materno) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Apellido Materno está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.parTemporal.parentesco) || $scope.fData.parTemporal.parentesco == 'NONE' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Parentesco está vacio', type: 'warning', delay: 2000 });
                return false;
              }
              var arrTemporal = { 
                'pariente' : $scope.fData.parTemporal.nombres+' '+$scope.fData.parTemporal.ap_paterno+' '+$scope.fData.parTemporal.ap_materno,
                'nombres': $scope.fData.parTemporal.nombres,
                'ap_paterno': $scope.fData.parTemporal.ap_paterno,
                'ap_materno': $scope.fData.parTemporal.ap_materno,
                'parentesco' : $scope.fData.parTemporal.parentesco,
                'fecha_nac' : $scope.fData.parTemporal.fecha_nacimiento,
                'ocupacion' : $scope.fData.parTemporal.ocupacion,
                'estado_civil_obj' : $scope.fData.parTemporal.estado_civil,
                'vive_obj' : $scope.fData.parTemporal.vive,
                'estado_civil' : $scope.fData.parTemporal.estado_civil.descripcion,
                'vive' : $scope.fData.parTemporal.vive.descripcion,
                'direccion' : $scope.fData.parTemporal.direccion,
                'telefono' : $scope.fData.parTemporal.telefono,
                'notificar_emergencia': $scope.fData.parTemporal.notificar_emergencia
              }; 
              $scope.gridOptionsParientes.data.push(arrTemporal); 
              $scope.fData.parTemporal = {};
              $scope.fData.parTemporal.estado_civil = $scope.listaEstadoCivil[0];
              $scope.fData.parTemporal.vive = $scope.listaVive[0];
              $scope.fData.parTemporal.parentesco = 'NONE';
            }
            $scope.btnQuitarDeLaCesta = function (row) {
              var index = $scope.gridOptionsParientes.data.indexOf(row.entity); 
              $scope.gridOptionsParientes.data.splice(index,1); 
              $scope.classEditPanel = null;
              return;
            }

          // ================
          // CONTRATOS - Nuevo
          // ================
            $scope.gridOptionsContrato = { 
              paginationPageSizes: [10, 50, 100, 500, 1000],
              minRowsToShow: 9,
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              useExternalFiltering : true,
              enableGridMenu: true,
              enableFiltering: false,
              enableSorting: false,
              data: null,
              rowHeight: 30,
              columnDefs: [ 
                // { field: 'codigo' displayName: 'COD.', width: '25' },
                { field: 'empresa', displayName: 'EMPRESA', width: '170' },
                { field: 'cargo', displayName: 'CARGO', width: '170' },
                { field: 'condicion_laboral', displayName: 'COND. LABORAL', width: '140' },
                { field: 'fecha_ing', displayName: 'FECHA ING.', width: '90' },
                { field: 'fecha_ini_contrato', displayName: 'FECHA INI. CONTRATO', width: '120' },
                { field: 'fecha_fin_contrato', displayName: 'FECHA FIN CONTRATO', width: '120' },
                { field: 'vigente', displayName: 'VIGENTE', width: '80' },
                // { field: 'descargar', displayName: 'DESCARGAR', width: '10',
                //   cellTemplate:'<div><a target="_blank" href="{{ grid.appScope.dirContratosEmpleados + COL_FIELD.documento }}"><img style="height:30px;" class="center-block" ng-src="{{ grid.appScope.dirIconoFormat + COL_FIELD.icono }}" /> </a> </div>' },
                { field: 'accion', pinnedRight:true, displayName: '', width: 80, enableCellEdit: false, enableSorting: false, 
                  cellTemplate:'<div class="ui-grid-cell-contents"><button type="button" class="btn btn-sm btn-warning mr-xs" ng-click="grid.appScope.btnEditarContrato(row)"> <i class="fa fa-edit"></i> </button>' + 
                  '<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCestaHC(row)"> <i class="fa fa-trash"></i> </button></div>' 
                } 
              ]
            };
            
            $scope.agregarContratoACesta = function (){ 
              // console.log($scope.fData.conTemporal.empresaadmin.id,'$scope.fData.conTemporal.empresaadmin.id');
              if( !($scope.fData.conTemporal.empresaadmin.id) || $scope.fData.conTemporal.empresaadmin.id == 'NONE' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Empresa está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.condicion_laboral.id) || $scope.fData.conTemporal.condicion_laboral.id == 'NONE' ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Condición Laboral está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.idcargo)  ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Cargo está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.fecha_ingreso) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Fecha Ingreso está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.fecha_inicio_contrato) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Fecha Inicio Contrato está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              if( !($scope.fData.conTemporal.fecha_fin_contrato) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo Fecha Fin Contrato está vacío', type: 'warning', delay: 2000 });
                return false;
              }
              blockUI.start('Procesando...');
              var arrTemporal = { 
                'empresa' : $scope.fData.conTemporal.empresaadmin.descripcion,
                'empresa_obj' : $scope.fData.conTemporal.empresaadmin,
                'cargo': $scope.fData.conTemporal.cargo.descripcion,
                'idcargo': $scope.fData.conTemporal.idcargo,
                'cargo_obj': $scope.fData.conTemporal.cargo,
                'condicion_laboral_obj': $scope.fData.conTemporal.condicion_laboral,
                'condicion_laboral': $scope.fData.conTemporal.condicion_laboral.descripcion,
                'fecha_ing': $scope.fData.conTemporal.fecha_ingreso,
                'fecha_ini_contrato': $scope.fData.conTemporal.fecha_inicio_contrato,
                'fecha_fin_contrato' : $scope.fData.conTemporal.fecha_fin_contrato,
                'sueldo' : $scope.fData.conTemporal.sueldo,
                'vigente' : $scope.fData.conTemporal.contrato_vigente == 1 ? 'SI' : 'NO',
                'vigenteBool' : $scope.fData.conTemporal.contrato_vigente
              }; 
              $scope.gridOptionsContrato.data.push(arrTemporal); 
              $scope.fData.conTemporal = {
                idcargo : null
              };
              $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
              $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
              blockUI.stop();
            }
            $scope.btnQuitarDeLaCestaHC = function (row) { 
              blockUI.start('Procesando...');
              var index = $scope.gridOptionsContrato.data.indexOf(row.entity); 
              $scope.gridOptionsContrato.data.splice(index,1); 
              $scope.editarContratoBool = false;
              $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
              $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
              $scope.classEditPanel = null;
              blockUI.stop();
              return;
            }
            $scope.btnEditarContrato = function (row) { 
              blockUI.start('Procesando...');
              $scope.editarContratoBool = true;
              $scope.classEditPanel = 'ui-editPanel';
              $scope.fData.conTemporal.empresaadmin = row.entity.empresa_obj;
              $scope.fData.conTemporal.condicion_laboral = row.entity.condicion_laboral_obj;
              $scope.fData.conTemporal.idcargo = row.entity.idcargo;
              $scope.fData.conTemporal.cargo = row.entity.cargo_obj;
              $scope.fData.conTemporal.fecha_ingreso = row.entity.fecha_ing;
              $scope.fData.conTemporal.fecha_inicio_contrato = row.entity.fecha_ini_contrato;
              $scope.fData.conTemporal.fecha_fin_contrato = row.entity.fecha_fin_contrato;
              $scope.fData.conTemporal.sueldo = row.entity.sueldo;
              $scope.fData.conTemporal.contrato_vigente = row.entity.vigenteBool;
              $scope.fData.estTemporal.idrow = row.entity.$$hashKey;
              blockUI.stop();
            }
            $scope.actualizarContrato = function (){ 
              blockUI.start('Procesando...');
              //console.log('fData',$scope.fData.conTemporal );
              angular.forEach($scope.gridOptionsContrato.data, function(value, key) { 
                if(value.$$hashKey == $scope.fData.estTemporal.idrow ){ 
                  $scope.gridOptionsContrato.data[key].empresa = $scope.fData.conTemporal.empresaadmin.descripcion;
                  $scope.gridOptionsContrato.data[key].empresa_obj = $scope.fData.conTemporal.empresaadmin;
                  $scope.gridOptionsContrato.data[key].cargo = $scope.fData.conTemporal.cargo.descripcion;
                  $scope.gridOptionsContrato.data[key].idcargo = $scope.fData.conTemporal.idcargo;
                  $scope.gridOptionsContrato.data[key].cargo_obj = $scope.fData.conTemporal.cargo;
                  $scope.gridOptionsContrato.data[key].condicion_laboral_obj = $scope.fData.conTemporal.condicion_laboral;
                  $scope.gridOptionsContrato.data[key].condicion_laboral = $scope.fData.conTemporal.condicion_laboral.descripcion;
                  $scope.gridOptionsContrato.data[key].fecha_ing = $scope.fData.conTemporal.fecha_ingreso;
                  $scope.gridOptionsContrato.data[key].fecha_ini_contrato = $scope.fData.conTemporal.fecha_inicio_contrato;
                  $scope.gridOptionsContrato.data[key].fecha_fin_contrato = $scope.fData.conTemporal.fecha_fin_contrato;
                  $scope.gridOptionsContrato.data[key].sueldo = $scope.fData.conTemporal.sueldo;
                  $scope.gridOptionsContrato.data[key].vigente = $scope.fData.conTemporal.contrato_vigente == 1 ? 'SI' : 'NO';
                  $scope.gridOptionsContrato.data[key].vigenteBool = $scope.fData.conTemporal.contrato_vigente;
                  // $scope.gridOptionsContrato.data[key].notificar_emergencia = $scope.fData.conTemporal.notificar_emergencia;
                  // console.log('modificado');
                  //return; 
                }
              });
              $scope.fData.conTemporal = { 
                idcargo : null 
              };
              $scope.fData.conTemporal.empresaadmin = $scope.metodos.listaEmpresaAdmin[0];
              $scope.fData.conTemporal.condicion_laboral = $scope.listaCondicionLaboral[0];
              $scope.classEditPanel = null;
              $scope.editarContratoBool = false;
              blockUI.stop();
            }
          // ================
          // ESTUDIOS - Nuevo
          // ================
            $scope.fData.estTemporal = {};
            $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[0];
            $scope.fData.estTemporal.estudio_completo = $scope.listaEstudioCompleto[0].id;
            $scope.cargarNivelEstudio = function(tipo, row){
              var row = row || false;
              empleadoServices.sListarNivelEstudio(tipo).then(function (rpta) { 
                $scope.listaNivelEstudio = rpta.datos;
                if($scope.fData.estTemporal.tipo_estudio.id == '1'){
                  $scope.fData.estTemporal.especialidad = null;
                  $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
                }
                if($scope.editarEstudioBool){
                  if(row){
                    angular.forEach($scope.listaNivelEstudio, function(value, key){
                      if(row.entity.id == value.id){
                        $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[key];
                        //return;
                      }
                    });
                  }else{
                    $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
                  }
                }else{
                  $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
                }
              });
            }
            $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id);
            $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
            $scope.gridOptionsEstudios = {
              paginationPageSizes: [10, 50, 100, 500, 1000],
              minRowsToShow: 9,
              paginationPageSize: 50,
              useExternalPagination: true,
              useExternalSorting: true,
              useExternalFiltering : true,
              enableGridMenu: true,
              //enableRowSelection: true,
              enableSelectAll: true,
              enableFiltering: false,
              // enableFullRowSelection: true,
              // multiSelect: true,
              data: null,
              rowHeight: 30,
              columnDefs: [ 

                { field: 'nivel_estudio', name: 'descripcion_ne', displayName: 'NIVEL  DE ESTUDIO', width: '10%', enableCellEdit: false, enableSorting: false },
                { field: 'centro_estudio', name: 'centro_estudio', displayName: 'CENTRO DE ESTUDIOS', width: '20%', enableCellEdit: false, enableSorting: false },
                { field: 'especialidad', name: 'especialidad', displayName: 'ESPECIALIDAD', width: '24%', enableCellEdit: false, enableSorting: false },
                { field: 'fecha_desde', name: 'fecha_desde', displayName: 'FECHA DESDE', width: '9%', enableCellEdit: true, enableSorting: false },
                { field: 'fecha_hasta', name: 'fecha_hasta', displayName: 'FECHA HASTA', width: '9%', enableCellEdit: true, enableSorting: false },
                { field: 'estudio_completo', name: 'estudio_completo', displayName: 'COMPLETO / INCOMPLETO', width: '10%', enableCellEdit: false, 
                    enableSorting: false,
                    cellTemplate: '<div class="text-center ui-grid-cell-contents" ng-if="COL_FIELD == 1">COMPLETO</div><div class="text-center text-red" ng-if="COL_FIELD == 2">INCOMPLETO</div>'
                },
                { field: 'grado_academico', name: 'grado_academico', displayName: 'GRADO ACADEMICO OBTENIDO', width: '12%', enableCellEdit: false, enableSorting: false },
                { field: 'accion', displayName: '', width: '6%', enableCellEdit: false, enableSorting: false, 
                  cellTemplate:'<button type="button" class="btn btn-sm btn-warning mr" ng-click="grid.appScope.btnEditarEstudio(row)"> <i class="fa fa-edit"></i> </button>' + 
                  '<button type="button" class="btn btn-sm btn-danger " ng-click="grid.appScope.btnQuitarDeLaCestaEST(row)"> <i class="fa fa-trash"></i> </button>'
                }
              ]
            };
            $scope.btnEditarEstudio = function (row) { 
              $scope.editarEstudioBool = true;
              var tipo_est = parseInt(row.entity.tipo_nivel);
              angular.forEach($scope.listaTipoEstudio, function(value, key){
                if(row.entity.tipo_nivel == value.id){
                  $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[key];
                  // return;
                }
              });
              $scope.listaNivelEstudio = $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id, row);
              $scope.fData.estTemporal.centro_estudio = row.entity.centro_estudio;
              $scope.fData.estTemporal.fecha_desde = row.entity.fecha_desde;
              $scope.fData.estTemporal.fecha_hasta = row.entity.fecha_hasta;
              $scope.fData.estTemporal.estudio_completo = parseInt(row.entity.estudio_completo);
              $scope.fData.estTemporal.especialidad = row.entity.especialidad;
              $scope.fData.estTemporal.grado_academico = row.entity.grado_academico;
              $scope.fData.estTemporal.idrow = row.entity.$$hashKey;
            }
            $scope.actualizarEstudio = function (){
              //console.log('fData',$scope.fData.estTemporal );
              angular.forEach($scope.gridOptionsEstudios.data, function(value, key) { 
                if(value.$$hashKey == $scope.fData.estTemporal.idrow ){ 
                  $scope.gridOptionsEstudios.data[key].centro_estudio = $scope.fData.estTemporal.centro_estudio;
                  $scope.gridOptionsEstudios.data[key].fecha_desde = $scope.fData.estTemporal.fecha_desde;
                  $scope.gridOptionsEstudios.data[key].fecha_hasta = $scope.fData.estTemporal.fecha_hasta;
                  $scope.gridOptionsEstudios.data[key].estudio_completo = $scope.fData.estTemporal.estudio_completo;
                  $scope.gridOptionsEstudios.data[key].especialidad = $scope.fData.estTemporal.especialidad;
                  $scope.gridOptionsEstudios.data[key].grado_academico = $scope.fData.estTemporal.grado_academico;
                  $scope.gridOptionsEstudios.data[key].tipo_nivel = $scope.fData.estTemporal.tipo_estudio.id;
                  $scope.gridOptionsEstudios.data[key].nivel_estudio = $scope.fData.estTemporal.nivel_estudio.descripcion;
                  $scope.gridOptionsEstudios.data[key].id = $scope.fData.estTemporal.nivel_estudio.id;
                }
              });
              $scope.fData.estTemporal = {};
              $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[0];
              $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id);
              $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
              $scope.fData.estTemporal.estudio_completo = $scope.listaEstudioCompleto[0].id;
              $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
              $scope.editarEstudioBool = false;
            }
            $scope.agregarEstudioACesta = function (){ 
              if($scope.fData.estTemporal.tipo_estudio.id ==  1){
                $scope.fData.estTemporal.grado_academico = null;
                $scope.fData.estTemporal.especialidad = null;
              }
              if( !($scope.fData.estTemporal.centro_estudio) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo "Centro de Estudios" está vacio', type: 'warning', delay: 2000 });
                $('#centro_estudio').focus();
                return false;
              }
              if( !($scope.fData.estTemporal.fecha_desde) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo "Fecha Desde" está vacio', type: 'warning', delay: 2000 });
                $('#fecha_desde').focus();
                return false;
              }
              if( !($scope.fData.estTemporal.fecha_hasta) ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'El campo "Fecha Hasta" está vacio', type: 'warning', delay: 2000 });
                $('#fecha_hasta').focus();
                return false;
              }
              if( $scope.fData.estTemporal.estudio_completo == 0 ){ 
                pinesNotifications.notify({ title: 'Advertencia.', text: 'Seleccione una opcion: Completo o Incompleto', type: 'warning', delay: 2000 });
                return false;
              }
              var arrTemporal = { 
                'id': $scope.fData.estTemporal.nivel_estudio.id,
                'tipo_nivel': $scope.fData.estTemporal.tipo_estudio.id,
                'nivel_estudio': $scope.fData.estTemporal.nivel_estudio.descripcion,
                'centro_estudio': $scope.fData.estTemporal.centro_estudio,
                'especialidad' : $scope.fData.estTemporal.especialidad,
                'fecha_desde' : $scope.fData.estTemporal.fecha_desde,
                'fecha_hasta' : $scope.fData.estTemporal.fecha_hasta,
                'estudio_completo' : $scope.fData.estTemporal.estudio_completo,
                'grado_academico' : $scope.fData.estTemporal.grado_academico,
                'es_temporal': true
              }; 
              $scope.gridOptionsEstudios.data.push(arrTemporal); 
              $scope.fData.estTemporal = {};
              $scope.fData.estTemporal.tipo_estudio = $scope.listaTipoEstudio[0];
              $scope.cargarNivelEstudio($scope.fData.estTemporal.tipo_estudio.id);
              $scope.fData.estTemporal.nivel_estudio = $scope.listaNivelEstudio[0];
              $scope.fData.estTemporal.estudio_completo = $scope.listaEstudioCompleto[0].id;
              $scope.fData.estTemporal.grado_academico = $scope.listaGradoAcademico[0].id;
            }
            $scope.btnQuitarDeLaCestaEST = function (row) {
              var index = $scope.gridOptionsEstudios.data.indexOf(row.entity); 
              $scope.gridOptionsEstudios.data.splice(index,1); 
              return;
            }
          // ================
          // BOTONES - Nuevo
          // ================ 
            $scope.cancel = function () {
              $modalInstance.dismiss('cancel');
            }
            $scope.aceptar = function () { 
              if( $scope.editarContratoBool){ 
                pinesNotifications.notify({ title: 'Advertencia', text: 'No puede guardar los cambios sin terminar las ediciones.', type: 'warning', delay: 2500 });
                return false;
              }

              $scope.fData.fecha_nacimiento = $filter('date')($scope.fData.fecha_nacimiento,'yyyy-MM-dd'); 
              var formData = new FormData();
              angular.forEach($scope.fData,function (index,val) { 
                formData.append(val,index);
              });
              formData.append('parientes',JSON.stringify($scope.gridOptionsParientes.data));
              formData.append('estudios',JSON.stringify($scope.gridOptionsEstudios.data));
              formData.append('contratos',JSON.stringify($scope.gridOptionsContrato.data));
              formData.append('afp',JSON.stringify($scope.fData.afp));
              formData.append('comision_afp',JSON.stringify($scope.fData.comision_afp));
              formData.append('tipoDocumento',JSON.stringify($scope.fData.tipoDocumento));
              formData.append('area_empresa',JSON.stringify($scope.fData.area_empresa));
              formData.append('categoriaPersonalSalud',JSON.stringify($scope.fData.categoriaPersonalSalud));
              formData.append('banco',JSON.stringify($scope.fData.banco));
              blockUI.start('Ejecutando proceso...');
              empleadoServices.sRegistrar(formData).then(function (rpta) {
                blockUI.stop();
                if(rpta.flag === 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $modalInstance.dismiss('cancel');
                  if( $scope.modulo == 'empresa' ){
                    $scope.getPaginationMedicoServerSide();
                  }else{
                    $scope.getPaginationServerSide();
                  }
                  
                }else if(rpta.flag === 0){
                  var pTitle = 'Advertencia!';
                  var pType = 'danger';
                }else{
                  alert('Algo salió mal...');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
              });
            }
          // ---------------------------------------------------------------
          // AGREGAMOS UN USUARIO NUEVO DESDE AQUI PARA NO TENER QUE SALIR
          // ---------------------------------------------------------------
            $scope.nuevoUsuario = function (size){ 
              $modal.open({
                templateUrl: angular.patchURLCI+'usuario/ver_popup_formulario',
                size: size || '',
                backdrop: 'static',
                keyboard:false,
                scope: $scope,
                controller: function ($scope, $modalInstance, getPaginationServerSide,listarGrupos) { 
                  $scope.fDataUsuario = {};
                  $scope.userTemporal = {};
                  $scope.userTemporal.empresa = {};
                  $scope.boolForm = 'reg';
                  $scope.titleForm = 'Registro de usuario';
                  $scope.getPaginationServerSide = getPaginationServerSide;
                  grupoServices.sListarGruposCbo().then(function (rpta) {
                    $scope.listaGrupos = rpta.datos;
                    $scope.listaGrupos.splice(0,0,{ id : '', descripcion:'--Seleccione grupo--'});
                    $scope.fDataUsuario.grupoId = $scope.listaGrupos[0].id;
                  });

                  // ******* LISTA SOLO SEDES ******* 
                  $scope.cargarSoloSedes = function(){ 
                    sedeServices.sListarSedeCbo().then(function (rpta) {
                      $scope.listaSede = rpta.datos;
                      $scope.userTemporal.sede = $scope.listaSede[0];
                    });  
                  };
                  /* LOGICA DE SEDES */ 
                  $scope.fDataUsuario.siEmpresa = true;
                  $scope.fDataUsuario.siSedeDeEmpresa = true;
                  $scope.fDataUsuario.siSoloSede = false;
                  if( $scope.fSessionCI.key_group == 'key_rrhh' || $scope.fSessionCI.key_group == 'key_rrhh_asistente' || $scope.fSessionCI.key_group == 'key_gerencia' ){ 
                    //console.log('entréee');
                    $scope.fDataUsuario.siEmpresa = false;
                    $scope.fDataUsuario.siSedeDeEmpresa = false;
                    $scope.fDataUsuario.siSoloSede = true;
                    $scope.cargarSoloSedes();
                  }else{
                    // ******* LISTA DE EMPRESAS ADMIN ******* 
                    empresaAdminServices.sListarEmpresaAdminVentaCbo().then(function (rpta) {
                      $scope.listaEmpresaAdmin = rpta.datos;
                      $scope.listaEmpresaAdmin.splice(0,0,{ id : '0', descripcion:'--Seleccione Empresa--'});
                      $scope.userTemporal.empresa = $scope.listaEmpresaAdmin[0];
                      $scope.cargarSedes($scope.userTemporal.empresa);
                    });
                    // ******* LISTA DE SEDES *******
                    $scope.cargarSedes = function(empresaadmin){
                      if(empresaadmin.id == '0'){
                        $scope.listaSede = [];
                        $scope.listaSede.push({ id : '0', descripcion:'--Primero seleccione Empresa--'});
                        $scope.userTemporal.sede = $scope.listaSede[0];
                      }else{
                        sedeServices.sListarSedePorEmpresaCbo(empresaadmin).then(function (rpta) {
                          $scope.listaSede = rpta.datos;
                          //$scope.listaSede.splice(0,0,{ id : '', descripcion:'--Seleccione Sede--'});
                          $scope.userTemporal.sede = $scope.listaSede[0];
                        });  
                      }
                    };
                  }

                  /* ********** GRILLA EMPRESA  - SEDE ********** */
                  $scope.gridOptionsEmpresaSede = {
                    paginationPageSizes: [10, 50, 100],
                    minRowsToShow: 9,
                    paginationPageSize: 50,
                    useExternalPagination: true,
                    useExternalSorting: true,
                    useExternalFiltering : true,
                    enableGridMenu: true,
                    enableFiltering: false,
                    data: null,
                    rowHeight: 30,
                    columnDefs: [
                      { field: 'empresa', name: 'empresa', displayName: 'EMPRESA', width: '40%', enableCellEdit: false, enableSorting: false },
                      { field: 'sede', name: 'sede', displayName: 'SEDE', width: '40%', enableCellEdit: false, enableSorting: false },
                      { field: 'accion', displayName: 'ACCIÓN', width: '20%', enableCellEdit: false, enableSorting: false,
                        cellTemplate:'<button type="button" class="btn btn-sm btn-danger" ng-click="grid.appScope.btnQuitarDeLaCesta(row)"> <i class="fa fa-trash"></i> </button>'
                      }
                    ]
                  };
                  $scope.agregarSedeACesta = function (){
                    var sedeNew = true;
                    angular.forEach($scope.gridOptionsEmpresaSede.data, function(value, key) { 
                      if( value.idsedeempresaadmin == $scope.userTemporal.sede.id ){ 
                        sedeNew = false;
                      }
                    });
                    if( !sedeNew ){ 
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'La sede ya ha sido agregado a la cesta.', type: 'warning', delay: 2000 });
                      return false;
                    }
                    if( $scope.userTemporal.sede.id == '0' ){
                      pinesNotifications.notify({ title: 'Advertencia.', text: 'Debe seleccionar una Empresa / Sede', type: 'warning', delay: 2000 });
                      return false;
                    }
                    var arrTemporal = {
                      'empresa': $scope.userTemporal.empresa.descripcion || '-',
                      'sede': $scope.userTemporal.sede.descripcion,
                      'idsede': $scope.userTemporal.sede.idsede,
                      'idsedeempresaadmin': $scope.userTemporal.sede.id || '1',
                      'es_temporal': true
                    };
                    // console.log($scope.userTemporal,'$scope.userTemporal');
                    $scope.gridOptionsEmpresaSede.data.push(arrTemporal); 
                  };
                  $scope.btnQuitarDeLaCesta = function (row) {
                    // var index = $scope.gridOptionsEmpresaSede.data.indexOf(row.entity); 
                    // $scope.gridOptionsEmpresaSede.data.splice(index,1); 
                    // return;
                    if( row.entity.es_temporal && row.entity.es_temporal === true ){
                      var index = $scope.gridOptionsEmpresaSede.data.indexOf(row.entity); 
                      $scope.gridOptionsEmpresaSede.data.splice(index,1); 
                      return;
                    }
                    var pMensaje = '¿Realmente desea realizar la acción?';
                    $bootbox.confirm(pMensaje, function(result) { 
                      if(result){
                        console.log(row.entity.idusersporsede);
                        usuarioServices.sQuitarSedeDeUsuario(row.entity).then(function (rpta) {
                          if(rpta.flag == 1){
                            var pTitle = 'OK!';
                            var pType = 'success';
                            $scope.getPaginationServerSideSede();
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
                  };

                  $scope.cancel = function () {
                    $modalInstance.dismiss('cancel');
                  }
                  $scope.aceptar = function () {
                    var paramDatos = {
                      'dataUsuario' : $scope.fDataUsuario,
                      'sedesEmpresa' : $scope.gridOptionsEmpresaSede.data
                    }
                    usuarioServices.sRegistrar(paramDatos).then(function (rpta) {
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
                      $scope.fData.idusuario = rpta.idusuario;
                      $scope.fData.usuario = rpta.usuario;
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                    });
                  }
                  // $scope.aceptar = function () {
                  //   usuarioServices.sRegistrar($scope.fDataUsuario).then(function (rpta) {
                  //     if(rpta.flag == 1){
                  //       pTitle = 'OK!';
                  //       pType = 'success';
                  //       $modalInstance.dismiss('cancel');
                  //       $scope.getPaginationServerSide();
                  //     }else if(rpta.flag == 0){
                  //       var pTitle = 'Error!';
                  //       var pType = 'danger';
                  //     }else{
                  //       alert('Error inesperado');
                  //     }
                  //     $scope.fData.idusuario = rpta.idusuario;
                  //     $scope.fData.usuario = rpta.usuario;
                  //     pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
                  //   });
                  // }
                },
                resolve: {
                   
                  getPaginationServerSide: function() {
                    return $scope.getPaginationServerSide;
                  },
                  listarGrupos : function () {
                    return $scope.listarGrupos;
                  }
                }
              });
            }
          // =======================================
          //  VERIFICAR SI EXISTE EN RENIEC
          // =======================================
            $scope.verificaDNI = function () {
              if($scope.fData.num_documento) {
                if($scope.fData.num_documento.length === 8 && $scope.fData.tipoDocumento.id == 1){
                  clienteServices.sVerificarCliente($scope.fData).then(function (rpta) {
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      //$scope.fData.dni = rpta.datos.dni;
                      $scope.fData.nombres = rpta.datos.Nombres;
                      $scope.fData.apellido_paterno = rpta.datos.Ape_Pat;
                      $scope.fData.apellido_materno = rpta.datos.Ape_Mat;
                      $scope.fData.fecha_nacimiento = rpta.datos.fecha_nacimiento;
                      $scope.fData.sexo = rpta.datos.sexo;
                      console.log($scope.fData.nombres);
                      //$scope.getPaginationServerSide();
                    }else if(rpta.flag == 0){
                      var pTitle = 'Oops';
                      var pType = 'danger';
                    }else{
                      // alert('Error inesperado');
                      pinesNotifications.notify({ title: 'Advertencia', text: 'No se pudo conectar a la BD', type: 'warning', delay: 3000 }); 
                      return false;
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1500 });
                  });
                }else {
                  $scope.fData.nombres = null;
                  $scope.fData.apellido_paterno = null;
                  $scope.fData.apellido_materno = null;
                  $scope.fData.fecha_nacimiento = null;
                  $scope.fData.sexo = null;
                }
              }
            }
          blockUI.stop();
          // $location.hash('topModal');
          // $anchorScroll(); 
        }, 
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          }, 
          dateUI: function() {
            return $scope.dateUI;
          }
        }
      });
       //$scope.$parent.blockUI.stop();
    }// fin nuevo

    $scope.btnConfirDarBaja = function () { 

      if(!$scope.mySelectionGrid[0].tercero_propio){
        var arrParams = {
          datos: $scope.mySelectionGrid[0],
          vigente: 1
        };
        historialContratoServices.sListarHistorialContratosLinea(arrParams).then(function (rpta) {   
          if(rpta.flag == 1){
            $scope.listaContratos = rpta.datos;
            $scope.btnDarBaja();
          }else if(rpta.flag == 0){
            var pTitle = 'Error!';
            var pType = 'danger';
            pinesNotifications.notify({ title: pTitle, text: 'No se puede dar de baja un empleado sin contrato vigente', type: pType, delay: 3000 });
          }else{
            alert('Algo salió mal...');
          }    
        });
      }else{
        $scope.btnDarBaja();
      }      
    }
    $scope.btnDarBaja = function (mensaje) {       
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          if(!$scope.mySelectionGrid[0].tercero_propio){
            $modal.open({
              templateUrl: angular.patchURLCI+'empleado/ver_popup_dar_baja',
              size: 'xs',
              backdrop: 'static',
              scope: $scope,
              controller: function ($scope, $modalInstance){
                $scope.titleForm = 'Edición al Dar de Baja';
                $scope.fData = {};
                $scope.fData.fecha_cese = $filter('date')(new Date(),'dd-MM-yyyy');
                $scope.listaEmprDarBaja = [];
                var index = 0;
                console.log($scope.mySelectionGrid);
                angular.forEach($scope.listaContratos,function (index,val) { 
                  $scope.listaEmprDarBaja[val] = { id: index.empresa_obj.id, descripcion: index.empresa_obj.descripcion, index:val };
                });
                $scope.cambiocontrato = function(item){
                  index = item.index;
                  $scope.fData.codigo = $scope.listaContratos[index].codigo;
                  $scope.fData.cond_laboral = $scope.listaContratos[index].condicion_laboral;
                  $scope.fData.idcargo = $scope.listaContratos[index].idcargo;
                  $scope.fData.cargo = $scope.listaContratos[index].cargo;
                  $scope.fData.fecha_ingreso = $scope.listaContratos[index].fecha_ing;
                  $scope.fData.fecha_inicio_contrato = $scope.listaContratos[index].fecha_ini_contrato;
                  $scope.fData.fecha_fin_contrato = $scope.listaContratos[index].fecha_fin_contrato;
                  $scope.fData.sueldo = $scope.listaContratos[index].sueldo;
                  $scope.fData.contrato_vigente = $scope.listaContratos[index].vigente;
                }

                $scope.fData.empresaadmin = $scope.listaEmprDarBaja[0];
                $scope.fData.codigo = $scope.listaContratos[0].codigo;
                $scope.fData.cond_laboral = $scope.listaContratos[0].condicion_laboral;
                $scope.fData.idcargo = $scope.listaContratos[0].idcargo;
                $scope.fData.cargo = $scope.listaContratos[0].cargo;
                $scope.fData.fecha_ingreso = $scope.listaContratos[0].fecha_ing;
                $scope.fData.fecha_inicio_contrato = $scope.listaContratos[0].fecha_ini_contrato;
                $scope.fData.fecha_fin_contrato = $scope.listaContratos[0].fecha_fin_contrato;
                $scope.fData.sueldo = $scope.listaContratos[0].sueldo;
                $scope.fData.contrato_vigente = $scope.listaContratos[0].vigente;

                $scope.aceptar = function () {
                  var condicion_laboral=$scope.fData.cond_laboral;
                  //$scope.fData.condicion_laboral = [];
                  $scope.fData.condicion_laboral = { id: condicion_laboral, descripcion: condicion_laboral };
                  $scope.fData.idempleado = $scope.mySelectionGrid[0].id;
                  console.log($scope.fData);
                  historialContratoServices.sEditarContrato($scope.fData).then(function (rpta) {   
                    if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      empleadoServices.sDarBaja($scope.mySelectionGrid).then(function (rpta) {
                        if(rpta.flag == 1){
                            pTitle = 'OK!';
                            pType = 'success';
                            $scope.getPaginationServerSide();
                            $modalInstance.dismiss('cancel');
                          }else if(rpta2.flag == 0){
                            var pTitle = 'Error!';
                            var pType = 'danger';
                          }else{
                            alert('Algo salió mal...');
                          }  
                          pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });   
                      });   
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                      pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });     
                    }else{
                      alert('Algo salió mal...');
                    }  
                  });
                }

                $scope.cancel = function () {
                  $modalInstance.dismiss('cancel');
                  $scope.fData = {};
                  $scope.getPaginationServerSide();
                }
              }
            });
          }else{
            empleadoServices.sDarBaja($scope.mySelectionGrid).then(function (rpta) {
              if(rpta.flag == 1){
                  pTitle = 'OK!';
                  pType = 'success';
                  $scope.getPaginationServerSide();
                }else if(rpta.flag == 0){
                  var pTitle = 'Error!';
                  var pType = 'danger';
                }else{
                  alert('Algo salió mal...');
                }
                pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
          }
        }
      });    
    }
    $scope.btnRevertirBaja = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){
          empleadoServices.sRevertirBaja($scope.mySelectionGrid).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
    $scope.btnAnular = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) { 
        if(result){ 
          blockUI.start('Ejecutando proceso...');
          empleadoServices.sAnular($scope.mySelectionGrid[0]).then(function (rpta) {
            if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.getPaginationServerSide();
              }else if(rpta.flag == 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
              blockUI.stop();
          });
        }
      });
    }
    $scope.btnVerFichaPdf = function (){
      $scope.fDataImprimir = {};
      $scope.fDataImprimir = angular.copy($scope.mySelectionGrid[0]);
      $scope.fDataImprimir.lugar_nacimiento = {
        'departamento' : '',
        'provincia' : '',
        'distrito' : ''
      }
      $scope.fDataImprimir.dirImagesEmpleados = $scope.dirImagesEmpleados;

      var arrParams = {
        titulo: 'FICHA DE DATOS DEL TRABAJADOR',
        datos:{
          resultado:  $scope.fDataImprimir,
          salida: 'pdf',
          tituloAbv: 'FIC-EMPL',
          titulo: 'FICHA DE DATOS DEL TRABAJADOR'
        },
        metodo: 'php'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/ficha_datos_empleado',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnAvanceProfesionalEmpleado = function () {
      var arrParams = {
        titulo: 'AVANCE PROFESIONAL DEL EMPLEADO',
        datos:{
          id:  $scope.mySelectionGrid[0].id, // idempleado 
          salida: 'pdf',
          tituloAbv: 'RH-AVPRE',
          titulo: 'AVANCE PROFESIONAL DEL EMPLEADO'
        },
        metodo: 'php'
      }
      //console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/avance_profesional_empleado',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnVerHistorialContratos = function () { 
      blockUI.start('Procesando Información...'); 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'HistorialContrato/ver_popup_historial_contrato',
        size: 'lg',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.metodos = {};
          $scope.fDataHistorial = {};
          $scope.fDataHistorial = angular.copy($scope.mySelectionGrid[0]);
          $scope.titleForm = 'HISTORIAL DE CONTRATOS';
          var arrParams = {
            datos: $scope.mySelectionGrid[0]
          };
          historialContratoServices.sListarHistorialContratosLinea(arrParams).then(function (rpta) {
            $scope.metodos.listaHistorial = rpta.datos;
          });
          $scope.subirDocumentoContrato = function (codigo) {
            blockUI.start('Mostrando Formulario...'); 
            $uibModal.open({ 
              templateUrl: angular.patchURLCI+'HistorialContrato/ver_popup_subir_contrato',
              size: 'md',
              scope: $scope,
              controller: function ($scope, $modalInstance) { 
                $scope.titleFormDet = 'SUBIR CONTRADO DE EMPLEADO';
                $scope.fDataSubida = {}; 
                $scope.aceptarDet = function () { 
                  blockUI.start('Ejecutando proceso...');
                  var formData = new FormData();
                  $scope.fDataSubida.codigo = codigo; 
                  angular.forEach($scope.fDataSubida,function (index,val) { 
                    formData.append(val,index);
                  });
                  historialContratoServices.sSubirArchivoContrato(formData).then(function (rpta) {
                    if(rpta.flag === 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      $modalInstance.dismiss('cancel');
                      var arrParams = { 
                        datos: $scope.mySelectionGrid[0]
                      };
                      historialContratoServices.sListarHistorialContratosLinea(arrParams).then(function (rpta) {
                        $scope.metodos.listaHistorial = rpta.datos;
                      });
                    }else if(rpta.flag === 0){
                      var pTitle = 'Advertencia!';
                      var pType = 'danger';
                    }else{
                      alert('Algo salió mal...');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
                    blockUI.stop();
                  });
                }
                $scope.cancelDet = function () {
                  $modalInstance.dismiss('cancel');
                }
                blockUI.stop();
              }
            });
          }
          $scope.quitarDocumentoContrato = function (codigo) { 
            var pMensaje = '¿Realmente desea realizar la acción?';
            $bootbox.confirm(pMensaje, function(result) { 
              if(result){ 
                blockUI.start('Ejecutando proceso...');
                var arrParams = {
                  'codigo': codigo
                }
                historialContratoServices.sQuitarDocumentoContrato(arrParams).then(function (rpta) { 
                  if(rpta.flag == 1){
                      pTitle = 'OK!';
                      pType = 'success';
                      var arrParamsDet = { 
                        datos: $scope.mySelectionGrid[0]
                      };
                      historialContratoServices.sListarHistorialContratosLinea(arrParamsDet).then(function (rpta) {
                        $scope.metodos.listaHistorial = rpta.datos;
                      });
                    }else if(rpta.flag == 0){
                      var pTitle = 'Error!';
                      var pType = 'danger';
                    }else{
                      alert('Algo salió mal...');
                    }
                    pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });

                    blockUI.stop();
                });
              }
            });
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.getPaginationServerSide();
          }
          blockUI.stop();
        }
      });
    }
    $scope.btnVerVacaciones = function () { 
      blockUI.start('Procesando Información...'); 
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Empleado/ver_popup_vacaciones',
        size: 'md',
        scope: $scope,
        controller: function ($scope, $modalInstance) { 
          $scope.fDataVac = {};
          $scope.fDataVac = angular.copy($scope.mySelectionGrid[0]);
          $scope.titleForm = 'VACACIONES';

          // Grilla de vacaciones
          $scope.gridOptions = {
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
              { field: 'id', name: 'idmotivohe', displayName: 'ITEM', maxWidth: 60,  sort: { direction: uiGridConstants.DESC}, visible:true},
              { field: 'descripcion', name: 'descripcion_mh', displayName: 'DESCRIPCIÓN' },
              { field: 'fecha_inicial', name: 'fecha_inicial', displayName: 'DESDE', width: 80 },
              { field: 'fecha_final', name: 'fecha_final', displayName: 'HASTA', width: 80 },
              { field: 'cantidad_dias', name: 'cantidad', displayName: 'DIAS', width: 60, cellClass:'text-right' },
            ],
            onRegisterApi: function(gridApi) { 
              $scope.gridApi = gridApi;
            }
          };
          var arrParams = {
            datos: $scope.fDataVac
          };
          empleadoServices.sListarVacacionesEmpleado(arrParams).then(function (rpta) {
            $scope.gridOptions.data = rpta.datos;
          });
          
          
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            // $scope.getPaginationServerSide();
          }
          blockUI.stop();
        }
      });
    }
    $scope.btnSubirCV = function () { 
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'DocumentoEmpleado/ver_popup_subir_cv',
        size: 'md',
        backdrop: 'static',
        scope: $scope,
        keyboard:false,
        controller: function ($scope, $modalInstance) {
          $scope.fData = {};
          $scope.titleForm = 'Subir Curriculum Vitae'; 
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
          }
          $scope.aceptar = function () { 
            blockUI.start('Subiendo los archivos...');
            var formData = new FormData();
            $scope.fData.idempleado = $scope.mySelectionGrid[0].id; 
            angular.forEach($scope.fData,function (index,val) { 
              formData.append(val,index);
            });
            documentoEmpleadoServices.sGuardarCV(formData).then(function (rpta) {
              if(rpta.flag === 1){
                pTitle = 'OK!';
                pType = 'success';
                //$scope.fData = {};
                $modalInstance.dismiss('cancel');
                $scope.getPaginationServerSide();
              }else if(rpta.flag === 0){
                var pTitle = 'Error!';
                var pType = 'danger';
              }else{
                alert('Algo salió mal...');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
            });
            blockUI.stop();
          }
        }
      });
    }
  }])
  .service("empleadoServices",function($http, $q) {
    return({
      sListarNivelEstudio: sListarNivelEstudio,
      sCargarEstudiosEmpleado: sCargarEstudiosEmpleado,
      sAnularEstudio: sAnularEstudio,
      sEditarEstudio: sEditarEstudio,
      sAgregarEstudioDeEmpleado: sAgregarEstudioDeEmpleado,
      sListarEmpleados: sListarEmpleados,
      sListarEmpleadosCbo : sListarEmpleadosCbo,
      sListarEmpleadoPorAutocompletado: sListarEmpleadoPorAutocompletado,
      sListarCumpleaneros: sListarCumpleaneros,
      sListarAgendaTelefonica: sListarAgendaTelefonica,
      sListarEmpleadosporCodigo : sListarEmpleadosporCodigo,
      sListarVacacionesEmpleado : sListarVacacionesEmpleado,
      sActualizarFechaCaducidad: sActualizarFechaCaducidad,
      sActualizarContrato: sActualizarContrato,
      sRegistrar: sRegistrar,
      sEditar: sEditar,
      sDarBaja: sDarBaja,
      sRevertirBaja: sRevertirBaja,
      sAnular: sAnular,      
    });
    function sListarNivelEstudio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NivelEstudios/lista_nivel_estudio_por_tipo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCargarEstudiosEmpleado(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NivelEstudios/cargar_estudios_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditarEstudio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NivelEstudios/editar_estudio_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAgregarEstudioDeEmpleado (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NivelEstudios/agregar_estudio_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnularEstudio(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"NivelEstudios/anular_estudio", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarAgendaTelefonica (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_telefono", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpleados(datos) { 
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpleadosCbo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_cbo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpleadoPorAutocompletado(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_por_autocomplete", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarCumpleaneros (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_cumpleaneros", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarEmpleadosporCodigo(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_empleados_por_codigo", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarVacacionesEmpleado(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/lista_vacaciones_por_empleado", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarFechaCaducidad(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/actualizar_fecha_caducidad", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sActualizarContrato(datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/actualizar_contrato", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/registrar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sEditar (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/editar", 
            data : datos,
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sDarBaja (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/darBaja", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRevertirBaja (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/revertirBaja", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sAnular (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"empleado/anular", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }    
  });