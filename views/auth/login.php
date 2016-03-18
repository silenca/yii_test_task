<?php
$this->title = 'Login';
?>
<div class="login-wrapper ">
    <!-- START Login Background Pic Wrapper-->
    <div class="bg-pic">
        <!-- START Background Pic-->
        <img src="/media/img/bg-login.jpg" data-src="/media/img/bg-login.jpg" alt="" class="lazy">
        <!-- END Background Pic-->
        <!-- START Background Caption-->
        <!-- END Background Caption-->
    </div>
    <!-- END Login Background Pic Wrapper-->
    <!-- START Login Right Container-->
    <div class="login-container bg-white">
        <div class="p-l-50 m-l-20 p-r-50 m-r-20 p-t-50 m-t-30 sm-p-l-15 sm-p-r-15 sm-p-t-40">
            <h4>Silent CRM</h4>
            <!-- START Login Form -->
            <form id="form-login" class="p-t-15" role="form" method="POST">
                <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>" />
                <!-- START Form Control-->
                <div class="form-group form-group-default">
                    <label>Login</label>
                    <div class="controls">
                        <input type="text" name="LoginForm[email]" placeholder="User Name" class="form-control" required value="<?= $model->email ? $model->email : null ?>">
                    </div>
                </div>
                <!-- END Form Control-->
                <!-- START Form Control-->
                <div class="form-group form-group-default">
                    <label>Password</label>
                    <div class="controls">
                        <input type="password" class="form-control" name="LoginForm[password]" placeholder="Credentials" required>
                    </div>
                </div>
                <div class="form-group">
                    <?php foreach($errors as $error): ?>
                        <label class="error"><?= $error ?></label>
                    <?php endforeach; ?>
                    
                </div>
                <!-- START Form Control-->
                <div class="row">
                    <div class="col-md-6 no-padding">
                        <div class="checkbox ">
                            <input type="checkbox" value="1" id="checkbox1" name="LoginForm[rememberMe]">
                            <label for="checkbox1">Keep Me Signed in</label>
                        </div>
                    </div>
                </div>
                <!-- END Form Control-->
                <button class="btn btn-primary btn-cons m-t-10" type="submit">Sign in</button>
            </form>
            <!--END Login Form-->

        </div>
    </div>
    <!-- END Login Right Container-->
</div>
