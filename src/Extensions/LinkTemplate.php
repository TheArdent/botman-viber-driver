<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class LinkTemplate implements JsonSerializable
{
	private $type = 'url';
	/**
	 * @var string
	 */
	private $url;

	/**
	 * LinkTemplate constructor.
	 *
	 * @param $url
	 */
	public function __construct($url)
	{
		$this->url = $url;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'  => $this->type,
			'media' => $this->url
		];
	}
}