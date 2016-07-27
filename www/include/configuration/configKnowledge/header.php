<?php
if (zend_loader_file_encoded() == true)	{
    $licenseValidity = zend_loader_install_license ($centreon_path . "www/modules/centreon-knowledgebase/license/merethis_lic.zl", true);
	if ($licenseValidity == false) {
	    echo "<div class='msg' align='center'>"._("The license is not valid. Please contact your administator for more information.")."</div>";
		echo "</td></tr></table></div>";
		include("./footer.php");
		exit(0);
    }
}
?>