<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
//use yii\bootstrap\Nav;
//use yii\bootstrap\NavBar;
//use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\NotificationAsset;
use app\assets\GoogleApiAsset;
use app\components\UtilHelper;
use app\models\User;

AppAsset::register($this);
if (!Yii::$app->user->isGuest) {
    NotificationAsset::register($this);
    GoogleApiAsset::register($this);
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <link rel="apple-touch-icon" href="pages/ico/60.png">
        <link rel="apple-touch-icon" sizes="76x76" href="pages/ico/76.png">
        <link rel="apple-touch-icon" sizes="120x120" href="pages/ico/120.png">
        <link rel="apple-touch-icon" sizes="152x152" href="pages/ico/152.png">
        <link rel="icon" type="image/x-icon" href="favicon.ico" />
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-touch-fullscreen" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta content="" name="description" />
        <meta content="" name="author" />
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <? if(!Yii::$app->user->isGuest): ?>
        <script type="text/javascript">
            var notify_id = "<?= Yii::$app->user->identity->notification_key; ?>";
            var notify_host = "<?= Yii::$app->params['host_notify'] ?>";
            var _csrf = "<?= Yii::$app->request->getCsrfToken() ?>";
        </script>
        <? endif; ?>
        <?php $this->head() ?>
        <script type="text/javascript">
            window.onload = function ()
            {
                // fix for windows 8
                if (navigator.appVersion.indexOf("Windows NT 6.2") != -1)
                    document.head.innerHTML += '<link rel="stylesheet" type="text/css" href="pages/css/windows.chrome.fix.css" />'
            }
        </script>
    </head>
    <body class="fixed-header">
        <?php $this->beginBody() ?>
        <?php if (!Yii::$app->user->isGuest): ?>
            <nav class="page-sidebar" data-pages="sidebar-custom">
                <!-- BEGIN SIDEBAR MENU HEADER-->
                <div class="sidebar-header m-b-20">
                    <div class="brand">Silent CRM</div>
                    <div class="sidebar-header-controls">
                        <button type="button" class="btn btn-link visible-lg-inline" data-toggle-pin="sidebar-custom"><i class="fa fs-12"></i>
                        </button>
                    </div>
                </div>
                <!-- END SIDEBAR MENU HEADER-->
                <!-- START SIDEBAR MENU -->
                <div class="sidebar-menu">
                    <div class="cstm-sidebar-switch js-switch-sidebar">
                        <i class="fa fa-lg fa-fw fa-bars"></i>
                    </div>
                    <!-- BEGIN SIDEBAR MENU ITEMS-->
                    <ul class="menu-items">
                        <?php if (Yii::$app->user->can('contacts')): ?>
                            <li>
                                <a href="/contacts" class="detailed">
                                    <span class="title">Контакты</span>
                                    <span class="details"></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'contact' ? 'bg-success' : null ?>" title="Контакты"><i class="pg-contact_book"></i></span>
                                </a>
                            </li>
                        <?php endif; ?>
<!--                        --><?php //if (Yii::$app->user->can('objects')): ?>
<!--                            <li>-->
<!--                                <a href="/object" class="detailed">-->
<!--                                    <span class="title">Объекты</span>-->
<!--                                    <span class="details"></span>-->
<!--                                    <span class="icon-thumbnail --><?//= $this->params['active'] == 'object' ? 'bg-success' : null ?><!--" title="Объекты"><i class="pg-home"></i></span>-->
<!--                                </a>-->
<!--                            </li>-->
<!--                        --><?php //endif; ?>
                        <?php if (Yii::$app->user->can('action')): ?>
                            <li>
                                <a href="/action" class="detailed">
                                    <span class="title">Действия</span>
                                    <span class="details"></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'action' ? 'bg-success' : null ?>" title="Действия"><i class="pg-centeralign"></i></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('calls')): ?>
                            <li>
                                <a href="/call" class="detailed">
                                    <span class="title">Звонки</span>
                                    <span class="details"><?= $this->params['missed_count']; ?> <?php echo UtilHelper::pluralForm($this->params['missed_count'], 'пропущенный звонок', 'пропущенных звонка', 'пропущенных звонков') ?></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'call' ? 'bg-success' : null ?>" title="Звонки">
                                        <i class="pg-telephone"></i>
                                        <?php if (Yii::$app->user->identity->role == User::ROLE_MANAGER && $this->params['missed_count'] > 0): ?>
                                            <span class="badge badge-danger"><?php echo $this->params['missed_count'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('reports')): ?>
                            <li>
                                <a href="/reports/index" class="detailed">
                                    <span class="title">Отчеты</span>
                                    <span class="details"></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'reports' ? 'bg-success' : null ?>" title="Отчеты">
                                        <i class="pg-tables"></i>
                                    </span>
                                </a>
                            </li>
                        <?php endif; ?>
<!--                        --><?php //if (Yii::$app->user->can('receivables')): ?>
<!--                            <li>-->
<!--                                <a href="/receivable" class="detailed">-->
<!--                                    <span class="title">Дебиторские задолженности</span>-->
<!--                                    <span class="details"></span>-->
<!--                                    <span class="icon-thumbnail --><?//= $this->params['active'] == 'receivable' ? 'bg-success' : null ?><!--" title="Дебиторские задолженности"><i class="pg-calender"></i></span>-->
<!--                                </a>-->
<!--                            </li>-->
<!--                        --><?php //endif; ?>
<!--                        --><?php //if (Yii::$app->user->can('notifications')): ?>
<!--                            <li>-->
<!--                                <a href="/managernotify" class="detailed">-->
<!--                                    <span class="title">Уведомления</span>-->
<!--                                    <span class="details">--><?//= $this->params['notify_count']; ?><!-- --><?php //echo UtilHelper::pluralForm($this->params['notify_count'], 'новый уведомлений', 'новых уведомления', 'новых уведомлений') ?><!--</span>-->
<!--                                    <span class="icon-thumbnail --><?//= $this->params['active'] == 'notification' ? 'bg-success' : null ?><!--"  title="Уведомления">-->
<!--                                        <i class="pg-comment"></i>-->
<!--                                        --><?php //if ($this->params['notify_count'] > 0): ?>
<!--                                            <span class="badge badge-danger">--><?php //echo $this->params['notify_count'] ?><!--</span>-->
<!--                                        --><?php //endif; ?><!--                                        -->
<!--                                    </span>-->
<!--                                </a>-->
<!--                            </li>-->
<!--                        --><?php //endif; ?>
<!--                        --><?php //if (Yii::$app->user->can('contracts')): ?>
<!--                            <li>-->
<!--                                <a href="/contracts" class="detailed">-->
<!--                                    <span class="title">Договоры</span>-->
<!--                                    <span class="details">0 новых уведомлений</span>-->
<!--                                    <span class="icon-thumbnail --><?//= $this->params['active'] == 'contracts' ? 'bg-success' : null ?><!--" title="Договоры">-->
<!--                                        <i class="pg-comment"></i>-->
<!--                                        --><?php //if ($this->params['new_contract_count'] > 0): ?>
<!--                                            <span class="js-contract_count badge badge-danger">--><?php //echo $this->params['new_contract_count'] ?><!--</span>-->
<!--                                        --><?php //endif; ?>
<!--                                    </span>-->
<!--                                </a>-->
<!--                            </li>-->
<!--                        --><?php //endif; ?>
                        <?php if (Yii::$app->user->can('tags')): ?>
                            <li>
                                <a href="/tags" class="detailed">
                                    <span class="title">Теги</span>
                                    <span class="details"></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'tags' ? 'bg-success' : null ?>" title="Теги"><i class="fa fa-tags"></i></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('import')): ?>
                            <li>
                                <a href="/import" class="detailed">
                                    <span class="title">Импорт</span>
                                    <span class="details"></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'import' ? 'bg-success' : null ?>" title="Импорт"><i class="fa fa-cloud-upload"></i></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('users')): ?>
                            <li>
                                <a href="/users" class="detailed">
                                    <span class="title">Пользователи</span>
                                    <span class="details"></span>
                                    <span class="icon-thumbnail <?= $this->params['active'] == 'users' ? 'bg-success' : null ?>" title="Пользователи"><i class="fa fa-user"></i></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('use_archived_tags')): ?>
                            <li>
                                <div class="row">
                                    <div class="col-md-12 p-l-30">
                                        <div class="col-md-9 text-white">Использовать удаленные теги</div>
                                        <div class="col-md-3">
                                            <input type="checkbox" class="js-switch" id="use_deleted_tags" <?= $this->params['use_archive_tags'] == 1 ? 'checked' : null ?> />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <div class="clearfix"></div>
                </div>
                <!-- END SIDEBAR MENU -->
            </nav>
            <div class="page-container">
                <div class="header ">
                    <!-- START MOBILE CONTROLS -->
                    <!-- RIGHT SIDE -->
                    <div class="pull-right full-height visible-sm visible-xs">
                        <!-- START ACTION BAR -->
                        <div class="sm-action-bar">
                            <a href="#" class="btn-link" data-toggle="quickview" data-toggle-element="#quickview">
                                <span class="icon-set menu-hambuger-plus"></span>
                            </a>
                        </div>
                        <!-- END ACTION BAR -->
                    </div>
                    <!-- END MOBILE CONTROLS -->
                    <div class=" pull-left sm-table">
                        <div class="header-inner">
                            <div class="brand inline"><h4><?= Html::encode($this->title) ?></h4></div>
                        </div>
                    </div>
                    <div class=" pull-right">
                        <!-- START User Info-->
                        <div class="visible-lg visible-md m-t-10">
                            <div class="pull-left p-r-10 p-t-10 fs-16 font-heading">
                                <span class="semi-bold"><?= Yii::$app->user->identity->firstname ?></span> <span class="text-master"><?= Yii::$app->user->identity->lastname ?></span>
                            </div>
                            <div class="dropdown pull-right">
                                <button class="profile-dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="thumbnail-wrapper d32 circular inline m-t-5">
                                        <img src="<?= Yii::getAlias('@web') ?>/media/img/avatar.jpg" alt="" data-src="<?= Yii::getAlias('@web') ?>/media/img/avatar.jpg" data-src-retina="<?= Yii::getAlias('@web') ?>/media/img/avatar_small2x.jpg" width="32" height="32">
                                    </span>
                                </button>
                                <ul class="dropdown-menu profile-dropdown" role="menu">
                                    <li><a href="#"><i class="pg-settings_small"></i> Settings</a>
                                    </li>
                                    <li><a href="#"><i class="pg-outdent"></i> Feedback</a>
                                    </li>
                                    <li><a href="#"><i class="pg-signals"></i> Help</a>
                                    </li>
                                    <li class="bg-master-lighter">
                                        <a href="/logout" class="clearfix" id="logout">
                                            <span class="pull-left">Logout</span>
                                            <span class="pull-right"><i class="pg-power"></i></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- END User Info-->
                    </div>
                </div>
                <div class="page-content-wrapper">
                    <?= $content ?>
                </div>
            </div>
        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>

        <?php if (!Yii::$app->user->isGuest): ?>
            <script type="text/javascript">
                var userRole = <?= json_encode($this->params['user_role']); ?>;
            </script>
        <?php endif; ?>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
