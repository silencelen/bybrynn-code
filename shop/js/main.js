(function($){
	"use strict";

	$(window).load(function() {
		var $container = $('#fh5co-projects-feed'),
		containerWidth = $container.outerWidth();

		$container.masonry({
			itemSelector : '.fh5co-project',
			columnWidth: function( containerWidth ) {
				if( containerWidth <= 330 ) {
					return 310;
				} else {
					return 330;
				}
			},
			isAnimated: !Modernizr.csstransitions
		});
	});

})(window.jQuery);

document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('#fh5co-header nav ul li');

    dropdowns.forEach(function(dropdown) {
        // Only prevent default for parent menu items that have a dropdown
        dropdown.addEventListener('click', function(event) {
            if (this.querySelector('.dropdown-menu')) {
                event.preventDefault();

                // Toggle the dropdown menu visibility
                var dropdownMenu = this.querySelector('.dropdown-menu');
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            }
        });

        // Ensure clicking on links within the dropdown works as expected
        var dropdownLinks = dropdown.querySelectorAll('.dropdown-menu li a');
        dropdownLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                // Allow normal navigation for dropdown links
                event.stopPropagation(); // Prevent the parent click event from closing the dropdown
            });
        });
    });

    // Close the dropdown if clicked outside
    document.addEventListener('click', function(event) {
        dropdowns.forEach(function(dropdown) {
            if (!dropdown.contains(event.target)) {
                var dropdownMenu = dropdown.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.style.display = 'none';
                }
            }
        });
    });
});
