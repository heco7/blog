// Referenser till element
const btn = document.getElementById("show-img");
const imgContainer = document.querySelector(".user-images");

// Lägg till/ta bort klassen show-images när man trycker på knappen. Containern är dold som standard.
btn.addEventListener("click", function() {
  imgContainer.classList.toggle("show-images");
  if (btn.textContent == "Visa bilder") {
    btn.textContent = "Dölj bilder";
   } else {
     btn.textContent = "Visa bilder";
   }
});