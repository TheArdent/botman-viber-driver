<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class ContactTemplate implements JsonSerializable
{
	private $type = 'contact';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $phone_number;

	/**
	 * ContactTemplate constructor.
	 *
	 * @param $name string
	 * @param $phone_number string
	 */
	public function __construct($name, $phone_number)
	{
		$this->name = $name;
		$this->phone_number = $phone_number;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'    => $this->type,
			'contact' => [
				'name'         => $this->name,
				'phone_number' => $this->phone_number,
			]
		];
	}
}