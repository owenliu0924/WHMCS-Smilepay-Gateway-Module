<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function smilepay_famiport_MetaData()
{
    return array(
        'DisplayName' => 'Smilepay FamiPort',
        'APIVersion' => '1.1',
    );
}

function smilepay_famiport_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Smilepay FamiPort',
        ),
        'dcvc' => array(
            'FriendlyName' => '商家代號',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => '請輸入 Smilepay 提供的商家代號',
        ),
        'rvg2c' => array(
            'FriendlyName' => '參數碼',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => '請輸入 Smilepay 提供的參數碼',
        ),
        'verify_key' => array(
            'FriendlyName' => '檢查碼',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => '請輸入 Smilepay 提供的檢查碼',
        ),
        'testMode' => array(
            'FriendlyName' => '測試模式',
            'Type' => 'yesno',
            'Description' => '勾選以使用測試環境',
        ),
        'Roturl_status' => array(
            'FriendlyName' => '回調狀態碼',
            'Type' => 'text',
            'Size' => '20',
            'Default' => 'RL_OK',
            'Description' => '設定回調成功時的回應狀態碼',
        ),
        'verify_param' => array(
            'FriendlyName' => '商家驗證參數',
            'Type' => 'text',
            'Size' => '4',
            'Default' => '0000',
            'Description' => '請輸入 Smilepay 提供的商家驗證參數 (四位數字)',
        ),
    );
}

function smilepay_famiport_link($params)
{
    $dcvc = $params['dcvc'];
    $rvg2c = $params['rvg2c'];
    $verify_key = $params['verify_key'];
    $testMode = $params['testMode'];

    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $returnUrl = $params['systemurl'] . '/modules/gateways/callback/smilepay_famiport.php';

    $apiUrl = $testMode ? 'https://ssl.smse.com.tw/ezpos_test/mtmk_utf.asp' : 'https://ssl.smse.com.tw/ezpos/mtmk_utf.asp';

    $postData = array(
        'Dcvc' => $dcvc,
        'Rvg2c' => $rvg2c,
        'Verify_key' => $verify_key,
        'Od_sob' => $description,
        'Pay_zg' => '6',
        'Data_id' => $invoiceId,
        'Amount' => $amount,
        'Pur_name' => $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'],
        'Tel_number' => $params['clientdetails']['phonenumber'],
        'Email' => $params['clientdetails']['email'],
        'Roturl' => $returnUrl,
        'Roturl_status' => $params['Roturl_status'],
    );

    $htmlOutput = '<form target="_blank" method="POST" action="' . $apiUrl . '">';
    foreach ($postData as $key => $value) {
        $htmlOutput .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
    }
    $htmlOutput .= '<input type="submit" value="前往繳費">';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

function smilepay_famiport_refund($params)
{
    return array(
        'status' => 'error',
        'rawdata' => '暫不支援退款功能'
    );
}