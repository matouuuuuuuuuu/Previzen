<footer>
        <span><a href="#top" id="backToTop" title="Retour en haut">⬆</a></span>
        <span data-email="mathis.albrun@etu.cyu.fr">Mathis Albrun</span>
        <span data-email="imene.khelil@etu.cyu.fr">Imène Khelil</span>
        <div style="text-align:center">
            <?php
                require_once './include/functions.inc.php';
                $visites = compter_visites();
                echo "<p>Nombre de visites : $visites</p>";
            ?>
        </div>

        

        <span>CYU TECH CERGY</span>
        <span><a href="tech.php">Page tech</a></span>
		<span><a href="#" onclick="manageCookies()">Gérer les cookies</a></span>
        <span><?php echo date("d/m/Y");?></span>
		<div id="cookie-banner" class="cookie-banner" style="display: none;">
    		<p>Ce site utilise des cookies pour améliorer l’expérience utilisateur.</p>
    		<div class="cookie-buttons">
        		<button id="accept-cookies">Accepter</button>
        		<button id="refuse-cookies">Refuser</button>
    		</div>
		</div>


    </footer>
    </main>
<script>
	function manageCookies() {
		localStorage.removeItem("cookiesAccepted");
		document.getElementById("cookie-banner").style.display = "flex";
	}

	document.addEventListener("DOMContentLoaded", () => {
		const banner = document.getElementById("cookie-banner");
		const acceptBtn = document.getElementById("accept-cookies");
		const refuseBtn = document.getElementById("refuse-cookies");

		const accepted = localStorage.getItem("cookiesAccepted");

		if (!accepted) {
			banner.style.display = "flex";
		}

		acceptBtn.addEventListener("click", () => {
			localStorage.setItem("cookiesAccepted", "yes");
			banner.style.display = "none";
		});

		refuseBtn.addEventListener("click", () => {
			localStorage.setItem("cookiesAccepted", "no");
			banner.style.display = "none";
		});
	});
</script>
<script>
function openDressAdvice() {
    const modal = document.getElementById("dressModal");
    const advice = document.getElementById("dress-advice-text");

    // Ferme le menu latéral car ca beug je ne sais pourquoi
    const toggle = document.getElementById("sidebar-toggle");
    if (toggle) toggle.checked = false;

    modal.style.display = "flex";
    const ville = "<?php echo $_COOKIE['ville'] ?? 'Paris'; ?>";

    fetch("advice.php?ville=" + encodeURIComponent(ville))
        .then(response => response.text())
        .then(text => {
            advice.innerHTML = text;
        });
}

function closeDressAdvice() {
    document.getElementById("dressModal").style.display = "none";
}

// si click a l'exterieur ca ferme la fenetre
document.addEventListener("click", function(event) {
    const modal = document.getElementById("dressModal");
    if (modal.style.display === "block" && !modal.contains(event.target) && !event.target.closest("a[onclick*='openDressAdvice']")) {
        closeDressAdvice();
    }
});

// ca marche si on ferme avec echap
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closeDressAdvice();
    }
});
</script>

    </body>
</html>