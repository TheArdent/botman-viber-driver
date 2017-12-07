<?php

namespace TheArdent\Drivers\Viber\Extensions;

use JsonSerializable;

class VideoTemplate implements JsonSerializable
{
	private $type = 'video';

	/**
	 * @var string
	 */
	protected $video;

	/**
	 * @var null|string
	 */
	private $thumbnail;

	/**
	 * @var nuLl|int
	 */
	protected $size;

	/**
	 * @var null|int
	 */
	private $duration;

	/**
	 * VideoTemplate constructor.
	 *
	 * @param      $videoUrl
	 * @param null $thumbnail
	 * @param null $size
	 * @param null $duration
	 */
	public function __construct($videoUrl, $thumbnail = null,$size = null,$duration = null)
	{
		$this->thumbnail = $thumbnail;
		$this->size = $size;
		$this->duration = $duration;
		$this->video = $videoUrl;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'type'      => $this->type,
			'media'     => $this->video,
			'thumbnail' => $this->thumbnail,
			'duration'  => $this->duration,
			'size'      => $this->getSize()
		];
	}

	/**
	 * @return int|null
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @return int|nuLl
	 */
	public function getSize()
	{
		if ($this->size)
			return $this->size;

		$ch = curl_init($this->video);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		$data = curl_exec($ch);
		$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		curl_close($ch);

		return $size;
	}

	/**
	 * @return null|string
	 */
	public function getThumbnail()
	{
		return $this->thumbnail;
	}

	/**
	 * @return string
	 */
	public function getVideo()
	{
		return $this->video;
	}
}