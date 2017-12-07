<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class FileTemplate implements JsonSerializable
{
	private $type = 'file';

	/**
	 * @var string
	 */
	protected $fileUrl;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var nuLl|int
	 */
	protected $size;

	/**
	 * FileTemplate constructor.
	 *
	 * @param      $fileUrl
	 * @param      $name
	 * @param null $size
	 */
	public function __construct($fileUrl, $name, $size = null)
	{
		$this->fileUrl = $fileUrl;
		$this->name = $name;
		$this->size = $size;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'      => $this->type,
			'media'     => $this->fileUrl,
			'file_name' => $this->name,
			'size'      => $this->getSize()
		];
	}

	/**
	 * @return int|nuLl
	 */
	public function getSize()
	{
		if ($this->size) {
			return $this->size;
		}

		$ch = curl_init($this->fileUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		curl_close($ch);

		return $size;
	}
}