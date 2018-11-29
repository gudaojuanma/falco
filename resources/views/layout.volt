<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>Falco</title>
        <link rel="icon" href="{{ static_url('/favicon.ico') }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ static_url('/favicon.ico') }}" type="image/x-icon">
        {{ assets.outputCss() }}
    </head>
    <body>
        {% block content %}{% endblock %}

        {{ assets.outputJs() }}
    </body>
</html>