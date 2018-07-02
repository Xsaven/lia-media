<?php

return [
    'types' => [
        'img' => [
            'title' => 'Image',
            'icon' => 'image',
            'class' => Lia\Media\Types\ImgType::class
        ],
        'video' => [
            'title' => 'Video',
            'icon' => 'video-camera',
            'class' => Lia\Media\Types\VideoType::class
        ]
    ],

    'relate' => [
        'name' => 'Products',
        'model' => false,
        'title_filed' => 'title',
        'id_filed' => 'id',
    ],

    'markers' => [
        'slider' => [
            'integer',
            'form' => ['type' => 'switch', 'default' => 0],
            'grid' => true
        ]
    ]
];