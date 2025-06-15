<?php

namespace AutoImport;

class InitAdminSettings
{
    public function addOptionPage()
    {
        add_options_page(
            'Auto Import Posts Settings',
            'Auto Import Posts',
            'manage_options',
            'aip_settings',
            [$this, 'renderSettings'],
        );
    }

    public function renderSettings()
    {
        ?>
        <div class="aip-wrapper"> 
            <h1>Auto Import Posts Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('aip_api_settings');
                do_settings_sections('aip_settings');
                submit_button('Update')
                ?>
            </form>
        </div>
        <?php
    }

    public function addSettings()
    {
        register_setting('aip_api_settings', 'aip_api_key');

        add_settings_section(
            'aip_settings_section',
            null,
            null,
            'aip_settings'
        );

        add_settings_field(
            'aip_settings_field',
            'API key',
            function () {
                $value = get_option('aip_api_key');
                echo '<input type="text" name="aip_api_key" value="' . esc_html($value) . '" class="aip_field">';
            },
            'aip_settings',
            'aip_settings_section',
        );
    }
}
