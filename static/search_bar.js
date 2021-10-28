function search_bar() {
    //fonction cachant les joueurs qui n'ont pas le nom
    //en compatibilité avec l'input de recherche

    // Déclare variables utilisé
    var input, filter, ul, li, elemWithTxt, txtValue;
    input = document.getElementById('search');
    filter = input.value.toUpperCase();
    ul = document.getElementById("ul");
    li = ul.getElementsByClassName('elem');
    
    // Boucle à travers tout les elems dans le li, 
    //et cache tout ceux donc le texte ne match pas le texte dans le input
    for (i = 0; i < li.length; i++) {
        elemWithTxt = li[i].getElementsByTagName("span")[0];
        txtValue = elemWithTxt.textContent || elemWithTxt.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
function search_bar_and_date() {
    //fonction cachant les parties qui n'ont pas le nom
    //en compatibilité avec l'input de recherche
    //ET les dates non présente dans l'interval

    // Déclare variables utilisé
    var input, filter, date1, date2, ul, li, name_html, date_html, i, txtValue, dateValue, date;

    input = document.getElementById('search');
    filter = input.value.toUpperCase();
    
    date1 = new Date(document.getElementById('start').value);   
    date1.setDate(date1.getDate() - 1); //décale d'un jour en arrière
    date2 = new Date(document.getElementById('end').value);
    ul = document.getElementById("ul");
    li = ul.getElementsByClassName('elem');
    
    // Boucle à travers tout les elems dans le li, 
    // et cache tout ceux donc le texte ne match pas le texte dans le input
    // ET dont la date nest pas dans l'interval des deux dates
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