<?php

if (!$_REQUEST['formid']) {
    return FALSE;
}

// роутер который проверяет какую форму нужно создавать
switch ($_REQUEST['formid']) {
    case 'form498830693':
        require_once __DIR__ . '/../../src/liveDevyatkino/ContactUs.php';
        ContactUs::add($_REQUEST);
        break;

    case 'form499627605':
        require_once __DIR__ . '/../../src/liveDevyatkino/BookTicket.php';
        BookTicket::add($_REQUEST);
        break;

    case 'form499627840':
        require_once __DIR__ . '/../../src/liveDevyatkino/ContactUs.php';
        ContactUs::add($_REQUEST);
        break;

    case 'form498830157':
        require_once __DIR__ . '/../../src/liveDevyatkino/ChatPerson.php';
        ChatPerson::add($_REQUEST);
        break;
}
