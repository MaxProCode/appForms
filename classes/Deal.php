<?php

require_once __DIR__ . '/../crest/CRestPlus.php';

class Deal
{
	public static function add(array $fields, $params = null)
	{
		return CRest::call(
			'crm.deal.add',
			[
				'fields' => $fields,
				'params' => $params
			]
		);
	}

	// добавление контакта к указанной сделки
	public static function conactAdd($idDeal, array $fields)
	{
		return CRest::call(
			'crm.deal.contact.add',
			[
				'id' => $idDeal,
				'fields' => $fields
			]
		);
	}

	public static function get($id)
	{
		return CRest::call(
			'crm.deal.get',
			[
				'id' => $id
			]
		);
	}

	public static function checkIdMarafon($idMarafon, $idDeal)
	{
		$res = self::get($idDeal);

		if ($res['result']['UF_CRM_1637166991983'] == $idMarafon) {
			return true;
		} else false;
	}

	public static function checkIdAnketa($idAnketa, $idDeal)
	{
		$res = self::get($idDeal);

		if ($res['result']['UF_CRM_1637226553'] == $idAnketa) {
			return true;
		} else false;
	}

	public static function update($id, array $fields, $params = null)
	{
		return CRest::call(
			'crm.deal.update',
			[
				'id' => $id,
				'fields' => $fields,
				'params' => $params
			]
		);
	}

	public static function stageUpdate($id, $stage)
	{
		return CRest::call(
			'crm.deal.update',
			[
				'id' => $id,
				'fields' => [
					'STAGE_ID' => $stage
				]
			]
		);
	}

	public static function userFieldList(array $data)
	{
		return CRest::call(
			'crm.deal.userfield.list',
			$data
		);
	}

	public static function getFildValue(string $idFields, string $valie)
	{

		$result = Deal::userFieldList(
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

	// ищем сделку по id контаката и воронке Инфобиз
	public static function findDealByContactId($contactId)
	{
		$res =	CRest::call(
			'crm.deal.list',
			[
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => 3
				]
			]
		);

		return $res['result'][0]['ID'];
	}

	public static function findDealsByContactId($contactId)
	{
		$res =	CRest::call(
			'crm.deal.list',
			[
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => 3
				],
				'select' => [
					'UF_CRM_1637226553', // ID Анкеты
					'UF_CRM_1637166991983', // ID Марафона
				]
			]
		);

		return $res['result'];
	}

	// ищем по id контакта сделки 
	/* $params - это
	[
		'filter' => [
			'CONTACT_ID' => $contactId,
			'CATEGORY_ID' => 3
		],
		'select' => [
			'UF_CRM_1637226553', // ID Анкеты
			'UF_CRM_1637166991983' // ID Марафона
		]
	]
	*/
	public static function findDealsByContactId2($contactId, array $params)
	{
		$res =	CRest::call('crm.deal.list', $params);

		return $res['result'];
	}

	public static function checkMarafon($id, $contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId($contactId);

			foreach ($arrDeals as $val) {
				if ($val['UF_CRM_1637166991983'] == $id) return $val['ID'];
			}
		}
	}

	// проверяем есть ли сделка с уже заполенной формой Получить консултация на сайте https://xn--80aaaak7aalujrbtdqm.xn--p1ai/
	public static function checkSite($id, $contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId
				],
				'select' => [
					'UF_CRM_1643881673' // id формы
				]
			]);
			foreach ($arrDeals as $val) {
				if ($val['UF_CRM_1643881673'] == $id) return $val['ID'];
			}
		}
	}


	// проверяем закрыта ли сделка с анкетой 
	public static function isClose($id, $contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId
				],
				'select' => [
					'UF_CRM_1643881673', // id формы
					'CLOSED'

				]
			]);
			foreach ($arrDeals as $val) {
				if ($val['UF_CRM_1643881673'] == $id && $val['CLOSED'] == 'Y') {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	// проверяем открытая  сделка с анкетой 
	public static function checkOpen($id, $contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId
				],
				'select' => [
					'UF_CRM_1643881673', // id формы
					'CLOSED'

				]
			]);
			foreach ($arrDeals as $val) {
				if ($val['UF_CRM_1643881673'] == $id && $val['CLOSED'] != 'Y') {
					return $val['ID'];
				}
			}
		}
	}


	// проверяем есть ли открытая сделка в воронке первичная, если есть, то возваращаем id сделки
	public static function checkOpenPervichka($contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => 0
				],
				'select' => [
					'CLOSED'
				]
			]);
			foreach ($arrDeals as $val) {
				if ($val['CLOSED'] != 'Y') {
					return $val['ID'];
				}
			}
		}
	}

	// проверяем есть ли открытая сделка в инфобизе, не учитывая первый статус, если есть, то возвращаем id ответственного 
	public static function checkOpenInfobiz($contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => 3 // воронка инфобиз
				],
				'select' => [
					//'UF_CRM_1643881673', // id формы
					'CLOSED',
					'STAGE_ID',
					'ASSIGNED_BY_ID'
				]
			]);

			foreach ($arrDeals as $val) {
				if ($val['CLOSED'] != 'Y' && $val['STAGE_ID'] != 'C3:NEW') {
					return $val['ASSIGNED_BY_ID'];
				}
			}
		}
	}

	// проверяем есть ли открытая сделка в первичке, не учитывая первый статус, если есть, то возвращаем id ответственного 
	public static function checkOpenPervichnaya($contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => 0 // воронка инфобиз
				],
				'select' => [
					//'UF_CRM_1643881673', // id формы
					'CLOSED',
					'STAGE_ID',
					'ASSIGNED_BY_ID'
				]
			]);

			foreach ($arrDeals as $val) {
				if ($val['CLOSED'] != 'Y' && $val['STAGE_ID'] != 'UC_UHVMUF') {
					return $val['ASSIGNED_BY_ID'];
				}
			}
		}
	}

	public static function checkVebinar($id, $contactId)
	{
		$arrDeals = self::findDealsByContactId2($contactId, [
			'filter' => [
				'CONTACT_ID' => $contactId,
				'CATEGORY_ID' => 3
			],
			'select' => [
				'UF_CRM_1639568496831' // id вебинара
			]
		]);

		foreach ($arrDeals as $val) {
			if ($val['UF_CRM_1639568496831'] == $id) return $val['ID'];
		}
	}

	public static function checkClaimVebinar($id, $contactId)
	{
		$arrDeals = self::findDealsByContactId2($contactId, [
			'filter' => [
				'CONTACT_ID' => $contactId,
				'CATEGORY_ID' => 3
			],
			'select' => [
				'UF_CRM_1639568522026' // id вебинара
			]
		]);

		foreach ($arrDeals as $val) {
			if ($val['UF_CRM_1639568522026'] == $id) return $val['ID'];
		}
	}

	public static function checkAnketa($id, $contactId)
	{
		$arrDeals = self::findDealsByContactId($contactId);

		foreach ($arrDeals as $val) {
			if ($val['UF_CRM_1637226553'] == $id) return $val['ID'];
		}
	}

	public static function checkClaimVebinar2($id, $contactId)
	{
		$arrDeals = self::findDealsByContactId($contactId);

		foreach ($arrDeals as $val) {
			if ($val['UF_CRM_1639568522026'] == $id) return $val['ID'];
		}
	}

	public static function addCommentInTimeline($idDeal, $comment)
	{
		$res =	CRest::call(
			'crm.timeline.comment.add',
			[
				'fields' => [
					'ENTITY_ID' => $idDeal,
					'ENTITY_TYPE' => 'deal',
					'COMMENT' => $comment
				]
			]
		);

		return $res['result'];
	}

	// проверяем есть ли открытая сделка в воронке Первичная, если есть, то проверяем если она старше 21-го дня, то возвращаем FALSE, если нет, то возвращаем id сделки 
	public static function checkDateCreatePervichka($contactId)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => 0
				],
				'select' => [
					'CLOSED',
					'DATE_CREATE'
				]
			]);

			foreach ($arrDeals as $val) {
				if ($val['CLOSED'] != 'Y') {

					$curentdate = time();
					$resDate = ($curentdate - strtotime(substr(substr($val['DATE_CREATE'], 0, 10), -10))) / 86400;
					$day = round($resDate);

					// если сделка больше 21-го дня, то выводим false
					if ($day > 21) {
						return false;
					} else {
						return $val['ID'];
					}
				}
			}
		}
	}

	// получаем id значений для множественного поля
	public static function getMultiFildValue(string $idFields, string $flatPay)
	{

		$result = Deal::userFieldList(
			[
				'filter' => [
					'FIELD_NAME' => $idFields
				]
			]
		);

		$flatPay = explode(';', $flatPay);

		for ($i = 0; $i < count($flatPay); $i++) {
			foreach ($result['result'][0]['LIST'] as $list) {
				if ($list['VALUE'] == trim($flatPay[$i], ' ')) {
					$ids[] = $list['ID'];
				}
			}
		}


		return $ids;
	}

	// проверяем есть ли открытая сделка в воронке
	public static function checkOpen2($contactId, $idCategory)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => $idCategory,
					'CLOSED' => 'N'
				]
			]);

			return $arrDeals[0]['ID'];
		}
	}

	// получаем id ответсвенного за сделку
	public static function getIdResposibele($idDeal)
	{
		$res = self::get($idDeal);
		return $res['result']['ASSIGNED_BY_ID'];
	}

	public static function getIdFunnelByNameCity($name)
	{
		if (!$name) return false;

		switch ($name) {
			case 'Санкт-Петербург':
				return 0;
				break;
			case 'Москва':
				return 17;
				break;
			case 'Дубай':
				return 7;
				break;
			case 'Аланья': // Турция
				return 21;
				break;
		}
	}

	public static function checkOpenDeal($contactId, $idFunnel)
	{
		if ($contactId) {
			$arrDeals = self::findDealsByContactId2($contactId, [
				'filter' => [
					'CONTACT_ID' => $contactId,
					'CATEGORY_ID' => $idFunnel
				],
				'select' => [
					'CLOSED',
					'DATE_CREATE',
					'STAGE_ID'
				]
			]);

			$stageException = [
				'UC_S8NOVG',
				'PREPAYMENT_INVOICE',
				'EXECUTING',
				'FINAL_INVOICE',
				'UC_7H86K0',
				'C17:FINAL_INVOICE',
				'C17:UC_KFH6T1',
				'C17:UC_TZMRHI',
				'C17:UC_SVNJ3C',
				'C17:UC_JSP8XA',
				'C7:UC_1PN5A0',
				'C7:UC_SOWLYL',
				'C7:UC_USEPMN',
				'C7:UC_F91FNA',
				'C7:UC_2NWXPQ',
				'C21:FINAL_INVOICE',
				'C21:UC_NV83ZS',
				'C21:UC_D71E0I',
				'C21:UC_06N4PL',
				'C21:UC_Z0YW76'
			];

			foreach ($arrDeals as $val) {
				if ($val['CLOSED'] != 'Y') {

					$curentdate = time();
					$resDate = ($curentdate - strtotime(substr(substr($val['DATE_CREATE'], 0, 10), -10))) / 86400;
					$day = round($resDate);

					// если сделка больше 21-го дня, то выводим false
					if ($day > 21) {
						return false;
					} else if ($day < 21 &&  (in_array($val['STAGE_ID'], $stageException))) {
						return $val['ID'];
					} else {
						return $val['ID'];
					}
				}
			}
		}
	}
}
