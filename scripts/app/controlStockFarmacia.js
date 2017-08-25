angular.module('theme.controlStockFarmacia', ['theme.core.services'])
.controller('controlStockFarmaciaController', ['$scope', '$sce', '$modal', '$bootbox', '$window', '$http', '$theme', '$log', '$timeout', 'uiGridConstants', 'pinesNotifications',
	'ModalReporteFactory',
  	'controlStockFarmaciaServices',
  	'almacenFarmServices',
	function($scope, $sce, $modal, $bootbox, $window, $http, $theme, $log, $timeout, uiGridConstants, pinesNotifications,
		ModalReporteFactory,
	 	controlStockFarmaciaServices,
		almacenFarmServices){
	'use strict';
	shortcut.remove("F2");
	$scope.modulo = 'controlStockFarmacia';
	$scope.fBusqueda = {}
	$scope.mySelectionGrid = [];
	$scope.btnToggleFiltering = function(){
	  $scope.gridOptions.enableFiltering = !$scope.gridOptions.enableFiltering;
	  $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
	};
	$scope.navegateToCell = function( rowIndex, colIndex ) {
	  $scope.gridApi.cellNav.scrollToFocus( $scope.gridOptions.data[rowIndex], $scope.gridOptions.columnDefs[colIndex]);
	};
	// ALMACEN 
    almacenFarmServices.sListarAlmacenesCboSession().then(function (rpta) { 
      $scope.listaAlmacen = rpta.datos; 
      // $scope.listaAlmacen.splice(0,0,{ id : '', descripcion:'-- Seleccione Presentacion --'}); 
      $scope.fBusqueda.almacen = $scope.listaAlmacen[0];
      $scope.getPaginationServerSide();
    });
	var paginationOptions = {
	  pageNumber: 1,
	  firstRow: 0,
	  pageSize: 10,
	  sort: uiGridConstants.ASC,
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
	    { field: 'idmedicamento', name: 'idmedicamento', displayName: 'COD.MED.', width: '8%', visible: false },
	    { field: 'medicamento', name: 'denominacion', displayName: 'MEDICAMENTO' },
	    { field: 'laboratorio', name: 'nombre_lab', displayName: 'LABORATORIO', width: '15%' },
	    { field: 'almacen', name: 'almacen', displayName: 'ALMACEN', width: '18%' },
	    { field: 'stock_actual_malm', name: 'stock_actual_malm', displayName: 'STOCK ACTUAL', width: '8%', enableFiltering: false, cellClass: "text-right" },
	    { field: 'stock_minimo', name: 'stock_minimo', displayName: 'STOCK MINIMO', width: '8%', enableFiltering: false, cellClass: "text-right" },
	    { field: 'stock_critico', name: 'stock_critico', displayName: 'STOCK CRITICO', width: '8%', enableFiltering: false, cellClass: "text-right" },
	    { field: 'stock_maximo', name: 'stock_maximo', displayName: 'STOCK MAXIMO', width: '8%', enableFiltering: false, cellClass: "text-right" },
	    { field: 'estadoStock', type: 'object', name: 'estado', displayName: 'ESTADO', width: '12%', enableFiltering: false,
	      	cellTemplate:'<label style="box-shadow: 1px 1px 0 black; margin: 6px auto; display: block; width: 120px;" class="label {{ COL_FIELD.clase }} ">{{ COL_FIELD.string }}</label>', sort: { direction: uiGridConstants.ASC}
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
				'me.idmedicamento' : grid.columns[1].filters[0].term,
				'denominacion' : grid.columns[2].filters[0].term,
				'nombre_lab' : grid.columns[3].filters[0].term,
				'nombre_alm' : grid.columns[4].filters[0].term,
				
	            
	        }
			$scope.getPaginationServerSide();
	      });
	  }
	};
	paginationOptions.sortName = $scope.gridOptions.columnDefs[8].name; // estado
	$scope.getPaginationServerSide = function() {
	  var arrParams = {
	    paginate : paginationOptions,
	    datos: $scope.fBusqueda
	  };
	  controlStockFarmaciaServices.sListarMedicamentoPorAgotarse(arrParams).then(function (rpta) {
	    $scope.gridOptions.totalItems = rpta.paginate.totalRows;
	    $scope.gridOptions.data = rpta.datos;
	  });
	  $scope.mySelectionGrid = [];
	};
	// $scope.getPaginationServerSide();
	$scope.btnExportarListaPdf = function (){
    	$scope.fBusqueda.titulo = 'CONTROL STOCK FARMACIA';
    	$scope.fBusqueda.tituloAbv = 'CSF';
    	$scope.fBusqueda.salida = 'pdf';
    	var arrParams = {
	        titulo: $scope.fBusqueda.titulo,
	        datos: $scope.fBusqueda,
	        url: angular.patchURLCI+'CentralReportesFarmaciaMPDF/report_control_stock_farmacia',
	        metodo: 'php'
	    }
      	ModalReporteFactory.getPopupReporte(arrParams);
    }
    $scope.btnExportarListaExcel = function (){
    	$scope.fBusqueda.titulo = 'CONTROL STOCK FARMACIA';
    	$scope.fBusqueda.tituloAbv = 'CSF';
    	$scope.fBusqueda.salida = 'excel';
    	var arrParams = {
	        titulo: $scope.fBusqueda.titulo,
	        datos: $scope.fBusqueda,
	        url: angular.patchURLCI+'CentralReportesFarmacia/report_control_stock_farmacia_excel',
	        metodo: 'js'
	    }
      	ModalReporteFactory.getPopupReporte(arrParams);
    }
}])
.service("controlStockFarmaciaServices",function($http, $q) {
    return({
        sListarMedicamentoPorAgotarse:sListarMedicamentoPorAgotarse
    });
    function sListarMedicamentoPorAgotarse (datos) {
      var request = $http({
            method : "post",
            url : angular.patchURLCI+"medicamentoAlmacen/lista_medicamento_por_agotarse",
            data : datos
      });
      return (request.then( handleSuccess,handleError ));
    }
});