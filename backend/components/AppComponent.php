<?php

namespace backend\components;

use Yii;
use yii\base\Component;
use yii\web\UnauthorizedHttpException;
use yii\base\Exception;

class AppComponent extends Component {
    
    public function init() {
        parent::init(); 
        $params = \backend\modules\core\classes\CoreQuery::getOptionsParams();
        \Yii::$app->params = \yii\helpers\ArrayHelper::merge(\Yii::$app->params, $params);
        Yii::setAlias('@storageUrl',  \Yii::$app->params['storageUrl']);
        //\appxq\sdii\utils\VarDumper::dump($params);
        
       if(isset(Yii::$app->params['commandUpdate']) && Yii::$app->params['commandUpdate'] == 1){
          self::commandUpdate();
       }//command update
      
    }
    //command update
    public static function commandUpdate(){
        try{
            $model = \backend\models\Dbupdate::find()
                    ->where('rstat not in(0,3) AND status = 0')->orderBy(['id'=>SORT_ASC])->all();
            if($model){
                foreach($model as $k=>$v){ 
                    $sql= $v['sql'];
                    try{
                        \Yii::$app->db->createCommand($sql)->execute();
                        $v->status = 1;
                        $v->update();
                    } catch (Exception $ex) {
                        \appxq\sdii\utils\VarDumper::dump($ex);
                        //Log
                    }
                }
            }
        } catch (Exception $ex) {
            //Log
        }
        
    }

    public static function navbarLeft() {
        $moduleId = (isset(Yii::$app->controller->module->id) && Yii::$app->controller->module->id != 'app-backend') ? Yii::$app->controller->module->id : '';
        $controllerId = isset(Yii::$app->controller->id) ? Yii::$app->controller->id : '';
        $actionId = isset(Yii::$app->controller->action->id) ? Yii::$app->controller->action->id : '';
        $viewId = \Yii::$app->request->get('id', '');

        $navbar = [

            'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
            'items' => [
                ['label' => \Yii::t('app','Dashboard'), 'icon' => 'tachometer', 'url' => ['/site/index'] ],
                [
                    'label' => Yii::t('app', 'จัดการบทเรียน'),
                    'icon' => 'book',
                    'url' => '#',
                    'items' => [
                        ['label' => 'บทเรียน','icon' => ' ', 'url' => ['/lessons?status=1'],'visible' => 1],
                        ['label' => 'บทเรียนที่ไม่แสดง','icon' => ' ', 'url' => ['/lessons?status=2'],'visible' => 1],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'จัดการผู้ใช้'),
                    'icon' => 'user',
                    'url' => '#',
                    'items' => [
                        ['label' => 'นักเรียน','icon' => ' ', 'url' => ['/student'],'visible' => 1],
                        ['label' => 'คุณครู/เจ้าหน้าที่','icon' => ' ', 'url' => ['/user/admin/index'],'visible' => 1],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'จัดการแบบทดสอบ'),
                    'icon' => 'files-o',
                    'url' => '#',
                    'items' => [
                        ['label' => 'แบบทดสอบก่อนเรียน','icon' => ' ', 'url' => ['/test?type=1'],'visible' => 1],
                        ['label' => 'แบบทดสอบหลังเรียน','icon' => ' ', 'url' => ['/test?type=2'],'visible' => 1],
                    ],
                ],


                ['label' => \Yii::t('appmenu','About'), 'icon' => 'info-circle', 'url' => ['/site/about']],
                ['label' => \Yii::t('appmenu','Contact'), 'icon' => 'phone-square', 'url' => ['/site/contact']],
                ['label' => \Yii::t('bill','จัดการพนักงานขับรถ'), 'icon' => 'user', 'url' => ['/customer'],'visible' => \Yii::$app->user->can('sell_shipping')],//sell-shipping

                [
                    'label' => Yii::t('appmenu', 'System Config'),
                    'visible' => \Yii::$app->user->can('admin'),
                    'icon' => 'cog',
                    'url' => '#',
                    'items' => [

                        [
                            'label' => Yii::t('appmenu', 'Authentication'),
                            'active'=>($moduleId == 'admin'),
                            'icon' => ' ',
                            'url' => '#',
                            'items' => [
                                //Assignments
                                ['label' => Yii::t('appmenu', 'Assignments'), 'icon' => ' ', 'url' => ['/admin'],'active'=>($moduleId == 'admin' && $controllerId == 'assignment'),],
                                ['label' => Yii::t('appmenu', 'Role'), 'icon' => ' ', 'url' => ['/admin/role'],'active'=>($moduleId == 'admin' && $controllerId == 'role')],
                                ['label' => Yii::t('appmenu', 'Route'), 'icon' => ' ', 'url' => ['/admin/route'],'active'=>($moduleId == 'admin' && $controllerId == 'route')],
                                ['label' => Yii::t('appmenu', 'Permission'), 'icon' => ' ', 'url' => ['/admin/permission'],'active'=>($moduleId == 'admin' && $controllerId == 'permission')],
                            ],
                        ],
                        [
                            'label' => Yii::t('appmenu', 'Tools'),
                            'icon' => ' ',
                            'url' => '#',
                            'items' => [
                                //options
                                ['label' => Yii::t('appmenu','Setting Config'),        'icon' => ' ', 'url' => ['/options'],],
                                ['label' => Yii::t('appmenu','System Log'),     'icon' => ' ', 'url' => ['/systemlog'],],
                                ['label' => Yii::t('appmenu','Skin'),           'icon' => ' ', 'url' => ['/skin'],],
                                ['label' => Yii::t('appmenu','Sql Update'),     'icon' => ' ', 'url' => ['/dbupdate'],],
                                ['label' => Yii::t('appmenu','Gii'),            'icon' => ' ', 'url' => ['/gii'],],
                                ['label' => Yii::t('appmenu','Debug'),          'icon' => ' ', 'url' => ['/debug'],]
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return $navbar;
    }
    public static function menuRight(){
        $fullName = \common\modules\user\classes\CNUserFunc::getFullName();
        $img = \common\modules\user\classes\CNUserFunc::getImagePath();
        $items = [            
            [
                'label' =>"<img src='{$img}' class='user-image'> ".$fullName,
                'visible' => !Yii::$app->user->isGuest,
                'items' => [
                     ['label' => '<i class="fa fa-user"></i> '.Yii::t('appmenu','User Profile'), 'url' => ['/user/settings/profile']],
                     '<li class="divider"></li>', 
                     ['label' => '<i class="fa fa-sign-out"></i> '.Yii::t('appmenu','Logout'), 'url' => ['/user/security/logout'], 'linkOptions' => ['data-method' => 'post']],
                ],
            ],
            //['label' => "<i class='fa fa-sign-in'></i> ".Yii::t('appmenu','Sign Up'), 'url' => ['/user/register'], 'visible' => Yii::$app->user->isGuest],
            ['label' => "<i class='fa fa-sign-in'></i> ".Yii::t('appmenu','Login'), 'url' => ['/user/login'], 'visible' => Yii::$app->user->isGuest],
        ];
        return $items;
    }
    public static function slideToggleLeft(){              
        return \yii\helpers\Html::a("<span class='sr-only'></span>", '#', [
            'class'=>'sidebar-toggle',
            'data-toggle'=>'push-menu',
            'role'=>'button',
            'id'=>'iconslideToggle'
        ]);
    }
    public static function slideToggleRight(){  
        return 
        
        \yii\helpers\Html::button("<i class='fa fa-bars'></i>", [
            'class'=>'navbar-toggle',
            'data-toggle'=>'collapse',
            'data-target'=>'#cnNavbar',
            
        ]);
         
    }
}
