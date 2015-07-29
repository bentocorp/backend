<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'BaseController' => $baseDir . '/app/controllers/BaseController.php',
    'Bento\\Admin\\Ctrl\\AdminBaseController' => $baseDir . '/app/controllers/admin/AdminBaseController.php',
    'Bento\\Admin\\Ctrl\\AdminUserCtrl' => $baseDir . '/app/controllers/admin/AdminUserCtrl.php',
    'Bento\\Admin\\Ctrl\\ApiTestCtrl' => $baseDir . '/app/controllers/admin/ApiTestCtrl.php',
    'Bento\\Admin\\Ctrl\\DashboardCtrl' => $baseDir . '/app/controllers/admin/DashboardCtrl.php',
    'Bento\\Admin\\Ctrl\\DishCtrl' => $baseDir . '/app/controllers/admin/DishCtrl.php',
    'Bento\\Admin\\Ctrl\\DriverCtrl' => $baseDir . '/app/controllers/admin/DriverCtrl.php',
    'Bento\\Admin\\Ctrl\\InventoryCtrl' => $baseDir . '/app/controllers/admin/InventoryCtrl.php',
    'Bento\\Admin\\Ctrl\\MenuCtrl' => $baseDir . '/app/controllers/admin/MenuCtrl.php',
    'Bento\\Admin\\Ctrl\\MiscCtrl' => $baseDir . '/app/controllers/admin/MiscCtrl.php',
    'Bento\\Admin\\Ctrl\\OrderCtrl' => $baseDir . '/app/controllers/admin/OrderCtrl.php',
    'Bento\\Admin\\Ctrl\\PendingOrderCtrl' => $baseDir . '/app/controllers/admin/PendingOrderCtrl.php',
    'Bento\\Admin\\Ctrl\\ReportsCtrl' => $baseDir . '/app/controllers/admin/ReportsCtrl.php',
    'Bento\\Admin\\Ctrl\\SettingsCtrl' => $baseDir . '/app/controllers/admin/SettingsCtrl.php',
    'Bento\\Admin\\Ctrl\\StatusCtrl' => $baseDir . '/app/controllers/admin/StatusCtrl.php',
    'Bento\\Admin\\Ctrl\\UserCtrl' => $baseDir . '/app/controllers/admin/UserCtrl.php',
    'Bento\\Admin\\Model\\AdminUser' => $baseDir . '/app/models/admin/AdminUser.php',
    'Bento\\Admin\\Model\\Dish' => $baseDir . '/app/models/admin/Dish.php',
    'Bento\\Admin\\Model\\Driver' => $baseDir . '/app/models/admin/Driver.php',
    'Bento\\Admin\\Model\\Menu' => $baseDir . '/app/models/admin/Menu.php',
    'Bento\\Admin\\Model\\Menu_Item' => $baseDir . '/app/models/admin/Menu_Item.php',
    'Bento\\Admin\\Model\\Orders' => $baseDir . '/app/models/admin/Orders.php',
    'Bento\\Admin\\Model\\Settings' => $baseDir . '/app/models/admin/Settings.php',
    'Bento\\Auth\\AuthInterface' => $baseDir . '/app/core/auth/AuthInterface.php',
    'Bento\\Auth\\FacebookAuth' => $baseDir . '/app/core/auth/FacebookAuth.php',
    'Bento\\Auth\\FacebookAuthServiceProvider' => $baseDir . '/app/core/auth/FacebookAuthServiceProvider.php',
    'Bento\\Auth\\FacebookAuthSvc' => $baseDir . '/app/core/auth/FacebookAuthSvc.php',
    'Bento\\Auth\\MainAuth' => $baseDir . '/app/core/auth/MainAuth.php',
    'Bento\\Auth\\MainAuthServiceProvider' => $baseDir . '/app/core/auth/MainAuthServiceProvider.php',
    'Bento\\Auth\\MainAuthSvc' => $baseDir . '/app/core/auth/MainAuthSvc.php',
    'Bento\\Coupon\\AppCoupon' => $baseDir . '/app/core/coupon/AppCoupon.php',
    'Bento\\Coupon\\CouponInterface' => $baseDir . '/app/core/coupon/CouponInterface.php',
    'Bento\\Coupon\\CouponTrait' => $baseDir . '/app/core/coupon/CouponTrait.php',
    'Bento\\Coupon\\UserCoupon' => $baseDir . '/app/core/coupon/UserCoupon.php',
    'Bento\\Ctrl\\BootstrapCtrl' => $baseDir . '/app/controllers/bootstrap/BootstrapCtrl.php',
    'Bento\\Ctrl\\CouponCtrl' => $baseDir . '/app/controllers/CouponCtrl.php',
    'Bento\\Ctrl\\InitCtrl' => $baseDir . '/app/controllers/InitCtrl.php',
    'Bento\\Ctrl\\MenuCtrl' => $baseDir . '/app/controllers/MenuCtrl.php',
    'Bento\\Ctrl\\MiscCtrl' => $baseDir . '/app/controllers/MiscCtrl.php',
    'Bento\\Ctrl\\OrderCtrl' => $baseDir . '/app/controllers/OrderCtrl.php',
    'Bento\\Ctrl\\RemindersController' => $baseDir . '/app/controllers/RemindersController.php',
    'Bento\\Ctrl\\StatusCtrl' => $baseDir . '/app/controllers/StatusCtrl.php',
    'Bento\\Ctrl\\UserCtrl' => $baseDir . '/app/controllers/UserCtrl.php',
    'Bento\\Drivers\\DriverMgr' => $baseDir . '/app/core/drivers/DriverMgr.php',
    'Bento\\Drivers\\DriverMgrSvc' => $baseDir . '/app/core/drivers/DriverMgrSvc.php',
    'Bento\\ExtApi\\Ctrl\\DishCtrl' => $baseDir . '/app/controllers/extapi/DishCtrl.php',
    'Bento\\ExtApi\\Ctrl\\Reports\\SurveyCtrl' => $baseDir . '/app/controllers/extapi/reports/SurveyCtrl.php',
    'Bento\\Filter\\AdminFilter' => $baseDir . '/app/filters/AdminFilter.php',
    'Bento\\Filter\\ApiAuthFilter' => $baseDir . '/app/filters/ApiAuthFilter.php',
    'Bento\\Filter\\ExtApiFilter' => $baseDir . '/app/filters/ExtApiFilter.php',
    'Bento\\Lib\\Lib' => $baseDir . '/app/models/lib/Lib.php',
    'Bento\\Model\\BaseModel' => $baseDir . '/app/models/BaseModel.php',
    'Bento\\Model\\Coupon' => $baseDir . '/app/models/Coupon.php',
    'Bento\\Model\\CouponRedemption' => $baseDir . '/app/models/CouponRedemption.php',
    'Bento\\Model\\CouponRequest' => $baseDir . '/app/models/CouponRequest.php',
    'Bento\\Model\\CouponUserHash' => $baseDir . '/app/models/CouponUserHash.php',
    'Bento\\Model\\CustomerBentoBox' => $baseDir . '/app/models/CustomerBentoBox.php',
    'Bento\\Model\\LiveInventory' => $baseDir . '/app/models/LiveInventory.php',
    'Bento\\Model\\MealType' => $baseDir . '/app/models/MealType.php',
    'Bento\\Model\\Menu' => $baseDir . '/app/models/Menu.php',
    'Bento\\Model\\Order' => $baseDir . '/app/models/Order.php',
    'Bento\\Model\\OrderStatus' => $baseDir . '/app/models/OrderStatus.php',
    'Bento\\Model\\PendingOrder' => $baseDir . '/app/models/PendingOrder.php',
    'Bento\\Model\\Status' => $baseDir . '/app/models/Status.php',
    'Bento\\Model\\Traits\\ShowLocalDatesTrait' => $baseDir . '/app/models/traits/ShowLocalDatesTrait.php',
    'Bento\\Payment\\PaymentServiceProviders' => $baseDir . '/app/core/payment/PaymentServiceProviders.php',
    'Bento\\Payment\\StripeMgr' => $baseDir . '/app/core/payment/StripeMgr.php',
    'Bento\\Payment\\StripeMgrSvc' => $baseDir . '/app/core/payment/StripeMgrSvc.php',
    'Bento\\Providers\\OtherServiceProviders' => $baseDir . '/app/providers/OtherServiceProviders.php',
    'Bento\\Tracking\\TrackingServiceProviders' => $baseDir . '/app/core/tracking/TrackingServiceProviders.php',
    'Bento\\Tracking\\Trak' => $baseDir . '/app/core/tracking/Trak.php',
    'Bento\\Tracking\\TrakSvc' => $baseDir . '/app/core/tracking/TrakSvc.php',
    'Bento\\ViewComposer\\MenuTodayComposer' => $baseDir . '/app/core/view-composers/MenuTodayComposer.php',
    'Bento\\app\\Bento' => $baseDir . '/app/core/Bento.php',
    'Bento\\app\\BentoSvc' => $baseDir . '/app/core/BentoSvc.php',
    'Bento\\core\\Status' => $baseDir . '/app/core/Status.php',
    'CreatePasswordRemindersTable' => $baseDir . '/app/database/migrations/2015_03_07_000743_create_password_reminders_table.php',
    'CreateSessionTable' => $baseDir . '/app/database/migrations/2015_01_13_014144_create_session_table.php',
    'DatabaseSeeder' => $baseDir . '/app/database/seeds/DatabaseSeeder.php',
    'FbMismatchedIdException' => $baseDir . '/app/exceptions/FacebookExceptions.php',
    'HomeController' => $baseDir . '/app/controllers/examples/HomeController.php',
    'IlluminateQueueClosure' => $vendorDir . '/laravel/framework/src/Illuminate/Queue/IlluminateQueueClosure.php',
    'PhotoController' => $baseDir . '/app/controllers/examples/PhotoController.php',
    'SessionHandlerInterface' => $vendorDir . '/symfony/http-foundation/Symfony/Component/HttpFoundation/Resources/stubs/SessionHandlerInterface.php',
    'Stripe' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Stripe.php',
    'Stripe_Account' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Account.php',
    'Stripe_ApiConnectionError' => $vendorDir . '/stripe/stripe-php/lib/Stripe/ApiConnectionError.php',
    'Stripe_ApiError' => $vendorDir . '/stripe/stripe-php/lib/Stripe/ApiError.php',
    'Stripe_ApiRequestor' => $vendorDir . '/stripe/stripe-php/lib/Stripe/ApiRequestor.php',
    'Stripe_ApiResource' => $vendorDir . '/stripe/stripe-php/lib/Stripe/ApiResource.php',
    'Stripe_ApplicationFee' => $vendorDir . '/stripe/stripe-php/lib/Stripe/ApplicationFee.php',
    'Stripe_ApplicationFeeRefund' => $vendorDir . '/stripe/stripe-php/lib/Stripe/ApplicationFeeRefund.php',
    'Stripe_AttachedObject' => $vendorDir . '/stripe/stripe-php/lib/Stripe/AttachedObject.php',
    'Stripe_AuthenticationError' => $vendorDir . '/stripe/stripe-php/lib/Stripe/AuthenticationError.php',
    'Stripe_Balance' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Balance.php',
    'Stripe_BalanceTransaction' => $vendorDir . '/stripe/stripe-php/lib/Stripe/BalanceTransaction.php',
    'Stripe_BitcoinReceiver' => $vendorDir . '/stripe/stripe-php/lib/Stripe/BitcoinReceiver.php',
    'Stripe_BitcoinTransaction' => $vendorDir . '/stripe/stripe-php/lib/Stripe/BitcoinTransaction.php',
    'Stripe_Card' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Card.php',
    'Stripe_CardError' => $vendorDir . '/stripe/stripe-php/lib/Stripe/CardError.php',
    'Stripe_Charge' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Charge.php',
    'Stripe_Coupon' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Coupon.php',
    'Stripe_Customer' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Customer.php',
    'Stripe_Error' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Error.php',
    'Stripe_Event' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Event.php',
    'Stripe_FileUpload' => $vendorDir . '/stripe/stripe-php/lib/Stripe/FileUpload.php',
    'Stripe_InvalidRequestError' => $vendorDir . '/stripe/stripe-php/lib/Stripe/InvalidRequestError.php',
    'Stripe_Invoice' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Invoice.php',
    'Stripe_InvoiceItem' => $vendorDir . '/stripe/stripe-php/lib/Stripe/InvoiceItem.php',
    'Stripe_List' => $vendorDir . '/stripe/stripe-php/lib/Stripe/List.php',
    'Stripe_Object' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Object.php',
    'Stripe_Plan' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Plan.php',
    'Stripe_RateLimitError' => $vendorDir . '/stripe/stripe-php/lib/Stripe/RateLimitError.php',
    'Stripe_Recipient' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Recipient.php',
    'Stripe_Refund' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Refund.php',
    'Stripe_RequestOptions' => $vendorDir . '/stripe/stripe-php/lib/Stripe/RequestOptions.php',
    'Stripe_SingletonApiResource' => $vendorDir . '/stripe/stripe-php/lib/Stripe/SingletonApiResource.php',
    'Stripe_Subscription' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Subscription.php',
    'Stripe_Token' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Token.php',
    'Stripe_Transfer' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Transfer.php',
    'Stripe_Util' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Util.php',
    'Stripe_Util_Set' => $vendorDir . '/stripe/stripe-php/lib/Stripe/Util/Set.php',
    'TestCase' => $baseDir . '/app/tests/TestCase.php',
    'User' => $baseDir . '/app/models/User.php',
    'Whoops\\Module' => $vendorDir . '/filp/whoops/src/deprecated/Zend/Module.php',
    'Whoops\\Provider\\Zend\\ExceptionStrategy' => $vendorDir . '/filp/whoops/src/deprecated/Zend/ExceptionStrategy.php',
    'Whoops\\Provider\\Zend\\RouteNotFoundStrategy' => $vendorDir . '/filp/whoops/src/deprecated/Zend/RouteNotFoundStrategy.php',
);
