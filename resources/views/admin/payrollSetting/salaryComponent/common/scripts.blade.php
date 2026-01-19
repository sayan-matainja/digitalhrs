<script>
    $('document').ready(function(){
        let valueType = $('#value_type').val();
        if(valueType !== 'fixed'){
            setMaxLimitForComponentValue();
        }

        $('#value_type').change(function(e){
           e.preventDefault();
           let valueType = $(this).val();
           if(valueType !== 'fixed'){
               setMaxLimitForComponentValue();
           }else{
               removeMaxLimitForComponentValue();
           }
        });

        function setMaxLimitForComponentValue(){
            let maxLimit = 100;
            $('#component_value_monthly').attr('max',maxLimit)
        }

        function removeMaxLimitForComponentValue(){
            $('#component_value_monthly').removeAttr('max')
        }

    });
</script>
