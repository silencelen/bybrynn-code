

// (function ($) {
//     "use strict";

//     $(window).load(function () {
//         var $container = $('#fh5co-projects-feed'),
//             containerWidth = $container.outerWidth();

//         $container.masonry({
//             itemSelector: '.fh5co-project',
//             columnWidth: function (containerWidth) {
//                 if (containerWidth <= 330) {
//                     return 310;
//                 } else {
//                     return 330;
//                 }
//             },

//             isAnimated: !Modernizr.csstransitions

//         });



//     });
   
// })(window.jQuery);




var dropdownVisible = false;
let isSelectedSort = false;
var selectedOptionElement = document.getElementById("selectedOption");
var projectsFeed = document.getElementById("fh5co-projects-feed");
var projectElements = Array.from(projectsFeed.getElementsByClassName("fh5co-project"));
if (selectedOptionElement.textContent.trim() === "") {
    selectedOptionElement.style.display = "none";
}

function toggleDropdown() {
    var dropdown = document.getElementById("sortingDropdown");
    dropdown.style.display = dropdownVisible ? "none" : "block";
    dropdownVisible = !dropdownVisible;
}


function sortBy(option) {

    // Update the isSelectedSort option display
    isSelectedSort = true;
    selectedOptionElement.style.display = "";

    let closeOutline = "close-outline";

    selectedOptionElement.innerHTML = `${option} <ion-icon name="${closeOutline}"></ion-icon>`;



    // Shuffle animation class
    const shuffleAnimationClass = "shuffle-animation";

    // Sorting based on the selected option
    if (option == "Newest") {
        projectElements.sort((a, b) => {
            const dateA = new Date(a.getAttribute("data-date"));
            const dateB = new Date(b.getAttribute("data-date"));
            return dateB - dateA;
        });
    } else if (option == "Oldest") {
        projectElements.sort((a, b) => {
            const dateA = new Date(a.getAttribute("data-date"));
            const dateB = new Date(b.getAttribute("data-date"));
            return dateA - dateB;
        });
    } else if (option == "Name") {
        projectElements.sort((a, b) => {
            const nameA = a.querySelector("img").getAttribute("alt").toLowerCase();
            const nameB = b.querySelector("img").getAttribute("alt").toLowerCase();
            return nameA.localeCompare(nameB);
        });
    }

    // Close the dropdown after selecting an option
    toggleDropdown();

    // Apply shuffle animation class
    projectsFeed.classList.add(shuffleAnimationClass);

    // Remove existing elements from the DOM after a short delay
    setTimeout(() => {
        projectElements.forEach((project) => {
            projectsFeed.removeChild(project);
        });

        // Remove shuffle animation class
        projectsFeed.classList.remove(shuffleAnimationClass);

        // Append the sorted elements back to the DOM
        projectElements.forEach((projectElement) => {
            projectsFeed.appendChild(projectElement);
        });




    }, 500); // Adjust the delay as needed
}


// const resetSorting = () => {
// 	if (isSelectedSort) {
// 		selectedOptionElement.style.display = "none";
// 		isSelectedSort = false;
// 	}
// }
const resetSorting = () => {
    if (isSelectedSort) {
        // Remove sorting animation class
        projectsFeed.classList.remove("shuffle-animation");

        // Remove existing elements from the DOM after a short delay
        setTimeout(() => {
            projectElements.forEach((project) => {
                projectsFeed.removeChild(project);
            });

            // Append the original order of elements back to the DOM
            originalOrder.forEach((projectElement) => {
                projectsFeed.appendChild(projectElement);
            });

            // Hide the sorting display
            selectedOptionElement.style.display = "none";
            isSelectedSort = false;
        }, 500); // Adjust the delay as needed
    }
}

// Save the original order of elements before any sorting
const originalOrder = Array.from(projectElements);



