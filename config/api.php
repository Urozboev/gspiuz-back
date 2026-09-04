<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API manzil prefiksi
    |--------------------------------------------------------------------------
    |
    | Barcha API marshrutlari shu prefiks ostida turadi. U maxfiy: sayt
    | xavfsizligining bir qismi API manzilining topib boʻlmasligiga
    | tayanadi, shuning uchun haqiqiy qiymat faqat `.env` da yashaydi va
    | repozitoriyga tushmaydi.
    |
    | `.env` da `API_PREFIX` boʻlmasa oddiy `api` ishlatiladi — bu holat
    | xavfsiz emas, faqat ishlab chiqish uchun. Serverda `API_PREFIX` ni
    | albatta toʻldiring (qarang: DEPLOY.md).
    |
    | Frontenddagi `BACKEND_API_PREFIX` bilan bir xil boʻlishi shart.
    |
    */

    'prefix' => env('API_PREFIX', 'api'),

];
