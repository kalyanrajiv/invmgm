<?php
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\View\Helper\UrlHelper;
use Cake\View\Helper\FormHelper;

//$relUrl = $this->Html->url(array('action' => 'check_if_active', 'controller' => 'devices'));
//$siteUrl = "http://" . $_SERVER['SERVER_NAME'];
//if(strpos($siteUrl,"hpwaheguru")){
//    $siteUrl = "http://hpwaheguru.co.uk";
//}else{
//     $siteUrl = "http://mbwaheguru.co.uk";
//}

$userAgent = $_SERVER['HTTP_USER_AGENT'];
$relUrl = "";//$relUrl.'?user_agent='.$userAgent;
?>
<script type="text/javascript" src="/evercookie-master/evercookie-master/js/swfobject-2.2.min.js"></script>
<script type="text/javascript" src="/evercookie-master/evercookie-master/js/evercookie.js"></script>

<script>
var ec = new evercookie({
    baseurl: '/evercookie-master/evercookie-master',
    asseturi: '/assets',
    phpuri: '/php'
});

// set a cookie "id" to "12345"
// usage: ec.set(key, value)
//ec.set("cookieCook", "MTQyMDc="); 

</script>
<?php
//pr($kioskArray);
foreach($kioskArray as $key => $kioskArr){
    $kioskCode = strtolower($kioskArr['code']);
    $kioskType = $kioskArr['kiosk_type'];
    if($kioskType==1){
        $kioskCategory = "Kiosk";
    }
    if($kioskType==2){
        $kioskCategory = "Service Center";
    }
    if($kioskType==3){
        $kioskCategory = "Unlocking Center";
    }
    $kioskName[$kioskCode] = $kioskArr['name'].'('. $kioskCategory.')';
}
    //echo $this->Form->create('User', array('action' => 'login'));
    echo $this->Form->create(null, ['url' => ['action' => 'login']]);
    ?>
    
<div id="login_div">
    <input type="hidden" name="div_co" value = "" id="div_co"/>
    <input type="hidden" name="age" value = "<?=$userAgent;?>"/>
    <input type="hidden" name="request_auth" value = "" id="reqst_auth"/>
    <?php echo $this->Form->inputs(array(
        //'legend' => __('Login'),
        'username',
        'password'
    ));
	if (Configure::read('AutoLogin.active')) {
	echo $this->Form->input('auto_login', array('type'=>'checkbox', 'label'=>__('Remember on this computer')));
	}
    //$kioskName['shop']='admin';    
    //echo $this->Form->input('subdomain',array('options'=>$kioskName,'label'=>false));?>
    <input type="button" name="request_authorization" value="Request Authorization" id="request_btn" style="width: 173px;height: 39px;border-radius: 5px;color: white;background: darkgrey;font-weight: bold;"/>
    <input type="submit" name="login_button" id="login_button" value="Login" id="login_button" style="width: 92px;"/>
    <?php //$options = array('id' => 'login_button', 'label' => 'Login');
    echo $this->Form->end(); //$options
?>
<a href="#" id='auth_device' onclick="showDeviceBlock();">Submit OTP</a>
<p><?php echo $this->Html->link(__('Forgot Password'), array('controller' => 'users', 'action' => 'forget_password')); ?></p>
</div>

<div id="device_block">
    <?php
        //form for submitting OTP
        //echo $this->Form->create('Device', array('controller' => 'devices', 'action' => 'request_authorization'));?>
        <!--<input type="hidden" name="div_co1" value = "" id="div_co1"/>
        <input type="hidden" name="age1" value = "<?=$userAgent;?>"/>-->
        
        <?php #echo $this->Form->input('Kiosk', array('options' => $kiosks, 'div' => false));
        //#echo $this->Form->input('Username', array('type' => 'text', 'div' => false, 'placeholder' => 'Username', 'label' => false, 'style' => "width: 115px;margin-left: 11px;height: 13px;"));
        //echo $this->Form->input('OTP', array('type' => 'text', 'div' => false, 'label' => false, 'placeholder' => 'One time password', 'style' => "width: 132px;margin-left: 11px;height: 13px;"));
        //$options1 = array('id' => 'request_button', 'label' => 'Submit', 'type' => 'button', 'style' => "width: 173px;height: 39px;border-radius: 5px;color: white;background: darkgrey;font-weight: bold;");
        //echo $this->Form->end($options1);
    ?>
    <input type="hidden" rel="<?=$relUrl;?>" id="ajax_field"/>
<!--<a href="#" onclick="showLoginBlock();">Login</a>-->
</div>

<script>
    /*$('#login_button').click(function(event){
        ec.get("cookieCook", function(value) {
            console.log(value);
            if (!value || value == "<p>Missing Controller</p>") {
                document.getElementById( 'div_co' ).value = '';
                alert("You are not authorized to login from this system. Please contact the administrator to authorize this device!");
                document.getElementById('request_btn').style.display = 'block';
                document.getElementById('login_button').style.display = 'none';
                document.getElementById('auth_device').style.display = 'block';
                event.preventDefault(event);
            } else {
                $.blockUI({ message: 'Just a moment...' });
                //if cookie found
                //ajax work here for checking if the cookie is active from the admin end
                var mainUrl = $('#ajax_field').attr('rel');
                var targeturl = mainUrl + '&cok=' + value;
                console.log(targeturl);
                $.ajax({
                    type: 'get',
                    url: targeturl,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    },
                    success: function(response) {
                        $.unblockUI();
                        if (response == 'true') {
                            document.getElementById('request_btn').style.display = 'none';
                            document.getElementById( 'div_co' ).value = value;
                            $('#UserLoginForm').submit();
                        } else {
                            alert("You are not authorized to login from this device. Please request authorization to gain access!");
                            document.getElementById('request_btn').style.display = 'block';
                            document.getElementById('login_button').style.display = 'none';
                            document.getElementById('auth_device').style.display = 'block';
                        }
                    },
                    error: function(e) {
                        alert("An error occurred: " + e.responseText.message);
                        console.log(e);
                    }
                });
            }
        });
        event.preventDefault(event);
    });*/
    
    //following is being used, when user clicks on request authorization
    $('#request_btn').click(function(ev){
        ec.get("cookieCook", function(valu) {
            console.log(valu);
            document.getElementById( 'reqst_auth' ).value = 'yes';
            if (!valu || valu == "<p>Missing Controller</p>") {
                document.getElementById( 'div_co' ).value = '';
                $('#UserLoginForm').submit();
            } else {
                document.getElementById( 'div_co' ).value = valu;
                $('#UserLoginForm').submit();
            }
        });
        //event.preventDefault(ev);
    });
    
    $('#request_button').click(function(ev){
        ec.get("cookieCook", function(valu) {
            console.log(valu);
            if (!valu || valu == "<p>Missing Controller</p>") {
                document.getElementById( 'div_co1' ).value = '';
                $('#DeviceRequestAuthorizationForm').submit();
            } else {
                document.getElementById( 'div_co1' ).value = valu;
                $('#DeviceRequestAuthorizationForm').submit();
            }
        });
        //event.preventDefault(ev);
    });
    
    $(document).ready(function(){
        document.getElementById( 'reqst_auth' ).value = '';
        document.getElementById( 'div_co' ).value = '';
    });
    
    function showDeviceBlock() {
        document.getElementById('device_block').style.display = 'block';
        document.getElementById('login_div').style.display = 'none';
    }
    
    function showLoginBlock() {
        document.getElementById('device_block').style.display = 'none';
        document.getElementById('login_div').style.display = 'block';
    }
    
    window.onload = function(){
        document.getElementById('request_btn').style.display = 'none';
        document.getElementById('device_block').style.display = 'none';
        document.getElementById('login_div').style.display = 'block';
        document.getElementById('auth_device').style.display = 'none';
    };
</script>
