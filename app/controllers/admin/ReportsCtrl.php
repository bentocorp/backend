<?php namespace Bento\Admin\Ctrl;

use DB;
use Input;
use Redirect;
use Request;
use Response;
use View;

class ReportsCtrl extends AdminBaseController {

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
            return View::make('admin.reports.orderhistory', $data);
        }
    }
}
