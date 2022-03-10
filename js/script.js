// Hamburger Button

// Referenser till element
const hamburgerBtn = document.getElementById("hamburger");
const navList = document.querySelector(".nav-list");

// När hamburgarknappen trycks på så ska den rotera 90 grader och visa navigationslänkarna. När man klickar återigen så ska menyn döljas och knappen ska rotera tillbaks. Detta sker genom att lägga till och ta bort CSS-klasser. 
hamburgerBtn.addEventListener("click", ()=> {
  hamburgerBtn.classList.toggle("rotate");
  navList.classList.toggle("show-nav");
});