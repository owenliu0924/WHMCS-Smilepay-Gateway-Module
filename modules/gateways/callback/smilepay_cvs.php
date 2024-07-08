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
    $a = str_pad($verifyParam, 4, '0', STR_PAD_LEFT);
    $b = str_pad($amount, 8, '0', STR_PAD_LEFT);
    $c = substr($smseid, -4);
    $c = preg_replace('/[^0-9]/', '9', $c);
    $d = $a . $b . $c;
    $e = 0;
    for ($i = 1; $i < strlen($d); $i += 2) {
        $e += intval($d[$i]);
    }
    $e *= 3;
    $f = 0;
    for ($i = 0; $i < strlen($d); $i += 2) {
        $f += intval($d[$i]);
    }
    $f *= 9;
    return $e + $f;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $a = '';
    $calculatedMidSmilepay = calculateMidSmilepay($gateway['verify_param'], $postData['Amount'], $postData['Smseid']);
    if ($calculatedMidSmilepay !== $postData['Mid_smilepay']) {
        logTransaction($gateway["name"], $postData, "Invalid Mid_smilepay");
        die("Invalid callback");
    }

    $invoiceId = $postData['Data_id'];
    $transactionId = $postData['Payment_no'];
    $paymentAmount = $postData['Amount'];
    $paymentSuccess = ($postData['Response_id'] == '1');

    $invoiceId = checkCbInvoiceID($invoiceId, $gateway["name"]);
    checkCbTransID($transactionId);

    if ($paymentSuccess) {
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