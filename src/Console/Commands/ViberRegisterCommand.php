<?php

namespace TheArdent\Drivers\Viber\Commands;

use Illuminate\Console\Command;

class ViberRegisterCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'botman:viber:register';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set webhook for Viber API';

	/**
	 * Create a new command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$callbackUrl = $this->ask('What is the target url for the Viber bot?');
		$api = 'https://chatapi.viber.com/pa/set_webhook';

		$postdata = json_encode(
			[
				"url"         => $callbackUrl,
				"event_types" => [
					"delivered",
					"seen",
					"failed",
					"subscribed",
					"unsubscribed",
					"conversation_started"
				]
			]
		);

		$opts = array(
			'http' =>
				array(
					'method'  => 'POST',
					'header'  => [
						'Content-type: application/x-www-form-urlencoded;',
						'X-Viber-Auth-Token: ' . config('botman.viber.token'),
					],
					'content' => $postdata
				)
		);

		$context = stream_context_create($opts);

		$result = json_decode(file_get_contents($api, false, $context));


		$this->info($result->status);

		if (isset($result->status) && $result->status === 0) {
			$this->info('Your bot is now set up with Viber\'s webhook!');
		}
		else {
			$this->error(
				'Something went wrong!Status: ' . $result['status'] . '. Message:' . $result['status_message']
			);
		}
	}
}