/* Afficher une alerte si l'accès à l'administration a été refusé. */
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has("access_denied")) {
  alert("Vous n'avez pas pu accéder à l'admin.");
}
