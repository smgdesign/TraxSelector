<?php
print_r($_GET);
?>
<form action="/api/venue/init" method="post">
    API Key <input name="api_key" value="9AdwAbXRB0D34ue4lN1G" /><br />
    UDID <input name="UUID" value="xre987" />
    <input type="submit" />
</form>

<form action="/api/photo/submit" method="post" enctype="multipart/form-data">
    <input type="file" name="image" />
    <!--<input type="text" name="image" />-->
    <input type="hidden" name="submitted" value="true" />
    <input type="hidden" name="UUID" value="C0CFDB02-ED39-40BD-8A57-6BA11C1696B5" />
    <input type="hidden" name="api_key" value="9AdwAbXRB0D34ue4lN1G" />
    <input type="submit" name="submitImage" />
</form>

<form action="/api/photo/get" method="post">
    <input type="hidden" name="submitted" value="true" />
    <input type="hidden" name="UUID" value="C0CFDB02-ED39-40BD-8A57-6BA11C1696B5" />
    <input type="hidden" name="api_key" value="9AdwAbXRB0D34ue4lN1G" />
    <input type="submit" name="Get Images" />
</form>
<form action="/api/photo/display/1407164653-17-53dfa0ed91a0f-523057_4524019786190_1101932316_n.jpg/thumb" method="post">
    <input type="hidden" name="submitted" value="true" />
    <input type="hidden" name="UUID" value="C0CFDB02-ED39-40BD-8A57-6BA11C1696B5" />
    <input type="hidden" name="api_key" value="9AdwAbXRB0D34ue4lN1G" />
    <input type="submit" name="Get Images" />
</form>
<pre><?php var_export($_SERVER)?></pre>
