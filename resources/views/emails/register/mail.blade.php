<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<div class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
Thank you for registering with TRUST Customer Management.<br /><br />
To activate the service, please click the link below to verify your email address. <br />
(Valid for 30 minutes from issuance.)<br /><br />

    <a target="_blank" href="{{$url}}/user/verification/{{$code}}">{{$url}}/user/verification/{{$code}}</a><br /><br />   
If you have no idea, do nothing and delete this email<br /><br />

    @include('emails.common.signature')
</div>
</body>
</html>
