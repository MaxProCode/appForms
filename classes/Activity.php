<?php

require_once __DIR__ . '/../crest/CRestPlus.php';

class Activity
{

  // получаем дела по фильтру
  public static function list($ownerId)
  {
    return CRest::call(
      'crm.activity.list',
      [
        'order' => [
          "ID" => "DESC"
        ],
        'filter' => [
          'OWNER_ID' => $ownerId
        ]
      ]
    );
  }
}
