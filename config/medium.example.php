<?php

return [
    'class' => 'app\components\MediumApi',

    // Домен API MEDIUM
    'mediumApiDomain' => 'http://91.225.122.210:8080',

    # Список забронированих сиансов к доктору
    # Прод
    'doctorsVisit' => '/C:1CDA3C6126B1EB1/I:PACK?loadmask=',
    # Тест
    //'doctorsVisit' => '/C:1D45F000F6704C0/I:PACK?loadmask=',

    # Список забронированых кабинетов
    #Прод
    'cabinetSchedule' => '/C:1CDA3C6126B1EB1/I:PACK?loadmask=',
    #Тест
    //'cabinetSchedule' => '/C:1D45F000F6704C0/I:PACK?loadmask=',
];