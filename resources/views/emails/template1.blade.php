<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
</head>
<body>
<p>
    收到来自教务系统的新消息。
</p>
<p>
    下面是消息明细:
</p>
<ul>
    <li>点击: <a  href="{{ $data['name'] }}">{{ $data['name'] }}</a></li>
</ul>
<hr>
<p>
    @foreach ($data['messageLines'] as $messageLine)
        {{ $messageLine }}<br>
    @endforeach
</p>
<hr>
</body>
</html>
