document.addEventListener('DOMContentLoaded', function() {
    var dropdowns = document.querySelectorAll('#fh5co-header nav ul li');

    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            // Prevent default link behavior
            if (this.querySelector('.dropdown-menu')) {
                event.preventDefault();

                // Toggle the dropdown menu visibility
                var dropdownMenu = this.querySelector('.dropdown-menu');
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            }
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
