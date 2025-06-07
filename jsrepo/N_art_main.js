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

    isSelectedSort = true;
    selectedOptionElement.style.display = "";

    let closeOutline = "close-outline";

    selectedOptionElement.innerHTML = `${option} <ion-icon name="${closeOutline}"></ion-icon>`;



    const shuffleAnimationClass = "shuffle-animation";

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

    toggleDropdown();

    projectsFeed.classList.add(shuffleAnimationClass);

    setTimeout(() => {
        projectElements.forEach((project) => {
            projectsFeed.removeChild(project);
        });

        projectsFeed.classList.remove(shuffleAnimationClass);

        projectElements.forEach((projectElement) => {
            projectsFeed.appendChild(projectElement);
        });




    }, 500);
}
const resetSorting = () => {
    if (isSelectedSort) {
        projectsFeed.classList.remove("shuffle-animation");

        setTimeout(() => {
            projectElements.forEach((project) => {
                projectsFeed.removeChild(project);
            });

            originalOrder.forEach((projectElement) => {
                projectsFeed.appendChild(projectElement);
            });

            selectedOptionElement.style.display = "none";
            isSelectedSort = false;
        }, 500);
    }
}

const originalOrder = Array.from(projectElements);



