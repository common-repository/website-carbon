<?php

// Exit if accessed directly
if(! defined('ABSPATH') ) {
    exit;
}

$untestedPosts = self::getPosts([
    'processed' => false
]);

?><div class="wrap">
    <h1><?php _e('Website Carbon emissions', 'website-carbon'); ?></h1>

    <div class="websitecarbon">
        <div class="websitecarbon-cell">
            <h2 class="websitecarbon-cell__header">
                <?php _e('Welcome to Website Carbon for WordPress.', 'website-carbon'); ?>
            </h2>

            <p>
                <?php _e('The internet consumes a lot of electricity. 416.2TWh per year to be precise. To give you some perspective, that’s more than the entire United Kingdom.', 'website-carbon'); ?>
            </p>

            <p>
                <?php _e('From data centres to transmission networks to the devices that we hold in our hands, it is all consuming electricity, and in turn producing carbon emissions.', 'website-carbon'); ?>
            </p>

            <p>
                <?php _e('Only by measuring the emissions of our web pages can we be informed about the impact that they are having. This plugin, powered by the Website Carbon project, allows for measuring and reporting on the carbon emissions of your site, so you to can understand the impact, and ultimately, reduce the emissions.', 'website-carbon'); ?>
            </p>
        </div>

        <div class="websitecarbon-cell">
            <h2 class="websitecarbon-cell__header">
                <?php _e('Renewable energy hosting', 'website-carbon'); ?>
            </h2>
            <?php if((bool) get_option('websitecarbon-green') === true) { ?>
                <p>
                    <?php _e('Yes', 'website-carbon'); ?>
                </p>
                <p>
                    <?php _e('It appears your site is powered by renewable energy', 'website-carbon'); ?>
                </p>
            <?php }else { ?>
                <p>
                    <?php _e('No', 'website-carbon'); ?>
                </p>
                <p>
                    <?php _e('It appears your site is not powered by renewable energy.', 'website-carbon'); ?>
                </p>
            <?php } ?>

        </div>
        <div class="websitecarbon-cell">
            <h2 class="websitecarbon-cell__header">
                <?php _e('Download as a CSV', 'website-carbon'); ?>
            </h2>
            <p>
                <?php _e('The results for all pages on your site can be downloaded as a CSV by clicking the link below.', 'website-carbon'); ?>
            </p>
            <p>
                <a class="button" href="<?php echo esc_url(self::downloadAsCSVLink()); ?>">
                    <?php _e('Download', 'website-carbon'); ?>
                </a>
            </p>
        </div>
        <div class="websitecarbon-cell">
            <h2 class="websitecarbon-cell__header">
                <?php _e('Bulk Process', 'website-carbon'); ?>
            </h2>

            <p>
                <?php _e('Bulk processing will measure the carbon emissions for all the public facing pages of your site.', 'website-carbon'); ?>
            </p>

            <p>
                <?php _e('You will need to keep this page open for the tests to run.', 'website-carbon'); ?>
            </p>

            <p>
                <?php _e('Warning: Each post is given 30 seconds to complete, so this could take some time.', 'website-carbon'); ?>
            </p>

            <p>
                <button class="js-wc-measure button" data-processed="">
                    <?php _e('Test all pages', 'website-carbon'); ?>
                </button>

                <?php if(count($untestedPosts) > 0){ ?>
                    <button class="js-wc-measure button" data-processed="false">
                        <?php _e('Test required pages', 'website-carbon'); ?>
                    </button>
                <?php } ?>
            </p>

            <div class="websitecarbon-batch js-wc-batch">
                <h3 class="websitecarbon-batch__heading js-wc-batch-heading">
                    <?php _e('Measuring', 'website-carbon'); ?>: <span class="js-wc-batch-heading-progress"></span>
                </h3>

                <div class="websitecarbon-batch__bars js-wc-batch-bars">
                    <div class="websitecarbon-batch__bar">

                        <label for="wc-total-progress" class="websitecarbon-batch__bar-heading">
                            <?php _e('Total', 'website-carbon'); ?>:
                        </label>

                        <div class="websitecarbon-batch__bar-progress">
                            <div id="wc-total-progress" class="websitecarbon-progress js-wc-total-progress">
                                <span></span>
                            </div>
                        </div>
                    </div>

                    <div class="websitecarbon-batch__bar">
                        <label for="wc-post-progress" class="websitecarbon-batch__bar-heading">
                            <?php _e('Post', 'website-carbon'); ?>: <span class="js-wc-post-progress-text"></span>
                        </label>

                        <div class="websitecarbon-batch__bar-progress">
                            <div id="wc-post-progress" class="websitecarbon-progress websitecarbon-progress--timer js-wc-post-progress">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="websitecarbon-batch__message js-wc-message"></p>
            </div>

        </div>
    </div>
</div>
