<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'BaseController' => $baseDir . '/app/controllers/BaseController.php',
    'Bento\\Admin\\Ctrl\\AdminBaseController' => $baseDir . '/app/controllers/admin/AdminBaseController.php',
    'Bento\\Admin\\Ctrl\\AdminUserCtrl' => $baseDir . '/app/controllers/admin/AdminUserCtrl.php',
    'Bento\\Admin\\Ctrl\\ApiTestCtrl' => $baseDir . '/app/controllers/admin/ApiTestCtrl.php',
    'Bento\\Admin\\Ctrl\\MiscCtrl' => $baseDir . '/app/controllers/admin/MiscCtrl.php',
    'Bento\\Admin\\Ctrl\\PendingOrderCtrl' => $baseDir . '/app/controllers/admin/PendingOrderCtrl.php',
    'Bento\\Admin\\Ctrl\\UserCtrl' => $baseDir . '/app/controllers/admin/UserCtrl.php',
    'Bento\\Admin\\Model\\AdminUser' => $baseDir . '/app/models/admin/AdminUser.php',
    'Bento\\Admin\\Model\\Misc' => $baseDir . '/app/models/admin/Misc.php',
    'Bento\\Ctrl\\BootstrapCtrl' => $baseDir . '/app/controllers/bootstrap/Bootstrap.php',
    'Bento\\Ctrl\\InitCtrl' => $baseDir . '/app/controllers/InitCtrl.php',
    'Bento\\Ctrl\\MenuCtrl' => $baseDir . '/app/controllers/MenuCtrl.php',
    'Bento\\Ctrl\\MiscCtrl' => $baseDir . '/app/controllers/MiscCtrl.php',
    'Bento\\Ctrl\\OrderCtrl' => $baseDir . '/app/controllers/OrderCtrl.php',
    'Bento\\Ctrl\\StatusCtrl' => $baseDir . '/app/controllers/StatusCtrl.php',
    'Bento\\Ctrl\\UserCtrl' => $baseDir . '/app/controllers/UserCtrl.php',
    'Bento\\Filter\\AdminFilter' => $baseDir . '/app/filters/AdminFilter.php',
    'Bento\\Filter\\ApiAuthFilter' => $baseDir . '/app/filters/ApiAuthFilter.php',
    'Bento\\Model\\LiveInventory' => $baseDir . '/app/models/LiveInventory.php',
    'Bento\\Model\\Menu' => $baseDir . '/app/models/Menu.php',
    'Bento\\Model\\PendingOrder' => $baseDir . '/app/models/PendingOrder.php',
    'Bento\\Model\\Status' => $baseDir . '/app/models/Status.php',
    'CreateSessionTable' => $baseDir . '/app/database/migrations/2015_01_13_014144_create_session_table.php',
    'DatabaseSeeder' => $baseDir . '/app/database/seeds/DatabaseSeeder.php',
    'HomeController' => $baseDir . '/app/controllers/examples/HomeController.php',
    'IlluminateQueueClosure' => $vendorDir . '/laravel/framework/src/Illuminate/Queue/IlluminateQueueClosure.php',
    'PhotoController' => $baseDir . '/app/controllers/examples/PhotoController.php',
    'SessionHandlerInterface' => $vendorDir . '/symfony/http-foundation/Symfony/Component/HttpFoundation/Resources/stubs/SessionHandlerInterface.php',
    'TestCase' => $baseDir . '/app/tests/TestCase.php',
    'User' => $baseDir . '/app/models/User.php',
    'Whoops\\Module' => $vendorDir . '/filp/whoops/src/deprecated/Zend/Module.php',
    'Whoops\\Provider\\Zend\\ExceptionStrategy' => $vendorDir . '/filp/whoops/src/deprecated/Zend/ExceptionStrategy.php',
    'Whoops\\Provider\\Zend\\RouteNotFoundStrategy' => $vendorDir . '/filp/whoops/src/deprecated/Zend/RouteNotFoundStrategy.php',
);
