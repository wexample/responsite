<?php

$config = [
    'name'             => 'Responsite | Respon.site',
    'metaDescription'  => 'Responsite | A responsive responsite site',
    'rootSiteFallback' => true,
    'admin'            => [
        'tabs' => [
            'contact' => 'Contacts',
        ],
    ],
    'section'          => [
        [
            'id'       => 'header',
            'type'     => 'header',
            'template' => 'header/content',
        ],
        [
            'id'       => 'landing',
            'type'     => 'landing',
            'template' => 'landing/content',
            'data'     => [
                'h1'       => '',
                'h2'       => '',
                'ctaTitle' => 'Créer mon responsite',
                'ctaLink'  => "javascript:app.section.modal.open('contact')",
            ],
        ],
        [
            'id'   => 'features',
            'type' => 'promotional_icons',
            'data' => [
                'blocks' => [
                    [
                        'icon'        => 'template::icon/coffee',
                        'title'       => 'Premier RDV gratuit',
                        'description' => 'Rencontrez un chef de projet pour définir vos objectifs et vous conseiller sur votre stratégie web.',
                    ],
                    [
                        'icon'        => 'template::icon/clipboard',
                        'title'       => 'Atelier de co-construction',
                        'description' => 'site::section/features/workshop.php',
                    ],
                    [
                        'icon'        => 'template::icon/edit-box',
                        'title'       => 'Design personnalisé',
                        'description' => 'Offrez-vous un design original, léger énergétiquement pour sublimer votre activité.',
                    ],
                    [
                        'icon'        => 'template::icon/code',
                        'title'       => 'Technologie Optimisée',
                        'description' => 'Faîtes le choix d’une technologie open source efficiente, solide et très bien documentée.',
                    ],
                ],
            ],
        ],
        [
            'id'   => 'lets_go',
            'type' => 'single_button',
            'data' => [
                'title' => 'C\'est parti !',
                'href'  => "javascript:app.section.modal.open('contact')",
            ],
        ],
        [
            'id'       => 'author',
            'type'     => 'single_text',
            'template' => 'author/content',
        ],
        [
            'type'     => 'footer',
            'template' => 'footer_legal/content',
        ],
        [
            'id'       => 'contact',
            'type'     => 'modal',
            'template' => 'contact/content',
            'data'     => [
                'close' => [
                    'size'  => 30,
                    'color' => 'black',
                ],
            ],
        ],
    ],
];