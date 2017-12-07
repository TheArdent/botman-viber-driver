<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class LocationTemplate implements JsonSerializable
{
	private $type = 'location';

	/**
	 * @var string
	 */
	private $lat;

	/**
	 * @var string
	 */
	private $lon;

	/**
	 * LocationTemplate constructor.
	 *
	 * @param $lat
	 * @param $lon
	 */
	public function __construct($lat,$lon)
	{
		$this->lat = $lat;
		$this->lon = $lon;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'    => $this->type,
			'location' => [
				'lat' => $this->lat,
				'lon' => $this->lon,
			]
		];
	}
}