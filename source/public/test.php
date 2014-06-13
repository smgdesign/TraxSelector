<?php
print_r($_GET);
?>
<form action="/api/venue/init" method="post">
    API Key <input name="api_key" value="9AdwAbXRB0D34ue4lN1G" /><br />
    UDID <input name="UUID" value="xre987" />
    <input type="submit" />
</form>

<pre><?php var_export($_SERVER)?></pre>
