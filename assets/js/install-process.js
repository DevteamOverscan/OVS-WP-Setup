// const INSTALL_URL = "/mu-plugins/ovs/install/install.php";
const SELECTORS = {
	INSTALL_MODAL: ".install-modal",
	INSTALL_WRAPPER: ".install-modal .wrapper",
	LIST_ITEMS: ".install-modal .wrapper .list p",
};
if (document.querySelector(SELECTORS.INSTALL_MODAL)) {
	document
		.querySelector(SELECTORS.INSTALL_MODAL + " .install")
    .addEventListener("click", configChoice);
	document
		.querySelector(SELECTORS.INSTALL_MODAL)
    .addEventListener("submit", async (e) => {
      e.preventDefault();
      if (e.target && e.target.id === "form-config") {
				try {
					await progressInstall(e);
					checkInstall();
				} catch (error) {
					console.error(
						"Une erreur s'est produite lors de l'installation :",
						error
					);
				}
			}
		});
}
function configChoice() {
	const installWrapper = document.querySelector(SELECTORS.INSTALL_WRAPPER);
	const html = `
        <div>
            <h1>Pamramètres & sélections des éléments à mettre en place</h1>
						<form action="" id="form-config">
							<div class="theme">
								<h3>Thème Enfant</h3>
								<label htmlFor="theme">Indiquer l'identifiant du thème parent auquel lier le thème enfant. <sup>*</sup></label>
								<input type="text" id="theme" name='theme' required placeholder="identifiant du theme parent" />
								<p>L'identifiant du thème parent correspond au nom du dossier du theme que vous pouvez trouver dans /wp-content/themes. Généralement il est en minuscule et ne comporte pas d'espace, ni de - ou de _ et pas d'accents. Exemeple Twenty twenty four = twentytwentyfour</p>
							</div>
              <div class="grid">
                <div>
                <h3>Liste des plugins qui vont être installés</h3>
                <ul>
                  <li><b>Contact Form 7 :</b> gestionnaire de formulaire</li>
                  <li><b>Resumh it :</b> optimise et compresse les images</li>
                  <li><b>WebP Converter :</b> Convertit les .jpg, .jpeg, .png au format .webp pour optimiser le poids des images</li>
                  <li><b>SEO press :</b> indicateur des bonne pratique SEO et permet une configuration de base pour optimiser le référencement du site.</li>
                  <li><b>Two factor :</b> Connexion à l'admin par double authentification.</li>
                </ul>
                </div>
                <div>
                  <h3>Sélectionnez parmis la liste les fonctionnalités que vous souhaitez mettre en place</h3>
                  <label htmlFor="comments">
                    <input type="checkbox" id="comments" name="features[]" value="remove-comments" id="" /> Supprime la possibilité d'ajouter des commentaires. Si les commentaires ne sont pas utilisés sur le site il est recommandé d'activer cette fonctionnalité pour gagner en sécurité. 
                  </label>
                  <label htmlFor="cookie">
                    <input type="checkbox" name="features[]" value="tarte-au-citron" id="cookie" /> Activer le gestionnaire de cookie Tarte Au Citron.
                  </label>
                </div>
              </div>
							<button type="submit" class="submit-config">Installer</button>
						</form>
        </div>`;
	installWrapper.innerHTML = html;
}
async function progressInstall(event) {
	clearInstallWrapper();
	progressBar();
	await executeActions(event);
}

function clearInstallWrapper() {
	const installWrapper = document.querySelector(SELECTORS.INSTALL_WRAPPER);
	while (installWrapper.firstChild) {
		installWrapper.removeChild(installWrapper.firstChild);
	}
}

function progressBar() {
	const installWrapper = document.querySelector(SELECTORS.INSTALL_WRAPPER);
	const html = `
        <div id="progress-bar">
            <h1>Installation des éléments de base</h1>
            <div class="progress-bar">
                <div id="progress" style="width: 0%;"></div>
            </div>
            <div class="list">
                <p class="theme"><span class="icon-spinner"></span> Theme enfant</p>
                <p class="security"><span class="icon-spinner"></span> Règles de sécurités</p>
                <p class="plugins"><span class="icon-spinner"></span> Plugins de base</p>
                <p class="features"><span class="icon-spinner"></span> Fonctionnalités</p>
            </div>
        </div>`;
	installWrapper.innerHTML = html;
}

function executeActions(event) {
	const actions = ["theme", "security", "plugins","features"];
	const lengthProgress = 100 / actions.length;
	let progress = 0;

	return Promise.all(
		actions.map((action) => {
			return loadFunction(event,action)
				.then(() => {
					successLoadFunction(action);
					progress += lengthProgress;
					updateProgress(progress);
				})
				.catch((error) => {
					console.error(`Erreur lors de l'action "${action}":`, error);
					errorLoadFunction(action);
				});
		})
	);
}

function loadFunction(event,action) {
	var formData = new FormData(event.target); // Récupérer les données du formulaire

	// Créer une nouvelle instance de URLSearchParams
	var params = new URLSearchParams();

	// Parcourir les entrées de l'objet FormData et les ajouter à URLSearchParams
	for (const [key, value] of formData.entries()) {
		params.append(key, value);
	}

	// Ajouter action et function à URLSearchParams
	params.append("action", "install_ajax");
	params.append("function", action);
	return fetch(ajaxurl, {
		method: "POST",
		headers: {
			"Content-Type": "application/x-www-form-urlencoded",
		},
		body: params.toString(),
	})
		.then((response) => {
			if (response.ok) {
				return response.json();
			} else {
				return response.json().then((err) => {
					throw new Error(
						`Échec de la requête AJAX: ${response.status} ${
							response.statusText
						} - ${err.message || JSON.stringify(err)}`
					);
				});
			}
		})
		.then((data) => {
			if (data.status === "success") {
				return data;
			} else {
				throw new Error(data.message);
			}
		});
}

function updateProgress(progress) {
	document.querySelector(".wrapper #progress").style.width = progress + "%";
}

function successLoadFunction(target) {
	document
		.querySelector(`${SELECTORS.LIST_ITEMS}.${target} span`)
		.classList.replace("icon-spinner", "icon-check-circle1");
}

function errorLoadFunction(target) {
	document
		.querySelector(`${SELECTORS.LIST_ITEMS}.${target} span`)
		.classList.replace("icon-spinner", "icon-close-outline");
}

// Fonction de test pour vérifier les éléments installés
function checkInstall() {
	const itemsInstalled = document.querySelectorAll(
		SELECTORS.LIST_ITEMS + " .icon-check-circle1"
	);
	const totalItems = document.querySelectorAll(SELECTORS.LIST_ITEMS).length;

	if (itemsInstalled.length === totalItems) {
		removeInstallProcess();
		document.querySelector(SELECTORS.INSTALL_MODAL).classList.add("remove");
		setTimeout(() => {
			document.querySelector(SELECTORS.INSTALL_MODAL).remove();
		}, 520);

		return;
	} else {
		throw new Error("Certains éléments n'ont pas été installés.");
	}
}

async function removeInstallProcess() {
	console.log("remove");
	try {
		let response = await fetch(ajaxurl, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
			},
			body: new URLSearchParams({
				action: "install_ajax",
				function: "remove",
			}),
		});

		if (!response.ok) {
			throw new Error("Network response was not ok");
		}

		let result = await response.json();

		if (result.success) {
			// Déconnexion réussie, rediriger vers la page d'accueil
			window.location.href = "/";
		} else {
			console.error("Error from server:", result);
		}
	} catch (error) {
		console.error("Fetch error:", error);
	}
}

