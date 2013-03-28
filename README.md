syslog 使用实例

文件说明

* `01als.conf`<br />syslog-ng 的配置文件
* `forward.php`<br />用于将 syslog-ng 通过 unix socket 接收到的信息转发给中央服务器
* `send.php`<br />本地测试脚本，验证 syslog-ng 是否成功配置
* `make_send.php`<br />benchmark 脚本，瞬间大量发送消息测试是否会有丢失
