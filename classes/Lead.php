<?php

require_once __DIR__ . '/../crest/CRestPlus.php';

class Lead
{
	public static function add(array $fields, $params = null)
	{
		return CRest::call(
			'crm.lead.add',
			[
				'fields' => $fields,
				'params' => $params
			]
		);
	}

	public static function userFieldList(array $data)
	{
		return CRest::call(
			'crm.lead.userfield.list',
			$data
		);
	}


	public static function getFildValue(string $idFields, string $valie)
	{

		$result = Lead::userFieldList(
			[
				'filter' => [
					'FIELD_NAME' => $idFields
				]
			]
		);

		foreach ($result['result'][0]['LIST'] as $list) {
			if ($list['VALUE'] == trim($valie, ' ')) return $list['ID'];
		}
	}

	public static function getUtm($data, $value)
	{
		foreach ($data as $key => $val) {
			if ($key == $value) return $val;
		}
	}
}