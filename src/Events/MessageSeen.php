<?php

namespace TheArdent\Drivers\Viber\Events;


class MessageSeen extends ViberEvent
{
	/**
	 * Return the event name to match.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'seen';
	}
}