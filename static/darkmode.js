var darkMode = localStorage.getItem('darkMode')
if(darkMode == null){
    localStorage.setItem('darkMode', "light")
    darkMode = "light"
}

const themes = {
    "dark": {
        "--main-bg-color": "#333",
        "--secondary-bg-color": "#666",
        "--main-fg-color": "#fcfcfc",
        "--secondary-fg-color": "#ddd",
        "--accent-color": "rgb(211, 136, 255)",
        "--accent-color-darker": "rgb(149, 96, 180)"
    },
    "light": {
        "--main-bg-color": "#e1e1e1",
        "--secondary-bg-color": "#fff",
        "--main-fg-color": "#333",
        "--secondary-fg-color": "#888",
        "--accent-color": "rgb(149, 96, 180)",
        "--accent-color-darker": "rgb(211, 136, 255)"
    },
};

function toggleDarkMode(){
    darkMode = localStorage.getItem('darkMode')
    if(darkMode == "dark"){
        localStorage.setItem('darkMode', "light")
    }else{
        localStorage.setItem('darkMode', "dark")
    }
    darkMode = localStorage.getItem('darkMode')
    applyDarkMode()
}
function applyDarkMode(){
    theme = themes[darkMode];
    for (var variable in theme) {
        document.documentElement.style.setProperty(variable, theme[variable]);
    };

    if (darkMode == "light"){
        document.getElementById("sun-icon").classList.remove("hidden");
        document.getElementById("moon-icon").classList.add("hidden");

        document.body.classList.add("light");
        document.body.classList.remove("dark");
    }
    else {
        document.getElementById("moon-icon").classList.remove("hidden");
        document.getElementById("sun-icon").classList.add("hidden");

        document.body.classList.add("dark");
        document.body.classList.remove("light");
    }
}
window.onload = function() {
    document.body.style.setProperty("transition", "color 0.2s linear 0s, background-color 0.2s linear 0s");
};
applyDarkMode()