<?php
/**
 * Template for themes list single item
 *
 * @uses $theme GDForm_Theme
 */

if( !isset( $theme ) ){
    throw new Exception( '"theme" variable is not reachable in themes-list-single-item template.' );
}

if( !( $theme instanceof GDForm_Theme ) ){
    throw new Exception( '"theme" variable must be instance of GDForm_Theme class.' );
}

$theme_id = $theme->get_id();

$edit_url = admin_url( 'admin.php?page=gdfrm_themes&task=edit_theme&id='.$theme_id );

$edit_url = wp_nonce_url( $edit_url, 'gdfrm_edit_theme_'.$theme_id );

$remove_url = admin_url( 'admin.php?page=gdfrm_themes&task=remove_theme&id='.$theme_id );

$remove_url = wp_nonce_url( $remove_url, 'gdfrm_remove_theme_'.$theme_id );

$duplicate_url = admin_url( 'admin.php?page=gdfrm_themes&task=duplicate_theme&id='.$theme_id );

$duplicate_url = wp_nonce_url( $duplicate_url, 'gdfrm_duplicate_theme_'.$theme_id );
?>
<tr>
    <td class="theme-id"><?php echo $theme_id; ?></td>
    <td class="theme-name"><a href="<?php echo $edit_url; ?>" ><?php echo esc_html( stripslashes( $theme->get_name() ) ); ?></a></td>
    <td class="theme-actions">
        <a class="gdfrm_edit_theme" href="<?php echo $edit_url; ?>" ><i class="fa fa-pencil" aria-hidden="true"></i></a>
        <a class="gdfrm_duplicate_theme" href="<?php echo $duplicate_url;?>"><i class="fa fa-files-o" aria-hidden="true"></i></a>
        <a class="gdfrm_delete_theme" href="<?php echo $remove_url; ?>" ><i class="fa fa-trash-o" aria-hidden="true"></i></a>
    </td>
</tr>
