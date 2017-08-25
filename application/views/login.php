<div class="container" id="login-form" ng-controller="loginController">
    <a href="" class="login-logo"><img style="max-width: 365px; width: 100%;" class="img-responsive center-block" masked-image ng-src="{{ dirImages + 'dinamic/empresa/' + $parent.fEmpresa.nombre_logo }} "></a>
        <div class="row">
            <div class="col-md-4 col-md-offset-4"> 
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Iniciar sesión </h2>
                    </div>
                    <div class="panel-body">
                        
                        <form action="" class="form-horizontal" id="validate-form">
                            <div class="form-group mb-md">
                                <div class="col-xs-12">
                                    <div class="input-group">                           
                                        <span class="input-group-addon">
                                            <i class="fa fa-user"></i>
                                        </span>
                                        <input ng-model="fLogin.usuario" type="text" class="form-control" placeholder="Usuario" data-parsley-minlength="6" required focus-me enter-as-tab/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-md">
                                <div class="col-xs-12">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-key"></i>
                                        </span>
                                        <input ng-model="fLogin.clave" type="password" class="form-control" id="exampleInputPassword1" placeholder="Clave" required ng-enter="btnLoginToSystem()"/>
                                    </div>
                                </div>
                            </div>
                            <alert type="{{fAlert.type}}" close="closeAlert()" ng-show='fAlert.type' class="p-sm">
                                <strong> {{ fAlert.strStrong }} </strong> <span ng-bind-html="fAlert.msg"></span>
                            </alert>
                            <!-- <div class="block" ng-if> -->
                                <div class="form-group mb-md" ng-show="fAlert.flag == 3 || fAlert.flag == 2 || fAlert.flag === 0">
                                    <div class="col-xs-12">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="ti ti-world"></i>
                                            </span>
                                            <select class="form-control" ng-model="fLogin.sede" ng-options="item.id as item.descripcion for item in listaSedes" > </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="form-group mb-md" ng-show="fAlert.flag == 3 || fAlert.flag === 0">
                                    <div class="col-xs-12">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="ti ti-bag"></i>
                                            </span>
                                            <select class="form-control" ng-model="fLogin.empresa" ng-options="item.id as item.descripcion for item in listaEmpresas" > </select>
                                        </div>
                                    </div>
                                </div> -->
                            <!-- </div> -->
                            <div class="form-group mb-n">
                                <div class="col-xs-12">
                                    <a href="extras-forgotpassword.html" class="pull-left">¿Olvidó su contraseña?</a>
                                    <!-- <div class="checkbox-inline icheck pull-right p-n">
                                        <label for="">
                                            <input icheck type="checkbox"></input>
                                            Remember me
                                        </label>
                                    </div> -->
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="panel-footer">
                        <div class="clearfix">
                            <!-- <a href="#/extras-registration" class="btn btn-default pull-left">Register</a> -->
                            <a href="" class="btn btn-primary pull-right" ng-click="btnLoginToSystem()">Iniciar sesión</a>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <ul class="demo-btns">
                        <li><a target="_blank" href=" {{ fEmpresa.rs_facebook }} " class="btn btn-social btn-facebook-alt"><i class="ti ti-facebook"></i></a></li>
                        <li><a target="_blank" href=" {{ fEmpresa.rs_twitter }} " class="btn btn-social btn-twitter-alt"><i class="ti ti-twitter"></i></a></li>
                        <li><a target="_blank" href="{{ fEmpresa.rs_youtube }}" class="btn btn-social btn-youtube-alt"><i class="ti ti-youtube"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
</div>