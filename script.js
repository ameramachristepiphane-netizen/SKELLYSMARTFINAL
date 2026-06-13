// ============================================================
//  SmartHome — script.js
//  2000+ annonces générées + filtres dynamiques
// ============================================================
 
const rues = [
  "Montmartre","Belleville","Nation","Oberkampf","Bastille","République","Pigalle",
  "Marais","La Défense","Vincennes","Châtelet","Alésia","Jussieu","Daumesnil",
  "Strasbourg-Saint-Denis","Boulogne","Pantin","Levallois","Gambetta","Cachan",
  "Châtillon","Montreuil","Opéra","Charonne","Saint-Denis","Issy-les-Moulineaux",
  "Père Lachaise","Nanterre","Montparnasse","Ivry","Courbevoie","Neuilly","Créteil",
  "Clichy","Convention","Villejuif","Aubervilliers","Invalides","Vitry","Bagneux",
  "Lamarck","Bobigny","Meudon","Gobelins","Suresnes","Exelmans","Arcueil",
  "Noisy-le-Grand","Vanves","Argenteuil","Malakoff","Trocadéro","Saint-Ouen",
  "Massy","Clamart","Gennevilliers","Rueil-Malmaison","Temple","Colombes","Antony",
  "Tolbiac","Versailles","Asnières","Kremlin-Bicêtre","Châtenay","Passy","Noisy-le-Sec",
  "Voltaire","Arts et Métiers","Sartrouville","Marne-la-Vallée",
  "Évry","Maisons-Alfort","Censier","Poissy","Montrouge","Bondy","Fontenay-aux-Roses",
  "Gif-sur-Yvette","Choisy-le-Roi","Wagram","Le Perreux","Saint-Maur","Joinville",
  "Champigny","Villemomble","Drancy","Aulnay","Épinay","Stains",
  "Bagnolet","Romainville","Les Lilas","Nogent","Fontenay-sous-Bois","Vincennes-Bois",
  "Saint-Mandé","Charenton","Orly","Rungis","L'Haÿ-les-Roses"
];
 
const villes = [
  "Paris 1er","Paris 2e","Paris 3e","Paris 4e","Paris 5e","Paris 6e","Paris 7e",
  "Paris 8e","Paris 9e","Paris 10e","Paris 11e","Paris 12e","Paris 13e","Paris 14e",
  "Paris 15e","Paris 16e","Paris 17e","Paris 18e","Paris 19e","Paris 20e",
  "Boulogne 92","Neuilly 92","Levallois 92","Clichy 92","Courbevoie 92",
  "Asnières 92","Colombes 92","Nanterre 92","Rueil 92","Suresnes 92",
  "Issy 92","Vanves 92","Malakoff 92","Montrouge 92","Châtillon 92",
  "Bagneux 92","Fontenay 92","Antony 92","Clamart 92","Meudon 92",
  "Châtenay 92","Vincennes 94","Créteil 94","Ivry 94","Vitry 94",
  "Choisy 94","Arcueil 94","Cachan 94","Villejuif 94","Kremlin-Bicêtre 94",
  "Fontenay 94","Maisons-Alfort 94","Saint-Maur 94","Charenton 94",
  "Saint-Denis 93","Montreuil 93","Pantin 93","Aubervilliers 93",
  "Saint-Ouen 93","Bobigny 93","Noisy-le-Grand 93","Bondy 93",
  "Gennevilliers 92","Épinay 93","Drancy 93","Aulnay 93",
  "Versailles 78","Sartrouville 78","Poissy 78",
  "Massy 91","Évry 91","Gif-sur-Yvette 91","Marne-la-Vallée 77","Argenteuil 95"
];
 
const types = ["Studio","T1","T2","T3","Colocation"];
 
const badges = [
  { label: "Nouveau",   cls: "badge-new"    },
  { label: "Étudiant",  cls: "badge-student" },
  { label: "Populaire", cls: "badge-hot"     },
  { label: "",          cls: ""              },
  { label: "",          cls: ""              },
  { label: "",          cls: ""              },
];
 
// Chaque description est liée à une photo précise qui lui correspond
const annoncesParType = {

  Studio: [
    { desc: "Studio lumineux avec coin cuisine ouvert, parquet chêne, grande fenêtre.", img: "images/Studio.jpg" },
    { desc: "Studio meublé moderne, lit en mezzanine, bureau intégré, idéal étudiant.", img: "images/Studio.jpg" },
    { desc: "Studio haussmannien avec moulures, parquet d'époque, lumineux, charges comprises.", img: "images/Studio.jpg" },
    { desc: "Studio avec balcon, vue sur cour intérieure arborée, lave-linge inclus.", img: "images/Studio.jpg" },
    { desc: "Studio tout équipé en résidence récente, digicode, interphone vidéo, calme.", img: "images/Studio.jpg" },
    { desc: "Studio cosy, kitchenette aménagée, salle de bain refaite, disponible de suite.", img: "images/Studio.jpg" },
    { desc: "Studio en rez-de-chaussée surélevé avec jardinette privative, très calme.", img: "images/Studio.jpg" },
    { desc: "Studio lumineux double vitrage, cuisine américaine neuve, fibre optique incluse.", img: "images/Studio.jpg" },
  ],

  T1: [
    { desc: "T1 lumineux avec salon séparé, cuisine équipée, cave incluse, proche métro.", img: "images/T1.jpg" },
    { desc: "T1 haussmannien, hauts plafonds 3m, moulures, parquet point de Hongrie.", img: "images/T1.jpg" },
    { desc: "T1 refait à neuf, cuisine ouverte avec îlot central, salle de bain italienne.", img: "images/T1.jpg" },
    { desc: "T1 traversant double exposition, calme absolu, parquet, gardien, cave.", img: "images/T1.jpg" },
    { desc: "T1 dans résidence moderne, ascenseur, parking en option, proche RER.", img: "images/T1.jpg" },
    { desc: "T1 calme en cour intérieure, parquet, cuisine aménagée, idéal jeune actif.", img: "images/T1.jpg" },
  ],

  T2: [
    { desc: "T2 avec grand salon lumineux, chambre séparée, parquet chêne, balcon.", img: "images/T2.jpg" },
    { desc: "T2 rénové, cuisine ouverte sur séjour, chambre avec dressing, cave et parking.", img: "images/T2.jpg" },
    { desc: "T2 haussmannien, moulures d'époque, parquet, chambre avec alcôve, très charmant.", img: "images/T2.jpg" },
    { desc: "T2 meublé coup de cœur, verrière cuisine, terrasse privative de 10m².", img: "images/T2.jpg" },
    { desc: "T2 hypercentre, vue sur toits parisiens, double séjour, digicode, cave.", img: "images/T2.jpg" },
    { desc: "Grand T2 lumineux, deux expositions, cuisine équipée, gardien, très calme.", img: "images/T2.jpg" },
  ],

  T3: [
    { desc: "Grand T3, séjour de 30m², deux chambres, balcon filant, parking, gardien.", img: "images/T3.jpg" },
    { desc: "T3 familial traversant, cuisine indépendante équipée, deux chambres, cave.", img: "images/T3.jpg" },
    { desc: "T3 atypique ex-atelier d'artiste, verrière, 3,5m sous plafond, poutres apparentes.", img: "images/T3.jpg" },
    { desc: "T3 dans bel immeuble bourgeois, double salon, parquet d'époque, gardien.", img: "images/T3.jpg" },
    { desc: "T3 familial avec jardin privatif de 20m², deux chambres, garage, très calme.", img: "images/T3.jpg" },
    { desc: "T3 lumineux entièrement rénové, cuisine américaine, salle de bain refaite.", img: "images/T3.jpg" },
  ],

  Colocation: [
    { desc: "Chambre meublée dans coloc de 3, salon partagé moderne, cuisine équipée.", img: "images/Chambre.jpg" },
    { desc: "Chambre dans grande coloc 4 personnes, internet fibre inclus, proche transports.", img: "images/Chambre.jpg" },
    { desc: "Chambre dans loft atypique, espaces communs spacieux, ambiance artistes.", img: "images/Chambre.jpg" },
    { desc: "Chambre en résidence étudiante, salle de bain privative, cafétéria sur place.", img: "images/Chambre.jpg" },
    { desc: "Chambre dans villa avec jardin partagé, coloc calme et sérieuse, proche bois.", img: "images/Chambre.jpg" },
    { desc: "Chambre lumineuse dans appartement 5 pièces, 4 colocataires sympas, proche fac.", img: "images/Chambre.jpg" },
    { desc: "Chambre dans appartement spacieux, coloc internationale, cuisine moderne partagée.", img: "images/Chambre.jpg" },
    { desc: "Chambre cosy avec bureau intégré, idéal télétravail, wifi fibre, calme.", img: "images/Chambre.jpg" },
  ],
};
 
const prixBase = { Studio: 600, T1: 850, T2: 1200, T3: 1700, Colocation: 420 };
 
// ── Génération des 2000+ annonces ──
function rand(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}
function pick(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}
 
const annonces = [];
for (let i = 0; i < 2000; i++) {
  const type    = pick(types);
  const rue     = pick(rues);
  const ville   = pick(villes);
  const badge   = pick(badges);
  const meuble  = Math.random() > 0.4;
 
  // On choisit une paire desc+photo cohérente
  const paire = pick(annoncesParType[type]);
 
  const surfaceMap = {
    Studio: rand(15,30), T1: rand(25,40),
    T2: rand(38,60),     T3: rand(55,85),
    Colocation: rand(10,18)
  };
  const surface   = surfaceMap[type];
  const variation = rand(-80, 250);
  const prix      = prixBase[type] + variation;
 
  annonces.push({
    titre     : `${type} — ${rue}`,
    ville,
    surface,
    prix,
    meuble,
    type,
    badge     : badge.label,
    badgeClass: badge.cls,
    img       : paire.img,
    desc      : paire.desc,
  });
}
 
// ── État des filtres ──
let filtreType  = "tous";
let filtrePrix  = "tous";
let filtreVille = "";
let page        = 1;
const PAR_PAGE  = 24;
 
// ── Injection des filtres (une seule fois) ──
function injecterFiltres() {
  if (document.getElementById("filtres")) return;
 
  const section = document.getElementById("types");
  const titre   = section.querySelector(".section-title");
 
  const html = `
    <div id="filtres" style="
      display:flex; flex-wrap:wrap; gap:12px; margin:1.5rem 0 2rem;
      align-items:center;
    ">
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        ${["tous","Studio","T1","T2","T3","Colocation"].map(t => `
          <button class="filtre-btn ${t === filtreType ? "actif" : ""}"
            data-type="${t}" onclick="setType('${t}')">
            ${t === "tous" ? "Tous" : t}
          </button>
        `).join("")}
      </div>
 
      <select onchange="setPrix(this.value)" style="
        padding:8px 14px; border-radius:8px; border:1px solid #e0e0e0;
        font-size:13px; cursor:pointer; background:#fff;
      ">
        <option value="tous">Tous les prix</option>
        <option value="0-500">Moins de 500€</option>
        <option value="500-800">500€ – 800€</option>
        <option value="800-1200">800€ – 1 200€</option>
        <option value="1200-2000">1 200€ – 2 000€</option>
        <option value="2000-9999">Plus de 2 000€</option>
      </select>
 
      <input
        type="text"
        placeholder="🔍 Filtrer par ville..."
        oninput="setVille(this.value)"
        style="padding:8px 14px;border-radius:8px;border:1px solid #e0e0e0;
               font-size:13px;min-width:200px;outline:none;"
      />
 
      <span id="compteur" style="margin-left:auto;font-size:13px;color:#999;"></span>
    </div>
  `;
 
  titre.insertAdjacentHTML("afterend", html);
}
 
// ── Filtrage ──
function annoncesFiltrees() {
  return annonces.filter(a => {
    const okType  = filtreType === "tous" || a.type === filtreType;
    const okVille = filtreVille === "" ||
                    a.ville.toLowerCase().includes(filtreVille.toLowerCase()) ||
                    a.titre.toLowerCase().includes(filtreVille.toLowerCase());
    let okPrix = true;
    if (filtrePrix !== "tous") {
      const [min, max] = filtrePrix.split("-").map(Number);
      okPrix = a.prix >= min && a.prix <= max;
    }
    return okType && okVille && okPrix;
  });
}
 
// ── Rendu des cartes ──
// ── Rendu des cartes (CORRIGÉ POUR L'ALIGNEMENT PARFAIT) ──
function rendrePage() {
  const grid    = document.getElementById("annonces-grid");
  const liste   = annoncesFiltrees();
  const debut   = (page - 1) * PAR_PAGE;
  const tranche = liste.slice(debut, debut + PAR_PAGE);
 
  grid.innerHTML = tranche.length === 0
    ? `<p style="grid-column:1/-1;text-align:center;color:#999;padding:3rem 0;">
         Aucune annonce ne correspond à vos filtres.
       </p>`
    : tranche.map(a => {
        const badge  = a.badge
          ? `<span class="listing-badge ${a.badgeClass}">${a.badge}</span>`
          : "";
        const meuble = a.meuble ? "Meublé" : "Non meublé";
        
       return `
  <div class="listing-card">
    <div class="listing-img-wrap">
      <img
        src="${a.img}"
        alt="${a.titre}"
        loading="lazy"
        onerror="this.style.background='#eef2f0';this.removeAttribute('src');"
      />
      ${badge}
    </div>
    <div class="listing-body">
      <h3>${a.titre}</h3>
      
      <div class="listing-location">— ${a.ville}</div>
      
      <div class="listing-tags">
        <span>${a.surface} m²</span>
        <span>${a.type}</span>
        <span>${meuble}</span>
      </div>
      
      <p>${a.desc}</p>
      
      <div class="listing-footer">
        <strong class="listing-price">${a.prix}€<span>/mois</span></strong>
        <a href="#" class="listing-btn">Voir →</a>
      </div>
    </div>
  </div>
`;
      }).join("");
 
  const compteur = document.getElementById("compteur");
  if (compteur) {
    compteur.textContent = `${liste.length} annonce${liste.length > 1 ? "s" : ""}`;
  }
 
  rendrePagination(liste.length);
}
 
// ── Pagination ──
function rendrePagination(total) {
  let pagi = document.getElementById("pagination");
  if (!pagi) {
    pagi = document.createElement("div");
    pagi.id = "pagination";
    pagi.style.cssText =
      "display:flex;justify-content:center;gap:8px;margin:2rem 0;flex-wrap:wrap;";
    document.getElementById("annonces-grid").after(pagi);
  }
 
  const totalPages = Math.ceil(total / PAR_PAGE);
  if (totalPages <= 1) { pagi.innerHTML = ""; return; }
 
  const bs = (actif = false) =>
    `padding:8px 14px;border-radius:8px;border:1px solid ${actif ? "#534AB7" : "#e0e0e0"};
     cursor:pointer;background:${actif ? "#EEEDFE" : "#fff"};
     color:${actif ? "#534AB7" : "#333"};font-size:13px;font-weight:${actif ? "600" : "400"};`;
 
  let html = "";
  html += `<button onclick="goPage(${page - 1})" ${page === 1 ? "disabled" : ""} style="${bs()}">← Préc.</button>`;
 
  const debut = Math.max(1, page - 3);
  const fin   = Math.min(totalPages, page + 3);
 
  if (debut > 1) {
    html += `<button onclick="goPage(1)" style="${bs()}">1</button>`;
    if (debut > 2) html += `<span style="padding:8px 4px;color:#999;">…</span>`;
  }
 
  for (let p = debut; p <= fin; p++) {
    html += `<button onclick="goPage(${p})" style="${bs(p === page)}">${p}</button>`;
  }
 
  if (fin < totalPages) {
    if (fin < totalPages - 1) html += `<span style="padding:8px 4px;color:#999;">…</span>`;
    html += `<button onclick="goPage(${totalPages})" style="${bs()}">${totalPages}</button>`;
  }
 
  html += `<button onclick="goPage(${page + 1})" ${page === totalPages ? "disabled" : ""} style="${bs()}">Suiv. →</button>`;
 
  pagi.innerHTML = html;
}
 
// ── Handlers ──
function setType(t) {
  filtreType = t;
  page = 1;
  document.querySelectorAll(".filtre-btn").forEach(b => {
    b.classList.toggle("actif", b.dataset.type === t);
  });
  rendrePage();
}
 
function setPrix(v) {
  filtrePrix = v;
  page = 1;
  rendrePage();
}
 
function setVille(v) {
  filtreVille = v;
  page = 1;
  rendrePage();
}
 
function goPage(p) {
  const total = annoncesFiltrees().length;
  const max   = Math.ceil(total / PAR_PAGE);
  if (p < 1 || p > max) return;
  page = p;
  rendrePage();
  document.getElementById("types").scrollIntoView({ behavior: "smooth", block: "start" });
}
 
// ── Init ──
document.addEventListener("DOMContentLoaded", () => {
  injecterFiltres();
  rendrePage();
});