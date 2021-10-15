<header>
    <div class="container">
        <div class="flex">
            <span class="left">
                <h2 class="logo">CSGO Stats</h2>
            </span>
            <span class="center">
                <ul class="links">
                    <li class="link"><a href="./">Accueil</a></li>
                    <li class="link"><a href="./joueurs.php">Joueurs</a></li>
                    <li class="link"><a href="./parties.php">Parties</a></li>
                </ul>
            </span>
            <span class="right">
                <button onclick="toggleDarkMode()">toggle</button>
                <script>  
                var darkMode = localStorage.getItem('darkMode')
                if(darkMode == null){
                    localStorage.setItem('darkMode', "false")
                    darkMode = "false"
                }
                function toggleDarkMode(){
                    darkMode = localStorage.getItem('darkMode')
                    if(darkMode == "true"){
                        localStorage.setItem('darkMode', "false")
                    }else{
                        localStorage.setItem('darkMode', "true")
                    }
                    var style = document.createElement('style');
                    document.head.appendChild(style);
                    if(darkMode == "true"){
                        style.sheet.insertRule(":root {--main-bg-color: #333;--secondary-bg-color: #666;--main-fg-color: #fcfcfc;--secondary-fg-color: #ddd;--accent-color: rgb(211, 136, 255);--accent-color-darker: rgb(149, 96, 180);}");
                    }else{
                        style.sheet.insertRule(":root {--main-bg-color: #fff;--secondary-bg-color: #eee;--main-fg-color: #333;--secondary-fg-color: #ccc;--accent-color: rgb(211, 136, 255);--accent-color-darker: rgb(149, 96, 180);}");
                    }
                }
                
                </script>
            </span>
        </div>
    </div>
</header>