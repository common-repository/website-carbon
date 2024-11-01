jQuery(document).ready(function($) {
    // Internal state tracker
    var state = {
        processedMode: '',
        page: 0,
        total: 0,
        completed: 0
    };

    // Little wrapper for all the element we work with
    var $element = {
        startButton: $(".js-wc-measure"),
        batch: $('.js-wc-batch'),
        batchHeading: $('.js-wc-batch-heading'),
        counter: $(".js-wc-batch-heading-progress"),
        progressBars: $(".js-wc-batch-bars"),
        progressTotal: $(".js-wc-total-progress span"),
        progressPost: $(".js-wc-post-progress span"),
        progressPostText: $('.js-wc-post-progress-text'),
        message: $(".js-wc-message")
    }

    $element.startButton.click(function(event) {
        event.preventDefault();

        // Make sure the batch button cant be clicked again
        $element.startButton.attr('disabled', true);

        // Show the batchProcessor
        $element.batch.addClass('active');

        state.processedMode = $(this).data('processed');

        getTotal();
    });

    function getTotal() {
        $.ajax({
            method: 'POST',
            url: window.websitecarbonvars.ajax,
            data: {
                action: 'websitecarbon-total',
                nonce: window.websitecarbonvars.nonce,
                processed: state.processedMode
            },
            success: (response) => {
                state.total = response.total;

                if(state.total > 0) {
                    // Update the title of the batch processor
                    $element.counter.html("0 / " + state.total);

                    // Show the bars now that there is something to do
                    $element.progressBars.show();

                    getPosts(response.nonce);

                }else {
                    $element.batchHeading.html('All done, nothing to do.').show();
                    $element.progressBars.hide();
                    $element.message.hide();
                }
            }
        });
    }

    function getPosts(nonce) {
        $.ajax({
            method: 'POST',
            url: window.websitecarbonvars.ajax,
            data: {
                action: 'websitecarbon-posts',
                nonce: nonce,
                pagination: state.page,
                processed: state.processedMode
            },
            success: (response) => {
                // Now that we have made a request, lets bump the page for the future
                state.page++;

                if(response.posts.length > 0){
                    batchProcess(response.posts, response.nonce);
                }else {
                    $element.batchHeading.html('All done, nothing more to measure').show();
                    $element.progressBars.hide();
                    $element.message.hide();
                }
            }
        });
    }

    function batchProcess(posts, nonce) {
        if(posts.length > 0) {
            // Shift does two things
            // - stores the first item in next
            // - removes the first item from posts
            var next = posts.shift();

            calculateEmissions(next, nonce, posts);
        } else {
            // Since we are out of this batch of posts, attempt to get another
            getPosts(nonce);
        }
    }

    function calculateEmissions(post, nonce, posts) {

        $element.progressPostText.html(post.title);

        // Reset the post processor to 0 as we need to start afresh
        $element.progressPost.stop().css('width', 0);

        $element.progressPost.animate({
            width: '100%'
        }, 20000); // Tests most likely wont take 30 seconds so 20 is a nice progress

        $.ajax({
            method: 'POST',
            url: window.websitecarbonvars.ajax,
            data: {
                action: 'websitecarbon-measure',
                nonce: nonce,
                id: post.id
            },
            success: (response) => {

                state.completed++;

                // update the batch progress counter
                $element.progressTotal.css('width', decimalToPercent(state.completed / state.total));

                // update the title total
                $element.counter.html(
                    state.completed + " / " + state.total
                );

                // update the message with
                if(response.error !== undefined) {
                    $element.message.html(post.title + ": " + response.error).show();
                }else {
                    $element.message.html(post.title + ": " + response.co2 + 'ppv').show();
                }

                // If we were passed a variable for untested, lets continue
                if(posts !== undefined) {
                    batchProcess(posts, response.nonce);
                }
            },
            error: () => {
                // We should probs just bail on the whole exercise
            }
        });
    }

    function decimalToPercent(decimal) {
        return Math.floor(decimal * 100) + "%";
    }
});
