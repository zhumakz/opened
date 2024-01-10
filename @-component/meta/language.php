<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

?>


<script>

    'use strict';
    
    $(document.currentScript).remove();
    $(() => {

        $('body').on('change', '.change-language', e => {
            setCookie('LANG', $(e.target).val(), {'expires': 315360000,'path':'/'});
            location.reload();
        });
        let current = getCookie('LANG') || 0;
        $('.change-language option[value=' + current + ']').prop('selected', true);

    });

</script>


<select class="w-auto custom-select custom-select-sm change-language">
    <option value="0">English</option>
    <option value="1">Қазақ</option>
    <option value="2">Русский</option>
</select>