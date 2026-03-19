<?php

return [
    'asset_url'       => null,
    'app_url'         => null,
    'middleware_group' => 'web',

    /*
     * Serve o livewire.js via rota Laravel em vez de arquivo estático.
     * Resolve o problema de 404 em ambientes com filesystem efêmero (Render, Railway).
     */
    'inject_assets' => true,

    'temporary_file_upload' => [
        'disk'            => null,
        'rules'           => ['required', 'file', 'max:12288'],
        'directory'       => null,
        'middleware'      => null,
        'preview_mimes'   => ['png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi', 'wmv', 'mp3', 'webp'],
        'max_upload_time' => 5,
    ],

    'render_on_redirect' => false,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],
];
