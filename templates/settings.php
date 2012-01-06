<?php if( $flash ) { ?>
    <div id="message" class="updated fade">
        <p><strong><?php echo $flash; ?></strong></p>
    </div>
<?php } ?>
<div id="icon-tools" class="icon32"><br /></div>
<div class="wrap">
    <h2><?php _e( 'WordPress Uploads Settings','uploads' ); ?></h2>
    <div id="poststuff" class="metabox-holder">
        <div class="postbox">
            <h3 class="hndle" ><?php _e( 'Introduction','uploads' )?></h3>
            <div class="inside">
                <p>
                    <?php _e( 'This page will help you change some uploads settings like','uploads' )?>
                    <ul class="ul-disc">
                        <li><?php _e( 'disallow uploads','uploads' )?></li>
                        <li><?php _e( 'extend allowed file extensions','uploads' )?></li>
                        <li><?php _e( 'file path masking to hide real location of the files','uploads' )?></li>
                    </ul>
                </p>
            </div>
        </div>
        
        <div class="postbox">
            <h3 class="hndle" ><?php _e( 'Options','uploads' )?></h3>
            <div class="inside">
                <form action="" method="post">
                    <?php wp_nonce_field( 'uploads', 'uploads_nonce' ); ?>
                    <p>
                        <input type="checkbox" name="uploads[disable]" id="upload-disable" <?php checked( $uploads_disabled ); ?> />
                        <label for="upload-disable"><?php _e( 'Disallow uploads','uploads' )?></label>
                    </p>
                    <p>
                        <input type="checkbox" name="uploads[mask]" id="upload-mask" <?php checked( $uploads_mask ); ?> />
                        <label for="upload-mask"><?php _e( 'Mask upload location','uploads' )?></label>
                    </p>
                    <p class="form-field">
                        <label for="upload-extensions"><?php _e( 'Allowed extensions/mime types','uploads' )?></label>
                        <textarea id="upload-extensions" name="uploads[ext]" class="widefat" style="min-height: 200px;"><?php echo $uploads_ext ? $uploads_ext : ''; ?></textarea>
                        <br />
                        <em><small>
                            <?php _e( 'One per line.','uploads' )?>
                            <a href="http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types">
                                <?php _e( 'Full list of mime types','uploads' )?>
                            </a>
                        </small></em>
                    </p>
                    <p>
                        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' )?>"/>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
