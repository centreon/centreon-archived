<?xml version="1.0" encoding="UTF-8"?>
<WebElementEntity>
   <description></description>
   <name>td_host-notify-by-emailhost-no</name>
   <tag></tag>
   <elementGuidId>2adee0eb-a354-4d7b-8cb5-4135313ef7b0</elementGuidId>
   <selectorMethod>BASIC</selectorMethod>
   <useRalativeImagePath>false</useRalativeImagePath>
   <webElementProperties>
      <isSelected>true</isSelected>
      <matchCondition>equals</matchCondition>
      <name>tag</name>
      <type>Main</type>
      <value>td</value>
   </webElementProperties>
   <webElementProperties>
      <isSelected>false</isSelected>
      <matchCondition>equals</matchCondition>
      <name>class</name>
      <type>Main</type>
      <value>FormRowValue</value>
   </webElementProperties>
   <webElementProperties>
      <isSelected>true</isSelected>
      <matchCondition>equals</matchCondition>
      <name>text</name>
      <type>Main</type>
      <value>host-notify-by-email×host-notify-by-email
            jQuery(function () {
                var $currentSelect2Objectcontact_hostNotifCmds = jQuery(&quot;#contact_hostNotifCmds&quot;).centreonSelect2({
                    allowClear: true,
                    pageLimit: 60,
                    select2: {
                        ajax: {
                url: &quot;./include/common/webServices/rest/internal.php?object=centreon_configuration_command&amp;action=list&amp;t=1&quot;
            },
                        
                        placeholder: &quot;Host Notification Commands&quot;,
                        disabled: false
                    }
                });
                
                $requestcontact_hostNotifCmds = jQuery.ajax({
            url: &quot;./include/common/webServices/rest/internal.php?object=centreon_configuration_command&amp;action=defaultValues&amp;target=contact&amp;field=contact_hostNotifCmds&amp;id=&quot;,
        });
        
        $requestcontact_hostNotifCmds.success(function (data) {
            for (var d = 0; d &lt; data.length; d++) {
                var item = data[d];
                
                // Create the DOM option that is pre-selected by default
                var option = &quot;&lt;option selected=\&quot;selected\&quot; value=\&quot;&quot; + item.id + &quot;\&quot; &quot;;
                if (item.hide === true) {
                    option += &quot;hidden&quot;;
                }
                option += &quot;>&quot; + item.text + &quot;&lt;/option>&quot;;
              
                // Append it to the select
                $currentSelect2Objectcontact_hostNotifCmds.append(option);
            }
 
            // Update the selected options that are displayed
            $currentSelect2Objectcontact_hostNotifCmds.trigger(&quot;change&quot;,[{origin:'select2defaultinit'}]);
        });

        $requestcontact_hostNotifCmds.error(function(data) {
            
        });
         
            });
         </value>
   </webElementProperties>
   <webElementProperties>
      <isSelected>false</isSelected>
      <matchCondition>equals</matchCondition>
      <name>xpath</name>
      <type>Main</type>
      <value>id(&quot;tab1&quot;)/table[@class=&quot;formTable table&quot;]/tbody[1]/tr[@class=&quot;list_one&quot;]/td[@class=&quot;FormRowValue&quot;]</value>
   </webElementProperties>
</WebElementEntity>
