// ✅ Tableau des messages à afficher au survol
const tooltips = [
  "Il assure la direction générale du bureau et veille à la bonne exécution des projets. C’est lui qui représente officiellement le BDE auprès des instances et partenaires.",
  "Il soutient le président et prend en charge la coordination de certains pôles. Il veille à la bonne circulation des informations et au bon fonctionnement global du BDE.",
  "Il est responsable de la gestion administrative et de la rédaction des comptes-rendus. Il assure le suivi régulier des projets et la bonne documentation du bureau.",
  "Elle appuie le secrétaire générale dans toutes ses missions. Elle peut le remplacer en cas d’indisponibilité et assure une continuité administrative.",
  "Elle gère les budgets, les entrées et sorties d’argent, et élabore les bilans financiers. Elle veille à la viabilité économique des projets du BDE.",
  "Elle contrôle les finances, vérifie les justificatifs et la bonne utilisation des fonds. Elle garantit la transparence et la conformité financière du BDE.",
  "Il conçoit les messages, anime les réseaux sociaux et prépare les campagnes d’information. Il organise et coordonne les événements étudiants tout au long de l’année.",
  "Il soutient le responsable principal dans la mise en œuvre des actions de communication et d'événementiel. Il contribue activement à la promotion des activités du BDE.",
  "Il développe les collaborations avec les entreprises, les associations et les autres BDE. Il cherche des opportunités de visibilité et de soutien financier.",
  "Elle accompagne la recherche de partenaires et la gestion des relations extérieures. Elle participe aux négociations et assure le suivi des engagements.",
  "Il planifie et anime les activités sportives du BDE : tournois, clubs, événements. Il contribue à créer une ambiance saine et dynamique à travers le spor",
  "Il soutient l’organisation des activités sportives et aide à mobiliser les étudiants. Il veille à la bonne tenue des événements et assure la relève si besoin."
];

// ✅ Fonction d'initialisation
document.addEventListener("DOMContentLoaded", () => {
  const elements = document.querySelectorAll('.hover-p');

  elements.forEach(el => {
    const index = el.getAttribute("data-index");
    el.setAttribute("data-bs-toggle", "tooltip");
    el.setAttribute("data-bs-placement", "bottom");
    el.setAttribute("title", tooltips[index]);
  });

  // 🧠 Initialiser les tooltips Bootstrap
  const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipList.forEach(el => new bootstrap.Tooltip(el));
});