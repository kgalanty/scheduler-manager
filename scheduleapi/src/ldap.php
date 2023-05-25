<?php
require_once('../../init.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use WHMCS\Database\Capsule as DB;

class LDAP
{
    public $ldap, $result;
    const HOST = 'ldap://ipa-3.gate.tmdhosting.com';
    const DN = 'uid=httpbind,cn=sysaccounts,cn=etc,dc=gate,dc=tmdhosting,dc=com';
    const PASS = 'epcYbQ3ug3CJxxQ9KMyfUjTZ';
    const SEARCH_BASE = 'cn=accounts,dc=gate,dc=tmdhosting,dc=com';

    public function __construct()
    {
        $this->ldap = ldap_connect(self::HOST);
        if ($this->ldap) {
            ldap_bind($this->ldap, self::DN, self::PASS);
        }
        return $this;
    }
    public function searchFor(?array $filters = ['uid' => '*']): LDAP
    {
        if (!$this->result) {
            $FilterString = '';
            foreach ($filters as $key => $filter) {
                $FilterString .= $key . '=' . $filter;
                break;
            }
            $search = ldap_search($this->ldap, self::SEARCH_BASE, $FilterString);
            $this->result = ['count' => ldap_count_entries($this->ldap, $search), 'data' => ldap_get_entries($this->ldap, $search)];
        }
        return $this;
    }
    public function filter(string $key, string $value)
    {
        if ($this->result && $this->result['data']) {
            foreach ($this->result['data'] as $item) {
                if ($item[$key]) {
                    for ($i = 0; $i < $item[$key]['count']; $i++) {
                        if ($item[$key][$i] == $value) {
                            $ar = [];
                            if ($item['displayname'][0]) {
                                $ar['ldap_username'] = ucwords($item['displayname'][0]);
                            }
                            if ($item['telephonenumber'][0]) {
                                $ar['ldap_phone'] = $item['telephonenumber'][0];
                            }
                            //return ['display_name' => $item['displayname'][0], 'username' => $value, 'phone' => $item['telephonenumber'][0]];
                            return $ar;
                        }
                    }
                }
            }
        }
    }
    public function __destruct()
    {
        ldap_close($this->ldap);
    }
}
class AgentsLDAPCron
{
    public $ldap;
    public function __construct(LDAP $instance)
    {
        $this->ldap = $instance->searchFor();
        return $this;
    }
    public function queryAgents()
    {
        $agents = DB::table('tbladmins as a')->where('a.disabled', '0')
            ->leftJoin('schedule_slackusers as s', 's.agent_id', '=', 'a.id')
            ->get(['a.id', 'a.username', 's.*']);
        return $agents;
    }
    public function handleAgent($agentRow)
    {

        return $this->ldap->filter('uid', $agentRow->username);
    }
}

$agentsHandler = (new AgentsLDAPCron(new LDAP()));
$agentsList = $agentsHandler->queryAgents();
echo('<pre>'); var_dump($agentsList);die;
$agentsReady = [];
foreach ($agentsList as $agent) {
    $a = $agentsHandler->handleAgent($agent);
   
    if ($a) {
        $agentsReady[] = array_merge(['id' => $agent->id, 'adminid' => $agent->agent_id], $a);
    }
}
foreach ($agentsReady as $ar) {

    DB::table('schedule_agents_details')->where('id', $ar['id'])->update(array_diff_key($ar, ['id' => '', 'adminid' => '']));
}
exit;

// $ldap = new LDAP();
// $entries = $ldap->searchFor();

// foreach ($agents as $agent) {
//     AgentsLDAPCron::handleAgent($agent);
// }
echo ('<pre>');
var_dump($agents);
die;
