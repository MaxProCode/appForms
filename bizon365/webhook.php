<?php

require_once __DIR__ . "/../classes/Deb.php";


if (chekCityTest($_REQUEST['items']) == 'TEST') {
    switch ($_REQUEST['action']) {
            // если пришла полная оплата MSK
        case 'paidorder':
            require_once __DIR__ . "/../src/Marafon/Msk/Pay/PayMsk.php";
            PayMsk::add($_REQUEST);
            break;
    }
} else if (chekCity($_REQUEST['items']) == 'MSK') {
    switch ($_REQUEST['action']) {
            // если пришла полная оплата MSK
        case 'paidorder':
            require_once __DIR__ . "/../src/Marafon/Msk/Pay/PayMsk.php";
            PayMsk::add($_REQUEST);
            break;
    }
} else if (chekCity($_REQUEST['items']) == 'SPB') {
    switch ($_REQUEST['action']) {
            // если пришла полная оплата SPB
        case 'paidorder':
            require_once __DIR__ . "/../src/Marafon/Spb/Pay/PaySpb.php";
            PaySpb::add($_REQUEST);
            break;
    }
}

function chekCity($data)
{
    $haystack = $data;
    $needle   = 'msk';
    $pos      = strripos($haystack, $needle);

    if ($pos === false) {
        return "SPB";
    } else {
        return "MSK";
    }
}

function chekCityTest($data)
{
    $haystack = $data;
    $msk   = 'test';
    $pos = strripos($haystack, $msk);


    if ($pos === false) {
        return false;
    } else {
        return "TEST";
    }
}
