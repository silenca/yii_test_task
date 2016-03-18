<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TableAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '/media/plugins/jquery-datatable/media/css/jquery.dataTables.css',
        '/media/plugins/jquery-datatable/extensions/FixedColumns/css/dataTables.fixedColumns.min.css',
        '/media/plugins/datatables-responsive/css/datatables.responsive.css',
    ];
    public $js = [
        '/media/plugins/jquery-datatable/media/js/jquery.dataTables.min.js',
        '/media/plugins/jquery-datatable/media/js/jquery.dataTables.columnFilter.js',
        '/media/plugins/jquery-datatable/extensions/TableTools/js/dataTables.tableTools.min.js',
        '/media/plugins/jquery-datatable/extensions/Bootstrap/jquery-datatable-bootstrap.js',
        '/media/plugins/datatables-responsive/js/datatables.responsive.js',
        '/media/plugins/datatables-responsive/js/lodash.min.js',
        '/media/js/datatables.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}
