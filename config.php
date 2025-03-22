<?php

//Enable Debug Mode 是否开启调试模式
const DEBUG_MODE = false;

//noVNC Path noVNC路径，需要根据实际情况修改
//如果使用魔方业务
const NOVNC_PATH = '/plugins/server/idcsmart_common/module/mfvirtualizor/noVNC/vnc.html';

//如果使用魔方财务
//const NOVNC_PATH = '/plugins/server/idcsmart_common/module/mfvirtualizor/noVNC/vnc.html';

//如果使用自建noVNC
//const NOVNC_PATH = '/noVNC/vnc.html';


//noVNC Hostname 地址
//使用本模块自建noVNC时，需要修改为noVNC服务器的地址
const NOVNC_USE_CUSTOM_HOST = false;
const NOVNC_HOST = 'YOUR_NOVNC_HOST';