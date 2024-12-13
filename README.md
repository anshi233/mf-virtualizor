# Virtualizor 魔方财务/魔方V10 插件

## 安装
1. 下载插件
2. 解压插件到module目录中
3. 本插件文件夹改名成mfvirtualizor

## 配置 (魔方V10)
1. 前往商品管理->接口管理->子接口管理->新建接口
2. 选择服务器模块mf-virtualizor
3. IP地址填写主控IP地址，也可以是域名
4. 在密码处按照以下方式填写密码
- "key,keypass"
- 举例，假设key是123456，keypass是abcdefg，那么密码处填写"123456,abcdefg"
5. 其他地方都留空，点击保存
6. 点击接口名称旁的按钮，如果显示绿色的勾，说明配置成功
7. 自行添加商品，商品相关文档待更新
 * OS选项设置可以参考[官方nokvm插件文档](https://www.idcsmart.com/wiki_list/1280.html)的操作系统可选配置。OS ID应填Virtualizor里的模板名字。

## 已完成功能
* createAccount （开通）
* TerminateAccount （删除）
* Suspend （暂停）
* Unsuspend （解除暂停）
* virtualizor enduser panel SSO登录跳转
* 开，关，重启 虚拟机
* 开通时自定义选项
* 升降配
* 手动绑定现存的虚拟机到用户账户
* 重置密码
* 重装系统

## 未完成功能

* Log记录
* VNC
* Domain Forwarding

## 注意事项
本模块基于[魔方业务V10](https://github.com/idcsmart/ZJMF-CBAP)开发,理论上也兼容魔方财务系统（作者没有授权license，未测试）。

欢迎提出建议和bug反馈。
