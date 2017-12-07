<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class KeyboardTemplate implements JsonSerializable
{
	private $type = 'text';

	/**
	 * @var string
	 */
	protected $text;

	/**
	 * @var array
	 */
	protected $buttons;

	/**
	 * PictureTemplate constructor.
	 *
	 * @param string $imageUrl
	 * @param string $text
	 */
	public function __construct($text)
	{
		$this->text = $text;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'     => $this->type,
			'text'     => $this->text,
			'keyboard' => [
				'Type'          => 'keyboard',
				'DefaultHeight' => true,
				'Buttons'       => $this->buttons
			]
		];
	}

	/**
	 * @param        $text
	 * @param string $actionType
	 * @param string $actionBody
	 * @param string $textSize
	 */
	public function addButton($text, $actionType = 'reply', $actionBody = 'reply to me', $textSize = 'regular')
	{
		$this->buttons[] = [
			"ActionType" => $actionType,
			"ActionBody" => $actionBody,
			"Text"       => $text,
			"TextSize"   => $textSize,
		];
	}
}