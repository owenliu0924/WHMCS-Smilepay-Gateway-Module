<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = 'smilepay_cvs';
$gateway = getGatewayVariables($gatewayModuleName);
if (!$gateway["type"])
    die("Module Not Activated");
function calculateMidSmilepay($verifyParam, $amount, $smseid)
{
    $mid = $verifyParam;
    $r_all = substr($smseid, -4, 4);
    $r1 = substr($r_all, 0, 1);
    $r2 = substr($r_all, 1, 1);
    $r3 = substr($r_all, 2, 1);
    $r4 = substr($r_all, 3, 1);
    if (!is_numeric($r1)) {
        $r1 = "9";
    }
    if (!is_numeric($r2)) {
        $r2 = "9";
    }
    if (!is_numeric($r3)) {
        $r3 = "9";
    }
    if (!is_numeric($r4)) {
        $r4 = "9";
    }
    $str0 = $r1 . $r2 . $r3 . $r4;
    $str1 = str_pad($amount, 8, '0', STR_PAD_LEFT);
    $str = $mid . $str1 . $str0;
    $odd = $even = 0;
    for ($i = 0; $i < 16; $i++) {
        if ($i % 2 == 0) {
            $even = $even + substr($str, $i, 1);
        }
        if ($i % 2 == 1) {
            $odd = $odd + substr($str, $i, 1);
        }
    }
    $mid = $even * 9 + $odd * 3;
    return $mid;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $a = '';
    $calculatedMidSmilepay = calculateMidSmilepay($gateway['verify_param'], $postData['Amount'], $postData['Smseid']);
    if ($calculatedMidSmilepay != $postData['Mid_smilepay']) {
        logTransaction($gateway["name"], $postData, "Invalid Mid_smilepay");
        die("Invalid callback");
    }

    $invoiceId = $postData['Data_id'];
    $transactionId = $postData['Smseid'];
    $paymentAmount = $postData['Amount'];

    $invoiceId = checkCbInvoiceID($invoiceId, $gateway["name"]);
    checkCbTransID($transactionId);

    if ($transactionId) {
        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $paymentAmount,
            0,
            $gatewayModuleName
        );
        logTransaction($gateway["name"], $postData, "Successful");
    } else {
        logTransaction($gateway["name"], $postData, "Unsuccessful");
    }
}

header('Content-Type: text/html; charset=utf-8');
echo "<Roturlstatus>" . $gateway['Roturl_status'] . "</Roturlstatus>";