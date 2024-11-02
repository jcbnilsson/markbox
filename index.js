function is_scrollable(el) { /* https://www.geeksforgeeks.org/how-to-check-if-a-scrollbar-is-visible/ */
    var y1 = el.scrollTop;
    el.scrollTop += 1;
    var y2 = el.scrollTop;
    el.scrollTop -= 1;
    var y3 = el.scrollTop;
    el.scrollTop = y1;

    var x1 = el.scrollLeft;
    el.scrollLeft += 1;
    var x2 = el.scrollLeft;
    el.scrollLeft -= 1;
    var x3 = el.scrollLeft;
    el.scrollLeft = x1;

    return {
        h: x1 !== x2 || x2 !== x3,
        v: y1 !== y2 || y2 !== y3
    }
}

window.onload = function() {
    var footer = document.getElementById("footer");

    footer.style.display = "none";

    if (is_scrollable(document.documentElement)) {
        footer.style.display = "block";
    }
}

window.onscroll = function() {
    var footer = document.getElementById("footer");
    var scroll_pos = window.pageYOffset || document.documentElement.scrollTop;

    if (document.getElementById("content").offsetHeight <= window.innerHeight + scroll_pos) {
        footer.style.display = "block";
    } else {
        footer.style.display = "none";
    }

    var menu = document.getElementById("bar_menu");
    var title = document.getElementById("bar_title");

    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        menu.style.display = "none";
        title.style.display = "none";
    } else {
        menu.style.display = "block";
        title.style.display = "block";
    }
}
