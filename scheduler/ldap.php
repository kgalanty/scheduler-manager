<?php
require_once('../init.php');
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
        $agents = DB::table('tbladmins as a')->where('a.disabled', '0')->where('a.id', 98)
            ->leftJoin('schedule_slackusers as s', 's.agent_id', '=', 'a.id')
            ->get(['a.id', 'a.username', 'a.email', 's.id as aid', 's.phone', 'realnamenormalized']);
        return $agents;
    }
    public function handleAgent($agentRow)
    {

        return $this->ldap->filter('uid', 'stoyan.momchilov');
    }
}

$agentsHandler = (new AgentsLDAPCron(new LDAP()));
$agentsList = $agentsHandler->queryAgents();


$agentsReady = [];
foreach ($agentsList as $agent) {
    $a = $agentsHandler->handleAgent($agent);


    if ($a) {
        
        if(!$a['ldap_username'] && !$a['ldap_phone']) continue;

        $agentReady = ['id' => $agent->aid, 'agent_id' => $agent->id, 'email' => $agent->email];

        if($a['ldap_username'])
        {
            $agentReady['realnamenormalized'] = $a['ldap_username'];
        }
        if($a['ldap_phone'])
        {
            $agentReady['phone'] = $a['ldap_phone'];
        }
        $agentsRdy[] = $agentReady;
    }
}
// }
echo ('<pre>');
var_dump($agentsRdy);

foreach ($agentsRdy as $ar) {
        if(!$ar['id'])
        {
            DB::table('schedule_slackusers')->insert($ar);
            continue;
        }
        else
        {
            DB::table('schedule_slackusers')->where('id', $ar['id'])->update(array_diff_key($ar, ['id' => '', 'agent_id' => '']));
        }
        
    //DB::table('schedule_agents_details')->where('id', $ar['id'])->update(array_diff_key($ar, ['id' => '', 'adminid' => '']));
}

// $ldap = new LDAP();
// $entries = $ldap->searchFor();

// foreach ($agents as $agent) {
//     AgentsLDAPCron::handleAgent($agent);
