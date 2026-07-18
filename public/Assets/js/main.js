// CAROUSEL

const slides = document.querySelectorAll('.carousel-slide');
let index = 0;

setInterval(() => {
  slides[index].classList.remove('active');
  index = (index + 1) % slides.length;
  slides[index].classList.add('active');
}, 4000);

// TOP NAVBAR traduction
const translations = {
  fr: {
    loginBtn: "Connexion",
    welcome: "Bienvenue sur le site du BDE !",
    description: "Ceci est la description de notre bureau des étudiants.",
    moreInfo: "En savoir plus"
  },
  en: {
    loginBtn: "Login",
    welcome: "Welcome to the Student Council site!",
    description: "This is the description of our student bureau.",
    moreInfo: "Learn more"
  }
};

function setLanguage(lang) {
  localStorage.setItem("lang", lang);
  applyTranslations(lang);
}

function applyTranslations(lang) {
  const elements = document.querySelectorAll("[data-i18n]");
  elements.forEach(el => {
    const key = el.getAttribute("data-i18n");
    el.textContent = translations[lang][key] || key;
  });

  // Met à jour l’état actif visuellement
  document.querySelectorAll(".lang-btn").forEach(btn => btn.classList.remove("active"));
  const activeBtn = [...document.querySelectorAll(".lang-btn")].find(btn => btn.textContent.toLowerCase() === lang);
  if (activeBtn) activeBtn.classList.add("active");
}

// Applique la langue enregistrée ou FR par défaut
window.addEventListener("DOMContentLoaded", () => {
  const lang = localStorage.getItem("lang") || "fr";
  setLanguage(lang);
});

// THEME

 const toggleBtn = document.getElementById("themeToggleBtn");
 const themeOptions = document.getElementById("themeOptions");

 toggleBtn.addEventListener("click", () => {
   themeOptions.style.display =
     themeOptions.style.display === "flex" ? "none" : "flex";
 });

 function changeTheme(theme) {
   document.body.className = ""; // Reset
   if (theme) document.body.classList.add(theme);
   themeOptions.style.display = "none"; // Re-cacher après sélection
 }

// TEXT SURVOL
 document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
