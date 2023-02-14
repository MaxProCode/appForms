<?php

require_once __DIR__ . '/../../../../classes/Deal.php';
require_once __DIR__ . '/../../../../classes/Contact.php';
require_once __DIR__ . '/../../../../classes/Deb.php';

class PaySpb
{
    public static function add(array $data)
    {
        $lastName = self::getLastName($_REQUEST['name_utf8']);
        $name = self::getName($_REQUEST['name_utf8']);
        $phone = $_REQUEST['phone'];
        $email = str_replace(' ', '', $_REQUEST['email']);
        $utmSource = $_REQUEST['utm_source'] ?? null;
        $utmMedium = $_REQUEST['utm_medium'] ?? null;
        $utmCampaign = $_REQUEST['utm_campaign'] ?? null;
        $utmContent = $_REQUEST['utm_content'] ?? null;
        $utmTerm = $_REQUEST['utm_term'] ?? null;
        $tarif = $data['Тариф'] ?? null;
        $url = $_REQUEST['url'] ?? null;
        $amount = $_REQUEST['amount'] ?? null;

        $idContactEmail = Contact::findByEmail($email);
        $idContactPhone = Contact::findByPhone($phone);
        if ($idContactEmail || $idContactPhone) {
            $idContact = $idContactEmail ? $idContactEmail : $idContactPhone;

            Contact::checkEmail($email, $phone);
            Contact::checkPhone($phone, $email);
        } else {
            $res = Contact::add([
                'NAME' => $name,
                'LAST_NAME' => $lastName,
                'PHONE' => [['VALUE' => $phone], 'VALUE_TYPE' => 'WORK'],
                'EMAIL' => [['VALUE' => $email], 'VALUE_TYPE' => 'WORK'],
                'UTM_SOURCE' => $utmSource,
                'UTM_MEDIUM' => $utmMedium,
                'UTM_CAMPAIGN' => $utmCampaign,
                'UTM_CONTENT' => $utmContent,
                'UTM_TERM' => $utmTerm,
            ]);
            $idContact = $res['result'];
        }

        $idDeal = self::checkDeal($idContact);
        Deb::log($idDeal, __DIR__ . '/../../../log/log.txt', 'Найдена сделка1');
        // прверяем если клиент заполняет первый или второй раз форму марафона
        if ($idDeal) {
            $res = Deal::update($idDeal, [
                'STAGE_ID' => 'C23:UC_WMM4YP',
                "CURRENCY_ID" => "RUB",
                "OPPORTUNITY" => $amount,
                'UF_CRM_1660248832107' => ['VALUE' => 1465] // город SPB
            ]);
        } else {
            $res = Deal::add([
                'TITLE' => 'Оплата SPB',
                "CURRENCY_ID" => "RUB",
                "OPPORTUNITY" => $amount,
                'STAGE_ID' => 'C23:UC_WMM4YP',
                'CONTACT_ID' => $idContact,
                'CATEGORY_ID' => 23, // воронка Свободный Агент
                'UTM_SOURCE' => $utmSource,
                'UTM_MEDIUM' => $utmMedium,
                'UTM_CAMPAIGN' => $utmCampaign,
                'UTM_CONTENT' => $utmContent,
                'UTM_TERM' => $utmTerm,
                'UF_CRM_1643184431' => $url, // ссылка на страницу
                'UF_CRM_1660248832107' => ['VALUE' => 1465] // город SPB
                // 'UF_CRM_1657050419189' => ['VALUE' => 1419]
            ]);
        }
    }

    // Проверяем есть ли открытая сделка в воронке Свободный Агент
    public static function checkDeal($idContact)
    {
        $res = Deal::findDealsByContactId2($idContact, [
            'filter' => [
                'CONTACT_ID' => $idContact,
                'CATEGORY_ID' => 23,
                'CLOSED' => 'N' // отбираем только открыте сделки
            ]
        ]);

        return $res[0]['ID'] ?? false;
    }

    public static function getName(string $data)
    {
        if (empty($data)) {
            return false;
        }

        $res = explode(' ', $data);

        return $res[1];
    }


    public static function getLastName(string $data)
    {
        if (empty($data)) {
            return false;
        }

        $res = explode(' ', $data);

        return $res[0];
    }
}
