<?php

require_once __DIR__ . '/../../classes/Deal.php';
require_once __DIR__ . '/../../classes/Contact.php';
require_once __DIR__ . '/../../classes/Task.php';

class ChatPerson
{
    public static function add(array $data)
    {
        if (!$data) return FALSE;

        $name = $data['name'];
        $phone = $data['Phone'];
        $utmSource = $_REQUEST['utm_source'] ?? null;
        $utmMedium = $_REQUEST['utm_medium'] ?? null;
        $utmCampaign = $_REQUEST['utm_campaign'] ?? null;
        $utmContent = $_REQUEST['utm_content'] ?? null;
        $utmTerm = $_REQUEST['utm_term'] ?? null;

        // ищем контакт клиента по email или по телефону, если не находим, то зоздаем новые контакт и новую сделку
        $idContactPhone = Contact::findByPhone($phone);
        if ($idContactPhone) {
            $idContact = $idContactPhone;
        } else {
            $res = Contact::add([
                'NAME' => $name,
                'PHONE' => [['VALUE' => $phone], 'VALUE_TYPE' => 'WORK'],
                'UTM_SOURCE' => $utmSource,
                'UTM_MEDIUM' => $utmMedium,
                'UTM_CAMPAIGN' => $utmCampaign,
                'UTM_CONTENT' => $utmContent,
                'UTM_TERM' => $utmTerm,
            ]);
            $idContact = $res['result'];
        }

        // проверяем есть ли уже такая сделка, если есть, то прикреаляем к уже существующей сдекли комментарий о том что клеинт повторно заполнил форму
        $idDeal = Deal::checkDateCreatePervichka($idContact);

        // прверяем если клиент заполняет первый или второй раз фому марафона
        if ($idDeal) {
            $text = "
        Имя: $name
        Телефон : $phone";

            Deal::addCommentInTimeline($idDeal, "Клиент повторно заполнил форму: 'Возможно, вы хотите пообщаться лично live-devyatkino.ru/'" . $text);

            $params = [
                'TITLE' => 'Клиент повторно заполнил форму: Возможно, вы хотите пообщаться лично live-devyatkino.ru/',
                'RESPONSIBLE_ID' => Deal::getIdResposibele($idDeal),
                'UF_CRM_TASK' => ["D_$idDeal"]
            ];

            Task::addForDeal($params);
        } else {
            // создаем сделку с найденным контактом
            if ($idContact) {
                $res = Deal::add([
                    'TITLE' => 'Возможно, вы хотите пообщаться лично live-devyatkino.ru/',
                    'CONTACT_ID' => $idContact,
                    'CATEGORY_ID' => 0, // воронка Первичная
                    'UTM_SOURCE' => $utmSource,
                    'UTM_MEDIUM' => $utmMedium,
                    'UTM_CAMPAIGN' => $utmCampaign,
                    'UTM_CONTENT' => $utmContent,
                    'UTM_TERM' => $utmTerm,
                    'ASSIGNED_BY_ID' => Deal::checkOpenInfobiz($idContact),
                    'STAGE_ID' => Deal::checkOpenInfobiz($idContact) ? 'NEW' : ''
                ]);
            }
        }
    }
}
