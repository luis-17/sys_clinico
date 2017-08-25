angular.module('theme.aperturaPlanilla', ['theme.core.services'])
  .controller('aperturaPlanillaController', ['$scope', '$sce', '$uibModal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'blockUI','$filter',
    'aperturaPlanillaServices', 'empresaServices','conceptoPlanillaServices', 'categoriaConceptoPlanillaServices', 'planillaMasterServices','empleadoPlanillaServices','ModalReporteFactory', 'asientoContableServices',
    function($scope, $sce, $uibModal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, blockUI, $filter
      , aperturaPlanillaServices
      , empresaServices      
      , conceptoPlanillaServices
      , categoriaConceptoPlanillaServices
      , planillaMasterServices
      , empleadoPlanillaServices
      , ModalReporteFactory
      , asientoContableServices
      ){
    'use strict';

    var paginationOptions = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null,
    };

    $scope.fBusqueda = {};

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
      enableSelectAll: true,
      enableFiltering: false,
      enableFullRowSelection: true,
      multiSelect: false,
      columnDefs: [
        { field: 'id', name: 'idplanilla', displayName: 'ID', maxWidth: 80, },
        { field: 'descripcion', name: 'descripcion_pl', displayName: 'Descripción' },
        { field: 'estado', type:'object', name: 'estado', displayName: 'ESTADO', minWidth:'150', maxWidth:'250', enableSorting:false, 
          cellTemplate:'<div class="ui-grid-cell-contents"><label style="width: 150px;" class="label {{ COL_FIELD.label }} ">{{ COL_FIELD.str_estado }}</label></div>' 
        }, 
        //{ field: 'fecha_cierre', name: 'fecha_cierre', displayName: 'FECHA CIERRE', maxWidth: 90, visible: false, sort: { direction: uiGridConstants.DESC}},
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
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptions.searchColumn = { 
            'idplanilla' : grid.columns[1].filters[0].term,
            'descripcion_pl' : grid.columns[2].filters[0].term,
          }
          $scope.getPaginationServerSide();
        });
      }
    };
    paginationOptions.sortName = $scope.gridOptions.columnDefs[0].name;
    $scope.getPaginationServerSide = function() {
      $scope.datosGrid = {
        paginate : paginationOptions,
        empresa: $scope.fBusqueda.empresa
      };
      aperturaPlanillaServices.sListarPlanillas($scope.datosGrid).then(function (rpta) {
        $scope.gridOptions.totalItems = rpta.paginate.totalRows;
        $scope.gridOptions.data = rpta.datos;
      });
      $scope.mySelectionGrid = [];
    };

    empresaServices.sListarEmpresasSoloAdminCbo().then(function(rpta){
      $scope.listaEmpresaAdmin = rpta.datos;
      $scope.fBusqueda.empresa = $scope.listaEmpresaAdmin[0];
      $scope.getPaginationServerSide();
    });    
    

      /* ============= */
     /* MANTENIMIENTO */
    /* ============= */
    $scope.changeViewEmpleado = function(value){
      $scope.viewEmpleados = value;      
    }
    $scope.changeViewEmpleado(false);  

    var paginationOptionsEmpl = {
      pageNumber: 1,
      firstRow: 0,
      pageSize: 10,
      sort: uiGridConstants.DESC,
      sortName: null,
      search: null,
    };

    $scope.btnToggleEmplFiltering = function(){
      $scope.gridOptionsEmpl.enableFiltering = !$scope.gridOptionsEmpl.enableFiltering;
      $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
    };
    $scope.gridOptionsEmpl = {
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
        { field: 'idempleado', name: 'idempleado', displayName: 'COD', maxWidth: 80, width:80,  sort: { direction: uiGridConstants.DESC} },
        { field: 'numero_documento', name: 'numero_documento', displayName: 'Nº DOC.', width: 100 },
        { field: 'empleado', name: 'empleado', displayName: 'Empleado', minWidth:150 },
        { field: 'centro_costo', name: 'centro_costo', displayName: 'CENTRO DE COSTO', width:150 },
        { field: 'sede', name: 'sede', displayName: 'SEDE', width:140 },
        { field: 'sueldo_contrato', name: 'sueldo_contrato', displayName: 'SUELDO', width:90 },
        { field: 'fecha_ingreso', name: 'fecha_ingreso', displayName: 'FEC. INGRESO', visible:true, width: 150, enableFiltering: false },
        { field: 'total_remuneraciones', name: 'total_remuneraciones', displayName: 'REMUN.', visible:true, width: 80, enableFiltering: false },
        { field: 'total_descuentos', name: 'total_descuentos', displayName: 'DESC', visible:true, width: 80, enableFiltering: false },
        { field: 'neto_a_pagar', name: 'neto_a_pagar', displayName: 'NETO A PAGAR', visible:true, width: 80, enableFiltering: false },
        { field: 'estado', type:'object', name: 'estado', displayName: 'ESTADO', minWidth:'150', width:140, enableFiltering: false, enableSorting:false,enableColumnMenus: false, enableColumnMenu: false, pinnedRight:true,
          cellTemplate:'<div class="ui-grid-cell-contents">' + 
                          '<label ng-click="grid.appScope.btnConfigConceptosEmp(row.entity,grid.appScope.callback);" style="cursor:pointer;width: 150px;" class="label {{ COL_FIELD.label }} ">{{ COL_FIELD.str_estado }}</label>'+
                        '</div>' 
        },  
      ],
      onRegisterApi: function(gridApi) {
        $scope.gridApi = gridApi;
        gridApi.selection.on.rowSelectionChanged($scope,function(row){
          $scope.mySelectionEmplGrid = gridApi.selection.getSelectedRows();
        });
        gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
          $scope.mySelectionEmplGrid = gridApi.selection.getSelectedRows();
        });

        $scope.gridApi.core.on.sortChanged($scope, function(grid, sortColumns) {
          //console.log(sortColumns);
          if (sortColumns.length == 0) {
            paginationOptionsEmpl.sort = null;
            paginationOptionsEmpl.sortName = null;
          } else {
            paginationOptionsEmpl.sort = sortColumns[0].sort.direction;
            paginationOptionsEmpl.sortName = sortColumns[0].name;
          }
          $scope.getPaginationEmplServerSide();
        });
        gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
          paginationOptionsEmpl.pageNumber = newPage;
          paginationOptionsEmpl.pageSize = pageSize;
          paginationOptionsEmpl.firstRow = (paginationOptionsEmpl.pageNumber - 1) * paginationOptionsEmpl.pageSize;
          $scope.getPaginationEmplServerSide(true);
        });
        $scope.gridApi.core.on.filterChanged( $scope, function(grid, searchColumns) {
          var grid = this.grid;
          paginationOptionsEmpl.search = true;
          // console.log(grid.columns);
          // console.log(grid.columns[1].filters[0].term);
          paginationOptionsEmpl.searchColumn = { 
            'empl.idempleado' : grid.columns[1].filters[0].term,
            'numero_documento' : grid.columns[2].filters[0].term,
            "empl.nombres || ' ' || empl.apellido_paterno || ' ' || empl.apellido_materno" : grid.columns[3].filters[0].term,
            'cc.nombre_cc' : grid.columns[4].filters[0].term,
            's.descripcion' : grid.columns[5].filters[0].term,
          }
          $scope.getPaginationEmplServerSide();
        });
      }
    };
    paginationOptionsEmpl.sortName = $scope.gridOptionsEmpl.columnDefs[0].name;
    $scope.getPaginationEmplServerSide = function(block) {
      if(block){
        blockUI.start('Cargando empleados...');
      }
      
      $scope.datosGrid = {
        paginate : paginationOptionsEmpl,
        planilla: $scope.mySelectionGrid[0],
      };
      empleadoPlanillaServices.sListarEmpleadosPlanilla($scope.datosGrid).then(function (rpta) {
        $scope.gridOptionsEmpl.totalItems = rpta.paginate.totalRows;
        $scope.gridOptionsEmpl.data = rpta.datos;
        if(block){          
          blockUI.stop();          
        }
      });
      $scope.mySelectionEmplGrid = [];

    };      

    $scope.btnEmpleados = function (size) {
      $scope.changeViewEmpleado(true);
      $scope.getPaginationEmplServerSide(true);
    }
    
    $scope.btnCierre = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          $scope.fData = $scope.mySelectionGrid[0];
          console.log($scope.fData );
          aperturaPlanillaServices.sCierrePlanilla($scope.fData).then(function(rpta){
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
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
          });
        }
      });
    }

    $scope.btnApertura = function (size) { 
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Planilla/ver_popup_apertura_planilla',
        size: size || ' ',
        backdrop: 'static',
        keyboard:false,
        controller: function ($scope, $modalInstance, getPaginationServerSide, empresa) {
          $scope.getPaginationServerSide = getPaginationServerSide;          
          $scope.fData = {};
          $scope.titleForm = 'Apertura de planilla';
          $scope.fData.empresa = empresa;
          console.log('$scope.fData.empresa', $scope.fData.empresa);

          var meses = ["Enero", "Febrero", "Marzo", 
                        "Abril", "Mayo", "Junio", 
                        "Julio", "Agosto", "Septiembre", 
                        "Octubre", "Noviembre", "Diciembre"];

          $scope.comboMeses = [{id:0, descripcion:"ENERO"},
                            {id:1, descripcion:"FEBRERO"},
                            {id:2, descripcion:"MARZO"},
                            {id:3, descripcion:"ABRIL"},
                            {id:4, descripcion:"MAYO"},
                            {id:5, descripcion:"JUNIO"},
                            {id:6, descripcion:"JULIO"},
                            {id:7, descripcion:"AGOSTO"},
                            {id:8, descripcion:"SEPTIEMBRE"},
                            {id:9, descripcion:"OCTUBRE"},
                            {id:10, descripcion:"NOVIEMBRE"},
                            {id:11, descripcion:"DICIEMBRE"}];

          
          //console.log($scope.comboAnios);
          var id = moment().months();          
          var objIndex = $scope.comboMeses.filter(function(obj) {
            return obj.id == id;
          }).shift(); 
          $scope.fData.periodoPlanilla = objIndex;

          $scope.comboAnios = [];
          var i=-1;
          for(i=-1;i<5;i++){
            var year = moment().add(i,'year').format('YYYY');
            $scope.comboAnios.push({id:year,descripcion:year});
          } 
          $scope.fData.anioPlanilla = $scope.comboAnios[1];

          planillaMasterServices.sListarPlanillasMasterCbo($scope.fData).then(function(rpta){
            $scope.listaPlanillasMaster = rpta.datos;
            $scope.listaPlanillasMaster.splice(0,0,{ id : '0', descripcion:'-- Seleccione Planilla --'});
            $scope.fData.planillaMaster = $scope.listaPlanillasMaster[0];
          });

          var dia = moment().date();
          var momentDesde;
          var momentHasta;
          //var dia = 26;
          if(dia < 26){
            momentDesde = moment().subtract(1,'months').date(26);
            momentHasta = moment().date(25);
            $scope.fData.desde =  $filter('date')(momentDesde.toDate(),'dd-MM-yyyy'); 
            $scope.fData.hasta =  $filter('date')(momentHasta.toDate(),'dd-MM-yyyy'); 
          }else{
            momentDesde = moment().date(26);
            momentHasta = moment().add(1,'months').date(25);
            $scope.fData.desde =  $filter('date')(momentDesde.toDate(),'dd-MM-yyyy'); 
            $scope.fData.hasta =  $filter('date')(momentHasta.toDate(),'dd-MM-yyyy'); 
          }       

          console.log('$scope.fData.desde', $scope.fData.desde);
          console.log('$scope.fData.hasta', $scope.fData.hasta);

          $scope.cargaNombrePlanilla = function(){
            momentDesde = moment().year($scope.fData.anioPlanilla.id).months($scope.fData.periodoPlanilla.id).subtract(1,'months').date(26);
            momentHasta = moment().year($scope.fData.anioPlanilla.id).months($scope.fData.periodoPlanilla.id).date(25);
            $scope.fData.desde =  $filter('date')(momentDesde.toDate(),'dd-MM-yyyy'); 
            $scope.fData.hasta =  $filter('date')(momentHasta.toDate(),'dd-MM-yyyy'); 

            if($scope.fData.planillaMaster.id != 0){
              $scope.fData.descripcion = $scope.fData.planillaMaster.descripcion 
                                          + ' ' + $scope.fData.periodoPlanilla.descripcion 
                                          + ' ' + $scope.fData.anioPlanilla.id;
              $scope.fData.descripcion = $scope.fData.descripcion.toUpperCase();
            }else{
              $scope.fData.descripcion = '';
            }
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            $scope.getPaginationServerSide();
          }

          $scope.aceptar = function () {             
            aperturaPlanillaServices.sAperturaPlanilla($scope.fData).then(function(rpta){
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                $scope.cancel();
              }else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
            });
          }
        }, 
        resolve: {
          getPaginationServerSide: function() {
            return $scope.getPaginationServerSide;
          },
          empresa: function() {
            return $scope.fBusqueda.empresa;
          },
        }
      });           
    }

    $scope.callback = function(value){
      $scope.getPaginationEmplServerSide(value);
    }

    $scope.btnConfigConceptosEmp = function(row, callback){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'Planilla/ver_popup_concepto_empl_planilla',
        size: 'xlg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance, planilla) {
          $scope.titleForm = 'Conceptos de Empleado.';     
          $scope.currentTab = 'Remuneraciones';
          $scope.indextipoCpt = 1;
          $scope.indexCat = 1;
          $scope.estadoPlanilla = $scope.mySelectionGrid[0].estado_pl;
          $scope.onClickTab = function (tabDescripcion,index) {
            console.log(tabDescripcion,index);
            $scope.currentTab = tabDescripcion;
            $scope.indextipoCpt = index;
          } 
          $scope.isActiveTab = function(tabDescripcion) {
            //console.log(tabDescripcion);
              return tabDescripcion == $scope.currentTab;
          }
          $scope.setIndexCat = function () {    
            $scope.indexCat = $scope.indexCat + 1;
            console.log($scope.indexCat);
          }
          $scope.setEstadoConceptoBoolean=function(){
            angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
              angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                  if(concepto.estado_pc_empleado == 1){
                    $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = true;
                    /*if(concepto.codigo_plame == '0605'){
                      $scope.activarRemAcumAnt = true;
                    }*/
                  }else{
                    $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = false;
                    /*if(concepto.codigo_plame == '0605'){
                      $scope.activarRemAcumAnt = false;
                    }*/
                  }                    
                });
              });
            });
          } 
          // $scope.setEstadoConceptoBoolean();        
          $scope.setEstadoConcepto= function(index, indexCat, indexConcepto){
            if($scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo){
              $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 1;              
            }else{
              $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 2;
              $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].valor_empleado = 0;
            }   
          }
          $scope.asignarValorConcepto = function(codigo_plame, value){
            angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
              angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                  if(concepto.codigo_plame == codigo_plame){
                    if(concepto.estado_pc_empleado == 1){
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].valor_empleado = value;
                    }else{
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].valor_empleado = 0;
                    }                    
                  }
                });
              });
            });
          }

          $scope.asignarEstadoConcepto = function(codigo_plame, value){
            angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
              angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                  if(concepto.codigo_plame == codigo_plame){
                    $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = value;                                        
                  }
                });
              });
            });
          }

          $scope.obtenerValorConcepto = function(codigo_plame){
            var value = 0;
            angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
              angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                  if(concepto.codigo_plame == codigo_plame){
                    var valor = $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].valor_empleado;
                    if(!isNaN(valor) && $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado == 1){
                      value = valor;
                    }else{
                      value = 0; 
                    }
                  }
                });
              });
            });
            return value;
          }

          $scope.obtenerEstadoConcepto = function(codigo_plame){
            var value = 2;
            angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
              angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                  if(concepto.codigo_plame == codigo_plame){
                    value = $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado;
                  }
                });
              });
            });
            return value;
          }

          $scope.actualizaRemuneracion = function(){
            var sueldo_basico = parseFloat($scope.fData.sueldo_basico);
            var sueldo_base = parseFloat($scope.obtenerValorConcepto('0121'));
            var horasEx25 = parseFloat($scope.obtenerValorConcepto('0105'));
            var horasEx35 = parseFloat($scope.obtenerValorConcepto('0106'));
            var tardanzas = parseFloat($scope.obtenerValorConcepto('0704'));
            var asignacionFamiliar = parseFloat($scope.obtenerValorConcepto('0201'));
            var vacaciones = 0;
            var vacaciones_no_computables = 0;
            if($scope.obtenerEstadoConcepto('0118')==1){
              vacaciones = parseFloat($scope.fData.empleado.calculoVacaciones.total_computable);
              vacaciones_no_computables = parseFloat($scope.fData.empleado.calculoVacaciones.total_no_computable);
            }
            
            var vacacionesTruncas = parseFloat($scope.obtenerValorConcepto('0114'));
            var gratificacionesTruncas = parseFloat($scope.obtenerValorConcepto('0407'));
            var bonificacion = parseFloat($scope.obtenerValorConcepto('0313'));
            var cts = parseFloat($scope.obtenerValorConcepto('0904'));

            var reintegros = 0;
            //console.log(sueldo_basico, horasEx25, horasEx35, tardanzas, asignacionFamiliar, vacaciones , reintegros);
            $scope.fData.total_remuneracion_computable = (sueldo_basico + horasEx25 + horasEx35 - tardanzas + asignacionFamiliar + vacaciones + reintegros).toFixed(2);
            //console.log('tardanzas',tardanzas);
           // console.log('total_remuneracion_computable ',$scope.fData.total_remuneracion_computable);
            var movilidad = parseFloat($scope.obtenerValorConcepto('0909')); 
            var condicion_trabajo = parseFloat($scope.obtenerValorConcepto('0917')); 
            var refrigerio = parseFloat($scope.obtenerValorConcepto('0914'));            

            var noDeducibles = 0;            
            //console.log('$scope.fData.movilidad',$scope.fData.movilidad);
            if($scope.obtenerEstadoConcepto('0909')==1){
              var movilidad_base=0;
              if(!isNaN($scope.fData.movilidad) && $scope.fData.movilidad != null && $scope.fData.movilidad != ''){
                movilidad_base=$scope.fData.movilidad;
              }
              //console.log('movilidad',movilidad);
              if(parseInt($scope.fData.faltas)>0){
                noDeducibles += (((parseFloat(movilidad_base)/30) * parseInt($scope.fData.dias_trabajados)) -  parseFloat(movilidad));              
              }
            }

            if($scope.obtenerEstadoConcepto('0917')==1){
              var condicion_trabajo_base = 0;
              //console.log('$scope.fData.condicion_trabajo',$scope.fData.condicion_trabajo);
              if(!isNaN($scope.fData.condicion_trabajo) && $scope.fData.condicion_trabajo != null && $scope.fData.condicion_trabajo != ''){
                condicion_trabajo_base =$scope.fData.condicion_trabajo;
              }
              //console.log('condicion_trabajo',condicion_trabajo);
              if(parseInt($scope.fData.faltas)>0){              
                noDeducibles += (((parseFloat(condicion_trabajo_base)/30) * parseInt($scope.fData.dias_trabajados)) -  parseFloat(condicion_trabajo));                 
              }
            }

            if($scope.obtenerEstadoConcepto('0914')==1){
              var refrigerio_base = 0;
              //console.log('$scope.fData.condicion_trabajo',$scope.fData.condicion_trabajo);
              if(!isNaN($scope.fData.refrigerio) && $scope.fData.refrigerio != null && $scope.fData.refrigerio != ''){
                refrigerio_base =$scope.fData.refrigerio;
              }

              //console.log('refrigerio',refrigerio);
              if(parseInt($scope.fData.faltas)>0){
                noDeducibles += (((parseFloat(refrigerio_base)/30) * parseInt($scope.fData.dias_trabajados)) -  parseFloat(refrigerio));                
              }             
            }

            //console.log('noDeducibles',noDeducibles);
            $scope.asignarValorConcepto('0706' ,parseFloat(noDeducibles).toFixed(2));

            //console.log(parseFloat($scope.fData.total_remuneracion_computable), movilidad, condicion_trabajo);
            $scope.fData.total_remuneracion = (parseFloat($scope.fData.total_remuneracion_computable) + 
                                                vacaciones_no_computables + 
                                                movilidad + condicion_trabajo + refrigerio + 
                                                gratificacionesTruncas + bonificacion +
                                                cts
                                              ).toFixed(2);  

            var aporteEsSalud = 0;
            if($scope.obtenerEstadoConcepto('0804')==1){
              if($scope.fData.total_remuneracion_computable > parseFloat($scope.variablesLey.rmv)){
                aporteEsSalud = (parseFloat($scope.fData.total_remuneracion_computable) * parseFloat($scope.variablesLey.essalud) /100).toFixed(2);
              }else{
                aporteEsSalud = (parseFloat($scope.variablesLey.rmv) * parseFloat($scope.variablesLey.essalud) /100).toFixed(2); 
              }
            }
            $scope.asignarValorConcepto('0804' ,aporteEsSalud);    
            $scope.fData.total_aportes = (aporteEsSalud);
            /*calculo regimen pensionario*/
            if($scope.fData.empleado.reg_pensionario == 'ONP'){
              $scope.fData.remuneracionRegPensionario = sueldo_basico + horasEx25 + horasEx35 - tardanzas + asignacionFamiliar + vacaciones + reintegros;
              var aporteONP = $scope.fData.remuneracionRegPensionario * parseFloat($scope.variablesLey.onp) / 100; 
              $scope.asignarValorConcepto('0607' ,(aporteONP).toFixed(2)); 
              $scope.asignarValorConcepto('0608' ,0);
              $scope.asignarValorConcepto('0601' ,0);
              $scope.asignarValorConcepto('0606' ,0);
            }else if($scope.fData.empleado.reg_pensionario == 'AFP'){
              var subMaternidad = parseFloat($scope.obtenerValorConcepto('0915'));
              var subIncEnfermedad = parseFloat($scope.obtenerValorConcepto('0916'));
              $scope.fData.remuneracionRegPensionario = sueldo_basico + horasEx25 + horasEx35 
                                                        - tardanzas + asignacionFamiliar + vacaciones 
                                                        + reintegros + subMaternidad + subIncEnfermedad;
              var remuneracionRegPensionario = $scope.fData.remuneracionRegPensionario;                                        
              if($scope.fData.remuneracionRegPensionario > parseFloat($scope.variablesLey.remun_max_asegurable)){
                remuneracionRegPensionario = parseFloat($scope.variablesLey.remun_max_asegurable);
              }
              var aporteObligatorio = remuneracionRegPensionario * parseFloat($scope.fData.empleado.a_oblig) / 100;
              var seguro = remuneracionRegPensionario * parseFloat($scope.fData.empleado.p_seguro) / 100;
              var comision = 0;
              if($scope.fData.empleado.tipo_comision == 'MIXTA'){
                comision = remuneracionRegPensionario * $scope.fData.empleado.comision_m / 100;
              }else if($scope.fData.empleado.tipo_comision == 'FLUJO'){
                comision = remuneracionRegPensionario * $scope.fData.empleado.comision / 100;
              }
              $scope.asignarValorConcepto('0608' ,(aporteObligatorio).toFixed(2));
              $scope.asignarValorConcepto('0601' ,(comision).toFixed(2));
              $scope.asignarValorConcepto('0606' ,(seguro).toFixed(2));
              $scope.asignarValorConcepto('0607' ,0);
            }
            //console.log('$scope.fData.remuneracionRegPensionario',$scope.fData.remuneracionRegPensionario);
            /*calculo renta 5ta */
            $scope.fData.remuneracionRentaQuinta = sueldo_base + horasEx25 + horasEx35 + asignacionFamiliar + vacaciones + reintegros + movilidad + refrigerio;
            //console.log('$scope.fData.remuneracionRentaQuinta',sueldo_base , horasEx25 , horasEx35 , asignacionFamiliar , vacaciones , reintegros , movilidad);
            if($scope.obtenerEstadoConcepto('0605')==1){ // RENTA QUINTA
              var datosQuinta = angular.copy($scope.fData);
              datosQuinta.gratificacion_trunca = gratificacionesTruncas;
              aperturaPlanillaServices.sCalcularRentaQuinta(datosQuinta).then(function(rpta){
                $scope.asignarValorConcepto('0605', rpta.datos);
                var onp = parseFloat($scope.obtenerValorConcepto('0607'));
                var afpObligatorio = parseFloat($scope.obtenerValorConcepto('0608'));
                var afpComision = parseFloat($scope.obtenerValorConcepto('0601'));
                var afpSeguro = parseFloat($scope.obtenerValorConcepto('0606'));
                var quinta = parseFloat($scope.obtenerValorConcepto('0605'));

                $scope.fData.total_descuentos = (onp + afpObligatorio + afpComision + afpSeguro + quinta).toFixed(2);
                $scope.fData.total_neto = (parseFloat($scope.fData.total_remuneracion) - parseFloat($scope.fData.total_descuentos)).toFixed(2);
              });
            }else{
              var onp = parseFloat($scope.obtenerValorConcepto('0607'));
              var afpObligatorio = parseFloat($scope.obtenerValorConcepto('0608'));
              var afpComision = parseFloat($scope.obtenerValorConcepto('0601'));
              var afpSeguro = parseFloat($scope.obtenerValorConcepto('0606'));
              $scope.fData.total_descuentos = (onp + afpObligatorio + afpComision + afpSeguro).toFixed(2);
              $scope.fData.total_neto = (parseFloat($scope.fData.total_remuneracion) - parseFloat($scope.fData.total_descuentos)).toFixed(2);
            }            
          }

          $scope.activaRegimenPensionario = function(){
            angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
              angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                  if(concepto.codigo_plame == '0607'){
                    if($scope.fData.empleado.reg_pensionario=='ONP'){
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = true;
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 1;                  
                    }else{
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = false;
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 2; 
                    }
                  }

                  if(concepto.codigo_plame == '0608' || concepto.codigo_plame == '0601'  || concepto.codigo_plame == '0606' ){
                    if($scope.fData.empleado.reg_pensionario=='AFP'){
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = true;
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 1;                  
                    }else{
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = false;
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 2; 
                    }
                  }                    
                });
              });
            });
          }

          $scope.activaRemuneracionVacaciones = function(){
            var datos = {
              empleado : $scope.fData.empleado, 
              empresa  : $scope.fBusqueda.empresa,
              planilla : $scope.fData.planilla,
            };

            aperturaPlanillaServices.sCalcularVacacionesEmpleado(datos).then(function (rpta){
              console.log('vacaciones', rpta); 
              $scope.fData.empleado.calculoVacaciones = rpta.datos;       
              var vacaciones;    
              angular.forEach($scope.fData.planilla.conceptos, function(tipoConcepto, index) {
                angular.forEach(tipoConcepto.categorias, function(categoria, indexCat) {
                  angular.forEach(categoria.conceptos, function(concepto, indexConcepto) {
                    if(concepto.codigo_plame == '0118'){
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].boolBloqueo = true;
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].estado_pc_empleado = 1;

                      var vacaciones = ($scope.fData.empleado.calculoVacaciones.total_computable + $scope.fData.empleado.calculoVacaciones.total_no_computable).toFixed(2)
                      $scope.fData.planilla.conceptos[index].categorias[indexCat].conceptos[indexConcepto].valor_empleado = vacaciones;
                      $scope.actualizaRemuneracion();
                    }                    
                  });
                });
              });
              
            });            
          }

          $scope.actualizaConceptos = function(input, arranque = false){            
            //console.log('input', input);
            var faltas = 0;
            //console.log('faltas scope exterior ', $scope.fData.faltas );
            if( $scope.fData.faltas == null || $scope.fData.faltas == '' ){
              //console.log('faltas vacio',faltas );
            }else{
              faltas = parseInt($scope.fData.faltas);
              //console.log('faltas scope',$scope.fData.faltas );
            }

            var dias_trabajados = 0;
            if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
              dias_trabajados = parseInt($scope.fData.dias_trabajados);
            }else{
              return;
            }

            switch ( input ) { 
              case 'sueldo_base':
                if(isNaN($scope.fData.horas_diarias) || $scope.fData.horas_diarias == null || $scope.fData.horas_diarias == ''){
                  pinesNotifications.notify({ title: 'Aviso', text: 'Debe ingresar un numero de horas diarias', type: 'warning', delay: 2500 });
                  return;
                }               

                var sueldo_base  = 0;
                if(!isNaN($scope.fData.sueldo_base) && $scope.fData.sueldo_base != null && $scope.fData.sueldo_base != ''){                  
                  sueldo_base = $scope.fData.sueldo_base; 
                }

                var importe_faltas = (parseFloat(sueldo_base)/30 * faltas).toFixed(2);
                //console.log('importe_faltas', importe_faltas);

                $scope.asignarValorConcepto('0705',importe_faltas);
                importe_faltas = parseFloat($scope.obtenerValorConcepto('0705'));
               
                //cambia remuneracion computable 
                $scope.asignarValorConcepto('0121',parseFloat((parseFloat(sueldo_base) / 30) * dias_trabajados));

                //actualiza sueldo
                $scope.fData.sueldo_basico = (((parseFloat(sueldo_base) / 30) * dias_trabajados ) - importe_faltas).toFixed(2);                               


                //cambia costo hora
                $scope.fData.costo_hora_trabajada = (parseFloat($scope.fData.sueldo_basico) / dias_trabajados / parseFloat($scope.fData.horas_diarias)).toFixed(2);
                if(!arranque){
                  //actualiza faltas
                  $scope.actualizaConceptos('faltas');            

                  //actualiza costo de horas extras
                  $scope.actualizaConceptos('horas25');
                  $scope.actualizaConceptos('horas35');
                }

                //$scope.actualizaRemuneracion();
              break;

              case 'tardanzas':
                var importe_tardanza = (parseFloat($scope.fData.costo_hora_trabajada)/60*parseFloat($scope.fData.tardanzas)).toFixed(2);
                $scope.asignarValorConcepto('0704',importe_tardanza);
                importe_tardanza = parseFloat($scope.obtenerValorConcepto('0704'));
                if(!arranque){
                  $scope.actualizaRemuneracion();
                } 
              break;

              case 'faltas':          
                var importe_faltas = 0;
                /*var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }*/
                var sueldo_base  = 0;
                if(!isNaN($scope.fData.sueldo_base) && $scope.fData.sueldo_base != null && $scope.fData.sueldo_base != ''){                  
                  sueldo_base = $scope.fData.sueldo_base; 
                }
                //var sueldo_base = parseFloat($scope.obtenerValorConcepto('0121'));
                //console.log('sueldo_base', sueldo_base);
                importe_faltas = (parseFloat(sueldo_base)/30 * faltas).toFixed(2);
                //console.log('importe_faltas', importe_faltas);
                $scope.asignarValorConcepto('0705',importe_faltas);
                importe_faltas = parseFloat($scope.obtenerValorConcepto('0705')); 

                //actualiza sueldo
                $scope.fData.sueldo_basico = (((sueldo_base / 30) * dias_trabajados ) - importe_faltas).toFixed(2);  

                //cambia costo hora
                $scope.fData.costo_hora_trabajada = (parseFloat($scope.fData.sueldo_basico)/ dias_trabajados / parseFloat($scope.fData.horas_diarias)).toFixed(2);
                if(!arranque){
                  //actualiza movilidad y condicion laboral
                  $scope.actualizaConceptos('movilidad');
                  $scope.actualizaConceptos('condicion_trabajo');              
                  $scope.actualizaConceptos('refrigerio');              
                  $scope.actualizaConceptos('tardanzas');              

                  //actualiza costo de horas extras
                  $scope.actualizaConceptos('horas25');
                  $scope.actualizaConceptos('horas35');
                }            
              break;

              case 'horas25':          
                //cambia monto horas extras 25%              
                /*var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }*/

                var horas_extras25 = 0;
                if(!isNaN($scope.fData.horas_extras25) && $scope.fData.horas_extras25 != null && $scope.fData.horas_extras25 != ''){
                  horas_extras25 = parseInt($scope.fData.horas_extras25);
                }

                var costo_hora_trabajada = (parseFloat($scope.fData.sueldo_basico)/ dias_trabajados / parseFloat($scope.fData.horas_diarias));
                var costo25 =parseFloat(costo_hora_trabajada) * 1.25;
                var horas25 = (parseFloat(horas_extras25)  * costo25 ).toFixed(2);
                $scope.asignarValorConcepto('0105',horas25);
                if(!arranque){
                  $scope.actualizaRemuneracion();
                }              
              break;

              case 'horas35': 
                //cambia monto horas extras 35%           
                /*var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }*/

                var horas_extras35 = 0;
                if(!isNaN($scope.fData.horas_extras35) && $scope.fData.horas_extras35 != null && $scope.fData.horas_extras35 != ''){
                  horas_extras35 = parseInt($scope.fData.horas_extras35);
                }

                var costo_hora_trabajada = (parseFloat($scope.fData.sueldo_basico)/ dias_trabajados / parseFloat($scope.fData.horas_diarias));
                var costo35 = parseFloat(costo_hora_trabajada) * 1.35;
                var horas35 = (parseFloat(horas_extras35)  * costo35 ).toFixed(2);
                $scope.asignarValorConcepto('0106',horas35);
                if(!arranque){
                  $scope.actualizaRemuneracion();
                }           
              break;

              case 'dias_trabajados':                
                /*var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }else{
                  return;
                }*/

                //var sueldo_base = parseFloat($scope.obtenerValorConcepto('0121'));
                var sueldo_base  = 0;
                if(!isNaN($scope.fData.sueldo_base) && $scope.fData.sueldo_base != null && $scope.fData.sueldo_base != ''){                  
                  sueldo_base = $scope.fData.sueldo_base; 
                }
                var importe_faltas = parseFloat($scope.obtenerValorConcepto('0705'));
               
                //actualiza sueldo

                $scope.fData.sueldo_basico = (((sueldo_base / 30) * dias_trabajados ) - importe_faltas).toFixed(2);
                $scope.fData.costo_hora_trabajada = (parseFloat($scope.fData.sueldo_basico)/ dias_trabajados / parseFloat($scope.fData.horas_diarias)).toFixed(2);
                if(!arranque){
                  //horas extras 
                  $scope.actualizaConceptos('horas25');
                  $scope.actualizaConceptos('horas35');
                  
                  //actualiza movilidad y condicion laboral
                  $scope.actualizaConceptos('movilidad');
                  $scope.actualizaConceptos('condicion_trabajo');
                  $scope.actualizaConceptos('refrigerio');
                }
              break;
              case 'movilidad':
                //cambia movilidad 
                /*var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }*/
                var  movilidad_base=0;
                //console.log('$scope.fData.movilidad',$scope.fData.movilidad);
                if(!isNaN($scope.fData.movilidad) && $scope.fData.movilidad != null && $scope.fData.movilidad != ''){
                  movilidad_base=$scope.fData.movilidad;
                }
                if($scope.obtenerEstadoConcepto('0705') == 1){
                  var movilidad = ( (parseFloat(movilidad_base)/30) * ( dias_trabajados - faltas ) ).toFixed(2);
                  console.log('movilidad ',movilidad );

                }else{
                  var movilidad = ( (parseFloat(movilidad_base)/30) * dias_trabajados ).toFixed(2);
                }
                $scope.asignarValorConcepto('0909',movilidad);
                if(!arranque){
                  $scope.actualizaRemuneracion();
                }
              break;

              case 'condicion_trabajo':
                //cambia condicion de trabajo 
               /* var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }*/
                var condicion_trabajo_base = 0;
                //console.log('$scope.fData.condicion_trabajo',$scope.fData.condicion_trabajo);
                if(!isNaN($scope.fData.condicion_trabajo) && $scope.fData.condicion_trabajo != null && $scope.fData.condicion_trabajo != ''){
                  condicion_trabajo_base =$scope.fData.condicion_trabajo;
                }
                if($scope.obtenerEstadoConcepto('0705') == 1){
                  var condicion_trabajo = ( (parseFloat(condicion_trabajo_base)/30) * ( dias_trabajados - faltas ) ).toFixed(2);
                }else{
                  var condicion_trabajo = ( (parseFloat(condicion_trabajo_base)/30) * dias_trabajados ).toFixed(2);
                }
                $scope.asignarValorConcepto('0917',condicion_trabajo);
                if(!arranque){
                  $scope.actualizaRemuneracion();
                }
              break;
              case 'refrigerio':                
                //cambia refrigerio 
                /*var dias_trabajados = 0;
                if(!isNaN($scope.fData.dias_trabajados) && $scope.fData.dias_trabajados != null && $scope.fData.dias_trabajados != ''){
                  dias_trabajados = parseInt($scope.fData.dias_trabajados);
                }*/
                var refrigerio_base = 0;
                //console.log('$scope.fData.condicion_trabajo',$scope.fData.condicion_trabajo);
                if(!isNaN($scope.fData.refrigerio) && $scope.fData.refrigerio != null && $scope.fData.refrigerio != ''){
                  refrigerio_base =$scope.fData.refrigerio;
                }
                if($scope.obtenerEstadoConcepto('0705') == 1){
                  var refrigerio = ( (parseFloat(refrigerio_base)/30) * ( dias_trabajados - faltas ) ).toFixed(2);
                }else{
                  var refrigerio = ( (parseFloat(refrigerio_base)/30) * dias_trabajados ).toFixed(2);
                }
                $scope.asignarValorConcepto('0914',refrigerio);
                if(!arranque){
                  $scope.actualizaRemuneracion();
                }
              break;

              default:                
            }            
          }
          // console.log('planilla', planilla);

          if(row.concepto_valor_json == null || row.concepto_valor_json.configuracion == null){            
            $scope.fData = {};
            $scope.fData.empleado = angular.copy(row);
            console.log('row',row);
            $scope.fData.planilla = angular.copy(planilla);
            $scope.fData.planilla.conceptos = angular.copy(planilla.conceptos_json.conceptos);
            $scope.fData.faltas_label = $scope.fData.empleado.faltas;
            $scope.fData.tardanzas_label = $scope.fData.empleado.tardanza;           
            $scope.fData.faltas = 0;
            $scope.fData.tardanzas = 0;
            $scope.fData.horas_diarias = 8;
            $scope.fData.dias_trabajados = 30;
            $scope.fData.movilidad = 0;
            $scope.fData.condicion_trabajo = 0;
            $scope.fData.refrigerio = 0;
            $scope.fData.horas_extras25 = 0;
            $scope.fData.horas_extras35 = 0;
            //$scope.activarRemAcumAnt = false;
            aperturaPlanillaServices.sObtenerVariablesLey().then(function(rpta){
              $scope.variablesLey = rpta.datos;
              // console.log('$scope.variablesLey',$scope.variablesLey);
              $scope.fData.empleado = angular.copy(row);
              $scope.activaRegimenPensionario();
              if($scope.fData.empleado.sueldo_contrato){
                $scope.fData.sueldo_base = $scope.fData.empleado.sueldo_contrato;
                $scope.actualizaConceptos('sueldo_base',true);
              }
            });           
          }else{ 
            $scope.fData = angular.copy(row.concepto_valor_json.configuracion);
            if($scope.fData.dias_trabajados == null || $scope.fData.dias_trabajados == '')
              $scope.fData.dias_trabajados = 30;    

            $scope.fData.empleado = angular.copy(row);
            $scope.fData.faltas_label = $scope.fData.empleado.faltas;
            $scope.fData.tardanzas_label = $scope.fData.empleado.tardanza;
            $scope.fData.planilla = angular.copy(planilla);
            $scope.fData.planilla.conceptos = angular.copy(row.concepto_valor_json.conceptos);
            if($scope.estadoPlanilla == 1){                          
              aperturaPlanillaServices.sObtenerVariablesLey().then(function(rpta){
                $scope.variablesLey = rpta.datos;           
                //$scope.fData.sueldo_base = $scope.obtenerValorConcepto('0121');
                $scope.actualizaConceptos('sueldo_base',true);
                $scope.actualizaConceptos('faltas',true);
                $scope.actualizaConceptos('tardanzas',true);
                $scope.actualizaConceptos('movilidad',true);
                $scope.actualizaConceptos('refrigerio',true);
                $scope.actualizaConceptos('condicion_trabajo',true);
                console.log('pl abierta - VL:',$scope.variablesLey);
                $scope.activaRegimenPensionario();
                $scope.actualizaRemuneracion();
              });
            }else{
              $scope.variablesLey = planilla.conceptos_json.variables_ley;  
              console.log('planilla:',planilla);            
              console.log('pl cerrada - VL:',$scope.variablesLey);            
              //$scope.fData.sueldo_base = $scope.obtenerValorConcepto('0121');
              $scope.actualizaConceptos('sueldo_base',true);
              $scope.actualizaConceptos('faltas',true);
              $scope.actualizaConceptos('tardanzas',true);
              $scope.actualizaConceptos('movilidad',true);
              $scope.actualizaConceptos('refrigerio',true);
              $scope.actualizaConceptos('condicion_trabajo',true);             
              $scope.activaRegimenPensionario();
              $scope.actualizaRemuneracion();
            }
          }
          $scope.fData.dias_vacaciones = angular.copy(row.calculos_asistencia.diasVacaciones);
          if($scope.fData.dias_vacaciones != '' && parseInt($scope.fData.dias_vacaciones) > 0){
            $scope.activaRemuneracionVacaciones();
          }else{
            $scope.asignarValorConcepto('0118',0);
            $scope.asignarEstadoConcepto('0118',2);
          }

          $scope.setEstadoConceptoBoolean();          
          // console.log('$scope.fData',$scope.fData);
          $scope.titleForm = 'Conceptos de Empleado.';          

          $scope.cambioConcepto = function(index, indexCat, indexConcepto, codigo_plame){
            //console.log('codigo_plame', codigo_plame);
            switch ( codigo_plame ){ 
              case '0201':
                $scope.actualizaRemuneracion();
              break;

              default: 
                $scope.actualizaRemuneracion();
            }                        
          }          
          
          $scope.changeEstadoConcepto = function(index, indexCat, indexConcepto, codigo_plame){
            //console.log('codigo_plame', codigo_plame);
            /** Activar intup Rem. Anteriores **/
            /*if(codigo_plame == '0605' && !$scope.activarRemAcumAnt){
              $scope.activarRemAcumAnt = true;
            }else if(codigo_plame == '0605' && $scope.activarRemAcumAnt){
              $scope.activarRemAcumAnt = false;
            }*/

            $scope.setEstadoConcepto(index, indexCat, indexConcepto);
            switch ( codigo_plame ) { 
              case '0121':
                $scope.actualizaConceptos('sueldo_base');
              break;
              case '0105':
                $scope.actualizaConceptos('horas25');
              break;
              case '0106':
                $scope.actualizaConceptos('horas35');
              break;
              case '0201':
                if($scope.obtenerEstadoConcepto('0201') == 2){
                  var asignacionFamiliar = 0;
                }else{
                  var asignacionFamiliar = parseFloat($scope.variablesLey.rmv) * parseFloat($scope.variablesLey.asignacion_familiar) / 100;
                }                
                $scope.asignarValorConcepto('0201',parseFloat(asignacionFamiliar));
                $scope.actualizaRemuneracion();
              break;
              case '0909':                
                if($scope.obtenerEstadoConcepto('0909') == 2){
                  $scope.fData.movilidad = 0;
                }
                $scope.actualizaConceptos('movilidad');

              break;
              case '0917':
                if($scope.obtenerEstadoConcepto('0917') == 2){
                  $scope.fData.condicion_trabajo = 0;
                }
                $scope.actualizaConceptos('condicion_trabajo');

              break;
              case '0914':
                if($scope.obtenerEstadoConcepto('0914') == 2){
                  $scope.fData.refrigerio = 0;
                }
                $scope.actualizaConceptos('refrigerio');

              break;
              case '0704':
                $scope.actualizaConceptos('tardanzas');
              break;
              case '0705':
                $scope.actualizaConceptos('faltas');
              break;

              case '0605':
                if($scope.obtenerEstadoConcepto('0605') == 2){
                  $scope.fData.remuneracion_acum = 0;
                  $scope.fData.retencion_acum = 0;
                }
                $scope.actualizaRemuneracion();                
              break;
              default: 
                $scope.actualizaRemuneracion();
            }
          }

          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fData = {};
            callback(true);
          }

          $scope.aceptar = function () {                      
            blockUI.start('Actualizando configuración de empleado...');
            aperturaPlanillaServices.sRegistrarConfigConceptos($scope.fData).then(function(rpta){
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                callback(false);
                //$scope.cancel();
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
          $scope.aceptarSalir = function () {                      
            blockUI.start('Actualizando configuración de empleado...');
            aperturaPlanillaServices.sRegistrarConfigConceptos($scope.fData).then(function(rpta){
              if(rpta.flag == 1){
                pTitle = 'OK!';
                pType = 'success';
                callback(false);
                $scope.cancel();
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
        }, 
        resolve: {
          planilla: function() {
            return $scope.mySelectionGrid[0];
          },
        }
      });
    }

    $scope.btnGeneraGratificaciones = function(row){
      console.log(row);
      var datos = row;
      datos.salida = 'excel';
      datos.empresa = $scope.fBusqueda.empresa;
      var arrParams = {          
        datos: datos,
        metodo: 'php',
      }     

      arrParams.url = angular.patchURLCI+'EmpleadoPlanilla/calcular_pago_gratificaciones';
      ModalReporteFactory.getPopupReporte(arrParams);
    }    

    $scope.btnGeneraCTS = function(row){
      console.log(row);
      var datos = row;
      datos.salida = 'excel';
      datos.empresa = $scope.fBusqueda.empresa;
      var arrParams = {          
        datos: datos,
        metodo: 'php',
      }     

      arrParams.url = angular.patchURLCI+'EmpleadoPlanilla/calcular_pago_cts';
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    /* VER VARIABLES DE LEY */
    $scope.btnVariablesLey = function (size){
      $uibModal.open({
        templateUrl: angular.patchURLCI+'ConfigVariable/ver_popup_config_variable',
        size: '',
        scope: $scope,
        controller: function ($scope, $modalInstance) {
          $scope.titleForm = 'Variables de Ley';
          $scope.pFecha = /^\d{2}-\d{2}-\d{4}$/;
          $scope.fDataVariables = {};
          aperturaPlanillaServices.sListarConfigVariable().then(function (rpta) {
            $scope.fDataVariables = rpta.datos;
            $scope.fDataVariables.oldUit = angular.copy($scope.fDataVariables.uit);
            $scope.fDataVariables.oldRmv = angular.copy($scope.fDataVariables.rmv);
            $scope.fDataVariables.oldOnp = angular.copy($scope.fDataVariables.onp);
            $scope.fDataVariables.oldRma = angular.copy($scope.fDataVariables.rma);
            $scope.fDataVariables.oldEssalud = angular.copy($scope.fDataVariables.essalud);
            $scope.fDataVariables.oldAsignacion_familiar = angular.copy($scope.fDataVariables.asignacion_familiar);
          });
          $scope.aceptar = function (){
            //blockUI.start('Ejecutando...');
            aperturaPlanillaServices.sRegistrarConfigVariable($scope.fDataVariables).then(function (rpta) {
              //blockUI.stop();
              if(rpta.flag == 1){
                var pTitle = 'Ok!';
                var pType = 'success';
              }
              else if(rpta.flag == 0){
                var pTitle = 'Aviso!';
                var pType = 'warning';
              }else{
                alert('Error inesperado');
              }

              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 1000 });
              $modalInstance.dismiss('cancel');
            });
            
          }
          $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
            $scope.fDataCambio = {};
          }
        }
      });
    }
    $scope.calcularPlanilla = function(tipo_calculo){
      var noConfigurado=false;
      angular.forEach( $scope.mySelectionEmplGrid, function(empl, indexEmpl) {
        if(empl.concepto_valor_json == null){
          noConfigurado=true;
        }                   
      });
      if(noConfigurado){
        pinesNotifications.notify({ title: 'Aviso', 
                                      text: 'Algunos empleados seleccionados no tienen configuración de conceptos.', 
                                      type: 'warning', 
                                      delay: 2500 });
        return;
      }   

      var datos = {
        tipo_calculo: tipo_calculo,
        planilla: $scope.mySelectionGrid[0],
        empleados: $scope.mySelectionEmplGrid,
        empresa: $scope.fBusqueda.empresa,
        salida: 'excel',
        titulo: $scope.mySelectionGrid[0].descripcion,
      }                  
      blockUI.start('Calculando Planilla...');
      aperturaPlanillaServices.sCalcularPlanillaEmpleado(datos).then(function(rpta){
        if(rpta.flag == 1){
          pTitle = 'OK!';
          pType = 'success';
          $scope.getPaginationEmplServerSide(false);
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

    /*actualizar JSON de planillas */
    $scope.btnActualizarJSON = function () {
      console.log($scope.mySelectionGrid[0]);
      var pMensaje = '¿Realmente desea realizar la acción?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          $scope.fData = $scope.mySelectionGrid[0];
          console.log($scope.fData );
          aperturaPlanillaServices.sActualizarJsonPlanilla($scope.fData).then(function(rpta){
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
            pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 2500 });
          });
        }
      });
    }
    /* IMPRESION PDF */
    $scope.btnImprimirboleta =function(){
      var arrParams = {
        titulo: 'BOLETA DE PAGO',
        datos:{
          planilla: $scope.mySelectionGrid[0],
          empleados: $scope.mySelectionEmplGrid,
          salida: 'pdf',
          tituloAbv: 'RH-BOL',
          titulo: 'BOLETA DE PAGO'
        },
        metodo: 'php'
      }
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/boleta_pago',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    /* IMPRESION PDF */
    $scope.btnImprimirCTS =function(){
      var arrParams = {
        titulo: 'CTS',
        datos:{
          planilla: $scope.mySelectionGrid[0],
          //empleados: $scope.mySelectionEmplGrid,
          salida: 'pdf',
          tituloAbv: 'RH-CTS',
          titulo: 'CTS'
        },
        metodo: 'php'
      }
      arrParams.url = angular.patchURLCI+'CentralReportesMPDF/reporte_cts',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    /* IMPRESION EXCEL */
    $scope.btnExportarExcel =function(){
      var arrParams = {
        titulo: 'PLANILLA',
        datos:{
          planilla: $scope.mySelectionGrid[0],
          //empleados: $scope.mySelectionEmplGrid,
          salida: 'excel',
          tituloAbv: 'RH-PLA',
          titulo: 'PLANILLA'
        },
        metodo: 'js'
      }
      arrParams.url = angular.patchURLCI+'CentralReportes/planilla_empleados',
      ModalReporteFactory.getPopupReporte(arrParams);
    }

    /* Ver Asientos Contables */
    $scope.btnAsientosContables =function(){
      blockUI.start('Abriendo Asientos Contables...');
      $uibModal.open({ 
        templateUrl: angular.patchURLCI+'AsientoContable/ver_popup_asientos_contables_planilla',
        size: 'lg',
        backdrop: 'static',
        keyboard:false,
        scope: $scope,
        controller: function ($scope, $modalInstance) {

          $scope.numberFormat = function(monto, decimales){
            monto += ''; // por si pasan un numero en vez de un string
            monto = parseFloat(monto.replace(/[^0-9\.\-]/g, '')); // elimino cualquier cosa que no sea numero o punto
            decimales = decimales || 0; // por si la variable no fue pasada
            // si no es un numero o es igual a cero retorno el mismo cero
            if (isNaN(monto) || monto === 0) 
                return parseFloat(0).toFixed(decimales);
            // si es mayor o menor que cero retorno el valor formateado como numero
            monto = '' + monto.toFixed(decimales);
            var monto_partes = monto.split('.'),
                regexp = /(\d+)(\d{3})/;
            while (regexp.test(monto_partes[0]))
                monto_partes[0] = monto_partes[0].replace(regexp, '$1' + ',' + '$2');
            return monto_partes.join('.');
          }

          $scope.titleForm = 'Asiento Contable'; 
          $scope.fDataDetalle = $scope.mySelectionGrid[0];         
          
          aperturaPlanillaServices.sCargarAsientoContable($scope.fDataDetalle).then(function(rpta){
            $scope.fDataDetalle.data = rpta.asiento_contable;
            $scope.fDataDetalle.dataProv = rpta.asiento_provisiones;
            $scope.fDataDetalle.total_a_pagar=rpta.total_haber;
            $scope.fDataDetalle.total_provisiones=rpta.total_haber_prov;
            console.log($scope.fDataDetalle.dataProv);
            blockUI.stop();
          });

          /*var arrParams = {
            datos: $scope.mySelectionGrid[0].id
          };
          asientoContableServices.sListarAsientoContablePlanilla(arrParams).then(function (rpta) {
            $scope.fDataDetalle.data = rpta.datos;
            $scope.fDataDetalle.fecha_emision=rpta.datos[0].fecha_emision;
            $scope.fDataDetalle.glosa=rpta.datos[0].glosa;
            $scope.fDataDetalle.total_a_pagar=rpta.total_pagar;
          });*/
          


          $scope.cancel = function(){ 
            $modalInstance.dismiss('cancel');
          }
        }
      });
    }

  }])
  .service("aperturaPlanillaServices",function($http, $q) {
    return({
        sListarPlanillas: sListarPlanillas,
        sCierrePlanilla: sCierrePlanilla,
        sAperturaPlanilla: sAperturaPlanilla,
        sCalcularRentaQuinta: sCalcularRentaQuinta,
        sRegistrarConfigConceptos: sRegistrarConfigConceptos,
        sListarConfigVariable:sListarConfigVariable,
        sRegistrarConfigVariable:sRegistrarConfigVariable,
        sObtenerVariablesLey:sObtenerVariablesLey,
        sCalcularPlanillaEmpleado: sCalcularPlanillaEmpleado,
        sActualizarJsonPlanilla:sActualizarJsonPlanilla,
        sCalcularPagoGratificaciones:sCalcularPagoGratificaciones,
        sCalcularVacacionesEmpleado: sCalcularVacacionesEmpleado,
        sCargarAsientoContable:sCargarAsientoContable,
    });
    function sListarPlanillas(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Planilla/lista_planillas", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sCierrePlanilla(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Planilla/cierre_planilla", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sAperturaPlanilla(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Planilla/apertura_planilla", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sCalcularRentaQuinta(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"EmpleadoPlanilla/calcular_renta_quinta", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sRegistrarConfigConceptos(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"EmpleadoPlanilla/registrar_configuracion_conceptos", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sListarConfigVariable (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConfigVariable/listar_config_variable", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sRegistrarConfigVariable (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"ConfigVariable/registrar_config_variable", 
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sObtenerVariablesLey(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Planilla/obtener_variables_ley", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sCalcularPlanillaEmpleado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"EmpleadoPlanilla/calcular_planilla_empleado", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    } 
    function sActualizarJsonPlanilla(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Planilla/actualizar_json_planilla", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCalcularPagoGratificaciones(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"EmpleadoPlanilla/calcular_pago_gratificaciones", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }    
    function sCalcularVacacionesEmpleado(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"EmpleadoPlanilla/calcular_vacaciones_empleado", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sCargarAsientoContable(pDatos) { 
      var datos = pDatos || {};
      var request = $http({
        method : "post",
        url : angular.patchURLCI+"Planilla/cargar_asiento_contable", 
        data : datos 
      });
      return (request.then( handleSuccess,handleError ));
    }
  });
  