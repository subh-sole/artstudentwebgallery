function disableRightClick(event) {
    event.preventDefault();
}
// Attach the event listener to the document
document.addEventListener('contextmenu', disableRightClick);

// Function to toggle the menu on mobile devices
function toggleMenu() {
    var menu = document.querySelector('nav ul');
    menu.classList.toggle('show');
}

// Function to make the menu follow as you scroll down
function followMenu() {
    var header = document.querySelector('header');
    var headerHeight = header.offsetHeight;
    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    if (scrollTop > headerHeight) {
        header.classList.add('sticky');
    } else {
        header.classList.remove('sticky');
    }
}

// JavaScript code here
function scrollToBottom() {
    var footerSection = document.querySelector('footer');
    footerSection.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }

