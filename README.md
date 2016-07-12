# backlog_to_slack_sample
Backlogから課題一覧を取得してSlackに吐き出すSample

# 使い方
<pre>
require_once __DIR__ . '/checkTicket.php';
$obj = new checkTicket('#feed_name', 'slack_token', 'slack_username', 'backlog_apikey', 'backlog_project_id', 'backlog_team_name');
$obj->check();
</pre>
