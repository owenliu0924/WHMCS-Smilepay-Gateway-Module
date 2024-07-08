<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = 'smilepay_cvs';
$gateway = getGatewayVariables($gatewayModuleName);
if (!$gateway["type"])
    die("Module Not Activated");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;

    $calculatedMidSmilepay = md5($gateway['dcvc'] . $postData['Data_id'] . $postData['Amount'] . $gateway['verify_key']);
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