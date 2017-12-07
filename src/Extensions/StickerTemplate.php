<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class StickerTemplate implements JsonSerializable
{
	private $type = 'sticker';

	/**
	 * @var integer
	 */
	private $stickerId;

	/**
	 * StickerTemplate constructor.
	 *
	 * @param $stickerId integer
	 */
	public function __construct($stickerId)
	{
		$this->stickerId = $stickerId;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'       => $this->type,
			'sticker_id' => $this->stickerId
		];
	}
}