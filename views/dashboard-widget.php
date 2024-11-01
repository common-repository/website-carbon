<?php

if(!empty($posts)){
    ?><table class="widefat fixed" cellspacing="0">
        <tr>
            <th>
                <?php _e('Post', 'website-carbon'); ?>
            </th>
            <th>
                <?php _e('Emissions', 'website-carbon'); ?>
            </th>
        </tr>
        <?php foreach ($posts as $key => $mypost) { ?>
            <tr>
                <td class="column-columnname">
                    <?php echo esc_html($mypost['title']); ?> |

                    <a href="<?php echo esc_url(get_permalink($mypost['id'])); ?>">
                        <?php _e('View', 'website-carbon'); ?>
                    </a>
                    |
                    <a href="<?php echo esc_url(get_edit_post_link($mypost['id'])); ?>">
                        <?php _e('Edit', 'website-carbon'); ?>
                    </a>
                </td>
                <td class="column-columnname">
                    <?php echo esc_html(self::getEmissionsValue($mypost['id'])); ?>
                </td>
            </tr>
        <?php } ?>
    </table><?php
} else {
    _e('Please test some pages to see the results', 'website-carbon');
}

