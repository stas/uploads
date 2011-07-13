<?php
/*
Plugin Name: WordPress Uploads
Plugin URI: http://wordpress.org/extend/plugins/uploads/
Description: Better control over WordPress uploads
Author: Stas Sușcov
Version: 0.1
Author URI: http://stas.nerd.ro/
*/
?>
<?php
/*  Copyright 2011  Stas Sușcov <stas@nerd.ro>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'UPLOADS', '0.1' );

class Uploads {
    /**
     * Name of the meta keys
     */
    static $meta_keys = array(
        'uploads_disabled',
        'uploads_mask',
        'uploads_ext'
    );
    
    /**
     * The query var to be prepended in the masked url
     */
    static $query_var = 'get_attachment';
    
    /**
     * init()
     * 
     * Sets the hooks and other initialization stuff
     */
    function init() {
        add_action( 'admin_menu', array( __CLASS__, 'page' ) );
        add_action( 'init', array( __CLASS__, 'localization' ) );
        add_action( 'wp', array( __CLASS__, 'serve_file' ) );
        add_filter( 'upload_mimes', array( __CLASS__, 'load_extensions' ) );
        add_filter( 'wp_get_attachment_url', array( __CLASS__, 'mask' ), 10, 2 );
    }

    /**
     * localization()
     * 
     * i18n
     */
    function localization() {
        load_plugin_textdomain( 'uploads', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }
    
    /**
     * page()
     * 
     * Adds the options page to existing menu
     */
    function page() {
        add_options_page(
            __( 'Uploads', 'uploads' ),
            __( 'Uploads', 'uploads' ),
            'administrator',
            'uploads',
            array( __CLASS__, 'page_body' )
        );
    }
    
    /**
     * page()
     * 
     * Callback to render the options page and handle it's form
     */
    function page_body() {
        $flash = null;
        $options = array_fill_keys( self::$meta_keys, null );
        
        if ( isset( $_POST['uploads_nonce'] ) && wp_verify_nonce( $_POST['uploads_nonce'], 'uploads' ) ) {
            if ( isset( $_POST['uploads']['disable'] ) )
                $options['uploads_disabled'] = !empty( $_POST['uploads']['disable'] );
            if ( isset( $_POST['uploads']['mask'] ) )
                $options['uploads_mask'] = !empty( $_POST['uploads']['mask'] );
            if ( isset( $_POST['uploads']['ext'] ) )
                $options['uploads_ext'] = esc_textarea( $_POST['uploads']['ext'] );
            
            foreach ( $options as $k => $v )
                update_option( $k, $v );
        }
        
        $vars = self::load_settings();
        $vars['flash'] = $flash;
        self::render( 'settings', $vars );
    }
    
    /**
     * parse_ext( $ext )
     * Parses the extension => mimetype string data
     *
     * @param String $ext that has to be parsed
     * @return Mixed, an array of extension `key` => mimetype `value`
     */
    function parse_ext( $ext ) {
        $ext_mime = array();
        
        foreach ( explode( "\n", $ext ) as $line )
            if ( $line ) {
                $e_m = preg_split( '/\s*\s/', $line );
                if ( count( $e_m ) > 1 )
                    $ext_mime[ $e_m[1] ] = $e_m[0];
            }
        
        return $ext_mime;
    }
    
    /**
     * load_extensions( $mime_types )
     * Filter extends existing WordPress allowed mime types
     *
     * @param $mime_types, initial set
     * @return Mixed, a new, extended set of mime types
     */
    function load_extensions( $mime_types ) {
        $options = self::load_settings();
        
        if ( $options['uploads_disabled'] )
            return array();
        
        if ( !empty( $options['uploads_ext'] ) ) {
            $new_mimes = self::parse_ext( $options['uploads_ext'] );
            return array_merge( $mime_types, $new_mimes );
        }
        return $mime_types;
    }
    
    /**
     * mask( $url, $post_id )
     * Filter masks the attachment url
     *
     * @param $url, initial url
     * @param $aid, the ID of the attachment
     * @return String, a new, masked url
     */
    function mask( $url, $aid ) {
        $options = self::load_settings();
        
        if ( !$options['uploads_mask'] )
            return $url;
        
        if ( $aid ) {
            $hash = base64_encode( $aid ) . base64_encode( NONCE_KEY );
            $hash = base64_encode( $hash );
            return get_site_url() . '?' . self::$query_var . '=' . $hash ;
        }
        
        return $mime_types;
    }
    
    /**
     * serve_file()
     * Serves the requested file if masked. Hooks into `wp`
     */
    function serve_file() {
        if ( !isset( $_REQUEST[self::$query_var] ) )
            return;
        
        $upload_name = $_REQUEST[self::$query_var];
        
        if ( $upload_name ) {
            $hash = base64_decode( $upload_name );
            $salt_hash = base64_encode( NONCE_KEY );
            $aid = base64_decode( str_replace( $salt_hash, '', $hash ) );
            $u = get_post( $aid );
            if( is_object( $u ) && $u->post_type = 'attachment' ) {
                header('Content-Type: ' . $u->post_mime_type );
                header('Content-Disposition: attachment; filename="' . basename( $u->guid ) . '"' );
                readfile( get_attached_file( $aid ) );
            }
        }
        return;
    }
    
    /**
     * load_settings()
     * Loads the `uploads` settings
     *
     * @return Mixed, array of fetched settings
     */
    function load_settings() {
        $values = array();
        foreach ( self::$meta_keys as $k )
            $values[$k] = get_option( $k, null );
        return $values;
    }
    
    /**
     * render( $name, $vars = null, $echo = true )
     *
     * Helper to load and render templates easily
     * @param String $name, the name of the template
     * @param Mixed $vars, some variables you want to pass to the template
     * @param Boolean $echo, to echo the results or return as data
     * @return String $data, the resulted data if $echo is `false`
     */
    function render( $name, $vars = null, $echo = true ) {
        ob_start();
        if( !empty( $vars ) )
            extract( $vars );
        
        include dirname( __FILE__ ) . '/templates/' . $name . '.php';
        
        $data = ob_get_clean();
        
        if( $echo )
            echo $data;
        else
            return $data;
    }
}

Uploads::init();

?>