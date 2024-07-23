// Vérifier si le paramètre access_denied est présent dans l'URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has("access_denied")) {
	alert("Vous n'avez pas pu accéder à l'admin.");
}
