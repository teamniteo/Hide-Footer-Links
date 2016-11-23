<?php

use Niteoweb\HideFooterLinks\FooterLinks;
use WP_Mock\Tools\TestCase;

class TestFooterLinks extends TestCase
{

    public function test_init_admin()
    {
        \WP_Mock::wpFunction('is_admin', array(
                'return' => true,
            )
        );

        \WP_Mock::wpFunction('current_user_can', array(
                'return' => true,
            )
        );

        $plugin = new FooterLinks;

        \WP_Mock::expectActionAdded('generate_rewrite_rules', array($plugin, 'generateRewriteRules'));
        \WP_Mock::expectActionAdded('wp_admin', array($plugin, 'wpAdminInit'));
        \WP_Mock::expectActionAdded('customize_register', array($plugin, 'customizeRegister'));
        \WP_Mock::expectActionAdded('wp_head', array($plugin, 'customizeCSS'));

        $plugin->__construct();
        $plugin->wpAdminInit();

        \WP_Mock::assertHooksAdded();
    }

    public function test_generate_rewrite_rules()
    {
        global $wp_rewrite;
        \WP_Mock::wpFunction('wp_make_link_relative', array(
                'return' => '/wp-content/plugins/footer-links/',
            )
        );
        \WP_Mock::wpFunction('plugin_dir_url', array(
                'return' => 'http://localhost/wp-content/plugins/footer-links/',
            )
        );

        $wp_rewrite = \Mockery::mock();
        $wp_rewrite->shouldReceive('add_external_rule')->withArgs(
            array(
                "wp-content/plugins/footer-links/",
                "index.php%{REQUEST_URI}"
            )
        );
        $wp_rewrite->shouldReceive('add_external_rule')->withArgs(
            array(
                "wp-content/plugins/footer-links/index.php",
                "index.php%{REQUEST_URI}"
            )
        );
        $wp_rewrite->shouldReceive('add_external_rule')->withArgs(
            array(
                "wp-content/plugins/footer-links/readme.txt",
                "index.php%{REQUEST_URI}"
            )
        );

        $plugin = new FooterLinks;
        $plugin->generateRewriteRules($wp_rewrite);
    }


    public function test_customizer()
    {
        $wp_customize = \Mockery::mock();
        $wp_customize->shouldReceive('add_section')->withArgs([
            "hfl_settings_section",
            [
                "title"=>"Footprint Settings",
                "priority"=>430,
            ]
        ]);
        $wp_customize->shouldReceive('add_setting');
        $wp_customize->shouldReceive('add_control');


        $wp_theme = \Mockery::mock();
        $wp_theme->shouldReceive('get_template')
            ->once()
            ->andReturn('some_theme!');
        \WP_Mock::wpFunction('wp_get_theme', array(
                'return' => $wp_theme,
            )
        );


        $plugin = new FooterLinks;
        $plugin->customizeRegister($wp_customize);
    }

    public function test_customizeCSS()
    {
        \WP_Mock::wpFunction('get_option', array(
                'return' => ['hide-selector'=>true, 'hide-enabled'=>true, 'use-important'=>true],
            )
        );

        $wp_theme = \Mockery::mock();
        $wp_theme->shouldReceive('get_template')
            ->once()
            ->andReturn('duster');
        \WP_Mock::wpFunction('wp_get_theme', array(
                'return' => $wp_theme,
            )
        );


        $plugin = new FooterLinks;
        $plugin->customizeCSS();
    }

    public function test_customizeCSSNotFoundTheme()
    {
        \WP_Mock::wpFunction('get_option', array(
                'return' => ['hide-selector'=>true, 'hide-enabled'=>true, 'use-important'=>true],
            )
        );

        $wp_theme = \Mockery::mock();
        $wp_theme->shouldReceive('get_template')
            ->once()
            ->andReturn('theme_does_not_exists');
        \WP_Mock::wpFunction('wp_get_theme', array(
                'return' => $wp_theme,
            )
        );


        $plugin = new FooterLinks;
        $plugin->customizeCSS();
    }

}