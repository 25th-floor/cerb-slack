<?php
if(class_exists('Extension_PluginSetup')):
class TwentyfifthfloorSlack_Setup extends Extension_PluginSetup {
	const POINT = '25th-floorslack.setup';

	function render() {
		$tpl = DevblocksPlatform::getTemplateService();

		$params = array(
			'webhook_url' => DevblocksPlatform::getPluginSetting('25th-floor.slack','webhook_url',''),
		);
		$tpl->assign('params', $params);

		$tpl->display('devblocks:25th-floor.slack::setup.tpl');
	}

	function save(&$errors) {
		try {
			@$webhook_url = DevblocksPlatform::importGPC($_REQUEST['webhook_url'],'string','');

			if(empty($webhook_url))
				throw new Exception("The Webhook URL is required.");

			$slack = TwentyfifthfloorSlack_API::getInstance();

			$response = $slack->sendMessageToWebhookUrl(
				null,
				null,
				'(This is an automated test of Cerb integration)',
				true,
				'green'
			);

			if(empty($response))
				throw new Exception("There was a problem connecting!  Please check your Webhook URL.");

			if(is_array($response) && isset($response['error']))
				throw new Exception($response['error']['message']);

			DevblocksPlatform::setPluginSetting('25th-floor.slack','webhook_url',$webhook_url);

			return true;

		} catch (Exception $e) {
			$errors[] = $e->getMessage();
			return false;
		}
	}
};
endif;

class TwentyfifthfloorSlack_API {
	static $_instance = null;
	private $_webhook_url = null;

	private function __construct() {
		$this->_webhook_url = DevblocksPlatform::getPluginSetting('25th-floor.slack','webhook_url','');
	}

	/**
	 * @return TwentyfifthfloorSlack_API
	 */
	static public function getInstance() {
		if(null == self::$_instance) {
			self::$_instance = new TwentyfifthfloorSlack_API();
		}

		return self::$_instance;
	}

	/**
	 *
	 * @param string $path
	 * @param string $post
	 * @return HTTPResponse
	 */
	private function _request($query) {
		$url = $this->_webhook_url;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		error_log($url);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public function sendMessageToWebhookUrl($room, $from, $message, $is_markdown=true, $color=null) {
		if(strlen($message) > 10000)
			$message = substr($message, 0, 10000);

		$query = array(
			'attachments' =>array(
				array(
					'fallback' => $message,
					'color'    => '#E3E4E6',
					'text'     => $message,
				)
			),
			'icon_url' => 'http://alt2cdn.blob.core.windows.net/dist/icons/cerberus_18033.png',
		);

		if($from != null)
			$query['username'] = $from;

		if($room != null)
			$query['channel'] = $room;

		if($is_markdown)
			$query['attachments'][0]['mrkdwn_in'] = array('text');

		$colors = array(
			'yellow' => '#DE9E31',
			'red'    => '#D50200',
			'green'  => '#2FA44F',
			'gray'   => '#E3E4E6',
			'purple' => '#800080',
		);

		if(!empty($color) && array_key_exists($color, $colors))
			$query['attachments'][0]['color'] = $colors[$color];

		$response = $this->_request(json_encode($query, JSON_UNESCAPED_UNICODE));
		$response = json_encode($response, true);
		error_log(var_export($response, true));

		return $response;
	}
};

if(class_exists('Extension_DevblocksEventAction')):
class TwentyfifthfloorSlack_EventActionPost extends Extension_DevblocksEventAction {
	function render(Extension_DevblocksEvent $event, Model_TriggerEvent $trigger, $params=array(), $seq=null) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('params', $params);

		if(!is_null($seq))
			$tpl->assign('namePrefix', 'action'.$seq);

		$tpl->display('devblocks:25th-floor.slack::action_post_slack.tpl');
	}

	function simulate($token, Model_TriggerEvent $trigger, $params, DevblocksDictionaryDelegate $dict) {
		$out = '';

		$tpl_builder = DevblocksPlatform::getTemplateBuilder();

		@$room = $tpl_builder->build($params['room'], $dict);
		@$from = $tpl_builder->build($params['from'], $dict);
		@$content = $tpl_builder->build($params['content'], $dict);
		@$run_in_simulator = $params['run_in_simulator'];
		@$is_markdown = $params['is_markdown'];

		if(empty($content))
			return "[ERROR] No content is defined.";

		$out .= sprintf(">>> Posting to Slack in channel: %s\n\n%s: %s\n",
			$room,
			$from,
			$content,
			$is_markdown
		);

		// Run in simulator?
		if($run_in_simulator) {
			$this->run($token, $trigger, $params, $dict);
		}

		return $out;
	}

	function run($token, Model_TriggerEvent $trigger, $params, DevblocksDictionaryDelegate $dict) {
		$slack = TwentyfifthfloorSlack_API::getInstance();

		// Translate message tokens
		$tpl_builder = DevblocksPlatform::getTemplateBuilder();

		@$room = $tpl_builder->build($params['room'], $dict);
		@$from = $tpl_builder->build($params['from'], $dict);
		@$content = $tpl_builder->build($params['content'], $dict);

		if(empty($content))
			return false;

		@$is_markdown = $params['is_markdown'];
		@$color = $params['color'];

		$messages = array($content);

		if(is_array($messages)) {
			foreach($messages as $message) {
				$slack->sendMessageToWebhookUrl(
					$room,
					$from,
					$message,
					$is_markdown,
					$color
				);
			}
		}

		return true;
	}
};
endif;
