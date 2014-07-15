<?php

/***************************************************************************
*                                                                          *
*   (c) 2014 Refiral                                                       *
*    http://www.refiral.com                                                *
*    support@refiral.com                                                   *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "LICENSE.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.              *
****************************************************************************/



use Tygh\Registry;

if (!defined('BOOTSTRAP'))  die('Access denied');

echo Registry::get('runtime.controller');

function refiral_campaign_init($apiKey, $status){
	echo '<script type="text/javascript">var apiKey = "'.$apiKey.'";</script>';
	if($status)
		echo '<script type="text/javascript">var showButton = true;</script>';
	else
		echo '<script type="text/javascript">var showButton = false;</script>';
	echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>';
	echo '<script type="text/javascript" src="//rfer.co/api/v1/js/all.js"></script>';
}

function fn_refiral_calculate_cart(&$cart, $cart_products, $auth, $calculate_shipping, $calculate_taxes, $apply_cart_promotions){
	session_start();
	
	$_SESSION['refiral'] = isset($_SESSION['refiral']) ? $_SESSION['refiral'] : array();
	if(strpos($_SERVER['REQUEST_URI'], 'checkout.complete') === false){
		/*print "<pre>";
		print_r($cart);
		print "</pre>";
		*/
		$_SESSION['refiral']['customer_name'] = $cart['user_data']['b_firstname']." ".$cart['user_data']['b_lastname'];
		$_SESSION['refiral']['customer_email'] = $cart['user_data']['email'];
		$_SESSION['refiral']['subTotal'] = $cart['original_subtotal'];
		$_SESSION['refiral']['grandTotal'] = $cart['total'];
		
		if(count($cart['coupons']) >= 1){
			foreach($cart['coupons'] as $code => $details){
				$_SESSION['refiral']['couponCode'] = $code;
			}
		}
		else{
			$_SESSION['refiral']['couponCode'] = '';
		}
		
		$refiral_cart_info = array();
		foreach($cart_products as $item_id => $item) {
			$refiral_cart_info[] = array('product_id' => $item['product_id'], 'quantity' => $item['amount'], 'name' => $item['product'], 'price' => $item['price']);
		}
		$_SESSION['refiral']['refiral_cart_items'] = $refiral_cart_info;
	}
}

$apiKey = Registry::get('addons.refiral.apiKey');
$refiral_campaign_status = Registry::get('addons.refiral.campaign_status');
if(($refiral_campaign_status == 'on') && (strpos($_SERVER['REQUEST_URI'], 'admin') === false) && (strpos($_SERVER['REQUEST_URI'], 'checkout.complete') === false)){
	refiral_campaign_init($apiKey, true);
}
else{
	refiral_campaign_init($apiKey, false);
}

if(strpos($_SERVER['REQUEST_URI'], 'checkout.complete') !== false){
	echo '<script type="text/javascript">';
	echo 'var customer_name = "'.$_SESSION['refiral']['customer_name'].'";';
	echo 'var customer_email = "'.$_SESSION['refiral']['customer_email'].'";';
	echo 'var subTotal = "'.$_SESSION['refiral']['subTotal'].'";';
	echo 'var grandTotal = "'.$_SESSION['refiral']['grandTotal'].'";';
	echo 'var couponCode = "'.$_SESSION['refiral']['couponCode'].'";';
	echo 'var refiral_cart_items = '.json_encode($_SESSION['refiral']['refiral_cart_items']).';';
	$refiral_cart_items = json_encode($_SESSION['refiral']['refiral_cart_items']);
	echo "invoiceRefiral(subTotal, grandTotal, couponCode, '$refiral_cart_items', customer_name, customer_email);";
	echo '</script>';
	unset($_SESSION['refiral']);
}