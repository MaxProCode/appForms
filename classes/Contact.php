<?php

require_once __DIR__ . '/../crest/CRestPlus.php';

class Contact
{
	public static function add(array $fields, $params = null)
	{
		return CRest::call(
			'crm.contact.add',
			[
				'fields' => $fields,
				'params' => $params
			]
		);
	}

	// $value - email или телефон
	// $type - EMAIL или PHONE
	public static function checkDublicate($value, $type)
	{
		return CRest::call('crm.duplicate.findbycomm', [
			'type' => $type,
			'entity_type' => 'CONTACT',
			'values' => [$value]
		]);
	}

	public static function findByEmail($email)
	{
		$res = CRest::call('crm.duplicate.findbycomm', [
			'type' => 'EMAIL',
			'entity_type' => 'CONTACT',
			'values' => [$email]
		]);
		return $res['result']['CONTACT'][0] ?? null;
	}

	public static function findByPhone($phone)
	{
		$res = CRest::call('crm.duplicate.findbycomm', [
			'type' => 'PHONE',
			'entity_type' => 'CONTACT',
			'values' => [$phone]
		]);
		return $res['result']['CONTACT'][0] ?? null;
	}

	// получем поля по id контакта
	public static function get($id)
	{
		return CRest::call(
			'crm.contact.get',
			[
				'id' => $id
			]
		);
	}

	// обновление полей контакта
	public static function update($id, array $fields)
	{
		return CRest::call(
			'crm.contact.update',
			[
				'id' => $id,
				'fields' => $fields
			]
		);
	}

	// проверяем есть ли заполненные utm метки
	// если есть хоть одна метака, то взваращем false, если нет true
	public static function checkUtm($id)
	{
		$res = Contact::get($id);
		$res = $res['result'];

		$UTM_SOURCE = $res['UTM_SOURCE'];
		$UTM_MEDIUM = $res['UTM_MEDIUM'];
		$UTM_CAMPAIGN = $res['UTM_CAMPAIGN'];
		$UTM_CONTENT = $res['UTM_CONTENT'];
		$UTM_TERM = $res['UTM_TERM'];

		// проверяем заполенена ли хоть одна utm метка или нет
		if ($UTM_SOURCE || $UTM_MEDIUM || $UTM_CAMPAIGN || $UTM_CONTENT || $UTM_TERM) {
			return false;
		} else {
			return true;
		}
	}

	// проверяем есть ли заполненные utm метки
	// если нет ни одной метки, то добавляем в контакт метки
	public static function addUtm($id, $utmUrl)
	{
		if (self::checkUtm($id) && $utmUrl) {
			// обновляем utm метки
			$res = Contact::update($id, [
				'UTM_SOURCE' => self::getUtm($utmUrl, 'UTM_SOURCE'),
				'UTM_MEDIUM' => self::getUtm($utmUrl, 'UTM_MEDIUM'),
				'UTM_CAMPAIGN' => self::getUtm($utmUrl, 'UTM_CAMPAIGN'),
				'UTM_CONTENT' => self::getUtm($utmUrl, 'UTM_CONTENT'),
				'UTM_TERM' => self::getUtm($utmUrl, 'UTM_TERM'),
			]);
		}
	}

	// функция для получения utm меток из адресной строки после /?
	// на вход функции передаются все данные после "/?"
	public static function getUtm($utmUrl, $utmSearch)
	{
		$utmSearch = mb_strtolower($utmSearch);
		// удаляем первый символ "?" 
		$utm = substr($utmUrl, 1);
		// получаеп utm в виде массива
		$arrUtm = explode('&', $utm);

		foreach ($arrUtm as $key => $value) {
			$utm = explode('=', $value);
			//print_r($utm) ;
			if ($utm[0] == $utmSearch) {
				return $utm[1];
			}
		}
	}

	// добавление номера телефона к контакту
	public static function addPhone($phone, $idContact)
	{
		return	self::update($idContact, [
			'PHONE' => [['VALUE' => $phone], 'VALUE_TYPE' => 'WORK']
		]);
	}

	// добавление email к контакту
	public static function addEmail($email, $idContact)
	{
		return	self::update($idContact, [
			'EMAIL' => [['VALUE' => self::removeSpace($email)], 'VALUE_TYPE' => 'WORK']
		]);
	}

	// если найден контак по email и отличается в нем номер телефона от вводимого пользователем в форме
	// то добавлем его в контакт
	public static function checkEmail($email, $searchPhone)
	{
		if ($idContact = self::findByEmail($email)) {
			$arrContact = self::get($idContact);

			foreach ($arrContact['result']['PHONE'] as $val) {
				$phone = $val['VALUE'];

				if ($phone != $searchPhone) {
					self::addPhone($searchPhone, $idContact);
					return $idContact;
				}
			}
		} else {
			return false;
		}
	}

	// если найден контакт по телефону и отличается в нем email от вводимого пользователем в форме
	// то добавлем его в контакт
	public static function checkPhone($phone, $searchEmail)
	{
		if ($idContact = self::findByPhone($phone)) {
			$arrContact = self::get($idContact);

			foreach ($arrContact['result']['EMAIL'] as $val) {
				$email = $val['VALUE'];

				if ($email != $searchEmail) {
					self::addEmail($searchEmail, $idContact);
					return $idContact;
				}
			}
		} else {
			return false;
		}
	}

	// убираем пробелы из строки
	public static function removeSpace($string)
	{
		return str_replace(' ', '', $string);
	}
}
