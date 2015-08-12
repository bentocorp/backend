<?php namespace Bento\Admin\Ctrl;

use DB;
use Input;
use Redirect;
use Request;
use Response;
use View;

class ReportsCtrl extends AdminBaseController {
    public function getCouponredemptions() {
        $minId = Input::get("min_id", "0");
        $format = Input::get("format", "html");

        $sql = <<<QUERY
          SELECT
              c.pk_CouponRedemption coupon_redemption_id,
              c.created_at created_at,
              c.fk_Coupon coupon_id,
              u.pk_User user_id,
              u.email email,
              u.created_at user_created_at
          FROM bento.`CouponRedemption` c
          LEFT JOIN User u on c.fk_User = u.pk_User
          WHERE pk_CouponRedemption >= ?
          ORDER BY c.pk_CouponRedemption ASC
          LIMIT 1000
QUERY;
        $data = array();
        $data['redemptions'] = Db::select($sql, array($minId));

        if (Request::wantsJson() || $format == "json") {
            return Response::json(array("data" => $data));
        } else {
            $data['minId'] = $minId;
            return View::make('admin.reports.couponredemptions', $data);
        }
    }

    public function getOrderhistory() {
        $minId = Input::get("min_id", "0");
        $format = Input::get("format", "html");

        $sql = <<<QUERY
          SELECT
              o.pk_Order order_id,
              o.created_at order_created_at,
              o.amount amount,
              o.fk_Coupon coupon_id,
              u.created_at user_created_at,
              u.email email
          FROM bento.`Order` o
          LEFT JOIN User u ON o.fk_User = u.pk_User
          WHERE o.pk_Order >= ?
          ORDER BY o.pk_Order ASC
          LIMIT 1000
QUERY;
        $data = array();
        $data['orders'] = Db::select($sql, array($minId));

        if (Request::wantsJson() || $format == "json") {
            return Response::json(array("data" => $data));
        } else {
            $data['minId'] = $minId;
            return View::make('admin.reports.orderhistory', $data);
        }
    }

    public function getUsers() {
        $minId = Input::get("min_id", "0");
        $format = Input::get("format", "html");

        $sql = <<<QUERY
          SELECT
              pk_User user_id,
              created_at,
              email,
              firstname,
              lastname,
              fb_id
          FROM bento.`User` u
          WHERE pk_User >= ?
          ORDER BY pk_User ASC
          LIMIT 1000
QUERY;
        $data = array();
        $data['users'] = Db::select($sql, array($minId));

        if (Request::wantsJson() || $format == "json") {
            return Response::json(array("data" => $data));
        } else {
            $data['minId'] = $minId;
            return View::make('admin.reports.users', $data);
        }
    }
}
