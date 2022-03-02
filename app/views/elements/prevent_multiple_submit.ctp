<script type="text/javascript">
    $(document).ready(function(){
        $("form").submit(function(){
            var isFormValidated=$(this).validationEngine('validate');
            if(isFormValidated){
                $("button[type=submit]", this).attr('disabled', 'disabled');
            }
        });
    });
</script>