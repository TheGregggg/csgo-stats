function search_bar() {
    // Declare variables
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById('search');
    filter = input.value.toUpperCase();
    ul = document.getElementById("ul");
    li = ul.getElementsByClassName('elem');
    
    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("span")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
function search_bar_and_date() {
    // Declare variables
    var input, filter, date1, date2, ul, li, name_html, date_html, i, txtValue, dateValue, date;

    input = document.getElementById('search');
    filter = input.value.toUpperCase();
    
    date1 = new Date(document.getElementById('start').value);   
    date1.setDate(date1.getDate() - 1);
    date2 = new Date(document.getElementById('end').value);
    ul = document.getElementById("ul");
    li = ul.getElementsByClassName('elem');
    
    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < li.length; i++) {
        name_html = li[i].getElementsByTagName("span")[0];
        txtValue = name_html.textContent || name_html.innerText;

        date_html = li[i].getElementsByClassName("date")[0];
        dateValue = date_html.textContent || date_html.innerText;
        
        date = new Date(dateValue);
        if (date.valueOf() <= date2.valueOf() && date.valueOf() >= date1.valueOf() && txtValue.toUpperCase().indexOf(filter) > -1 ) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}