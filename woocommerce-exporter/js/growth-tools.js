// Using jQuery to simplify the process
jQuery(document).ready(function ($) {
  // Function to filter cards based on the hash value
  function filterCardsByHash(hash) {
    var hrefValue = hash ? hash.replace('#', '') : 'by-our-team'; // Use 'by-our-team' as default if hash is empty
    $('.growth-tools-card').hide(); // Hide all cards initially

    // Show only the cards that match the data-tags of the hrefValue
    if (hrefValue) {
      var tags = hrefValue.split(',');
      tags.forEach(function (tag) {
        $(`.growth-tools-card[data-tags*="${tag.trim()}"]`).show();
      });
    }

    // Update active class for menu items
    $('.growth-tools-left-menu li a').removeClass('active'); // Remove active class from all menu items
    $(`.growth-tools-left-menu li a[href='#${hrefValue}']`).addClass('active'); // Add active class to the current item
  }

  // Event listener for menu item clicks
  $('.growth-tools-left-menu li a').on('click', function (e) {
    e.preventDefault(); // Prevent the default anchor link behavior
    var hrefValue = $(this).attr('href');
    filterCardsByHash(hrefValue); // Filter cards based on clicked item's href

    // Change the URL hash without reloading the page
    history.pushState(null, null, hrefValue);
  });

  // Check if there's a hash in the URL on page load and filter cards accordingly
  // If no hash is present, default to 'by-our-team'
  var initialHash = window.location.hash || '#by-our-team';
  filterCardsByHash(initialHash);

  // Click event listener for the install button
  $('.growth-tools-card .card-footer a.button').on('click', function (e) {
    e.preventDefault(); // Prevent the default button behavior

    // Check if the button is disabled. If it is, return early.
    if ($(this).data('disabled')) {
      return;
    }

    var $button = $(this); // Cache the button jQuery object
    var pluginSlug = $button.data('plugin-slug'); // Get the plugin slug from the data attribute
    var nonce = $('input[name="woo_ce_install_plugin"]').val(); // Get the nonce value

    // Disable the button and change its text
    $button.text('Installing...').data('disabled', true);
    $button.addClass('disabled');

    // Make the AJAX call to the backend
    $.ajax({
      url: ajaxurl, // Replace with the actual backend URL
      type: 'POST',
      data: {
        action: 'woo_ce_install_plugin',
        plugin_slug: pluginSlug,
        silent: true,
        nonce: nonce,
      },
      success: function (response) {
        // Check the response to determine if the action was successful.
        if (response.success) {
          // If successful, update the UI accordingly
          $button.parent().find('.install-status-value').text('Installed'); // Update the install status text
          $button.remove(); // Remove the install button
        } else {
          // If the action fails, revert the button text
          $button.text('Install Plugin').data('disabled', false);
          $button.removeClass('disabled');
          // Fail silently, so no further action is taken
        }
      },
      error: function () {
        // In case of an AJAX error, revert the button text
        $button.text('Install Plugin').data('disabled', false);
        $button.removeClass('disabled');
        // Fail silently
      },
    });
  });

  // Listen to hash change events
  $(window).on('hashchange', function () {
    filterCardsByHash(window.location.hash);
  });
});
