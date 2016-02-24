<?php



// OD vs OA txt

# Defaults
$txtThanks = 'scheduling';
$txtEnroute = "On the day of delivery, once your order is en route, we'll send you text message updates along the way. Enjoy your meal, and please feel free to reply directly to this email with any feedback.";


# OD
if ($order->order_type == 1) 
{
    $txtThanks = 'ordering';
    $txtEnroute = "Once your order is en route, we'll send you text message updates. Enjoy your meal, and please feel free to reply directly to this email with any feedback.";
}
# OA
else if ($order->order_type == 2) 
{
    # Use some defaults
    # --
    
    $txtSchedDate = $order->getScheduledDateStr('l M jS');
    $txtSchedWindow = $order->getScheduledTimeWindowStr();
    
    $txtWhen = "$txtSchedDate, $txtSchedWindow";
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Your Bento Order</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0 " />
	<style type="text/css">
		* {
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: none;
			-webkit-text-resize: 100%;
			text-resize: 100%;
		}
		a{outline: none;}
		a:hover{text-decoration: none !important;}
		table td {border-collapse: collapse !important;}
		.ExternalClass, .ExternalClass a, .ExternalClass span, .ExternalClass b, .ExternalClass br, .ExternalClass p, .ExternalClass div{line-height: inherit;}
		.address-01 a{
			text-decoration: none;
			color: #a1a7ad;
		}
		@media only screen and (max-width:500px) {
			table[class="wrapper"]{min-width: 320px !important;}
			table[class="flexible"]{width: 100% !important;}
			th[class="flex"]{
				display: block !important;
				width: 100% !important;
			}
			td[class="hide"],
			th[class="hide"],
			span[class="hide"],
			br[class="hide"],
			td[class="fix-gmail"]{
				display: none !important;
				width: 0 !important;
				height: 0 !important;
				padding: 0 !important;
				font-size: 0 !important;
				line-height: 0 !important;
			}
			td[class="img-flex"] img{
				width: 100% !important;
				height: auto !important;
			}
			td[class="aligncenter"]{text-align: center !important;}
			td[class="textblock-01"],
			td[class="address-01"]{
				padding: 20px 10px !important;
				font-size: 20px !important;
				line-height: 25px !important;
			}
			td[class="textblock-02"]{
				padding: 0 0 40px !important;
				font-size: 20px !important;
				line-height: 25px !important;
			}
			td[class="text-01"]{
				font-size: 20px !important;
				line-height: 25px !important;
			}
			td[class="img-box"]{width: auto !important;}
			td[class="img-box"] img{
				width: 100% !important;
				height: auto !important;
			}
			td[class="footer"]{
				font-size: 17px !important;
				line-height: 28px !important;
				padding: 30px 10px !important;
			}
		}
	</style>
</head>
<body style="margin: 0; padding: 0;" bgcolor="#edf0f0" link="#95ca61">
<table class="wrapper" width="100%" cellspacing="0" cellpadding="0" bgcolor="#edf0f0">
	<tr>
		<td>
			<table class="flexible" style="margin: 0 auto;" width="750" align="center" cellpadding="0" cellspacing="0">
				<!-- gmail fix -->
				<tr>
					<td class="fix-gmail">
						<table width="750" cellpadding="0" cellspacing="0" style="width: 750px !important;">
							<tr>
								<td style="min-width: 750px; font-size: 0; line-height: 0;">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- header -->
				<tr>
					<td mc:edit="block-01" class="img-flex" style="padding: 30px;" bgcolor="#95ca61" align="center">
						<a target="_blank" style="text-decoration: none;" href="http://bentonow.com"><img src="http://cdn.bentonow.com/email/logo.png" border="0" style="vertical-align: top; font: 80px/80px Arial, Verdana, Helvetica, sans-serif; color: #fff; max-width: 374px; width: 374px; height: 80px;" width="374" height="80" alt="" /></a>
					</td>
				</tr>
				<!-- shadow-blocks -->
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#e0e0e0">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#e7e7e7">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#f1f1f1">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#f1f1f1">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#fdfdfd">&nbsp;</td>
				</tr>
				<!-- block -->
				<tr>
					<td mc:edit="block-02" class="textblock-01" style="padding: 23px 10px 28px; font: 28px/32px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="center" bgcolor="#ffffff">
						Hi {{$user->firstname}}. Thanks for {{$txtThanks}} Bento!
					</td>
				</tr>
				<!-- block -->
				<tr>
					<td mc:edit="block-20" class="textblock-01" style="padding: 25px 40px 27px; font: 18px/24px Arial, Verdana, Helvetica, sans-serif; color: #fff;" align="center" bgcolor="#8abb5a">
						Give your friends $5 off their first Bento order when they use your promo code: <b>{{$user->coupon_code}}</b>.<br>
						(It works once for you too.)
					</td>
				</tr>
				<!-- shadow-blocks -->
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#e0e0e0">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#e7e7e7">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#f1f1f1">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#f1f1f1">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#fdfdfd">&nbsp;</td>
				</tr>
                                <?php if ($order->order_type == 2): ?>
				<!-- block -->
				<tr>
					<td mc:edit="block-04" class="address-01" style="padding: 25px 30px 26px; font: 25px/25px Arial, Verdana, Helvetica, sans-serif; color: #a1a7ad; border-bottom: 1px solid #d7dbdb;" bgcolor="#ffffff">
						When: {{$txtWhen}}
					</td>
				</tr>
                                <?php endif; ?>
				<!-- block -->
				<tr>
					<td mc:edit="block-04" class="address-01" style="padding: 25px 30px 26px; font: 25px/25px Arial, Verdana, Helvetica, sans-serif; color: #a1a7ad; border-bottom: 1px solid #d7dbdb;" bgcolor="#ffffff">
						Going to: {{$order->number}} {{$order->street}}, {{$order->zip}}
					</td>
				</tr>
				<!-- block -->
				<tr mc:repeatable="repeatable-01">
					<td mc:edit="block-05" class="textblock-01" style="padding: 25px 30px 31px; font: 25px/25px Arial, Verdana, Helvetica, sans-serif; color: #a1a7ad; border-bottom: 1px solid #d7dbdb;" bgcolor="#ffffff">
						Your number: {{$user->phone}}
                                                <!-- <a style="text-decoration: none; color: #a1a7ad;" href="tel:4155099815">555-123-4567</a> -->
					</td>
				</tr>
				<!-- block -->
				<tr>
					<td class="textblock-01" style="padding: 25px 37px 20px;">
						<table width="100%" cellpadding="0" cellspacing="0">
							<!-- post -->
							<tr>
								<td mc:edit="block-06" class="textblock-02" style="padding: 0 0 0px; font: 18px/22px Arial, Verdana, Helvetica, sans-serif; color: #4e5863;" align="left">
									If anything is incorrect, please reply to this email or give us a call: <a style="text-decoration:none; color:#4e5863;" href="tel:415-300-1332">(415) 300-1332</a>.<br>
									<br>
									{{$txtEnroute}}<br>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- Order Items block -->
				<tr>
					<td bgcolor="#ffffff">
						<table width="100%" cellpadding="0" cellspacing="0">
							<!-- row -->
							<tr mc:repeatable="repeatable-05">
								<td class="textblock-01" style="padding: 25px 35px 28px;"> <!-- border-top: 1px solid #d7dbdb; -->
									<?php $cashier->printEmailItems(); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- shadow-blocks -->
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#e0e0e0">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#e7e7e7">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#f1f1f1">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#f1f1f1">&nbsp;</td>
				</tr>
				<tr>
					<td height="1" style="font-size: 0; line-height: 0;" bgcolor="#fdfdfd">&nbsp;</td>
				</tr>
				<!-- Money block -->
				<tr>
					<td bgcolor="#ffffff">
						<?php $cashier->printEmailTotals($order); ?>
					</td>
				</tr>
				<!-- block -->
				<tr>
					<td mc:edit="block-20" class="textblock-01" style="padding: 25px 40px 27px; font: 22px/30px Arial, Verdana, Helvetica, sans-serif; color: #fff;" align="center" bgcolor="#8abb5a">
						Made with &hearts; in our very own kitchen.<br>
						Arigatō and Bento on!
					</td>
				</tr>
				<!-- footer -->
				<tr>
					<td mc:edit="block-21" class="footer" style="padding: 52px 10px 85px; font: 11px/16px Arial, Verdana, Helvetica, sans-serif; color: #b9b9af;" align="center">
						2152 3rd Street, San Francisco, California 94107 Ph: (415) 300-1332
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- fix -->
	<tr>
            <td><div style="display: none; white-space: nowrap; font: 15px/2px courier; color: #fff;">&nbsp;</div></td>
	</tr>
</table>
</body>
</html>