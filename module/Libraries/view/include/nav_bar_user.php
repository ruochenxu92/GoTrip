<?php
$auth_status = \User\Util\ControllerUtil::checkAuthentication($this->service_locator);
echo '<ul class="deng pull-right">';
if (!$auth_status['is_authenticated']) {
    echo '<li><a href="/user/log/in/">登陆/注册</a></li>
    ';//<li><a href="/user/log/register/">注册</a></li>
} else {
    echo '<li><a href="/user/account/query/">用户中心</a></li>
    <li><a href="/user/log/out/">退出</a></li>';
}
echo '<li class="gou"><a href="/order/control/cart/">购物车</a></li>
    <li class="ding"><a href="/user/log/orderquery/" target="_blank">订单查询</a></li>
    
</ul>';
?>
