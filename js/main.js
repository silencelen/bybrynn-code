window.onload = function() {
    var dropdowns = document.querySelectorAll('#fh5co-header nav ul li');

    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            if (this.querySelector('.dropdown-menu')) {
                event.preventDefault();
                var dropdownMenu = this.querySelector('.dropdown-menu');
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            }
        });

        var dropdownLinks = dropdown.querySelectorAll('.dropdown-menu li a');
        dropdownLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    });

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
};
