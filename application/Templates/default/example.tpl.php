<html>
<head>
    <title>{var.page_title}</title>
</head>
<body>
--- START TEMPLATE !<br />
<h1>{var.page_title}</h1>
<p>{var.page_message}</p>
<div>
    --- START __THIS__ !<br />
    <!-- widget addon="__THIS__" --><br />
    --- END __THIS__ !<br />
</div>
<br><br><br><br><br>
--- Widget in Template ! <br />
<!-- widget addon="Abc" module="Lala" action="showView" params="param1=param1_value|param2=param2_value" -->
<br />
--- END Widget in Template ! <br />
---END TEMPLATE !
</body>
</html>