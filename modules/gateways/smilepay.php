<?php

/**
 * Smilepay Gateway Module
 * 
 * @author owenliu0924
 * @copyright 2024 SHD Cloud
 * @see https://github.com/owenliu0924/WHMCS-Smilepay-Gateway-Module/
 * 
 * 有沒有覺得變數名稱很亂？
 * 我也這麼覺得，但我是為了方便完全根據文檔的參數來寫
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function smilepay_MetaData()
{
    return array(
        'DisplayName' => 'Smilepay Gateway Module',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function smilepay_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Smilepay 速買配',
        ),
        'Dcvc' => array(
            'FriendlyName' => '商家代號',
            'Type' => 'text',
            'Size' => '5',
            'Default' => '',
            'Description' => '輸入商家代號',
        ),
        'Rvg2c' => array(
            'FriendlyName' => '參數碼',
            'Type' => 'text',
            'Size' => '1',
            'Default' => '',
            'Description' => '我也不知道這是什麼',
        ),
        'Verify_key' => array(
            'FriendlyName' => '檢查碼',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '好像是驗證用的吧',
        ),
        'Od_sob' => array(
            'FriendlyName' => '商品名稱',
            'Type' => 'text',
            'Size' => '32',
            'Default' => '',
            'Description' => '因為 WHMCS 沒有回傳商品名稱，然後 Smilepay 又需要商品名稱，所以就自己設定一個吧',
        ),
        'testMode' => array(
            'FriendlyName' => '測試模式',
            'Type' => 'yesno',
            'Description' => '切換測試模式',
        ),
    );
}

function smilepay_link($params)
{
    // 設置參數
    $Dcvc = $params['Dcvc'];
    $Rvg2c = $params['Rvg2c'];
    // $Verify_key = $params['Verify_key'];
    $testMode = $params['testMode'];
    $Od_sob = $params['Od_sob'];

    // 收據參數
    $invoice_name = $params['description'];
    $invoice_id = $params['invoiceid'];
    $amount = $params['amount'];

    // 客戶參數
    $name = $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    // $postcode = $params['clientdetails']['postcode'];
    // $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // 系統參數
    // $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    // $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    // $moduleDisplayName = $params['name'];
    // $moduleName = $params['paymentmethod'];
    // $whmcsVersion = $params['whmcsVersion'];

    if ($testMode == true) {
        $url = 'https://ssl.smse.com.tw/ezpos_test/mtmk_utf.asp?';
    } else {
        $url = 'https://ssl.smse.com.tw/ezpos/mtmk_utf.asp?';
    }

    $postfields = array();
    $postfields['Dcvc'] = $Dcvc;
    $postfields['Rvg2c'] = $Rvg2c;
    $postfields['Od_sob'] = $Od_sob;
    $postfields['Pay_zg'] = 3;
    $postfields['Data_id'] = $invoice_id;
    $postfields['Amount'] = $amount;
    $postfields['Pur_name'] = $name;
    $postfields['Tel_number'] = $phone;
    $postfields['Mobile_number'] = $phone;
    $postfields['Address'] = $state . ' ' . $city . ' ' . $address1 . ' ' . $address2;
    $postfields['Email'] = $email;
    $postfields['Invoice_name'] = $invoice_name;
    $postfields['Roturl'] = $systemUrl . 'modules/gateways/callback/smilepay.php';


    $htmlOutput = '<form method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}
