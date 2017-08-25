<ol class="breadcrumb">
  <li><a href="#/">Inicio</a></li>
  <li ng-if="!boolModulo">{{unidadNegocio}}</li>
  <li ng-if="boolModulo">{{modulo}}</li>
</ol>
<div class="container-fluid" ng-controller="moduloBienvenidaController">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-danger" data-widget='{"id" : "wiget10000"}'>
              	<div class="panel-heading">
                	<div class="panel-ctrls button-icon" data-actions-container="" data-action-collapse='{"target": ".panel-body"}' data-action-colorpicker=''> </div>
                	<h2  ng-if="!boolModulo">Módulo {{unidadNegocio}}</h2>
                  <h2  ng-if="boolModulo">Módulo {{modulo}}</h2>
            	</div>
              	
              	<div class="panel-body text-center" style="min-height:400px;" ng-if="!boolModulo">
              	  <h1 style="font-size:50px; display:block;opacity:0.5"> MÓDULO: <b>{{unidadNegocio}}</b> </h1>
	                <div class="btn-group" ng-repeat="item in menuUnidadNegocio">
	                	<a ng-href="{{item.url}}" ng-if="!item.children.length">
	                    <div type="button" class="btn btn-info-alt dropdown-toggle mr-md mb-md" data-toggle="dropdown" style="width: 120px; height:120px" ng-model="item">
	                      <i class="{{item.iconClasses}} mt-md mb-sm" style="font-size:50px;display:block;"></i>
	                      <div style="font-size:11px;word-wrap:break-word; width:100px;white-space: normal;">{{item.label}}</div>
	                    </div>
	                  </a>
                    <div type="button" class="btn btn-info-alt dropdown-toggle mr-md mb-md" data-toggle="dropdown" style="width: 120px; height:120px" ng-model="item" ng-if="item.children.length">
                      <i class="{{item.iconClasses}} mt-md mb-sm" style="font-size:50px;display:block;"></i>
                      <div style="font-size:11px;word-wrap:break-word; width:100px;white-space: normal;">{{item.label}} <span ng-if="item.children.length" class="caret"></span></div>
                    </div>
                    <ul class="dropdown-menu" role="menu" ng-if="item.children.length" >
          					  <li ng-repeat="item in item.children"
          						   	ng-class="{ hasChild: (item.children!==undefined),
          						                active: item.selected,
          						                open: (item.children!==undefined) && item.open,
          						                'search-focus': (searchQuery.length>0 && item.selected) }"
          						    ng-show="!(searchQuery.length>0 && !item.selected)"
          						    ng-include="'templates/nav_renderer.html'"
          					  ></li>
						        </ul>
                  </div>
              	</div>

                <div class="panel-body text-center" style="min-height:400px;" ng-if="boolModulo">
                  <h1 style="font-size:50px; display:block;opacity:0.5"> MÓDULO: <b>{{modulo}}</b> </h1>
                  <div class="btn-group" ng-repeat="item in menu">
                    <a ng-href="{{item.url}}" ng-if="!item.children.length">
                      <div type="button" class="btn btn-info-alt dropdown-toggle mr-md mb-md" data-toggle="dropdown" style="width: 120px; height:120px" ng-model="item">
                        <i class="{{item.iconClasses}} mt-md mb-sm" style="font-size:50px;display:block;"></i>
                        <div style="font-size:11px;word-wrap:break-word; width:100px;white-space: normal;">{{item.label}}</div>
                      </div>
                    </a>
                    <div type="button" class="btn btn-info-alt dropdown-toggle mr-md mb-md" data-toggle="dropdown" style="width: 120px; height:120px" ng-model="item" ng-if="item.children.length">
                      <i class="{{item.iconClasses}} mt-md mb-sm" style="font-size:50px;display:block;"></i>
                      <div style="font-size:11px;word-wrap:break-word; width:100px;white-space: normal;">{{item.label}} <span ng-if="item.children.length" class="caret"></span></div>
                    </div>
                    <ul class="dropdown-menu" role="menu" ng-if="item.children.length" >
                      <li ng-repeat="item in item.children"
                          ng-class="{ hasChild: (item.children!==undefined),
                                      active: item.selected,
                                      open: (item.children!==undefined) && item.open,
                                      'search-focus': (searchQuery.length>0 && item.selected) }"
                          ng-show="!(searchQuery.length>0 && !item.selected)"
                          ng-include="'templates/nav_renderer.html'"
                      ></li>
                    </ul>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>  