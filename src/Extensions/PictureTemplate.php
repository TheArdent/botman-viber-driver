<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class PictureTemplate implements JsonSerializable
{
	private $type = 'picture';

	/**
	 * @var string
	 */
	protected $image;

	/**
	 * @var string
	 */
	protected $text;

	/**
	 * @var null|string
	 */
	private $thumbnail;

	/**
	 * PictureTemplate constructor.
	 *
	 * @param string        $imageUrl
	 * @param string        $text
	 * @param null | string $thumbnail
	 */
	public function __construct($imageUrl, $text, $thumbnail = null)
	{
		$this->image = $imageUrl;
		$this->text = $text;
		$this->thumbnail = $thumbnail;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'      => $this->type,
			'text'      => $this->text,
			'media'     => $this->image,
			'thumbnail' => $this->thumbnail
		];
	}
}