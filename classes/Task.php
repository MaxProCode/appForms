<?php

require_once __DIR__ . '/../crest/CRestPlus.php';

class Task
{

  // создание задачи
  public static function add(array $fields)
  {
    return CRest::call(
      'tasks.task.add',
      $fields
    );
  }

  // получаем поля задачи по id
  public static function get($id)
  {
    return CRest::call(
      'tasks.task.get', [
        'taskId' => $id
      ]      
    );
  }


 /*
    $params = [
      'TITLE' => 'Test3',
      'RESPONSIBLE_ID' => 1,
      'UF_CRM_TASK' => ['D_3']
    ];
  */ 
  // создание задачи для сделки
  public static function addForDeal(array $params, $fields = null)
    {
        if (!$params) return FALSE;

        return  CRest::call(
            'task.item.add',
            [
                'fields' => $fields,
                'params' => $params
            ]
        );
    }
}
