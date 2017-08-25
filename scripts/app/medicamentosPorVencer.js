angular.module('theme.medicamentosPorVencer', ['theme.core.services'])
.controller('medicamentosPorVencerController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications', 'ModalReporteFactory',
  	'medicamentoPorVencerServices',
	function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications, ModalReporteFactory,
	  medicamentoPorVencerServices){
	'use strict';
	shortcut.remove("F2");
	$scope.modulo = 'medicamentosPorVencer';
	$scope.mySelectionGrid = [];
	$scope.fBusqueda = {};
	// TIPO DE VENCIMIENTOS
	$scope.listaTipoVence = [
		{ 'id': '0', 'descripcion': 'VER TODOS' },
      	{ 'id': '1', 'descripcion': 'VENCIDO' },
      	{ 'id': '2', 'descripcion': 'MES ACTUAL' },
      	{ 'id': '3', 'descripcion': 'DE 2 A 3 MESES' },
    ];
    $scope.fBusqueda.tipoVence = $scope.listaTipoVence[0];
	$scope.btnToggleFiltering = function(){
	  $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
	  $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
	};
	$scope.navegateToCell = function( rowIndex, colIndex ) {
	  $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
	};
	var paginationOptions = {
	  pageNumber: 1,
	  firstRow: 0,
	  pageSize: 10,
	  sort: uiGridConstants.DESC,
	  sortName: null,
	  search: null
	};
	$scope.gridOptions = {
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
	  multiSelect: true,
	  columnDefs: [
	    { field: 'idmovimiento', name: 'idmovimiento', displayName: 'COD. MOV.', width: '8%', visible: false },
	    { field: 'num_lote', name: 'num_lote', displayName: 'LOTE', maxWidth: 80 },
	    { field: 'medicamento', name: 'medicamento', displayName: 'MEDICAMENTO' },
	    { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '15%' },
	    { field: 'almacen', name: 'almacen', displayName: 'ALMACEN', width: '18%' },
	    { field: 'medida', name: 'descripcion_pres', displayName: 'MEDIDA', width: '8%' },
	    { field: 'cantidad', name: 'cantidad', displayName: 'CANTIDAD', width: '8%' },
	    { field: 'fecha_vencimiento', name: 'fecha_vencimiento', displayName: 'FECH. VENCIMIENTO', width: '10%',
	    	sort: { direction: uiGridConstants.DESC}
	    },
	    { field: 'estadoMed', type: 'object', name: 'estado_med', displayName: 'VENCE', width: '12%', enableFiltering: false,
	      	cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>'
	    }
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
				'idmovimiento' : grid.columns[1].filters[0].term,
				'num_lote' : grid.columns[2].filters[0].term,
				'denominacion' : grid.columns[3].filters[0].term,
				'nombre_lab' : grid.columns[4].filters[0].term,
				'nombre_alm' : grid.columns[5].filters[0].term,
				'descripcion_pres' : grid.columns[6].filters[0].term,
	            
	        }
			$scope.getPaginationServerSide();
	      });
	  }
	};
	paginationOptions.sortName = $scope.gridOptions.columnDefs[7].name;
	$scope.getPaginationServerSide = function() {
	  var arrParams = {
	    paginate : paginationOptions,
	    datos :  $scope.fBusqueda,
	  };
	  medicamentoPorVencerServices.sListarMedicamentoPorVencer(arrParams).then(function (rpta) {
	    $scope.gridOptions.totalItems = rpta.paginate.totalRows;
	    $scope.gridOptions.data = rpta.datos;
	  });
	  $scope.mySelectionGrid = [];
	};
	$scope.getPaginationServerSide();
	$scope.btnQuitarAlerta = function (mensaje) { 
      var pMensaje = mensaje || '¿Realmente desea quitar la(s) alerta(s) seleccionada(s)?';
      $bootbox.confirm(pMensaje, function(result) {
        if(result){
          medicamentoPorVencerServices.sQuitarAlerta($scope.mySelectionGrid).then(function (rpta) {
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
              pinesNotifications.notify({ title: pTitle, text: rpta.message, type: pType, delay: 3000 });
          });
        }
      });
    }
    /* ============= */
    /* EXPORTACIONES */
    /* ============= */
    $scope.btnExportarListaPdf = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'MEDICAMENTOS VENCIDOS Y POR VENCER',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'pdf',
          tituloAbv: 'FAR-MVV',
          titulo: 'MEDICAMENTOS VENCIDOS Y POR VENCER',
        },
        metodo: 'php'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_medicamentos_vencidos_farmacia',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnExportarListaExcel = function(){
      console.log('fBusqueda: ', $scope.fBusqueda);
      console.log('paginate: ', paginationOptions);
      var arrParams = {
        titulo: 'MEDICAMENTOS VENCIDOS Y POR VENCER',
        datos:{
          resultado: $scope.fBusqueda,
          paginate: paginationOptions,
          salida: 'excel',
          tituloAbv: 'FAR-MVV',
          titulo: 'MEDICAMENTOS VENCIDOS Y POR VENCER',
        },
        metodo: 'js'
      }
      console.log('arrParams: ', arrParams);
      arrParams.url = angular.patchURLCI+'CentralReportesFarmacia/report_medicamentos_vencidos_farmacia_excel',
      ModalReporteFactory.getPopupReporte(arrParams);
    }
}])
.service("medicamentoPorVencerServices",function($http, $q) {
    return({
        sListarMedicamentoPorVencer:sListarMedicamentoPorVencer,
        sQuitarAlerta: sQuitarAlerta,
    });
    function sListarMedicamentoPorVencer (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamentoAlmacen/lista_medicamento_por_vencer",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
    function sQuitarAlerta (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamentoAlmacen/quitarAlerta",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
});