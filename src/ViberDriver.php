<?php

namespace TheArdent\Drivers\Viber;

use BotMan\BotMan\Interfaces\DriverEventInterface;
use BotMan\BotMan\Interfaces\VerifiesService;
use BotMan\BotMan\Users\User;
use Illuminate\Support\Collection;
use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use TheArdent\Drivers\Viber\Events\MessageDelivered;
use TheArdent\Drivers\Viber\Events\MessageFailed;
use TheArdent\Drivers\Viber\Events\MessageSeen;
use TheArdent\Drivers\Viber\Events\MessageStarted;
use TheArdent\Drivers\Viber\Events\UserSubscribed;
use TheArdent\Drivers\Viber\Events\UserUnsubscribed;
use TheArdent\Drivers\Viber\Extensions\ContactTemplate;
use TheArdent\Drivers\Viber\Extensions\FileTemplate;
use TheArdent\Drivers\Viber\Extensions\KeyboardTemplate;
use TheArdent\Drivers\Viber\Extensions\LinkTemplate;
use TheArdent\Drivers\Viber\Extensions\LocationTemplate;
use TheArdent\Drivers\Viber\Extensions\PictureTemplate;
use TheArdent\Drivers\Viber\Extensions\VideoTemplate;

class ViberDriver extends HttpDriver implements VerifiesService
{
	const DRIVER_NAME = 'Viber';

	const API_ENDPOINT = 'https://chatapi.viber.com/pa/';

	/** @var  DriverEventInterface */
	protected $driverEvent;

	/** @var string|null */
	private $botId;

	/** @var  array|object */
	private $bot;

	/**
	 * @param Request $request
	 */
	public function buildPayload(Request $request)
	{
		$this->payload = new ParameterBag(json_decode($request->getContent(), true));
		$this->event = Collection::make($this->payload->get('event'));
		$this->config = Collection::make($this->config->get('viber'));
	}

	/**
	 * @return array
	 */
	protected function getHeaders()
	{
		return [
			'Accept:application/json',
			'Content-Type:application/json',
			'X-Viber-Auth-Token: '.$this->config->get('token'),
		];
	}

	/**
	 * Determine if the request is for this driver.
	 *
	 * @return bool
	 */
	public function matchesRequest()
	{
		return $this->payload->get('event') && $this->payload->get('message_token');
	}

	/**
	 * @return bool|DriverEventInterface
	 */
	public function hasMatchingEvent()
	{
		$event = $this->getEventFromEventData($this->event->toArray());
		if ($event) {
			$this->driverEvent = $event;
			return $this->driverEvent;
		}
		return false;
	}

	/**
	 * @param array $eventData
	 *
	 * @return bool|DriverEventInterface
	 */
	public function getEventFromEventData(array $eventData)
	{
		switch ($this->event->first()) {
			case 'delivered':
				return new MessageDelivered($eventData);
				break;
			case 'failed':
				return new MessageFailed($eventData);
				break;
			case 'subscribed':
				return new UserSubscribed($eventData);
				break;
			case 'conversation_started':
				return new MessageStarted($eventData);
				break;
			case 'unsubscribed':
				return new UserUnsubscribed($eventData);
				break;
			case 'seen':
				return new MessageSeen($eventData);
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * @param  \BotMan\BotMan\Messages\Incoming\IncomingMessage $message
	 * @return Answer
	 */
	public function getConversationAnswer(IncomingMessage $message)
	{
		$interactive = $this->payload->get('silent', false);
		if (is_string($interactive)) {
		    $interactive = ($interactive !== 'false') && ($interactive !== '0');
		} else {
		    $interactive = (bool)$interactive;
		}

		$text = $message->getText();

		return Answer::create($message->getText())
		    ->setMessage($message)
		    ->setValue($text)
		    ->setInteractiveReply($interactive);
	}

	/**
	 * Retrieve the chat message.
	 *
	 * @return array
	 */
	public function getMessages()
	{
		$user = $this->payload->get('sender') ? $this->payload->get('sender')['id'] : $this->payload->get('user')['id'];
		$message = new IncomingMessage($this->payload->get('message')['text'], $user, $this->getBotId(), $this->payload);

		return [$message];
	}

	/**
	 * @param string|Question|IncomingMessage $message
	 * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $matchingMessage
	 * @param array $additionalParameters
	 * @return array
	 */
	public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
	{
		$parameters = array_merge_recursive([
			                                    'receiver' => $matchingMessage->getSender(),
		                                    ], $additionalParameters);

		/*
		 * If we send a Question with buttons, ignore
		 * the text and append the question.
		 */
		if ($message instanceof \JsonSerializable) {
			$parameters = array_merge($message->jsonSerialize(),$parameters);
		} else {
			$parameters['text'] = $message->getText();
			$parameters['type'] = 'text';
		}

		return $parameters;
	}

	/**
	 * @param mixed $payload
	 * @return Response
	 */
	public function sendPayload($payload)
	{
		return $this->http->post(self::API_ENDPOINT.'send_message', [], $payload, $this->getHeaders(), true);
	}

	/**
	 * @return bool
	 */
	public function isConfigured()
	{
		return ! is_null($this->config->get('token'));
	}

	/**
	 * Retrieve User information.
	 * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $matchingMessage
	 * @return User
	 */
	public function getUser(IncomingMessage $matchingMessage)
	{
		$personId = $matchingMessage->getSender();
		$response = $this->http->post(self::API_ENDPOINT.'get_user_details',[],['id' => $personId], $this->getHeaders());
		$userInfo = Collection::make(json_decode($response->getContent(), true)['user']);

		return new User($userInfo->get('id'), $userInfo->get('name'), null, $userInfo->get('name'), $userInfo->toArray());
	}


	/**
	 * Low-level method to perform driver specific API requests.
	 *
	 * @param string          $endpoint
	 * @param array           $parameters
	 * @param IncomingMessage $matchingMessage
	 *
	 * @return Response
	 */
	public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
	{
		return $this->http->post(self::API_ENDPOINT.$endpoint, [], $parameters, $this->getHeaders());
	}

	/**
	 * Returns the chatbot ID.
	 * @return string
	 */
	private function getBotId()
	{
		if (is_null($this->bot)) {
			$response = $this->http->post(self::API_ENDPOINT.'get_account_info', [], [], $this->getHeaders());
			$bot = json_decode($response->getContent());
			$this->bot = $bot;
			$this->botId = $bot->id;
		}

		return $this->botId;
	}

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function verifyRequest(Request $request)
	{
		return hash_equals($request->get('sig'), hash_hmac('sha256',$request->getContent(),$this->config->get('viber')));
	}
}
