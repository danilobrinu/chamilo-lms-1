<?php

/* For license terms, see /license.txt */
/**
 * List page for Paypal Payout for the Buy Courses plugin
 * @package chamilo.plugin.buycourses
 */
/**
 * Initialization
 */

$cidReset = true;

require_once '../../../main/inc/global.inc.php';

$htmlHeadXtra[] = '<link rel="stylesheet" href="../resources/css/style.css" type="text/css">';

api_protect_admin_script(true);

$plugin = BuyCoursesPlugin::create();

$paypalEnable = $plugin->get('paypal_enable');
$comissionsEnable = $plugin->get('comissions_enable');

if ($paypalEnable !== "true" && $comissionsEnable !== "true") {
    api_not_allowed(true);
}

$payouts = $plugin->getPayouts();

$payoutList = [];

foreach ($payouts as $payout) {
    $payoutList[] = [
        'id' => $payout['id'],
        'reference' => $payout['sale_reference'],
        'date' => api_format_date($payout['date'], DATE_TIME_FORMAT_LONG_24H),
        'currency' => $payout['iso_code'],
        'price' => $payout['item_price'],
        'comission' => $payout['comission'],
        'paypal_account' => $payout['paypal_account']
    ];
}

$templateName = $plugin->get_lang('PaypalPayoutComissions');

$template = new Template($templateName);

$template->assign('payout_list', $payoutList);

$content = $template->fetch('buycourses/view/paypal_payout.tpl');

$template->assign('header', $templateName);
$template->assign('content', $content);
$template->display_one_col_template();