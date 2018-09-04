<?xml version="1.0" encoding="UTF-8"?>
<WebElementEntity>
   <description></description>
   <name>td_24x724x7            jQuery(</name>
   <tag></tag>
   <elementGuidId>9ad3e103-2e26-4fb6-a92c-55e90d31c486</elementGuidId>
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
      <value>24x724x7
            jQuery(function () {
                var $currentSelect2Objectcheck_period = jQuery(&quot;#check_period&quot;).centreonSelect2({
                    allowClear: true,
                    pageLimit: 60,
                    select2: {
                        ajax: {
                url: &quot;./include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&amp;action=list&quot;
            },
                        
                        placeholder: &quot;Check Period&quot;,
                        disabled: false
                    }
                });
                
                $requestcheck_period = jQuery.ajax({
            url: &quot;./include/common/webServices/rest/internal.php?object=centreon_configuration_timeperiod&amp;action=defaultValues&amp;target=meta&amp;field=check_period&amp;id=&quot;,
        });
        
        $requestcheck_period.success(function (data) {
            for (var d = 0; d &lt; data.length; d++) {
                var item = data[d];
                
                // Create the DOM option that is pre-selected by default
                var option = &quot;&lt;option selected=\&quot;selected\&quot; value=\&quot;&quot; + item.id + &quot;\&quot; &quot;;
                if (item.hide === true) {
                    option += &quot;hidden&quot;;
                }
                option += &quot;>&quot; + item.text + &quot;&lt;/option>&quot;;
              
                // Append it to the select
                $currentSelect2Objectcheck_period.append(option);
            }
 
            // Update the selected options that are displayed
            $currentSelect2Objectcheck_period.trigger(&quot;change&quot;,[{origin:'select2defaultinit'}]);
        });

        $requestcheck_period.error(function(data) {
            
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
