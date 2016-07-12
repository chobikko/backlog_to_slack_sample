<?php
class checkTicket {
    public $msgs= array();
    public $slackChannel = NULL;
    public $slackToken = NULL;
    public $slackUsername = NULL;
    public $backlogApikey = NULL;
    public $backlogProjectId = NULL;
    public $backlogTeamName = NULL;

   function __construct($channel, $token, $username, $apikey, $pid, $team) {
        $this->slackChannel = $channel;
        $this->slackToken = $token;
        $this->slackUsername = $username;
        $this->backlogApikey = $apikey;
        $this->backlogProjectId = $pid;
        $this->backlogTeamName = $team;
   }

    function check() {
        $this->getList();
        foreach ($this->msgs as $msg) {
            $this->post($msg);
        }
    }

    function post($msg) {
        $url = "https://slack.com/api/chat.postMessage";
        $POST_DATA = array(
                'token' => $this->slackToken,
                'channel' => $this->slackChannel,
                'text' => $msg,
                'username' => $this->slackUsername,
                );
        $curl=curl_init($url);
        curl_setopt($curl,CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($POST_DATA));
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE);
        curl_exec($curl);
    }

    function getList() {
        $offset = 0;
        do {
            $data = $this->getListProc($offset);
            $offset = count($data) - 1;
        } while(count($data) >= 100);
    }

    function getListProc($offset=0) {
        // 課題一覧の取得
        // http://developer.nulab-inc.com/ja/docs/backlog/api/2/get-issues
        // 詳しくは上記参考
        // ここでは、ステータスが１(未対応)2(処理中)3(処理済)を抽出
        $url = "https://{$this->backlogTeamName}.backlog.jp/api/v2/issues?apiKey={$this->backlogApikey}" . 
            "&projectId[]={$this->backlogProjectId}&statusId[]=1&statusId[]=2&statusId[]=3&sort=created&order=asc&count=100" .
            "&offset=$offset";
        $data = @file_get_contents($url);
        if (empty($data)) {
            $tihs->error();
            return;
        }
        $data = json_decode($data, true);
        foreach ($data as $rec) {
            $this->checkTicket($rec);
        }
        return $data;
    }

    function checkTicket($ticket) {
        if (empty($ticket['assignee']['id'])) {
            $this->msgs[] = $this->makeMsg($ticket, "担当者を設定してください。{$ticket['createdUser']['name']}さん");
        }
    }

    function makeMsg($ticket, $msg) {
        $ret = "━━━━━━━━━━━━━━━━━\n";
		$ret .= "https://{$this->backlogTeamName}.backlog.jp/view/" . $ticket['issueKey'] . "\n";
        $ret .= $msg;
        return $ret;
    }
}
