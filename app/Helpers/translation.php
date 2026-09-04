<?php

use App\Models\TranslationGroup;

function translation($key)
{
    $lang = app()->getLocale();
    
    $explode_array = explode('.', $key);
    $translation_group__title = $explode_array[0];
    unset($explode_array[0]);
    $translation__title = implode($explode_array);

    $translation_group = TranslationGroup::where('sub_text', $translation_group__title)
        ->first();

    $translation = null;
    if ($translation_group) {
        $translation = $translation_group->translations()
            ->where('key', $translation__title)
            ->first()['val'][$lang];
    }
    
    return $translation;
}
