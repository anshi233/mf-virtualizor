<?php

use app\common\model\HostModel;
use app\common\model\ServerModel;

//require_once('sdk/admin.php');
#function mfvirtualizor_idcsmartauthorize(){}



// 配置数据
function mfvirtualizor_MetaData(){
    return ['DisplayName'=>'mf-virtualizor', 'APIVersion'=>'1.1', 'HelpDoc'=>'https://www.idcsmart.com/wiki_list/339.html#3.3','version'=>'1.0.0'];
}

function mfvirtualizor_ConfigOptions(){
    return [
        [
            'type'=>'text',
            'name'=>'virt type',
            'description'=>'openvz,kvm,lxc,proxo,proxk,proxl',
            'key'=>'mf_virt_type'
        ],
        [
            'type'=>'yesno',
            'name'=>'use server group',
            'description'=>'宿主ID是否是服务器组ID',
            'default'=>'0',   //by default use server id not server group id
            'key'=>'mf_use_server_group',
        ],
        [
            'type'=>'text',
            'name'=>'server (group) id',
            'description'=>'宿主ID或服务器组ID',
            'key'=>'mf_server_id',
        ],
        [
            'type'=>'text',
            'name'=>'Plan ID',
            'description'=>'Plan ID',
            'key'=>'mf_plan_id',
        ],
        [
            'type'=>'text',
            'name'=>'可用 OS',
            'description'=>'os name seperate by comma',
            'key'=>'mf_os_list',
        ],
        [
            'type'=>'text',
            'name'=>'默认OS',
            'description'=>'如果设置可选配置则优先可选配置',
            'key'=>'mf_os',
        ],
        [
            'type'=>'text',
            'name'=>'Domain Suffix',
            'description'=>'Format (xxx.yyy e.g. catserver.ca)',
            'key'=>'mf_domain_suffix',
        ]

    ];
}

// 连接测试
function mfvirtualizor_TestLink($params){

    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $api_path = 'index.php?act=serverinfo';
    $ret = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $api_path);

    $result['status'] = 200;
    if (!$ret){
        $result['data']['server_status'] = 0;
        $result['data']['msg'] = '[ERROR] Failed to connect master server. Please check your configuration.';
    } else {
        $result['data']['server_status'] = 1;
    }

    return $result;

}

// 图表
// 暂时不支持图表
function mfvirtualizor_Chart(){
    return null;
}


// 标准输出
function mfvirtualizor_ClientArea($params){
    $panel = [
        'control_panel'=>[
            'name'=>'控制面板'
        ]
    ];
    return $panel;
}

// 输出内容
function mfvirtualizor_ClientAreaOutput($params, $key){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '';
    }

    if($key == 'control_panel'){
        return [
            'template'=>'templates/end_user_panel.html',
            'vars'=>[
                //'list'=>$res['data']
            ]
        ];
    }
}

// 可以执行自定义方法
function mfvirtualizor_AllowFunction(){
    return [
        'client'=>['loginEndUserPanel']
    ];
}


// 前台自定义按钮

// Virtualizor EndUser Panel SSO 客户端面板一键登录
function mfvirtualizor_loginEndUserPanel($params){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return ['status'=>'error', 'msg'=>'无法找到虚拟机ID'];
    }

    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    // Make the Login system
    $_GET['SET_REMOTE_IP'] = $_SERVER['REMOTE_ADDR'];

    $port = ($params['secure'] == 0) ? 80 : 4083;

    // If $tmp_hostname is still empty that means $var does not have server data filled.
    // So now we have to find the server details byfrom DB.

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid,
        '?act=sso&SET_REMOTE_IP='.$_SERVER['REMOTE_ADDR'].'&goto_cp='.
        rawurlencode(mfvirtualizor_virtualizor_get_current_url()).'&svs='.$vserverid);

    $redirect_url = '';
    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '获取控制面板登录信息失败'];
    }else{
        //if(empty($virt_resp['done'])){
        //    $create_error = implode('<br>', $virt_resp['error']);
        //    return ['status'=>'error', 'msg'=>$create_error ?: '获取控制面板登录信息失败'];
        //}else{
        $redirect_url = 'https://'.$api_ip.':'.$port.'/'.$virt_resp['token_key'].
            '/?as='.$virt_resp['sid'].'&goto_cp='.
            rawurlencode(mfvirtualizor_virtualizor_get_current_url()).'&svs='.$vserverid;
        //}

    }
    $return_info = ['status'=>'success', 'msg'=>'获取控制面板登录信息成功', 'url'=>$redirect_url];

    return $return_info;
}

// 开通

//Mapping to blesta virtualizor plugin -> addService()
function mfvirtualizor_CreateAccount($params){
    // 获取自定义字段
    // vserverid === vid in virtualizor
    $vserverid = mfvirtualizor_GetServerid($params);
    if(!empty($vserverid)){
        return '已开通,不能重复开通';
    }
    //Although we can set password value directly. For security concern, always use rand passwd
    if(empty($params['password'])){
        $sys_pwd = rand_str(12);
    }else{
        $sys_pwd = $params['password'];
    }
    //MF does not passing domain into module. We need to generate our random hostname
    $sys_hostname = strtolower(rand_str(6)).'.'.$params['configoptions']['mf_domain_suffix'];


    //virtualizor do this for us
    //$vnc_pwd = rand_str(8);

    // Check virtualization type e.g. kvm, lxc .etc
    // TO-DO: continue

    //From Server ID, get server API info


    // Get the info from the server
    // Get API Key and Pass from password param
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $api_path = 'index.php?act=addvs';

    $server_group = '';
    $slave_server = '';

    //For first version will assume server_group will always set
    //Server ID is one of the server id or server group id
    if(($params['configoptions']['mf_use_server_group'] == 0) || empty($params['configoptions']['server_group_id'])){
        $post['slave_server'] = $params['configoptions']['mf_server_id'];
    }else{
        $post['server_group'] = $params['configoptions']['mf_server_id'];
    }

    $post['plid'] = $params['configoptions']['mf_plan_id'];
    $virttype = $params['configoptions']['mf_virt_type'];


    //Check is OS list is set
    if(empty($params['configoptions']['mf_os_list'])){
        //log error
        return ['status'=>'error', 'msg'=>'OS列表不应为空，请检查OS列表配置'];
    }

    $OSlist = explode(",", $params['configoptions']['mf_os_list']);
    if(isset($params['configoptions']['os'])){
        //Check if OS is in the list
        if(in_array($params['configoptions']['os'],$OSlist)){
            $post['os_name'] = $params['configoptions']['os'];
        }else{
            return ['status'=>'error', 'msg'=>'OS: '.$params['configoptions']['os'].' 不在OS操作系统列表中，请检查OS和OS列表配置'];
        }

    }else{
        if(in_array($params['configoptions']['mf_os'],$OSlist)){
            $post['os_name'] = $params['configoptions']['mf_os'];
        }else{
            $post['os_name'] = $OSlist[0];
        }
    }
    $OS = $post['os_name'];

    //configuration options
    // Copy params to temp array
    $additional_config = array();
    $additional_config = $params['configoptions'];
    //Delete the key that is used by this module
    unset($additional_config['mf_virt_type']);
    unset($additional_config['mf_use_server_group']);
    unset($additional_config['mf_server_id']);
    unset($additional_config['mf_plan_id']);
    unset($additional_config['mf_os_list']);
    unset($additional_config['mf_os']);
    unset($additional_config['mf_domain_suffix']);
    //OS has been processed
    unset($additional_config['os']);

    //copy the rest of the config options to post
    foreach($additional_config as $k => $v){
        if(!isset($post[$k])){
            $post[$k] = $v;
        }
    }

    //if hostname is not set by additional config, set it with random hostname
    if(!isset($post['hostname'])){
        $post['hostname'] = $sys_hostname;
    }

    //Validate the hostname
    if(empty($post['hostname'])){
        return ['status'=>'error', 'msg'=>'Hostname不能为空'];
    }

    $post['rootpass'] = $sys_pwd;

    // Pass the user details
    $post['user_email'] = $params['user_info']['email'];
    //TO-DO: Might need to force assign random password here
    $post['user_pass'] = $sys_pwd;

    //ZJMF does not record First Name and Last Name. Use username instead for work around
    $post['fname'] = $params['user_info']['username'];
    $post['lname'] = $params['user_info']['username'];
    $post['node_select'] = 1;
    $post['addvps'] = 1;
    $cookies = array();

    $masked_array = $post;


    //Log the creation of service
    //TO-DO: Change to ZJMF log api
    //$this->log($row->meta->host . "|create service", serialize($masked_array), "input", true);
    // print_r($vars);
    // Are there any configurable options
    // TO-DO: support it for ZJMF common module
    //if(!empty($vars['configoptions'])){
    //    foreach($vars['configoptions'] as $k => $v){
    //        if(!isset($post[$k])){
    //            $post[$k] = $v;
    //        }
    //    }
    //}

    // Virtualizor API call
    $ret = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $api_path.'&virt='.$virttype, array(), $post, $cookies);



    //TO-DO: using ZJMF log
    //this->log($row->meta->host."| ret from addvs : ", serialize($ret), "output", true);

    if(empty($ret['done'])){
        $create_error = implode('<br>', $ret['error']);
        return ['status'=>'error', 'msg'=>$create_error ?: '开通失败'];
    }

    if(!empty($ret['newvs']['ips'])){
        $_ips = $ret['newvs']['ips'];
    }

    if(!empty($ret['newvs']['ipv6'])){
        $_ips6 = $ret['newvs']['ipv6'];
    }

    if(!empty($ret['newvs']['ipv6_subnet'])){
        $_ips6_subnet = $ret['newvs']['ipv6_subnet'];
    }

    $tmp_ips = empty($_ips) ? array() : $_ips;

    if(!empty($_ips6_subnet)){
        foreach($_ips6_subnet as $k => $v){
            $tmp_ips[] = $v;
        }
    }

    if(!empty($_ips6)){
        foreach($_ips6 as $k => $v){
            $tmp_ips[] = $v;
        }
    }

    if(!empty($tmp_ips[0])){

        $primary_ip = $tmp_ips[0];

        // Extra IPs
        unset($tmp_ips[0]);
    }
    // 存入IP
    $mainip = '';
    $ip = [];
    $mainip = @$primary_ip;
    $ip = $tmp_ips;
    $username = 'root';

    $HostModel = new HostModel();
    $HostModel->where('id',$params['hostid'])
        ->update([
            'name' => (isset($ret['newvs']['vpsid']) ? $ret['newvs']['vpsid'] : null),
            'status' => 'Active'
        ]);

    $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
    $update['dedicatedip'] = $mainip;
    $update['assignedips'] = implode(',', $ip);
    $update['username'] = $username;
    $update['password'] = password_encrypt($sys_pwd);
    $update['os'] = $OS;
    //TO-DO: Use API to get plan's data limit

    $update['vserverid'] =(isset($ret['newvs']['vpsid']) ? $ret['newvs']['vpsid'] : null); // 虚拟机ID
    $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update($update);

    $hostIpData = [
        'host_id' => $params['hostid'],
        'dedicate_ip' => $mainip,
        'assign_ip' => implode(',', $ip),
        'write_log' => true
    ];
    $HostIpModel = new \app\common\model\HostIpModel();
    $HostIpModel->hostIpSave($hostIpData);

    return 'ok';

}

// 暂停
// In Virtualizor Blesta plugin, it is suspendService()
function mfvirtualizor_SuspendAccount($params){

    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return ['status'=>'error', 'msg'=>'[ERROR] Can not find vm id (vid)'] ;
    }

    $api_path = 'index.php?act=vs&suspend='.$vserverid;

    $virt_resp = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '暂停失败'];
    }else{
        if(empty($virt_resp['done'])){
            $create_error = implode('<br>', $virt_resp['error']);
            return ['status'=>'error', 'msg'=>$create_error ?: '暂停失败'];
        }else{
            return ['status'=>'success', 'msg'=>serialize($virt_resp['done_msg'])];
        }

    }
}

// 解除暂停
function mfvirtualizor_UnsuspendAccount($params){
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return ['status'=>'error', 'msg'=>'无法找到虚拟机ID'];
    }

    $api_path = 'index.php?act=vs&unsuspend='.$vserverid;

    $virt_resp = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '解除暂停失败'];
    }else{
        if(empty($virt_resp['done'])){
            $create_error = implode('<br>', $virt_resp['error']);
            return ['status'=>'error', 'msg'=>$create_error ?: '解除暂停失败'];
        }else{
            return ['status'=>'success', 'msg'=>serialize($virt_resp['done_msg'])];
        }
    }
}

// 删除
function mfvirtualizor_TerminateAccount($params){

    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return ['status'=>'error', 'msg'=>'无法找到虚拟机ID'];
    }

    $api_path = 'index.php?act=vs&delete='.$vserverid;

    $virt_resp = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '无法删除虚拟机'];
    }else{
        //if(empty($virt_resp['done'])){
        //    $create_error = implode('<br>', $virt_resp['error']);
        //    return ['status'=>'error', 'msg'=>$create_error ?: '无法删除虚拟机'];
        //}else{
        return ['status'=>'success', 'msg'=>'删除成功'];
        //}
    }
}

// 开机
function mfvirtualizor_On($params){
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }

    $api_path = 'index.php?act=start&do=1';

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '无法启动虚拟机'];
    }else{
        return ['status'=>'success', 'msg'=>serialize($virt_resp)];
    }
}

// 关机
function mfvirtualizor_Off($params){
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }

    $api_path = 'index.php?act=stop&do=1';

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '无法启动虚拟机'];
    }else{
        return ['status'=>'success', 'msg'=>serialize($virt_resp)];
    }
}

// 重启
function mfvirtualizor_Reboot($params){
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }

    $api_path = 'index.php?act=restart&do=1';

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '无法启动虚拟机'];
    }else{
        return ['status'=>'success', 'msg'=>serialize($virt_resp)];
    }
}

// 硬关机
function mfvirtualizor_HardOff($params){
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];

    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }

    $api_path = 'index.php?act=poweroff&do=1';

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $api_path);

    if(empty($virt_resp)){
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '无法启动虚拟机'];
    }else{
        return ['status'=>'success', 'msg'=>serialize($virt_resp)];
    }
}

// 硬重启
function mfvirtualizor_HardReboot($params){
    //Virtualizor reboot is also the hard reboot
    return mfvirtualizor_Reboot($params);
}

// Vnc  (not done)
function mfvirtualizor_Vnc_Not_Done($params){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }
    $novnc_type = true;


    // 先获取vnc密码
    // For the Call
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    //$path = 'index.php?act=vnc&launch=1';
    //$response = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $path);

    if(!$novnc_type)
    {
        //Just get VNC information
        $path = 'index.php?act=vnc&launch=1';
        $response = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $path);
        if(empty($response)){
            $result['status'] = 'error';
            $result['msg'] = 'VNC获取失败';

        }else{
            $result['status'] = 'success';
            $result['msg'] = 'vnc获取成功';
            $result['url'] = $response['info']['ip'].':'.$response['info']['port'].'&password='.$response['info']['password'];
        }
    }else{
        //Use noVNC Client
        $path = 'index.php?act=vnc&novnc=1';
        $response = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $path);
        if(empty($response)){
            $result['status'] = 'error';
            $result['msg'] = 'VNC获取失败';
            return $result;
        }
        $novnc_password = $response['info']['password'];

        //CatServer modification: fix vnc cluster hostname issue
        //Get Server Hostname
        //$host = $module_row->meta->host;
        //make another api call to get the hostname of the host server that provide vnc
        $data_host = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $path.'act=servers&serverip='.$response['info']['ip']);

        foreach ($data_host['servs'] as $serv) {
            if (!empty($serv['server_name'])) {
                $host = $serv['server_name'];
                break; // Exit the loop once the first non-empty server name is found
            }
        }
        $proto = 'http';
        $port = 4081;
        $virt_port = 4082;
        $websockify = 'websockify';
        $novnc_serverip = empty($host) ? $api_ip : $host;
        $vpsid = $vserverid;

        //In case if HTTPS is enabled
        if(!empty($_SERVER['HTTPS'])){
            $proto = 'https';
            $port = 4083;
            $virt_port = 4083;
            $websockify = 'novnc/';
            $novnc_serverip = empty($host) ? $api_ip : $host;
        }


        if($response['info']['virt'] == 'xcp'){
            $vpsid .= '-'.$response['info']['password'];
        }



        $result['status'] = 'success';
        $result['msg'] = 'vnc获取成功';
        //Get URL
        // Use build in noVNC client

    }
    //}
    //}






    //$res = mfvirtualizor_Curl($url, $post_data, 30, 'GET');
    return $result;
}

// 重装系统 (not done)
function mfvirtualizor_Reinstall($params){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }

    if(empty($params['reinstall_os'])){
        return ['status'=>'error', 'msg'=>'操作系统名不应为空'];
    }


    //Check is reinstall os name in the os list
    $os_list = explode(",", $params['configoptions']['mf_os_list']);
    if(!in_array($params['reinstall_os'], $os_list)){
        return ['status'=>'error', 'msg'=>'操作系统 '.$params['reinstall_os'].' 不在套餐OS列表中。请联系管理员。'];
    }

    // For the Call
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $path = 'index.php?act=rebuild';

    $post_data['osid'] = $params['reinstall_os'];
    $post_data['vpsid'] = $vserverid;
    $post_data['reos'] = 0;
    //generate a random password
    $post_data['password'] = rand_str(12);
    //conf is confirm???
    $post_data['conf'] = $post_data['password'];

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $path, $post_data);

    if(isset($virt_resp['done'])){
        if(stripos(strtolower($params['reinstall_os_name']), 'Windows') !== false){
            $username = 'administrator';
        }else{
            $username = 'root';
        }
        $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update([
            'username' => $username,
            'os' => $params['reinstall_os_name'],
            'password' => password_encrypt($post_data['password'])
        ]);

        return ['status'=>'success', 'msg'=>'重装成功'];
    }else{
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '重装失败'];
    }
}

// 破解密码 (reset password)
function mfvirtualizor_CrackPassword($params, $new_pass){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }

    // For the Call
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $path = 'index.php?act=changepassword';


    //generate a random password
    if(!empty($new_pass)){
        $post_data['newpass'] = $new_pass;
    }else{
        $post_data['newpass'] = rand_str(12);
    }

    //conf is confirm???
    $post_data['conf'] = $post_data['newpass'];
    $post_data['changepass'] = 'Change Password';

    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $path, $post_data);

    if(isset($virt_resp['done'])){
        $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update([
            'password' => password_encrypt($post_data['newpass'])
        ]);

        return ['status'=>'success', 'msg'=>'重设密码成功'];
    }else{
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '重设密码失败'];
    }
}

// 同步
function mfvirtualizor_Sync($params){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }


    // 先获取vnc密码
    // For the Call
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $api_path = 'index.php?act=vpsmanage&';
    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $api_path);





    if(!empty($virt_resp)){
        if(!empty($ret['newvs']['ips'])){
            $_ips = $ret['newvs']['ips'];
        }

        if(!empty($ret['newvs']['ipv6'])){
            $_ips6 = $ret['newvs']['ipv6'];
        }

        if(!empty($ret['newvs']['ipv6_subnet'])){
            $_ips6_subnet = $ret['newvs']['ipv6_subnet'];
        }

        $tmp_ips = empty($_ips) ? array() : $_ips;

        if(!empty($_ips6_subnet)){
            foreach($_ips6_subnet as $k => $v){
                $tmp_ips[] = $v;
            }
        }

        if(!empty($_ips6)){
            foreach($_ips6 as $k => $v){
                $tmp_ips[] = $v;
            }
        }

        if(!empty($tmp_ips[0])){

            $primary_ip = $tmp_ips[0];

            // Extra IPs
            unset($tmp_ips[0]);
        }
        // 存入IP
        $mainip = '';
        $ip = [];
        $mainip = @$primary_ip;
        $ip = $tmp_ips;
        // 存入IP
        $update['dedicatedip'] = $mainip;
        $update['assignedips'] = implode(',', $ip);



        $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
        $IdcsmartCommonServerHostLinkModel->where('host_id',$params['hostid'])->update($update);

        return ['status'=>'success', 'msg'=>'同步成功'];
    }else{
        return ['status'=>'error', 'msg'=>serialize($virt_resp) ?: '同步失败'];
    }
}

// 升降级
function mfvirtualizor_ChangePackage($params){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return ['status'=>'error', 'msg'=>'[ERROR] Can not find vm id (vid)'] ;
    }
    $post_vps = array();
    $is_edit = false;

    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $api_path = 'index.php?act=editvs&vpsid='.$vserverid;
    // Only support change of Plan ID for now.
    // To-DO: is change of additional configuration will call this function?
    // Get new plan id
    // Check is plan id has been updated
    if(isset($params['configoptions_upgrade']['mf_plan_id'])){
        $plan_id = $params['configoptions']['mf_plan_id'];
        $is_edit = true;
    }else{
        $plan_id = '';
    }


    // Placeholder for additional configuration
    // some code ...




    if($is_edit) {
        $post_vps['editvps'] = 1;
        $post_vps['plid'] = $plan_id;
        $post_vps['vpsid'] = $vserverid;
        // Placeholder for additional configuration

        $response = mfvirtualizor_make_api_call($api_ip, $api_username, $api_pass, $api_path, array(), $post_vps);
        if (empty($response)) {
            return ['status' => 'error', 'msg' => '无法修改套餐'];
        } else {
            if (empty($response['done'])) {
                $create_error = implode('<br>', $response['error']);
                return ['status' => 'error', 'msg' => $create_error ?: '无法修改套餐'];
            } else {
                return ['status' => 'success', 'msg' => serialize($response['done_msg'])];
            }
        }
    }
}

// 云主机状态
// Not implemented yet
function mfvirtualizor_Status($params){
    $vserverid = mfvirtualizor_GetServerid($params);
    if(empty($vserverid)){
        return '[ERROR] Can not find vm id (vid)';
    }


    // 先获取vnc密码
    // For the Call
    $api_credentials = explode(",", $params['server_password']);
    $api_username = $api_credentials[0];
    $api_pass = $api_credentials[1];
    $api_ip = $params['server_ip'];
    $api_path = 'index.php?act=vpsmanage&';
    $virt_resp = mfvirtualizor_e_make_api_call($api_ip, $api_username, $api_pass, $vserverid, $api_path);

    if(!empty($virt_resp)){
        //based on priority
        if($virt_resp['info']['vps']['suspend'] == 1) {
            $result['data']['status'] = 'suspend';
            $result['data']['des'] = $virt_resp['info']['vps']['suspend_reason'];
        } else if($virt_resp['info']['status'] == 1){
            $result['data']['status'] = 'on';
            $result['data']['des'] = '开机';
        } else if($virt_resp['info']['status'] == 0) {
            $result['data']['status'] = 'off';
            $result['data']['des'] = '关机';
        } else {
            $result['data']['status'] = 'unknown';
            $result['data']['des'] = '未知';
        }
        return $result;
    }else{
        return ['status'=>'error', 'msg'=>'获取失败'];
    }
}


function mfvirtualizor_FiveMinuteCron_not_implemented(){
    $time = time();
    $HostModel = new HostModel();
    $host_data = $HostModel->alias('a')
        ->field('a.id,a.status domainstatus,a.suspend_reason,a.suspend_type,a.client_id uid,c.ip_address server_ip,
        c.hostname server_host,c.username server_username,c.password server_password,c.accesshash,c.secure,c.port,shl.vserverid')
        ->leftJoin('module_idcsmart_common_server_host_link shl','shl.host_id=a.id')
        ->leftJoin('module_idcsmart_common_server c','c.id=shl.server_id')
        ->leftJoin('module_idcsmart_common_server_group d','c.gid=d.id')
        ->whereIn('a.status','Active,Suspended')
        ->where('a.due_time=0 OR a.due_time>'.$time)
        ->where('d.system_type', 'normal')
        ->where('c.type', 'nokvm')
        ->where('a.is_delete', 0)
        ->select()
        ->toArray();

    $IdcsmartCommonServerHostLinkModel = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();

    foreach($host_data as $v){
        $v['server_password'] = aes_password_decode($v['server_password']);
        $sign = mfvirtualizor_CreateSign($v['server_password']);
        $url = mfvirtualizor_GetUrl($v, '/api/virtual/'.$v['vserverid'], $sign);
        $res = mfvirtualizor_Curl($url, [], 10, 'GET');
        if(is_numeric($res['data']['flow']['code']) && $res['data']['flow']['code'] == 0){
            $update = [];
            $update['bwusage'] = round(($res['data']['flow']['data']['in'] + $res['data']['flow']['data']['out'])/1024/1024/1024, 2);
            if(is_numeric($res['data']['flow_limit'])){
                $update['bwlimit'] = (int)$res['data']['flow_limit'];
            }
            $IdcsmartCommonServerHostLinkModel->where('id',$v['id'])->update($update);
            // 流量超额暂停
            if($v['domainstatus'] == 'Active' && $update['bwlimit'] > 0 && $update['bwusage'] > $update['bwlimit']){
                $HostModel->suspendAccount([
                    'id' => $v['id'],
                    'suspend_reason' => '用量超额',
                    'suspend_type' => 'overtraffic'
                ]);
            }
            // 解除暂停
            if($v['domainstatus'] == 'Suspended' && ($update['bwlimit'] == 0 || $update['bwusage'] < $update['bwlimit']) && ($v['suspend_type']=='overtraffic')){
                $HostModel->unsuspendAccount($v['id']);
            }
        }
    }
}

// 每天任务
function mfvirtualizor_DailyCron_not_implemented(){
    // 每月开头还原流量上限
    //if(date('Y-m-d') == date('Y-m-01')){
    //	$time = time();
    //    $HostModel = new HostModel();
    //	$host_data = $HostModel->alias('a')
    //        ->field('a.id,a.status domainstatus,a.suspend_reason,a.suspend_type,a.client_id uid,c.ip_address server_ip,
    //        c.hostname server_host,c.username server_username,c.password server_password,c.accesshash,c.secure,c.port,shl.vserverid')
    //        ->leftJoin('module_idcsmart_common_server_host_link shl','shl.host_id=a.id')
    //        ->leftJoin('module_idcsmart_common_server c','c.id=shl.server_id')
    //        ->leftJoin('module_idcsmart_common_server_group d','c.gid=d.id')
    //        ->whereIn('a.status','Active,Suspended')
    //        ->where('a.due_time=0 OR a.due_time>'.$time)
    //        ->where('d.system_type', 'normal')
    //        ->where('c.type', 'nokvm')
    //        ->where('a.is_delete', 0)
    //        ->select()
    //        ->toArray();
    //    $model = new \server\idcsmart_common\model\IdcsmartCommonServerHostLinkModel();
    //    foreach($host_data as $v){
    //    	$params = $model->getProvisionParams($v['id']);
    //    	if(isset($params['configoptions']['flow_limit'])){
    //    		/*if($params['configoptions']['flow_limit'] > 0){
    //    			$capacity = Db::name('dcim_buy_record')
    //				            ->where('type', 'flow_packet')
    //				            ->where('hostid', $v['id'])
    //				            ->where('uid', $v['uid'])
    //				            ->where('status', 1)
    //				            ->where('show_status', 0)
    //				            ->where('pay_time', '>', strtotime(date('Y-m-01 00:00:00')))
    //				            ->sum('capacity');
    //    			$post_data['flow_limit'] = $params['configoptions']['flow_limit'] + $capacity;
    //    		}else{
    //    			$post_data['flow_limit'] = 0;
    //    		}*/
    //            $post_data['flow_limit'] = 0;
    //			$sign = mfvirtualizor_CreateSign($params['server_password']);
    //			$url = mfvirtualizor_GetUrl($params, '/api/virtual/'.$v['vserverid'], $sign);
    //			mfvirtualizor_Curl($url, $post_data, 20, 'PUT');
    //
    //			mfvirtualizor_Sync($params);
    //    	}
    //    }
    //}
}

// 


// 后台按钮隐藏
function mfvirtualizor_AdminButtonHide($params){
    if(!empty(mfvirtualizor_GetServerid($params)) && $params['serverid']>0){
        return ['CreateAccount'];
    }else{
        return ['SuspendAccount','UnsuspendAccount','TerminateAccount','On','Off','Reboot','HardOff','HardReboot','Reinstall','CrackPassword','Vnc','Sync'];
    }
}

// 获取自定义字段value
function mfvirtualizor_GetServerid($params){
    return (int)($params['vserverid']??0);
}

// 创建签名



/*
 * Virtualizor API tools function
 *
 *
*/
function mfvirtualizor_make_api_call($ip, $username, $pass, $path, $data = array(), $post = array(), $cookies = array()){

    $key = mfvirtualizor_generateRandStr(8);
    $apikey = mfvirtualizor_make_apikey($key, $pass);
    $url = 'https://'.$ip.':4085/'.$path;
    $url .= (strstr($url, '?') ? '' : '?');
    $url .= '&adminapikey='.rawurlencode($username).'&adminapipass='.rawurlencode($pass);
    $url .= '&api=serialize&apikey='.rawurlencode($apikey).'&skip_callback=blesta';

    // Pass some data if there
    if(!empty($data)){
        $url .= '&apidata='.rawurlencode(base64_encode(serialize($data)));
    }

    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    // Time OUT
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

    // Turn off the server and peer verification (TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    // UserAgent
    curl_setopt($ch, CURLOPT_USERAGENT, 'Softaculous');

    // Cookies
    if(!empty($cookies)){
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookies, '', '; '));
    }

    if(!empty($post)){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Get response from the server.
    $resp = curl_exec($ch);

    if(empty($resp)){
        $GLOBALS['virt_curl_err'] = curl_error($ch);
    }

    curl_close($ch);

    if(empty($resp)){
        return false;
    }

    // As a security prevention measure - Though this cannot happen
    $resp = str_replace($pass, '12345678901234567890123456789012', $resp);

    $r = mfvirtualizor_unserialize($resp);

    if(empty($r)){
        return false;
    }

    return $r;
}

function mfvirtualizor_e_make_api_call($ip, $username, $pass, $vid, $path, $post = array()){

    $key = mfvirtualizor_generateRandStr(8);
    $apikey = mfvirtualizor_make_apikey($key, $pass);

    $url = 'https://'.$ip.':4083/'.$path;
    $url .= (strstr($url, '?') ? '' : '?');
    $url .= '&adminapikey='.rawurlencode($username);
    $url .= '&svs='.$vid.'&api=serialize&apikey='.rawurlencode($apikey).'&skip_callback=blesta';

    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    // Time OUT
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    // Turn off the server and peer verification (TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    // UserAgent and Cookies
    curl_setopt($ch, CURLOPT_USERAGENT, 'Softaculous');

    if(!empty($post)){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Get response from the server.
    $resp = curl_exec($ch);
    curl_close($ch);

    if(empty($resp)){
        return false;
    }

    // As a security prevention measure - Though this cannot happen
    $resp = str_replace($pass, '12345678901234567890123456789012', $resp);

    $r = mfvirtualizor_unserialize($resp);

    if(empty($r)){
        return false;
    }

    return $r;
}

function mfvirtualizor_make_apikey($key, $pass){
    return $key.md5($pass.$key);
}

function mfvirtualizor_unserialize($str){

    $var = @unserialize($str);

    if(empty($var)){

        preg_match_all('!s:(\d+):"(.*?)";!s', $str, $matches);
        foreach($matches[2] as $mk => $mv){
            $tmp_str = 's:'.strlen($mv).':"'.$mv.'";';
            $str = str_replace($matches[0][$mk], $tmp_str, $str);
        }
        $var = @unserialize($str);

    }

    //If it is still empty false
    if(empty($var)){

        return false;

    }else{

        return $var;

    }

}

//generates random strings
function mfvirtualizor_generateRandStr($length){
    $randstr = "";
    for($i = 0; $i < $length; $i++){
        $randnum = mt_rand(0,61);
        if($randnum < 10){
            $randstr .= chr($randnum+48);
        }elseif($randnum < 36){
            $randstr .= chr($randnum+55);
        }else{
            $randstr .= chr($randnum+61);
        }
    }
    return strtolower($randstr);
}

function r_print($data){

    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function mfvirtualizor_getdecryptpass($enc_pass){

    $dec_path = dirname(dirname(dirname(dirname(__FILE__)))).'/app/app_model.php';

    include_once($dec_path);

    $obj = new AppModel();

    return $obj->systemDecrypt($enc_pass);

}

//Copied from virtualizor WHMCS module
function mfvirtualizor_virtualizor_get_current_url(){

    $protocol = (!empty($_SERVER['HTTPS']) ? "https://" : "http://");
    $server_port = ((!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) ? ':'.$_SERVER['SERVER_PORT'] : '');

    $parse = parse_url($_SERVER['HTTP_HOST']);
    if(empty($parse['port'])){
        $full_url = $protocol.$_SERVER['HTTP_HOST'].$server_port.$_SERVER['REQUEST_URI'];
    }else{
        $full_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    $strpos = strpos($full_url, 'vp_login');
    $full_url = substr($full_url, 0, $strpos);
    $full_url = str_replace('&amp;', '&', $full_url);
    $full_url = rtrim($full_url, '&');

    return $full_url;
}

function validateHostName($host_name) {
    if (strlen($host_name) > 255) {
        return false;
    }

    return preg_match(
            "/^([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9])(\.([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9]))+$/",
            $host_name ) === 1;
}